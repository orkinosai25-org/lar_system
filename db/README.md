# LAR System — Database Schema Files

This directory contains the SQL schema definitions for all six LAR System databases.
These scripts are used by `scripts/db-setup.sh` and the `database-setup.yml` GitHub
Actions workflow to automate **Step 7 — Initial Database Setup** from `AZURE_DEPLOYMENT.md`.

## Databases

| File | Database | Vertical |
|------|----------|---------|
| `lar_b2c.sql` | lar_b2c | B2C Consumer Website |
| `lar_agent.sql` | lar_agent | B2B Agent Panel |
| `lar_supplier.sql` | lar_supplier | Supplier Portal |
| `lar_supervision.sql` | lar_supervision | Back-office / Supervision Panel |
| `lar_webservices.sql` | lar_webservices | Web Services API |
| `lar_ultralux.sql` | lar_ultralux | UltraLux Premium Module |

## Usage

### Automated (GitHub Actions)
Trigger the **Database Setup** workflow from the GitHub Actions tab.
It imports all schema files to the Azure MySQL server configured via GitHub Secrets.

### Manual (local machine or Azure Cloud Shell)
```bash
chmod +x scripts/db-setup.sh
./scripts/db-setup.sh
```

See `scripts/db-setup.sh --help` for available options.

## Character set
All databases use `utf8mb4` / `utf8mb4_unicode_ci` to support the full Unicode range.

## Adding new migrations
Place incremental migration scripts in `db/migrations/` using the naming convention:
`YYYYMMDDHHMMSS_description.sql`

They will be picked up automatically by `scripts/db-setup.sh --migrate`.
