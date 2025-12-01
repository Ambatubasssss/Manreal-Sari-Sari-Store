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
        $filter = [];
        
        if (!empty($filters['product_id'])) {
            $filter['product_id'] = $filters['product_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $filter['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $filter['action_type'] = $filters['action_type'];
        }
        
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $filter['created_at'] = [];
            if (!empty($filters['start_date'])) {
                $filter['created_at']['$gte'] = new \MongoDB\BSON\UTCDateTime(strtotime($filters['start_date'] . ' 00:00:00') * 1000);
            }
            if (!empty($filters['end_date'])) {
                $filter['created_at']['$lte'] = new \MongoDB\BSON\UTCDateTime(strtotime($filters['end_date'] . ' 23:59:59') * 1000);
            }
        }
        
        $total = $this->mongodb->count($this->collection, $filter);
        
        $options = [
            'limit' => $perPage,
            'skip' => ($page - 1) * $perPage,
            'sort' => ['created_at' => -1]
        ];
        
        $cursor = $this->mongodb->find($this->collection, $filter, $options);
        $logs = [];
        
        $productModel = new ProductModel();
        $userModel = new UserModel();
        
        foreach ($cursor as $document) {
            $log = $this->convertDocumentToArray($document);
            
            // Get product info
            if (!empty($log['product_id'])) {
                $product = $productModel->find($log['product_id']);
                $log['product_name'] = $product['name'] ?? 'Unknown';
                $log['product_code'] = $product['product_code'] ?? 'N/A';
            }
            
            // Get user info
            if (!empty($log['user_id'])) {
                $user = $userModel->find($log['user_id']);
                $log['user_name'] = $user['full_name'] ?? 'Unknown';
            }
            
            $logs[] = $log;
        }
        
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
        $filter = ['product_id' => $productId];
        
        $options = [
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];
        
        $cursor = $this->mongodb->find($this->collection, $filter, $options);
        $history = [];
        
        $userModel = new UserModel();
        
        foreach ($cursor as $document) {
            $log = $this->convertDocumentToArray($document);
            
            // Get user info
            if (!empty($log['user_id'])) {
                $user = $userModel->find($log['user_id']);
                $log['user_name'] = $user['full_name'] ?? 'Unknown';
            }
            
            $history[] = $log;
        }
        
        return $history;
    }

    /**
     * Get inventory movement summary
     */
    public function getMovementSummary($startDate = null, $endDate = null)
    {
        $pipeline = [];
        
        // Only add $match stage if we have date filters
        if ($startDate || $endDate) {
            $filter = [];
            if ($startDate) {
                $filter['created_at']['$gte'] = new \MongoDB\BSON\UTCDateTime(strtotime($startDate . ' 00:00:00') * 1000);
            }
            if ($endDate) {
                $filter['created_at']['$lte'] = new \MongoDB\BSON\UTCDateTime(strtotime($endDate . ' 23:59:59') * 1000);
            }
            if (!empty($filter)) {
                $pipeline[] = ['$match' => $filter];
            }
        }
        
        $pipeline[] = ['$group' => [
            '_id' => '$action_type',
            'total_transactions' => ['$sum' => 1],
            'total_quantity_moved' => ['$sum' => ['$abs' => '$quantity_change']],
            'total_in' => [
                '$sum' => [
                    '$cond' => [
                        ['$gt' => ['$quantity_change', 0]],
                        '$quantity_change',
                        0
                    ]
                ]
            ],
            'total_out' => [
                '$sum' => [
                    '$cond' => [
                        ['$lt' => ['$quantity_change', 0]],
                        ['$abs' => '$quantity_change'],
                        0
                    ]
                ]
            ]
        ]];
        $pipeline[] = ['$sort' => ['total_transactions' => -1]];
        
        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);
        $result = $collection->aggregate($pipeline)->toArray();
        
        $summary = [];
        foreach ($result as $item) {
            $summary[] = [
                'action_type' => $item['_id'] ?? 'unknown',
                'total_transactions' => (int)($item['total_transactions'] ?? 0),
                'total_quantity_moved' => (int)($item['total_quantity_moved'] ?? 0),
                'total_in' => (int)($item['total_in'] ?? 0),
                'total_out' => (int)($item['total_out'] ?? 0)
            ];
        }
        
        return $summary;
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
        $filter = [];
        
        if ($startDate || $endDate) {
            $filter['created_at'] = [];
            if ($startDate) {
                $filter['created_at']['$gte'] = new \MongoDB\BSON\UTCDateTime(strtotime($startDate . ' 00:00:00') * 1000);
            }
            if ($endDate) {
                $filter['created_at']['$lte'] = new \MongoDB\BSON\UTCDateTime(strtotime($endDate . ' 23:59:59') * 1000);
            }
        }
        
        // Get all inventory logs in the date range
        $logs = $this->mongodb->find($this->collection, $filter);
        $productModel = new ProductModel();
        
        $dailyValues = [];
        
        foreach ($logs as $log) {
            $logArray = $this->convertDocumentToArray($log);
            $productId = $logArray['product_id'] ?? null;
            $quantityChange = (int)($logArray['quantity_change'] ?? 0);
            
            if (!$productId) continue;
            
            // Get product to get prices
            $product = $productModel->find($productId);
            if (!$product) continue;
            
            // Get date from created_at
            $createdAt = $logArray['created_at'] ?? null;
            if ($createdAt instanceof \MongoDB\BSON\UTCDateTime) {
                $date = $createdAt->toDateTime()->format('Y-m-d');
            } else {
                $date = date('Y-m-d', strtotime($createdAt));
            }
            
            if (!isset($dailyValues[$date])) {
                $dailyValues[$date] = [
                    'date' => $date,
                    'value_in' => 0,
                    'value_out' => 0
                ];
            }
            
            $costPrice = (float)($product['cost_price'] ?? 0);
            $price = (float)($product['price'] ?? 0);
            
            if ($quantityChange > 0) {
                // Incoming inventory - use cost price
                $dailyValues[$date]['value_in'] += $quantityChange * $costPrice;
            } else {
                // Outgoing inventory - use selling price
                $dailyValues[$date]['value_out'] += abs($quantityChange) * $price;
            }
        }
        
        // Sort by date
        ksort($dailyValues);
        
        return array_values($dailyValues);
    }
}
