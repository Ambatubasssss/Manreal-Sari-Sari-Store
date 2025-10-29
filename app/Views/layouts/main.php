<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Manreal Store POS' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivrr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0.5rem;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .pagination .page-link {
            border-radius: 0.5rem;
            margin: 0 0.25rem;
        }
        .badge {
            border-radius: 0.5rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .stats-card .stats-icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .stats-card .stats-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php if (isset($user) && $user): ?>
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white fw-bold">Manreal Store</h4>
                        <small class="text-white-50">POS System</small>
                    </div>
                    
                    <div class="text-center mb-4">
                        <div class="text-white-50 small">Welcome,</div>
                        <div class="text-white fw-bold"><?= $user['full_name'] ?></div>
                        <span class="badge bg-light text-dark"><?= ucfirst($user['role']) ?></span>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link <?= $current_url == base_url('dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        
                        <?php if ($is_admin): ?>
                        <a class="nav-link <?= strpos($current_url, 'products') !== false ? 'active' : '' ?>" href="<?= base_url('products') ?>">
                            <i class="bi bi-box-seam"></i> Products
                        </a>
                        <?php endif; ?>
                        
                        <a class="nav-link <?= strpos($current_url, 'pos') !== false ? 'active' : '' ?>" href="<?= base_url('pos') ?>">
                            <i class="bi bi-cart-check"></i> POS
                        </a>
                        
                        <a class="nav-link <?= strpos($current_url, 'sales') !== false ? 'active' : '' ?>" href="<?= base_url('sales') ?>">
                            <i class="bi bi-receipt"></i> Sales
                        </a>
                        
                        <a class="nav-link <?= strpos($current_url, 'chat') !== false ? 'active' : '' ?>" href="<?= base_url('chat') ?>" id="chatNavLink">
                            <i class="bi bi-chat-dots"></i> Chat
                        </a>
                        
                        <?php if ($is_admin): ?>
                        <a class="nav-link <?= strpos($current_url, 'reports') !== false ? 'active' : '' ?>" href="<?= base_url('reports') ?>">
                            <i class="bi bi-graph-up"></i> Reports
                        </a>
                        <?php endif; ?>
                        
                        <hr class="text-white-50">
                        
                        <a class="nav-link <?= strpos($current_url, 'profile') !== false ? 'active' : '' ?>" href="<?= base_url('profile') ?>">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                        
                        <a class="nav-link" href="<?= base_url('auth/change-password') ?>">
                            <i class="bi bi-key"></i> Change Password
                        </a>
                        
                        <a class="nav-link" href="<?= base_url('auth/logout') ?>">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <!-- Top Navigation -->
                    <?php if (isset($user) && $user): ?>
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                        <div class="container-fluid">
                            <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            
                            <div class="navbar-nav ms-auto">
                                <div class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-person-circle"></i> <?= $user['full_name'] ?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= base_url('profile') ?>">
                                            <i class="bi bi-person"></i> My Profile
                                        </a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('auth/change-password') ?>">
                                            <i class="bi bi-key"></i> Change Password
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>
                    <?php endif; ?>
                    
                    <!-- Page Content -->
                    <div class="p-4">
                        <!-- Flash Messages -->
                        <?php if (isset($messages)): ?>
                            <?php foreach ($messages as $type => $message): ?>
                                <div class="alert alert-<?= $type == 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                                    <?= $message ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Page Title -->
                        <?php if (isset($title)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0"><?= $title ?></h1>
                            <?php if (isset($page_actions)): ?>
                                <div class="page-actions">
                                    <?= $page_actions ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Main Content Area -->
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
