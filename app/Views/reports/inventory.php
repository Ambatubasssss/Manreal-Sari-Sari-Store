<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-boxes me-2"></i>Inventory Reports
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Inventory Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-box"></i>
                                </div>
                                <div class="stats-number"><?= $product_stats['total_products'] ?? 0 ?></div>
                                <div class="stats-label">Total Products</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="stats-number"><?= $product_stats['low_stock_count'] ?? 0 ?></div>
                                <div class="stats-label">Low Stock Items</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                                <div class="stats-number"><?= $product_stats['out_of_stock_count'] ?? 0 ?></div>
                                <div class="stats-label">Out of Stock</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="stats-number">₱<?= number_format($product_stats['total_value'] ?? 0, 2) ?></div>
                                <div class="stats-label">Total Inventory Value</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <!-- Inventory Movement Chart -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Inventory Movement Summary</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="inventoryMovementChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Low Stock Alerts -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Low Stock Alerts</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($low_stock_products)): ?>
                                        <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <div class="fw-bold"><?= $product['name'] ?></div>
                                                    <small class="text-muted"><?= $product['category'] ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-warning"><?= $product['quantity'] ?></div>
                                                    <small class="text-muted">Min: <?= $product['min_stock'] ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($low_stock_products) > 5): ?>
                                            <div class="text-center mt-2">
                                                <small class="text-muted">+<?= count($low_stock_products) - 5 ?> more items</small>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center">No low stock items</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Value Changes Chart -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Inventory Value Changes Over Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="inventoryValueChart" height="100"></canvas>
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
    // Inventory Movement Chart
    const movementCtx = document.getElementById('inventoryMovementChart').getContext('2d');
    
    <?php if (!empty($inventory_movement)): ?>
    const movementData = <?= json_encode($inventory_movement) ?>;
    
    new Chart(movementCtx, {
        type: 'doughnut',
        data: {
            labels: movementData.map(item => item.action_type.charAt(0).toUpperCase() + item.action_type.slice(1)),
            datasets: [{
                data: movementData.map(item => item.total_transactions),
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#4facfe'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Inventory Value Changes Chart
    const valueCtx = document.getElementById('inventoryValueChart').getContext('2d');
    
    <?php if (!empty($inventory_value_changes)): ?>
    const valueData = <?= json_encode($inventory_value_changes) ?>;
    
    new Chart(valueCtx, {
        type: 'line',
        data: {
            labels: valueData.map(item => item.date),
            datasets: [{
                label: 'Value In',
                data: valueData.map(item => item.value_in),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Value Out',
                data: valueData.map(item => item.value_out),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
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
                        text: 'Value (₱)'
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
