# LAR System - Detailed Remediation Roadmap
## Technical Implementation Guide

**Version:** 1.0  
**Date:** February 26, 2026  
**Target Audience:** Development Team, DevOps, Security Engineers

---

## Overview

This document provides detailed, actionable steps to remediate all critical and high-priority security vulnerabilities identified in the LAR system audit. Each task includes specific file locations, code examples, and acceptance criteria.

---

## Phase 1: Emergency Security Fixes (Week 1-2)

### Task 1.1: Implement Environment-Based Configuration

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 2 days  
**Assignee:** Senior PHP Developer

#### Steps:

1. **Install PHP dotenv library:**
```bash
cd /home/runner/work/lar_system/lar_system
composer require vlucas/phpdotenv
```

2. **Create `.env.example` template:**
```env
# Database Configuration
DB_HOSTNAME=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_DATABASE=your_database
DB_PORT=3306

# Database Secondary
DB2_HOSTNAME=localhost
DB2_USERNAME=your_username
DB2_PASSWORD=your_password
DB2_DATABASE=your_database

# Application
APP_ENV=production
APP_DEBUG=false
BASE_URL=https://yourdomain.com

# API Keys
TBO_USERNAME=your_tbo_username
TBO_PASSWORD=your_tbo_password
TBO_API_KEY=your_tbo_api_key

PROVAB_API_KEY=your_provab_key
PROVAB_USERNAME=your_provab_username

# Payment Gateway - PayPal
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_SECRET=your_secret

# Payment Gateway - PayU
PAYU_MERCHANT_KEY=your_merchant_key
PAYU_MERCHANT_SALT=your_merchant_salt
PAYU_MERCHANT_ID=your_merchant_id
PAYU_MODE=live

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email
SMTP_PASSWORD=your_password

# Security
SESSION_KEY=your_random_32_char_key
ENCRYPTION_KEY=your_random_32_char_key
```

3. **Add `.env` to `.gitignore`:**
```bash
echo ".env" >> .gitignore
echo ".env.local" >> .gitignore
echo ".env.*.local" >> .gitignore
```

4. **Update `index.php` to load environment:**
```php
// Add at the top of index.php (after <?php)
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Define environment
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
```

5. **Update database configuration files:**

**File:** `b2c/config/database.php`
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'   => '',
    'hostname' => getenv('DB_HOSTNAME') ?: 'localhost',
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'database' => getenv('DB_DATABASE'),
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => (ENVIRONMENT !== 'production')
);

$db['seconddb'] = array(
    'dsn'   => '',
    'hostname' => getenv('DB2_HOSTNAME') ?: 'localhost',
    'username' => getenv('DB2_USERNAME'),
    'password' => getenv('DB2_PASSWORD'),
    'database' => getenv('DB2_DATABASE'),
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => (ENVIRONMENT !== 'production')
);
```

**Repeat for:**
- `agent/application/config/database.php`
- `services/webservices/application/config/database.php`
- `ultralux/application/config/database.php`
- `supervision/application/config/database.php`
- `supplier/application/config/database.php`

6. **Rotate all exposed credentials:**
- Generate new database passwords
- Update database server with new passwords
- Update `.env` file with new credentials
- Test connection from application

#### Acceptance Criteria:
- ✅ No credentials visible in source code
- ✅ Application loads successfully with `.env` config
- ✅ All modules (B2C, Agent, Admin) connect to database
- ✅ `.env` file is in `.gitignore`
- ✅ `.env.example` committed to repo

---

### Task 1.2: Remove Debug Statements

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 1 day  
**Assignee:** Any Developer

#### Files to Fix:

1. **`b2c/controllers/payment_gateway.php`:**

**Find and remove:**
```php
// Line 43
debug($params);exit;

// Line 195
debug($response);exit;

// Line 212
debug($this->payment_model->get_payment_details($book_id));exit;

// Line 287
var_dump($payment_data);exit;
```

**Replace with proper logging:**
```php
// Use CodeIgniter's log_message() instead
if (ENVIRONMENT !== 'production') {
    log_message('debug', 'Payment params: ' . json_encode($params));
}
```

2. **Search entire codebase:**
```bash
# Find all debug statements
grep -r "debug(" --include="*.php" .
grep -r "var_dump(" --include="*.php" .
grep -r "print_r(" --include="*.php" .
grep -r "exit;" --include="*.php" . | grep -v "exit('No direct"
```

3. **Remove all instances in:**
- `b2c/controllers/payment_gateway.php`
- `agent/application/controllers/payment_gateway.php`
- Any other controllers with debug code

4. **Implement proper logging:**

**Create:** `system/helpers/custom/logging_helper.php`
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('log_debug')) {
    function log_debug($message, $data = array()) {
        if (ENVIRONMENT !== 'production') {
            $log_data = is_array($data) ? json_encode($data) : $data;
            log_message('debug', $message . ': ' . $log_data);
        }
    }
}

if (!function_exists('log_payment')) {
    function log_payment($action, $data = array()) {
        // Log to secure payment log file
        $CI =& get_instance();
        $log_file = APPPATH . 'logs/payment_' . date('Y-m-d') . '.log';
        
        // Remove sensitive data before logging
        unset($data['card_number'], $data['cvv'], $data['card_expiry']);
        
        $log_entry = date('Y-m-d H:i:s') . ' - ' . $action . ': ' . json_encode($data) . PHP_EOL;
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}
```

