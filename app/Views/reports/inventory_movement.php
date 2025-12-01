<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i>Inventory Movement Report
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Date Range Filter -->
                    <form method="get" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>

                    <!-- Movement Summary -->
                    <?php if (!empty($movement_summary)): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Movement Summary by Action Type</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Action Type</th>
                                                <th>Total Transactions</th>
                                                <th>Total Quantity Moved</th>
                                                <th>Quantity In</th>
                                                <th>Quantity Out</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($movement_summary as $movement): ?>
                                                <tr>
                                                    <td><strong><?= ucfirst($movement['action_type'] ?? 'Unknown') ?></strong></td>
                                                    <td><?= $movement['total_transactions'] ?? 0 ?></td>
                                                    <td><?= number_format($movement['total_quantity_moved'] ?? 0) ?></td>
                                                    <td class="text-success">+<?= number_format($movement['total_in'] ?? 0) ?></td>
                                                    <td class="text-danger">-<?= number_format($movement['total_out'] ?? 0) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Value Changes -->
                    <?php if (!empty($value_changes)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Inventory Value Changes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Value In</th>
                                                <th>Value Out</th>
                                                <th>Net Change</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($value_changes as $change): ?>
                                                <tr>
                                                    <td><?= date('F d, Y', strtotime($change['date'])) ?></td>
                                                    <td class="text-success">₱<?= number_format($change['value_in'] ?? 0, 2) ?></td>
                                                    <td class="text-danger">₱<?= number_format($change['value_out'] ?? 0, 2) ?></td>
                                                    <td>
                                                        <?php 
                                                        $netChange = ($change['value_in'] ?? 0) - ($change['value_out'] ?? 0);
                                                        $class = $netChange >= 0 ? 'text-success' : 'text-danger';
                                                        ?>
                                                        <span class="<?= $class ?>">
                                                            <?= $netChange >= 0 ? '+' : '' ?>₱<?= number_format($netChange, 2) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">No inventory movement found for this period</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

