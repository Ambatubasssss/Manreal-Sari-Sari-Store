<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-graph-up me-2"></i>Reports</h2>
</div>

<div class="row">
    <!-- Sales Reports -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Sales Reports</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= base_url('reports/sales') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-bar-chart me-2"></i>Sales Overview
                    </a>
                    <a href="<?= base_url('reports/daily-sales') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-day me-2"></i>Daily Sales
                    </a>
                    <a href="<?= base_url('reports/weekly-sales') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-week me-2"></i>Weekly Sales
                    </a>
                    <a href="<?= base_url('reports/monthly-sales') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-month me-2"></i>Monthly Sales
                    </a>
                    <a href="<?= base_url('reports/top-products') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-star me-2"></i>Top Selling Products
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventory Reports -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Reports</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= base_url('reports/inventory') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-box me-2"></i>Inventory Overview
                    </a>
                    <a href="<?= base_url('reports/low-stock') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts
                    </a>
                    <a href="<?= base_url('reports/inventory-movement') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-left-right me-2"></i>Inventory Movement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-cart-check text-primary" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Today's Sales</h5>
                <p class="card-text h4">₱<?= number_format($today_sales ?? 0, 2) ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-graph-up text-success" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">This Month</h5>
                <p class="card-text h4">₱<?= number_format($monthly_sales ?? 0, 2) ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-box-seam text-warning" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Low Stock</h5>
                <p class="card-text h4"><?= $low_stock_count ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Total Users</h5>
                <p class="card-text h4"><?= $total_users ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
