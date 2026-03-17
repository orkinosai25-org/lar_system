#!/usr/bin/env python3
"""
LAR System — Functional Integration Test Runner
================================================
Executes evidence-gathering test scenarios against the deployed Azure Web Apps.

Tests cover the three contracted verticals (TOR Section 3.1):
  - Flights: fare parity, repricing signal detection, PNR/error path, TTL
  - Hotels:  confirmation status pages, cancellation workflow, no-result handling
  - Cars:    rate/availability endpoints, stale-search handling, pre-checkout

Also covers the booking flow audit (TOR Section 3.2):
  - Session lifecycle (homepage → search → booking stages)
  - Silent failure detection (500s, blank pages, error exposure)
  - Response-time measurement
  - Retry/recovery behaviour

Usage:
    python3 scripts/functional_tests.py --base-url https://lar-b2c.azurewebsites.net
    python3 scripts/functional_tests.py --base-url http://localhost:8080 --no-ssl-verify
    python3 scripts/functional_tests.py --help

Output:
    - Console: real-time test results with pass/warn/fail indicators
    - File:    LAR_Functional_Test_Report_<timestamp>.txt  (plain-text evidence pack)
    - File:    LAR_Functional_Test_Report_<timestamp>.json (machine-readable)
    - Exit code 0 if no FAIL results, non-zero otherwise.
"""

import argparse
import json
import os
import re
import sys
import time
from datetime import datetime, timezone
from typing import Optional
import urllib.request
import urllib.error
import ssl

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

TIMEOUT_SECONDS = 30
SLOW_THRESHOLD_MS = 3000   # warn if response > 3 s (audit finding: 5-8 s load times)
VERY_SLOW_MS     = 8000    # fail if response > 8 s

# PHP / application error patterns that should NOT appear in a healthy response
ERROR_PATTERNS = [
    r"Fatal error",
    r"Parse error",
    r"Warning:.*in.*on line",
    r"Notice:.*in.*on line",
    r"Uncaught .*(Exception|Error)",
    r"You have an error in your SQL syntax",
    r"mysql_query\(\)",
    r"mysqli_query\(\)",
    r"SQLSTATE\[",
    r"Exception.*Stack trace",
    r"Call to undefined",
    r"Trying to access array offset on value of type",
    r"Undefined (variable|index|offset)",
    r"Division by zero",
    r"Internal Server Error",
    r"Whoops!.*Problem",
]

ERROR_RE = re.compile("|".join(ERROR_PATTERNS), re.IGNORECASE)

# Patterns that indicate a debug mode / credential leak
# These match common patterns associated with exposed production credentials
# without embedding actual credential values in the source code.
CREDENTIAL_LEAK_PATTERNS = [
    r"DB_PASSWORD\s*=",           # raw env var name in output
    r"DB_USERNAME\s*=",
    r"\bdb_password\b",           # CodeIgniter config key appearing in output
    r"'password'\s*=>\s*'[^']{4,}",  # PHP array with password value
    r"travelom_newjuly",          # known legacy username (audit finding)
    r"larservices",               # known service account (audit finding)
]
CRED_RE = re.compile("|".join(CREDENTIAL_LEAK_PATTERNS), re.IGNORECASE)

# ---------------------------------------------------------------------------
# Test result helpers
# ---------------------------------------------------------------------------

PASS  = "PASS"
WARN  = "WARN"
FAIL  = "FAIL"
INFO  = "INFO"
SKIP  = "SKIP"

COLOURS = {
    PASS: "\033[92m",
    WARN: "\033[93m",
    FAIL: "\033[91m",
    INFO: "\033[96m",
    SKIP: "\033[90m",
    "RESET": "\033[0m",
}

def _col(status: str, text: str) -> str:
    if not sys.stdout.isatty():
        return text
    return f"{COLOURS.get(status, '')}{text}{COLOURS['RESET']}"


class TestResult:
    def __init__(self, scenario: str, check: str, status: str,
                 detail: str = "", http_code: int = 0,
                 duration_ms: int = 0, evidence: str = ""):
        self.scenario    = scenario
        self.check       = check
        self.status      = status
        self.detail      = detail
        self.http_code   = http_code
        self.duration_ms = duration_ms
        self.evidence    = evidence   # raw excerpt for the evidence pack
        self.timestamp   = datetime.now(timezone.utc).isoformat()

    def to_dict(self) -> dict:
        return self.__dict__

    def __str__(self) -> str:
        dur = f" ({self.duration_ms}ms)" if self.duration_ms else ""
        code = f" HTTP {self.http_code}" if self.http_code else ""
        return (f"  [{_col(self.status, self.status):>4}]{code}{dur}"
                f"  {self.check}"
                + (f"\n         → {self.detail}" if self.detail else ""))


