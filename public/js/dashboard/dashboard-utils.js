/**
 * Dashboard Utility Functions
 * Common utilities like toast notifications, ripple effects, etc.
 */

class DashboardUtils {
    
    // Toast notification function
    static showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
        toast.style.cssText = `
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 250px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            padding: 12px 20px;
            font-weight: 500;
        `;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, DashboardConfig.settings.toastDuration);
    }

    // Confirm action with callback
    static confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    // Handle navigation with loading states
    static handleNavigation(url, newTab = false) {
        if (newTab) {
            window.open(url, '_blank');
        } else {
            window.location.href = url;
        }
    }

    // Add ripple effect CSS
    static addRippleStyles() {
        if (!document.querySelector('#ripple-style')) {
            const rippleStyle = document.createElement('style');
            rippleStyle.id = 'ripple-style';
            rippleStyle.textContent = `
                .ripple {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple-animation 0.6s linear;
                    pointer-events: none;
                }
                
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                @keyframes glow {
                    from {
                        text-shadow: 0 0 1px #fff, 0 0 5px #fff, 0 0 10px #e60073, 0 0 20px #e60073, 0 0 30px #e60073;
                    }
                    to {
                        text-shadow: 0 0 1px #fff, 0 0 10px #ff4da6, 0 0 20px #ff4da6, 0 0 30px #ff4da6, 0 0 50px #ff4da6;
                    }
                }
                
                .glow {
                    animation: glow 1s ease-in-out infinite alternate;
                }
                
                /* Mobile optimizations */
                @media (max-width: 768px) {
                    #wheelModal .modal-dialog {
                        margin: 0.5rem;
                        max-width: calc(100% - 1rem);
                    }
                    
                    #fortuneWheelModal {
                        width: 280px !important;
                        height: 280px !important;
                    }
                    
                    .wrap-text-frame {
                        margin: 0 1rem;
                        font-size: 0.7rem;
                    }
                }
            `;
            document.head.appendChild(rippleStyle);
        }
    }

    // Preload wheel assets for better performance
    static preloadWheelAssets(baseUrl) {
        const assets = [
            `${baseUrl}img/fortune_wheel/bg_spin.png`,
            `${baseUrl}img/fortune_wheel/text_frame.png`,
            `${baseUrl}img/fortune_wheel/arrow.png`,
            `${baseUrl}img/fortune_wheel/bg_wheel_frame.png`,
            `${baseUrl}img/fortune_wheel/red.png`,
            `${baseUrl}img/fortune_wheel/brown.png`
        ];
        
        assets.forEach(src => {
            const img = new Image();
            img.src = src;
        });
    }
}