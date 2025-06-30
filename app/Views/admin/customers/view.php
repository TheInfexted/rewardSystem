<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/checkin-settings.css') ?>">

<div class="container-fluid">
    <div class="row">
        <!-- Customer Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?= t('Admin.customers.view.profile') ?></h4>
                </div>
                <div class="card-body text-center">
                    <!-- Profile Background Preview -->
                    <div class="profile-preview-large mb-3">
                        <div class="profile-header-preview <?= $customer['profile_background'] ?>-bg"
                             style="<?= !empty($customer['profile_background_image']) ? 'background-image: url(' . base_url('uploads/profile_backgrounds/' . $customer['profile_background_image']) . ');' : '' ?>">
                            <?php if (!empty($customer['profile_background_image'])): ?>
                                <div class="profile-header-overlay"></div>
                            <?php endif; ?>
                            <div class="profile-content">
                                <h5 class="text-white"><?= esc($customer['username']) ?></h5>
                                <p class="text-white-50 mb-0"><?= t('Admin.customers.view.premium_member') ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dashboard Color Preview -->
                    <div class="dashboard-color-preview mb-3">
                        <small class="text-muted d-block mb-1"><?= t('Admin.customers.view.dashboard_background') ?></small>
                        <div class="color-swatch-large" style="background-color: <?= $customer['dashboard_bg_color'] ?>"></div>
                        <small class="text-muted"><?= $customer['dashboard_bg_color'] ?></small>
                    </div>
                    
                    <!-- Customer Details -->
                    <div class="customer-details text-left">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td><?= $customer['id'] ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('Admin.customers.table.username') ?>:</strong></td>
                                <td><?= esc($customer['username']) ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('Admin.customers.table.name') ?>:</strong></td>
                                <td><?= esc($customer['name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('Admin.customers.table.email') ?>:</strong></td>
                                <td><?= esc($customer['email'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= t('Admin.customers.table.status') ?>:</strong></td>
                                <td>
                                    <?php if ($customer['is_active']): ?>
                                        <span class="badge badge-success"><?= t('Admin.customers.table.active') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?= t('Admin.customers.table.inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?= t('Admin.customers.table.joined') ?>:</strong></td>
                                <td><?= date('M j, Y', strtotime($customer['created_at'])) ?></td>
                            </tr>
                            <?php if (!empty($customer['last_login'])): ?>
                            <tr>
                                <td><strong><?= t('Admin.customers.table.last_login') ?>:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($customer['last_login'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> <?= t('Admin.customers.actions.edit_customer') ?>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" 
                                onclick="resetDashboard(<?= $customer['id'] ?>)">
                            <i class="fas fa-undo"></i> <?= t('Admin.customers.view.reset_dashboard') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Actions Panel -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary-black border-gold d-flex justify-content-between align-items-center">
                    <h5 class="text-gold mb-0">
                        <i class="fas fa-tools me-2"></i><?= t('Admin.customers.view.management_actions') ?>
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-outline-gold btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i> <?= t('Admin.customers.view.more_actions') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="#" onclick="copyCustomerInfo(<?= $customer['id'] ?>)">
                                <i class="fas fa-copy me-2"></i><?= t('Admin.customers.view.copy_info') ?></a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportCustomerData(<?= $customer['id'] ?>)">
                                <i class="fas fa-download me-2"></i><?= t('Admin.customers.view.export_data') ?></a></li>
                            <li><a class="dropdown-item" href="#" onclick="refreshStatistics()">
                                <i class="fas fa-refresh me-2"></i><?= t('Admin.customers.view.refresh_stats') ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteCustomer(<?= $customer['id'] ?>)">
                                <i class="fas fa-trash me-2"></i><?= t('Admin.customers.view.delete_customer') ?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Management Column -->
                        <div class="col-md-6">
                            <h6 class="text-light mb-3 border-bottom border-secondary pb-2">
                                <i class="fas fa-user-edit me-2"></i><?= t('Admin.customers.view.profile_management') ?>
                            </h6>
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" 
                                class="btn btn-outline-warning btn-sm"
                                data-bs-toggle="tooltip" 
                                title="<?= t('Admin.customers.view.edit_profile_desc') ?>">
                                    <i class="fas fa-edit me-1"></i> <?= t('Admin.customers.view.edit_profile') ?>
                                </a>
                                
                                <div class="btn-group" role="group">
                                    <a href="<?= base_url('admin/customers/tokens/' . $customer['id']) ?>" 
                                    class="btn btn-outline-info btn-sm"
                                    data-bs-toggle="tooltip" 
                                    title="<?= t('Admin.customers.view.manage_tokens_desc') ?>">
                                        <i class="fas fa-coins me-1"></i> <?= t('Admin.customers.actions.manage_tokens') ?>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm dropdown-toggle dropdown-toggle-split" 
                                            data-bs-toggle="dropdown">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark">
                                        <li><a class="dropdown-item" href="#" onclick="quickAddTokens(<?= $customer['id'] ?>, 5)">
                                            <i class="fas fa-plus me-2"></i><?= t('Admin.customers.view.quick_add_5') ?></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="quickAddTokens(<?= $customer['id'] ?>, 10)">
                                            <i class="fas fa-plus me-2"></i><?= t('Admin.customers.view.quick_add_10') ?></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="quickAddTokens(<?= $customer['id'] ?>)">
                                            <i class="fas fa-plus me-2"></i><?= t('Admin.customers.view.custom_amount') ?></a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-warning" href="#" onclick="quickRemoveTokens(<?= $customer['id'] ?>)">
                                            <i class="fas fa-minus me-2"></i><?= t('Admin.customers.view.remove_tokens') ?></a></li>
                                    </ul>
                                </div>
                                
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="resetDashboard(<?= $customer['id'] ?>)"
                                        data-bs-toggle="tooltip" 
                                        title="<?= t('Admin.customers.view.reset_dashboard_desc') ?>">
                                    <i class="fas fa-undo me-1"></i> <?= t('Admin.customers.view.reset_dashboard') ?>
                                </button>
                                
                                <button class="btn btn-outline-<?= $customer['is_active'] ? 'danger' : 'success' ?> btn-sm" 
                                        onclick="toggleCustomerStatus(<?= $customer['id'] ?>)"
                                        data-bs-toggle="tooltip" 
                                        title="<?= $customer['is_active'] ? t('Admin.customers.view.deactivate_desc') : t('Admin.customers.view.activate_desc') ?>">
                                    <i class="fas fa-<?= $customer['is_active'] ? 'user-slash' : 'user-check' ?> me-1"></i> 
                                    <?= $customer['is_active'] ? t('Admin.customers.actions.deactivate') : t('Admin.customers.actions.activate') ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Activity & Analytics Column -->
                        <div class="col-md-6">
                            <h6 class="text-light mb-3 border-bottom border-secondary pb-2">
                                <i class="fas fa-chart-line me-2"></i><?= t('Admin.customers.view.activity_analytics') ?>
                            </h6>
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('admin/customers/checkin-settings/' . $customer['id']) ?>" 
                                class="btn btn-gold btn-sm"
                                data-bs-toggle="tooltip" 
                                title="<?= t('Admin.customers.view.checkin_settings_desc') ?>">
                                    <i class="fas fa-calendar-check me-1"></i> <?= t('Admin.customers.view.checkin_settings') ?>
                                </a>
                                
                                <button class="btn btn-outline-success btn-sm" 
                                        onclick="viewActivityHistory(<?= $customer['id'] ?>)"
                                        data-bs-toggle="tooltip" 
                                        title="<?= t('Admin.customers.view.activity_history_desc') ?>">
                                    <i class="fas fa-history me-1"></i> <?= t('Admin.customers.view.activity_history') ?>
                                </button>
                                
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-light btn-sm" 
                                            onclick="exportCustomerData(<?= $customer['id'] ?>)"
                                            data-bs-toggle="tooltip" 
                                            title="<?= t('Admin.customers.view.export_data_desc') ?>">
                                        <i class="fas fa-download me-1"></i> <?= t('Admin.customers.view.export_data') ?>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-light btn-sm dropdown-toggle dropdown-toggle-split" 
                                            data-bs-toggle="dropdown">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark">
                                        <li><a class="dropdown-item" href="#" onclick="exportCustomerData(<?= $customer['id'] ?>)">
                                            <i class="fas fa-file-csv me-2"></i><?= t('Admin.customers.view.export_csv') ?></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="copyCustomerInfo(<?= $customer['id'] ?>)">
                                            <i class="fas fa-clipboard me-2"></i><?= t('Admin.customers.view.copy_clipboard') ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats Row -->
                    <div class="row mt-4 pt-3 border-top border-secondary">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 text-success mb-1" id="total-checkins">
                                    <?= $stats['checkins']['total_checkins'] ?: 0 ?>
                                </div>
                                <small class="text-muted"><?= t('Admin.customers.view.total_checkins') ?></small>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-success" 
                                        style="width: <?= min(100, ($stats['checkins']['total_checkins'] ?: 0) * 2) ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 text-warning mb-1" id="points-from-checkins">
                                    <?= $stats['checkins']['total_points_earned'] ?: 0 ?>
                                </div>
                                <small class="text-muted"><?= t('Admin.customers.view.points_from_checkins') ?></small>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-warning" 
                                        style="width: <?= min(100, (($stats['checkins']['total_points_earned'] ?: 0) / 500) * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 text-info mb-1" id="monthly-checkins">
                                    <?= $stats['monthly']['monthly_checkins'] ?: 0 ?>
                                </div>
                                <small class="text-muted"><?= t('Admin.customers.view.this_month') ?></small>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-info" 
                                        style="width: <?= min(100, (($stats['monthly']['monthly_checkins'] ?: 0) / 31) * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 text-primary mb-1" id="last-checkin">
                                    <?= $stats['checkins']['last_checkin'] ? date('M j', strtotime($stats['checkins']['last_checkin'])) : t('Admin.customers.view.never') ?>
                                </div>
                                <small class="text-muted"><?= t('Admin.customers.view.last_checkin') ?></small>
                                <div class="mt-1">
                                    <?php if ($stats['checkins']['last_checkin']): ?>
                                        <?php 
                                        $lastCheckin = new DateTime($stats['checkins']['last_checkin']);
                                        $today = new DateTime();
                                        $diff = $today->diff($lastCheckin)->days;
                                        $statusColor = $diff == 0 ? 'success' : ($diff <= 3 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?> badge-sm">
                                            <?= $diff == 0 ? t('Admin.customers.view.today') : ($diff == 1 ? t('Admin.customers.view.yesterday') : t('Admin.customers.view.days_ago', ['days' => $diff])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary badge-sm"><?= t('Admin.customers.view.no_checkins') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keyboard Shortcuts Info -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fas fa-keyboard me-1"></i>
                                <strong><?= t('Admin.customers.view.keyboard_shortcuts') ?>:</strong> 
                                <?= t('Admin.customers.view.ctrl_e_edit') ?>, <?= t('Admin.customers.view.ctrl_t_tokens') ?>, <?= t('Admin.customers.view.ctrl_h_history') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Status Alert -->
            <?php if (!$customer['is_active']): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong><?= t('Admin.customers.view.account_inactive') ?>:</strong> <?= t('Admin.customers.view.account_inactive_desc') ?>
                <a href="#" onclick="toggleCustomerStatus(<?= $customer['id'] ?>)" class="alert-link"><?= t('Admin.customers.view.click_activate') ?></a>.
            </div>
            <?php endif; ?>
            
            <!-- Quick Actions Bar (Floating) -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
                <div class="btn-group-vertical" role="group">
                    <button type="button" 
                            class="btn btn-gold btn-sm mb-1" 
                            onclick="viewActivityHistory(<?= $customer['id'] ?>)"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="left"
                            title="<?= t('Admin.customers.view.checkin_settings_desc') ?>">
                        <i class="fas fa-calendar-check"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-outline-light btn-sm mb-1" 
                            onclick="refreshStatistics()"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="left"
                            title="<?= t('Admin.customers.view.refresh_stats') ?>"
                            id="refreshStatsBtn">
                        <i class="fas fa-refresh"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-outline-secondary btn-sm" 
                            onclick="window.history.back()"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="left"
                            title="Go back">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="row mt-3">
        <div class="col-12">
            <a href="<?= base_url('admin/customers') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?= t('Admin.customers.view.back_to_list') ?>
            </a>
        </div>
    </div>
</div>

<style>
.profile-preview-large {
    max-width: 300px;
    margin: 0 auto;
}

.profile-header-preview {
    height: 120px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-header-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
}

.profile-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.color-swatch-large {
    width: 60px;
    height: 30px;
    border-radius: 8px;
    margin: 0 auto;
    border: 2px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dashboard-color-preview {
    text-align: center;
}

/* Background themes */
.default-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.blue-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.purple-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.green-bg {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
}

.orange-bg {
    background: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%) !important;
}

.pink-bg {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.dark-bg {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
}

/* Activity Timeline */
.activity-timeline {
    position: relative;
    padding-left: 0;
}

.timeline-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 20px;
    background: #dee2e6;
}

.timeline-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
}

.timeline-content h6 {
    margin: 0;
    font-weight: 600;
    color: #333;
}

.timeline-content p {
    margin: 0;
    font-size: 0.9rem;
}

/* Card enhancements */
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0 !important;
}

.card-title {
    margin: 0;
    font-weight: 600;
    color: #333;
}

.table-sm td {
    padding: 0.5rem 0.75rem;
    border: none;
}

.table-sm tr:nth-child(even) {
    background-color: #f8f9fa;
}
</style>

<script src="<?= base_url('js/checkin-settings.js') ?>"></script>

<script>
function resetDashboard(customerId) {
    if (confirm('<?= t('Admin.customers.messages.dashboard_reset') ?>')) {
        fetch(`<?= base_url('admin/customers/resetDashboard/') ?>${customerId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?= t('Admin.customers.messages.dashboard_reset') ?>');
                location.reload();
            } else {
                alert('Failed to reset dashboard: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resetting the dashboard.');
        });
    }
}

function viewActivityHistory(customerId) {
    // For now, redirect to check-in settings which includes history
    window.location.href = `<?= base_url('admin/customers/checkin-settings/') ?>${customerId}`;
}

function generateReport(customerId) {
    if (confirm('Generate a comprehensive activity report for this customer?')) {
        // This could be implemented to generate PDF reports
        alert('Report generation feature coming soon!');
    }
}

function resetDashboard(customerId) {
    if (confirm('Are you sure you want to reset this customer\'s dashboard to default settings?')) {
        fetch(`<?= base_url('admin/customers/resetDashboard/') ?>${customerId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Failed to reset dashboard');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resetting dashboard');
        });
    }
}

// Additional utility functions for customer management

function toggleCustomerStatus(customerId) {
    if (confirm('Are you sure you want to toggle this customer\'s status?')) {
        fetch(`<?= base_url('admin/customers/deactivate/') ?>${customerId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status');
        });
    }
}

function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone!')) {
        if (confirm('This will permanently delete all customer data. Are you absolutely sure?')) {
            fetch(`<?= base_url('admin/customers/delete/') ?>${customerId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '<?= base_url('admin/customers') ?>';
                } else {
                    alert(data.message || 'Failed to delete customer');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting customer');
            });
        }
    }
}

function manageTokens(customerId) {
    window.location.href = `<?= base_url('admin/customers/tokens/') ?>${customerId}`;
}

function editCustomer(customerId) {
    window.location.href = `<?= base_url('admin/customers/edit/') ?>${customerId}`;
}

// Quick actions for tokens management
function quickAddTokens(customerId, amount = null) {
    const tokens = amount || prompt('<?= t('Admin.customers.view.custom_amount') ?>');
    if (tokens && !isNaN(tokens) && parseInt(tokens) > 0) {
        const reason = prompt('<?= t('Admin.customers.tokens.reason_placeholder') ?>');
        if (reason) {
            updateTokensQuick(customerId, 'add', parseInt(tokens), reason);
        }
    }
}

function quickRemoveTokens(customerId, amount = null) {
    const tokens = amount || prompt('How many spin tokens would you like to remove?');
    if (tokens && !isNaN(tokens) && parseInt(tokens) > 0) {
        const reason = prompt('<?= t('Admin.customers.tokens.reason_placeholder') ?>');
        if (reason) {
            updateTokensQuick(customerId, 'remove', parseInt(tokens), reason);
        }
    }
}

function updateTokensQuick(customerId, action, amount, reason) {
    const formData = new FormData();
    formData.append('customer_id', customerId);
    formData.append('action', action);
    formData.append('amount', amount);
    formData.append('reason', reason);

    fetch('<?= base_url('admin/customers/updateTokens') ?>', {
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
            alert(`<?= t('Admin.customers.messages.tokens_updated', ['balance' => '']) ?>${data.new_balance}`);
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update tokens');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating tokens');
    });
}

// Profile image management
function removeProfileImage(customerId) {
    if (confirm('Are you sure you want to remove the customer\'s profile background image?')) {
        const formData = new FormData();
        formData.append('remove_background_image', '1');

        fetch(`<?= base_url('admin/customers/update/') ?>${customerId}`, {
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
                alert('Profile image removed successfully!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to remove profile image');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing profile image');
        });
    }
}

// Customer statistics refresh
function refreshStatistics() {
    const refreshBtn = document.querySelector('#refreshStatsBtn');
    if (refreshBtn) {
        const originalText = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        refreshBtn.disabled = true;

        // Simulate refresh delay
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } else {
        window.location.reload();
    }
}

// Copy customer information to clipboard
function copyCustomerInfo(customerId) {
    const customerData = {
        id: customerId,
        username: '<?= esc($customer['username']) ?>',
        email: '<?= esc($customer['email'] ?? 'N/A') ?>',
        points: '<?= $customer['points'] ?>',
        tokens: '<?= $customer['spin_tokens'] ?>',
        created: '<?= date('Y-m-d H:i:s', strtotime($customer['created_at'])) ?>'
    };

    const textToCopy = `<?= t('Admin.customers.view.profile') ?>:
ID: ${customerData.id}
<?= t('Admin.customers.table.username') ?>: ${customerData.username}
<?= t('Admin.customers.table.email') ?>: ${customerData.email}
<?= t('Admin.customers.table.points') ?>: ${customerData.points}
<?= t('Admin.customers.table.spin_tokens') ?>: ${customerData.tokens}
Created: ${customerData.created}`;

    if (navigator.clipboard) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            alert('<?= t('Admin.customers.messages.info_copied') ?>');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(textToCopy);
        });
    } else {
        fallbackCopyTextToClipboard(textToCopy);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            alert('<?= t('Admin.customers.messages.info_copied') ?>');
        } else {
            alert('Failed to copy customer information');
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        alert('Failed to copy customer information');
    }

    document.body.removeChild(textArea);
}

// Export customer data
function exportCustomerData(customerId) {
    if (confirm('<?= t('Admin.customers.view.export_csv') ?>?')) {
        // Create a simple CSV export
        const customerData = [
            ['Field', 'Value'],
            ['Customer ID', customerId],
            ['<?= t('Admin.customers.table.username') ?>', '<?= esc($customer['username']) ?>'],
            ['<?= t('Admin.customers.table.email') ?>', '<?= esc($customer['email'] ?? 'N/A') ?>'],
            ['<?= t('Admin.customers.table.name') ?>', '<?= esc($customer['name'] ?? 'N/A') ?>'],
            ['<?= t('Admin.customers.table.points') ?>', '<?= $customer['points'] ?>'],
            ['<?= t('Admin.customers.table.spin_tokens') ?>', '<?= $customer['spin_tokens'] ?>'],
            ['Monthly Check-ins', '<?= $customer['monthly_checkins'] ?>'],
            ['<?= t('Admin.customers.table.status') ?>', '<?= $customer['is_active'] ? t('Admin.customers.table.active') : t('Admin.customers.table.inactive') ?>'],
            ['Created', '<?= date('Y-m-d H:i:s', strtotime($customer['created_at'])) ?>'],
            ['Last Updated', '<?= date('Y-m-d H:i:s', strtotime($customer['updated_at'])) ?>'],
            ['<?= t('Admin.customers.table.last_login') ?>', '<?= $customer['last_login'] ? date('Y-m-d H:i:s', strtotime($customer['last_login'])) : t('Admin.customers.view.never') ?>']
        ];

        const csvContent = customerData.map(row => row.map(field => `"${field}"`).join(',')).join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `customer_${customerId}_data.csv`;
        link.click();
        window.URL.revokeObjectURL(url);
        
        alert('<?= t('Admin.customers.messages.data_exported') ?>');
    }
}

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltips to action buttons
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined') {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+E for edit
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            editCustomer(<?= $customer['id'] ?>);
        }
        
        // Ctrl+T for tokens
        if (e.ctrlKey && e.key === 't') {
            e.preventDefault();
            manageTokens(<?= $customer['id'] ?>);
        }
        
        // Ctrl+H for history
        if (e.ctrlKey && e.key === 'h') {
            e.preventDefault();
            viewActivityHistory(<?= $customer['id'] ?>);
        }
    });

    // Auto-refresh statistics every 5 minutes
    setInterval(function() {
        const lastUpdate = localStorage.getItem('customer_stats_last_update');
        const now = Date.now();
        
        if (!lastUpdate || (now - parseInt(lastUpdate)) > 300000) { // 5 minutes
            console.log('Auto-refreshing customer statistics...');
            localStorage.setItem('customer_stats_last_update', now.toString());
            // Uncomment the next line if you want auto-refresh
            // refreshStatistics();
        }
    }, 300000); // Check every 5 minutes

    console.log('Customer view page initialized successfully');
});

// Global error handler for AJAX requests
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    alert('An unexpected error occurred. Please refresh the page and try again.');
});
</script>
<?= $this->endSection() ?>