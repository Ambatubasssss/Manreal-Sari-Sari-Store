<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Product Search and Cart -->
    <div class="col-md-8">
        <!-- Barcode Scanner -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-upc-scan me-2"></i>Barcode Scanner</h5>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" class="form-control" id="barcodeInput" placeholder="Scan barcode or enter product code...">
                    <button class="btn btn-success" type="button" onclick="scanBarcode()">
                        <i class="bi bi-upc-scan"></i> Scan
                    </button>
                </div>
                <div id="barcodeResult" class="mt-2" style="display: none;">
                    <!-- Scanned product will be displayed here -->
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-search me-2"></i>Product Search</h5>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="productSearch" placeholder="Search by product name or code...">
                    <button class="btn btn-outline-primary" type="button" onclick="searchProducts()">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>

                <div id="searchResults" class="row g-2">
                    <!-- Search results will be displayed here -->
                </div>
            </div>
        </div>
        
        <!-- Shopping Cart -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Shopping Cart</h5>
            </div>
            <div class="card-body">
                <div id="cartItems">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3">Cart is empty</p>
                        <p class="small">Search for products above to add them to your cart</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Checkout Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Checkout</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="customerName" class="form-label">Customer Name (Optional)</label>
                    <input type="text" class="form-control" id="customerName" placeholder="Walk-in Customer">
                </div>
                
                <div class="mb-3">
                    <label for="paymentMethod" class="form-label">Payment Method</label>
                    <select class="form-select" id="paymentMethod">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="gcash">GCash</option>
                        <option value="maya">Maya</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="cashReceived" class="form-label">Cash Received</label>
                    <input type="number" class="form-control" id="cashReceived" placeholder="0.00" step="0.01">
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (12%):</span>
                    <span id="tax">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total:</span>
                    <strong id="total">₱0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Change:</span>
                    <span id="change">₱0.00</span>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg" onclick="processSale()" disabled id="checkoutBtn">
                        <i class="bi bi-check-circle me-2"></i>Complete Sale
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearCart()">
                        <i class="bi bi-trash me-2"></i>Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="modalProductImage" src="" alt="Product" class="img-fluid rounded">
                    </div>
                    <div class="col-md-8">
                        <h6 id="modalProductName"></h6>
                        <p class="text-muted" id="modalProductCode"></p>
                        <p class="h5 text-primary" id="modalProductPrice"></p>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" value="1" min="1">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addToCart()">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let cart = [];
let currentProduct = null;

function scanBarcode() {
    const barcode = document.getElementById('barcodeInput').value.trim();
    if (!barcode) {
        alert('Please enter a barcode or product code');
        return;
    }

    fetch(`<?= base_url('products/by-code') ?>?product_code=${encodeURIComponent(barcode)}`)
        .then(response => response.json())
        .then(product => {
            if (product.error) {
                displayBarcodeError(product.error);
            } else {
                displayScannedProduct(product);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayBarcodeError('Error scanning barcode');
        });
}

function displayScannedProduct(product) {
    const container = document.getElementById('barcodeResult');
    container.style.display = 'block';
    container.innerHTML = `
        <div class="alert alert-success">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${product.name}</h6>
                    <p class="mb-1 text-muted small">Code: ${product.product_code}</p>
                    <p class="mb-0 fw-bold">₱${parseFloat(product.price).toFixed(2)}</p>
                </div>
                <div class="ms-3">
                    <button class="btn btn-sm btn-success" onclick="addScannedProductToCart(${JSON.stringify(product).replace(/"/g, '"')})">
                        <i class="bi bi-plus-circle"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `;

    // Clear the input after successful scan
    document.getElementById('barcodeInput').value = '';
}

function displayBarcodeError(message) {
    const container = document.getElementById('barcodeResult');
    container.style.display = 'block';
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>${message}
        </div>
    `;

    // Hide error after 3 seconds
    setTimeout(() => {
        container.style.display = 'none';
    }, 3000);
}

function addScannedProductToCart(product) {
    currentProduct = product;
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductCode').textContent = product.product_code;
    document.getElementById('modalProductPrice').textContent = `₱${parseFloat(product.price).toFixed(2)}`;
    document.getElementById('modalProductImage').src = product.image ? `<?= base_url('uploads/products/') ?>${product.image}` : '';
    document.getElementById('quantity').value = 1;

    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();

    // Hide the barcode result after adding to cartt
    document.getElementById('barcodeResult').style.display = 'none';
}

function searchProducts() {
    const searchTerm = document.getElementById('productSearch').value;
    if (searchTerm.length < 2) return;

    fetch(`<?= base_url('products/pos-search') ?>?search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(products => {
            displaySearchResults(products);
        })
        .catch(error => console.error('Error:', error));
}

function displaySearchResults(products) {
    const container = document.getElementById('searchResults');
    container.innerHTML = '';
    
    if (products.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted">No products found</div>';
        return;
    }
    
    products.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'col-md-4 col-sm-6';
        productCard.innerHTML = `
            <div class="card h-100 product-card" onclick="selectProduct(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                <div class="card-body text-center">
                    <h6 class="card-title">${product.name}</h6>
                    <p class="text-muted small">${product.product_code}</p>
                    <p class="h6 text-primary">₱${parseFloat(product.price).toFixed(2)}</p>
                    <p class="text-muted small">Stock: ${product.quantity}</p>
                </div>
            </div>
        `;
        container.appendChild(productCard);
    });
}

function selectProduct(product) {
    currentProduct = product;
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductCode').textContent = product.product_code;
    document.getElementById('modalProductPrice').textContent = `₱${parseFloat(product.price).toFixed(2)}`;
    document.getElementById('modalProductImage').src = product.image ? `<?= base_url('uploads/products/') ?>${product.image}` : '';
    document.getElementById('quantity').value = 1;
    
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}

function addToCart() {
    if (!currentProduct) return;
    
    const quantity = parseInt(document.getElementById('quantity').value);
    if (quantity <= 0) return;
    
    const existingItem = cart.find(item => item.id === currentProduct.id);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: currentProduct.id,
            name: currentProduct.name,
            product_code: currentProduct.product_code,
            price: parseFloat(currentProduct.price),
            quantity: quantity
        });
    }
    
    updateCartDisplay();
    updateTotals();
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
    modal.hide();
    
    document.getElementById('productSearch').value = '';
    document.getElementById('searchResults').innerHTML = '';
}

function updateCartDisplay() {
    const container = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3">Cart is empty</p>
                <p class="small">Search for products above to add them to your cart</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm">';
    html += '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead><tbody>';
    
    cart.forEach((item, index) => {
        html += `
            <tr>
                <td>
                    <div><strong>${item.name}</strong></div>
                    <small class="text-muted">${item.product_code}</small>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" style="width: 70px" 
                           value="${item.quantity}" min="1" onchange="updateQuantity(${index}, this.value)">
                </td>
                <td>₱${item.price.toFixed(2)}</td>
                <td>₱${(item.price * item.quantity).toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function updateQuantity(index, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(index);
        return;
    }
    
    cart[index].quantity = parseInt(newQuantity);
    updateTotals();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.12;
    const total = subtotal + tax;
    const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;
    const change = cashReceived - total;
    
    document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `₱${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
    document.getElementById('change').textContent = `₱${change.toFixed(2)}`;
    
    document.getElementById('checkoutBtn').disabled = cart.length === 0;
}

function clearCart() {
    cart = [];
    updateCartDisplay();
    updateTotals();
}

function processSale() {
    if (cart.length === 0) return;
    
    const customerName = document.getElementById('customerName').value || 'Walk-in Customer';
    const paymentMethod = document.getElementById('paymentMethod').value;
    const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;
    
    const saleData = {
        customer_name: customerName,
        payment_method: paymentMethod,
        cash_received: cashReceived,
        items: cart
    };
    
    fetch('<?= base_url('sales/process') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(saleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sale completed successfully!');
            clearCart();
            // Redirect to receipt or sales list
            window.location.href = '<?= base_url('sales') ?>';
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing sale');
    });
}

// Update totals when cash received changes
document.getElementById('cashReceived').addEventListener('input', updateTotals);

// Search on Enter key
document.getElementById('productSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchProducts();
    }
});

// Barcode scan on Enter key
document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        scanBarcode();
    }
});
</script>
<?= $this->endSection() ?>