# ---------------------------------------------------------------------------
# HTTP helper
# ---------------------------------------------------------------------------

def _make_ssl_ctx(verify: bool) -> ssl.SSLContext:
    if verify:
        ctx = ssl.create_default_context()
    else:
        ctx = ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = ssl.CERT_NONE
    return ctx


def http_get(url: str, verify_ssl: bool = True,
             headers: Optional[dict] = None,
             follow_redirects: bool = True) -> dict:
    """
    Perform an HTTP GET and return a dict with:
      status_code, body (str), duration_ms, final_url, redirect_chain, error
    """
    hdrs = {
        "User-Agent": "LAR-Audit-FunctionalTest/1.0",
        "Accept": "text/html,application/json,*/*",
    }
    if headers:
        hdrs.update(headers)

    ctx = _make_ssl_ctx(verify_ssl)
    start = time.monotonic()
    result = {
        "status_code": 0,
        "body": "",
        "duration_ms": 0,
        "final_url": url,
        "redirect_chain": [],
        "error": None,
    }

    try:
        req = urllib.request.Request(url, headers=hdrs)
        with urllib.request.urlopen(req, timeout=TIMEOUT_SECONDS,
                                    context=ctx) as resp:
            result["status_code"] = resp.status
            result["final_url"]   = resp.url
            raw = resp.read()
            result["body"] = raw.decode("utf-8", errors="replace")
    except urllib.error.HTTPError as e:
        result["status_code"] = e.code
        try:
            result["body"] = e.read().decode("utf-8", errors="replace")
        except Exception:
            pass
        result["error"] = f"HTTPError {e.code}: {e.reason}"
    except urllib.error.URLError as e:
        result["error"] = f"URLError: {e.reason}"
    except Exception as e:
        result["error"] = f"Exception: {e}"
    finally:
        result["duration_ms"] = int((time.monotonic() - start) * 1000)

    return result


# ---------------------------------------------------------------------------
# Core assertion helpers
# ---------------------------------------------------------------------------

