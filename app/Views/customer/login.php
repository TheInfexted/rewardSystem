<?php 
$title = $title ?? 'Customer Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/reward-system.css')?>?v=<?= time() ?>">
</head>
<body>
    <!-- CSRF Token for AJAX requests -->
    <script>
        const csrfToken = '<?= csrf_hash() ?>';
        const csrfName = '<?= csrf_token() ?>';
    </script>
    
    <div class="portrait-container">
        <!-- Header -->
        <header class="reward-header">
            <h1><i class="bi bi-person-circle"></i> Customer Login</h1>
        </header>
        
        <!-- Main Content -->
        <main class="reward-content">
            <!-- Alert Messages -->
            <div id="alertContainer">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="loading-spinner">
                <div class="text-center text-warning">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Processing...</p>
                </div>
            </div>
            
            <div class="reward-card">
                <div class="customer-welcome">
                    <h3><i class="bi bi-person-circle"></i> Account Access</h3>
                    <p>Login to access your dashboard.</p>
                </div>

                <form id="customerLoginForm">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="loginUsername" name="username" 
                               placeholder="Enter your username" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="loginPassword" name="password" 
                               placeholder="Enter your password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-claim">
                            <i class="bi bi-box-arrow-in-right"></i> Login to Account
                        </button>
                        <a href="<?= base_url('/') ?>" class="btn btn-platform">
                            <i class="bi bi-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </form>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="reward-footer">
            <p>&copy; 2025 TapTapWin. All rights reserved.</p>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show alert message
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Show loading spinner
        function showLoading(show = true) {
            const spinner = document.getElementById('loadingSpinner');
            spinner.classList.toggle('active', show);
        }

        // Customer login
        document.getElementById('customerLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append(csrfName, csrfToken);
            showLoading(true);
            
            fetch('<?= base_url('customer/login') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    if (data.redirect) {
                        // Redirect to dashboard
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        // Fallback: reload page
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Login failed. Please try again.', 'danger');
                console.error('Error:', error);
            });
        });

        // Debug: Log page load
        console.log('Customer login page loaded');
    </script>
</body>
</html>
