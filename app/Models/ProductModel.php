<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'product_code', 'name', 'description', 'category', 'price', 
        'cost_price', 'quantity', 'min_stock', 'image', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'product_code' => 'required|min_length[3]|max_length[50]|is_unique[products.product_code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[200]',
        'category' => 'required|max_length[100]',
        'price' => 'required|decimal',
        'cost_price' => 'required|decimal',
        'quantity' => 'required|integer|greater_than_equal_to[0]',
        'min_stock' => 'required|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'product_code' => [
            'required' => 'Product code is required',
            'min_length' => 'Product code must be at least 3 characters long',
            'max_length' => 'Product code cannot exceed 50 characters',
            'is_unique' => 'Product code already exists',
        ],
        'name' => [
            'required' => 'Product name is required',
            'min_length' => 'Product name must be at least 2 characters long',
            'max_length' => 'Product name cannot exceed 200 characters',
        ],
        'category' => [
            'required' => 'Category is required',
            'max_length' => 'Category cannot exceed 100 characters',
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal' => 'Price must be a valid decimal number',
        ],
        'cost_price' => [
            'required' => 'Cost price is required',
            'decimal' => 'Cost price must be a valid decimal number',
        ],
        'quantity' => [
            'required' => 'Quantity is required',
            'integer' => 'Quantity must be a whole number',
            'greater_than_equal_to' => 'Quantity cannot be negative',
        ],
        'min_stock' => [
            'required' => 'Minimum stock is required',
            'integer' => 'Minimum stock must be a whole number',
            'greater_than_equal_to' => 'Minimum stock cannot be negative',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get products with search and pagination
     */
    public function getProducts($search = '', $category = '', $page = 1, $perPage = 20)
    {
        $builder = $this->builder();
        
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('product_code', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
        }
        
        if (!empty($category)) {
            $builder->where('category', $category);
        }
        
        $builder->where('is_active', true);
        
        $total = $builder->countAllResults(false);
        
        $offset = ($page - 1) * $perPage;
        $products = $builder->limit($perPage, $offset)
                           ->orderBy('name', 'ASC')
                           ->get()
                           ->getResultArray();
        
        return [
            'products' => $products,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'current_page' => $page,
        ];
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        $categories = $this->select('category')
                   ->distinct()
                   ->where('is_active', true)
                   ->orderBy('category', 'ASC')
                   ->get()
                   ->getResultArray();
        
        // Extract just the category names
        return array_column($categories, 'category');
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts()
    {
        return $this->where('quantity <= min_stock')
                   ->where('is_active', true)
                   ->findAll();
    }

    /**
     * Update product quantity
     */
    public function updateQuantity($productId, $quantityChange, $type = 'adjustment')
    {
        $product = $this->find($productId);
        if (!$product) {
            return false;
        }

        $newQuantity = $product['quantity'] + $quantityChange;
        if ($newQuantity < 0) {
            return false; // Cannot have negative stock
        }

        return $this->update($productId, ['quantity' => $newQuantity]);
    }

    /**
     * Get product by code
     */
    public function getByCode($productCode)
    {
        return $this->where('product_code', $productCode)
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * Get products for POS (active products with stock)
     */
    public function getProductsForPOS($search = '')
    {
        $builder = $this->builder();
        
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('product_code', $search)
                    ->groupEnd();
        }
        
        return $builder->where('is_active', true)
                     ->where('quantity >', 0)
                     ->orderBy('name', 'ASC')
                     ->limit(50)
                     ->get()
                     ->getResultArray();
    }

    /**
     * Get product statistics
     */
    public function getProductStats()
    {
        $stats = [
            'total_products' => $this->where('is_active', true)->countAllResults(),
            'low_stock_count' => $this->where('quantity <= min_stock')->where('is_active', true)->countAllResults(),
            'out_of_stock_count' => $this->where('quantity', 0)->where('is_active', true)->countAllResults(),
            'total_value' => 0, // We'll calculate this separately if needed
        ];
        
        // Calculate total inventory value using raw SQL
        $result = $this->db->query("SELECT SUM(price * quantity) as total_value FROM products WHERE is_active = 1")->getRow();
        if ($result && $result->total_value !== null) {
            $stats['total_value'] = $result->total_value;
        }
        
        return $stats;
    }
}
