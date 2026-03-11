<?php
/**
 * About Us Page (about.php)
 * Tells the story of Sweet Delights Bakery and founder Aldren Marino
 */

require_once 'config/database.php';
require_once 'config/validation.php';

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <section class="relative h-[400px] bg-cover bg-center rounded-lg overflow-hidden mb-16" 
             style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1555507036-ab1f4038808a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
        <div class="absolute inset-0 flex items-center justify-center text-center">
            <div class="text-white px-4">
                <h1 class="text-5xl md:text-6xl font-bold mb-4 font-['Playfair_Display']">About Sweet Delights</h1>
                <p class="text-xl md:text-2xl">A passion for baking, a commitment to quality</p>
            </div>
        </div>
    </section>

    <!-- Founder Story -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        <div class="flex items-center">
            <img src="/bakery-website/assets/images/aldren.jpg" 
                 alt="Aldren Marino - Founder" 
                 class="rounded-lg shadow-xl w-full max-w-md mx-auto">
        </div>
        <div class="flex flex-col justify-center">
            <h2 class="text-4xl font-bold mb-6 text-[#8B4513] font-['Playfair_Display']">Meet Our Founder</h2>
            <div class="bg-[#FDF8F5] p-6 rounded-lg border-l-4 border-[#D2691E]">
                <h3 class="text-2xl font-bold text-[#8B4513] mb-3">Aldren Marino</h3>
                <p class="text-gray-700 mb-4 italic">"Baking is not just about mixing ingredients; it's about creating memories, one loaf at a time."</p>
            </div>
            <p class="text-gray-700 mb-4 text-lg leading-relaxed">
                Founded in 2010 by <strong>Aldren Marino</strong>, Sweet Delights Bakery began as a small home kitchen with a big dream - to bring authentic, freshly baked goods to the Davao community.
            </p>
            <p class="text-gray-700 text-lg leading-relaxed">
                With over 13 years of baking experience and a passion passed down through generations, Aldren transformed his childhood love for baking into a beloved local institution.
            </p>
        </div>
    </section>

    <!-- Our Story Timeline -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-12 text-[#8B4513] font-['Playfair_Display']">Our Journey</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-[#8B4513] text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    2010
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">The Beginning</h3>
                <p class="text-gray-600">Started from a small home kitchen in Davao City with just 5 original recipes</p>
            </div>
            <div class="text-center">
                <div class="bg-[#D2691E] text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    2015
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">First Bakery</h3>
                <p class="text-gray-600">Opened our first physical store in Ecoland Drive, serving the local community</p>
            </div>
            <div class="text-center">
                <div class="bg-[#8B4513] text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    2024
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">Digital Expansion</h3>
                <p class="text-gray-600">Launched online ordering to serve more customers across Davao City</p>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="bg-white rounded-lg shadow-lg p-8 mb-16">
        <h2 class="text-4xl font-bold text-center mb-12 text-[#8B4513] font-['Playfair_Display']">Our Values</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="bg-[#FDF8F5] rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-2xl text-[#D2691E]"></i>
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">Baked with Love</h3>
                <p class="text-gray-600">Every product is made with passion and attention to detail</p>
            </div>
            <div class="text-center">
                <div class="bg-[#FDF8F5] rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-leaf text-2xl text-[#D2691E]"></i>
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">Fresh Ingredients</h3>
                <p class="text-gray-600">Only the finest, locally-sourced ingredients in every recipe</p>
            </div>
            <div class="text-center">
                <div class="bg-[#FDF8F5] rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl text-[#D2691E]"></i>
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">Community First</h3>
                <p class="text-gray-600">Serving our Davao community with pride and dedication</p>
            </div>
            <div class="text-center">
                <div class="bg-[#FDF8F5] rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-award text-2xl text-[#D2691E]"></i>
                </div>
                <h3 class="text-xl font-bold text-[#8B4513] mb-2">Quality Promise</h3>
                <p class="text-gray-600">Every item meets our highest standards of excellence</p>
            </div>
        </div>
    </section>

    <!-- Our Team -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-12 text-[#8B4513] font-['Playfair_Display']">Meet Our Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <img src="/bakery-website/assets/images/aldren.jpg" 
                     alt="Aldren Marino" 
                     class="rounded-full w-32 h-32 mx-auto mb-4 object-cover">
                <h3 class="text-xl font-bold text-[#8B4513] mb-1">Aldren Marino</h3>
                <p class="text-gray-600 mb-2">Founder & Head Baker</p>
                <p class="text-sm text-gray-500">13+ years of baking experience</p>
            </div>
            <div class="text-center">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80" 
                     alt="Maria Santos" 
                     class="rounded-full w-32 h-32 mx-auto mb-4 object-cover">
                <h3 class="text-xl font-bold text-[#8B4513] mb-1">Maria Santos</h3>
                <p class="text-gray-600 mb-2">Pastry Chef</p>
                <p class="text-sm text-gray-500">Specialist in cakes and pastries</p>
            </div>
            <div class="text-center">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80" 
                     alt="Bakery Team Member" 
                     class="rounded-full w-32 h-32 mx-auto mb-4 object-cover">
                <h3 class="text-xl font-bold text-[#8B4513] mb-1">Juan Reyes</h3>
                <p class="text-gray-600 mb-2">Master Baker</p>
                <p class="text-sm text-gray-500">Artisan bread specialist</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-[#8B4513] text-white rounded-lg p-12 text-center">
        <h2 class="text-3xl font-bold mb-4 font-['Playfair_Display']">Visit Sweet Delights Today</h2>
        <p class="text-xl mb-8">Experience the warmth and taste of authentic baking</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/bakery-website/menu.php" class="bg-[#D2691E] text-white px-8 py-3 rounded-lg hover:bg-white hover:text-[#8B4513] transition duration-300 font-bold">
                View Our Menu
            </a>
            <a href="/bakery-website/contact.php" class="bg-white text-[#8B4513] px-8 py-3 rounded-lg hover:bg-[#D2691E] hover:text-white transition duration-300 font-bold">
                Get in Touch
            </a>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
