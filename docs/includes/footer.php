<?php
/**
 * Footer Include File
 * Contains footer and common scripts
 */
?>
    </main>

    <!-- Footer -->
    <footer class="bg-[#8B4513] text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Playfair_Display']">Sweet Delights</h3>
                    <p class="text-[#FDF8F5] mb-4">Creating delicious moments since 2010. We bake with love and the finest ingredients.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white hover:text-[#FDF8F5] transition duration-300"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white hover:text-[#FDF8F5] transition duration-300"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white hover:text-[#FDF8F5] transition duration-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white hover:text-[#FDF8F5] transition duration-300"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Playfair_Display']">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="/bakery-website/index.php" class="text-[#FDF8F5] hover:text-white transition duration-300">Home</a></li>
                        <li><a href="/bakery-website/menu.php" class="text-[#FDF8F5] hover:text-white transition duration-300">Menu</a></li>
                        <li><a href="/bakery-website/about.php" class="text-[#FDF8F5] hover:text-white transition duration-300">About Us</a></li>
                        <li><a href="/bakery-website/contact.php" class="text-[#FDF8F5] hover:text-white transition duration-300">Contact</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Playfair_Display']">Contact Info</h3>
                    <ul class="space-y-2 text-[#FDF8F5]">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Ecoland Drive, cor Peacock St, Talomo, Davao City, Davao del Sur</li>
                        <li><i class="fas fa-phone mr-2"></i> 09123456789</li>
                        <li><i class="fas fa-envelope mr-2"></i> aldren_bakery@gmail.com</li>
                        <li><i class="fas fa-clock mr-2"></i> Mon-Sat: 7am - 8pm</li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Playfair_Display']">Newsletter</h3>
                    <p class="text-[#FDF8F5] mb-4">Subscribe for special offers and updates!</p>
                    <form id="newsletter-form" class="flex flex-col space-y-2">
                        <input type="email" placeholder="Your email" class="px-4 py-2 rounded-lg text-[#8B4513] focus:outline-none focus:ring-2 focus:ring-[#D2691E]">
                        <button type="submit" class="bg-[#D2691E] text-white px-4 py-2 rounded-lg hover:bg-[#8B4513] transition duration-300">Subscribe</button>
                    </form>
                </div>
            </div>

            <div class="border-t border-[#FDF8F5] mt-8 pt-8 text-center text-[#FDF8F5]">
                <p>&copy; 2024 Sweet Delights Bakery. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="/bakery-website/assets/js/service.js"></script>
    
    <!-- Newsletter Form Handler -->
    <script>
        document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email && isValidEmail(email)) {
                showToast('Thank you for subscribing!', 'success');
                this.reset();
            } else {
                showToast('Please enter a valid email', 'error');
            }
        });
    </script>
</body>
</html>