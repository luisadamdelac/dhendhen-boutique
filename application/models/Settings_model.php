<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    private function settingsTableExists(): bool {
        // If constant missing or mapped to invalid value, treat as not existing.
        if (!defined('SETTINGS_TABLE') || empty(SETTINGS_TABLE)) {
            return FALSE;
        }

        try {
            $dbName = $this->db->database ?? 'dropsell_db';
            $row = $this->db->select('COUNT(*) as cnt', FALSE)
                ->from('information_schema.tables')
                ->where('table_schema', $dbName)
                ->where('table_name', SETTINGS_TABLE)
                ->get()->row_array();

            return isset($row['cnt']) && (int)$row['cnt'] > 0;
        } catch (Throwable $e) {
            return FALSE;
        }
    }

    /**
     * Get all settings
     */
    public function get_all() {
        if (!$this->settingsTableExists()) {
            return [];
        }

        try {
            return $this->db->select('*')
                ->from(SETTINGS_TABLE)
                ->get()->result_array();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Get setting by key
     */
    public function get($key) {
        if (!$this->settingsTableExists()) {
            return NULL;
        }

        try {
            $result = $this->db->select('*')
                ->from(SETTINGS_TABLE)
                ->where('setting_key', $key)
                ->get()->row_array();
            return $result['setting_value'] ?? NULL;
        } catch (Throwable $e) {
            return NULL;
        }
    }

    /**
     * Get settings as array
     */
    public function get_settings_array() {
        $settings = [];
        $results = $this->get_all();
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Update setting
     */
    public function update_setting($key, $value) {
        if (!$this->settingsTableExists()) {
            return FALSE;
        }

        try {
            $existing = $this->db->select('*')
                ->from(SETTINGS_TABLE)
                ->where('setting_key', $key)
                ->count_all_results();

            if ($existing > 0) {
                return $this->db->update(SETTINGS_TABLE, ['setting_value' => $value], ['setting_key' => $key]);
            } else {
                return $this->db->insert(SETTINGS_TABLE, ['setting_key' => $key, 'setting_value' => $value]);
            }
        } catch (Throwable $e) {
            return FALSE;
        }
    }

    /**
     * Update multiple settings
     */
    public function update_multiple($settings = []) {
        foreach ($settings as $key => $value) {
            $this->update_setting($key, $value);
        }
        return TRUE;
    }

    /**
     * Get commission rate
     */
    public function get_commission_rate() {
        return $this->get('commission_rate') ?? 10;
    }

    /**
     * Get company name
     */
    public function get_company_name() {
        return $this->get('company_name') ?? 'DropSell';
    }

    /**
     * Get company email
     */
    public function get_company_email() {
        return $this->get('company_email') ?? 'support@dropsell.com';
    }

    /**
     * Get company phone
     */
    public function get_company_phone() {
        return $this->get('company_phone') ?? '';
    }

    /**
     * Get minimum withdrawal
     */
    public function get_minimum_withdrawal() {
        return $this->get('minimum_withdrawal') ?? 100;
    }

    /**
     * Get minimum cumulative spend a customer needs before they can apply
     * to become a reseller (matches reseller_application_tbl.minimum_spend's
     * schema default).
     */
    public function get_minimum_spend() {
        return $this->get('minimum_spend') ?? 3000;
    }

    /**
     * Days of the month (e.g. [15, 30]) admin has scheduled for commission
     * withdrawal processing.
     */
    public function get_withdrawal_schedule_dates() {
        $raw = $this->get('withdrawal_schedule_dates') ?? '15,30';
        $days = array_filter(array_map('intval', explode(',', $raw)));
        sort($days);
        return $days ?: [15, 30];
    }

    /**
     * Next upcoming scheduled withdrawal-processing date (today included).
     */
    public function get_next_withdrawal_schedule_date() {
        $days = $this->get_withdrawal_schedule_dates();
        $today = (int) date('j');
        foreach ($days as $day) {
            if ($day >= $today) {
                return date('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
            }
        }
        // None left this month — first scheduled day next month.
        $next_month = date('Y-m-01', strtotime('first day of next month'));
        return date('Y-m-', strtotime($next_month)) . str_pad($days[0], 2, '0', STR_PAD_LEFT);
    }

    /**
     * Default Pasabay fee per municipality, derived from the LTFRB TPUJ fare
     * (₱14 for the first 4km, +₱2.00 per succeeding km) applied to each
     * municipality's approximate road distance from Calapan City (the hub
     * branch). These are estimates — adjust exact values any time from
     * Settings > Shipping Fees.
     */
    private function default_shipping_fees() {
        return [
            'Calapan City'   => 14.00,
            'Baco'           => 26.00,
            'San Teodoro'    => 56.00,
            'Naujan'         => 56.00,
            'Victoria'       => 42.00,
            'Socorro'        => 66.00,
            'Pola'           => 76.00,
            'Puerto Galera'  => 86.00,
            'Pinamalayan'    => 106.00,
            'Gloria'         => 136.00,
            'Bansud'         => 166.00,
            'Bongabong'      => 196.00,
            'Roxas'          => 226.00,
            'Mansalay'       => 266.00,
            'Bulalacao'      => 316.00,
        ];
    }

    /**
     * Pasabay delivery fee per Oriental Mindoro municipality, stored as a
     * single JSON blob under one settings key. Municipalities with no saved
     * fee yet fall back to the TPUJ-fare-based default above.
     */
    public function get_shipping_fees() {
        $this->load->helper('address');
        $municipalities = array_keys(get_oriental_mindoro_barangays());
        $defaults = $this->default_shipping_fees();

        $raw = $this->get('shipping_fees_by_municipality');
        $saved = $raw ? json_decode($raw, TRUE) : [];
        if (!is_array($saved)) {
            $saved = [];
        }

        $fees = [];
        foreach ($municipalities as $municipality) {
            $fees[$municipality] = isset($saved[$municipality]) ? (float) $saved[$municipality] : ($defaults[$municipality] ?? 30.00);
        }
        return $fees;
    }

    /**
     * Fee for a single municipality (used by Checkout to price Pasabay).
     */
    public function get_shipping_fee_for_municipality($municipality) {
        $fees = $this->get_shipping_fees();
        return $fees[$municipality] ?? 30.00;
    }

    public function save_shipping_fees(array $fees) {
        return $this->update_setting('shipping_fees_by_municipality', json_encode($fees));
    }
}
?>
