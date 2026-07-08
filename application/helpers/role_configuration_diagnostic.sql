-- ==============================================================================
-- DropSell User Role Configuration Diagnostic & Fix Script
-- ==============================================================================
-- This script helps identify and fix users without role assignments
-- 
-- Usage:
-- 1. Run the diagnostic queries first to identify the problem
-- 2. Use the fix queries to repair the issue
-- ==============================================================================

-- ==============================================================================
-- DIAGNOSTIC QUERIES (Read-Only - Safe to Run)
-- ==============================================================================

-- 1. Find all users without role assignments
SELECT 
    u.user_account_id,
    u.email,
    u.status,
    'NO ROLE ASSIGNED' as issue,
    u.created_at,
    u.last_login
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
WHERE a.admin_id IS NULL 
  AND s.staff_id IS NULL 
  AND r.reseller_id IS NULL 
  AND c.customer_id IS NULL
ORDER BY u.user_account_id;

-- 2. User role summary (shows all users with their roles)
SELECT 
    u.user_account_id,
    u.email,
    u.status as account_status,
    CASE 
        WHEN a.admin_id IS NOT NULL THEN 'ADMIN'
        WHEN s.staff_id IS NOT NULL THEN 'STAFF'
        WHEN r.reseller_id IS NOT NULL THEN 'RESELLER'
        WHEN c.customer_id IS NOT NULL THEN 'CUSTOMER'
        ELSE 'NO ROLE ASSIGNED' 
    END as role,
    COALESCE(a.admin_id, s.staff_id, r.reseller_id, c.customer_id) as role_id,
    u.created_at,
    u.last_login
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
ORDER BY u.user_account_id;

-- 3. Count summary
SELECT 
    (SELECT COUNT(*) FROM user_account_tbl) as total_users,
    (SELECT COUNT(*) FROM admin_tbl) as admin_count,
    (SELECT COUNT(*) FROM staff_tbl) as staff_count,
    (SELECT COUNT(*) FROM reseller_tbl) as reseller_count,
    (SELECT COUNT(*) FROM customer_tbl) as customer_count,
    (
        SELECT COUNT(*) FROM user_account_tbl u
        LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
        LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
        LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
        LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
        WHERE a.admin_id IS NULL AND s.staff_id IS NULL AND r.reseller_id IS NULL AND c.customer_id IS NULL
    ) as orphaned_users;

-- 4. Check specific user by email
-- Replace 'user@example.com' with actual email
SELECT 
    u.user_account_id,
    u.email,
    u.status,
    u.created_at,
    u.last_login,
    CASE 
        WHEN a.admin_id IS NOT NULL THEN 'ADMIN (ID: ' || a.admin_id || ')'
        WHEN s.staff_id IS NOT NULL THEN 'STAFF (ID: ' || s.staff_id || ')'
        WHEN r.reseller_id IS NOT NULL THEN 'RESELLER (ID: ' || r.reseller_id || ', Status: ' || r.status || ')'
        WHEN c.customer_id IS NOT NULL THEN 'CUSTOMER (ID: ' || c.customer_id || ')'
        ELSE 'NO ROLE ASSIGNED' 
    END as role_info
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
WHERE u.email = 'user@example.com';

-- ==============================================================================
-- CREATE AUDIT VIEW (Helpful for ongoing monitoring)
-- ==============================================================================
CREATE OR REPLACE VIEW user_role_audit AS
SELECT 
    u.user_account_id,
    u.email,
    u.status as account_status,
    CASE 
        WHEN a.admin_id IS NOT NULL THEN 'admin'
        WHEN s.staff_id IS NOT NULL THEN 'staff'
        WHEN r.reseller_id IS NOT NULL THEN 'reseller'
        WHEN c.customer_id IS NOT NULL THEN 'customer'
        ELSE 'NO ROLE' 
    END as role,
    COALESCE(a.admin_id, s.staff_id, r.reseller_id, c.customer_id) as role_id,
    u.created_at,
    u.last_login
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id;

-- Query the view:
-- SELECT * FROM user_role_audit WHERE role = 'NO ROLE';

-- ==============================================================================
-- FIX QUERIES (Modify with caution - back up database first!)
-- ==============================================================================

-- OPTION 1: Assign a specific user to CUSTOMER role
-- BEFORE RUNNING: Replace values in square brackets with actual data
-- [USER_ACCOUNT_ID], [FIRST_NAME], [LAST_NAME], [EMAIL_ADDRESS]
/*
INSERT INTO customer_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (
    5,                           -- Replace with user_account_id
    'John',                      -- Replace with first_name
    'Doe',                       -- Replace with last_name
    'john@example.com',          -- Replace with email
    'active',                    -- Status: active or inactive
    NOW()
);
*/

-- OPTION 2: Assign a specific user to ADMIN role
/*
INSERT INTO admin_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (5, 'John', 'Doe', 'john@example.com', 'active', NOW());
*/

-- OPTION 3: Assign a specific user to STAFF role
/*
INSERT INTO staff_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (5, 'John', 'Doe', 'john@example.com', 'active', NOW());
*/

-- OPTION 4: Assign a specific user to RESELLER role (defaults to pending approval)
/*
INSERT INTO reseller_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (5, 'John', 'Doe', 'john@example.com', 'pending', NOW());
-- Note: Change status to 'active' if immediate access is needed
*/

-- BULK FIX: Assign all orphaned users to CUSTOMER role
-- WARNING: This assumes all orphaned users should be customers - verify first!
/*
INSERT INTO customer_tbl (user_account_id, first_name, last_name, email, status, created_at)
SELECT 
    u.user_account_id,
    'User',
    'Account',
    u.email,
    'active',
    NOW()
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
WHERE a.admin_id IS NULL 
  AND s.staff_id IS NULL 
  AND r.reseller_id IS NULL 
  AND c.customer_id IS NULL;
*/

-- ==============================================================================
-- VERIFICATION QUERY (Run after fix to verify)
-- ==============================================================================

-- Check if the fix worked
SELECT 
    u.user_account_id,
    u.email,
    CASE 
        WHEN a.admin_id IS NOT NULL THEN 'ADMIN'
        WHEN s.staff_id IS NOT NULL THEN 'STAFF'
        WHEN r.reseller_id IS NOT NULL THEN 'RESELLER'
        WHEN c.customer_id IS NOT NULL THEN 'CUSTOMER'
        ELSE 'STILL NO ROLE' 
    END as current_role
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
WHERE u.user_account_id IN (5);  -- Replace 5 with your user_account_id

-- ==============================================================================
-- DATA INTEGRITY CHECKS
-- ==============================================================================

-- Check for duplicate user accounts with same email
SELECT email, COUNT(*) as count
FROM user_account_tbl
GROUP BY email
HAVING COUNT(*) > 1;

-- Check for users in multiple role tables (shouldn't happen)
SELECT u.user_account_id, u.email,
    (SELECT COUNT(*) FROM admin_tbl WHERE user_account_id = u.user_account_id) +
    (SELECT COUNT(*) FROM staff_tbl WHERE user_account_id = u.user_account_id) +
    (SELECT COUNT(*) FROM reseller_tbl WHERE user_account_id = u.user_account_id) +
    (SELECT COUNT(*) FROM customer_tbl WHERE user_account_id = u.user_account_id) as role_count
FROM user_account_tbl u
HAVING role_count > 1;

-- Find inactive users
SELECT user_account_id, email, status, last_login
FROM user_account_tbl
WHERE status != 'active'
ORDER BY created_at DESC;

-- ==============================================================================
-- END OF SCRIPT
-- ==============================================================================
