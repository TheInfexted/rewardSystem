<?= $this->extend('layouts/customer_layout') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/customer-dashboard.css') ?>">

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
                <h4 class="username"><?= esc($username) ?></h4>
                <p class="user-subtitle">Premium Member</p>
                <p class="spin-tokens">
                    <i class="bi bi-coin"></i> 
                    <span id="spinTokensCount"><?= $spin_tokens ?></span> Spin Tokens
                </p>
            </div>
            
            <div class="header-actions">
                <button class="btn btn-wallet" onclick="openDepositModal('spin')">
                    <i class="bi bi-wallet2 me-1"></i>
                    Spin Wallet
                </button>
                <button class="btn btn-wallet" onclick="openDepositModal('user')">
                    <i class="bi bi-cash-coin me-1"></i>
                    User Wallet
                </button>
                <button class="btn-wheel" onclick="openWheelModal()" style="width: auto; padding: 0 15px; border-radius: 25px;">
                    <i class="bi bi-bullseye me-1"></i>
                </button>
            </div>
        </div>
        
        <!-- Background Upload Options -->
        <div class="bg-upload-container">
            <input type="file" 
                id="backgroundImageInput" 
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                onchange="uploadBackgroundImage(this)">
            
            <button class="btn-upload-bg" onclick="document.getElementById('backgroundImageInput').click()">
                <i class="bi bi-camera"></i> Change Background
            </button>
            
            <?php if (!empty($profile_background_image)): ?>
                <button class="btn-remove-bg" onclick="removeBackgroundImage()">
                    <i class="bi bi-trash"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>

        <!-- Color Picker Panel -->
    <div class="color-picker-panel" id="colorPickerPanel">
        <div class="panel-header">
            <h5>Dashboard Background</h5>
            <button class="close-btn" onclick="closeColorPicker()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        
        <div class="preset-colors">
            <div class="color-swatch" style="background: #FFFFFF" onclick="setDashboardColor('#FFFFFF')"></div>
            <div class="color-swatch" style="background: #f8f9fa" onclick="setDashboardColor('#f8f9fa')"></div>
            <div class="color-swatch" style="background: #e9ecef" onclick="setDashboardColor('#e9ecef')"></div>
            <div class="color-swatch" style="background: #212529" onclick="setDashboardColor('#212529')"></div>
            <div class="color-swatch" style="background: #e3f2fd" onclick="setDashboardColor('#e3f2fd')"></div>
            <div class="color-swatch" style="background: #f3e5f5" onclick="setDashboardColor('#f3e5f5')"></div>
            <div class="color-swatch" style="background: #e8f5e9" onclick="setDashboardColor('#e8f5e9')"></div>
            <div class="color-swatch" style="background: #fff3e0" onclick="setDashboardColor('#fff3e0')"></div>
        </div>
        
        <div class="custom-color">
            <label>Custom Color:</label>
            <input type="color" id="customColorInput" value="#ffffff">
            <button class="apply-btn" onclick="applyCustomColor()">Apply</button>
        </div>
    </div>

    <!-- Weekly Check-in Section for Dashboard -->
    <div class="checkin-section">
        <h5 class="section-title">
            <i class="bi bi-calendar-check text-primary me-2"></i>
            Weekly Check-in (<?= date('M j', strtotime($current_week_start)) ?> - <?= date('M j', strtotime($current_week_end)) ?>)
        </h5>
        
        <div class="checkin-card">
            <div class="checkin-streak"><?= $checkin_streak ?>/7</div>
            <div class="streak-text">Days Completed</div>
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
        
        <!-- Weekly Check-in Calendar -->
        <div class="weekly-calendar">
            <div class="week-header">
                <span>This Week's Progress</span>
                <small>Monday resets weekly progress</small>
            </div>
            
            <div class="week-days">
                <?php if (is_array($weekly_progress)): ?>
                    <?php foreach ($weekly_progress as $day => $dayData): ?>
                        <div class="day-item <?= $dayData['checked_in'] ? 'completed' : '' ?> 
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
            
            <div class="week-summary">
                <div class="summary-item">
                    <span class="label">This Week:</span>
                    <span class="value"><?= $checkin_streak ?>/7 days</span>
                </div>
                <div class="summary-item">
                    <span class="label">Next Reset:</span>
                    <span class="value">
                        <?php 
                        $nextMonday = date('M j', strtotime('next monday'));
                        echo $nextMonday;
                        ?>
                    </span>
                </div>
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
    
    <!-- Advertisement Section -->
    <div class="advertisement-section">
        <div id="adsContainer"> 
            <?php if (!empty($ads)): ?>
                <div class="ads-stack">
                    <?php foreach ($ads as $ad): ?>
                        <?php 
                        $mediaUrl = $ad['media_file'] 
                            ? base_url('uploads/reward_ads/' . $ad['media_file'])
                            : $ad['media_url'];
                        ?>
                        <div class="ad-item-stacked <?= $ad['ad_type'] === 'video' ? 'video-container' : '' ?>" 
                            data-id="<?= $ad['id'] ?>"
                            <?= $ad['click_url'] ? 'data-url="' . $ad['click_url'] . '"' : '' ?>>
                            <?php if ($ad['ad_type'] === 'video'): ?>
                                <video class="ad-media-stacked" autoplay muted loop playsinline>
                                    <source src="<?= $mediaUrl ?>" type="video/mp4">
                                </video>
                            <?php else: ?>
                                <img class="ad-media-stacked" src="<?= $mediaUrl ?>" alt="<?= esc($ad['ad_title'] ?? 'Advertisement') ?>">
                            <?php endif; ?>
                            
                            <?php if (!empty($ad['ad_title']) || !empty($ad['ad_description'])): ?>
                                <div class="ad-overlay-content">
                                    <?php if (!empty($ad['ad_title'])): ?>
                                        <h4 class="ad-overlay-title"><?= esc($ad['ad_title']) ?></h4>
                                    <?php endif; ?>
                                    <?php if (!empty($ad['ad_description'])): ?>
                                        <p class="ad-overlay-description"><?= esc($ad['ad_description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="no-ads">
                    <i class="bi bi-image"></i>
                    <p>Check back later for special offers!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dashboard Background Color Picker -->
    <div class="color-fab" id="colorFab">
        <button class="fab-button" onclick="toggleColorPicker()" title="Change Dashboard Color">
            <i class="bi bi-palette-fill"></i>
        </button>
    </div>

    <!-- Deposit Modal -->
    <div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="depositModalTitle">Contact Customer Service</h5>
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
<script src="<?= base_url('js/dashboard/dashboard-color-picker.js') ?>"></script>   

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
</script>

<script>
// Simple color picker initialization
document.addEventListener('DOMContentLoaded', function() {
    // Apply saved color if available
    if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.dashboardBgColor) {
        document.body.style.backgroundColor = dashboardPhpConfig.dashboardBgColor;
        const container = document.querySelector('.dashboard-container');
        if (container) {
            container.style.backgroundColor = dashboardPhpConfig.dashboardBgColor;
        }
    }
});

// Global functions for color picker
function toggleColorPicker() {
    const panel = document.getElementById('colorPickerPanel');
    panel.classList.toggle('active');
}

function closeColorPicker() {
    document.getElementById('colorPickerPanel').classList.remove('active');
}

function setDashboardColor(color) {
    // Apply color
    document.body.style.backgroundColor = color;
    const container = document.querySelector('.dashboard-container');
    if (container) {
        container.style.backgroundColor = color;
    }
    
    // Save to database
    const formData = new FormData();
    formData.append('color', color);
    formData.append(dashboardPhpConfig.csrfName, dashboardPhpConfig.csrfToken);
    
    fetch(dashboardPhpConfig.baseUrl + 'customer/updateDashboardColor', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    });
    
    closeColorPicker();
}

