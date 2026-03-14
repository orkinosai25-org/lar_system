# LAR Platform — Remediation Services Pricing Proposal

**Document Reference:** LAR-REMEDIATION-PRICING-v1.0  
**Prepared by:** Dr. Ismail Kucukdurgut, OrkinosAI  
**Prepared Date:** 2026-03-12  
**Engagement Reference:** LAR Technical & Commercial Audit — TOR v1.01 / SOW  

> ⚠️ **INTERNAL USE ONLY — NOT FOR CLIENT DISTRIBUTION**  
>
> This document is a confidential internal pricing reference prepared by the auditor for
> future quoting purposes only. It must **not** be submitted to LAR or included in any
> audit deliverable. Per SOW Section 6a, any remediation proposal may only be submitted
> as a separate document, at LAR's explicit invitation, following formal acceptance of
> the audit report. The audit findings and this pricing document are independently
> maintained.

---

## Introduction

This document sets out indicative pricing for remediation services addressing the findings
identified in LAR Audit Report LAR-AUDIT-v7.5-REVISED (the "Audit Report"). Work bundles
are based on the 29 findings documented in that report (12 P0, 8 P1, 9 P2).

Pricing is structured as fixed-price work bundles. Each bundle can be engaged
independently or as part of a phased programme. Bundles are sequenced by risk priority —
Bundle 1 (P0 emergency fixes) should be completed before Bundles 3–6 (vertical
remediations).

**Standard day rate applied throughout:** USD 1,600 / day (8 hours)  
**Sprint capacity assumption:** 1 engineer, 5 days/week  

---

## Work Bundle Summary

| Bundle | Description | Est. Days | Fixed Price |
|--------|-------------|-----------|-------------|
| **B-01** | Emergency Security Remediation (P0) | 18 | $28,800 |
| **B-02** | High Priority Security Remediation (P1) | 11 | $17,600 |
| **B-03** | Flights Vertical — Full Remediation | 26 | $41,600 |
| **B-04** | Hotels Vertical — Full Remediation | 21 | $33,600 |
| **B-05** | Cars Vertical — Full Remediation | 9 | $14,400 |
| **B-06** | Booking Flow & Session Management | 16 | $25,600 |
| **B-07** | Infrastructure & DevOps | 18 | $28,800 |
| **B-08** | Testing & Quality Assurance | 22 | $35,200 |
| **B-09** | PCI DSS Compliance Programme | 30 | $48,000 |
| **B-10** | Platform Modernisation (optional) | 50 | $80,000 |
| **B-11** | Post-Remediation Re-Audit & Go-Live Sign-Off | 8 | $12,800 |
| | **Total (B-01 through B-10)** | **221** | **$353,600** |
| | **Total (B-01 through B-11 incl. re-audit)** | **229** | **$366,400** |

> All prices are in USD and exclusive of applicable taxes.  
> Travel and accommodation (if on-site work is required) billed at cost.  
> Prices valid for 90 days from document date.

---

## Bundle Detail

---

### B-01 — Emergency Security Remediation (P0)

**Addresses:** P0-001, P0-002, P0-003, P0-004, P0-005, P0-006, P0-007, P0-008, P0-009,
P0-010, P0-011, P0-012  
**Priority:** Must be completed before any other bundle or go-live activity  
**Fixed Price:** $28,800 (18 days)

#### Scope

| Task | Audit Ref | Est. Days |
|------|-----------|-----------|
| Remove all hardcoded database credentials; migrate to `.env` / environment variables; rotate all exposed credentials | P0-001 | 2.5 |
| Remove all `debug()`, `var_dump()`, `print_r()`, `exit` statements from all production-path controllers and models | P0-002 | 1.5 |
| Migrate password hashing from MD5 to `PASSWORD_ARGON2ID`; implement forced reset for existing accounts | P0-003 | 2.0 |
| Enable CSRF protection across all six modules (`$config['csrf_protection'] = TRUE`); validate on all POST/PUT/DELETE endpoints | P0-004 | 1.5 |
| Set `ENVIRONMENT = 'production'`; disable `display_errors`, `db_debug`; configure error logging to server-side files | P0-005 | 1.0 |
| Audit all controllers and models; replace raw query string interpolation with CodeIgniter Active Record parameterisation | P0-006 | 3.0 |
| Set `cookie_secure = TRUE` and `cookie_httponly = TRUE` across all six modules | P0-007 | 0.5 |
| Implement global XSS filtering; encode all user-derived output with `htmlspecialchars()` in view layer | P0-008 | 2.5 |
| Remove all API credentials (PayU, PayPal, TBO, Amadeus) from source code; load from environment variables; rotate keys | P0-009 | 1.5 |
| Fix all PHP parse/fatal errors identified by `php -l`; add CI lint gate to deployment pipeline | P0-010 | 1.0 |
| Complete stub/dead-end methods in booking confirmation and notification path; implement error recovery and ops alerting | P0-011 | 1.5 |
| Implement HTTP → HTTPS redirect in `.htaccess`; install valid TLS certificate; enable HSTS header | P0-012 | 0.5 |

