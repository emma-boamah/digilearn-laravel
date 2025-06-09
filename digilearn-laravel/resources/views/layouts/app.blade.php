<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ShoutOutGh') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <style>
        /* CSS Variables */
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #b91c1c;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e40af;
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
            border: 1px solid var(--primary-red);
            color: var(--primary-red);
            background-color: transparent;
        }

        @media (min-width: 48rem) {
            .btn-outline {
                display: inline-flex;
            }
        }

        .btn-outline:hover {
            background-color:rgb(255, 239, 239);
        }

        .btn-primary {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius-full);
        }

        .btn-primary:hover {
            background-color: var(--primary-red-hover);
        }

        .btn-white {
            background-color: var(--white);
            color: var(--primary-red);
            border: 2px solid var(--white);
        }

        .btn-white:hover {
            background-color: var(--gray-50);
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
                            <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
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
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        @include('layouts.footer')
    </div>

    <script>
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
            });
        });
    });
    </script>
</body>
</html>
