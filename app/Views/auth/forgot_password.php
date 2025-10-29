<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Manreal Store POS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .forgot-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3);
        }
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-to-login a:hover {
            color: #764ba2;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem 0 0 0.5rem;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.25rem;
        }
        .info-box p {
            margin: 0;
            color: #0c5460;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="forgot-container">
                    <div class="forgot-header">
                        <i class="bi bi-shield-lock" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-0">Forgot Password</h2>
                        <p class="mb-0">Reset your account password</p>
                    </div>
                    
                    <div class="forgot-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="info-box">
                            <p>
                                <i class="bi bi-info-circle"></i>
                                Enter your registered email address below. We'll send you a link to reset your password.
                            </p>
                        </div>
                        
                        <form action="<?= base_url('auth/forgot-password') ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter your registered email"
                                           value="<?= old('email') ?>" required autofocus>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-reset btn-primary text-white">
                                    <i class="bi bi-send"></i> Send Reset Link
                                </button>
                            </div>
                        </form>
                        
                        <div class="back-to-login">
                            <a href="<?= base_url('auth') ?>">
                                <i class="bi bi-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