**Deliverables:**
- All P0 findings remediated and tested
- `.env.example` template and credential rotation log
- Updated CI/CD lint and security scan configuration
- Evidence log suitable for CGo-01 through CGo-12 sign-off

---

### B-02 — High Priority Security Remediation (P1)

**Addresses:** RR-013, RR-014, RR-015, RR-016, RR-017, RR-018, RR-019, RR-020  
**Priority:** Complete before or concurrently with go-live  
**Fixed Price:** $17,600 (11 days)

#### Scope

| Task | Audit Ref | Est. Days |
|------|-----------|-----------|
| Implement rate limiting on login and registration endpoints (recommend: 5 attempts / 10 minutes, exponential backoff) | RR-013 | 1.5 |
| Configure session expiry: 30 minutes inactivity for B2C; 8 hours for B2B agent portal; implement re-auth warning | RR-014 | 1.0 |
| Complete or fully remove Amadeus GDS integration; if retained, implement working SOAP session pool and token exchange | RR-015 | 3.0 |
| Move payment gateway mode switch (live/test) to environment variable; verify no test credentials active in production | RR-016 | 0.5 |
| Implement PayPal IPN signature verification; reject unverified IPN notifications | RR-017 | 1.0 |
| Disable database debug mode in all production configs; suppress schema information from error responses | RR-018 | 0.5 |
| Add HTTP security response headers: `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` | RR-019 | 1.0 |
| Review and resolve all `FIXME`/`TODO` markers in active booking and payment code paths | RR-020 | 2.5 |

**Deliverables:**
- All P1 security findings remediated
- Security header configuration
- Updated IPN verification implementation
- Evidence suitable for CGo-13 through CGo-18 sign-off

---

### B-03 — Flights Vertical — Full Remediation

**Addresses:** F-001, F-002, F-003, F-004, F-005; CGo-12, CGo-13, CGo-18  
**Priority:** Required before live flight bookings  
**Fixed Price:** $41,600 (26 days)

#### Scope

| Task | Audit Ref | Est. Days |
|------|-----------|-----------|
| Implement live GDS reprice call (TBO `PricePNRWithFare` or equivalent) immediately before payment capture; halt booking and re-present fare to customer if price has changed | F-001 | 4.0 |
| Implement TTL validation on cached fare results; block booking submission if fare TTL has expired; redirect to re-search with user notification | F-002 | 2.0 |
| Implement post-booking PNR status poll (immediate + scheduled +5 min); hold booking in PENDING status until GDS confirmation received; trigger customer email only on confirmed PNR | F-003 | 4.0 |
| Integrate TBO void/cancellation API into `cancel_booking()` controller; surface airline cancellation fees before confirming cancellation; log all supplier responses | F-004 | 3.0 |
| Decision point: Complete Amadeus integration (SOAP session pool, token exchange, fare search, booking) OR remove Amadeus from all production routing configurations | F-005 | 10.0 |
| End-to-end flight booking integration testing in TBO sandbox (search → fare select → reprice → book → confirm → cancel) | — | 3.0 |

**Deliverables:**
- Fully functional flight booking flow with live repricing
- PNR status polling implementation
- Void/cancellation integration
- Amadeus integration (completed or cleanly removed)
- Pre-production test evidence for CGo-12, CGo-13, CGo-18

---

### B-04 — Hotels Vertical — Full Remediation

**Addresses:** H-001, H-002, H-003, H-004; CGo-14, CGo-15  
**Priority:** Required before live hotel bookings  
**Fixed Price:** $33,600 (21 days)

#### Scope

