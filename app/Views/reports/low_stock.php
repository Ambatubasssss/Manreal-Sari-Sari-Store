<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Low Stock Products Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Products with Low Stock</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($low_stock_products)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product Code</th>
                                                <th>Product Name</th>
                                                <th>Category</th>
                                                <th>Current Stock</th>
                                                <th>Minimum Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($low_stock_products as $product): ?>
                                                <tr>
                                                    <td><code><?= $product['product_code'] ?? 'N/A' ?></code></td>
                                                    <td><?= $product['name'] ?? 'Unknown' ?></td>
                                                    <td><span class="badge bg-secondary"><?= $product['category'] ?? 'N/A' ?></span></td>
                                                    <td>
                                                        <?php if (($product['quantity'] ?? 0) == 0): ?>
                                                            <span class="badge bg-danger">Out of Stock</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark"><?= $product['quantity'] ?? 0 ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $product['min_stock'] ?? 0 ?></td>
                                                    <td>
                                                        <?php if (($product['quantity'] ?? 0) == 0): ?>
                                                            <span class="badge bg-danger">Critical</span>
                                                        <?php elseif (($product['quantity'] ?? 0) <= ($product['min_stock'] ?? 0)): ?>
                                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">OK</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('products/adjust-inventory/' . ($product['id'] ?? '')) ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="bi bi-plus-circle me-1"></i>Restock
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-success">All products have sufficient stock!</p>
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

