<?= $this->extend('layouts/customer_layout') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Force portrait mode styling */
    * {
        touch-action: manipulation;
    }
    
    html, body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        background-color: #f5f5f5;
        touch-action: pan-x pan-y;
    }
    
    /* Prevent zooming on input focus (iOS) */
    input, textarea, select {
        font-size: 16px !important;
    }
    
    /* Additional zoom prevention */
    body {
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        overflow-x: hidden;
    }
    
    /* Portrait container wrapper */
    .dashboard-wrapper {
        width: 100%;
        max-width: 414px;
        background-color: #f5f5f5;
        min-height: 100vh;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    
    /* Ensure user-dashboard fills the container */
    .user-dashboard {
        flex: 1;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    
    /* Adjust all sections to respect portrait boundaries */
    .profile-header,
    .checkin-section,
    .quick-actions,
    .recent-activity {
        width: 100%;
        max-width: 414px;
        margin: 0 auto;
    }
    
    /* Modal adjustments for portrait mode */
    @media (max-width: 414px) {
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
        
        .modal-lg {
            max-width: calc(100% - 1rem);
        }
    }
    
    /* Ensure modals don't exceed portrait width */
    .modal-content {
        max-width: 100%;
    }
    
    /* Prevent horizontal scrolling */
    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
        overflow-x: hidden;
    }
</style>

<div class="dashboard-wrapper">
    <div class="user-dashboard">
        <!-- User Profile Header -->
        <div class="profile-header <?= $profile_background ?>-bg">
            <div class="container-fluid px-3">
                <div class="row align-items-center">
                    <div class="col-8">
                        <div class="user-info">
                            <div class="user-details">
                                <h4 class="username text-white mb-1"><?= esc($username) ?></h4>
                                <p class="user-subtitle text-light mb-0">Premium Member</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <button class="btn btn-deposit" onclick="redirectToDeposit()">
                            <i class="bi bi-plus-circle me-1"></i>
                            Deposit
                        </button>
                        <button class="btn btn-settings ms-2" onclick="openWheelModal()">
                            <i class="bi bi-gear"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Background Options -->
            <div class="bg-options">
                <button class="bg-option" data-bg="default" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)"></button>
                <button class="bg-option" data-bg="blue" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%)"></button>
                <button class="bg-option" data-bg="purple" style="background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%)"></button>
                <button class="bg-option" data-bg="green" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)"></button>
                <button class="bg-option" data-bg="red" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)"></button>
                <button class="bg-option" data-bg="orange" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%)"></button>
            </div>
        </div>

        <!-- Daily Check-in Section -->
        <div class="checkin-section">
            <div class="container-fluid px-3">
                <div class="section-header">
                    <h5 class="section-title">
                        <i class="bi bi-calendar-check text-success me-2"></i>
                        Daily Check-in
                    </h5>
                    <span class="streak-badge">
                        <i class="bi bi-fire"></i>
                        <?= $checkin_streak ?> days
                    </span>
                </div>
                
                <div class="checkin-grid">
                    <?php for ($i = 1; $i <= 7; $i++): ?>
                        <?php 
                        $isChecked = $i <= $checkin_streak;
                        $isToday = $i == 1; // Simplified for demo
                        $canCheckIn = $isToday && !$today_checkin;
                        ?>
                        <div class="checkin-day <?= $isChecked ? 'checked' : '' ?> <?= $canCheckIn ? 'available' : '' ?>" 
                             <?= $canCheckIn ? 'onclick="performCheckin()"' : '' ?>>
                            <div class="day-number">Day <?= $i ?></div>
                            <div class="reward-info">
                                <div class="coin-icon">
                                    <i class="bi bi-coin"></i>
                                </div>
                                <span class="reward-text"><?= 10 * $i ?> Points</span>
                            </div>
                            <?php if ($isChecked): ?>
                                <div class="check-mark">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            <?php elseif ($canCheckIn): ?>
                                <div class="checkin-btn">
                                    Check In
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div class="checkin-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?= $monthly_checkins ?></span>
                        <span class="stat-label">This Month</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= $checkin_streak ?></span>
                        <span class="stat-label">Current Streak</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= $total_points ?></span>
                        <span class="stat-label">Total Points</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="container-fluid px-3">
                <div class="section-header">
                    <h5 class="section-title">
                        <i class="bi bi-lightning-fill text-warning me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                
                <div class="action-grid">
                    <div class="action-item" onclick="window.location.href='<?= base_url('/') ?>'">
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
                    <div class="action-item" onclick="window.location.href='<?= base_url('reward') ?>'">
                        <div class="action-icon">
                            <i class="bi bi-gift-fill"></i>
                        </div>
                        <span class="action-text">Rewards</span>
                    </div>
                    <div class="action-item">
                        <div class="action-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span class="action-text">Profile</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="container-fluid px-3">
                <div class="section-header">
                    <h5 class="section-title">
                        <i class="bi bi-clock-history text-info me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                
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
                        <div class="text-center text-muted py-3">
                            No recent activity
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fortune Wheel Modal -->
<div class="modal fade" id="wheelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-gold">
                    <i class="bi bi-pie-chart-fill me-2"></i>
                    Fortune Wheel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <p class="text-light">Redirecting to Fortune Wheel...</p>
                    <div class="spinner-border text-gold" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Congratulations Modal -->
<div class="modal fade" id="winModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content win-modal-content">
            <div class="modal-body text-center py-4">
                <div class="win-animation">
                    <h5 class="win-title mb-3">🎉 CONGRATULATIONS! 🎉</h5>
                    <div class="win-prize mb-3" id="modalPrize">You Won Amazing Prize!</div>
                    <button type="button" class="btn btn-win" data-bs-dismiss="modal">
                        Collect Your Prize
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// WhatsApp redirect URL (you can change this)
const WHATSAPP_URL = 'https://wa.me/601159599022?text=Hello%20my%20user%20ID%20is%20'; 

// Background change functionality
document.querySelectorAll('.bg-option').forEach(option => {
    option.addEventListener('click', function() {
        const bg = this.dataset.bg;
        updateBackground(bg);
    });
});

function updateBackground(background) {
    fetch('<?= base_url('customer/update-background') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `background=${background}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.profile-header').className = `profile-header ${background}-bg`;
            showToast('Background updated!', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update background', 'error');
    });
}

function redirectToDeposit() {
    const username = '<?= esc($username) ?>';
    window.open(WHATSAPP_URL + username, '_blank');
}

function performCheckin() {
    fetch('<?= base_url('customer/checkin') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Check-in successful! +${data.reward_points} points`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Check-in failed', 'error');
    });
}

function openWheelModal() {
    const modal = new bootstrap.Modal(document.getElementById('wheelModal'));
    modal.show();
    
    // Redirect to wheel page after showing modal
    setTimeout(() => {
        window.location.href = '<?= base_url('/') ?>';
    }, 1500);
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    toast.innerHTML = `${message}<button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>`;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

// Prevent zoom on mobile
document.addEventListener('gesturestart', function (e) {
    e.preventDefault();
});

document.addEventListener('touchstart', function(event) {
    if (event.touches.length > 1) {
        event.preventDefault();
    }
});

let lastTouchEnd = 0;
document.addEventListener('touchend', function (event) {
    const now = (new Date()).getTime();
    if (now - lastTouchEnd <= 300) {
        event.preventDefault();
    }
    lastTouchEnd = now;
}, false);
</script>
<?= $this->endSection() ?>