#### Acceptance Criteria:
- ✅ No `debug()`, `var_dump()`, `print_r()` in production code
- ✅ All debugging replaced with `log_message()` or custom logger
- ✅ Logs stored in files, not displayed to users
- ✅ Sensitive data (passwords, cards) never logged

---

### Task 1.3: Disable Production Error Display

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 1 hour  
**Assignee:** DevOps Engineer

#### Steps:

1. **Update `index.php` in all modules:**

**Files to update:**
- `index.php` (root)
- `b2c/index.php`
- `agent/index.php`
- `services/webservices/index.php`
- `ultralux/application/index.php`
- `supervision/application/index.php`

**Add after ENVIRONMENT definition:**
```php
switch (ENVIRONMENT)
{
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
    break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}
```

2. **Update `config.php`:**
```php
$config['log_threshold'] = (ENVIRONMENT === 'production') ? 1 : 4;
// 0 = Disables logging
// 1 = Error Messages (including PHP errors)
// 2 = Debug Messages
// 3 = Informational Messages
// 4 = All Messages
```

3. **Create custom error pages:**

**File:** `b2c/errors/error_general.php`
```php
<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>We're sorry, something went wrong</h1>
    <p>Our team has been notified and is working to fix the issue.</p>
    <p>Please try again in a few minutes.</p>
    <p><a href="/">Return to homepage</a></p>
</body>
</html>
```

#### Acceptance Criteria:
- ✅ No PHP errors displayed in production
- ✅ No stack traces visible to users
- ✅ Custom error page shows for all errors
- ✅ Errors logged to files for debugging

---

### Task 1.4: Fix Password Hashing

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 3 days  
**Assignee:** Senior PHP Developer

#### Current Implementation (INSECURE):
```php
// user_model.php - INSECURE
$data['password'] = provab_encrypt(md5(trim($password)));
```

#### New Implementation (SECURE):

1. **Create new password helper:**

**File:** `system/helpers/custom/password_helper.php`
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('hash_password')) {
    /**
     * Hash password using Argon2id (most secure)
     * Falls back to bcrypt if Argon2id not available
     */
    function hash_password($password) {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,  // 64 MB
                'time_cost' => 4,
                'threads' => 3
            ]);
        } else {
            // Fallback to bcrypt
            return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }
    }
}

if (!function_exists('verify_password')) {
    /**
     * Verify password against hash
     */
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}

if (!function_exists('needs_rehash')) {
    /**
     * Check if password needs rehashing (algorithm upgrade)
     */
    function needs_rehash($hash) {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2ID);
        } else {
            return password_needs_rehash($hash, PASSWORD_BCRYPT);
        }
    }
}
```

2. **Add new column to user table:**

```sql
-- Add migration script
ALTER TABLE user ADD COLUMN password_new VARCHAR(255) NULL AFTER password;
ALTER TABLE user ADD COLUMN password_migrated TINYINT(1) DEFAULT 0;
ALTER TABLE user ADD COLUMN password_migration_date DATETIME NULL;
```

3. **Update user model for dual authentication:**

**File:** `b2c/models/user_model.php`, `agent/application/models/user_model.php`

```php
public function verify_login($email, $password) {
    $this->db->where('email', $email);
    $query = $this->db->get('user');
    
    if ($query->num_rows() == 0) {
        return FALSE;
    }
    
    $user = $query->row();
    
    // Try new password hash first
    if (!empty($user->password_new) && verify_password($password, $user->password_new)) {
        // New password verified successfully
        
        // Check if needs rehash (algorithm upgrade)
        if (needs_rehash($user->password_new)) {
            $this->update_password_hash($user->user_id, $password);
        }
        
        return $user;
    }
    
    // Fall back to old password for migration
    $old_hash = provab_encrypt(md5(trim($password)));
    if ($old_hash === $user->password) {
        // Old password correct - migrate to new hash
        $this->migrate_password($user->user_id, $password);
        return $user;
    }
    
    return FALSE;
}

