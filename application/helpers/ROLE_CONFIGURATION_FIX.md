# "User Role Not Configured" - Error Explanation & Fix Guide

## Why This Error Happens

The error **"Your user account exists but has no role assigned"** occurs when:

1. **User account exists** in `user_account_tbl` ✓
2. **BUT** user does NOT exist in ANY of the role-specific tables:
   - `admin_tbl` ✗
   - `staff_tbl` ✗
   - `reseller_tbl` ✗
   - `customer_tbl` ✗

### Common Causes

| Cause | Description | Solution |
|-------|-------------|----------|
| **Incomplete Registration** | User created account but didn't complete role selection | Recreate user in correct role table |
| **Manual User Creation** | Admin created user in `user_account_tbl` only, forgot role table | Insert user into appropriate role table |
| **Database Migration Error** | Data was corrupted or lost during migration | Restore or manually fix role assignments |
| **API Integration Bug** | Third-party script created user without role | Run diagnostic to identify & fix |
| **Manual User Deletion** | Role record was deleted but account remains | Re-insert into role table |

## Diagnostic URLs

Access these to identify and fix the issue:

### 1. Check Orphaned Users (All users without roles)
```
http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/check_orphaned_users
```
**Output:** JSON list of all users without assigned roles

### 2. Check Specific User
```
http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/check_user_role?email=user@example.com
```
**Output:** Shows which role tables the user exists in

### 3. View Database Schema
```
http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/database_schema
```
**Output:** Shows table structures and row counts

## Fix Options

### Option 1: Use Diagnostic API (Easiest)

POST to `http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/fix_user`

**Required Fields:**
```json
{
    "user_account_id": 5,
    "role": "customer",
    "first_name": "John",
    "last_name": "Doe"
}
```

**Example using cURL:**
```bash
curl -X POST http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/fix_user \
  -d "user_account_id=5" \
  -d "role=customer" \
  -d "first_name=John" \
  -d "last_name=Doe"
```

**Roles:** `admin`, `staff`, `reseller`, `customer`

### Option 2: Manual SQL Fix

#### For Customer
```sql
INSERT INTO customer_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (
    5,                              -- Use the user_account_id
    'John',                        -- First name
    'Doe',                         -- Last name
    'john@example.com',            -- Email from user_account_tbl
    'active',                      -- Status (active, inactive)
    NOW()                          -- Created at
);
```

#### For Admin
```sql
INSERT INTO admin_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (5, 'John', 'Doe', 'john@example.com', 'active', NOW());
```

#### For Staff
```sql
INSERT INTO staff_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (5, 'John', 'Doe', 'john@example.com', 'active', NOW());
```

#### For Reseller
```sql
INSERT INTO reseller_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (5, 'John', 'Doe', 'john@example.com', 'pending', NOW());
-- Note: Reseller defaults to 'pending' - admin must approve
```

## Step-by-Step Fix Process

### Step 1: Identify the Problem
```bash
# Visit this URL to see all users without roles
http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/check_orphaned_users
```

Take note of the `user_account_id` of the affected user.

### Step 2: Check User Details
```bash
# Visit this URL with user's email
http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/check_user_role?email=affected@user.com
```

This shows which role tables the user exists in (or doesn't exist in).

### Step 3: Determine the Correct Role

Decide what role the user should have:
- **Customer** - For shoppers
- **Reseller** - For resellers (will need admin approval)
- **Staff** - For support staff
- **Admin** - For administrators

### Step 4: Fix Using Diagnostic API

Use the diagnostic fix endpoint (easiest method):

```bash
curl -X POST http://localhost/Dropshipping_System/DropSell/index.php/diagnostic/fix_user \
  -d "user_account_id=5" \
  -d "role=customer" \
  -d "first_name=John" \
  -d "last_name=Doe"
```

**Or** run the SQL INSERT manually in phpMyAdmin/MySQL.

### Step 5: Test Login
Try logging in with the user's email to verify the role is now assigned.

## Prevention: Correct User Creation Process

### When Creating New Users, ALWAYS Do Both:

#### 1. Create in user_account_tbl
```sql
INSERT INTO user_account_tbl (email, password, status, created_at)
VALUES ('newuser@example.com', bcrypt('password123'), 'active', NOW());

-- Get the user_account_id
SELECT user_account_id FROM user_account_tbl WHERE email = 'newuser@example.com';
```

#### 2. Create in Role-Specific Table
```sql
-- If customer:
INSERT INTO customer_tbl (user_account_id, first_name, last_name, email, status, created_at)
VALUES (6, 'New', 'User', 'newuser@example.com', 'active', NOW());
```

## Troubleshooting Error Codes

If you see: **`ERR_ROLE_NOT_FOUND_5`**

This means:
- User account ID is `5`
- No role found for this user
- Check `diagnostic/check_user_role?email=user@example.com` to see what's missing

## Database Integrity Check

To audit all users and their roles:

```sql
-- Show all users with their roles
SELECT 
    u.user_account_id,
    u.email,
    u.status,
    CASE 
        WHEN a.admin_id IS NOT NULL THEN 'admin'
        WHEN s.staff_id IS NOT NULL THEN 'staff'
        WHEN r.reseller_id IS NOT NULL THEN 'reseller'
        WHEN c.customer_id IS NOT NULL THEN 'customer'
        ELSE 'NO ROLE ASSIGNED' 
    END as role,
    u.created_at
FROM user_account_tbl u
LEFT JOIN admin_tbl a ON u.user_account_id = a.user_account_id
LEFT JOIN staff_tbl s ON u.user_account_id = s.user_account_id
LEFT JOIN reseller_tbl r ON u.user_account_id = r.user_account_id
LEFT JOIN customer_tbl c ON u.user_account_id = c.user_account_id
ORDER BY u.user_account_id;
```

Look for any rows with `role = 'NO ROLE ASSIGNED'` - these need fixing.

## Quick Fix Script

Run this SQL to create a view for easy identification:

```sql
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
```

Then query it:
```sql
SELECT * FROM user_role_audit WHERE role = 'NO ROLE';
```

## Support Contact

If you need help after trying these steps:
1. Note your error code (e.g., `ERR_ROLE_NOT_FOUND_5`)
2. Visit `/diagnostic/check_user_role?email=your@email.com` and save the JSON output
3. Contact support with this information
