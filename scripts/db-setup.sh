#!/bin/bash
# ============================================================
# LAR System — Automated Database Setup (Step 7)
#
# Automates Step 7 — Initial Database Setup from AZURE_DEPLOYMENT.md.
# Imports the SQL schema files in db/ into the Azure MySQL Flexible
# Server (or any MySQL server).
#
# Usage:
#   chmod +x scripts/db-setup.sh
#   ./scripts/db-setup.sh [OPTIONS]
#
# Options:
#   --host HOST            MySQL hostname (overrides DB_SERVER_NAME)
#   --user USER            MySQL admin username
#   --password PASS        MySQL admin password
#   --server-name NAME     Azure MySQL Flexible Server name (for host suffix)
#   --ssl-ca FILE          Path to SSL CA cert (default: DigiCertGlobalRootCA.crt.pem)
#   --no-ssl               Disable SSL (for local/dev MySQL only)
#   --dry-run              Print commands without executing them
#   --migrate              Also run migration scripts from db/migrations/
#   --db DB_NAME           Import only the specified database (default: all)
#   -h, --help             Show this help
#
# Environment variables (used as defaults if flags are not set):
#   DB_SERVER_NAME   Azure MySQL Flexible Server name  (e.g. lar-mysql-server)
#   DB_ADMIN_USER    Admin username                     (e.g. laradmin)
#   DB_ADMIN_PASSWORD Admin password
#
# Examples:
#   # Import all schemas using environment variables
#   DB_SERVER_NAME=lar-mysql-server DB_ADMIN_USER=laradmin DB_ADMIN_PASSWORD=secret \
#     ./scripts/db-setup.sh
#
#   # Import only lar_b2c using explicit flags
#   ./scripts/db-setup.sh --host lar-mysql-server.mysql.database.azure.com \
#     --user laradmin --password secret --db lar_b2c
#
#   # Local development (no SSL, root user)
#   ./scripts/db-setup.sh --host localhost --user root --password '' --no-ssl
# ============================================================
set -euo pipefail

# ---- Defaults ------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
DB_DIR="${REPO_ROOT}/db"
MIGRATIONS_DIR="${DB_DIR}/migrations"

HOST=""
USER="${DB_ADMIN_USER:-}"
PASSWORD="${DB_ADMIN_PASSWORD:-}"
SERVER_NAME="${DB_SERVER_NAME:-}"
SSL_CA="${REPO_ROOT}/DigiCertGlobalRootCA.crt.pem"
USE_SSL=true
DRY_RUN=false
RUN_MIGRATIONS=false
ONLY_DB=""

# All six LAR databases in dependency order
DATABASES=(lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux)

# ---- Colours --------------------------------------------------
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; }
die()     { error "$*"; exit 1; }

# ---- Parse arguments -----------------------------------------
while [[ $# -gt 0 ]]; do
  case "$1" in
    --host)        HOST="$2";        shift 2 ;;
    --user)        USER="$2";        shift 2 ;;
    --password)    PASSWORD="$2";    shift 2 ;;
    --server-name) SERVER_NAME="$2"; shift 2 ;;
    --ssl-ca)      SSL_CA="$2";      shift 2 ;;
    --no-ssl)      USE_SSL=false;    shift ;;
    --dry-run)     DRY_RUN=true;     shift ;;
    --migrate)     RUN_MIGRATIONS=true; shift ;;
    --db)          ONLY_DB="$2";     shift 2 ;;
    -h|--help)
      grep '^#' "$0" | sed 's/^# \{0,1\}//' | sed '1{/^!/d}'
      exit 0 ;;
    *) die "Unknown option: $1. Use --help for usage." ;;
  esac
done

# ---- Derive MySQL hostname ------------------------------------
if [[ -z "${HOST}" ]]; then
  if [[ -z "${SERVER_NAME}" ]]; then
    die "Provide --host or set DB_SERVER_NAME (e.g. lar-mysql-server)."
  fi
  # Azure MySQL Flexible Server hostname format
  HOST="${SERVER_NAME}.mysql.database.azure.com"
fi

# ---- Validate required values --------------------------------
[[ -z "${USER}" ]]     && die "Provide --user or set DB_ADMIN_USER."
# Empty password is allowed (local dev root)

# ---- Build mysql base command --------------------------------
MYSQL_CMD=(mysql --host="${HOST}" --user="${USER}")

# Password: pass via env to avoid it appearing in process list
if [[ -n "${PASSWORD}" ]]; then
  export MYSQL_PWD="${PASSWORD}"
fi

