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

function redirectToDeposit() {
    DashboardCore.redirectToDeposit();
}

function openSettings() {
    DashboardCore.openSettings();
}

function openWheelModal() {
    if (fortuneWheelInstance) {
        fortuneWheelInstance.openModal();
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initialization started...');
    
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
    
    console.log('Dashboard loaded successfully');
    logDashboardInfo();
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
    // Enhanced error handling
    window.addEventListener('error', function(e) {
        console.error('Dashboard error:', e.error);
    });

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
    if (typeof dashboardPhpConfig !== 'undefined') {
        console.log('Username:', dashboardPhpConfig.username);
        console.log('Points:', dashboardPhpConfig.totalPoints);
        console.log('Monthly checkins:', dashboardPhpConfig.monthlyCheckins);
        console.log('Today checkin status:', dashboardPhpConfig.todayCheckin);
        
        if (dashboardPhpConfig.weeklyProgress) {
            console.log('Weekly progress loaded:', dashboardPhpConfig.weeklyProgress);
        }
        
        if (dashboardPhpConfig.recentActivities) {
            console.log('Recent activities loaded:', dashboardPhpConfig.recentActivities);
        }
    }
    
    console.log('Available functions:', [
        'openWheelModal()', 
        'performCheckin()',
        'changeBackground(type)',
        'redirectToDeposit()',
        'openSettings()'
    ]);
}

// Debug functions for testing
window.debugDashboard = {
    testWheel: function() {
        console.log('Testing wheel instance:', fortuneWheelInstance);
        if (fortuneWheelInstance) {
            console.log('Wheel state:', {
                scriptsLoaded: fortuneWheelInstance.scriptsLoaded,
                wheelSpinning: fortuneWheelInstance.wheelSpinning,
                spinsRemaining: fortuneWheelInstance.spinsRemaining
            });
        }
    },
    
    forceWheelSpin: function() {
        if (fortuneWheelInstance && fortuneWheelInstance.wheel) {
            fortuneWheelInstance.wheelSpinning = false;
            fortuneWheelInstance.spinsRemaining = 1;
            fortuneWheelInstance.startSpin();
        } else {
            console.log('Wheel not initialized');
        }
    },
    
    showToast: function(message, type = 'info') {
        DashboardUtils.showToast(message, type);
    }
};