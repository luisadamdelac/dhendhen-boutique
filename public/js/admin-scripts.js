// DropSell Dashboard JavaScript
// BASE_URL is defined in footer.php before this script loads

// Self-contained confirm modal (inline-styled, no external CSS dependency)
// so it renders identically across every role's stylesheet (admin-style.css
// or style.css). Use in place of the native confirm() dialog.
function customConfirm(message, onConfirm, opts) {
    opts = opts || {};

    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px;';

    const box = document.createElement('div');
    box.style.cssText = 'background:#fff;width:100%;max-width:380px;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.2);padding:24px;font-family:inherit;';

    const title = document.createElement('h4');
    title.textContent = opts.title || 'Please Confirm';
    title.style.cssText = 'margin:0 0 10px;font-size:1.05rem;color:#1a1a2e;font-weight:700;';

    const msg = document.createElement('p');
    msg.textContent = message;
    msg.style.cssText = 'margin:0 0 20px;font-size:.9rem;color:#475569;line-height:1.5;';

    const actions = document.createElement('div');
    actions.style.cssText = 'display:flex;justify-content:flex-end;gap:10px;';

    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.textContent = opts.cancelText || 'Cancel';
    cancelBtn.style.cssText = 'padding:9px 18px;border-radius:8px;border:1px solid #ddd;background:#fff;color:#333;font-weight:600;cursor:pointer;';

    const okBtn = document.createElement('button');
    okBtn.type = 'button';
    okBtn.textContent = opts.okText || 'OK';
    okBtn.style.cssText = 'padding:9px 18px;border-radius:8px;border:none;background:#EC4899;color:#fff;font-weight:600;cursor:pointer;';

    actions.appendChild(cancelBtn);
    actions.appendChild(okBtn);
    box.appendChild(title);
    box.appendChild(msg);
    box.appendChild(actions);
    overlay.appendChild(box);
    document.body.appendChild(overlay);

    function close() { overlay.remove(); }
    cancelBtn.addEventListener('click', function() { close(); if (opts.onCancel) opts.onCancel(); });
    okBtn.addEventListener('click', function() { close(); onConfirm(); });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) { close(); if (opts.onCancel) opts.onCancel(); } });
}

// Self-contained info/alert modal — same look as customConfirm() but with a
// single OK button, for messages that don't need a Cancel option. Use in
// place of the native alert() dialog.
function customAlert(message, opts) {
    opts = opts || {};

    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px;';

    const box = document.createElement('div');
    box.style.cssText = 'background:#fff;width:100%;max-width:380px;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.2);padding:24px;font-family:inherit;';

    const title = document.createElement('h4');
    title.textContent = opts.title || 'Notice';
    title.style.cssText = 'margin:0 0 10px;font-size:1.05rem;color:#1a1a2e;font-weight:700;';

    const msg = document.createElement('p');
    msg.textContent = message;
    msg.style.cssText = 'margin:0 0 20px;font-size:.9rem;color:#475569;line-height:1.5;';

    const actions = document.createElement('div');
    actions.style.cssText = 'display:flex;justify-content:flex-end;';

    const okBtn = document.createElement('button');
    okBtn.type = 'button';
    okBtn.textContent = opts.okText || 'OK';
    okBtn.style.cssText = 'padding:9px 18px;border-radius:8px;border:none;background:#EC4899;color:#fff;font-weight:600;cursor:pointer;';

    actions.appendChild(okBtn);
    box.appendChild(title);
    box.appendChild(msg);
    box.appendChild(actions);
    overlay.appendChild(box);
    document.body.appendChild(overlay);

    function close() { overlay.remove(); if (opts.onClose) opts.onClose(); }
    okBtn.addEventListener('click', close);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });
}

// For "remove"-style links that must confirm before navigating — customConfirm
// is async (unlike native confirm()), so the link's default navigation is
// prevented and re-triggered manually once the user confirms.
function confirmRemovePhoto(event, link, message) {
    event.preventDefault();
    customConfirm(message || 'Remove your profile photo?', function() {
        window.location.href = link.href;
    });
    return false;
}

// Generic version of the above for any confirm-before-navigate link.
function confirmNavigate(event, link, message, title) {
    event.preventDefault();
    customConfirm(message, function() { window.location.href = link.href; }, { title: title });
    return false;
}

// Generic confirm-before-submit for a <form onsubmit="return ...">.
function confirmSubmit(event, form, message, title) {
    event.preventDefault();
    customConfirm(message, function() { form.submit(); }, { title: title });
    return false;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeDataTables();
    initializeModals();
    initializeSidebarActiveState();
    initResponsiveTableStacking();
});

