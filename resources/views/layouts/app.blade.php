<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'ShoutOutGh'))</title>

    <!-- Additional head content -->
    @yield('head')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* CSS Variables */
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #b91c1c;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e58afff;
            --white: #ffffff;
            --black: #000000;
            --red:#ffefef;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-900: #111827;
            --font-family-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --container-max-width: 1200px;
            --border-radius-xsm: 0.0125rem;
            --border-radius-sm: 0.125rem;
            --border-radius-md: 0.375rem;
            --border-radius-lg: 0.5rem;
            --border-radius-full: 9999px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --transition-duration: 0.3s;
        }

        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family-sans);
            line-height: 1.6;
            color: #333;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 120px;
            }
        }

        /* Container */
        .container {
            max-width: 100%;
            margin: 0 3rem;
            padding: 0 1rem;
        }

        /* Utility classes */
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .min-h-screen { min-height: 100vh; }
        .flex-1 { flex: 1 1 0%; }

        /* Header styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            padding: 0 1rem;
            transition: all var(--transition-duration) ease;
        }

        .nav-container {
            max-width: 100%;
            margin: 0;
        }

        .nav-content {
            background-color: var(--white);
            border-radius: var(--border-radius-full);
            box-shadow: var(--shadow-sm);
            padding: 0.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all var(--transition-duration) ease;
            max-width: 100%;
            margin: 0;
            min-height: 65px;
            grid-template-columns: 1fr 2fr 1fr;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            flex-shrink: 0;
            min-width: 180px;
        }

        .logo-image {
            height: 47px;
            width: 100%;
            max-width: 500px;
            object-fit: contain;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .logo-image {
                height: 55px;
            }
            
            .nav-content {
                padding: 1rem 1.5rem;
                min-height: 70px;
            }

            body {
                padding-top: 130px; /* Increased from 120px */
            }
            
            /* .nav-links {
                position: static;
                transform: none;
                order: 3;
                flex-basis: 100%;
                justify-content: center;
                gap: 2rem;
                padding-top: 1rem;
                border-top: 1px solid var(--gray-300);
                margin-top: 1rem;
            } */
            
            .nav-buttons {
                gap: 0.5rem;
            }
            
            .nav-buttons::before {
                height: 2rem;
                margin-right: 0.5rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            .btn-primary {
                padding: 0.5rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .logo-image {
                height: 45px;
            }
            
            .nav-content {
                padding: 0.75rem 1rem;
                flex-wrap: wrap;
            }
            
            .nav-links {
                gap: 1.5rem;
            }
            
            .nav-links a {
                font-size: 1rem;
            }
        }

        .nav-links {
            display: none;
            gap: 2rem;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            align-items: center;
        }

        @media (min-width: 48rem) {
            .nav-links {
                display: flex;
            }
        }

        .nav-links a {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--secondary-blue);
            text-decoration: none;
            transition: color 0.2s ease;
            position: relative;
            padding: 0.75rem 0;
            white-space: nowrap;
        }

        .nav-links a:hover {
            color: var(--secondary-blue-hover);
        }

        /* Active nav link with blue underline */
        .nav-links a.active {
            color: var(--secondary-blue);
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--secondary-blue);
            border-radius: 2px;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            flex-shrink: 0;
        }

        /* Grey vertical line before login button */
        .nav-buttons::before {
            content: '';
            width: 1px;
            height: 2.5rem;
            background-color: var(--gray-300);
            margin-right: 1rem;
        }

        .btn {
            border-radius: var(--border-radius-full);
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .btn-outline {
            display: none;
            border: 1px solid var(--secondary-blue);
            color: var(--secondary-blue);
            background-color: transparent;
        }

        @media (min-width: 48rem) {
            .btn-outline {
                display: inline-flex;
            }
        }

        .btn-outline:hover {
            background-color:rgba(239, 245, 255, 1);
        }

        .btn-primary {
            background-color: var(--secondary-blue);
            color: var(--white);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius-full);
        }

        .btn-primary:hover {
            background-color: var(--secondary-blue-hover);
        }

        .btn-white {
            background-color: var(--white);
            color: var(--primary-red);
            border: 2px solid var(--white);
        }

        .btn-white:hover {
            background-color: var(--gray-50);
        }

        /* Modern Mobile Menu Styles */
        #mobile-menu-button {
            display: none;
            position: fixed;
            top: 0.50rem;
            right: 1.25rem;
            z-index: 100;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: var(--primary-red);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(225, 30, 45, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #mobile-menu-button:hover {
            transform: scale(1.05);
            background: var(--primary-red-hover);
        }

        #mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 99;
            padding: 2rem;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        #mobile-menu.open {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }

        .mobile-menu-item {
            width: 100%;
            max-width: 300px;
            padding: 1.2rem;
            margin: 0.5rem 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--gray-900);
            text-decoration: none;
            transition: all 0.3s ease;
            transform: translateY(0);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .mobile-menu-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            color: var(--primary-red);
        }

        .login {
            border-color: var(--secondary-blue);
            margin-top: 1.5rem;
        }

        .login:hover {
            background: var(--secondary-blue-hover);
        }

        .mobile-menu-item.signup {
            border-color: var(--primary-red);
            margin-top: 1.5rem;
        }

        .mobile-menu-item.signup:hover {
            background: var(--primary-red-hover);
        }

        .hamburger-icon, .close-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            transition: opacity 0.3s ease;
        }

        .close-icon {
            opacity: 0;
        }

        #mobile-menu-button.active .hamburger-icon {
            opacity: 0;
        }

        #mobile-menu-button.active .close-icon {
            opacity: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #mobile-menu-button {
                display: block;
            }
            
            .nav-links, .nav-buttons {
                display: none;
            }
            
            body {
                padding-top: 70px;
            }
            
            .nav-content {
                position: relative;
            }
        }

    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col">
        <!-- Main Navigation -->
        <header class="header">
            <div class="nav-container">
                <div class="nav-content" id="nav-content">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="logo">
                            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                        </a>
                        <nav class="nav-links">
                            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About Us</a>
                            <a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'active' : '' }}">Pricing</a>
                            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                        </nav>
                    </div>
                    <div class="nav-buttons">
                        <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                        <a href="{{ route('signup') }}" class="btn btn-primary">Sign Up Free</a>
                    </div>
                </div>

                <!-- Modern Mobile Menu -->
                <button id="mobile-menu-button" aria-label="Open navigation menu">
                    <svg class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Mobile menu -->
                <div id="mobile-menu">
                    <a href="{{ route('home') }}" class="mobile-menu-item">Home</a>
                    <a href="{{ route('about') }}" class="mobile-menu-item">About Us</a>
                    <a href="{{ route('pricing') }}" class="mobile-menu-item">Pricing</a>
                    <a href="{{ route('contact') }}" class="mobile-menu-item">Contact</a>
                    <a href="{{ route('login') }}" class="mobile-menu-item login">Login</a>
                    <a href="{{ route('signup') }}" class="mobile-menu-item signup">Sign Up Free</a>
                </div>
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        @include('layouts.footer')

        <!-- Cookie Consent Banner -->
        @include('cookie-consent-banner')
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // YouTube-style hover-to-play video functionality
    document.addEventListener('DOMContentLoaded', function() {
        const videoCards = document.querySelectorAll('.hover-video-card');
        
        videoCards.forEach(card => {
            const videoId = card.getAttribute('data-video-id');
            const video = document.getElementById(videoId);
            
            // Play video on hover
            card.addEventListener('mouseenter', function() {
                if (video.paused) {
                    // Reset to beginning if it was at the end
                    if (video.currentTime === video.duration) {
                        video.currentTime = 0;
                    }
                    
                    // Play the video
                    video.play().catch(e => {
                        // Handle any autoplay restrictions
                        console.log('Autoplay prevented:', e);
                    });
                }
            });
            
            // Pause video when mouse leaves
            card.addEventListener('mouseleave', function() {
                if (!video.paused) {
                    video.pause();
                }
            });
            
            // Handle touch devices
            card.addEventListener('touchstart', function() {
                if (video.paused) {
                    video.play().catch(e => console.log('Autoplay prevented on touch:', e));
                } else {
                    video.pause();
                }
            }, { passive: true });
        });

        // Mobile Menu Functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', function() {
            mobileMenuButton.classList.toggle('active');
            mobileMenu.classList.toggle('open');
            document.body.classList.toggle('no-scroll');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileMenu.classList.contains('open') && 
                !mobileMenu.contains(event.target) && 
                !mobileMenuButton.contains(event.target)) {
                mobileMenuButton.classList.remove('active');
                mobileMenu.classList.remove('open');
                document.body.classList.remove('no-scroll');
            }
        });

        // Close menu when clicking links
        document.querySelectorAll('.mobile-menu-item').forEach(item => {
            item.addEventListener('click', () => {
                mobileMenuButton.classList.remove('active');
                mobileMenu.classList.remove('open');
                document.body.classList.remove('no-scroll');
            });
        });
    });

    // periodic ping to keep user session active
    setInterval(() => {
                if (document.visibilityState === 'visible') {
                    fetch('/ping', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                }
            }, 300000); // 5 minutes
    </script>
</body>
</html>
