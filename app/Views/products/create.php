<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>Add New Product
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('products/store') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_code" class="form-label">Product Code *</label>
                                    <input type="text" class="form-control" id="product_code" name="product_code" 
                                           value="<?= old('product_code') ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= old('name') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category *</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php if (isset($categories) && is_array($categories)): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category ?>" <?= old('category') === $category ? 'selected' : '' ?>>
                                                    <?= ucfirst($category) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Brand</label>
                                    <input type="text" class="form-control" id="brand" name="brand" 
                                           value="<?= old('brand') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cost_price" class="form-label">Cost Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="cost_price" name="cost_price" 
                                               step="0.01" min="0" value="<?= old('cost_price') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Selling Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               step="0.01" min="0" value="<?= old('price') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Initial Quantity *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           min="0" value="<?= old('quantity', 0) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_stock" class="form-label">Minimum Stock Level</label>
                                    <input type="number" class="form-control" id="min_stock" name="min_stock" 
                                           min="0" value="<?= old('min_stock', 5) ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit</label>
                                    <input type="text" class="form-control" id="unit" name="unit" 
                                           value="<?= old('unit', 'pcs') ?>" placeholder="e.g., pcs, kg, liters">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Product description..."><?= old('description') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/*">
                            <small class="form-text text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       <?= old('is_active', '1') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Product is active
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('products') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Products
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate selling price based on cost price and markup
    const costPriceInput = document.getElementById('cost_price');
    const priceInput = document.getElementById('price');
    
    costPriceInput.addEventListener('input', function() {
        const costPrice = parseFloat(this.value) || 0;
        const currentPrice = parseFloat(priceInput.value) || 0;
        
        // If selling price is not set or is less than cost price, set a default markup
        if (currentPrice === 0 || currentPrice < costPrice) {
            const defaultMarkup = 1.3; // 30% markup
            priceInput.value = (costPrice * defaultMarkup).toFixed(2);
        }
    });
    
    // Validate that selling price is not less than cost price
    priceInput.addEventListener('input', function() {
        const costPrice = parseFloat(costPriceInput.value) || 0;
        const price = parseFloat(this.value) || 0;
        
        if (price < costPrice) {
            this.setCustomValidity('Selling price cannot be less than cost price');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?= $this->endSection() ?>
