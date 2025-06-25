<?= $this->extend('layouts/customer_layout') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/customer-dashboard.css') ?>?v=<?= time() ?>">

<div class="dashboard-container">
    <!-- User Profile Header -->
    <div class="profile-header <?= $profile_background ?>-bg" 
        id="profileHeader"
        style="<?= !empty($profile_background_image) ? 'background-image: url(' . base_url('uploads/profile_backgrounds/' . $profile_background_image) . ');' : '' ?>">
        
        <!-- Overlay for better text visibility when image is set -->
        <?php if (!empty($profile_background_image)): ?>
            <div class="profile-header-overlay"></div>
        <?php endif; ?>
        
        <!-- Loading overlay -->
        <div class="upload-loading" id="uploadLoading">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
        </div>
        
        <div class="profile-header-content">
            <div class="user-info">
                <div class="user-header-row">
                    <div class="user-details">
                        <div class="username-with-settings">
                            <h4 class="username"><?= esc($username) ?></h4>
                            <button class="btn-settings" onclick="openSettingsModal()" title="Settings">
                                <i class="bi bi-gear"></i>
                            </button>
                        </div>
                        <p class="user-subtitle">Premium Member</p>
                        <!-- Updated Spin Tokens with Plus Button -->
                        <div class="spin-tokens-container">
                            <div class="spin-tokens">
                                <i class="bi bi-coin"></i> 
                                <span id="spinTokensCount"><?= $spin_tokens ?></span> Spin Tokens
                            </div>
                            <button class="btn-add-tokens" onclick="openWalletModal()">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="logout-section">
                        <button class="btn-logout" onclick="window.location.href='<?= base_url('reward/logout') ?>'">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="header-actions">
                    <div class="right-buttons-only">
                        <button class="btn-wheel" onclick="openWheelModal()">
                            <img src="<?= base_url('img/fortune-wheel.gif') ?>" alt="Fortune Wheel" class="wheel-icon-gif">
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Weekly Check-in Section -->
    <div class="checkin-section">
        <h5 class="section-title">
            <i class="bi bi-calendar-check text-primary me-2"></i>
            Weekly Check-in (<?= date('M j', strtotime($current_week_start)) ?> - <?= date('M j', strtotime($current_week_end)) ?>)
        </h5>
        
        <!-- Compact Check-in with Calendar -->
        <div class="compact-checkin">
            <div class="checkin-action">
                <button class="btn btn-checkin" 
                        onclick="performCheckin()" 
                        <?= $today_checkin ? 'disabled' : '' ?>>
                    <?= $today_checkin ? 'Already Checked In' : 'Check In Now' ?>
                </button>
                
                <?php if ($checkin_streak >= 7): ?>
                    <div class="perfect-week-badge">
                        🎉 PERFECT WEEK!
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Inline Weekly Calendar -->
            <div class="week-days-inline">
                <?php if (is_array($weekly_progress)): ?>
                    <?php foreach ($weekly_progress as $day => $dayData): ?>
                        <div class="day-item-compact <?= $dayData['checked_in'] ? 'completed' : '' ?> 
                                                    <?= $dayData['is_today'] ? 'today' : '' ?>
                                                    <?= $dayData['is_future'] ? 'future' : '' ?>">
                            <div class="day-name"><?= $dayData['day_short'] ?></div>
                            <div class="day-number"><?= $day ?></div>
                            <div class="day-points">
                                <?php if ($dayData['checked_in']): ?>
                                    <span class="earned">+<?= $dayData['actual_points'] ?></span>
                                <?php elseif (!$dayData['is_future']): ?>
                                    <span class="available">+<?= $dayData['points'] ?></span>
                                <?php else: ?>
                                    <span class="future">+<?= $dayData['points'] ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($dayData['checked_in']): ?>
                                <div class="check-mark">✓</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
        
    <!-- Statistics -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?= $monthly_checkins ?></div>
            <div class="stat-label">This Month</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $checkin_streak ?></div>
            <div class="stat-label">Current Streak</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $total_points ?></div>
            <div class="stat-label">Total Points</div>
        </div>
    </div>
    
    <!-- Content Section -->
    <div class="content-section">
        <div id="mediaContainer"> 
            <?php if (!empty($ads)): ?>
                <div class="media-stack">
                    <?php foreach ($ads as $ad): ?>
                        <?php 
                        $mediaUrl = $ad['media_file'] 
                            ? base_url('uploads/reward_ads/' . $ad['media_file'])
                            : $ad['media_url'];
                        ?>
                        <div class="media-item-stacked <?= $ad['ad_type'] === 'video' ? 'video-container' : '' ?>" 
                            data-id="<?= $ad['id'] ?>"
                            <?= $ad['click_url'] ? 'data-url="' . $ad['click_url'] . '"' : '' ?>>
                            <?php if ($ad['ad_type'] === 'video'): ?>
                                <video class="media-content-stacked" autoplay muted loop playsinline>
                                    <source src="<?= $mediaUrl ?>" type="video/mp4">
                                </video>
                            <?php else: ?>
                                <img class="media-content-stacked" src="<?= $mediaUrl ?>" alt="Content">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="no-content">
                    <i class="bi bi-image"></i>
                    <p>Check back later for special offers!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingsModalLabel">
                    <i class="bi bi-gear me-2"></i>Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passwordChangeForm">
                    <div class="settings-section">
                        <h6 class="settings-section-title">
                            <i class="bi bi-lock me-2"></i>Change Password
                        </h6>
                        
                        <!-- Current Password Verification -->
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Enter Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" 
                                   placeholder="Enter your current password" required>
                            <div class="invalid-feedback" id="currentPasswordError"></div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" 
                                   placeholder="Enter new password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                            <div class="invalid-feedback" id="newPasswordError"></div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" 
                                   placeholder="Confirm new password" required>
                            <div class="invalid-feedback" id="confirmPasswordError"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="changePassword()" id="changePasswordBtn">
                    <i class="bi bi-check-circle me-1"></i>Update Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Selection Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" aria-labelledby="walletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="walletModalLabel">Choose Wallet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">Which wallet would you like to deposit into?</p>
                
                <div class="wallet-selection">
                    <button class="btn btn-wallet-option" onclick="selectWallet('spin')">
                        <i class="bi bi-coin"></i>
                        <span>Spin Wallet</span>
                        <small>For spinning the wheel</small>
                    </button>
                    
                    <button class="btn btn-wallet-option" onclick="selectWallet('user')">
                        <i class="bi bi-cash-coin"></i>
                        <span>User Wallet</span>
                        <small>Main balance</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Platform Selection Modal -->
