<?php
/**
 * Contact Page
 * Displays contact form and bakery information
 */

require_once 'config/database.php';
require_once 'config/validation.php';

// Handle form submission
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message_text = sanitizeInput($_POST['message'] ?? '');
    
    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!isValidPhone($phone)) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message_text)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message_text) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    }
    
    if (empty($errors)) {
        try {
            // Save to database (optional - you can also send email)
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([$name, $email, $phone, $subject, $message_text]);
            
            // Show success message
            $_SESSION['success_message'] = 'Thank you for contacting us! We will get back to you within 24 hours.';
            header('Location: /bakery-website/contact.php?success=1');
            exit();
            
        } catch (Exception $e) {
            $errors[] = 'Sorry, something went wrong. Please try again.';
        }
    }
}

// Check for success message
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">Thank you for contacting us! We will get back to you within 24 hours.</div>';
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8 text-[#8B4513] font-['Playfair_Display']">Contact Us</h1>
        
        <?php if ($message): ?>
            <div class="mb-6">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6 text-[#8B4513]">Send us a Message</h2>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-gray-700 font-bold mb-2">Full Name *</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-gray-700 font-bold mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-gray-700 font-bold mb-2">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-gray-700 font-bold mb-2">Subject *</label>
                        <input type="text" id="subject" name="subject" required
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-gray-700 font-bold mb-2">Message *</label>
                        <textarea id="message" name="message" rows="5" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D2691E]"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-[#8B4513] text-white px-6 py-3 rounded-lg hover:bg-[#D2691E] transition duration-300 font-bold">
                        Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-6 text-[#8B4513]">Visit Our Bakery</h2>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="text-[#D2691E] text-2xl">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-2">Address</h3>
                            <p class="text-gray-600">Ecoland Drive, cor Peacock St<br>Talomo, Davao City<br>Davao del Sur</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="text-[#D2691E] text-2xl">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-2">Phone</h3>
                            <p class="text-gray-600">09123456789</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="text-[#D2691E] text-2xl">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-2">Email</h3>
                            <p class="text-gray-600">aldren_bakery@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="text-[#D2691E] text-2xl">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 mb-2">Business Hours</h3>
                            <p class="text-gray-600">Monday - Saturday<br>7:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="mt-8 p-4 bg-[#FDF8F5] rounded-lg">
                        <h3 class="font-bold text-gray-800 mb-2">Quick Response</h3>
                        <p class="text-gray-600">We typically respond to all inquiries within 24 hours during business hours. For urgent orders, please call us directly!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<section class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-[#8B4513] font-['Playfair_Display']">Find Us</h2>
            <p class="text-gray-600">Visit our bakery for the freshest treats in Davao City!</p>
        </div>
        
        <!-- Simple map placeholder - you can replace with Google Maps -->
        <div class="bg-white rounded-lg shadow-lg p-4 max-w-4xl mx-auto">
            <div class="aspect-video bg-gray-200 rounded-lg flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-map-marked-alt text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Sweet Delights Bakery</h3>
                    <p class="text-gray-600">Ecoland Drive, cor Peacock St<br>Talomo, Davao City, Davao del Sur</p>
                    <p class="text-sm text-gray-500 mt-2">📍 Located in the heart of Davao City</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
