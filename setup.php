<?php
/**
 * Manreal Store POS System Setup Script
 * 
 * This script helps you set up the POS system for the first time.
 * Run this script in your browser to configure the system.
 */

// Check if system is already configured
if (file_exists('.env') && file_exists('app/Config/Database.php')) {
    echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;">';
    echo '<strong>System Already Configured!</strong><br>';
    echo 'The POS system appears to be already configured. If you need to reconfigure, please delete the .env file and run this script again.';
    echo '</div>';
    exit;
}

// Handle form submission
if ($_POST) {
    $baseURL = $_POST['base_url'] ?? '';
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? 'manreal_pos';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    $encryptionKey = bin2hex(random_bytes(32));
    
    // Test database connection
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create .env file
        $envContent = "#--------------------------------------------------------------------
# Manreal Store POS System Environment Configuration
#--------------------------------------------------------------------

CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = '$baseURL'
app.forceGlobalSecureRequests = false
app.CSPEnabled = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = $dbHost
database.default.database = $dbName
database.default.username = $dbUser
database.default.password = $dbPass
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

encryption.key = '$encryptionKey'

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------

session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.savePath = null

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------

logger.threshold = 4
";
        
        if (file_put_contents('.env', $envContent)) {
            // Create uploads directory
            if (!is_dir('public/uploads')) {
                mkdir('public/uploads', 0755, true);
            }
            if (!is_dir('public/uploads/products')) {
                mkdir('public/uploads/products', 0755, true);
            }
            
            echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;">';
            echo '<strong>Setup Complete!</strong><br>';
            echo 'The system has been configured successfully. Please follow these steps:<br><br>';
            echo '1. <strong>Run Database Migrations:</strong><br>';
            echo '   <code>php spark migrate</code><br><br>';
            echo '2. <strong>Seed Initial Data:</strong><br>';
            echo '   <code>php spark db:seed UserSeeder</code><br>';
            echo '   <code>php spark db:seed ProductSeeder</code><br><br>';
            echo '3. <strong>Access the System:</strong><br>';
            echo '   <a href="' . $baseURL . '" style="color: #155724;">Click here to access the POS system</a><br><br>';
            echo '4. <strong>Default Login Credentials:</strong><br>';
            echo '   Admin: admin / admin123<br>';
            echo '   Cashier: cashier1 / cashier123<br><br>';
            echo '5. <strong>Delete this setup file:</strong><br>';
            echo '   <code>rm setup.php</code>';
            echo '</div>';
            exit;
        } else {
            $error = 'Failed to create .env file. Please check file permissions.';
        }
    } catch (PDOException $e) {
        $error = 'Database connection failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manreal Store POS System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .setup-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .setup-body {
            padding: 2rem;
        }
        .store-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="setup-container">
                    <div class="setup-header">
                        <div class="store-logo">🏪</div>
                        <h3 class="mb-2">Manreal Store POS System</h3>
                        <p class="mb-0">Initial Setup & Configuration</p>
                    </div>
                    
                    <div class="setup-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <h5 class="mb-3">System Configuration</h5>
                            
                            <div class="mb-3">
                                <label for="base_url" class="form-label">Base URL</label>
                                <input type="url" class="form-control" id="base_url" name="base_url" 
                                       value="<?= $_POST['base_url'] ?? 'http://localhost/Manrealstore/' ?>" required>
                                <div class="form-text">The base URL where your system will be accessed (e.g., http://localhost/Manrealstore/)</div>
                            </div>
                            
                            <h5 class="mb-3 mt-4">Database Configuration</h5>
                            
                            <div class="mb-3">
                                <label for="db_host" class="form-label">Database Host</label>
                                <input type="text" class="form-control" id="db_host" name="db_host" 
                                       value="<?= $_POST['db_host'] ?? 'localhost' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="db_name" class="form-label">Database Name</label>
                                <input type="text" class="form-control" id="db_name" name="db_name" 
                                       value="<?= $_POST['db_name'] ?? 'manreal_pos' ?>" required>
                                <div class="form-text">Create this database first in your MySQL server</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="db_user" class="form-label">Database Username</label>
                                <input type="text" class="form-control" id="db_user" name="db_user" 
                                       value="<?= $_POST['db_user'] ?? 'root' ?>" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="db_pass" class="form-label">Database Password</label>
                                <input type="password" class="form-control" id="db_pass" name="db_pass" 
                                       value="<?= $_POST['db_pass'] ?? '' ?>">
                                <div class="form-text">Leave empty if no password is set</div>
                            </div>
                            
                            <div class="alert alert-info" role="alert">
                                <strong>Prerequisites:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>MySQL/MariaDB server running</li>
                                    <li>Database 'manreal_pos' created</li>
                                    <li>PHP with mysqli extension enabled</li>
                                    <li>CodeIgniter 4 framework files</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    🚀 Configure System
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <h6>What happens next?</h6>
                            <ol class="text-start text-muted">
                                <li>System configuration files will be created</li>
                                <li>Upload directories will be set up</li>
                                <li>You'll need to run database migrations</li>
                                <li>Initial data will be seeded</li>
                                <li>System will be ready for use</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
