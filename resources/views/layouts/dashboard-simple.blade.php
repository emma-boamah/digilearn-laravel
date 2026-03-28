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

    <!-- Alpine.js -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Conditional Analytics -->
    @if(auth()->check() ? $cookieManager->isAllowed('analytics') : (request()->cookie('digilearn_consent') ?
    json_decode(request()->cookie('digilearn_consent'), true)['analytics'] ?? false : false))
    @include('partials.analytics')
    @endif


    <!-- Theme Initialization -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root,
        [data-theme="light"] {
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

            /* Semantic variables */
            --bg-main: var(--gray-100);
            --bg-surface: var(--white);
            --text-main: var(--gray-900);
            --text-muted: var(--gray-500);
            --border-color: var(--gray-200);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --bg-main: #000000;
            --bg-surface: #16181c;
            --text-main: #ffffff;
            --text-muted: #71767b;
            --border-color: #2f3336;

            /* Overrides for hardcoded grays */
            --gray-50: #16181c;
            --gray-100: #000000;
            --gray-200: #2f3336;
            --gray-300: #3e4144;
            --gray-400: #71767b;
            --gray-500: #8b98a5;
            --gray-600: #a4b1cd;
            --gray-700: #e2e8f0;
            --gray-800: #f1f5f9;
            --gray-900: #ffffff;
            --white: #16181c;

            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);

            color-scheme: dark;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            padding-top: var(--safe-area-inset-top);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header Styles */
        .header {
            background-color: var(--bg-surface);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
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
            color: var(--text-main);
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
            color: var(--text-muted);
        }

        .header-divider {
            width: 1px;
            height: 24px;
            background-color: var(--border-color);
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
            background: var(--bg-surface);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
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
            border-bottom: 1px solid var(--border-color);
        }

        .user-info .user-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .user-info .user-email {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .user-dropdown-body {
            padding: 0.5rem;
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
            background-color: var(--gray-50);
            color: var(--text-main);
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
            color: var(--text-muted);
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
            background-color: var(--bg-surface);
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-top: 3.8rem;
        }

        /* Main Content */
        .main-content {
            padding: 1rem 0 2rem 0;
            /* Added top padding for fixed header */
        }

        /* Card Styles */
        .card {
            background-color: var(--bg-surface);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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
            color: var(--text-main);
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
    @include('cookie-consent-banner')
</body>

</html>