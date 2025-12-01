<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-star me-2"></i>Top Selling Products
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
                        <div class="col-md-2">
                            <label for="limit" class="form-label">Limit</label>
                            <select class="form-select" id="limit" name="limit">
                                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>Top 10</option>
                                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>Top 20</option>
                                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>Top 50</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>

                    <!-- Top Products Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top <?= $limit ?> Selling Products</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($top_products)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Product Code</th>
                                                <th>Product Name</th>
                                                <th>Category</th>
                                                <th>Quantity Sold</th>
                                                <th>Total Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $rank = 1; foreach ($top_products as $product): ?>
                                                <tr>
                                                    <td><strong>#<?= $rank++ ?></strong></td>
                                                    <td><code><?= $product['product_code'] ?? 'N/A' ?></code></td>
                                                    <td><?= $product['product_name'] ?? 'Unknown' ?></td>
                                                    <td><span class="badge bg-secondary"><?= $product['category'] ?? 'N/A' ?></span></td>
                                                    <td><strong><?= number_format($product['total_quantity'] ?? 0) ?></strong> units</td>
                                                    <td><strong>₱<?= number_format($product['total_revenue'] ?? 0, 2) ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">No sales found for this period</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

