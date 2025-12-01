<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar-week me-2"></i>Weekly Sales Report
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

                    <!-- Weekly Sales Summary -->
                    <?php 
                    $totalSales = 0;
                    $totalRevenue = 0;
                    $totalDiscount = 0;
                    foreach ($weekly_sales as $day) {
                        $totalSales += $day['total_sales'] ?? 0;
                        $totalRevenue += $day['total_revenue'] ?? 0;
                        $totalDiscount += $day['total_discount'] ?? 0;
                    }
                    $averageSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;
                    ?>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Sales</h5>
                                    <h3 class="mb-0"><?= $totalSales ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Revenue</h5>
                                    <h3 class="mb-0">₱<?= number_format($totalRevenue, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Discount</h5>
                                    <h3 class="mb-0">₱<?= number_format($totalDiscount, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-muted">Average Sale</h5>
                                    <h3 class="mb-0">₱<?= number_format($averageSale, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Sales Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Weekly Sales Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($weekly_sales)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
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
                                            <?php foreach ($weekly_sales as $day): ?>
                                                <tr>
                                                    <td><?= date('F d, Y', strtotime($day['date'])) ?></td>
                                                    <td><?= $day['total_sales'] ?? 0 ?></td>
                                                    <td>₱<?= number_format($day['total_revenue'] ?? 0, 2) ?></td>
                                                    <td>₱<?= number_format($day['total_discount'] ?? 0, 2) ?></td>
                                                    <td>₱<?= number_format(($day['total_sales'] ?? 0) > 0 ? ($day['total_revenue'] ?? 0) / ($day['total_sales'] ?? 1) : 0, 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-info">
                                                <th>Total</th>
                                                <th><?= $totalSales ?></th>
                                                <th>₱<?= number_format($totalRevenue, 2) ?></th>
                                                <th>₱<?= number_format($totalDiscount, 2) ?></th>
                                                <th>₱<?= number_format($averageSale, 2) ?></th>
                                            </tr>
                                        </tfoot>
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