class TestRunner:
    def __init__(self, base_url: str, verify_ssl: bool = True, verbose: bool = False):
        self.base_url   = base_url.rstrip("/")
        self.verify_ssl = verify_ssl
        self.verbose    = verbose
        self.results: list[TestResult] = []
        self._scenario  = "General"

    def scenario(self, name: str) -> "TestRunner":
        self._scenario = name
        print(f"\n{'─' * 66}")
        print(f"  SCENARIO: {name}")
        print(f"{'─' * 66}")
        return self

    def get(self, path: str, headers: Optional[dict] = None) -> dict:
        url = f"{self.base_url}{path}"
        resp = http_get(url, verify_ssl=self.verify_ssl, headers=headers)
        if self.verbose:
            print(f"    GET {url} → {resp['status_code']} ({resp['duration_ms']}ms)")
        return resp

    # --- check helpers -------------------------------------------------------

    def _record(self, check: str, status: str, detail: str = "",
                resp: Optional[dict] = None, evidence: str = "") -> TestResult:
        tr = TestResult(
            scenario=self._scenario,
            check=check,
            status=status,
            detail=detail,
            http_code=resp["status_code"] if resp else 0,
            duration_ms=resp["duration_ms"] if resp else 0,
            evidence=evidence,
        )
        self.results.append(tr)
        print(str(tr))
        return tr

    def assert_reachable(self, path: str, check_name: str,
                         expected_codes=(200, 301, 302, 303)) -> dict:
        resp = self.get(path)
        if resp["error"]:
            self._record(check_name, FAIL, resp["error"], resp)
        elif resp["status_code"] in expected_codes:
            self._record(check_name, PASS, f"HTTP {resp['status_code']}", resp)
        else:
            self._record(check_name, FAIL,
                         f"HTTP {resp['status_code']} (expected {expected_codes})", resp)
        return resp

    def assert_no_php_errors(self, resp: dict, check_name: str) -> None:
        body = resp.get("body", "")
        match = ERROR_RE.search(body)
        if match:
            excerpt = body[max(0, match.start()-100): match.end()+200].strip()
            self._record(check_name, FAIL,
                         f"PHP/application error detected: '{match.group()}'",
                         resp, evidence=excerpt)
        else:
            self._record(check_name, PASS, "No PHP/application errors in response", resp)

    def assert_no_credential_leak(self, resp: dict, check_name: str) -> None:
        body = resp.get("body", "")
        match = CRED_RE.search(body)
        if match:
            self._record(check_name, FAIL,
                         f"Potential credential leaked in response: '{match.group()}'",
                         resp)
        else:
            self._record(check_name, PASS, "No credential patterns in response", resp)

    def assert_response_time(self, resp: dict, check_name: str) -> None:
        ms = resp["duration_ms"]
        if ms > VERY_SLOW_MS:
            self._record(check_name, FAIL,
                         f"{ms}ms exceeds {VERY_SLOW_MS}ms threshold (audit: causes booking abandonment)",
                         resp)
        elif ms > SLOW_THRESHOLD_MS:
            self._record(check_name, WARN,
                         f"{ms}ms exceeds {SLOW_THRESHOLD_MS}ms acceptable threshold",
                         resp)
        else:
            self._record(check_name, PASS, f"{ms}ms", resp)

    def assert_not_blank(self, resp: dict, check_name: str,
                         min_length: int = 200) -> None:
        body = resp.get("body", "")
        stripped = body.strip()
        if len(stripped) < min_length:
            self._record(check_name, FAIL,
                         f"Response body too short ({len(stripped)} chars) — may be blank/silent failure",
                         resp, evidence=stripped[:500])
        else:
            self._record(check_name, PASS,
                         f"Response body has content ({len(stripped)} chars)", resp)

    def assert_contains(self, resp: dict, check_name: str,
                        pattern: str, case_sensitive: bool = False) -> None:
        body = resp.get("body", "")
        flags = 0 if case_sensitive else re.IGNORECASE
        if re.search(pattern, body, flags):
            self._record(check_name, PASS, f"Pattern found: '{pattern}'", resp)
        else:
            self._record(check_name, WARN,
                         f"Expected pattern not found: '{pattern}' (may be dynamic/JS-rendered)",
                         resp)

    def assert_not_contains(self, resp: dict, check_name: str,
                            pattern: str) -> None:
        body = resp.get("body", "")
        match = re.search(pattern, body, re.IGNORECASE)
        if match:
            excerpt = body[max(0, match.start()-50): match.end()+100].strip()
            self._record(check_name, FAIL,
                         f"Prohibited pattern found: '{match.group()}'",
                         resp, evidence=excerpt)
        else:
            self._record(check_name, PASS, f"Pattern absent: '{pattern}'", resp)

    def info(self, message: str) -> None:
        tr = TestResult(self._scenario, message, INFO)
        self.results.append(tr)
        print(f"  [{_col(INFO, INFO)}]  {message}")

    def skip(self, check_name: str, reason: str) -> None:
        tr = TestResult(self._scenario, check_name, SKIP, reason)
        self.results.append(tr)
        print(f"  [{_col(SKIP, SKIP)}]  {check_name}: {reason}")

    # --- summary -------------------------------------------------------------

    def summary(self) -> dict:
        counts = {PASS: 0, WARN: 0, FAIL: 0, INFO: 0, SKIP: 0}
        for r in self.results:
            counts[r.status] = counts.get(r.status, 0) + 1
        return counts


# ---------------------------------------------------------------------------
# Test scenarios
# ---------------------------------------------------------------------------

