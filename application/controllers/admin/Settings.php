<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model('Settings_model');
    }

    /**
     * System settings
     */
    public function index() {
        $data['page_title'] = 'Settings';
        $data['current_page'] = 'settings';
        if ($this->input->method() === 'post') {
            $this->_save_settings();
            return;
        }

        $data['settings'] = $this->Settings_model->get_settings_array();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/index', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Save settings
     */
    private function _save_settings() {
        $settings = [
            'company_name' => $this->input->post('company_name') ?? 'DropSell',
            'company_email' => $this->input->post('company_email') ?? 'support@dropsell.com',
            'company_phone' => $this->input->post('company_phone') ?? '',
            'company_address' => $this->input->post('company_address') ?? 'Calapan City, Oriental Mindoro',
            'commission_rate' => $this->input->post('commission_rate') ?? '10',
            'minimum_withdrawal' => $this->input->post('minimum_withdrawal') ?? '100',
            'minimum_spend' => $this->input->post('minimum_spend') ?? '3000',
            'withdrawal_schedule_dates' => $this->input->post('withdrawal_schedule_dates') ?? '15,30',
            'currency' => $this->input->post('currency') ?? 'PHP',
            'items_per_page' => $this->input->post('items_per_page') ?? '50',
        ];

        $this->Settings_model->update_multiple($settings);
        $this->session->set_flashdata('success', 'Settings updated successfully');
        redirect('admin/settings');
    }

    /**
     * Email settings
     */
    public function email() {
        $data['page_title'] = 'Email Settings';
        $data['current_page'] = 'settings';
        if ($this->input->method() === 'post') {
            $smtp_host = trim($this->input->post('smtp_host') ?? '');

            // A hostname never contains "@" — this specifically catches the
            // easy mistake of pasting an email address into the Host field
            // (which would otherwise fail later as a cryptic fsockopen/DNS
            // error instead of a clear validation message here).
            if ($smtp_host !== '' && strpos($smtp_host, '@') !== FALSE) {
                $this->session->set_flashdata('error', 'SMTP Host must be a server address (e.g. smtp.gmail.com), not an email address.');
                redirect('admin/settings/email');
                return;
            }

            $settings = [
                'smtp_host' => $smtp_host,
                'smtp_port' => $this->input->post('smtp_port') ?? '587',
                'smtp_user' => $this->input->post('smtp_user') ?? '',
                'smtp_encryption' => $this->input->post('smtp_encryption') ?? 'tls',
                'smtp_enabled' => $this->input->post('smtp_enabled') ? '1' : '0',
                'from_email' => $this->input->post('from_email') ?? 'noreply@dropsell.com',
                'from_name' => $this->input->post('from_name') ?? 'DropSell',
                'reply_to' => $this->input->post('reply_to') ?? '',
            ];

            // An empty submitted password means "leave the saved one alone" —
            // this is what makes it safe to never echo the real value back
            // into the form after save.
            $newPassword = $this->input->post('smtp_password');
            if ($newPassword !== NULL && $newPassword !== '') {
                $settings['smtp_password'] = $newPassword;
            }

            $this->Settings_model->update_multiple($settings);
            $this->session->set_flashdata('success', 'Email settings updated successfully');
            redirect('admin/settings/email');
        }

        $data['settings'] = $this->Settings_model->get_settings_array();

        require_once APPPATH . 'services/EmailQueueService.php';
        $data['email_stats'] = array_merge(EmailQueueService::getQueueStats(), [
            'smtp_enabled' => EmailQueueService::isSmtpEnabled(),
            'configured' => !empty($data['settings']['smtp_host']) && !empty($data['settings']['smtp_user']) && !empty($data['settings']['smtp_password']),
            'last_sent' => EmailQueueService::getLastSentAt(),
        ]);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/email', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Send a one-off test email using the currently saved SMTP settings.
     * AJAX POST, admin-only (enforced by the constructor).
     */
    public function send_test_email() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $toEmail = trim((string) $this->input->post('test_email'));
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => FALSE, 'message' => 'Please enter a valid email address.']);
            return;
        }

        require_once APPPATH . 'services/EmailQueueService.php';
        echo json_encode(EmailQueueService::sendTestEmail($toEmail));
    }

    /**
     * Email queue logs — search, status filter, pagination.
     */
    public function email_logs() {
        $data['page_title'] = 'Email Logs';
        $data['current_page'] = 'settings';

        $page = (int) ($this->input->get('page') ?? 1);
        $search = trim((string) ($this->input->get('search') ?? ''));
        $status = $this->input->get('status') ?? '';
        $limit = (int) ($this->Settings_model->get('items_per_page') ?: 50);
        $offset = ($page - 1) * $limit;

        // CI3's query builder shares $this->db's internal FROM/WHERE state
        // across chained calls until get() executes and resets it — so each
        // query below must be fully built AND executed before the next one
        // starts, or their FROM clauses merge into "FROM x, x" (error 1066).
        $apply_filters = function ($q) use ($search, $status) {
            if ($search !== '') {
                $q->group_start()
                    ->like('recipient_email', $search)
                    ->or_like('subject', $search)
                    ->group_end();
            }
            if (!empty($status)) {
                $q->where('status', $status);
            }
            return $q;
        };

        $count_query = $apply_filters($this->db->select('COUNT(*) as count')->from('email_queue_tbl'));
        $data['total'] = (int) ($count_query->get()->row()->count ?? 0);

        $query = $apply_filters($this->db->select('*')->from('email_queue_tbl'));
        $data['logs'] = $query->order_by('created_at', 'DESC')->limit($limit, $offset)->get()->result_array();
        $data['pages'] = (int) ceil($data['total'] / $limit);
        $data['page'] = $page;
        $data['search'] = $search;
        $data['status'] = $status;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/email_logs', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Payment settings
     */
    public function payment() {
        $data['page_title'] = 'Payment Settings';
        $data['current_page'] = 'settings';
        if ($this->input->method() === 'post') {
            $settings = [
                'payment_method' => $this->input->post('payment_method') ?? '',
                'gcash_name' => $this->input->post('gcash_name') ?? '',
                'gcash_number' => $this->input->post('gcash_number') ?? '',
            ];

            if (!empty($_FILES['gcash_qr_code']['name'])) {
                $upload_path = FCPATH . 'uploads/settings/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, TRUE);
                }

                $this->load->library('upload', [
                    'upload_path'   => $upload_path,
                    'allowed_types' => 'jpg|jpeg|png',
                    'max_size'      => 2048,
                    'encrypt_name'  => TRUE,
                ]);

                if (!$this->upload->do_upload('gcash_qr_code')) {
                    $this->session->set_flashdata('error', 'QR code upload failed: ' . $this->upload->display_errors('', ''));
                    redirect('admin/settings/payment');
                    return;
                }

                $upload_data = $this->upload->data();
                $settings['gcash_qr_code'] = 'uploads/settings/' . $upload_data['file_name'];
            }

            $this->Settings_model->update_multiple($settings);
            $this->session->set_flashdata('success', 'Payment settings updated successfully');
            redirect('admin/settings/payment');
        }

        $data['settings'] = $this->Settings_model->get_settings_array();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/payment', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Pasabay delivery fee per Oriental Mindoro municipality.
     */
    public function shipping() {
        $data['page_title'] = 'Shipping Fee Settings';
        $data['current_page'] = 'settings';

        $this->load->helper('address');
        $data['municipalities'] = array_keys(get_oriental_mindoro_barangays());

        if ($this->input->method() === 'post') {
            $fees = [];
            foreach ($data['municipalities'] as $municipality) {
                $fees[$municipality] = (float) $this->input->post('fee_' . md5($municipality));
            }
            $this->Settings_model->save_shipping_fees($fees);
            $this->session->set_flashdata('success', 'Shipping fees updated successfully');
            redirect('admin/settings/shipping');
        }

        $data['shipping_fees'] = $this->Settings_model->get_shipping_fees();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/shipping', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Social media links shown in the customer-facing footer's "Follow Us"
     * section. Left blank, that icon is hidden rather than linking to "#".
     */
    public function social() {
        $data['page_title'] = 'Social Media Settings';
        $data['current_page'] = 'settings';

        if ($this->input->method() === 'post') {
            $settings = [
                'social_facebook' => trim($this->input->post('social_facebook') ?? ''),
                'social_instagram' => trim($this->input->post('social_instagram') ?? ''),
                'social_tiktok' => trim($this->input->post('social_tiktok') ?? ''),
            ];

            $this->Settings_model->update_multiple($settings);
            $this->session->set_flashdata('success', 'Social media links updated successfully');
            redirect('admin/settings/social');
        }

        $data['settings'] = $this->Settings_model->get_settings_array();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/social', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Activity log — admin-facing view of activity_logs, so admins no longer
     * need to open the database directly to see what happened.
     */
    public function activity_log() {
        $data['page_title'] = 'Activity Log';
        $data['current_page'] = 'settings';

        // Every other admin list page (Orders, Products, Reseller
        // Applications) fetches its full set once and filters/searches
        // client-side via DataTables — this page was the only one still
        // doing a server round-trip per keystroke via a "Filter" button,
        // which felt broken/inconsistent next to those. Matches that
        // pattern now instead. Capped well above realistic log volume so
        // it stays a single fast query even as the table grows.
        $data['logs'] = $this->db->select('*')
            ->from(ACTIVITY_LOGS_TABLE)
            ->order_by('created_at', 'DESC')
            ->limit(2000)
            ->get()->result_array();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/settings/activity_log', $data);
        $this->load->view('admin/layout/footer', $data);
    }
}
?>
