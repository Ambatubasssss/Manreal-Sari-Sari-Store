<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Profile Card -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i>
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Header -->
                    <div class="text-center mb-4 py-4 bg-light rounded">
                        <div class="mb-3">
                            <i class="bi bi-person-circle" style="font-size: 5rem; color: #667eea;"></i>
                        </div>
                        <h4 class="mb-1"><?= esc($profile_data['full_name']) ?></h4>
                        <span class="badge bg-primary"><?= ucfirst(esc($profile_data['role'])) ?></span>
                        <div class="text-muted small mt-2">
                            <i class="bi bi-calendar3"></i>
                            Member since: <?= isset($profile_data['created_at']) && $profile_data['created_at'] ? date('F d, Y', strtotime($profile_data['created_at'])) : 'Unknown' ?>
                        </div>
                    </div>

                    <!-- Profile Form -->
                    <form action="<?= base_url('index.php/profile/update') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="bi bi-person"></i> Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       value="<?= esc($profile_data['full_name']) ?>" 
                                       required>
                                <small class="text-muted">Your full name as it will appear in the system</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-at"></i> Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?= esc($profile_data['username']) ?>" 
                                       required
                                       minlength="3"
                                       maxlength="50">
                                <small class="text-muted">Used for login (3-50 characters)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= esc($profile_data['email']) ?>" 
                                       required>
                                <small class="text-muted">For password recovery and notifications</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact_number" class="form-label">
                                    <i class="bi bi-phone"></i> Contact Number
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="contact_number" 
                                       name="contact_number" 
                                       value="<?= esc($profile_data['contact_number'] ?? '') ?>"
                                       maxlength="20">
                                <small class="text-muted">Your phone number (optional)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-shield-check"></i> Role
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?= ucfirst(esc($profile_data['role'])) ?>" 
                                       disabled>
                                <small class="text-muted">Account role (cannot be changed)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-clock-history"></i> Last Login
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?= isset($profile_data['last_login']) && $profile_data['last_login'] ? date('M d, Y h:i A', strtotime($profile_data['last_login'])) : 'Never' ?>" 
                                       disabled>
                                <small class="text-muted">Your last login time</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Fields marked with <span class="text-danger">*</span> are required
                                </small>
                            </div>
                            <div>
                                <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-shield-lock"></i> Account Security
                    </h6>
                    <p class="card-text text-muted">
                        Want to change your password? 
                        <a href="<?= base_url('auth/change-password') ?>" class="text-decoration-none">
                            Click here to change your password
                        </a>
                    </p>
                    
                    <hr>
                    
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> Need Help?
                    </h6>
                    <p class="card-text text-muted mb-0">
                        If you need to change your role or have any issues with your account, 
                        please contact your system administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Client-side validation
document.querySelector('form').addEventListener('submit', function(e) {
    const fullName = document.getElementById('full_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    
    if (fullName.length < 2) {
        e.preventDefault();
        alert('Full name must be at least 2 characters long');
        return false;
    }
    
    if (username.length < 3) {
        e.preventDefault();
        alert('Username must be at least 3 characters long');
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
});

// Character counter for username
document.getElementById('username').addEventListener('input', function() {
    const length = this.value.length;
    const maxLength = this.getAttribute('maxlength');
    console.log(`Username: ${length}/${maxLength} characters`);
});
</script>
<?= $this->endSection() ?>