private function migrate_password($user_id, $password) {
    $data = array(
        'password_new' => hash_password($password),
        'password_migrated' => 1,
        'password_migration_date' => date('Y-m-d H:i:s')
    );
    
    $this->db->where('user_id', $user_id);
    $this->db->update('user', $data);
    
    log_message('info', 'Password migrated for user: ' . $user_id);
}

private function update_password_hash($user_id, $password) {
    $data = array(
        'password_new' => hash_password($password)
    );
    
    $this->db->where('user_id', $user_id);
    $this->db->update('user', $data);
}
```

4. **Update registration to use new hash:**

```php
public function register_user($user_data) {
    $data = array(
        'email' => $user_data['email'],
        'password_new' => hash_password($user_data['password']),  // New field
        'password_migrated' => 1,
        'created_date' => date('Y-m-d H:i:s')
        // ... other fields
    );
    
    return $this->db->insert('user', $data);
}
```

5. **Create migration script:**

**File:** `scripts/migrate_passwords.php`
```php
<?php
/**
 * Password Migration Script
 * Run once to force all users to reset passwords
 */

require_once 'index.php';

$CI =& get_instance();
$CI->load->database();
$CI->load->model('user_model');

// Get all users with old passwords
$CI->db->where('password_migrated', 0);
$CI->db->or_where('password_new IS NULL', NULL, FALSE);
$users = $CI->db->get('user')->result();

echo "Found " . count($users) . " users to migrate\n";

foreach ($users as $user) {
    // Generate password reset token
    $token = bin2hex(random_bytes(32));
    
    $data = array(
        'reset_token' => $token,
        'reset_expiry' => date('Y-m-d H:i:s', strtotime('+7 days'))
    );
    
    $CI->db->where('user_id', $user->user_id);
    $CI->db->update('user', $data);
    
    // Send password reset email
    $reset_link = site_url('auth/reset_password/' . $token);
    
    $message = "Dear " . $user->name . ",\n\n";
    $message .= "We have upgraded our security systems. Please reset your password using the link below:\n\n";
    $message .= $reset_link . "\n\n";
    $message .= "This link will expire in 7 days.\n\n";
    $message .= "Luxury Africa Resorts";
    
    // Send email (implement your email function)
    send_email($user->email, 'Security Upgrade - Password Reset Required', $message);
    
    echo "Sent reset email to: " . $user->email . "\n";
}

echo "Migration complete!\n";
```

#### Acceptance Criteria:
- ✅ All new registrations use `password_hash()`
- ✅ Login supports both old and new passwords during migration
- ✅ Passwords automatically migrate on successful login
- ✅ Old password column removed after 90-day transition
- ✅ Password reset emails sent to all users

---

### Task 1.5: Add CSRF Protection

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 2 days  
**Assignee:** PHP Developer

#### Steps:

1. **Enable CSRF in `config.php`:**

**Files:** All `config/config.php` files in each module

```php
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;  // 2 hours
$config['csrf_regenerate'] = TRUE;  // Regenerate token on submit
$config['csrf_exclude_uris'] = array(
    'api/.*',  // Exclude API endpoints (use API key auth instead)
    'webhook/.*'  // Exclude webhooks
);
```

2. **Update all forms to include CSRF token:**

**Example: Payment form**
```php
<!-- b2c/views/payment/payment_form.php -->
<form method="POST" action="<?php echo site_url('payment_gateway/process'); ?>">
    
    <!-- Add CSRF token -->
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    
    <!-- Rest of form -->
    <input type="text" name="amount" value="<?php echo $amount; ?>">
    <button type="submit">Pay Now</button>
</form>
```

3. **Search and update all forms:**

```bash
# Find all forms
grep -r "<form" --include="*.php" b2c/views/
grep -r "<form" --include="*.php" agent/application/views/
```

**Forms to update:**
- Payment forms
- Booking forms
- Login/registration forms
- Profile update forms
- Admin forms

4. **Handle AJAX requests:**

**File:** `b2c/views/template_list/template_v1/js/common.js`
```javascript
// Add CSRF token to all AJAX requests
$.ajaxSetup({
    data: {
        '<?php echo $this->security->get_csrf_token_name(); ?>': 
        '<?php echo $this->security->get_csrf_hash(); ?>'
    }
});

// Or use header
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

**Add meta tag in header:**
```php
<!-- b2c/views/template_list/template_v1/header.php -->
<head>
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">
</head>
```

5. **Test CSRF protection:**

Create test script:
```php
// tests/csrf_test.php
public function test_csrf_protection() {
    // Try to submit form without CSRF token
    $response = $this->post('/payment_gateway/process', [
        'amount' => 1000
        // No CSRF token
    ]);
    
    // Should return error
    $this->assertResponseStatus(403);
    $this->assertContains('Invalid request', $response);
}
```

