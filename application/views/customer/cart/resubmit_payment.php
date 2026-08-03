<style>
    .resubmit-wrap { max-width: 560px; margin: 30px auto; }
    .resubmit-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .rejection-banner {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--dark-gray); }
    .form-group input[type="text"] {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
    }

    @media (max-width: 600px) {
        .resubmit-wrap { margin: 16px auto; }
        .resubmit-card { padding: 16px; }
    }
</style>

<div class="resubmit-wrap">
    <h1 style="font-size:22px; margin-bottom:20px;"><i class="fas fa-redo"></i> Resubmit Payment</h1>

    <div class="resubmit-card">
        <div class="rejection-banner">
            <i class="fas fa-exclamation-circle"></i> Your previous payment proof for Order #<?php echo htmlspecialchars($order['order_number']); ?> was rejected:
            <br><strong><?php echo htmlspecialchars($payment['rejection_reason']); ?></strong>
        </div>

        <?php if (!empty($gcash_qr_code)): ?>
        <div style="background:#fafafa; border-radius:10px; padding:20px; margin-bottom:20px; text-align:center;">
            <img src="<?php echo BASE_URL . htmlspecialchars($gcash_qr_code); ?>" alt="GCash QR Code" style="width:160px; height:160px; object-fit:contain; display:block; margin:0 auto;">
        </div>
        <?php endif; ?>

        <div style="background:#fafafa; border-radius:10px; padding:15px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                <span style="color:#666; font-size:13px;">Account Name:</span>
                <span style="font-weight:600; color:#333;"><?php echo htmlspecialchars($gcash_name); ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                <span style="color:#666; font-size:13px;">GCash Number:</span>
                <span style="font-weight:600; color:#007DFF; font-size:16px;"><?php echo htmlspecialchars($gcash_number); ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0;">
                <span style="color:#666; font-size:13px;">Amount to Send:</span>
                <span style="font-weight:700; color:#007DFF; font-size:18px;">₱<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>

        <form id="resubmitForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>GCash Reference Number *</label>
                <input type="text" name="gcash_reference" id="gcash_reference" placeholder="13 digits, e.g. 1234567890123"
                       inputmode="numeric" pattern="\d{13}" maxlength="13">
                <small style="color:#888;">Exactly 13 digits, as shown in your GCash receipt.</small>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>GCash Receipt Screenshot *</label>
                <input type="file" name="gcash_receipt" id="gcash_receipt" accept="image/*"
                       style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px; font-size:13px; font-family:inherit; box-sizing:border-box; background:white;">
                <small style="color:#888;">JPG/PNG, max 2MB.</small>
            </div>
            <div id="resubmitError" style="display:none; background:#f8d7da; color:#842029; border:1px solid #f5c2c7; border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:13px;"></div>
            <button type="submit" class="btn btn-primary btn-lg w-100" id="btnResubmit">
                <i class="fas fa-paper-plane"></i> Resubmit Payment
            </button>
            <a href="<?php echo BASE_URL; ?>customer/orders/view/<?php echo $order['order_id']; ?>" style="display:block; text-align:center; margin-top:15px; color:var(--gray); text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back to Order
            </a>
        </form>
    </div>
</div>

<script>
document.getElementById('gcash_reference').addEventListener('input', function() {
    const digitsOnly = this.value.replace(/\D/g, '').slice(0, 13);
    if (digitsOnly !== this.value) this.value = digitsOnly;
});

document.getElementById('resubmitForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const ref = document.getElementById('gcash_reference').value.trim();
    const errorBox = document.getElementById('resubmitError');
    errorBox.style.display = 'none';

    if (!/^\d{13}$/.test(ref)) {
        errorBox.textContent = 'GCash Reference Number must be exactly 13 digits.';
        errorBox.style.display = 'block';
        return;
    }
    if (!document.getElementById('gcash_receipt').files.length) {
        errorBox.textContent = 'Please upload a screenshot of your GCash receipt.';
        errorBox.style.display = 'block';
        return;
    }

    const btn = document.getElementById('btnResubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

    fetch(window.location.href, { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                errorBox.textContent = data.message || 'Failed to resubmit payment.';
                errorBox.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Resubmit Payment';
            }
        })
        .catch(() => {
            errorBox.textContent = 'Something went wrong. Please try again.';
            errorBox.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Resubmit Payment';
        });
});
</script>
