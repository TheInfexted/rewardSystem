/**
 * Dashboard Advertisement Handler - Stacked Layout - NO TITLES
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
        this.detectVideoOrientation();
    }

    /**
     * Render stacked ads WITHOUT TITLES
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
     * Create ad HTML - MEDIA ONLY, NO TITLES OR DESCRIPTIONS
     */
    createAdHtml(ad, index) {
        const mediaUrl = ad.media_file 
            ? `${DashboardConfig.baseUrl}uploads/reward_ads/${ad.media_file}`
            : ad.media_url;

        // For lazy loading, use data-src for images beyond the first two
        const isLazyLoad = index > 1;
        const srcAttr = isLazyLoad ? 'data-src' : 'src';
        const lazyClass = isLazyLoad ? 'lazy' : '';
        
        let html = `<div class="ad-item-stacked ${ad.ad_type === 'video' ? 'video-container' : ''}" data-id="${ad.id}"`;
        
        // Add click URL if available
        if (ad.click_url) {
            html += ` data-url="${ad.click_url}"`;
        }
        
        html += `>`;
        
        // Add media (image or video) - NO TITLES OR DESCRIPTIONS
        if (ad.ad_type === 'video') {
            html += `<video class="ad-media-stacked ${lazyClass}" autoplay muted loop playsinline ${srcAttr}="${mediaUrl}">`;
            if (!isLazyLoad) {
                html += `<source src="${mediaUrl}" type="video/mp4">`;
            }
            html += `</video>`;
        } else {
            html += `<img class="ad-media-stacked ${lazyClass}" ${srcAttr}="${mediaUrl}" alt="Advertisement">`;
        }
        
        html += `</div>`;
        
        return html;
    }

    /**
     * Attach click handlers
     */
    attachClickHandlers() {
        const adItems = this.container.querySelectorAll('.ad-item-stacked[data-url]');
        
        adItems.forEach(item => {
            item.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const adId = this.getAttribute('data-id');
                
                if (url) {
                    // Track click if needed
                    console.log(`Ad ${adId} clicked`);
                    
                    // Open in new tab
                    window.open(url, '_blank');
                }
            });
        });
    }

    /**
     * Attach lazy loading for images
     */
    attachLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.getAttribute('data-src');
                        
                        if (src) {
                            img.setAttribute('src', src);
                            img.removeAttribute('data-src');
                            img.classList.remove('lazy');
                        }
                        
                        observer.unobserve(img);
                    }
                });
            });

            const lazyImages = this.container.querySelectorAll('img.lazy, video.lazy');
            lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for browsers without IntersectionObserver
            const lazyImages = this.container.querySelectorAll('img.lazy, video.lazy');
            lazyImages.forEach(img => {
                const src = img.getAttribute('data-src');
                if (src) {
                    img.setAttribute('src', src);
                    img.removeAttribute('data-src');
                    img.classList.remove('lazy');
                }
            });
        }
    }

    /**
     * Show empty state when no ads
     */
    showEmptyState() {
        if (this.container) {
            this.container.innerHTML = `
                <div class="no-ads">
                    <i class="bi bi-image"></i>
                    <p>Check back later for special offers!</p>
                </div>
            `;
        }
    }
}

// Initialize ads when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait for dashboard config to be ready
    if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.ads) {
        const adsHandler = new DashboardAds();
        adsHandler.init(dashboardPhpConfig.ads);
    }
});

// Make it globally available if needed
window.DashboardAds = DashboardAds;