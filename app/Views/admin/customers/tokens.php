<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Spin Tokens</h3>
                </div>
                <div class="card-body">
                    <div class="customer-info mb-4">
                        <h5><?= esc($customer['name']) ?> (<?= esc($customer['username']) ?>)</h5>
                        <p class="mb-1">Current Balance: 
                            <span class="badge badge-primary" id="currentBalance">
                                <?= $customer['spin_tokens'] ?? 0 ?> tokens
                            </span>
                        </p>
                    </div>
                    
                    <form id="tokenForm">
                        <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                        
                        <div class="form-group">
                            <label>Action</label>
                            <select name="action" class="form-control" required>
                                <option value="add">Add Tokens</option>
                                <option value="remove">Remove Tokens</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" 
                                   min="1" required placeholder="Number of tokens">
                        </div>
                        
                        <div class="form-group">
                            <label>Reason (Optional)</label>
                            <textarea name="reason" class="form-control" rows="2" 
                                      placeholder="Reason for this transaction"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Tokens</button>
                        <a href="<?= base_url('admin/customers') ?>" class="btn btn-secondary">Back</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Token History</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $record): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i', strtotime($record['created_at'])) ?></td>
                                    <td>
                                        <?php if ($record['action'] === 'add'): ?>
                                            <span class="badge badge-success">Added</span>
                                        <?php elseif ($record['action'] === 'remove'): ?>
                                            <span class="badge badge-danger">Removed</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Used</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $record['amount'] ?></td>
                                    <td><?= $record['balance_after'] ?></td>
                                    <td><?= esc($record['reason'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tokenForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= base_url('admin/customers/update-tokens') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('currentBalance').textContent = data.new_balance + ' tokens';
            location.reload(); // Reload to update history
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update tokens');
    });
});
</script>
<?= $this->endSection() ?>