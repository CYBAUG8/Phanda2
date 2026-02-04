<!DOCTYPE html>
<html lang="en">
<head>
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
