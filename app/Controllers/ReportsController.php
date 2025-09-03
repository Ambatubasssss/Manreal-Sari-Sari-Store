<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\ProductModel;
use App\Models\InventoryLogModel;

class ReportsController extends BaseController
{
    protected $saleModel;
    protected $productModel;
    protected $inventoryLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->saleModel = new SaleModel();
        $this->productModel = new ProductModel();
        $this->inventoryLogModel = new InventoryLogModel();
    }

    /**
     * Show reports dashboard
     */
    public function index()
    {
        $this->requireAuth();
        
        $data = [
            'title' => 'Reports & Analytics',
        ];
        
        return $this->renderView('reports/index', $data);
    }

    /**
     * Show sales reports
     */
    public function sales()
    {
        $this->requireAuth();
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $salesStats = $this->saleModel->getSalesStats($startDate, $endDate);
        $weeklySales = $this->saleModel->getWeeklySales($startDate, $endDate);
        $topProducts = $this->saleModel->getTopSellingProducts(10, $startDate, $endDate);
        $salesByCategory = $this->saleModel->getTopSellingProducts(10, $startDate, $endDate);
        
        $data = [
            'title' => 'Sales Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sales_stats' => $salesStats,
            'weekly_sales' => $weeklySales,
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
        ];
        
        return $this->renderView('reports/sales', $data);
    }

    /**
     * Show inventory reports
     */
    public function inventory()
    {
        $this->requireAuth();
        
        $productStats = $this->productModel->getProductStats();
        $lowStockProducts = $this->productModel->getLowStockProducts();
        $inventoryMovement = $this->inventoryLogModel->getMovementSummary();
        $inventoryValueChanges = $this->inventoryLogModel->getInventoryValueChanges();
        
        $data = [
            'title' => 'Inventory Reports',
            'product_stats' => $productStats,
            'low_stock_products' => $lowStockProducts,
            'inventory_movement' => $inventoryMovement,
            'inventory_value_changes' => $inventoryValueChanges,
        ];
        
        return $this->renderView('reports/inventory', $data);
    }

    /**
     * Show daily sales report
     */
    public function dailySales()
    {
        $this->requireAuth();
        
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $dailySales = $this->saleModel->getDailySales($date);
        
        $data = [
            'title' => 'Daily Sales Report',
            'date' => $date,
            'daily_sales' => $dailySales,
        ];
        
        return $this->renderView('reports/daily_sales', $data);
    }

    /**
     * Show weekly sales report
     */
    public function weeklySales()
    {
        $this->requireAuth();
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('monday this week'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d', strtotime('sunday this week'));
        
        $weeklySales = $this->saleModel->getWeeklySales($startDate, $endDate);
        
        $data = [
            'title' => 'Weekly Sales Report',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'weekly_sales' => $weeklySales,
        ];
        
        return $this->renderView('reports/weekly_sales', $data);
    }

    /**
     * Show monthly sales report
     */
    public function monthlySales()
    {
        $this->requireAuth();
        
        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('m');
        
        $monthlySales = $this->saleModel->getMonthlySales($year, $month);
        
        $data = [
            'title' => 'Monthly Sales Report',
            'year' => $year,
            'month' => $month,
            'monthly_sales' => $monthlySales,
        ];
        
        return $this->renderView('reports/monthly_sales', $data);
    }

    /**
     * Show top selling products report
     */
    public function topProducts()
    {
        $this->requireAuth();
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $limit = $this->request->getGet('limit') ?? 20;
        
        $topProducts = $this->saleModel->getTopSellingProducts($limit, $startDate, $endDate);
        
        $data = [
            'title' => 'Top Selling Products',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'top_products' => $topProducts,
        ];
        
        return $this->renderView('reports/top_products', $data);
    }

    /**
     * Show inventory movement report
     */
    public function inventoryMovement()
    {
        $this->requireAuth();
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $movementSummary = $this->inventoryLogModel->getMovementSummary($startDate, $endDate);
        $valueChanges = $this->inventoryLogModel->getInventoryValueChanges($startDate, $endDate);
        
        $data = [
            'title' => 'Inventory Movement Report',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'movement_summary' => $movementSummary,
            'value_changes' => $valueChanges,
        ];
        
        return $this->renderView('reports/inventory_movement', $data);
    }

    /**
     * Show low stock report
     */
    public function lowStock()
    {
        $this->requireAuth();
        
        $lowStockProducts = $this->productModel->getLowStockProducts();
        
        $data = [
            'title' => 'Low Stock Report',
            'low_stock_products' => $lowStockProducts,
        ];
        
        return $this->renderView('reports/low_stock', $data);
    }

    /**
     * Export sales report to Excel
     */
    public function exportSales()
    {
        $this->requireAuth();
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $sales = $this->saleModel->getSales(['start_date' => $startDate, 'end_date' => $endDate], 1, 10000)['sales'];
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="sales_report_' . $startDate . '_to_' . $endDate . '.xls"');
        
        // Output Excel content
        echo "<table border='1'>";
        echo "<tr><th>Sale #</th><th>Date</th><th>Cashier</th><th>Customer</th><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Total</th><th>Payment Method</th><th>Status</th></tr>";
        
        foreach ($sales as $sale) {
            echo "<tr>";
            echo "<td>" . $sale['sale_number'] . "</td>";
            echo "<td>" . $this->formatDateTime($sale['created_at']) . "</td>";
            echo "<td>" . $sale['cashier_name'] . "</td>";
            echo "<td>" . ($sale['customer_name'] ?: 'Walk-in') . "</td>";
            echo "<td>" . $this->formatCurrency($sale['subtotal']) . "</td>";
            echo "<td>" . $this->formatCurrency($sale['discount']) . "</td>";
            echo "<td>" . $this->formatCurrency($sale['tax']) . "</td>";
            echo "<td>" . $this->formatCurrency($sale['total_amount']) . "</td>";
            echo "<td>" . ucfirst($sale['payment_method']) . "</td>";
            echo "<td>" . ucfirst($sale['status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }

    /**
     * Export inventory report to Excel
     */
    public function exportInventory()
    {
        $this->requireAuth();
        
        $products = $this->productModel->where('is_active', true)->findAll();
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="inventory_report_' . date('Y-m-d') . '.xls"');
        
        // Output Excel content
        echo "<table border='1'>";
        echo "<tr><th>Product Code</th><th>Name</th><th>Category</th><th>Price</th><th>Cost Price</th><th>Quantity</th><th>Min Stock</th><th>Status</th><th>Value</th></tr>";
        
        foreach ($products as $product) {
            $value = $product['price'] * $product['quantity'];
            $status = $product['quantity'] <= $product['min_stock'] ? 'Low Stock' : 'In Stock';
            
            echo "<tr>";
            echo "<td>" . $product['product_code'] . "</td>";
            echo "<td>" . $product['name'] . "</td>";
            echo "<td>" . $product['category'] . "</td>";
            echo "<td>" . $this->formatCurrency($product['price']) . "</td>";
            echo "<td>" . $this->formatCurrency($product['cost_price']) . "</td>";
            echo "<td>" . $product['quantity'] . "</td>";
            echo "<td>" . $product['min_stock'] . "</td>";
            echo "<td>" . $status . "</td>";
            echo "<td>" . $this->formatCurrency($value) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }

    /**
     * Get chart data for reports (AJAX)
     */
    public function getChartData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $reportType = $this->request->getGet('type') ?? 'sales';
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $data = [];
        
        switch ($reportType) {
            case 'sales':
                $data['weekly_sales'] = $this->saleModel->getWeeklySales($startDate, $endDate);
                $data['top_products'] = $this->saleModel->getTopSellingProducts(10, $startDate, $endDate);
                break;
                
            case 'inventory':
                $data['movement_summary'] = $this->inventoryLogModel->getMovementSummary($startDate, $endDate);
                $data['value_changes'] = $this->inventoryLogModel->getInventoryValueChanges($startDate, $endDate);
                break;
                
            default:
                return $this->response->setJSON(['error' => 'Invalid report type']);
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * Generate PDF report
     */
    public function generatePDF($type = 'sales')
    {
        $this->requireAuth();
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        switch ($type) {
            case 'sales':
                $this->generateSalesPDF($startDate, $endDate);
                break;
                
            case 'inventory':
                $this->generateInventoryPDF($startDate, $endDate);
                break;
                
            default:
                return redirect()->to('/reports')->with('error', 'Invalid report type');
        }
    }

    /**
     * Generate sales PDF report
     */
    private function generateSalesPDF($startDate, $endDate)
    {
        $salesStats = $this->saleModel->getSalesStats($startDate, $endDate);
        $weeklySales = $this->saleModel->getWeeklySales($startDate, $endDate);
        $topProducts = $this->saleModel->getTopSellingProducts(10, $startDate, $endDate);
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="sales_report_' . $startDate . '_to_' . $endDate . '.pdf"');
        
        // Generate HTML report
        $html = $this->generateSalesReportHTML($startDate, $endDate, $salesStats, $weeklySales, $topProducts);
        
        echo $html;
        exit;
    }

    /**
     * Generate inventory PDF report
     */
    private function generateInventoryPDF($startDate, $endDate)
    {
        $productStats = $this->productModel->getProductStats();
        $lowStockProducts = $this->productModel->getLowStockProducts();
        $movementSummary = $this->inventoryLogModel->getMovementSummary($startDate, $endDate);
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="inventory_report_' . $startDate . '_to_' . $endDate . '.pdf"');
        
        // Generate HTML report
        $html = $this->generateInventoryReportHTML($startDate, $endDate, $productStats, $lowStockProducts, $movementSummary);
        
        echo $html;
        exit;
    }

    /**
     * Generate sales report HTML
     */
    private function generateSalesReportHTML($startDate, $endDate, $stats, $weeklySales, $topProducts)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Sales Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .report-title { font-size: 18px; font-weight: bold; }
                .summary { margin: 20px 0; }
                .summary-table { width: 100%; border-collapse: collapse; }
                .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .summary-table th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="report-title">Sales Report</div>
                <div>Period: ' . $startDate . ' to ' . $endDate . '</div>
                <div>Generated: ' . date('Y-m-d H:i:s') . '</div>
            </div>
            
            <div class="summary">
                <h3>Summary</h3>
                <table class="summary-table">
                    <tr><th>Total Sales</th><th>Total Revenue</th><th>Average Sale</th><th>Total Discount</th></tr>
                    <tr>
                        <td>' . $stats['total_sales'] . '</td>
                        <td>' . $this->formatCurrency($stats['total_revenue']) . '</td>
                        <td>' . $this->formatCurrency($stats['average_sale']) . '</td>
                        <td>' . $this->formatCurrency($stats['total_discount']) . '</td>
                    </tr>
                </table>
            </div>
            
            <div class="weekly-sales">
                <h3>Weekly Sales Breakdown</h3>
                <table class="summary-table">
                    <tr><th>Date</th><th>Sales Count</th><th>Revenue</th><th>Discount</th></tr>';
        
        foreach ($weeklySales as $sale) {
            $html .= '
                    <tr>
                        <td>' . $sale['date'] . '</td>
                        <td>' . $sale['total_sales'] . '</td>
                        <td>' . $this->formatCurrency($sale['total_revenue']) . '</td>
                        <td>' . $this->formatCurrency($sale['total_discount']) . '</td>
                    </tr>';
        }
        
        $html .= '
                </table>
            </div>
            
            <div class="top-products">
                <h3>Top Selling Products</h3>
                <table class="summary-table">
                    <tr><th>Product</th><th>Category</th><th>Quantity Sold</th><th>Revenue</th></tr>';
        
        foreach ($topProducts as $product) {
            $html .= '
                    <tr>
                        <td>' . $product['product_name'] . '</td>
                        <td>' . $product['category'] . '</td>
                        <td>' . $product['total_quantity'] . '</td>
                        <td>' . $this->formatCurrency($product['total_revenue']) . '</td>
                    </tr>';
        }
        
        $html .= '
                </table>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Generate inventory report HTML
     */
    private function generateInventoryReportHTML($startDate, $endDate, $stats, $lowStockProducts, $movementSummary)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Inventory Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .report-title { font-size: 18px; font-weight: bold; }
                .summary { margin: 20px 0; }
                .summary-table { width: 100%; border-collapse: collapse; }
                .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .summary-table th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="report-title">Inventory Report</div>
                <div>Period: ' . $startDate . ' to ' . $endDate . '</div>
                <div>Generated: ' . date('Y-m-d H:i:s') . '</div>
            </div>
            
            <div class="summary">
                <h3>Inventory Summary</h3>
                <table class="summary-table">
                    <tr><th>Total Products</th><th>Low Stock Items</th><th>Out of Stock</th><th>Total Value</th></tr>
                    <tr>
                        <td>' . $stats['total_products'] . '</td>
                        <td>' . $stats['low_stock_count'] . '</td>
                        <td>' . $stats['out_of_stock_count'] . '</td>
                        <td>' . $this->formatCurrency($stats['total_value']) . '</td>
                    </tr>
                </table>
            </div>
            
            <div class="low-stock">
                <h3>Low Stock Products</h3>
                <table class="summary-table">
                    <tr><th>Product Code</th><th>Name</th><th>Category</th><th>Current Stock</th><th>Min Stock</th></tr>';
        
        foreach ($lowStockProducts as $product) {
            $html .= '
                    <tr>
                        <td>' . $product['product_code'] . '</td>
                        <td>' . $product['name'] . '</td>
                        <td>' . $product['category'] . '</td>
                        <td>' . $product['quantity'] . '</td>
                        <td>' . $product['min_stock'] . '</td>
                    </tr>';
        }
        
        $html .= '
                </table>
            </div>
            
            <div class="movement-summary">
                <h3>Inventory Movement Summary</h3>
                <table class="summary-table">
                    <tr><th>Action Type</th><th>Transactions</th><th>Quantity Moved</th><th>Quantity In</th><th>Quantity Out</th></tr>';
        
        foreach ($movementSummary as $movement) {
            $html .= '
                    <tr>
                        <td>' . ucfirst($movement['action_type']) . '</td>
                        <td>' . $movement['total_transactions'] . '</td>
                        <td>' . $movement['total_quantity_moved'] . '</td>
                        <td>' . $movement['total_in'] . '</td>
                        <td>' . $movement['total_out'] . '</td>
                    </tr>';
        }
        
        $html .= '
                </table>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}
