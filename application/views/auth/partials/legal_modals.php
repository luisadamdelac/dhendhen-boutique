<?php
/**
 * Shared Terms of Service + Privacy Policy modal content, included from
 * every registration view (customer + reseller) so the "Terms of Service"
 * / "Privacy Policy" links in the agreement checkbox are real, readable
 * documents instead of href="#" placeholders. Pure markup/JS — no routes,
 * controllers, or form fields are affected.
 */
?>
<div class="legal-modal-overlay" id="termsModalOverlay" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle" hidden>
    <div class="legal-modal">
        <div class="legal-modal-header">
            <h3 id="termsModalTitle"><i class="fas fa-file-contract"></i> Terms of Service</h3>
            <button type="button" class="legal-modal-close" data-close-modal="termsModalOverlay" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="legal-modal-body">
            <p class="legal-updated">Last updated: July 2026</p>

            <h4>1. Acceptance of Terms</h4>
            <p>By creating an account or using DropSell in any capacity — as a Customer, Reseller, Staff, or Administrator — you agree to be bound by these Terms of Service. If you do not agree, please do not register or use the platform.</p>

            <h4>2. Account Registration &amp; Eligibility</h4>
            <p>You must provide accurate, current, and complete information when registering. You are responsible for safeguarding your password and for all activity that occurs under your account. Reseller applications are subject to review and approval by DropSell administrators before login access is granted.</p>

            <h4>3. Reseller Program &amp; Commissions</h4>
            <p>Approved Resellers may publish products from the central catalog to their own mini-shop at a price they choose, subject to the platform's minimum price floor. Commission is calculated per the rate configured by DropSell at the time of sale. Once the order has successfully reached the "Delivered" status, the reseller automatically receives their commission, which is credited to their dedicated internal E-wallet. Commission may be reversed if the related order is later cancelled or refunded.</p>

            <h4>4. Orders &amp; Payments</h4>
            <p>All payments on DropSell are currently processed via GCash. Customers are responsible for sending the correct amount and providing an accurate GCash reference number; orders may be delayed or cancelled if payment cannot be verified. Prices, delivery fees, and available delivery methods (pickup or Pasabay via Jeep) are shown at checkout before an order is placed.</p>

            <h4>5. Returns &amp; Refunds</h4>
            <p>Refund requests may be submitted for eligible delivered orders through your account, along with a reason and any supporting evidence. Refund requests are reviewed by an Administrator, who may approve or reject the request. Approved refunds may result in stock being restored and any related commission being reversed.</p>

            <h4>6. Withdrawals (Resellers)</h4>
            <p>Resellers may request withdrawal of their available commission balance via GCash. Withdrawals are subject to a minimum amount, a scheduled processing date, and one-time password (OTP) verification before an Administrator can approve disbursement.</p>

            <h4>7. Prohibited Conduct</h4>
            <p>You may not use DropSell to list counterfeit or prohibited goods, submit fraudulent payment references, misrepresent your identity, or attempt to interfere with the security or normal operation of the platform.</p>

            <h4>8. Suspension &amp; Termination</h4>
            <p>DropSell may suspend or terminate an account that violates these Terms, engages in fraudulent activity, or poses a risk to other users of the platform.</p>

            <h4>9. Limitation of Liability</h4>
            <p>DropSell is provided on an "as is" basis. To the fullest extent permitted by law, DropSell is not liable for indirect or incidental damages arising from your use of the platform.</p>

            <h4>10. Changes to These Terms</h4>
            <p>These Terms may be updated from time to time. Continued use of DropSell after changes take effect constitutes acceptance of the revised Terms.</p>

            <h4>11. Contact</h4>
            <p>Questions about these Terms can be directed to the DropSell support team through your account or the contact details provided on the platform.</p>
        </div>
        <div class="legal-modal-footer">
            <button type="button" class="legal-modal-btn" data-close-modal="termsModalOverlay">
                <i class="fas fa-check"></i> I Understand
            </button>
        </div>
    </div>
</div>