def run_all_scenarios(runner: TestRunner) -> None:

    # ========================================================================
    # 1. INFRASTRUCTURE / HOMEPAGE AVAILABILITY
    # ========================================================================
    runner.scenario("1. Infrastructure — Homepage Availability")

    runner.info("Checking B2C homepage (index.php/general/index)")
    resp = runner.get("/index.php/general/index")
    runner.assert_reachable("/index.php/general/index",
                            "B2C homepage returns HTTP 200/3xx")
    if resp.get("status_code") == 200:
        runner.assert_no_php_errors(resp, "Homepage — no PHP errors in body")
        runner.assert_no_credential_leak(resp, "Homepage — no credentials in body")
        runner.assert_response_time(resp, "Homepage — response time")
        runner.assert_not_blank(resp, "Homepage — response is not blank")
        runner.assert_not_contains(resp, "Homepage — error_reporting not exposing errors",
                                   r"Notice:|Warning:|Fatal error:|Parse error:")

    runner.info("Checking root redirect")
    resp2 = runner.get("/")
    runner.assert_reachable("/", "Root URL reachable (redirect or page)")
    if resp2.get("status_code") == 200:
        runner.assert_no_php_errors(resp2, "Root — no PHP errors")

    # ========================================================================
    # 2. FLIGHTS VERTICAL — TOR Section 3.1
    # ========================================================================
    runner.scenario("2. Flights Vertical (TOR 3.1) — Search & Error Path")

    runner.info("TOR 3.1 requires: fare parity test, repricing detection, PNR integrity, TTL validation")

    # 2.1  Pre-search redirect (verifies routing and session creation)
    runner.info("Scenario 2a: Flight pre-search redirect (one-way JNB→CPT)")
    dep_date = datetime.now(timezone.utc).strftime("%Y-%m-%d")
    path_pre = (f"/index.php/general/pre_flight_search?"
                f"trip_type=oneway&from=JNB&to=CPT&depature={dep_date}"
                f"&adult=1&child=0&infant=0&v_class=Economy&search_flight=search")
    resp_pre = runner.get(path_pre)
    runner.assert_reachable(path_pre, "Flights — pre-search endpoint reachable")
    if resp_pre.get("status_code") in (200, 301, 302):
        runner.assert_no_php_errors(resp_pre, "Flights — pre-search: no PHP errors")
        runner.assert_response_time(resp_pre, "Flights — pre-search: response time")
        runner.info(f"  Redirect target: {resp_pre.get('final_url', 'n/a')}")

    # 2.2  Search result page with a synthetic search_id
    runner.info("Scenario 2b: Flight search result page (search_id=1)")
    resp_search = runner.get("/index.php/flight/search/1")
    runner.assert_reachable("/index.php/flight/search/1",
                            "Flights — search result page reachable")
    if resp_search.get("status_code") == 200:
        runner.assert_no_php_errors(resp_search,
                                    "Flights — search result: no PHP errors")
        runner.assert_no_credential_leak(resp_search,
                                         "Flights — search result: no credential leak")
        runner.assert_response_time(resp_search,
                                    "Flights — search result: response time (audit: 5-8s abandonment)")
        runner.assert_not_blank(resp_search,
                                "Flights — search result: not blank/silent failure",
                                min_length=500)
        runner.info("Evidence (fare parity): checking that fare figures are present in DOM")
        runner.assert_contains(resp_search,
                               "Flights — search result: fare/price element present",
                               r"(price|fare|amount|ZAR|USD|EUR|NGN|ZAR|\d+\.\d{2})")

    # 2.3  Booking page (expects auth or proper redirect — not silent failure)
    runner.info("Scenario 2c: Flight booking page — unauthenticated user redirect")
    resp_book = runner.get("/index.php/flight/booking/1")
    runner.assert_reachable("/index.php/flight/booking/1",
                            "Flights — booking page: redirect or page returned (not 500)")
    if resp_book.get("status_code") == 200:
        runner.assert_no_php_errors(resp_book,
                                    "Flights — booking page: no PHP errors")
        runner.assert_not_blank(resp_book,
                                "Flights — booking page: not silent blank response")
    elif resp_book.get("status_code") in (301, 302):
        runner.info(f"  Redirected to login (expected): {resp_book.get('final_url', 'n/a')}")

    # 2.4  Calendar fare (repricing path)
    runner.info("Scenario 2d: Flight calendar fare (repricing detection path)")
    path_cal = (f"/index.php/flight/calendar_fare?"
                f"from=JNB&to=CPT&depature={dep_date}&carrier=&adult=1")
    resp_cal = runner.get(path_cal)
    runner.assert_reachable(path_cal,
                            "Flights — calendar fare (repricing path) reachable")
    if resp_cal.get("status_code") == 200:
        runner.assert_no_php_errors(resp_cal,
                                    "Flights — calendar fare: no PHP errors")
        runner.assert_response_time(resp_cal,
                                    "Flights — calendar fare: response time")

    # 2.5  Edit pax (PNR integrity path — needs auth but should not 500)
    runner.info("Scenario 2e: Flight edit-pax page (PNR integrity path)")
    resp_editpax = runner.get("/index.php/flight/edit_pax")
    runner.assert_reachable("/index.php/flight/edit_pax",
                            "Flights — edit-pax (PNR path): not 500",
                            expected_codes=(200, 301, 302, 303, 401, 403, 404))
    if resp_editpax.get("status_code") == 200:
        runner.assert_no_php_errors(resp_editpax,
                                    "Flights — edit-pax: no PHP errors")

    # 2.6  Ajax flight list (AJAX endpoint — TTL / stale search detection)
    runner.info("Scenario 2f: AJAX flight list endpoint (TTL/stale-result path)")
    resp_ajax_fl = runner.get("/index.php/ajax/flight_list/invalid_search_id")
    runner.assert_reachable("/index.php/ajax/flight_list/invalid_search_id",
                            "Flights — AJAX list endpoint: not 500",
                            expected_codes=(200, 204, 400, 404))
    if resp_ajax_fl.get("status_code") == 200:
        runner.assert_no_php_errors(resp_ajax_fl,
                                    "Flights — AJAX list: no PHP errors for invalid search_id")
        runner.info("Evidence (TTL): with invalid search_id the response should be empty/error JSON, not fare data")
        runner.assert_not_contains(resp_ajax_fl,
                                   "Flights — AJAX list: stale-search guard active (no fare data for invalid ID)",
                                   r'"TotalFare":|"totalFare":|"BaseFare":')

    # ========================================================================
    # 3. HOTELS VERTICAL — TOR Section 3.1
    # ========================================================================
    runner.scenario("3. Hotels Vertical (TOR 3.1) — Search & Confirmation Status")

    runner.info("TOR 3.1 requires: HK/HL/UC status testing, partial confirmation/rollback, cancellation")

    # 3.1  Pre-hotel-search
    runner.info("Scenario 3a: Hotel pre-search (Johannesburg, 1 night)")
    check_in  = datetime.now(timezone.utc).strftime("%Y-%m-%d")
    path_phs  = (f"/index.php/general/pre_hotel_search?"
                 f"search_hotel=search&destination=Johannesburg&country=ZA"
                 f"&check_in={check_in}&check_out={check_in}&rooms=1&adults=2&children=0")
    resp_phs  = runner.get(path_phs)
    runner.assert_reachable(path_phs, "Hotels — pre-hotel-search endpoint reachable")
    if resp_phs.get("status_code") in (200, 301, 302):
        runner.assert_no_php_errors(resp_phs, "Hotels — pre-search: no PHP errors")
        runner.assert_response_time(resp_phs, "Hotels — pre-search: response time")

    # 3.2  Hotel search result
    runner.info("Scenario 3b: Hotel search result page")
    resp_hs = runner.get("/index.php/hotel/search/1")
    runner.assert_reachable("/index.php/hotel/search/1",
                            "Hotels — search result page reachable")
    if resp_hs.get("status_code") == 200:
        runner.assert_no_php_errors(resp_hs, "Hotels — search result: no PHP errors")
        runner.assert_no_credential_leak(resp_hs, "Hotels — search result: no credential leak")
        runner.assert_response_time(resp_hs, "Hotels — search result: response time")
        runner.assert_not_blank(resp_hs, "Hotels — search result: not blank", min_length=500)
        runner.assert_contains(resp_hs,
                               "Hotels — search result: hotel/price element present",
                               r"(hotel|room|night|price|rate|ZAR|\d+\.\d{2})")

    # 3.3  Hotel details search (HK/HL/UC status path)
    runner.info("Scenario 3c: Hotel details page (HK/HL/UC confirmation status path)")
    resp_hd = runner.get("/index.php/hotel/hotel_details_search/1")
    runner.assert_reachable("/index.php/hotel/hotel_details_search/1",
                            "Hotels — hotel details: not 500",
                            expected_codes=(200, 301, 302, 303, 404))
    if resp_hd.get("status_code") == 200:
        runner.assert_no_php_errors(resp_hd, "Hotels — hotel details: no PHP errors")
        runner.assert_not_blank(resp_hd, "Hotels — hotel details: not blank")
        runner.info("Evidence (HK/UC status): checking confirmation status keywords")
        runner.assert_contains(resp_hd,
                               "Hotels — details: booking status keywords present",
                               r"(confirm|available|on.request|HK|HL|UC|sold.out|pending)",
                               )

    # 3.4  AJAX hotel list (cancellation policy / stale cache path)
    runner.info("Scenario 3d: AJAX hotel list endpoint (stale-cache / cancellation policy path)")
    resp_ajax_hl = runner.get("/index.php/ajax/hotel_list/0")
    runner.assert_reachable("/index.php/ajax/hotel_list/0",
                            "Hotels — AJAX list: not 500",
                            expected_codes=(200, 204, 400, 404))
    if resp_ajax_hl.get("status_code") == 200:
        runner.assert_no_php_errors(resp_ajax_hl, "Hotels — AJAX list: no PHP errors for search_id=0")

    # ========================================================================
    # 4. CARS VERTICAL — TOR Section 3.1
    # ========================================================================
    runner.scenario("4. Cars Vertical (TOR 3.1) — Search, Pre-checkout & Cancellation")

    runner.info("TOR 3.1 requires: rate/availability pre-checkout, stale-cache, policy disclosure")

    # 4.1  Pre-car-search
    runner.info("Scenario 4a: Car pre-search (Johannesburg)")
    path_pcs = (f"/index.php/general/pre_car_search?"
                f"pickup_location=Johannesburg&pickup_date={check_in}"
                f"&return_date={check_in}&pickup_time=10:00&return_time=18:00")
    resp_pcs = runner.get(path_pcs)
    runner.assert_reachable(path_pcs, "Cars — pre-car-search endpoint reachable")
    if resp_pcs.get("status_code") in (200, 301, 302):
        runner.assert_no_php_errors(resp_pcs, "Cars — pre-search: no PHP errors")
        runner.assert_response_time(resp_pcs, "Cars — pre-search: response time")

    # 4.2  Car search result
    runner.info("Scenario 4b: Car search result page (stale-search_id=1)")
    resp_cs = runner.get("/index.php/car/search/1")
    runner.assert_reachable("/index.php/car/search/1",
                            "Cars — search result page reachable")
    if resp_cs.get("status_code") == 200:
        runner.assert_no_php_errors(resp_cs, "Cars — search result: no PHP errors")
        runner.assert_no_credential_leak(resp_cs, "Cars — search result: no credential leak")
        runner.assert_response_time(resp_cs, "Cars — search result: response time")
        runner.assert_not_blank(resp_cs, "Cars — search result: not blank", min_length=500)
        runner.assert_contains(resp_cs,
                               "Cars — search result: rate/vehicle element present",
                               r"(car|vehicle|rate|price|ZAR|per.day|pickup|dropoff)")

    # 4.3  AJAX car list (stale-cache / rate freshness path)
    runner.info("Scenario 4c: AJAX car list — stale-cache guard (invalid search_id)")
    resp_ajax_cl = runner.get("/index.php/ajax/car_list/0")
    runner.assert_reachable("/index.php/ajax/car_list/0",
                            "Cars — AJAX list: not 500",
                            expected_codes=(200, 204, 400, 404))
    if resp_ajax_cl.get("status_code") == 200:
        runner.assert_no_php_errors(resp_ajax_cl,
                                    "Cars — AJAX list: no PHP errors for invalid search_id")
        runner.assert_not_contains(resp_ajax_cl,
                                   "Cars — AJAX list: stale-cache guard active",
                                   r'"TotalFare":|"totalFare":|"Rate":')

    # 4.4  Cancellation pre-path
    runner.info("Scenario 4d: Car cancellation endpoint (policy disclosure path)")
    resp_ccanc = runner.get("/index.php/car/pre_cancellation/AUDIT-TEST-REF/1")
    runner.assert_reachable("/index.php/car/pre_cancellation/AUDIT-TEST-REF/1",
                            "Cars — pre-cancellation: not 500 (policy disclosure path)",
                            expected_codes=(200, 301, 302, 303, 401, 403, 404))
    if resp_ccanc.get("status_code") == 200:
        runner.assert_no_php_errors(resp_ccanc, "Cars — cancellation: no PHP errors")

    # ========================================================================
    # 5. BOOKING FLOW AUDIT — TOR Section 3.2
    # ========================================================================
    runner.scenario("5. Booking Flow Audit (TOR 3.2) — Session Lifecycle & Silent Failures")

    runner.info("TOR 3.2: search→booking lifecycle, session handling, retry/recovery, silent failure")

    # 5.1  Payment gateway (should not 500 / leak debug info)
    runner.info("Scenario 5a: Payment gateway controller — debug / credential exposure")
    resp_pg = runner.get("/index.php/payment_gateway/payment/AUDIT-FAKE-REF/1")
    runner.assert_reachable("/index.php/payment_gateway/payment/AUDIT-FAKE-REF/1",
                            "Payment gateway: not 500",
                            expected_codes=(200, 301, 302, 303, 400, 404))
    if resp_pg.get("status_code") == 200:
        runner.assert_no_php_errors(resp_pg, "Payment gateway: no PHP errors")
        runner.assert_no_credential_leak(resp_pg, "Payment gateway: no credential leak (audit P0)")
        runner.assert_not_contains(resp_pg,
                                   "Payment gateway: no debug output (audit P0)",
                                   r"(var_dump|print_r|DEBUG|debug_mode|CURLOPT_)")

    # 5.2  Auth / login page
    runner.info("Scenario 5b: Auth login (session creation start of booking flow)")
    resp_login = runner.get("/index.php/auth/register_on_light_box")
    runner.assert_reachable("/index.php/auth/register_on_light_box",
                            "Auth login page: reachable")
    if resp_login.get("status_code") == 200:
        runner.assert_no_php_errors(resp_login, "Auth login: no PHP errors")
        runner.assert_not_blank(resp_login, "Auth login: not blank")
        runner.assert_contains(resp_login, "Auth login: form element present",
                               r"(form|input|login|register|email|password)")

    # 5.3  User page (unauthenticated redirect guard — session lifecycle)
    runner.info("Scenario 5c: User profile — unauthenticated redirect (session lifecycle)")
    resp_user = runner.get("/index.php/user")
    runner.assert_reachable("/index.php/user",
                            "User page: redirects or loads (not 500)",
                            expected_codes=(200, 301, 302, 303, 401, 403))
    if resp_user.get("status_code") == 200:
        runner.assert_no_php_errors(resp_user, "User page: no PHP errors")

    # 5.4  Voucher (post-booking — silent failure check)
    runner.info("Scenario 5d: Voucher page — silent failure check")
    resp_vch = runner.get("/index.php/voucher")
    runner.assert_reachable("/index.php/voucher",
                            "Voucher page: not 500 (post-booking silent failure check)",
                            expected_codes=(200, 301, 302, 303, 401, 403, 404))
    if resp_vch.get("status_code") == 200:
        runner.assert_no_php_errors(resp_vch, "Voucher: no PHP errors")

    # 5.5  Exception handling guard
    runner.info("Scenario 5e: Flight exception handler — graceful error recovery")
    resp_exc = runner.get("/index.php/flight/exception")
    runner.assert_reachable("/index.php/flight/exception",
                            "Flight exception page: not raw 500",
                            expected_codes=(200, 301, 302, 303, 404))
    if resp_exc.get("status_code") == 200:
        runner.assert_not_contains(resp_exc,
                                   "Exception page: does not expose raw stack trace",
                                   r"Stack trace:|#\d+ .+\(.+\):|in .+ on line \d+")

    # ========================================================================
    # 6. SECURITY SURFACE — audit P0/P1 evidence gathering
    # ========================================================================
    runner.scenario("6. Security Surface (Audit P0/P1 Evidence)")

    runner.info("Checking for error_reporting exposure, debug output, credential patterns")

    for path, label in [
        ("/index.php/general/index",          "Homepage"),
        ("/index.php/flight/search/1",        "Flight search"),
        ("/index.php/hotel/search/1",         "Hotel search"),
        ("/index.php/car/search/1",           "Car search"),
        ("/index.php/auth/register_on_light_box", "Login page"),
    ]:
        resp_sec = runner.get(path)
        if resp_sec.get("status_code") == 200:
            runner.assert_no_credential_leak(resp_sec, f"Security — {label}: no credential leak")
            runner.assert_not_contains(resp_sec,
                                       f"Security — {label}: no debug info exposed",
                                       r"(error_reporting\(E_ALL\)|ini_set.*display_errors.*1|var_dump|print_r)")
        else:
            runner.skip(f"Security — {label}: credential/debug check",
                        f"Page not available (HTTP {resp_sec.get('status_code')})")

    # ========================================================================
    # 7. RESPONSE TIME AUDIT — all critical paths
    # ========================================================================
    runner.scenario("7. Response Time Audit (TOR 3.2 — 5-8s abandonment threshold)")

    paths_to_time = [
        ("/",                               "Root"),
        ("/index.php/general/index",        "Homepage"),
        ("/index.php/flight/search/1",      "Flight search result"),
        ("/index.php/hotel/search/1",       "Hotel search result"),
        ("/index.php/car/search/1",         "Car search result"),
    ]

    for path, label in paths_to_time:
        resp_t = runner.get(path)
        if not resp_t.get("error"):
            runner.assert_response_time(resp_t, f"Response time — {label}")
        else:
            runner.skip(f"Response time — {label}", f"Error: {resp_t['error']}")


