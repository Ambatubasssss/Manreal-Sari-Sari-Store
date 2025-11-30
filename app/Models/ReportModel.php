<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class ReportModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'reports';
    protected array $allowedFields = [
        'type', 'data', 'generated_by', 'generated_at', 'period', 'report_data'
    ];

    public function __construct()
    {
        $this->mongodb = new MongoDB();
    }

    /**
     * Find a single document by ID
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
     * Insert a new report
     */
    public function insert($data, bool $returnID = true)
    {
        $data = $this->filterAllowedFields($data);

        // Add timestamps if not present
        if (!isset($data['generated_at'])) {
            $data['generated_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->mongodb->insert($this->collection, $data);

        return $returnID ? (string) $result : ($result !== null);
    }

    /**
     * Get reports with filtering
     */
    public function getReports($filters = [], $limit = 50, $offset = 0)
    {
        $query = [];

        if (isset($filters['type'])) {
            $query['type'] = $filters['type'];
        }

        if (isset($filters['generated_by'])) {
            $query['generated_by'] = $filters['generated_by'];
        }

        if (isset($filters['period'])) {
            $query['period'] = $filters['period'];
        }

        // Date range filtering
        if (isset($filters['start_date']) || isset($filters['end_date'])) {
            $query['generated_at'] = [];
            if (isset($filters['start_date'])) {
                $query['generated_at']['$gte'] = $filters['start_date'] . ' 00:00:00';
            }
            if (isset($filters['end_date'])) {
                $query['generated_at']['$lte'] = $filters['end_date'] . ' 23:59:59';
            }
        }

        $options = [
            'sort' => ['generated_at' => -1],
            'skip' => $offset,
            'limit' => $limit
        ];

        $reports = $this->mongodb->find($this->collection, $query, $options);
        $result = [];

        foreach ($reports as $report) {
            $result[] = $this->convertDocumentToArray($report);
        }

        return $result;
    }

    /**
     * Generate POS report
     */
    public function generatePOSReport($userId, $reportType = 'daily')
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Get sales data based on type
        $salesData = $this->getSalesData($reportType, $today, $yesterday);
        $productData = $this->getProductData();
        $cashierStats = $this->getCashierStats($userId);

        $report = [
            'type' => $reportType,
            'generated_by' => $userId,
            'generated_at' => date('Y-m-d H:i:s'),
            'period' => $reportType === 'daily' ? $today : $yesterday . ' to ' . $today,
            'report_data' => [
                'sales_summary' => $salesData,
                'product_summary' => $productData,
                'cashier_stats' => $cashierStats
            ]
        ];

        return $this->insert($report);
    }

    /**
     * Get sales data for report
     */
    private function getSalesData($type, $today, $yesterday)
    {
        $saleModel = new SaleModel();

        if ($type === 'daily') {
            return $saleModel->getDailySales($today);
        } else {
            // For closing shift, get yesterday's sales
            return $saleModel->getDailySales($yesterday);
        }
    }

    /**
     * Get product data for report
     */
    private function getProductData()
    {
        $productModel = new ProductModel();
        return $productModel->getProductStats();
    }

    /**
     * Get cashier stats
     */
    private function getCashierStats($userId)
    {
        // This would track statistics for the specific cashier
        // For now, return basic info
        return [
            'cashier_id' => $userId,
            'shift_start' => date('Y-m-d H:i:s'),
            'transactions_processed' => 0 // Would need to track this
        ];
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
}
