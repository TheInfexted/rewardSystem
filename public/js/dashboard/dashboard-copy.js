/**
 * Dashboard Copy Functionality
 * Handles copying username and other copyable elements
 */

class DashboardCopy {
    constructor() {
        this.initializeCopyFeatures();
        this.setupEventListeners();
    }

    /**
     * Initialize copy functionality
     */
    initializeCopyFeatures() {
        console.log('Dashboard Copy: Initializing copy features...');
        
        // Add copy icon to username if not already present
        this.enhanceUsernameDisplay();
        
        // Setup keyboard shortcuts
        this.setupKeyboardShortcuts();
    }

    /**
     * Enhance username display with copy functionality
     */
    enhanceUsernameDisplay() {
        const usernameElement = document.getElementById('customerUsername');
        
        if (usernameElement && !usernameElement.querySelector('.copy-icon')) {
            // Add copy icon if it doesn't exist
            const copyIcon = document.createElement('i');
            copyIcon.className = 'bi bi-copy copy-icon';
            copyIcon.id = 'copyIcon';
            usernameElement.appendChild(copyIcon);
        }
        
        // Add visual feedback classes
        if (usernameElement) {
            usernameElement.classList.add('copyable-username');
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Username click handler
        const usernameElement = document.getElementById('customerUsername');
        if (usernameElement) {
            usernameElement.addEventListener('click', (e) => {
                e.preventDefault();
                this.copyUsername();
            });

            // Add hover effects
            usernameElement.addEventListener('mouseenter', () => {
                this.showCopyHint();
            });

            usernameElement.addEventListener('mouseleave', () => {
                this.hideCopyHint();
            });
        }

        // Add click handlers for other copyable elements
        this.setupAdditionalCopyHandlers();
    }

    /**
     * Setup keyboard shortcuts for copy functionality
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+U or Cmd+U to copy username
            if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
                e.preventDefault();
                this.copyUsername();
            }
        });
    }

    /**
     * Copy username to clipboard
     */
    copyUsername() {
        const username = this.getUsername();
        
        if (!username) {
            console.error('Dashboard Copy: Username not found');
            return;
        }

        this.copyToClipboard(username, 'Username copied to clipboard!');
    }

    /**
     * Get username from config or DOM
     */
    getUsername() {
        // Try to get from global config first
        if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.username) {
            return dashboardPhpConfig.username;
        }

        // Fallback to DOM
        const usernameElement = document.getElementById('customerUsername');
        if (usernameElement) {
            return usernameElement.textContent.trim();
        }

        return null;
    }

    /**
     * Generic copy to clipboard function
     */
    copyToClipboard(text, successMessage = 'Copied to clipboard!') {
        if (navigator.clipboard && window.isSecureContext) {
            // Use modern clipboard API
            navigator.clipboard.writeText(text).then(() => {
                this.showCopySuccess(successMessage);
                this.animateCopyIcon();
            }).catch(err => {
                console.error('Dashboard Copy: Modern clipboard failed:', err);
                this.fallbackCopyTextToClipboard(text, successMessage);
            });
        } else {
            // Fallback for older browsers or non-HTTPS
            this.fallbackCopyTextToClipboard(text, successMessage);
        }
    }

    /**
     * Fallback copy method for older browsers
     */
    fallbackCopyTextToClipboard(text, successMessage) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Style to make it invisible
        textArea.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            opacity: 0;
            pointer-events: none;
            z-index: -1;
        `;
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                this.showCopySuccess(successMessage);
                this.animateCopyIcon();
            } else {
                this.showCopyError('Failed to copy');
            }
        } catch (err) {
            console.error('Dashboard Copy: Fallback copy failed:', err);
            this.showCopyError('Copy not supported');
        }
        
        document.body.removeChild(textArea);
    }

    /**
     * Show copy success toast
     */
    showCopySuccess(message) {
        this.showToast(message, 'success');
    }

    /**
     * Show copy error toast
     */
    showCopyError(message) {
        this.showToast(message, 'error');
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        // Remove existing toasts
        this.removeExistingToasts();

        const toast = document.createElement('div');
        toast.className = `copy-toast copy-toast-${type}`;
        toast.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto remove after delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 2000);
    }

    /**
     * Remove existing toasts
     */
    removeExistingToasts() {
        const existingToasts = document.querySelectorAll('.copy-toast');
        existingToasts.forEach(toast => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        });
    }

    /**
     * Animate copy icon
     */
    animateCopyIcon() {
        const copyIcon = document.getElementById('copyIcon');
        if (copyIcon) {
            copyIcon.style.transform = 'scale(1.2)';
            copyIcon.style.color = '#28a745';
            
            setTimeout(() => {
                copyIcon.style.transform = 'scale(1)';
                copyIcon.style.color = '';
            }, 200);
        }
    }

    /**
     * Show copy hint on hover
     */
    showCopyHint() {
        const usernameElement = document.getElementById('customerUsername');
        if (usernameElement) {
            usernameElement.style.opacity = '0.9';
        }
    }

    /**
     * Hide copy hint
     */
    hideCopyHint() {
        const usernameElement = document.getElementById('customerUsername');
        if (usernameElement) {
            usernameElement.style.opacity = '1';
        }
    }

    /**
     * Setup additional copy handlers for other elements
     */
    setupAdditionalCopyHandlers() {
        // Add copy functionality to customer ID if present
        const customerIdElement = document.querySelector('[data-copyable="customer-id"]');
        if (customerIdElement) {
            customerIdElement.addEventListener('click', () => {
                this.copyToClipboard(customerIdElement.textContent.trim(), 'Customer ID copied!');
            });
        }

        // Add copy functionality to any element with data-copyable attribute
        const copyableElements = document.querySelectorAll('[data-copyable]');
        copyableElements.forEach(element => {
            element.style.cursor = 'pointer';
            element.title = 'Click to copy';
            
            element.addEventListener('click', () => {
                const text = element.getAttribute('data-copy-text') || element.textContent.trim();
                const message = element.getAttribute('data-copy-message') || 'Copied to clipboard!';
                this.copyToClipboard(text, message);
            });
        });
    }
}

// Global functions for backwards compatibility
function copyUsername() {
    if (window.dashboardCopy) {
        window.dashboardCopy.copyUsername();
    }
}

function showCopyToast(message = 'Username copied to clipboard!') {
    if (window.dashboardCopy) {
        window.dashboardCopy.showCopySuccess(message);
    }
}

function animateCopyIcon() {
    if (window.dashboardCopy) {
        window.dashboardCopy.animateCopyIcon();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardCopy = new DashboardCopy();
    console.log('Dashboard Copy: Initialized successfully');
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardCopy;
}