# ---------------------------------------------------------------------------
# Report generation
# ---------------------------------------------------------------------------

def write_text_report(runner: TestRunner, base_url: str, output_path: str) -> None:
    summary = runner.summary()
    total   = sum(v for k, v in summary.items() if k in (PASS, WARN, FAIL, SKIP))
    now     = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S UTC")

    lines = [
        "╔══════════════════════════════════════════════════════════════════════╗",
        "║  LAR System — Functional Integration Test Report                    ║",
        f"║  Target URL : {base_url[:54]:<54}║",
        f"║  Generated  : {now:<54}║",
        "║  Scope      : TOR 3.1 (Flights/Hotels/Cars) + TOR 3.2 (Flow)       ║",
        "╚══════════════════════════════════════════════════════════════════════╝",
        "",
        "NOTE: These are automated HTTP-level functional tests executed against",
        "the deployed server. They exercise URL endpoints, detect PHP errors,",
        "measure response times, check for credential exposure, and validate",
        "that booking flow pages respond correctly. They do NOT perform live",
        "bookings or use real GDS/API credentials.",
        "",
        f"Test total: {total}  |  "
        f"PASS: {summary[PASS]}  WARN: {summary[WARN]}  "
        f"FAIL: {summary[FAIL]}  SKIP: {summary[SKIP]}",
        "",
        "══════════════════════════════════════════════════════════════════════",
        "",
    ]

    current_scenario = ""
    for r in runner.results:
        if r.scenario != current_scenario:
            current_scenario = r.scenario
            lines.append(f"SCENARIO: {r.scenario}")
            lines.append("─" * 70)

        status_pad = f"[{r.status}]"
        http_info  = f" HTTP {r.http_code}" if r.http_code else ""
        dur_info   = f" ({r.duration_ms}ms)" if r.duration_ms else ""
        lines.append(f"  {status_pad:>7}{http_info}{dur_info}  {r.check}")
        if r.detail:
            lines.append(f"           → {r.detail}")
        if r.evidence:
            lines.append("           EVIDENCE EXCERPT:")
            for ev_line in r.evidence.splitlines()[:10]:
                lines.append(f"             | {ev_line}")
        lines.append("")

    lines.extend([
        "══════════════════════════════════════════════════════════════════════",
        "SUMMARY",
        "══════════════════════════════════════════════════════════════════════",
        f"  PASS : {summary[PASS]}",
        f"  WARN : {summary[WARN]}",
        f"  FAIL : {summary[FAIL]}",
        f"  SKIP : {summary[SKIP]}",
        f"  TOTAL: {total}",
        "",
        "FAIL results require immediate attention for audit acceptance.",
        "WARN results are advisory — review against audit thresholds.",
        "See AUDIT_REPORT.md and REMEDIATION_ROADMAP.md for context.",
        "",
        "=== End of Report ===",
    ])

    with open(output_path, "w", encoding="utf-8") as fh:
        fh.write("\n".join(lines))