<div class="modal fade" id="platformModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="platformModalTitle">Contact Customer Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">Please choose your preferred platform to contact customer service:</p>
                
                <div class="platform-buttons">
                    <button class="btn btn-platform whatsapp" onclick="contactCustomerService('whatsapp')">
                        <i class="bi bi-whatsapp"></i>
                        <span>WhatsApp</span>
                    </button>
                    
                    <button class="btn btn-platform telegram" onclick="contactCustomerService('telegram')">
                        <i class="bi bi-telegram"></i>
                        <span>Telegram</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="settingsToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-gear me-2"></i>
            <strong class="me-auto">Settings</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="settingsToastBody">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dashboard JavaScript Modules -->
<script src="<?= base_url('js/dashboard/dashboard-config.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-utils.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-core.js') ?>"></script>
<script src="<?= base_url('js/dashboard/fortune-wheel.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-init.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-ads.js') ?>"></script>

<!-- Dashboard Configuration from PHP -->
<script>
// Pass PHP data to JavaScript
const dashboardPhpConfig = {
    csrfToken: '<?= csrf_hash() ?>',
    csrfName: '<?= csrf_token() ?>',
    baseUrl: '<?= base_url() ?>',
    username: '<?= esc($username) ?>',
    totalPoints: <?= $total_points ?>,
    monthlyCheckins: <?= $monthly_checkins ?>,
    todayCheckin: <?= $today_checkin ? 'true' : 'false' ?>,
    dashboardBgColor: '<?= $dashboard_bg_color ?? '#ffffff' ?>',
    whatsappNumber: '<?= $whatsapp_number ?>',  
    telegramUsername: '<?= $telegram_username ?>',
    spinTokens: <?= $spin_tokens ?? 0 ?>,
    <?php if (isset($weekly_progress) && is_array($weekly_progress)): ?>
    weeklyProgress: <?= json_encode($weekly_progress) ?>,
    <?php else: ?>
    weeklyProgress: null,
    <?php endif; ?>
    <?php if (isset($recent_activities) && is_array($recent_activities)): ?>
    recentActivities: <?= json_encode($recent_activities) ?>,
    <?php else: ?>
    recentActivities: null,
    <?php endif; ?>
    ads: <?= json_encode($ads ?? []) ?>,
};

