<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\InventoryLogModel;

class ProductsController extends BaseController
{
    protected $productModel;
    protected $inventoryLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new ProductModel();
        $this->inventoryLogModel = new InventoryLogModel();
    }

    /**
     * Show products list
     */
    public function index()
    {
        $this->requireAuth();
        
        $search = $this->request->getGet('search') ?? '';
        $category = $this->request->getGet('category') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        
        $data = $this->productModel->getProducts($search, $category, $page);
        $categories = $this->productModel->getCategories();
        
        $viewData = [
            'title' => 'Products Management',
            'products' => $data['products'],
            'categories' => $categories,
            'pagination' => [
                'current_page' => $data['current_page'],
                'total_pages' => $data['total_pages'],
                'total' => $data['total'],
            ],
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
            'selected_category' => $category,
        ];
        
        return $this->renderView('products/index', $viewData);
    }

    /**
     * Show create product formm
     */
    public function create()
    {
        $this->requireAdmin();
        
        $categories = $this->productModel->getCategories();
        
        $data = [
            'title' => 'Add New Product',
            'categories' => $categories,
        ];
        
        return $this->renderView('products/create', $data);
    }

    /**
     * Store new product
     */
    public function store()
    {
        $this->requireAdmin();
        
        if (!$this->validateCSRF()) {
            return redirect()->back()->withInput();
        }
        
        $productData = [
            'product_code' => $this->request->getPost('product_code'),
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'price' => $this->request->getPost('price'),
            'cost_price' => $this->request->getPost('cost_price'),
            'quantity' => $this->request->getPost('quantity'),
            'min_stock' => $this->request->getPost('min_stock'),
            'is_active' => true,
        ];
        
        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/products', $newName);
            $productData['image'] = 'uploads/products/' . $newName;
        }
        
        if ($this->productModel->insert($productData)) {
            $this->setSuccessMessage('Product created successfully');
            return redirect()->to('/products');
        } else {
            $this->setErrorMessage('Failed to create product. Please check the form.');
            return redirect()->back()->withInput()->with('errors', $this->productModel->errors());
        }
    }

    /**
     * Show edit product form
     */
    public function edit($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            return redirect()->to('/products')->with('error', 'Product ID is required');
        }
        
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found');
        }
        
        $categories = $this->productModel->getCategories();
        
        $data = [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
        ];
        
        return $this->renderView('products/edit', $data);
    }

    /**
     * Update product
     */
    public function update($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            return redirect()->to('/products')->with('error', 'Product ID is required');
        }
        
        if (!$this->validateCSRF()) {
            return redirect()->back()->withInput();
        }
        
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found');
        }
        
        $productData = [
            'product_code' => $this->request->getPost('product_code'),
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'price' => $this->request->getPost('price'),
            'cost_price' => $this->request->getPost('cost_price'),
            'quantity' => $this->request->getPost('quantity'),
            'min_stock' => $this->request->getPost('min_stock'),
        ];
        
        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Delete old image if exists
            if ($product['image'] && file_exists(ROOTPATH . 'public/' . $product['image'])) {
                unlink(ROOTPATH . 'public/' . $product['image']);
            }
            
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/products', $newName);
            $productData['image'] = 'uploads/products/' . $newName;
        }
        
        if ($this->productModel->update($id, $productData)) {
            $this->setSuccessMessage('Product updated successfully');
            return redirect()->to('/products');
        } else {
            $this->setErrorMessage('Failed to update product. Please check the form.');
            return redirect()->back()->withInput()->with('errors', $this->productModel->errors());
        }
    }

    /**
     * Delete product
     */
    public function delete($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            return redirect()->to('/products')->with('error', 'Product ID is required');
        }
        
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found');
        }
        
        // Soft delete by setting is_active to false
        if ($this->productModel->update($id, ['is_active' => false])) {
            $this->setSuccessMessage('Product deleted successfully');
        } else {
            $this->setErrorMessage('Failed to delete product');
        }
        
        return redirect()->to('/products');
    }

    /**
     * Show product details
     */
    public function show($id = null)
    {
        $this->requireAuth();
        
        if (!$id) {
            return redirect()->to('/products')->with('error', 'Product ID is required');
        }
        
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found');
        }
        
        // Get inventory history
        $inventoryHistory = $this->inventoryLogModel->getProductHistory($id, 50);
        
        $data = [
            'title' => 'Product Details',
            'product' => $product,
            'inventory_history' => $inventoryHistory,
        ];
        
        return $this->renderView('products/show', $data);
    }

    /**
     * Show inventory adjustment form
     */
    public function adjustInventory($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            return redirect()->to('/products')->with('error', 'Product ID is required');
        }
        
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found');
        }
        
        $data = [
            'title' => 'Adjust Inventory',
            'product' => $product,
        ];
        
        return $this->renderView('products/adjust_inventory', $data);
    }

    /**
     * Process inventory adjustment
     */
    public function processInventoryAdjustment($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            return redirect()->to('/products')->with('error', 'Product ID is required');
        }
        
        if (!$this->validateCSRF()) {
            return redirect()->back()->withInput();
        }
        
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found');
        }
        
        $adjustmentType = $this->request->getPost('adjustment_type');
        $quantity = (int)$this->request->getPost('quantity');
        $notes = $this->request->getPost('notes');
        
        if ($quantity <= 0) {
            $this->setErrorMessage('Quantity must be greater than 0');
            return redirect()->back()->withInput();
        }
        
        $previousQuantity = $product['quantity'];
        $quantityChange = 0;
        
        switch ($adjustmentType) {
            case 'restock':
                $quantityChange = $quantity;
                break;
            case 'damaged':
            case 'return':
                $quantityChange = -$quantity;
                break;
            case 'adjustment':
                $quantityChange = $quantity - $previousQuantity;
                break;
            default:
                $this->setErrorMessage('Invalid adjustment type');
                return redirect()->back()->withInput();
        }
        
        // Update product quantity
        if ($this->productModel->updateQuantity($id, $quantityChange, $adjustmentType)) {
            // Log the inventory change
            $this->inventoryLogModel->logChange([
                'product_id' => $id,
                'user_id' => $this->userData['id'],
                'action_type' => $adjustmentType,
                'quantity_change' => $quantityChange,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $previousQuantity + $quantityChange,
                'notes' => $notes,
            ]);
            
            $this->setSuccessMessage('Inventory adjusted successfully');
            return redirect()->to('/products');
        } else {
            $this->setErrorMessage('Failed to adjust inventory');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Get products for POS (AJAX)
     */
    public function getProductsForPOS()
    {
        $search = $this->request->getGet('search') ?? '';
        $products = $this->productModel->getProductsForPOS($search);

        return $this->response->setJSON($products);
    }

    /**
     * Get product by code (AJAX)
     */
    public function getProductByCode()
    {
        $productCode = $this->request->getGet('product_code') ?? '';
        if (empty($productCode)) {
            return $this->response->setJSON(['error' => 'Product code is required']);
        }

        $product = $this->productModel->getByCode($productCode);
        if (!$product) {
            return $this->response->setJSON(['error' => 'Product not found']);
        }

        return $this->response->setJSON($product);
    }

    /**
     * Barcode Scanner API - Dedicated endpoint for barcode scanning
     */
    public function scanBarcode()
    {
        // Accept both GET and POST requests
        $barcode = $this->request->getGet('barcode') ?? $this->request->getPost('barcode') ?? '';
        
        // Also check for product_code parameter for backward compatibility
        if (empty($barcode)) {
            $barcode = $this->request->getGet('product_code') ?? $this->request->getPost('product_code') ?? '';
        }
        
        // Trim whitespace
        $barcode = trim($barcode);
        
        if (empty($barcode)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Barcode is required'
            ]);
        }

        // Search by product code
        $product = $this->productModel->getByCode($barcode);
        
        if (!$product) {
            // Also try searching by name or partial code
            $products = $this->productModel->getProductsForPOS($barcode);
            if (count($products) > 0) {
                // Return first match
                return $this->response->setJSON([
                    'success' => true,
                    'product' => $products[0],
                    'search_results' => $products
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Product not found',
                'barcode' => $barcode
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'product' => $product,
            'barcode' => $barcode
        ]);
    }

    /**
     * Get product by ID for AJAX requests
     */
    public function getProductById($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['error' => 'Product ID is required']);
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON(['error' => 'Product not found']);
        }

        return $this->response->setJSON($product);
    }

    /**
     * Export products to Excel
     */
    public function export()
    {
        $this->requireAdmin();
        
        $products = $this->productModel->where('is_active', true)->findAll();
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.xls"');
        
        // Output Excel content
        echo "<table border='1'>";
        echo "<tr><th>Product Code</th><th>Name</th><th>Category</th><th>Price</th><th>Cost Price</th><th>Quantity</th><th>Min Stock</th></tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . $product['product_code'] . "</td>";
            echo "<td>" . $product['name'] . "</td>";
            echo "<td>" . $product['category'] . "</td>";
            echo "<td>" . $this->formatCurrency($product['price']) . "</td>";
            echo "<td>" . $this->formatCurrency($product['cost_price']) . "</td>";
            echo "<td>" . $product['quantity'] . "</td>";
            echo "<td>" . $product['min_stock'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }
}
