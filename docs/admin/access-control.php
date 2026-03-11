<?php
/**
 * Admin Access Control Summary
 * Shows current access restrictions for admin users
 */

require_once '../config/database.php';
requireAdmin();

include '../includes/header.php';
?>

<style>
.access-summary { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.access-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
.access-item { padding: 1.5rem; border-radius: 0.5rem; border: 2px solid; }
.access-allowed { background: #d1fae5; border-color: #065f46; color: #065f46; }
.access-denied { background: #fee2e2; border-color: #991b1b; color: #991b1b; }
.access-title { font-weight: bold; margin-bottom: 0.5rem; }
.access-status { font-size: 0.875rem; margin-bottom: 0.25rem; }
</style>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8 text-[#8B4513] font-['Playfair_Display']">Admin Access Control</h1>
    
    <div class="access-summary">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">🔒 Admin Access Restrictions</h2>
        <p class="text-gray-600 mb-6">
            Admin users are restricted to management-only access. They cannot place orders or access customer-facing features.
        </p>
        
        <div class="access-grid">
            <div class="access-item access-allowed">
                <div class="access-title">✅ Admin Dashboard</div>
                <div class="access-status">/admin/dashboard.php</div>
                <div class="access-status">Full Management Access</div>
            </div>
            
            <div class="access-item access-allowed">
                <div class="access-title">✅ Order Management</div>
                <div class="access-status">/admin/orders.php</div>
                <div class="access-status">View & Manage Orders</div>
            </div>
            
            <div class="access-item access-allowed">
                <div class="access-title">✅ Order Details</div>
                <div class="access-status">/admin/order-details.php</div>
                <div class="access-status">Complete Order Information</div>
            </div>
            
            <div class="access-item access-denied">
                <div class="access-title">❌ Homepage</div>
                <div class="access-status">/index.php</div>
                <div class="access-status">Redirects to Admin Dashboard</div>
            </div>
            
            <div class="access-item access-denied">
                <div class="access-title">❌ Menu</div>
                <div class="access-status">/menu.php</div>
                <div class="access-status">Redirects to Admin Dashboard</div>
            </div>
            
            <div class="access-item access-denied">
                <div class="access-title">❌ Shopping Cart</div>
                <div class="access-status">/cart.php</div>
                <div class="access-status">Redirects to Admin Dashboard</div>
            </div>
            
            <div class="access-item access-denied">
                <div class="access-title">❌ Checkout</div>
                <div class="access-status">/checkout.php</div>
                <div class="access-status">Redirects to Admin Dashboard</div>
            </div>
            
            <div class="access-item access-denied">
                <div class="access-title">❌ Add to Cart</div>
                <div class="access-status">Hidden from Admin View</div>
                <div class="access-status">Navigation Links Removed</div>
            </div>
            
            <div class="access-item access-allowed">
                <div class="access-title">✅ User Profile</div>
                <div class="access-status">/profile.php</div>
                <div class="access-status">View & Edit Profile</div>
            </div>
            
            <div class="access-item access-allowed">
                <div class="access-title">✅ Order History</div>
                <div class="access-status">/orders.php</div>
                <div class="access-status">View Personal Orders</div>
            </div>
        </div>
        
        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-lg font-bold text-blue-800 mb-2">🛡️ Admin Mode Indicator</h3>
            <p class="text-blue-700">
                When admin users are logged in, a red "ADMIN MODE" banner appears at the top of every page, 
                clearly indicating management-only access.
            </p>
        </div>
        
        <div class="mt-8 p-4 bg-green-50 border border-green-200 rounded-lg">
            <h3 class="text-lg font-bold text-green-800 mb-2">🔄 Testing & Development</h3>
            <p class="text-green-700">
                To test customer functionality, log out and create a regular user account. 
                Admin accounts are strictly for management purposes only.
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
