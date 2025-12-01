<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam me-2"></i>Products</h2>
    <a href="<?= base_url('products/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Product
    </a>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('products') ?>" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= $search ?? '' ?>">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category ?>" <?= ($selected_category == $category) ? 'selected' : '' ?>>
                            <?= ucfirst($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('products/export') ?>" class="btn btn-success w-100">
                    <i class="bi bi-download"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 text-muted">No products found</p>
                <a href="<?= base_url('products/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Your First Product
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (isset($product['image']) && $product['image']): ?>
                                        <img src="<?= base_url($product['image']) ?>" 
                                             alt="<?= $product['name'] ?>" 
                                             class="img-thumbnail" style="width: 50px; height: 50px;">
                                    <?php else: ?>
                                        <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= $product['product_code'] ?></code></td>
                                <td>
                                    <div class="fw-bold"><?= $product['name'] ?></div>
                                    <?php if ($product['description']): ?>
                                        <small class="text-muted"><?= $product['description'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= $product['category'] ?></span></td>
                                <td>₱<?= number_format($product['price'], 2) ?></td>
                                <td>
                                    <?php if ($product['quantity'] <= $product['min_stock']): ?>
                                        <span class="badge bg-warning text-dark"><?= $product['quantity'] ?></span>
                                    <?php elseif ($product['quantity'] == 0): ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= $product['quantity'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="text-primary text-decoration-none">Edit</a> | 
                                    <a href="javascript:void(0);" onclick="deleteProduct('<?= $product['id'] ?>')" class="text-danger text-decoration-none">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Products pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?= ($i == $pagination['current_page']) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= base_url('products?page=' . $i . '&search=' . ($filters['search'] ?? '') . '&category=' . ($filters['category'] ?? '')) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        window.location.href = '<?= base_url('products/delete/') ?>' + productId;
    }
}
</script>
<?= $this->endSection() ?>
