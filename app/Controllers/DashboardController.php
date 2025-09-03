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
        $thisMonth = date('Y-m');
        
        // Today's sales
        $todaySales = $this->saleModel->getDailySales($today);
        
        // This month's sales
        $monthlySales = $this->saleModel->getMonthlySales(date('Y'), date('m'));
        $monthlyTotal = 0;
        if ($monthlySales) {
            foreach ($monthlySales as $sale) {
                $monthlyTotal += $sale['total_revenue'];
            }
        }
        
        // Product statistics
        $productStats = $this->productModel->getProductStats();
        
        // User statistics
        $totalUsers = $this->userModel->countAllResults();
        $activeUsers = $this->userModel->getActiveUsers();
        $totalActiveUsers = count($activeUsers);
        
        return [
            'today_sales' => $todaySales ? $todaySales['total_sales'] : 0,
            'today_revenue' => $todaySales ? $todaySales['total_revenue'] : 0,
            'monthly_revenue' => $monthlyTotal,
            'total_products' => $productStats['total_products'],
            'low_stock_count' => $productStats['low_stock_count'],
            'out_of_stock_count' => $productStats['out_of_stock_count'],
            'total_users' => $totalUsers,
            'active_users' => $totalActiveUsers,
        ];
    }

    /**
     * Get recent sales
     */
    private function getRecentSales()
    {
        return $this->saleModel->getSales([], 1, 5)['sales'];
    }

    /**
     * Get low stock products
     */
    private function getLowStockProducts()
    {
        return $this->productModel->getLowStockProducts();
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts()
    {
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        return $this->saleModel->getTopSellingProducts(5, $startDate, $endDate);
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
        $db = \Config\Database::connect();
        $platform = $db->getPlatform();
        
        return [
            'platform' => $platform,
            'database' => $db->database,
            'hostname' => $db->hostname,
            'port' => $db->port,
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
