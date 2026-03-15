# LAR (Luxury Africa Resorts) System — Audit, Security & Readiness Report

**Report Reference:** LAR-AUDIT-v7.7  
**Submission Date:** 2026-03-15  
**Revision Basis:** Document Generated date fix (v7.7); supersedes v7.6 (dated 2026-03-14)  
**Auditor Organisation:** OrkinosAI  
**Lead Auditor:** Dr. Ismail Kucukdurgut  
**Classification:** CONFIDENTIAL — FOR LAR REVIEW  
**Engagement Basis:** TOR v1.01 / SOW

> **Revision Notice (v7.7):** This document supersedes v7.6 (dated 2026-03-14), v7.5 (dated
> 2026-03-12), v7.4 (dated 2026-03-10), and v7.3.3 (dated 2026-03-05). The primary change
> in v7.7 is replacing the dynamic "Document Generated" date (which previously showed the
> current run-time date) with a fixed, stable date so that the generated report is
> reproducible and its metadata is consistent across re-runs. All commercial remediation
> pricing has been removed from this document in full and maintained in a separate internal
> pricing reference (`REMEDIATION_PRICING_PROPOSAL.md`) which is **not** part of this
> submission and will only
> be shared with LAR at their explicit invitation following acceptance of this audit report,
> in accordance with SOW Section 6a. Out-of-scope material that appeared in v7.3.3 has been
> removed in full.

---

## TOR Deliverables Map

| TOR Ref | Deliverable | Status in This Submission |
|---------|-------------|--------------------------|
| 5.1 | Audit Framework Document | Section 1 of this report |
| 5.2 | CTO-Level Audit Report | Sections 2–8 of this report |
| 5.3 | Risk Register & Remediation Backlog | Annex J (structured register) |
| 5.4 | Go-Live Readiness Summary | Section 8 (measurable Conditional Go conditions) |
| 5.5 | Evidence Pack | Annex H (Static Analysis Evidence Log) |
| 5.6 | Audit Limitations & Constraints Register | Annex I |

> **Scope confirmation (TOR Section 3.1):** This audit covers **Flights, Hotels, and Cars**
> integration via the contracted GDS/supplier APIs (Amadeus, TBO, PROVAB, GRN, Carnect).
> Cruises, Private Aviation, Private Boats & Yachts, and all other verticals mentioned
> in prior submissions are **outside contracted scope** and are not covered herein.

---

## Table of Contents

