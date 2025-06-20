/**
 * Dashboard Color Picker Module
 * Handles dashboard background color customization
 */

// Global color picker state
let currentDashboardColor = '#ffffff';
let colorPickerOpen = false;

// Initialize color picker when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load saved color from PHP config
    if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.dashboardBgColor) {
        currentDashboardColor = dashboardPhpConfig.dashboardBgColor;
        applyBackgroundColor(currentDashboardColor);
    }
    
    // Add overlay element for modal backdrop
    const overlay = document.createElement('div');
    overlay.className = 'color-picker-overlay';
    overlay.id = 'colorPickerOverlay';
    overlay.onclick = closeColorPicker;
    document.body.appendChild(overlay);
});

// Toggle color picker
function toggleColorPicker() {
    if (colorPickerOpen) {
        closeColorPicker();
    } else {
        openColorPicker();
    }
}

// Open color picker
function openColorPicker() {
    const panel = document.getElementById('colorPickerPanel');
    const overlay = document.getElementById('colorPickerOverlay');
    
    if (panel && overlay) {
        panel.classList.add('active');
        overlay.classList.add('active');
        colorPickerOpen = true;
        
        // Update custom color input
        const customInput = document.getElementById('customColorInput');
        if (customInput) {
            customInput.value = currentDashboardColor;
        }
    }
}

// Close color picker
function closeColorPicker() {
    const panel = document.getElementById('colorPickerPanel');
    const overlay = document.getElementById('colorPickerOverlay');
    
    if (panel && overlay) {
        panel.classList.remove('active');
        overlay.classList.remove('active');
        colorPickerOpen = false;
    }
}

// Set dashboard color
function setDashboardColor(color) {
    applyBackgroundColor(color);
    saveColorPreference(color);
    closeColorPicker();
    
    // Show success message if available
    if (typeof DashboardUtils !== 'undefined' && DashboardUtils.showToast) {
        DashboardUtils.showToast('Background color updated!', 'success');
    }
}

// Apply custom color from input
function applyCustomColor() {
    const customInput = document.getElementById('customColorInput');
    if (customInput) {
        setDashboardColor(customInput.value);
    }
}

// Apply background color to page
function applyBackgroundColor(color) {
    // Apply to body
    document.body.style.backgroundColor = color;
    
    // Apply to dashboard container
    const dashboardContainer = document.querySelector('.dashboard-container');
    if (dashboardContainer) {
        dashboardContainer.style.backgroundColor = color;
    }
    
    // Apply to main content area if exists
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.style.backgroundColor = color;
    }
    
    // Update FAB appearance
    updateFabAppearance(color);
    
    // Store current color
    currentDashboardColor = color;
}

// Update FAB button appearance based on background
function updateFabAppearance(bgColor) {
    const fab = document.querySelector('.fab-button');
    if (!fab) return;
    
    // Calculate brightness
    const rgb = hexToRgb(bgColor);
    const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
    
    // Use contrasting color
    if (brightness > 128) {
        fab.style.backgroundColor = '#667eea';
        fab.style.color = '#ffffff';
    } else {
        fab.style.backgroundColor = '#ffffff';
        fab.style.color = '#667eea';
    }
}

// Convert hex to RGB
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : { r: 255, g: 255, b: 255 };
}

// Save color preference to database
function saveColorPreference(color) {
    if (typeof DashboardConfig === 'undefined') {
        console.error('DashboardConfig not loaded');
        return;
    }
    
    const formData = new FormData();
    formData.append('color', color);
    formData.append(DashboardConfig.csrf.name, DashboardConfig.csrf.token);
    
    fetch(DashboardConfig.baseUrl + 'customer/updateDashboardColor', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to save color:', data.message);
            if (typeof DashboardUtils !== 'undefined' && DashboardUtils.showToast) {
                DashboardUtils.showToast('Color applied but not saved', 'warning');
            }
        }
    })
    .catch(error => {
        console.error('Error saving color:', error);
        if (typeof DashboardUtils !== 'undefined' && DashboardUtils.showToast) {
            DashboardUtils.showToast('Color applied but not saved', 'warning');
        }
    });
}