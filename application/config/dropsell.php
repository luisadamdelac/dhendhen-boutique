<?php
/**
 * DropSell System Configuration
 * Database constants and system settings
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================================================
// ENVIRONMENT & DEBUG
// ============================================================================
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}
define('DEBUG_MODE', TRUE);

// ============================================================================
// USER ROLES
// ============================================================================
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_RESELLER', 'reseller');
define('ROLE_CUSTOMER', 'customer');

$config['allowed_roles'] = array(
    ROLE_ADMIN,
    ROLE_STAFF,
    ROLE_RESELLER,
    ROLE_CUSTOMER
);

// ============================================================================
// LOGIN BRUTE-FORCE PROTECTION (strictly per-account — see Auth::_check_brute_force_limit)
// ============================================================================
$config['max_login_attempts']    = 5;
$config['login_lockout_minutes'] = 15;

// ============================================================================
// DATABASE TABLES - NAMING CONVENTION
// ============================================================================
// All tables follow naming: table_name_tbl or table_name
$config['db_tables'] = array(
    'users'                 => 'user_account_tbl',
    'admins'                => 'admin_tbl',
    'staff'                 => 'staff_tbl',
    'resellers'             => 'reseller_tbl',
    'customers'             => 'customer_tbl',
    'branches'              => 'branches',
    'products'              => 'product_tbl',
    'product_categories'    => 'product_category',
    'product_images'        => 'product_image',
    'product_reviews'       => 'product_reviews',
    'reseller_products'     => 'reseller_products',
    'reseller_applications' => 'reseller_application_tbl',
    'orders'                => 'order_tbl',
    'order_details'         => 'order_details',
    'carts'                 => 'carts',
    'cart_items'            => 'cart_items',
    'payments'              => 'payment_transaction_tbl',
    'commissions'           => 'commission_transaction_tbl',
    'withdrawals'           => 'withdrawal_tbl',
    'refunds'               => 'refund_request_tbl',
    'notifications'         => 'notifications',
    'activity_logs'         => 'activity_logs',
    'reports'               => 'reports'
);

// ============================================================================
// USER STATUS
// ============================================================================
$config['user_status'] = array(
    'active'   => 'active',
    'inactive' => 'inactive'
);

$config['user_verified'] = array(
    'verified'     => 1,
    'not_verified' => 0
);

// ============================================================================
// ORDER STATUS
// ============================================================================
$config['order_status'] = array(
    'pending'        => 'pending',
    'paid'           => 'paid',
    'processing'     => 'processing',
    'to_ship'        => 'to_ship',
    'shipped'        => 'shipped',
    'delivered'      => 'delivered',
    'return_refund'  => 'return_refund',
    'cancelled'      => 'cancelled'
);

// ============================================================================
// PAYMENT STATUS
// ============================================================================
$config['payment_status'] = array(
    'pending'   => 'pending',
    'completed' => 'completed',
    'failed'    => 'failed',
    'refunded'  => 'refunded'
);

$config['payment_method'] = array(
    'gcash' => 'GCash'
);

// ============================================================================
// REFUND STATUS
// ============================================================================
$config['refund_status'] = array(
    'pending'   => 'pending',
    'approved'  => 'approved',
    'rejected'  => 'rejected',
    'completed' => 'completed',
    'cancelled' => 'cancelled'
);

// ============================================================================
// WITHDRAWAL STATUS
// ============================================================================
$config['withdrawal_status'] = array(
    'pending'   => 'pending',
    'approved'  => 'approved',
    'completed' => 'completed',
    'rejected'  => 'rejected',
    'cancelled' => 'cancelled'
);

// ============================================================================
// COMMISSION STATUS
// ============================================================================
$config['commission_status'] = array(
    'pending'   => 'pending',
    'released'  => 'released',
    'withdrawn' => 'withdrawn'
);

// ============================================================================
// RESELLER APPLICATION STATUS
// ============================================================================
$config['reseller_application_status'] = array(
    'pending'  => 'pending',
    'approved' => 'approved',
    'rejected' => 'rejected'
);

// ============================================================================
// PRODUCT REVIEW STATUS
// ============================================================================
$config['product_review_status'] = array(
    'pending'  => 'pending',
    'approved' => 'approved',
    'hidden'   => 'hidden'
);

// ============================================================================
// PRODUCT STATUS
// ============================================================================
$config['product_status'] = array(
    'available'     => 'available',
    'not_available' => 'not_available'
);

// ============================================================================
// DELIVERY METHODS
// ============================================================================
$config['delivery_methods'] = array(
    'pickup'  => 'Pickup',
    'pasabay' => 'Pasabay'
);

// ============================================================================
// NOTIFICATION TYPES
// ============================================================================
$config['notification_type'] = array(
    'order'      => 'order',
    'payment'    => 'payment',
    'withdrawal' => 'withdrawal',
    'refund'     => 'refund',
    'system'     => 'system'
);

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================
$config['session_timeout'] = 3600; // 1 hour
$config['session_keys'] = array(
    'user_type'      => 'user_type',      // admin, staff, reseller, customer
    'user_id'        => 'user_id',        // admin_id, staff_id, reseller_id, customer_id
    'user_account_id'=> 'user_account_id', // From user_account_tbl
    'email'          => 'email',
    'first_name'     => 'first_name',
    'last_name'      => 'last_name',
    'full_name'      => 'full_name',
    'profile_image'  => 'profile_image',
    'logged_in_at'   => 'logged_in_at'
);

// ============================================================================
// PAGINATION
// ============================================================================
$config['pagination_per_page'] = 15;
$config['pagination_links_per_page'] = 5;

// ============================================================================
// FILE UPLOAD
// ============================================================================
$config['upload_path'] = 'uploads/';
$config['max_file_size'] = 5242880; // 5MB
$config['allowed_image_types'] = array('jpg', 'jpeg', 'png', 'gif');
$config['allowed_document_types'] = array('pdf', 'doc', 'docx', 'xls', 'xlsx');

// ============================================================================
// PASSWORD REQUIREMENTS
// ============================================================================
$config['password_min_length'] = 8;
$config['password_require_uppercase'] = TRUE;
$config['password_require_numbers'] = TRUE;
$config['password_require_special'] = FALSE;

// ============================================================================
// EMAIL CONFIGURATION
// ============================================================================
$config['email_from'] = 'noreply@dropsell.com';
$config['email_from_name'] = 'DropSell System';

/* End of file dropsell.php */
/* Location: ./application/config/dropsell.php */
