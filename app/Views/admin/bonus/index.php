<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold">Bonus Claims Management</h1>
        <div class="btn-group">
            <a href="<?= base_url('admin/bonus/export?' . http_build_query($filters)) ?>" 
               class="btn btn-gold">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Enhanced Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-gold"><?= number_format($stats['total_claims']) ?></h5>
                    <p class="text-light mb-0">Total Claims</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-warning"><?= number_format($stats['today_claims']) ?></h5>
                    <p class="text-light mb-0">Today's Claims</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-info"><?= number_format($stats['unique_real_ips'] ?? 0) ?></h5>
                    <p class="text-light mb-0">Unique Real IPs</p>
                    <small class="text-muted">
                        <?= ($stats['localhost_claims'] ?? 0) ?> localhost, 
                        <?= ($stats['no_ip_claims'] ?? 0) ?> no IP
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-success"><?= $stats['conversion_rate'] ?? 0 ?>%</h5>
                    <p class="text-light mb-0">Spin → Claim Rate</p>
                    <small class="text-muted"><?= number_format($stats['total_bonus_spins'] ?? 0) ?> bonus spins</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form method="GET" action="<?= base_url('admin/bonus') ?>" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label text-gold">From Date</label>
                    <input type="date" name="date_from" class="form-control bg-dark text-light border-gold"
                           value="<?= $filters['date_from'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-gold">To Date</label>
                    <input type="date" name="date_to" class="form-control bg-dark text-light border-gold"
                           value="<?= $filters['date_to'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-gold">Search</label>
                    <input type="text" name="search" class="form-control bg-dark text-light border-gold"
                           placeholder="Name, phone, email, IP..." value="<?= $filters['search'] ?? '' ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="<?= base_url('admin/bonus') ?>" class="btn btn-outline-secondary">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="card bg-dark border-secondary">
        <div class="card-body">
            <?php if (empty($claims)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No Claims Found</h5>
                    <p class="text-muted">No bonus claims match your current filters.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Claim Time</th>
                                <th>User Details</th>
                                <th>Contact</th>
                                <th>IP Address</th>
                                <th>Bonus</th>
                                <th>Spin Correlation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td class="text-gold">#<?= $claim['id'] ?></td>
                                <td>
                                    <small class="text-light">
                                        <?= date('M j, Y', strtotime($claim['claim_time'])) ?><br>
                                        <?= date('H:i:s', strtotime($claim['claim_time'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-light"><?= esc($claim['user_name']) ?></strong><br>
                                    <small class="text-muted">Session: <?= substr($claim['session_id'], 0, 16) ?>...</small>
                                </td>
                                <td>
                                    <div class="text-light">
                                        <i class="bi bi-telephone"></i> <?= esc($claim['phone_number']) ?><br>
                                        <?php if ($claim['email']): ?>
                                            <i class="bi bi-envelope"></i> <?= esc($claim['email']) ?>
                                        <?php else: ?>
                                            <small class="text-muted">No email provided</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php 
                                        $displayIp = $claim['display_ip'] ?? $claim['user_ip'] ?? 'No IP';
                                        $ipClass = 'text-warning';
                                        if (strpos($displayIp, 'Localhost') !== false) {
                                            $ipClass = 'text-info';
                                        } elseif ($displayIp === 'No IP Recorded' || $displayIp === 'No IP') {
                                            $ipClass = 'text-danger';
                                        }
                                        ?>
                                        <code class="<?= $ipClass ?>"><?= $displayIp ?></code><br>
                                        
                                        <?php if (isset($claim['claims_same_ip']) && $claim['claims_same_ip'] > 0): ?>
                                            <small class="text-danger">
                                                <i class="bi bi-exclamation-triangle"></i> 
                                                <?= $claim['claims_same_ip'] ?> other claims from this IP
                                            </small>
                                        <?php else: ?>
                                            <small class="text-success">
                                                <i class="bi bi-check-circle"></i> Unique IP
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-gold text-dark"><?= esc($claim['bonus_type']) ?></span><br>
                                    <?php if ($claim['bonus_amount'] > 0): ?>
                                        <small class="text-success">$<?= number_format($claim['bonus_amount'], 2) ?></small>
                                    <?php endif; ?>
                                    <br>
                                </td>
                                <td>
                                    <?php if (isset($claim['related_spin_item']) && $claim['related_spin_item']): ?>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-down-up"></i> 
                                            <?= esc($claim['related_spin_item']) ?>
                                        </small><br>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($claim['spins_same_day']) && $claim['spins_same_day'] > 0): ?>
                                        <small class="text-info">
                                            <?= $claim['spins_same_day'] ?> spins same day
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">No spins tracked</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="viewDetails(<?= htmlspecialchars(json_encode($claim), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center">
                        <!-- Previous Page -->
                        <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= $currentPage > 1 ? base_url('admin/bonus?page=' . ($currentPage - 1) . '&' . http_build_query($filters)) : '#' ?>">
                                Previous
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php 
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link <?= $i === $currentPage ? 'bg-gold border-gold text-dark' : 'bg-secondary border-secondary text-light' ?>" 
                                   href="<?= base_url('admin/bonus?page=' . $i . '&' . http_build_query($filters)) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= $currentPage < $totalPages ? base_url('admin/bonus?page=' . ($currentPage + 1) . '&' . http_build_query($filters)) : '#' ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Claim Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header border-gold">
                <h5 class="modal-title text-gold">Claim Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-gold">User Information</h6>
                        <p><strong>ID:</strong> <span id="detail-id"></span></p>
                        <p><strong>Name:</strong> <span id="detail-name"></span></p>
                        <p><strong>Phone:</strong> <span id="detail-phone"></span></p>
                        <p><strong>Email:</strong> <span id="detail-email"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-gold">Technical Details</h6>
                        <p><strong>IP Address:</strong> <code id="detail-ip"></code></p>
                        <p><strong>Session:</strong> <code id="detail-session"></code></p>
                        <p><strong>User Agent:</strong> <small id="detail-agent"></small></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-gold">Bonus Information</h6>
                        <p><strong>Type:</strong> <span id="detail-bonus"></span></p>
                        <p><strong>Amount:</strong> <span id="detail-amount"></span></p>
                        <p><strong>Claim Time:</strong> <span id="detail-time"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-gold">Correlation Data</h6>
                        <p><strong>Related Spin:</strong> <span id="detail-spin"></span></p>
                        <p><strong>Same Day Spins:</strong> <span id="detail-spins-count"></span></p>
                        <p><strong>Same IP Claims:</strong> <span id="detail-ip-claims"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fixed View claim details function
function viewDetails(claim) {
    document.getElementById('detail-id').textContent = '#' + claim.id;
    document.getElementById('detail-name').textContent = claim.user_name;
    document.getElementById('detail-phone').textContent = claim.phone_number;
    document.getElementById('detail-email').textContent = claim.email || 'Not provided';
    document.getElementById('detail-ip').textContent = claim.display_ip || claim.user_ip || 'No IP';
    document.getElementById('detail-session').textContent = claim.session_id;
    document.getElementById('detail-bonus').textContent = claim.bonus_type;
    
    // Fix the syntax error here - was missing '$' and had wrong quote
    document.getElementById('detail-amount').textContent = claim.bonus_amount > 0 
        ? '$' + parseFloat(claim.bonus_amount).toFixed(2) 
        : 'N/A';
    
    document.getElementById('detail-time').textContent = formatDateTime(claim.claim_time);
    document.getElementById('detail-agent').textContent = claim.user_agent || 'Not recorded';
    
    // Enhanced correlation data
    document.getElementById('detail-spin').textContent = claim.related_spin_item || 'No related spin';
    document.getElementById('detail-spins-count').textContent = claim.spins_same_day || '0';
    document.getElementById('detail-ip-claims').textContent = claim.claims_same_ip || '0';
    
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// Update claim status with proper CSRF token handling
function updateStatus(claimId, status) {
    if (confirm(`Are you sure you want to mark this claim as ${status}?`)) {
        // Get CSRF token properly
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const csrfName = '<?= csrf_token() ?>';
        const csrfHash = '<?= csrf_hash() ?>';
        
        fetch(`<?= base_url('admin/bonus/status/') ?>${claimId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `status=${status}&${csrfName}=${csrfHash}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status');
        });
    }
}

// Format date time
function formatDateTime(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleString();
}

// Auto-refresh stats every 60 seconds
setInterval(() => {
    fetch('<?= base_url('admin/bonus/stats') ?>')
        .then(response => response.json())
        .then(data => {
            // Update stat cards without full page reload
            const goldElement = document.querySelector('.text-gold');
            const warningElement = document.querySelector('.text-warning');
            const infoElement = document.querySelector('.text-info');
            const successElement = document.querySelector('.text-success');
            
            if (goldElement && data.total_claims !== undefined) {
                goldElement.textContent = data.total_claims.toLocaleString();
            }
            if (warningElement && data.today_claims !== undefined) {
                warningElement.textContent = data.today_claims.toLocaleString();
            }
            if (infoElement && data.unique_real_ips !== undefined) {
                infoElement.textContent = data.unique_real_ips.toLocaleString();
            }
            if (successElement && data.conversion_rate !== undefined) {
                successElement.textContent = data.conversion_rate + '%';
            }
        })
        .catch(error => console.error('Stats update error:', error));
}, 60000);
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.table-dark th {
    border-color: #495057;
    color: #ffd700;
}

.table-dark td {
    border-color: #495057;
    vertical-align: middle;
}

.btn-group-vertical > .btn {
    margin-bottom: 2px;
}

.text-gold {
    color: #ffd700 !important;
}

.bg-gold {
    background-color: #ffd700 !important;
}

.border-gold {
    border-color: #ffd700 !important;
}

.pagination .page-link:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.dropdown-menu-dark .dropdown-item:hover {
    background-color: #495057;
}

code {
    font-size: 0.875rem;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

.modal-xl {
    max-width: 1200px;
}

#detailsModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
</style>

<?= $this->endSection() ?>