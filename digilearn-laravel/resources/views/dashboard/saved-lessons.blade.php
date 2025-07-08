<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saved Lessons - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
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
        }

        /* Main Layout Container */
        .main-container {
            display: flex;
            min-height: 100vh;
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
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            margin-bottom: 0.5rem;
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

        .youtube-sidebar.collapsed .sidebar-menu-item {
            padding: 0.75rem;
            justify-content: center;
            gap: 0;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border-left: none;
        }

        .sidebar-menu-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
            border-left-color: var(--gray-300);
        }

        .youtube-sidebar.collapsed .sidebar-menu-item:hover {
            border-left-color: transparent;
        }

        .sidebar-menu-item.active {
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            border-left-color: var(--primary-red);
            font-weight: 600;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item.active {
            border-left-color: transparent;
            background-color: var(--primary-red);
            color: var(--white);
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

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width-expanded);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .youtube-sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed);
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background-color: var(--gray-100);
        }

        .notification-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-600);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        /* Page Header */
        .page-header {
            padding: 2rem 1rem 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            font-weight: 500;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
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
            background-color: var(--primary-red-hover);
        }

        .search-icon {
            color: white;
            stroke: currentColor;
        }

        .filter-dropdown {
            position: relative;
            min-width: 120px;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--primary-red);
            font-size: 0.875rem;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .dropdown-toggle:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .dropdown-chevron {
            width: 16px;
            height: 16px;
            color: var(--primary-red);
            transition: transform 0.2s ease;
        }

        /* Content Section */
        .content-section {
            padding: 2rem 1rem;
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
            position: relative;
        }

        .lesson-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .lesson-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
        }

        .lesson-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .lesson-fallback-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
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

        .saved-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background-color: var(--primary-red);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.3);
            opacity: 1;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .lesson-card.playing .play-overlay {
            opacity: 0;
        }

        .lesson-card:not(.playing):hover .play-overlay {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .play-button {
            width: 60px;
            height: 60px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-red);
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .lesson-card:hover .play-button {
            background-color: var(--primary-red);
            color: var(--white);
            transform: scale(1.1);
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
        }

        .lesson-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .lesson-subject {
            color: var(--secondary-blue);
            font-weight: 500;
        }

        .lesson-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: space-between;
            align-items: center;
        }

        .saved-date {
            font-size: 0.75rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        .unsave-btn {
            background: none;
            border: 1px solid var(--gray-300);
            color: var(--gray-600);
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .unsave-btn:hover {
            border-color: var(--primary-red);
            color: var(--primary-red);
            background-color: rgba(225, 30, 45, 0.05);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            color: var(--gray-300);
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-state-description {
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .browse-lessons-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            background-color: var(--primary-red);
            color: var(--white);
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .browse-lessons-btn:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .youtube-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .youtube-sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }

            .top-header {
                padding: 0.75rem 0.5rem;
            }

            .filter-bar {
                padding: 1rem 0.5rem;
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .search-box {
                min-width: auto;
                max-width: none;
            }
            
            .content-section {
                padding: 1rem 0.5rem;
            }

            .page-header {
                padding: 1.5rem 0.5rem 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
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
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- YouTube-style Sidebar -->
        <aside class="youtube-sidebar" id="youtubeSidebar">
            <div class="sidebar-header">
                <button class="sidebar-toggle-btn" id="sidebarToggle">
                    <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="sidebar-logo">
                    <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                </div>
            </div>
            
            <div class="sidebar-content">
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Main</div>
                    <a href="{{ route('dashboard.main') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2M8 5a2 2 0 000 4h8a2 2 0 000-4M8 5v0"/>
                        </svg>
                        <span class="sidebar-menu-text">Dashboard</span>
                        <div class="tooltip">Dashboard</div>
                    </a>
                    <a href="{{ route('dashboard.digilearn') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span class="sidebar-menu-text">Lessons</span>
                        <div class="tooltip">Lessons</div>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Learning</div>
                    <a href="{{ route('dashboard.saved-lessons') }}" class="sidebar-menu-item active">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span class="sidebar-menu-text">Saved Lessons</span>
                        <div class="tooltip">Saved Lessons</div>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Account</div>
                    <a href="{{ route('profile.show') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="sidebar-menu-text">Profile</span>
                        <div class="tooltip">Profile</div>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem;">
                        @csrf
                        <button type="submit" class="sidebar-menu-item" style="border: none; background: none; width: 100%; text-align: left;">
                            <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="sidebar-menu-text">Log out</span>
                            <div class="tooltip">Log out</div>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <div class="header-left">
                    <!-- Empty for now, hamburger is in sidebar -->
                </div>
                
                <div class="header-right">
                    <button class="notification-btn">
                        <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                        </svg>
                    </button>
                    
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Saved Lessons</h1>
                <p class="page-subtitle">Your collection of saved lessons for later viewing</p>
            </div>
            
            <!-- Search/Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search saved lessons..." id="searchInput">
                    <button class="search-button">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
                
                <div class="filter-dropdown">
                    <button class="dropdown-toggle" id="subjectFilter">
                        <span>All Subjects</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <div class="filter-dropdown">
                    <button class="dropdown-toggle" id="levelFilter">
                        <span>All Levels</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content Section with Lessons Grid -->
            <div class="content-section">
                @if($savedLessons->count() > 0)
                    <div class="content-grid" id="lessonsGrid">
                        @foreach($savedLessons as $savedLesson)
                        <div class="lesson-card hover-video-card" 
                             data-lesson-id="{{ $savedLesson->lesson_id }}" 
                             data-subject="{{ $savedLesson->lesson_subject }}"
                             data-level="{{ $savedLesson->selected_level }}"
                             data-title="{{ strtolower($savedLesson->lesson_title) }}">
                            <div class="lesson-thumbnail">
                                <!-- Saved badge -->
                                <div class="saved-badge">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    Saved
                                </div>

                                <!-- Fallback image -->
                                <img 
                                    src="{{ asset($savedLesson->lesson_thumbnail) }}" 
                                    alt="{{ $savedLesson->lesson_title }}" 
                                    class="lesson-fallback-image"
                                    onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Video+Lesson'"
                                >
                                
                                <div class="lesson-duration">{{ $savedLesson->lesson_duration }}</div>
                                
                                <!-- Play overlay -->
                                <div class="play-overlay">
                                    <div class="play-button">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="lesson-info">
                                <h3 class="lesson-title">{{ $savedLesson->lesson_title }}</h3>
                                <div class="lesson-meta">
                                    <span class="lesson-subject">({{ $savedLesson->lesson_subject }})</span>
                                    <span>{{ $savedLesson->lesson_instructor }} | {{ $savedLesson->lesson_year }}</span>
                                </div>
                                <div class="lesson-actions">
                                    <span class="saved-date">Saved {{ $savedLesson->saved_at->diffForHumans() }}</span>
                                    <button class="unsave-btn" data-lesson-id="{{ $savedLesson->lesson_id }}">
                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <h3 class="empty-state-title">No saved lessons yet</h3>
                        <p class="empty-state-description">Start saving lessons you want to watch later. They'll appear here for easy access.</p>
                        <a href="{{ route('dashboard.digilearn') }}" class="browse-lessons-btn">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Browse Lessons
                        </a>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all functionality
            initializeSidebar();
            initializeSearch();
            initializeFilters();
            initializeLessonCards();
            initializeUnsaveButtons();
        });

        // YouTube-style sidebar functionality
        function initializeSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const youtubeSidebar = document.getElementById('youtubeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Toggle sidebar collapse/expand
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    // Mobile behavior - show/hide sidebar
                    youtubeSidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('active');
                    document.body.style.overflow = youtubeSidebar.classList.contains('mobile-open') ? 'hidden' : '';
                } else {
                    // Desktop behavior - collapse/expand sidebar
                    youtubeSidebar.classList.toggle('collapsed');
                }
            });

            // Close mobile sidebar when clicking overlay
            sidebarOverlay.addEventListener('click', function() {
                youtubeSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && youtubeSidebar.classList.contains('mobile-open')) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }

        // Search functionality
        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');
            const lessonsGrid = document.getElementById('lessonsGrid');
            
            if (searchInput && lessonsGrid) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    const lessonCards = lessonsGrid.querySelectorAll('.lesson-card');
                    
                    lessonCards.forEach(card => {
                        const title = card.dataset.title || '';
                        const subject = card.dataset.subject || '';
                        
                        if (title.includes(searchTerm) || subject.toLowerCase().includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        }

        // Filter functionality
        function initializeFilters() {
            const subjectFilter = document.getElementById('subjectFilter');
            const levelFilter = document.getElementById('levelFilter');
            const lessonsGrid = document.getElementById('lessonsGrid');
            
            if (subjectFilter && levelFilter && lessonsGrid) {
                let currentSubjectFilter = 'all';
                let currentLevelFilter = 'all';
                
                function applyFilters() {
                    const lessonCards = lessonsGrid.querySelectorAll('.lesson-card');
                    
                    lessonCards.forEach(card => {
                        const subject = card.dataset.subject || '';
                        const level = card.dataset.level || '';
                        
                        const subjectMatch = currentSubjectFilter === 'all' || subject.toLowerCase() === currentSubjectFilter.toLowerCase();
                        const levelMatch = currentLevelFilter === 'all' || level.toLowerCase() === currentLevelFilter.toLowerCase();
                        
                        if (subjectMatch && levelMatch) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
                
                // Subject filter (simplified for demo)
                subjectFilter.addEventListener('click', function() {
                    // Toggle between all subjects and specific ones
                    const subjects = ['All Subjects', 'Mathematics', 'Science', 'English'];
                    const currentIndex = subjects.indexOf(this.querySelector('span').textContent);
                    const nextIndex = (currentIndex + 1) % subjects.length;
                    
                    this.querySelector('span').textContent = subjects[nextIndex];
                    currentSubjectFilter = subjects[nextIndex] === 'All Subjects' ? 'all' : subjects[nextIndex];
                    applyFilters();
                });
                
                // Level filter (simplified for demo)
                levelFilter.addEventListener('click', function() {
                    const levels = ['All Levels', 'Primary 1', 'Primary 2', 'Primary 3'];
                    const currentIndex = levels.indexOf(this.querySelector('span').textContent);
                    const nextIndex = (currentIndex + 1) % levels.length;
                    
                    this.querySelector('span').textContent = levels[nextIndex];
                    currentLevelFilter = levels[nextIndex] === 'All Levels' ? 'all' : levels[nextIndex].toLowerCase().replace(' ', '-');
                    applyFilters();
                });
            }
        }

        // Lesson card click functionality
        function initializeLessonCards() {
            const lessonCards = document.querySelectorAll('.lesson-card');
            
            lessonCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't navigate if clicking on unsave button
                    if (e.target.closest('.unsave-btn')) {
                        return;
                    }
                    
                    const lessonId = this.dataset.lessonId;
                    
                    // Add visual feedback
                    this.style.opacity = '0.7';
                    this.style.transform = 'scale(0.98)';
                    
                    // Navigate to lesson view
                    setTimeout(() => {
                        window.location.href = `/dashboard/lesson/${lessonId}`;
                    }, 200);
                });
            });
        }

        // Unsave button functionality
        function initializeUnsaveButtons() {
            const unsaveButtons = document.querySelectorAll('.unsave-btn');
            
            unsaveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent card click
                    
                    const lessonId = this.dataset.lessonId;
                    const lessonCard = this.closest('.lesson-card');
                    
                    if (confirm('Are you sure you want to remove this lesson from your saved lessons?')) {
                        // Show loading state
                        this.innerHTML = '<div style="width: 12px; height: 12px; border: 2px solid transparent; border-top: 2px solid currentColor; border-radius: 50%; animation: spin 1s linear infinite;"></div> Removing...';
                        this.disabled = true;
                        
                        // Make API call to unsave lesson
                        fetch(`/dashboard/lesson/${lessonId}/unsave`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Animate card removal
                                lessonCard.style.transition = 'all 0.3s ease';
                                lessonCard.style.opacity = '0';
                                lessonCard.style.transform = 'scale(0.8)';
                                
                                setTimeout(() => {
                                    lessonCard.remove();
                                    
                                    // Check if no lessons left
                                    const remainingCards = document.querySelectorAll('.lesson-card');
                                    if (remainingCards.length === 0) {
                                        location.reload(); // Reload to show empty state
                                    }
                                }, 300);
                                
                                // Show success message
                                showNotification('Lesson removed from saved lessons!', 'success');
                            } else {
                                // Reset button on error
                                this.innerHTML = '<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg> Remove';
                                this.disabled = false;
                                showNotification(data.message || 'Failed to remove lesson', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.innerHTML = '<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg> Remove';
                            this.disabled = false;
                            showNotification('Failed to remove lesson. Please try again.', 'error');
                        });
                    }
                });
            });
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background-color: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                border-radius: 0.5rem;
                font-weight: 500;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            @keyframes slideIn {
                from { opacity: 0; transform: translateX(100%); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