#### Acceptance Criteria:
- ✅ CSRF protection enabled globally
- ✅ All forms include CSRF tokens
- ✅ AJAX requests include CSRF tokens
- ✅ Forms without tokens are rejected (403 error)
- ✅ Payment forms specifically tested

---

## Phase 2: Critical Security Hardening (Week 3-4)

### Task 2.1: Fix SQL Injection Vulnerabilities

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 5 days  
**Assignee:** Senior PHP Developer

#### Vulnerable Code Locations:

1. **`system/custom/models/custom_db.php` - Line 45**

**BEFORE (VULNERABLE):**
```php
public function join_record($tables, $constraints, $cols, $condition) {
    $this->db->select($cols);
    $this->db->from($tables[0]);
    
    for ($i=1; $i<sizeof($tables); $i++) {
        $const = explode("=", $constraints[$i]);
        $ck = $const[0];
        $cv = $const[1];
        
        // VULNERABLE - Direct string concatenation
        $this->db->join($tables[$i], "$ck=$cv");
    }
    
    // More vulnerable code...
    if ($condition != '') {
        $this->db->where($condition);  // VULNERABLE if $condition is user input
    }
}
```

**AFTER (SECURE):**
```php
public function join_record($tables, $constraints, $cols, $condition) {
    $this->db->select($cols);
    $this->db->from($tables[0]);
    
    for ($i=1; $i<sizeof($tables); $i++) {
        $const = explode("=", $constraints[$i]);
        $ck = trim($const[0]);
        $cv = trim($const[1]);
        
        // Use FALSE to prevent escaping (table names), but validate inputs
        if (!$this->is_valid_column_name($ck) || !$this->is_valid_column_name($cv)) {
            log_message('error', 'Invalid column name in join');
            return FALSE;
        }
        
        $this->db->join($tables[$i], "$ck = $cv", 'left', FALSE);
    }
    
    // Use associative array for conditions
    if (is_array($condition) && !empty($condition)) {
        $this->db->where($condition);  // Automatically escaped
    } elseif (is_string($condition) && $condition != '') {
        log_message('warning', 'String condition passed to join_record - use array instead');
        // Reject raw string conditions
        return FALSE;
    }
}

// Add validation helper
private function is_valid_column_name($name) {
    // Allow only alphanumeric, underscore, dot (for table.column)
    return preg_match('/^[a-zA-Z0-9_.]+$/', $name);
}
```

2. **Update all model methods to use query builder:**

**Example: `flight_model.php`**

**BEFORE:**
```php
public function get_flight_by_id($flight_id) {
    $sql = "SELECT * FROM flights WHERE flight_id = " . $flight_id;
    return $this->db->query($sql)->row();
}
```

**AFTER:**
```php
public function get_flight_by_id($flight_id) {
    $this->db->where('flight_id', $flight_id);  // Automatically escaped
    return $this->db->get('flights')->row();
}
```

3. **For complex queries, use bindings:**

**BEFORE:**
```php
$sql = "SELECT * FROM bookings WHERE user_id = $user_id AND status = '$status'";
$result = $this->db->query($sql);
```

**AFTER:**
```php
$sql = "SELECT * FROM bookings WHERE user_id = ? AND status = ?";
$result = $this->db->query($sql, array($user_id, $status));
```

#### Files to Audit and Fix:

Run this to find potentially vulnerable queries:
```bash
# Find direct SQL queries
grep -rn "->query(" --include="*.php" system/custom/models/
grep -rn "WHERE.*\$" --include="*.php" system/custom/models/
grep -rn 'WHERE.*".*\$' --include="*.php" system/custom/models/
```

**Critical files to review:**
- `system/custom/models/custom_db.php`
- `b2c/models/flight_model.php`
- `b2c/models/hotel_model.php`
- `b2c/models/user_model.php`
- `b2c/models/payment_model.php`
- `services/webservices/application/models/*.php`

#### Testing SQL Injection:

**Create test script:**
```php
// tests/security/sql_injection_test.php

public function test_sql_injection_in_search() {
    // Attempt SQL injection in flight search
    $malicious_input = "London' OR '1'='1";
    
    $this->post('/flight/search', [
        'destination' => $malicious_input
    ]);
    
    // Should not return all flights
    $response = $this->response->getBody();
    $this->assertNotContains('OR 1=1', $this->db->last_query());
}

public function test_sql_injection_in_booking() {
    // Test booking ID injection
    $malicious_id = "123; DROP TABLE bookings;--";
    
    $this->get('/booking/view/' . $malicious_id);
    
    // Should not execute DROP
    $tables = $this->db->list_tables();
    $this->assertContains('bookings', $tables);
}
```

