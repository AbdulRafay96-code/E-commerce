<?php
/**
 * Common Scripts Component
 * Include this before closing </body> tag
 */
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // HEADER SCROLL EFFECT
    // ==========================================
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('header-scrolled', window.scrollY > 50);
        });
    }

    // ==========================================
    // SIDE NAVIGATION
    // ==========================================
    const sideNavToggle = document.querySelector('.menu-toggle');
    const sideNav = document.querySelector('.side-nav');
    const sideNavBackdrop = document.querySelector('.side-nav-backdrop');
    const sideNavClose = document.querySelector('.close-sidenav');

    if (sideNavToggle && sideNav) {
        sideNavToggle.addEventListener('click', () => {
            sideNav.classList.add('open');
            sideNavBackdrop.classList.add('active');
            document.body.classList.add('no-scroll');
        });
    }

    function closeSideNav() {
        if (sideNav) {
            sideNav.classList.remove('open');
            sideNavBackdrop.classList.remove('active');
            document.body.classList.remove('no-scroll');
        }
    }

    if (sideNavClose) sideNavClose.addEventListener('click', closeSideNav);
    if (sideNavBackdrop) sideNavBackdrop.addEventListener('click', closeSideNav);

    // ==========================================
    // BACK TO TOP BUTTON
    // ==========================================
    const backToTopBtn = document.querySelector('.back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            backToTopBtn.classList.toggle('show', window.pageYOffset > 300);
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ==========================================
    // MOBILE NAV TOGGLE
    // ==========================================
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');
    if (menuToggle && nav) {
        menuToggle.addEventListener('click', () => nav.classList.toggle('active'));
    }

    // ==========================================
    // IMAGE ERROR HANDLING
    // ==========================================
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            if (!this.src.includes('placeholder')) {
                const src = this.src;
                if (src.endsWith('.jpg')) {
                    this.src = src.replace('.jpg', '.webp');
                } else if (src.endsWith('.webp')) {
                    this.src = 'images/placeholder.jpg';
                } else {
                    this.src = 'images/placeholder.jpg';
                }
            }
        });
    });

    // ==========================================
    // ANNOUNCEMENT BAR
    // ==========================================
    const announcementBar = document.getElementById('announcement-bar');
    const announcementClose = document.getElementById('announcement-close');
    if (announcementBar && sessionStorage.getItem('announcementClosed') === 'true') {
        announcementBar.style.display = 'none';
    }
    if (announcementClose) {
        announcementClose.addEventListener('click', () => {
            announcementBar.style.display = 'none';
            sessionStorage.setItem('announcementClosed', 'true');
        });
    }

    // ==========================================
    // SCROLL REVEAL ANIMATIONS
    // ==========================================
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // ==========================================
    // CART DRAWER
    // ==========================================
    const cartDrawer = document.getElementById('cart-drawer');
    const cartBackdrop = document.getElementById('cart-drawer-backdrop');
    const cartClose = document.getElementById('cart-drawer-close');
    const cartContinue = document.getElementById('cart-drawer-continue');
    const cartTrigger = document.getElementById('cart-drawer-trigger');

    function openCartDrawer(e) {
        if (e) e.preventDefault();
        if (cartDrawer) cartDrawer.classList.add('open');
        if (cartBackdrop) cartBackdrop.classList.add('active');
        document.body.classList.add('no-scroll');
    }

    function closeCartDrawer() {
        if (cartDrawer) cartDrawer.classList.remove('open');
        if (cartBackdrop) cartBackdrop.classList.remove('active');
        document.body.classList.remove('no-scroll');
    }

    if (cartTrigger) {
        const cartLink = cartTrigger.querySelector('a');
        if (cartLink) {
            cartLink.addEventListener('click', openCartDrawer);
        }
    }
    const cartFloatBtn = document.getElementById('cart-float-btn');
    if (cartFloatBtn) {
        cartFloatBtn.addEventListener('click', openCartDrawer);
        initCartFloatDrag(cartFloatBtn);
    }
    if (cartClose) cartClose.addEventListener('click', closeCartDrawer);
    if (cartBackdrop) cartBackdrop.addEventListener('click', closeCartDrawer);
    if (cartContinue) cartContinue.addEventListener('click', closeCartDrawer);

    // Floating cart button — drag anywhere, click to open drawer
    function initCartFloatDrag(btn) {
        const STORAGE_KEY = 'cartFloatPos';
        const DRAG_THRESHOLD = 5; // px to distinguish click from drag
        let isDragging = false;
        let didDrag = false;
        let startX, startY, startLeft, startTop;

        // Restore saved position (clamped to current viewport)
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
            if (saved && typeof saved.left === 'number' && typeof saved.top === 'number') {
                const maxLeft = window.innerWidth - btn.offsetWidth;
                const maxTop = window.innerHeight - btn.offsetHeight;
                btn.style.left = Math.max(0, Math.min(saved.left, maxLeft)) + 'px';
                btn.style.top = Math.max(0, Math.min(saved.top, maxTop)) + 'px';
                btn.style.right = 'auto';
                btn.style.bottom = 'auto';
            }
        } catch (_) {}

        function getPos(e) {
            if (e.touches && e.touches[0]) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            return { x: e.clientX, y: e.clientY };
        }

        function onStart(e) {
            const rect = btn.getBoundingClientRect();
            const pos = getPos(e);
            startX = pos.x;
            startY = pos.y;
            startLeft = rect.left;
            startTop = rect.top;
            isDragging = true;
            didDrag = false;
            btn.style.cursor = 'grabbing';
            if (e.touches) e.preventDefault();
        }

        function onMove(e) {
            if (!isDragging) return;
            const pos = getPos(e);
            const dx = pos.x - startX;
            const dy = pos.y - startY;
            if (!didDrag && Math.hypot(dx, dy) > DRAG_THRESHOLD) didDrag = true;
            if (!didDrag) return;
            if (e.cancelable) e.preventDefault();

            const maxLeft = window.innerWidth - btn.offsetWidth;
            const maxTop = window.innerHeight - btn.offsetHeight;
            btn.style.left = Math.max(0, Math.min(startLeft + dx, maxLeft)) + 'px';
            btn.style.top  = Math.max(0, Math.min(startTop + dy, maxTop)) + 'px';
            btn.style.right = 'auto';
            btn.style.bottom = 'auto';
        }

        function onEnd() {
            if (!isDragging) return;
            isDragging = false;
            btn.style.cursor = 'grab';
            if (didDrag) {
                const rect = btn.getBoundingClientRect();
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify({ left: rect.left, top: rect.top }));
                } catch (_) {}
            }
        }

        // Suppress click immediately after a drag so we don't open the drawer
        btn.addEventListener('click', function(e) {
            if (didDrag) {
                e.stopImmediatePropagation();
                e.preventDefault();
                // Reset flag after this tick so next click works
                setTimeout(() => { didDrag = false; }, 0);
            }
        }, true); // capture phase — runs before the openCartDrawer listener

        btn.addEventListener('mousedown', onStart);
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onEnd);
        btn.addEventListener('touchstart', onStart, { passive: false });
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('touchend', onEnd);

        // Keep button on-screen when the window resizes
        window.addEventListener('resize', () => {
            if (btn.style.left) {
                const maxLeft = window.innerWidth - btn.offsetWidth;
                const maxTop = window.innerHeight - btn.offsetHeight;
                btn.style.left = Math.max(0, Math.min(parseInt(btn.style.left, 10), maxLeft)) + 'px';
                btn.style.top = Math.max(0, Math.min(parseInt(btn.style.top, 10), maxTop)) + 'px';
            }
        });
    }

    // ==========================================
    // TESTIMONIAL CAROUSEL
    // ==========================================
    const testimonialSlides = document.querySelectorAll('.testimonial-slide');
    const testimonialDotsContainer = document.querySelector('.testimonial-dots');

    if (testimonialSlides.length > 1 && testimonialDotsContainer) {
        let currentTestimonial = 0;

        // Create dots
        testimonialSlides.forEach((_, i) => {
            const dot = document.createElement('button');
            dot.className = 'testimonial-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => goToTestimonial(i));
            testimonialDotsContainer.appendChild(dot);
        });

        function goToTestimonial(index) {
            testimonialSlides.forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.testimonial-dot').forEach(d => d.classList.remove('active'));
            testimonialSlides[index].classList.add('active');
            document.querySelectorAll('.testimonial-dot')[index].classList.add('active');
            currentTestimonial = index;
        }

        setInterval(() => {
            goToTestimonial((currentTestimonial + 1) % testimonialSlides.length);
        }, 5000);
    }

    // ==========================================
    // USER DROPDOWN
    // ==========================================
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.user-dropdown').forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
});

// Global function for user dropdown toggle
function toggleUserDropdown(btn) {
    const dropdown = btn.closest('.user-dropdown');
    if (dropdown) dropdown.classList.toggle('active');
}

// Cart drawer quantity update (PHP version - full page form post)
function updateDrawerQty(index, delta) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart.php';
    form.innerHTML = '<input type="hidden" name="action" value="update_quantity">' +
        '<input type="hidden" name="index" value="' + index + '">' +
        '<input type="hidden" name="quantity" value="' + Math.max(1, delta) + '">';
    document.body.appendChild(form);
    form.submit();
}

function removeDrawerItem(index) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart.php';
    form.innerHTML = '<input type="hidden" name="action" value="remove_item">' +
        '<input type="hidden" name="index" value="' + index + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>
<script src="3d-effects.js"></script>
