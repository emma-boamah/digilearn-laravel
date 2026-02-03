<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --sidebar-width-expanded: 240px;
            --sidebar-width-collapsed: 72px;
            --safe-area-inset-top: env(safe-area-inset-top);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--gray-50); color: var(--gray-900); line-height: 1.6; overflow-x: hidden; }
        .main-container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: var(--sidebar-width-expanded); transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); padding-top: calc(60px + var(--safe-area-inset-top)); }
        .youtube-sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-width-collapsed); }
        .content-wrapper { padding: 2rem; max-width: 900px; margin: 0 auto; }
        .page-title { font-size: 1.875rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; }
        .page-subtitle { color: var(--gray-600); margin-bottom: 2rem; }

        /* Sidebar and Header styles from digilearn.blade.php */
        .youtube-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--sidebar-width-expanded) !important;
            height: 100vh !important;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            z-index: 2000;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            padding-top: var(--safe-area-inset-top);
        }
        .youtube-sidebar.collapsed { width: var(--sidebar-width-collapsed) !important; }
        .top-header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            padding-left: var(--sidebar-width-expanded);
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border-bottom: 1px solid rgba(229, 231, 235, 0.6);
            z-index: 999 !important;
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: calc(60px + var(--safe-area-inset-top));
            padding-top: calc(0.75rem + var(--safe-area-inset-top));
        }
        .youtube-sidebar.collapsed ~ .top-header { padding-left: var(--sidebar-width-collapsed) !important; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .content-wrapper { padding: 1.5rem 1rem; }
            .page-title { font-size: 1.5rem; }
            .youtube-sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }
            .top-header {
                padding-left: 0 !important;
            }
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.6);
                z-index: 1999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }
            body.sidebar-open {
                overflow: hidden;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-container">
        @include('components.dashboard-sidebar')
        @include('components.dashboard-header')

        <main class="main-content">
            <div class="content-wrapper">
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Manage your application preferences and notifications.</p>

                @include('components.grade-notification-settings')

                {{-- You can add other settings sections here in the future --}}
            </div>
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const youtubeSidebar = document.getElementById('youtubeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    youtubeSidebar.classList.toggle('mobile-open');
                    if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
                    body.classList.toggle('sidebar-open');
                } else {
                    youtubeSidebar.classList.toggle('collapsed');
                }
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    youtubeSidebar.classList.remove('mobile-open');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && youtubeSidebar.classList.contains('mobile-open')) {
                    toggleSidebar();
                }
            });
        });
    </script>
</body>
</html>