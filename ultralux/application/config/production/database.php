<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'default';
$active_record = TRUE;

// Production — credentials injected via Azure App Service application settings.
// Set DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE in Azure Portal or via
// azure-provision.sh / GitHub Actions (never hardcode credentials here).
$db['default']['hostname'] = getenv('DB_HOSTNAME') ?: 'localhost';
$db['default']['username'] = getenv('DB_USERNAME') ?: '';
$db['default']['password'] = getenv('DB_PASSWORD') ?: '';
$db['default']['database'] = getenv('DB_DATABASE') ?: 'lar_ultralux';
$db['default']['db_debug'] = FALSE;

$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8mb4';
$db['default']['dbcollat'] = 'utf8mb4_unicode_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

/* End of file database.php */
/* Location: ./ultralux/application/config/production/database.php */