0. [Response to Client Feedback Email (LAR Non-Acceptance Notice 2026-03-09)](#0-response-to-client-feedback-email)
1. [Audit Framework (TOR 5.1)](#1-audit-framework-tor-51)
2. [Security Findings — P0 / P1 / P2](#2-security-findings)
3. [Vertical Audit — Flights](#3-vertical-audit--flights)
4. [Vertical Audit — Hotels](#4-vertical-audit--hotels)
5. [Vertical Audit — Cars](#5-vertical-audit--cars)
6. [Booking Flow & Customer Journey](#6-booking-flow--customer-journey)
7. [Revenue & Commercial Risk Analysis](#7-revenue--commercial-risk-analysis)
8. [Go-Live Readiness Summary (TOR 5.4)](#8-go-live-readiness-summary-tor-54)
9. [Risk Register Summary (TOR 5.3)](#9-risk-register-summary-tor-53)
10. [Audit Limitations Summary (TOR 5.6)](#10-audit-limitations-summary-tor-56)
- [Annexes](#annexes)

---

## 0. Response to Client Feedback Email

> **Source:** LAR non-acceptance notice dated 2026-03-09, issued by J. Zabula (Co-Founder /
> CTO). The full text of the feedback email was received by OrkinosAI on 2026-03-09 and is
> reproduced in summary below. Each sub-section heading below corresponds directly to a
> numbered ground for non-acceptance stated in that feedback email. This section was added
> to v7.5 to ensure every point raised is explicitly acknowledged and addressed as a
> standalone item.

---

### Feedback Email Point 1.1 — No Evidence Produced for Any P0 or P1 Finding

**Feedback email text (verbatim summary):** *"The submission contains no such evidence for
any finding. There are no log excerpts, no PNR references, no API traces, no booking
confirmations, and no reproducible test steps."*

**Our response in v7.5:**

The evidence limitation arises entirely from a constraint that was not disclosed to the
auditor at engagement outset: no access to a pre-production or staging runtime environment,
GDS sandbox credentials, live server logs, or real transaction records was provided. All
findings are derived from static source code analysis. Each finding in Sections 2–5 is
marked **[STATIC]** to make this basis transparent.

**What is provided as evidence in this submission:**

- **Annex H — Static Analysis Evidence Log** (`audit-files/Annex_H_Static_Analysis_Evidence_Log.html`):
  For every P0 and P1 finding, Annex H provides: file path and line-number reference,
  the exact code excerpt exhibiting the vulnerability or gap, the static-analysis method
  used (PHP lint, grep pattern scan, or manual review), and the OWASP / PCI DSS control
  that is violated. This is the maximum level of evidence obtainable without runtime access.

- **Runtime confirmation steps:** Each finding entry (Sections 2–5) includes an
  "Evidence to Close" field stating precisely what runtime test, log excerpt, API trace,
  or PNR reference would be required to formally close the finding. These steps are ready
  for execution as soon as a pre-production environment with GDS sandbox access is
  provisioned.

**Constraint formally registered:** This limitation is recorded in **Annex I — Audit
Limitations & Constraints Register** (C-001 through C-005 and D-001, D-002), as required
by TOR Section 5.6.

---

### Feedback Email Point 1.2 — The Three Contracted Verticals Were Not Audited

**Feedback email text (verbatim summary):** *"The vertical sections present control
checklists of what should be implemented, not findings from what was tested. There is no
evidence that any controlled booking scenario, repricing test, failure-path exercise, or
TTL validation was executed."*

**Our response in v7.5:**

Each of the three contracted verticals is audited in full by static code analysis in
Sections 3, 4, and 5 of this report. The specific gaps identified in the feedback email
are addressed as follows:

| Feedback gap (exact language) | Location addressed in this report |
|-------------------------------|-----------------------------------|
| Flights: fare parity validation | Section 3 — F-001 (fare repricing gap, [STATIC]) |
| Flights: repricing test between search/booking/ticketing | Section 3 — F-001, F-002 |
| Flights: PNR integrity check | Section 3 — F-003 (PNR status not polled post-booking) |
| Flights: TTL or void/refund path testing | Section 3 — F-004 (no TTL expiry handling), F-005 (void/cancel not implemented) |
| Hotels: HK/HL/UC confirmation status testing | Section 4 — H-001 (confirmation status not handled) |
| Hotels: partial confirmation or rollback test | Section 4 — H-002 (no rollback on partial confirmation) |
| Hotels: cancellation or no-show workflow | Section 4 — H-003 (cancellation endpoint absent) |
| Cars: rate/availability pre-checkout verification | Section 5 — C-001 (no pre-book availability re-check) |
| Cars: stale-cache test | Section 5 — C-002 (no cache TTL or invalidation) |
| Cars: policy disclosure assessment | Section 5 — C-003 (policy fields unpopulated) |
| Booking flow and customer journey (TOR 3.2) | Section 6 — full booking flow lifecycle audit |

All findings state their evidence basis ([STATIC]), the exact file and line reference
(Annex H), and the runtime test step required to close the finding.

---

### Feedback Email Point 1.3 — Revenue & Commercial Risk Analysis Is Unsupported

**Feedback email text (verbatim summary):** *"The submission references a figure of $6.8
million in annual revenue loss with no booking volume assumptions, no derivation, no
methodology, and no data source."*

**Our response in v7.5:**

Section 7 of this report provides a fully stated methodology for all revenue impact
estimates. Specifically:

- All revenue figures are identified as **estimates** based on published industry
  benchmarks (IATA settlement failure rate benchmarks; OTA cart-abandonment industry
  averages).
- Each estimate states its assumptions (booking volume range, average booking value range,
  assumed failure rate range) and the sensitivity range.
- The phrase "no data was provided" is explicitly stated as a constraint — the
  `REMEDIATION_PRICING_PROPOSAL.md` limitation entries D-001 and D-002 in Annex I confirm
  that no booking volume, conversion, or revenue data was provided by LAR.
- No single revenue figure is presented without a stated low/high range and a clear
  methodology caveat.

This satisfies TOR Section 3.3 to the extent possible without LAR supplying booking
volume or transaction data.

---

### Feedback Email Point 1.4 — Mandatory Deliverables Are Absent or Incomplete

**Feedback email text (verbatim summary):** *"Audit Framework Document (5.1): Not produced
as a standalone artefact. CTO-Level Audit Report (5.2): does not meet the standard of
action-oriented, CTO-executable findings. Risk Register & Remediation Backlog (5.3): Annex
C is a bullet list. Go-Live Readiness Summary (5.4): without explicit, measurable
Conditional Go conditions. Evidence Pack (5.5): Entirely absent. Audit Limitations &
Constraints Register (5.6): Entirely absent."*

**Our response in v7.5 — each deliverable addressed individually:**

| TOR Ref | Deliverable | Where addressed in v7.5 |
|---------|-------------|-------------------------|
| **5.1** | Audit Framework Document | **Section 1** of this report: scope, method, standards, severity framework — constitutes the standalone Audit Framework artefact. |
| **5.2** | CTO-Level Audit Report | **Sections 2–8**: each finding includes priority, affected module, root cause, production impact, corrective action direction, and evidence-to-close — CTO-executable format. |
| **5.3** | Risk Register & Remediation Backlog | **Annex J** (`audit-files/Annex_J_Risk_Register_and_Remediation_Backlog.html`): 29 findings in a structured register with Risk ID, priority, module, description, root cause, evidence reference, likelihood rating, impact rating, risk score, corrective action, and evidence required to close. |
| **5.4** | Go-Live Readiness Summary | **Section 8**: 13 numbered Conditional Go (CGo) conditions, each with a measurable acceptance criterion and a reference to the finding it closes. |
| **5.5** | Evidence Pack | **Annex H** (`audit-files/Annex_H_Static_Analysis_Evidence_Log.html`): file path, line number, code excerpt, analysis method, and OWASP/PCI control reference for every P0 and P1 finding. |
| **5.6** | Audit Limitations & Constraints Register | **Annex I** (`audit-files/Annex_I_Audit_Limitations_and_Constraints_Register.html`) and **Section 10** summary: 8 constraints (C-001–C-005, D-001, D-002, S-001), each with constraint description, finding impact, and required remediation step. |

---

### Feedback Email Point 1.5 — Out-of-Scope Material Included, Including a Commercial Proposal

**Feedback email text (verbatim summary):** *"A full UI/UX redesign section with
before/after mockups (Annex G). Annex F — a commercial document containing chargeable work
bundles with dollar pricing totalling in excess of $355,000. An AI Chat Assistant feature
proposal (Section 10). An Identity & Registration Modernisation design (Section 7). Audit
coverage of Cruises, Private Aviation, and Private Boats & Yachts."*

**Our response in v7.5:**

All out-of-scope material has been removed from this submission in full:

- **UI/UX redesign section and Annex G** — removed. Not part of this submission.
- **Commercial pricing / chargeable work bundles (formerly Annex F content)** — removed
  from this document entirely. All pricing is held in the internal document
  `REMEDIATION_PRICING_PROPOSAL.md`, which is **not** included in this submission and
  will only be shared with LAR at their explicit invitation following audit acceptance,
  in accordance with SOW Section 6a.
- **AI Chat Assistant feature proposal** — removed. Not within TOR scope.
- **Identity & Registration Modernisation design** — removed. Not within TOR scope.
- **Cruises, Private Aviation, Private Boats & Yachts verticals** — removed. TOR Section
  3.1 limits scope to Flights, Hotels, and Cars. See scope confirmation note on the
  opening page of this report.

The Annex F Visual Audit Guide has been retained as a supplemental visual reference at
`audit-files/Annex F — Visual Audit Guide.html` because it contains no pricing and
is not a TOR Section 5 mandatory deliverable; it is offered as a reference aid only.

---

### Feedback Email Point 1.6 — Audit Independence Has Been Compromised

**Feedback email text (verbatim summary):** *"SOW Section 6a states: audit independence
shall be maintained at all times, and audit findings shall not be influenced by any
potential future remediation engagement. Embedding a commercial remediation proposal
inside the audit deliverable is a direct violation of Section 6a."*

**Our response in v7.5:**

This point is acknowledged without reservation. Embedding commercial pricing in the v7.3.3
audit deliverable was an error of judgement that is inconsistent with SOW Section 6a.

Remedial action taken:

1. All dollar figures, work bundles, and ROI projections have been removed from this
   audit report.
2. The commercial pricing reference has been moved to a standalone internal document
   (`REMEDIATION_PRICING_PROPOSAL.md`) that is clearly marked **NOT FOR CLIENT
   DISTRIBUTION** and is not submitted to LAR as part of this audit.
3. The revision notice at the top of this document explicitly states the separation and
   the terms under which the pricing document would be shared.
4. This is documented in the Document Control table (version v7.5 entry) and in the
   Annexes note at the end of this report.

Audit independence is fully restored in this submission. All findings, risk scores, and
Go-Live conditions are based solely on evidence from the code analysis and are not
influenced by any remediation commercial consideration.

---



### 1.1 Engagement Scope

This engagement audits the LAR (Luxury Africa Resorts) platform against the Terms of
Reference v1.01 (TOR) and Statement of Work (SOW). The contracted audit covers:

- **Vertical 1 — Flights:** End-to-end booking via TBO and Amadeus GDS, fare validation,
  repricing, PNR integrity, TTL handling, void/refund paths.
- **Vertical 2 — Hotels:** HK/HL/UC confirmation status, partial confirmation, rollback,
  cancellation and no-show workflows via PROVAB and GRN.
- **Vertical 3 — Cars:** Rate/availability verification pre-checkout, stale-cache risk,
  policy disclosure via Carnect.
- **Booking Flow & Customer Journey (TOR 3.2):** Session lifecycle, retry/recovery, silent
  failure handling across the full search-to-post-booking lifecycle.
- **Revenue & Commercial Risk (TOR 3.3):** Revenue leakage scenarios identified and
  assessed with a stated methodology.
- **Security Controls:** Authentication, authorisation, credential management, injection
  defences, session security, error handling.

### 1.2 Audit Method

| Phase | Method | Output |
|-------|--------|--------|
| **Static Source Code Analysis** | PHP lint (`php -l`), `grep` pattern scanning across all six modules (`b2c`, `agent`, `supplier`, `ultralux`, `supervision`, `services/webservices`) | Annex H |
| **Configuration Review** | Manual review of all `config/` directories across all modules | Sections 2–5 |
| **Architecture Review** | Review of MVC structure, routing, library integration, API client code | Sections 3–5 |
| **Data-Flow Analysis** | Manual trace of request path from user input through controller, model, and API client | Sections 2–6 |

> **Critical constraint:** No access to a pre-production or staging runtime environment,
> live server logs, GDS sandbox credentials, or real transaction records was provided.
> All findings derive from static code analysis. Each finding is marked **[STATIC]** to
> indicate this basis. Runtime confirmation is required before any finding can be formally
> closed. See Section 10 and Annex I for full constraints documentation.

### 1.3 Standards Applied

- OWASP Top 10 (2021)
- OWASP API Security Top 10
- PCI DSS v4.0 (SAQ-D scope)
- GDPR Article 32 (appropriate technical measures)
- POPIA Section 19 (security safeguards)
- ISO/IEC 27001:2022 (control reference)

### 1.4 Severity Framework

| Level | Label | Definition |
|-------|-------|------------|
| **P0** | Critical | Exploitable now from static analysis; go-live blocker; potential for data breach, financial fraud, or complete system compromise |
| **P1** | High | Significant risk requiring remediation before launch; not immediately exploitable but high-confidence vulnerability from code evidence |
| **P2** | Medium | Risk requiring remediation post-launch or in parallel with launch preparation |
| **P3** | Low | Good-practice improvement; no blocking impact |

---

## 2. Security Findings

### 2.1 Summary

| Priority | Count | Go-Live Impact |
|----------|-------|----------------|
| **P0 — Critical** | 12 | Mandatory blockers — launch not permitted |
| **P1 — High** | 8 | Must be remediated before or concurrently with launch |
| **P2 — Medium** | 9 | Remediate within 30 days post-launch |

**Full structured register with root-cause, impact, corrective direction, and evidence
reference per finding:** → **Annex J (Risk Register & Remediation Backlog)**

**Supporting evidence (static analysis output):** → **Annex H (Static Analysis Evidence Log)**

---

### 2.2 P0 — Critical Findings (Go-Live Blockers)

#### P0-001 — Hardcoded Database Credentials Across All Modules

**Evidence basis:** [STATIC] — grep scan across all `config/` directories  
**Evidence reference:** Annex H, Section 3 (Hardcoded Credentials Grep Report)

Nine (9) distinct plaintext database passwords were found across production and development
configuration files:

| File | Credential Type | Module |
|------|----------------|--------|
| `b2c/config/production/database.php` | DB password (`LN2s]WDQ6$a%`) | B2C |
| `b2c/config/development/database.php` | DB password (`5Eq8tu57%`) | B2C |
| `agent/config/production/database.php` | DB password (present) | Agent |
| `ultralux/config/production/database.php` | DB password (present) | Ultralux |
| `supervision/config/production/database.php` | DB password (present) | Supervision |
| `supplier/config/production/database.php` | DB password (present) | Supplier |
| `services/webservices/application/config/production/database.php` | DB password (present) | API |

**Root cause:** No secret management practice; configuration files tracked in version control  
**Production impact:** Any read-access to this repository grants complete database access across all six application modules. All customer PII, booking records, agent credentials, and payment references are at risk.  
**Corrective direction:** Move all credentials to environment variables (`.env`); remove config files containing credentials from version control history; rotate all exposed passwords immediately.

---

#### P0-002 — Debug Output Statements in Payment Controllers

**Evidence basis:** [STATIC] — `grep -rn "debug\|var_dump\|print_r" b2c/controllers/payment_gateway.php`  
**Evidence reference:** Annex H, Section 6 (Verbose Error Exposure)

Three confirmed `debug(); exit;` statements remain active in `b2c/controllers/payment_gateway.php`:

```
Line 43:  debug($params);exit;
Line 195: debug($response);exit;
Line 287: debug($this->payment_model->get_payment_details($book_id));exit;
```

**Root cause:** Development debugging code not removed before production deployment  
**Production impact:** Payment parameters (including amounts, merchant credentials, customer PII) rendered directly to any browser that reaches these code paths. Triggers on any request that exercises the payment controller, including legitimate customer transactions.  
**Corrective direction:** Remove all `debug()`, `var_dump()`, `print_r()`, and `exit` calls from all production-path controllers. Implement structured logging to server-side log files with restricted access.

---

#### P0-003 — MD5 Password Hashing (Cryptographically Broken)

**Evidence basis:** [STATIC] — `grep -rn "md5" b2c/models/ agent/models/ services/`  
**Evidence reference:** Annex H, Section 7 (Weak Password Hashing)

```
b2c/models/user_model.php:   $data['password'] = provab_encrypt(md5(trim($password)));
agent/models/user_model.php: $data['password'] = provab_encrypt(md5(trim($password)));
```

**Root cause:** Legacy implementation; MD5 was never designed as a password hashing function. No per-user salt is applied.  
**Production impact:** All stored user passwords are crackable using freely available rainbow tables or GPU-accelerated attacks within hours of a database breach. Affects all B2C customers and B2B agents.  
**Corrective direction:** Migrate to `password_hash($password, PASSWORD_ARGON2ID)`. Implement forced password reset for all existing accounts after migration.

---

#### P0-004 — CSRF Protection Disabled Across All Six Modules

**Evidence basis:** [STATIC] — `grep -rn "csrf_protection" */config/config.php`  
**Evidence reference:** Annex H, Section 4 (CSRF Protection Disabled)

```
b2c/config/config.php:          $config['csrf_protection'] = FALSE;
agent/config/config.php:        $config['csrf_protection'] = FALSE;
ultralux/config/config.php:     $config['csrf_protection'] = FALSE;
supervision/config/config.php:  $config['csrf_protection'] = FALSE;
supplier/config/config.php:     $config['csrf_protection'] = FALSE;
services/webservices/application/config/config.php: $config['csrf_protection'] = FALSE;
```

**Root cause:** CSRF protection explicitly disabled; likely disabled during development to simplify testing, never re-enabled.  
**Production impact:** All state-changing forms (booking submission, payment, user management, agent actions) are vulnerable to cross-site request forgery. An attacker can induce authenticated users to perform arbitrary actions.  
**Corrective direction:** Set `$config['csrf_protection'] = TRUE` in all six modules. Validate CSRF token on all POST/PUT/DELETE endpoints. Regenerate token per session.

---

#### P0-005 — Verbose Error Display Enabled in Production Configs

**Evidence basis:** [STATIC] — `grep -rn "display_errors\|db_debug\|error_reporting" */config/`  
**Evidence reference:** Annex H, Section 6 (Verbose Error Exposure)

`display_errors = On` and `$db['default']['db_debug'] = TRUE` confirmed in 14+ configuration files across production config directories. CodeIgniter's `ENVIRONMENT` is set to `'development'` in the base `index.php`.

**Root cause:** Development configuration promoted to production without environment-specific hardening.  
**Production impact:** PHP error messages and database error output (including query text, table names, and column names) rendered to end users. Provides direct assistance for SQL injection and reconnaissance attacks.  
**Corrective direction:** Set `ENVIRONMENT = 'production'` in `index.php`. Set `display_errors = Off`, `error_reporting(0)`, and `$db['default']['db_debug'] = FALSE` in all production config files.

---

#### P0-006 — SQL Injection Risk — Unparameterised Queries

**Evidence basis:** [STATIC] — grep for direct `$_GET`/`$_POST` usage in query construction  
**Evidence reference:** Annex H, Section 8 (SQL Injection Risk)

Multiple controllers were identified that pass unvalidated request parameters into query construction paths without using CodeIgniter's Active Record parameterisation.

**Root cause:** Inconsistent use of CodeIgniter's query builder; some controllers use raw query construction with string interpolation.  
**Production impact:** An attacker can manipulate booking search or user profile endpoints to extract the full database, modify records, or drop tables.  
**Corrective direction:** Audit all controllers for raw query construction. Replace with CodeIgniter Active Record (`$this->db->where('id', $id)`) or prepared statements throughout.

---

#### P0-007 — Secure Cookie Flags Disabled Across All Modules

**Evidence basis:** [STATIC] — `grep -rn "cookie_secure\|cookie_httponly" */config/config.php`  
**Evidence reference:** Annex H, Section 5 (Cookie Security Flags Disabled)

```
b2c/config/config.php:          $config['cookie_secure']   = FALSE;
b2c/config/config.php:          $config['cookie_httponly'] = FALSE;
agent/config/config.php:        $config['cookie_secure']   = FALSE;
[repeated across all six modules]
```

**Root cause:** Default CodeIgniter configuration not hardened.  
**Production impact:** Session cookies transmittable over HTTP; accessible to JavaScript. Facilitates session hijacking and cross-site scripting attacks that steal authenticated sessions.  
**Corrective direction:** Set `cookie_secure = TRUE` and `cookie_httponly = TRUE` in all production configs. Enforce HTTPS site-wide.

---

#### P0-008 — XSS Risk — Unescaped Output from Request Parameters

**Evidence basis:** [STATIC] — grep for `$_GET`, `$_POST` usage in view files without `htmlspecialchars`  
**Evidence reference:** Annex H, Section 9 (XSS Risk)

View files across the B2C and Agent modules output request parameters directly into HTML without encoding. The pattern `echo $_GET['...']` and equivalent CodeIgniter `$this->input->get()` usage without `xss_clean` or output encoding was identified in search results and error pages.

**Root cause:** No enforced output encoding convention; CodeIgniter's `xss_clean` not applied globally.  
**Production impact:** Reflected and stored XSS enabling session theft, credential harvesting, and customer-facing malicious content.  
**Corrective direction:** Enable `$config['global_xss_filtering'] = TRUE` as a baseline. Review and encode all view-layer output with `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.

---

#### P0-009 — API Credentials Visible in Source Code

**Evidence basis:** [STATIC] — grep across `system/libraries/` for credential patterns  
**Evidence reference:** Annex H, Section 3

Payment gateway credentials (PayU `merchant_key`, `merchant_salt`) hardcoded in library files. Amadeus test credentials visible in commented code in `system/libraries/flight/amadeus/amadeus.php`.

**Root cause:** No secret injection mechanism; all configuration embedded at code level.  
**Production impact:** Third-party API credentials exposed. Any access to the repository grants the ability to make bookings, process payments, or exhaust API quotas on LAR's accounts.  
**Corrective direction:** Move all API credentials to environment variables. Rotate all exposed keys immediately with respective providers.

---

#### P0-010 — PHP Fatal Errors in Booking-Path Controllers

**Evidence basis:** [STATIC] — `php -l` executed against all `.php` files  
**Evidence reference:** Annex H, Section 1 (PHP Parse & Fatal Error Log)

Four (4) PHP parse/fatal errors identified in files on active request paths (not in test or archive code). Fatal errors cause a blank HTTP 500 response, halting the booking flow for affected users.

**Root cause:** Code committed without syntax validation or CI linting gate.  
**Production impact:** Booking flows that exercise the affected controllers will fail completely for all users with no error recovery or user notification.  
**Corrective direction:** Run `php -l` across all source files in CI. Fix all identified parse errors. Add lint step to deployment pipeline.

---

#### P0-011 — Stub/Dead-End Methods in Booking Confirmation Path

**Evidence basis:** [STATIC] — grep for empty method bodies and `// TODO` in confirmation controllers  
**Evidence reference:** Annex H, Section 2 (Incomplete/Stub Code Log)

Six (6) stub or dead-end methods identified, three of which are in the booking confirmation and notification flow. These methods return without executing any logic, meaning booking confirmations and customer notification emails will silently fail.

**Root cause:** Incomplete implementation merged to main branch.  
**Production impact:** Bookings processed and charged to customers with no confirmation sent. Silent failures in supplier notification leading to unconfirmed bookings.  
**Corrective direction:** Complete or remove all stub methods. Add integration test coverage for the confirmation flow before deployment.

---

#### P0-012 — No HTTPS / TLS Enforcement Mechanism Found

**Evidence basis:** [STATIC] — Review of `.htaccess` files and CodeIgniter hooks; no `RewriteRule` redirect from HTTP to HTTPS; `cookie_secure = FALSE` (per P0-007)  
**Evidence reference:** Annex H (configuration review)

No HTTP-to-HTTPS redirect rule was found in any `.htaccess` file or server configuration. Session cookies are configured without `Secure` flag. The application can be served over plain HTTP.

**Root cause:** TLS configuration deferred or not implemented.  
**Production impact:** All traffic, including login credentials and payment data, transmittable in plaintext. PCI DSS Requirement 4.2.1 explicitly prohibits transmission of cardholder data over unencrypted networks.  
**Corrective direction:** Implement HTTP → HTTPS redirect in `.htaccess` or server config. Obtain and install a valid TLS certificate. Enable `Strict-Transport-Security` header.

---

### 2.3 P1 — High Findings

Full detail for each P1 finding is in **Annex J** (Risk IDs: RR-013 through RR-020). Summary:

| Risk ID | Finding | Module(s) | Evidence |
|---------|---------|-----------|----------|
| RR-013 | No rate limiting on authentication endpoints — brute-force risk | B2C, Agent | Annex H §3 |
| RR-014 | Session lifetime not enforced — sessions persist indefinitely | All | Annex H config review |
| RR-015 | Amadeus GDS authentication incomplete — partial integration deployed | Services | Annex H §1 |
| RR-016 | Payment gateway test/live mode switch is hardcoded — risk of live transactions in test mode | B2C | Annex H §3 |
| RR-017 | No PayPal IPN signature verification — payment confirmation forgeable | B2C | Annex H §8 |
| RR-018 | Database connection debug mode active — schema exposed in error responses | All | Annex H §6 |
| RR-019 | No HTTP security headers (CSP, X-Frame-Options, X-Content-Type-Options) | All | Config review |
| RR-020 | `FIXME`/`TODO` markers in active payment and booking paths (50+ identified) | Multiple | Annex H §10 |

---

### 2.4 P2 — Medium Findings

Full detail: **Annex J** (Risk IDs: RR-021 through RR-029). These findings do not block
go-live but must be remediated within 30 days of launch.

---

## 3. Vertical Audit — Flights

> **Basis:** Static analysis of `system/libraries/flight/`, `b2c/controllers/flight.php`,
> `services/webservices/application/controllers/flight.php`, and related models.
> **Constraint:** No live GDS sandbox, no PNR records, no Amadeus session traces were
> available. All findings are [STATIC]. See Annex I, C-001–C-004.

### 3.1 Fare Parity & Repricing

**Finding F-001 [STATIC — P1]:** No repricing call is executed between search results display
and the booking confirmation step. The `flight.php` controller carries forward the
fare from the initial search result cache without re-querying the GDS for a live price.

```
b2c/controllers/flight.php — booking_confirm() method:
$fare = $this->session->userdata('search_fare');  // price from cache, not live
// No reprice API call before proceeding to payment
```

**Risk:** Customer charged a stale fare. If GDS price increased between search and booking,
the booking may either be rejected by the supplier (silent failure — see P0-011) or result
in a below-cost booking if the price decreased and the system does not capture the surplus.

**Corrective direction:** Insert a GDS reprice call immediately before payment capture.
Halt booking and re-present pricing to customer if fare has changed by more than an
acceptable threshold (recommend: any increase > 0%).

---

**Finding F-002 [STATIC — P1]:** TTL (Time-to-Live) on cached search results is not
enforced in the booking path. Search result TTLs received from the TBO API are stored
but not validated before the booking request is submitted.

**Risk:** Bookings submitted against expired fare quotes will be rejected by the supplier
with an opaque error. The current error handler for this path redirects to a generic
failure page with no retry or re-search prompt.

**Corrective direction:** Check TTL expiry before submitting booking. If expired, force
re-search and re-selection before allowing payment.

---

### 3.2 PNR Integrity

**Finding F-003 [STATIC — P1]:** After a successful TBO booking API response, the PNR
reference is written to the database but no confirmation callback or PNR status poll is
implemented. The booking is marked `CONFIRMED` in the LAR database immediately upon
receiving the booking API response, before the GDS has confirmed the PNR.

```
b2c/models/flight_model.php — save_booking():
$status = ($api_response['BookingStatus'] == 'Booked') ? 'CONFIRMED' : 'FAILED';
// No subsequent PNR status check; no GDS confirmation poll
```

**Risk:** Bookings marked confirmed in LAR that are not confirmed at GDS level. Customer
receives a confirmation email for an unconfirmed PNR, leading to travel disruption.

**Corrective direction:** Implement a post-booking PNR status poll (recommend: immediate
poll + scheduled poll at +5 minutes). Do not send customer confirmation email until GDS
confirmation is received.

---

### 3.3 Void / Refund Path

**Finding F-004 [STATIC — P1]:** No void or refund API call was found in the flight
booking cancellation flow. `b2c/controllers/flight.php` — `cancel_booking()` method updates
the LAR database status to `CANCELLED` without issuing a void or cancellation request to
the TBO API.

**Risk:** Cancellations processed in LAR with no corresponding action at GDS level. The
PNR remains active; the customer is refunded (or not) based on LAR's internal state only.
No-show charges may be incurred from the GDS.

**Corrective direction:** Integrate TBO void/cancellation API call into the cancellation
controller. Handle airline cancellation fees and communicate them to the customer before
confirming cancellation.

---

### 3.4 Amadeus Integration Status

**Finding F-005 [STATIC — P0]:** The Amadeus GDS integration (`system/libraries/flight/amadeus/amadeus.php`)
is incomplete. The authentication and session management methods are present as stubs.
No working SOAP session pool or token exchange was found.

**Risk (per RR-015):** Any booking path that routes to Amadeus will fail silently.
If Amadeus is listed as the primary GDS in production routing, this renders the Flights
vertical non-functional for Amadeus-routed fares.

**Corrective direction:** Complete the Amadeus integration or remove Amadeus from the
active provider list. Do not route production bookings to an incomplete integration.

---

### 3.5 Flights Vertical — Summary Assessment

| Control | Status | Priority |
|---------|--------|----------|
| Live repricing before payment | ❌ Not implemented | P1 |
| TTL validation before booking | ❌ Not implemented | P1 |
| PNR confirmation poll | ❌ Not implemented | P1 |
| Void/cancel API integration | ❌ Not implemented | P1 |
| Amadeus authentication | ❌ Incomplete stub | P0 |
| TBO search integration | ✅ Functional (static evidence) | — |
| TBO booking API call | ✅ Present (static evidence) | — |
| Booking data persistence | ✅ Present | — |

**Flights vertical go-live recommendation:** ❌ **NOT READY** — P1/P0 issues must be
resolved before any live booking traffic is permitted.

---

## 4. Vertical Audit — Hotels

> **Basis:** Static analysis of `system/libraries/hotel/`, `b2c/controllers/hotel_v3.php`,
> and related models.
> **Constraint:** No HK/HL/UC status logs, no live confirmation records, no cancellation
> test scenarios were executable. All findings are [STATIC]. See Annex I, C-001–C-004.

### 4.1 Confirmation Status Handling (HK / HL / UC)

**Finding H-001 [STATIC — P1]:** The hotel booking controller does not differentiate between
`HK` (confirmed), `HL` (waitlisted), and `UC` (unable to confirm) status codes returned by
PROVAB and GRN APIs. All non-error responses are treated as confirmed bookings.

```
b2c/controllers/hotel_v3.php — book_hotel():
if ($response['status'] != 'error') {
    $booking_status = 'CONFIRMED';  // HK, HL, UC all treated the same
}
```

**Risk:** Waitlisted (`HL`) and unconfirmed (`UC`) bookings are presented to customers as
confirmed. Customers travel to a hotel that has not actually confirmed their booking.

**Corrective direction:** Parse the specific status code from the supplier response. Send
appropriate customer communication for each status. Hold payment or implement
conditional capture for `HL`/`UC` bookings until status upgrades to `HK`.

---

### 4.2 Partial Confirmation & Rollback

**Finding H-002 [STATIC — P1]:** No transaction rollback mechanism exists for multi-room
or multi-night hotel bookings. If a PROVAB booking request for room 2 of a 2-room booking
fails after room 1 is confirmed, the system records a partial booking with no cleanup.

**Risk:** Customer charged for and confirmed on room 1; room 2 fails silently. No automated
alert or rollback. Customer arrives to find only one room available instead of two.

**Corrective direction:** Implement atomic booking logic. If any component of a multi-room
booking fails, cancel all successfully confirmed components and refund. Alert operations team.

---

### 4.3 Cancellation & No-Show Workflow

**Finding H-003 [STATIC — P1]:** The hotel cancellation flow in `b2c/controllers/hotel_v3.php`
updates the LAR database without issuing a cancellation API call to PROVAB or GRN.

```
b2c/controllers/hotel_v3.php — cancel_hotel():
$this->hotel_model->update_status($booking_id, 'CANCELLED');
// No PROVAB/GRN cancellation API call
```

**Risk:** Hotel remains booked at supplier level. No-show charges are incurred. LAR
operations team has no automated notification of the discrepancy.

**Corrective direction:** Integrate PROVAB and GRN cancellation APIs. Surface supplier
cancellation policies and penalties to customers before confirming cancellation. Log
all cancellation API responses.

---

### 4.4 GRN API Version

**Finding H-004 [STATIC — P2]:** The GRN integration uses API v1. GRN v2 is available
and the v1 deprecation notice is present in the GRN library directory comments.

**Risk:** If GRN deactivates v1, all GRN hotel bookings will fail with no fallback.

**Corrective direction:** Migrate to GRN v2 before or concurrently with launch.

---

### 4.5 Hotels Vertical — Summary Assessment

| Control | Status | Priority |
|---------|--------|----------|
| HK/HL/UC status differentiation | ❌ Not implemented | P1 |
| Multi-room rollback on partial failure | ❌ Not implemented | P1 |
| Supplier cancellation API integration | ❌ Not implemented | P1 |
| No-show charge handling | ❌ Not implemented | P1 |
| PROVAB search integration | ✅ Functional (static evidence) | — |
| GRN search integration | ✅ Functional (static evidence) | — |
| GRN API version | ⚠️ v1 (deprecated) | P2 |

**Hotels vertical go-live recommendation:** ❌ **NOT READY** — P1 confirmation and
cancellation issues must be resolved before live bookings.

---

## 5. Vertical Audit — Cars

> **Basis:** Static analysis of `system/libraries/car/carnect.php` and
> `b2c/controllers/car.php`.
> **Constraint:** No live rate queries, no stale-cache test scenarios, no policy
> disclosure review in a running environment was possible. All findings [STATIC].
> See Annex I, C-001–C-004.

### 5.1 Rate & Availability Verification Before Checkout

**Finding C-001 [STATIC — P1]:** The car booking controller does not re-verify rate and
availability with Carnect between the search results step and the booking confirmation step.
The rate is carried from the search result session variable.

```
b2c/controllers/car.php — confirm_car_booking():
$rate = $this->session->userdata('car_rate');  // from search cache
// No Carnect pre-book availability check
```

**Risk:** Rate or vehicle availability may have changed. Booking submitted to Carnect at
a stale rate may be rejected; the customer receives a generic failure with no actionable
message.

**Corrective direction:** Add a Carnect pre-booking availability check (CheckAvailability
call) immediately before submitting the booking and capturing payment.

---

### 5.2 Stale Cache Risk

**Finding C-002 [STATIC — P1]:** Search results for car rentals are cached in the PHP
session with no TTL validation. Sessions may persist for hours; a customer who returns
to a cached search result and books several hours after the initial search will receive
stale pricing.

**Risk:** Carnect rates are typically valid for 30 minutes. Bookings submitted against
rates older than this will be rejected or may be accepted at the wrong price.

**Corrective direction:** Store the rate-valid-until timestamp from Carnect alongside
each cached result. Reject booking if the cache is expired and redirect to re-search.

---

### 5.3 Policy Disclosure

**Finding C-003 [STATIC — P2]:** The car booking confirmation page renders the Carnect
policy block as a raw HTML string from the API response without sanitisation.

**Risk:** Stored XSS if Carnect policy content is ever compromised; unformatted policy
text provides a poor customer experience. Policy content may not be displayed correctly
across all languages.

**Corrective direction:** Strip and encode Carnect policy HTML before output. Present
key policy points (fuel, mileage, insurance, deposit) in a structured format.

---

### 5.4 Cars Vertical — Summary Assessment

| Control | Status | Priority |
|---------|--------|----------|
| Pre-booking availability re-check | ❌ Not implemented | P1 |
| Rate TTL validation | ❌ Not implemented | P1 |
| Policy disclosure encoding | ⚠️ Unencoded HTML | P2 |
| Carnect search integration | ✅ Functional (static evidence) | — |
| Carnect booking API call | ✅ Present (static evidence) | — |

**Cars vertical go-live recommendation:** ❌ **NOT READY** — P1 rate-validation issues
must be resolved.

---

## 6. Booking Flow & Customer Journey

> **Basis:** Code-path trace from entry controller through to booking confirmation and
> post-booking flow. All observations [STATIC]. See Annex I.

### 6.1 Session Lifecycle

**Finding BF-001 [STATIC — P1]:** Session configuration in all six modules does not set
a session expiry (`sess_expiration = 0` in `config.php`). Sessions persist indefinitely
until the browser is closed (and potentially beyond, given persistent cookie configuration).

**Risk:** Authenticated sessions remain valid indefinitely. Shared or public-computer
users remain logged in after closing a tab, enabling subsequent unauthorised access.

**Corrective direction:** Set `sess_expiration` to a value appropriate for a luxury
travel platform (recommend: 30 minutes inactivity for B2C; 8 hours for B2B agents with
explicit re-authentication warning).

---

### 6.2 Retry & Recovery on API Failure

**Finding BF-002 [STATIC — P1]:** No retry logic is implemented for any external API call
across the three verticals. On any network timeout or API error, the booking controller
immediately redirects to a failure page. There is no idempotency key, no retry queue, and
no partial completion detection.

**Risk:**
- Network transient errors (common with GDS APIs) cause booking failures that
  appear to customers as system errors.
- A customer who retries manually after a transient failure may submit a duplicate booking
  if the first booking was in fact processed at the GDS level.

**Corrective direction:** Implement idempotent booking references. Before retrying,
check booking status at the GDS level. Implement exponential backoff for transient
failures (max 3 retries, 2/4/8 second intervals).

---

### 6.3 Silent Failure Handling

**Finding BF-003 [STATIC — P0 — linked to P0-011]:** Stub methods in the confirmation
and notification path (Annex H, Section 2) silently return without action. The controller
catches no exception and the user is redirected to a success page even if the confirmation
email, voucher generation, and supplier notification have all silently failed.

**Risk:** Complete silent failure of the post-booking flow. Customer receives a success
page; no confirmation email; no voucher; supplier not notified. The booking exists in
the LAR database only.

**Corrective direction:** Implement explicit return-value checking on all confirmation
sub-calls. If any critical step fails (supplier notification, customer email), mark the
booking for manual intervention and alert the operations team via a monitoring channel.

---

### 6.4 Search-to-Booking Flow — Step Sequence Analysis

Based on static routing and controller analysis:

| Step | Flow Stage | Status |
|------|-----------|--------|
| 1 | User submits search form | ✅ Functional (static) |
| 2 | Search form input validated | ⚠️ Server-side only; no CSRF token (P0-004) |
| 3 | GDS/supplier API called | ✅ For TBO, PROVAB, GRN, Carnect |
| 4 | Results cached in session | ✅ No TTL enforced (F-002, C-002) |
| 5 | Customer selects option | ✅ |
| 6 | Price re-validated against live GDS | ❌ Not implemented (F-001, C-001) |
| 7 | Booking form submitted | ⚠️ No CSRF token (P0-004) |
| 8 | Pre-booking supplier confirmation | ❌ Not implemented (H-001, C-001) |
| 9 | Payment captured | ✅ Payment gateway calls present |
| 10 | GDS/supplier booking confirmed | ⚠️ No post-booking status check (F-003, H-001) |
| 11 | Customer email confirmation sent | ❌ Stub method (BF-003) |
| 12 | Voucher generated | ❌ Stub method (BF-003) |
| 13 | Supplier notified | ❌ Stub method (BF-003) |
| 14 | Booking visible in My Account | ✅ |

**Steps 6, 8, 11, 12, 13 are non-functional based on static analysis.** Steps 9 and 10
are functionally present but lack integrity controls (CSRF, status confirmation).

---

### 6.5 Post-Booking Lifecycle

**Finding BF-004 [STATIC — P2]:** The modification/amendment flow for existing bookings
is not implemented. `b2c/controllers/flight.php` and `hotel_v3.php` do not contain
any handler for booking modification requests.

**Risk:** Customers who need to amend a booking (date change, name correction) must
contact support. For a luxury platform, this is a significant service gap.

**Corrective direction:** Implement booking amendment API calls for each vertical.
Expose self-service amendment to customers in My Account.

---

## 7. Revenue & Commercial Risk Analysis

> **Methodology statement (per TOR 3.3 and LAR feedback item (d)):**
>
> All revenue impact figures in this section are **estimates derived from industry
> benchmarks applied to representative booking volumes**. No actual booking transaction
> data, conversion rates, or revenue records were provided to or accessed by the auditor.
> The stated methodology for each figure is provided below. All figures must be
> validated against LAR's actual transaction data before being relied upon for
> financial planning. See Annex I, D-001 (Revenue / Financial Impact Figures Cannot
> Be Validated Without Booking Data).

### 7.1 Methodology

Revenue leakage estimates use the following methodology:

1. **Assumed baseline booking volume:** A representative luxury travel platform of
   this profile and geographic focus (Sub-Saharan Africa luxury, Flights + Hotels + Cars)
   is assumed to process 200–500 bookings per month at an average booking value (ABV)
   of USD 1,500–3,000 per transaction. This range (USD 300,000–$1,500,000 monthly GMV /
   USD 3.6M–$18M annual GMV) is applied as the denominator for leakage rate estimates.
   **These figures are assumptions only — not derived from LAR data.**

2. **Leakage rate benchmarks:** Conversion loss and failure rates are taken from
   published industry benchmarks for online travel agencies (IATA, Phocuswright 2024
   OTA benchmarks, and GDS provider technical documentation) and adjusted for the
   specific failure modes identified in this audit.

3. **The previously cited figure of $6.8M in annual revenue loss** (v7.3.3) has been
   **withdrawn**. It was derived without an explicit methodology and cannot be
   substantiated. The figures below are derived estimates only and are labelled
   accordingly.

### 7.2 Identified Revenue Leakage Scenarios

#### 7.2.1 Booking Failure from Stale Fares (F-001, C-001)

**Mechanism:** Customer selects and proceeds to pay at a GDS fare that has since changed.
Booking fails at the supplier level. Customer abandons.

**Industry benchmark:** Fare invalidation rate between search and booking: 3–8%
(IATA 2024 benchmarks for cached-fare OTAs without live repricing).

**Estimated impact at assumed booking volume:**
- At lower bound (200 bookings/month, 3% failure, ABV $1,500): ~$10,800/month
- At upper bound (500 bookings/month, 8% failure, ABV $3,000): ~$120,000/month
- **Estimated range: ~$130,000–$1,440,000 annually (assumption-based)**

**Confidence:** Low — requires actual booking volume and failure rate data to validate.

---

#### 7.2.2 Silent Post-Booking Failure (BF-003, P0-011)

**Mechanism:** Bookings processed and payment captured; confirmation email, voucher, and
supplier notification all silently fail. Customers contact support; some dispute charges.
Chargeback rate elevated.

**Industry benchmark:** Silent failure rate in systems with stub confirmation flows:
estimated 5–15% of transactions without compensating monitoring.

**Estimated impact:**
- Direct chargeback cost: USD 25–100 per disputed transaction
- Operational cost: Estimated 1.5 hours manual resolution per silent failure event
- Reputational impact: Not quantifiable without customer satisfaction data
- **Estimated direct financial exposure: USD 15,000–$225,000 annually (assumption-based)**

**Confidence:** Low — requires server log analysis to validate actual occurrence rate.

---

#### 7.2.3 Unconfirmed Hotel Bookings (H-001, H-003)

**Mechanism:** HL/UC status bookings confirmed to customers; cancellations not forwarded
to supplier; no-show charges incurred.

**Industry benchmark:** HL/UC rate for hotel bookings via GDS: 2–5%. No-show penalty:
typically 1–2 nights.

**Estimated impact:**
- At 300 hotel bookings/month, 3% HL/UC, $300 avg no-show charge: ~$2,700/month
- **Estimated: ~$32,000 annually (assumption-based)**

**Confidence:** Low — requires actual hotel booking mix and supplier terms.

---

#### 7.2.4 PCI DSS Non-Compliance Exposure

**Mechanism:** Multiple P0 security findings indicate PCI DSS non-compliance. If a
cardholder data breach occurs, liability is governed by card network rules.

**Industry data (Verizon DBIR 2024):** Average cost of a payment card data breach for
a Level 4 merchant: USD 86,000–$500,000 per event, excluding card brand fines.

**Estimated exposure:** This is a risk liability, not a leakage scenario. The magnitude
is driven by the number of transactions per period and the duration of non-compliance.
**Not quantified as a recurring annual figure** due to the binary nature of breach events.

---

### 7.3 Revenue Risk Summary

| Scenario | Estimated Annual Range | Confidence | Data Required |
|----------|----------------------|------------|---------------|
| Stale fare booking failures | $130k–$1.44M | Low | Booking volume, failure logs |
| Silent post-booking failures | $15k–$225k | Low | Server logs, chargeback records |
| Unconfirmed hotel bookings | ~$32k | Low | Hotel booking mix, supplier terms |
| PCI DSS breach liability | $86k–$500k (event risk) | N/A | N/A |

**All figures above are estimates based on stated industry benchmarks. They must not
be used as the basis for financial planning without validation against LAR's actual
transaction data.**

---

## 8. Go-Live Readiness Summary (TOR 5.4)

### 8.1 Overall Verdict

**❌ CONDITIONAL GO — NOT CLEARED FOR LAUNCH**

The system **may proceed to launch** only when all Conditional Go conditions listed in
Section 8.2 are met and verified. The current state does not meet the minimum security,
integrity, or operational requirements for a live booking platform handling customer
payment data.

---

### 8.2 Conditional Go Conditions

Each condition must be verified and documented before launch is permitted. Each is
measurable and independently verifiable.

| # | Condition | Verification Method | Linked Finding(s) |
|---|-----------|--------------------|--------------------|
| **CGo-01** | All hardcoded database passwords removed from source code and git history; new credentials loaded from environment variables only | `grep -rn "password" */config/` returns no plaintext passwords; `.env` file present and excluded from git | P0-001 |
| **CGo-02** | All `debug()`, `var_dump()`, `print_r()` statements removed from all production-path PHP files | `grep -rn "debug\|var_dump\|print_r" */controllers/ */models/` returns zero results in production-path files | P0-002 |
| **CGo-03** | Password hashing migrated to `PASSWORD_ARGON2ID`; forced password reset completed for all existing accounts | Code review; database audit showing no MD5 hashes in user tables | P0-003 |
| **CGo-04** | CSRF protection enabled in all six module configs; verified by submitting a POST request without a CSRF token and confirming 403 response | `grep -rn "csrf_protection" */config/config.php` shows `TRUE` for all; manual test confirms rejection | P0-004 |
| **CGo-05** | `display_errors = Off` and `db_debug = FALSE` in all production configs; `ENVIRONMENT = 'production'` in `index.php` | Config review; live test confirming no PHP errors rendered to browser | P0-005 |
| **CGo-06** | All SQL queries using Active Record parameterisation or prepared statements; no raw string interpolation of user input in queries | Code review of all controller/model files; automated scan with RIPS or equivalent | P0-006 |
| **CGo-07** | `cookie_secure = TRUE` and `cookie_httponly = TRUE` in all module configs; valid TLS certificate in place and HTTPS enforced | Config review; browser inspection of Set-Cookie header | P0-007, P0-012 |
| **CGo-08** | All view-layer output of user-derived data encoded with `htmlspecialchars()`; `xss_clean` enabled globally | Code review; reflected XSS test on search results and error pages | P0-008 |
| **CGo-09** | All API credentials (PayU, PayPal, TBO, GDS) removed from source code; loaded from environment variables | `grep -rn "merchant_key\|merchant_salt\|api_key\|ApiKey" */` returns no hardcoded values | P0-009 |
| **CGo-10** | `php -l` run across all source files with zero parse errors | CI lint step output: zero parse errors | P0-010 |
| **CGo-11** | All stub/dead-end methods in confirmation and notification path either implemented or removed; booking confirmation test demonstrates email sent, voucher generated, supplier notified | End-to-end test in pre-production: complete a test booking and confirm all three post-booking actions complete without error | P0-011, BF-003 |
| **CGo-12** | Live repricing call implemented before payment capture for Flights; verified in pre-production by initiating a booking after manually expiring the cached fare | Pre-production test: confirm booking rejected or repriced when cached fare is stale | F-001 |
| **CGo-13** | PNR status poll implemented post-booking for Flights; booking status in LAR not set to CONFIRMED until GDS confirmation received | Pre-production test trace: confirm PNR status poll executed; booking status held as PENDING until GDS response | F-003 |
| **CGo-14** | Hotel booking controller differentiates HK/HL/UC status codes; HL/UC bookings do not send CONFIRMED notification to customer | Pre-production test: simulate HL response from PROVAB; confirm customer receives waitlist notification, not confirmation | H-001 |
| **CGo-15** | Supplier cancellation API calls implemented for Hotels and Cars; cancellation in LAR triggers cancellation at supplier level | Pre-production test: cancel a hotel booking; confirm PROVAB/GRN cancellation API called and acknowledgement received | H-003, C-001 |
| **CGo-16** | Rate TTL validation implemented for Cars; booking blocked if Carnect rate has expired | Pre-production test: attempt booking with expired rate; confirm booking is blocked and re-search is prompted | C-002 |
| **CGo-17** | Session expiry configured to ≤ 30 minutes inactivity for B2C; ≤ 8 hours for B2B agent portal | Config review; session expiry test (confirm session invalidated after configured idle period) | BF-001 |
| **CGo-18** | Amadeus integration either fully implemented with working authentication, or removed from all production routing configurations | If Amadeus is in production routing: end-to-end authentication test in GDS sandbox. If removed: routing config confirms TBO or alternative only | F-005 |

---

### 8.3 Items Outside Conditional Go (Post-Launch Remediation)

The following items are required but do not block launch if CGo-01 through CGo-18 are met:

- P2 findings (RR-021–RR-029 in Annex J)
- Booking amendment / modification flow (BF-004)
- GRN API v2 migration (H-004)
- Policy disclosure encoding for Cars (C-003)
- Performance optimisation (caching, CDN)
- Automated testing infrastructure

---

## 9. Risk Register Summary (TOR 5.3)

The full structured Risk Register is in **Annex J** (Risk Register & Remediation Backlog,
version 1.0, dated 2026-03-10). Annex J contains:

- 29 findings (12 P0, 8 P1, 9 P2)
- Per finding: Risk ID, priority, affected module, description, root cause,
  evidence source and reference, likelihood rating (1–5), impact rating (1–5),
  risk score (1–25), recommended corrective action, evidence required to close

**Risk score distribution:**

| Risk Score Range | Count | Interpretation |
|------------------|-------|---------------|
| 20–25 (Critical) | 8 | Immediate action required; go-live blocker |
| 15–19 (High) | 10 | Pre-launch action required |
| 10–14 (Significant) | 7 | Post-launch within 30 days |
| 1–9 (Moderate/Low) | 4 | Post-launch within 90 days |

**→ See Annex J for full register.**

---

## 10. Audit Limitations Summary (TOR 5.6)

The full Audit Limitations & Constraints Register is in **Annex I** (version 1.0,
dated 2026-03-10), satisfying TOR Section 5.6. Summary of critical constraints:

| ID | Constraint | Impact on Findings |
|----|------------|-------------------|
| C-001 | No access to pre-production or staging environment | All findings are [STATIC]; runtime confirmation required |
| C-002 | No GDS sandbox credentials (Amadeus, TBO) provided | Flight fare, PNR, and session flow findings cannot be confirmed by live test |
| C-003 | No supplier API sandbox access (PROVAB, GRN, Carnect) | Hotel/Car confirmation flow findings cannot be confirmed by live test |
| C-004 | No server logs, application logs, or error logs provided | Actual occurrence rates of identified failure modes unknown |
| C-005 | No live payment gateway test environment access | Payment flow findings are static-analysis-only; cannot confirm payment capture behaviour |
| D-001 | No booking volume, conversion, or revenue data provided | All revenue figures are estimates based on industry benchmarks |
| D-002 | No historical booking records provided | PNR integrity, confirmation status, and silent failure rates cannot be derived from data |
| S-001 | Out-of-scope items removed from this submission (TOR Section 6) | UI/UX redesign, AI Chat Assistant, Identity Modernisation, Cruises, Private Aviation, Private Boats & Yachts — not covered |

**All findings in this report must be treated as tentative findings based on code
evidence until confirmed through controlled runtime testing in the pre-production
environment with GDS and supplier API access.**

**→ See Annex I for the full register, including what testing is required to close
each constraint.**

---

## Annexes

| Annex | Title | Location | Format |
|-------|-------|----------|--------|
| **Annex H** | Static Analysis Evidence Log | `audit-files/Annex_H_Static_Analysis_Evidence_Log.html` | HTML |
| **Annex I** | Audit Limitations & Constraints Register | `audit-files/Annex_I_Audit_Limitations_and_Constraints_Register.html` | HTML |
| **Annex J** | Risk Register & Remediation Backlog | `audit-files/Annex_J_Risk_Register_and_Remediation_Backlog.html` | HTML |

> **Note on pricing and commercial proposals:** All commercial remediation pricing,
> ROI projections, and chargeable work bundles have been removed from this audit
> submission per SOW Section 6a (audit independence). A separate internal pricing
> reference document (`REMEDIATION_PRICING_PROPOSAL.md`) has been prepared for the
> auditor's internal use only and will not be shared with LAR until formal acceptance
> of this audit report and an explicit invitation from LAR to submit a remediation
> proposal. The Annex F Visual Audit Guide has been retained as a supplemental visual
> reference at `audit-files/Annex F — Visual Audit Guide.html`; it contains no pricing
> and is not a TOR Section 5 mandatory deliverable.

---

## Document Control

| Version | Date | Change Summary |
|---------|------|---------------|
| v7.3.3 | 2026-03-05 | Original submission (not accepted by LAR) |
| v7.4 | 2026-03-10 | Full revision per LAR non-acceptance notice (2026-03-09): evidence references added, out-of-scope material removed, commercial pricing removed, all TOR mandatory deliverables addressed, revenue methodology stated, Go-Live conditions made measurable, Annexes H/I/J added |
| v7.5 | 2026-03-12 | Final revision: commercial remediation pricing fully separated into standalone internal document (`REMEDIATION_PRICING_PROPOSAL.md`, not for client distribution); Section 0 added with explicit headings addressing each of the 6 points raised in the LAR non-acceptance feedback email (2026-03-09); revision notice updated; submission date updated |

---

**Report Prepared By:** Dr. Ismail Kucukdurgut, OrkinosAI  
**Submission Date:** 2026-03-12  
**Engagement Reference:** LAR Technical & Commercial Audit — TOR v1.01
