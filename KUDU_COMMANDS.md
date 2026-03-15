# Kudu Console — Ready-to-Run Commands

Paste these into the **Kudu Bash console** for each Azure Web App.

## Open Kudu Console

| App | Kudu URL |
|-----|----------|
| B2C | https://lar-b2c.scm.azurewebsites.net/DebugConsole |
| Agent | https://lar-agent.scm.azurewebsites.net/DebugConsole |
| Supplier | https://lar-supplier.scm.azurewebsites.net/DebugConsole |
| Supervision | https://lar-supervision.scm.azurewebsites.net/DebugConsole |
| Services | https://lar-services.scm.azurewebsites.net/DebugConsole |

Sign in with the same Azure account used for the Azure Portal. The console opens in `/home` by default — use the **Bash** tab (not CMD).

---

## 1 — PHP Version and Extensions

```bash
php -v
php -m
php -i | grep -E "mysql|pdo|curl|json|mbstring|openssl"
```

Expected output includes `PHP 8.2.x` and `mysqli`, `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl`.

---

## 2 — Find and Tail Error Logs

```bash
# List all log files (most recent first)
find /home/LogFiles -name "*.log" -printf "%T@ %p\n" | sort -rn | head -20

# Tail the PHP error log
tail -100 /home/LogFiles/php_errors.log 2>/dev/null || echo "No php_errors.log yet"

# Tail the application (Apache/stdout) log
tail -100 /home/LogFiles/Application/application.log 2>/dev/null || echo "No application.log yet"

# Show all errors in the last 500 lines of every log
find /home/LogFiles -name "*.log" | xargs grep -l "error\|fatal\|warning" 2>/dev/null
```

---

## 3 — Test Database Connectivity

Paste this as a single block — it runs a quick PHP connection test from the command line:

```bash
php -r "
\$host = 'localhost';
\$user = 'travelom_newjuly';
\$pass = 'LN2s]WDQ6\$a%';
\$db   = 'travelom_new_july';

\$conn = @new mysqli(\$host, \$user, \$pass, \$db);
if (\$conn->connect_error) {
    echo 'FAIL: ' . \$conn->connect_error . PHP_EOL;
} else {
    echo 'OK: connected to ' . \$db . PHP_EOL;
    \$conn->close();
}
"
```

> **Note:** This will output `FAIL: ...` because the app uses `localhost` credentials that point to the original hosting server — not Azure. This is the expected result for the audit test. The error is the same one that will appear in the runtime logs.

For the **services** app, the credentials are different:

```bash
php -r "
\$conn = @new mysqli('localhost', 'root', '', 'lar_webservices');
if (\$conn->connect_error) {
    echo 'FAIL: ' . \$conn->connect_error . PHP_EOL;
} else {
    echo 'OK: connected to lar_webservices' . PHP_EOL;
    \$conn->close();
}
"
```

---

## 4 — Verify Deployed Files

```bash
# Confirm app files are deployed
ls /home/site/wwwroot/

# Check the main index.php exists
head -5 /home/site/wwwroot/index.php

# Confirm the web.config is present (IIS URL rewrite)
cat /home/site/wwwroot/web.config 2>/dev/null | head -20 || echo "web.config not found"

# Show disk usage
du -sh /home/site/wwwroot/
```

---

## 5 — Check PHP Config / php.ini

```bash
# Show loaded php.ini path
php --ini

# Key settings
php -r "echo 'display_errors: ' . ini_get('display_errors') . PHP_EOL;
echo 'error_reporting: ' . ini_get('error_reporting') . PHP_EOL;
echo 'log_errors: ' . ini_get('log_errors') . PHP_EOL;
echo 'error_log: ' . ini_get('error_log') . PHP_EOL;
echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;
echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;
"

# Show all custom ini files being loaded
php --ini | grep "Scan for additional .ini files"
ls $(php --ini | grep "Scan for additional" | awk -F': ' '{print $2}') 2>/dev/null
```

---

## 6 — Enable PHP Error Display (temporary — for audit only)

> ⚠️ **Security warning:** `display_errors = On` exposes stack traces, file paths, and credentials to anyone who can reach the URL. Enable only while actively debugging on a non-public or IP-restricted app, and **disable immediately afterward**.

