<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'sale_number', 'user_id', 'customer_name', 'subtotal', 'discount', 
        'tax', 'total_amount', 'cash_received', 'change_amount', 
        'payment_method', 'status', 'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'sale_number' => 'required|is_unique[sales.sale_number,id,{id}]',
        'user_id' => 'required|integer',
        'subtotal' => 'required|decimal',
        'total_amount' => 'required|decimal',
        'payment_method' => 'required|in_list[cash,card,gcash,maya]',
        'status' => 'required|in_list[completed,cancelled,refunded]',
    ];

    protected $validationMessages = [
        'sale_number' => [
            'required' => 'Sale number is required',
            'is_unique' => 'Sale number already exists',
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer',
        ],
        'subtotal' => [
            'required' => 'Subtotal is required',
            'decimal' => 'Subtotal must be a valid decimal number',
        ],
        'total_amount' => [
            'required' => 'Total amount is required',
            'decimal' => 'Total amount must be a valid decimal number',
        ],
        'payment_method' => [
            'required' => 'Payment method is required',
            'in_list' => 'Invalid payment method selected',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status selected',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Generate unique sale number
     */
    public function generateSaleNumber()
    {
        $prefix = 'SALE';
        $date = date('Ymd');
        $lastSale = $this->where('DATE(created_at)', date('Y-m-d'))
                         ->orderBy('id', 'DESC')
                         ->first();
        
        if ($lastSale) {
            $lastNumber = $lastSale['sale_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get sales with pagination and filters
     */
    public function getSales($filters = [], $page = 1, $perPage = 20)
    {
        $builder = $this->builder();
        
        // Apply filters
        if (!empty($filters['start_date'])) {
            $builder->where('DATE(created_at) >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('DATE(created_at) <=', $filters['end_date']);
        }
        
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['payment_method'])) {
            $builder->where('payment_method', $filters['payment_method']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        $total = $builder->countAllResults(false);
        
        $offset = ($page - 1) * $perPage;
        $sales = $builder->select('sales.*, users.full_name as cashier_name')
                        ->join('users', 'users.id = sales.user_id')
                        ->limit($perPage, $offset)
                        ->orderBy('sales.created_at', 'DESC')
                        ->get()
                        ->getResultArray();
        
        return [
            'sales' => $sales,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'current_page' => $page,
        ];
    }

    /**
     * Get sale details with items
     */
    public function getSaleWithItems($saleId)
    {
        $sale = $this->select('sales.*, users.full_name as cashier_name')
                     ->join('users', 'users.id = sales.user_id')
                     ->find($saleId);
        
        if (!$sale) {
            return null;
        }
        
        // Get sale items
        $saleItemModel = new SaleItemModel();
        $sale['items'] = $saleItemModel->where('sale_id', $saleId)->findAll();
        
        return $sale;
    }

    /**
     * Get daily sales report
     */
    public function getDailySales($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        return $this->select('
                DATE(created_at) as date,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(discount) as total_discount,
                AVG(total_amount) as average_sale
            ')
            ->where('DATE(created_at)', $date)
            ->where('status', 'completed')
            ->groupBy('DATE(created_at)')
            ->first();
    }

    /**
     * Get weekly sales report
     */
    public function getWeeklySales($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('monday this week'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        }
        
        return $this->select('
                DATE(created_at) as date,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(discount) as total_discount
            ')
            ->where('DATE(created_at) >=', $startDate)
            ->where('DATE(created_at) <=', $endDate)
            ->where('status', 'completed')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    /**
     * Get monthly sales report
     */
    public function getMonthlySales($year = null, $month = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        
        return $this->select('
                DATE(created_at) as date,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(discount) as total_discount
            ')
            ->where('YEAR(created_at)', $year)
            ->where('MONTH(created_at)', $month)
            ->where('status', 'completed')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 10, $startDate = null, $endDate = null)
    {
        $builder = $this->db->table('sale_items si')
                           ->select('
                                p.name as product_name,
                                p.category,
                                SUM(si.quantity) as total_quantity,
                                SUM(si.total_price) as total_revenue
                            ')
                           ->join('products p', 'p.id = si.product_id')
                           ->join('sales s', 's.id = si.sale_id')
                           ->where('s.status', 'completed');
        
        if ($startDate) {
            $builder->where('DATE(s.created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(s.created_at) <=', $endDate);
        }
        
        return $builder->groupBy('si.product_id')
                      ->orderBy('total_quantity', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Get sales statistics
     */
    public function getSalesStats($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('DATE(created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(created_at) <=', $endDate);
        }
        
        $builder->where('status', 'completed');
        
        $stats = $builder->select('
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(discount) as total_discount,
                AVG(total_amount) as average_sale,
                MIN(total_amount) as min_sale,
                MAX(total_amount) as max_sale
            ')
            ->get()
            ->getRowArray();
        
        return $stats;
    }
}
