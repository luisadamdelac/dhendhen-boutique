<style>
    .payment-confirm-wrap {
        max-width: 480px;
        margin: 60px auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 40px 30px;
        text-align: center;
    }
    .payment-confirm-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        font-size: 28px;
    }
    .payment-confirm-icon.state-waiting { background: #eef0ff; color: #4361ee; }
    .payment-confirm-icon.state-success { background: #e8f5e9; color: #28a745; }
    .payment-confirm-icon.state-failed { background: #ffebee; color: #dc3545; }
    .payment-confirm-icon.state-waiting i { animation: pcSpin 1s linear infinite; }
    @keyframes pcSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .payment-confirm-wrap h2 { font-size: 20px; margin-bottom: 10px; }
    .payment-confirm-wrap p { color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 22px; }
    .payment-confirm-wrap .btn {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 10px;
        background: linear-gradient(135deg, #ff69b4, #ee82ee);
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }
    .payment-confirm-wrap .btn-outline {
        background: white;
        border: 2px solid #ddd;
        color: #666;
        margin-left: 10px;
    }
</style>

<div class="payment-confirm-wrap" id="paymentConfirmCard">
    <div class="payment-confirm-icon state-waiting" id="paymentConfirmIcon">
        <i class="fas fa-circle-notch"></i>
    </div>
    <h2 id="paymentConfirmTitle">Confirming your payment&hellip;</h2>
    <p id="paymentConfirmMessage">Please wait while we confirm your payment with PayMongo. This usually only takes a few seconds &mdash; don't close this page.</p>
    <div id="paymentConfirmActions"></div>
</div>

<script>
(function () {
    var sessionId = <?php echo json_encode($session_id); ?>;
    var statusUrl = '<?php echo BASE_URL; ?>checkout/status?session=' + encodeURIComponent(sessionId || '');
    var attempts = 0;
    var maxAttempts = 15; // ~30s at 2s intervals
    var pollTimer = null;

    var icon = document.getElementById('paymentConfirmIcon');
    var title = document.getElementById('paymentConfirmTitle');
    var message = document.getElementById('paymentConfirmMessage');
    var actions = document.getElementById('paymentConfirmActions');

    function showSuccess(orderId) {
        icon.className = 'payment-confirm-icon state-success';
        icon.innerHTML = '<i class="fas fa-check"></i>';
        title.textContent = 'Payment confirmed!';
        message.textContent = 'Your order has been placed successfully.';
        actions.innerHTML = '<a class="btn" href="<?php echo BASE_URL; ?>customer/orders/view/' + orderId + '">View Order</a>';
    }

    function showFailed() {
        icon.className = 'payment-confirm-icon state-failed';
        icon.innerHTML = '<i class="fas fa-times"></i>';
        title.textContent = "Payment didn't go through";
        message.textContent = 'Your payment was not completed. Your cart is still here, so you can try again.';
        actions.innerHTML = '<a class="btn" href="<?php echo BASE_URL; ?>checkout">Back to Checkout</a>';
    }

    function showTimeout() {
        icon.className = 'payment-confirm-icon state-waiting';
        icon.innerHTML = '<i class="fas fa-clock"></i>';
        title.textContent = 'Still confirming&hellip;';
        message.textContent = "This is taking longer than expected. Check My Orders shortly — we'll update it as soon as your payment is confirmed.";
        actions.innerHTML = '<a class="btn" href="<?php echo BASE_URL; ?>orders">My Orders</a>' +
            '<a class="btn btn-outline" href="<?php echo BASE_URL; ?>checkout">Back to Checkout</a>';
    }

    function poll() {
        if (!sessionId) {
            showFailed();
            return;
        }

        attempts++;
        fetch(statusUrl)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.order_status === 'paid' || data.order_status === 'processing') {
                    showSuccess(data.order_id);
                } else if (data.order_status === 'cancelled') {
                    showFailed();
                } else if (attempts >= maxAttempts) {
                    showTimeout();
                } else {
                    pollTimer = setTimeout(poll, 2000);
                }
            })
            .catch(function () {
                if (attempts >= maxAttempts) {
                    showTimeout();
                } else {
                    pollTimer = setTimeout(poll, 2000);
                }
            });
    }

    poll();
})();
</script>
