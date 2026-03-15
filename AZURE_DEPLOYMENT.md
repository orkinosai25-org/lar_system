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
6. [Step 4 — Configure GitHub Secrets & Variables](#6-step-4--configure-github-secrets--variables)
7. [Step 5 — Enable GitHub Actions Deployment](#7-step-5--enable-github-actions-deployment)
8. [Step 6 — Initial Database Setup](#8-step-6--initial-database-setup)
9. [Step 7 — Accessing Logs for Audit Analysis](#9-step-7--accessing-logs-for-audit-analysis)
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
       ├──► [Build] PHP syntax check → artifact: php-syntax-report.txt
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
                                │
                                ▼
              [collect-logs job] → artifact: azure-runtime-logs/
```

**Azure services used:**

| Service | Tier (recommended) | Purpose |
|---|---|---|
| Azure App Service Plan | P1v3 (Linux, PHP 8.1) | Hosts all five web apps |
| Azure Database for MySQL Flexible Server | General Purpose, 2 vCores | Primary database |

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
DB_SERVER_NAME="lar-mysql-server"     # Must be globally unique
DB_ADMIN_USER="laradmin"
DB_ADMIN_PASSWORD="<YourStr0ngP@ssword!>"  # Min 8 chars, upper+lower+number+symbol

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
  --version 8.0 \
  --public-access 0.0.0.0   # Opens firewall to Azure services; restrict later

# Create the databases (one per module)
for DB_NAME in lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux; do
  az mysql flexible-server db create \
    --resource-group "$RESOURCE_GROUP" \
    --server-name "$DB_SERVER_NAME" \
    --database-name "$DB_NAME"
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

---

## 5. Step 3 — Create Azure Web Apps

Create a single **App Service Plan** and five **Web Apps**.

```bash
APP_SERVICE_PLAN="asp-lar-system"

# Create the App Service Plan (Linux, PHP 8.1, P1v3 SKU)
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
    --runtime "PHP|8.1"
done
```

### Enable application logging on all Web Apps

This ensures PHP errors and HTTP errors are captured to files that the workflow can download:

```bash
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

## 6. Step 4 — Configure GitHub Secrets & Variables

### 6a. Get Publish Profiles

Download the publish profile for each Web App — this is the credential GitHub Actions uses
to deploy:

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

### 6b. Create Service Principal for Log Collection

The `collect-logs` job needs Azure management API access. Create a service principal
scoped to the resource group:

```bash
SUBSCRIPTION_ID=$(az account show --query id -o tsv)

az ad sp create-for-rbac \
  --name "lar-github-actions" \
  --role contributor \
  --scopes "/subscriptions/${SUBSCRIPTION_ID}/resourceGroups/${RESOURCE_GROUP}" \
  --sdk-auth
```

Copy the entire JSON output — you will paste it as the `AZURE_CREDENTIALS` secret.

### 6c. Add GitHub Secrets

Go to your repository on GitHub:
**Settings → Secrets and variables → Actions → New repository secret**

Add the following **Secrets**:

| Secret Name | Value |
|---|---|
| `AZUREAPPSERVICE_PUBLISHPROFILE_B2C` | Contents of `publishprofile-lar-b2c.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_AGENT` | Contents of `publishprofile-lar-agent.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SUPPLIER` | Contents of `publishprofile-lar-supplier.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SUPERVISION` | Contents of `publishprofile-lar-supervision.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SERVICES` | Contents of `publishprofile-lar-services.xml` |
| `AZURE_CREDENTIALS` | Full JSON from `az ad sp create-for-rbac` above |

### 6d. Add GitHub Variables

Go to: **Settings → Secrets and variables → Actions → Variables tab → New repository variable**

| Variable Name | Value |
|---|---|
| `AZURE_RESOURCE_GROUP` | `rg-lar-system` |
| `AZURE_WEBAPP_NAME_B2C` | `lar-b2c` |
| `AZURE_WEBAPP_NAME_AGENT` | `lar-agent` |
| `AZURE_WEBAPP_NAME_SUPPLIER` | `lar-supplier` |
| `AZURE_WEBAPP_NAME_SUPERVISION` | `lar-supervision` |
| `AZURE_WEBAPP_NAME_SERVICES` | `lar-services` |

---

## 7. Step 5 — Enable GitHub Actions Deployment

The workflow file `.github/workflows/azure-deploy.yml` is already included in this
repository and will run automatically when you push to the `main` branch.

**Trigger deployment manually:**

1. Go to GitHub → **Actions** tab
2. Select **"Deploy to Azure Web App"**
3. Click **"Run workflow"** → **"Run workflow"**

**What the workflow does:**

| Job | What it does |
|---|---|
| **Build** | Sets up PHP 8.1, runs syntax checks across all modules — errors go into a downloadable `php-syntax-report.txt` artifact |
| **deploy-b2c / agent / supplier / supervision / services** | Deploys each module to its Azure Web App (runs in parallel) |
| **collect-logs** | Waits 60 s for apps to start, enables verbose logging, downloads all PHP error logs, HTTP logs, and failed-request traces as a `azure-runtime-logs` artifact |

---

## 8. Step 6 — Initial Database Setup

After creating the databases, import your SQL schema. Use MySQL Workbench or the CLI:

```bash
# Import schema for each database
mysql \
  --host="<DB_SERVER_NAME>.mysql.database.azure.com" \
  --user="laradmin@<DB_SERVER_NAME>" \
  --****** \
  --ssl-ca=DigiCertGlobalRootCA.crt.pem \
  --ssl-mode=REQUIRED \
  lar_b2c < /path/to/your/lar_b2c_schema.sql
```

Repeat for each database: `lar_agent`, `lar_supplier`, `lar_supervision`,
`lar_webservices`, `lar_ultralux`.

---

## 9. Step 7 — Accessing Logs for Audit Analysis

After the workflow completes, two artifacts are available for download from GitHub Actions:

### Downloading artifacts from GitHub

1. Go to GitHub → **Actions** tab
2. Click the latest **"Deploy to Azure Web App"** workflow run
3. Scroll to the **Artifacts** section at the bottom
4. Download:
   - **`php-syntax-report`** — PHP parse errors and warnings across all modules
   - **`azure-runtime-logs`** — ZIP archives of PHP error logs, HTTP access logs, and
     failed-request traces from every Web App

### What to look for in the logs

| Log file | Location in ZIP | What it shows |
|---|---|---|
| PHP application log | `LogFiles/application/` | PHP errors, warnings, database connection failures |
| HTTP access log | `LogFiles/http/RawLogs/` | HTTP request/response codes (4xx, 5xx errors) |
| Detailed error pages | `LogFiles/DetailedErrors/` | Full HTML error pages with stack traces |
| Failed request traces | `LogFiles/W3SVC*/` | Slow or failed HTTP requests with full trace |

### Streaming logs live from Azure CLI

To watch errors in real time during testing:

```bash
az webapp log tail \
  --resource-group "$RESOURCE_GROUP" \
  --name "lar-b2c"
```

### Downloading logs manually from Azure CLI

```bash
# Download log archive for a single app
az webapp log download \
  --resource-group "$RESOURCE_GROUP" \
  --name "lar-b2c" \
  --log-file /tmp/lar-b2c-logs.zip

unzip -l /tmp/lar-b2c-logs.zip  # list contents
unzip /tmp/lar-b2c-logs.zip -d /tmp/lar-b2c-logs/
```

### Accessing Kudu (advanced log browser)

Each Web App has a Kudu console for file-system access to logs:

```
https://<app-name>.scm.azurewebsites.net/DebugConsole
```

Navigate to `LogFiles/` to browse or download individual log files.

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
- Check the downloaded `azure-runtime-logs` artifact for PHP startup errors
- Confirm the publish profile secret in GitHub is correct (not expired)

### Database connection errors

The application uses hardcoded `localhost` database credentials. On Azure, `localhost`
refers to the App Service container itself (no MySQL there), so database connection errors
are expected and will be visible in the logs. These errors are captured for audit analysis.

To see them:
```bash
unzip /tmp/lar-b2c-logs.zip -d /tmp/lar-b2c-logs/
grep -r "database\|mysql\|connection\|error" /tmp/lar-b2c-logs/LogFiles/ -i
```

### CodeIgniter routing issues (404 on all pages except home)

- On Linux App Service, the `.htaccess` file should work with `mod_rewrite` enabled
- On Windows App Service, the `web.config` file included in this repo handles URL rewriting
- Verify `mod_rewrite` is enabled:
  ```bash
  az webapp config set \
    --resource-group "$RESOURCE_GROUP" \
    --name "lar-b2c" \
    --generic-configurations '{"linuxFxVersion": "PHP|8.1"}'
  ```

### `collect-logs` job fails

- Confirm `AZURE_CREDENTIALS` secret is set (see Step 4b above)
- Confirm `AZURE_RESOURCE_GROUP` variable is set
- The job runs even if deploy jobs fail (`if: always()`) so logs are always collected

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
DB_ADMIN_PASSWORD="<YourStr0ngP@ssword!>"  # Change this!
# -------------------------------------------------------------

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
  --version 8.0 \
  --public-access 0.0.0.0

echo "==> Creating databases..."
for DB_NAME in lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux; do
  az mysql flexible-server db create \
    --resource-group "$RESOURCE_GROUP" \
    --server-name "$DB_SERVER_NAME" \
    --database-name "$DB_NAME"
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
    --runtime "PHP|8.1"
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
  --sdk-auth > /tmp/azure-credentials.json
echo "  Service principal JSON saved to /tmp/azure-credentials.json"
echo "  Add its contents as the AZURE_CREDENTIALS GitHub Secret"

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
echo "  2. Add the publish profile XMLs as GitHub Secrets (see AZURE_DEPLOYMENT.md Step 4c)"
echo "  3. Add the app name and resource group as GitHub Variables (see AZURE_DEPLOYMENT.md Step 4d)"
echo "  4. Import your database schema (see AZURE_DEPLOYMENT.md Step 6)"
echo "  5. Push to main or manually trigger the GitHub Actions workflow"
echo "  6. After the workflow completes, download the azure-runtime-logs artifact to analyze errors"
```

Save this script as `/tmp/azure-provision.sh`, update the variables, and run:

```bash
chmod +x /tmp/azure-provision.sh
/tmp/azure-provision.sh
```

---

## Summary — GitHub Secrets & Variables Checklist

Before triggering the workflow, confirm ALL of the following are set in GitHub:

**Secrets** (Settings → Secrets and variables → Actions → Secrets):
- [ ] `AZURE_CREDENTIALS` (service principal JSON — for log collection)
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_B2C`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_AGENT`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SUPPLIER`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SUPERVISION`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SERVICES`

**Variables** (Settings → Secrets and variables → Actions → Variables):
- [ ] `AZURE_RESOURCE_GROUP` = `rg-lar-system`
- [ ] `AZURE_WEBAPP_NAME_B2C` = `lar-b2c`
- [ ] `AZURE_WEBAPP_NAME_AGENT` = `lar-agent`
- [ ] `AZURE_WEBAPP_NAME_SUPPLIER` = `lar-supplier`
- [ ] `AZURE_WEBAPP_NAME_SUPERVISION` = `lar-supervision`
- [ ] `AZURE_WEBAPP_NAME_SERVICES` = `lar-services`


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
| Azure App Service Plan | P1v3 (Linux, PHP 8.1) | Hosts all five web apps |
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
DB_SERVER_NAME="lar-mysql-server"     # Must be globally unique
DB_ADMIN_USER="laradmin"
DB_ADMIN_PASSWORD="<YourStr0ngP@ssword!>"  # Min 8 chars, upper+lower+number+symbol

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
  --version 8.0 \
  --public-access 0.0.0.0   # Opens firewall to Azure services; restrict later

# Create the databases (one per module)
for DB_NAME in lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux; do
  az mysql flexible-server db create \
    --resource-group "$RESOURCE_GROUP" \
    --server-name "$DB_SERVER_NAME" \
    --database-name "$DB_NAME"
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

Azure MySQL Flexible Server requires SSL. Download the DigiCert CA bundle:

```bash
# Download to the repository root so it is deployed with the app
curl -o DigiCertGlobalRootCA.crt.pem \
  https://dl.cacerts.digicert.com/DigiCertGlobalRootCA.crt.pem
```

---

## 5. Step 3 — Create Azure Web Apps

Create a single **App Service Plan** and five **Web Apps**.

```bash
APP_SERVICE_PLAN="asp-lar-system"

# Create the App Service Plan (Linux, PHP 8.1, P1v3 SKU)
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
    --runtime "PHP|8.1"
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
DB_HOST="<DB_SERVER_NAME>.mysql.database.azure.com"
DB_USER="laradmin"
DB_PASS="<YourStr0ngP@ssword!>"

# B2C
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-b2c" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_USER}@<DB_SERVER_NAME>" \
    DB_PASSWORD="$DB_PASS" \
    DB_DATABASE="lar_b2c" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Agent
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-agent" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_USER}@<DB_SERVER_NAME>" \
    DB_PASSWORD="$DB_PASS" \
    DB_DATABASE="lar_agent" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Supplier
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-supplier" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_USER}@<DB_SERVER_NAME>" \
    DB_PASSWORD="$DB_PASS" \
    DB_DATABASE="lar_supplier" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Supervision
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-supervision" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_USER}@<DB_SERVER_NAME>" \
    DB_PASSWORD="$DB_PASS" \
    DB_DATABASE="lar_supervision" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"

# Services API
az webapp config appsettings set \
  --resource-group "$RESOURCE_GROUP" --name "lar-services" \
  --settings \
    DB_HOSTNAME="$DB_HOST" \
    DB_USERNAME="${DB_USER}@<DB_SERVER_NAME>" \
    DB_PASSWORD="$DB_PASS" \
    DB_DATABASE="lar_webservices" \
    ENVIRONMENT="production" \
    WEBSITE_RUN_FROM_PACKAGE="1"
```

### Required PHP extensions

Enable the necessary PHP extensions on each Web App:

```bash
for APP_NAME in lar-b2c lar-agent lar-supplier lar-supervision lar-services; do
  az webapp config set \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --php-version "8.1"
done
```

The following extensions are available by default on Azure App Service PHP 8.1:
`mysqli`, `mbstring`, `gd`, `curl`, `zip`, `xml`, `intl`, `pdo_mysql`

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

### Add GitHub Secrets

Go to your repository on GitHub:
**Settings → Secrets and variables → Actions → New repository secret**

Add the following **Secrets** (paste the full XML content from each publish profile file):

| Secret Name | Value |
|---|---|
| `AZUREAPPSERVICE_PUBLISHPROFILE_B2C` | Contents of `publishprofile-lar-b2c.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_AGENT` | Contents of `publishprofile-lar-agent.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SUPPLIER` | Contents of `publishprofile-lar-supplier.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SUPERVISION` | Contents of `publishprofile-lar-supervision.xml` |
| `AZUREAPPSERVICE_PUBLISHPROFILE_SERVICES` | Contents of `publishprofile-lar-services.xml` |

### Add GitHub Variables

Go to: **Settings → Secrets and variables → Actions → Variables tab → New repository variable**

| Variable Name | Value |
|---|---|
| `AZURE_WEBAPP_NAME_B2C` | `lar-b2c` |
| `AZURE_WEBAPP_NAME_AGENT` | `lar-agent` |
| `AZURE_WEBAPP_NAME_SUPPLIER` | `lar-supplier` |
| `AZURE_WEBAPP_NAME_SUPERVISION` | `lar-supervision` |
| `AZURE_WEBAPP_NAME_SERVICES` | `lar-services` |

---

## 8. Step 6 — Enable GitHub Actions Deployment

The workflow file `.github/workflows/azure-deploy.yml` is already included in this
repository and will run automatically when you push to the `main` branch.

**Trigger deployment manually:**

1. Go to GitHub → **Actions** tab
2. Select **"Deploy to Azure Web App"**
3. Click **"Run workflow"** → **"Run workflow"**

**What the workflow does:**

1. **Build job** — Checks out the code, sets up PHP 8.1, validates PHP syntax for all modules
2. **Deploy jobs** — Deploys each module to its respective Azure Web App in parallel

---

## 9. Step 7 — Initial Database Setup

After creating the databases, import your SQL schema. Use MySQL Workbench or the CLI:

```bash
# Import schema for each database
mysql \
  --host="<DB_SERVER_NAME>.mysql.database.azure.com" \
  --user="laradmin@<DB_SERVER_NAME>" \
  --password="<YourStr0ngP@ssword!>" \
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
- Ensure SSL is not required in the connection if the `DigiCertGlobalRootCA.crt.pem`
  file is not deployed; disable SSL requirement on the server if needed for initial testing:
  ```bash
  az mysql flexible-server update \
    --resource-group "$RESOURCE_GROUP" \
    --name "$DB_SERVER_NAME" \
    --ssl-enforcement Disabled
  ```

### CodeIgniter routing issues (404 on all pages except home)

- Confirm `web.config` is present at the repository root (it is included in this repo)
- On Linux App Service, the `.htaccess` file should work — verify `mod_rewrite` is enabled:
  ```bash
  az webapp config set \
    --resource-group "$RESOURCE_GROUP" \
    --name "lar-b2c" \
    --generic-configurations '{"linuxFxVersion": "PHP|8.1"}'
  ```

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
DB_ADMIN_PASSWORD="<YourStr0ngP@ssword!>"  # Change this!
# -------------------------------------------------------------

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
  --version 8.0 \
  --public-access 0.0.0.0

echo "==> Creating databases..."
for DB_NAME in lar_b2c lar_agent lar_supplier lar_supervision lar_webservices lar_ultralux; do
  az mysql flexible-server db create \
    --resource-group "$RESOURCE_GROUP" \
    --server-name "$DB_SERVER_NAME" \
    --database-name "$DB_NAME"
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
    --runtime "PHP|8.1"
done

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
echo "  1. Add the publish profile XMLs as GitHub Secrets (see AZURE_DEPLOYMENT.md Step 5)"
echo "  2. Add the app name variables as GitHub Variables (see AZURE_DEPLOYMENT.md Step 5)"
echo "  3. Import your database schema (see AZURE_DEPLOYMENT.md Step 7)"
echo "  4. Push to main or manually trigger the GitHub Actions workflow"
```

Save this script as `/tmp/azure-provision.sh`, update the variables, and run:

```bash
chmod +x /tmp/azure-provision.sh
/tmp/azure-provision.sh
```

---

## Summary — GitHub Secrets & Variables Checklist

Before triggering the workflow, confirm ALL of the following are set in GitHub:

**Secrets** (Settings → Secrets and variables → Actions → Secrets):
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_B2C`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_AGENT`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SUPPLIER`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SUPERVISION`
- [ ] `AZUREAPPSERVICE_PUBLISHPROFILE_SERVICES`

**Variables** (Settings → Secrets and variables → Actions → Variables):
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
