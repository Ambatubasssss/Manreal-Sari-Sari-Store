<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Manreal Store POS</title>
    
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
        .reset-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .reset-body {
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
        .password-requirements {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .password-requirements ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }
        .password-requirements li {
            color: #6c757d;
        }
        .toggle-password {
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s ease;
        }
        .toggle-password:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="reset-container">
                    <div class="reset-header">
                        <i class="bi bi-key" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-0">Reset Password</h2>
                        <p class="mb-0">Create a new password for your account</p>
                    </div>
                    
                    <div class="reset-body">
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

                        <div class="password-requirements">
                            <strong>Password Requirements:</strong>
                            <ul>
                                <li>At least 6 characters long</li>
                                <li>Both passwords must match</li>
                            </ul>
                        </div>
                        
                        <form action="<?= base_url('auth/reset-password') ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <input type="hidden" name="token" value="<?= esc($token) ?>">
                            <input type="hidden" name="email" value="<?= esc($email) ?>">
                            
                            <div class="mb-3">
                                <label for="email_display" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email_display" 
                                           value="<?= esc($email) ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter new password" required minlength="6">
                                    <span class="input-group-text toggle-password" onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="password-icon"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm new password" required minlength="6">
                                    <span class="input-group-text toggle-password" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye" id="confirm_password-icon"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-reset btn-primary text-white">
                                    <i class="bi bi-shield-check"></i> Reset Password
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
    
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Client-side password match validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>

