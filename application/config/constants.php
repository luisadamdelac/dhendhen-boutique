<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

defined('PAGINATION_LIMIT')    OR define('PAGINATION_LIMIT', 15); // default pagination page size

// Currency symbol — referenced by several staff views (previously undefined,
// which fataled with "Undefined constant CURRENCY" under PHP 8).
defined('CURRENCY') OR define('CURRENCY', '₱');

// Base URL constants for views and assets
if (!defined('BASE_URL')) {
    $https = $_SERVER['HTTPS'] ?? null;
    $scheme = ($https !== null && strtolower((string)$https) !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if ($host === '[::1]' || $host === '::1') {
        $host = 'localhost';
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $path = rtrim(str_replace('\\', '/', dirname($script)), '/');
    // BASE_URL deliberately omits index.php — it's used for both page links
    // and static asset paths (CSS/JS/images), and assets must never be
    // routed through index.php. application/../.htaccess rewrites clean
    // page URLs to index.php/... while leaving real files untouched.
    $baseUrl = $scheme . '://' . $host . ($path ? $path . '/' : '/');
    define('BASE_URL', $baseUrl);
}

defined('URLROOT') OR define('URLROOT', BASE_URL);

/*
|--------------------------------------------------------------------------
| Database table constants
|--------------------------------------------------------------------------
|
| Admin controllers/models reference constants like PRODUCT_TABLE directly.
| DropSell stores table mappings in application/config/dropsell.php
| as $config['db_tables'].
|
| This file can be loaded very early during boot, so we MUST NOT use
| get_instance()/CI classes here.
*/
if (!function_exists('define_db_table_constants')) {
    function define_db_table_constants(): void
    {
        // Explicit mapping from application/config/dropsell.php (db_tables).
        // This avoids fragile text parsing and guarantees constants exist.
        // (Also avoids executing dropsell.php which defines DEBUG_MODE/ROLE_*.)

        $map = [
            'USER_ACCOUNT_TABLE' => 'user_account_tbl',
            'ADMIN_TABLE'        => 'admin_tbl',
            'STAFF_TABLE'        => 'staff_tbl',
            'RESELLER_TABLE'     => 'reseller_tbl',
            'CUSTOMER_TABLE'    => 'customer_tbl',
            'CUSTOMER_ADDRESS_TABLE' => 'customer_address_tbl',
            'BRANCHES_TABLE'    => 'branches',

            'PRODUCT_TABLE'         => 'product_tbl',
            'CATEGORY_TABLE'        => 'product_category',
            'PRODUCT_IMAGE_TABLE'  => 'product_image',
            'PRODUCT_VARIATION_TABLE' => 'product_variation_tbl',
            'PRODUCT_REVIEWS_TABLE'=> 'product_reviews',
            'RESELLER_PRODUCTS_TABLE'=> 'reseller_products',
            'RESELLER_APPLICATION_TABLE'=> 'reseller_application_tbl',

            'ORDER_TABLE'         => 'order_tbl',
            'ORDER_DETAILS_TABLE'=> 'order_details',

            'CARTS_TABLE'         => 'carts',
            'CART_ITEMS_TABLE'    => 'cart_items',

            'PAYMENTS_TABLE'      => 'payment_transaction_tbl',
            'COMMISSIONS_TABLE'   => 'commission_transaction_tbl',

            'WITHDRAWAL_TABLE'   => 'withdrawal_tbl',
            'REFUND_REQUEST_TABLE'=> 'refund_request_tbl',

            'NOTIFICATIONS_TABLE' => 'notifications',
            'ACTIVITY_LOGS_TABLE' => 'activity_logs',
            'REPORTS_TABLE'       => 'reports',

            // Inventory (true per-branch, per-product stock ledger)
            'INVENTORY_BATCH_TABLE'        => 'inventory_batches',
            'INVENTORY_MOVEMENTS_TABLE'    => 'inventory_movements',
            'INVENTORY_RESERVATIONS_TABLE' => 'inventory_reservations',

            // Settings
            'SETTINGS_TABLE'      => 'settings_tbl',
        ];

        foreach ($map as $const => $tableName) {
            if (!defined($const)) {
                define($const, $tableName);
            }
        }
    }
}

// Define constants as early as possible (without CI dependencies)
define_db_table_constants();

