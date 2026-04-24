/**
 * Stitch House - 3D Effects & Premium Interactions
 * Mouse-tracking tilt, floating particles, and enhanced interactions
 * Does NOT modify any PHP logic or existing cart/auth functionality
 */

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // 1. 3D CARD TILT EFFECT (Mouse Tracking)
    // ==========================================
    function initTiltCards() {
        const cards = document.querySelectorAll('.shop-product-card');

        cards.forEach(card => {
            card.addEventListener('mousemove', function (e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -8;
                const rotateY = ((x - centerX) / centerX) * 8;

                card.classList.add('tilt-active');
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-12px)`;
            });

            card.addEventListener('mouseleave', function () {
                card.classList.remove('tilt-active');
                card.style.transform = '';
            });
        });
    }

    // ==========================================
    // 2. BENTO CARD — no tilt (flip handled by CSS)
    // ==========================================
    function initBentoTilt() {
        // Flip is handled purely by CSS :hover on .bento-flip-inner
    }

    // ==========================================
    // 3. FLOATING GOLD PARTICLE BACKGROUND
    // ==========================================
    function initParticles() {
        // Only on homepage
        if (!document.querySelector('.carousel') || window.innerWidth < 769) return;

        const canvas = document.createElement('canvas');
        canvas.id = 'particle-canvas';
        document.body.prepend(canvas);

        const ctx = canvas.getContext('2d');
        let particles = [];
        const particleCount = 40;

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        class Particle {
            constructor() {
                this.reset();
            }

            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2.5 + 0.5;
                this.speedX = (Math.random() - 0.5) * 0.4;
                this.speedY = Math.random() * -0.3 - 0.1;
                this.opacity = Math.random() * 0.4 + 0.1;
                this.fadeSpeed = Math.random() * 0.003 + 0.001;
                this.growing = Math.random() > 0.5;
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.growing) {
                    this.opacity += this.fadeSpeed;
                    if (this.opacity >= 0.5) this.growing = false;
                } else {
                    this.opacity -= this.fadeSpeed;
                    if (this.opacity <= 0) this.reset();
                }

                if (this.y < -10 || this.x < -10 || this.x > canvas.width + 10) {
                    this.reset();
                    this.y = canvas.height + 10;
                }
            }

            draw() {
                ctx.save();
                ctx.globalAlpha = this.opacity;
                ctx.fillStyle = '#C9A96E';
                ctx.shadowColor = '#C9A96E';
                ctx.shadowBlur = this.size * 3;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            }
        }

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animate);
        }
        animate();
    }

    // ==========================================
    // 4. PARALLAX DEPTH ON CAROUSEL
    // ==========================================
    function initCarouselParallax() {
        const carousel = document.querySelector('.carousel');
        if (!carousel) return;

        carousel.addEventListener('mousemove', function (e) {
            const rect = carousel.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            const activeSlide = carousel.querySelector('.carousel-item.active-slide');
            if (!activeSlide) return;

            const caption = activeSlide.querySelector('.carousel-caption');
            if (caption) {
                caption.style.transform = `translateZ(60px) translateX(${x * 20}px) translateY(${y * 15}px)`;
            }
        });

        carousel.addEventListener('mouseleave', function () {
            const caption = carousel.querySelector('.active-slide .carousel-caption');
            if (caption) {
                caption.style.transform = 'translateZ(60px)';
                caption.style.transition = 'transform 0.5s ease';
                setTimeout(() => { caption.style.transition = ''; }, 500);
            }
        });
    }

    // ==========================================
    // 5. FABRIC SLIDER 3D TILT
    // ==========================================
    function initSliderTilt() {
        const sliderItems = document.querySelectorAll('.slider-item');

        sliderItems.forEach(item => {
            item.addEventListener('mousemove', function (e) {
                const rect = item.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -10;
                const rotateY = ((x - centerX) / centerX) * 10;

                item.style.transform = `perspective(600px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
                item.style.transition = 'none';
            });

            item.addEventListener('mouseleave', function () {
                item.style.transform = '';
                item.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            });
        });
    }

    // ==========================================
    // 6. MAGNETIC BUTTON EFFECT
    // ==========================================
    function initMagneticButtons() {
        const buttons = document.querySelectorAll(
            '.btn-shop-now, .btn-view-all, .checkout-btn, .order-popup-btn'
        );

        buttons.forEach(btn => {
            btn.addEventListener('mousemove', function (e) {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                btn.style.transform = `translateY(-3px) translateX(${x * 0.15}px) translateY(${y * 0.15}px)`;
            });

            btn.addEventListener('mouseleave', function () {
                btn.style.transform = '';
            });
        });
    }

    // ==========================================
    // 7. SCROLL PROGRESS BAR
    // ==========================================
    function initScrollProgress() {
        const bar = document.createElement('div');
        bar.id = 'scroll-progress';
        document.body.prepend(bar);

        window.addEventListener('scroll', function () {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (scrollTop / docHeight) * 100;
            bar.style.width = progress + '%';
        });
    }

    // ==========================================
    // 8. COUNTER ANIMATION
    // ==========================================
    function initCounterAnimation() {
        const counters = document.querySelectorAll('.stat-number');
        if (!counters.length) return;

        const counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'));
                    const suffix = el.getAttribute('data-suffix') || '';
                    const duration = 2000;
                    const step = target / (duration / 16);
                    let current = 0;

                    function updateCounter() {
                        current += step;
                        if (current >= target) {
                            el.textContent = target + suffix;
                        } else {
                            el.textContent = Math.floor(current) + suffix;
                            requestAnimationFrame(updateCounter);
                        }
                    }
                    updateCounter();
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function (c) { counterObserver.observe(c); });
    }

    // ==========================================
    // 9. CURSOR GOLD TRAIL
    // ==========================================
    function initCursorTrail() {
        if (window.innerWidth < 769 || 'ontouchstart' in window) return;

        const canvas = document.createElement('canvas');
        canvas.id = 'cursor-trail-canvas';
        document.body.appendChild(canvas);
        const ctx = canvas.getContext('2d');

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        var particles = [];
        var mouseX = 0, mouseY = 0;

        document.addEventListener('mousemove', function (e) {
            mouseX = e.clientX;
            mouseY = e.clientY;

            for (var i = 0; i < 2; i++) {
                particles.push({
                    x: mouseX + (Math.random() - 0.5) * 8,
                    y: mouseY + (Math.random() - 0.5) * 8,
                    size: Math.random() * 2.5 + 0.5,
                    life: 1,
                    decay: Math.random() * 0.03 + 0.015,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5 - 0.3
                });
            }
        });

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            for (var i = particles.length - 1; i >= 0; i--) {
                var p = particles[i];
                p.x += p.vx;
                p.y += p.vy;
                p.life -= p.decay;

                if (p.life <= 0) {
                    particles.splice(i, 1);
                    continue;
                }

                ctx.save();
                ctx.globalAlpha = p.life * 0.6;
                ctx.fillStyle = '#C9A96E';
                ctx.shadowColor = '#C9A96E';
                ctx.shadowBlur = p.size * 2;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size * p.life, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            }

            if (particles.length > 80) {
                particles.splice(0, particles.length - 80);
            }

            requestAnimationFrame(animate);
        }
        animate();
    }

    // ==========================================
    // 10. 3D MAP PIN DROP (Contact Page)
    // ==========================================
    function initMapPin() {
        var mapSection = document.querySelector('.map-section');
        if (!mapSection) return;

        // Don't add if already exists
        if (mapSection.querySelector('.map-pin-3d')) return;

        var pin = document.createElement('div');
        pin.className = 'map-pin-3d';
        pin.innerHTML = '<i class="fas fa-map-marker-alt pin-icon"></i><div class="pin-shadow"></div>';
        mapSection.style.position = 'relative';
        mapSection.appendChild(pin);

        // Trigger animation when map scrolls into view
        var pinObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    pin.style.animation = 'none';
                    void pin.offsetHeight; // Force reflow
                    pin.style.animation = '';
                    pinObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        pinObserver.observe(mapSection);
    }

    // ==========================================
    // 11. PAGE TRANSITION (simple fade)
    // ==========================================
    function initPageTransitions() { }

    // ==========================================
    // INITIALIZE ALL EFFECTS
    // ==========================================
    initTiltCards();
    initBentoTilt();
    initParticles();
    initCarouselParallax();
    initSliderTilt();
    initMagneticButtons();
    initScrollProgress();
    initCounterAnimation();
    initCursorTrail();
    initMapPin();
    initPageTransitions();

    // Re-init tilt cards after AJAX adds new products
    const observer = new MutationObserver(function () {
        initTiltCards();
    });

    const productGrids = document.querySelectorAll('.product-grid');
    productGrids.forEach(grid => {
        observer.observe(grid, { childList: true });
    });
});
