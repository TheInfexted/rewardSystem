<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold">Spin Details - #<?= $spin['id'] ?></h1>
        <a href="<?= base_url('admin/reports') ?>" class="btn btn-outline-gold">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <div class="row">
        <!-- Main Spin Details -->
        <div class="col-md-8">
            <div class="card bg-dark border-secondary">
                <div class="card-header">
                    <h5 class="text-gold mb-0">Spin Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-light">Basic Details</h6>
                            <table class="table table-dark table-sm">
                                <tr>
                                    <td class="text-muted">Spin ID:</td>
                                    <td class="text-gold">#<?= $spin['id'] ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Spin Time:</td>
                                    <td class="text-light"><?= date('M j, Y H:i:s', strtotime($spin['spin_time'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Session ID:</td>
                                    <td>
                                        <?php if (!empty($spin['session_id'])): ?>
                                            <code class="text-info"><?= esc($spin['session_id']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">Not available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">User IP:</td>
                                    <td><code class="text-warning"><?= $spin['user_ip'] ?></code></td>
                                </tr>
                                <?php if (isset($spin['spin_number']) && $spin['spin_number']): ?>
                                <tr>
                                    <td class="text-muted">Spin Number:</td>
                                    <td><span class="badge bg-info">#<?= $spin['spin_number'] ?></span></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-light">Prize Details</h6>
                            <table class="table table-dark table-sm">
                                <tr>
                                    <td class="text-muted">Item Won:</td>
                                    <td class="text-light font-weight-bold"><?= esc($spin['item_won']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Prize Type:</td>
                                    <td>
                                        <span class="badge <?= $spin['prize_type'] === 'cash' ? 'bg-success' : 'bg-info' ?>">
                                            <?= ucfirst($spin['prize_type']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Prize Amount:</td>
                                    <td>
                                        <?php if ($spin['prize_amount'] > 0): ?>
                                            <span class="text-success h6">
                                                <?= $spin['prize_type'] === 'cash' ? '¥' : '' ?><?= number_format($spin['prize_amount'], 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No monetary value</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Bonus Claimed:</td>
                                    <td>
                                        <?php if (isset($spin['bonus_claimed']) && $spin['bonus_claimed']): ?>
                                            <span class="badge bg-success">Yes</span>
                                            <?php if (isset($spin['claim_id']) && $spin['claim_id']): ?>
                                                <br><small class="text-muted">Claim ID: #<?= $spin['claim_id'] ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- User Agent Info -->
                    <?php if (!empty($spin['user_agent'])): ?>
                    <div class="mt-4">
                        <h6 class="text-light">User Agent Information</h6>
                        <div class="bg-secondary p-3 rounded">
                            <small class="text-light font-monospace"><?= esc($spin['user_agent']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Information -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card bg-dark border-secondary mb-3">
                <div class="card-header">
                    <h6 class="text-gold mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-2">
                            <span class="text-muted">Time Since Spin:</span><br>
                            <span class="text-light"><?= $this->timeAgo($spin['spin_time']) ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Day of Week:</span><br>
                            <span class="text-light"><?= date('l', strtotime($spin['spin_time'])) ?></span>
                        </div>
                        <div>
                            <span class="text-muted">Hour:</span><br>
                            <span class="text-light"><?= date('H:i', strtotime($spin['spin_time'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session-related Spins -->
            <?php if (!empty($sessionSpins)): ?>
            <div class="card bg-dark border-secondary mb-3">
                <div class="card-header">
                    <h6 class="text-gold mb-0">Other Spins in Same Session</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Item</th>
                                    <th>Prize</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessionSpins as $sessionSpin): ?>
                                <tr>
                                    <td>
                                        <small><?= date('H:i:s', strtotime($sessionSpin['spin_time'])) ?></small>
                                    </td>
                                    <td>
                                        <small><?= esc($sessionSpin['item_won']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($sessionSpin['prize_amount'] > 0): ?>
                                            <small class="text-success">
                                                <?= $sessionSpin['prize_type'] === 'cash' ? '¥' : '' ?><?= number_format($sessionSpin['prize_amount']) ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- IP-related Spins -->
            <?php if (!empty($ipSpins)): ?>
            <div class="card bg-dark border-secondary">
                <div class="card-header">
                    <h6 class="text-gold mb-0">Other Spins from Same IP (Today)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Item</th>
                                    <th>Session</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($ipSpins, 0, 10) as $ipSpin): ?>
                                <tr>
                                    <td>
                                        <small><?= date('H:i', strtotime($ipSpin['spin_time'])) ?></small>
                                    </td>
                                    <td>
                                        <small><?= esc($ipSpin['item_won']) ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= !empty($ipSpin['session_id']) ? substr($ipSpin['session_id'], 0, 8) . '...' : 'N/A' ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($ipSpins) > 10): ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <small class="text-muted">... and <?= count($ipSpins) - 10 ?> more</small>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function for time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}
?>

<style>
.table-sm td, .table-sm th {
    padding: 0.5rem 0.75rem;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}

.card:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.bg-secondary {
    background-color: #495057 !important;
}
</style>

<?= $this->endSection() ?>