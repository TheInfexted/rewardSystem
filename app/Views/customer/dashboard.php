<?= $this->extend('layouts/customer_layout') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Mobile-first responsive design for customer dashboard */
    body {
        margin: 0;
        padding: 0;
        background: #f8f9fa;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    /* Main container - centered like reward system */
    .dashboard-container {
        max-width: 414px;
        margin: 0 auto;
        background: white;
        min-height: 100vh;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        position: relative;
    }
    
    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header.default-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .profile-header.blue-bg { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .profile-header.green-bg { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .profile-header.purple-bg { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .profile-header.gold-bg { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); }
    
    .user-info {
        margin-bottom: 20px;
    }
    
    .username {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .user-subtitle {
        opacity: 0.9;
        font-size: 0.9rem;
    }
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
    }
    
    .btn-deposit {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        backdrop-filter: blur(10px);
    }
    
    .btn-settings {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Background Options */
    .bg-options {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
    }
    
    .bg-option {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .bg-option.active {
        border-color: white;
        transform: scale(1.1);
    }
    
    /* Weekly Check-in Section */
    .checkin-section {
        padding: 20px;
        background: white;
    }

    .section-title {
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }

    .checkin-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 20px;
        color: white;
        text-align: center;
        margin-bottom: 20px;
    }

    .checkin-streak {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .streak-text {
        opacity: 0.9;
        margin-bottom: 15px;
    }

    .btn-checkin {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        width: 100%;
    }

    .btn-checkin:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* NEW WEEKLY SYSTEM STYLES */
    .weekly-calendar {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-top: 15px;
    }

    .week-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .week-header span {
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
    }

    .week-header small {
        display: block;
        color: #6c757d;
        margin-top: 5px;
        font-size: 0.8rem;
    }

    .week-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }

    .day-item {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 8px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        min-height: 90px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .day-item.completed {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: #28a745;
        color: white;
        transform: scale(1.05);
    }

    .day-item.today {
        border-color: #007bff;
        border-width: 3px;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    .day-item.future {
        opacity: 0.6;
        background: #f8f9fa;
    }

    .day-name {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .day-number {
        font-size: 1.2rem;
        font-weight: bold;
        margin: 5px 0;
    }

    .day-points {
        font-size: 0.7rem;
        margin-top: auto;
    }

    .day-points .earned {
        color: #fff;
        font-weight: bold;
    }

    .day-points .available {
        color: #28a745;
        font-weight: 600;
    }

    .day-points .future {
        color: #6c757d;
    }

    .check-mark {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ffc107;
        color: #000;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
    }

    .week-summary {
        display: flex;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }

    .summary-item {
        text-align: center;
    }

    .summary-item .label {
        font-size: 0.8rem;
        color: #6c757d;
        display: block;
    }

    .summary-item .value {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    .perfect-week-badge {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #333;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
        margin-top: 10px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Mobile responsive adjustments */
    @media (max-width: 375px) {
        .week-days {
            gap: 5px;
        }
        
        .day-item {
            padding: 8px 4px;
            min-height: 70px;
        }
        
        .day-name {
            font-size: 0.6rem;
        }
        
        .day-number {
            font-size: 1rem;
        }
        
        .day-points {
            font-size: 0.6rem;
        }
        
        .check-mark {
            width: 16px;
            height: 16px;
            font-size: 0.6rem;
        }
    }
    
    /* Statistics Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    /* Quick Actions */
    .quick-actions {
        padding: 0 20px 20px;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .action-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .action-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: #333;
        text-decoration: none;
    }
    
    .action-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.2rem;
        color: white;
    }
    
    .action-text {
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    /* Recent Activity */
    .recent-activity {
        padding: 0 20px 100px; /* Extra bottom padding for navigation */
    }
    
    .activity-list {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 15px;
        font-size: 1rem;
    }
    
    .activity-icon.bg-success { background: #28a745; }
    .activity-icon.bg-info { background: #17a2b8; }
    .activity-icon.bg-warning { background: #ffc107; color: #333; }
    .activity-icon.bg-primary { background: #667eea; }
    
    .activity-content {
        flex: 1;
    }
    
    .activity-title {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }
    
    .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .activity-reward {
        font-weight: 600;
        color: #28a745;
        font-size: 0.9rem;
    }
    
    /* Empty state */
    .empty-activity {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .empty-activity i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    /* Responsive adjustments */
    @media (max-width: 375px) {
        .dashboard-container {
            max-width: 100%;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="dashboard-container">
    <!-- User Profile Header -->
    <div class="profile-header <?= $profile_background ?>-bg">
        <div class="user-info">
            <h4 class="username"><?= esc($username) ?></h4>
            <p class="user-subtitle">Premium Member</p>
        </div>
        
        <div class="header-actions">
            <button class="btn btn-deposit" onclick="redirectToDeposit()">
                <i class="bi bi-plus-circle me-1"></i>
                Deposit
            </button>
            <button class="btn btn-settings" onclick="openSettings()">
                <i class="bi bi-gear"></i>
            </button>
        </div>
        
        <!-- Background Options -->
        <div class="bg-options">
            <button class="bg-option <?= $profile_background === 'default' ? 'active' : '' ?>" 
                    data-bg="default" 
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                    onclick="changeBackground('default')"></button>
            <button class="bg-option <?= $profile_background === 'blue' ? 'active' : '' ?>" 
                    data-bg="blue" 
                    style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"
                    onclick="changeBackground('blue')"></button>
            <button class="bg-option <?= $profile_background === 'green' ? 'active' : '' ?>" 
                    data-bg="green" 
                    style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"
                    onclick="changeBackground('green')"></button>
            <button class="bg-option <?= $profile_background === 'purple' ? 'active' : '' ?>" 
                    data-bg="purple" 
                    style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);"
                    onclick="changeBackground('purple')"></button>
            <button class="bg-option <?= $profile_background === 'gold' ? 'active' : '' ?>" 
                    data-bg="gold" 
                    style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);"
                    onclick="changeBackground('gold')"></button>
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
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h5 class="section-title">
            <i class="bi bi-lightning text-warning me-2"></i>
            Quick Actions
        </h5>
        
        <div class="actions-grid">
            <div class="action-item" onclick="openWheelModal()">
                <div class="action-icon">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <span class="action-text">Spin Wheel</span>
            </div>
            <div class="action-item" onclick="redirectToDeposit()">
                <div class="action-icon">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <span class="action-text">Deposit</span>
            </div>
            <div class="action-item" onclick="showRewards()">
                <div class="action-icon">
                    <i class="bi bi-gift-fill"></i>
                </div>
                <span class="action-text">Rewards</span>
            </div>
            <div class="action-item" onclick="showProfile()">
                <div class="action-icon">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span class="action-text">Profile</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
        <h5 class="section-title">
            <i class="bi bi-clock-history text-info me-2"></i>
            Recent Activity
        </h5>
        
        <div class="activity-list">
            <?php if (!empty($recent_activities)): ?>
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon bg-<?= $activity['type_color'] ?>">
                            <i class="bi bi-<?= $activity['icon'] ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title"><?= esc($activity['title']) ?></div>
                            <div class="activity-time"><?= $activity['time_ago'] ?></div>
                        </div>
                        <div class="activity-reward"><?= esc($activity['reward']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-activity">
                    <i class="bi bi-inbox"></i>
                    <p>No recent activity</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
// CSRF token for AJAX requests
const csrfToken = '<?= csrf_hash() ?>';
const csrfName = '<?= csrf_token() ?>';

// Check-in functionality
function performCheckin() {
    fetch('<?= base_url('customer/checkin') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            document.querySelector('.btn-checkin').disabled = true;
            document.querySelector('.btn-checkin').textContent = 'Already Checked In';
            
            // Show success message
            showToast('Check-in successful! +' + (data.points || 10) + ' points', 'success');
            
            // Refresh page after delay
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showToast(data.message || 'Check-in failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Check-in failed. Please try again.', 'error');
    });
}

// Change background
// Fixed changeBackground function for customer dashboard
function changeBackground(bgType) {
    console.log('Changing background to:', bgType);
    
    // Create FormData instead of JSON
    const formData = new FormData();
    formData.append('background', bgType);
    
    // Add CSRF token if available
    if (typeof csrfName !== 'undefined' && typeof csrfToken !== 'undefined') {
        formData.append(csrfName, csrfToken);
    }
    
    fetch('<?= base_url('customer/update-background') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData  // Use FormData instead of JSON
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            // Update UI immediately
            const profileHeader = document.querySelector('.profile-header');
            if (profileHeader) {
                // Remove all background classes
                profileHeader.className = profileHeader.className.replace(/\w+-bg/g, '');
                // Add new background class
                profileHeader.classList.add(`${bgType}-bg`);
            }
            
            // Update active button
            document.querySelectorAll('.bg-option').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const selectedBtn = document.querySelector(`[data-bg="${bgType}"]`);
            if (selectedBtn) {
                selectedBtn.classList.add('active');
            }
            
            showToast('Background updated successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to update background', 'error');
        }
    })
    .catch(error => {
        console.error('Background change error:', error);
        showToast('Failed to update background. Please try again.', 'error');
    });
}

// Navigation functions
function redirectToDeposit() {
    window.open('<?= base_url('admin/bonus') ?>', '_blank');
}

function openWheelModal() {
    window.location.href = '<?= base_url('/') ?>';
}

function openSettings() {
    showToast('Settings feature coming soon!', 'info');
}

function showRewards() {
    showToast('Rewards feature coming soon!', 'info');
}

function showProfile() {
    showToast('Profile feature coming soon!', 'info');
}

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = `
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 250px;
        text-align: center;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
<?= $this->endSection() ?>