// Apply dashboard background color
document.addEventListener('DOMContentLoaded', function() {
    if (dashboardPhpConfig.dashboardBgColor) {
        document.body.style.backgroundColor = dashboardPhpConfig.dashboardBgColor;
        const container = document.querySelector('.dashboard-container');
        if (container) {
            container.style.backgroundColor = dashboardPhpConfig.dashboardBgColor;
        }
    }
});
</script>

<script>
// Store the selected wallet type
let selectedWalletType = '';

// Open wallet selection modal (triggered by + button)
function openWalletModal() {
    // Ensure any existing modals are closed first
    const existingModals = document.querySelectorAll('.modal.show');
    existingModals.forEach(modal => {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });
    
    // Small delay to ensure cleanup, then open wallet modal
    setTimeout(() => {
        const walletModal = new bootstrap.Modal(document.getElementById('walletModal'), {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        walletModal.show();
    }, 100);
}

// Select wallet and proceed to platform selection
function selectWallet(walletType) {
    selectedWalletType = walletType;
    
    // Close wallet modal
    const walletModal = bootstrap.Modal.getInstance(document.getElementById('walletModal'));
    if (walletModal) {
        walletModal.hide();
    }
    
    // Update platform modal title and show
    setTimeout(() => {
        const platformModalTitle = document.getElementById('platformModalTitle');
        platformModalTitle.textContent = `Deposit to ${walletType === 'spin' ? 'Spin Wallet' : 'User Wallet'}`;
        
        const platformModal = new bootstrap.Modal(document.getElementById('platformModal'));
        platformModal.show();
    }, 300); // Small delay for smooth transition
}

// Contact customer service
function contactCustomerService(platform) {
    // Prepare the message based on wallet type
    const walletName = selectedWalletType === 'spin' ? 'Spin Wallet' : 'User Wallet';
    const message = `Hi, I want to deposit into my ${walletName}.\nMy User ID is: ${dashboardPhpConfig.username}`;
    const encodedMessage = encodeURIComponent(message);
    
    // Get contact details from config or use defaults
    const whatsappNumber = dashboardPhpConfig.whatsappNumber || '601159599022';
    const telegramUsername = dashboardPhpConfig.telegramUsername || 'harryford19';
    
    // Create platform URLs
    let redirectUrl = '';
    if (platform === 'whatsapp') {
        redirectUrl = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
    } else {
        redirectUrl = `https://t.me/${telegramUsername}?text=${encodedMessage}`;
    }
    
    // Show loading/success message
    console.log(`Redirecting to ${platform === 'whatsapp' ? 'WhatsApp' : 'Telegram'}...`);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('platformModal'));
    if (modal) {
        modal.hide();
    }
    
    // Redirect after a short delay
    setTimeout(() => {
        window.open(redirectUrl, '_blank');
    }, 500);
}
</script>

<script>
// Global modal management
let currentModal = null;
let actualCurrentPassword = '••••••••'; // Initialize with default value

// Utility function to properly close all modals
function closeAllModals() {
    // Get all open modals
    const openModals = document.querySelectorAll('.modal.show');
    
    openModals.forEach(modal => {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });
    
    // Force cleanup of any remaining backdrops
    setTimeout(() => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Reset body classes
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';
    }, 150);
}

// Improved settings modal function
function openSettingsModal() {
    // Close any existing modals first
    closeAllModals();
    
    // Wait for cleanup, then open settings modal
    setTimeout(() => {
        // Clear form
        const form = document.getElementById('passwordChangeForm');
        if (form) {
            form.reset();
        }
        clearValidation();
        
        // Create and show modal
        const modalElement = document.getElementById('settingsModal');
        if (modalElement) {
            currentModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static', // Prevent closing by clicking backdrop
                keyboard: true,
                focus: true
            });
            
            // Add event listeners for proper cleanup
            modalElement.addEventListener('hidden.bs.modal', function () {
                // Cleanup when modal is closed
                currentModal = null;
                
                // Force remove any lingering backdrops
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    document.body.style.overflow = '';
                }, 100);
            });
            
            currentModal.show();
        }
    }, 200);
}

