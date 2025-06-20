<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Management</h3>
                    <div class="card-tools">
                        <form method="get" class="form-inline">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search customers..." value="<?= esc($search) ?>">
                            <button type="submit" class="btn btn-primary ml-2">Search</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Points</th>
                                <th>Spin Tokens</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['id'] ?></td>
                                <td><?= esc($customer['username']) ?></td>
                                <td><?= esc($customer['name']) ?></td>
                                <td><?= $customer['points'] ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= $customer['spin_tokens'] ?? 0 ?> tokens
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/customers/tokens/' . $customer['id']) ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-coins"></i> Manage Tokens
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?= $pager->links() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>