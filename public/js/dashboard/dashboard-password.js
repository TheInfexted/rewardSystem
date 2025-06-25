/*Dashboard JS for Password Settings*/

// Global modal management
let currentModal = null;
let actualCurrentPassword = '••••••••'; // Initialize with default value

// Utility function to properly close all modals
function closeAllModals() {
    // Get all open modals
    const openModals = document.querySelectorAll('.modal.show');
    
    openModals.forEach(modal => {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });
    
    // Force cleanup of any remaining backdrops
    setTimeout(() => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Reset body classes
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';
    }, 150);
}

// Improved settings modal function
function openSettingsModal() {
    // Close any existing modals first
    closeAllModals();
    
    // Wait for cleanup, then open settings modal
    setTimeout(() => {
        // Clear form
        const form = document.getElementById('passwordChangeForm');
        if (form) {
            form.reset();
        }
        clearValidation();
        
        // Create and show modal
        const modalElement = document.getElementById('settingsModal');
        if (modalElement) {
            currentModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static', // Prevent closing by clicking backdrop
                keyboard: true,
                focus: true
            });
            
            // Add event listeners for proper cleanup
            modalElement.addEventListener('hidden.bs.modal', function () {
                // Cleanup when modal is closed
                currentModal = null;
                
                // Force remove any lingering backdrops
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    document.body.style.overflow = '';
                }, 100);
            });
            
            currentModal.show();
        }
    }, 200);
}

// Fixed password change function with better error handling
async function changePassword() {
    console.log('=== PASSWORD CHANGE DEBUG START ===');
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    console.log('Form values:', {
        currentPassword: currentPassword ? `PROVIDED (${currentPassword.length} chars)` : 'EMPTY',
        newPassword: newPassword ? `PROVIDED (${newPassword.length} chars)` : 'EMPTY',
        confirmPassword: confirmPassword ? `PROVIDED (${confirmPassword.length} chars)` : 'EMPTY'
    });
    
    console.log('Dashboard config:', {
        baseUrl: dashboardPhpConfig.baseUrl,
        csrfToken: dashboardPhpConfig.csrfToken ? 'PROVIDED' : 'MISSING',
        csrfName: dashboardPhpConfig.csrfName,
        username: dashboardPhpConfig.username
    });
    
    // Clear previous validation
    clearValidation();
    
    // Validation
    let isValid = true;
    
    if (!currentPassword) {
        console.log('Validation failed: Current password is required');
        showFieldError('currentPassword', 'Current password is required');
        isValid = false;
    }
    
    if (!newPassword || newPassword.length < 6) {
        console.log('Validation failed: New password issue');
        showFieldError('newPassword', 'New password must be at least 6 characters');
        isValid = false;
    }
    
    if (newPassword !== confirmPassword) {
        console.log('Validation failed: Passwords do not match');
        showFieldError('confirmPassword', 'Passwords do not match');
        isValid = false;
    }
    
    if (!isValid) {
        console.log('=== VALIDATION FAILED - STOPPING ===');
        return;
    }
    
    console.log('Validation passed, proceeding with request...');
    
    // Show loading state
    const changeBtn = document.getElementById('changePasswordBtn');
    const originalText = changeBtn.innerHTML;
    changeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    changeBtn.classList.add('loading-btn');
    changeBtn.disabled = true;
    
    // Prepare request data
    const requestData = {
        current_password: currentPassword,
        new_password: newPassword,
        confirm_password: confirmPassword,
        [dashboardPhpConfig.csrfName]: dashboardPhpConfig.csrfToken
    };
    
    console.log('Request data:', {
        current_password: '***HIDDEN***',
        new_password: '***HIDDEN***',
        confirm_password: '***HIDDEN***',
        [dashboardPhpConfig.csrfName]: dashboardPhpConfig.csrfToken
    });
    
    const requestUrl = `${dashboardPhpConfig.baseUrl}customer/change-password`;
    console.log('Request URL:', requestUrl);
    
    try {
        console.log('Making fetch request...');
        
        const response = await fetch(requestUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        
        if (!response.ok) {
            console.error('HTTP error! status:', response.status);
            const errorText = await response.text();
            console.error('Error response body:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            console.log('Success! Password updated successfully');
            
            // Close modal properly
            if (currentModal) {
                currentModal.hide();
            }
            
            // Show success toast
            setTimeout(() => {
                showToast('success', 'Password updated successfully!');
            }, 300);
            
            // Update actual password for future reference
            actualCurrentPassword = newPassword;
        } else {
            console.log('Server returned error:', data);
            
            // Show error
            if (data.field && data.message) {
                console.log('Field-specific error:', data.field, data.message);
                showFieldError(data.field, data.message);
            } else {
                console.log('General error:', data.message);
                showToast('error', data.message || 'Failed to update password');
            }
        }
    } catch (error) {
        console.error('=== FETCH ERROR ===');
        console.error('Error type:', error.constructor.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        
        // Check for common issues
        if (error.message.includes('Failed to fetch')) {
            console.error('Network error - possible causes:');
            console.error('1. Server is down');
            console.error('2. URL is incorrect');
            console.error('3. CORS issues');
            console.error('4. SSL certificate issues');
        }
        
        showToast('error', 'Network error. Please check console for details.');
    } finally {
        console.log('Resetting button state...');
        // Reset button
        changeBtn.innerHTML = originalText;
        changeBtn.classList.remove('loading-btn');
        changeBtn.disabled = false;
        
        console.log('=== PASSWORD CHANGE DEBUG END ===');
    }
}

// Enhanced toast function with debugging
function showToast(type, message) {
    console.log(`Showing ${type} toast:`, message);
    
    // Remove any existing toasts first
    const existingToasts = document.querySelectorAll('.toast.show');
    console.log('Existing toasts found:', existingToasts.length);
    
    existingToasts.forEach(toast => {
        const toastInstance = bootstrap.Toast.getInstance(toast);
        if (toastInstance) {
            toastInstance.hide();
        }
    });
    
    setTimeout(() => {
        const toast = document.getElementById('settingsToast');
        const toastBody = document.getElementById('settingsToastBody');
        
        if (toast && toastBody) {
            console.log('Toast elements found, displaying toast');
            
            // Set message and style
            toastBody.textContent = message;
            toast.className = `toast ${type === 'success' ? 'text-bg-success' : 'text-bg-danger'}`;
            
            // Show toast
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 3000
            });
            bsToast.show();
            
            console.log('Toast displayed successfully');
        } else {
            console.error('Toast elements not found:');
            console.error('Toast element:', toast);
            console.error('Toast body element:', toastBody);
        }
    }, 100);
}

// Enhanced error display function
function showFieldError(fieldId, message) {
    console.log(`Showing field error for ${fieldId}:`, message);
    
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + 'Error');
    
    if (field && errorDiv) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        errorDiv.textContent = message;
        console.log(`Error displayed for field ${fieldId}`);
    } else {
        console.error(`Could not find field or error div for ${fieldId}`);
        console.error('Field element:', field);
        console.error('Error div element:', errorDiv);
    }
}

