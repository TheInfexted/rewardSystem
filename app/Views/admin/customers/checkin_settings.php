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
                                <?= t('Admin.customers.checkin.settings_history') ?>
                            </h4>
                            <p class="text-light mb-0">
                                <?= t('Admin.customers.checkin.customer_username')?>: <strong class="text-gold"><?= esc($customer['username']) ?></strong>
                                | <?= t('Admin.customers.checkin.customer_points', ['points' => '']) ?><span class="badge bg-warning text-dark"><?= $customer['points'] ?></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" 
                               class="btn btn-outline-gold btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> <?= t('Admin.customers.checkin.back_to_profile') ?>
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
                        <i class="fas fa-cogs me-2"></i><?= t('Admin.customers.checkin.checkin_settings') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="checkinSettingsForm">
                        
                        <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.customers.checkin.daily_points') ?></label>
                            <input type="number" class="form-control" name="default_checkin_points" 
                                   value="<?= $checkin_settings['default_checkin_points'] ?>" min="1" max="999">
                            <small class="text-muted"><?= t('Admin.customers.checkin.base_points_desc') ?></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.customers.checkin.consecutive_bonus') ?></label>
                            <input type="number" class="form-control" name="consecutive_bonus_points" 
                                value="<?= $checkin_settings['consecutive_bonus_points'] ?? 5 ?>" min="0" max="50" step="0.1">
                            <small class="text-muted"><?= t('Admin.customers.checkin.consecutive_desc') ?></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.customers.checkin.max_streak_days') ?></label>
                            <input type="number" class="form-control" name="max_streak_days" 
                                   value="<?= $checkin_settings['max_streak_days'] ?>" min="1" max="365">
                            <small class="text-muted"><?= t('Admin.customers.checkin.max_streak_desc') ?></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-info"><?= t('Admin.customers.checkin.weekend_bonus') ?></label>
                            <input type="number" class="form-control" name="weekend_bonus_points" 
                                   value="<?= $checkin_settings['weekend_bonus_points'] ?>" min="0" max="500">
                            <small class="text-muted"><?= t('Admin.customers.checkin.weekend_desc') ?></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-info"><?= t('Admin.customers.checkin.weekly_multiplier') ?></label>
                            <input type="number" class="form-control" name="weekly_bonus_multiplier" 
                                   value="<?= $checkin_settings['weekly_bonus_multiplier'] ?>" 
                                   min="1" max="10" step="0.1">
                            <small class="text-muted"><?= t('Admin.customers.checkin.weekly_desc') ?></small>
                        </div>

                        <button type="submit" class="btn btn-gold btn-sm w-100">
                            <i class="fas fa-save me-1"></i> <?= t('Admin.customers.checkin.update_settings') ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-tools me-2"></i><?= t('Admin.customers.checkin.quick_actions') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-warning btn-sm" 
                                onclick="resetCheckinProgress('current_streak')">
                            <i class="fas fa-redo me-1"></i> <?= t('Admin.customers.checkin.reset_current_streak') ?>
                        </button>
                        <button class="btn btn-outline-info btn-sm" 
                                onclick="resetCheckinProgress('monthly_progress')">
                            <i class="fas fa-calendar-times me-1"></i> <?= t('Admin.customers.checkin.reset_monthly') ?>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" 
                                onclick="resetCheckinProgress('all_history')">
                            <i class="fas fa-trash me-1"></i> <?= t('Admin.customers.checkin.reset_all_history') ?>
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
                        <i class="fas fa-calendar-week me-2"></i><?= t('Admin.customers.checkin.current_week_progress') ?>
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
                                            <?= t('Admin.customers.checkin.streak') ?>: <?= $day['checkin_data']['streak_day'] ?>
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
                                <h6 class="text-light"><?= t('Admin.customers.checkin.week_summary') ?></h6>
                                <p class="mb-1">
                                    <span class="text-gold"><?= $weekly_progress['total_current_week'] ?>/7</span> <?= t('Admin.customers.checkin.days_completed', ['count' => '']) ?>
                                </p>
                                <?php if ($weekly_progress['perfect_week']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-trophy me-1"></i><?= t('Admin.customers.checkin.perfect_week') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if ($weekly_progress['previous_week']): ?>
                                <div class="previous-week-info">
                                    <h6 class="text-light"><?= t('Admin.customers.checkin.previous_week') ?></h6>
                                    <p class="mb-1">
                                        <span class="text-info"><?= $weekly_progress['previous_week']['checkin_count'] ?>/7</span> <?= t('Admin.customers.checkin.days_points', [
                                            'days' => '',
                                            'points' => $weekly_progress['previous_week']['reward_points']
                                        ]) ?>
                                        | <span class="text-warning"><?= $weekly_progress['previous_week']['reward_points'] ?></span> <?= t('Admin.customers.checkin.points') ?>
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
                        <div class="stats-label"><?= t('Admin.customers.view.total_checkins') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $checkin_statistics['basic']['total_points_earned'] ?: 0 ?></div>
                        <div class="stats-label"><?= t('Admin.customers.view.points_from_checkins') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $checkin_statistics['basic']['max_streak'] ?: 0 ?></div>
                        <div class="stats-label">Max <?= t('Admin.customers.checkin.streak') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= round($checkin_statistics['basic']['avg_points_per_checkin'] ?: 0) ?></div>
                        <div class="stats-label">Avg <?= t('Admin.customers.checkin.points') ?></div>
                    </div>
                </div>
            </div>

            <!-- Check-in History -->
            <div class="card">
                <div class="card-header bg-primary-black border-gold">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-history me-2"></i><?= t('Admin.customers.checkin.checkin_history') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th><?= t('Admin.customers.checkin.date') ?></th>
                                    <th><?= t('Admin.customers.checkin.day') ?></th>
                                    <th><?= t('Admin.customers.checkin.points') ?></th>
                                    <th><?= t('Admin.customers.checkin.streak') ?></th>
                                    <th><?= t('Admin.customers.checkin.type') ?></th>
                                    <th><?= t('Admin.customers.checkin.time') ?></th>
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
                                            <?= $checkin['day_type'] === 'weekend' ? t('Admin.customers.checkin.weekend') : t('Admin.customers.checkin.weekday') ?>
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
                                            <br><?= t('Admin.customers.checkin.no_history') ?>
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
                        <i class="fas fa-chart-bar me-2"></i><?= t('Admin.customers.checkin.monthly_statistics') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($checkin_statistics['monthly'], 0, 6) as $month): ?>
                        <div class="col-md-2">
                            <div class="monthly-stat-card">
                                <div class="month-name"><?= $month['month_name'] ?> <?= $month['year'] ?></div>
                                <div class="checkin-count"><?= $month['checkin_count'] ?> <?= t('Admin.customers.checkin.days_points', ['days' => '', 'points' => '']) ?></div>
                                <div class="points-earned"><?= $month['points_earned'] ?> <?= t('Admin.customers.checkin.points') ?></div>
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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> <?= t('Admin.customers.checkin.updating') ?>';
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
        'current_streak': '<?= t('Admin.customers.checkin.confirm_reset_streak') ?>',
        'monthly_progress': '<?= t('Admin.customers.checkin.confirm_reset_monthly') ?>',
        'all_history': '<?= t('Admin.customers.checkin.confirm_reset_all') ?>'
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