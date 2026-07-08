# Role-Based Login Guide

## Overview
The DropSell system uses **automatic role-based login**. When a user logs in, the system:

1. **Verifies** login credentials from `user_account_tbl`
2. **Detects role** by checking which role-specific table the user exists in
3. **Retrieves role-specific ID** (admin_id, staff_id, reseller_id, or customer_id)
4. **Sets session data** with role information
5. **Auto-redirects** to appropriate dashboard

## User Roles

### 1. **ADMIN** (Role: `admin`)
- **Table:** `admin_tbl`
- **User ID Column:** `admin_id`
- **Dashboard:** `admin/dashboard`
- **Privileges:** Full system access

### 2. **STAFF** (Role: `staff`)
- **Table:** `staff_tbl`
- **User ID Column:** `staff_id`
- **Dashboard:** `staff/dashboard`
- **Privileges:** Staff-level operations

### 3. **RESELLER** (Role: `reseller`)
- **Table:** `reseller_tbl`
- **User ID Column:** `reseller_id`
- **Dashboard:** `reseller/dashboard`
- **Status Options:** `active`, `pending`, `rejected`
- **Note:** Pending/rejected resellers cannot log in

### 4. **CUSTOMER** (Role: `customer`)
- **Table:** `customer_tbl`
- **User ID Column:** `customer_id`
- **Dashboard:** `customer/shop`
- **Privileges:** Shop browsing, orders, refunds

## Login Flow

```
User submits email + password
    ↓
Verify in user_account_tbl
    ↓
Check if account is ACTIVE
    ↓
Verify password (bcrypt)
    ↓
Determine role from role-specific table
    ↓
Validate role is in allowed_roles config
    ↓
Get role-specific ID
    ↓
Check role status (especially for resellers)
    ↓
Regenerate session ID (security)
    ↓
Set session data with role and user info
    ↓
Log activity to activity_log_model
    ↓
Auto-redirect to role dashboard
```

## Database Structure

### user_account_tbl
```sql
- user_account_id (PK)
- email (UNIQUE)
- password (bcrypt hash)
- status (active, inactive)
- last_login
- created_at
```

### admin_tbl / staff_tbl / reseller_tbl / customer_tbl
```sql
- [role_id] (admin_id, staff_id, etc.) (PK)
- user_account_id (FK)
- first_name
- last_name
- email
- status
- profile_image
- created_at
```

## Session Variables Set After Login

```php
[
    'user_type'       => 'admin|staff|reseller|customer',
    'user_id'         => [role-specific ID],
    'user_account_id' => [user account ID],
    'email'           => [user email],
    'first_name'      => [first name],
    'last_name'       => [last name],
    'full_name'       => [full name],
    'profile_image'   => [image URL],
    'logged_in_at'    => [timestamp],
    'is_logged_in'    => true
]
```

## Common Issues & Solutions

### Issue: "User role not found"
**Cause:** User doesn't exist in any role-specific table
**Solution:** Ensure user account is linked to a role table (admin_tbl, staff_tbl, reseller_tbl, or customer_tbl)

### Issue: "User profile not found"
**Cause:** User exists in role table but ID retrieval failed
**Solution:** Verify the role-specific ID column exists and has a value

### Issue: "Reseller cannot login - pending approval"
**Cause:** Reseller account status is 'pending'
**Solution:** Admin must approve the reseller application

### Issue: "Invalid user role. Please contact support"
**Cause:** Role is not in `allowed_roles` config
**Solution:** Check `application/config/dropsell.php` for `allowed_roles` array

## Testing Role-Based Login

### Create Test User for Each Role

#### Admin User
```php
// 1. Create in user_account_tbl
INSERT INTO user_account_tbl (email, password, status, created_at)
VALUES ('admin@test.com', bcrypt('password123'), 'active', NOW());

// 2. Get user_account_id and create in admin_tbl
INSERT INTO admin_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (1, 'Admin', 'User', 'admin@test.com', 'active', NOW());
```

#### Staff User
```php
INSERT INTO user_account_tbl (email, password, status, created_at)
VALUES ('staff@test.com', bcrypt('password123'), 'active', NOW());

INSERT INTO staff_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (2, 'Staff', 'User', 'staff@test.com', 'active', NOW());
```

#### Reseller User
```php
INSERT INTO user_account_tbl (email, password, status, created_at)
VALUES ('reseller@test.com', bcrypt('password123'), 'active', NOW());

INSERT INTO reseller_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (3, 'Reseller', 'User', 'reseller@test.com', 'active', NOW());
```

#### Customer User
```php
INSERT INTO user_account_tbl (email, password, status, created_at)
VALUES ('customer@test.com', bcrypt('password123'), 'active', NOW());

INSERT INTO customer_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (4, 'Customer', 'User', 'customer@test.com', 'active', NOW());
```

### Login URLs
- **Login Form:** `http://localhost/Dropshipping_System/DropSell/index.php/auth/login`
- **Process Login:** `http://localhost/Dropshipping_System/DropSell/index.php/auth/do_login` (POST only)
- **Logout:** `http://localhost/Dropshipping_System/DropSell/index.php/auth/logout`

## Security Features

1. **Password Hashing:** Bcrypt with PASSWORD_BCRYPT
2. **Session Regeneration:** New session ID generated on successful login
3. **HTTP-only Cookies:** Prevents JavaScript access to session cookies
4. **SameSite Cookies:** Set to 'Lax' to prevent CSRF
5. **Activity Logging:** All login/logout events are logged
6. **IP Tracking:** Failed login attempts logged with IP address
7. **Account Status Check:** Only active accounts can log in
8. **Role Validation:** Only roles in allowed_roles config are accepted

## Code References

### Auth Controller
- **File:** `application/controllers/Auth.php`
- **Key Method:** `do_login()` - Handles login processing

### User Model
- **File:** `application/models/User_model.php`
- **Key Methods:**
  - `verify_login()` - Validates credentials
  - `get_user_role()` - Determines user role
  - `get_user_role_with_profile()` - Gets role with detailed info
  - `get_user_profile()` - Retrieves role-specific profile

### Base Controller
- **File:** `application/core/Authenticated_Controller.php`
- **Purpose:** Enforces login requirement for protected pages
- **Key Methods:**
  - `require_role()` - Requires specific role
  - `require_any_role()` - Requires one of multiple roles

## Configuration

**File:** `application/config/dropsell.php`

```php
// Allowed roles
$config['allowed_roles'] = array(
    'admin',
    'staff',
    'reseller',
    'customer'
);

// Database tables
$config['db_tables'] = array(
    'users' => 'user_account_tbl',
    'admins' => 'admin_tbl',
    'staff' => 'staff_tbl',
    'resellers' => 'reseller_tbl',
    'customers' => 'customer_tbl',
    // ... other tables
);
```

**File:** `application/config/config.php`

```php
// Session configuration
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_samesite'] = 'Lax';
$config['sess_expiration'] = 7200;  // 2 hours
$config['sess_save_path'] = APPPATH . 'cache/sessions';

// Cookie configuration
$config['cookie_httponly'] = TRUE;
$config['cookie_samesite'] = 'Lax';
```