```bash
# Create a custom ini to force error display on (TEMPORARY — disable after use)
cat > /home/site/wwwroot/error_audit.ini << 'EOF'
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /home/LogFiles/php_errors.log
EOF

echo "Done. Add this line to .htaccess or php.ini scan dir to activate."
echo "IMPORTANT: Remove this file when diagnostics are complete."
```

Or set via App Service config (persists across restarts) — run from Azure CLI on your local machine, not Kudu:

> ⚠️ **Remove this setting immediately after diagnostics** — rerun the command with `--settings DISPLAY_ERRORS="0"` or delete the setting in the Azure Portal.

```bash
az webapp config appsettings set \
  --resource-group rg-lar-system \
  --name lar-b2c \
  --settings PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d:/home/site/ini" \
             DISPLAY_ERRORS="1"
```

---

## 7 — Network / Hostname Check

```bash
# Check outbound DNS from the app container
nslookup google.com || host google.com

# Ping Azure MySQL Flexible Server (replace hostname with your server)
# Example: lar-mysql.mysql.database.azure.com
nslookup lar-mysql.mysql.database.azure.com 2>/dev/null || echo "DNS lookup failed"

# Check if port 3306 is reachable (replace with your MySQL FQDN)
timeout 5 bash -c 'echo > /dev/tcp/lar-mysql.mysql.database.azure.com/3306' \
  && echo "Port 3306 OPEN" || echo "Port 3306 CLOSED or unreachable"
```

---

## 8 — List Environment Variables

```bash
# Show all environment variables set on this App Service instance
env | sort

# Show only app-specific settings (set via Azure Portal / GitHub Variables)
env | grep -E "AZURE|WEBSITE|MYSQL|DB_|APP_"
```

---

## 9 — Restart the App (from Azure CLI — not Kudu)

Run on your local machine after making config changes:

```bash
az webapp restart --resource-group rg-lar-system --name lar-b2c
az webapp restart --resource-group rg-lar-system --name lar-agent
az webapp restart --resource-group rg-lar-system --name lar-supplier
az webapp restart --resource-group rg-lar-system --name lar-supervision
az webapp restart --resource-group rg-lar-system --name lar-services
```

---

## 10 — Quick Audit Summary Script

Run this in Kudu to get a one-shot audit snapshot:

```bash
echo "=== PHP VERSION ===" && php -v | head -1
echo ""
echo "=== LOADED EXTENSIONS ===" && php -m | grep -E "mysqli|pdo_mysql|curl|mbstring|json|openssl"
echo ""
echo "=== DEPLOYED FILES ===" && ls /home/site/wwwroot/ | head -20
echo ""
echo "=== RECENT LOG ERRORS ===" && find /home/LogFiles -name "*.log" 2>/dev/null | \
  xargs grep -h -i "error\|fatal\|warning" 2>/dev/null | tail -20
echo ""
echo "=== DATABASE TEST ===" && php -r "
\$c = @new mysqli('localhost','travelom_newjuly','LN2s]WDQ6\$a%','travelom_new_july');
echo \$c->connect_error ? 'DB FAIL: '.\$c->connect_error : 'DB OK';
echo PHP_EOL;
"
echo ""
echo "=== ENVIRONMENT ===" && env | grep -E "WEBSITE_SITE_NAME|WEBSITE_SKU|WEBSITE_PHP" | sort
```

---

## Useful Kudu REST API (call from browser or curl)

These URLs work in your browser while logged in to the Azure Portal:

| Action | URL |
|--------|-----|
| List all files | `https://lar-b2c.scm.azurewebsites.net/api/vfs/site/wwwroot/` |
| Download error log | `https://lar-b2c.scm.azurewebsites.net/api/vfs/home/LogFiles/php_errors.log` |
| List LogFiles folder | `https://lar-b2c.scm.azurewebsites.net/api/vfs/home/LogFiles/` |
| Download all logs ZIP | `https://lar-b2c.scm.azurewebsites.net/api/dump` |
| Process list | `https://lar-b2c.scm.azurewebsites.net/api/processes` |
| Environment variables | `https://lar-b2c.scm.azurewebsites.net/Env` |

Replace `lar-b2c` with `lar-agent`, `lar-supplier`, `lar-supervision`, or `lar-services` for the other apps.
