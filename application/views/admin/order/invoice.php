<?php
$page_title = 'Invoice';

$orderStatus = $order['order_status'] ?? 'pending';
$sLabel = ucfirst($orderStatus);
switch ($orderStatus) {
    case 'pending':
        $sBadge = 'badge-warning';
        break;
    case 'processing':
        $sBadge = 'badge-info';
        break;
    case 'shipped':
    case 'to_ship':
        $sBadge = 'badge-primary';
        $sLabel = 'To Ship';
        break;
    case 'delivered':
        $sBadge = 'badge-success';
        break;
    case 'paid':
        $sBadge = 'badge-success';
        $sLabel = 'Paid';
        break;
    case 'refund_return':
        $sBadge = 'badge-warning';
        $sLabel = 'Refund/Return';
        break;
    case 'cancelled':
        $sBadge = 'badge-danger';
        break;
    default:
        $sBadge = 'badge-warning';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($order['order_id']); ?></title>
    <link href="<?php echo base_url('public/vendor/bootstrap/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('public/vendor/fontawesome/css/all.min.css'); ?>">
    <style>
        body { background: #F8FAFC; font-family: 'Poppins', 'Inter', Arial, sans-serif; color: #374151; }
        .invoice-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 0.75rem;
            box-shadow: 0 3px 10px rgba(17,24,39,0.08);
            overflow: hidden;
        }
        .invoice-card-body { padding: 2rem; }
        table.invoice-table { width: 100%; border-collapse: collapse; background: #fff; }
        table.invoice-table thead { background: #F9FAFB; }
        table.invoice-table thead th {
            padding: .75rem 1rem; text-align: left; font-weight: 600; font-size: .75rem;
            text-transform: uppercase; letter-spacing: .5px; color: #6b7280;
            border-bottom: 2px solid #E5E7EB;
        }
        table.invoice-table tbody td { padding: .75rem 1rem; font-size: .875rem; border-bottom: 1px solid #E5E7EB; }
        table.invoice-table tbody tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: .5rem 1rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .badge-primary { background: #FBCFE8; color: #DB2777; }
        .badge-success { background: rgba(40,167,69,.15); color: #28a745; }
        .badge-danger  { background: rgba(220,53,69,.15); color: #dc3545; }
        .badge-warning { background: rgba(255,193,7,.15); color: #d39e00; }
        .badge-info    { background: rgba(23,162,184,.15); color: #17a2b8; }
        .invoice-brand { font-size: 1.25rem; font-weight: 700; color: #374151; }
        .invoice-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 2px; }
        .btn-pink { background: #EC4899; color: #fff; border: none; border-radius: .625rem; padding: .5rem 1rem; font-weight: 600; }
        .btn-pink:hover { background: #DB2777; color: #fff; }
        @media print {
            body { background: #fff; }
            .invoice-card { border: none; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-end gap-2 mb-3 no-print">
            <button class="btn btn-outline-secondary" onclick="window.close()"><i class="fas fa-arrow-left me-1"></i> Back</button>
            <button class="btn btn-pink" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
        </div>
        <div class="invoice-card">
            <div class="invoice-card-body">
                <div class="d-flex justify-content-between flex-wrap mb-4">
                    <div>
                        <div class="invoice-brand">DropSell Invoice</div>
                        <p class="mb-0 text-muted">Order #<?= htmlspecialchars($order['order_id']); ?></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-1"><strong>Date:</strong> <?= !empty($order['order_date']) ? date('M d, Y', strtotime($order['order_date'])) : '-'; ?></p>
                        <p class="mb-0"><strong>Status:</strong> <span class="badge <?= $sBadge; ?>"><?= $sLabel; ?></span></p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="invoice-label">Customer</div>
                        <p class="mb-0"><?= htmlspecialchars($order['customer_name'] ?? '-'); ?></p>
                        <p class="mb-0 text-muted"><?= htmlspecialchars($order['customer_email'] ?? '-'); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="invoice-label">Reseller</div>
                        <p class="mb-0"><?= htmlspecialchars(trim(($order['reseller_first_name'] ?? '') . ' ' . ($order['reseller_last_name'] ?? '')) ?: '-'); ?></p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="invoice-table">
                        <thead>
                            <tr><th>Item</th><th>Qty</th><th>Price</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name'] ?? '-'); ?></td>
                                        <td><?= (int)($item['quantity'] ?? 0); ?></td>
                                        <td>₱<?= number_format($item['unit_price'] ?? 0, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">No items</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <div class="invoice-label">Total</div>
                    <h4 class="mb-0" style="color:#28a745;font-weight:700;">₱<?= number_format($order['total_amount'] ?? 0, 2); ?></h4>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
