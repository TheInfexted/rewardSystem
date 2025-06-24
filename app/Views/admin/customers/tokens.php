<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Manage Spin Tokens - <?= esc($customer['username']) ?></h4>
                    <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    <!-- Current Balance -->
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-1">
                                    <i class="fas fa-coins text-warning"></i>
                                    Current Balance: <span id="currentBalance"><?= $customer['spin_tokens'] ?></span> Tokens
                                </h5>
                                <small class="text-muted">Customer: <?= esc($customer['username']) ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Token Management Form -->
                    <form id="tokenForm" onsubmit="updateTokens(event)">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="action" class="form-label">Action</label>
                                    <select class="form-control" id="action" name="action" required>
                                        <option value="">Select Action</option>
                                        <option value="add">Add Tokens</option>
                                        <option value="remove">Remove Tokens</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           min="1" max="10000" required placeholder="Enter amount">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="reason" class="form-label">Reason (Optional)</label>
                            <input type="text" class="form-control" id="reason" name="reason" 
                                   placeholder="Enter reason for this transaction">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Update Tokens
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Transaction History -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($history)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <p>No transaction history available</p>
                        </div>
                    <?php else: ?>
                        <div class="transaction-list">
                            <?php foreach ($history as $transaction): ?>
                                <div class="transaction-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge badge-<?= $transaction['action'] === 'add' ? 'success' : 'danger' ?> mb-1">
                                                <?= ucfirst($transaction['action']) ?>
                                            </span>
                                            <div class="amount">
                                                <?= $transaction['action'] === 'add' ? '+' : '-' ?><?= $transaction['amount'] ?> tokens
                                            </div>
                                            <small class="text-muted">
                                                Balance: <?= $transaction['old_balance'] ?> → <?= $transaction['new_balance'] ?>
                                            </small>
                                            <?php if (!empty($transaction['reason'])): ?>
                                                <div class="reason mt-1">
                                                    <small class="text-info"><?= esc($transaction['reason']) ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('M j, H:i', strtotime($transaction['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.transaction-item {
    background: #f8f9fa;
    border-left: 4px solid #dee2e6;
}

.transaction-item .badge-success {
    background-color: #28a745;
}

.transaction-item .badge-danger {
    background-color: #dc3545;
}

.amount {
    font-weight: 600;
    font-size: 1.1rem;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-control:focus {
    border-color: #ffd700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.btn-primary {
    background-color: #ffd700;
    border-color: #ffd700;
    color: #000;
}

.btn-primary:hover {
    background-color: #ffed4e;
    border-color: #ffed4e;
    color: #000;
}
</style>

<script>
async function updateTokens(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('submitBtn');
    const currentBalanceSpan = document.getElementById('currentBalance');
    
    // Get form data
    const formData = new FormData(form);
    formData.append('customer_id', <?= $customer['id'] ?>);
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    try {
        const response = await fetch('<?= base_url('admin/customers/updateTokens') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update balance display
            currentBalanceSpan.textContent = data.new_balance;
            
            // Show success message
            showAlert('success', data.message);
            
            // Reset form
            form.reset();
            
            // Reload page to show updated history
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while updating tokens');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Tokens';
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const actionSelect = document.getElementById('action');
    const amountInput = document.getElementById('amount');
    const currentBalance = parseInt(document.getElementById('currentBalance').textContent);
    
    actionSelect.addEventListener('change', function() {
        if (this.value === 'remove') {
            amountInput.max = currentBalance;
            amountInput.placeholder = `Enter amount (max: ${currentBalance})`;
        } else {
            amountInput.max = 10000;
            amountInput.placeholder = 'Enter amount';
        }
    });
    
    amountInput.addEventListener('input', function() {
        const action = actionSelect.value;
        const amount = parseInt(this.value);
        
        if (action === 'remove' && amount > currentBalance) {
            this.setCustomValidity(`Cannot remove more than current balance (${currentBalance})`);
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
<?= $this->endSection() ?>