#### Acceptance Criteria:
- ✅ All SQL queries use prepared statements or query builder
- ✅ No raw SQL with string concatenation
- ✅ All user inputs properly escaped
- ✅ SQL injection tests pass
- ✅ No "OR 1=1" style attacks possible

---

### Task 2.2: Add XSS Output Encoding

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 3 days  
**Assignee:** PHP Developer

#### Steps:

1. **Create output encoding helper:**

**File:** `system/helpers/custom/security_helper.php`
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('e')) {
    /**
     * Escape output (shorthand)
     */
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('escape_js')) {
    /**
     * Escape for JavaScript context
     */
    function escape_js($string) {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

if (!function_exists('escape_url')) {
    /**
     * Escape for URL context
     */
    function escape_url($string) {
        return urlencode($string);
    }
}

if (!function_exists('escape_html_attr')) {
    /**
     * Escape for HTML attribute context
     */
    function escape_html_attr($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
```

2. **Update all view files:**

**Find unescaped output:**
```bash
# Find potentially vulnerable echo statements
grep -rn "<?php echo \$" --include="*.php" b2c/views/
grep -rn "<?=\$" --include="*.php" b2c/views/
```

**BEFORE (VULNERABLE):**
```php
<!-- b2c/views/flight/results.php -->
<h2>Flights to <?php echo $_GET['destination']; ?></h2>
<p>Passenger: <?php echo $booking['passenger_name']; ?></p>
<div data-price="<?php echo $flight['price']; ?>"></div>

<script>
var searchQuery = '<?php echo $_GET['query']; ?>';
</script>
```

**AFTER (SECURE):**
```php
<!-- b2c/views/flight/results.php -->
<h2>Flights to <?php echo e($_GET['destination']); ?></h2>
<p>Passenger: <?php echo e($booking['passenger_name']); ?></p>
<div data-price="<?php echo escape_html_attr($flight['price']); ?>"></div>

<script>
var searchQuery = <?php echo escape_js($_GET['query']); ?>;
</script>
```

3. **Auto-load helper:**

**File:** `config/autoload.php`
```php
$autoload['helper'] = array('url', 'form', 'security');
```

4. **Update critical views:**

**Priority files (user input displayed):**
- `b2c/views/flight/search_results.php`
- `b2c/views/hotel/search_results.php`
- `b2c/views/booking/confirmation.php`
- `b2c/views/user/profile.php`
- `agent/application/views/booking/list.php`

5. **Test XSS protection:**

```php
// tests/security/xss_test.php

public function test_reflected_xss() {
    // Attempt XSS in search
    $xss_payload = '<script>alert("XSS")</script>';
    
    $response = $this->get('/flight/search?destination=' . urlencode($xss_payload));
    
    // Script should be escaped
    $this->assertNotContains('<script>', $response);
    $this->assertContains('&lt;script&gt;', $response);
}

public function test_stored_xss() {
    // Book flight with XSS in passenger name
    $xss_name = '<img src=x onerror=alert("XSS")>';
    
    $this->post('/booking/create', [
        'passenger_name' => $xss_name
    ]);
    
    // View booking
    $response = $this->get('/booking/view/123');
    
    // Should be escaped
    $this->assertNotContains('<img src=x', $response);
    $this->assertContains('&lt;img', $response);
}
```

#### Acceptance Criteria:
- ✅ All user input escaped on output
- ✅ XSS helper functions created and used
- ✅ JavaScript context properly escaped
- ✅ HTML attribute context properly escaped
- ✅ XSS tests pass

---

### Task 2.3: Implement Security Headers

**Priority:** 🟠 P1 - HIGH  
**Effort:** 2 days  
**Assignee:** DevOps Engineer

#### Steps:

1. **Add security headers to `.htaccess`:**

**File:** `.htaccess` (root)
```apache
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "DENY"
    
    # Prevent MIME type sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.tbo.com https://api.provab.com; frame-src 'self' https://www.paypal.com https://test.payu.in https://secure.payu.in;"
    
    # HSTS (only after testing!)
    # Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    
    # Permissions Policy (formerly Feature Policy)
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>
```

2. **Or add via PHP in config:**

**File:** `hooks/hooks.php`
```php
class SecurityHeaders {
    public function set_headers() {
        if (!headers_sent()) {
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // CSP (adjust as needed)
            $csp = "default-src 'self'; ";
            $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://cdnjs.cloudflare.com; ";
            $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
            $csp .= "img-src 'self' data: https:; ";
            $csp .= "font-src 'self' https://fonts.gstatic.com;";
            
            header("Content-Security-Policy: $csp");
            
            // HSTS (enable after testing HTTPS)
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }
}
```

**Enable in `config/hooks.php`:**
```php
$hook['post_controller_constructor'][] = array(
    'class'    => 'SecurityHeaders',
    'function' => 'set_headers',
    'filename' => 'hooks.php',
    'filepath' => 'hooks'
);
```

3. **Test headers:**

```bash
# Check headers with curl
curl -I https://yourdomain.com

# Should see:
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff
# Content-Security-Policy: ...
```

4. **Use online security header checker:**
- https://securityheaders.com/
- Target score: A+

#### Acceptance Criteria:
- ✅ All security headers present
- ✅ SecurityHeaders.com score A+
- ✅ CSP does not break existing functionality
- ✅ HSTS enabled after HTTPS verification

---

### Task 2.4: Secure API Credentials

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 1 day  
**Assignee:** Senior PHP Developer

#### Steps:

1. **Move all API credentials to `.env`:**

Already added in `.env.example` in Task 1.1

2. **Update API libraries:**

**File:** `system/libraries/flight/tbo/tbo.php`

**BEFORE:**
```php
class TBO_API {
    private $username = 'your_username';  // HARDCODED
    private $password = 'your_password';  // HARDCODED
    private $api_key = 'your_api_key';    // HARDCODED
}
```

**AFTER:**
```php
class TBO_API {
    private $username;
    private $password;
    private $api_key;
    
    public function __construct() {
        $this->username = getenv('TBO_USERNAME');
        $this->password = getenv('TBO_PASSWORD');
        $this->api_key = getenv('TBO_API_KEY');
        
        // Validate credentials are set
        if (empty($this->username) || empty($this->password) || empty($this->api_key)) {
            log_message('error', 'TBO API credentials not configured');
            throw new Exception('API configuration error');
        }
    }
}
```

**Files to update:**
- `system/libraries/flight/tbo/tbo.php`
- `system/libraries/flight/amadeus/amadeus.php`
- `system/libraries/hotel/provab_hotelcrs.php`
- `system/libraries/hotel/GRN/grn_api.php`
- `system/libraries/car/carnect.php`
- `system/libraries/payment_gateway/paypal.php`
- `system/libraries/payment_gateway/payu.php`

3. **Remove commented credentials:**

```bash
# Find commented credentials
grep -rn "username.*=.*'" --include="*.php" system/libraries/
grep -rn "password.*=.*'" --include="*.php" system/libraries/
grep -rn "api.*key.*=.*'" --include="*.php" system/libraries/
```

Remove all commented-out credentials.

4. **Implement credential rotation:**

**File:** `scripts/rotate_api_keys.php`
```php
<?php
/**
 * API Key Rotation Script
 * Run this every 90 days to rotate API credentials
 */

// Log rotation
log_message('info', 'Starting API key rotation');

// Send alert to admin
$admins = array('admin@luxuryafricaresorts.com');
$message = "API keys scheduled for rotation. Please update .env file with new credentials.";

foreach ($admins as $admin) {
    mail($admin, 'API Key Rotation Required', $message);
}

// Document last rotation
file_put_contents(
    APPPATH . 'logs/key_rotation.log',
    date('Y-m-d H:i:s') . " - Rotation reminder sent\n",
    FILE_APPEND
);
```

#### Acceptance Criteria:
- ✅ All API credentials in `.env` file
- ✅ No hardcoded credentials in code
- ✅ No credentials in comments
- ✅ API classes throw errors if credentials missing
- ✅ Rotation reminders scheduled

---

## Phase 3: Testing Infrastructure (Week 5-7)

### Task 3.1: Set Up PHPUnit

**Priority:** 🟠 P1 - HIGH  
**Effort:** 2 days  
**Assignee:** QA Engineer

#### Steps:

1. **Install PHPUnit:**

```bash
cd /home/runner/work/lar_system/lar_system
composer require --dev phpunit/phpunit ^9.0
```

2. **Create `phpunit.xml` configuration:**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true"
         stopOnFailure="false">
    
    <testsuites>
        <testsuite name="Unit Tests">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration Tests">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Security Tests">
            <directory>tests/Security</directory>
        </testsuite>
    </testsuites>
    
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">b2c/models</directory>
            <directory suffix=".php">b2c/controllers</directory>
            <directory suffix=".php">system/libraries</directory>
        </include>
        <exclude>
            <directory>vendor</directory>
            <directory>tests</directory>
        </exclude>
    </coverage>
    
    <php>
        <env name="ENVIRONMENT" value="testing"/>
        <env name="DB_DATABASE" value="lar_test"/>
    </php>
</phpunit>
```

3. **Create test bootstrap:**

**File:** `tests/bootstrap.php`
```php
<?php
// Load CodeIgniter framework for testing
define('ENVIRONMENT', 'testing');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../b2c/');

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Load CodeIgniter
require_once __DIR__ . '/../index.php';
```

4. **Create base test case:**

**File:** `tests/TestCase.php`
```php
<?php
namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected $CI;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get CodeIgniter instance
        $this->CI =& get_instance();
        
        // Start transaction for database tests
        $this->CI->db->trans_start();
    }
    
    protected function tearDown(): void
    {
        // Rollback transaction
        $this->CI->db->trans_rollback();
        
        parent::tearDown();
    }
}
```

5. **Create first test:**

**File:** `tests/Unit/UserModelTest.php`
```php
<?php
namespace Tests\Unit;

use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_can_create_user()
    {
        $this->CI->load->model('user_model');
        
        $user_data = array(
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'name' => 'Test User'
        );
        
        $user_id = $this->CI->user_model->register_user($user_data);
        
        $this->assertIsInt($user_id);
        $this->assertGreaterThan(0, $user_id);
    }
    
    public function test_password_is_hashed()
    {
        $this->CI->load->model('user_model');
        
        $password = 'PlainTextPassword123';
        $user_data = array(
            'email' => 'test2@example.com',
            'password' => $password,
            'name' => 'Test User 2'
        );
        
        $user_id = $this->CI->user_model->register_user($user_data);
        
        // Fetch user
        $user = $this->CI->user_model->get_user($user_id);
        
        // Password should NOT be plain text
        $this->assertNotEquals($password, $user->password_new);
        
        // Should start with hash algorithm identifier
        $this->assertStringStartsWith('$2y$', $user->password_new); // bcrypt
    }
}
```

6. **Run tests:**

```bash
./vendor/bin/phpunit
```

#### Acceptance Criteria:
- ✅ PHPUnit installed and configured
- ✅ Test directory structure created
- ✅ Base test case with database transactions
- ✅ At least 5 unit tests passing
- ✅ Tests run in CI/CD pipeline

---

### Task 3.2: Write Critical Path Tests

**Priority:** 🔴 P0 - CRITICAL  
**Effort:** 5 days  
**Assignee:** QA Engineer + Developer

#### Test Suite Outline:

**File:** `tests/Integration/BookingFlowTest.php`
```php
<?php
namespace Tests\Integration;

use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    /**
     * Test complete booking flow: Search → Select → Book → Pay
     */
    public function test_complete_flight_booking_flow()
    {
        // 1. Search for flights
        $search_params = array(
            'origin' => 'JNB',
            'destination' => 'CPT',
            'departure_date' => date('Y-m-d', strtotime('+30 days')),
            'adults' => 2
        );
        
        $this->CI->load->model('flight_model');
        $results = $this->CI->flight_model->search_flights($search_params);
        
        $this->assertNotEmpty($results);
        $this->assertIsArray($results);
        
        // 2. Select a flight
        $selected_flight = $results[0];
        $this->assertArrayHasKey('price', $selected_flight);
        $this->assertGreaterThan(0, $selected_flight['price']);
        
        // 3. Create booking
        $booking_data = array(
            'flight_id' => $selected_flight['id'],
            'passengers' => array(
                array(
                    'title' => 'Mr',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com'
                )
            ),
            'contact_email' => 'john@example.com',
            'contact_phone' => '+27123456789'
        );
        
        $this->CI->load->model('booking_model');
        $booking_id = $this->CI->booking_model->create_booking($booking_data);
        
        $this->assertIsInt($booking_id);
        
        // 4. Verify booking created
        $booking = $this->CI->booking_model->get_booking($booking_id);
        $this->assertEquals('pending', $booking->status);
        
        // 5. Process payment (sandbox)
        $this->CI->load->model('payment_model');
        $payment_result = $this->CI->payment_model->process_payment(array(
            'booking_id' => $booking_id,
            'amount' => $selected_flight['price'],
            'gateway' => 'paypal_sandbox'
        ));
        
        // Check payment processed
        $this->assertTrue($payment_result['success']);
        
        // 6. Verify booking confirmed
        $booking = $this->CI->booking_model->get_booking($booking_id);
        $this->assertEquals('confirmed', $booking->status);
    }
    
    public function test_booking_rollback_on_payment_failure()
    {
        // Create booking
        $booking_id = $this->create_test_booking();
        
        // Simulate payment failure
        $this->CI->load->model('payment_model');
        $payment_result = $this->CI->payment_model->process_payment(array(
            'booking_id' => $booking_id,
            'amount' => 1000,
            'gateway' => 'test_failure'  // Special test gateway that always fails
        ));
        
        $this->assertFalse($payment_result['success']);
        
        // Booking should remain pending or be cancelled
        $this->CI->load->model('booking_model');
        $booking = $this->CI->booking_model->get_booking($booking_id);
        $this->assertContains($booking->status, ['pending', 'cancelled']);
    }
}
```

**File:** `tests/Integration/PaymentGatewayTest.php`
```php
<?php
namespace Tests\Integration;

use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    public function test_paypal_sandbox_connection()
    {
        $this->CI->load->library('payment_gateway/paypal');
        
        $result = $this->CI->paypal->test_connection();
        
        $this->assertTrue($result);
    }
    
    public function test_payment_amount_validation()
    {
        $this->CI->load->model('payment_model');
        
        // Test invalid amounts
        $invalid_amounts = [-100, 0, 'abc', null];
        
        foreach ($invalid_amounts as $amount) {
            $result = $this->CI->payment_model->validate_amount($amount);
            $this->assertFalse($result);
        }
        
        // Test valid amounts
        $valid_amounts = [100, 1000.50, '500'];
        
        foreach ($valid_amounts as $amount) {
            $result = $this->CI->payment_model->validate_amount($amount);
            $this->assertTrue($result);
        }
    }
}
```

**File:** `tests/Security/SecurityTest.php`
```php
<?php
namespace Tests\Security;

use Tests\TestCase;

class SecurityTest extends TestCase
{
    public function test_sql_injection_protection()
    {
        $this->CI->load->model('flight_model');
        
        // Attempt SQL injection
        $malicious_input = "' OR '1'='1";
        
        $result = $this->CI->flight_model->search_by_destination($malicious_input);
        
        // Should return empty or proper results, not all records
        if (!empty($result)) {
            // Check query was escaped
            $query = $this->CI->db->last_query();
            $this->assertStringNotContainsString("OR '1'='1'", $query);
        }
    }
    
    public function test_xss_protection_in_output()
    {
        // Create booking with XSS attempt
        $xss_payload = '<script>alert("XSS")</script>';
        
        $this->CI->load->model('booking_model');
        $booking_id = $this->CI->booking_model->create_booking(array(
            'passenger_name' => $xss_payload
        ));
        
        // Load booking view
        $this->CI->load->view('booking/view', array('booking_id' => $booking_id));
        $output = $this->CI->output->get_output();
        
        // XSS should be escaped
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }
    
    public function test_csrf_token_validation()
    {
        // Try to submit form without CSRF token
        $_POST = array(
            'amount' => 1000,
            'booking_id' => 123
            // No CSRF token
        );
        
        $this->CI->security->csrf_verify();
        
        // Should fail (show_error called)
        $this->expectException(\Exception::class);
    }
}
```

#### Run Test Suite:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite="Security Tests"

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

#### Acceptance Criteria:
- ✅ 50+ tests covering critical paths
- ✅ All security tests pass
- ✅ Payment flow tests pass (sandbox)
- ✅ Booking flow tests pass
- ✅ Code coverage >70%

---

## Summary Checklist

### Phase 1: Emergency Security (Week 1-2)
- [ ] Environment-based configuration implemented
- [ ] All debug statements removed
- [ ] Production error display disabled
- [ ] Password hashing migrated to bcrypt/Argon2
- [ ] CSRF protection enabled

### Phase 2: Security Hardening (Week 3-4)
- [ ] SQL injection vulnerabilities fixed
- [ ] XSS output encoding implemented
- [ ] Security headers deployed
- [ ] API credentials secured

### Phase 3: Testing (Week 5-7)
- [ ] PHPUnit installed and configured
- [ ] 50+ tests written
- [ ] Critical path tests passing
- [ ] Security tests passing
- [ ] 70%+ code coverage achieved

### Deployment Checklist
- [ ] All changes tested in staging environment
- [ ] Database migrations run successfully
- [ ] `.env` file configured on production
- [ ] All credentials rotated
- [ ] Monitoring and alerting configured
- [ ] Team trained on security best practices
- [ ] Incident response plan documented

---

## Support & Resources

**Security Resources:**
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PCI DSS: https://www.pcisecuritystandards.org/
- PHP Security Best Practices: https://www.php.net/manual/en/security.php

**Testing Resources:**
- PHPUnit Documentation: https://phpunit.de/
- Codeception: https://codeception.com/

**Monitoring Tools:**
- Sentry (error tracking): https://sentry.io/
- Datadog (APM): https://www.datadoghq.com/
- New Relic: https://newrelic.com/

---

**Document Version:** 1.0  
**Last Updated:** February 26, 2026  
**Next Review:** After Phase 1 completion
