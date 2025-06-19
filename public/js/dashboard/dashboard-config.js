/**
 * Dashboard Configuration and Constants
 * Contains CSRF tokens, API endpoints, and global settings
 */

// CSRF Configuration
const DashboardConfig = {
    csrf: {
        token: '', // Will be set from PHP
        name: ''   // Will be set from PHP
    },
    
    // API Endpoints
    endpoints: {
        checkin: '/customer/checkin',
        updateBackground: '/customer/update-background',
        wheelData: '/api/wheel-data',
        spin: '/spin',
        storeWinner: '/store-winner',
        reward: '/reward',
        bonus: '/admin/bonus'
    },
    
    // Default Settings
    settings: {
        toastDuration: 3000,
        wheelRefreshDelay: 500,
        redirectDelay: 1500
    },
    
    // Initialize configuration with PHP values
    init: function(csrfToken, csrfName, baseUrl) {
        this.csrf.token = csrfToken;
        this.csrf.name = csrfName;
        this.baseUrl = baseUrl;
        
        // Convert relative endpoints to full URLs
        Object.keys(this.endpoints).forEach(key => {
            if (this.endpoints[key].startsWith('/')) {
                this.endpoints[key] = baseUrl + this.endpoints[key];
            }
        });
    }
};