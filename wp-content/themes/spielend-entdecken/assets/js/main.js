// Spielend Entdecken - Main JavaScript
// Performance-first, vanilla JS, no dependencies

(function() {
    'use strict';

    // ============================================
    // CONFIGURATION
    // ============================================
    const SE = window.se_ajax || {
        ajaxurl: '/wp-admin/admin-ajax.php',
        nonce: '',
        cart_url: '/warenkorb/',
        checkout_url: '/kasse/'
    };

    // ============================================
    // UTILITIES
    // ============================================
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    const debounce = (fn, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    const throttle = (fn, limit) => {
        let inThrottle;
        return (...args) => {
            if (!inThrottle) {
                fn.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    const fetchAPI = (action, data = {}) => {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', SE.nonce);
        Object.entries(data).forEach(([k, v]) => formData.append(k, v));
        return fetch(SE.ajaxurl, { method: 'POST', body: formData })
            .then(r => r.json())
            .catch(err => ({ success: false, error: err.message }));
    };

    // ============================================
    // MOBILE NAVIGATION
    // ============================================
    function initMobileNav() {
        const toggle = $('.se-mobile-toggle');
        const nav = $('.se-nav');
        const overlay = document.createElement('div');
        overlay.className = 'se-nav-overlay';
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:99;opacity:0;visibility:hidden;transition:all .3s';
        document.body.appendChild(overlay);

        function openNav() {
            nav.classList.add('open');
            overlay.style.opacity = '1';
            overlay.style.visibility = 'visible';
            document.body.style.overflow = 'hidden';
        }

        function closeNav() {
            nav.classList.remove('open');
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
            document.body.style.overflow = '';
        }

        toggle?.addEventListener('click', openNav);
        overlay.addEventListener('click', closeNav);
        $$('.se-nav a').forEach(a => a.addEventListener('click', closeNav));
    }

    // ============================================
    // STICKY HEADER
    // ============================================
    function initStickyHeader() {
        const header = $('.se-header');
        if (!header) return;

        let lastScroll = 0;
        const threshold = 100;

        window.addEventListener('scroll', throttle(() => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > threshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        }, 16));
    }

    // ============================================
    // SEARCH AUTOCOMPLETE
    // ============================================
    function initSearchAutocomplete() {
        const input = $('.se-search-form input');
        if (!input) return;

        let debounceTimer;
        let dropdown = null;

        function createDropdown() {
            dropdown = document.createElement('div');
            dropdown.className = 'se-search-dropdown';
            dropdown.style.cssText = 'position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #eee;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);margin-top:8px;max-height:400px;overflow-y:auto;z-index:100;display:none';
            input.parentNode.appendChild(dropdown);
        }

        input.addEventListener('input', debounce(async function() {
            const query = this.value.trim();
            if (query.length < 2) {
                dropdown?.classList.remove('show');
                return;
            }

            if (!dropdown) createDropdown();

            try {
                const res = await fetch(`${SE.ajaxurl}?action=se_autocomplete_search&nonce=${SE.nonce}&q=${encodeURIComponent(query)}`);
                const data = await res.json();
                if (data.success && data.results.length) {
                    dropdown.innerHTML = data.results.map(r => `
                        <a href="${r.url}" class="se-search-result" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0">
                            <img src="${r.image}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px">
                            <div>
                                <div style="font-weight:500">${r.title}</div>
                                <div style="font-size:.8rem;color:#666">${r.type === 'product' ? 'Produkt' : 'Beitrag'}${r.price ? ' · ' + r.price : ''}</div>
                            </div>
                        </a>
                    `).join('');
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            } catch (e) {
                dropdown.style.display = 'none';
            }
        }, 300));

        document.addEventListener('click', e => {
            if (dropdown && !e.target.closest('.se-search-form')) {
                dropdown.style.display = 'none';
            }
        });
    }

    // ============================================
    // AJAX ADD TO CART
    // ============================================
    function initAjaxAddToCart() {
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.se-quick-add, .se-product-footer .button, .single_add_to_cart_button');
            if (!btn) return;

            const productId = btn.dataset.productId || btn.dataset.product_id || btn.value;
            if (!productId) return;

            if (btn.classList.contains('disabled') || btn.disabled) return;

            e.preventDefault();
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Wird hinzugefügt...';
            btn.classList.add('loading');

            try {
                const formData = new FormData();
                formData.append('action', 'woocommerce_ajax_add_to_cart');
                formData.append('product_id', productId);
                formData.append('quantity', 1);

                const res = await fetch(SE.ajaxurl, { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    btn.textContent = 'Hinzugefügt! ✓';
                    btn.classList.add('added');
                    updateMiniCart();
                    showNotification('Produkt in den Warenkorb gelegt', 'success');
                } else {
                    throw new Error(data.data || 'Fehler beim Hinzufügen');
                }
            } catch (err) {
                btn.textContent = 'Fehler';
                btn.classList.add('error');
                showNotification(err.message, 'error');
            }

            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
                btn.classList.remove('loading', 'added', 'error');
            }, 2000);
        });
    }

    function updateMiniCart() {
        const countEl = $('.se-mini-cart-count');
        if (countEl) {
            fetch(`${SE.ajaxurl}?action=woocommerce_get_cart_count&nonce=${SE.nonce}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        countEl.textContent = data.count;
                        countEl.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                });
        }
    }

    // ============================================
    // QUICK VIEW MODAL
    // ============================================
    function initQuickView() {
        let modal = null;

        function createModal() {
            modal = document.createElement('div');
            modal.className = 'se-quick-view-modal';
            modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;transition:all .3s';
            modal.innerHTML = `
                <div class="se-quick-view-content" style="background:#fff;max-width:900px;width:100%;max-height:90vh;overflow-y:auto;border-radius:16px;position:relative">
                    <button class="se-modal-close" style="position:absolute;top:16px;right:16px;width:40px;height:40px;border-radius:50%;background:#f0f0f0;border:none;cursor:pointer;z-index:10;display:flex;align-items:center;justify-content:center">×</button>
                    <div class="se-quick-view-body" style="padding:32px;display:grid;grid-template-columns:1fr 1fr;gap:32px"></div>
                </div>
            `;
            document.body.appendChild(modal);

            modal.querySelector('.se-modal-close').addEventListener('click', closeModal);
            modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
        }

        function openModal(productId) {
            if (!modal) createModal();
            const body = modal.querySelector('.se-quick-view-body');
            body.innerHTML = '<div class="se-skeleton" style="height:400px"></div>';
            modal.style.opacity = '1';
            modal.style.visibility = 'visible';
            document.body.style.overflow = 'hidden';

            fetch(`${SE.ajaxurl}?action=se_quick_view&nonce=${SE.nonce}&product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        modal.querySelector('.se-quick-view-body').innerHTML = data.html;
                        initQuickViewActions();
                    }
                });
        }

        function closeModal() {
            if (modal) {
                modal.style.opacity = '0';
                modal.style.visibility = 'hidden';
                document.body.style.overflow = '';
            }
        }

        function initQuickViewActions() {
            if (!modal) return;
            const form = modal.querySelector('form.cart');
            if (form) {
                form.addEventListener('submit', async e => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    formData.append('action', 'woocommerce_ajax_add_to_cart');
                    const btn = form.querySelector('button[type="submit"]');
                    const originalText = btn.textContent;
                    btn.disabled = true;
                    btn.textContent = 'Wird hinzugefügt...';

                    try {
                        const res = await fetch(SE.ajaxurl, { method: 'POST', body: formData });
                        const data = await res.json();
                        if (data.success) {
                            showNotification('In den Warenkorb gelegt', 'success');
                            closeModal();
                            updateMiniCart();
                        }
                    } catch (err) {
                        showNotification('Fehler: ' + err.message, 'error');
                    } finally {
                        btn.textContent = originalText;
                        btn.disabled = false;
                    }
                });
            }
        }

        document.addEventListener('click', e => {
            const btn = e.target.closest('.se-quick-view');
            if (btn) {
                e.preventDefault();
                openModal(btn.dataset.productId);
            }
        });
    }

    // ============================================
    // PRODUCT GALLERY (ZOOM, THUMBNAILS, VIDEO)
    // ============================================
    function initProductGallery() {
        const gallery = $('.woocommerce-product-gallery');
        if (!gallery) return;

        const mainImage = $('.woocommerce-product-gallery__wrapper .slide:first-child img');
        const thumbnails = $$('.woocommerce-product-gallery__trigger button');
        let currentIndex = 0;

        function showImage(index) {
            const slides = $$('.woocommerce-product-gallery__wrapper .slide');
            const thumbs = $$('.woocommerce-product-gallery__trigger button');
            slides.forEach((slide, i) => slide.style.display = i === index ? 'block' : 'none');
            thumbs.forEach((thumb, i) => thumb.classList.toggle('flex-active', i === index));
            currentIndex = index;
        }

        $$('.woocommerce-product-gallery__trigger button').forEach((btn, i) => {
            btn.addEventListener('click', () => showImage(i));
        });

        // Keyboard navigation
        document.addEventListener('keydown', e => {
            if (e.target.closest('.woocommerce-product-gallery')) {
                if (e.key === 'ArrowRight') showImage((currentIndex + 1) % $$('.woocommerce-product-gallery__wrapper .slide').length);
                if (e.key === 'ArrowLeft') showImage((currentIndex - 1 + $$('.woocommerce-product-gallery__wrapper .slide').length) % $$('.woocommerce-product-gallery__wrapper .slide').length);
            }
        });

        // Zoom on hover
        const mainImg = $('.woocommerce-product-gallery__wrapper .slide:first-child img');
        if (mainImg) {
            mainImg.style.cursor = 'zoom-in';
            mainImg.addEventListener('click', () => {
                openLightbox(mainImg.src);
            });
        }

        function openLightbox(src) {
            const lightbox = document.createElement('div');
            lightbox.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.95);z-index:2000;display:flex;align-items:center;justify-content:center;cursor:zoom-out';
            lightbox.innerHTML = `<img src="${src}" style="max-width:90%;max-height:90vh" alt=""><button style="position:absolute;top:20px;right:30px;background:none;border:none;color:#fff;font-size:3rem;cursor:pointer">×</button>`;
            document.body.appendChild(lightbox);
            document.body.style.overflow = 'hidden';
            lightbox.addEventListener('click', () => { lightbox.remove(); document.body.style.overflow = ''; });
        }
    }

    // ============================================
    // QUANTITY INCREMENT/DECREMENT
    // ============================================
    function initQuantityControls() {
        document.addEventListener('click', e => {
            const btn = e.target.closest('.qty-btn');
            if (!btn) return;

            const input = btn.parentElement.querySelector('input.qty');
            if (!input) return;

            const min = parseFloat(input.min) || 1;
            const max = parseFloat(input.max) || 99;
            let value = parseFloat(input.value) || 1;

            if (btn.classList.contains('plus')) {
                value = Math.min(value + 1, max);
            } else if (btn.classList.contains('minus')) {
                value = Math.max(value - 1, min);
            }

            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // ============================================
    // SMOOTH SCROLL
    // ============================================
    function initSmoothScroll() {
        document.addEventListener('click', e => {
            const link = e.target.closest('a[href^="#"]');
            if (!link) return;

            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.pushState(null, '', link.getAttribute('href'));
            }
        });
    }

    // ============================================
    // INTERSECTION OBSERVER ANIMATIONS
    // ============================================
    function initScrollAnimations() {
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('se-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        $$('.se-card, .se-product-card, .wp-block-group, .wp-block-columns, .wp-block-cover').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Add visible class style
        const style = document.createElement('style');
        style.textContent = '.se-visible { opacity: 1 !important; transform: translateY(0) !important; }';
        document.head.appendChild(style);
    }

    // ============================================
    // FORM VALIDATION
    // ============================================
    function initFormValidation() {
        $$('form[data-validate]').forEach(form => {
            form.addEventListener('submit', e => {
                let valid = true;
                $$('[required]', form).forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('error');
                        valid = false;
                    } else {
                        field.classList.remove('error');
                    }
                    if (field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                        field.classList.add('error');
                        valid = false;
                    }
                });
                if (!valid) e.preventDefault();
            });

            $$('input, textarea, select', form).forEach(field => {
                field.addEventListener('blur', () => {
                    if (field.hasAttribute('required') && !field.value.trim()) {
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                });
            });
        });
    }

    // ============================================
    // COOKIE CONSENT
    // ============================================
    function initCookieConsent() {
        if (localStorage.getItem('se_cookie_consent')) return;

        const banner = document.createElement('div');
        banner.className = 'se-cookie-banner';
        banner.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#2D2D2D;color:#fff;padding:16px 20px;box-shadow:0 -4px 24px rgba(0,0,0,.2);z-index:2000;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;padding-bottom:max(16px,env(safe-area-inset-bottom))';
        banner.innerHTML = `
            <p style="margin:0;font-size:.9rem;max-width:600px">Wir nutzen Cookies für Analyse & Marketing. <a href="/datenschutz" style="color:#F9C80E">Details</a></p>
            <div style="display:flex;gap:8px">
                <button class="se-cookie-accept" style="padding:10px 20px;border-radius:50px;background:#FF6B35;color:#fff;border:none;font-weight:600">Alle akzeptieren</button>
                <button class="se-cookie-necessary" style="padding:10px 20px;border-radius:50px;background:transparent;color:#fff;border:2px solid #fff;font-weight:600">Nur notwendig</button>
            </div>
        `;
        document.body.appendChild(banner);

        banner.querySelector('.se-cookie-accept').addEventListener('click', () => {
            localStorage.setItem('se_cookie_consent', 'all');
            banner.remove();
            loadAnalytics();
        });
        banner.querySelector('.se-cookie-necessary').addEventListener('click', () => {
            localStorage.setItem('se_cookie_consent', 'necessary');
            banner.remove();
        });

        function loadAnalytics() {
            if (window.se_gtm_id) {
                // GTM or GA loading logic here
            }
        }
    }

    // ============================================
    // NOTIFICATIONS
    // ============================================
    function showNotification(message, type = 'info') {
        const container = document.getElementById('se-notifications') || createNotificationContainer();
        const notification = document.createElement('div');
        notification.className = `se-notification se-notification-${type}`;
        notification.style.cssText = `
            background: ${type === 'success' ? '#2B7A62' : type === 'error' ? '#dc3545' : '#FF6B35'};
            color: white;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,.2);
            animation: se-slide-in 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            max-width: 400px;
        `;
        notification.innerHTML = `
            <span>${message}</span>
            <button style="background:none;border:none;color:white;font-size:1.2rem;cursor:pointer;line-height:1">&times;</button>
        `;
        container.appendChild(notification);

        notification.querySelector('button').addEventListener('click', () => notification.remove());
        setTimeout(() => notification.remove(), 5000);
    }

    function createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'se-notifications';
        container.style.cssText = 'position:fixed;top:90px;right:20px;z-index:3000;display:flex;flex-direction:column;gap:10px';
        document.body.appendChild(container);
        return container;
    }

    // ============================================
    // WISHLIST
    // ============================================
    function initWishlist() {
        document.addEventListener('click', async e => {
            const btn = e.target.closest('.se-wishlist-toggle');
            if (!btn) return;

            e.preventDefault();
            const productId = btn.dataset.productId;
            const isActive = btn.classList.toggle('active');

            try {
                await fetchAPI('se_wishlist_toggle', { product_id: productId, add: isActive });
                btn.setAttribute('aria-label', isActive ? 'Von Wunschliste entfernen' : 'Zur Wunschliste hinzufügen');
                showNotification(isActive ? 'Zur Wunschliste hinzugefügt' : 'Von Wunschliste entfernt', 'success');
            } catch (err) {
                btn.classList.toggle('active');
                showNotification('Fehler: ' + err.message, 'error');
            }
        });
    }

    // ============================================
    // INITIALIZATION
    // ============================================
    function init() {
        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        initMobileNav();
        initStickyHeader();
        initSearchAutocomplete();
        initAjaxAddToCart();
        initQuickView();
        initProductGallery();
        initQuantityControls();
        initSmoothScroll();
        initScrollAnimations();
        initFormValidation();
        initCookieConsent();
        initWishlist();

        // Initial mini cart load
        updateMiniCart();

        console.log('🎮 Spielend Entdecken - Theme JS loaded');
    }

    init();
})();