| Task | Audit Ref | Est. Days |
|------|-----------|-----------|
| Implement HK/HL/UC status code differentiation in hotel booking controller; send appropriate customer communications per status; hold/conditional-capture payment for HL/UC | H-001 | 4.0 |
| Implement atomic multi-room booking logic: if any room component fails, cancel all confirmed components and initiate refund; alert operations team | H-002 | 4.0 |
| Integrate PROVAB and GRN cancellation APIs into `cancel_hotel()` controller; surface supplier cancellation policies before confirming; log all cancellation responses | H-003 | 4.0 |
| Migrate GRN integration from API v1 to v2 before v1 deprecation | H-004 | 4.0 |
| End-to-end hotel booking integration testing in PROVAB and GRN sandbox (search → book → HL/UC handling → cancellation flow) | — | 5.0 |

**Deliverables:**
- Correct confirmation status handling for all booking outcomes
- Atomic multi-room booking with rollback
- Supplier cancellation integration for PROVAB and GRN
- GRN v2 migration
- Pre-production test evidence for CGo-14, CGo-15

---

### B-05 — Cars Vertical — Full Remediation

**Addresses:** C-001, C-002, C-003; CGo-16  
**Priority:** Required before live car rental bookings  
**Fixed Price:** $14,400 (9 days)

#### Scope

| Task | Audit Ref | Est. Days |
|------|-----------|-----------|
| Implement Carnect `CheckAvailability` pre-booking call in `confirm_car_booking()` immediately before payment capture; handle unavailability gracefully | C-001 | 2.5 |
| Implement rate TTL validation: store `rate-valid-until` timestamp from Carnect alongside cached results; block booking if rate has expired; redirect to re-search | C-002 | 2.0 |
| Sanitise and encode Carnect policy HTML before output; present key policy points (fuel, mileage, insurance, deposit) in structured format | C-003 | 1.5 |
| End-to-end car rental integration testing in Carnect sandbox (search → availability re-check → book → TTL expiry test) | — | 3.0 |

**Deliverables:**
- Pre-booking availability verification
- Rate TTL enforcement
- Policy content encoding and display
- Pre-production test evidence for CGo-16

---

### B-06 — Booking Flow & Session Management

**Addresses:** BF-001, BF-002, BF-003, BF-004; CGo-11, CGo-17  
**Priority:** Required before go-live  
**Fixed Price:** $25,600 (16 days)

#### Scope

| Task | Audit Ref | Est. Days |
|------|-----------|-----------|
| Configure session expiry: `sess_expiration = 1800` (30 min) for B2C; 28,800 (8 hours) for B2B agent portal; implement idle session warning and re-authentication prompt | BF-001 | 1.5 |
| Implement idempotent booking references; add retry logic with exponential backoff (max 3 retries, 2/4/8 second intervals) for all external API calls; check booking status before retry to prevent duplicates | BF-002 | 4.0 |
| Complete all stub methods in booking confirmation, customer email, voucher generation, and supplier notification path; implement explicit failure handling and operations alerting | BF-003 | 4.0 |
| Implement booking amendment/modification flow for Flights (date change, name correction) and Hotels; expose self-service amendment in customer My Account portal | BF-004 | 5.0 |
| End-to-end booking flow test: confirm steps 6, 8, 11, 12, 13 (per Section 6.4 of Audit Report) are all functional | — | 1.5 |

**Deliverables:**
- Session expiry configuration for all modules
- Idempotent retry logic for all three verticals
- Complete confirmation and notification flow
- Customer-facing booking amendment functionality
- Pre-production test evidence for CGo-11, CGo-17

---

### B-07 — Infrastructure & DevOps

**Priority:** Recommended before go-live; some items can follow post-launch  
**Fixed Price:** $28,800 (18 days)

#### Scope

