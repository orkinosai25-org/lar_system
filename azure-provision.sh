#!/bin/bash
set -euo pipefail

# ============================================================
# LAR System — Azure Quick-Start Provisioning Script
#
# Usage:
#   chmod +x azure-provision.sh
#   ./azure-provision.sh                                 # prompts for DB password
#   DB_ADMIN_PASSWORD='mypassword' ./azure-provision.sh  # non-interactive
#   ./azure-provision.sh --env-file .env                 # read all values from .env
#
# IMPORTANT: Run this script from a logged-in Azure CLI session.
#   Local machine: az login
#   Cloud Shell:   already logged in
#
# After provisioning, run scripts/azure-appsettings-sync.sh to push all
# API keys, payment credentials, and other secrets to the Web Apps.
# ============================================================

# --- CONFIGURATION -------------------------------------------
RESOURCE_GROUP="rg-lar-system"
LOCATION="southafricanorth"     # Closest region to Africa; alternatives: westeurope, eastus
APP_SERVICE_PLAN="asp-lar-system"
DB_SERVER_NAME="lar-mysql-server"           # Must be globally unique across all Azure customers
DB_ADMIN_USER="${DB_ADMIN_USER:-laradmin}"
DB_ADMIN_PASSWORD="${DB_ADMIN_PASSWORD:-}"  # Set via env var, --env-file, or interactive prompt
# -------------------------------------------------------------

# ---- Parse --env-file argument ------------------------------
ENV_FILE_ARG=""
for arg in "$@"; do
  if [[ "$arg" == "--env-file" ]]; then
    ENV_FILE_ARG="NEXT"
  elif [[ "$ENV_FILE_ARG" == "NEXT" ]]; then
    ENV_FILE_ARG="$arg"
  fi
done

