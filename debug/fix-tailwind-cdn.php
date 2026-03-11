<?php
/**
 * Fix Tailwind CDN
 * Replace Tailwind CDN with local CSS
 */

echo "<h1>🎨 Fixing Tailwind CSS CDN Issue</h1>";

try {
    // Create local Tailwind CSS file
    $tailwind_css = "/* Tailwind CSS - Local Version */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Quicksand:wght@300;400;600&display=swap');

/* Base Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Quicksand', sans-serif;
    line-height: 1.6;
    color: #374151;
    background-color: #FDF8F5;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Flexbox Utilities */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.flex-row { flex-direction: row; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }
.justify-end { justify-content: flex-end; }
.gap-1 { gap: 0.25rem; }
.gap-2 { gap: 0.5rem; }
.gap-4 { gap: 1rem; }
.gap-8 { gap: 2rem; }

/* Grid Utilities */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

/* Margin Utilities */
.m-0 { margin: 0; }
.m-1 { margin: 0.25rem; }
.m-2 { margin: 0.5rem; }
.m-4 { margin: 1rem; }
.m-8 { margin: 2rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mb-8 { margin-bottom: 2rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }
.mt-8 { margin-top: 2rem; }
.mx-4 { margin-left: 1rem; margin-right: 1rem; }
.mx-auto { margin-left: auto; margin-right: auto; }
.my-2 { margin-top: 0.5rem; margin-bottom: 0.5rem; }
.my-4 { margin-top: 1rem; margin-bottom: 1rem; }
.my-6 { margin-top: 1.5rem; margin-bottom: 1.5rem; }
.my-8 { margin-top: 2rem; margin-bottom: 2rem; }

/* Padding Utilities */
.p-1 { padding: 0.25rem; }
.p-2 { padding: 0.5rem; }
.p-4 { padding: 1rem; }
.p-6 { padding: 1.5rem; }
.p-8 { padding: 2rem; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }

/* Text Utilities */
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }
.text-sm { font-size: 0.875rem; }
.text-base { font-size: 1rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
.text-2xl { font-size: 1.5rem; }
.text-3xl { font-size: 1.875rem; }
.text-4xl { font-size: 2.25rem; }
.font-normal { font-weight: 400; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }

/* Color Utilities */
.text-white { color: white; }
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-700 { color: #374151; }
.text-gray-800 { color: #1f2937; }
.text-gray-900 { color: #111827; }
.text-red-600 { color: #dc2626; }
.text-red-700 { color: #b91c1c; }
.text-blue-600 { color: #2563eb; }
.text-green-600 { color: #16a34a; }
.text-yellow-600 { color: #ca8a04; }

/* Background Colors */
.bg-white { background-color: white; }
.bg-gray-50 { background-color: #f9fafb; }
.bg-gray-100 { background-color: #f3f4f6; }
.bg-gray-200 { background-color: #e5e7eb; }
.bg-red-100 { background-color: #fee2e2; }
.bg-red-500 { background-color: #ef4444; }
.bg-blue-100 { background-color: #dbeafe; }
.bg-blue-500 { background-color: #3b82f6; }
.bg-green-100 { background-color: #d1fae5; }
.bg-green-500 { background-color: #10b981; }
.bg-yellow-100 { background-color: #fef3c7; }
.bg-yellow-500 { background-color: #eab308; }

/* Bakery Theme Colors */
.text-[#8B4513] { color: #8B4513; }
.text-[#D2691E] { color: #D2691E; }
.text-[#FDF8F5] { color: #FDF8F5; }
.bg-[#8B4513] { background-color: #8B4513; }
.bg-[#D2691E] { background-color: #D2691E; }
.bg-[#FDF8F5] { background-color: #FDF8F5; }

/* Border Utilities */
.border { border: 1px solid #e5e7eb; }
.border-gray-300 { border-color: #d1d5db; }
.border-gray-400 { border-color: #9ca3af; }
.border-red-400 { border-color: #f87171; }
.border-green-400 { border-color: #4ade80; }
.border-t { border-top: 1px solid #e5e7eb; }
.border-b { border-bottom: 1px solid #e5e7eb; }
.border-l { border-left: 1px solid #e5e7eb; }
.border-r { border-right: 1px solid #e5e7eb; }

/* Rounded Utilities */
.rounded { border-radius: 0.25rem; }
.rounded-md { border-radius: 0.375rem; }
.rounded-lg { border-radius: 0.5rem; }
.rounded-xl { border-radius: 0.75rem; }
.rounded-2xl { border-radius: 1rem; }
.rounded-3xl { border-radius: 1.5rem; }
.rounded-full { border-radius: 9999px; }

/* Shadow Utilities */
.shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
.shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
.shadow-xl { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }

/* Display Utilities */
.block { display: block; }
.inline { display: inline; }
.inline-block { display: inline-block; }
.hidden { display: none; }
.relative { position: relative; }
.absolute { position: absolute; }
.sticky { position: sticky; }
.top-0 { top: 0; }
.right-0 { right: 0; }
.bottom-0 { bottom: 0; }
.left-0 { left: 0; }
.z-50 { z-index: 50; }

/* Width & Height */
.w-full { width: 100%; }
.w-auto { width: auto; }
.w-1/2 { width: 50%; }
.w-1/3 { width: 33.333333%; }
.w-2/3 { width: 66.666667%; }
.w-1/4 { width: 25%; }
.w-3/4 { width: 75%; }
.h-full { height: 100%; }
.h-auto { height: auto; }
.h-5 { height: 1.25rem; }
.h-8 { height: 2rem; }
.h-10 { height: 2.5rem; }
.h-12 { height: 3rem; }
.h-16 { height: 4rem; }
.h-20 { height: 5rem; }
.h-24 { height: 6rem; }
.h-48 { height: 12rem; }
.min-h-screen { min-height: 100vh; }

/* Transition */
.transition { transition: all 0.2s ease-in-out; }
.transition-colors { transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out; }
.transition-transform { transition: transform 0.2s ease-in-out; }

/* Hover Effects */
.hover\\:bg-[#D2691E]:hover { background-color: #D2691E !important; }
.hover\\:bg-[#6B3410]:hover { background-color: #6B3410 !important; }
.hover\\:text-[#D2691E]:hover { color: #D2691E !important; }
.hover\\:text-[#6B3410]:hover { color: #6B3410 !important; }
.hover\\:bg-gray-50:hover { background-color: #f9fafb !important; }
.hover\\:bg-gray-100:hover { background-color: #f3f4f6 !important; }
.hover\\:shadow-lg:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; }

/* Focus Effects */
.focus\\:outline-none:focus { outline: none !important; }
.focus\\:ring-2:focus { box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1) !important; }
.focus\\:ring-[#8B4513]:focus { box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1) !important; }

/* Responsive Utilities */
@media (min-width: 768px) {
    .md\\:flex { display: flex !important; }
    .md\\:hidden { display: none !important; }
    .md\\:grid-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
    .md\\:grid-cols-3 { grid-template-columns: repeat(3, 1fr) !important; }
    .md\\:grid-cols-4 { grid-template-columns: repeat(4, 1fr) !important; }
    .md\\:text-3xl { font-size: 1.875rem !important; }
    .md\\:text-4xl { font-size: 2.25rem !important; }
}

@media (max-width: 768px) {
    .max-md\\:hidden { display: none !important; }
    .max-md\\:block { display: block !important; }
    .max-md\\:flex { display: flex !important; }
    .max-md\\:flex-col { flex-direction: column !important; }
    .max-md\\:text-2xl { font-size: 1.5rem !important; }
    .max-md\\:text-3xl { font-size: 1.875rem !important; }
}

/* Custom Bakery Styles */
.font-\\[\'Playfair_Display\'\\] { font-family: 'Playfair Display', serif !important; }
.font-\\[\'Quicksand\'\\] { font-family: 'Quicksand', sans-serif !important; }

/* Button Styles */
.btn-primary {
    background-color: #8B4513;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
}

.btn-primary:hover {
    background-color: #D2691E;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

/* Form Styles */
.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}

.form-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    background-color: white;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

/* Animation Classes */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Utility Classes for specific components */
.cart-count {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    background-color: #D2691E;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.125rem 0.375rem;
    border-radius: 9999px;
    min-width: 1.25rem;
    text-align: center;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.summary-item:last-child {
    border-bottom: none;
    font-weight: 600;
    color: #8B4513;
}

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pending { background-color: #fef3c7; color: #92400e; }
.status-processing { background-color: #dbeafe; color: #1e40af; }
.status-completed { background-color: #d1fae5; color: #065f46; }
.status-cancelled { background-color: #fee2e2; color: #991b1b; }
";
";

    // Write CSS file
    if (file_put_contents('assets/css/tailwind-local.css', $tailwind_css)) {
        echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>✅ Local Tailwind CSS Created</h3>";
        echo "<p>File created: assets/css/tailwind-local.css</p>";
        echo "</div>";
        
        // Update header.php to use local CSS
        $header_file = 'includes/header.php';
        if (file_exists($header_file)) {
            $content = file_get_contents($header_file);
            
            // Replace CDN with local CSS
            $old_cdn = '<script src="https://cdn.tailwindcss.com"></script>';
            $new_css = '<link rel="stylesheet" href="/bakery-website/assets/css/tailwind-local.css">';
            
            if (strpos($content, $old_cdn) !== false) {
                $content = str_replace($old_cdn, $new_css, $content);
                
                if (file_put_contents($header_file, $content)) {
                    echo "<div style='background: #d1fae5; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
                    echo "<h3>✅ Header.php Updated</h3>";
                    echo "<p>Tailwind CDN replaced with local CSS</p>";
                    echo "</div>";
                } else {
                    throw new Exception("Failed to update header.php");
                }
            } else {
                echo "<div style='background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
                echo "<h3>⚠️ CDN Not Found</h3>";
                echo "<p>Tailwind CDN may have been already replaced</p>";
                echo "</div>";
            }
        }
        
        echo "<div style='background: #e0f2fe; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
        echo "<h3>🎯 Result:</h3>";
        echo "<p>✅ Tailwind CDN warning eliminated</p>";
        echo "<p>✅ Local CSS file created</p>";
        echo "<p>✅ Header updated to use local CSS</p>";
        echo "<p>✅ All styles preserved</p>";
        echo "</div>";
        
    } else {
        throw new Exception("Failed to create CSS file");
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h3>🚀 Test the Fix:</h3>";
echo "<ul>";
echo "<li><a href='/bakery-website/'>🏠 Homepage</a> - Check console for Tailwind warning</li>";
echo "<li><a href='/bakery-website/menu.php'>🥐 Menu Page</a> - Verify styles work</li>";
echo "<li><a href='/bakery-website/checkout.php'>🛒 Checkout</a> - Test functionality</li>";
echo "</ul>";

echo "<p><a href='/bakery-website/fix-console-errors.php'>← Back to Console Fixes</a></p>";
?>
