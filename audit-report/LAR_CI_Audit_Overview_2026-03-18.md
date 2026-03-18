# LAR System — CI/CD Audit Overview Report

**Report Date:** 2026-03-18  
**Prepared by:** GitHub Actions CI/CD Pipeline (Vertical Tests & Error Report)  
**Workflow Run ID:** 23225145549  
**Commit SHA:** `185d0bb15723dad99199ad4986ff09af9a84490f`  
**Branch:** `main`  
**For inclusion in:** LAR System Audit Report v7.7 — Section: CI/CD Code Quality & Deployment Audit  
**Source data:** [`vertical-error-report-23225145549.txt`](./vertical-error-report-23225145549.txt)  
**GitHub Actions run:** https://github.com/orkinosai25-org/lar_system/actions/runs/23225145549

---

## Executive Summary

An automated vertical check was executed across all 6 LAR System application modules covering **1,625 PHP files**. The scan identified **33 fatal PHP parse errors** (files that cannot be loaded by the runtime), **15 deprecation-level syntax warnings**, **11 database configuration security issues**, and **122 TODO/FIXME/HACK markers** indicating incomplete or deferred work.

The Services vertical is the most critical: **30 of the 33 fatal errors** originate from outdated third-party libraries (PHPExcel and TCPDF) that use PHP array/string offset syntax (`$var{n}`) which is no longer supported in PHP 8.2. These libraries cannot be loaded at runtime and must be replaced or patched before the Services API is production-safe.

All 6 verticals store hardcoded database passwords in their production configuration files instead of reading credentials from environment variables, representing a material credential-exposure risk.

| Severity | Finding | Count |
|----------|---------|-------|
| 🔴 CRITICAL | Fatal PHP parse errors (files unloadable by runtime) | 33 files |
| 🔴 CRITICAL | Hardcoded DB passwords in production config | 5 of 6 verticals |
| 🟠 HIGH | Production DB configs not using `getenv()` | 5 of 6 verticals |
| 🟡 MEDIUM | PHP 8.2 deprecation warnings (parameter ordering) | 15+ occurrences |
| 🟡 MEDIUM | Hardcoded credential patterns in application code | All 6 verticals |
| 🟢 INFO | TODO/FIXME/HACK markers (incomplete work) | 122 markers |

---

## 1. Scope of Automated Checks

The CI/CD pipeline runs four automated checks for each of the six verticals:

| Check | Tool | Description |
|-------|------|-------------|
| PHP Syntax Validation | `php -l` | Full recursive scan of all `.php` files using PHP 8.2 |
| Critical File Existence | Shell | Verifies production DB config, controllers, models directories |
| DB Configuration Security | Grep/Shell | Checks for hardcoded passwords and missing `getenv()` usage |
| CodeIgniter Structure | Grep/Shell | Counts TODO/FIXME markers; flags hardcoded credentials in app code |

**Files scanned per vertical:**

| Vertical | PHP Files Scanned |
|----------|-------------------|
| Agent | 220 |
| B2C | 215 |
| Services | 522 |
| Supervision | 342 |
| Supplier | 111 |
| UltraLux | 215 |
| **Total** | **1,625** |

---

## 2. PHP Syntax Check Results

### 2.1 Fatal Parse Errors (33 files — runtime-breaking)

These files **cannot be executed** by PHP 8.2. Any request that loads these files will result in a 500 error.

#### 2.1.1 Agent Vertical (1 fatal error)

| File | Error | Line |
|------|-------|------|
| `agent/application/controllers/hotel.php` | `PHP Parse error: Unclosed '{' on line 12` | 1028 |

**Analysis:** Unclosed brace in the hotel controller. The opening `{` on line 12 (likely a function or if-block) does not have a matching closing brace at end of file. This makes the entire hotel controller unusable.

**Remediation:** Add the missing closing `}` to close the unclosed block in `hotel.php`.

---

#### 2.1.2 B2C Vertical (1 fatal error)

| File | Error | Line |
|------|-------|------|
| `b2c/views/template_list/template_v3/share/transfer_search.php` | `PHP Fatal error: Unparenthesized 'a ? b : c ? d : e' is not supported` | 26 |

**Analysis:** PHP 8.0 removed support for unparenthesized chained ternary operators. The expression on line 26 uses `$a ? $b : $c ? $d : $e` without parentheses, which is ambiguous and was removed from PHP 8.0+.

**Remediation:** Add parentheses: change `$a ? $b : $c ? $d : $e` to `$a ? $b : ($c ? $d : $e)`.

