// Dashboard Theme Management System
// File: public/js/dashboard/dashboard-theme.js

document.addEventListener('DOMContentLoaded', function() {
    applyThemeToCheckinSection();
});

function applyThemeToCheckinSection() {
    const profileHeader = document.querySelector('.profile-header');
    const checkinSection = document.querySelector('.compact-checkin');
    
    if (!profileHeader || !checkinSection) return;
    
    // Get the current theme from the profile header classes
    const themeClass = Array.from(profileHeader.classList).find(cls => cls.endsWith('-bg'));
    
    if (!themeClass) return;
    
    const theme = themeClass.replace('-bg', '');
    
    // Define theme gradients
    const themeGradients = {
        'default': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'blue': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'purple': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'green': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'orange': 'linear-gradient(135deg, #ff9a56 0%, #ffad56 100%)',
        'pink': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'dark': 'linear-gradient(135deg, #2c3e50 0%, #3498db 100%)',
        'coral': 'linear-gradient(135deg, #ff7b7b 0%, #ff416c 100%)',
        'emerald': 'linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%)',
        'sapphire': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'gold': 'linear-gradient(135deg, #f7931e 0%, #ffd200 100%)',
        'crimson': 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)',
        'violet': 'linear-gradient(135deg, #8360c3 0%, #2ebf91 100%)'
    };
    
    // Apply the gradient to the check-in section
    if (themeGradients[theme]) {
        checkinSection.style.background = themeGradients[theme];
        
        // Also apply the theme class for CSS targeting
        checkinSection.classList.add(`theme-${theme}`);
        
        // Apply theme to parent section for CSS cascade
        const parentCheckinSection = document.querySelector('.checkin-section');
        if (parentCheckinSection) {
            parentCheckinSection.classList.add(`${theme}-bg`);
        }
        
        console.log(`Applied ${theme} theme to check-in section`);
    }
}

// Function to update theme dynamically (useful for future theme switching)
function updateTheme(newTheme) {
    const profileHeader = document.querySelector('.profile-header');
    const checkinSection = document.querySelector('.compact-checkin');
    const parentCheckinSection = document.querySelector('.checkin-section');
    
    if (!profileHeader || !checkinSection) return;
    
    // Remove old theme classes from profile header
    const oldThemeClasses = Array.from(profileHeader.classList).filter(cls => cls.endsWith('-bg'));
    oldThemeClasses.forEach(cls => profileHeader.classList.remove(cls));
    
    // Remove old theme classes from checkin sections
    if (parentCheckinSection) {
        const oldCheckinThemes = Array.from(parentCheckinSection.classList).filter(cls => cls.endsWith('-bg'));
        oldCheckinThemes.forEach(cls => parentCheckinSection.classList.remove(cls));
    }
    
    const oldCheckinThemes = Array.from(checkinSection.classList).filter(cls => cls.startsWith('theme-'));
    oldCheckinThemes.forEach(cls => checkinSection.classList.remove(cls));
    
    // Apply new theme classes
    profileHeader.classList.add(`${newTheme}-bg`);
    if (parentCheckinSection) {
        parentCheckinSection.classList.add(`${newTheme}-bg`);
    }
    checkinSection.classList.add(`theme-${newTheme}`);
    
    // Apply the new gradient
    const themeGradients = {
        'default': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'blue': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'purple': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'green': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'orange': 'linear-gradient(135deg, #ff9a56 0%, #ffad56 100%)',
        'pink': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'dark': 'linear-gradient(135deg, #2c3e50 0%, #3498db 100%)',
        'coral': 'linear-gradient(135deg, #ff7b7b 0%, #ff416c 100%)',
        'emerald': 'linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%)',
        'sapphire': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'gold': 'linear-gradient(135deg, #f7931e 0%, #ffd200 100%)',
        'crimson': 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)',
        'violet': 'linear-gradient(135deg, #8360c3 0%, #2ebf91 100%)'
    };
    
    if (themeGradients[newTheme]) {
        checkinSection.style.background = themeGradients[newTheme];
        
        // Update data attribute on dashboard container
        const dashboardContainer = document.querySelector('.dashboard-container');
        if (dashboardContainer) {
            dashboardContainer.setAttribute('data-theme', newTheme);
        }
        
        // Update body data attribute
        document.body.setAttribute('data-theme', newTheme);
        
        console.log(`Updated to ${newTheme} theme`);
    }
}

// Function to sync theme changes from admin updates
function syncThemeFromServer() {
    if (!window.dashboardPhpConfig || !window.dashboardPhpConfig.username) {
        return;
    }
    
    fetch(`${dashboardPhpConfig.baseUrl}customer/getTheme`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.theme) {
            const currentTheme = dashboardPhpConfig.profileBackground;
            
            // Only update if theme actually changed
            if (data.theme !== currentTheme) {
                updateTheme(data.theme);
                
                // Update the global config
                dashboardPhpConfig.profileBackground = data.theme;
                
                // Show notification of theme change
                showThemeChangeNotification(data.theme);
            }
        }
    })
    .catch(error => {
        console.error('Error syncing theme:', error);
    });
}

// Show notification when theme changes
function showThemeChangeNotification(newTheme) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'theme-change-notification';
    notification.innerHTML = `
        <i class="bi bi-palette"></i>
        Theme updated to ${newTheme.charAt(0).toUpperCase() + newTheme.slice(1)}
    `;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// Auto-refresh theme every 30 seconds to catch admin changes
function startThemeAutoSync() {
    // Check immediately on start
    setTimeout(syncThemeFromServer, 2000);
    
    // Then check every 30 seconds
    setInterval(syncThemeFromServer, 30000);
}

// Initialize auto-sync on page load
if (typeof dashboardPhpConfig !== 'undefined') {
    // Start auto-sync after a delay to prevent immediate server calls
    setTimeout(startThemeAutoSync, 5000);
}

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

.theme-change-notification {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}
`;
document.head.appendChild(style);

// Export functions for global use
window.applyThemeToCheckinSection = applyThemeToCheckinSection;
window.updateTheme = updateTheme;
window.syncThemeFromServer = syncThemeFromServer;