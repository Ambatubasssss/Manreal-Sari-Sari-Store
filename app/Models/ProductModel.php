<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class ProductModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'products';
    protected array $allowedFields = [
        'product_code', 'name', 'description', 'category', 'price',
        'cost_price', 'quantity', 'min_stock', 'image', 'is_active',
        'created_at', 'updated_at'
    ];

    protected $whereConditions = [];

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

        $this->whereConditions = []; // Reset for next query

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

            if ($purge) {
                $result = $this->mongodb->deleteOne($this->collection, ['_id' => $id]);
                return $result->getDeletedCount() > 0;
            } else {
                // Soft delete - update is_active to false
                // Update the document - if it exists, the operation succeeds
                $result = $this->mongodb->updateOne(
                    $this->collection,
                    ['_id' => $id],
                    ['$set' => ['is_active' => false, 'updated_at' => new \MongoDB\BSON\UTCDateTime()]]
                );
                // Check if document was matched (found) - this means the operation succeeded
                // Modified count might be 0 if already false, but matched count tells us if document exists
                return $result->getMatchedCount() > 0;
            }
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
     * Get products for POS (active products with stock)
     */
    public function getProductsForPOS($search = '')
    {
        // Filter for active products (include products where is_active is not set or is true)
        // and has quantity > 0
        $filter = [
            '$and' => [
                [
                    '$or' => [
                        ['is_active' => true],
                        ['is_active' => ['$exists' => false]]
                    ]
                ],
                ['quantity' => ['$gt' => 0]]
            ]
        ];

        $products = [];

        if (!empty($search)) {
            $trimmedSearch = trim($search);

            // First, try exact product code match (case-insensitive)
            $exactCodeFilter = $filter;
            $exactCodeFilter['$and'][] = [
                'product_code' => ['$regex' => '^' . preg_quote($trimmedSearch, '/') . '$', '$options' => 'i']
            ];
            $exactMatch = $this->mongodb->findOne($this->collection, $exactCodeFilter);
            if ($exactMatch) {
                $products[] = $this->convertDocumentToArray($exactMatch);
            }

            // Also try exact name match (case-insensitive) - avoid duplicates
            $exactNameFilter = $filter;
            $exactNameFilter['$and'][] = [
                'name' => ['$regex' => '^' . preg_quote($trimmedSearch, '/') . '$', '$options' => 'i']
            ];
            $exactNameMatch = $this->mongodb->findOne($this->collection, $exactNameFilter);
            if ($exactNameMatch) {
                $productArray = $this->convertDocumentToArray($exactNameMatch);
                // Avoid duplicates
                $duplicate = false;
                foreach ($products as $existing) {
                    if ($existing['id'] === $productArray['id']) {
                        $duplicate = true;
                        break;
                    }
                }
                if (!$duplicate) {
                    $products[] = $productArray;
                }
            }

            // Then add other matches using regex (partial matches)
            $regexFilter = $filter;
            $regexFilter['$and'][] = [
                '$or' => [
                    ['name' => ['$regex' => $trimmedSearch, '$options' => 'i']],
                    ['product_code' => ['$regex' => $trimmedSearch, '$options' => 'i']],
                    ['description' => ['$regex' => $trimmedSearch, '$options' => 'i']]
                ]
            ];

            $options = [
                'limit' => 50,
                'sort' => ['name' => 1]
            ];

            $cursor = $this->mongodb->find($this->collection, $regexFilter, $options);

            foreach ($cursor as $document) {
                $productArray = $this->convertDocumentToArray($document);
                // Avoid duplicates
                $duplicate = false;
                foreach ($products as $existing) {
                    if ($existing['id'] === $productArray['id']) {
                        $duplicate = true;
                        break;
                    }
                }
                if (!$duplicate) {
                    $products[] = $productArray;
                }
            }
        } else {
            $options = [
                'limit' => 50,
                'sort' => ['name' => 1]
            ];

            $cursor = $this->mongodb->find($this->collection, $filter, $options);
            foreach ($cursor as $document) {
                $products[] = $this->convertDocumentToArray($document);
            }
        }

        return $products;
    }

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
        $filter = ['is_active' => true];

        if (!empty($category)) {
            $filter['category'] = $category;
        }

        if (!empty($search)) {
            $trimmedSearch = trim($search);
            // Check for exact product code match
            $exactMatch = $this->mongodb->findOne($this->collection, array_merge($filter, ['product_code' => $trimmedSearch]));
            if ($exactMatch) {
                // Return array with the exact match first
                $results = [$this->convertDocumentToArray($exactMatch)];
                // Then add other matches
                $filter['$or'] = [
                    ['name' => ['$regex' => '^' . preg_quote($trimmedSearch, '') . '$', '$options' => 'i']],
                    ['product_code' => ['$regex' => '^' . preg_quote($trimmedSearch, '') . '$', '$options' => 'i']],
                    ['description' => ['$regex' => '^' . preg_quote($trimmedSearch, '') . '$', '$options' => 'i']]
                ];
            } else {
                $filter['$or'] = [
                    ['name' => ['$regex' => $trimmedSearch, '$options' => 'i']],
                    ['product_code' => ['$regex' => $trimmedSearch, '$options' => 'i']],
                    ['description' => ['$regex' => $trimmedSearch, '$options' => 'i']]
                ];
            }
        }

        $total = $this->mongodb->count($this->collection, $filter);

        $options = [
            'limit' => $perPage,
            'skip' => ($page - 1) * $perPage,
            'sort' => ['name' => 1]
        ];

        $cursor = $this->mongodb->find($this->collection, $filter, $options);
        $products = [];

        foreach ($cursor as $document) {
            $products[] = $this->convertDocumentToArray($document);
        }

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
        // MongoDB distinct operation
        $pipeline = [
            ['$match' => ['is_active' => true]],
            ['$group' => ['_id' => '$category']],
            ['$sort' => ['_id' => 1]]
        ];

        $cursor = $this->mongodb->getDatabase()->selectCollection($this->collection)->aggregate($pipeline);

        $categories = [];
        foreach ($cursor as $doc) {
            $categories[] = $doc['_id'];
        }

        return $categories;
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts()
    {
        // Filter for active products where quantity <= min_stock
        // Only include products where min_stock > 0 (exclude products with min_stock = 0 or null)
        $filter = [
            '$and' => [
                [
                    '$or' => [
                        ['is_active' => true],
                        ['is_active' => ['$exists' => false]]
                    ]
                ],
                // min_stock must exist and be greater than 0
                ['min_stock' => ['$exists' => true, '$gt' => 0]],
                // quantity must be less than or equal to min_stock
                [
                    '$expr' => [
                        '$lte' => [
                            ['$ifNull' => ['$quantity', 0]],
                            '$min_stock'
                        ]
                    ]
                ]
            ]
        ];
        
        $options = [
            'sort' => ['quantity' => 1] // Sort by quantity ascending
        ];
        
        $cursor = $this->mongodb->find($this->collection, $filter, $options);
        $products = [];
        
        foreach ($cursor as $document) {
            $products[] = $this->convertDocumentToArray($document);
        }
        
        return $products;
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
        $result = $this->mongodb->findOne($this->collection, [
            'product_code' => ['$regex' => '^' . preg_quote(trim($productCode), '') . '$', '$options' => 'i'],
            'is_active' => true,
            'quantity' => ['$gt' => 0]  // Must have stock > 0
        ]);
        return $result ? $this->convertDocumentToArray($result) : null;
    }

    /**
     * Get product statistics
     */
    public function getProductStats()
    {
        // Filter for active products (including those without is_active field)
        $filter = ['$or' => [['is_active' => ['$exists' => false]], ['is_active' => true]]];

        $collection = $this->mongodb->getDatabase()->selectCollection($this->collection);

        // Count total products
        $totalProducts = $this->mongodb->count($this->collection, $filter) ?? 0;
        
        // Count out of stock products
        $outOfStockFilter = array_merge($filter, ['quantity' => 0]);
        $outOfStockCount = $this->mongodb->count($this->collection, $outOfStockFilter) ?? 0;
        
        // Count low stock products (quantity <= min_stock) using aggregation
        // Only count products where min_stock > 0 (exclude products with min_stock = 0 or null)
        // MongoDB countDocuments may not support $expr directly, so use aggregation
        $lowStockPipeline = [
            ['$match' => $filter],
            ['$match' => [
                // min_stock must exist and be greater than 0
                'min_stock' => ['$exists' => true, '$gt' => 0],
                // quantity must be less than or equal to min_stock
                '$expr' => [
                    '$lte' => [
                        ['$ifNull' => ['$quantity', 0]],
                        '$min_stock'
                    ]
                ]
            ]],
            ['$count' => 'count']
        ];
        
        $lowStockResult = $collection->aggregate($lowStockPipeline)->toArray();
        $lowStockCount = !empty($lowStockResult) ? (int)($lowStockResult[0]['count'] ?? 0) : 0;

        $stats = [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_value' => 0,
        ];

        // Calculate total inventory value using MongoDB aggregation
        $pipeline = [
            ['$match' => $filter],
            ['$group' => [
                '_id' => null,
                'total_value' => [
                    '$sum' => [
                        '$multiply' => [
                            ['$toDouble' => ['$ifNull' => ['$price', 0]]],
                            ['$toDouble' => ['$ifNull' => ['$quantity', 0]]]
                        ]
                    ]
                ]
            ]]
        ];

        $result = $collection->aggregate($pipeline)->toArray();

        if (!empty($result) && isset($result[0]['total_value'])) {
            $stats['total_value'] = (float) $result[0]['total_value'];
        }

        return $stats;
    }

    /**
     * Merge conditions into whereConditions
     */
    private function mergeWhereConditions($newConditions)
    {
        if (empty($this->whereConditions)) {
            $this->whereConditions = $newConditions;
        } else {
            $this->whereConditions = ['$and' => [$this->whereConditions, $newConditions]];
        }
    }

    /**
     * Filter data to only include allowed fields
     */
    protected function filterAllowedFields(array $data): array
    {
        if (empty($this->allowedFields)) {
            return $data;
        }

        $filtered = [];
        foreach ($this->allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }

        return $filtered;
    }
}
