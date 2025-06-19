/**
 * Dashboard Advertisement Handler
 */

class DashboardAds {
    constructor() {
        this.container = document.getElementById('adsContainer');
        this.currentIndex = 0;
        this.ads = [];
        this.autoRotateInterval = null;
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

        if (this.ads.length === 1) {
            this.renderSingleAd(this.ads[0]);
        } else {
            this.renderCarousel();
            this.startAutoRotate();
        }
    }

    /**
     * Render single ad
     */
    renderSingleAd(ad) {
        const adHtml = this.createAdHtml(ad);
        this.container.innerHTML = adHtml;
        this.attachClickHandler(ad);
    }

    /**
     * Render carousel for multiple ads
     */
    renderCarousel() {
        let carouselHtml = '<div class="ads-carousel">';
        carouselHtml += '<div class="ads-track" id="adsTrack">';
        
        this.ads.forEach(ad => {
            carouselHtml += this.createAdHtml(ad);
        });
        
        carouselHtml += '</div>';
        
        // Add dots
        if (this.ads.length > 1) {
            carouselHtml += '<div class="carousel-dots">';
            for (let i = 0; i < this.ads.length; i++) {
                carouselHtml += `<span class="carousel-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></span>`;
            }
            carouselHtml += '</div>';
        }
        
        carouselHtml += '</div>';
        
        this.container.innerHTML = carouselHtml;
        
        // Attach handlers
        this.attachCarouselHandlers();
    }

    /**
     * Create ad HTML
     */
    createAdHtml(ad) {
        const mediaUrl = ad.media_file 
            ? `${DashboardConfig.baseUrl}uploads/reward_ads/${ad.media_file}`
            : ad.media_url;

        let html = `<div class="ad-item" data-id="${ad.id}" data-url="${ad.click_url || ''}">`;
        
        if (ad.ad_type === 'video') {
            html += `
                <video class="ad-media ad-video" controls autoplay muted loop>
                    <source src="${mediaUrl}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            `;
        } else {
            html += `<img class="ad-media" src="${mediaUrl}" alt="${ad.ad_title}">`;
        }
        
        if (ad.ad_title || ad.ad_description) {
            html += '<div class="ad-content">';
            if (ad.ad_title) {
                html += `<h4 class="ad-title">${ad.ad_title}</h4>`;
            }
            if (ad.ad_description) {
                html += `<p class="ad-description">${ad.ad_description}</p>`;
            }
            html += '</div>';
        }
        
        html += '</div>';
        
        return html;
    }

    /**
     * Attach click handler
     */
    attachClickHandler(ad) {
        const adElement = this.container.querySelector('.ad-item');
        if (adElement && ad.click_url) {
            adElement.style.cursor = 'pointer';
            adElement.addEventListener('click', () => {
                window.open(ad.click_url, '_blank');
            });
        }
    }

    /**
     * Attach carousel handlers
     */
    attachCarouselHandlers() {
        // Click handlers for ads
        this.ads.forEach((ad, index) => {
            const adElement = this.container.querySelectorAll('.ad-item')[index];
            if (adElement && ad.click_url) {
                adElement.addEventListener('click', () => {
                    window.open(ad.click_url, '_blank');
                });
            }
        });

        // Dot navigation
        const dots = this.container.querySelectorAll('.carousel-dot');
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const index = parseInt(dot.dataset.index);
                this.goToSlide(index);
                this.stopAutoRotate();
                this.startAutoRotate();
            });
        });

        // Touch/swipe support
        this.addSwipeSupport();
    }

    /**
     * Go to specific slide
     */
    goToSlide(index) {
        this.currentIndex = index;
        const track = document.getElementById('adsTrack');
        if (track) {
            track.style.transform = `translateX(-${index * 100}%)`;
        }
        
        // Update dots
        const dots = this.container.querySelectorAll('.carousel-dot');
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    /**
     * Start auto-rotate
     */
    startAutoRotate() {
        this.autoRotateInterval = setInterval(() => {
            this.nextSlide();
        }, 5000); // Change slide every 5 seconds
    }

    /**
     * Stop auto-rotate
     */
    stopAutoRotate() {
        if (this.autoRotateInterval) {
            clearInterval(this.autoRotateInterval);
        }
    }

    /**
     * Next slide
     */
    nextSlide() {
        const nextIndex = (this.currentIndex + 1) % this.ads.length;
        this.goToSlide(nextIndex);
    }

    /**
     * Add swipe support
     */
    addSwipeSupport() {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;
        
        const track = document.getElementById('adsTrack');
        if (!track) return;

        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
        });

        track.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            currentX = e.touches[0].clientX;
        });

        track.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            
            const diff = startX - currentX;
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                if (diff > 0) {
                    // Swipe left - next
                    this.nextSlide();
                } else {
                    // Swipe right - previous
                    const prevIndex = (this.currentIndex - 1 + this.ads.length) % this.ads.length;
                    this.goToSlide(prevIndex);
                }
                this.stopAutoRotate();
                this.startAutoRotate();
            }
        });
    }

    /**
     * Show empty state
     */
    showEmptyState() {
        this.container.innerHTML = `
            <div class="no-ads">
                <i class="bi bi-image"></i>
                <p>No advertisements available</p>
            </div>
        `;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const dashboardAds = new DashboardAds();
    
    // Check if ads data is available from PHP
    if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.ads) {
        dashboardAds.init(dashboardPhpConfig.ads);
    }
});