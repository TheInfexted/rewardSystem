<?= $this->extend('layouts/customer_layout') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/customer-dashboard.css') ?>?v=<?= time() ?>">
<link rel="stylesheet" href="<?= base_url('css/dashboard-theme.css') ?>?v=<?= time() ?>">

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
                            <h4 class="username copyable-username" 
                                id="customerUsername" 
                                onclick="copyUsername()" 
                                title="Click to copy username">
                                <?= esc($username) ?>
                                <i class="bi bi-copy copy-icon" id="copyIcon"></i>
                            </h4>
                            <button class="btn-settings" onclick="openSettingsModal()" title="Settings">
                                <i class="bi bi-gear"></i>
                            </button>
                        </div>
                        <p class="user-subtitle">Premium Member</p>
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
                            <div class="day-number"><?= $dayData['day'] ?></div>
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

    <!-- Copy confirmation toast -->
    <div class="copy-toast" id="copyToast">
        <i class="bi bi-check-circle"></i>
        <span>Username copied to clipboard!</span>
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
        <h5 class="section-title ms-4" >
            <i class="bi bi-cash-stack text-primary me-2"></i>
            Sponsors
        </h5>
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

<!-- Only load essential dashboard modules -->
<script src="<?= base_url('js/dashboard/dashboard-config.js') ?>" onerror="console.log('dashboard-config.js not found')"></script>
<script src="<?= base_url('js/dashboard/dashboard-utils.js') ?>" onerror="console.log('dashboard-utils.js not found')"></script>
<script src="<?= base_url('js/dashboard/dashboard-core.js') ?>" onerror="console.log('dashboard-core.js not found')"></script>
<script src="<?= base_url('js/dashboard/dashboard-copy.js') ?>?v=<?= time() ?>" onerror="console.log('dashboard-copy.js not found')"></script>

<!-- Dashboard Configuration from PHP -->
<script>
// Debug: Check if we're on the right page
console.log('Loading customer dashboard');

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
    profileBackground: '<?= $profile_background ?? 'default' ?>', 
    // Dynamic customer service settings from admin_settings
    whatsappNumber: '<?= esc($whatsapp_number ?? '601159599022') ?>',  
    telegramUsername: '<?= esc($telegram_username ?? 'harryford19') ?>',
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

console.log('Dashboard config loaded:', dashboardPhpConfig);

// Apply dashboard background color and theme
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard theme
    initializeDashboardTheme();
    
    // Initialize wallet functionality
    initializeWalletModals();
});

function initializeDashboardTheme() {
    if (dashboardPhpConfig.dashboardBgColor && dashboardPhpConfig.dashboardBgColor !== '#ffffff') {
        // Apply background only to dashboard container, not body
        const dashboardContainer = document.querySelector('.dashboard-container');
        if (dashboardContainer) {
            dashboardContainer.style.backgroundColor = dashboardPhpConfig.dashboardBgColor;
            
            // Add themed class for better styling
            dashboardContainer.classList.add('custom-background');
            
            // Determine if background is dark for text contrast
            const luminance = getColorLuminance(dashboardPhpConfig.dashboardBgColor);
            if (luminance < 0.5) {
                dashboardContainer.classList.add('dark-bg');
            } else {
                dashboardContainer.classList.add('light-bg');
            }
        }
        
        // Apply theme to weekly check-in section
        const checkinSection = document.querySelector('.checkin-section');
        if (checkinSection) {
            checkinSection.classList.add('themed-section');
        }
        
        // Apply theme to stats section
        const statsRow = document.querySelector('.stats-row');
        if (statsRow) {
            statsRow.classList.add('themed-section');
        }
    }
}

function initializeWalletModals() {
    // Ensure modals are properly initialized
    const walletModal = document.getElementById('walletModal');
    const platformModal = document.getElementById('platformModal');
    
    if (walletModal) {
        console.log('Wallet modal found and ready');
    }
    
    if (platformModal) {
        console.log('Platform modal found and ready');
    }
}

// Helper function to calculate color luminance
function getColorLuminance(hexColor) {
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16) / 255;
    const g = parseInt(hex.substr(2, 2), 16) / 255;
    const b = parseInt(hex.substr(4, 2), 16) / 255;
    
    const a = [r, g, b].map(v => {
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    
    return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}

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

// Contact customer service with dynamic settings
function contactCustomerService(platform) {
    // Prepare the message based on wallet type
    const walletName = selectedWalletType === 'spin' ? 'Spin Wallet' : 'User Wallet';
    const message = `Hi, I want to deposit into my ${walletName}.\nMy User ID is: ${dashboardPhpConfig.username}`;
    const encodedMessage = encodeURIComponent(message);
    
    // Get contact details from dynamic config (loaded from admin_settings)
    const whatsappNumber = dashboardPhpConfig.whatsappNumber;
    const telegramUsername = dashboardPhpConfig.telegramUsername;
    
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
<?= $this->endSection() ?>