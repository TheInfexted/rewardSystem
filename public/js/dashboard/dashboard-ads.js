/**
 * Dashboard Advertisement Handler - Stacked Layout
 */
if (typeof DashboardConfig === 'undefined') {
    console.error('DashboardConfig not initialized. Make sure dashboard-config.js is loaded first.');
}

class DashboardAds {
    constructor() {
        this.container = document.getElementById('adsContainer');
        this.ads = [];
    }

    /**
     * Initialize ads
     */
    init(ads) {
        this.ads = ads;
        
        if (!this.container || !this.ads || this.ads.length === 0) {
            this.showEmptyState();
            return;
        }

        this.renderStackedAds();
        this.attachLazyLoading();
        this.detectVideoOrientation(); // Call it here after rendering
    }

    /**
     * Render stacked ads
     */
    renderStackedAds() {
        let stackHtml = '<div class="ads-stack">';
        
        this.ads.forEach((ad, index) => {
            stackHtml += this.createAdHtml(ad, index);
        });
        
        stackHtml += '</div>';
        
        this.container.innerHTML = stackHtml;
        
        // Attach click handlers
        this.attachClickHandlers();
    }

    /**
     * Detect video orientation and add appropriate classes
     */
    detectVideoOrientation() {
        const videos = this.container.querySelectorAll('video.ad-media-stacked');
        
        videos.forEach(video => {
            video.addEventListener('loadedmetadata', function() {
                const aspectRatio = this.videoWidth / this.videoHeight;
                const container = this.closest('.ad-item-stacked');
                
                if (aspectRatio > 1.3) {
                    // Horizontal video (16:9, etc.)
                    container.classList.add('horizontal-video');
                } else if (aspectRatio < 0.8) {
                    // Vertical video (9:16, etc.)
                    container.classList.add('vertical-video');
                } else {
                    // Square-ish video
                    container.classList.add('square-video');
                }
            });
        });
    }

    /**
     * Create ad HTML
     */
    createAdHtml(ad, index) {
        const mediaUrl = ad.media_file 
            ? `${DashboardConfig.baseUrl}uploads/reward_ads/${ad.media_file}`
            : ad.media_url;

        // For lazy loading, use data-src for images beyond the first two
        const isLazyLoad = index > 1;
        
        let html = `<div class="ad-item-stacked ${ad.ad_type === 'video' ? 'video-container' : ''}" data-id="${ad.id}" ${ad.click_url ? `data-url="${ad.click_url}"` : ''}>`;
        
        if (ad.ad_type === 'video') {
            // Videos should not lazy load to ensure smooth playback
            html += `
                <video class="ad-media-stacked" autoplay muted loop playsinline>
                    <source src="${mediaUrl}" type="video/mp4">
                </video>
            `;
        } else {
            if (isLazyLoad) {
                // Lazy load images after the first two
                html += `<img class="ad-media-stacked lazy" data-src="${mediaUrl}" alt="${ad.ad_title || 'Advertisement'}">`;
            } else {
                // Load first two images immediately
                html += `<img class="ad-media-stacked" src="${mediaUrl}" alt="${ad.ad_title || 'Advertisement'}">`;
            }
        }
        
        // Only add overlay if title or description exists
        if (ad.ad_title || ad.ad_description) {
            html += '<div class="ad-overlay-content">';
            if (ad.ad_title) {
                html += `<h4 class="ad-overlay-title">${this.escapeHtml(ad.ad_title)}</h4>`;
            }
            if (ad.ad_description) {
                html += `<p class="ad-overlay-description">${this.escapeHtml(ad.ad_description)}</p>`;
            }
            html += '</div>';
        }
        
        html += '</div>';
        
        return html;
    }

    /**
     * Attach click handlers
     */
    attachClickHandlers() {
        const adElements = this.container.querySelectorAll('.ad-item-stacked[data-url]');
        
        adElements.forEach(adElement => {
            adElement.style.cursor = 'pointer';
            adElement.addEventListener('click', (e) => {
                e.preventDefault();
                const url = adElement.dataset.url;
                if (url) {
                    window.open(url, '_blank');
                    
                    // Track click (optional analytics)
                    this.trackAdClick(adElement.dataset.id);
                }
            });
        });
    }

    /**
     * Lazy loading for images
     */
    attachLazyLoading() {
        const lazyImages = this.container.querySelectorAll('img.lazy');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px', // Start loading 50px before entering viewport
                threshold: 0.01
            });

            lazyImages.forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            lazyImages.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
            });
        }
    }

    /**
     * Track ad click (optional - for analytics)
     */
    trackAdClick(adId) {
        console.log('Ad clicked:', adId);
        
        // You can send this to your analytics endpoint
        // Example:
        // fetch('/api/track-ad-click', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-Requested-With': 'XMLHttpRequest'
        //     },
        //     body: JSON.stringify({ ad_id: adId })
        // });
    }

    /**
     * Show empty state
     */
    showEmptyState() {
        this.container.innerHTML = `
            <div class="no-ads">
                <i class="bi bi-image"></i>
                <p>Check back later for special offers!</p>
            </div>
        `;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Refresh ads (optional - for dynamic updates)
     */
    async refreshAds() {
        try {
            const response = await fetch('/customer/get-ads', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.ads) {
                    this.ads = data.ads;
                    this.renderStackedAds();
                    this.attachLazyLoading();
                    this.detectVideoOrientation(); // Also call it here after refresh
                }
            }
        } catch (error) {
            console.error('Failed to refresh ads:', error);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const dashboardAds = new DashboardAds();
    
    // Check if ads data is available from PHP
    if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.ads) {
        dashboardAds.init(dashboardPhpConfig.ads);
    }
    
    // Optional: Auto-refresh ads every 5 minutes
    // setInterval(() => {
    //     dashboardAds.refreshAds();
    // }, 5 * 60 * 1000);
});