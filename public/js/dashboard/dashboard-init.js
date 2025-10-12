/**
 * Dashboard Initialization and Event Handlers
 * Initialize the dashboard and set up all event listeners
 */

// Global instances
let fortuneWheelInstance;

// Global functions for backward compatibility
function performCheckin() {
    DashboardCore.performCheckin();
}

function changeBackground(bgType) {
    DashboardCore.changeBackground(bgType);
}

// NEW: Global function for image upload
function uploadBackgroundImage(input) {
    DashboardCore.uploadBackgroundImage(input);
}

// NEW: Global function for image removal
function removeBackgroundImage() {
    DashboardCore.removeBackgroundImage();
}

function openSettings() {
    openWheelModal();
}

function openWheelModal() {
    if (fortuneWheelInstance) {
        fortuneWheelInstance.openModal();
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize configuration with PHP values (these will be set in the view)
    if (typeof dashboardPhpConfig !== 'undefined') {
        DashboardConfig.init(
            dashboardPhpConfig.csrfToken,
            dashboardPhpConfig.csrfName,
            dashboardPhpConfig.baseUrl
        );
    }
    
    // Initialize fortune wheel instance
    fortuneWheelInstance = new FortuneWheel();
    
    // Add ripple styles
    DashboardUtils.addRippleStyles();
    
    // Preload wheel assets
    DashboardUtils.preloadWheelAssets(DashboardConfig.baseUrl);
    
    // Initialize UI state
    initializeUIState();
    
    // Set up event listeners
    setupEventListeners();
});

// Initialize UI state based on data
function initializeUIState() {
    // Initialize check-in button state
    const checkinButton = document.querySelector('.btn-checkin');
    if (checkinButton && typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.todayCheckin) {
        checkinButton.disabled = true;
        checkinButton.textContent = 'Already Checked In';
    }
}

// Set up all event listeners
function setupEventListeners() {
    // Ripple effect for buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.action-item, .btn-checkin, .bg-option')) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            e.target.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }
    });
}

// Log dashboard information for debugging
function logDashboardInfo() {
    // Dashboard info logging disabled for production
}