---

#### 2.1.3 Services Vertical (30 fatal errors)

The Services vertical has the most critical failures. All 30 are caused by **deprecated third-party library code** that uses PHP 5/7-era syntax removed in PHP 8.0/8.2.

**Application Code (1 file):**

| File | Error | Line |
|------|-------|------|
| `services/webservices/application/models/flight_model.php` | `PHP Parse error: syntax error, unexpected token "public"` | 279 |

**Framework Libraries (1 file):**

| File | Error |
|------|-------|
| `services/system/libraries/Profiler.php` | `PHP Fatal error: Array and string offset access syntax with curly braces is no longer supported` |

**PHPExcel Library (19 files — entire library broken):**

PHPExcel is an abandoned library that extensively uses `$string{$index}` curly-brace array/string offset syntax, which was removed in PHP 8.0. All 19 files fail to parse:

| File |
|------|
| `PHPExcel/Shared/OLE.php` |
| `PHPExcel/Shared/String.php` |
| `PHPExcel/Shared/ZipStreamWrapper.php` |
| `PHPExcel/Cell/DefaultValueBinder.php` |
| `PHPExcel/Calculation/FormulaParser.php` |
| `PHPExcel/Calculation/Functions.php` |
| `PHPExcel/Calculation/Engineering.php` |
| `PHPExcel/Calculation/TextData.php` |
| `PHPExcel/Reader/Excel5.php` |
| `PHPExcel/Reader/Excel2003XML.php` |
| `PHPExcel/Reader/SYLK.php` |
| `PHPExcel/Reader/Excel5/Escher.php` |
| `PHPExcel/ReferenceHelper.php` |
| `PHPExcel/Calculation.php` |
| `PHPExcel/Worksheet/AutoFilter.php` |
| `PHPExcel/Cell.php` |
| `PHPExcel/Writer/Excel5/Workbook.php` |
| `PHPExcel/Writer/Excel5/Worksheet.php` |
| `PHPExcel/Writer/Excel5/Parser.php` |

> ⚠️ **Note:** PHPExcel was officially abandoned in 2017. Its recommended replacement is **PhpSpreadsheet** (`phpoffice/phpspreadsheet`). Any Excel export/import functionality in the Services API is currently non-functional on PHP 8.2.

**TCPDF Library (9 files — entire library broken):**

TCPDF also uses curly-brace offset syntax throughout its codebase. All 9 affected files fail to parse:

| File |
|------|
| `tcpdf/tcpdf.php` |
| `tcpdf/include/tcpdf_filters.php` |
| `tcpdf/include/barcodes/datamatrix.php` |
| `tcpdf/include/barcodes/pdf417.php` |
| `tcpdf/include/tcpdf_static.php` |
| `tcpdf/include/tcpdf_images.php` |
| `tcpdf/include/tcpdf_colors.php` |
| `tcpdf/tcpdf_barcodes_1d.php` |
| `tcpdf/tcpdf_parser.php` |

> ⚠️ **Note:** The bundled TCPDF version in `/services/system/libraries/tcpdf/` is PHP 7-era. The recommended fix is to upgrade to TCPDF ≥ 6.6.0 (the current release has PHP 8.x support) or replace with **mPDF** or **Dompdf**. Any PDF generation in the Services API is currently non-functional on PHP 8.2.

---

#### 2.1.4 Supplier Vertical (1 fatal error)

| File | Error | Line |
|------|-------|------|
| `supplier/application/controllers/eco_stays.php` | `PHP Fatal error: Cannot use string as default value for parameter $season_origin of type int` | 700 |

**Analysis:** A function parameter is declared as `int $season_origin = 'some_string'` — the default value type does not match the declared type. In PHP 8.x this is a fatal error (in PHP 7 it was only a warning).

**Remediation:** Change the default value to an integer (e.g., `0`) or remove the type declaration if a string is a valid value.

---

### 2.2 PHP Deprecation Warnings (non-fatal, should be addressed)

The following files generate deprecation notices in PHP 8.2 but are not fatal parse errors. They will produce warning messages in logs and may become fatal in future PHP versions.

