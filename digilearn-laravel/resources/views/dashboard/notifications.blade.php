<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications - DigiLearn</title>
    <link rel="icon" type="image/x-icon" href="{{ secure_asset('images/favicon.ico') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* DigiLearn Brand Colors */
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --white: #ffffff;
            
            /* Light Theme Colors (Default) */
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
            
            /* Light Theme Variables */
            --background: var(--white);
            --surface: var(--white);
            --surface-elevated: var(--gray-50);
            --surface-hover: var(--gray-100);
            --border: var(--gray-200);
            --border-hover: var(--gray-300);
            
            /* Text Colors */
            --foreground: var(--gray-900);
            --foreground-muted: var(--gray-600);
            --foreground-subtle: var(--gray-500);
            
            /* Accent Colors */
            --accent-blue: var(--secondary-blue);
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-red: var(--primary-red);
            --accent-purple: #8b5cf6;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px 0 rgb(0 0 0 / 0.06);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
            /* Layout */
            --sidebar-width-expanded: 280px;
            --sidebar-width-collapsed: 80px;
            --header-height: 64px;
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
        }

        /* Dark theme variables */
        [data-theme="dark"] {
            /* Dark Theme Colors */
            --background: #0a0a0a;
            --surface: #111111;
            --surface-elevated: #1a1a1a;
            --surface-hover: #222222;
            --border: #2a2a2a;
            --border-hover: #3a3a3a;
            
            /* Dark Text Colors */
            --foreground: #ffffff;
            --foreground-muted: #a1a1aa;
            --foreground-subtle: #71717a;
            
            /* Dark Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4), 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background);
            color: var(--foreground);
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
            background-color: var(--background);
        }

        /* Modern Sidebar Styling */
        .youtube-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width-expanded);
            height: 100vh;
            background-color: var(--surface);
            border-right: 1px solid var(--border);
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
            border-bottom: 1px solid var(--border);
            height: var(--header-height);
            min-height: var(--header-height);
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-right: 1rem;
            color: var(--foreground-muted);
        }

        .sidebar-toggle-btn:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
        }

        .hamburger-icon {
            width: 20px;
            height: 20px;
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
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
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
            color: var(--foreground-subtle);
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
            color: var(--foreground-muted);
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
            border-radius: var(--radius);
            border-left: none;
        }

        .sidebar-menu-item:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
            border-left-color: var(--border-hover);
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

        /* Modern Main Content Area */
        .main-content {
            flex: 1;
            width: calc(100vw - var(--sidebar-width-expanded));
            max-width: calc(100vw - var(--sidebar-width-expanded));
            margin-left: var(--sidebar-width-expanded);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            overflow-x: hidden;
            background-color: var(--background);
        }

        .youtube-sidebar.collapsed ~ .main-content {
            width: calc(100vw - var(--sidebar-width-collapsed));
            max-width: calc(100vw - var(--sidebar-width-collapsed));
            margin-left: var(--sidebar-width-collapsed);
        }

        /* Modern Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(8px);
            height: var(--header-height);
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

        /* Added dark mode toggle button */
        .theme-toggle-btn {
            background: none;
            border: 1px solid var(--border);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius);
            transition: all 0.2s ease;
            color: var(--foreground-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }

        .theme-toggle-btn:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
            border-color: var(--border-hover);
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: var(--radius);
            transition: all 0.2s ease;
            color: var(--foreground-muted);
        }

        .notification-btn:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
        }

        .notification-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background-color: var(--accent-red);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
            min-width: 1.25rem;
            text-align: center;
        }

        /* Modern Notifications Content */
        .notifications-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .notifications-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .notifications-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--secondary-blue);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-blue-hover);
        }

        .btn-secondary {
            background-color: var(--surface-elevated);
            color: var(--foreground-muted);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
            border-color: var(--border-hover);
        }

        .btn-danger {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--primary-red-hover);
        }

        /* Modern Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .stat-card:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-card-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card-icon {
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        .stat-card-change {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-card-change.positive {
            color: var(--accent-green);
        }

        .stat-card-change.negative {
            color: var(--accent-red);
        }

        /* Modern Filters */
        .filters-section {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground-muted);
        }

        .filter-select {
            background-color: var(--surface-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem;
            color: var(--foreground);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        /* Modern Notifications List */
        .notifications-list {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: var(--surface-hover);
        }

        .notification-item.unread {
            background-color: rgba(38, 119, 184, 0.05);
            border-left: 3px solid var(--secondary-blue);
        }

        .notification-item.unread:hover {
            background-color: rgba(38, 119, 184, 0.08);
        }

        .notification-icon-wrapper {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-message {
            font-size: 0.875rem;
            color: var(--foreground-muted);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--foreground-subtle);
        }

        .notification-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .notification-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius);
            transition: all 0.2s ease;
            color: var(--foreground-subtle);
        }

        .notification-action-btn:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-new {
            background-color: var(--secondary-blue);
            color: white;
        }

        .badge-success {
            background-color: var(--accent-green);
            color: white;
        }

        .badge-warning {
            background-color: var(--accent-orange);
            color: white;
        }

        .badge-danger {
            background-color: var(--primary-red);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--foreground-muted);
        }

        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            background-color: var(--surface-elevated);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--foreground-subtle);
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        .empty-state-message {
            font-size: 0.875rem;
            color: var(--foreground-muted);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .youtube-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
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

            .notifications-container {
                padding: 1rem;
            }

            .notifications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .notifications-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .notification-item {
                padding: 1rem;
            }

            .notification-actions {
                flex-direction: column;
            }
        }

        /* User Avatar Component */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            border: 2px solid var(--border);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            box-shadow: var(--shadow-md);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Modern Sidebar with DigiLearn Navigation -->
        <aside class="youtube-sidebar" id="youtubeSidebar">
            <div class="sidebar-header">
                <button class="sidebar-toggle-btn" id="sidebarToggle">
                    <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="sidebar-logo">
                    <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="DigiLearn">
                </div>
            </div>
            
            <div class="sidebar-content">
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Main</div>
                    <a href="{{ route('dashboard.main') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                    <a href="{{ route('dashboard.digilearn') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span class="sidebar-menu-text">Lessons</span>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="sidebar-menu-text">Quiz</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Learning</div>
                    <a href="/dashboard/my-progress" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="sidebar-menu-text">My Progress</span>
                    </a>
                    <a href="/dashboard/saved-lessons" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span class="sidebar-menu-text">Saved Lessons</span>
                    </a>
                    <a href="{{ route('dashboard.join-class') }}" class="sidebar-menu-item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="5" width="18" height="12" rx="2" stroke-width="2"/>
                            <circle cx="8" cy="15" r="1.5" stroke-width="2"/>
                            <circle cx="12" cy="15" r="1.5" stroke-width="2"/>
                            <circle cx="16" cy="15" r="1.5" stroke-width="2"/>
                        </svg>
                        <span class="sidebar-menu-text">Join Your Class</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Account</div>
                    <a href="{{ route('dashboard.notifications') }}" class="sidebar-menu-item active">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="sidebar-menu-text">Notifications</span>
                    </a>
                    <a href="/profile" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="sidebar-menu-text">Profile</span>
                    </a>
                    <a href="/settings" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="sidebar-menu-text">Settings</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem;">
                        @csrf
                        <button type="submit" class="sidebar-menu-item" style="border: none; background: none; width: 100%; text-align: left;">
                            <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="sidebar-menu-text">Log out</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Modern Header -->
            <div class="top-header">
                <div class="header-left">
                    <!-- <button class="sidebar-toggle-btn" id="sidebarToggleMain">
                        <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button> -->
                </div>
                
                <div class="header-right">
                    <!-- Added dark mode toggle button -->
                    <button class="theme-toggle-btn" id="toggledarkmodebutton" title="Toggle Dark Mode">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    
                    <button class="notification-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                    </button>
                    
                    <x-user-avatar :user="auth()->user()" :size="36" class="border-2 border-white"/>
                </div>
            </div>

            <!-- Modern Notifications Content -->
            <div class="notifications-container">
                <div class="notifications-header">
                    <h1 class="notifications-title">Notifications</h1>
                    <div class="notifications-actions">
                        <button type="button" class="btn btn-secondary" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i>
                            Mark All Read
                        </button>
                        <button type="button" class="btn btn-danger" onclick="clearAllNotifications()">
                            <i class="fas fa-trash"></i>
                            Clear All
                        </button>
                    </div>
                </div>

                <!-- Modern Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Total Notifications</span>
                            <div class="stat-card-icon" style="background-color: rgba(59, 130, 246, 0.1); color: var(--accent-blue);">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">{{ isset($notifications) ? $notifications->total() : 0 }}</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 12% from last week
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Unread</span>
                            <div class="stat-card-icon" style="background-color: rgba(239, 68, 68, 0.1); color: var(--accent-red);">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">{{ auth()->user()->unreadNotifications->count() }}</div>
                        <div class="stat-card-change negative">
                            <i class="fas fa-arrow-down"></i> 5% from yesterday
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">This Week</span>
                            <div class="stat-card-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--accent-green);">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">{{ auth()->user()->notifications()->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 8% increase
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Response Rate</span>
                            <div class="stat-card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--accent-purple);">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">94%</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 2% improvement
                        </div>
                    </div>
                </div>

                <!-- Modern Filters -->
                <div class="filters-section">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select class="filter-select" id="notificationFilter">
                                <option value="all">All Notifications</option>
                                <option value="unread">Unread Only</option>
                                <option value="read">Read Only</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Type</label>
                            <select class="filter-select" id="typeFilter">
                                <option value="all">All Types</option>
                                @foreach(\App\Models\NotificationType::active()->get() ?? [] as $type)
                                <option value="{{ $type->slug }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Date Range</label>
                            <select class="filter-select" id="dateFilter">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modern Notifications List -->
                <div class="notifications-list" id="notificationsContainer">
                    @if(isset($notifications) && $notifications->count() > 0)
                        @foreach($notifications as $notification)
                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                             data-id="{{ $notification->id }}"
                             data-type="{{ $notification->notificationType?->slug ?? 'general' }}">
                            
                            <div class="notification-icon-wrapper" 
                                 style="background-color: {{ $notification->notificationType?->color ?? 'rgba(59, 130, 246, 0.1)' }};">
                                <i class="{{ $notification->notificationType?->icon ?? 'fas fa-bell' }}" 
                                   style="color: {{ $notification->notificationType?->color ?? 'var(--accent-blue)' }};"></i>
                            </div>

                            <div class="notification-content">
                                <div class="notification-title">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                    @if(!$notification->read_at)
                                        <span class="badge badge-new">New</span>
                                    @endif
                                </div>
                                <div class="notification-message">
                                    {{ Str::limit($notification->data['message'] ?? '', 150) }}
                                </div>
                                <div class="notification-meta">
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if($notification->notificationType)
                                        <span>
                                            <i class="fas fa-tag"></i>
                                            {{ $notification->notificationType->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="notification-actions">
                                @if($notification->data['url'] ?? false)
                                    <a href="{{ $notification->data['url'] }}" 
                                       class="notification-action-btn" 
                                       target="_blank" 
                                       title="Open Link">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                                @if(!$notification->read_at)
                                    <button type="button" 
                                            class="notification-action-btn" 
                                            onclick="markAsRead({{ $notification->id }})"
                                            title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                <button type="button" 
                                        class="notification-action-btn" 
                                        onclick="deleteNotification({{ $notification->id }})"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        @if($notifications->hasPages())
                        <div style="padding: 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: center;">
                            {{ $notifications->links() }}
                        </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-bell fa-2x"></i>
                            </div>
                            <h3 class="empty-state-title">No notifications yet</h3>
                            <p class="empty-state-message">You'll receive notifications about important updates and activities here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        $(document).ready(function() {
            const toggleButton = $('#toggledarkmodebutton');
            const themeIcon = $('#themeIcon');
            const body = $('body');
            
            // Check for saved theme preference or default to light mode
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
            
            // Toggle theme on button click
            toggleButton.click(function() {
                const currentTheme = body.attr('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                setTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });
            
            function setTheme(theme) {
                if (theme === 'dark') {
                    body.attr('data-theme', 'dark');
                    themeIcon.removeClass('fa-moon').addClass('fa-sun');
                    toggleButton.attr('title', 'Switch to Light Mode');
                } else {
                    body.removeAttr('data-theme');
                    themeIcon.removeClass('fa-sun').addClass('fa-moon');
                    toggleButton.attr('title', 'Switch to Dark Mode');
                }
            }

            // Sidebar toggle functionality
            $('#sidebarToggle, #sidebarToggleMain').click(function() {
                $('#youtubeSidebar').toggleClass('collapsed');
            });

            // Filter notifications
            $('#notificationFilter, #typeFilter, #dateFilter').change(function() {
                filterNotifications();
            });

            // Mark notification as read when clicked
            $('.notification-item').click(function(e) {
                if (!$(e.target).closest('.notification-actions').length) {
                    const notificationId = $(this).data('id');
                    if ($(this).hasClass('unread')) {
                        markAsRead(notificationId);
                    }
                }
            });

            // Mobile sidebar overlay
            $(document).click(function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('.youtube-sidebar, .sidebar-toggle-btn').length) {
                        $('#youtubeSidebar').removeClass('mobile-open');
                    }
                }
            });

            // Mobile sidebar toggle
            $('#sidebarToggle, #sidebarToggleMain').click(function() {
                if ($(window).width() <= 768) {
                    $('#youtubeSidebar').toggleClass('mobile-open');
                }
            });
        });

        function filterNotifications() {
            const statusFilter = $('#notificationFilter').val();
            const typeFilter = $('#typeFilter').val();
            const dateFilter = $('#dateFilter').val();

            $('.notification-item').each(function() {
                const $item = $(this);
                const isUnread = $item.hasClass('unread');
                const itemType = $item.data('type');
                
                let showItem = true;

                // Status filter
                if (statusFilter === 'unread' && !isUnread) {
                    showItem = false;
                } else if (statusFilter === 'read' && isUnread) {
                    showItem = false;
                }

                // Type filter
                if (typeFilter !== 'all' && itemType !== typeFilter) {
                    showItem = false;
                }

                // Date filter would require additional data attributes
                // Implementation depends on your specific needs

                $item.toggle(showItem);
            });
        }

        function markAsRead(notificationId) {
            $.ajax({
                url: `/notifications/${notificationId}/read`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $(`.notification-item[data-id="${notificationId}"]`)
                            .removeClass('unread')
                            .find('.badge-new')
                            .remove();
                        updateNotificationCount();
                        toastr.success('Notification marked as read');
                    }
                },
                error: function() {
                    toastr.error('Failed to mark notification as read');
                }
            });
        }

        function markAllAsRead() {
            if (!confirm('Mark all notifications as read?')) return;

            $.ajax({
                url: '/notifications/mark-all-read',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $('.notification-item.unread')
                            .removeClass('unread')
                            .find('.badge-new')
                            .remove();
                        updateNotificationCount();
                        toastr.success('All notifications marked as read');
                    }
                },
                error: function() {
                    toastr.error('Failed to mark all notifications as read');
                }
            });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) return;

            $.ajax({
                url: `/notifications/${notificationId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $(`.notification-item[data-id="${notificationId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        updateNotificationCount();
                        toastr.success('Notification deleted');
                    }
                },
                error: function() {
                    toastr.error('Failed to delete notification');
                }
            });
        }

        function clearAllNotifications() {
            if (!confirm('Clear all notifications? This action cannot be undone.')) return;

            $.ajax({
                url: '/notifications/clear-all',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $('.notification-item').fadeOut(300, function() {
                            $('#notificationsContainer').html(`
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-bell fa-2x"></i>
                                    </div>
                                    <h3 class="empty-state-title">No notifications yet</h3>
                                    <p class="empty-state-message">You'll receive notifications about important updates and activities here.</p>
                                </div>
                            `);
                        });
                        updateNotificationCount();
                        toastr.success('All notifications cleared');
                    }
                },
                error: function() {
                    toastr.error('Failed to clear all notifications');
                }
            });
        }

        function updateNotificationCount() {
            const unreadCount = $('.notification-item.unread').length;
            $('.notification-badge').text(unreadCount > 0 ? unreadCount : '0');
            if (unreadCount === 0) {
                $('.notification-badge').hide();
            }
        }

        // Configure toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
            "extendedTimeOut": "1000"
        };
    </script>
</body>
</html>
