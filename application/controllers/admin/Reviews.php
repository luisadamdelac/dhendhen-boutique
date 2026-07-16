<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reviews extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
    }

    private function base_query() {
        return $this->db->select('pr.*, p.product_name, c.first_name as customer_first_name, c.last_name as customer_last_name, c.profile_image as customer_profile_image, u.email as customer_email')
            ->from('product_reviews pr')
            ->join(RESELLER_PRODUCTS_TABLE . ' rp', 'pr.reseller_product_id = rp.reseller_product_id')
            ->join(PRODUCT_TABLE . ' p', 'rp.product_id = p.product_id')
            ->join(CUSTOMER_TABLE . ' c', 'pr.customer_id = c.customer_id')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id');
    }

    public function index() {
        $data['page_title'] = 'Product Reviews';
        $data['current_page'] = 'reviews';

        $data['reviews'] = $this->base_query()->order_by('pr.created_at', 'DESC')->get()->result_array();
        foreach ($data['reviews'] as &$review) {
            $review['customer_name'] = trim($review['customer_first_name'] . ' ' . $review['customer_last_name']);
            $review['review_text'] = $review['comment'];
            $review['is_visible'] = $review['status'] === 'approved' ? 1 : 0;
            $review['is_verified'] = $review['status'] === 'approved' ? 1 : 0;
        }
        unset($review);

        $data['stats'] = [
            'total_reviews' => count($data['reviews']),
            'pending_reviews' => count(array_filter($data['reviews'], fn($r) => $r['status'] === 'pending')),
            'approved_reviews' => count(array_filter($data['reviews'], fn($r) => $r['status'] === 'approved')),
            'average_rating' => count($data['reviews']) ? array_sum(array_column($data['reviews'], 'rating')) / count($data['reviews']) : 0,
        ];

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reviews/index', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function view($review_id = '') {
        if (empty($review_id)) {
            redirect('admin/reviews');
        }

        $review = $this->base_query()->where('pr.review_id', $review_id)->get()->row_array();
        if (!$review) {
            $this->session->set_flashdata('error', 'Review not found');
            redirect('admin/reviews');
        }

        $review['customer_name'] = trim($review['customer_first_name'] . ' ' . $review['customer_last_name']);
        $review['review_text'] = $review['comment'];
        $review['is_visible'] = $review['status'] === 'approved' ? 1 : 0;
        $review['is_verified'] = $review['status'] === 'approved' ? 1 : 0;

        $data['page_title'] = 'Review Details';
        $data['current_page'] = 'reviews';
        $data['review'] = $review;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reviews/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }
}
?>