| Task | Est. Days |
|------|-----------|
| CI/CD pipeline setup (GitHub Actions or equivalent): automated PHP lint (`php -l`), CSRF/cookie config checks, automated test run on push | 3.0 |
| Structured server-side application logging implementation (replace debug output with log files; log rotation; restricted access) | 2.0 |
| Application performance monitoring setup: response time alerts, API failure rate monitoring, booking conversion funnel tracking | 3.0 |
| Database backup automation: scheduled backups, retention policy, restoration testing | 2.0 |
| HTTPS/TLS certificate installation and management: certificate procurement, auto-renewal (Let's Encrypt or equivalent), HSTS preload | 1.5 |
| Security scanning integration: automated `grep`-based credential scan, PHP lint, OWASP ZAP baseline scan in CI pipeline | 2.5 |
| Deployment hardening: environment-based config separation, no production deployment from developer workstations, deployment checklist | 2.0 |
| Incident response runbook: documented procedures for data breach, payment failure, and booking system outage scenarios | 2.0 |

**Deliverables:**
- Configured CI/CD pipeline with automated security checks
- Structured logging framework
- Monitoring dashboards and alerting
- Backup and recovery procedures
- Incident response runbook

---

### B-08 — Testing & Quality Assurance

**Priority:** Complete before go-live; some test infrastructure can be built in parallel with B-03 through B-06  
**Fixed Price:** $35,200 (22 days)

#### Scope

| Task | Est. Days |
|------|-----------|
| Unit test suite for critical security functions: authentication, password hashing, CSRF validation, input sanitisation | 3.0 |
| Integration tests: flight booking flow against TBO sandbox (search → reprice → book → confirm → cancel) | 4.0 |
| Integration tests: hotel booking flow against PROVAB/GRN sandbox (search → HK/HL/UC handling → cancel) | 3.0 |
| Integration tests: car booking flow against Carnect sandbox (search → TTL check → book) | 2.0 |
| End-to-end booking flow tests: full user journey from search to post-booking confirmation for all three verticals | 4.0 |
| Security regression tests: CSRF bypass, XSS injection, SQL injection, session fixation | 3.0 |
| Load and performance baseline testing: search response times, booking flow throughput, concurrent user simulation | 3.0 |

**Deliverables:**
- Automated unit test suite (target: ≥80% coverage of critical paths)
- Integration test suite with sandbox API fixtures
- End-to-end test scenarios for all three verticals
- Security regression test suite
- Performance baseline report

---

### B-09 — PCI DSS Compliance Programme

**Priority:** Required for any platform processing live payment card data  
**Fixed Price:** $48,000 (30 days)

#### Scope

| Task | Est. Days |
|------|-----------|
| PCI DSS v4.0 gap assessment against all 12 requirements; current compliance rating: ~17% (audit finding) | 3.0 |
| Requirement 2: Default credential removal, system hardening documentation | 2.0 |
| Requirement 3: Cardholder data inventory; confirm no card data stored; tokenisation review | 3.0 |
| Requirement 4: TLS verification; cipher suite review; encrypted transit for all cardholder data | 1.5 |
| Requirement 6: Secure development lifecycle; code review process; patch management policy | 2.0 |
| Requirement 7 & 8: Access control review; authentication hardening; MFA for admin and privileged access | 2.5 |
| Requirement 10: Audit logging implementation; log retention (minimum 12 months); log integrity | 3.0 |
| Requirement 11: Vulnerability scanning (ASV quarterly scan); penetration test coordination (scoped separately) | 2.0 |
| Requirement 12: Information security policy documentation; security awareness programme | 3.0 |
| SAQ-D completion (or ROC if applicable); evidence file assembly | 4.0 |
| QSA liaison and submission coordination | 4.0 |

> **Note:** Formal QSA assessment fees and card brand submission fees are not included.
> These are charged directly by the QSA and card networks and are typically USD
> 5,000–25,000 depending on QSA firm and scope.

**Deliverables:**
- PCI DSS gap remediation across all 12 requirements
- Completed SAQ-D questionnaire with evidence file
- Security policy and procedure documentation
- Audit log infrastructure
- QSA submission package

---

### B-10 — Platform Modernisation (Optional)

**Priority:** Recommended post-launch; required within 12–18 months given CodeIgniter 2.x EOL  
**Fixed Price:** $80,000 (50 days)

#### Scope

| Task | Est. Days |
|------|-----------|
| Migration assessment: dependency inventory, third-party library audit, upgrade path planning | 3.0 |
| CodeIgniter 3.x migration (intermediate step): update routing, models, views; resolve 2.x deprecations | 12.0 |
| PHP 8.x compatibility: resolve deprecated functions, type error fixes, strict mode compliance | 5.0 |
| Database query optimisation: index review, slow query analysis, N+1 query elimination | 4.0 |
| API response caching layer: implement Redis or Memcached for GDS search result caching with proper TTL management | 5.0 |
| API client standardisation: abstract GDS/supplier clients behind a unified interface for easier provider switching | 8.0 |
| Front-end performance: asset bundling, CDN integration, lazy loading for search results | 5.0 |
| Architecture documentation: updated system architecture diagrams, API integration maps, data flow documentation | 4.0 |
| Full regression testing post-migration | 4.0 |

> **Optional add-on:** Full CodeIgniter 4.x migration (from step 3.x above) — additional
> 25 days / $40,000. Recommended for long-term maintainability.

**Deliverables:**
- Platform running on supported PHP 8.x and CodeIgniter 3.x (or 4.x with add-on)
- Database optimisation report with before/after performance metrics
- Redis caching layer for GDS results
- Updated architecture documentation
- Full regression test results

---

### B-11 — Post-Remediation Re-Audit & Go-Live Sign-Off

**Priority:** Final step; required to issue Go-Live clearance  
**Fixed Price:** $12,800 (8 days)

#### Scope

| Task | Est. Days |
|------|-----------|
| Re-audit verification of all 18 Conditional Go conditions (CGo-01 through CGo-18) from Audit Report Section 8.2 | 4.0 |
| Controlled pre-production testing: booking flow tests for all three verticals with GDS sandbox access | 2.0 |
| Updated audit report with closure evidence for each finding | 1.0 |
| Go-Live clearance certificate and executive summary for LAR CTO and management | 0.5 |
| Handover session: findings summary, outstanding P2 items, post-launch monitoring recommendations | 0.5 |

**Deliverables:**
- Signed Go-Live clearance certificate (issued only if all CGo conditions met)
- Updated audit report with closure evidence
- Post-launch monitoring recommendations report

---

## Engagement Options

### Option A — Full Programme (B-01 through B-10)

| Bundles | Price | Duration |
|---------|-------|----------|
| B-01 to B-10 | $353,600 | ~44 weeks (sequential) |
| B-01 to B-10 with parallel execution | $353,600 | ~24 weeks (2 engineers) |
| Add B-11 (re-audit) | +$12,800 | +1 week |
| **Full programme + re-audit** | **$366,400** | **~25 weeks (2 engineers)** |

### Option B — Minimum Viable Launch Programme (B-01 through B-06)

Addresses all 18 Conditional Go conditions without platform modernisation, full QA suite,
or PCI DSS programme. Suitable for a time-constrained launch followed by a phased
post-launch remediation schedule.

| Bundles | Price | Duration |
|---------|-------|----------|
| B-01 to B-06 | $161,600 | ~20 weeks (1 engineer) |
| B-01 to B-06 with parallel execution | $161,600 | ~12 weeks (2 engineers) |
| Add B-11 (re-audit) | +$12,800 | +1 week |
| **MVL programme + re-audit** | **$174,400** | **~13 weeks (2 engineers)** |

### Option C — Security-Only Stabilisation (B-01 + B-02)

Addresses all P0 and P1 security findings only. Does not address vertical integration
deficiencies. Suitable as an emergency stabilisation prior to full remediation engagement.

| Bundles | Price | Duration |
|---------|-------|----------|
| B-01 + B-02 | $46,400 | ~4.5 weeks (1 engineer) |

---

## Terms & Conditions

### Payment Schedule

| Milestone | Trigger | Amount |
|-----------|---------|--------|
| Commencement | Signed engagement letter | 25% of bundle price |
| Mid-point | Delivery of draft implementation | 50% of bundle price |
| Completion | Acceptance of deliverables | 25% of bundle price |

### Assumptions & Exclusions

- **Included:** All code changes, implementation, documentation, and integration testing described in scope above
- **Included:** Up to 2 rounds of client feedback per deliverable
- **Included:** Knowledge transfer session on completion of each bundle
- **Excluded:** QSA fees, card brand fees, and third-party penetration testing (B-09)
- **Excluded:** GDS/supplier sandbox API credentials (to be provided by LAR)
- **Excluded:** Server provisioning, hosting costs, and third-party licence fees
- **Excluded:** Ongoing support and maintenance after bundle delivery (quoted separately on request)
- **Excluded:** Work outside the scope defined in each bundle above

### Change Management

Any work outside the defined scope above will be quoted separately at the standard day
rate of USD 1,600/day before commencement. No out-of-scope work will be undertaken
without a signed change order.

### Validity

Prices are valid for 90 days from the document date (2026-03-12). Prices are subject to
review for engagements commencing more than 90 days from this date.

---

## Document Control

| Version | Date | Notes |
|---------|------|-------|
| v1.0 | 2026-03-12 | Initial internal pricing document; prepared following issuance of Audit Report LAR-AUDIT-v7.5-REVISED |

---

**Prepared by:** Dr. Ismail Kucukdurgut, OrkinosAI  
**Status:** INTERNAL DRAFT — For quoting reference only  
**Engagement Reference:** LAR Technical & Commercial Audit — TOR v1.01 / SOW
