<?php
/**
 * User Model - Unified Authentication Model
 * Handles all user types: Admin, Staff, Reseller, Customer
 * 
 * Uses user_account_tbl as the primary authentication table
 * Links to role-specific tables: admin_tbl, staff_tbl, reseller_tbl, customer_tbl
 */

class User_model extends CI_Model {
    
    protected $users_table = 'user_account_tbl';
    protected $admins_table = 'admin_tbl';
    protected $staff_table = 'staff_tbl';
    protected $resellers_table = 'reseller_tbl';
    protected $customers_table = 'customer_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->config('dropsell');
    }

    /**
     * Get user by email
     * 
     * @param string $email User email address
     * @return array|null User record with all fields
     */
    public function get_by_email($email) {
        $query = $this->db->where('email', $email)
                         ->get($this->users_table);
        
        return $query->row_array();
    }

    /**
     * Get user by ID
     * 
     * @param int $user_account_id User account ID
     * @return array|null User record
     */
    public function get_by_id($user_account_id) {
        $query = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->users_table);
        
        return $query->row_array();
    }

    /**
     * Verify user login credentials
     * 
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return array|false User data if valid, false otherwise
     */
    public function verify_login($email, $password) {
        $user = $this->get_by_email($email);
        
        if (!$user) {
            return FALSE;
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            return FALSE;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return FALSE;
        }
        
        // Update last login
        $this->db->where('user_account_id', $user['user_account_id'])
                ->update($this->users_table, array('last_login' => date('Y-m-d H:i:s')));
        
        return $user;
    }

    /**
     * Determine user role by checking role-specific tables
     * 
     * @param int $user_account_id User account ID
     * @return string|false User role (admin, staff, reseller, customer) or FALSE
     */
    public function get_user_role($user_account_id) {
        // Check admin
        $admin = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->admins_table)
                         ->row_array();
        if ($admin) {
            return ROLE_ADMIN;
        }
        
        // Check staff
        $staff = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->staff_table)
                         ->row_array();
        if ($staff) {
            return ROLE_STAFF;
        }
        
        // Check reseller
        $reseller = $this->db->where('user_account_id', $user_account_id)
                            ->get($this->resellers_table)
                            ->row_array();
        if ($reseller) {
            return ROLE_RESELLER;
        }
        
        // Check customer
        $customer = $this->db->where('user_account_id', $user_account_id)
                            ->get($this->customers_table)
                            ->row_array();
        if ($customer) {
            return ROLE_CUSTOMER;
        }
        
        return FALSE;
    }

    /**
     * Get user role with detailed information
     * Returns role and all role-specific data for validation
     * 
     * @param int $user_account_id User account ID
     * @return array|false Array with 'role' and 'profile' or FALSE
     */
    public function get_user_role_with_profile($user_account_id) {
        // Check admin
        $admin = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->admins_table)
                         ->row_array();
        if ($admin) {
            return array(
                'role' => ROLE_ADMIN,
                'profile' => $admin,
                'role_id' => $admin['admin_id'] ?? NULL,
                'status' => $admin['status'] ?? 'active'
            );
        }
        
        // Check staff
        $staff = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->staff_table)
                         ->row_array();
        if ($staff) {
            return array(
                'role' => ROLE_STAFF,
                'profile' => $staff,
                'role_id' => $staff['staff_id'] ?? NULL,
                'status' => $staff['status'] ?? 'active'
            );
        }
        
        // Check reseller
        $reseller = $this->db->where('user_account_id', $user_account_id)
                            ->get($this->resellers_table)
                            ->row_array();
        if ($reseller) {
            return array(
                'role' => ROLE_RESELLER,
                'profile' => $reseller,
                'role_id' => $reseller['reseller_id'] ?? NULL,
                'status' => $reseller['status'] ?? 'pending'
            );
        }
        
        // Check customer
        $customer = $this->db->where('user_account_id', $user_account_id)
                            ->get($this->customers_table)
                            ->row_array();
        if ($customer) {
            return array(
                'role' => ROLE_CUSTOMER,
                'profile' => $customer,
                'role_id' => $customer['customer_id'] ?? NULL,
                'status' => $customer['status'] ?? 'active'
            );
        }
        
        return FALSE;
    }

    /**
     * Get complete user data with role-specific profile
     * 
     * @param int $user_account_id User account ID
     * @return array User data merged with profile data
     */
    public function get_complete_user($user_account_id) {
        $user = $this->get_by_id($user_account_id);
        
        if (!$user) {
            return FALSE;
        }
        
        $role = $this->get_user_role($user_account_id);
        $user['role'] = $role;
        
        // Get role-specific profile data
        $profile = $this->get_user_profile($user_account_id, $role);
        
        if ($profile) {
            $user = array_merge($user, $profile);
        }
        
        return $user;
    }

    /**
     * Get user profile based on role
     * 
     * @param int $user_account_id User account ID
     * @param string $role User role
     * @return array|false Profile data
     */
    public function get_user_profile($user_account_id, $role) {
        switch ($role) {
            case ROLE_ADMIN:
                return $this->db->where('user_account_id', $user_account_id)
                               ->get($this->admins_table)
                               ->row_array();
            
            case ROLE_STAFF:
                return $this->db->where('user_account_id', $user_account_id)
                               ->get($this->staff_table)
                               ->row_array();
            
            case ROLE_RESELLER:
                return $this->db->where('user_account_id', $user_account_id)
                               ->get($this->resellers_table)
                               ->row_array();
            
            case ROLE_CUSTOMER:
                return $this->db->where('user_account_id', $user_account_id)
                               ->get($this->customers_table)
                               ->row_array();
            
            default:
                return FALSE;
        }
    }

    /**
     * Get role-specific ID (admin_id, staff_id, reseller_id, customer_id)
     * 
     * @param int $user_account_id User account ID
     * @param string $role User role
     * @return int|false Role-specific ID
     */
    public function get_role_id($user_account_id, $role) {
        $profile = $this->get_user_profile($user_account_id, $role);
        
        if (!$profile) {
            return FALSE;
        }
        
        // Extract role ID based on role
        switch ($role) {
            case ROLE_ADMIN:
                return $profile['admin_id'] ?? FALSE;
            case ROLE_STAFF:
                return $profile['staff_id'] ?? FALSE;
            case ROLE_RESELLER:
                return $profile['reseller_id'] ?? FALSE;
            case ROLE_CUSTOMER:
                return $profile['customer_id'] ?? FALSE;
            default:
                return FALSE;
        }
    }

    /**
     * Create new user account
     * 
     * @param array $data User data
     * @return int|false User account ID on success, FALSE on failure
     */
    public function create_user($data) {
        $user_data = array(
            'email'       => $data['email'],
            'password'    => password_hash($data['password'], PASSWORD_BCRYPT),
            'is_verified' => $data['is_verified'] ?? 0,
            'status'      => $data['status'] ?? 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        );
        
        if ($this->db->insert($this->users_table, $user_data)) {
            return $this->db->insert_id();
        }
        
        return FALSE;
    }

    /**
     * Update user password
     * 
     * @param int $user_account_id User account ID
     * @param string $new_password New password (plain text)
     * @return bool Success status
     */
    public function update_password($user_account_id, $new_password) {
        $data = array(
            'password'   => password_hash($new_password, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $this->db->where('user_account_id', $user_account_id);
        return $this->db->update($this->users_table, $data);
    }

    /**
     * Verify OTP token
     * 
     * @param int $user_account_id User account ID
     * @param string $otp OTP token
     * @return bool Verification success
     */
    public function verify_otp($user_account_id, $otp) {
        $user = $this->get_by_id($user_account_id);
        
        if (!$user || $user['token_otp'] !== $otp) {
            return FALSE;
        }
        
        // Clear OTP and mark as verified
        $data = array(
            'token_otp'   => NULL,
            'is_verified' => 1,
            'updated_at'  => date('Y-m-d H:i:s')
        );
        
        $this->db->where('user_account_id', $user_account_id);
        return $this->db->update($this->users_table, $data);
    }

    /**
     * Update last login
     * 
     * @param int $user_account_id User account ID
     * @return bool Success status
     */
    public function update_last_login($user_account_id) {
        $data = array('last_login' => date('Y-m-d H:i:s'));
        
        $this->db->where('user_account_id', $user_account_id);
        return $this->db->update($this->users_table, $data);
    }

    /**
     * Update user status
     * 
     * @param int $user_account_id User account ID
     * @param string $status New status (active, inactive)
     * @return bool Success status
     */
    public function update_status($user_account_id, $status) {
        $data = array('status' => $status);
        
        $this->db->where('user_account_id', $user_account_id);
        return $this->db->update($this->users_table, $data);
    }

    /**
     * Create new user account
     * 
     * @param array $data User account data (email, password_hash or password, status, etc.)
     * @return int|false New user_account_id or false on failure
     */
    public function create_account($data) {
        // Ensure we're using 'password' column, not 'password_hash'
        if (isset($data['password_hash']) && !isset($data['password'])) {
            $data['password'] = $data['password_hash'];
            unset($data['password_hash']);
        }
        
        if ($this->db->insert($this->users_table, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }
}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */
