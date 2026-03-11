/**
 * Cart Management JavaScript
 * Handles all cart-related functionality
 */

// Cart object with methods
const Cart = {
    // Get cart from localStorage
    getCart: function() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    },
    
    // Save cart to localStorage
    saveCart: function(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
        this.updateCartCount();
    },
    
    // Add item to cart
    addItem: function(product) {
        let cart = this.getCart();
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                ...product,
                quantity: 1
            });
        }
        
        this.saveCart(cart);
        this.showNotification('Product added to cart!', 'success');
    },
    
    // Update item quantity
    updateQuantity: function(productId, quantity) {
        let cart = this.getCart();
        const itemIndex = cart.findIndex(item => item.id === productId);
        
        if (itemIndex !== -1) {
            if (quantity <= 0) {
                cart.splice(itemIndex, 1);
                this.showNotification('Item removed from cart', 'info');
            } else {
                cart[itemIndex].quantity = quantity;
                this.showNotification('Cart updated', 'success');
            }
            this.saveCart(cart);
        }
    },
    
    // Remove item from cart
    removeItem: function(productId) {
        let cart = this.getCart();
        cart = cart.filter(item => item.id !== productId);
        this.saveCart(cart);
        this.showNotification('Item removed from cart', 'info');
    },
    
    // Clear entire cart
    clearCart: function() {
        localStorage.removeItem('cart');
        this.updateCartCount();
        this.showNotification('Cart cleared', 'info');
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
    
    // Update cart count in navigation
    updateCartCount: function() {
        const count = this.getItemCount();
        const cartCountElements = document.querySelectorAll('#cart-count');
        
        cartCountElements.forEach(el => {
            el.textContent = count;
            if (count === 0) {
                el.classList.add('hidden');
            } else {
                el.classList.remove('hidden');
            }
        });
    },
    
    // Show notification
    showNotification: function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 transform transition-all duration-500 translate-x-0`;
        toast.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (container.contains(toast)) {
                    container.removeChild(toast);
                }
            }, 500);
        }, 3000);
    },
    
    // Validate email format
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Validate phone number (simple validation)
    validatePhone: function(phone) {
        const re = /^[\d\s\-\(\)]+$/;
        return re.test(phone);
    }
};

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    Cart.updateCartCount();
});

// Export for use in other files
window.Cart = Cart;