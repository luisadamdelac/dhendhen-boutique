<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Thin wrapper around PayMongo's REST API (Checkout Sessions) plus webhook
 * signature verification. Static/self-contained like StockService and
 * CommissionService — safe to call from a controller or from the webhook
 * handler without any shared instance state.
 */
class PaymongoService {

    private const API_BASE = 'https://api.paymongo.com/v1';

    private static function settingsModel() {
        $CI =& get_instance();
        $CI->load->model('settings_model');
        return $CI->settings_model;
    }

    private static function secretKey() {
        return trim((string) self::settingsModel()->get('paymongo_secret_key'));
    }

    private static function webhookSecret() {
        return trim((string) self::settingsModel()->get('paymongo_webhook_secret'));
    }

    public static function isEnabled() {
        return self::settingsModel()->get('paymongo_enabled') === '1' && self::secretKey() !== '';
    }

    /**
     * Authenticated request against PayMongo's API. PayMongo uses HTTP
     * Basic Auth with the secret key as username and an empty password:
     * Authorization: Basic base64("sk_xxx:")
     */
    private static function request($method, $path, array $body = NULL) {
        $ch = curl_init(self::API_BASE . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode(self::secretKey() . ':'),
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => TRUE,
        ]);
        if ($body !== NULL) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            log_message('error', 'PayMongo cURL error: ' . $error);
            return ['ok' => FALSE, 'status' => 0, 'body' => NULL];
        }

        return [
            'ok'     => $httpCode >= 200 && $httpCode < 300,
            'status' => $httpCode,
            'body'   => json_decode($raw, TRUE),
        ];
    }

    /**
     * Create a Checkout Session covering every reseller-order-group in one
     * cart. $orderIds is embedded in metadata so the webhook has a fallback
     * lookup path alongside the checkout_session_id join on order_tbl.
     *
     * @param array $lineItems [['name' => string, 'amount' => float (PHP pesos), 'quantity' => int], ...]
     * @return array{success:bool, checkout_url?:string, session_id?:string, message?:string}
     */
    public static function createCheckoutSession(array $lineItems, array $orderIds, $customerEmail, $successUrl, $cancelUrl) {
        // PayMongo amounts are in centavos (smallest currency unit).
        $lineItemsPayload = array_map(function ($item) {
            return [
                'currency' => 'PHP',
                'amount'   => (int) round($item['amount'] * 100),
                'name'     => $item['name'],
                'quantity' => (int) $item['quantity'],
            ];
        }, $lineItems);

        $attributes = [
            'send_email_receipt'   => FALSE,
            'show_description'     => TRUE,
            'show_line_items'      => TRUE,
            'line_items'           => $lineItemsPayload,
            'payment_method_types' => ['gcash', 'card', 'paymaya', 'grab_pay'],
            'description'          => 'DropSell Order Payment',
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
            'metadata'             => ['order_ids' => implode(',', $orderIds)],
        ];
        if (!empty($customerEmail)) {
            $attributes['billing'] = ['email' => $customerEmail];
        }

        $result = self::request('POST', '/checkout_sessions', ['data' => ['attributes' => $attributes]]);

        if (!$result['ok'] || empty($result['body']['data'])) {
            $message = $result['body']['errors'][0]['detail'] ?? 'Failed to create PayMongo checkout session.';
            log_message('error', 'PayMongo checkout session creation failed: ' . json_encode($result['body']));
            return ['success' => FALSE, 'message' => $message];
        }

        $session = $result['body']['data'];
        return [
            'success'      => TRUE,
            'session_id'   => $session['id'],
            'checkout_url' => $session['attributes']['checkout_url'],
        ];
    }

    /**
     * Verify PayMongo's webhook signature.
     *
     * PayMongo sends a `Paymongo-Signature` header shaped like:
     *   t=1633000000,te=<test-mode-hmac>,li=<live-mode-hmac>
     * (only one of te/li is populated depending on whether this webhook
     * endpoint is registered in test or live mode). The signed payload is
     * the literal string "{t}.{raw_request_body}" — not the body alone —
     * hashed with HMAC-SHA256 using the webhook's signing secret, hex
     * encoded. Compared with hash_equals() to avoid timing attacks.
     */
    public static function verifyWebhookSignature($rawBody, $signatureHeader) {
        $secret = self::webhookSecret();
        if ($secret === '' || empty($signatureHeader)) {
            return FALSE;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, NULL);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? NULL;
        $providedSignature = $parts['li'] ?? $parts['te'] ?? NULL;
        if (!$timestamp || !$providedSignature) {
            return FALSE;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
        return hash_equals($expectedSignature, $providedSignature);
    }
}
