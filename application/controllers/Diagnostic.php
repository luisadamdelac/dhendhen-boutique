<?php
/**
 * Diagnostic Controller
 * Helps troubleshoot and fix user role assignment issues
 * 
 * ADMIN ONLY - Use for debugging purposes
 */

class Diagnostic extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_ADMIN);
        $this->load->database();
        $this->load->model('user_model');
    }

    /**
     * Check for orphaned users without roles
     */
    public function check_orphaned_users() {
        // Get all users
        $query = $this->db->get('user_account_tbl');
        $all_users = $query->result_array();

        $orphaned = array();
        $with_roles = array();

        foreach ($all_users as $user) {
            $role = $this->user_model->get_user_role($user['user_account_id']);
            
            if (!$role) {
                $orphaned[] = array(
                    'user_account_id' => $user['user_account_id'],
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'created_at' => $user['created_at']
                );
            } else {
                $with_roles[] = array(
                    'user_account_id' => $user['user_account_id'],
                    'email' => $user['email'],
                    'role' => $role,
                    'status' => $user['status']
                );
            }
        }

        $data = array(
            'total_users' => count($all_users),
            'users_with_roles' => count($with_roles),
            'orphaned_users' => count($orphaned),
            'orphaned_list' => $orphaned,
            'users_with_roles_list' => $with_roles
        );

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Check a specific user's role status
     */
    public function check_user_role($email = '') {
        if (empty($email)) {
            $email = $this->input->get('email', TRUE);
        }

        $user = $this->user_model->get_by_email($email);

        if (!$user) {
            $result = array(
                'status' => 'error',
                'message' => 'User not found',
                'email' => $email
            );
        } else {
            $role = $this->user_model->get_user_role($user['user_account_id']);
            $role_with_profile = $this->user_model->get_user_role_with_profile($user['user_account_id']);

            // Check all role tables
            $admin = $this->db->where('user_account_id', $user['user_account_id'])->get('admin_tbl')->row_array();
            $staff = $this->db->where('user_account_id', $user['user_account_id'])->get('staff_tbl')->row_array();
            $reseller = $this->db->where('user_account_id', $user['user_account_id'])->get('reseller_tbl')->row_array();
            $customer = $this->db->where('user_account_id', $user['user_account_id'])->get('customer_tbl')->row_array();

            $result = array(
                'status' => 'ok',
                'user_account_id' => $user['user_account_id'],
                'email' => $user['email'],
                'user_status' => $user['status'],
                'detected_role' => $role,
                'role_profile' => $role_with_profile,
                'roles_in_database' => array(
                    'admin' => !empty($admin) ? 'YES (ID: ' . $admin['admin_id'] . ')' : 'NO',
                    'staff' => !empty($staff) ? 'YES (ID: ' . $staff['staff_id'] . ')' : 'NO',
                    'reseller' => !empty($reseller) ? 'YES (ID: ' . $reseller['reseller_id'] . ')' : 'NO',
                    'customer' => !empty($customer) ? 'YES (ID: ' . $customer['customer_id'] . ')' : 'NO'
                ),
                'last_login' => $user['last_login']
            );
        }

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * Get database schema info
     */
    public function database_schema() {
        $tables = array('user_account_tbl', 'admin_tbl', 'staff_tbl', 'reseller_tbl', 'customer_tbl');
        
        $schema = array();

        foreach ($tables as $table) {
            $fields = $this->db->field_data($table);
            $schema[$table] = array(
                'total_rows' => $this->db->from($table)->count_all_results(),
                'columns' => $fields
            );
        }

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($schema, JSON_PRETTY_PRINT));
    }

    /**
     * Fix orphaned user by assigning a role
     * Usage: POST to /diagnostic/fix_user
     * POST data: user_account_id, role (admin|staff|reseller|customer)
     */
    public function fix_user() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            show_error('This endpoint accepts POST requests only', 405);
        }

        $user_account_id = $this->input->post('user_account_id', TRUE);
        $role = $this->input->post('role', TRUE);

        if (empty($user_account_id) || empty($role)) {
            $result = array(
                'status' => 'error',
                'message' => 'user_account_id and role are required'
            );
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode($result));
            return;
        }

        // Validate role
        $allowed_roles = $this->config->item('allowed_roles');
        if (!in_array($role, $allowed_roles)) {
            $result = array(
                'status' => 'error',
                'message' => 'Invalid role. Allowed roles: ' . implode(', ', $allowed_roles)
            );
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode($result));
            return;
        }

        // Get user
        $user = $this->user_model->get_by_id($user_account_id);
        if (!$user) {
            $result = array(
                'status' => 'error',
                'message' => 'User account not found'
            );
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode($result));
            return;
        }

        // Check if user already has a role
        $existing_role = $this->user_model->get_user_role($user_account_id);
        if ($existing_role) {
            $result = array(
                'status' => 'error',
                'message' => 'User already has role: ' . $existing_role
            );
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode($result));
            return;
        }

        // Assign role based on type
        try {
            $table = $this->_get_role_table($role);
            
            $role_data = array(
                'user_account_id' => $user_account_id,
                'first_name' => $this->input->post('first_name', TRUE) ?? 'User',
                'last_name' => $this->input->post('last_name', TRUE) ?? 'Account',
                'contact_number' => $this->input->post('contact_number', TRUE) ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );

            // city is NOT NULL on admin_tbl/reseller_tbl only
            if (in_array($role, ['admin', 'reseller'], TRUE)) {
                $role_data['city'] = $this->input->post('city', TRUE) ?? '';
            }

            if ($this->db->insert($table, $role_data)) {
                $role_id = $this->db->insert_id();

                log_message('info', "Assigned role {$role} (ID: {$role_id}) to user {$user_account_id}");

                $result = array(
                    'status' => 'success',
                    'message' => "Successfully assigned role '{$role}' to user",
                    'user_account_id' => $user_account_id,
                    'role' => $role,
                    'role_id' => $role_id,
                    'role_table' => $table
                );
            } else {
                $result = array(
                    'status' => 'error',
                    'message' => 'Failed to insert role record: ' . $this->db->error()['message']
                );
            }
        } catch (Exception $e) {
            $result = array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($result));
    }

    /**
     * Get role-specific table name
     */
    private function _get_role_table($role) {
        $tables = array(
            'admin' => 'admin_tbl',
            'staff' => 'staff_tbl',
            'reseller' => 'reseller_tbl',
            'customer' => 'customer_tbl'
        );

        return $tables[$role] ?? NULL;
    }

    /**
     * Display help
     */
    public function index() {
        echo "<h1>DropSell Diagnostic Tools</h1>";
        echo "<p>Use these endpoints to troubleshoot role assignment issues:</p>";
        echo "<ul>";
        echo "<li><a href='" . site_url('diagnostic/check_orphaned_users') . "'>/diagnostic/check_orphaned_users</a> - Find users without roles</li>";
        echo "<li>/diagnostic/check_user_role?email=user@example.com - Check specific user's role</li>";
        echo "<li><a href='" . site_url('diagnostic/database_schema') . "'>/diagnostic/database_schema</a> - View database structure</li>";
        echo "<li>POST to /diagnostic/fix_user - Assign role to orphaned user</li>";
        echo "</ul>";
    }
}

/* End of file Diagnostic.php */
/* Location: ./application/controllers/Diagnostic.php */
