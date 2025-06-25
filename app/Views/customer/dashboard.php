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
                <div class="user-header-row">
                    <div class="user-details">
                        <h4 class="username"><?= esc($username) ?></h4>
                        <p class="user-subtitle">Premium Member</p>
                        <p class="spin-tokens">
                            <i class="bi bi-coin"></i> 
                            <span id="spinTokensCount"><?= $spin_tokens ?></span> Spin Tokens
                        </p>
                    </div>
                    <div class="logout-section">
                        <button class="btn-logout" onclick="window.location.href='<?= base_url('reward/logout') ?>'">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="header-actions">
                    <div class="left-buttons">
                        <button class="btn btn-wallet" onclick="openDepositModal('spin')">
                            <i class="bi bi-wallet2 me-1"></i>
                            Spin Wallet
                        </button>
                        <button class="btn btn-wallet" onclick="openDepositModal('user')">
                            <i class="bi bi-cash-coin me-1"></i>
                            User Wallet
                        </button>
                    </div>
                    <div class="right-buttons">
                        <button class="btn-wheel" onclick="openWheelModal()">
                            <i class="bi bi-bullseye"></i>
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
                                <img class="ad-media-stacked" src="<?= $mediaUrl ?>" alt="Advertisement">
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
    console.log(`Redirecting to ${platform === 'whatsapp' ? 'WhatsApp' : 'Telegram'}...`);
    
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