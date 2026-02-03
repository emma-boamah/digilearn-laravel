<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ShoutOutGh') }} - Dashboard</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #b91c1c;
            --secondary-blue: #2e7ab8ff;
            --secondary-blue-hover: #1f6f9f;
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
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            padding-top: var(--safe-area-inset-top);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header Styles */
        .header {
            background-color: var(--white);
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: none;
            border: none;
            color: var(--gray-800);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 1rem;
            width: 100%;
            justify-content: flex-start;
            margin-top: 4rem;
        }

        .back-button:hover {
            opacity: 0.8;
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
            font-weight: 600;
            font-size: 1rem;
        }
        
        .shoutout-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shoutout-logo img {
            height: 38px;
        }

        .page-title {
            color: var(--primary-red);
            font-size: 1.25rem;
            font-weight: 600;
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

        .header-divider {
            width: 1px;
            height: 24px;
            background-color: var(--gray-300);
        }

        /* User Avatar Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-avatar-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .user-avatar-btn:hover {
            background-color: var(--gray-100);
        }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            width: 260px;
            max-width: 100vw;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
            margin: 0.5rem;
        }

        .user-dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .user-info .user-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .user-info .user-email {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .user-dropdown-body {
            padding: 0.5rem;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
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

        .dropdown-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: var(--gray-500);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-red);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            cursor: pointer;
        }

        .level-info-container {
            background-color: var(--gray-50);
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
            margin-top: 3.8rem;
        }

        /* Main Content */
        .main-content {
            padding: 1rem 0 2rem 0; /* Added top padding for fixed header */
        }

        /* Card Styles */
        .card {
            background-color: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-image {
            width: 100%;
            height: 200px;
            background-color: var(--gray-300);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--primary-red);
            margin-bottom: 0.75rem;
        }

        .card-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .card-button {
            width: 100%;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .card-button:hover {
            background-color: var(--secondary-blue-hover);
        }

        /* Grid Layouts */
        .three-column-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .three-column-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .three-column-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .four-column-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .four-column-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .four-column-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .four-column-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .logo-section {
                order: 1;
                flex: 1;
            }
            
            .page-title {
                order: 2;
                flex-basis: 100%;
                text-align: center;
            }
            
            .header-right {
                order: 3;
            }
        }
    </style>
</head>
<body>
    @include('components.dashboard-header')
    @yield('content')
    @stack('scripts')
</body>
</html>
