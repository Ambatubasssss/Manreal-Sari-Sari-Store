<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class SaleItemModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'sale_items';
    protected array $allowedFields = [
        'sale_id', 'product_id', 'product_code', 'product_name',
        'quantity', 'unit_price', 'total_price', 'created_at', 'updated_at'
    ];

    public function __construct()
    {
        $this->mongodb = new MongoDB();
    }

    // Basic CRUD
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

    public function where($key, $value = null)
    {
        // Simplified where for this context
        $this->_whereConditions[$key] = $value;
        return $this;
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

    // Specific methods

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
