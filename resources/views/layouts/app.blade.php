<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'ShoutOutGh'))</title>

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="@yield('description', 'ShoutOutGH is Ghana\'s premier online learning platform offering interactive lessons, quizzes, and educational resources for students from primary to tertiary level.')">
    <meta name="keywords"
        content="@yield('keywords', 'online education in ghana, online school, education, school, e-learning ghana, ghana education, digital learning, online classes ghana, ShoutOutGH')">
    <meta name="robots" content="index, follow">
    <meta name="author" content="ShoutOutGH">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Additional head content (OG tags, structured data, etc.) -->
    @yield('head')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&family=work-sans:400,600,700&display=swap"
        rel="stylesheet" />

    <!-- Alpine.js -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Conditional Analytics -->
    @if(
            auth()->check() ? $cookieManager->isAllowed('analytics') : (request()->cookie('digilearn_consent') ?
                json_decode(request()->cookie('digilearn_consent'), true)['analytics'] ?? false : false)
        )
        @include('partials.analytics')
    @endif

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* CSS Variables */
        :root,
        [data-theme="light"] {
            --primary-red: #E11E2D;
            --primary-red-hover: #b91c1c;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e58afff;
            --white: #ffffff;
            --black: #000000;
            --red: #ffefef;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-900: #111827;

            /* Semantic Light Mode Variables */
            --bg-main: #f9fafb;
            --bg-surface: #ffffff;
            --text-main: #333333;
            --text-muted: #6b7280;
            --border-color: #d1d5db;
            --mobile-menu-bg: rgba(255, 255, 255, 0.95);
            --accent: #2677B8;

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
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
        }

        [data-theme="dark"] {
            --bg-main: #000000;
            /* Pure Black for battery saving */
            --bg-surface: #16181c;
            /* Very dark gray for cards/inputs */
            --text-main: #ffffff;
            --text-muted: #71767b;
            --border-color: transparent;
            /* Invisible border to match YouTube's flat dark UI design */
            --mobile-menu-bg: rgba(22, 24, 28, 0.95);
            --accent: #1d9bf0;

            /* Custom CSS variables override */
            --white: #16181c;
            --black: #ffffff;
            --gray-50: #000000;
            --gray-100: #202327;
            --gray-200: #2f3336;
            --gray-300: #3e4144;
            --gray-400: #71767b;
            --gray-500: #8b98a5;
            --gray-600: #a4b1cd;
            --gray-700: #e2e8f0;
            --gray-800: #f1f5f9;
            --gray-900: #ffffff;

            color-scheme: dark;
        }

        /* Invisible border and color overrides */
        [data-theme="dark"] .user-avatar-header {
            border-color: transparent !important;
        }

        [data-theme="dark"] .btn-outline {
            border: 1px solid var(--secondary-blue) !important;
        }

        [data-theme="dark"] .btn-outline:hover {
            background-color: var(--secondary-blue) !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .hero-content {
            color: #ffffff !important;
        }

        [data-theme="dark"] .about-hero {
            background: #000000;
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
            color: var(--text-main);
            background-color: var(--bg-main);
            transition: background-color var(--transition-duration) ease, color var(--transition-duration) ease;
        }

        @media (max-width: 1024px) {
            body {
                /* Add safe area inset + header height */
                padding-top: calc(130px + var(--safe-area-inset-top));
            }
        }

        /* Container */
        .container {
            max-width: 100%;
            margin: 0 1rem;
            padding: 0 1rem;
        }

        /* Utility classes */
        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .items-center {
            align-items: center;
        }

        .justify-center {
            justify-content: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .min-h-screen {
            min-height: 100vh;
        }

        .flex-1 {
            flex: 1 1 0%;
        }

        /* Header styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            padding: calc(0.75rem + var(--safe-area-inset-top)) 1rem 0 1rem;
            transition: all var(--transition-duration) ease;
        }

        .nav-container {
            max-width: 100%;
            margin: 0;
        }

        .nav-content {
            background-color: var(--bg-surface);
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
        @media (max-width: 1024px) {
            .logo-image {
                height: 42px;
                width: auto;
            }

            .nav-content {
                padding: 1rem 1.5rem;
                min-height: 70px;
            }

            body {
                padding-top: calc(130px + var(--safe-area-inset-top));
                /* Increased from 120px */
            }

            .nav-buttons {
                display: flex !important;
                gap: 0.5rem;
            }

            .nav-buttons::before {
                display: none !important;
            }

            .nav-buttons>*:not(#themeToggleBtn) {
                display: none !important;
            }

            #themeToggleBtn {
                margin-right: 4.5rem;
                /* Space for the floating hamburger menu */
            }
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: var(--border-color);
            color: var(--text-main);
        }

        .dropdown-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: var(--text-muted);
        }

        .dropdown-item-form {
            margin: 0;
            padding: 0;
            background: none;
            border: none;
        }

        .logout-btn {
            background: none;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            padding: 0;
            color: inherit;
            font: inherit;
        }

        .logout-btn:hover {
            background: none;
            color: inherit;
        }

        @media (max-width: 480px) {
            .logo-image {
                height: 45px;
            }

            .nav-content {
                padding: 0.75rem 1rem;
                flex-wrap: nowrap;
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

        @media (min-width: 64rem) {
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

        @media (min-width: 64rem) {
            .btn-outline {
                display: inline-flex;
            }
        }

        .btn-outline:hover {
            background-color: rgba(239, 245, 255, 1);
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
            top: calc(1rem + var(--safe-area-inset-top));
            right: 1.25rem;
            z-index: 100;
            width: 3rem;
            height: 3rem;
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
            background: var(--mobile-menu-bg);
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
            background: var(--bg-surface);
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.3s ease;
            transform: translateY(0);
            border: 1px solid var(--border-color);
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

        .hamburger-icon,
        .close-icon {
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
        @media (max-width: 1024px) {
            #mobile-menu-button {
                display: block;
            }

            .nav-links {
                display: none;
            }

            body {
                padding-top: calc(75px + var(--safe-area-inset-top));
            }

            .nav-content {
                position: relative;
                min-height: 75px;
                padding: 0.5rem 1.25rem;
            }
        }
    </style>
    <!-- Theme Selection Script (Before Body to prevent FOUC) -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) { }
        })();
    </script>
</head>

<body>
    <div class="min-h-screen flex flex-col">
        <!-- Main Navigation -->
        <header class="header">
            <div class="nav-container">
                <div class="nav-content" id="nav-content">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="logo">
                            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh"
                                class="logo-image">
                        </a>
                        <nav class="nav-links">
                            <a href="{{ route('home', ['show_home' => 'true']) }}"
                                class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                            <a href="{{ route('about') }}"
                                class="{{ request()->routeIs('about') ? 'active' : '' }}">About Us</a>
                            <a href="{{ route('pricing') }}"
                                class="{{ request()->routeIs('pricing') ? 'active' : '' }}">Pricing</a>
                            <a href="{{ route('contact') }}"
                                class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                        </nav>
                    </div>

                    <div class="nav-buttons">
                        <!-- Theme Toggle Button -->
                        <button id="themeToggleBtn"
                            style="background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; padding: 0.5rem; border-radius: 50%; z-index: 60;"
                            aria-label="Toggle Theme">
                            <svg id="themeIconDark" style="display: none; width: 22px; height: 22px;"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg id="themeIconLight" style="display: none; width: 22px; height: 22px;"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 2.364a1 1 0 011.415 0l.707.707a1 1 0 01-1.414 1.415l-.707-.707a1 1 0 010-1.415zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zm-2.364 4.22a1 1 0 010 1.415l-.707.707a1 1 0 01-1.415-1.414l.707-.707a1 1 0 011.415 0zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4.22-2.364a1 1 0 01-1.415 0l-.707-.707a1 1 0 011.414-1.415l.707.707a1 1 0 010 1.415zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm2.364-4.22a1 1 0 010-1.415l.707-.707a1 1 0 011.415 1.414l-.707.707a1 1 0 01-1.415 0zM10 14a4 4 0 100-8 4 4 0 000 8z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>

                        @auth
                            <a href="{{ route('dashboard.main') }}" class="btn btn-primary" style="margin-right: 1rem;">
                                Learning Hub
                            </a>

                            <div class="user-dropdown" style="position: relative; display: inline-block;">
                                <button class="user-avatar-header" id="userDropdownToggle"
                                    style="border: none; background: none; cursor: pointer; padding: 0;">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="Profile"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    @else
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #E11E2D, #2677B8); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.2rem;">
                                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                        </div>
                                    @endif
                                </button>

                                <div class="user-dropdown-menu" id="publicUserDropdown"
                                    style="position: absolute; right: 0; top: 100%; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); min-width: 200px; display: none; z-index: 50; margin-top: 0.5rem;">
                                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                        <div style="font-weight: 600; color: var(--text-main);">{{ auth()->user()->name }}
                                        </div>
                                        <div
                                            style="font-size: 0.875rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ auth()->user()->email }}
                                        </div>
                                    </div>

                                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profile
                                    </a>
                                    <a href="{{ route('settings') }}" class="dropdown-item">
                                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Settings
                                    </a>

                                    <form action="{{ route('logout') }}" method="POST" class="dropdown-item-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item logout-btn">
                                            <svg class="dropdown-icon" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                            <a href="{{ route('signup') }}" class="btn btn-primary">Sign Up Free</a>
                        @endauth
                    </div>
                </div>

                <!-- Modern Mobile Menu -->
                <button id="mobile-menu-button" aria-label="Open navigation menu">
                    <svg class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <!-- Mobile menu -->
                <div id="mobile-menu">
                    <a href="{{ route('home', ['show_home' => 'true']) }}" class="mobile-menu-item">Home</a>
                    <a href="{{ route('about') }}" class="mobile-menu-item">About Us</a>
                    <a href="{{ route('pricing') }}" class="mobile-menu-item">Pricing</a>
                    <a href="{{ route('contact') }}" class="mobile-menu-item">Contact</a>
                    @auth
                        <a href="{{ route('dashboard.main') }}" class="mobile-menu-item"
                            style="color: var(--secondary-blue);">Learning Hub</a>
                        <a href="{{ route('profile.show') }}" class="mobile-menu-item">Profile</a>
                        <a href="{{ route('settings') }}" class="mobile-menu-item">Settings</a>
                        <form action="{{ route('logout') }}" method="POST" style="width: 100%;">
                            @csrf
                            <button type="submit" class="mobile-menu-item"
                                style="width: 100%; color: var(--primary-red); border: none; background: none; cursor: pointer;">Log
                                Out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mobile-menu-item login">Login</a>
                        <a href="{{ route('signup') }}" class="mobile-menu-item signup">Sign Up Free</a>
                    @endauth
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
        // Theme Toggle Logic
        document.addEventListener('DOMContentLoaded', function () {
            const themeBtn = document.getElementById('themeToggleBtn');
            const iconDark = document.getElementById('themeIconDark');
            const iconLight = document.getElementById('themeIconLight');

            function updateThemeIcon() {
                if (document.documentElement.getAttribute('data-theme') === 'dark') {
                    iconDark.style.display = 'none';
                    iconLight.style.display = 'block';
                } else {
                    iconDark.style.display = 'block';
                    iconLight.style.display = 'none';
                }
            }

            if (themeBtn) {
                updateThemeIcon();
                themeBtn.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    try {
                        localStorage.setItem('theme', newTheme);
                    } catch (e) { }
                    updateThemeIcon();
                });
            }
        });

        // YouTube-style hover-to-play video functionality
        document.addEventListener('DOMContentLoaded', function () {
            const videoCards = document.querySelectorAll('.hover-video-card');

            videoCards.forEach(card => {
                const videoId = card.getAttribute('data-video-id');
                const video = document.getElementById(videoId);

                // Play video on hover
                card.addEventListener('mouseenter', function () {
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
                card.addEventListener('mouseleave', function () {
                    if (!video.paused) {
                        video.pause();
                    }
                });

                // Handle touch devices
                card.addEventListener('touchstart', function () {
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

            mobileMenuButton.addEventListener('click', function () {
                mobileMenuButton.classList.toggle('active');
                mobileMenu.classList.toggle('open');
                document.body.classList.toggle('no-scroll');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (event) {
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

            // User Dropdown Toggle for Desktop
            const userToggle = document.getElementById('userDropdownToggle');
            const userMenu = document.getElementById('publicUserDropdown');

            if (userToggle && userMenu) {
                userToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
                });

                document.addEventListener('click', function (e) {
                    if (!userToggle.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.style.display = 'none';
                    }
                });
            }
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
    @stack('scripts')
</body>

</html>