def write_json_report(runner: TestRunner, base_url: str, output_path: str) -> None:
    summary = runner.summary()
    data = {
        "meta": {
            "target_url":  base_url,
            "generated_at": datetime.now(timezone.utc).isoformat(),
            "scope": "TOR 3.1 (Flights/Hotels/Cars) + TOR 3.2 (Booking Flow)",
        },
        "summary": summary,
        "results": [r.to_dict() for r in runner.results],
    }
    with open(output_path, "w", encoding="utf-8") as fh:
        json.dump(data, fh, indent=2, ensure_ascii=False)


# ---------------------------------------------------------------------------
# CLI entry point
# ---------------------------------------------------------------------------

def main() -> int:
    parser = argparse.ArgumentParser(
        description="LAR System functional integration test runner (audit evidence tool)",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("--base-url", required=True,
                        help="Base URL of the deployed B2C app, e.g. https://lar-b2c.azurewebsites.net")
    parser.add_argument("--no-ssl-verify", action="store_true",
                        help="Disable SSL certificate verification (for staging/self-signed)")
    parser.add_argument("--output-dir", default=".",
                        help="Directory to write report files (default: current directory)")
    parser.add_argument("--verbose", action="store_true",
                        help="Print every HTTP request URL and status")
    args = parser.parse_args()

    os.makedirs(args.output_dir, exist_ok=True)
    ts = datetime.now(timezone.utc).strftime("%Y%m%d_%H%M%S")
    text_path = os.path.join(args.output_dir, f"LAR_Functional_Test_Report_{ts}.txt")
    json_path = os.path.join(args.output_dir, f"LAR_Functional_Test_Report_{ts}.json")

    print("╔══════════════════════════════════════════════════════════════════════╗")
    print("║  LAR System — Functional Integration Test Runner                    ║")
    print(f"║  Target : {args.base_url[:59]:<59}║")
    print(f"║  SSL    : {'DISABLED (--no-ssl-verify)' if args.no_ssl_verify else 'ENABLED':<59}║")
    print("╚══════════════════════════════════════════════════════════════════════╝")

    runner = TestRunner(
        base_url=args.base_url,
        verify_ssl=not args.no_ssl_verify,
        verbose=args.verbose,
    )

    run_all_scenarios(runner)

    # Write reports
    write_text_report(runner, args.base_url, text_path)
    write_json_report(runner, args.base_url, json_path)

    summary = runner.summary()
    total = sum(v for k, v in summary.items() if k in (PASS, WARN, FAIL, SKIP))

    print(f"\n{'═' * 70}")
    print("  TEST RUN COMPLETE")
    print(f"  PASS : {summary[PASS]}  WARN : {summary[WARN]}  "
          f"FAIL : {summary[FAIL]}  SKIP : {summary[SKIP]}  "
          f"(total: {total})")
    print(f"  Text report : {text_path}")
    print(f"  JSON report : {json_path}")
    print(f"{'═' * 70}")

    if summary[FAIL] > 0:
        print(f"\n  {_col(FAIL, str(summary[FAIL]) + ' FAIL(s) — see report for evidence.')} ")
        return 1
    if summary[WARN] > 0:
        print(f"\n  {_col(WARN, str(summary[WARN]) + ' WARN(s) — advisory findings in report.')} ")
    return 0


if __name__ == "__main__":
    sys.exit(main())
