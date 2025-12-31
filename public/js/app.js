// =========================================
// AGROCONECTA - JAVASCRIPT PRINCIPAL
// Sistema de e-commerce agrícola
// =========================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // === CONFIGURACIÓN GLOBAL ===
    const AgroConecta = {
        config: {
            apiUrl: '/api',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            currency: 'MXN',
            currencySymbol: '$',
            locale: 'es-MX'
        },
        
        // Cache para optimización
        cache: new Map(),
        
        // Event emitter simple
        events: {},
        
        // Emit custom event
        emit: function(event, data) {
            if (this.events[event]) {
                this.events[event].forEach(callback => callback(data));
            }
        },
        
        // Listen to custom event
        on: function(event, callback) {
            if (!this.events[event]) {
                this.events[event] = [];
            }
            this.events[event].push(callback);
        }
    };
    
    window.AgroConecta = AgroConecta;
    
    // === UTILIDADES ===
    const Utils = {
        // Formatear precio
        formatPrice: function(price) {
            return new Intl.NumberFormat(AgroConecta.config.locale, {
                style: 'currency',
                currency: AgroConecta.config.currency
            }).format(price);
        },
        
        // Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    timeout = null;
                    if (!immediate) func(...args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func(...args);
            };
        },
        
        // Throttle function
        throttle: function(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        // Fetch con configuración por defecto
        fetchAPI: async function(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': AgroConecta.config.csrfToken
                }
            };
            
            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };
            
            try {
                const response = await fetch(AgroConecta.config.apiUrl + url, mergedOptions);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        },
        
        // Mostrar loading
        showLoading: function(element) {
            if (element) {
                const loading = element.querySelector('.loading-spinner') || 
                             element.querySelector('.product-loading');
                if (loading) loading.style.display = 'flex';
            } else {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }
        },
        
        // Ocultar loading
        hideLoading: function(element) {
            if (element) {
                const loading = element.querySelector('.loading-spinner') || 
                             element.querySelector('.product-loading');
                if (loading) loading.style.display = 'none';
            } else {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        },
        
        // Mostrar toast notification
        showToast: function(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type} show`;
            
            const icon = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            }[type] || 'info-circle';
            
            toast.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span class="toast-message">${message}</span>
                <button class="toast-close" type="button">&times;</button>
            `;
            
            container.appendChild(toast);
            
            // Auto-remove
            const removeToast = () => {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            };
            
            // Close button
            toast.querySelector('.toast-close').addEventListener('click', removeToast);
            
            // Auto-close
            if (duration > 0) {
                setTimeout(removeToast, duration);
            }
        }
    };
    
    // === NAVEGACIÓN MÓVIL ===
    const MobileNav = {
        init: function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
            const mobileNavClose = document.querySelector('.mobile-nav-close');
            const submenuToggles = document.querySelectorAll('.submenu-toggle');
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', this.toggleMenu);
            }
            
            if (mobileNavClose) {
                mobileNavClose.addEventListener('click', this.closeMenu);
            }
            
            if (mobileNavOverlay) {
                mobileNavOverlay.addEventListener('click', (e) => {
                    if (e.target === mobileNavOverlay) {
                        this.closeMenu();
                    }
                });
            }
            
            // Submenu toggles
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    const submenu = toggle.nextElementSibling;
                    const isOpen = submenu.style.display === 'block';
                    
                    // Close all submenus
                    document.querySelectorAll('.submenu').forEach(sm => {
                        sm.style.display = 'none';
                    });
                    
                    // Toggle current submenu
                    if (!isOpen) {
                        submenu.style.display = 'block';
                    }
                    
                    // Update icon
                    const icon = toggle.querySelector('.fa-chevron-down');
                    if (icon) {
                        icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                    }
                });
            });
        },
        
        toggleMenu: function() {
            const overlay = document.querySelector('.mobile-nav-overlay');
            const btn = document.querySelector('.mobile-menu-btn');
            
            overlay.classList.toggle('show');
            btn.setAttribute('aria-expanded', overlay.classList.contains('show'));
            
            // Prevent body scroll when menu is open
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : '';
        },
        
        closeMenu: function() {
            const overlay = document.querySelector('.mobile-nav-overlay');
            const btn = document.querySelector('.mobile-menu-btn');
            
            overlay.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    };
    
    // === DROPDOWN MENUS ===
    const Dropdowns = {
        init: function() {
            const dropdowns = document.querySelectorAll('.dropdown, .user-dropdown, .cart-dropdown');
            
            dropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.nav-link, .user-btn, .cart-link');
                const menu = dropdown.querySelector('.dropdown-menu, .user-menu, .cart-preview');
                
                if (trigger && menu) {
                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.toggle(dropdown);
                    });
                }
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.dropdown, .user-dropdown, .cart-dropdown')) {
                    this.closeAll();
                }
            });
        },
        
        toggle: function(dropdown) {
            const isOpen = dropdown.classList.contains('open');
            this.closeAll();
            
            if (!isOpen) {
                dropdown.classList.add('open');
                const trigger = dropdown.querySelector('.user-btn');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'true');
                }
                
                // Load cart preview if it's cart dropdown
                if (dropdown.classList.contains('cart-dropdown')) {
                    this.loadCartPreview();
                }
            }
        },
        
        closeAll: function() {
            const dropdowns = document.querySelectorAll('.dropdown, .user-dropdown, .cart-dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('open');
                const trigger = dropdown.querySelector('.user-btn');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
        },
        
        loadCartPreview: async function() {
            const cartPreview = document.getElementById('cartPreview');
            if (!cartPreview) return;
            
            try {
                const response = await Utils.fetchAPI('/cart/preview');
                cartPreview.innerHTML = response.html;
            } catch (error) {
                console.error('Error loading cart preview:', error);
                cartPreview.innerHTML = '<div class="cart-error">Error al cargar el carrito</div>';
            }
        }
    };
    
    // === CARRITO DE COMPRAS ===
    const ShoppingCart = {
        init: function() {
            // Add to cart buttons
            document.addEventListener('click', (e) => {
                if (e.target.closest('.add-to-cart')) {
                    e.preventDefault();
                    const btn = e.target.closest('.add-to-cart');
                    this.addToCart(btn);
                }
                
                // Quantity controls
                if (e.target.closest('.quantity-btn')) {
                    const btn = e.target.closest('.quantity-btn');
                    const action = btn.dataset.action;
                    const input = btn.parentNode.querySelector('.quantity-input');
                    
                    if (action === 'increase') {
                        this.increaseQuantity(input);
                    } else if (action === 'decrease') {
                        this.decreaseQuantity(input);
                    }
                }
                
                // Buy now buttons
                if (e.target.closest('.buy-now')) {
                    e.preventDefault();
                    const btn = e.target.closest('.buy-now');
                    this.buyNow(btn);
                }
            });
            
            // Quantity input changes
            document.addEventListener('input', (e) => {
                if (e.target.classList.contains('quantity-input')) {
                    this.validateQuantity(e.target);
                }
            });
        },
        
        addToCart: async function(button) {
            const productId = button.dataset.productId;
            const productCard = button.closest('.product-card');
            const quantityInput = productCard?.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            if (!productId) {
                Utils.showToast('Error: ID de producto no encontrado', 'error');
                return;
            }
            
            Utils.showLoading(productCard);
            button.disabled = true;
            
            try {
                const response = await Utils.fetchAPI('/cart/add', {
                    method: 'POST',
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                });
                
                if (response.success) {
                    Utils.showToast(response.message || 'Producto agregado al carrito', 'success');
                    this.updateCartCount(response.cart_count);
                    
                    // Animate button
                    this.animateAddToCart(button);
                    
                    // Emit event
                    AgroConecta.emit('cart:added', {
                        productId: productId,
                        quantity: quantity,
                        cartCount: response.cart_count
                    });
                } else {
                    Utils.showToast(response.message || 'Error al agregar producto', 'error');
                }
            } catch (error) {
                console.error('Add to cart error:', error);
                Utils.showToast('Error de conexión. Intenta de nuevo.', 'error');
            } finally {
                Utils.hideLoading(productCard);
                button.disabled = false;
            }
        },
        
        buyNow: async function(button) {
            const productId = button.dataset.productId;
            const productCard = button.closest('.product-card');
            const quantityInput = productCard?.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            // First add to cart
            await this.addToCart(button.parentNode.querySelector('.add-to-cart'));
            
            // Then redirect to checkout
            window.location.href = '/checkout';
        },
        
        increaseQuantity: function(input) {
            const currentValue = parseInt(input.value) || 1;
            const maxValue = parseInt(input.getAttribute('max')) || 999;
            
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
                input.dispatchEvent(new Event('input'));
            }
        },
        
        decreaseQuantity: function(input) {
            const currentValue = parseInt(input.value) || 1;
            const minValue = parseInt(input.getAttribute('min')) || 1;
            
            if (currentValue > minValue) {
                input.value = currentValue - 1;
                input.dispatchEvent(new Event('input'));
            }
        },
        
        validateQuantity: function(input) {
            const value = parseInt(input.value);
            const min = parseInt(input.getAttribute('min')) || 1;
            const max = parseInt(input.getAttribute('max')) || 999;
            
            if (isNaN(value) || value < min) {
                input.value = min;
            } else if (value > max) {
                input.value = max;
                Utils.showToast(`Máximo ${max} unidades disponibles`, 'warning');
            }
        },
        
        updateCartCount: function(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
                
                // Add bounce animation
                element.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                }, 200);
            });
        },
        
        animateAddToCart: function(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> ¡Agregado!';
            button.style.backgroundColor = '#28a745';
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.backgroundColor = '';
            }, 2000);
        }
    };
    
    // === LISTA DE DESEOS ===
    const Wishlist = {
        init: function() {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.wishlist')) {
                    e.preventDefault();
                    const btn = e.target.closest('.wishlist');
                    this.toggle(btn);
                }
            });
        },
        
        toggle: async function(button) {
            const productId = button.dataset.productId;
            const isActive = button.classList.contains('active');
            
            if (!productId) {
                Utils.showToast('Error: ID de producto no encontrado', 'error');
                return;
            }
            
            button.disabled = true;
            
            try {
                const response = await Utils.fetchAPI('/wishlist/toggle', {
                    method: 'POST',
                    body: JSON.stringify({
                        product_id: productId
                    })
                });
                
                if (response.success) {
                    button.classList.toggle('active');
                    const icon = button.querySelector('i');
                    
                    if (button.classList.contains('active')) {
                        icon.classList.replace('far', 'fas');
                        Utils.showToast('Agregado a favoritos', 'success');
                    } else {
                        icon.classList.replace('fas', 'far');
                        Utils.showToast('Removido de favoritos', 'info');
                    }
                    
                    // Update title
                    button.title = button.classList.contains('active') ? 
                                  'Quitar de favoritos' : 'Agregar a favoritos';
                } else {
                    Utils.showToast(response.message || 'Error al actualizar favoritos', 'error');
                }
            } catch (error) {
                console.error('Wishlist error:', error);
                Utils.showToast('Error de conexión. Intenta de nuevo.', 'error');
            } finally {
                button.disabled = false;
            }
        }
    };
    
    // === BÚSQUEDA ===
    const Search = {
        init: function() {
            const searchForms = document.querySelectorAll('.search-form');
            const searchInputs = document.querySelectorAll('.search-input');
            
            searchForms.forEach(form => {
                form.addEventListener('submit', this.handleSearch);
            });
            
            // Live search
            searchInputs.forEach(input => {
                input.addEventListener('input', 
                    Utils.debounce(this.liveSearch.bind(this), 300)
                );
            });
        },
        
        handleSearch: function(e) {
            const form = e.target;
            const formData = new FormData(form);
            const query = formData.get('q');
            
            if (!query || query.trim().length < 2) {
                e.preventDefault();
                Utils.showToast('Por favor ingresa al menos 2 caracteres', 'warning');
                return;
            }
            
            // Add loading to search button
            const searchBtn = form.querySelector('.search-btn');
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                searchBtn.disabled = true;
            }
        },
        
        liveSearch: async function(e) {
            const input = e.target;
            const query = input.value.trim();
            
            if (query.length < 2) return;
            
            try {
                const response = await Utils.fetchAPI(`/search/suggestions?q=${encodeURIComponent(query)}`);
                this.showSuggestions(input, response.suggestions);
            } catch (error) {
                console.error('Live search error:', error);
            }
        },
        
        showSuggestions: function(input, suggestions) {
            // Remove existing suggestions
            const existing = document.querySelector('.search-suggestions');
            if (existing) existing.remove();
            
            if (!suggestions || suggestions.length === 0) return;
            
            const suggestionBox = document.createElement('div');
            suggestionBox.className = 'search-suggestions';
            suggestionBox.innerHTML = suggestions.map(item => `
                <a href="/productos/${item.slug}" class="suggestion-item">
                    <img src="/img/products/${item.image}" alt="${item.name}" class="suggestion-image">
                    <div class="suggestion-content">
                        <div class="suggestion-name">${item.name}</div>
                        <div class="suggestion-price">${Utils.formatPrice(item.price)}</div>
                    </div>
                </a>
            `).join('');
            
            // Position and show
            const searchContainer = input.closest('.search-bar');
            searchContainer.appendChild(suggestionBox);
            
            // Close suggestions when clicking outside
            const closeSuggestions = (e) => {
                if (!searchContainer.contains(e.target)) {
                    suggestionBox.remove();
                    document.removeEventListener('click', closeSuggestions);
                }
            };
            
            setTimeout(() => {
                document.addEventListener('click', closeSuggestions);
            }, 100);
        }
    };
    
    // === CAROUSELS Y SLIDERS ===
    const Carousel = {
        init: function() {
            this.initHeroCarousel();
            this.initProductCarousels();
        },
        
        initHeroCarousel: function() {
            const carousel = document.querySelector('.hero-carousel');
            if (!carousel) return;
            
            const slides = carousel.querySelectorAll('.hero-slide');
            const indicators = document.querySelectorAll('.hero-indicators .indicator');
            const prevBtn = document.querySelector('.hero-nav .prev');
            const nextBtn = document.querySelector('.hero-nav .next');
            
            let currentSlide = 0;
            let autoplayInterval;
            
            const showSlide = (index) => {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
                
                indicators.forEach((indicator, i) => {
                    indicator.classList.toggle('active', i === index);
                });
                
                currentSlide = index;
            };
            
            const nextSlide = () => {
                showSlide((currentSlide + 1) % slides.length);
            };
            
            const prevSlide = () => {
                showSlide((currentSlide - 1 + slides.length) % slides.length);
            };
            
            const startAutoplay = () => {
                autoplayInterval = setInterval(nextSlide, 5000);
            };
            
            const stopAutoplay = () => {
                clearInterval(autoplayInterval);
            };
            
            // Event listeners
            if (nextBtn) nextBtn.addEventListener('click', () => { stopAutoplay(); nextSlide(); startAutoplay(); });
            if (prevBtn) prevBtn.addEventListener('click', () => { stopAutoplay(); prevSlide(); startAutoplay(); });
            
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    stopAutoplay();
                    showSlide(index);
                    startAutoplay();
                });
            });
            
            // Pause on hover
            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
            
            // Start autoplay
            startAutoplay();
        },
        
        initProductCarousels: function() {
            const carousels = document.querySelectorAll('.products-carousel');
            
            carousels.forEach(carousel => {
                const slider = carousel.querySelector('.products-slider');
                const prevBtn = carousel.querySelector('.carousel-btn.prev');
                const nextBtn = carousel.querySelector('.carousel-btn.next');
                const slides = slider.querySelectorAll('.product-slide');
                
                if (slides.length === 0) return;
                
                let currentIndex = 0;
                const slidesToShow = this.getSlidesToShow();
                const maxIndex = Math.max(0, slides.length - slidesToShow);
                
                const updateCarousel = () => {
                    const translateX = -(currentIndex * (100 / slidesToShow));
                    slider.style.transform = `translateX(${translateX}%)`;
                    
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex >= maxIndex;
                };
                
                prevBtn?.addEventListener('click', () => {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateCarousel();
                    }
                });
                
                nextBtn?.addEventListener('click', () => {
                    if (currentIndex < maxIndex) {
                        currentIndex++;
                        updateCarousel();
                    }
                });
                
                // Update on resize
                window.addEventListener('resize', Utils.debounce(() => {
                    const newSlidesToShow = this.getSlidesToShow();
                    const newMaxIndex = Math.max(0, slides.length - newSlidesToShow);
                    if (currentIndex > newMaxIndex) {
                        currentIndex = newMaxIndex;
                    }
                    updateCarousel();
                }, 250));
                
                updateCarousel();
            });
        },
        
        getSlidesToShow: function() {
            const width = window.innerWidth;
            if (width < 576) return 1;
            if (width < 768) return 2;
            if (width < 992) return 3;
            return 4;
        }
    };
    
    // === BACK TO TOP ===
    const BackToTop = {
        init: function() {
            const btn = document.getElementById('backToTop');
            if (!btn) return;
            
            const toggleVisibility = Utils.throttle(() => {
                if (window.pageYOffset > 300) {
                    btn.style.display = 'block';
                } else {
                    btn.style.display = 'none';
                }
            }, 100);
            
            window.addEventListener('scroll', toggleVisibility);
            
            btn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    };
    
    // === COUNTER ANIMATIONS ===
    const CounterAnimations = {
        init: function() {
            const counters = document.querySelectorAll('[data-count]');
            if (counters.length === 0) return;
            
            const animateCounter = (counter) => {
                const target = parseInt(counter.dataset.count);
                const duration = 2000;
                const start = parseInt(counter.textContent) || 0;
                const increment = target / (duration / 16);
                let current = start;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.ceil(current).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target.toLocaleString();
                    }
                };
                
                updateCounter();
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            counters.forEach(counter => {
                observer.observe(counter);
            });
        }
    };
    
    // === ALERT DISMISSAL ===
    const Alerts = {
        init: function() {
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('alert-close')) {
                    const alert = e.target.closest('.alert');
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-20px)';
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }
                }
            });
        }
    };
    
    // === SMOOTH SCROLLING ===
    const SmoothScroll = {
        init: function() {
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href^="#"]');
                if (!link) return;
                
                const href = link.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        }
    };
    
    // === LAZY LOADING ===
    const LazyLoading = {
        init: function() {
            if ('IntersectionObserver' in window) {
                const images = document.querySelectorAll('img[loading="lazy"]');
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                            }
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
        }
    };
    
    // === INICIALIZACIÓN ===
    const init = function() {
        try {
            // Initialize all modules
            MobileNav.init();
            Dropdowns.init();
            ShoppingCart.init();
            Wishlist.init();
            Search.init();
            Carousel.init();
            BackToTop.init();
            CounterAnimations.init();
            Alerts.init();
            SmoothScroll.init();
            LazyLoading.init();
            
            console.log('AgroConecta initialized successfully');
            
            // Emit initialization complete event
            AgroConecta.emit('app:initialized');
            
        } catch (error) {
            console.error('AgroConecta initialization error:', error);
        }
    };
    
    // Start the application
    init();
    
    // Export utilities for external use
    window.AgroConectaUtils = Utils;
    
    // Service Worker Registration
    if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered:', registration);
            })
            .catch(error => {
                console.log('SW registration failed:', error);
            });
    }
});

// === GLOBAL ERROR HANDLER ===
window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
    // You can send error reports to your logging service here
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
    // Handle unhandled promise rejections
});