function applyCustomColor() {
    const color = document.getElementById('customColorInput').value;
    setDashboardColor(color);
}
</script>

<script>
// Store the selected wallet type
let selectedWalletType = '';

// Open deposit modal
function openDepositModal(walletType) {
    selectedWalletType = walletType;
    const modal = new bootstrap.Modal(document.getElementById('depositModal'));
    
    // Update modal title based on wallet type
    const modalTitle = document.getElementById('depositModalTitle');
    modalTitle.textContent = `Deposit to ${walletType === 'spin' ? 'Spin Wallet' : 'User Wallet'}`;
    
    modal.show();
}

// Contact customer service
function contactCustomerService(platform) {
    // Prepare the message based on wallet type
    const walletName = selectedWalletType === 'spin' ? 'Spin Wallet' : 'User Wallet';
    const message = `Hi, I want to deposit into my ${walletName}. My User ID is: ${dashboardPhpConfig.username}`;
    const encodedMessage = encodeURIComponent(message);
    
    // Get contact details from config or use defaults
    const whatsappNumber = dashboardPhpConfig.whatsappNumber || '60102763672';
    const telegramUsername = dashboardPhpConfig.telegramUsername || 'brendxn1127';
    
    // Create platform URLs
    let redirectUrl = '';
    if (platform === 'whatsapp') {
        redirectUrl = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
    } else {
        redirectUrl = `https://t.me/${telegramUsername}?text=${encodedMessage}`;
    }
    
    // Show loading/success message
    if (typeof DashboardUtils !== 'undefined' && DashboardUtils.showToast) {
        DashboardUtils.showToast(`Redirecting to ${platform === 'whatsapp' ? 'WhatsApp' : 'Telegram'}...`, 'info');
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('depositModal'));
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