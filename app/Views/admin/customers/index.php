<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Customer Management</h3>
                    <div class="card-tools">
                        <form method="get" class="form-inline">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search customers..." value="<?= esc($search) ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Points</th>
                                    <th>Spin Tokens</th>
                                    <th>Profile Theme</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= $customer['id'] ?></td>
                                    <td>
                                        <strong><?= esc($customer['username']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Joined: <?= date('M j, Y', strtotime($customer['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td><?= esc($customer['name'] ?? 'N/A') ?></td>
                                    <td><?= esc($customer['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?= number_format($customer['points']) ?> pts
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= $customer['spin_tokens'] ?? 0 ?> tokens
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-preview me-2">
                                                <?php if (!empty($customer['profile_background_image'])): ?>
                                                    <img src="<?= base_url('uploads/profile_backgrounds/' . $customer['profile_background_image']) ?>" 
                                                         alt="Profile BG" class="profile-bg-thumb">
                                                <?php else: ?>
                                                    <div class="profile-bg-thumb <?= $customer['profile_background'] ?? 'default' ?>-bg"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <small class="d-block">BG: <?= ucfirst($customer['profile_background'] ?? 'default') ?></small>
                                                <small class="text-muted">
                                                    <i class="fas fa-palette"></i> 
                                                    <?= $customer['dashboard_bg_color'] ?? '#ffffff' ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($customer['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                        <?php if (!empty($customer['last_login'])): ?>
                                            <br><small class="text-muted">
                                                Last: <?= date('M j, H:i', strtotime($customer['last_login'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" 
                                               class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" 
                                               class="btn btn-warning" title="Edit Customer">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('admin/customers/tokens/' . $customer['id']) ?>" 
                                               class="btn btn-primary" title="Manage Tokens">
                                                <i class="fas fa-coins"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-<?= $customer['is_active'] ? 'secondary' : 'success' ?>" 
                                                    title="<?= $customer['is_active'] ? 'Deactivate' : 'Activate' ?> Customer"
                                                    onclick="toggleCustomerStatus(<?= $customer['id'] ?>, <?= $customer['is_active'] ?>)">
                                                <i class="fas fa-<?= $customer['is_active'] ? 'user-slash' : 'user-check' ?>"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    title="Delete Customer"
                                                    onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= esc($customer['username']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($customers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No customers found</h5>
                            <p class="text-muted">
                                <?= $search ? 'No customers match your search criteria.' : 'No customers registered yet.' ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <?= $pager->links() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Permanently Delete Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>⚠️ WARNING: This action cannot be undone!</strong>
                </div>
                
                <p>You are about to permanently delete customer: <strong id="deleteCustomerName"></strong></p>
                
                <div class="mb-3">
                    <p><strong>This will:</strong></p>
                    <ul class="text-danger">
                        <li>Permanently remove all customer data</li>
                        <li>Delete profile images and customizations</li>
                        <li>Remove access to the customer dashboard</li>
                        <li><strong>Cannot be reversed or undone</strong></li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Recommendation:</strong> Consider deactivating the customer instead to preserve data while preventing access.
                </div>
                
                <div class="mb-3">
                    <label for="confirmUsername" class="form-label">
                        To confirm deletion, type the customer's username: <strong id="confirmUsernameTarget"></strong>
                    </label>
                    <input type="text" class="form-control" id="confirmUsername" 
                           placeholder="Enter username to confirm deletion">
                    <div class="form-text text-danger" id="confirmUsernameError" style="display: none;">
                        Username doesn't match. Please type the exact username to confirm.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> Permanently Delete Customer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.profile-bg-thumb {
    width: 30px;
    height: 20px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.profile-bg-thumb.default-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-bg-thumb.blue-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-bg-thumb.purple-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-bg-thumb.green-bg {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.profile-bg-thumb.orange-bg {
    background: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%);
}

.profile-bg-thumb.pink-bg {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.profile-bg-thumb.dark-bg {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.profile-preview {
    display: flex;
    align-items: center;
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Status indicators */
.btn-group .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
let currentCustomerId = null;
let currentCustomerUsername = null;

function deleteCustomer(customerId, username) {
    currentCustomerId = customerId;
    currentCustomerUsername = username;
    
    // Update modal content
    document.getElementById('deleteCustomerName').textContent = username;
    document.getElementById('confirmUsernameTarget').textContent = username;
    document.getElementById('confirmUsername').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('confirmUsernameError').style.display = 'none';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));
    modal.show();
}

// Real-time validation of username input
document.getElementById('confirmUsername').addEventListener('input', function() {
    const enteredUsername = this.value.trim();
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const errorDiv = document.getElementById('confirmUsernameError');
    
    if (enteredUsername === currentCustomerUsername) {
        confirmBtn.disabled = false;
        errorDiv.style.display = 'none';
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    } else {
        confirmBtn.disabled = true;
        if (enteredUsername.length > 0) {
            errorDiv.style.display = 'block';
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            errorDiv.style.display = 'none';
            this.classList.remove('is-invalid', 'is-valid');
        }
    }
});

// Handle actual deletion
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    const deleteBtn = this;
    const originalHtml = deleteBtn.innerHTML;
    
    // Show loading state
    deleteBtn.classList.add('loading');
    deleteBtn.disabled = true;
    
    // Send delete request
    fetch(`<?= base_url('admin/customers/delete/') ?>${currentCustomerId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        deleteBtn.classList.remove('loading');
        deleteBtn.innerHTML = originalHtml;
        
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal'));
        modal.hide();
        
        if (data.success) {
            // Show success message
            showToast('✅ ' + data.message, 'success');
            
            // Remove the row from table with animation
            const customerRow = document.querySelector(`button[onclick*="${currentCustomerId}"]`).closest('tr');
            customerRow.style.transition = 'opacity 0.3s ease';
            customerRow.style.opacity = '0';
            setTimeout(() => {
                customerRow.remove();
                
                // Show empty state if no customers left
                const tbody = document.querySelector('tbody');
                if (tbody.children.length === 0) {
                    location.reload();
                }
            }, 300);
            
        } else {
            showToast('❌ Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        deleteBtn.classList.remove('loading');
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalHtml;
        
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal'));
        modal.hide();
        
        console.error('Delete error:', error);
        showToast('❌ An error occurred while deleting the customer. Please try again.', 'error');
    });
});

function toggleCustomerStatus(customerId, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    const statusText = currentStatus ? 'deactivated' : 'activated';
    
    const confirmed = confirm(
        `Are you sure you want to ${action} this customer?\n\n` +
        `${currentStatus ? 
            '• Customer will not be able to login\n• Dashboard will be inaccessible\n• Data will be preserved' : 
            '• Customer will regain access\n• Dashboard will be accessible\n• All data will be restored'
        }`
    );
    
    if (!confirmed) return;
    
    // Find the status button and show loading state
    const statusBtn = event.target.closest('button');
    const originalHtml = statusBtn.innerHTML;
    statusBtn.classList.add('loading');
    statusBtn.disabled = true;
    
    // Send status update request
    fetch(`<?= base_url('admin/customers/deactivate/') ?>${customerId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        statusBtn.classList.remove('loading');
        statusBtn.disabled = false;
        
        if (data.success) {
            // Update button appearance
            const newStatus = data.new_status;
            statusBtn.className = `btn btn-sm btn-${newStatus ? 'secondary' : 'success'}`;
            statusBtn.title = `${newStatus ? 'Deactivate' : 'Activate'} Customer`;
            statusBtn.innerHTML = `<i class="fas fa-${newStatus ? 'user-slash' : 'user-check'}"></i>`;
            
            // Update status badge in the table
            const statusCell = statusBtn.closest('tr').querySelector('td:nth-last-child(2)');
            const badge = statusCell.querySelector('.badge');
            if (badge) {
                badge.className = `badge badge-${newStatus ? 'success' : 'danger'}`;
                badge.textContent = newStatus ? 'Active' : 'Inactive';
            }
            
            // Update the onclick attribute for next toggle
            statusBtn.setAttribute('onclick', `toggleCustomerStatus(${customerId}, ${newStatus})`);
            
            // Show success message
            showToast(data.message, 'success');
            
        } else {
            statusBtn.innerHTML = originalHtml;
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        statusBtn.classList.remove('loading');
        statusBtn.disabled = false;
        statusBtn.innerHTML = originalHtml;
        
        console.error('Status update error:', error);
        showToast('An error occurred while updating customer status.', 'error');
    });
}

function showToast(message, type) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 4000);
}
</script>
<?= $this->endSection() ?>