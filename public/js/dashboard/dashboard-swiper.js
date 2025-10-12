/**
 * Dashboard Advertisement Swiper Handler
 * Modern touch-friendly ad slider using Swiper.js
 */

class DashboardSwiper {
    constructor() {
        this.swiperInstance = null;
        this.ads = [];
        this.isInitialized = false;
    }

    /**
     * Initialize the swiper with ads data
     */
    init(ads) {
        this.ads = ads || [];
        
        if (!this.ads || this.ads.length === 0) {
            this.showEmptyState();
            return;
        }

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeSwiper());
        } else {
            this.initializeSwiper();
        }
    }

    /**
     * Initialize Swiper instance
     */
    initializeSwiper() {
        const swiperContainer = document.querySelector('.ads-swiper');
        
        if (!swiperContainer) {
            return;
        }

        // Check if Swiper is available
        if (typeof Swiper === 'undefined') {
            return;
        }

        // Initialize Swiper
        this.swiperInstance = new Swiper('.ads-swiper', {
            // Basic settings
            slidesPerView: 1,
            spaceBetween: 0,
            loop: this.ads.length > 1,
            centeredSlides: true,
            
            // Speed and timing
            speed: 600,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            
            // Touch settings
            touchRatio: 1,
            touchAngle: 45,
            grabCursor: true,
            
            // Effects
            effect: 'slide',
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            
            // Navigation
            navigation: {
                nextEl: '.ads-button-next',
                prevEl: '.ads-button-prev',
                hideOnClick: false,
            },
            
            // Pagination
            pagination: {
                el: '.ads-pagination',
                clickable: true,
                dynamicBullets: true,
                dynamicMainBullets: 3,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + (index + 1) + '</span>';
                },
            },
            
            // Scrollbar
            scrollbar: {
                el: '.ads-scrollbar',
                draggable: true,
                hide: false,
                snapOnRelease: true,
            },
            
            // Lazy loading
            lazy: {
                loadPrevNext: true,
                loadPrevNextAmount: 2,
                loadOnTransitionStart: true,
            },
            
            // Preload images
            preloadImages: false,
            watchSlidesProgress: true,
            watchSlidesVisibility: true,
            
            // Responsive breakpoints
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                },
                480: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                },
                768: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                },
            },
            
            // Keyboard control
            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },
            
            // Mouse wheel
            mousewheel: {
                forceToAxis: true,
                sensitivity: 1,
                releaseOnEdges: true,
            },
            
            // Accessibility
            a11y: {
                enabled: true,
                prevSlideMessage: 'Previous advertisement',
                nextSlideMessage: 'Next advertisement',
                firstSlideMessage: 'This is the first advertisement',
                lastSlideMessage: 'This is the last advertisement',
            },
            
            // Event handlers
            on: {
                init: () => {
                    this.isInitialized = true;
                    this.attachClickHandlers();
                    this.handleVideoPlayback();
                    this.detectOrientation();
                },
                
                slideChange: () => {
                    this.handleVideoPlayback();
                },
                
                slideChangeTransitionEnd: () => {
                    this.optimizePerformance();
                },
                
                touchStart: () => {
                    this.pauseAllVideos();
                },
                
                touchEnd: () => {
                    this.resumeActiveVideo();
                },
            },
        });

        // Add custom controls
        this.addCustomControls();
    }

    /**
     * Handle video playback for active slide
     */
    handleVideoPlayback() {
        if (!this.swiperInstance) return;

        // Pause all videos first
        this.pauseAllVideos();
        
        // Play video in active slide
        const activeSlide = this.swiperInstance.slides[this.swiperInstance.activeIndex];
        if (activeSlide) {
            const video = activeSlide.querySelector('video');
            if (video) {
                video.play().catch(e => {
                    // Video autoplay prevented
                });
            }
        }
    }

    /**
     * Pause all videos
     */
    pauseAllVideos() {
        if (!this.swiperInstance) return;
        
        const videos = this.swiperInstance.el.querySelectorAll('video');
        videos.forEach(video => {
            video.pause();
        });
    }

    /**
     * Resume active video
     */
    resumeActiveVideo() {
        if (!this.swiperInstance) return;
        
        setTimeout(() => {
            const activeSlide = this.swiperInstance.slides[this.swiperInstance.activeIndex];
            if (activeSlide) {
                const video = activeSlide.querySelector('video');
                if (video) {
                    video.play().catch(e => {
                        // Video resume prevented
                    });
                }
            }
        }, 100);
    }

    /**
     * Detect and handle different media orientations
     */
    detectOrientation() {
        if (!this.swiperInstance) return;
        
        const slides = this.swiperInstance.slides;
        slides.forEach(slide => {
            const media = slide.querySelector('img, video');
            if (media) {
                if (media.tagName === 'VIDEO') {
                    media.addEventListener('loadedmetadata', () => {
                        this.classifyMediaOrientation(media, slide);
                    });
                } else {
                    media.addEventListener('load', () => {
                        this.classifyMediaOrientation(media, slide);
                    });
                }
            }
        });
    }

    /**
     * Classify media orientation and add appropriate classes
     */
    classifyMediaOrientation(media, slide) {
        const aspectRatio = media.videoWidth ? 
            media.videoWidth / media.videoHeight : 
            media.naturalWidth / media.naturalHeight;
        
        slide.classList.remove('horizontal-media', 'vertical-media', 'square-media');
        
        if (aspectRatio > 1.3) {
            slide.classList.add('horizontal-media');
        } else if (aspectRatio < 0.8) {
            slide.classList.add('vertical-media');
        } else {
            slide.classList.add('square-media');
        }
    }

    /**
     * Attach click handlers for ads
     */
    attachClickHandlers() {
        if (!this.swiperInstance) return;
        
        const slides = this.swiperInstance.slides;
        slides.forEach(slide => {
            const url = slide.dataset.url;
            if (url) {
                slide.style.cursor = 'pointer';
                slide.addEventListener('click', (e) => {
                    // Prevent click during swipe
                    if (this.swiperInstance.animating) return;
                    
                    // Open URL
                    window.open(url, '_blank');
                });
            }
        });
    }

    /**
     * Add custom controls
     */
    addCustomControls() {
        // Add play/pause button for autoplay
        const swiperContainer = document.querySelector('.ads-swiper-container');
        if (swiperContainer && this.ads.length > 1) {
            const playPauseBtn = document.createElement('button');
            playPauseBtn.className = 'ads-play-pause-btn';
            playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            playPauseBtn.title = 'Pause autoplay';
            
            playPauseBtn.addEventListener('click', () => {
                if (this.swiperInstance.autoplay.running) {
                    this.swiperInstance.autoplay.stop();
                    playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
                    playPauseBtn.title = 'Resume autoplay';
                } else {
                    this.swiperInstance.autoplay.start();
                    playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
                    playPauseBtn.title = 'Pause autoplay';
                }
            });
            
            swiperContainer.appendChild(playPauseBtn);
        }

        // Add slide counter
        if (this.ads.length > 1) {
            const slideCounter = document.createElement('div');
            slideCounter.className = 'ads-slide-counter';
            slideCounter.textContent = `1 / ${this.ads.length}`;
            
            this.swiperInstance.on('slideChange', () => {
                slideCounter.textContent = `${this.swiperInstance.realIndex + 1} / ${this.ads.length}`;
            });
            
            swiperContainer.appendChild(slideCounter);
        }
    }

    /**
     * Optimize performance by managing DOM elements
     */
    optimizePerformance() {
        if (!this.swiperInstance) return;
        
        // Lazy load nearby slides
        const activeIndex = this.swiperInstance.activeIndex;
        const slides = this.swiperInstance.slides;
        
        slides.forEach((slide, index) => {
            const distance = Math.abs(index - activeIndex);
            
            if (distance <= 1) {
                // Load media for current and adjacent slides
                this.loadSlideMedia(slide);
            } else if (distance > 3) {
                // Unload distant slides to save memory
                this.unloadSlideMedia(slide);
            }
        });
    }

    /**
     * Load media for a slide
     */
    loadSlideMedia(slide) {
        const lazyMedia = slide.querySelectorAll('[data-src]');
        lazyMedia.forEach(media => {
            const src = media.dataset.src;
            if (src && !media.src) {
                media.src = src;
                media.removeAttribute('data-src');
            }
        });
    }

    /**
     * Unload media for a slide (for memory optimization)
     */
    unloadSlideMedia(slide) {
        // Only unload if there are many slides
        if (this.ads.length < 10) return;
        
        const videos = slide.querySelectorAll('video');
        videos.forEach(video => {
            video.pause();
            // Optionally remove src to free memory
            // video.removeAttribute('src');
        });
    }

    /**
     * Show empty state when no ads
     */
    showEmptyState() {
        const container = document.querySelector('.ads-swiper-container');
        if (container) {
            container.innerHTML = `
                <div class="no-content">
                    <i class="bi bi-image"></i>
                    <p>Check back later for special offers!</p>
                </div>
            `;
        }
    }

    /**
     * Update ads data and refresh swiper
     */
    updateAds(newAds) {
        this.ads = newAds || [];
        
        if (this.swiperInstance) {
            this.swiperInstance.destroy(true, true);
            this.swiperInstance = null;
        }
        
        if (this.ads.length > 0) {
            this.initializeSwiper();
        } else {
            this.showEmptyState();
        }
    }

    /**
     * Destroy swiper instance
     */
    destroy() {
        if (this.swiperInstance) {
            this.swiperInstance.destroy(true, true);
            this.swiperInstance = null;
        }
        this.isInitialized = false;
    }

    /**
     * Go to specific slide
     */
    goToSlide(index) {
        if (this.swiperInstance && index >= 0 && index < this.ads.length) {
            this.swiperInstance.slideTo(index);
        }
    }

    /**
     * Enable/disable autoplay
     */
    toggleAutoplay() {
        if (!this.swiperInstance) return;
        
        if (this.swiperInstance.autoplay.running) {
            this.swiperInstance.autoplay.stop();
        } else {
            this.swiperInstance.autoplay.start();
        }
    }

    /**
     * Get current slide info
     */
    getCurrentSlideInfo() {
        if (!this.swiperInstance) return null;
        
        return {
            activeIndex: this.swiperInstance.activeIndex,
            realIndex: this.swiperInstance.realIndex,
            total: this.ads.length,
            isFirst: this.swiperInstance.isBeginning,
            isLast: this.swiperInstance.isEnd
        };
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for dashboard config to be ready
    if (typeof dashboardPhpConfig !== 'undefined' && dashboardPhpConfig.ads) {
        // Create global instance
        window.dashboardSwiper = new DashboardSwiper();
        window.dashboardSwiper.init(dashboardPhpConfig.ads);
    }
});

// Handle visibility change to pause/resume autoplay
document.addEventListener('visibilitychange', function() {
    if (window.dashboardSwiper && window.dashboardSwiper.swiperInstance) {
        if (document.hidden) {
            window.dashboardSwiper.swiperInstance.autoplay.stop();
            window.dashboardSwiper.pauseAllVideos();
        } else {
            window.dashboardSwiper.swiperInstance.autoplay.start();
            window.dashboardSwiper.resumeActiveVideo();
        }
    }
});

// Export for global use
window.DashboardSwiper = DashboardSwiper;