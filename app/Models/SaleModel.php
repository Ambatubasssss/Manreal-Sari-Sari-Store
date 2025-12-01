<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class SaleModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'sales';
    protected array $allowedFields = [
        'sale_number', 'user_id', 'customer_name', 'subtotal', 'discount',
        'tax', 'total_amount', 'cash_received', 'change_amount',
        'payment_method', 'status', 'notes', 'created_at', 'updated_at'
    ];

    public function __construct()
    {
        $this->mongodb = new MongoDB();
    }

    /**
     * Find a single document by ID or conditions
     */
    public function find($id = null)
    {
        if ($id !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }
            $result = $this->mongodb->findOne($this->collection, ['_id' => $id]);
        } else {
            $result = $this->mongodb->findOne($this->collection, $this->whereConditions ?? []);
        }

        return $result ? $this->convertDocumentToArray($result) : null;
    }

    /**
     * Find all documents matching conditions
     */
    public function findAll(int $limit = 0, int $offset = 0)
    {
        $options = [];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }
        if ($offset > 0) {
            $options['skip'] = $offset;
        }

        $cursor = $this->mongodb->find($this->collection, $this->whereConditions ?? [], $options);
        $results = [];

        foreach ($cursor as $document) {
            $results[] = $this->convertDocumentToArray($document);
        }

        return $results;
    }

    /**
     * Insert a new document
     */
    public function insert($data, bool $returnID = true)
    {
        $data = $this->filterAllowedFields($data);

        // Add timestamps if not present
        if (!isset($data['created_at'])) {
            $data['created_at'] = new \MongoDB\BSON\UTCDateTime();
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        }

        $result = $this->mongodb->insert($this->collection, $data);

        return $returnID ? (string) $result : ($result !== null);
    }

    /**
     * Update a document
     */
    public function update($id = null, $data = null)
    {
        if ($id !== null && $data !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }

            $data = $this->filterAllowedFields($data);
            $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();

            $result = $this->mongodb->updateOne(
                $this->collection,
                ['_id' => $id],
                ['$set' => $data]
            );

            return $result->getModifiedCount() > 0;
        }

        return false;
    }

    /**
     * Delete a document
     */
    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }

            $result = $this->mongodb->deleteOne($this->collection, ['_id' => $id]);
            return $result->getDeletedCount() > 0;
        }

        return false;
    }

    /**
     * Add WHERE condition
     */
    public function where($key, $value = null)
    {
        if (!isset($this->whereConditions)) {
            $this->whereConditions = [];
        }

        if (is_array($key)) {
            $this->whereConditions = array_merge($this->whereConditions, $key);
        } else {
            $this->whereConditions[$key] = $value;
        }

        return $this;
    }

    /**
     * Convert MongoDB document to array
     */
    private function convertDocumentToArray($document): array
    {
        $array = (array) $document;
        $array['id'] = (string) $array['_id'];
        unset($array['_id']);

        return $array;
    }

    /**
     * Filter data to only allowed fields
     */
    private function filterAllowedFields(array $data): array
    {
        return array_intersect_key($data, array_flip($this->allowedFields));
    }

    /**
     * Count documents - alias for MongoDB
     */
    public function countAllResults(): int
    {
        return $this->mongodb->count($this->collection, $this->whereConditions ?? []);
    }

    /**
     * Alias for countAllResults
     */
    public function countAll(): int
    {
        return $this->countAllResults();
    }

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

        // Find the last sale for today using MongoDB
        $todayString = date('Y-m-d');
        $todayStart = new \MongoDB\BSON\UTCDateTime(strtotime($todayString . ' 00:00:00') * 1000);
        $todayEnd = new \MongoDB\BSON\UTCDateTime(strtotime($todayString . ' 23:59:59') * 1000);

        $pipeline = [
            ['$match' => [
                'created_at' => ['$gte' => $todayStart->toDateTime(), '$lte' => $todayEnd->toDateTime()]
            ]],
            ['$sort' => ['created_at' => -1]],
            ['$limit' => 1]
        ];

        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);
        $result = $collection->aggregate($pipeline)->toArray();

        if (!empty($result)) {
            $lastSale = $this->convertDocumentToArray($result[0]);
            $lastNumber = $lastSale['sale_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get sales with pagination and filters - SIMPLIFIED
     */
    public function getSales($filters = [], $page = 1, $perPage = 20)
    {
        $filter = [];

        if (!empty($filters['status'])) {
            $filter['status'] = $filters['status'];
        }

        $total = $this->mongodb->count($this->collection, $filter);
        $sales = $this->findAll($perPage, ($page - 1) * $perPage);

        // Add cashier name from user lookup (simplified)
        foreach ($sales as &$sale) {
            $userModel = new UserModel();
            $user = $userModel->find($sale['user_id']);
            $sale['cashier_name'] = $user ? $user['full_name'] : 'Unknown';
        }

        return [
            'sales' => $sales,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'current_page' => $page,
        ];
    }

    /**
     * Get sale details with items - SIMPLIFIED
     */
    public function getSaleWithItems($saleId)
    {
        $sale = $this->find($saleId);

        if (!$sale) {
            return null;
        }

        // Add cashier name
        $userModel = new UserModel();
        $user = $userModel->find($sale['user_id']);
        $sale['cashier_name'] = $user ? $user['full_name'] : 'Unknown';

        // Get sale items (placeholder for now)
        // $saleItemModel = new SaleItemModel();
        // $sale['items'] = $saleItemModel->where('sale_id', $saleId)->findAll();
        $sale['items'] = []; // Empty for now

        return $sale;
    }

    /**
     * Get daily sales report - SIMPLIFIED VERSION
     */
    public function getDailySales($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Simple aggregation to get daily totals
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';

        $pipeline = [
            ['$match' => [
                'created_at' => ['$gte' => new \MongoDB\BSON\UTCDateTime(strtotime($startDate) * 1000),
                                '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($endDate) * 1000)],
                'status' => 'completed'
            ]],
            ['$group' => [
                '_id' => [
                    '$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']
                ],
                'total_sales' => ['$sum' => 1],
                'total_revenue' => ['$sum' => ['$toDouble' => '$total_amount']],
                'total_discount' => ['$sum' => ['$toDouble' => '$discount']]
            ]],
            ['$project' => [
                'date' => '$_id',
                'total_sales' => 1,
                'total_revenue' => 1,
                'total_discount' => 1,
                'average_sale' => ['$divide' => ['$total_revenue', '$total_sales']]
            ]]
        ];

        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);
        $result = $collection->aggregate($pipeline)->toArray();

        if (!empty($result)) {
            return (array) $result[0];
        }

        return [
            'date' => $date,
            'total_sales' => 0,
            'total_revenue' => 0,
            'total_discount' => 0,
            'average_sale' => 0
        ];
    }

    /**
     * Get weekly sales report
     */
    public function getWeeklySales($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-7 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }

        $startTimestamp = new \MongoDB\BSON\UTCDateTime(strtotime($startDate . ' 00:00:00') * 1000);
        $endTimestamp = new \MongoDB\BSON\UTCDateTime(strtotime($endDate . ' 23:59:59') * 1000);

        $pipeline = [
            ['$match' => [
                'created_at' => [
                    '$gte' => $startTimestamp,
                    '$lte' => $endTimestamp
                ],
                'status' => 'completed'
            ]],
            ['$group' => [
                '_id' => [
                    '$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']
                ],
                'total_sales' => ['$sum' => 1],
                'total_revenue' => ['$sum' => ['$toDouble' => '$total_amount']],
                'total_discount' => ['$sum' => ['$toDouble' => '$discount']]
            ]],
            ['$project' => [
                'date' => '$_id',
                'total_sales' => 1,
                'total_revenue' => 1,
                'total_discount' => 1
            ]],
            ['$sort' => ['date' => 1]]
        ];

        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);
        $result = $collection->aggregate($pipeline)->toArray();

        $sales = [];
        foreach ($result as $item) {
            $sales[] = [
                'date' => $item['date'],
                'total_sales' => (int)($item['total_sales'] ?? 0),
                'total_revenue' => (float)($item['total_revenue'] ?? 0),
                'total_discount' => (float)($item['total_discount'] ?? 0)
            ];
        }

        return $sales;
    }

    /**
     * Get monthly sales report
     */
    public function getMonthlySales($year = null, $month = null)
    {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');

        $startDate = strtotime("{$year}-{$month}-01");
        $endDate = strtotime("{$year}-{$month}-01 +1 month") - 1;

        $pipeline = [
            ['$match' => [
                'created_at' => ['$gte' => new \MongoDB\BSON\UTCDateTime($startDate * 1000),
                                '$lte' => new \MongoDB\BSON\UTCDateTime($endDate * 1000)],
                'status' => 'completed'
            ]],
            ['$group' => [
                '_id' => [
                    '$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']
                ],
                'total_sales' => ['$sum' => 1],
                'total_revenue' => ['$sum' => ['$toDouble' => '$total_amount']],
                'total_discount' => ['$sum' => ['$toDouble' => '$discount']]
            ]],
            ['$project' => [
                'date' => '$_id',
                'total_sales' => 1,
                'total_revenue' => 1,
                'total_discount' => 1
            ]],
            ['$sort' => ['date' => 1]]
        ];

        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);
        $result = $collection->aggregate($pipeline)->toArray();

        return array_map(function($item) {
            return (array) $item;
        }, $result);
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 10, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }

        $startTimestamp = new \MongoDB\BSON\UTCDateTime(strtotime($startDate . ' 00:00:00') * 1000);
        $endTimestamp = new \MongoDB\BSON\UTCDateTime(strtotime($endDate . ' 23:59:59') * 1000);

        // First, get all completed sales in the date range
        $filter = [
            'created_at' => [
                '$gte' => $startTimestamp,
                '$lte' => $endTimestamp
            ],
            'status' => 'completed'
        ];
        
        $sales = $this->mongodb->find($this->collection, $filter);
        $saleIds = [];
        
        foreach ($sales as $sale) {
            $saleArray = $this->convertDocumentToArray($sale);
            $saleIds[] = $saleArray['id'];
        }

        if (empty($saleIds)) {
            return [];
        }

        // Get sale items for these sales directly from MongoDB
        // sale_id is stored as string in sale_items
        $saleItemsCollection = $this->mongodb->getDatabase()->selectCollection('sale_items');
        $saleItems = $saleItemsCollection->find(['sale_id' => ['$in' => $saleIds]]);
        
        $productStats = [];
        
        // Get product model to look up categories
        $productModel = new ProductModel();
        
        foreach ($saleItems as $item) {
            $itemArray = (array)$item;
            $productCode = $itemArray['product_code'] ?? '';
            $productName = $itemArray['product_name'] ?? 'Unknown';
            $productId = $itemArray['product_id'] ?? null;
            
            if (!isset($productStats[$productCode])) {
                // Look up product category
                $category = 'Unknown';
                if ($productId) {
                    $product = $productModel->find($productId);
                    if ($product && isset($product['category'])) {
                        $category = $product['category'];
                    }
                }
                
                $productStats[$productCode] = [
                    'product_code' => $productCode,
                    'product_name' => $productName,
                    'category' => $category,
                    'total_quantity' => 0,
                    'total_revenue' => 0
                ];
            }
            
            $productStats[$productCode]['total_quantity'] += (int)($itemArray['quantity'] ?? 0);
            $productStats[$productCode]['total_revenue'] += (float)($itemArray['total_price'] ?? 0);
        }

        // Sort by revenue and limit
        usort($productStats, function($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        return array_slice($productStats, 0, $limit);
    }

    /**
     * Get sales statistics
     */
    public function getSalesStats($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }

        $startTimestamp = new \MongoDB\BSON\UTCDateTime(strtotime($startDate . ' 00:00:00') * 1000);
        $endTimestamp = new \MongoDB\BSON\UTCDateTime(strtotime($endDate . ' 23:59:59') * 1000);

        $pipeline = [
            ['$match' => [
                'created_at' => [
                    '$gte' => $startTimestamp,
                    '$lte' => $endTimestamp
                ],
                'status' => 'completed'
            ]],
            ['$group' => [
                '_id' => null,
                'total_sales' => ['$sum' => 1],
                'total_revenue' => ['$sum' => ['$toDouble' => '$total_amount']],
                'total_discount' => ['$sum' => ['$toDouble' => '$discount']],
                'min_sale' => ['$min' => ['$toDouble' => '$total_amount']],
                'max_sale' => ['$max' => ['$toDouble' => '$total_amount']],
                'avg_sale' => ['$avg' => ['$toDouble' => '$total_amount']]
            ]]
        ];

        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);
        $result = $collection->aggregate($pipeline)->toArray();

        if (!empty($result)) {
            $stats = (array)$result[0];
            return [
                'total_sales' => (int)($stats['total_sales'] ?? 0),
                'total_revenue' => (float)($stats['total_revenue'] ?? 0),
                'total_discount' => (float)($stats['total_discount'] ?? 0),
                'average_sale' => (float)($stats['avg_sale'] ?? 0),
                'min_sale' => (float)($stats['min_sale'] ?? 0),
                'max_sale' => (float)($stats['max_sale'] ?? 0)
            ];
        }

        return [
            'total_sales' => 0,
            'total_revenue' => 0,
            'total_discount' => 0,
            'average_sale' => 0,
            'min_sale' => 0,
            'max_sale' => 0
        ];
    }
}
