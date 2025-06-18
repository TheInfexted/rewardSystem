<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Your Reward - Reward System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .reward-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reward-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        
        .prize-display {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            color: #fff;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .prize-icon {
            font-size: 3rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .login-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
        }
        
        .btn-reward {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-reward:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .btn-secondary-reward {
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-secondary-reward:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
            color: white;
        }
        
        .account-details {
            background: #e7f3ff;
            border: 2px solid #0066cc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .account-details h5 {
            color: #0066cc;
            margin-bottom: 15px;
        }
        
        .credential-item {
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        
        .credential-label {
            font-weight: bold;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            color: #007bff;
            word-break: break-all;
        }
        
        .loading-spinner {
            display: none;
        }
        
        .fade-out {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        .fade-in {
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        
        .success-animation {
            animation: bounce 0.6s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 20%, 60%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            80% { transform: translateY(-5px); }
        }
        
        .celebration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }
        
        @media (max-width: 768px) {
            .reward-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .prize-display {
                font-size: 1.2rem;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="reward-container">
        <div class="reward-card">
            <!-- Prize Display -->
            <?php if (isset($winner_data) && !empty($winner_data)): ?>
                <div class="prize-display">
                    <i class="bi bi-trophy-fill prize-icon"></i>
                    <div>🎉 CONGRATULATIONS! 🎉</div>
                    <div>You Won: <?= htmlspecialchars($winner_data['name'] ?? 'Unknown Prize') ?></div>
                    <?php if (isset($winner_data['prize']) && $winner_data['prize'] > 0): ?>
                        <div class="mt-2">Value: <?= ($winner_data['type'] ?? '') === 'cash' ? '¥' : '' ?><?= number_format($winner_data['prize'], 2) ?></div>
                    <?php endif; ?>
            
            <!-- Account Details Section (Hidden initially) -->
            <div class="account-details" id="accountDetails" style="display: none;">
                <h5><i class="bi bi-check-circle-fill text-success"></i> Account Created Successfully!</h5>
                <p class="text-muted mb-3">Please save these details for future reference:</p>
                
                <div class="credential-item">
                    <div class="credential-label">Username:</div>
                    <div class="credential-value" id="newUsername"></div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">Password:</div>
                    <div class="credential-value" id="newPassword"></div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Important:</strong> Please save these credentials as you'll need them for future logins.
                </div>
                
                <div class="d-grid mt-3">
                    <button type="button" class="btn btn-reward btn-lg" onclick="proceedToLogin()">
                        <i class="bi bi-arrow-right-circle-fill"></i> Continue to Claim Reward
                    </button>
                </div>
            </div>
                </div>
            <?php else: ?>
                <div class="prize-display">
                    <i class="bi bi-exclamation-triangle prize-icon"></i>
                    <div>⚠️ NO PRIZE DATA FOUND</div>
                    <div>Please spin the wheel first to win a prize</div>
                </div>
            <?php endif; ?>
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <?php if (empty($winner_data)): ?>
                <!-- No Prize Data Section -->
                <div class="login-section">
                    <h4 class="mb-4 text-warning">
                        <i class="bi bi-exclamation-triangle"></i> No Prize Data Found
                    </h4>
                    <p class="text-muted mb-4">You need to spin the wheel and win a prize first before accessing this page.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-warning btn-lg">
                            <i class="bi bi-arrow-left"></i> Go Back to Game
                        </a>
                    </div>
                    
                    <?php if (!$logged_in): ?>
                    <div class="mt-4">
                        <h6 class="text-muted">Want to create an account for future use?</h6>
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-secondary-reward" onclick="autoRegister()">
                                <i class="bi bi-person-plus-fill"></i> Create Account
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
            <?php elseif (!$logged_in): ?>
                <!-- Login/Register Section (Has Prize Data) -->
                <div class="login-section" id="loginSection">
                    <h4 class="mb-4"><i class="bi bi-person-circle"></i> Access Your Reward</h4>
                    <p class="text-muted mb-4">To claim your prize, please login or create a new account</p>
                    
                    <!-- Auto Register Button -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-reward btn-lg" onclick="autoRegister()">
                            <i class="bi bi-person-plus-fill"></i> Create New Account
                        </button>
                    </div>
                    
                    <div class="text-center mb-3">
                        <span class="text-muted">- OR -</span>
                    </div>
                    
                    <!-- Manual Login Form -->
                    <form id="manualLoginForm" onsubmit="manualLogin(event)">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-secondary-reward">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Already Logged In with Prize Data -->
                <div class="login-section">
                    <h4 class="mb-3 text-success">
                        <i class="bi bi-check-circle-fill"></i> Welcome, <?= htmlspecialchars($user_data['name'] ?? 'User') ?>!
                    </h4>
                    <p class="text-muted mb-4">You are already logged in. Click below to claim your reward.</p>
                    
                    <div class="d-grid">
                        <button type="button" class="btn btn-reward btn-lg" onclick="claimReward()">
                            <i class="bi bi-gift-fill"></i> Claim My Reward
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Loading Section (Hidden initially) -->
            <div class="text-center loading-spinner" id="loadingSection" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Processing your request...</p>
            </div>
            
            <!-- Back to Game -->
            <div class="mt-4">
                <a href="<?= base_url('user/dashboard') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Game
                </a>
            </div>
        </div>
    </div>
    
    <!-- Celebration Canvas -->
    <div class="celebration" id="celebration"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let generatedCredentials = null;
        
        // Auto register new account
        async function autoRegister() {
            showLoading(true);
            
            try {
                const response = await fetch('<?= base_url('reward/auto-register') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    generatedCredentials = data.account_details;
                    showAccountDetails(data.account_details);
                    showAlert('Account created successfully!', 'success');
                } else {
                    showAlert(data.message || 'Failed to create account', 'danger');
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Proceed to auto-login with generated credentials
        async function proceedToLogin() {
            if (!generatedCredentials) {
                showAlert('No account credentials found', 'danger');
                return;
            }
            
            showLoading(true);
            
            try {
                const formData = new FormData();
                formData.append('username', generatedCredentials.username);
                formData.append('password', generatedCredentials.password);
                
                const response = await fetch('<?= base_url('reward/auto-login') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Login successful! Processing your reward...', 'success');
                    
                    // Show success animation
                    document.querySelector('.reward-card').classList.add('success-animation');
                    triggerCelebration();
                    
                    // Handle claim result
                    if (data.claim_result && data.claim_result.success) {
                        setTimeout(() => {
                            showAlert('Reward claimed successfully! Redirecting...', 'success');
                            if (data.claim_result.redirect_url) {
                                setTimeout(() => {
                                    window.location.href = data.claim_result.redirect_url;
                                }, 2000);
                            }
                        }, 1500);
                    } else if (data.claim_result && data.claim_result.redirect_to_game) {
                        // No prize data case - redirect to game
                        setTimeout(() => {
                            showAlert('No prize found. Redirecting to game...', 'warning');
                            setTimeout(() => {
                                window.location.href = '<?= base_url('/') ?>';
                            }, 2000);
                        }, 1500);
                    } else {
                        showAlert(data.claim_result?.message || 'Failed to claim reward', 'warning');
                    }
                } else {
                    showAlert(data.message || 'Login failed', 'danger');
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Manual login
        async function manualLogin(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            showLoading(true);
            
            try {
                const response = await fetch('<?= base_url('reward/login') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Login successful! Processing your reward...', 'success');
                    
                    // Show success animation
                    document.querySelector('.reward-card').classList.add('success-animation');
                    triggerCelebration();
                    
                    // Handle claim result
                    if (data.claim_result && data.claim_result.success) {
                        setTimeout(() => {
                            showAlert('Reward claimed successfully! Redirecting...', 'success');
                            if (data.claim_result.redirect_url) {
                                setTimeout(() => {
                                    window.location.href = data.claim_result.redirect_url;
                                }, 2000);
                            }
                        }, 1500);
                    } else if (data.claim_result && data.claim_result.redirect_to_game) {
                        // No prize data case - redirect to game
                        setTimeout(() => {
                            showAlert('No prize found. Redirecting to game...', 'warning');
                            setTimeout(() => {
                                window.location.href = '<?= base_url('/') ?>';
                            }, 2000);
                        }, 1500);
                    } else {
                        showAlert(data.claim_result?.message || 'Failed to claim reward', 'warning');
                    }
                } else {
                    showAlert(data.message || 'Login failed', 'danger');
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Show account details after registration
        function showAccountDetails(accountDetails) {
            document.getElementById('newUsername').textContent = accountDetails.username;
            document.getElementById('newPassword').textContent = accountDetails.password;
            
            // Hide login section and show account details
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('accountDetails').style.display = 'block';
        }
        
        // Show/hide loading state (improved version)
        function showLoading(show) {
            console.log('showLoading called with:', show);
            
            let loadingSection = document.getElementById('loadingSection');
            
            // If loading section doesn't exist, create it
            if (!loadingSection) {
                loadingSection = document.createElement('div');
                loadingSection.id = 'loadingSection';
                loadingSection.className = 'text-center loading-spinner';
                loadingSection.style.display = 'none';
                loadingSection.innerHTML = `
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Processing your request...</p>
                `;
                
                // Insert it before the alert container
                const alertContainer = document.getElementById('alertContainer');
                if (alertContainer) {
                    alertContainer.parentNode.insertBefore(loadingSection, alertContainer.nextSibling);
                } else {
                    // Fallback: add to reward card
                    const rewardCard = document.querySelector('.reward-card');
                    if (rewardCard) {
                        rewardCard.appendChild(loadingSection);
                    }
                }
            }
            
            if (show) {
                loadingSection.style.display = 'block';
                
                // Hide other sections if they exist
                const otherSections = document.querySelectorAll('.login-section');
                otherSections.forEach(section => {
                    section.style.display = 'none';
                });
                
                const accountDetails = document.getElementById('accountDetails');
                if (accountDetails) {
                    accountDetails.style.display = 'none';
                }
            } else {
                loadingSection.style.display = 'none';
                
                // Show other sections
                const otherSections = document.querySelectorAll('.login-section');
                otherSections.forEach(section => {
                    section.style.display = 'block';
                });
                
                // Show account details if credentials were generated
                if (generatedCredentials) {
                    const accountDetails = document.getElementById('accountDetails');
                    if (accountDetails) {
                        accountDetails.style.display = 'block';
                    }
                }
            }
        }
        
        // Show alert messages
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" id="${alertId}" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.innerHTML = alertHtml;
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    alertElement.remove();
                }
            }, 5000);
        }
        
        // Trigger celebration animation
        function triggerCelebration() {
            const celebration = document.getElementById('celebration');
            celebration.innerHTML = `
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 4rem; animation: bounce 1s infinite;">
                    🎉🎊🎁
                </div>
            `;
            
            setTimeout(() => {
                celebration.innerHTML = '';
            }, 3000);
        }
        
        // For already logged in users
        async function claimReward() {
            console.log('claimReward() called');
            
            showLoading(true);
            
            try {
                console.log('Making claim request...');
                
                // Make AJAX call to process the claim
                const response = await fetch('<?= base_url('reward/claim') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'claim_reward'
                    })
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const data = await response.json();
                console.log('Full claim response:', data);
                
                if (data.success) {
                    showAlert('Reward claimed successfully! Redirecting...', 'success');
                    triggerCelebration();
                    
                    setTimeout(() => {
                        if (data.redirect_url) {
                            console.log('Redirecting to:', data.redirect_url);
                            window.location.href = data.redirect_url;
                        } else {
                            console.log('No redirect URL, going to home');
                            window.location.href = '<?= base_url('/') ?>';
                        }
                    }, 2000);
                } else if (data.redirect_to_game) {
                    showAlert('No prize found. Redirecting to game...', 'warning');
                    setTimeout(() => {
                        window.location.href = '<?= base_url('/') ?>';
                    }, 2000);
                } else {
                    // Show detailed error information
                    let errorMessage = data.message || 'Failed to claim reward';
                    if (data.debug_info) {
                        console.error('Debug info from server:', data.debug_info);
                        errorMessage += ' (Check console for details)';
                    }
                    if (data.debug) {
                        console.error('Additional debug info:', data.debug);
                    }
                    showAlert(errorMessage, 'danger');
                }
                
            } catch (error) {
                console.error('Claim error:', error);
                showAlert('Network error: ' + error.message, 'danger');
            } finally {
                showLoading(false);
            }
        }
    </script>
</body>
</html>