| Vertical | File | Warning |
|----------|------|---------|
| B2C | `b2c/models/payment_model.php` | Optional parameter `$convenience_fees` and `$promocode_discount` declared before required parameters |
| Services | `services/webservices/application/models/hotel_model_v3.php` | Optional parameter `$pan_card_number` before required parameter `$status` |
| Services | `services/webservices/application/models/user_model.php` | Optional parameters before required parameters (2 methods) |
| Services | `services/system/libraries/Log.php` | Optional parameter `$level` before required parameter `$msg` |
| Services | `services/system/libraries/Xmlrpcs.php` | Using `${var}` in strings (deprecated, use `{$var}`) |
| Services | `services/system/libraries/Api_Interface.php` | Optional parameter `$request` before required parameter |
| Services | `services/system/libraries/PHPExcel/Shared/Drawing.php` | Multiple optional-before-required parameters |
| Services | `services/system/libraries/PHPExcel/Shared/trend/trendClass.php` | Optional parameter before required |
| Services | `services/system/libraries/hotel/GRN/HB/hb.php` | Optional parameter before required |
| Supervision | `supervision/application/controllers/eco_stays.php` | Optional parameter before required (`$season_origin` before `$end_date`) |
| UltraLux | `ultralux/application/models/domain_management_model.php` | Optional parameter `$markup_origin` before required parameter `$domain_origin` |

**Pattern:** The `$season_origin` / `$markup_origin` optional-before-required pattern appears in multiple verticals (`eco_stays.php` in both Supplier and Supervision, `domain_management_model.php` in both Agent and UltraLux), suggesting the same base code is deployed across multiple verticals with the same bug.

---

### 2.3 Vertical Summary Table

| Vertical | Files Checked | Fatal Errors | Deprecation Warnings | Missing Files |
|----------|--------------|--------------|----------------------|---------------|
| Agent | 220 | 1 | 0 | 0 |
| B2C | 215 | 1 | 1 | 0 |
| Services | 522 | 30 | 8 | 0 |
| Supervision | 342 | 0 | 1 | 0 |
| Supplier | 111 | 1 | 0 | 0 |
| UltraLux | 215 | 0 | 1 | 0 |
| **TOTAL** | **1,625** | **33** | **11** | **0** |

---

## 3. Database Configuration Security Audit

### 3.1 Findings per Vertical

| Vertical | Hardcoded Password | No `getenv()` | `db_debug` FALSE | utf8mb4 |
|----------|--------------------|---------------|------------------|---------|
| Agent | ⚠️ YES | ⚠️ YES | ✅ OK | ℹ️ Not set |
| B2C | ⚠️ YES | ⚠️ YES | ✅ OK | ℹ️ Not set |
| Services | ✅ OK | ⚠️ YES | ✅ OK | ℹ️ Not set |
| Supervision | ⚠️ YES | ⚠️ YES | ✅ OK | ℹ️ Not set |
| Supplier | ⚠️ YES | ⚠️ YES | ✅ OK | ℹ️ Not set |
| UltraLux | ⚠️ YES | ⚠️ YES | ✅ OK | ℹ️ Not set |

**Total DB config issues: 11** (across 6 verticals)

### 3.2 Analysis

**Hardcoded Passwords (5 verticals):** The production database configuration files for Agent, B2C, Supervision, Supplier, and UltraLux contain literal plaintext password values. If the repository becomes accessible to unauthorized parties (e.g., accidental public exposure, insider threat, or repository breach), these credentials are immediately compromised.

**No `getenv()` usage (5 verticals):** Best practice for Azure-hosted applications is to read database credentials from Azure App Service Application Settings (environment variables), not from committed config files. The Services vertical appears to have partial improvement (password check passed) but still doesn't use `getenv()` for all credentials.

**`db_debug` (all OK):** All 6 verticals correctly set `db_debug = FALSE` in their production configurations. This prevents database error messages from being exposed to end users.

**`utf8mb4` charset (all missing):** All 6 verticals use legacy `utf8` charset rather than `utf8mb4`. The `utf8` collation in MySQL cannot store 4-byte Unicode characters (emoji, certain special characters). This can cause silent data truncation. This is a non-critical issue for the current use case but should be addressed for full Unicode support.

### 3.3 Remediation

For each vertical, update the production database config to use environment variables:

```php
// BEFORE (insecure — hardcoded):
$db['default']['password'] = 'MyPlainTextPassword';

// AFTER (secure — env var driven):
$db['default']['password'] = getenv('DB_PASSWORD') ?: '';
```

Set the values in Azure App Service → Application Settings:
- `DB_HOSTNAME`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

---

## 4. Code Quality Audit — TODO/FIXME/HACK Markers

### 4.1 Summary