# SSL
if ${USE_SSL}; then
  if [[ ! -f "${SSL_CA}" ]]; then
    warn "SSL CA cert not found at '${SSL_CA}'. Attempting connection without SSL."
    warn "Download it from: https://dl.cacerts.digicert.com/DigiCertGlobalRootCA.crt.pem"
    USE_SSL=false
  else
    MYSQL_CMD+=(--ssl-ca="${SSL_CA}" --ssl-mode=REQUIRED)
  fi
fi

MYSQL_CMD+=(--connect-timeout=30)

# ---- Helper: run a SQL file against a database ---------------
run_sql_file() {
  local db_name="$1"
  local sql_file="$2"

  if [[ ! -f "${sql_file}" ]]; then
    warn "Schema file not found: ${sql_file} — skipping ${db_name}"
    return 0
  fi

  info "Importing $(basename "${sql_file}") → ${db_name} ..."

  if ${DRY_RUN}; then
    echo "  [DRY-RUN] ${MYSQL_CMD[*]} ${db_name} < ${sql_file}"
    return 0
  fi

  if "${MYSQL_CMD[@]}" "${db_name}" < "${sql_file}"; then
    success "${db_name} imported successfully."
  else
    error "Failed to import ${sql_file} into ${db_name}."
    return 1
  fi
}

# ---- Helper: verify connectivity before attempting imports ---
verify_connection() {
  info "Verifying MySQL connectivity to ${HOST} ..."
  if ${DRY_RUN}; then
    echo "  [DRY-RUN] ${MYSQL_CMD[*]} -e 'SELECT 1'"
    return 0
  fi
  if ! "${MYSQL_CMD[@]}" -e "SELECT 1" &>/dev/null; then
    die "Cannot connect to MySQL at '${HOST}'. Check credentials and firewall rules."
  fi
  success "Connection verified."
}

# ---- Determine which databases to process --------------------
if [[ -n "${ONLY_DB}" ]]; then
  DATABASES=("${ONLY_DB}")
fi

# ---- Main ----------------------------------------------------
echo ""
echo "============================================================"
echo " LAR System — Database Setup (Step 7)"
echo " Host    : ${HOST}"
echo " User    : ${USER}"
echo " SSL     : ${USE_SSL}"
echo " Dry run : ${DRY_RUN}"
echo " Databases: ${DATABASES[*]}"
echo "============================================================"
echo ""

verify_connection

IMPORT_ERRORS=0

for DB_NAME in "${DATABASES[@]}"; do
  SQL_FILE="${DB_DIR}/${DB_NAME}.sql"
  run_sql_file "${DB_NAME}" "${SQL_FILE}" || IMPORT_ERRORS=$((IMPORT_ERRORS + 1))
done

# ---- Run migrations if requested -----------------------------
if ${RUN_MIGRATIONS} && [[ -d "${MIGRATIONS_DIR}" ]]; then
  echo ""
  info "Running migration scripts from ${MIGRATIONS_DIR} ..."
  MIGRATION_FILES=()
  while IFS= read -r -d '' f; do
    MIGRATION_FILES+=("$f")
  done < <(find "${MIGRATIONS_DIR}" -name "*.sql" -print0 | sort -z)

  if [[ ${#MIGRATION_FILES[@]} -eq 0 ]]; then
    info "No migration scripts found."
  else
    for MIGRATION_FILE in "${MIGRATION_FILES[@]}"; do
      MIGRATION_BASENAME="$(basename "${MIGRATION_FILE}")"
      # Parse target DB from filename prefix: YYYYMMDDHHMMSS_DB_NAME_description.sql
      # e.g. 20260301120000_lar_b2c_add_users_table.sql
      TARGET_DB="$(echo "${MIGRATION_BASENAME}" | grep -oP '(?<=^\d{14}_)[a-z_]+(?=_)')" || TARGET_DB=""

      if [[ -z "${TARGET_DB}" ]]; then
        warn "Cannot determine target DB from filename '${MIGRATION_BASENAME}' — skipping."
        continue
      fi

      # Apply only to requested databases
      if [[ -n "${ONLY_DB}" && "${TARGET_DB}" != "${ONLY_DB}" ]]; then
        continue
      fi

      info "Applying migration: ${MIGRATION_BASENAME} → ${TARGET_DB}"
      run_sql_file "${TARGET_DB}" "${MIGRATION_FILE}" || IMPORT_ERRORS=$((IMPORT_ERRORS + 1))
    done
  fi
fi

echo ""
if [[ ${IMPORT_ERRORS} -eq 0 ]]; then
  success "All database schemas imported successfully."
  echo ""
  echo "Next step: push code to main or manually trigger the GitHub Actions"
  echo "workflow to deploy the application to Azure Web Apps."
else
  error "${IMPORT_ERRORS} import(s) failed. Review the output above."
  exit 1
fi
