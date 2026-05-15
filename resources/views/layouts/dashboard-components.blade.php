<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csp-nonce" content="{{ request()->attributes->get('csp_nonce') }}">
    <title>{{ $title ?? config('app.name', 'ShoutOutGh') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Theme Selection Script (Before Body to prevent FOUC) -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function() {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>

    @yield('head')

    <!-- Alpine.js -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Conditional Analytics -->
    @if(auth()->check() ? (isset($cookieManager) ? $cookieManager->isAllowed('analytics') : false) : (request()->cookie('digilearn_consent') ? json_decode(request()->cookie('digilearn_consent'), true)['analytics'] ?? false : false))
        @include('partials.analytics')
    @endif


    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root, [data-theme="light"] {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --white: #ffffff;
            --gray-25: #fcfcfd;
            --gray-50: #f8f9fa;
            --gray-100: #f1f3f5;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            /* Semantic Light Mode Variables */
            --bg-main: #f8f9fa;
            --bg-surface: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --header-bg: rgba(255, 255, 255, 0.8);
            --filter-bg: rgba(255, 255, 255, 0.75);
            --accent: #E11E2D;

            --shadow-sm: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --sidebar-width-expanded: 240px;
            --sidebar-width-collapsed: 0px;
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
            --transition-speed: 0.4s;
            --transition-timing: cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="dark"] {
            --bg-main: #000000;
            --bg-surface: #16181c;
            --text-main: #ffffff;
            --text-muted: #71767b;
            --border-color: transparent;
            --header-bg: rgba(22, 24, 28, 0.8);
            --filter-bg: rgba(22, 24, 28, 0.75);
            --accent: #E11E2D;
            color-scheme: dark;
            
            /* Overrides */
            --gray-25: #000000;
            --gray-50: #16181c;
            --gray-100: #202327;
            --gray-200: #2f3336;
            --gray-300: #3e4144;
            --gray-400: #71767b;
            --gray-500: #8b98a5;
            --gray-600: #a4b1cd;
            --gray-700: #e2e8f0;
            --gray-800: #f1f5f9;
            --gray-900: #ffffff;
            --white: #16181c;
        }

        .no-pointer-events {
            pointer-events: none !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            transition: background-color var(--transition-duration) ease, color var(--transition-duration) ease;
        }

        /* YouTube-style Sidebar */
        .filter-bar {
            position: fixed !important;
            top: calc(60px + var(--safe-area-inset-top)) !important; /* Directly below the header */
            left: 0; /* Start from left edge for full width */
            width: 100%;
            background-color: var(--filter-bg);
            border-bottom: 1px solid var(--border-color);
            z-index: 999 !important;
            backdrop-filter: blur(10px) saturate(160%);
            -webkit-backdrop-filter: blur(10px) saturate(160%);
            transition: padding-left var(--transition-speed) var(--transition-timing);
            height: calc(60px + var(--safe-area-inset-top));
            padding-top: calc(0.75rem + var(--safe-area-inset-top));
        }
        .youtube-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--sidebar-width-expanded) !important;
            height: 100vh !important;
            max-height: 100vh !important;
            background-color: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            z-index: 1000;
            transition: width var(--transition-speed) var(--transition-timing), 
                        transform var(--transition-speed) var(--transition-timing),
                        opacity var(--transition-speed) var(--transition-timing);
            overflow-y: scroll;
            overflow-x: hidden;   /* clip horizontal bleed when collapsed */
            display: flex;
            flex-direction: column;
            padding-top: var(--safe-area-inset-top);
        }

        .youtube-sidebar.collapsed {
            width: 0 !important;
            transform: translateX(-10%);
            opacity: 0;
            pointer-events: none;
            border-right-color: transparent;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            height: 64px;
            min-height: 64px;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-right: 1rem;
        }

        .sidebar-toggle-btn:hover {
            background-color: var(--gray-100);
        }

        .hamburger-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-700);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .sidebar-logo {
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-logo img {
            height: 32px;
            width: auto;
        }

        .sidebar-brand {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-red);
            letter-spacing: -0.025em;
            white-space: nowrap;
        }

        .sidebar-content {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto !important;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            scrollbar-width: thin;
            scrollbar-color: var(--gray-400) transparent;
            scroll-behavior: smooth;
            max-height: calc(100vh - (64px + var(--safe-area-inset-top))) !important;
        }

        /* Custom scrollbar for sidebar content */
        .sidebar-content::-webkit-scrollbar {
            width: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-content:hover::-webkit-scrollbar {
            opacity: 1;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: transparent;
            margin: 8px 0;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 4px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        .sidebar-section {
            margin-bottom: 1.5rem;
            flex-shrink: 0;
        }

        .sidebar-section:last-of-type {
            margin-top: 0;
            margin-bottom: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--gray-100);
        }

        .sidebar-section-title {
            font-size: 0.7rem;      /* Even more subtle */
            font-weight: 500;
            color: var(--gray-500);
            padding: 1rem 1.5rem 0.5rem; /* Better spacing for headers */
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .sidebar-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
            position: relative;
        }

        .sidebar-menu-item:hover {
            background-color: var(--border-color);
            color: var(--text-main);
            border-left-color: var(--border-color);
        }

        .sidebar-menu-item.active {
            background-color: rgba(225, 30, 45, 0.05); /* Lighter red tint */
            color: var(--primary-red);
            border-left-color: var(--primary-red);
            font-weight: 500; /* YouTube uses medium weight for active */
        }

        /* AI Tutor Premium Sidebar Effect */
        .sidebar-menu-item.ai-tutor-premium {
            overflow: hidden;
            z-index: 1;
            margin: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            border-left: none !important;
            padding: 0.75rem 1.25rem;
        }

        .sidebar-menu-item.ai-tutor-premium::before {
            content: '';
            position: absolute;
            z-index: -2;
            top: -150%;
            left: -150%;
            width: 400%;
            height: 400%;
            background: conic-gradient(transparent,
                    var(--secondary-blue),
                    transparent 30%,
                    #1a508b,
                    transparent 50%);
            animation: rotate-border 4s linear infinite;
        }

        .sidebar-menu-item.ai-tutor-premium::after {
            content: '';
            position: absolute;
            z-index: -1;
            inset: 1.5px;
            background: var(--bg-surface);
            border-radius: calc(0.5rem - 1.5px);
            transition: background 0.2s;
        }

        .sidebar-menu-item.ai-tutor-premium:hover::after {
            background: var(--gray-100);
        }

        .sidebar-menu-item.ai-tutor-premium.active {
            background-color: transparent;
            color: #2677B8;
        }

        .sidebar-menu-item.ai-tutor-premium.active::after {
            background: rgba(38, 119, 184, 0.08);
        }

        @keyframes rotate-border {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .sidebar-menu-icon {
            width: 24px;   /* YouTube standard */
            height: 24px;
            flex-shrink: 0;
        }

        .sidebar-menu-text {
            font-size: 0.875rem; /* 14px */
            font-weight: 400;    /* YouTube uses regular for default */
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .sidebar-menu-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item {
            padding: 0.75rem;
            justify-content: center;
            gap: 0;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border-left: none;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item:hover {
            border-left-color: transparent;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item.active {
            border-left-color: transparent;
            background-color: var(--primary-red);
            color: var(--white);
        }

        /* Tooltip for collapsed state */
        .sidebar-menu-item .tooltip {
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--gray-800);
            color: var(--white);
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 1001;
            pointer-events: none;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }

        .main-content {
            position: relative;
            margin-left: var(--sidebar-width-expanded) !important;
            margin-top: var(--safe-area-inset-top) !important;
            min-height: calc(100vh - 60px);
            background-color: var(--bg-main);
            /* Synchronise all three so nothing jumps to the right */
            transition: margin-left var(--transition-speed) var(--transition-timing),
                        width var(--transition-speed) var(--transition-timing),
                        max-width var(--transition-speed) var(--transition-timing);
            width: calc(100% - var(--sidebar-width-expanded));
            max-width: calc(100% - var(--sidebar-width-expanded));
            overflow-x: hidden;
        }

        /* Collapsed state — !important to beat the base !important rules above */
        .youtube-sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed) !important;
            width: calc(100% - var(--sidebar-width-collapsed)) !important;
            max-width: calc(100% - var(--sidebar-width-collapsed)) !important;
        }

        .top-header {
            padding-left: var(--sidebar-width-expanded);
        }

        /* Mobile overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Improved mobile responsive adjustments for all devices */
        @media (max-width: 768px) {
            .youtube-sidebar {
                transform: translateX(-100%);
                width: 280px;
                transition: transform 0.3s ease;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            /* Main content takes full width on mobile */
            .subjects-filter-container {
            position: fixed !important;
            left: 0 !important;
            top: calc(116px + var(--safe-area-inset-top)) !important;
            width: 100vw !important;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
            }

            .youtube-sidebar.collapsed ~ .main-content {
                margin-left: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
            }

            /* Ensure sidebar is never collapsed on mobile when open */
            .youtube-sidebar.mobile-open.collapsed {
                width: 280px;
            }
        }

        /* Extra small mobile devices */
        @media (max-width: 480px) {
            .youtube-sidebar {
                width: 260px;
            }

            .sidebar-header {
                padding: 1rem;
            }

            .sidebar-menu-item {
                padding: 0.625rem 1rem;
            }

            .sidebar-brand {
                font-size: 1rem;
            }
        }

        /* Tablet landscape adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .main-content {
                margin-left: var(--sidebar-width-collapsed);
                width: calc(100% - var(--sidebar-width-collapsed));
                max-width: calc(100% - var(--sidebar-width-collapsed));
            }

            .youtube-sidebar {
                width: var(--sidebar-width-collapsed);
            }

            .youtube-sidebar.collapsed {
                width: var(--sidebar-width-collapsed);
            }
        }

        /* Initial sidebar state for lesson view (Desktop only) */
        @media (min-width: 769px) {
            .sidebar-collapsed-initial .youtube-sidebar.collapsed {
                width: var(--sidebar-width-collapsed, 0px) !important;
            }
            .sidebar-collapsed-initial .youtube-sidebar.collapsed ~ .main-content {
                margin-left: var(--sidebar-width-collapsed, 0px) !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .sidebar-collapsed-initial .youtube-sidebar.collapsed ~ .top-header {
                padding-left: var(--sidebar-width-collapsed, 0px) !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="{{ request()->routeIs('dashboard.lesson.view') ? 'sidebar-collapsed-initial' : '' }}">
    @include('partials._upgrade_modal')
    <div class="main-container">
        @include('components.dashboard-sidebar')

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        @include('components.dashboard-header')
        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
        });

        function initializeSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const youtubeSidebar = document.getElementById('youtubeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content');

            const EXPANDED = '240px';
            const COLLAPSED = '0px';

            function applyCollapsed(collapsed) {
                if (collapsed) {
                    youtubeSidebar.classList.add('collapsed');
                    youtubeSidebar.style.width = COLLAPSED;
                    if (mainContent) {
                        mainContent.style.marginLeft = COLLAPSED;
                        mainContent.style.width = '100%';
                        mainContent.style.maxWidth = '100%';
                    }
                } else {
                    youtubeSidebar.classList.remove('collapsed');
                    youtubeSidebar.style.width = EXPANDED;
                    if (mainContent) {
                        mainContent.style.marginLeft = EXPANDED;
                        mainContent.style.width = `calc(100% - ${EXPANDED})`;
                        mainContent.style.maxWidth = `calc(100% - ${EXPANDED})`;
                    }
                }
            }


            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        // Mobile: slide in/out
                        const isOpen = youtubeSidebar.classList.toggle('mobile-open');
                        sidebarOverlay.classList.toggle('active', isOpen);
                        document.body.style.overflow = isOpen ? 'hidden' : '';
                    } else {
                        // Desktop: collapse/expand
                        const isCollapsed = youtubeSidebar.classList.contains('collapsed');
                        applyCollapsed(!isCollapsed);
                    }
                });
            }

            // Close mobile sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Close sidebar with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && youtubeSidebar.classList.contains('mobile-open')) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
    </script>

    @stack('scripts')
    @include('cookie-consent-banner')
</body>

</html>