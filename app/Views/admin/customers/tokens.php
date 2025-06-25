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
                    <!-- Current Balance - Permanent Display -->
                    <div class="balance-display mb-4" id="balanceDisplay">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-1 text-dark">
                                    <i class="fas fa-coins text-warning"></i>
                                    Current Balance: <span id="currentBalance"><?= $customer['spin_tokens'] ?></span> Tokens
                                </h5>
                                <small class="text-dark">Customer: <?= esc($customer['username']) ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Container for Messages -->
                    <div id="alertContainer"></div>

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
                        <div class="transaction-list" id="transactionList">
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

#balanceDisplay {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border: 2px solid #b6d7ff;
    border-radius: 10px;
    padding: 15px;
    position: sticky;
    top: 0;
    z-index: 10;
    margin-bottom: 1rem !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-container {
    margin-bottom: 1rem;
}

.balance-update-animation {
    animation: balanceUpdate 0.8s ease-in-out;
}

@keyframes balanceUpdate {
    0% { 
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        transform: scale(1);
    }
    50% { 
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        transform: scale(1.02);
    }
    100% { 
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        transform: scale(1);
    }
}
</style>

<script>
async function updateTokens(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('submitBtn');
    const currentBalanceSpan = document.getElementById('currentBalance');
    const balanceDisplay = document.getElementById('balanceDisplay');
    
    // Get form data
    const formData = new FormData(form);
    formData.append('customer_id', <?= $customer['id'] ?>);
    
    // Basic validation
    const action = formData.get('action');
    const amount = parseInt(formData.get('amount'));
    
    if (!action) {
        showAlert('danger', 'Please select an action');
        return;
    }
    
    if (!amount || amount <= 0) {
        showAlert('danger', 'Please enter a valid amount');
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    try {
        const response = await fetch('<?= base_url('admin/customers/updateTokens') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            },
            body: formData
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server did not return JSON response');
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Update balance display with animation
            currentBalanceSpan.textContent = data.new_balance;
            balanceDisplay.classList.add('balance-update-animation');
            
            // Remove animation class after animation completes
            setTimeout(() => {
                balanceDisplay.classList.remove('balance-update-animation');
            }, 800);
            
            // Update form validation with new balance
            updateFormValidation();
            
            // Show success message
            showAlert('success', data.message);
            
            // Reset form
            form.reset();
            
            // Remove any existing warnings after reset
            const existingWarning = document.querySelector('.remove-warning');
            if (existingWarning) existingWarning.remove();
            
            // Add new transaction to history without full page reload
            addTransactionToHistory(action, amount, data.new_balance, formData.get('reason'));
            
        } else {
            showAlert('danger', data.message || 'Failed to update tokens');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'Network error: ' + error.message);
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Tokens';
    }
}

function showAlert(type, message) {
    // Remove any existing alerts first
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = '';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show temporary-alert`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.remove()"></button>
    `;
    
    // Insert into alert container (NOT the balance display)
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds (only temporary alerts)
    setTimeout(() => {
        if (alertDiv.parentNode && alertDiv.classList.contains('temporary-alert')) {
            alertDiv.remove();
        }
    }, 5000);
}

function addTransactionToHistory(action, amount, newBalance, reason) {
    const transactionList = document.getElementById('transactionList');
    const currentBalance = getCurrentBalance(); // Use dynamic balance
    const oldBalance = action === 'add' ? (newBalance - amount) : (newBalance + amount);
    
    // Create new transaction item
    const transactionItem = document.createElement('div');
    transactionItem.className = 'transaction-item mb-3 p-3 border rounded';
    transactionItem.style.animation = 'fadeIn 0.5s ease-in-out';
    
    const badgeClass = action === 'add' ? 'success' : 'danger';
    const amountPrefix = action === 'add' ? '+' : '-';
    const reasonHtml = reason ? `<div class="reason mt-1"><small class="text-info">${reason}</small></div>` : '';
    
    transactionItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="badge badge-${badgeClass} mb-1">${action.charAt(0).toUpperCase() + action.slice(1)}</span>
                <div class="amount">${amountPrefix}${amount} tokens</div>
                <small class="text-muted">Balance: ${oldBalance} → ${newBalance}</small>
                ${reasonHtml}
            </div>
            <small class="text-muted">Just now</small>
        </div>
    `;
    
    // Insert at the beginning of the transaction list
    if (transactionList.firstChild) {
        transactionList.insertBefore(transactionItem, transactionList.firstChild);
    } else {
        // If no transactions exist, replace the "no history" message
        const parentContainer = transactionList.parentNode;
        parentContainer.innerHTML = '<div class="transaction-list" id="transactionList"></div>';
        document.getElementById('transactionList').appendChild(transactionItem);
    }
    
    // Remove oldest transaction if we have too many (keep only 10 visible)
    const transactions = transactionList.children;
    if (transactions.length > 10) {
        transactionList.removeChild(transactions[transactions.length - 1]);
    }
}

// Function to get current balance dynamically
function getCurrentBalance() {
    return parseInt(document.getElementById('currentBalance').textContent) || 0;
}

// Function to update form validation based on current balance
function updateFormValidation() {
    const actionSelect = document.getElementById('action');
    const amountInput = document.getElementById('amount');
    const currentBalance = getCurrentBalance();
    
    if (actionSelect.value === 'remove') {
        amountInput.max = currentBalance;
        amountInput.placeholder = `Enter amount (max: ${currentBalance})`;
        
        // Update warning
        const existingWarning = document.querySelector('.remove-warning');
        if (existingWarning) existingWarning.remove();
        
        if (currentBalance > 0) {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'alert alert-warning remove-warning mt-2';
            warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Maximum tokens that can be removed: ${currentBalance}`;
            actionSelect.parentElement.appendChild(warningDiv);
        }
    } else {
        amountInput.max = 10000;
        amountInput.placeholder = 'Enter amount';
        
        // Remove warning
        const existingWarning = document.querySelector('.remove-warning');
        if (existingWarning) existingWarning.remove();
    }
}

// Enhanced form validation
document.addEventListener('DOMContentLoaded', function() {
    const actionSelect = document.getElementById('action');
    const amountInput = document.getElementById('amount');
    
    actionSelect.addEventListener('change', function() {
        updateFormValidation();
    });
    
    amountInput.addEventListener('input', function() {
        const action = actionSelect.value;
        const amount = parseInt(this.value);
        const currentBalance = getCurrentBalance(); // Get real-time balance
        
        if (action === 'remove' && amount > currentBalance) {
            this.setCustomValidity(`Cannot remove more than current balance (${currentBalance})`);
        } else if (amount <= 0) {
            this.setCustomValidity('Amount must be greater than 0');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Add fade-in animation for existing transactions
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
});
</script>
<?= $this->endSection() ?>