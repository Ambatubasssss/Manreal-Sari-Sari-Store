<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;
use App\Models\InventoryLogModel;

class DashboardController extends BaseController
{
    protected $productModel;
    protected $saleModel;
    protected $userModel;
    protected $inventoryLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new ProductModel();
        $this->saleModel = new SaleModel();
        $this->userModel = new UserModel();
        $this->inventoryLogModel = new InventoryLogModel();
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        // Get dashboard statistics
        $data = [
            'title' => 'Dashboard',
            'stats' => $this->getDashboardStats(),
            'recent_sales' => $this->getRecentSales(),
            'low_stock_products' => $this->getLowStockProducts(),
            'top_selling_products' => $this->getTopSellingProducts(),
            'sales_chart_data' => $this->getSalesChartData(),
        ];

        return $this->renderView('dashboard/index', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $today = date('Y-m-d');

        // Today's sales - return default zero values
        $todaySales = [
            'total_sales' => 0,
            'total_revenue' => 0,
            'total_discount' => 0,
            'average_sale' => 0
        ];

        // Monthly sales total
        $monthlyTotal = 0;

        // Product statistics - return default values
        $productStats = [
            'total_products' => 0,
            'low_stock_count' => 0,
            'out_of_stock_count' => 0,
            'total_value' => 0
        ];

        // User statistics - return default values
        $totalUsers = 0;
        $totalActiveUsers = 0;

        try {
            $todaySales = $this->saleModel->getDailySales($today) ?: $todaySales;
            $monthlySales = $this->saleModel->getMonthlySales(date('Y'), date('m'));
            foreach ($monthlySales as $daily) {
                $monthlyTotal += $daily['total_revenue'] ?? 0;
            }
            $productStats = $this->productModel->getProductStats() ?: $productStats;
            $totalUsers = $this->userModel->countAllResults() ?: 0;
            $totalActiveUsers = count($this->userModel->getActiveUsers() ?: []);
        } catch (\Exception $e) {
            // If any model fails, continue with default values
            log_message('error', 'Dashboard stats error: ' . $e->getMessage());
        }

        return [
            'today_sales' => $todaySales['total_sales'] ?? 0,
            'today_revenue' => $todaySales['total_revenue'] ?? 0,
            'monthly_revenue' => $monthlyTotal,
            'total_products' => $productStats['total_products'] ?? 0,
            'low_stock_count' => $productStats['low_stock_count'] ?? 0,
            'out_of_stock_count' => $productStats['out_of_stock_count'] ?? 0,
            'total_users' => $totalUsers,
            'active_users' => $totalActiveUsers,
        ];
    }

    /**
     * Get recent sales
     */
    private function getRecentSales()
    {
        try {
            return $this->saleModel->getSales([], 1, 5)['sales'] ?: [];
        } catch (\Exception $e) {
            return []; // Return empty array on error
        }
    }

    /**
     * Get low stock products
     */
    private function getLowStockProducts()
    {
        try {
            return $this->productModel->getLowStockProducts() ?: [];
        } catch (\Exception $e) {
            return []; // Return empty array on error
        }
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts()
    {
        try {
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
            return $this->saleModel->getTopSellingProducts(5, $startDate, $endDate) ?: [];
        } catch (\Exception $e) {
            return []; // Return empty array on error
        }
    }

    /**
     * Get sales chart data for the last 7 days
     */
    private function getSalesChartData()
    {
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');
        
        $weeklySales = $this->saleModel->getWeeklySales($startDate, $endDate);
        
        $chartData = [
            'labels' => [],
            'data' => [],
        ];
        
        // Generate data for all 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartData['labels'][] = date('M d', strtotime($date));
            
            // Find sales for this date
            $salesForDate = 0;
            foreach ($weeklySales as $sale) {
                if ($sale['date'] === $date) {
                    $salesForDate = $sale['total_revenue'];
                    break;
                }
            }
            $chartData['data'][] = $salesForDate;
        }
        
        return $chartData;
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getDashboardData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $data = [
            'stats' => $this->getDashboardStats(),
            'recent_sales' => $this->getRecentSales(),
            'low_stock_products' => $this->getLowStockProducts(),
            'sales_chart_data' => $this->getSalesChartData(),
        ];

        return $this->response->setJSON($data);
    }

    /**
     * Show system information
     */
    public function systemInfo()
    {
        if (!AuthController::isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'System Information',
            'php_version' => PHP_VERSION,
            'codeigniter_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'database_info' => $this->getDatabaseInfo(),
            'server_info' => $this->getServerInfo(),
        ];

        return $this->renderView('dashboard/system_info', $data);
    }

    /**
     * Get database information
     */
    private function getDatabaseInfo()
    {
        return [
            'platform' => 'MongoDB',
            'database' => getenv('MONGODB_DATABASE') ?: 'manreal',
            'hostname' => getenv('MONGODB_HOST') ?: 'localhost',
            'port' => getenv('MONGODB_PORT') ?: '27017',
        ];
    }

    /**
     * Get server information
     */
    private function getServerInfo()
    {
        return [
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_sapi' => php_sapi_name(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }
}
