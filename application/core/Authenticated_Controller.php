<?php
/**
 * Authenticated Base Controller
 * All role-specific controllers should extend this class
 * Provides automatic authentication checks and role validation
 */

class Authenticated_Controller extends CI_Controller {

    protected $user_type;
    protected $user_id;
    protected $user_account_id;
    protected $user_email;
    protected $user_full_name;

    public function __construct() {
        parent::__construct();

        $this->load->config('dropsell');
        $this->load->helper('session');
        load_scoped_session(); // isolate this role's session from the other 3
        $this->load->model('activity_log_model');

        // Check if user is logged in
        $this->_require_login();

        // Load session data into properties
        $this->user_type = $this->session->userdata('user_type');
        $this->user_id = $this->session->userdata('user_id');
        $this->user_account_id = $this->session->userdata('user_account_id');
        $this->user_email = $this->session->userdata('email');
        $this->user_full_name = $this->session->userdata('full_name');

        // A browser can stay "logged in" (session cookie still valid) even
        // after the underlying admin/staff/reseller row it points to was
        // deleted — the session cache (name, email, photo) keeps showing
        // stale data, but every fresh query scoped to $this->user_id matches
        // nothing: profile fields render empty, and writes "succeed" while
        // silently affecting zero rows. Catch that here, once, for every
        // page, instead of leaving each controller to fail in its own way.
        $this->load->database();
        $role_tables = [
            ROLE_ADMIN    => ['table' => ADMIN_TABLE, 'pk' => 'admin_id'],
            ROLE_STAFF    => ['table' => STAFF_TABLE, 'pk' => 'staff_id'],
            ROLE_RESELLER => ['table' => RESELLER_TABLE, 'pk' => 'reseller_id'],
        ];
        if (isset($role_tables[$this->user_type])) {
            $still_exists = $this->db
                ->where($role_tables[$this->user_type]['pk'], $this->user_id)
                ->count_all_results($role_tables[$this->user_type]['table']);
            if (!$still_exists) {
                // Clear just the key ensure_logged_in() checks, rather than
                // destroying the whole session outright — that would also
                // wipe the flashdata message before it ever reaches the
                // login page.
                $this->session->unset_userdata('user_account_id');
                $this->session->set_flashdata('error', 'Your session is no longer valid. Please log in again.');
                redirect('auth/login');
            }
        }

        // Make current page available to all views, including header/sidebar.
        // Provide safe defaults so the shared admin layout can render even when
        // a controller does not set every view variable explicitly.
        $this->load->vars($this->get_default_view_data());
    }

    /**
     * Private: Require login
     */
    private function _require_login() {
        $this->load->helper('auth');
        if (!ensure_logged_in()) {
            $this->session->set_userdata('redirect_url', current_url());
            redirect('auth/login');
        }
    }

    /**
     * Require specific role
     */
    protected function require_role($required_role) {
        if ($this->user_type !== $required_role) {
            $this->load->helper('auth');
            deny_role_access();
        }
    }

    /**
     * Require any of multiple roles
     */
    protected function require_any_role($required_roles = array()) {
        if (!in_array($this->user_type, $required_roles)) {
            $this->load->helper('auth');
            deny_role_access();
        }
    }

    /**
     * Log activity
     */
    protected function log_activity($action, $entity_type = NULL, $entity_id = NULL, $details = NULL) {
        $log_data = array(
            'user_type'  => $this->user_type,
            'user_id'    => $this->user_id,
            'action'     => $action,
            'entity_type'=> $entity_type,
            'entity_id'  => $entity_id,
            'details'    => $details,
            'ip_address' => $this->input->ip_address()
        );

        $this->activity_log_model->create($log_data);
    }

    /**
     * Set view data
     */
    protected function get_current_page() {
        $page = $this->uri->segment(2);

        if (empty($page) && $this->uri->segment(1) === 'admin') {
            return 'dashboard';
        }

        return $page ?: '';
    }

