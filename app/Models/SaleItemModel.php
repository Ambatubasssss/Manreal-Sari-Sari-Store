<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleItemModel extends Model
{
    protected $table = 'sale_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'sale_id', 'product_id', 'product_code', 'product_name', 
        'quantity', 'unit_price', 'total_price'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'sale_id' => 'required|integer',
        'product_id' => 'required|integer',
        'product_code' => 'required|max_length[50]',
        'product_name' => 'required|max_length[200]',
        'quantity' => 'required|integer|greater_than[0]',
        'unit_price' => 'required|decimal',
        'total_price' => 'required|decimal',
    ];

    protected $validationMessages = [
        'sale_id' => [
            'required' => 'Sale ID is required',
            'integer' => 'Sale ID must be a valid integer',
        ],
        'product_id' => [
            'required' => 'Product ID is required',
            'integer' => 'Product ID must be a valid integer',
        ],
        'product_code' => [
            'required' => 'Product code is required',
            'max_length' => 'Product code cannot exceed 50 characters',
        ],
        'product_name' => [
            'required' => 'Product name is required',
            'max_length' => 'Product name cannot exceed 200 characters',
        ],
        'quantity' => [
            'required' => 'Quantity is required',
            'integer' => 'Quantity must be a whole number',
            'greater_than' => 'Quantity must be greater than 0',
        ],
        'unit_price' => [
            'required' => 'Unit price is required',
            'decimal' => 'Unit price must be a valid decimal number',
        ],
        'total_price' => [
            'required' => 'Total price is required',
            'decimal' => 'Total price must be a valid decimal number',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get sale items by sale ID
     */
    public function getBySaleId($saleId)
    {
        return $this->where('sale_id', $saleId)->findAll();
    }

    /**
     * Get sale items with product details
     */
    public function getBySaleIdWithProduct($saleId)
    {
        return $this->select('sale_items.*, products.category, products.image')
                   ->join('products', 'products.id = sale_items.product_id')
                   ->where('sale_id', $saleId)
                   ->findAll();
    }

    /**
     * Calculate total for sale items
     */
    public function calculateTotal($items)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['total_price'];
        }
        return $subtotal;
    }

    /**
     * Get product sales summary
     */
    public function getProductSalesSummary($productId, $startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        $builder->select('
                SUM(quantity) as total_quantity,
                SUM(total_price) as total_revenue,
                AVG(unit_price) as average_price,
                COUNT(*) as total_sales
            ')
            ->where('product_id', $productId);
        
        if ($startDate) {
            $builder->join('sales', 'sales.id = sale_items.sale_id')
                   ->where('DATE(sales.created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->join('sales', 'sales.id = sale_items.sale_id')
                   ->where('DATE(sales.created_at) <=', $endDate);
        }
        
        return $builder->first();
    }

    /**
     * Get daily product sales
     */
    public function getDailyProductSales($productId, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        return $this->select('
                DATE(sales.created_at) as date,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.total_price) as total_revenue
            ')
            ->join('sales', 'sales.id = sale_items.sale_id')
            ->where('sale_items.product_id', $productId)
            ->where('DATE(sales.created_at)', $date)
            ->where('sales.status', 'completed')
            ->groupBy('DATE(sales.created_at)')
            ->first();
    }

    /**
     * Get top selling products by date range
     */
    public function getTopSellingProductsByDateRange($startDate, $endDate, $limit = 10)
    {
        return $this->select('
                p.name as product_name,
                p.category,
                SUM(si.quantity) as total_quantity,
                SUM(si.total_price) as total_revenue,
                COUNT(DISTINCT si.sale_id) as total_sales
            ')
            ->from('sale_items si')
            ->join('products p', 'p.id = si.product_id')
            ->join('sales s', 's.id = si.sale_id')
            ->where('s.status', 'completed')
            ->where('DATE(s.created_at) >=', $startDate)
            ->where('DATE(s.created_at) <=', $endDate)
            ->groupBy('si.product_id')
            ->orderBy('total_quantity', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get sales by category
     */
    public function getSalesByCategory($startDate = null, $endDate = null)
    {
        $builder = $this->select('
                p.category,
                SUM(si.quantity) as total_quantity,
                SUM(si.total_price) as total_revenue,
                COUNT(DISTINCT si.sale_id) as total_sales
            ')
            ->from('sale_items si')
            ->join('products p', 'p.id = si.product_id')
            ->join('sales s', 's.id = si.sale_id')
            ->where('s.status', 'completed');
        
        if ($startDate) {
            $builder->where('DATE(s.created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(s.created_at) <=', $endDate);
        }
        
        return $builder->groupBy('p.category')
                      ->orderBy('total_revenue', 'DESC')
                      ->findAll();
    }
}
