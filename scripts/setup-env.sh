#!/bin/bash
# ============================================================
# LAR System — Interactive Environment Setup
#
# Guides you through entering all required credentials and
# writes them to a local .env file for development/testing.
#
# Usage:
#   chmod +x scripts/setup-env.sh
#   ./scripts/setup-env.sh
#   ./scripts/setup-env.sh --output /path/to/.env   # custom output path
#   ./scripts/setup-env.sh --env-file .env.staging  # different target file
#   ./scripts/setup-env.sh --non-interactive         # load defaults only, no prompts
#
# After running this script, use scripts/azure-appsettings-sync.sh to push
# the generated .env values to Azure App Service Application Settings.
# ============================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# ---- Defaults ------------------------------------------------
ENV_OUTPUT="${REPO_ROOT}/.env"
NON_INTERACTIVE=false

# ---- Colours -------------------------------------------------
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; }
die()     { error "$*"; exit 1; }
header()  { echo -e "\n${BOLD}${CYAN}$*${RESET}"; echo "$(echo "$*" | tr '[:print:]' '-')"; }

# ---- Parse arguments -----------------------------------------
while [[ $# -gt 0 ]]; do
  case "$1" in
    --output|--env-file) ENV_OUTPUT="$2"; shift 2 ;;
    --non-interactive)   NON_INTERACTIVE=true; shift ;;
    -h|--help)
      grep '^#' "$0" | sed 's/^# \{0,1\}//' | sed '1{/^!/d}'
      exit 0 ;;
    *) die "Unknown option: $1. Use --help for usage." ;;
  esac
done

