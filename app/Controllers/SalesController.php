<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\SaleItemModel;
use App\Models\ProductModel;
use App\Models\InventoryLogModel;

class SalesController extends BaseController
{
    protected $saleModel;
    protected $saleItemModel;
    protected $productModel;
    protected $inventoryLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->saleModel = new SaleModel();
        $this->saleItemModel = new SaleItemModel();
        $this->productModel = new ProductModel();
        $this->inventoryLogModel = new InventoryLogModel();
    }

    /**
     * Show POS interface
     */
    public function pos()
    {
        $this->requireAuth();
        
        $data = [
            'title' => 'Point of Sale',
            'sale_number' => $this->saleModel->generateSaleNumber(),
        ];
        
        return $this->renderView('sales/pos', $data);
    }

    /**
     * Show sales list
     */
    public function index()
    {
        $this->requireAuth();
        
        $filters = $this->getSearchParams();
        $page = $this->request->getGet('page') ?? 1;
        
        $data = $this->saleModel->getSales($filters, $page);
        
        $viewData = [
            'title' => 'Sales Management',
            'sales' => $data['sales'],
            'pagination' => [
                'current_page' => $data['current_page'],
                'total_pages' => $data['total_pages'],
                'total' => $data['total'],
            ],
            'filters' => $filters,
        ];
        
        return $this->renderView('sales/index', $viewData);
    }

    /**
     * Show sale details
     */
    public function show($id = null)
    {
        $this->requireAuth();
        
        if (!$id) {
            return redirect()->to('/sales')->with('error', 'Sale ID is required');
        }
        
        $sale = $this->saleModel->getSaleWithItems($id);
        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found');
        }
        
        $data = [
            'title' => 'Sale Details',
            'sale' => $sale,
        ];
        
        return $this->renderView('sales/show', $data);
    }

    /**
     * Process POS sale
     */
    public function processSale()
    {
        $this->requireAuth();
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $saleData = json_decode($this->request->getPost('sale_data'), true);
        
        if (!$saleData || empty($saleData['items'])) {
            return $this->response->setJSON(['error' => 'Sale data is required']);
        }
        
        // Validate items
        foreach ($saleData['items'] as $item) {
            $product = $this->productModel->find($item['product_id']);
            if (!$product || $product['quantity'] < $item['quantity']) {
                return $this->response->setJSON(['error' => 'Insufficient stock for ' . $product['name']]);
            }
        }
        
        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Create sale record
            $saleRecord = [
                'sale_number' => $saleData['sale_number'],
                'user_id' => $this->userData['id'],
                'customer_name' => $saleData['customer_name'] ?? '',
                'subtotal' => $saleData['subtotal'],
                'discount' => $saleData['discount'] ?? 0,
                'tax' => $saleData['tax'] ?? 0,
                'total_amount' => $saleData['total_amount'],
                'cash_received' => $saleData['cash_received'],
                'change_amount' => $saleData['change_amount'],
                'payment_method' => $saleData['payment_method'],
                'status' => 'completed',
                'notes' => $saleData['notes'] ?? '',
            ];
            
            $saleId = $this->saleModel->insert($saleRecord);
            
            if (!$saleId) {
                throw new \Exception('Failed to create sale record');
            }
            
            // Create sale items and update inventory
            foreach ($saleData['items'] as $item) {
                $product = $this->productModel->find($item['product_id']);
                $previousQuantity = $product['quantity'];
                
                // Create sale item
                $saleItem = [
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'product_code' => $item['product_code'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ];
                
                if (!$this->saleItemModel->insert($saleItem)) {
                    throw new \Exception('Failed to create sale item');
                }
                
                // Update product quantity
                if (!$this->productModel->updateQuantity($item['product_id'], -$item['quantity'], 'sale')) {
                    throw new \Exception('Failed to update product quantity');
                }
                
                // Log inventory change
                $this->inventoryLogModel->logSale(
                    $item['product_id'],
                    $this->userData['id'],
                    $item['quantity'],
                    $previousQuantity,
                    $saleId
                );
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return $this->response->setJSON(['error' => 'Transaction failed']);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'sale_id' => $saleId,
                'message' => 'Sale completed successfully'
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel sale
     */
    public function cancelSale($id = null)
    {
        $this->requireAuth();
        
        if (!$id) {
            return redirect()->to('/sales')->with('error', 'Sale ID is required');
        }
        
        $sale = $this->saleModel->find($id);
        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found');
        }
        
        if ($sale['status'] !== 'completed') {
            return redirect()->to('/sales')->with('error', 'Sale cannot be cancelled');
        }
        
        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Update sale status
            $this->saleModel->update($id, ['status' => 'cancelled']);
            
            // Restore product quantities
            $saleItems = $this->saleItemModel->getBySaleId($id);
            foreach ($saleItems as $item) {
                $product = $this->productModel->find($item['product_id']);
                $previousQuantity = $product['quantity'];
                
                // Restore quantity
                $this->productModel->updateQuantity($item['product_id'], $item['quantity'], 'return');
                
                // Log inventory change
                $this->inventoryLogModel->logChange([
                    'product_id' => $item['product_id'],
                    'user_id' => $this->userData['id'],
                    'action_type' => 'return',
                    'quantity_change' => $item['quantity'],
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $previousQuantity + $item['quantity'],
                    'reference_id' => $id,
                    'reference_type' => 'sale_cancellation',
                    'notes' => 'Sale cancellation - quantity restored',
                ]);
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                $this->setErrorMessage('Failed to cancel sale');
            } else {
                $this->setSuccessMessage('Sale cancelled successfully');
            }
            
        } catch (\Exception $e) {
            $db->transRollback();
            $this->setErrorMessage('Error cancelling sale: ' . $e->getMessage());
        }
        
        return redirect()->to('/sales');
    }

    /**
     * Generate PDF receipt
     */
    public function generateReceipt($id = null)
    {
        $this->requireAuth();
        
        if (!$id) {
            return redirect()->to('/sales')->with('error', 'Sale ID is required');
        }
        
        $sale = $this->saleModel->getSaleWithItems($id);
        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found');
        }
        
        // Generate PDF receipt
        $this->generatePDFReceipt($sale);
    }

    /**
     * Generate PDF receipt content
     */
    private function generatePDFReceipt($sale)
    {
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="receipt_' . $sale['sale_number'] . '.pdf"');
        
        // Simple HTML to PDF conversion (you can use libraries like TCPDF or Dompdf)
        $html = $this->generateReceiptHTML($sale);
        
        // For now, output HTML (you can integrate with a PDF library)
        echo $html;
        exit;
    }

    /**
     * Generate receipt HTML
     */
    private function generateReceiptHTML($sale)
    {
        $storeName = 'Manreal Store';
        $storeAddress = '123 Main Street, City, Province';
        $storePhone = '+63 123 456 7890';
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Receipt - ' . $sale['sale_number'] . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .store-name { font-size: 18px; font-weight: bold; }
                .store-info { margin: 5px 0; }
                .receipt-info { margin: 20px 0; }
                .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .items-table th { background-color: #f2f2f2; }
                .totals { text-align: right; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="store-name">' . $storeName . '</div>
                <div class="store-info">' . $storeAddress . '</div>
                <div class="store-info">' . $storePhone . '</div>
            </div>
            
            <div class="receipt-info">
                <strong>Receipt #:</strong> ' . $sale['sale_number'] . '<br>
                <strong>Date:</strong> ' . $this->formatDateTime($sale['created_at']) . '<br>
                <strong>Cashier:</strong> ' . $sale['cashier_name'] . '<br>
                <strong>Customer:</strong> ' . ($sale['customer_name'] ?: 'Walk-in Customer') . '
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($sale['items'] as $item) {
            $html .= '
                    <tr>
                        <td>' . $item['product_name'] . '</td>
                        <td>' . $item['quantity'] . '</td>
                        <td>' . $this->formatCurrency($item['unit_price']) . '</td>
                        <td>' . $this->formatCurrency($item['total_price']) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="totals">
                <strong>Subtotal:</strong> ' . $this->formatCurrency($sale['subtotal']) . '<br>
                <strong>Discount:</strong> ' . $this->formatCurrency($sale['discount']) . '<br>
                <strong>Tax:</strong> ' . $this->formatCurrency($sale['tax']) . '<br>
                <strong>Total Amount:</strong> ' . $this->formatCurrency($sale['total_amount']) . '<br>
                <strong>Cash Received:</strong> ' . $this->formatCurrency($sale['cash_received']) . '<br>
                <strong>Change:</strong> ' . $this->formatCurrency($sale['change_amount']) . '<br>
                <strong>Payment Method:</strong> ' . ucfirst($sale['payment_method']) . '
            </div>
            
            <div class="footer">
                Thank you for your purchase!<br>
                Please come again.
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Get sales data for charts (AJAX)
     */
    public function getSalesChartData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $weeklySales = $this->saleModel->getWeeklySales($startDate, $endDate);
        $topProducts = $this->saleModel->getTopSellingProducts(10, $startDate, $endDate);
        
        return $this->response->setJSON([
            'weekly_sales' => $weeklySales,
            'top_products' => $topProducts,
        ]);
    }

    /**
     * Export sales to Excel
     */
    public function export()
    {
        $this->requireAuth();
        
        $filters = $this->getSearchParams();
        $sales = $this->saleModel->getSales($filters, 1, 1000)['sales'];
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="sales_' . date('Y-m-d') . '.xls"');
        
        // Output Excel content
        echo "<table border='1'>";
        echo "<tr><th>Sale #</th><th>Date</th><th>Cashier</th><th>Customer</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Payment Method</th><th>Status</th></tr>";
        
        foreach ($sales as $sale) {
            echo "<tr>";
            echo "<td>" . $sale['sale_number'] . "</td>";
            echo "<td>" . $this->formatDateTime($sale['created_at']) . "</td>";
            echo "<td>" . $sale['cashier_name'] . "</td>";
            echo "<td>" . ($sale['customer_name'] ?: 'Walk-in') . "</td>";
            echo "<td>" . count($sale['items'] ?? []) . "</td>";
            echo "<td>" . $this->formatCurrency($sale['subtotal']) . "</td>";
            echo "<td>" . $this->formatCurrency($sale['discount']) . "</td>";
            echo "<td>" . $this->formatCurrency($sale['total_amount']) . "</td>";
            echo "<td>" . ucfirst($sale['payment_method']) . "</td>";
            echo "<td>" . ucfirst($sale['status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }
}
