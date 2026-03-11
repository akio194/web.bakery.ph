/**
 * Bakery Website Service JavaScript
 * Handles all interactive functionality
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Main initialization function
 */
function initializeApp() {
    initializeMobileMenu();
    initializeCart();
    initializeForms();
    initializeProductFilters();
    initializeImageGallery();
    initializeSearch();
    setupEventListeners();
}

/**
 * Mobile Menu Functionality
 */
function initializeMobileMenu() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            const icon = this.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        document.addEventListener('click', function(event) {
            if (!menuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                const icon = menuBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                mobileMenu.classList.add('hidden');
                const icon = menuBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }
}

/**
 * Cart Management System
 */
const CartService = {
    // Get cart from localStorage
    getCart: function() {
        try {
            return JSON.parse(localStorage.getItem('cart')) || [];
        } catch (e) {
            console.error('Error parsing cart:', e);
            return [];
        }
    },

    // Save cart to localStorage
    saveCart: function(cart) {
        try {
            localStorage.setItem('cart', JSON.stringify(cart));
            this.updateCartCount();
            this.updateCartDisplay();
            window.dispatchEvent(new CustomEvent('cartUpdated', { 
                detail: { cart: cart } 
            }));
        } catch (e) {
            console.error('Error saving cart:', e);
            this.showNotification('Error saving cart', 'error');
        }
    },

    // Add item to cart
    addItem: function(product) {
        let cart = this.getCart();
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            existingItem.quantity += 1;
            this.showNotification(`Added another ${product.name} to cart`, 'success');
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                image: product.image || 'default-product.jpg'
            });
            this.showNotification(`${product.name} added to cart`, 'success');
        }

        this.saveCart(cart);
        this.animateCartIcon();
    },

    // Update item quantity
    updateQuantity: function(productId, newQuantity) {
        let cart = this.getCart();
        const itemIndex = cart.findIndex(item => item.id === productId);

        if (itemIndex !== -1) {
            if (newQuantity <= 0) {
                this.removeItem(productId);
                return;
            }
            
            cart[itemIndex].quantity = newQuantity;
            this.saveCart(cart);
            this.showNotification('Cart updated', 'success');
        }
    },

    // Remove item from cart
    removeItem: function(productId) {
        let cart = this.getCart();
        const removedItem = cart.find(item => item.id === productId);
        cart = cart.filter(item => item.id !== productId);
        this.saveCart(cart);
        
        if (removedItem) {
            this.showNotification(`${removedItem.name} removed from cart`, 'info');
        }
    },

    // Clear entire cart
    clearCart: function() {
        if (this.getCart().length > 0) {
            localStorage.removeItem('cart');
            this.updateCartCount();
            this.updateCartDisplay();
            this.showNotification('Cart cleared', 'info');
            window.dispatchEvent(new CustomEvent('cartCleared'));
        }
    },

    // Get cart total
    getTotal: function() {
        const cart = this.getCart();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    // Get item count
    getItemCount: function() {
        const cart = this.getCart();
        return cart.reduce((count, item) => count + item.quantity, 0);
    },

    // Update cart count display
    updateCartCount: function() {
        const count = this.getItemCount();
        const cartCountElements = document.querySelectorAll('.cart-count');
        
        cartCountElements.forEach(el => {
            el.textContent = count;
            if (count === 0) {
                el.classList.add('hidden');
            } else {
                el.classList.remove('hidden');
            }
        });
    },

    // Update cart display on cart page
    updateCartDisplay: function() {
        const cartContainer = document.getElementById('cart-items');
        if (!cartContainer) return;

        const cart = this.getCart();
        const emptyCartDiv = document.getElementById('empty-cart');
        const checkoutBtn = document.getElementById('checkout-btn');

        if (cart.length === 0) {
            cartContainer.innerHTML = '';
            if (emptyCartDiv) emptyCartDiv.classList.remove('hidden');
            if (checkoutBtn) {
                checkoutBtn.disabled = true;
                checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            this.updateSummary(cart);
            return;
        }

        if (emptyCartDiv) emptyCartDiv.classList.add('hidden');
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        let html = '';
        cart.forEach(item => {
            html += this.renderCartItem(item);
        });

        cartContainer.innerHTML = html;
        this.updateSummary(cart);
        this.attachCartItemEvents();
    },

    // Render individual cart item  
    renderCartItem: function(item) {
        return `
            <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col sm:flex-row items-center gap-4 cart-item" data-id="${item.id}">
                <img src="/bakery-website/assets/images/${item.image || 'default-product.jpg'}" 
                     alt="₱{item.name}" 
                     class="w-24 h-24 object-cover rounded-lg"
                     onerror="this.src='/bakery-website/assets/images/default-product.jpg'">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-[#8B4513]">${item.name}</h3>
                    <p class="text-[#D2691E] font-bold">₱${item.price.toFixed(2)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="quantity-decrease w-8 h-8 bg-gray-200 rounded-full hover:bg-gray-300 transition duration-300">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="quantity-display w-8 text-center">${item.quantity}</span>
                    <button class="quantity-increase w-8 h-8 bg-gray-200 rounded-full hover:bg-gray-300 transition duration-300">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button class="remove-item text-red-500 hover:text-red-700 transition duration-300">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    },

    // Attach events to cart items
    attachCartItemEvents: function() {
        const self = this;
        
        document.querySelectorAll('.quantity-decrease').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const item = e.target.closest('.cart-item');
                const id = parseInt(item.dataset.id);
                const currentQty = parseInt(item.querySelector('.quantity-display').textContent);
                self.updateQuantity(id, currentQty - 1);
            });
        });

        document.querySelectorAll('.quantity-increase').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const item = e.target.closest('.cart-item');
                const id = parseInt(item.dataset.id);
                const currentQty = parseInt(item.querySelector('.quantity-display').textContent);
                self.updateQuantity(id, currentQty + 1);
            });
        });

        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const item = e.target.closest('.cart-item');
                const id = parseInt(item.dataset.id);
                self.removeItem(id);
            });
        });
    },

    // Update order summary
    updateSummary: function(cart) {
        const summaryContainer = document.getElementById('cart-summary');
        const totalElement = document.getElementById('cart-total');
        
        if (!summaryContainer || !totalElement) return;

        let subtotal = 0;
        let summaryHtml = '';

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            summaryHtml += `
                <div class="flex justify-between text-gray-600">
                    <span>${item.name} x${item.quantity}</span>
                    <span>$${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });

        summaryContainer.innerHTML = summaryHtml;
        totalElement.textContent = `$${subtotal.toFixed(2)}`;
    },

    // Animate cart icon when item added
    animateCartIcon: function() {
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.classList.add('scale-125');
            setTimeout(() => {
                cartIcon.classList.remove('scale-125');
            }, 200);
        }
    },

    // Show notification
    showNotification: function(message, type = 'success') {
        showToast(message, type);
    }
};