<div class="legal-modal-overlay" id="privacyModalOverlay" role="dialog" aria-modal="true" aria-labelledby="privacyModalTitle" hidden>
    <div class="legal-modal">
        <div class="legal-modal-header">
            <h3 id="privacyModalTitle"><i class="fas fa-user-shield"></i> Privacy Policy</h3>
            <button type="button" class="legal-modal-close" data-close-modal="privacyModalOverlay" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="legal-modal-body">
            <p class="legal-updated">Last updated: July 2026</p>

            <h4>1. Information We Collect</h4>
            <p>When you register or use DropSell, we collect information such as your name, email address, contact number, delivery address (province, municipality, barangay, street), and — for Resellers — your business name and GCash payout details. We also record order history, cart activity, and account activity logs (e.g. logins, status changes).</p>

            <h4>2. How We Use Your Information</h4>
            <p>We use your information to create and manage your account, process orders and payments, attribute sales to the correct Reseller, calculate and credit commissions, deliver order and refund status notifications, and improve the platform's products and services.</p>

            <h4>3. Payment Information</h4>
            <p>DropSell does not store GCash account passwords or PINs. We only retain the GCash reference number, sender number, and account name you provide to verify a payment, and — for Reseller payouts — the GCash number and account name used for withdrawals.</p>

            <h4>4. Information Sharing</h4>
            <p>Your order details (name, delivery address, and contact number) are shared with the Reseller and Staff involved in fulfilling that specific order. We do not sell your personal information to third parties.</p>

            <h4>5. Data Retention</h4>
            <p>We retain account and order information for as long as your account is active and as needed to comply with legal, accounting, or dispute-resolution obligations.</p>

            <h4>6. Security</h4>
            <p>Passwords are stored using industry-standard hashing. Each role (Customer, Reseller, Staff, Administrator) uses an isolated session, and sensitive actions such as commission withdrawals require OTP verification.</p>

            <h4>7. Cookies &amp; Sessions</h4>
            <p>DropSell uses session cookies to keep you signed in and to remember your shopping cart. These cookies are necessary for the platform to function and are not used for third-party advertising.</p>

            <h4>8. Your Rights</h4>
            <p>You may review and update your account information at any time from your account settings. You may request the closure of your account by contacting DropSell support.</p>

            <h4>9. Children's Privacy</h4>
            <p>DropSell is not directed at children under 13, and we do not knowingly collect personal information from children under 13.</p>

            <h4>10. Changes to This Policy</h4>
            <p>We may update this Privacy Policy from time to time. Material changes will be reflected by updating the "Last updated" date above.</p>

            <h4>11. Contact</h4>
            <p>For questions about this Privacy Policy or your personal information, please contact the DropSell support team through your account.</p>
        </div>
        <div class="legal-modal-footer">
            <button type="button" class="legal-modal-btn" data-close-modal="privacyModalOverlay">
                <i class="fas fa-check"></i> I Understand
            </button>
        </div>
    </div>
</div>

<style>
    .legal-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(20, 12, 24, 0.55);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 1000;
        animation: legalFadeIn 200ms ease;
    }
    .legal-modal-overlay[hidden] { display: none; }

    @keyframes legalFadeIn { from { opacity: 0; } to { opacity: 1; } }

    .legal-modal {
        background: #fff;
        border-radius: 18px;
        max-width: 560px;
        width: 100%;
        max-height: 82vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 30px 70px rgba(0,0,0,0.28);
        animation: legalSlideUp 250ms ease;
    }

    @keyframes legalSlideUp {
        from { opacity: 0; transform: translateY(16px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    @media (prefers-reduced-motion: reduce) {
        .legal-modal-overlay, .legal-modal { animation: none; }
    }

    .legal-modal-header {
        padding: 20px 22px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .legal-modal-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #24202b;
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .legal-modal-header h3 i { color: #ff69b4; }

    .legal-modal-close {
        background: #f6f6f8;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #666;
        cursor: pointer;
        font-size: 13px;
        flex-shrink: 0;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .legal-modal-close:hover { background: #ffe4ef; color: #ff69b4; }

    .legal-modal-body {
        padding: 6px 22px 18px;
        overflow-y: auto;
        font-size: 13px;
        line-height: 1.65;
        color: #444;
    }
    .legal-modal-body .legal-updated { color: #999; font-size: 11.5px; margin: 10px 0 16px; }
    .legal-modal-body h4 { font-size: 13.5px; font-weight: 700; color: #24202b; margin: 16px 0 6px; }
    .legal-modal-body h4:first-of-type { margin-top: 0; }
    .legal-modal-body p { margin-bottom: 4px; }

    .legal-modal-footer {
        padding: 16px 22px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: flex-end;
    }

    .legal-modal-btn {
        border: none;
        background: linear-gradient(135deg, #ff69b4 0%, #ee82ee 50%, #9370db 100%);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 13.5px;
        padding: 10px 24px;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 200ms ease, box-shadow 200ms ease;
    }
    .legal-modal-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(255, 105, 180, 0.3); }
    .legal-modal-btn:active { transform: translateY(0); }
</style>

<script>
    (function() {
        function openModal(id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.hidden = false;
            document.body.style.overflow = 'hidden';
            var closeBtn = el.querySelector('.legal-modal-close');
            if (closeBtn) closeBtn.focus();
        }

        function closeModal(id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.hidden = true;
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-legal="terms"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('termsModalOverlay');
            });
        });

        document.querySelectorAll('[data-legal="privacy"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('privacyModalOverlay');
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                closeModal(btn.getAttribute('data-close-modal'));
            });
        });

        document.querySelectorAll('.legal-modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) closeModal(overlay.id);
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.legal-modal-overlay').forEach(function(overlay) {
                if (!overlay.hidden) closeModal(overlay.id);
            });
        });
    })();
</script>
