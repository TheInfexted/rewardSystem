/**
 * CHECK-IN SETTINGS JAVASCRIPT
 * For: public/js/checkin-settings.js
 */

class CheckinSettingsManager {
    constructor() {
        this.baseUrl = window.location.origin;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialData();
        this.startAutoRefresh();
    }

    bindEvents() {
        // Settings form submission
        const settingsForm = document.getElementById('checkinSettingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateSettings();
            });
        }

        // Quick action buttons
        document.querySelectorAll('[onclick*="resetCheckinProgress"]').forEach(btn => {
            btn.removeAttribute('onclick');
            const resetType = btn.getAttribute('data-reset-type') || 
                            btn.textContent.toLowerCase().includes('streak') ? 'current_streak' :
                            btn.textContent.toLowerCase().includes('monthly') ? 'monthly_progress' : 'all_history';
            btn.addEventListener('click', () => this.resetProgress(resetType));
        });

        // Form input validation
        this.setupFormValidation();
    }

    setupFormValidation() {
        const form = document.getElementById('checkinSettingsForm');
        if (!form) return;

        const inputs = form.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            input.addEventListener('input', this.validateInput.bind(this));
            input.addEventListener('blur', this.validateInput.bind(this));
        });
    }

    validateInput(event) {
        const input = event.target;
        const value = parseFloat(input.value);
        const min = parseFloat(input.getAttribute('min'));
        const max = parseFloat(input.getAttribute('max'));

        // Remove existing validation classes
        input.classList.remove('is-valid', 'is-invalid');

        if (isNaN(value) || value < min || value > max) {
            input.classList.add('is-invalid');
            this.showInputError(input, `Value must be between ${min} and ${max}`);
        } else {
            input.classList.add('is-valid');
            this.hideInputError(input);
        }
    }

    showInputError(input, message) {
        let errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            input.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    hideInputError(input) {
        const errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    async updateSettings() {
        const form = document.getElementById('checkinSettingsForm');
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Validate all inputs first
        const inputs = form.querySelectorAll('input[type="number"]');
        let isValid = true;
        
        inputs.forEach(input => {
            this.validateInput({ target: input });
            if (input.classList.contains('is-invalid')) {
                isValid = false;
            }
        });

        if (!isValid) {
            this.showToast('error', 'Please correct the validation errors before submitting.');
            return;
        }

        // Show loading state
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(`${this.baseUrl}/admin/customers/updateCheckinSettings`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', data.message);
                this.logActivity('Settings Updated', 'Check-in settings have been updated successfully');
            } else {
                this.showToast('error', data.message || 'Failed to update settings');
                if (data.errors) {
                    this.displayValidationErrors(data.errors);
                }
            }
        } catch (error) {
            console.error('Settings update error:', error);
            this.showToast('error', 'An error occurred while updating settings');
        } finally {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async resetProgress(resetType) {
        const messages = {
            'current_streak': {
                title: 'Reset Current Streak',
                message: 'Are you sure you want to reset the current streak? This will set the streak back to 1.',
                confirmText: 'Reset Streak'
            },
            'monthly_progress': {
                title: 'Reset Monthly Progress',
                message: 'Are you sure you want to reset this month\'s progress? This will delete all check-ins for the current month.',
                confirmText: 'Reset Month'
            },
            'all_history': {
                title: 'Reset All History',
                message: 'Are you sure you want to reset ALL check-in history? This action cannot be undone and will delete all check-in records!',
                confirmText: 'Delete All'
            }
        };

        const config = messages[resetType];
        if (!config) return;

        // Custom confirmation dialog
        if (!await this.showConfirmDialog(config.title, config.message, config.confirmText)) {
            return;
        }

        const customerId = this.getCustomerIdFromUrl();
        if (!customerId) {
            this.showToast('error', 'Customer ID not found');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('reset_type', resetType);

            const response = await fetch(`${this.baseUrl}/admin/customers/resetCheckinProgress/${customerId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', data.message);
                this.logActivity('Progress Reset', `${config.title} completed successfully`);
                
                // Reload page after a short delay to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showToast('error', data.message || 'Failed to reset progress');
            }
        } catch (error) {
            console.error('Reset progress error:', error);
            this.showToast('error', 'An error occurred while resetting progress');
        }
    }

    async showConfirmDialog(title, message, confirmText) {
        return new Promise((resolve) => {
            // Create custom modal dialog
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-light">
                        <div class="modal-header border-warning">
                            <h5 class="modal-title text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>${title}
                            </h5>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger confirm-btn">${confirmText}</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const bootstrapModal = new bootstrap.Modal(modal);
            
            modal.querySelector('.confirm-btn').addEventListener('click', () => {
                bootstrapModal.hide();
                resolve(true);
            });

            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
                resolve(false);
            });

            bootstrapModal.show();
        });
    }

    displayValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                this.showInputError(input, errors[field]);
            }
        });
    }

    showToast(type, message, duration = 5000) {
        // Remove existing toasts
        document.querySelectorAll('.custom-toast').forEach(toast => toast.remove());

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed custom-toast`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 350px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);';
        
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2 fs-5"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 150);
            }
        }, duration);
    }

    getCustomerIdFromUrl() {
        const pathSegments = window.location.pathname.split('/');
        const checkinIndex = pathSegments.indexOf('checkin-settings');
        return checkinIndex !== -1 && pathSegments[checkinIndex + 1] ? pathSegments[checkinIndex + 1] : null;
    }

    logActivity(action, details) {
        const timestamp = new Date().toLocaleString();
        console.log(`[${timestamp}] Admin Action: ${action} - ${details}`);
        
        // Could be extended to send to server for admin activity logging
        // this.sendActivityLog(action, details);
    }

    loadInitialData() {
        // Add any initial data loading here
        this.updateLastActivityTime();
        this.initializeCharts();
    }

    updateLastActivityTime() {
        const timeElements = document.querySelectorAll('[data-time-ago]');
        timeElements.forEach(element => {
            const timestamp = element.getAttribute('data-time-ago');
            if (timestamp) {
                element.textContent = this.formatTimeAgo(new Date(timestamp));
            }
        });
    }

    formatTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
        
        return date.toLocaleDateString();
    }

    initializeCharts() {
        // Initialize any charts or visualizations
        this.createWeeklyProgressChart();
        this.createStreakChart();
    }

    createWeeklyProgressChart() {
        const chartContainer = document.getElementById('weeklyProgressChart');
        if (!chartContainer) return;

        // Simple progress visualization
        const weeklyCards = document.querySelectorAll('.weekly-day-card');
        weeklyCards.forEach((card, index) => {
            if (card.classList.contains('checked-in')) {
                card.style.animationDelay = `${index * 100}ms`;
                card.classList.add('fade-in');
            }
        });
    }

    createStreakChart() {
        // Could implement a streak visualization chart here
        const streakElements = document.querySelectorAll('.streak-day');
        streakElements.forEach(element => {
            const streakValue = parseInt(element.textContent.match(/\d+/)?.[0] || 0);
            if (streakValue > 3) {
                element.style.color = '#ffc107';
                element.innerHTML = `<i class="fas fa-fire me-1"></i>${element.textContent}`;
            }
        });
    }

    startAutoRefresh() {
        // Auto-refresh data every 5 minutes
        setInterval(() => {
            this.updateLastActivityTime();
        }, 300000);
    }

    // Utility method for exporting data
    exportCheckinData() {
        const customerId = this.getCustomerIdFromUrl();
        if (!customerId) return;

        const data = this.gatherCheckinData();
        this.downloadCSV(data, `customer_${customerId}_checkin_data.csv`);
    }

    gatherCheckinData() {
        const rows = [];
        const table = document.querySelector('.table tbody');
        if (!table) return rows;

        table.querySelectorAll('tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 6) {
                rows.push({
                    date: cells[0].textContent.trim(),
                    day: cells[1].textContent.trim(),
                    points: cells[2].textContent.trim(),
                    streak: cells[3].textContent.trim(),
                    type: cells[4].textContent.trim(),
                    time: cells[5].textContent.trim()
                });
            }
        });

        return rows;
    }

    downloadCSV(data, filename) {
        if (!data.length) return;

        const headers = Object.keys(data[0]);
        const csvContent = [
            headers.join(','),
            ...data.map(row => headers.map(header => `"${row[header]}"`).join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        window.URL.revokeObjectURL(url);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.checkinManager = new CheckinSettingsManager();
});

// Global functions for backward compatibility
function updateCheckinSettings() {
    if (window.checkinManager) {
        window.checkinManager.updateSettings();
    }
}

function resetCheckinProgress(resetType) {
    if (window.checkinManager) {
        window.checkinManager.resetProgress(resetType);
    }
}

function exportData() {
    if (window.checkinManager) {
        window.checkinManager.exportCheckinData();
    }
}