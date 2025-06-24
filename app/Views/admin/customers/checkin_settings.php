<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Customer Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-secondary-black border-gold">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-gold mb-1">
                                <i class="fas fa-calendar-check me-2"></i>
                                Check-in Settings & History
                            </h4>
                            <p class="text-light mb-0">
                                Customer: <strong class="text-gold"><?= esc($customer['username']) ?></strong>
                                | Total Points: <span class="badge bg-warning text-dark"><?= $customer['points'] ?></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" 
                               class="btn btn-outline-gold btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Check-in Settings Panel -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-cogs me-2"></i>Check-in Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form id="checkinSettingsForm">
                        <div class="mb-3">
                            <label class="form-label text-light">Daily Check-in Points</label>
                            <input type="number" class="form-control" name="default_checkin_points" 
                                   value="<?= $checkin_settings['default_checkin_points'] ?>" min="1" max="999">
                            <small class="text-muted">Base points awarded for daily check-ins</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Weekly Bonus Multiplier</label>
                            <input type="number" class="form-control" name="weekly_bonus_multiplier" 
                                   value="<?= $checkin_settings['weekly_bonus_multiplier'] ?>" 
                                   min="1" max="10" step="0.1">
                            <small class="text-muted">Multiplier for perfect week completion</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Max Streak Days</label>
                            <input type="number" class="form-control" name="max_streak_days" 
                                   value="<?= $checkin_settings['max_streak_days'] ?>" min="1" max="365">
                            <small class="text-muted">Maximum consecutive days for streak bonus</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Weekend Bonus Points</label>
                            <input type="number" class="form-control" name="weekend_bonus_points" 
                                   value="<?= $checkin_settings['weekend_bonus_points'] ?>" min="0" max="500">
                            <small class="text-muted">Extra points for weekend check-ins</small>
                        </div>

                        <button type="submit" class="btn btn-gold btn-sm w-100">
                            <i class="fas fa-save me-1"></i> Update Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-tools me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-warning btn-sm" 
                                onclick="resetCheckinProgress('current_streak')">
                            <i class="fas fa-redo me-1"></i> Reset Current Streak
                        </button>
                        <button class="btn btn-outline-info btn-sm" 
                                onclick="resetCheckinProgress('monthly_progress')">
                            <i class="fas fa-calendar-times me-1"></i> Reset Monthly Progress
                        </button>
                        <button class="btn btn-outline-danger btn-sm" 
                                onclick="resetCheckinProgress('all_history')">
                            <i class="fas fa-trash me-1"></i> Reset All History
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Progress -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-calendar-week me-2"></i>Current Week Progress
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($weekly_progress['current_week'] as $day): ?>
                        <div class="col">
                            <div class="text-center">
                                <div class="weekly-day-card <?= $day['checkin_data'] ? 'checked-in' : '' ?> 
                                                            <?= $day['is_weekend'] ? 'weekend-day' : '' ?>">
                                    <div class="day-name"><?= substr($day['day_name'], 0, 3) ?></div>
                                    <?php if ($day['checkin_data']): ?>
                                        <div class="check-icon">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </div>
                                        <div class="points-earned">
                                            +<?= $day['checkin_data']['reward_points'] ?>
                                        </div>
                                        <div class="streak-day">
                                            Streak: <?= $day['checkin_data']['streak_day'] ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-checkin">
                                            <i class="fas fa-times-circle text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="progress-summary">
                                <h6 class="text-light">Week Summary</h6>
                                <p class="mb-1">
                                    <span class="text-gold"><?= $weekly_progress['total_current_week'] ?>/7</span> days completed
                                </p>
                                <?php if ($weekly_progress['perfect_week']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-trophy me-1"></i>Perfect Week!
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if ($weekly_progress['previous_week']): ?>
                                <div class="previous-week-info">
                                    <h6 class="text-light">Previous Week</h6>
                                    <p class="mb-1">
                                        <span class="text-info"><?= $weekly_progress['previous_week']['checkin_count'] ?>/7</span> days
                                        | <span class="text-warning"><?= $weekly_progress['previous_week']['reward_points'] ?></span> points
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $checkin_statistics['basic']['total_checkins'] ?: 0 ?></div>
                        <div class="stats-label">Total Check-ins</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $checkin_statistics['basic']['total_points_earned'] ?: 0 ?></div>
                        <div class="stats-label">Points Earned</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $checkin_statistics['basic']['max_streak'] ?: 0 ?></div>
                        <div class="stats-label">Max Streak</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= round($checkin_statistics['basic']['avg_points_per_checkin'] ?: 0) ?></div>
                        <div class="stats-label">Avg Points</div>
                    </div>
                </div>
            </div>

            <!-- Check-in History -->
            <div class="card">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-history me-2"></i>Check-in History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Points</th>
                                    <th>Streak</th>
                                    <th>Type</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customer_checkin_history)): ?>
                                    <?php foreach ($customer_checkin_history as $checkin): ?>
                                    <tr>
                                        <td>
                                            <strong><?= date('M j, Y', strtotime($checkin['checkin_date'])) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?= $checkin['day_type'] === 'weekend' ? 'bg-info' : 'bg-secondary' ?>">
                                                <?= $checkin['day_name'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-warning">+<?= $checkin['reward_points'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($checkin['streak_day'] > 1): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-fire me-1"></i><?= $checkin['streak_day'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">1</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= ucfirst($checkin['day_type']) ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($checkin['created_at'])) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>No check-in history found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <?php if (!empty($checkin_statistics['monthly'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Monthly Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($checkin_statistics['monthly'], 0, 6) as $month): ?>
                        <div class="col-md-2">
                            <div class="monthly-stat-card">
                                <div class="month-name"><?= $month['month_name'] ?> <?= $month['year'] ?></div>
                                <div class="checkin-count"><?= $month['checkin_count'] ?> days</div>
                                <div class="points-earned"><?= $month['points_earned'] ?> points</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Custom CSS for Check-in Interface -->
<style>
.weekly-day-card {
    background: var(--secondary-black);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.weekly-day-card.checked-in {
    border-color: var(--success);
    background: linear-gradient(135deg, var(--secondary-black) 0%, rgba(40, 167, 69, 0.1) 100%);
}

.weekly-day-card.weekend-day {
    border-color: var(--info);
    background: linear-gradient(135deg, var(--secondary-black) 0%, rgba(23, 162, 184, 0.1) 100%);
}

.day-name {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.check-icon i {
    font-size: 1.5rem;
    margin: 5px 0;
}

.no-checkin i {
    font-size: 1.5rem;
    margin: 5px 0;
}

.points-earned {
    font-size: 0.75rem;
    color: var(--warning);
    font-weight: bold;
}

.streak-day {
    font-size: 0.65rem;
    color: var(--success);
    margin-top: 2px;
}

.monthly-stat-card {
    background: var(--secondary-black);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 15px;
    text-align: center;
    margin-bottom: 15px;
}

.month-name {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 5px;
}

.checkin-count {
    font-size: 1.1rem;
    color: var(--gold);
    font-weight: bold;
    margin-bottom: 3px;
}

.points-earned {
    font-size: 0.9rem;
    color: var(--success);
}

.progress-summary h6 {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 5px;
}

.previous-week-info h6 {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 5px;
}

@media (max-width: 768px) {
    .weekly-day-card {
        min-height: 80px;
        padding: 8px;
    }
    
    .stats-card {
        margin-bottom: 15px;
    }
    
    .monthly-stat-card {
        margin-bottom: 10px;
    }
}
</style>

<!-- JavaScript for Check-in Settings -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle settings form submission
    const settingsForm = document.getElementById('checkinSettingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateCheckinSettings();
        });
    }
});

/**
 * Update check-in settings
 */
function updateCheckinSettings() {
    const formData = new FormData(document.getElementById('checkinSettingsForm'));
    
    // Show loading state
    const submitBtn = document.querySelector('#checkinSettingsForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch('<?= base_url('admin/customers/updateCheckinSettings') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
        } else {
            showToast('error', data.message || 'Failed to update settings');
            if (data.errors) {
                console.error('Validation errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while updating settings');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Reset check-in progress
 */
function resetCheckinProgress(resetType) {
    const messages = {
        'current_streak': 'Are you sure you want to reset the current streak?',
        'monthly_progress': 'Are you sure you want to reset this month\'s progress? This will delete all check-ins for the current month.',
        'all_history': 'Are you sure you want to reset ALL check-in history? This action cannot be undone!'
    };
    
    if (!confirm(messages[resetType] || 'Are you sure you want to proceed?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('reset_type', resetType);
    
    fetch('<?= base_url('admin/customers/resetCheckinProgress/' . $customer['id']) ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Failed to reset progress');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while resetting progress');
    });
}

/**
 * Show toast notification
 */
function showToast(type, message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}
</script>

<?= $this->endSection() ?>