# ---- Helper: generate a cryptographically random 32-char hex key -
generate_key() {
  local key=""
  # openssl is the preferred method — exits 0 and reliably produces 32 hex chars
  if command -v openssl &>/dev/null; then
    key="$(openssl rand -hex 16)"
  else
    # Fallback: read 128 bytes (plenty of entropy) and hex-encode via od, then truncate
    key="$(dd if=/dev/urandom bs=128 count=1 2>/dev/null | od -An -tx1 | tr -d ' \n' | head -c 32)"
  fi
  if [[ ${#key} -lt 32 ]]; then
    echo "ERROR: generate_key produced a key shorter than 32 characters — cannot continue." >&2
    exit 1
  fi
  echo "$key"
}

# ---- Helper: prompt with a default and optional secret mode --
# Usage: prompt VAR_NAME "Prompt text" "default_value" [secret]
prompt() {
  local varname="$1"
  local label="$2"
  local default="$3"
  local secret="${4:-}"

  if ${NON_INTERACTIVE}; then
    printf -v "$varname" '%s' "$default"
    return
  fi

  local display_default=""
  if [[ -n "$default" && "$secret" != "secret" ]]; then
    display_default=" [${default}]"
  elif [[ -n "$default" && "$secret" == "secret" ]]; then
    display_default=" [leave blank to keep existing / press Enter for empty]"
  fi

  echo -e -n "  ${BOLD}${label}${display_default}: ${RESET}"

  if [[ "$secret" == "secret" ]]; then
    read -rs value
    echo  # newline after hidden input
  else
    read -r value
  fi

  # Use default if empty
  if [[ -z "$value" ]]; then
    value="$default"
  fi

  printf -v "$varname" '%s' "$value"
}

# ---- Check for existing .env to use as defaults --------------
EXISTING_ENV="${REPO_ROOT}/.env"
load_existing() {
  local key="$1"
  if [[ -f "$EXISTING_ENV" ]]; then
    grep -m1 "^${key}=" "$EXISTING_ENV" | cut -d'=' -f2- | sed "s/^['\"]//;s/['\"]$//" || echo ""
  else
    echo ""
  fi
}

# ---- Banner --------------------------------------------------
echo ""
echo -e "${BOLD}${CYAN}========================================================"
echo "  LAR System — Environment Setup"
echo "========================================================${RESET}"
echo ""
echo "  This script will create: ${BOLD}${ENV_OUTPUT}${RESET}"
echo "  Press Enter to accept defaults shown in [brackets]."
echo "  Password fields are hidden."
echo ""

if [[ -f "$ENV_OUTPUT" ]] && ! ${NON_INTERACTIVE}; then
  warn "A .env file already exists at: ${ENV_OUTPUT}"
  echo -e -n "  Overwrite it? [y/N]: "
  read -r CONFIRM
  if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo "Aborted. No changes made."
    exit 0
  fi
fi

# ==============================================================
# SECTION 1: Application Environment
# ==============================================================
header "1 / 7  Application Environment"
prompt APP_ENV "Environment (development | testing | production)" \
  "$(load_existing APP_ENV)" 2>/dev/null || APP_ENV="production"
[[ -z "$APP_ENV" ]] && APP_ENV="production"

# ==============================================================
# SECTION 2: Primary Database
# ==============================================================
header "2 / 7  Primary Database  (B2C, Agent, Supplier, Supervision, UltraLux)"
echo "  (Azure MySQL: <server-name>.mysql.database.azure.com)"
echo ""
prompt DB_HOSTNAME "  DB Hostname" "$(load_existing DB_HOSTNAME)"
prompt DB_USERNAME "  DB Username" "$(load_existing DB_USERNAME)"
prompt DB_PASSWORD "  DB Password" "" secret
[[ -z "$DB_PASSWORD" ]] && DB_PASSWORD="$(load_existing DB_PASSWORD)"
prompt DB_DATABASE "  DB Database name" "$(load_existing DB_DATABASE)"

# ==============================================================
# SECTION 3: Secondary Database (Services API)
# ==============================================================
header "3 / 7  Secondary Database  (Services WebServices API)"
echo "  (Used by services/ module — may share the same server)"
echo ""
_db2h="$(load_existing DB2_HOSTNAME)"
[[ -z "$_db2h" ]] && _db2h="${DB_HOSTNAME}"
prompt DB2_HOSTNAME "  DB2 Hostname" "$_db2h"
_db2u="$(load_existing DB2_USERNAME)"
[[ -z "$_db2u" ]] && _db2u="${DB_USERNAME}"
prompt DB2_USERNAME "  DB2 Username" "$_db2u"
prompt DB2_PASSWORD "  DB2 Password (Enter to reuse DB_PASSWORD)" "" secret
if [[ -z "$DB2_PASSWORD" ]]; then
  _ex="$(load_existing DB2_PASSWORD)"
  DB2_PASSWORD="${_ex:-$DB_PASSWORD}"
fi
prompt DB2_DATABASE "  DB2 Database name" "$(load_existing DB2_DATABASE)"
[[ -z "$DB2_DATABASE" ]] && DB2_DATABASE="lar_webservices"

# ==============================================================
# SECTION 4: Payment Gateways
# ==============================================================
header "4 / 7  Payment Gateways"
echo ""
echo -e "  ${BOLD}PayU${RESET}"
prompt PAYU_MERCHANT_KEY  "    Merchant Key"  "$(load_existing PAYU_MERCHANT_KEY)"
prompt PAYU_MERCHANT_SALT "    Merchant Salt" "" secret
[[ -z "$PAYU_MERCHANT_SALT" ]] && PAYU_MERCHANT_SALT="$(load_existing PAYU_MERCHANT_SALT)"
prompt PAYU_MERCHANT_ID   "    Merchant ID"   "$(load_existing PAYU_MERCHANT_ID)"
prompt PAYU_MODE          "    Mode (live | sandbox)" "$(load_existing PAYU_MODE)"
[[ -z "$PAYU_MODE" ]] && PAYU_MODE="live"

echo ""
echo -e "  ${BOLD}PayPal${RESET}"
prompt PAYPAL_CLIENT_ID "    Client ID"     "$(load_existing PAYPAL_CLIENT_ID)"
prompt PAYPAL_SECRET    "    Secret"        "" secret
[[ -z "$PAYPAL_SECRET" ]] && PAYPAL_SECRET="$(load_existing PAYPAL_SECRET)"
prompt PAYPAL_MODE      "    Mode (live | sandbox)" "$(load_existing PAYPAL_MODE)"
[[ -z "$PAYPAL_MODE" ]] && PAYPAL_MODE="live"

# ==============================================================
# SECTION 5: GDS / Supplier API Credentials
# ==============================================================
header "5 / 7  GDS / Supplier API Credentials"
echo ""
echo -e "  ${BOLD}TBO (Flights)${RESET}"
prompt TBO_USERNAME "    Username" "$(load_existing TBO_USERNAME)"
prompt TBO_PASSWORD "    Password" "" secret
[[ -z "$TBO_PASSWORD" ]] && TBO_PASSWORD="$(load_existing TBO_PASSWORD)"
prompt TBO_API_KEY  "    API Key"  "$(load_existing TBO_API_KEY)"

echo ""
echo -e "  ${BOLD}PROVAB (Hotels)${RESET}"
prompt PROVAB_USERNAME "    Username" "$(load_existing PROVAB_USERNAME)"
prompt PROVAB_API_KEY  "    API Key"  "$(load_existing PROVAB_API_KEY)"

# ==============================================================
# SECTION 6: Email / SMTP
# ==============================================================
header "6 / 7  Email / SMTP"
echo ""
prompt SMTP_HOST     "  SMTP Host"       "$(load_existing SMTP_HOST)"
[[ -z "$SMTP_HOST" ]] && SMTP_HOST="smtp.example.com"
prompt SMTP_PORT     "  SMTP Port"       "$(load_existing SMTP_PORT)"
[[ -z "$SMTP_PORT" ]] && SMTP_PORT="587"
prompt SMTP_USER     "  SMTP Username"   "$(load_existing SMTP_USER)"
prompt SMTP_PASSWORD "  SMTP Password"   "" secret
[[ -z "$SMTP_PASSWORD" ]] && SMTP_PASSWORD="$(load_existing SMTP_PASSWORD)"

# ==============================================================
# SECTION 7: Application Security Keys
# ==============================================================
header "7 / 7  Application Security Keys"
echo ""
echo "  Auto-generating random 32-character keys (press Enter to accept)..."
echo "  You can paste your own key if you need to match an existing install."
echo ""

_existing_session="$(load_existing SESSION_KEY)"
if [[ -z "$_existing_session" ]]; then
  _existing_session="$(generate_key)"
fi
prompt SESSION_KEY "  Session Key (32 chars)" "$_existing_session"

_existing_enc="$(load_existing ENCRYPTION_KEY)"
if [[ -z "$_existing_enc" ]]; then
  _existing_enc="$(generate_key)"
fi
prompt ENCRYPTION_KEY "  Encryption Key (32 chars)" "$_existing_enc"

# ==============================================================
# Write .env file
# ==============================================================
echo ""
info "Writing ${ENV_OUTPUT} ..."

cat > "${ENV_OUTPUT}" << EOF
# =============================================================================
# LAR System — Environment Configuration
# Generated by scripts/setup-env.sh on $(date -u '+%Y-%m-%d %H:%M:%S UTC')
#
# NEVER commit this file to version control.
# Use scripts/azure-appsettings-sync.sh to push these settings to Azure.
# =============================================================================

# Application Environment
APP_ENV=${APP_ENV}

# Primary Database
DB_HOSTNAME=${DB_HOSTNAME}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_DATABASE=${DB_DATABASE}

# Secondary Database (Services API)
DB2_HOSTNAME=${DB2_HOSTNAME}
DB2_USERNAME=${DB2_USERNAME}
DB2_PASSWORD=${DB2_PASSWORD}
DB2_DATABASE=${DB2_DATABASE}

# Payment Gateway — PayU
PAYU_MERCHANT_KEY=${PAYU_MERCHANT_KEY}
PAYU_MERCHANT_SALT=${PAYU_MERCHANT_SALT}
PAYU_MERCHANT_ID=${PAYU_MERCHANT_ID}
PAYU_MODE=${PAYU_MODE}

# Payment Gateway — PayPal
PAYPAL_CLIENT_ID=${PAYPAL_CLIENT_ID}
PAYPAL_SECRET=${PAYPAL_SECRET}
PAYPAL_MODE=${PAYPAL_MODE}

# TBO Flights API
TBO_USERNAME=${TBO_USERNAME}
TBO_PASSWORD=${TBO_PASSWORD}
TBO_API_KEY=${TBO_API_KEY}

# PROVAB Hotels API
PROVAB_USERNAME=${PROVAB_USERNAME}
PROVAB_API_KEY=${PROVAB_API_KEY}

# Email / SMTP
SMTP_HOST=${SMTP_HOST}
SMTP_PORT=${SMTP_PORT}
SMTP_USER=${SMTP_USER}
SMTP_PASSWORD=${SMTP_PASSWORD}

# Application Security
SESSION_KEY=${SESSION_KEY}
ENCRYPTION_KEY=${ENCRYPTION_KEY}
EOF

# Restrict file permissions — credentials should not be world-readable
chmod 600 "${ENV_OUTPUT}"

success ".env file written to: ${ENV_OUTPUT}"
echo ""
echo "  Next steps:"
echo "    1. Review the file: ${BOLD}cat ${ENV_OUTPUT}${RESET}"
echo "    2. Push to Azure:   ${BOLD}./scripts/azure-appsettings-sync.sh${RESET}"
echo ""
