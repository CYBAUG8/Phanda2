<!DOCTYPE html>
<html lang="en">
<head>
<<<<<<< HEAD
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Phanda</title>

    {{-- Vite JS entry (dev or built fallback) --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/firstpage.js'])
    @else
        <script src="/build/assets/firstpage.js"></script>
    @endif
</head>
<body>
    <!-- Geometric Background (no CSS included as requested) -->
    <div class="bg-geometric">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
    </div>
    
    <!-- Corner Accents -->
    <div class="corner-accent top-left"></div>
    <div class="corner-accent top-right"></div>
    <div class="corner-accent bottom-left"></div>
    <div class="corner-accent bottom-right"></div>
    
    <div class="welcome-container">
        <div class="welcome-content">
            <!-- Main Header -->
            <header class="main-header">
                <h1 class="phanda-logo">Phanda</h1>
                <p class="tagline">Where Users Meet Providers</p>
            </header>

            <!-- Login Section -->
            <section class="login-options">
                <!-- User Login Card -->
                <div class="login-card">
                    <div class="card-icon">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                    <h4>User</h4>               
                    <button class="login-btn" id="userLogin">User Portal</button>
                </div>
                
                <!-- Provider Login Card -->
                <div class="login-card">
                    <div class="card-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4>Provider</h4>                   
                    <button class="login-btn provider-btn" id="providerLogin">Provider Portal</button>
                </div>
            </section>
            
            <!-- Footer -->
            <footer class="main-footer">
                <div class="footer-links">
                    <a href="#" class="footer-link">About Phanda</a>
                    <a href="#" class="footer-link">Security</a>
                    <a href="#" class="footer-link">Contact</a>
                    <a href="#" class="footer-link">FAQ</a>
                </div>
                <p class="copyright">© 2025 Phanda. All rights reserved. The future of service connections.</p>
            </footer>
        </div>
    </div>

    {{-- No inline CSS per request; JS is loaded via Vite entry `resources/js/firstpage.js` --}}
</body>
</html>
=======
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Phanda – where users meet providers</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    /* Soft warm gray background with very subtle dots */
    .bg-soft {
        background-color: #f7f6f5;  
        background-image: radial-gradient(rgba(249,115,22,0.02) 1px, transparent 1px);
        background-size: 40px 40px;
    }
</style>
</head>
<body class="bg-soft font-sans antialiased text-gray-600 min-h-screen flex flex-col">

<!-- Navigation -->
<div class="container mx-auto px-6 pt-6 flex justify-between items-center">
    <a href="/" class="text-2xl font-medium text-orange-500">Phanda<span class="text-gray-400">.</span></a>
    <div class="space-x-6 text-sm text-gray-400">
        <a href="#" class="hover:text-orange-500 transition">About</a>
        <a href="#" class="hover:text-orange-500 transition">Contact</a>
    </div>
</div>

<!-- Hero Section -->
<main class="flex-grow flex items-center justify-center px-4 py-16 md:py-20">
    <div class="max-w-4xl mx-auto text-center">

        <!-- Headline – lighter and softer -->
        <h1 class="text-3xl md:text-4xl font-normal mb-5 leading-snug text-gray-700">
            Connect with trusted <br class="hidden sm:block">
            <span class="text-orange-500 font-medium">users & providers</span>
        </h1>
        <p class="text-base md:text-lg text-gray-500 mb-10 max-w-2xl mx-auto">
            Phanda brings people together. One account, two worlds — whether you're looking for a service or offering one.
        </p>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="{{ route('login') }}" 
               class="group flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-medium px-6 py-3 rounded-lg shadow-sm transition transform hover:scale-105 text-base">
                <i class="fas fa-arrow-right-to-bracket text-orange-200 group-hover:translate-x-0.5 transition"></i>
                <span>Log in</span>
            </a>
            <a href="/register" 
               class="group flex items-center gap-2 bg-white/70 backdrop-blur-sm border border-gray-200 hover:border-orange-300 text-gray-600 font-medium px-6 py-3 rounded-lg shadow-sm transition transform hover:scale-105 text-base">
                <i class="fas fa-user-plus text-orange-400 group-hover:scale-105 transition"></i>
                <span>Register</span>
            </a>
        </div>

        <!-- Subtle role hint -->
        <p class="mt-6 text-sm text-gray-400 flex items-center justify-center gap-3">
            <span class="flex items-center gap-1"><i class="fas fa-user-astronaut text-orange-300"></i> I need a service</span>
            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
            <span class="flex items-center gap-1"><i class="fas fa-rocket text-orange-300"></i> I offer a service</span>
        </p>

        <!-- Cards -->
        <div class="mt-16 grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            <!-- User card -->
            <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-5 border border-gray-100 shadow-sm">
                <div class="w-12 h-12 bg-orange-100/50 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-user-astronaut text-orange-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700 mb-1">For users</h3>
                <p class="text-gray-500 text-sm mb-3">Find trusted providers for any service – from home repairs to creative work.</p>
                <span class="text-xs font-medium text-orange-500 bg-orange-50 px-2 py-1 rounded-full">Browse services</span>
            </div>
            <!-- Provider card -->
            <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-5 border border-gray-100 shadow-sm">
                <div class="w-12 h-12 bg-orange-100/50 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-rocket text-orange-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700 mb-1">For providers</h3>
                <p class="text-gray-500 text-sm mb-3">Showcase your skills, manage bookings, and grow your business.</p>
                <span class="text-xs font-medium text-orange-500 bg-orange-50 px-2 py-1 rounded-full">List your service</span>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="container mx-auto px-6 pb-8 pt-12 border-t border-gray-200 mt-12 text-gray-400 text-sm">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex gap-6">
            <a href="#" class="hover:text-orange-500 transition">About Phanda</a>
            <a href="#" class="hover:text-orange-500 transition">Security</a>
            <a href="#" class="hover:text-orange-500 transition">Contact</a>
            <a href="#" class="hover:text-orange-500 transition">FAQ</a>
        </div>
        <div class="text-xs">
            © 2025 Phanda. The future of service connections.
        </div>
    </div>
    <div class="flex justify-center gap-1 mt-4 opacity-10">
        <span class="w-1 h-1 bg-orange-300 rounded-full"></span>
        <span class="w-1 h-1 bg-orange-300 rounded-full"></span>
        <span class="w-1 h-1 bg-orange-300 rounded-full"></span>
    </div>
</footer>
</body>
</html>
>>>>>>> Lethokuhle
