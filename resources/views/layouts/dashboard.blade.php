<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ShoutOutGh') }} - Dashboard</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #b91c1c;
            --secondary-blue: #2677B8;
            --white: #ffffff;
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        
        /* Add this new style for the sidebar toggle */
        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .sidebar-toggle:hover {
            background-color: var(--gray-100);
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            padding: 1.5rem 0;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar.sidebar-open {
            transform: translateX(0);
        }


        @media (min-width: 769px) {
            .sidebar.sidebar-open ~ .main-content {
                padding-left: 280px;
            }

            .sidebar {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 280px;
            }
        }
        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 2rem;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--primary-red);
        }

        .sidebar-logo img {
            height: 43px;
            width: auto;
        }

        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-red);
        }

        .sidebar-nav {
            padding: 0 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .nav-link.active {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-weight: 500;
            border: 1px solid var(--gray-200);
        }

        .logout-link:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
        }

        /* Top Header - Minimal with just notifications and user profile */
        .top-header {
            background-color: var(--white);
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-icon {
            width: 24px;
            height: 24px;
            color: var(--gray-600);
            cursor: pointer;
        }

        .header-divider {
            width: 1px;
            height: 24px;
            background-color: var(--gray-300);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Search/Filter Bar */
        .filter-bar {
            background-color: var(--white);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
            display: flex;
        }

        .search-input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
            padding-right: 3.5rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .search-button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 2.5rem;
            background-color: var(--primary-red);
            border: none;
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .search-button:hover {
            background-color: #c41e2a;
        }

        .search-icon {
            color: white; /* Make icon white */
            stroke: currentColor; /* Ensure stroke uses text color */
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            cursor: pointer;
            min-width: 120px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-red);
        }

        .filter-button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-button.question {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .filter-button.quiz {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .filter-button:hover {
            opacity: 0.9;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .hero-background video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 400;
            color: var(--white);
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.5rem;
            color: var(--white);
            margin-top: 0.5rem;
            opacity: 0.9;
        }

        .hero-view-button {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hero-view-button:hover {
            background-color: var(--primary-red-hover);
        }

        /* Content Section */
        .content-section {
            padding: 2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .lesson-card {
            background-color: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .lesson-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .lesson-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
        }

        .lesson-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .lesson-duration {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            background-color: rgba(0, 0, 0, 0.8);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .lesson-card:hover .play-overlay {
            opacity: 1;
        }

        .play-button {
            width: 60px;
            height: 60px;
            background-color: var(--primary-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .lesson-info {
            padding: 1.25rem;
        }

        .lesson-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lesson-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .lesson-subject {
            color: var(--secondary-blue);
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1000;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar.sidebar-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-header {
                padding: 1rem;
            }
            
            .filter-bar {
                padding: 1rem;
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1.25rem;
            }
            
            .content-section {
                padding: 1rem;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                    </svg>
                    <span class="sidebar-brand">DigiLearn</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
        <!-- Top Header - Minimal with just notifications and user profile -->
        <header class="top-header">
            <div class="sidebar-toggle">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </div>

            <div class="header-right">
                <!-- Notification Dropdown -->
                <div class="notification-dropdown">
                    <button class="notification-btn" id="notificationToggle">
                        <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 7v5H9v-5H4V3h11v4z"/>
                        </svg>
                        <span class="notification-badge" id="notificationBadge">0</span>
                    </button>

                    <div class="notification-menu" id="notificationMenu">
                        <div class="notification-header">
                            <h3 class="notification-title">Notifications</h3>
                            <button class="notification-mark-all" id="markAllReadBtn">Mark all read</button>
                        </div>

                        <div class="notification-body" id="notificationBody">
                            <div class="notification-empty" id="notificationEmpty">
                                <div class="notification-empty-icon">🔔</div>
                                <div class="notification-empty-text">No notifications yet</div>
                            </div>
                        </div>

                        <div class="notification-footer">
                            <button class="notification-view-all" onclick="window.location.href='/profile'">
                                View all notifications
                            </button>
                        </div>
                    </div>
                </div>

                <div class="header-divider"></div>

                <div class="user-avatar">
                    <span>{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
            </div>
        </header>
                <div class="nav-item">
                    <a href="{{ route('dashboard.main') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
                        </svg>
                        Home
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('dashboard.lessons') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Lessons
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('dashboard.notes') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Notes
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('dashboard.projects') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Projects
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="{{ route('dashboard.notifications') }}" class="nav-link {{ request()->routeIs('dashboard.notifications') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notifications
                    </a>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('dashboard.personalized') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Personalized
                    </a>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('dashboard.shop') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Shop
                    </a>
                </div>
                
                <div class="nav-item" style="margin-top: 2rem;">
                    <a href="#" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Help & Information
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-link" style="border: none; background: none; width: 100%;">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');

            // Function to handle sidebar toggle
            function toggleSidebar() {
                sidebar.classList.toggle('sidebar-open');
            }

            // Add event listener to all toggle buttons
            document.addEventListener('click', function(event) {
                if (event.target.closest('.sidebar-toggle')) {
                    event.stopPropagation();
                    toggleSidebar();
                }
                
                // Close sidebar when clicking outside on mobile
                if (window.innerWidth < 769 && sidebar.classList.contains('sidebar-open')) {
                    if (!sidebar.contains(event.target) && !event.target.closest('.sidebar-toggle')) {
                        sidebar.classList.remove('sidebar-open');
                    }
                }
            });
        });
    </script>
<script src="/js/avatar-updater.js"></script>
</body>
</html>