// Mobile "stacked card" tables — copies each <thead> column's text onto the
// matching <td> as a data-label attribute, so a table only needs
// class="table-stack" on the <table> itself; the CSS (.table-stack in
// admin-style.css / style.css) does the rest at narrow widths. Re-run this
// after dynamically adding/removing rows to a table.table-stack.
function initResponsiveTableStacking() {
    document.querySelectorAll('table.table-stack').forEach(function(table) {
        const headers = Array.from(table.querySelectorAll('thead th')).map(function(th) {
            return th.textContent.trim();
        });
        if (!headers.length) return;
        table.querySelectorAll('tbody tr').forEach(function(tr) {
            // A colspan row (e.g. a section-group divider) doesn't map to a
            // single header — leave it unlabeled rather than mislabel it.
            if (tr.children.length === 1 && tr.children[0].colSpan > 1) return;
            Array.from(tr.children).forEach(function(td, i) {
                if (headers[i]) td.setAttribute('data-label', headers[i]);
            });
        });
    });
}

// Event Listeners
function initializeEventListeners() {
    // Confirmation dialogs for delete actions
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this item?')) {
                window.location.href = this.href;
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Form Validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = 'var(--danger)';
            
            // Remove error styling on input
            input.addEventListener('input', function() {
                this.style.borderColor = '';
            });
        }
    });
    
    if (!isValid) {
        alert('Please fill in all required fields');
    }
    
    return isValid;
}

// DataTables Enhancement
function initializeDataTables() {
    // Add search and sort functionality to tables
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        addTableFeatures(table);
    });
}

function initializeSidebarActiveState() {
    // Improve sidebar active highlighting consistency
    // Uses: data-sidebar attribute on sidebar links
    try {
        const current = (window.location.pathname || '').toLowerCase();
        const links = document.querySelectorAll('a.nav-link[data-sidebar]');
        if (!links || links.length === 0) return;

        links.forEach(link => {
            const page = (link.getAttribute('data-sidebar') || '').toLowerCase();
            const isActive = current.includes('/' + page);
            if (isActive) link.classList.add('active');
            else link.classList.remove('active');
        });
    } catch (e) {
        // no-op
    }
}

function addTableFeatures(table) {
    // Add search functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Search...';
    searchInput.className = 'form-control';
    searchInput.style.maxWidth = '300px';
    searchInput.style.marginBottom = '15px';
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    if (table.previousElementSibling && table.previousElementSibling.classList.contains('table-responsive')) {
        table.previousElementSibling.insertBefore(searchInput, table);
    }
}

// Modal Functions
function initializeModals() {
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });
    
    // Close modal buttons
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modal) {
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// AJAX Form Submission
function submitFormAjax(formId, callback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = this.action;
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (callback) {
                callback(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
}

// Stock Update Function
function updateStock(productId, quantity, operation) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('operation', operation);
    
    fetch(BASE_URL + 'product/updateStock', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Stock updated successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message || 'Failed to update stock');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred');
    });
}

// Commission OTP Request
function requestCommissionOTP(commissionId) {
    const formData = new FormData();
    formData.append('commission_id', commissionId);
    
    fetch(BASE_URL + 'commission/requestOtp', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const otp = prompt('Enter OTP (sent to your registered contact):\n\nFor testing: ' + data.otp);
            if (otp) {
                verifyCommissionOTP(commissionId, otp);
            }
        } else {
            showAlert('danger', data.message || 'Failed to send OTP');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred');
    });
}

// Verify Commission OTP
function verifyCommissionOTP(commissionId, otp) {
    const formData = new FormData();
    formData.append('commission_id', commissionId);
    formData.append('otp', otp);
    
    fetch(BASE_URL + 'commission/approvePayout', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Commission payout approved successfully');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Invalid OTP');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred');
    });
}

// Show Alert Function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fade-in`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
}

// Export to PDF/Excel
function exportTable(format) {
    // Build the export URL using the admin base path
    var url = window.location.href;
    var adminIndex = url.indexOf('admin/');
    
    if (adminIndex === -1) {
        adminIndex = url.indexOf('admin');
    }
    
    var base = url.substring(0, adminIndex + 6); // includes 'admin/'
    
    // Determine which controller's export to call based on current page
    if (url.indexOf('product') !== -1 || url.indexOf('inventory') !== -1 || url.indexOf('/admin/$') !== -1) {
        window.location.href = base + 'product/export/' + format;
    } else if (url.indexOf('reseller') !== -1) {
        window.location.href = base + 'reseller/export/' + format;
    } else if (url.indexOf('user') !== -1) {
        window.location.href = base + 'user/export/' + format;
    } else if (url.indexOf('order') !== -1) {
        window.location.href = base + 'order/export/' + format;
    } else {
        // Default to product/inventory export
        window.location.href = base + 'product/export/' + format;
    }
}

