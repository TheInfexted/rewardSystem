<?php 
// Check if winner_data exists and is valid
$hasValidPrize = isset($winner_data) && is_array($winner_data) && isset($winner_data['name']);
$isLoggedIn = isset($logged_in) && $logged_in;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Claim Your Reward' ?></title>
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
            <h1><i class="bi bi-gift-fill"></i> Claim Your Reward</h1>
        </header>
        
        <!-- Main Content -->
        <main class="reward-content">
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
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
                <?php if ($hasValidPrize): ?>
                    <!-- Prize Display -->
                    <div class="prize-display">
                        <p class="prize-text">🎉 Congratulations! You Won:</p>
                        <p class="prize-amount"><?= esc($winner_data['name']) ?></p>
                        <?php if (isset($winner_data['prize']) && $winner_data['prize'] > 0): ?>
                            <p class="prize-text">Value: $<?= number_format($winner_data['prize']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <!-- Logged In User - Ready to Claim -->
                        <div class="customer-welcome">
                            <div class="customer-info">
                                <i class="bi bi-person-check-fill"></i> 
                                Welcome back, <?= esc($customer_data['username'] ?? 'Customer') ?>!
                            </div>
                            <?php if (isset($customer_data['points'])): ?>
                                <div class="points-display">
                                    <i class="bi bi-star-fill"></i> Points: <?= number_format($customer_data['points']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="claimStep">
                            <h5 class="text-center text-warning mb-3">Choose Platform to Contact:</h5>
                            <div class="platform-selection">
                                <div class="platform-btn whatsapp" onclick="claimReward('whatsapp')">
                                    <i class="bi bi-whatsapp"></i>
                                    <span>WhatsApp</span>
                                </div>
                                <div class="platform-btn telegram" onclick="claimReward('telegram')">
                                    <i class="bi bi-telegram"></i>
                                    <span>Telegram</span>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Not Logged In - Show Registration Options -->
                        <div id="authContainer">
                            <!-- Registration Options (Initially Visible) -->
                            <div id="registrationOptions">
                                <h5 class="text-center text-warning mb-3">Create Account to Claim</h5>
                                <p class="text-center text-dark mb-4">We'll create an instant account for you!</p>
                                
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-claim" onclick="autoRegister()">
                                        <i class="bi bi-lightning-fill"></i> Create New Account
                                    </button>
                                    <a href="<?= base_url('customer') ?>" class="btn btn-platform">
                                        <i class="bi bi-box-arrow-in-right"></i> Already have an account? Login
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Account Created Step (Hidden Initially) -->
                            <div id="accountCreatedStep" style="display: none;">
                                <h5 class="text-center text-success mb-3">
                                    <i class="bi bi-check-circle-fill"></i> Account Created!
                                </h5>
                                
                                <div class="credentials-box">
                                    <h6 class="text-warning mb-3">Save Your Login Details:</h6>
                                    <div class="credential-item">
                                        <div class="credential-label">Username:</div>
                                        <div id="displayUsername">-</div>
                                    </div>
                                    <div class="credential-item">
                                        <div class="credential-label">Password:</div>
                                        <div id="displayPassword">-</div>
                                    </div>
                                </div>
                                
                                <div class="important-note">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    Important: Save these credentials to access your account later!
                                </div>

                                <h5 class="text-center text-warning mb-4">Contact Customer Service to Claim Your Prize</h5>
                                <div class="platform-selection">
                                    <div class="platform-btn whatsapp" onclick="claimReward('whatsapp')">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>WhatsApp</span>
                                    </div>
                                    <div class="platform-btn telegram" onclick="claimReward('telegram')">
                                        <i class="bi bi-telegram"></i>
                                        <span>Telegram</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php elseif ($isLoggedIn): ?>
                    <!-- Logged In User - No Prize but Show Dashboard Access -->
                    <div class="customer-welcome">
                        <div class="customer-info">
                            <i class="bi bi-person-check-fill"></i> 
                            Welcome back, <?= esc($customer_data['username'] ?? 'Customer') ?>!
                        </div>
                        <?php if (isset($customer_data['points'])): ?>
                            <div class="points-display">
                                <i class="bi bi-star-fill"></i> Points: <?= number_format($customer_data['points']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="no-prize-message">                        
                            <div class="d-grid gap-2 mt-2">
                                <a href="<?= base_url('customer/dashboard') ?>" class="btn btn-platform">
                                    <i class="bi bi-speedometer2"></i> View Dashboard
                                </a>
                                <button type="button" class="btn btn-platform" onclick="logout()">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </div>
                    </div>

                <?php else: ?>
                    <!-- Not Logged In - Show Login Options -->
                    <div id="authContainer">
                        <div class="no-prize-message">
                            <h3><i class="bi bi-person-circle"></i> Account Access</h3>
                            <p>Login to access your dashboard or create a new account.</p>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="button" class="btn btn-claim" onclick="loginWithWinnerData()">
                                    <i class="bi bi-box-arrow-in-right"></i> Login to Account
                                </button>
                                <button type="button" class="btn btn-platform" onclick="autoRegister()">
                                    <i class="bi bi-person-plus-fill"></i> Create New Account
                                </button>
                            </div>
                        </div>
                        
                        <!-- Account Created Step (Hidden Initially) -->
                        <div id="accountCreatedStep" style="display: none;">
                            <h5 class="text-center text-success mb-3">
                                <i class="bi bi-check-circle-fill"></i> Account Created!
                            </h5>
                            
                            <div class="credentials-box">
                                <h6 class="text-warning mb-3">Save Your Login Details:</h6>
                                <div class="credential-item">
                                    <div class="credential-label">Username:</div>
                                    <div id="displayUsername">-</div>
                                </div>
                                <div class="credential-item">
                                    <div class="credential-label">Password:</div>
                                    <div id="displayPassword">-</div>
                                </div>
                            </div>
                            
                            <div class="important-note">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                Important: Save these credentials to access your account later!
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <a href="<?= base_url('customer') ?>" class="btn btn-claim">
                                    <i class="bi bi-box-arrow-in-right"></i> Go to Login Page
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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


        function autoRegister() {
            showLoading(true);

            const formData = new FormData();
            formData.append(csrfName, csrfToken);

            fetch('<?= base_url('reward/auto-register') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);

                if (data.success && data.customer_data) {
                    // Hide registration options and show credentials step
                    document.getElementById('registrationOptions').style.display = 'none';
                    document.getElementById('accountCreatedStep').style.display = 'block';

                    // Populate credentials
                    document.getElementById('displayUsername').textContent = data.customer_data.username;
                    document.getElementById('displayPassword').textContent = data.customer_data.password;

                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message || 'Registration failed. Please try again.', 'danger');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                showLoading(false);
                showAlert('Registration failed. Please try again.', 'danger');
            });
        }

        // Claim reward function
        function claimReward(platform) {
            showLoading(true);
            
            const formData = new FormData();
            formData.append('platform', platform);
            formData.append(csrfName, csrfToken);
            
            fetch('<?= base_url('reward/claim-reward') ?>', {
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
                    
                    // Redirect to platform after short delay
                    setTimeout(() => {
                        window.open(data.redirect_url, '_blank');
                        // Redirect to home page after opening platform
                        setTimeout(() => {
                            window.location.href = '<?= base_url('/customer/dashboard') ?>';
                        }, 2000);
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Claim failed. Please try again.', 'danger');
                console.error('Error:', error);
            });
        }

        // Login function with winner data preservation
        function loginWithWinnerData() {
            <?php if (isset($winner_data) && !empty($winner_data)): ?>
            const winnerStorageId = <?= session()->get('winner_storage_id') ?? 'null' ?>;
            if (winnerStorageId) {
                const redirectUrl = '<?= base_url('customer') ?>?redirect=' + encodeURIComponent('<?= base_url('reward') ?>?winner_storage_id=' + winnerStorageId);
                window.location.href = redirectUrl;
            } else {
                // Fallback to URL encoding if no storage ID
                const winnerData = <?= json_encode($winner_data) ?>;
                const redirectUrl = '<?= base_url('customer') ?>?redirect=' + encodeURIComponent('<?= base_url('reward') ?>?winner_data=' + encodeURIComponent(JSON.stringify(winnerData)));
                window.location.href = redirectUrl;
            }
            <?php else: ?>
            window.location.href = '<?= base_url('customer') ?>?redirect=' + encodeURIComponent('<?= base_url('reward') ?>');
            <?php endif; ?>
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                showLoading(true);
                
                const formData = new FormData();
                formData.append(csrfName, csrfToken);
                
                fetch('<?= base_url('reward/logout') ?>', {
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
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('Logout failed. Please try again.', 'danger');
                    }
                })
                .catch(error => {
                    showLoading(false);
                    showAlert('Logout failed. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            }
        }

        // Copy to clipboard function for credentials
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert('Copied to clipboard!', 'info');
            }).catch(() => {
                showAlert('Could not copy to clipboard', 'warning');
            });
        }

        // Add click-to-copy functionality to credentials
        document.addEventListener('DOMContentLoaded', function() {
            const credentialElements = document.querySelectorAll('#displayUsername, #displayPassword');
            credentialElements.forEach(element => {
                element.style.cursor = 'pointer';
                element.title = 'Click to copy';
                element.addEventListener('click', function() {
                    copyToClipboard(this.textContent);
                });
            });
        });


        
    </script>
</body>
</html>