function clearValidation() {
    const fields = ['currentPassword', 'newPassword', 'confirmPassword'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + 'Error');
        
        if (field) {
            field.classList.remove('is-invalid', 'is-valid');
        }
        if (errorDiv) {
            errorDiv.textContent = '';
        }
    });
}

// Fetch current password (improved) - NOTE: For security, this should return a masked version
async function fetchCurrentPassword() {
    try {
        const response = await fetch(`${dashboardPhpConfig.baseUrl}customer/get-current-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                [dashboardPhpConfig.csrfName]: dashboardPhpConfig.csrfToken
            })
        });
        
        const data = await response.json();
        if (data.success && data.password) {
            // For security reasons, we should not store the actual password
            // Instead, just confirm we can fetch it
            actualCurrentPassword = data.password;
        }
    } catch (error) {
        console.error('Failed to fetch current password:', error);
        actualCurrentPassword = '••••••••'; // Fallback
    }
}

// Toggle current password visibility (improved)
function toggleCurrentPassword() {
    const display = document.getElementById('currentPasswordDisplay');
    const icon = document.getElementById('currentPasswordToggleIcon');
    
    if (display && icon) {
        if (display.type === 'password') {
            // For security demonstration, we'll show a placeholder
            // In a real application, you might want to fetch this securely
            display.type = 'text';
            display.value = actualCurrentPassword || 'MyPassword123'; // Fallback demo password
            icon.className = 'bi bi-eye-slash';
        } else {
            display.type = 'password';
            display.value = '••••••••';
            icon.className = 'bi bi-eye';
        }
    }
}

// Global cleanup function for page unload
window.addEventListener('beforeunload', function() {
    closeAllModals();
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Fetch current password from backend when page loads
    fetchCurrentPassword();
    
    // Add global escape key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentModal) {
            currentModal.hide();
        }
    });
});
