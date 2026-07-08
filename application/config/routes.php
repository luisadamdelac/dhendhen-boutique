<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
            | class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Landing';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Landing Page
$route['landing'] = 'Landing/index';
$route['landing/(:any)'] = 'Landing/$1';

// Customer page aliases used by the existing customer views
$route['shop'] = 'customer/Shop';
$route['shop/(:any)/(:any)'] = 'customer/Shop/$1/$2';
$route['shop/(:any)'] = 'customer/Shop/$1';
$route['orders'] = 'customer/Orders';
$route['orders/(:any)/(:any)'] = 'customer/Orders/$1/$2';
$route['orders/(:any)'] = 'customer/Orders/$1';
$route['account'] = 'customer/Account';
$route['account/(:any)'] = 'customer/Account/$1';
$route['settings'] = 'customer/Settings';
$route['addresses'] = 'customer/Addresses';
$route['addresses/(:any)/(:any)'] = 'customer/Addresses/$1/$2';
$route['addresses/(:any)'] = 'customer/Addresses/$1';
$route['refund'] = 'customer/Refunds';
$route['refund/(:any)'] = 'customer/Refunds/$1';
$route['cart'] = 'customer/Cart';
$route['cart/(:any)/(:any)'] = 'customer/Cart/$1/$2';
$route['cart/(:any)'] = 'customer/Cart/$1';
$route['checkout'] = 'customer/Checkout/index';
$route['checkout/(:any)'] = 'customer/Checkout/$1';

// Authentication Routes
$route['login'] = 'auth/login';
$route['register'] = 'auth/register';
$route['register-reseller'] = 'auth/register_reseller';
$route['forgot-password'] = 'auth/forgot_password';
$route['auth/login'] = 'auth/login';
$route['auth/do_login'] = 'auth/do_login';
$route['auth/register'] = 'auth/register';
$route['auth/do_register'] = 'auth/do_register';
$route['auth/register-reseller'] = 'auth/register_reseller';
$route['auth/do-register-reseller'] = 'auth/do_register_reseller';
$route['auth/logout'] = 'auth/logout';
$route['auth/forgot-password'] = 'auth/forgot_password';
$route['auth/(:any)'] = 'auth/$1';

 // Admin Routes
$route['admin/dashboard'] = 'admin/Dashboard';
$route['admin/staff'] = 'admin/Staff';
$route['admin/staff/(:any)'] = 'admin/Staff/$1';
$route['admin/reseller'] = 'admin/Reseller';
$route['admin/reseller/pending-registrations'] = 'admin/Reseller/pending_registrations';
$route['admin/reseller/approve-registration/(:num)'] = 'admin/Reseller/approve_registration/$1';
$route['admin/reseller/reject-registration/(:num)'] = 'admin/Reseller/reject_registration/$1';
$route['admin/reseller/(:any)'] = 'admin/Reseller/$1';
$route['admin/product'] = 'admin/Product';
$route['admin/product/(:any)'] = 'admin/Product/$1';

// Alias: Inventory pages live under admin/Product controller
$route['admin/inventory'] = 'admin/Product/index';
$route['admin/inventory/(:any)'] = 'admin/Product/$1';

// Branches — index redirects to inventory tab; save/delete kept on Branches controller
$route['admin/branches'] = 'admin/Branches/index';
$route['admin/branches/save'] = 'admin/Branches/save';
$route['admin/branches/delete/(:num)'] = 'admin/Branches/delete/$1';
$route['admin/branches/(:any)'] = 'admin/Branches/$1';
$route['admin/order'] = 'admin/Order';
$route['admin/order/(:any)'] = 'admin/Order/$1';
$route['admin/refund'] = 'admin/Refund';
$route['admin/refund/(:any)'] = 'admin/Refund/$1';
$route['admin/withdrawal'] = 'admin/Withdrawal';
$route['admin/withdrawal/(:any)'] = 'admin/Withdrawal/$1';
$route['admin/commission'] = 'admin/Commission';
$route['admin/commission/(:any)'] = 'admin/Commission/$1';
$route['admin/profile'] = 'admin/Profile';
$route['admin/profile/(:any)'] = 'admin/Profile/$1';
$route['admin/settings'] = 'admin/Settings';
$route['admin/settings/(:any)'] = 'admin/Settings/$1';
$route['admin/notification'] = 'admin/Notification';
$route['admin/notification/(:any)/(:any)'] = 'admin/Notification/$1/$2';
$route['admin/notification/(:any)'] = 'admin/Notification/$1';

// Staff Routes
$route['staff/dashboard'] = 'staff/Dashboard';
$route['staff/profile'] = 'staff/Profile';
$route['staff/profile/(:any)'] = 'staff/Profile/$1';
$route['staff/inventory'] = 'staff/Inventory';
$route['staff/inventory/(:any)'] = 'staff/Inventory/$1';
$route['staff/orders'] = 'staff/Orders';
$route['staff/orders/(:any)'] = 'staff/Orders/$1';

// Reseller Routes
$route['reseller/dashboard'] = 'reseller/Dashboard';
$route['reseller/profile'] = 'reseller/Profile';
$route['reseller/profile/(:any)'] = 'reseller/Profile/$1';
$route['reseller/sales'] = 'reseller/Sales';
$route['reseller/sales/(:any)'] = 'reseller/Sales/$1';
$route['reseller/inventory'] = 'reseller/Inventory';
$route['reseller/inventory/(:any)'] = 'reseller/Inventory/$1';
$route['reseller/commission'] = 'reseller/Commissions';
$route['reseller/commission/(:any)'] = 'reseller/Commissions/$1';
$route['reseller/withdrawals'] = 'reseller/Withdrawals';
$route['reseller/withdrawals/(:any)'] = 'reseller/Withdrawals/$1';
$route['reseller/orders'] = 'reseller/Orders';
$route['reseller/orders/(:any)'] = 'reseller/Orders/$1';
$route['reseller/notifications'] = 'reseller/Notifications';
$route['reseller/notifications/(:any)'] = 'reseller/Notifications/$1';

// Customer Routes
$route['customer/shop'] = 'customer/Shop';
$route['customer/orders'] = 'customer/Orders';
$route['customer/orders/(:any)'] = 'customer/Orders/$1';
$route['customer/account'] = 'customer/Account';
$route['customer/account/(:any)'] = 'customer/Account/$1';
$route['customer/refunds'] = 'customer/Refunds';
$route['customer/refunds/(:any)'] = 'customer/Refunds/$1';

// Generic Profile Route - Redirects based on user role
$route['profile'] = 'core/ProfileRedirect';
$route['profile/(:any)'] = 'core/ProfileRedirect/$1';

// API Routes
$route['api/notifications'] = 'api/notifications';
$route['api/mark-notification-read'] = 'api/mark_notification_read';
$route['api/get-all-notifications'] = 'api/get_all_notifications';