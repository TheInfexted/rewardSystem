/**
 * DASHBOARD THEME MANAGEMENT
 * File: public/js/dashboard/dashboard-theme.js
 * 
 * Handles theme synchronization between profile header and check-in section
 */

// ==============================================
// MAIN THEME APPLICATION FUNCTION
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard theme script loaded');
    
    // Apply theme with a short delay to ensure DOM is fully rendered
    setTimeout(() => {
        applyThemeToCheckinSection();
        
        // Set up a mutation observer to watch for theme changes
        observeThemeChanges();
    }, 150);
});

/**
 * Apply theme from profile header to check-in section
 */
function applyThemeToCheckinSection() {
    const profileHeader = document.querySelector('.profile-header');
    const checkinSection = document.querySelector('.compact-checkin');
    
    if (!profileHeader || !checkinSection) {
        console.warn('Profile header or checkin section not found:', {
            profileHeader: !!profileHeader,
            checkinSection: !!checkinSection
        });
        return;
    }
    
    // Get the current theme from the profile header classes
    const themeClass = Array.from(profileHeader.classList).find(cls => cls.endsWith('-bg'));
    const theme = themeClass ? themeClass.replace('-bg', '') : 'default';
    
    console.log('Detected profile theme:', theme, 'from class:', themeClass);
    
    // Apply the appropriate gradient to the check-in section
    applyGradientTheme(checkinSection, theme);
}

/**
 * Apply gradient theme to check-in section
 */
function applyGradientTheme(checkinSection, theme) {
    // Remove any existing overlays from previous image applications
    const existingOverlay = checkinSection.querySelector('.checkin-image-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }
    
    // Clear any background image that might have been applied
    checkinSection.style.backgroundImage = 'none';
    
    // Define theme gradients - comprehensive mapping
    const themeGradients = {
        // Blue variants
        'default': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'blue': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'purple': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'ocean': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'galaxy': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'sapphire': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'violet': 'linear-gradient(135deg, #8360c3 0%, #2ebf91 100%)',
        
        // Green variants
        'green': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'forest': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'emerald': 'linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%)',
        
        // Orange variants
        'orange': 'linear-gradient(135deg, #ff9a56 0%, #ffad56 100%)',
        'sunset': 'linear-gradient(135deg, #ff9a56 0%, #ffad56 100%)',
        'gold': 'linear-gradient(135deg, #f7931e 0%, #ffd200 100%)',
        
        // Pink/Red variants
        'pink': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'rose': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'coral': 'linear-gradient(135deg, #ff7b7b 0%, #ff416c 100%)',
        'crimson': 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)',
        
        // Dark variants
        'dark': 'linear-gradient(135deg, #2c3e50 0%, #3498db 100%)',
        'midnight': 'linear-gradient(135deg, #2c3e50 0%, #3498db 100%)'
    };
    
    // Apply the gradient theme
    if (themeGradients[theme]) {
        checkinSection.style.background = themeGradients[theme];
        checkinSection.style.backgroundSize = 'cover';
        checkinSection.style.backgroundPosition = 'center';
        
        // Add theme class for CSS targeting
        const existingThemeClasses = Array.from(checkinSection.classList).filter(cls => cls.startsWith('theme-'));
        existingThemeClasses.forEach(cls => checkinSection.classList.remove(cls));
        checkinSection.classList.add(`theme-${theme}`);
        
        // Apply to parent section for CSS cascade
        const parentCheckinSection = document.querySelector('.checkin-section');
        if (parentCheckinSection) {
            const existingParentThemes = Array.from(parentCheckinSection.classList).filter(cls => cls.endsWith('-bg'));
            existingParentThemes.forEach(cls => parentCheckinSection.classList.remove(cls));
            parentCheckinSection.classList.add(`${theme}-bg`);
        }
        
        // Reset content positioning (remove any overlay effects)
        const checkinContent = checkinSection.children;
        for (let i = 0; i < checkinContent.length; i++) {
            if (!checkinContent[i].classList.contains('checkin-image-overlay')) {
                checkinContent[i].style.position = 'relative';
                checkinContent[i].style.zIndex = '2';
            }
        }
        
        console.log(`✅ Applied ${theme} gradient theme to check-in section`);
        console.log('Applied background:', themeGradients[theme]);
        
    } else {
        // Fallback to default gradient
        checkinSection.style.background = themeGradients['default'];
        checkinSection.classList.add('theme-default');
        
        console.warn(`⚠️ Theme '${theme}' not found, applied default gradient`);
    }
}

// ==============================================
// DYNAMIC THEME UPDATES
// ==============================================

/**
 * Function to update theme dynamically
 */
