<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - ShoutoutGH</title>

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

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

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
      tailwind.config = {
        darkMode: ['class', '[data-theme="dark"]'],
        theme: {
          extend: {
            colors: {
              white: 'var(--tw-col-white, #ffffff)',
              black: 'var(--tw-col-black, #000000)',
              gray: {
                50: 'var(--tw-col-gray-50, #f8fafc)',
                100: 'var(--tw-col-gray-100, #f1f5f9)',
                200: 'var(--tw-col-gray-200, #e2e8f0)',
                300: 'var(--tw-col-gray-300, #cbd5e1)',
                400: 'var(--tw-col-gray-400, #94a3b8)',
                500: 'var(--tw-col-gray-500, #64748b)',
                600: 'var(--tw-col-gray-600, #475569)',
                700: 'var(--tw-col-gray-700, #334155)',
                800: 'var(--tw-col-gray-800, #1e293b)',
                900: 'var(--tw-col-gray-900, #0f172a)',
              }
            }
          }
        }
      }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom styles -->
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--tw-col-gray-50, #f8fafc);
            color: var(--tw-col-gray-900, #0f172a);
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Color scheme variables */
        :root {
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --primary-blue-light: #dbeafe;
            --accent-red: #dc2626;
            --accent-red-hover: #b91c1c;
            --accent-red-light: #fecaca;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        [data-theme="dark"] {
            /* Tailwind mappings override */
            --tw-col-white: #16181c;
            --tw-col-black: #ffffff;
            --tw-col-gray-50: #000000;
            --tw-col-gray-100: #202327;
            --tw-col-gray-200: transparent;
            --tw-col-gray-300: #3e4144;
            --tw-col-gray-400: #71767b;
            --tw-col-gray-500: #8b98a5;
            --tw-col-gray-600: #a4b1cd;
            --tw-col-gray-700: #e2e8f0;
            --tw-col-gray-800: #f1f5f9;
            --tw-col-gray-900: #ffffff;

            /* Custom CSS variables override */
            --white: #16181c;
            --gray-50: #000000;
            --gray-100: #202327;
            --gray-200: transparent;
            --gray-300: #3e4144;
            --gray-400: #71767b;
            --gray-500: #8b98a5;
            --gray-600: #a4b1cd;
            --gray-700: #e2e8f0;
            --gray-800: #f1f5f9;
            --gray-900: #ffffff;
            --border-color: transparent;
            
            color-scheme: dark;
        }

        /* Layout styles */
        .min-h-screen {
            min-height: 100vh;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .flex-1 {
            flex: 1;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .space-x-4>*+* {
            margin-left: 1rem;
        }

        .space-y-2>*+* {
            margin-top: 0.5rem;
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #0f172a; /* Sleek dark slate theme */
            color: var(--white);
            width: 18rem;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            padding: 1.25rem 0.875rem;
            transition: all 0.3s ease-in-out;
            z-index: 40;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
        }

        .sidebar.collapsed {
            width: 4.5rem;
        }

        .sidebar-link-text {
            display: block;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-link-text {
            display: none;
        }

        .sidebar-toggle-icon {
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed .sidebar-toggle-icon {
            transform: rotate(180deg);
        }

        /* Modern Navigation styles */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s ease-in-out;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: #f8fafc;
        }

        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-left-color: #3b82f6;
            font-weight: 600;
        }

        .nav-link i {
            width: 1.25rem;
            text-align: center;
            margin-right: 0.75rem;
            font-size: 0.95rem;
        }

        .nav-link.active i {
            color: #60a5fa;
        }

        /* Submenu refinement */
        .submenu-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            border-radius: 0.375rem;
            color: #94a3b8;
            border-left: 2px solid transparent;
        }

        .submenu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .submenu-link.active {
            background-color: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border-left-color: #3b82f6;
            font-weight: 600;
        }

        /* Header styles */
        .header {
            background-color: var(--white);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--gray-200);
        }

        .header-content {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 1rem 1.5rem;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .header p {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        /* Button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-blue-hover);
        }

        .btn-secondary {
            background-color: var(--accent-red);
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: var(--accent-red-hover);
        }

        /* Dropdown styles */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 0.25rem 0;
            z-index: 50;
            display: none;
        }

        .dropdown.open .dropdown-menu {
            display: block;
        }

        /* Content dropdown specific styles */
        .dropdown.open #contentDropdownMenu {
            display: block !important;
        }

        /* Rotate chevron when dropdown is open */
        .dropdown.open .chevron-transition {
            transform: rotate(180deg);
        }

        .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: var(--gray-100);
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            overflow-x: hidden;
            overflow-y: auto;
            background-color: var(--gray-50);
            margin-left: 18rem;
            width: calc(100% - 18rem);
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
        }

        .sidebar.collapsed + .main-content,
        .sidebar.collapsed ~ .main-content,
        body.sidebar-collapsed .main-content {
            margin-left: 4.5rem !important;
            width: calc(100% - 4.5rem) !important;
        }

        .content-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 1.25rem 1.75rem;
        }

        /* Alert styles */
        .alert {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid;
        }

        .alert-success {
            background-color: #dcfce7;
            border-color: #16a34a;
            color: #15803d;
        }

        .alert-error {
            background-color: var(--accent-red-light);
            border-color: var(--accent-red);
            color: var(--accent-red-hover);
        }

        /* Utility classes */
        .hidden {
            display: none;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-medium {
            font-weight: 500;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mt-auto {
            margin-top: auto;
        }

        .mr-3 {
            margin-right: 0.75rem;
        }

        .ml-2 {
            margin-left: 0.5rem;
        }

        .ml-6 {
            margin-left: 1.5rem;
        }

        .p-2 {
            padding: 0.5rem;
        }

        .p-4 {
            padding: 1rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .pt-4 {
            padding-top: 1rem;
        }

        .w-5 {
            width: 1.25rem;
        }

        .w-6 {
            width: 1.5rem;
        }

        .w-8 {
            width: 2rem;
        }

        .h-5 {
            height: 1.25rem;
        }

        .h-6 {
            height: 1.5rem;
        }

        .h-8 {
            height: 2rem;
        }

        .rounded {
            border-radius: 0.25rem;
        }

        .rounded-lg {
            border-radius: 0.5rem;
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .border-t {
            border-top: 1px solid var(--gray-700);
        }

        /* Logo styles */
        .logo {
            background-color: var(--primary-blue);
            border-radius: 0.5rem;
            padding: 0.5rem;
            margin-right: 0.75rem;
        }

        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            display: block;
            height: 0.5rem;
            width: 0.5rem;
            border-radius: 9999px;
            background-color: var(--accent-red);
        }

        /* Avatar styles */
        .avatar {
            height: 2rem;
            width: 2rem;
            border-radius: 9999px;
            background-color: var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar span {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        /* Custom styles for inline style replacements */
        .sidebar-toggle-btn {
            color: white;
            background: none;
            border: none;
            position: absolute;
            right: 1rem;
            top: 1rem;
            cursor: pointer;
        }

        .content-dropdown-btn {
            width: 100%;
            justify-content: space-between;
            background: none;
            border: none;
            text-align: left;
        }

        .chevron-transition {
            transition: transform 0.2s;
        }

        .dropdown-menu-wide {
            width: 20rem;
        }

        .notification-header {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: var(--gray-700);
            border-bottom: 1px solid var(--gray-200);
        }

        .notification-title {
            font-weight: 500;
        }

        .notification-subtitle {
            color: var(--gray-500);
        }

        .notification-btn {
            padding: 0.5rem;
            color: var(--gray-400);
            background: none;
            border: none;
            cursor: pointer;
            position: relative;
        }

        .user-dropdown-btn {
            font-size: 0.875rem;
            background: none;
            border: none;
            cursor: pointer;
        }

        .user-name {
            color: var(--gray-700);
        }

        .logout-btn {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
        }

        .error-list {
            list-style-type: disc;
            list-style-position: inside;
        }

        .icon-small {
            width: 1rem;
            height: 1rem;
        }

        /* Dynamic background for active content items */
        .content-item-active {
            background-color: var(--primary-blue-hover);
        }

        .content-item-inactive {
            background-color: transparent;
        }

        /* Dynamic display for dropdown menu */
        .dropdown-menu-show {
            display: block;
        }

        .dropdown-menu-hide {
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 4rem;
            }

            .sidebar-link-text {
                display: none;
            }

            .main-content {
                margin-left: 4rem;
            }
        }
    </style>
    @stack('styles')
    @stack('extra-css')
</head>

<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar">
            <div class="flex items-center mb-8 justify-between">
                <div class="flex items-center">
                    <div class="logo">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold sidebar-link-text">ShoutoutGH Admin</h1>
                </div>
                <button id="sidebar-toggle" class="sidebar-toggle-btn">
                    <i class="fas fa-chevron-left sidebar-toggle-icon"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>

                @role('super-admin')
                <a href="{{ route('admin.users') }}"
                    class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-link-text">Users</span>
                </a>

                <!-- Tutor Operations Parent Menu -->
                @php
                    $isTutorHubActive = request()->routeIs('admin.tutors*');
                    $pendingTutorsCount = \App\Models\TutorProfile::where('is_approved', false)->count();
                @endphp
                <div class="space-y-1 my-1">
                    <button type="button" 
                            onclick="document.getElementById('tutorHubSubmenu').classList.toggle('hidden'); document.getElementById('tutorHubChevron').classList.toggle('rotate-180');" 
                            class="w-full nav-link flex items-center justify-between {{ $isTutorHubActive ? 'active' : '' }}">
                        <div class="flex items-center">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span class="sidebar-link-text">Tutor Operations</span>
                        </div>
                        <div class="flex items-center gap-1.5 ml-auto">
                            @if($pendingTutorsCount > 0)
                                <span class="bg-red-500 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full shadow-sm">{{ $pendingTutorsCount }}</span>
                            @endif
                            <i id="tutorHubChevron" class="fas fa-chevron-down text-xs transition-transform duration-200 ml-1 {{ $isTutorHubActive ? 'rotate-180' : '' }}"></i>
                        </div>
                    </button>

                    <div id="tutorHubSubmenu" class="{{ $isTutorHubActive ? '' : 'hidden' }} ml-4 pl-3 border-l border-slate-700/60 space-y-1 mt-1">
                        <a href="{{ route('admin.tutors.index') }}"
                            class="nav-link submenu-link flex items-center justify-between {{ request()->routeIs('admin.tutors*') ? 'active' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-user-check"></i>
                                <span class="sidebar-link-text">Applications & Verification</span>
                            </div>
                            @if($pendingTutorsCount > 0)
                                <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1">{{ $pendingTutorsCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
                @endrole

                <!-- Student Progress Management -->
                @role('super-admin')
                <a href="{{ route('admin.progress.overview') }}"
                    class="nav-link {{ request()->routeIs('admin.progress*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span class="sidebar-link-text">Student Progress</span>
                </a>

                <a href="{{ route('admin.quizzes.review.index') }}"
                    class="nav-link {{ request()->routeIs('admin.quizzes.review*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    <span class="sidebar-link-text">Quiz Integrity</span>
                </a>
                @endrole

                <!-- Added notifications link to sidebar -->
                <a href="{{ route('admin.notifications.index') }}"
                    class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span class="sidebar-link-text">Notifications</span>
                </a>

                <!-- Platform Settings Hub -->
                @role('super-admin')
                <a href="{{ route('admin.platform-settings.index') }}"
                    class="nav-link {{ request()->routeIs('admin.platform-settings*') || request()->routeIs('admin.hero-banners*') || request()->routeIs('admin.level-groups*') || request()->routeIs('admin.pricing*') ? 'active' : '' }}">
                    <i class="fas fa-cogs"></i>
                    <span class="sidebar-link-text">Platform Settings</span>
                </a>
                @endrole

                <!-- Simplified Contents Section -->
                <a href="{{ route('admin.contents.index') }}"
                    class="nav-link {{ request()->routeIs('admin.contents*') ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i>
                    <span class="sidebar-link-text">Contents</span>
                </a>

                <a href="{{ route('admin.ai-contents.index') }}"
                    class="nav-link {{ request()->routeIs('admin.ai-contents*') ? 'active' : '' }}">
                    <i class="fas fa-robot"></i>
                    <span class="sidebar-link-text">AI Contents</span>
                </a>

                @role('super-admin')
                <a href="{{ route('admin.revenue') }}"
                    class="nav-link {{ request()->routeIs('admin.revenue*') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i>
                    <span class="sidebar-link-text">Revenue Analytics</span>
                </a>
 
                <a href="{{ route('admin.subscriber-analytics') }}"
                    class="nav-link {{ request()->routeIs('admin.subscriber-analytics*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i>
                    <span class="sidebar-link-text">Subscriber Analytics</span>
                </a>
                @endrole

                <a href="{{ route('admin.tasks.index') }}"
                    class="nav-link {{ request()->routeIs('admin.tasks*') ? 'active' : '' }}">
                    <i class="fas fa-tasks"></i>
                    <span class="sidebar-link-text">Tasks</span>
                </a>

                @role('super-admin')
                <a href="{{ route('admin.analytics') }}"
                    class="nav-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span class="sidebar-link-text">Reports & Web Analytics</span>
                </a>
 
                <a href="#" class="nav-link">
                    <i class="fas fa-shield-alt"></i>
                    <span class="sidebar-link-text">Security</span>
                </a>
 
                <a href="#" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span class="sidebar-link-text">Settings</span>
                </a>
                @endrole

                @role('super-admin')
                @if(Auth::user()->is_superuser)
                <a href="{{ route('admin.classes.create') }}"
                    class="nav-link {{ request()->routeIs('admin.classes.create') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span class="sidebar-link-text">Create Class</span>
                </a>
                @endif
                @endrole
            </nav>

            <div class="mt-auto pt-4 border-t">
                <a href="{{ route('dashboard.main') }}" class="nav-link">
                    <i class="fas fa-arrow-left"></i>
                    <span class="sidebar-link-text">Back to Site</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="sidebar-link-text">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="header">
                <div class="header-content">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1>@yield('page-title', 'Dashboard')</h1>
                            <p>@yield('page-description', 'Welcome to the admin dashboard')</p>
                        </div>

                        <div class="flex items-center space-x-4">

                            <!-- Dynamic Real-time Admin Notifications -->
                            <div class="dropdown" id="notificationDropdown">
                                <button id="notificationDropdownBtn" class="notification-btn relative p-2 text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors" title="Notifications">
                                    <i class="fas fa-bell text-xl"></i>
                                    <span id="adminNotificationBadge" class="hidden absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full min-w-[18px] text-center shadow-sm">0</span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-wide shadow-xl rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800" style="width: 350px; max-height: 450px; overflow-y: auto;">
                                    <div class="p-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                        <strong class="text-sm font-bold text-gray-900 dark:text-white">Notifications</strong>
                                        <button id="markAllReadBtn" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold hidden">Mark all read</button>
                                    </div>
                                    <div id="adminNotificationList" class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <div class="p-4 text-center text-xs text-gray-400">Loading notifications...</div>
                                    </div>
                                    <div class="p-2.5 text-center border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                                        <a href="{{ route('admin.notifications.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">View All Notifications →</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <button id="toggledarkmodebutton" class="notification-btn" title="Toggle Dark Mode" style="margin-right: 0.5rem;">
                                <i id="themeIcon" class="fas fa-moon w-5 h-5 text-gray-500 hover:text-gray-900 transition-colors"></i>
                            </button>

                            <!-- User Menu -->
                            <div class="dropdown" id="userDropdown">
                                <button id="userDropdownBtn" class="flex items-center user-dropdown-btn">
                                    <x-user-avatar :user="auth()->user()" :size="30" id="user-avatar" />
                                </button>

                                <div class="dropdown-menu">
                                    <a href="{{ route('profile.show') }}" class="dropdown-item">Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item logout-btn">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="content-wrapper">
                <!-- Flash Messages -->
                @if(session('success') || session('error') || (isset($errors) && $errors->any()))
                <div id="flashAlertContainer" class="max-w-2xl mx-auto px-4 pt-4 transition-all duration-300">
                    @if(session('success'))
                    <div class="alert-banner bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm font-medium mb-4 transition-all duration-300" id="flashAlertSuccess">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-check-circle text-emerald-600 text-base flex-shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="dismissFlashAlert('flashAlertSuccess')" class="text-emerald-500 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert-banner bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm font-medium mb-4 transition-all duration-300" id="flashAlertError">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-exclamation-circle text-red-600 text-base flex-shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="dismissFlashAlert('flashAlertError')" class="text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-100 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                    <div class="alert-banner bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm flex items-start justify-between gap-3 text-sm font-medium mb-4 transition-all duration-300" id="flashAlertErrors">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-base flex-shrink-0 mt-0.5"></i>
                            <ul class="list-disc pl-4 space-y-1">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" onclick="dismissFlashAlert('flashAlertErrors')" class="text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-100 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endif
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function dismissFlashAlert(alertId) {
            const el = document.getElementById(alertId);
            if (el) {
                el.style.transition = 'all 0.3s ease-out';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => el.remove(), 300);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-banner');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentElement) {
                        alert.style.transition = 'all 0.4s ease-out';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 400);
                    }
                }, 5000);
            });
        });

        // JavaScript for dropdown functionality and sidebar toggle
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) {
                console.error('Dropdown not found:', dropdownId);
                return;
            }

            const isOpen = dropdown.classList.contains('open');

            // Close all dropdowns
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));

            // Toggle current dropdown
            if (!isOpen) {
                dropdown.classList.add('open');

                // Special handling for content dropdown
                if (dropdownId === 'contentDropdown') {
                    const contentMenu = document.getElementById('contentDropdownMenu');
                    if (contentMenu) {
                        contentMenu.classList.remove('dropdown-menu-hide');
                        contentMenu.classList.add('dropdown-menu-show');
                    }
                }
            } else {
                // Special handling for content dropdown when closing
                if (dropdownId === 'contentDropdown') {
                    const contentMenu = document.getElementById('contentDropdownMenu');
                    if (contentMenu) {
                        contentMenu.classList.remove('dropdown-menu-show');
                        contentMenu.classList.add('dropdown-menu-hide');
                    }
                }
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(d => {
                    d.classList.remove('open');

                    // Special handling for content dropdown
                    if (d.id === 'contentDropdown') {
                        const contentMenu = document.getElementById('contentDropdownMenu');
                        if (contentMenu) {
                            contentMenu.classList.remove('dropdown-menu-show');
                            contentMenu.classList.add('dropdown-menu-hide');
                        }
                    }
                });
            }
        });

        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('#sidebar-toggle');
            const mainContent = document.querySelector('.main-content');

            if (sidebarToggle && sidebar && mainContent) {
                const updateSidebarState = (isCollapsed) => {
                    if (isCollapsed) {
                        sidebar.classList.add('collapsed');
                        document.body.classList.add('sidebar-collapsed');
                        mainContent.style.marginLeft = '4.5rem';
                        mainContent.style.width = 'calc(100% - 4.5rem)';
                    } else {
                        sidebar.classList.remove('collapsed');
                        document.body.classList.remove('sidebar-collapsed');
                        mainContent.style.marginLeft = '18rem';
                        mainContent.style.width = 'calc(100% - 18rem)';
                    }

                    const icon = sidebarToggle.querySelector('i');
                    if (icon) {
                        if (isCollapsed) {
                            icon.classList.remove('fa-chevron-left');
                            icon.classList.add('fa-chevron-right');
                        } else {
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-left');
                        }
                    }
                };

                // Restore sidebar state from localStorage
                try {
                    if (localStorage.getItem('sidebarCollapsed') === 'true') {
                        updateSidebarState(true);
                    }
                } catch (e) {}

                sidebarToggle.addEventListener('click', function () {
                    const currentlyCollapsed = sidebar.classList.contains('collapsed');
                    const nextState = !currentlyCollapsed;
                    updateSidebarState(nextState);

                    try {
                        localStorage.setItem('sidebarCollapsed', nextState ? 'true' : 'false');
                    } catch (e) {}
                });
            }

            // Content dropdown functionality removed - now using simple navigation

            const notificationDropdownBtn = document.getElementById('notificationDropdownBtn');
            if (notificationDropdownBtn) {
                notificationDropdownBtn.addEventListener('click', function () {
                    toggleDropdown('notificationDropdown');
                });
            }

            const userDropdownBtn = document.getElementById('userDropdownBtn');
            if (userDropdownBtn) {
                userDropdownBtn.addEventListener('click', function () {
                    toggleDropdown('userDropdown');
                });
            }

            // Dark Mode Toggle Logic
            const toggleButton = document.getElementById('toggledarkmodebutton');
            const themeIcon = document.getElementById('themeIcon');
        
            if (toggleButton) {
                const initialTheme = document.documentElement.getAttribute('data-theme') || 'light';
                if (themeIcon) {
                    if (initialTheme === 'dark') {
                        themeIcon.classList.remove('fa-moon');
                        themeIcon.classList.add('fa-sun');
                        toggleButton.setAttribute('title', 'Switch to Light Mode');
                    } else {
                        themeIcon.classList.remove('fa-sun');
                        themeIcon.classList.add('fa-moon');
                        toggleButton.setAttribute('title', 'Switch to Dark Mode');
                    }
                }

                toggleButton.addEventListener('click', function () {
                    const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
                    const newTheme = activeTheme === 'light' ? 'dark' : 'light';
                    
                    document.documentElement.setAttribute('data-theme', newTheme);
                    try { localStorage.setItem('theme', newTheme); } catch(e) {}
                    
                    if (themeIcon) {
                        if (newTheme === 'dark') {
                            themeIcon.classList.remove('fa-moon');
                            themeIcon.classList.add('fa-sun');
                            toggleButton.setAttribute('title', 'Switch to Light Mode');
                        } else {
                            themeIcon.classList.remove('fa-sun');
                            themeIcon.classList.add('fa-moon');
                            toggleButton.setAttribute('title', 'Switch to Dark Mode');
                        }
                    }
                });
            }
        });

        // Keep session alive
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                fetch('/ping', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }
        }, 300000); // 5 minutes
    </script>
    <script src="/js/avatar-updater.js"></script>


    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Header upload modal functionality
        document.addEventListener('DOMContentLoaded', function () {
            const headerUploadBtn = document.getElementById('headerUploadBtn');
            const headerUploadModal = document.getElementById('headerUploadModal');
            const closeHeaderModal = document.getElementById('closeHeaderModal');
            const headerCancelUpload = document.getElementById('headerCancelUpload');
            const headerFileUploadArea = document.getElementById('headerFileUploadArea');
            const headerFileInput = document.getElementById('headerFileInput');
            const headerUploadForm = document.getElementById('headerUploadForm');

            // Open modal
            headerUploadBtn.addEventListener('click', () => {
                headerUploadModal.classList.add('show');
            });

            // Close modal
            [closeHeaderModal, headerCancelUpload].forEach(btn => {
                btn.addEventListener('click', () => {
                    headerUploadModal.classList.remove('show');
                    headerUploadForm.reset();
                    headerFileUploadArea.innerHTML = `
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500">MP4, PDF, DOC, DOCX up to 600MB</p>
                `;
                });
            });

            // File upload area click
            headerFileUploadArea.addEventListener('click', () => {
                headerFileInput.click();
            });

            // File input change
            headerFileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    updateHeaderFileUploadArea(file);
                }
            });

            // Drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                headerFileUploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                headerFileUploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                headerFileUploadArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                headerFileUploadArea.classList.add('dragover');
            }

            function unhighlight() {
                headerFileUploadArea.classList.remove('dragover');
            }

            headerFileUploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files.length > 0) {
                    headerFileInput.files = files;
                    updateHeaderFileUploadArea(files[0]);
                }
            }

            function updateHeaderFileUploadArea(file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                headerFileUploadArea.innerHTML = `
                <i class="fas fa-file text-2xl text-blue-600 mb-2"></i>
                <p class="text-gray-900 font-medium">${file.name}</p>
                <p class="text-sm text-gray-500">${fileSize} MB</p>
            `;
            }

            // Form submission
            headerUploadForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(headerUploadForm);
                const contentType = formData.get('content_type');

                try {
                    let response;
                    if (contentType === 'video') {
                        response = await fetch('{{ route("admin.content.videos.store") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                    } else if (contentType === 'document') {
                        response = await fetch('{{ route("admin.content.documents.store") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                    } else if (contentType === 'quiz') {
                        response = await fetch('{{ route("admin.contents.store") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                    }

                    if (response.ok) {
                        headerUploadModal.classList.remove('show');
                        headerUploadForm.reset();
                        headerFileUploadArea.innerHTML = `
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Click to upload or drag and drop</p>
                        <p class="text-sm text-gray-500">MP4, PDF, DOC, DOCX up to 600MB</p>
                    `;
                        // Redirect to contents page to see new content
                        window.location.href = '{{ route("admin.contents.index") }}';
                    } else {
                        const error = await response.json();
                        alert('Upload failed: ' + (error.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                }
            });
        });
    </script>

    <!-- Real-time Admin Notification Engine & Toast Banner Container -->
    <div id="adminToastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full"></div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const badge = document.getElementById('adminNotificationBadge');
            const listContainer = document.getElementById('adminNotificationList');
            const markAllBtn = document.getElementById('markAllReadBtn');
            let knownNotificationIds = new Set();
            let isInitialFetch = true;

            async function fetchAdminNotifications() {
                try {
                    const response = await fetch('/api/notifications?per_page=10', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    if (!data.success) return;

                    const rawNotifications = data.notifications.data || data.notifications || [];
                    const unreadCount = data.unread_count || 0;

                    // Update Badge
                    if (badge) {
                        if (unreadCount > 0) {
                            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    // Update Mark All Read button
                    if (markAllBtn) {
                        if (unreadCount > 0) {
                            markAllBtn.classList.remove('hidden');
                        } else {
                            markAllBtn.classList.add('hidden');
                        }
                    }

                    // Render Dropdown List
                    if (listContainer) {
                        if (rawNotifications.length === 0) {
                            listContainer.innerHTML = '<div class="p-4 text-center text-xs text-gray-400">No notifications found</div>';
                        } else {
                            listContainer.innerHTML = rawNotifications.map(n => {
                                const title = n.data?.title || n.title || 'System Alert';
                                const message = n.data?.message || n.message || '';
                                const hasActionUrl = !!(n.data?.url || n.url);
                                const showUrl = `/admin/notifications/${n.id}`;
                                const isUnread = !n.read_at;
                                const timeAgo = n.created_at ? new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';

                                return `
                                    <a href="${showUrl}" class="block p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition ${isUnread ? 'bg-blue-50/50 dark:bg-blue-900/10 font-semibold' : ''}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5 flex-1 min-w-0">
                                                ${isUnread ? '<span class="w-2 h-2 rounded-full bg-blue-600 inline-block flex-shrink-0"></span>' : ''}
                                                <span class="truncate">${title}</span>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                ${hasActionUrl ? '<i class="fas fa-external-link-alt text-[10px] text-blue-500" title="Has action link"></i>' : ''}
                                                <span class="text-[10px] text-gray-400">${timeAgo}</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">${message}</div>
                                    </a>
                                `;
                            }).join('');
                        }
                    }

                    // Check for NEW notifications to trigger Toast Popup
                    rawNotifications.forEach(n => {
                        if (!knownNotificationIds.has(n.id)) {
                            knownNotificationIds.add(n.id);

                            if (!isInitialFetch && !n.read_at) {
                                showNotificationToast(n);
                            }
                        }
                    });

                    isInitialFetch = false;
                } catch (e) {
                    console.error('Error fetching admin notifications:', e);
                }
            }

            function showNotificationToast(notification) {
                const toastContainer = document.getElementById('adminToastContainer');
                if (!toastContainer) return;

                const title = notification.data?.title || notification.title || 'New Notification';
                const message = notification.data?.message || notification.message || '';
                const showUrl = `/admin/notifications/${notification.id}`;

                const toast = document.createElement('div');
                toast.className = 'pointer-events-auto bg-white dark:bg-gray-800 border-l-4 border-blue-600 rounded-lg shadow-xl p-4 transform transition-all duration-300 translate-y-5 opacity-0 flex items-start gap-3';
                toast.innerHTML = `
                    <div class="text-blue-600 text-xl font-bold mt-0.5">🔔</div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">${title}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">${message}</p>
                        <a href="${showUrl}" class="inline-block mt-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">View Notification Details →</a>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-xs p-1" onclick="this.parentElement.remove()">✕</button>
                `;

                toastContainer.appendChild(toast);

                // Animate In
                setTimeout(() => {
                    toast.classList.remove('translate-y-5', 'opacity-0');
                }, 50);

                // Auto Dismiss after 8 seconds
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-5');
                    setTimeout(() => toast.remove(), 300);
                }, 8000);
            }

            if (markAllBtn) {
                markAllBtn.addEventListener('click', async function () {
                    try {
                        await fetch('/api/notifications/mark-all-read', {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        fetchAdminNotifications();
                    } catch (e) {
                        console.error('Error marking all as read:', e);
                    }
                });
            }

            // Initial load + interval polling every 6 seconds
            fetchAdminNotifications();
            setInterval(fetchAdminNotifications, 6000);
        });
    </script>

    @stack('scripts')
    @stack('extra-js')
</body>

</html>