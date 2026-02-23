<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Settings') - {{ config('app.name', 'EduPlatform') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-color: #2563eb; /* Blue-600 */
            --primary-hover: #1d4ed8; /* Blue-700 */
            --bg-body: #f3f4f6; /* Gray-100 */
            --bg-card: #ffffff;
            --text-main: #111827; /* Gray-900 */
            --text-secondary: #4b5563; /* Gray-600 */
            --text-muted: #9ca3af; /* Gray-400 */
            --border-color: #e5e7eb; /* Gray-200 */
            --sidebar-width: 250px;
            --header-height: 64px;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
            --safe-area-inset-bottom: env(safe-area-inset-bottom, 0px);
            --safe-area-inset-left: env(safe-area-inset-left, 0px);
            --safe-area-inset-right: env(safe-area-inset-right, 0px);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .settings-sidebar {
            width: var(--sidebar-width);
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: transform 0.3s ease;
            padding-top: var(--safe-area-inset-top);
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-logo {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .logo-image {
            height: 47px;
            width: 100%;
            max-width: 500px;
            object-fit: contain;
        }

        .sidebar-nav {
            padding: 1.5rem 1rem;
            flex: 1;
            overflow-y: auto;
        }

        .nav-group {
            margin-bottom: 2rem;
        }

        .nav-group-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
            padding-left: 0.75rem;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .nav-item:hover {
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .nav-item.active {
            background-color: #eff6ff; /* Blue-50 */
            color: var(--primary-color);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }
        
        .back-link:hover {
            background-color: var(--bg-body);
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0; /* Allow flex to shrink beyond content */
            width: 100%;
        }

        .top-bar {
            height: calc(var(--header-height) + var(--safe-area-inset-top));
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--safe-area-inset-top) calc(2rem + var(--safe-area-inset-right)) 0 calc(2rem + var(--safe-area-inset-left));
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .breadcrumb {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .breadcrumb span {
            color: var(--text-muted);
            margin: 0 0.5rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .content-area {
            padding: 2rem;
            padding-bottom: calc(2rem + var(--safe-area-inset-bottom));
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            max-width: 100%; /* Ensure containment */
            box-sizing: border-box;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .page-description {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .settings-sidebar {
                transform: translateX(-100%);
            }
            .settings-sidebar.active {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
                margin-right: 1rem;
            }
            .top-bar {
                padding: var(--safe-area-inset-top) calc(1rem + var(--safe-area-inset-right)) 0 calc(1rem + var(--safe-area-inset-left));
                justify-content: flex-start;
            }
            .content-area {
                padding: 1rem calc(1rem + var(--safe-area-inset-right)) calc(1rem + var(--safe-area-inset-bottom)) calc(1rem + var(--safe-area-inset-left));
            }
        }
        /* Shared Responsive Utilities */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
            position: relative;
            scrollbar-width: thin;
        }

        /* Shadow indicator for horizontal scroll */
        .table-responsive::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 30px;
            background: linear-gradient(to left, rgba(0,0,0,0.05), transparent);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .table-responsive.is-scrollable::after {
            opacity: 1;
        }
        
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 10px;
        }

        .text-wrap {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
        }

        @media (max-width: 768px) {
            .grid-1-col {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .stack-mobile {
                flex-direction: column !important;
            }
        }

        /* CSP utilities */
        .flex-1 { flex: 1; }
        .w-10 { width: 40px; }
        .h-10 { height: 40px; }
        .object-cover { object-fit: cover; }
        .border-2 { border: 2px solid var(--border-color); }
        .alert-success { background-color: #d1fae5; color: #065f46; }
        .alert-error { background-color: #fee2e2; color: #991b1b; }
        .hidden { display: none; }
        .text-right { text-align: right; }
        .font-medium { font-weight: 500; }
        .bg-transparent { background: none; }
        .border-none { border: none; }
        .cursor-pointer { cursor: pointer; }
        .text-left { text-align: left; }
        .font-inherit { font-family: inherit; }
        .text-inherit { font-size: inherit; }
        .grid { display: grid; }
        .cursor-not-allowed { cursor: not-allowed; }
        
        @media (min-width: 640px) {
            .sm-block { display: block; }
        }
        .w-full { width: 100%; }
        .max-w-full { max-width: 100%; }
        .h-auto { height: auto; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .inline-flex { display: inline-flex; }
        .items-start { align-items: flex-start; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .justify-start { justify-content: flex-start; }
        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .p-4 { padding: 1rem; }
        .p-5 { padding: 1.25rem; }
        .p-6 { padding: 1.5rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .p-2-5 { padding: 0.625rem 1rem; }
        .p-3 { padding: 0.75rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .w-12 { width: 3rem; }
        .h-12 { height: 3rem; }
        .w-14 { width: 3.5rem; }
        .h-14 { height: 3.5rem; }
        .h-2 { height: 0.5rem; }
        .h-full { height: 100%; }
        .flex-shrink-0 { flex-shrink: 0; }
        .transition-all { transition: all 0.2s; }
        .border-collapse { border-collapse: collapse; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .rounded-2xl { border-radius: 1rem; }
        .rounded-full { border-radius: 9999px; }
        .border { border: 1px solid var(--border-color); }
        .bg-card { background-color: var(--bg-card); }
        .bg-body { background-color: var(--bg-body); }
        .bg-white { background-color: #ffffff; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-blue-50 { background-color: #eff6ff; }
        .bg-green-50 { background-color: #dcfce7; }
        .bg-orange-50 { background-color: #fff7ed; }
        .bg-red-50 { background-color: #fee2e2; }
        .text-white { color: #ffffff; }
        .text-blue-700 { color: #1d4ed8; }
        .text-green-700 { color: #15803d; }
        .text-orange-700 { color: #c2410c; }
        .text-red-700 { color: #b91c1c; }
        .text-blue-800 { color: #1e40af; }
        .text-blue-900 { color: #1e3a8a; }
        .overflow-hidden { overflow: hidden; }
        .relative { position: relative; }
        .text-center { text-align: center; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .font-extrabold { font-weight: 800; }
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        .text-3xl { font-size: 1.875rem; }
        .text-4xl { font-size: 2.25rem; }
        .text-main { color: var(--text-main); }
        .text-secondary { color: var(--text-secondary); }
        .text-muted { color: var(--text-muted); }
        .text-primary { color: var(--primary-color); }
        .shadow-sm { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .uppercase { text-transform: uppercase; }
        .no-underline { text-decoration: none; }
        .block { display: block; }
        .inline-block { display: inline-block; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="settings-sidebar" id="settingsSidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard.main') }}" class="brand-logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                <!-- <span>EduConnect</span> -->
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-group">
                <div class="nav-group-title">Settings Menu</div>
                
                <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                
                <a href="#" class="nav-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </a>
                
                <a href="{{ route('settings.notifications') }}" class="nav-item {{ request()->routeIs('settings.notifications') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Preferences</span>
                </a>
                
                <a href="{{ route('settings.billing') }}" class="nav-item {{ request()->routeIs('settings.billing') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Billing</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">Account Action</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item w-full bg-transparent border-none cursor-pointer text-left font-inherit text-inherit">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a href="{{ route('dashboard.main') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="top-bar">
            <button class="mobile-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="flex-1 flex items-center">
                <div class="breadcrumb">
                    Settings <span>/</span> @yield('breadcrumb', 'Overview')
                </div>
            </div>
            
            <div class="user-menu">
                <div class="hidden sm-block text-right">
                    <div class="font-medium text-sm">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-secondary">{{ Auth::user()->email }}</div>
                </div>
                <img src="{{ Auth::user()->avatar_url ? (str_starts_with(Auth::user()->avatar_url, 'http') ? Auth::user()->avatar_url : asset('storage/' . Auth::user()->avatar_url)) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" 
                     alt="Avatar" 
                     class="w-10 h-10 rounded-full object-cover border-2">
            </div>
        </header>

        <main class="content-area">
            @if(session('success'))
                <div class="alert-success flex items-center gap-3 p-4 rounded-lg mb-6">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error flex items-center gap-3 p-4 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('settingsSidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('settingsSidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Check horizontal scroll on tables to show/hide shadow indicator
        function updateScrollIndicator() {
            document.querySelectorAll('.table-responsive').forEach(container => {
                if (container.scrollWidth > container.clientWidth) {
                    // Check if scrolled to the end
                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 5) {
                        container.classList.remove('is-scrollable');
                    } else {
                        container.classList.add('is-scrollable');
                    }
                } else {
                    container.classList.remove('is-scrollable');
                }
            });
        }

        window.addEventListener('resize', updateScrollIndicator);
        document.querySelectorAll('.table-responsive').forEach(container => {
            container.addEventListener('scroll', updateScrollIndicator);
        });
        
        // Initial check
        setTimeout(updateScrollIndicator, 100);
    </script>
    @stack('scripts')
</body>
</html>