| Vertical | TODO/FIXME/HACK Count |
|----------|----------------------|
| Agent | 14 |
| B2C | 7 |
| Services | 74 |
| Supervision | 11 |
| Supplier | 1 |
| UltraLux | 15 |
| **Total** | **122** |

### 4.2 Notable Markers

**Recurring patterns across verticals (same code copied):**
- `//FIXME: insert the country code to DB` — found in Agent, B2C, UltraLux car booking views
- `//FIXME get ISO CODE --- ISO_INDIA` — found in Agent, B2C, UltraLux TBO booking pages
- `if($v != 'package') {//FIXME: remove later` — found in navigation and report templates across multiple verticals
- `// TODO: Send SMS notification` — found in Agent, UltraLux domain management models

**Services vertical (74 markers):** The high count in Services is expected for a complex API integration layer. These should be triaged with the development team to identify which represent genuine functionality gaps vs. cosmetic notes.

**Agent `auth.php` — hardcoded default password:**
```
agent/application/controllers/auth.php:66:
$user_record['data'][0]['password'] = provab_encrypt(md5(trim('Provab@123')));
```
This line sets a user's password to `Provab@123` (a likely vendor default). If this is reachable in production, it represents a high-risk credential backdoor.

---

## 5. Hardcoded Credentials in Application Code

The CodeIgniter structure check flags any password-handling code matching the pattern `['password'] = '...'` in non-production files. All 6 verticals triggered this warning. The majority of matches are legitimate password form validation or encryption handlers, not actual hardcoded secrets. However, the following warrants specific review:

| Vertical | File | Concern |
|----------|------|---------|
| Agent | `controllers/auth.php:66` | `provab_encrypt(md5(trim('Provab@123')))` — default vendor password hardcoded |
| UltraLux | `controllers/auth.php:67` | Same `Provab@123` default password |
| Supplier | `controllers/auth.php:49, 130, 134, 136` | MD5-hashed passwords (MD5 is not a secure password hash) |
| All verticals | Various | Use of `md5()` for password hashing — SHA-256/bcrypt should be used instead |

> ⚠️ **MD5 Password Hashing:** Multiple controllers use `md5($password)` for password operations. MD5 is cryptographically broken for password storage. PHP's `password_hash()` / `password_verify()` with bcrypt should replace all md5-based password operations.

---

## 6. CI/CD Pipeline Health

### 6.1 Workflow Run Details

