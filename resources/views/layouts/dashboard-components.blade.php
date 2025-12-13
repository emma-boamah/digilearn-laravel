<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'ShoutOutGh') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --white: #ffffff;
            --gray-25: #fcfcfd;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --sidebar-width-expanded: 280px;
            --sidebar-width-collapsed: 72px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-25);
            color: var(--gray-900);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* YouTube-style Sidebar */
        .youtube-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width-expanded);
            height: 100vh;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            z-index: 1000;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease;
            overflow: hidden;
        }

        .youtube-sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
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
            overflow-y: auto;
            height: calc(100vh - 64px);
        }

        .sidebar-section {
            margin-bottom: 1.5rem;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            padding: 0.5rem 1.5rem;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
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
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
            position: relative;
        }

        .sidebar-menu-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
            border-left-color: var(--gray-300);
        }

        .sidebar-menu-item.active {
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            border-left-color: var(--primary-red);
            font-weight: 600;
        }

        .sidebar-menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-menu-text {
            font-size: 0.875rem;
            font-weight: 500;
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

        /* Main Content */
        .main-content {
            position: relative;
            margin-left: var(--sidebar-width-expanded);
            min-height: calc(100vh - 60px);
            background-color: var(--gray-25);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: calc(100% - var(--sidebar-width-expanded));
            max-width: calc(100% - var(--sidebar-width-expanded));
            overflow-x: hidden;
        }

        .youtube-sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed);
            width: calc(100% - var(--sidebar-width-collapsed));
            max-width: calc(100% - var(--sidebar-width-collapsed));
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
    </style>

    @stack('styles')
</head>
<body>
    <div class="main-container">
        @include('components.dashboard-sidebar')

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="main-content">
            @include('components.dashboard-header')

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

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        // Mobile behavior
                        youtubeSidebar.classList.toggle('mobile-open');
                        sidebarOverlay.classList.toggle('active');
                        document.body.style.overflow = youtubeSidebar.classList.contains('mobile-open') ? 'hidden' : '';
                    } else {
                        // Desktop behavior
                        youtubeSidebar.classList.toggle('collapsed');
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
</body>
</html>
