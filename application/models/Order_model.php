<?php
/**
 * Order Model
 * Handles order management from order_tbl and order_details
 */

class Order_model extends CI_Model {
    
    protected $table = 'order_tbl';
    protected $details_table = 'order_details';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get order by order_id
     */
    public function get_by_id($order_id) {
        $query = $this->db->where('order_id', $order_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all orders with pagination
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('o.*, c.first_name as customer_first, c.last_name as customer_last, r.business_name')
                         ->from($this->table . ' o')
                         ->join('customer_tbl c', 'o.customer_id = c.customer_id')
                         ->join('reseller_tbl r', 'o.reseller_id = r.reseller_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('o.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Get orders by status
     */
    public function get_by_status($status, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('order_status', $status);
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('created_at', 'DESC');
        return $query->get($this->table)->result_array();
    }

    /**
     * Get orders by customer
     */
    public function get_by_customer($customer_id, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('customer_id', $customer_id);
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('created_at', 'DESC');
        return $query->get($this->table)->result_array();
    }

    /**
     * Get orders by reseller
     */
    public function get_by_reseller($reseller_id, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('reseller_id', $reseller_id);
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('created_at', 'DESC');
        return $query->get($this->table)->result_array();
    }

    /**
     * Count all orders
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Count by status
     */
    public function count_by_status($status) {
        $this->db->where('order_status', $status);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get order with details and items
     */
    public function get_with_details($order_id) {
        $order = $this->get_by_id($order_id);
        
        if (!$order) {
            return FALSE;
        }

        // Get order items
        $items = $this->db->where('order_id', $order_id)
                         ->get($this->details_table)
                         ->result_array();
        
        $order['items'] = $items;
        
        return $order;
    }

    /**
     * Create new order
     */
    public function create($data) {
        $order_data = array(
            'order_number'   => $data['order_number'],
            'customer_id'    => $data['customer_id'],
            'reseller_id'    => $data['reseller_id'],
            'staff_id'       => $data['staff_id'] ?? NULL,
            'total_amount'   => (float)$data['total_amount'],
            'delivery_method'=> $data['delivery_method'],
            'delivery_street'=> $data['delivery_street'] ?? NULL,
            'delivery_barangay'=> $data['delivery_barangay'] ?? NULL,
            'delivery_city'  => $data['delivery_city'] ?? NULL,
            'delivery_zip_code'=> $data['delivery_zip_code'] ?? NULL,
            'delivery_fee'   => (float)($data['delivery_fee'] ?? 0),
            'order_status'   => $data['order_status'] ?? 'pending',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        );

        if ($this->db->insert($this->table, $order_data)) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Update order
     */
    public function update($order_id, $data) {
        $update_data = array(
            'order_status'   => $data['order_status'] ?? NULL,
            'delivery_method'=> $data['delivery_method'] ?? NULL,
            'delivery_fee'   => isset($data['delivery_fee']) ? (float)$data['delivery_fee'] : NULL,
            'updated_at'     => date('Y-m-d H:i:s')
        );

        // Remove NULL values
        $update_data = array_filter($update_data, function($v) { return $v !== NULL; });

        $this->db->where('order_id', $order_id);
        return $this->db->update($this->table, $update_data);
    }

    /**
     * Update order status
     */
    public function update_status($order_id, $status) {
        $data = array(
            'order_status' => $status,
            'updated_at'   => date('Y-m-d H:i:s')
        );

        $this->db->where('order_id', $order_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Search orders by order number or customer
     */
    public function search($keyword, $limit = NULL, $offset = NULL) {
        $query = $this->db->select('o.*, c.first_name, c.last_name')
                         ->from($this->table . ' o')
                         ->join('customer_tbl c', 'o.customer_id = c.customer_id')
                         ->group_start()
                         ->like('o.order_number', $keyword)
                         ->or_like('c.first_name', $keyword)
                         ->or_like('c.last_name', $keyword)
                         ->group_end();
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('o.created_at', 'DESC');
        return $query->get()->result_array();
    }
}

/* End of file Order_model.php */
/* Location: ./application/models/Order_model.php */
