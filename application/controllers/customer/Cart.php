<?php
class Cart extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form', 'auth']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        $this->load->config('dropsell');
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
        require_once APPPATH . 'services/CartService.php';
    }

    public function index() {
        $data['page_title'] = 'Cart';
        $cart = $this->session->userdata('cart') ?: [];
        [$data['cartItems'], $data['subtotal'], $validCart] = CartService::getCartItems($cart);

        if (count($validCart) !== count($cart)) {
            $this->session->set_userdata('cart', $validCart);
        }

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/cart/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Add a product (optionally a specific variation) to the cart. If
     * $reseller_id is posted (buying from a specific mini-shop), locks to
     * that reseller's listing; otherwise auto-picks the cheapest active
     * listing from any reseller.
     */
    public function add() {
        $product_id = (int) $this->input->post('product_id');
        $variation_id = (int) $this->input->post('variation_id') ?: NULL;
        $quantity = max(1, (int) $this->input->post('quantity'));
        $reseller_id = (int) $this->input->post('reseller_id') ?: NULL;

        $listing = CartService::resolveListing($product_id, $reseller_id);
        if (!$listing) {
            $this->_respond(FALSE, 'This product is not currently available from any reseller.');
            return;
        }

        if ($variation_id) {
            $variation = $this->db->where('variation_id', $variation_id)
                ->where('product_id', $product_id)
                ->where('status', 'active')
                ->get(PRODUCT_VARIATION_TABLE)->row_array();
            if (!$variation) {
                $this->_respond(FALSE, 'The selected variation is no longer available.');
                return;
            }
        }

        $cart_key = $variation_id ? $product_id . '_v' . $variation_id : (string) $product_id;
        $cart = $this->session->userdata('cart') ?: [];
        $existing_qty = $cart[$cart_key]['qty'] ?? 0;
        $cart[$cart_key] = [
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'qty'          => $existing_qty + $quantity,
            'reseller_id'  => (int) $listing['reseller_id'],
        ];
        $this->session->set_userdata('cart', $cart);

        $this->_respond(TRUE, 'Product added to cart');
    }

    public function update() {
        $cart_key = $this->input->post('product_id', TRUE);
        $quantity = (int) $this->input->post('quantity');
        $cart = $this->session->userdata('cart') ?: [];

        if ($quantity < 1) {
            unset($cart[$cart_key]);
        } elseif (isset($cart[$cart_key])) {
            $cart[$cart_key]['qty'] = $quantity;
        }

        $this->session->set_userdata('cart', $cart);
        redirect('cart');
    }

    public function remove($cart_key = NULL) {
        $cart = $this->session->userdata('cart') ?: [];
        unset($cart[$cart_key]);
        $this->session->set_userdata('cart', $cart);
        redirect('cart');
    }

    public function clear() {
        $this->session->set_userdata('cart', []);
        redirect('cart');
    }

    /** Total quantity across all cart lines, for the header cart badge. */
    public function count() {
        $cart = $this->session->userdata('cart') ?: [];
        $count = array_sum(array_column($cart, 'qty'));
        echo json_encode(['count' => $count]);
    }

    private function _respond($success, $message) {
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => $success, 'message' => $message]);
            return;
        }
        $this->session->set_flashdata($success ? 'success' : 'error', $message);
        redirect($success ? 'cart' : 'shop');
    }
}