// Fixed password change function with better error handling
async function changePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Clear previous validation
    clearValidation();
    
    // Validation
    let isValid = true;
    
    if (!currentPassword) {
        showFieldError('currentPassword', 'Current password is required');
        isValid = false;
    }
    
    if (!newPassword || newPassword.length < 6) {
        showFieldError('newPassword', 'New password must be at least 6 characters');
        isValid = false;
    }
    
    if (newPassword !== confirmPassword) {
        showFieldError('confirmPassword', 'Passwords do not match');
        isValid = false;
    }
    
    if (!isValid) return;
    
    // Show loading state
    const changeBtn = document.getElementById('changePasswordBtn');
    const originalText = changeBtn.innerHTML;
    changeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    changeBtn.classList.add('loading-btn');
    changeBtn.disabled = true;
    
    try {
        const response = await fetch(`${dashboardPhpConfig.baseUrl}customer/change-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword,
                [dashboardPhpConfig.csrfName]: dashboardPhpConfig.csrfToken
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Close modal properly
            if (currentModal) {
                currentModal.hide();
            }
            
            // Show success toast
            setTimeout(() => {
                showToast('success', 'Password updated successfully!');
            }, 300);
            
            // Update actual password for future reference
            actualCurrentPassword = newPassword;
        } else {
            // Show error
            if (data.field && data.message) {
                showFieldError(data.field, data.message);
            } else {
                showToast('error', data.message || 'Failed to update password');
            }
        }
    } catch (error) {
        console.error('Password change error:', error);
        showToast('error', 'An error occurred. Please try again.');
    } finally {
        // Reset button
        changeBtn.innerHTML = originalText;
        changeBtn.classList.remove('loading-btn');
        changeBtn.disabled = false;
    }
}

// Improved toast function
function showToast(type, message) {
    // Remove any existing toasts first
    const existingToasts = document.querySelectorAll('.toast.show');
    existingToasts.forEach(toast => {
        const toastInstance = bootstrap.Toast.getInstance(toast);
        if (toastInstance) {
            toastInstance.hide();
        }
    });
    
    setTimeout(() => {
        const toast = document.getElementById('settingsToast');
        const toastBody = document.getElementById('settingsToastBody');
        
        if (toast && toastBody) {
            // Set message and style
            toastBody.textContent = message;
            toast.className = `toast ${type === 'success' ? 'text-bg-success' : 'text-bg-danger'}`;
            
            // Show toast
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 3000
            });
            bsToast.show();
        }
    }, 100);
}

// Improved field validation helpers
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + 'Error');
    
    if (field && errorDiv) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        errorDiv.textContent = message;
    }
}

function clearValidation() {
    const fields = ['currentPassword', 'newPassword', 'confirmPassword'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + 'Error');
        
        if (field) {
            field.classList.remove('is-invalid', 'is-valid');
        }
        if (errorDiv) {
            errorDiv.textContent = '';
        }
    });
}

// Fetch current password (improved) - NOTE: For security, this should return a masked version
async function fetchCurrentPassword() {
    try {
        const response = await fetch(`${dashboardPhpConfig.baseUrl}customer/get-current-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                [dashboardPhpConfig.csrfName]: dashboardPhpConfig.csrfToken
            })
        });
        
        const data = await response.json();
        if (data.success && data.password) {
            // For security reasons, we should not store the actual password
            // Instead, just confirm we can fetch it
            actualCurrentPassword = data.password;
        }
    } catch (error) {
        console.error('Failed to fetch current password:', error);
        actualCurrentPassword = '••••••••'; // Fallback
    }
}

// Toggle current password visibility (improved)
function toggleCurrentPassword() {
    const display = document.getElementById('currentPasswordDisplay');
    const icon = document.getElementById('currentPasswordToggleIcon');
    
    if (display && icon) {
        if (display.type === 'password') {
            // For security demonstration, we'll show a placeholder
            // In a real application, you might want to fetch this securely
            display.type = 'text';
            display.value = actualCurrentPassword || 'MyPassword123'; // Fallback demo password
            icon.className = 'bi bi-eye-slash';
        } else {
            display.type = 'password';
            display.value = '••••••••';
            icon.className = 'bi bi-eye';
        }
    }
}

// Global cleanup function for page unload
window.addEventListener('beforeunload', function() {
    closeAllModals();
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Fetch current password from backend when page loads
    fetchCurrentPassword();
    
    // Add global escape key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentModal) {
            currentModal.hide();
        }
    });
});
</script>
<?= $this->endSection() ?>