if [[ -n "$ENV_FILE_ARG" && "$ENV_FILE_ARG" != "NEXT" && -f "$ENV_FILE_ARG" ]]; then
  echo "==> Loading configuration from ${ENV_FILE_ARG} ..."
  while IFS= read -r line || [[ -n "$line" ]]; do
    [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue
    [[ "$line" != *=* ]] && continue
    key="${line%%=*}"
    value="${line#*=}"
    # Strip only matching outer quotes (single or double)
    if [[ "$value" =~ ^\'(.*)\'$ ]]; then
      value="${BASH_REMATCH[1]}"
    elif [[ "$value" =~ ^\"(.*)\"$ ]]; then
      value="${BASH_REMATCH[1]}"
    fi
    export "$key"="$value"
  done < "$ENV_FILE_ARG"
  # DB_PASSWORD in .env is the same credential as DB_ADMIN_PASSWORD for Azure MySQL.
  # The .env file uses DB_PASSWORD (app-facing name); azure-provision.sh uses
  # DB_ADMIN_PASSWORD (the MySQL admin account name) — they are the same value.
  DB_ADMIN_PASSWORD="${DB_PASSWORD:-${DB_ADMIN_PASSWORD:-}}"
  [[ -n "${DB_USERNAME:-}" ]] && DB_ADMIN_USER="${DB_USERNAME}"
fi

# ---- Prompt for password if still unset ---------------------
if [[ -z "$DB_ADMIN_PASSWORD" ]]; then
  echo -n "==> Enter DB admin password (min 8 chars, upper+lower+number+symbol): "
  read -rs DB_ADMIN_PASSWORD
  echo
fi

if [[ -z "$DB_ADMIN_PASSWORD" ]]; then
  echo "ERROR: DB_ADMIN_PASSWORD is required. Set it via environment variable, --env-file, or interactive prompt."
  exit 1
fi

echo "==> Updating MySQL Flexible Server CLI extension..."
# In Azure Cloud Shell the CLI binary cannot be self-updated ('az upgrade' will fail).
# Update only the extension to avoid InvalidApiVersionParameter errors.
# On a local machine you can instead run: az upgrade --yes --all
az extension update --name rdbms-connect \
  || az extension add --upgrade --name rdbms-connect \
  || echo "NOTE: Extension update skipped — retry if you see InvalidApiVersionParameter errors."

echo "==> Creating resource group..."
az group create --name "$RESOURCE_GROUP" --location "$LOCATION"

echo "==> Creating MySQL Flexible Server..."
az mysql flexible-server create \
  --resource-group "$RESOURCE_GROUP" \
  --name "$DB_SERVER_NAME" \
  --location "$LOCATION" \
  --admin-user "$DB_ADMIN_USER" \
  --admin-password "$DB_ADMIN_PASSWORD" \
  --sku-name Standard_D2ds_v4 \
  --tier GeneralPurpose \
  --storage-size 20 \
  --version 8.0.21 \
  --public-access 0.0.0.0
# NOTE: If "The requested VM size is not available in the current region", replace the
# --sku-name and --tier lines above with: --sku-name Standard_B1ms --tier Burstable

echo "==> Creating databases..."
for DB_NAME in lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux; do
  az mysql flexible-server db create \
    --resource-group "$RESOURCE_GROUP" \
    --server-name "$DB_SERVER_NAME" \
    --database-name "$DB_NAME" \
    --charset utf8mb4 \
    --collation utf8mb4_unicode_ci
done

echo "==> Creating App Service Plan..."
az appservice plan create \
  --resource-group "$RESOURCE_GROUP" \
  --name "$APP_SERVICE_PLAN" \
  --location "$LOCATION" \
  --is-linux \
  --sku P1v3

echo "==> Creating Web Apps..."
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp create \
    --resource-group "$RESOURCE_GROUP" \
    --plan "$APP_SERVICE_PLAN" \
    --name "$APP_NAME" \
    --runtime "PHP|8.2"
done

echo "==> Enabling diagnostic logging on all Web Apps..."
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp log config \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --application-logging filesystem \
    --level verbose \
    --web-server-logging filesystem \
    --detailed-error-messages true \
    --failed-request-tracing true
done

echo "==> Creating service principal for GitHub Actions..."
SUBSCRIPTION_ID=$(az account show --query id -o tsv)
az ad sp create-for-rbac \
  --name "lar-github-actions" \
  --role contributor \
  --scopes "/subscriptions/${SUBSCRIPTION_ID}/resourceGroups/${RESOURCE_GROUP}" \
  --json-auth > /tmp/azure-credentials.json
echo "  Service principal JSON saved to /tmp/azure-credentials.json"
echo "  Add its contents as the AZURE_CREDENTIALS GitHub Secret"

echo "==> Configuring App Settings (DB, environment, and base security settings)..."
echo "    NOTE: To push API keys, payment credentials, and SMTP settings,"
echo "    run: scripts/azure-appsettings-sync.sh --env-file .env"
DB_HOST="${DB_SERVER_NAME}.mysql.database.azure.com"
APPS_AND_DBS=(
  "lar-b2c:lar_b2c"
  "lar-agent:lar_agent"
  "lar-supplier:lar_supplier"
  "lar-supervision:lar_supervision"
  "lar-services:lar_webservices"
)
for ENTRY in "${APPS_AND_DBS[@]}"; do
  APP_NAME="${ENTRY%%:*}"
  DB_NAME="${ENTRY##*:}"
  az webapp config appsettings set \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --settings \
      DB_HOSTNAME="$DB_HOST" \
      DB_USERNAME="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
      DB_PASSWORD="$DB_ADMIN_PASSWORD" \
      DB_DATABASE="$DB_NAME" \
      APP_ENV="production" \
      ENVIRONMENT="production" \
      WEBSITE_RUN_FROM_PACKAGE="1" \
    --output none
  echo "  Settings applied: ${APP_NAME}"
done

echo "==> Downloading publish profiles..."
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp deployment list-publishing-profiles \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --xml \
    --output tsv > /tmp/publishprofile-${APP_NAME}.xml
  echo "  Saved /tmp/publishprofile-${APP_NAME}.xml — add to GitHub Secrets (see AZURE_DEPLOYMENT.md Step 5 / Section 7)"
done

echo ""
echo "==> DONE. Next steps:"
echo "  1. Add AZURE_CREDENTIALS from /tmp/azure-credentials.json as a GitHub Secret"
echo "  2. Add the publish profile XMLs as GitHub Secrets (see AZURE_DEPLOYMENT.md Step 5)"
echo "  3. Add the app name and resource group as GitHub Variables (see AZURE_DEPLOYMENT.md Step 5)"
echo "  4. Run: scripts/setup-env.sh   → fill in API keys, payment, SMTP settings"
echo "  5. Run: scripts/azure-appsettings-sync.sh   → push all settings to Azure"
echo "  6. Import your database schema (see AZURE_DEPLOYMENT.md Step 7)"
echo "  7. Push to main or manually trigger the GitHub Actions workflow"
