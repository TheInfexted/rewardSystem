/**
 * Dashboard Utility Functions
 * Common utilities like toast notifications, ripple effects, etc.
 */

class DashboardUtils {
    
    // Toast notification function with optional close button
    static showToast(message, type = 'info', duration = null, showCloseButton = false) {
        // Remove any existing toasts first
        const existingToasts = document.querySelectorAll('.dashboard-toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed dashboard-toast d-flex align-items-center justify-content-between`;
        toast.style.cssText = `
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 250px;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            padding: 12px 20px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        `;
        
        // Create message container
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        messageSpan.style.flex = '1';
        toast.appendChild(messageSpan);
        
        // Add close button if requested
        if (showCloseButton) {
            const closeButton = document.createElement('button');
            closeButton.innerHTML = '&times;';
            closeButton.className = 'btn-close ms-2';
            closeButton.style.cssText = `
                background: none;
                border: none;
                color: inherit;
                font-size: 1.2rem;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0.7;
            `;
            
            closeButton.onclick = () => {
                if (toast.parentNode) {
                    toast.style.animation = 'slideUp 0.3s ease';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            };
            
            closeButton.onmouseover = () => {
                closeButton.style.opacity = '1';
            };
            
            closeButton.onmouseout = () => {
                closeButton.style.opacity = '0.7';
            };
            
            toast.appendChild(closeButton);
        }
        
        document.body.appendChild(toast);
        
        // Auto-dismiss if duration is provided
        if (duration !== null) {
            const toastDuration = duration || (DashboardConfig?.settings?.toastDuration || 3000);
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.animation = 'slideUp 0.3s ease';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, toastDuration);
        }
    }

    // Dismiss all toasts (useful for clearing loading messages)
    static dismissAllToasts() {
        const existingToasts = document.querySelectorAll('.dashboard-toast');
        existingToasts.forEach(toast => {
            if (toast.parentNode) {
                toast.remove();
            }
        });
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
                
                @keyframes slideDown {
                    from {
                        transform: translateX(-50%) translateY(-100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(-50%) translateY(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideUp {
                    from {
                        transform: translateX(-50%) translateY(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(-50%) translateY(-100%);
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