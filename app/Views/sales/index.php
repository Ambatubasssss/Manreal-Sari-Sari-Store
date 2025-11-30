<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt me-2"></i>Sales</h2>
    <div class="btn-group" role="group">
        <button class="btn btn-info" onclick="generateReport()">
            <i class="bi bi-file-earmark-text me-2"></i>Generate Report
        </button>
        <a href="<?= base_url('pos') ?>" class="btn btn-primary">
            <i class="bi bi-cart-check me-2"></i>New Sale
        </a>
    </div>
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

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generated Report - <span id="reportType"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reportContent">
                    <!-- Report content will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="downloadReport()"><i class="bi bi-download me-2"></i>Download Report</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentReportId = null;

function cancelSale(saleId) {
    if (confirm('Are you sure you want to cancel this sale? This will restore the inventory.')) {
        window.location.href = '<?= base_url('sales/cancel/') ?>' + saleId;
    }
}

function generateReport() {
    const reportType = 'daily'; // Default to daily report

    fetch('<?= base_url('sales/generate-report') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        },
        body: new URLSearchParams({
            'report_type': reportType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show modal with report data first
            document.getElementById('reportType').textContent = data.report.type.toUpperCase();
            const reportContent = document.getElementById('reportContent');
            const report = data.report.report_data;
            reportContent.innerHTML = `
                <h6>Report Summary</h6>
                <p><strong>Report ID:</strong> ${data.report_id}</p>
                <p><strong>Generated At:</strong> ${new Date(data.report.generated_at).toLocaleString()}</p>
                <p><strong>Period:</strong> ${data.report.period}</p>

                <h6>Sales Summary</h6>
                <ul>
                    <li>Total Sales: ${report.sales_summary.total_sales}</li>
                    <li>Total Revenue: ₱${report.sales_summary.total_revenue.toLocaleString()}</li>
                    <li>Average Sale: ₱${report.sales_summary.average_sale.toLocaleString()}</li>
                </ul>

                <h6>Product Summary</h6>
                <ul>
                    <li>Total Products: ${report.product_summary.total_products}</li>
                    <li>Low Stock Items: ${report.product_summary.low_stock_count}</li>
                    <li>Out of Stock: ${report.product_summary.out_of_stock_count}</li>
                    <li>Total Inventory Value: ₱${report.product_summary.total_value.toLocaleString()}</li>
                </ul>
            `;

            // Set report ID for download
            currentReportId = data.report_id;

            // Show modal with download button
            const modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating report');
    });
}

function downloadReport() {
    if (currentReportId) {
        // Trigger download
        const downloadLink = document.createElement('a');
        downloadLink.href = '<?= base_url('sales/download-report/') ?>' + currentReportId;
        downloadLink.download = 'report_' + currentReportId + '.html';
        downloadLink.target = '_blank';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    } else {
        alert('No report available for download.');
    }
}
</script>
<?= $this->endSection() ?>
