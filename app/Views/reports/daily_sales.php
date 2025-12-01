<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar-day me-2"></i>Daily Sales Report
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Date Filter -->
                    <form method="get" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= $date ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>

                    <!-- Daily Sales Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Sales</h5>
                                    <h3 class="mb-0"><?= $daily_sales['total_sales'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Revenue</h5>
                                    <h3 class="mb-0">₱<?= number_format($daily_sales['total_revenue'] ?? 0, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Discount</h5>
                                    <h3 class="mb-0">₱<?= number_format($daily_sales['total_discount'] ?? 0, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Average Sale</h5>
                                    <h3 class="mb-0">₱<?= number_format($daily_sales['average_sale'] ?? 0, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Sales Summary for <?= date('F d, Y', strtotime($date)) ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (($daily_sales['total_sales'] ?? 0) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Total Sales</th>
                                                <th>Total Revenue</th>
                                                <th>Total Discount</th>
                                                <th>Average Sale</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?= $daily_sales['date'] ?? $date ?></td>
                                                <td><?= $daily_sales['total_sales'] ?? 0 ?></td>
                                                <td>₱<?= number_format($daily_sales['total_revenue'] ?? 0, 2) ?></td>
                                                <td>₱<?= number_format($daily_sales['total_discount'] ?? 0, 2) ?></td>
                                                <td>₱<?= number_format($daily_sales['average_sale'] ?? 0, 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">No sales found for this date</p>
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

