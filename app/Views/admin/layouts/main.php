<!DOCTYPE html>
<html lang="<?= get_current_locale() ?>" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? t('Admin.dashboard.title') ?> - Fortune Wheel Admin</title>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <div class="admin-sidebar" id="adminSidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <h5><i class="bi bi-currency-dollar"></i> Fortune Wheel</h5>
                <small class="text-muted">Admin Panel</small>
            </div>

            <!-- Navigation -->
            <nav class="px-3">
                <!-- Dashboard Section -->
                <div class="nav-section">
                    <div class="nav-section-title"><?= t('Admin.nav.dashboard') ?></div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= uri_string() === 'admin' || uri_string() === 'admin/dashboard' ? 'active' : '' ?>" 
                               href="<?= base_url('admin/dashboard') ?>">
                                <i class="bi bi-speedometer2"></i>
                                <?= t('Admin.nav.dashboard') ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Content Management Section -->
                <div class="nav-section">
                    <div class="nav-section-title"><?= t('Admin.nav.content_management') ?></div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/landing-page') !== false ? 'active' : '' ?>" 
                               href="<?= base_url('admin/landing-page') ?>">
                                <i class="bi bi-file-earmark-text"></i>
                                <?= t('Admin.nav.landing_page') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/media') !== false ? 'active' : '' ?>" 
                               href="<?= base_url('admin/media') ?>">
                                <i class="bi bi-images"></i>
                                <?= t('Admin.nav.media_library') ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Wheel  Management Section -->
                <div class="nav-section">
                    <div class="nav-section-title"><?= t('Admin.nav.wheel_management') ?></div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/wheel') !== false ? 'active' : '' ?>" 
                               href="<?= base_url('admin/wheel') ?>">
                                <i class="bi bi-gear"></i>
                                <?= t('Admin.nav.wheel_items') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/bonus') !== false ? 'active' : '' ?>" 
                               href="<?= base_url('admin/bonus') ?>">
                                <i class="bi bi-gift"></i>
                                <?= t('Admin.nav.bonus_claims') ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Reward System Section -->
                <div class="nav-section">
                    <div class="nav-section-title"><?= t('Admin.nav.reward_system') ?></div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/reward-system') !== false ? 'active' : '' ?>" 
                            href="<?= base_url('admin/reward-system') ?>">
                                <i class="bi bi-badge-ad"></i>
                                <?= t('Admin.nav.reward_ads') ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Reports Section -->
                <div class="nav-section">
                    <div class="nav-section-title"><?= t('Admin.nav.reports_analytics') ?></div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/reports') !== false ? 'active' : '' ?>" 
                               href="<?= base_url('admin/reports') ?>">
                                <i class="bi bi-bar-chart"></i>
                                <?= t('Admin.nav.spin_reports') ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- System Section -->
                <div class="nav-section">
                    <div class="nav-section-title"><?= t('Admin.nav.system') ?></div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'admin/settings') !== false ? 'active' : '' ?>" 
                               href="<?= base_url('admin/settings') ?>">
                                <i class="bi bi-gear"></i>
                                <?= t('Admin.nav.settings') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('/') ?>" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i>
                                <?= t('Admin.nav.view_site') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                                <i class="bi bi-box-arrow-right"></i>
                                <?= t('Admin.nav.logout') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Navigation Bar -->
            <nav class="admin-header">
                <div class="d-flex align-items-center">
                    <!-- Mobile Menu Toggle -->
                    <button class="btn btn-outline-gold d-md-none me-3" type="button" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>

                    <!-- Page Title -->
                    <h4 class="text-gold mb-0"><?= $title ?? t('Admin.dashboard.title') ?></h4>
                </div>

                <!-- User Info -->
                <div class="dropdown">
                    <button class="btn btn-outline-gold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?=$_SESSION['user_name'];?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= base_url('admin/settings') ?>">
                                <i class="bi bi-gear"></i> <?= t('Admin.nav.settings') ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?= is_locale('en') ? 'active' : '' ?>" href="javascript:void(0);" onclick="translation('en');">
                                <i class="bi bi-translate me-1"></i>English
                                <?php if (is_locale('en')): ?>
                                    <i class="bi bi-check float-end"></i>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= is_locale('zh') ? 'active' : '' ?>" href="javascript:void(0);" onclick="translation('zh');">
                                <i class="bi bi-translate me-1"></i>简体中文
                                <?php if (is_locale('zh')): ?>
                                    <i class="bi bi-check float-end"></i>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                                <i class="bi bi-box-arrow-right"></i> <?= t('Admin.nav.logout') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="admin-content">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= session()->getFlashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <?= session()->getFlashdata('info') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Main Content Area -->
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Admin JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.querySelector('[onclick="toggleSidebar()"]');
        
        if (window.innerWidth <= 768 && 
            sidebar.classList.contains('show') &&
            !sidebar.contains(event.target) && 
            !toggleBtn?.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        });
    }, 5000);

    // Add fade-in animation to content
    const content = document.querySelector('.admin-content');
    if (content) {
        content.classList.add('fade-in');
    }

});

// Mobile sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('show');
}

// Translation function
function translation(vlang) {
    // Show loading indicator
    const dropdownButton = document.querySelector('.dropdown-toggle');
    const originalHtml = dropdownButton.innerHTML;
    dropdownButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    dropdownButton.disabled = true;

    $.ajax({
        url: "/translate/" + vlang,
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.code == 1) {
                // Show success message before reload
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
                successDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
                successDiv.innerHTML = `
                    <i class="bi bi-check-circle"></i> Language changed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(successDiv);
                
                // Reload after a short delay
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                // Restore button state
                dropdownButton.innerHTML = originalHtml;
                dropdownButton.disabled = false;
                
                // Show error message
                alert('Failed to change language. Please try again.');
            }
        },
        error: function() {
            // Restore button state
            dropdownButton.innerHTML = originalHtml;
            dropdownButton.disabled = false;
            
            // Show error message
            alert('Network error. Please try again.');
        }
    });
}

// Add active language indicator style
const style = document.createElement('style');
style.textContent = `
    .dropdown-item.active {
        background-color: rgba(255, 215, 0, 0.1);
        color: #ffd700;
    }
    .dropdown-item.active:hover {
        background-color: rgba(255, 215, 0, 0.2);
        color: #ffd700;
    }
    .dropdown-header {
        font-size: 0.875rem;
        color: #6c757d;
        padding: 0.5rem 1rem;
    }
`;
document.head.appendChild(style);
</script>

<!-- Additional JavaScript for specific pages -->
<?= $this->renderSection('scripts') ?>
</body>
</html>