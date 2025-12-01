<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Sales Reports
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Date Range Filter -->
                    <form method="get" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <a href="<?= base_url('reports/export-sales') ?>?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                               class="btn btn-success w-100">
                                <i class="bi bi-download me-2"></i>Export
                            </a>
                        </div>
                    </form>

                    <!-- Sales Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                                <div class="stats-number"><?= $sales_stats['total_sales'] ?? 0 ?></div>
                                <div class="stats-label">Total Sales</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="stats-number">₱<?= number_format($sales_stats['total_revenue'] ?? 0, 2) ?></div>
                                <div class="stats-label">Total Revenue</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <div class="stats-number">₱<?= number_format($sales_stats['total_discount'] ?? 0, 2) ?></div>
                                <div class="stats-label">Total Discount</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-calculator"></i>
                                </div>
                                <div class="stats-number">₱<?= number_format($sales_stats['average_sale'] ?? 0, 2) ?></div>
                                <div class="stats-label">Average Sale</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-arrow-up"></i>
                                </div>
                                <div class="stats-number">₱<?= number_format($sales_stats['max_sale'] ?? 0, 2) ?></div>
                                <div class="stats-label">Highest Sale</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-arrow-down"></i>
                                </div>
                                <div class="stats-number">₱<?= number_format($sales_stats['min_sale'] ?? 0, 2) ?></div>
                                <div class="stats-label">Lowest Sale</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <!-- Weekly Sales Chart -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Weekly Sales Trend</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="weeklySalesChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Top Products -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Selling Products</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($top_products)): ?>
                                        <?php foreach (array_slice($top_products, 0, 5) as $product): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <div class="fw-bold"><?= $product['product_name'] ?? 'Unknown' ?></div>
                                                    <small class="text-muted"><?= $product['category'] ?? 'N/A' ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold"><?= $product['total_quantity'] ?> units</div>
                                                    <small class="text-muted">₱<?= number_format($product['total_revenue'], 2) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center">No data available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Sales Chart
    const weeklySalesCtx = document.getElementById('weeklySalesChart').getContext('2d');
    
    <?php if (!empty($weekly_sales)): ?>
    const weeklySalesData = <?= json_encode($weekly_sales) ?>;
    
    new Chart(weeklySalesCtx, {
        type: 'line',
        data: {
            labels: weeklySalesData.map(item => item.date),
            datasets: [{
                label: 'Sales Count',
                data: weeklySalesData.map(item => item.total_sales),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }, {
                label: 'Revenue',
                data: weeklySalesData.map(item => item.total_revenue),
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Sales'
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue (₱)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
