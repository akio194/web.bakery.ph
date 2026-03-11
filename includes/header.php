<?php
/**
 * Header Include File
 * Contains navigation and common head elements
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Delights Bakery</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Quicksand:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/bakery-website/assets/css/style.css">
    <style>
        /* Custom Tailwind Configuration */
        @layer base {
            body {
                font-family: 'Quicksand', sans-serif;
            }
            h1, h2, h3, h4, h5, h6 {
                font-family: 'Playfair Display', serif;
            }
        }
        /* Cart animation */
        .cart-icon {
            transition: transform 0.2s ease;
        }
        .cart-icon.scale-125 {
            transform: scale(1.25);
        }
        /* Toast animations */
        .toast {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #8B4513;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Small spinner for buttons */
        .spinner-sm {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        /* Button loading state */
        .btn-loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }
        /* Mobile improvements */
        @media (max-width: 768px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .text-4xl {
                font-size: 2rem;
            }
            
            .text-3xl {
                font-size: 1.5rem;
            }
            
            .grid-cols-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .grid-cols-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            
            .md\:grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .md\:grid-cols-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        /* Touch-friendly buttons */
        @media (max-width: 640px) {
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
            
            .px-6 {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .py-3 {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }
        }
        /* Dropdown styles */
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 50;
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            color: #8B4513;
            transition: all 0.3s;
        }
        .dropdown-item:hover {
            background-color: #FDF8F5;
        }
        .dropdown-item:first-child {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .dropdown-item:last-child {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-[#FDF8F5]">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <i class="fas fa-cake-candles text-3xl text-[#8B4513]"></i>
                    <span class="font-bold text-2xl text-[#8B4513] font-['Playfair_Display']">Sweet Delights</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <!-- Admin Only Navigation -->
                        <a href="/bakery-website/admin/dashboard.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Dashboard</a>
                        <a href="/bakery-website/admin/orders.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Orders</a>
                        <a href="/bakery-website/admin/products.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Products</a>
                        <a href="/bakery-website/admin/inventory.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Inventory</a>
                        <a href="/bakery-website/admin/customers.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Customers</a>
                        <a href="/bakery-website/admin/analytics.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Analytics</a>
                    <?php else: ?>
                        <!-- Regular User Navigation -->
                        <a href="/bakery-website/index.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Home</a>
                        <a href="/bakery-website/menu.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Menu</a>
                        <a href="/bakery-website/wishlist.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium relative">
                            <i class="fas fa-heart mr-1"></i>Wishlist
                            <?php 
                            if (isset($_SESSION['user_id'])) {
                                $pdo = getDBConnection();
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $wishlist_count = $stmt->fetchColumn();
                                if ($wishlist_count > 0) {
                                    echo '<span class="absolute -top-2 -right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">' . $wishlist_count . '</span>';
                                }
                            }
                            ?>
                        </a>
                        <a href="/bakery-website/cart.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium relative cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count absolute -top-2 -right-4 bg-[#D2691E] text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative" id="user-dropdown-container">
                            <button id="user-dropdown-btn" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium flex items-center space-x-1 focus:outline-none">
                                <i class="fas fa-user mr-1"></i> 
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-sm transition-transform duration-300" id="dropdown-arrow"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="dropdown-menu" class="dropdown-menu">
                                <a href="/bakery-website/profile.php" class="dropdown-item">
                                    <i class="fas fa-user-circle mr-2"></i> My Profile
                                </a>
                                <a href="/bakery-website/orders.php" class="dropdown-item">
                                    <i class="fas fa-shopping-bag mr-2"></i> My Orders
                                </a>
                                <hr class="my-1 border-gray-200">
                                <a href="/bakery-website/logout.php" class="dropdown-item text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/bakery-website/login.php" class="text-[#8B4513] hover:text-[#D2691E] transition duration-300 font-medium">Login</a>
                        <a href="/bakery-website/register.php" class="bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#D2691E] transition duration-300">Register</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-[#8B4513] text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <!-- Admin Mobile Menu -->
                    <a href="/bakery-website/admin/dashboard.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Dashboard</a>
                    <a href="/bakery-website/admin/orders.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Orders</a>
                    <a href="/bakery-website/admin/products.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Products</a>
                    <a href="/bakery-website/admin/inventory.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Inventory</a>
                    <a href="/bakery-website/admin/customers.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Customers</a>
                    <a href="/bakery-website/admin/analytics.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Analytics</a>
                <?php else: ?>
                    <!-- Regular User Mobile Menu -->
                    <a href="/bakery-website/index.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Home</a>
                    <a href="/bakery-website/menu.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Menu</a>
                    <a href="/bakery-website/wishlist.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Wishlist</a>
                    <a href="/bakery-website/cart.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Cart</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/bakery-website/profile.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">My Profile</a>
                    <a href="/bakery-website/orders.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">My Orders</a>
                    <a href="/bakery-website/logout.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Logout</a>
                <?php else: ?>
                    <a href="/bakery-website/login.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Login</a>
                    <a href="/bakery-website/register.php" class="block py-2 text-[#8B4513] hover:text-[#D2691E] transition duration-300">Register</a>
                <?php endif; ?>
            </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <!-- Main Content -->
    <main>

    <!-- JavaScript for Dropdown and UI Enhancements -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('user-dropdown-btn');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const dropdownArrow = document.getElementById('dropdown-arrow');
        
        if (dropdownBtn && dropdownMenu) {
            // Toggle dropdown on button click
            dropdownBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle menu
                dropdownMenu.classList.toggle('show');
                
                // Rotate arrow
                if (dropdownArrow) {
                    if (dropdownMenu.classList.contains('show')) {
                        dropdownArrow.style.transform = 'rotate(180deg)';
                    } else {
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    if (dropdownArrow) {
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Prevent dropdown from closing when clicking inside menu
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Close dropdown on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                    if (dropdownArrow) {
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });
        }
    });

    // Global functions for loading states
    function setButtonLoading(button, loading = true) {
        if (loading) {
            button.classList.add('btn-loading');
            const originalText = button.innerHTML;
            button.dataset.originalText = originalText;
            button.innerHTML = '<span class="spinner-sm"></span>Loading...';
        } else {
            button.classList.remove('btn-loading');
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }
    }

    // Enhanced toast function
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;
        
        const toast = document.createElement('div');
        toast.className = 'toast p-4 rounded-lg shadow-lg mb-2 flex items-center';
        
        const colors = {
            success: 'bg-green-100 text-green-700 border-green-400',
            error: 'bg-red-100 text-red-700 border-red-400',
            warning: 'bg-yellow-100 text-yellow-700 border-yellow-400',
            info: 'bg-blue-100 text-blue-700 border-blue-400'
        };
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        toast.className += ' ' + colors[type];
        toast.innerHTML = `
            <i class="${icons[type]} mr-2"></i>
            <span>${message}</span>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Form validation helper
    function validateForm(form) {
        const errors = [];
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                errors.push(`${field.name || field.id} is required`);
                field.classList.add('border-red-500');
            } else {
                field.classList.remove('border-red-500');
            }
        });
        
        return errors;
    }

    // AJAX helper with loading states
    function ajaxRequest(url, options = {}) {
        return new Promise((resolve, reject) => {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            const config = { ...defaults, ...options };
            
            fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => resolve(data))
                .catch(error => reject(error));
        });
    }
    </script>