function updateTheme(newTheme) {
    const profileHeader = document.querySelector('.profile-header');
    const checkinSection = document.querySelector('.compact-checkin');
    const parentCheckinSection = document.querySelector('.checkin-section');
    
    if (!profileHeader || !checkinSection) {
        console.error('Required elements not found for theme update');
        return;
    }
    
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
    
    // Apply new theme classes to profile header
    profileHeader.classList.add(`${newTheme}-bg`);
    
    // Always apply gradient theme to check-in section (ignore any background images)
    applyGradientTheme(checkinSection, newTheme);
    
    // Update data attribute on dashboard container
    const dashboardContainer = document.querySelector('.dashboard-container');
    if (dashboardContainer) {
        dashboardContainer.setAttribute('data-theme', newTheme);
    }
    
    // Update body data attribute
    document.body.setAttribute('data-theme', newTheme);
    
    console.log(`✅ Updated to ${newTheme} theme - Profile header: ${getProfileHeaderStyle()}, Check-in: gradient only`);
}

// ==============================================
// MUTATION OBSERVER FOR THEME CHANGES
// ==============================================

/**
 * Set up mutation observer to watch for theme changes
 */
function observeThemeChanges() {
    const profileHeader = document.querySelector('.profile-header');
    if (!profileHeader) return;
    
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'class' || mutation.attributeName === 'style')) {
                
                console.log('Profile header changed, reapplying theme...');
                setTimeout(() => {
                    applyThemeToCheckinSection();
                }, 100);
            }
        });
    });
    
    observer.observe(profileHeader, {
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    console.log('Theme change observer set up');
}

// ==============================================
// UTILITY FUNCTIONS
// ==============================================

/**
 * Helper function to check profile header style
 */
function getProfileHeaderStyle() {
    const profileHeader = document.querySelector('.profile-header');
    if (!profileHeader) return 'unknown';
    
    const hasBackgroundImage = profileHeader.style.backgroundImage && 
                              profileHeader.style.backgroundImage !== 'none' && 
                              profileHeader.style.backgroundImage !== '';
    
    return hasBackgroundImage ? 'custom image' : 'theme gradient';
}

/**
 * Debug function to manually test theme application
 */
function debugTheme(theme = null) {
    if (theme) {
        console.log(`🔧 Debug: Testing ${theme} theme`);
        applyGradientTheme(document.querySelector('.compact-checkin'), theme);
    } else {
        console.log('🔧 Debug: Current theme state');
        console.log('Profile header classes:', Array.from(document.querySelector('.profile-header').classList));
        console.log('Check-in section classes:', Array.from(document.querySelector('.compact-checkin').classList));
        console.log('Check-in background:', document.querySelector('.compact-checkin').style.background);
        
        // Test all available themes
        const testThemes = ['green', 'orange', 'pink', 'dark', 'blue'];
        testThemes.forEach((theme, index) => {
            setTimeout(() => {
                console.log(`Testing ${theme} theme...`);
                applyGradientTheme(document.querySelector('.compact-checkin'), theme);
            }, index * 1000);
        });
    }
}

/**
 * Reset to default theme
 */
function resetToDefaultTheme() {
    const checkinSection = document.querySelector('.compact-checkin');
    if (checkinSection) {
        applyGradientTheme(checkinSection, 'default');
        console.log('Reset to default theme');
    }
}

// ==============================================
// GLOBAL EXPORTS
// ==============================================

// Export functions for global use
window.applyThemeToCheckinSection = applyThemeToCheckinSection;
window.updateTheme = updateTheme;
window.debugTheme = debugTheme;
window.resetToDefaultTheme = resetToDefaultTheme;

// ==============================================
// AUTOMATIC THEME SYNCHRONIZATION
// ==============================================

/**
 * Sync theme from server settings (for admin changes)
 */
function syncThemeFromServer() {
    if (typeof dashboardPhpConfig === 'undefined') return;
    
    fetch(`${dashboardPhpConfig.baseUrl}customer/getThemeSettings`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': dashboardPhpConfig.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.profile_background) {
            const currentTheme = data.profile_background;
            
            // Check if theme has changed
            const profileHeader = document.querySelector('.profile-header');
            const currentThemeClass = Array.from(profileHeader.classList).find(cls => cls.endsWith('-bg'));
            const currentThemeName = currentThemeClass ? currentThemeClass.replace('-bg', '') : 'default';
            
            if (currentThemeName !== currentTheme) {
                console.log(`Theme changed from ${currentThemeName} to ${currentTheme}`);
                updateTheme(currentTheme);
                showThemeChangeNotification(currentTheme);
            }
        }
    })
    .catch(error => {
        console.log('Theme sync error (normal if endpoint not available):', error);
    });
}

/**
 * Show notification when theme changes
 */
function showThemeChangeNotification(theme) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
    `;
    
    notification.innerHTML = `
        <i class="bi bi-palette" style="font-size: 16px;"></i>
        <span>Theme updated to ${theme}</span>
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

/**
 * Start automatic theme synchronization
 */
function startThemeAutoSync() {
    // Check immediately on start
    setTimeout(syncThemeFromServer, 2000);
    
    // Then check every 30 seconds
    setInterval(syncThemeFromServer, 30000);
}

// Initialize auto-sync if dashboard config is available
if (typeof dashboardPhpConfig !== 'undefined') {
    // Start auto-sync after a delay to prevent immediate server calls
    setTimeout(startThemeAutoSync, 5000);
}

console.log('Dashboard theme management loaded and ready');