// Initialize cart
function initializeCart() {
    CartService.updateCartCount();
    
    if (document.getElementById('cart-items')) {
        CartService.updateCartDisplay();
    }

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const product = {
                id: parseInt(this.dataset.id),
                name: this.dataset.name,
                price: parseFloat(this.dataset.price),
                image: this.dataset.image
            };
            CartService.addItem(product);
        });
    });
    
    window.addEventListener('cartCleared', function() {
        CartService.updateCartCount();
        CartService.updateCartDisplay();
    });
    
    window.addEventListener('cartUpdated', function(e) {
        CartService.updateCartCount();
        if (document.getElementById('cart-items')) {
            CartService.updateCartDisplay();
        }
    });
}

/**
 * Form Validation
 */
function initializeForms() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegisterForm);
    }

    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', validateCheckoutForm);
    }

    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', validateContactForm);
    }
}

function validateLoginForm(e) {
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    let isValid = true;

    if (!email.value || !isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    } else {
        clearFieldError(email);
    }

    if (!password.value) {
        showFieldError(password, 'Please enter your password');
        isValid = false;
    } else {
        clearFieldError(password);
    }

    if (!isValid) {
        e.preventDefault();
        showToast('Please fix the errors in the form', 'error');
    }
}

function validateRegisterForm(e) {
    const name = document.querySelector('input[name="name"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    let isValid = true;

    if (!name.value || name.value.length < 2) {
        showFieldError(name, 'Please enter your full name');
        isValid = false;
    } else {
        clearFieldError(name);
    }

    if (!email.value || !isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    } else {
        clearFieldError(email);
    }

    if (!password.value || password.value.length < 6) {
        showFieldError(password, 'Password must be at least 6 characters');
        isValid = false;
    } else {
        clearFieldError(password);
    }

    if (password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    } else {
        clearFieldError(confirmPassword);
    }

    if (!isValid) {
        e.preventDefault();
        showToast('Please fix the errors in the form', 'error');
    }
}

function validateCheckoutForm(e) {
    const name = document.querySelector('input[name="name"]');
    const email = document.querySelector('input[name="email"]');
    const phone = document.querySelector('input[name="phone"]');
    const address = document.querySelector('textarea[name="address"]');
    let isValid = true;

    if (!name.value) {
        showFieldError(name, 'Please enter your name');
        isValid = false;
    } else {
        clearFieldError(name);
    }

    if (!email.value || !isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email');
        isValid = false;
    } else {
        clearFieldError(email);
    }

    if (!phone.value || !isValidPhone(phone.value)) {
        showFieldError(phone, 'Please enter a valid phone number');
        isValid = false;
    } else {
        clearFieldError(phone);
    }

    if (!address.value) {
        showFieldError(address, 'Please enter your delivery address');
        isValid = false;
    } else {
        clearFieldError(address);
    }

    const cart = CartService.getCart();
    if (cart.length === 0) {
        showToast('Your cart is empty', 'error');
        isValid = false;
        e.preventDefault();
    }

    if (!isValid) {
        e.preventDefault();
        showToast('Please fill in all required fields', 'error');
    }
}

function validateContactForm(e) {
    const name = document.querySelector('input[name="name"]');
    const email = document.querySelector('input[name="email"]');
    const message = document.querySelector('textarea[name="message"]');
    let isValid = true;

    if (!name.value) {
        showFieldError(name, 'Please enter your name');
        isValid = false;
    }

    if (!email.value || !isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email');
        isValid = false;
    }

    if (!message.value) {
        showFieldError(message, 'Please enter your message');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        showToast('Please fill in all fields', 'error');
    }
}

// Helper validation functions
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[\d\s\-\(\)\+]{10,}$/;
    return re.test(phone);
}

