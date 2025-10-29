<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication routes
$routes->get('auth', 'AuthController::index');
$routes->get('login', 'AuthController::index'); // Add direct login route
$routes->get('register', 'AuthController::register');
$routes->post('auth/login', 'AuthController::login');
$routes->post('auth/processRegistration', 'AuthController::processRegistration');
$routes->get('auth/logout', 'AuthController::logout');
$routes->get('auth/change-password', 'AuthController::changePassword');
$routes->post('auth/update-password', 'AuthController::updatePassword');

// Password reset routes
$routes->get('auth/forgot-password', 'AuthController::forgotPassword');
$routes->post('auth/forgot-password', 'AuthController::processForgotPassword');
$routes->get('auth/reset-password', 'AuthController::resetPassword');
$routes->post('auth/reset-password', 'AuthController::processResetPassword');

// Profile routes
$routes->get('profile', 'ProfileController::index', ['filter' => 'auth']);
$routes->post('profile/update', 'ProfileController::update', ['filter' => 'auth']);

// Dashboard routes
$routes->get('dashboard', 'DashboardController::index');
$routes->get('dashboard/system-info', 'DashboardController::systemInfo');
$routes->get('dashboard/data', 'DashboardController::getDashboardData');

// Products routes
$routes->group('products', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ProductsController::index');
    $routes->get('create', 'ProductsController::create');
    $routes->post('store', 'ProductsController::store');
    $routes->get('edit/(:num)', 'ProductsController::edit/$1');
    $routes->post('update/(:num)', 'ProductsController::update/$1');
    $routes->get('delete/(:num)', 'ProductsController::delete/$1');
    $routes->get('show/(:num)', 'ProductsController::show/$1');
    $routes->get('adjust-inventory/(:num)', 'ProductsController::adjustInventory/$1');
    $routes->post('process-inventory-adjustment/(:num)', 'ProductsController::processInventoryAdjustment/$1');
    $routes->get('export', 'ProductsController::export');
    
    // AJAX routes
    $routes->get('pos-search', 'ProductsController::getProductsForPOS');
    $routes->get('by-code', 'ProductsController::getProductByCode');
});

// Sales routes
$routes->group('sales', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'SalesController::index');
    $routes->get('show/(:num)', 'SalesController::show/$1');
    $routes->get('cancel/(:num)', 'SalesController::cancelSale/$1');
    $routes->get('receipt/(:num)', 'SalesController::generateReceipt/$1');
    $routes->get('export', 'SalesController::export');
    
    // AJAX routes
    $routes->post('process', 'SalesController::processSale');
    $routes->get('chart-data', 'SalesController::getSalesChartData');
});

// POS routes
$routes->group('pos', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'SalesController::pos');
});

// Reports routes
$routes->group('reports', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ReportsController::index');
    $routes->get('sales', 'ReportsController::sales');
    $routes->get('inventory', 'ReportsController::inventory');
    $routes->get('daily-sales', 'ReportsController::dailySales');
    $routes->get('weekly-sales', 'ReportsController::weeklySales');
    $routes->get('monthly-sales', 'ReportsController::monthlySales');
    $routes->get('top-products', 'ReportsController::topProducts');
    $routes->get('inventory-movement', 'ReportsController::inventoryMovement');
    $routes->get('low-stock', 'ReportsController::lowStock');
    
    // Export routes
    $routes->get('export-sales', 'ReportsController::exportSales');
    $routes->get('export-inventory', 'ReportsController::exportInventory');
    
    // PDF generation routes
    $routes->get('pdf/(:segment)', 'ReportsController::generatePDF/$1');
    
    // AJAX routes
    $routes->get('chart-data', 'ReportsController::getChartData');
});

// Chat routes
$routes->group('chat', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ChatController::index');
    
    // AJAX routes
    $routes->get('users', 'ChatController::users');
    $routes->post('send', 'ChatController::sendMessage');
    $routes->get('fetch', 'ChatController::fetchMessages');
    $routes->get('search-users', 'ChatController::searchUsers');
    $routes->get('unread-count', 'ChatController::getUnreadCount');
    $routes->get('online-users', 'ChatController::getOnlineUsers');
    $routes->post('update-activity', 'ChatController::updateActivity');
});

// Default route
$routes->get('/', 'AuthController::index');

// Catch all route for 404
$routes->set404Override(function() {
    return view('errors/html/error_404', ['message' => 'The page you requested was not found.']);
});
