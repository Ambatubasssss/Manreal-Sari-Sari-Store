<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class InventoryLogModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'inventory_logs';
    protected array $allowedFields = [
        'product_id', 'user_id', 'action_type', 'quantity_change',
        'previous_quantity', 'new_quantity', 'reference_id',
        'reference_type', 'notes', 'created_at', 'updated_at'
    ];

    public function __construct()
    {
        $this->mongodb = new MongoDB();
    }

    public function find($id = null)
    {
        if ($id !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }
            $result = $this->mongodb->findOne($this->collection, ['_id' => $id]);
        }
        return $result ? $this->convertDocumentToArray($result) : null;
    }

    public function findAll(int $limit = 0, int $offset = 0)
    {
        $options = [];
        if ($limit > 0) $options['limit'] = $limit;
        if ($offset > 0) $options['skip'] = $offset;
        $cursor = $this->mongodb->find($this->collection, [], $options);
        $results = [];
        foreach ($cursor as $document) {
            $results[] = $this->convertDocumentToArray($document);
        }
        return $results;
    }

    public function insert($data, bool $returnID = true)
    {
        $data = $this->filterAllowedFields($data);
        if (!isset($data['created_at'])) $data['created_at'] = new \MongoDB\BSON\UTCDateTime();
        if (!isset($data['updated_at'])) $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        $result = $this->mongodb->insert($this->collection, $data);
        return $returnID ? (string) $result : ($result !== null);
    }

    public function update($id = null, $data = null)
    {
        if ($id !== null && $data !== null) {
            if (is_string($id) && strlen($id) === 24) $id = new ObjectId($id);
            $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
            $result = $this->mongodb->updateOne($this->collection, ['_id' => $id], ['$set' => $data]);
            return $result->getModifiedCount() > 0;
        }
        return false;
    }

    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            if (is_string($id) && strlen($id) === 24) $id = new ObjectId($id);
            $result = $this->mongodb->deleteOne($this->collection, ['_id' => $id]);
            return $result->getDeletedCount() > 0;
        }
        return false;
    }

    private function convertDocumentToArray($document): array
    {
        $array = (array) $document;
        $array['id'] = (string) $array['_id'];
        unset($array['_id']);
        return $array;
    }

    private function filterAllowedFields(array $data): array
    {
        return array_intersect_key($data, array_flip($this->allowedFields));
    }

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
