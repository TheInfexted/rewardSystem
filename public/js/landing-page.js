// Landing Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe content elements
    document.querySelectorAll('.content-image-item, .header-image-wrapper, .footer-image-wrapper').forEach(el => {
        observer.observe(el);
    });

    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        .content-image-item,
        .header-image-wrapper,
        .footer-image-wrapper {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-in {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);

    // Lazy loading for images
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }
    
    // Background Music Player
    document.addEventListener('DOMContentLoaded', function() {
        const audio = document.getElementById('bgMusic');
        const toggleBtn = document.getElementById('musicToggle');
        const icon = document.getElementById('musicIcon');
        
        // Only run if music player exists
        if (!audio || !toggleBtn) return;
        
        // Music player functionality
        toggleBtn.addEventListener('click', function() {
            if (audio.paused) {
                audio.play();
                icon.classList.add('bi-pause-fill');
                icon.classList.remove('bi-music-note-beamed');
                toggleBtn.classList.add('pulse-animation');
            } else {
                audio.pause();
                icon.classList.remove('bi-pause-fill');
                icon.classList.add('bi-music-note-beamed');
                toggleBtn.classList.remove('pulse-animation');
            }
        });
        
        // Handle audio events
        audio.addEventListener('ended', function() {
            icon.classList.remove('bi-pause-fill');
            icon.classList.add('bi-music-note-beamed');
            toggleBtn.classList.remove('pulse-animation');
        });
    });
});