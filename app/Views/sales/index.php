<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt me-2"></i>Sales</h2>
    <a href="<?= base_url('pos') ?>" class="btn btn-primary">
        <i class="bi bi-cart-check me-2"></i>New Sale
    </a>
</div>

<!-- Sales Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Sale #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                                <p class="mt-3 text-muted">No sales found</p>
                                <a href="<?= base_url('pos') ?>" class="btn btn-primary">
                                    <i class="bi bi-cart-check me-2"></i>Make Your First Sale
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><code><?= $sale['sale_number'] ?></code></td>
                                <td><?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></td>
                                <td><?= $sale['customer_name'] ?: 'Walk-in Customer' ?></td>
                                <td><?= $sale['total_items'] ?? 0 ?> items</td>
                                <td>₱<?= number_format($sale['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-info"><?= ucfirst($sale['payment_method']) ?></span>
                                </td>
                                <td>
                                    <?php if ($sale['status'] == 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($sale['status'] == 'cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><?= ucfirst($sale['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= base_url('sales/show/' . $sale['id']) ?>" 
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= base_url('sales/receipt/' . $sale['id']) ?>" 
                                           class="btn btn-sm btn-outline-secondary" title="Receipt">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <?php if ($sale['status'] == 'completed'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="cancelSale(<?= $sale['id'] ?>)" title="Cancel">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function cancelSale(saleId) {
    if (confirm('Are you sure you want to cancel this sale? This will restore the inventory.')) {
        window.location.href = '<?= base_url('sales/cancel/') ?>' + saleId;
    }
}
</script>
<?= $this->endSection() ?>