// Image Preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Number Formatting
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatNumber(number) {
    return parseInt(number).toLocaleString('en-US');
}

// Date Formatting
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Loading Overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Bulk Actions
function bulkAction(action, selectedIds) {
    if (selectedIds.length === 0) {
        showAlert('danger', 'Please select at least one item');
        return;
    }
    
    if (confirm('Are you sure you want to ' + action + ' ' + selectedIds.length + ' item(s)? This action cannot be undone.')) {
        showLoading();
        
        // Determine the correct endpoint based on current page
        var url = window.location.href;
        var adminIndex = url.indexOf('admin/');
        if (adminIndex === -1) adminIndex = url.indexOf('admin');
        var base = url.substring(0, adminIndex + 6);
        
        var endpoint = base + 'product/bulkDelete';
        if (url.indexOf('reseller') !== -1) {
            endpoint = base + 'reseller/bulkDelete';
        } else if (url.indexOf('user') !== -1) {
            endpoint = base + 'user/bulkDelete';
        } else if (url.indexOf('order') !== -1) {
            endpoint = base + 'order/bulkDelete';
        }
        
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: action, ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', data.message || 'Operation failed');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while processing the request');
        });
    }
}

// Real-time Search
let searchTimeout;
function liveSearch(input, url, resultContainer) {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        const searchTerm = input.value.trim();
        
        if (searchTerm.length < 2) {
            resultContainer.innerHTML = '';
            return;
        }
        
        fetch(`${url}?search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data, resultContainer);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }, 300);
}

function displaySearchResults(data, container) {
    container.innerHTML = '';
    
    if (data.results && data.results.length > 0) {
        data.results.forEach(result => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.innerHTML = result.html;
            container.appendChild(item);
        });
    } else {
        container.innerHTML = '<p class="text-center">No results found</p>';
    }
}

// Notification System
function checkNotifications() {
    const apiUrl = (typeof BASE_URL !== 'undefined') ?
        BASE_URL + 'index.php/api/notifications?scope=admin' :
        '/Dropshipping_System/DropSell/index.php/api/notifications?scope=admin';
    
    fetch(apiUrl)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON. Received: ' + contentType);
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            updateNotificationBadge(data.count);
        }
    })
    .catch(error => {
        console.warn('Notification check failed:', error);
        // Fail silently - don't break page if API fails
    });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('#notificationBadge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

// checkNotifications()/initNotificationDropdowns() are hardcoded to
// scope=admin and admin/notification/* endpoints — admin-scripts.js is now
// shared by every role's layout, so this must only run on actual admin
// pages. Each role's session cookie persists independently (by design, for
// same-browser multi-role testing), so on a staff/reseller/customer page
// this would otherwise silently authenticate as whatever admin account is
// still logged in in another tab and overwrite that page's own
// #notificationBadge with the admin's unread count.
if (window.location.pathname.toLowerCase().indexOf('/admin/') !== -1 ||
    /\/admin$/.test(window.location.pathname.toLowerCase())) {
    // Check notifications every 30 seconds
    setInterval(checkNotifications, 30000);

    // Check on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkNotifications();
        initNotificationDropdowns();
    });
}

initializeSidebarActiveState();

// --- Header notification / order-alert dropdowns ---
function notifTypeToUrl(type) {
    const base = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/Dropshipping_System/DropSell/';
    switch (type) {
        case 'order':
        case 'payment':
            return base + 'admin/order';
        case 'withdrawal':
            return base + 'admin/withdrawal';
        case 'refund':
            return base + 'admin/refund';
        default:
            return base + 'admin/notification';
    }
}

function notifTimeAgo(dateStr) {
    const then = new Date(dateStr.replace(' ', 'T'));
    const diffSec = Math.floor((Date.now() - then.getTime()) / 1000);
    if (diffSec < 60) return 'just now';
    if (diffSec < 3600) return Math.floor(diffSec / 60) + 'm ago';
    if (diffSec < 86400) return Math.floor(diffSec / 3600) + 'h ago';
    return Math.floor(diffSec / 86400) + 'd ago';
}

function renderNotificationList(container, notifications, emptyText) {
    if (!notifications || !notifications.length) {
        container.innerHTML = '<div style="padding:24px;text-align:center;color:#999;font-size:13px;">' + emptyText + '</div>';
        return;
    }

    container.innerHTML = notifications.map(function (n) {
        const unread = parseInt(n.is_read, 10) === 0;
        return '<div class="notif-item" data-id="' + n.notification_id + '" data-type="' + (n.type || 'system') + '" '
            + 'style="padding:12px 16px;border-bottom:1px solid #f5f5f5;cursor:pointer;' + (unread ? 'background:#fff7fb;' : '') + '">'
            + '<div style="display:flex;gap:8px;align-items:flex-start;">'
            + (unread ? '<span style="width:8px;height:8px;border-radius:50%;background:#ff69b4;margin-top:5px;flex-shrink:0;"></span>' : '<span style="width:8px;flex-shrink:0;"></span>')
            + '<div style="min-width:0;">'
            + '<div style="font-size:13px;color:#333;' + (unread ? 'font-weight:600;' : '') + 'word-break:break-word;">' + escapeHtmlNotif(n.title || 'Notification') + '</div>'
            + '<div style="font-size:12px;color:#777;margin-top:2px;word-break:break-word;">' + escapeHtmlNotif(n.message || '') + '</div>'
            + '<div style="font-size:11px;color:#aaa;margin-top:4px;">' + notifTimeAgo(n.created_at) + '</div>'
            + '</div></div></div>';
    }).join('');

    container.querySelectorAll('.notif-item').forEach(function (el) {
        el.addEventListener('click', function () {
            const id = this.dataset.id;
            const type = this.dataset.type;
            fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '/Dropshipping_System/DropSell/') + 'admin/notification/mark_as_read/' + id, { method: 'POST' })
                .finally(function () { window.location.href = notifTypeToUrl(type); });
        });
    });
}

function escapeHtmlNotif(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function initNotificationDropdowns() {
    const bellIcon = document.getElementById('notificationIcon');
    const bellPanel = document.getElementById('notificationPanel');
    const msgIcon = document.getElementById('messageIcon');
    const msgPanel = document.getElementById('messagePanel');
    const base = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/Dropshipping_System/DropSell/';

    if (!bellIcon || !msgIcon) return;

    function closeAllPanels() {
        bellPanel.style.display = 'none';
        msgPanel.style.display = 'none';
    }

    function loadBellPanel() {
        const list = document.getElementById('notificationList');
        list.innerHTML = '<div style="padding:20px;text-align:center;color:#999;font-size:13px;">Loading…</div>';
        fetch(base + 'admin/notification/recent')
            .then(r => r.json())
            .then(data => renderNotificationList(list, data.notifications, 'No notifications yet.'))
            .catch(() => { list.innerHTML = '<div style="padding:20px;text-align:center;color:#c00;font-size:13px;">Failed to load.</div>'; });
    }

    function loadMessagePanel() {
        const list = document.getElementById('messageList');
        list.innerHTML = '<div style="padding:20px;text-align:center;color:#999;font-size:13px;">Loading…</div>';
        fetch(base + 'admin/notification/recent/order')
            .then(r => r.json())
            .then(data => renderNotificationList(list, data.notifications, 'No order alerts yet.'))
            .catch(() => { list.innerHTML = '<div style="padding:20px;text-align:center;color:#c00;font-size:13px;">Failed to load.</div>'; });
    }

    bellIcon.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = bellPanel.style.display === 'block';
        closeAllPanels();
        if (!isOpen) { bellPanel.style.display = 'block'; loadBellPanel(); }
    });

    msgIcon.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = msgPanel.style.display === 'block';
        closeAllPanels();
        if (!isOpen) { msgPanel.style.display = 'block'; loadMessagePanel(); }
    });

    document.addEventListener('click', closeAllPanels);
    bellPanel.addEventListener('click', e => e.stopPropagation());
    msgPanel.addEventListener('click', e => e.stopPropagation());

    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            markAllBtn.disabled = true;
            fetch(base + 'admin/notification/mark_all_as_read', { method: 'POST' })
                .then(r => r.json())
                .then(() => {
                    updateNotificationBadge(0);
                    loadBellPanel();
                })
                .finally(() => { markAllBtn.disabled = false; });
        });
    }

    const markAllOrdersBtn = document.getElementById('markAllReadOrdersBtn');
    if (markAllOrdersBtn) {
        markAllOrdersBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            markAllOrdersBtn.disabled = true;
            fetch(base + 'admin/notification/mark_all_as_read/order', { method: 'POST' })
                .then(r => r.json())
                .then(() => {
                    const msgBadge = document.getElementById('messageBadge');
                    if (msgBadge) { msgBadge.textContent = '0'; msgBadge.style.display = 'none'; }
                    loadMessagePanel();
                })
                .finally(() => { markAllOrdersBtn.disabled = false; });
        });
    }
}

// Print Function
function printContent(elementId) {
    const content = document.getElementById(elementId);
    if (content) {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link rel="stylesheet" href="' + BASE_URL + 'public/css/admin-style.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
}
