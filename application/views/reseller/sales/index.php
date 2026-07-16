<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-chart-line"></i> Sales Report</h3>
    </div>
    <div class="card-body">
        <!-- Monthly Sales -->
        <h4 style="margin-bottom: 1rem;">Monthly Sales (Last 6 Months)</h4>
        <?php if (!empty($salesData['monthly'])): ?>
            <div class="table-responsive" style="margin-bottom: 3rem;">
                <table class="table table-striped table-stack">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Orders</th>
                            <th>Total Sales</th>
                            <th>Total Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salesData['monthly'] as $monthly): ?>
                            <tr>
                                <td><?php echo date('F Y', strtotime($monthly['month'] . '-01')); ?></td>
                                <td><?php echo number_format($monthly['total_orders']); ?></td>
                                <td>₱<?php echo number_format($monthly['total_sales'], 2); ?></td>
                                <td>₱<?php echo number_format($monthly['total_commission'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem; margin-bottom: 3rem;">No sales data available</p>
        <?php endif; ?>

        <!-- Top Selling Products -->
        <h4 style="margin-bottom: 1rem;">Top Selling Products</h4>
        <?php if (!empty($salesData['topProducts'])): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                <?php foreach ($salesData['topProducts'] as $product): ?>
                    <div style="border: 1px solid var(--gray-300); border-radius: 0.5rem; padding: 1rem;">
                        <h5 style="margin: 0 0 0.5rem 0; font-size: 1rem;">
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </h5>
                        <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: var(--gray-600);">
                            <span>Sold: <strong><?php echo number_format($product['total_sold']); ?></strong></span>
                            <span>Revenue: <strong>₱<?php echo number_format($product['revenue'], 2); ?></strong></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No product sales data available</p>
        <?php endif; ?>
    </div>
</div>
