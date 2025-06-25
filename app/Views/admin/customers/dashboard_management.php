<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('title') ?>Dashboard Theme Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Dashboard Themes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" onclick="showThemeStats()">
                            <i class="fas fa-chart-bar"></i> View Statistics
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Theme Statistics Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Customers</span>
                                    <span class="info-box-number" id="total-customers"><?= $stats['total_active_customers'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-palette"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Custom Themes</span>
                                    <span class="info-box-number" id="custom-themes"><?= $stats['custom_background_count'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-image"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Custom Backgrounds</span>
                                    <span class="info-box-number" id="custom-backgrounds"><?= $stats['custom_background_count'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Default Theme</span>
                                    <span class="info-box-number" id="default-theme"><?= $stats['default_theme_count'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Color Distribution Chart -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Color Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="colorChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Most Popular Colors</h5>
                                </div>
                                <div class="card-body">
                                    <div id="popular-colors">
                                        <?php foreach (array_slice($stats['color_distribution'], 0, 5) as $index => $colorData): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="color-swatch me-3" style="background-color: <?= $colorData['color'] ?>; width: 30px; height: 30px; border-radius: 5px; border: 1px solid #ddd;"></div>
                                            <div class="flex-grow-1">
                                                <strong><?= $colorData['color'] ?></strong>
                                                <div class="text-muted small"><?= $colorData['count'] ?> customers</div>
                                            </div>
                                            <div class="badge bg-primary"><?= round(($colorData['count'] / $stats['total_active_customers']) * 100, 1) ?>%</div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Dashboard Themes Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="dashboard-themes-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Username</th>
                                    <th>Background Color</th>
                                    <th>Background Image</th>
                                    <th>Last Updated</th>
                                    <th>Preview</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($customer['name']) ?></strong><br>
                                        <small class="text-muted">ID: <?= $customer['id'] ?></small>
                                    </td>
                                    <td><?= esc($customer['username']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="color-swatch me-2" style="background-color: <?= $customer['dashboard_bg_color'] ?>; width: 25px; height: 25px; border-radius: 3px; border: 1px solid #ddd;"></div>
                                            <code><?= $customer['dashboard_bg_color'] ?></code>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($customer['profile_background_image']): ?>
                                            <span class="badge bg-success">Custom Image</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= date('M d, Y H:i', strtotime($customer['updated_at'])) ?></small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewDashboard(<?= $customer['id'] ?>)">
                                            <i class="fas fa-eye"></i> Preview
                                        </button>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" onclick="editCustomerTheme(<?= $customer['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="resetCustomerTheme(<?= $customer['id'] ?>)">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </td>
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

<!-- Dashboard Preview Modal -->
<div class="modal fade" id="dashboardPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dashboard Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="dashboard-preview-container" style="max-width: 414px; margin: 0 auto;">
                    <!-- Dashboard preview will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Theme Edit Modal -->
<div class="modal fade" id="themeEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Dashboard Theme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="theme-edit-form">
                    <input type="hidden" id="edit-customer-id">
                    <div class="mb-3">
                        <label for="edit-bg-color" class="form-label">Background Color</label>
                        <input type="color" class="form-control form-control-color" id="edit-bg-color">
                    </div>
                    <div class="mb-3">
                        <label for="edit-bg-image" class="form-label">Background Image</label>
                        <input type="file" class="form-control" id="edit-bg-image" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveThemeChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize color distribution chart
const ctx = document.getElementById('colorChart').getContext('2d');
const colorChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($stats['color_distribution'], 'color')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($stats['color_distribution'], 'count')) ?>,
            backgroundColor: <?= json_encode(array_column($stats['color_distribution'], 'color')) ?>,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Preview dashboard function
function previewDashboard(customerId) {
    fetch(`/admin/customers/${customerId}/dashboard-preview`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('dashboard-preview-container').innerHTML = html;
            new bootstrap.Modal(document.getElementById('dashboardPreviewModal')).show();
        })
        .catch(error => {
            console.error('Error loading dashboard preview:', error);
            alert('Failed to load dashboard preview');
        });
}

// Edit customer theme
function editCustomerTheme(customerId) {
    document.getElementById('edit-customer-id').value = customerId;
    new bootstrap.Modal(document.getElementById('themeEditModal')).show();
}

// Reset customer theme
function resetCustomerTheme(customerId) {
    if (confirm('Are you sure you want to reset this customer\'s dashboard theme?')) {
        fetch(`/admin/customers/${customerId}/reset-dashboard`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Dashboard theme reset successfully');
                location.reload();
            } else {
                alert('Failed to reset theme: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Save theme changes
function saveThemeChanges() {
    const customerId = document.getElementById('edit-customer-id').value;
    const bgColor = document.getElementById('edit-bg-color').value;
    const bgImage = document.getElementById('edit-bg-image').files[0];
    
    const formData = new FormData();
    formData.append('dashboard_bg_color', bgColor);
    if (bgImage) {
        formData.append('background_image', bgImage);
    }
    
    fetch(`/admin/customers/${customerId}/update-dashboard`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Theme updated successfully');
            location.reload();
        } else {
            alert('Failed to update theme: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Initialize DataTable
$(document).ready(function() {
    $('#dashboard-themes-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[4, 'desc']] // Sort by last updated
    });
});
</script>
<?= $this->endSection() ?>