function showFieldError(field, message) {
    field.classList.add('border-red-500');
    let errorDiv = field.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('error-message')) {
        errorDiv = document.createElement('p');
        errorDiv.className = 'error-message text-red-500 text-sm mt-1';
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    errorDiv.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('error-message')) {
        errorDiv.remove();
    }
}

/**
 * Product Filters and Search
 */
function initializeProductFilters() {
    const categoryFilter = document.getElementById('category-filter');
    const priceFilter = document.getElementById('price-filter');
    const sortFilter = document.getElementById('sort-filter');

    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }
    if (priceFilter) {
        priceFilter.addEventListener('change', filterProducts);
    }
    if (sortFilter) {
        sortFilter.addEventListener('change', filterProducts);
    }
}

function initializeSearch() {
    const searchInput = document.getElementById('search-products');
    const searchBtn = document.getElementById('search-btn');

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 500);
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const searchValue = document.getElementById('search-products')?.value || '';
            performSearch(searchValue);
        });
    }
}

function filterProducts() {
    const category = document.getElementById('category-filter')?.value || '';
    const price = document.getElementById('price-filter')?.value || '';
    const sort = document.getElementById('sort-filter')?.value || '';

    const url = new URL(window.location.href);
    if (category) url.searchParams.set('category', category);
    if (price) url.searchParams.set('price', price);
    if (sort) url.searchParams.set('sort', sort);

    window.location.href = url.toString();
}

function performSearch(query) {
    if (query.length > 2) {
        window.location.href = `/bakery-website/menu.php?search=${encodeURIComponent(query)}`;
    } else if (query.length === 0) {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    }
}

/**
 * Image Gallery and Lightbox
 */
function initializeImageGallery() {
    const galleryImages = document.querySelectorAll('.gallery-image');
    
    galleryImages.forEach(img => {
        img.addEventListener('click', function() {
            openLightbox(this.src);
        });
    });
}

function openLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center';
    lightbox.innerHTML = `
        <div class="relative max-w-4xl mx-4">
            <img src="${imageSrc}" class="max-h-screen object-contain" alt="Gallery image">
            <button class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 transition duration-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    lightbox.addEventListener('click', function(e) {
        if (e.target === this || e.target.closest('button')) {
            document.body.removeChild(lightbox);
            document.body.style.overflow = 'auto';
        }
    });
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
}

/**
 * Toast Notification System
 */
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-20 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    
    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 
                   type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 transform transition-all duration-500 translate-x-0 toast`;
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 500);
    }, 3000);
}

/**
 * Create product card HTML
 */
function createProductCard(product) {
    return `
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
            <img src="/bakery-website/assets/images/${product.image || 'default-product.jpg'}" 
                 alt="${product.name}" 
                 class="w-full h-48 object-cover"
                 onerror="this.src='/bakery-website/assets/images/default-product.jpg'">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-2 text-[#8B4513]">${product.name}</h3>
                <p class="text-gray-600 mb-4">${product.description ? product.description.substring(0, 100) + '...' : 'Delicious freshly baked goods'}</p>
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold text-[#D2691E]">$${parseFloat(product.price).toFixed(2)}</span>
                    <button class="add-to-cart bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300"
                            data-id="${product.id}"
                            data-name="${product.name}"
                            data-price="${product.price}"
                            data-image="${product.image || 'default-product.jpg'}">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Setup global event listeners
 */
function setupEventListeners() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal, .fixed.inset-0.bg-black');
            modals.forEach(modal => {
                if (modal.classList.contains('bg-black')) {
                    document.body.removeChild(modal);
                } else {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });

    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 0) this.value = 0;
            if (this.hasAttribute('min') && parseFloat(this.value) < parseFloat(this.min)) {
                this.value = this.min;
            }
            if (this.hasAttribute('max') && parseFloat(this.value) > parseFloat(this.max)) {
                this.value = this.max;
            }
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

/**
 * Export functions for global use
 */
window.CartService = CartService;
window.showToast = showToast;
window.addToCart = (id, name, price, image) => {
    CartService.addItem({ id, name, price, image });
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeApp);