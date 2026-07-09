<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Receives server-to-server webhook calls from third parties. No session
 * context — these requests come directly from the third-party's servers, so
 * each handler must authenticate the request itself (see the PayMongo
 * signature check below).
 */
class Webhooks extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->config('dropsell');
        require_once APPPATH . 'services/PaymongoService.php';
        require_once APPPATH . 'services/OrderFulfillmentService.php';
    }

    /**
     * PayMongo webhook receiver. CSRF protection is globally off app-wide
     * (config.php) so no exclusion is needed, but nothing else authenticates
     * this endpoint — the signature check below is the only gate.
     */
    public function paymongo() {
        $raw_body = file_get_contents('php://input');
        $signature = $this->input->get_request_header('Paymongo-Signature', TRUE);

        if (!PaymongoService::verifyWebhookSignature($raw_body, $signature)) {
            log_message('error', 'PayMongo webhook: signature verification failed.');
            http_response_code(401);
            echo json_encode(['error' => 'invalid_signature']);
            return;
        }

        $event = json_decode($raw_body, TRUE);
        $event_type = $event['data']['attributes']['type'] ?? NULL;
        $event_data = $event['data']['attributes']['data'] ?? NULL;

        // Always 200 once the signature is valid — PayMongo retries on
        // non-2xx responses, and retrying an event type we don't act on (or
        // one we've already processed) achieves nothing.
        http_response_code(200);

        switch ($event_type) {
            case 'checkout_session.payment.paid':
                $this->_handle_checkout_paid($event_data);
                break;
            case 'payment.failed':
                $this->_handle_payment_failed($event_data);
                break;
            default:
                log_message('info', 'PayMongo webhook: unhandled event type ' . $event_type);
        }

        echo json_encode(['received' => TRUE]);
    }

    private function _handle_checkout_paid($session_data) {
        $session_id = $session_data['id'] ?? NULL;
        if (!$session_id) {
            return;
        }

        $orders = $this->db->select('order_id')
            ->where('paymongo_checkout_session_id', $session_id)
            ->get(ORDER_TABLE)->result_array();

        if (empty($orders)) {
            log_message('error', 'PayMongo webhook: no orders found for checkout session ' . $session_id);
            return;
        }

        $payment_id = $session_data['attributes']['payments'][0]['id'] ?? NULL;
        $channel = $session_data['attributes']['payments'][0]['attributes']['source']['type'] ?? NULL;

        foreach ($orders as $row) {
            // finalizeOrderPayment() is itself idempotent, so a duplicate
            // webhook delivery for the same session safely no-ops here.
            OrderFulfillmentService::finalizeOrderPayment($row['order_id'], $payment_id, $channel);
        }
    }

    private function _handle_payment_failed($payment_data) {
        $intent_id = $payment_data['attributes']['payment_intent_id'] ?? NULL;
        if (!$intent_id) {
            return;
        }

        $payment = $this->db->where('paymongo_payment_intent_id', $intent_id)->get(PAYMENTS_TABLE)->row_array();
        if (!$payment) {
            return;
        }

        OrderFulfillmentService::markOrderPaymentFailed($payment['order_id']);
    }
}
