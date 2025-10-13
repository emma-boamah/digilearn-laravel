<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $program['name'] ?? 'Program' }} Courses - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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
            scrollbar-width: none;
        }

        .sidebar-content::-webkit-scrollbar {
            display: none;
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
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
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
            width: calc(100vw - var(--sidebar-width-expanded));
            max-width: calc(100vw - var(--sidebar-width-expanded));
            margin-left: var(--sidebar-width-expanded);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .youtube-sidebar.collapsed ~ .main-content {
            width: calc(100vw - var(--sidebar-width-collapsed));
            max-width: calc(100vw - var(--sidebar-width-collapsed));
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

        /* Breadcrumb Navigation */
        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.875rem;
        }

        .breadcrumb-item {
            color: var(--gray-500);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb-item:hover {
            color: var(--primary-red);
        }

        .breadcrumb-item.active {
            color: var(--gray-900);
            font-weight: 600;
        }

        .breadcrumb-separator {
            color: var(--gray-400);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 250px;
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 400;
            color: var(--white);
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.25rem;
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
            padding: 2rem 1rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            background-color: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .course-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
        }

        .course-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .course-fallback-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .course-duration {
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
            opacity: 1;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .course-card.playing .play-overlay {
            opacity: 0;
        }

        .course-card:not(.playing):hover .play-overlay {
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

        .course-card:hover .play-button {
            background-color: var(--primary-red);
            color: var(--white);
            transform: scale(1.1);
        }

        .course-level-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        .course-card:hover .course-level-badge {
            background-color: var(--primary-red);
            transform: scale(1.05);
            transition: all 0.2s ease;
        }

        .video-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .course-card.video-error .course-video {
            display: none;
        }

        .course-card.video-error .course-fallback-image {
            display: block;
        }

        .course-info {
            padding: 1.25rem;
        }

        .course-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .course-subject {
            color: var(--secondary-blue);
            font-weight: 500;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .course-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
            justify-content: center;
        }

        .course-action-btn.primary {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .course-action-btn.primary:hover {
            background-color: var(--primary-red-hover);
        }

        .course-action-btn.secondary {
            background-color: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .course-action-btn.secondary:hover {
            background-color: var(--gray-200);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                overflow: hidden;
            }
            
            .youtube-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
                width: 280px;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100vw;
                max-width: 100vw;
            }

            .youtube-sidebar.collapsed ~ .main-content {
                margin-left: 0;
                width: 100vw;
                max-width: 100vw;
            }

            .top-header {
                position: sticky;
                top: 0;
                z-index: 1000;
                padding: 0.5rem 1rem;
            }

            .content-section {
                padding: 1rem 0.5rem;
            }

            .hero-section {
                height: 180px;
            }

            .hero-content h1 {
                font-size: 1.75rem;
                margin-bottom: 8px;
            }
            
            .hero-content p {
                font-size: 1rem;
                margin-bottom: 16px;
            }

            .hero-view-button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .header-left {
                gap: 0.5rem;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.6);
                z-index: 999;
                opacity: 0;
                transition: all 0.3s ease;
            }

            .sidebar-overlay.active {
                opacity: 1;
                display: block;
            }

            .body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-logo img {
                height: 28px;
            }

            .hero-overlay {
                padding: 0 20px;
                flex-direction: column;
                justify-content: center;
                text-align: center;
            }

            .course-card {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
        }

        @media (min-width: 1024px) {
            .content-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- YouTube-style Sidebar -->
        <aside class="youtube-sidebar" id="youtubeSidebar">
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
                    <a href="{{ route('dashboard.digilearn') }}" class="sidebar-menu-item active">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span class="sidebar-menu-text">Lessons</span>
                        <div class="tooltip">Lessons</div>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="sidebar-menu-text">Quiz</span>
                        <div class="tooltip">Quiz</div>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Learning</div>
                    <a href="/dashboard/my-progress" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="sidebar-menu-text">My Progress</span>
                        <div class="tooltip">My Progress</div>
                    </a>
                    <a href="/dashboard/saved-lessons" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span class="sidebar-menu-text">Saved Lessons</span>
                        <div class="tooltip">Saved Lessons</div>
                    </a>
                    <a href="/dashboard/my-projects" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="sidebar-menu-text">My Projects</span>
                        <div class="tooltip">My Projects</div>
                    </a>
                    <a href="{{ route('dashboard.notes') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="sidebar-menu-text">My Notes</span>
                        <div class="tooltip">My Notes</div>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Account</div>
                    <a href="/profile" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="sidebar-menu-text">Profile</span>
                        <div class="tooltip">Profile</div>
                    </a>
                    <a href="/settings" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="sidebar-menu-text">Settings</span>
                        <div class="tooltip">Settings</div>
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
                    <button class="sidebar-toggle-btn" id="sidebarToggle">
                        <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="sidebar-logo">
                        <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                    </div>
                </div>
                
                <div class="header-right">
                    <button class="notification-btn">
                        <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v0.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                    
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>

            <!-- Breadcrumb Navigation -->
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard.university.years') }}" class="breadcrumb-item">University</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('dashboard.university.programs', $yearId) }}" class="breadcrumb-item">{{ $yearId }}</a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-item active">{{ $program['name'] ?? 'Program' }}</span>
            </div>
            
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="hero-background"></div>
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>{{ $program['name'] ?? 'Program Courses' }}</h1>
                        <p>Explore courses and start learning</p>
                    </div>
                    <button class="hero-view-button">View All</button>
                </div>
            </div>
            
            <!-- Content Section with Courses Grid -->
            <div class="content-section">
                <div class="content-grid">
                    @forelse($courses ?? [] as $course)
                    <div class="course-card hover-video-card" data-course-id="{{ $course['id'] }}">
                        <div class="course-thumbnail">
                            <!-- Video element for hover-to-play functionality -->
                            <video 
                                id="course-video-{{ $course['id'] }}" 
                                class="course-video" 
                                muted 
                                loop 
                                preload="metadata"
                                poster="{{ secure_asset($course['thumbnail']) }}"
                            >
                                <source src="{{ secure_asset($course['video_url']) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            
                            <!-- Fallback image if video fails to load -->
                            <img 
                                src="{{ secure_asset($course['thumbnail']) }}" 
                                alt="{{ $course['title'] }}" 
                                class="course-fallback-image"
                                style="display: none;"
                                onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Course+Video'"
                            >
                            
                            <div class="course-duration">{{ $course['duration'] }}</div>
                            
                            <!-- Level badge -->
                            <div class="course-level-badge">{{ $course['level_display'] ?? 'Course' }}</div>
                            
                            <!-- Play overlay that appears on hover -->
                            <div class="play-overlay">
                                <div class="play-button">
                                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">{{ $course['title'] }}</h3>
                            <div class="course-meta">
                                <span class="course-subject">({{ $course['subject'] }})</span>
                                <span>{{ $course['instructor'] }} | {{ $course['year'] }}</span>
                            </div>
                            <div class="course-actions">
                                <a href="{{ route('dashboard.lesson.view', ['lessonId' => $course['id']]) }}" class="course-action-btn primary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Watch
                                </a>
                                <a href="{{ route('dashboard.university.course.lessons', [$yearId, $programId, $course['id']]) }}" class="course-action-btn secondary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    Lessons
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No courses available</h3>
                        <p style="color: var(--gray-500);">Courses for {{ $program['name'] ?? 'this program' }} are coming soon!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all functionality
            initializeSidebar();
            initializeVideoCards();
        });

        // YouTube-style sidebar functionality
        function initializeSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const youtubeSidebar = document.getElementById('youtubeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        // Mobile behavior
                        youtubeSidebar.classList.toggle('mobile-open');
                        sidebarOverlay.classList.toggle('active');
                        body.classList.toggle('sidebar-open');
                    } else {
                        // Desktop behavior
                        youtubeSidebar.classList.toggle('collapsed');
                    }
                });
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
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

        // Video card functionality
        function initializeVideoCards() {
            const videoCards = document.querySelectorAll('.hover-video-card');
            let currentlyPlaying = null;
            
            videoCards.forEach(card => {
                const courseId = card.getAttribute('data-course-id');
                const videoId = `course-video-${courseId}`;
                const video = document.getElementById(videoId);
                const loadingIndicator = card.querySelector('.video-loading');
                
                if (!video) return;
                
                // Handle video loading
                video.addEventListener('loadstart', function() {
                    if (loadingIndicator) loadingIndicator.style.display = 'flex';
                });
                
                video.addEventListener('canplay', function() {
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                });
                
                // Handle video errors
                video.addEventListener('error', function() {
                    console.log('Video failed to load:', videoId);
                    card.classList.add('video-error');
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                });
                
                // Play video on hover
                card.addEventListener('mouseenter', function() {
                    if (currentlyPlaying && currentlyPlaying !== video) {
                        currentlyPlaying.pause();
                        currentlyPlaying.currentTime = 0;
                        currentlyPlaying.parentElement.parentElement.classList.remove('playing');
                    }
                    
                    if (video.paused && !card.classList.contains('video-error')) {
                        if (video.currentTime === video.duration) {
                            video.currentTime = 0;
                        }
                        
                        const playPromise = video.play();
                        
                        if (playPromise !== undefined) {
                            playPromise
                                .then(() => {
                                    card.classList.add('playing');
                                    currentlyPlaying = video;
                                })
                                .catch(error => {
                                    console.log('Autoplay prevented:', error);
                                    card.classList.add('video-error');
                                });
                        }
                    }
                });
                
                // Pause video when mouse leaves
                card.addEventListener('mouseleave', function() {
                    if (!video.paused) {
                        video.pause();
                        video.currentTime = 0;
                        card.classList.remove('playing');
                        if (currentlyPlaying === video) {
                            currentlyPlaying = null;
                        }
                    }
                });
                
                // Handle video end
                video.addEventListener('ended', function() {
                    this.currentTime = 0;
                    card.classList.remove('playing');
                    if (currentlyPlaying === video) {
                        currentlyPlaying = null;
                    }
                });
            });
        }
    </script>
</body>
</html>
