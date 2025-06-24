<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <!-- Customer Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Profile</h4>
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
                                <p class="text-white-50 mb-0">Premium Member</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dashboard Color Preview -->
                    <div class="dashboard-color-preview mb-3">
                        <small class="text-muted d-block mb-1">Dashboard Background</small>
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
                                <td><strong>Username:</strong></td>
                                <td><?= esc($customer['username']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?= esc($customer['name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= esc($customer['email'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if ($customer['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Joined:</strong></td>
                                <td><?= date('M j, Y', strtotime($customer['created_at'])) ?></td>
                            </tr>
                            <?php if (!empty($customer['last_login'])): ?>
                            <tr>
                                <td><strong>Last Login:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($customer['last_login'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Customer
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" 
                                onclick="resetDashboard(<?= $customer['id'] ?>)">
                            <i class="fas fa-undo"></i> Reset Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="col-md-8">
            <div class="row">
                <!-- Points & Tokens -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <h3 class="text-success"><?= number_format($customer['points']) ?></h3>
                                    <p class="text-muted mb-0">Total Points</p>
                                </div>
                                <div class="col-6 text-center">
                                    <h3 class="text-info"><?= $customer['spin_tokens'] ?></h3>
                                    <p class="text-muted mb-0">Spin Tokens</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Check-in Stats -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <h3 class="text-primary"><?= $stats['checkins']['total_checkins'] ?? 0 ?></h3>
                                    <p class="text-muted mb-0">Total Check-ins</p>
                                </div>
                                <div class="col-6 text-center">
                                    <h3 class="text-warning"><?= $stats['monthly']['monthly_checkins'] ?? 0 ?></h3>
                                    <p class="text-muted mb-0">This Month</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Spin Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Spin Wheel Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary"><?= $stats['spins']['total_spins'] ?? 0 ?></h4>
                            <p class="text-muted">Total Spins</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success"><?= $stats['spins']['winning_spins'] ?? 0 ?></h4>
                            <p class="text-muted">Winning Spins</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <?php 
                            $totalSpins = $stats['spins']['total_spins'] ?? 0;
                            $winningSpins = $stats['spins']['winning_spins'] ?? 0;
                            $winRate = $totalSpins > 0 ? round(($winningSpins / $totalSpins) * 100, 1) : 0;
                            ?>
                            <h4 class="text-warning"><?= $winRate ?>%</h4>
                            <p class="text-muted">Win Rate</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">
                                <?= !empty($stats['spins']['last_spin']) ? date('M j', strtotime($stats['spins']['last_spin'])) : 'Never' ?>
                            </h4>
                            <p class="text-muted">Last Spin</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Timeline -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <?php if (!empty($stats['checkins']['last_checkin'])): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Last Check-in</h6>
                                <p class="text-muted mb-0"><?= date('F j, Y', strtotime($stats['checkins']['last_checkin'])) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($stats['spins']['last_spin'])): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="fas fa-dice"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Last Spin</h6>
                                <p class="text-muted mb-0"><?= date('F j, Y', strtotime($stats['spins']['last_spin'])) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="timeline-item">
                            <div class="timeline-icon bg-info">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Account Created</h6>
                                <p class="text-muted mb-0"><?= date('F j, Y', strtotime($customer['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= base_url('admin/customers/tokens/' . $customer['id']) ?>" 
                               class="btn btn-primary btn-block">
                                <i class="fas fa-coins"></i> Manage Spin Tokens
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" 
                               class="btn btn-warning btn-block">
                                <i class="fas fa-palette"></i> Customize Appearance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="row mt-3">
        <div class="col-12">
            <a href="<?= base_url('admin/customers') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Customer List
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

<script>
function resetDashboard(customerId) {
    if (confirm('Are you sure you want to reset this customer\'s dashboard to default settings? This will remove their custom background image and reset colors.')) {
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
                alert('Dashboard reset successfully!');
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
</script>
<?= $this->endSection() ?>