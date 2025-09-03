<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryLogModel extends Model
{
    protected $table = 'inventory_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'product_id', 'user_id', 'action_type', 'quantity_change', 
        'previous_quantity', 'new_quantity', 'reference_id', 
        'reference_type', 'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'product_id' => 'required|integer',
        'user_id' => 'required|integer',
        'action_type' => 'required|in_list[sale,restock,adjustment,damaged,return]',
        'quantity_change' => 'required|integer',
        'previous_quantity' => 'required|integer',
        'new_quantity' => 'required|integer',
    ];

    protected $validationMessages = [
        'product_id' => [
            'required' => 'Product ID is required',
            'integer' => 'Product ID must be a valid integer',
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer',
        ],
        'action_type' => [
            'required' => 'Action type is required',
            'in_list' => 'Invalid action type selected',
        ],
        'quantity_change' => [
            'required' => 'Quantity change is required',
            'integer' => 'Quantity change must be a whole number',
        ],
        'previous_quantity' => [
            'required' => 'Previous quantity is required',
            'integer' => 'Previous quantity must be a whole number',
        ],
        'new_quantity' => [
            'required' => 'New quantity is required',
            'integer' => 'New quantity must be a whole number',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Log inventory change
     */
    public function logChange($data)
    {
        return $this->insert($data);
    }

    /**
     * Log sale transaction
     */
    public function logSale($productId, $userId, $quantity, $previousQuantity, $saleId)
    {
        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'action_type' => 'sale',
            'quantity_change' => -$quantity, // Negative for sales
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $previousQuantity - $quantity,
            'reference_id' => $saleId,
            'reference_type' => 'sale',
            'notes' => 'Sale transaction',
        ];

        return $this->logChange($data);
    }

    /**
     * Log restock transaction
     */
    public function logRestock($productId, $userId, $quantity, $previousQuantity, $notes = '')
    {
        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'action_type' => 'restock',
            'quantity_change' => $quantity, // Positive for restock
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $previousQuantity + $quantity,
            'reference_type' => 'restock',
            'notes' => $notes ?: 'Restock transaction',
        ];

        return $this->logChange($data);
    }

    /**
     * Log manual adjustment
     */
    public function logAdjustment($productId, $userId, $quantityChange, $previousQuantity, $notes = '')
    {
        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'action_type' => 'adjustment',
            'quantity_change' => $quantityChange,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $previousQuantity + $quantityChange,
            'reference_type' => 'adjustment',
            'notes' => $notes ?: 'Manual adjustment',
        ];

        return $this->logChange($data);
    }

    /**
     * Log damaged/returned items
     */
    public function logDamagedReturn($productId, $userId, $quantity, $previousQuantity, $actionType, $notes = '')
    {
        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'quantity_change' => -$quantity, // Negative for damaged/return
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $previousQuantity - $quantity,
            'reference_type' => $actionType,
            'notes' => $notes ?: ucfirst($actionType) . ' transaction',
        ];

        return $this->logChange($data);
    }

    /**
     * Get inventory logs with filters
     */
    public function getLogs($filters = [], $page = 1, $perPage = 50)
    {
        $builder = $this->builder();
        
        // Apply filters
        if (!empty($filters['product_id'])) {
            $builder->where('product_id', $filters['product_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['action_type'])) {
            $builder->where('action_type', $filters['action_type']);
        }
        
        if (!empty($filters['start_date'])) {
            $builder->where('DATE(created_at) >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('DATE(created_at) <=', $filters['end_date']);
        }
        
        $total = $builder->countAllResults(false);
        
        $offset = ($page - 1) * $perPage;
        $logs = $builder->select('
                inventory_logs.*,
                products.name as product_name,
                products.product_code,
                users.full_name as user_name
            ')
            ->join('products', 'products.id = inventory_logs.product_id')
            ->join('users', 'users.id = inventory_logs.user_id')
            ->limit($perPage, $offset)
            ->orderBy('inventory_logs.created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        return [
            'logs' => $logs,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'current_page' => $page,
        ];
    }

    /**
     * Get product inventory history
     */
    public function getProductHistory($productId, $limit = 100)
    {
        return $this->select('
                inventory_logs.*,
                users.full_name as user_name
            ')
            ->join('users', 'users.id = inventory_logs.user_id')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get inventory movement summary
     */
    public function getMovementSummary($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('DATE(created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(created_at) <=', $endDate);
        }
        
        return $builder->select('
                action_type,
                COUNT(*) as total_transactions,
                SUM(ABS(quantity_change)) as total_quantity_moved,
                SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as total_in,
                SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as total_out
            ')
            ->groupBy('action_type')
            ->orderBy('total_transactions', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts()
    {
        return $this->select('
                p.id,
                p.name,
                p.product_code,
                p.quantity,
                p.min_stock,
                p.category
            ')
            ->from('products p')
            ->where('p.quantity <= p.min_stock')
            ->where('p.is_active', true)
            ->orderBy('p.quantity', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get inventory value changes
     */
    public function getInventoryValueChanges($startDate = null, $endDate = null)
    {
        $builder = $this->db->table('inventory_logs il')
                           ->select('
                                DATE(il.created_at) as date,
                                SUM(CASE WHEN il.quantity_change > 0 THEN il.quantity_change * p.cost_price ELSE 0 END) as value_in,
                                SUM(CASE WHEN il.quantity_change < 0 THEN ABS(il.quantity_change) * p.price ELSE 0 END) as value_out
                            ')
                           ->join('products p', 'p.id = il.product_id');
        
        if ($startDate) {
            $builder->where('DATE(il.created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(il.created_at) <=', $endDate);
        }
        
        return $builder->groupBy('DATE(il.created_at)')
                      ->orderBy('date', 'ASC')
                      ->get()
                      ->getResultArray();
    }
}
