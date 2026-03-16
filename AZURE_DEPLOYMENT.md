# Azure Deployment Guide — LAR System (Audit Test Deployment)

This document provides complete, step-by-step instructions for creating the Azure resources
and setting up automated GitHub Actions deployment for the Luxury Africa Resorts (LAR) system.

> **Audit Test Deployment:** This guide deploys the code **as-is** to surface and capture all
> runtime errors, database connection failures, and PHP warnings for audit analysis. No code
> changes are applied. All errors are captured in downloadable log artifacts.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Prerequisites](#2-prerequisites)
3. [Step 1 — Create Azure Resource Group](#3-step-1--create-azure-resource-group)
4. [Step 2 — Create Azure Database for MySQL](#4-step-2--create-azure-database-for-mysql)
5. [Step 3 — Create Azure Web Apps](#5-step-3--create-azure-web-apps)
6. [Step 4 — Configure App Settings (Environment Variables)](#6-step-4--configure-app-settings-environment-variables)
7. [Step 5 — Configure GitHub Secrets & Variables](#7-step-5--configure-github-secrets--variables)
8. [Step 6 — Enable GitHub Actions Deployment](#8-step-6--enable-github-actions-deployment)
9. [Step 7 — Initial Database Setup](#9-step-7--initial-database-setup)
10. [Step 8 — Verify Deployment](#10-step-8--verify-deployment)
11. [Troubleshooting](#11-troubleshooting)
12. [Azure CLI Quick-Start Script](#12-azure-cli-quick-start-script)

---

## 1. Architecture Overview

The LAR system consists of five separate PHP/CodeIgniter modules, each deployed as its own
Azure Web App pointing at a shared Azure Database for MySQL Flexible Server.

```
GitHub Repository
       │
       │  push to main
       ▼
GitHub Actions Workflow
       │
       ├──► Azure Web App: lar-b2c          (Consumer Website)
       ├──► Azure Web App: lar-agent        (B2B Agent Panel)
       ├──► Azure Web App: lar-supplier     (Supplier Portal)
       ├──► Azure Web App: lar-supervision  (Back-office / Supervision)
       └──► Azure Web App: lar-services     (Internal Web Services API)
                                │
                                ▼
                   Azure Database for MySQL
                   Flexible Server (shared)
```

**Azure services used:**

| Service | Tier (recommended) | Purpose |
|---|---|---|
| Azure App Service Plan | P1v3 (Linux, PHP 8.2) | Hosts all five web apps |
| Azure Database for MySQL Flexible Server | General Purpose, 2 vCores | Primary database |
| Azure Key Vault (optional) | Standard | Secure secret storage |

---

## 2. Prerequisites

- An **Azure subscription** (free trial or paid)
- **Azure CLI** installed locally, or use **Azure Cloud Shell** at https://shell.azure.com
- A **GitHub account** with access to this repository
- Basic familiarity with PHP/MySQL

Install or update the Azure CLI:

```bash
# macOS
brew install azure-cli

# Windows (PowerShell)
winget install Microsoft.AzureCLI

# Linux
curl -sL https://aka.ms/InstallAzureCLIDeb | sudo bash
```

Log in:

```bash
az login
```

---

## 3. Step 1 — Create Azure Resource Group

A resource group is a logical container for all LAR Azure resources.

```bash
# Set variables — change these to match your preferences
RESOURCE_GROUP="rg-lar-system"
LOCATION="southafricanorth"   # Closest Azure region to Africa
                               # Alternatives: westeurope, eastus

az group create \
  --name "$RESOURCE_GROUP" \
  --location "$LOCATION"
```

---

## 4. Step 2 — Create Azure Database for MySQL

Use **Azure Database for MySQL Flexible Server** — it supports SSL, VNet integration,
and is fully managed (automatic backups, patching).

```bash
# Re-declare the variables from Step 1 in case you are running this in a new shell session.
# (Azure Cloud Shell sessions are ephemeral — variables do not persist across sessions.)
RESOURCE_GROUP="rg-lar-system"
LOCATION="southafricanorth"

DB_SERVER_NAME="lar-mysql-server"     # Must be globally unique
DB_ADMIN_USER="laradmin"
DB_ADMIN_PASSWORD='ChangeMe_S3cure!'  # Replace with your password (min 8 chars, upper+lower+number+symbol)
# NOTE: Use single quotes around the password to prevent bash from interpreting special
# characters such as ! (which triggers history expansion in interactive shells).

# Ensure the MySQL Flexible Server CLI extension is up to date to avoid api-version errors.
# In Azure Cloud Shell the CLI binary cannot be self-updated, so update only the extension:
az extension update --name rdbms-connect \
  || az extension add --upgrade --name rdbms-connect \
  || true
# If running locally (not Cloud Shell) you can also run: az upgrade --yes --all

# Create the Flexible Server (General Purpose, 2 vCores, 20 GB storage)
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
  --public-access 0.0.0.0   # Opens firewall to Azure services; restrict later

# NOTE: If you see "The requested VM size is not available in the current region", use the
# Burstable tier instead (also suitable for audit/test deployments):
#   --sku-name Standard_B1ms \
#   --tier Burstable \

# Create the databases (one per module)
for DB_NAME in lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux; do
  az mysql flexible-server db create \
    --resource-group "$RESOURCE_GROUP" \
    --server-name "$DB_SERVER_NAME" \
    --database-name "$DB_NAME" \
    --charset utf8mb4 \
    --collation utf8mb4_unicode_ci
done
```

**Note the connection hostname** — it will be:
```
<DB_SERVER_NAME>.mysql.database.azure.com
```

### Firewall rules

Allow connections from Azure services (required for App Service):

```bash
az mysql flexible-server firewall-rule create \
  --resource-group "$RESOURCE_GROUP" \
  --name "$DB_SERVER_NAME" \
  --rule-name "AllowAzureServices" \
  --start-ip-address 0.0.0.0 \
  --end-ip-address 0.0.0.0
```

Allow your local IP for initial database schema import:

```bash
MY_IP=$(curl -s https://api.ipify.org)
az mysql flexible-server firewall-rule create \
  --resource-group "$RESOURCE_GROUP" \
  --name "$DB_SERVER_NAME" \
  --rule-name "AllowMyIP" \
  --start-ip-address "$MY_IP" \
  --end-ip-address "$MY_IP"
```

### SSL Certificate

Azure MySQL Flexible Server requires SSL. The `DigiCertGlobalRootCA.crt.pem` file is
**already included in this repository root** — no download is needed. It will be deployed
automatically with the application by GitHub Actions.

If you ever need to refresh the certificate manually (e.g., after a CA rotation), run one
of the following commands from the repository root **on a local machine** (Azure Cloud Shell
can block connections to external certificate authorities):

```bash
# Option A — curl (recommended on a local machine)
curl -o DigiCertGlobalRootCA.crt.pem \
  https://dl.cacerts.digicert.com/DigiCertGlobalRootCA.crt.pem

# Option B — wget fallback
wget -O DigiCertGlobalRootCA.crt.pem \
  https://dl.cacerts.digicert.com/DigiCertGlobalRootCA.crt.pem
```

> **Azure Cloud Shell note:** If you see `curl: (35) TLS connect error` when running the
> download command above, this is a known TLS handshake restriction in some Cloud Shell
> sessions. Use the pre-bundled certificate already present in the repository — it is the
> standard DigiCert Global Root CA and is identical to the file hosted on DigiCert's CDN.

---

## 5. Step 3 — Create Azure Web Apps

Create a single **App Service Plan** and five **Web Apps**.

```bash
# Re-declare all variables if running in a new shell session.
# (Azure Cloud Shell sessions are ephemeral — variables do not persist across sessions.)
RESOURCE_GROUP="rg-lar-system"
LOCATION="southafricanorth"
APP_SERVICE_PLAN="asp-lar-system"

# Create the App Service Plan (Linux, PHP 8.2, P1v3 SKU)
az appservice plan create \
  --resource-group "$RESOURCE_GROUP" \
  --name "$APP_SERVICE_PLAN" \
  --location "$LOCATION" \
  --is-linux \
  --sku P1v3

# Create each Web App
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp create \
    --resource-group "$RESOURCE_GROUP" \
    --plan "$APP_SERVICE_PLAN" \
    --name "$APP_NAME" \
    --runtime "PHP|8.2"
done
```

**Your app URLs will be:**

| Module | URL |
|---|---|
| B2C Consumer Website | https://lar-b2c.azurewebsites.net |
| Agent Panel | https://lar-agent.azurewebsites.net |
| Supplier Portal | https://lar-supplier.azurewebsites.net |
| Supervision | https://lar-supervision.azurewebsites.net |
| Web Services API | https://lar-services.azurewebsites.net |

---

## 6. Step 4 — Configure App Settings (Environment Variables)

Each Web App needs database connection settings configured as **Application Settings**
(these become PHP `getenv()` environment variables — no secrets in code).

Replace the placeholder values with your actual Azure MySQL server details.

```bash
# Re-declare variables if running in a new shell session.
# (Azure Cloud Shell sessions are ephemeral — variables do not persist across sessions.)
RESOURCE_GROUP="rg-lar-system"
DB_SERVER_NAME="lar-mysql-server"
DB_ADMIN_USER="laradmin"
DB_ADMIN_PASSWORD='YOUR_SECURE_PASSWORD_HERE'  # same password as Step 2

DB_HOST="${DB_SERVER_NAME}.mysql.database.azure.com"

# B2C
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-b2c" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
    DB_PASSWORD="$DB_ADMIN_PASSWORD" \
    DB_DATABASE="lar_b2c" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Agent
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-agent" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
    DB_PASSWORD="$DB_ADMIN_PASSWORD" \
    DB_DATABASE="lar_agent" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Supplier
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-supplier" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
    DB_PASSWORD="$DB_ADMIN_PASSWORD" \
    DB_DATABASE="lar_supplier" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Supervision
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-supervision" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
    DB_PASSWORD="$DB_ADMIN_PASSWORD" \
    DB_DATABASE="lar_supervision" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Services API
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-services" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
    DB_PASSWORD="$DB_ADMIN_PASSWORD" \
    DB_DATABASE="lar_webservices" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"
```

### PHP version and extensions

Because the App Service Plan was created with `--is-linux`, the PHP version is already
embedded in the `linuxFxVersion` property set at creation time via `--runtime "PHP|8.2"`.
**No additional command is required** — do **not** run `az webapp config set --php-version`
on a Linux web app; it applies only to Windows-based plans and will return
`Operation returned an invalid status 'Bad Request'` on Linux.

To confirm the runtime version is correct on any web app:

```bash
az webapp config show \
  --resource-group "$RESOURCE_GROUP" \
  --name "lar-b2c" \
  --query linuxFxVersion \
  --output tsv
# Expected output: PHP|8.2
```

The following extensions are available by default on Azure App Service PHP 8.2 (Linux):
`mysqli`, `pdo_mysql`, `mbstring`, `gd`, `curl`, `zip`, `xml`, `intl`

---

## 7. Step 5 — Configure GitHub Secrets & Variables

### Get Publish Profiles

Download the publish profile for each Web App — this is the credential GitHub Actions uses
to authenticate with Azure:

```bash
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp deployment list-publishing-profiles \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --xml \
    --output tsv > /tmp/publishprofile-${APP_NAME}.xml
  echo "Saved /tmp/publishprofile-${APP_NAME}.xml"
done
```

### Create Service Principal for Log Collection

The `collect-logs` job needs Azure management API access. Create a service principal
scoped to the resource group:

```bash
RESOURCE_GROUP="rg-lar-system"
SUBSCRIPTION_ID=$(az account show --query id -o tsv)

az ad sp create-for-rbac \
  --name "lar-github-actions" \
  --role contributor \
  --scopes "/subscriptions/${SUBSCRIPTION_ID}/resourceGroups/${RESOURCE_GROUP}" \
  --json-auth > /tmp/azure-credentials.json
echo "Saved /tmp/azure-credentials.json"
```

Copy the entire JSON content of `/tmp/azure-credentials.json` — you will paste it as
the `AZURE_CREDENTIALS` secret.

### Add GitHub Secrets

Go to your repository on GitHub:
**Settings → Secrets and variables → Actions → Secrets tab → New repository secret**

> **⚠️ SECRETS vs VARIABLES — Important distinction:**
> GitHub Actions has two separate stores:
> - **Secrets** — for sensitive credentials (passwords, tokens, keys). Accessed in workflows as `${{ secrets.NAME }}`. Values are masked in logs.
> - **Variables** — for non-sensitive configuration data (names, IDs, resource names). Accessed in workflows as `${{ vars.NAME }}`. Values are NOT masked.
>
> `AZURE_RESOURCE_GROUP` and `AZURE_WEBAPP_NAME_*` **must be added as Variables, NOT Secrets**.
> If you accidentally add them as secrets, the workflow will silently receive empty values
> because `${{ vars.NAME }}` only reads from the Variables store.

Add the following **Secrets** (paste the full XML content from each publish profile file):

| Secret Name | Value |
|---|---|
| `AZURE_CREDENTIALS` | Full JSON from `/tmp/azure-credentials.json` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_B2C` | Contents of `publishprofile-lar-b2c.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_AGENT` | Contents of `publishprofile-lar-agent.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SUPPLIER` | Contents of `publishprofile-lar-supplier.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SUPERVISION` | Contents of `publishprofile-lar-supervision.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SERVICES` | Contents of `publishprofile-lar-services.xml` |

### Add GitHub Variables

> **⚠️ These must be Variables — NOT Secrets.**
> In the GitHub UI the Secrets and Variables pages look similar. Make sure you are on the
> **Variables tab**, not the Secrets tab. Variables are accessed in the workflow via
> `${{ vars.NAME }}`; secrets are accessed via `${{ secrets.NAME }}`.

**Option A — Repository-level variables (recommended)**

Go to: **Settings → Secrets and variables → Actions → Variables tab → New repository variable**

| Variable Name | Value |
|---|---|
| `AZURE_RESOURCE_GROUP` | `rg-lar-system` |
| `AZURE_WEBAPP_NAME_B2C` | `lar-b2c` |
| `AZURE_WEBAPP_NAME_AGENT` | `lar-agent` |
| `AZURE_WEBAPP_NAME_SUPPLIER` | `lar-supplier` |
| `AZURE_WEBAPP_NAME_SUPERVISION` | `lar-supervision` |
| `AZURE_WEBAPP_NAME_SERVICES` | `lar-services` |

**Option B — Environment-level variables (if using the `production` environment)**

If you are using a GitHub environment named `production` (the workflow targets
`environment: name: production`), you can set these at the environment level instead.

Go to: **Settings → Environments → production → Environment variables → Add variable**

Add the same six variables listed in the table above. Environment variables take precedence
over repository variables and are also read via `${{ vars.NAME }}` in workflow jobs that
reference the `production` environment.

> **⚠️ Common mistake:** GitHub Environments have both **"Environment secrets"** and
> **"Environment variables"** sections. `AZURE_RESOURCE_GROUP` and `AZURE_WEBAPP_NAME_*`
> must go under **"Environment variables"** — **not** under "Environment secrets".
> Adding them as environment secrets will cause the workflow to silently fail with empty
> app-name values.

---

## 8. Step 6 — Enable GitHub Actions Deployment

The workflow file `.github/workflows/azure-deploy.yml` is already included in this
repository and will run automatically when you push to the `main` branch.

**Trigger deployment manually:**

1. Go to GitHub → **Actions** tab
2. Select **"Deploy to Azure Web App"**
3. Click **"Run workflow"** → **"Run workflow"**

**What the workflow does:**

1. **Build job** — Checks out the code, sets up PHP 8.2, validates PHP syntax for all modules
2. **Deploy jobs** — Deploys each module to its respective Azure Web App in parallel

---

## 9. Step 7 — Initial Database Setup

After creating the databases, import your SQL schema. Use MySQL Workbench or the CLI:

```bash
# Import schema for each database
# (DB_SERVER_NAME, DB_ADMIN_USER and DB_ADMIN_PASSWORD were set in Step 2)
mysql \
  --host="${DB_SERVER_NAME}.mysql.database.azure.com" \
  --user="${DB_ADMIN_USER}@${DB_SERVER_NAME}" \
  --password="${DB_ADMIN_PASSWORD}" \
  --ssl-ca=DigiCertGlobalRootCA.crt.pem \
  --ssl-mode=REQUIRED \
  lar_b2c < /path/to/your/lar_b2c_schema.sql
```

Repeat for each database: `lar_agent`, `lar_supplier`, `lar_supervision`,
`lar_webservices`, `lar_ultralux`.

---

## 10. Step 8 — Verify Deployment

After a successful GitHub Actions run:

1. Open https://lar-b2c.azurewebsites.net — the B2C consumer website
2. Open https://lar-agent.azurewebsites.net — the Agent panel
3. Check the **Log stream** in Azure Portal for each Web App:
   - Azure Portal → App Services → `lar-b2c` → **Log stream**

### Health check

```bash
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://${APP_NAME}.azurewebsites.net")
  echo "${APP_NAME}: HTTP ${STATUS}"
done
```

---

## 11. Troubleshooting

### 502 Bad Gateway / App not starting

- Check **Log stream** in Azure Portal for PHP errors
- Verify `ENVIRONMENT` app setting is set to `production`
- Confirm the publish profile secret in GitHub is correct (not expired)

### Database connection errors

- Confirm `DB_HOSTNAME`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE` app settings
- Verify the MySQL firewall rule allows Azure services (IP `0.0.0.0` to `0.0.0.0`)
- For MySQL 8.0 on Azure, the username format must include the server name:
  `laradmin@lar-mysql-server`
- Ensure SSL is not required in the connection. The `DigiCertGlobalRootCA.crt.pem`
  file is bundled in this repository and will be deployed automatically; however, if you
  need to disable SSL temporarily for initial testing:
  ```bash
  az mysql flexible-server update \
    --resource-group "$RESOURCE_GROUP" \
    --name "$DB_SERVER_NAME" \
    --require-secure-transport OFF
  ```

### `curl: (35) TLS connect error` when downloading the SSL certificate

Azure Cloud Shell occasionally blocks outbound TLS connections to external certificate
authorities (e.g. `dl.cacerts.digicert.com`). You do **not** need to download the
certificate — `DigiCertGlobalRootCA.crt.pem` is already bundled in this repository and
will be deployed alongside the application code automatically.

If you need to refresh it in the future, run the download command from a **local
machine** (outside Cloud Shell) where outbound TLS is unrestricted:

```bash
curl -o DigiCertGlobalRootCA.crt.pem \
  https://dl.cacerts.digicert.com/DigiCertGlobalRootCA.crt.pem
```

### CodeIgniter routing issues (404 on all pages except home)

- Confirm `web.config` is present at the repository root (it is included in this repo)
- On Linux App Service, the `.htaccess` file should work — verify `mod_rewrite` is enabled:
  ```bash
  az webapp config set \
    --resource-group "$RESOURCE_GROUP" \
    --name "lar-b2c" \
    --generic-configurations '{"linuxFxVersion": "PHP|8.2"}'
  ```

### `Operation returned an invalid status 'Bad Request'` from `az webapp config set`

If you ran the `--php-version` flag on a Linux web app:

```bash
# ❌ WRONG — --php-version is for Windows App Service plans only
az webapp config set --resource-group "$RESOURCE_GROUP" --name "$APP_NAME" --php-version "8.2"
```

you will get `Operation returned an invalid status 'Bad Request'` for every app. This flag
is not supported on Linux-based App Service plans.

For Linux web apps the PHP version is baked in at creation time via `--runtime "PHP|8.2"`.
No additional command is needed. To verify the version is correct, run:

```bash
az webapp config show \
  --resource-group "$RESOURCE_GROUP" \
  --name "lar-b2c" \
  --query linuxFxVersion \
  --output tsv
# Expected: PHP|8.2
```

If the value is wrong, correct it with:

```bash
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp config set \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --linux-fx-version "PHP|8.2"
done
```

### `InvalidApiVersionParameter` error when running Azure CLI commands

If you see an error like:
```
(InvalidApiVersionParameter) The api-version '2025-06-01-preview' is invalid.
```
this means the `rdbms` CLI extension installed in your session is using a preview API
version that is not yet available on your Azure subscription.

**In Azure Cloud Shell** the CLI binary cannot be self-updated (`az upgrade` will report
"Not able to upgrade automatically"), but extensions can be updated independently:

```bash
az extension update --name rdbms-connect \
  || az extension add --upgrade --name rdbms-connect \
  || true
```

**On a local machine** you can upgrade the full CLI and all extensions at once:

```bash
az upgrade --yes --all
```

Then retry the failed command. This issue commonly affects `az mysql flexible-server`
commands in Azure Cloud Shell when the bundled `rdbms` extension uses a preview API
that is not yet promoted to the stable API surface.

> **Also check that shell variables are set.** If you see errors about an empty resource
> group name (e.g. `resourcegroups/?api-version=…`) it means the `$RESOURCE_GROUP`
> variable was not set in your current session. Azure Cloud Shell sessions are ephemeral —
> variables do not persist. Re-declare all variables at the top of each command block
> before running it.

### GitHub Actions: "No credentials found" on deploy jobs

If the deploy jobs fail with:
```
Deployment Failed, Error: No credentials found. Add an Azure login action before this action.
```
ensure the `AZURE_CREDENTIALS` secret is set in your repository
(**Settings → Secrets and variables → Actions → Secrets**). This secret is required for
all deploy jobs as well as the log-collection job. Generate it with:

```bash
RESOURCE_GROUP="rg-lar-system"
SUBSCRIPTION_ID=$(az account show --query id -o tsv)
az ad sp create-for-rbac \
  --name "lar-github-actions" \
  --role contributor \
  --scopes "/subscriptions/${SUBSCRIPTION_ID}/resourceGroups/${RESOURCE_GROUP}" \
  --json-auth > /tmp/azure-credentials.json
cat /tmp/azure-credentials.json
```

Paste the entire JSON output as the `AZURE_CREDENTIALS` secret. The `--json-auth` flag
produces the camelCase keys (`clientId`, `clientSecret`, `tenantId`, `subscriptionId`)
required by `azure/login@v2`.

### GitHub Actions: "Not all values are present. Ensure 'client-id' and 'tenant-id' are supplied"

This error means the `AZURE_CREDENTIALS` secret is present but not in the expected format.
The `azure/login@v2` action requires a JSON object with camelCase keys:
```json
{
  "clientId": "...",
  "clientSecret": "...",
  "tenantId": "...",
  "subscriptionId": "..."
}
```
Regenerate the secret using `az ad sp create-for-rbac --json-auth` as shown above.

### Deployment not triggering

- Confirm the workflow file is in `.github/workflows/azure-deploy.yml`
- Check that the branch name in the workflow (`main`) matches your default branch
- Review the **Actions** tab on GitHub for error messages

---

## 12. Azure CLI Quick-Start Script

The following script automates Steps 1–5 above. **Review and update all variables
before running.**

```bash
#!/bin/bash
set -euo pipefail

# ============================================================
# LAR System — Azure Quick-Start Provisioning Script
# Update all variables in the CONFIGURATION section below.
# ============================================================

# --- CONFIGURATION -------------------------------------------
RESOURCE_GROUP="rg-lar-system"
LOCATION="southafricanorth"
APP_SERVICE_PLAN="asp-lar-system"
DB_SERVER_NAME="lar-mysql-server"          # Must be globally unique
DB_ADMIN_USER="laradmin"
DB_ADMIN_PASSWORD='ChangeMe_S3cure!'  # Replace with your password — min 8 chars, upper+lower+number+symbol
# NOTE: Use single quotes to prevent bash from interpreting ! as history expansion.
# -------------------------------------------------------------

echo "==> Updating MySQL Flexible Server CLI extension..."
# In Azure Cloud Shell the CLI binary cannot be self-updated; update the extension only.
# On a local machine, replace the two lines below with: az upgrade --yes --all
az extension update --name rdbms-connect \
  || az extension add --upgrade --name rdbms-connect \
  || echo "NOTE: Extension update skipped — retry the commands below if you see InvalidApiVersionParameter errors."
# Updating the extension prevents 'InvalidApiVersionParameter' errors caused by the rdbms
# extension using a preview API version not yet available in the management plane.

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
# NOTE: If "The requested VM size is not available in the current region", replace the two lines
# above with: --sku-name Standard_B1ms --tier Burstable

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

echo "==> Creating service principal for log collection..."
SUBSCRIPTION_ID=$(az account show --query id -o tsv)
az ad sp create-for-rbac \
  --name "lar-github-actions" \
  --role contributor \
  --scopes "/subscriptions/${SUBSCRIPTION_ID}/resourceGroups/${RESOURCE_GROUP}" \
  --json-auth > /tmp/azure-credentials.json
echo "  Service principal JSON saved to /tmp/azure-credentials.json"
echo "  Add its contents as the AZURE_CREDENTIALS GitHub Secret"

echo "==> Configuring App Settings..."
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
      ENVIRONMENT="production" \
      WEBSITE_RUN_FROM_PACKAGE="1"
done

echo "==> Downloading publish profiles..."
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp deployment list-publishing-profiles \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --xml \
    --output tsv > /tmp/publishprofile-${APP_NAME}.xml
  echo "  Saved /tmp/publishprofile-${APP_NAME}.xml — add to GitHub Secrets"
done

echo ""
echo "==> DONE. Next steps:"
echo "  1. Add AZURE_CREDENTIALS from /tmp/azure-credentials.json as a GitHub Secret"
echo "  2. Add the publish profile XMLs as GitHub Secrets (see AZURE_DEPLOYMENT.md Step 5)"
echo "  3. Add the app name and resource group as GitHub Variables (see AZURE_DEPLOYMENT.md Step 5)"
echo "  4. Import your database schema (see AZURE_DEPLOYMENT.md Step 7)"
echo "  5. Push to main or manually trigger the GitHub Actions workflow"
```

Save this script as `/tmp/azure-provision.sh`, update the variables, and run:

```bash
chmod +x /tmp/azure-provision.sh
/tmp/azure-provision.sh
```

The script is also included in this repository as `azure-provision.sh`. In Azure Cloud Shell
you can run it directly after cloning (or downloading) the repository:

```bash
bash azure-provision.sh
```

---

## Summary — GitHub Secrets & Variables Checklist

Before triggering the workflow, confirm ALL of the following are set in GitHub:

> **⚠️ SECRETS vs VARIABLES reminder:**
> - Items listed under **Secrets** must be added via **Settings → Secrets and variables → Actions → Secrets tab** (or under an environment's **"Environment secrets"** section).
> - Items listed under **Variables** must be added via **Settings → Secrets and variables → Actions → Variables tab** (or under an environment's **"Environment variables"** section).
>
> Do **not** mix them up — adding `AZURE_RESOURCE_GROUP` or `AZURE_WEBAPP_NAME_*` as secrets
> will cause the workflow to receive empty values and deployment will fail silently.

**Secrets** (Settings → Secrets and variables → Actions → **Secrets tab**):
- [ ] `AZURE_CREDENTIALS` (service principal JSON — **required for all deploy and log-collection jobs**)
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_B2C`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_AGENT`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SUPPLIER`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SUPERVISION`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SERVICES`

**Variables** (Settings → Secrets and variables → Actions → **Variables tab** — ⚠️ NOT the Secrets tab):
- [ ] `AZURE_RESOURCE_GROUP` = `rg-lar-system`
- [ ] `AZURE_WEBAPP_NAME_B2C` = `lar-b2c`
- [ ] `AZURE_WEBAPP_NAME_AGENT` = `lar-agent`
- [ ] `AZURE_WEBAPP_NAME_SUPPLIER` = `lar-supplier`
- [ ] `AZURE_WEBAPP_NAME_SUPERVISION` = `lar-supervision`
- [ ] `AZURE_WEBAPP_NAME_SERVICES` = `lar-services`

**Azure App Settings on each Web App:**
- [ ] `DB_HOSTNAME`
- [ ] `DB_USERNAME`
- [ ] `DB_PASSWORD`
- [ ] `DB_DATABASE`
- [ ] `ENVIRONMENT` = `production`