| Workflow | Run ID | Status | Trigger |
|----------|--------|--------|---------|
| Vertical Tests & Error Report | [23225145549](https://github.com/orkinosai25-org/lar_system/actions/runs/23225145549) | ✅ Completed (Success) | Push to `main` (PR #47 merge) |
| Vertical Tests & Error Report | [23224710191](https://github.com/orkinosai25-org/lar_system/actions/runs/23224710191) | ✅ Completed (Success) | Push to `main` (PR #45 merge) |

### 6.2 Workflow Design Notes

The Vertical Tests workflow is designed to **always pass** (it never fails the CI build) — it reports errors but does not block deployment. This is intentional to allow iterative remediation without hard-stopping the pipeline. The findings must be reviewed and actioned manually.

Scheduled runs (Sundays 02:00 UTC) ensure a fresh report is generated even without a code push.

### 6.3 Artefact Retention

- Per-vertical reports: retained as GitHub Actions artifacts for **30 days**
- Aggregated error report: retained as GitHub Actions artifacts for **90 days**
- Committed reports in `audit-report/`: **permanent** (committed to `main` branch)

---

## 7. Prioritised Remediation Summary

Based on severity and business impact, the following remediation order is recommended:

### 🔴 Priority 1 — Immediate (Pre-Production Blockers)

| # | Action | Vertical(s) | Effort |
|---|--------|-------------|--------|
| P1.1 | Replace PHPExcel with PhpSpreadsheet (`composer require phpoffice/phpspreadsheet`) | Services | Medium |
| P1.2 | Upgrade or replace bundled TCPDF library to version supporting PHP 8.x | Services | Medium |
| P1.3 | Fix unclosed `{` in `hotel.php` | Agent | Low |
| P1.4 | Fix unparenthesized ternary in `transfer_search.php` | B2C | Low |
| P1.5 | Fix type mismatch default value in `eco_stays.php` | Supplier | Low |
| P1.6 | Fix `flight_model.php` parse error (unexpected token "public") | Services | Low |
| P1.7 | Move all DB credentials to Azure App Service env vars; use `getenv()` | All | Medium |

### 🟠 Priority 2 — High (Security & Stability)

| # | Action | Vertical(s) | Effort |
|---|--------|-------------|--------|
| P2.1 | Remove or rotate hardcoded `Provab@123` default password in `auth.php` | Agent, UltraLux | Low |
| P2.2 | Replace MD5 password hashing with `password_hash()` / `password_verify()` | All | High |
| P2.3 | Fix optional-before-required parameter ordering in `eco_stays.php`, `domain_management_model.php`, `payment_model.php` | B2C, Services, Supervision, Supplier, UltraLux | Low–Medium |

### 🟡 Priority 3 — Medium (Code Quality)

| # | Action | Vertical(s) | Effort |
|---|--------|-------------|--------|
| P3.1 | Triage and resolve 74 TODO/FIXME markers in Services | Services | High |
| P3.2 | Fix `${var}` string interpolation deprecation in `Xmlrpcs.php` | Services | Low |
| P3.3 | Update database charset to `utf8mb4` across all verticals | All | Low |
| P3.4 | Resolve remaining 48 TODO/FIXME markers (Agent, B2C, Supervision, UltraLux) | Multiple | Medium |

---

## 8. Cross-Reference to Main Audit Report

This CI/CD audit section maps to the following items in **LAR_Audit_Report_v7.7**:

| CI Finding | Audit Report Section | Risk Register Item |
|------------|---------------------|--------------------|
| PHPExcel/TCPDF PHP 8.2 incompatibility | TOR 3.x — Technical Debt | Annex J — Risk Register |
| Hardcoded DB credentials | TOR 4.x — Security | Annex H — Static Analysis Evidence |
| MD5 password hashing | TOR 4.x — Security | Annex J — Risk Register |
| No `getenv()` for credentials | TOR 4.x — Security | Annex H — Static Analysis Evidence |
| Unclosed brace / parse errors | TOR 3.x — Technical Debt | Annex J — Risk Register |
| TODO/FIXME markers | TOR 5.x — Code Quality | REMEDIATION_ROADMAP.md |

---

## Appendix A — Files Generating Fatal Parse Errors

Complete list of 33 files with fatal PHP 8.2 parse errors:

```
agent/application/controllers/hotel.php
b2c/views/template_list/template_v3/share/transfer_search.php
services/webservices/application/models/flight_model.php
services/system/libraries/Profiler.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Shared/OLE.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Shared/String.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Shared/ZipStreamWrapper.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Cell/DefaultValueBinder.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Calculation/FormulaParser.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Calculation/Functions.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Calculation/Engineering.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Calculation/TextData.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Reader/Excel5.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Reader/Excel2003XML.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Reader/SYLK.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Reader/Excel5/Escher.php
services/system/libraries/PHPExcel/Classes/PHPExcel/ReferenceHelper.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Calculation.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Worksheet/AutoFilter.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Cell.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Writer/Excel5/Workbook.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Writer/Excel5/Worksheet.php
services/system/libraries/PHPExcel/Classes/PHPExcel/Writer/Excel5/Parser.php
services/system/libraries/tcpdf/tcpdf.php
services/system/libraries/tcpdf/include/tcpdf_filters.php
services/system/libraries/tcpdf/include/barcodes/datamatrix.php
services/system/libraries/tcpdf/include/barcodes/pdf417.php
services/system/libraries/tcpdf/include/tcpdf_static.php
services/system/libraries/tcpdf/include/tcpdf_images.php
services/system/libraries/tcpdf/include/tcpdf_colors.php
services/system/libraries/tcpdf/tcpdf_barcodes_1d.php
services/system/libraries/tcpdf/tcpdf_parser.php
supplier/application/controllers/eco_stays.php
```

---

## Appendix B — Workflow Configuration

The checks in this report are run by `.github/workflows/vertical-tests.yml`.  
Post-deployment comprehensive reports (combining these checks with Azure runtime logs and integration tests) are generated by `.github/workflows/post-deploy-audit-report.yml`.

Both reports are committed to this `audit-report/` folder on every successful run against `main`.

---

*This document was generated from automated CI/CD pipeline data. For the full technical audit, see [`AUDIT_REPORT.md`](../AUDIT_REPORT.md). For the remediation roadmap, see [`REMEDIATION_ROADMAP.md`](../REMEDIATION_ROADMAP.md). For the risk register, see [`audit-files/Annex_J_Risk_Register_and_Remediation_Backlog.html`](../audit-files/Annex_J_Risk_Register_and_Remediation_Backlog.html).*
