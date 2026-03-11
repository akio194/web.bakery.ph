<?php
/**
 * Add Product Page
 * Admin can add new products to the bakery
 */

require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? 'general';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($description) || empty($price)) {
        $error = 'Please fill in all required fields';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Please enter a valid price';
    } else {
        // Handle image upload
        $image = 'default-product.jpg'; // Default image
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $fileName;
            
            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $image = $fileName;
                } else {
                    $error = 'Failed to upload image';
                }
            } else {
                $error = 'Invalid file type. Please upload JPEG, PNG, or GIF';
            }
        }
        
        if (empty($error)) {
            // Insert product into database
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, image, category, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$name, $description, $price, $image, $category, $is_featured])) {
                $message = 'Product added successfully!';
            } else {
                $error = 'Failed to add product';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-[#8B4513] font-['Playfair_Display']">Add New Product</h1>
        
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Product Name *</label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Description *</label>
                    <textarea name="description" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]"></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                        <option value="cakes">Cakes</option>
                        <option value="bread">Bread</option>
                        <option value="pastries">Pastries</option>
                        <option value="pies">Pies</option>
                        <option value="cookies">Cookies</option>
                        <option value="general">General</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Product Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    <p class="text-sm text-gray-500 mt-1">Leave empty to use default image</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" class="mr-2">
                    <label for="is_featured" class="text-gray-700">Feature this product on homepage</label>
                </div>
            </div>
            
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300">
                    Add Product
                </button>
                <a href="/bakery-website/admin/dashboard.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition duration-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>