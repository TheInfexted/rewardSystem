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
            </div>
            
            <div class="header-actions">
                <button class="btn btn-deposit" onclick="redirectToDeposit()">
                    <i class="bi bi-plus-circle me-1"></i>
                    Deposit
                </button>
                <button class="btn-wheel" onclick="openWheelModal()" style="width: auto; padding: 0 15px; border-radius: 25px;">
                    <i class="bi bi-bullseye me-1"></i> Fortune Wheel
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
        <div class="ads-container" id="adsContainer">
            <!-- Ads will be loaded here dynamically -->
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
    <?php if (isset($weekly_progress) && is_array($weekly_progress)): ?>
    weeklyProgress: <?= json_encode($weekly_progress) ?>,
    <?php else: ?>
    weeklyProgress: null,
    <?php endif; ?>
    <?php if (isset($recent_activities) && is_array($recent_activities)): ?>
    recentActivities: <?= json_encode($recent_activities) ?>
    <?php else: ?>
    recentActivities: null
    <?php endif; ?>
    ads: <?= json_encode($ads ?? []) ?>
};
</script>

<!-- Dashboard JavaScript Modules -->
<script src="<?= base_url('js/dashboard/dashboard-config.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-utils.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-core.js') ?>"></script>
<script src="<?= base_url('js/dashboard/fortune-wheel.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-init.js') ?>"></script>
<script src="<?= base_url('js/dashboard/dashboard-ads.js') ?>"></script>

<?= $this->endSection() ?>