<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-label">Today's Sales</div>
                    <div class="stats-number"><?= $stats['today_sales'] ?></div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-label">Today's Revenue</div>
                    <div class="stats-number">₱<?= number_format($stats['today_revenue'], 2) ?></div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-label">Monthly Revenue</div>
                    <div class="stats-number">₱<?= number_format($stats['monthly_revenue'], 2) ?></div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-label">Total Products</div>
                    <div class="stats-number"><?= $stats['total_products'] ?></div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Additional Stats -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Sales Trend (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock_products)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">All products are well stocked!</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div>
                                    <div class="fw-bold"><?= $product['name'] ?></div>
                                    <small class="text-muted"><?= $product['product_code'] ?></small>
                                </div>
                                <span class="badge bg-warning text-dark">
                                    <?= $product['quantity'] ?> left
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($low_stock_products) > 5): ?>
                        <div class="text-center mt-2">
                            <a href="<?= base_url('products') ?>" class="btn btn-sm btn-outline-primary">
                                View All (<?= count($low_stock_products) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity and Top Products -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Sales</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_sales)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No recent sales</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('sales/show/' . $sale['id']) ?>" class="text-decoration-none">
                                                <?= $sale['sale_number'] ?>
                                            </a>
                                        </td>
                                        <td><?= $sale['customer_name'] ?: 'Walk-in' ?></td>
                                        <td class="fw-bold">₱<?= number_format($sale['total_amount'], 2) ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, H:i', strtotime($sale['created_at'])) ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-2">
                        <a href="<?= base_url('sales') ?>" class="btn btn-sm btn-outline-primary">View All Sales</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-star me-2"></i>Top Selling Products</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_selling_products)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No sales data available</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($top_selling_products as $index => $product): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">#<?= $index + 1 ?></span>
                                    <div>
                                        <div class="fw-bold"><?= $product['product_name'] ?></div>
                                        <small class="text-muted"><?= $product['category'] ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold"><?= $product['total_quantity'] ?> sold</div>
                                    <small class="text-muted">₱<?= number_format($product['total_revenue'], 2) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('pos') ?>" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-cart-check d-block mb-2" style="font-size: 2rem;"></i>
                            New Sale
                        </a>
                    </div>
                    
                    <?php if ($is_admin): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('products/create') ?>" class="btn btn-success w-100 py-3">
                            <i class="bi bi-plus-circle d-block mb-2" style="font-size: 2rem;"></i>
                            Add Product
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('reports/sales') ?>" class="btn btn-info w-100 py-3">
                            <i class="bi bi-graph-up d-block mb-2" style="font-size: 2rem;"></i>
                            Sales Report
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('reports/inventory') ?>" class="btn btn-warning w-100 py-3">
                            <i class="bi bi-box-seam d-block mb-2" style="font-size: 2rem;"></i>
                            Inventory Report
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('sales') ?>" class="btn btn-info w-100 py-3">
                            <i class="bi bi-receipt d-block mb-2" style="font-size: 2rem;"></i>
                            View Sales
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle d-block mb-2" style="font-size: 2rem;"></i>
                            <p class="mb-0">Contact admin for additional features</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($sales_chart_data['labels']) ?>,
        datasets: [{
            label: 'Daily Revenue (₱)',
            data: <?= json_encode($sales_chart_data['data']) ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Auto-refresh dashboard data every 30 seconds
setInterval(function() {
    fetch('<?= base_url('dashboard/data') ?>')
        .then(response => response.json())
        .then(data => {
            // Update stats cards
            if (data.stats) {
                // You can update the stats here if needed
            }
        })
        .catch(error => console.log('Dashboard refresh failed:', error));
}, 30000);
</script>
<?= $this->endSection() ?>