    protected function get_default_view_data($extra = array()) {
        $defaults = array(
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'user_email' => $this->user_email,
            'user_full_name' => $this->user_full_name,
            'page_title' => '',
            'current_page' => $this->get_current_page(),
            'low_stock_products' => [],
            'expiring_products' => [],
            'inventory_attention_count' => 0,
            'pending_orders_notifications' => 0,
            'pending_refunds' => 0,
            'pending_reviews' => 0,
            'pending_resellers' => 0,
            'pending_withdrawals' => 0,
        );

        // These sidebar/tab badges are only meaningful for admins, and this
        // runs on every admin page load (constructor + set_view_data), so
        // computing them here — instead of in every individual controller —
        // is what keeps the badges from going stale as pages are added.
        if ($this->user_type === 'admin') {
            // Direct reseller sign-ups AND customer upgrade applications both
            // land on the Resellers tab's action queues — either kind sitting
            // at 'pending' means admin has something to approve/reject there.
            $pendingRegistrations = (int) $this->db
                ->where('status', 'pending')
                ->count_all_results(RESELLER_TABLE);
            $pendingApplications = (int) $this->db
                ->where('status', 'pending')
                ->count_all_results(RESELLER_APPLICATION_TABLE);
            $defaults['pending_resellers'] = $pendingRegistrations + $pendingApplications;

            // Only count withdrawals the admin can actually act on right now
            // (reseller has verified their OTP) — otherwise the badge would
            // flag requests that are still waiting on the reseller, which
            // would look like a bug ("why is there a badge with nothing to approve").
            $defaults['pending_withdrawals'] = (int) $this->db
                ->where('status', 'pending')
                ->where('otp_verified', 1)
                ->count_all_results(WITHDRAWAL_TABLE);

            // Orders still awaiting admin's manual payment verification —
            // this was always in the sidebar template but never actually
            // computed anywhere, so the Orders badge silently never showed.
            $defaults['pending_orders_notifications'] = (int) $this->db
                ->where('order_status', 'pending')
                ->count_all_results(ORDER_TABLE);

            // Refunds/Reviews live under the same sidebar "Orders" tab as
            // Orders (see sidebar.php's $ordersActive check) and each has
            // its own badge pill on the in-page order_tabs.php partial —
            // kept as separate counts (not folded into the total above) so
            // each pill shows what specifically needs a look, instead of
            // one queue's backlog showing up on a different tab's badge.
            $defaults['pending_refunds'] = (int) $this->db
                ->where('status', 'pending')
                ->count_all_results(REFUND_REQUEST_TABLE);
            $defaults['pending_reviews'] = (int) $this->db
                ->where('status', 'pending')
                ->count_all_results(PRODUCT_REVIEWS_TABLE);

            // Products at/below their own min_stock_alert (including sold
            // out, 0) — same gap: the Inventory badge template existed but
            // this was never populated, so a product selling down to zero
            // never surfaced anywhere in the nav.
            $defaults['low_stock_products'] = $this->db
                ->select('product_id')
                ->where('is_archived', 0)
                ->where('min_stock_alert IS NOT NULL')
                ->where('stock <= min_stock_alert')
                ->get(PRODUCT_TABLE)->result_array();

            // Products expiring within 30 days (same lookahead window as the
            // dashboard's "Expiring Soon" stat) — the Inventory badge should
            // flag these too, not just low stock, since both need the admin
            // to go look at the product.
            $defaults['expiring_products'] = $this->db
                ->select('product_id')
                ->where('is_archived', 0)
                ->where('expiry_date IS NOT NULL', NULL, FALSE)
                ->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
                ->get(PRODUCT_TABLE)->result_array();

            // Distinct products across both lists, so one that's both low
            // stock and expiring doesn't get counted twice in the badge.
            $defaults['inventory_attention_count'] = count(array_unique(array_merge(
                array_column($defaults['low_stock_products'], 'product_id'),
                array_column($defaults['expiring_products'], 'product_id')
            )));
        } elseif ($this->user_type === 'staff') {
            // Staff can't touch an order until admin has confirmed payment —
            // so their Orders badge counts what's actually actionable by
            // them right now (fulfillment), not admin's pending-verification
            // queue, which would just look like a stuck badge to staff.
            $defaults['pending_orders_notifications'] = (int) $this->db
                ->where_in('order_status', ['paid', 'processing', 'to_ship'])
                ->count_all_results(ORDER_TABLE);

            // Staff Inventory only ever shows their own branch's stock (see
            // staff/Inventory.php), so the sidebar badge has to be scoped
            // the same way — a product that's low everywhere else but fine
            // at this branch shouldn't flag here, and vice versa.
            $staff_branch_id = (int) ($this->db->select('branch_id')
                ->where('staff_id', $this->user_id)
                ->get(STAFF_TABLE)->row_array()['branch_id'] ?? 0);

            if ($staff_branch_id) {
                require_once APPPATH . 'services/StockService.php';

                $alertable_products = $this->db
                    ->select('product_id, min_stock_alert')
                    ->where('is_archived', 0)
                    ->where('min_stock_alert IS NOT NULL')
                    ->get(PRODUCT_TABLE)->result_array();

                $branch_stock = StockService::getAvailableStockForProducts(
                    array_column($alertable_products, 'product_id'),
                    $staff_branch_id
                );

                $low_stock_product_ids = [];
                foreach ($alertable_products as $p) {
                    $qty = $branch_stock[(int) $p['product_id']] ?? 0;
                    if ($qty <= (int) $p['min_stock_alert']) {
                        $low_stock_product_ids[] = (int) $p['product_id'];
                    }
                }

                $defaults['expiring_products'] = $this->db
                    ->select('product_id')
                    ->where('is_archived', 0)
                    ->where('expiry_date IS NOT NULL', NULL, FALSE)
                    ->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
                    ->get(PRODUCT_TABLE)->result_array();

                $defaults['inventory_attention_count'] = count(array_unique(array_merge(
                    $low_stock_product_ids,
                    array_column($defaults['expiring_products'], 'product_id')
                )));
            }
        } elseif ($this->user_type === 'reseller') {
            // Resellers don't act on orders either, but a pending order is
            // still new/incoming for them — worth flagging the same way
            // admin's badge flags "needs a look."
            $defaults['pending_orders_notifications'] = (int) $this->db
                ->where('reseller_id', $this->user_id)
                ->where('order_status', 'pending')
                ->count_all_results(ORDER_TABLE);

            // A withdrawal sitting unverified is squarely on the reseller —
            // they're the only one who can enter the OTP, so this is exactly
            // the kind of "needs your action" state the sidebar badges exist
            // for elsewhere.
            $defaults['pending_withdrawals'] = (int) $this->db
                ->where('reseller_id', $this->user_id)
                ->where('status', 'pending')
                ->where('otp_verified', 0)
                ->count_all_results(WITHDRAWAL_TABLE);
        }

        return array_merge($defaults, $extra);
    }

    protected function set_view_data($extra = array()) {
        $data = $this->get_default_view_data($extra);
        $this->load->vars($data);
        return $data;
    }
}

/* End of file Authenticated_Controller.php */
/* Location: ./application/core/Authenticated_Controller.php */
