<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile Settings - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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
            --success-green: #10b981;
            --warning-yellow: #f59e0b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            color: var(--gray-600);
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .digilearn-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-red);
            font-weight: 700;
            font-size: 1.125rem;
        }

        .shoutout-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary-blue);
            font-weight: 600;
            font-size: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            color: var(--gray-600);
            position: relative;
        }

        .notification-btn:hover {
            background: var(--gray-100);
        }

        .user-avatar-header {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Main Layout */
        .main-container {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            padding: 2rem 0;
            position: sticky;
            top: 64px;
            height: calc(100vh - 64px);
            overflow-y: auto;
        }

        .profile-summary {
            text-align: center;
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow-lg);
        }

        .profile-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .profile-email {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .profile-location {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .flag-icon {
            width: 16px;
            height: 12px;
            border-radius: 2px;
        }

        /* Navigation Menu */
        .nav-menu {
            padding: 0 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 0.875rem;
        }

        .nav-item:hover {
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .nav-item.active {
            background: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            font-weight: 600;
        }

        .nav-item.danger {
            color: var(--primary-red);
        }

        .nav-item.danger:hover {
            background: rgba(225, 30, 45, 0.1);
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--gray-50);
        }

        .content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-red);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-red-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Profile Form */
        .profile-form {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
        }

        /* Avatar Upload */
        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            font-weight: 700;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .edit-avatar-btn {
            background: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .edit-avatar-btn:hover {
            background: var(--primary-red-hover);
            transform: translateY(-1px);
        }

        .avatar-upload-input {
            display: none;
        }

        /* Form Fields */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .form-input:disabled {
            background: var(--gray-50);
            color: var(--gray-500);
            cursor: not-allowed;
        }

        /* Select with Flag */
        .select-with-flag {
            position: relative;
        }

        .select-with-flag select {
            padding-left: 3rem;
        }

        .flag-display {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            pointer-events: none;
            z-index: 1;
        }

        /* Success Message */
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-green);
            color: var(--success-green);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                border-right: none;
                border-bottom: 1px solid var(--gray-200);
            }

            .profile-summary {
                display: none;
            }

            .nav-menu {
                display: flex;
                overflow-x: auto;
                padding: 1rem;
                gap: 0.5rem;
            }

            .nav-item {
                white-space: nowrap;
                margin-bottom: 0;
                flex-shrink: 0;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0.75rem 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .profile-form {
                padding: 1.5rem;
            }

            .header-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn {
                font-size: 0.8125rem;
                padding: 0.5rem 1rem;
            }
        }

        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            min-width: 280px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .user-info .user-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .user-info .user-email {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 0.5rem 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .dropdown-item.logout-item {
            color: var(--primary-red);
        }

        .dropdown-item.logout-item:hover {
            background: rgba(225, 30, 45, 0.1);
        }

        .dropdown-form {
            margin: 0;
        }

        /* Phone Input Styles */
        .phone-input-container {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
        }

        .country-code-selector {
            position: relative;
            flex-shrink: 0;
        }

        .country-code-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background: var(--white);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            min-width: 120px;
        }

        .country-code-btn:hover {
            border-color: var(--primary-red);
        }

        .country-flag {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        .country-code {
            font-weight: 500;
            color: var(--gray-700);
        }

        .country-code-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            max-height: 300px;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .country-code-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .country-search {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .country-search-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .country-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .country-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 0.875rem;
        }

        .country-option:hover {
            background: var(--gray-50);
        }

        .country-name {
            flex: 1;
            color: var(--gray-700);
        }

        .phone-number-input {
            flex: 1;
        }

        .phone-verification {
            margin-top: 0.5rem;
        }

        .verify-phone-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--success-green);
            color: var(--white);
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .verify-phone-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="back-button" onclick="history.back()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="logo-section">
                <div class="digilearn-logo">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    DigiLearn
                </div>
                
                <div class="shoutout-logo">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    ShoutOutGh
                </div>
            </div>
        </div>
        
        <div class="header-right">
            <button class="notification-btn">
                <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
            
            <div class="user-dropdown">
                <button class="user-avatar-header" id="userDropdownToggle">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="user-info">
                            <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="user-email">{{ auth()->user()->email ?? 'user@example.com' }}</div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile Settings
                    </a>
                    <a href="{{ route('dashboard.digilearn') }}" class="dropdown-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Back to Lessons
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="dropdown-form">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-summary">
                <div class="profile-avatar">
                    {{ substr(auth()->user()->name ?? 'AS', 0, 2) }}
                </div>
                <div class="profile-name">{{ auth()->user()->name ?? 'Aboagye Samuel' }}</div>
                <div class="profile-email">{{ auth()->user()->email ?? 'samuel.aboagye@gmail.com' }}</div>
                <div class="profile-location">
                    <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="flag-icon">
                    <span>Ghana | 6+ JHS 2024</span>
                </div>
            </div>
            
            <nav class="nav-menu">
                <a href="#" class="nav-item active" data-section="account">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Account
                </a>
                
                <a href="#" class="nav-item" data-section="notifications">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                    </svg>
                    Notification
                </a>
                
                <a href="#" class="nav-item" data-section="language">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    Language
                </a>
                
                <a href="#" class="nav-item" data-section="questions">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Question
                </a>
                
                <a href="#" class="nav-item" data-section="help">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75A9.75 9.75 0 0012 2.25z"/>
                    </svg>
                    Help
                </a>
                
                <button class="nav-item danger" onclick="confirmDeleteAccount()">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Account
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">Account</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-secondary" onclick="cancelChanges()">Cancel</button>
                    <button type="submit" form="profileForm" class="btn btn-primary">Update</button>
                </div>
            </div>

            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                Profile updated successfully!
            </div>

            <form class="profile-form" id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Avatar Upload Section -->
                <div class="avatar-upload">
                    <div class="avatar-large" id="avatarPreview">
                        @if(auth()->user()->avatar ?? false)
                            <img src="{{ auth()->user()->avatar }}" alt="Profile" class="avatar-image">
                        @else
                            {{ substr(auth()->user()->name ?? 'AS', 0, 2) }}
                        @endif
                    </div>
                    <button type="button" class="edit-avatar-btn" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-camera"></i>
                        Edit
                    </button>
                    <input type="file" id="avatarInput" name="avatar" class="avatar-upload-input" accept="image/*" onchange="previewAvatar(this)">
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" value="{{ explode(' ', auth()->user()->name ?? 'Aboagye Samuel')[0] }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" value="{{ explode(' ', auth()->user()->name ?? 'Aboagye Samuel')[1] ?? '' }}" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" value="{{ auth()->user()->email ?? 'samuel.aboagye@gmail.com' }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="phone-input-container">
                                <div class="country-code-selector">
                                    <button type="button" class="country-code-btn" id="countryCodeBtn">
                                        <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="country-flag" id="selectedFlag">
                                        <span class="country-code" id="selectedCode">+233</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div class="country-code-dropdown" id="countryCodeDropdown">
                                        <div class="country-search">
                                            <input type="text" placeholder="Search countries..." class="country-search-input" id="countrySearch">
                                        </div>
                                        <div class="country-list" id="countryList">
                                            <!-- Countries will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                <input type="tel" id="phone" name="phone" class="form-input phone-number-input" 
                                       value="{{ ltrim(auth()->user()->phone ?? '24 123 4567', '+233 ') }}" 
                                       placeholder="24 123 4567">
                                <input type="hidden" id="country_code" name="country_code" value="+233">
                            </div>
                            <div class="phone-verification" id="phoneVerification" style="display: none;">
                                <button type="button" class="verify-phone-btn" id="verifyPhoneBtn">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Verify Phone Number
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="{{ auth()->user()->date_of_birth ?? '2008-01-15' }}">
                        </div>
                    </div>
                </div>

                <!-- Location & Education -->
                <div class="form-section">
                    <h2 class="section-title">Location & Education</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="country" class="form-label">Country</label>
                            <div class="select-with-flag">
                                <div class="flag-display">
                                    <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="flag-icon">
                                </div>
                                <select id="country" name="country" class="form-input" onchange="updateFlag(this)">
                                    <option value="GH" selected>Ghana</option>
                                    <option value="NG">Nigeria</option>
                                    <option value="KE">Kenya</option>
                                    <option value="ZA">South Africa</option>
                                    <option value="US">United States</option>
                                    <option value="GB">United Kingdom</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-input" value="{{ auth()->user()->city ?? 'Accra' }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="education_level" class="form-label">Education Level</label>
                            <select id="education_level" name="education_level" class="form-input">
                                <option value="primary">Primary School</option>
                                <option value="jhs" selected>Junior High School (JHS)</option>
                                <option value="shs">Senior High School (SHS)</option>
                                <option value="university">University</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="grade" class="form-label">Grade/Year</label>
                            <select id="grade" name="grade" class="form-input">
                                <option value="1">Grade 1</option>
                                <option value="2">Grade 2</option>
                                <option value="3">Grade 3</option>
                                <option value="4">Grade 4</option>
                                <option value="5">Grade 5</option>
                                <option value="6">Grade 6</option>
                                <option value="7" selected>Grade 7 (JHS 1)</option>
                                <option value="8">Grade 8 (JHS 2)</option>
                                <option value="9">Grade 9 (JHS 3)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="form-section">
                    <h2 class="section-title">Learning Preferences</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="preferred_language" class="form-label">Preferred Language</label>
                            <select id="preferred_language" name="preferred_language" class="form-input">
                                <option value="en" selected>English</option>
                                <option value="tw">Twi</option>
                                <option value="ga">Ga</option>
                                <option value="ee">Ewe</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="learning_style" class="form-label">Learning Style</label>
                            <select id="learning_style" name="learning_style" class="form-input">
                                <option value="visual">Visual Learner</option>
                                <option value="auditory" selected>Auditory Learner</option>
                                <option value="kinesthetic">Kinesthetic Learner</option>
                                <option value="mixed">Mixed Learning</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="bio" class="form-label">Bio (Optional)</label>
                            <textarea id="bio" name="bio" class="form-input" rows="3" placeholder="Tell us a bit about yourself...">{{ auth()->user()->bio ?? 'Passionate student from Ghana, currently in JHS 2. I love learning new things and exploring different subjects!' }}</textarea>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Navigation functionality
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!this.classList.contains('danger')) {
                    e.preventDefault();
                    
                    // Remove active class from all items
                    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Here you would typically show/hide different sections
                    const section = this.getAttribute('data-section');
                    console.log('Switching to section:', section);
                }
            });
        });

        // Avatar preview functionality
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const avatarPreview = document.getElementById('avatarPreview');
                    avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Profile" class="avatar-image">`;
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Country flag update
        function updateFlag(select) {
            const flagDisplay = select.parentElement.querySelector('.flag-display img');
            const countryCode = select.value.toLowerCase();
            flagDisplay.src = `https://flagcdn.com/w20/${countryCode}.png`;
            flagDisplay.alt = select.options[select.selectedIndex].text;
        }

        // Form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = document.querySelector('.btn-primary');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Show success message
                document.getElementById('successMessage').classList.add('show');
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Hide success message after 3 seconds
                setTimeout(() => {
                    document.getElementById('successMessage').classList.remove('show');
                }, 3000);
                
                // In a real app, you would submit the form data here
                console.log('Profile updated successfully');
            }, 2000);
        });

        // Cancel changes
        function cancelChanges() {
            if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                location.reload();
            }
        }

        // Delete account confirmation
        function confirmDeleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                if (confirm('This will permanently delete all your data. Are you absolutely sure?')) {
                    // In a real app, you would handle account deletion here
                    alert('Account deletion functionality would be implemented here.');
                }
            }
        }

        // Auto-save functionality (optional)
        let autoSaveTimeout;
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Auto-save logic would go here
                    console.log('Auto-saving changes...');
                }, 2000);
            });
        });

        // User dropdown functionality
        const userDropdownToggle = document.getElementById('userDropdownToggle');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdownToggle && userDropdownMenu) {
            userDropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdownToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.remove('active');
                }
            });
        }

        // Phone number functionality
        const countries = [
            { name: 'Ghana', code: '+233', flag: 'gh' },
            { name: 'Nigeria', code: '+234', flag: 'ng' },
            { name: 'Kenya', code: '+254', flag: 'ke' },
            { name: 'South Africa', code: '+27', flag: 'za' },
            { name: 'United States', code: '+1', flag: 'us' },
            { name: 'United Kingdom', code: '+44', flag: 'gb' },
            { name: 'Canada', code: '+1', flag: 'ca' },
            { name: 'Australia', code: '+61', flag: 'au' },
            { name: 'Germany', code: '+49', flag: 'de' },
            { name: 'France', code: '+33', flag: 'fr' },
            { name: 'India', code: '+91', flag: 'in' },
            { name: 'China', code: '+86', flag: 'cn' },
            { name: 'Japan', code: '+81', flag: 'jp' },
            { name: 'Brazil', code: '+55', flag: 'br' },
            { name: 'Mexico', code: '+52', flag: 'mx' }
        ];

        function initializePhoneInput() {
            const countryCodeBtn = document.getElementById('countryCodeBtn');
            const countryCodeDropdown = document.getElementById('countryCodeDropdown');
            const countryList = document.getElementById('countryList');
            const countrySearch = document.getElementById('countrySearch');
            const selectedFlag = document.getElementById('selectedFlag');
            const selectedCode = document.getElementById('selectedCode');
            const countryCodeInput = document.getElementById('country_code');
            const phoneInput = document.getElementById('phone');
            const phoneVerification = document.getElementById('phoneVerification');

            if (!countryCodeBtn || !countryList) return;

            // Populate country list
            function populateCountries(filteredCountries = countries) {
                countryList.innerHTML = '';
                filteredCountries.forEach(country => {
                    const option = document.createElement('div');
                    option.className = 'country-option';
                    option.innerHTML = `
                        <img src="https://flagcdn.com/w20/${country.flag}.png" alt="${country.name}" class="country-flag">
                        <span class="country-name">${country.name}</span>
                        <span class="country-code">${country.code}</span>
                    `;
                    option.addEventListener('click', () => selectCountry(country));
                    countryList.appendChild(option);
                });
            }

            // Select country
            function selectCountry(country) {
                selectedFlag.src = `https://flagcdn.com/w20/${country.flag}.png`;
                selectedFlag.alt = country.name;
                selectedCode.textContent = country.code;
                countryCodeInput.value = country.code;
                countryCodeDropdown.classList.remove('active');
                
                // Show phone verification if phone number exists
                if (phoneInput.value.trim()) {
                    phoneVerification.style.display = 'block';
                }
            }

            // Toggle dropdown
            countryCodeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                countryCodeDropdown.classList.toggle('active');
                if (countryCodeDropdown.classList.contains('active')) {
                    countrySearch.focus();
                }
            });

            // Search functionality
            if (countrySearch) {
                countrySearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filtered = countries.filter(country => 
                        country.name.toLowerCase().includes(searchTerm) ||
                        country.code.includes(searchTerm)
                    );
                    populateCountries(filtered);
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!countryCodeBtn.contains(e.target) && !countryCodeDropdown.contains(e.target)) {
                    countryCodeDropdown.classList.remove('active');
                }
            });

            // Show verification when phone number changes
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    if (this.value.trim()) {
                        phoneVerification.style.display = 'block';
                    } else {
                        phoneVerification.style.display = 'none';
                    }
                });
            }

            // Auto-detect country based on user's location (optional)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // This would typically involve a geolocation API call
                    // For now, we'll default to Ghana
                    const defaultCountry = countries.find(c => c.flag === 'gh');
                    if (defaultCountry) {
                        selectCountry(defaultCountry);
                    }
                });
            }

            // Initialize with default countries
            populateCountries();
        }

        // Initialize phone input
        initializePhoneInput();

        // Phone verification functionality
        const verifyPhoneBtn = document.getElementById('verifyPhoneBtn');
        if (verifyPhoneBtn) {
            verifyPhoneBtn.addEventListener('click', function() {
                const phoneNumber = document.getElementById('phone').value;
                const countryCode = document.getElementById('country_code').value;
                const fullNumber = countryCode + phoneNumber;
                
                // Here you would typically send a verification SMS
                alert(`Verification SMS would be sent to: ${fullNumber}`);
                
                // Update button state
                this.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Verified
                `;
                this.style.background = '#10b981';
                this.disabled = true;
            });
        }
    </script>
</body>
</html>
