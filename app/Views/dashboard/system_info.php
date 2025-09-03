<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?= $php_version ?></td>
                    </tr>
                    <tr>
                        <td><strong>CodeIgniter Version:</strong></td>
                        <td><?= $codeigniter_version ?></td>
                    </tr>
                    <tr>
                        <td><strong>Environment:</strong></td>
                        <td><span class="badge bg-info"><?= ENVIRONMENT ?></span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-database me-2"></i>Database Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Platform:</strong></td>
                        <td><?= $database_info['platform'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database:</strong></td>
                        <td><?= $database_info['database'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Host:</strong></td>
                        <td><?= $database_info['hostname'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Port:</strong></td>
                        <td><?= $database_info['port'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-server me-2"></i>Server Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Server Software:</strong></td>
                                <td><?= $server_info['server_software'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>PHP SAPI:</strong></td>
                                <td><?= $server_info['php_sapi'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Memory Limit:</strong></td>
                                <td><?= $server_info['memory_limit'] ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Max Execution Time:</strong></td>
                                <td><?= $server_info['max_execution_time'] ?> seconds</td>
                            </tr>
                            <tr>
                                <td><strong>Upload Max Filesize:</strong></td>
                                <td><?= $server_info['upload_max_filesize'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Post Max Size:</strong></td>
                                <td><?= $server_info['post_max_size'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?= $this->endSection() ?>
