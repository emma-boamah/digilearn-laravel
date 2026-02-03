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

    <style>
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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
        }

        .top-bar {
            height: calc(var(--header-height) + var(--safe-area-inset-top));
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--safe-area-inset-top) 2rem 0 2rem;
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
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
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
                padding: var(--safe-area-inset-top) 1rem 0 1rem;
                justify-content: flex-start;
            }
            .content-area {
                padding: 1rem;
            }
        }
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
                
                <a href="#" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Billing</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">Account Action</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; cursor: pointer; text-align: left; font-family: inherit; font-size: inherit;">
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
            
            <div style="flex: 1; display: flex; align-items: center;">
                <div class="breadcrumb">
                    Settings <span>/</span> @yield('breadcrumb', 'Overview')
                </div>
            </div>
            
            <div class="user-menu">
                <div style="text-align: right; display: none; @media(min-width: 640px){display: block;}">
                    <div style="font-weight: 500; font-size: 0.875rem;">{{ Auth::user()->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ Auth::user()->email }}</div>
                </div>
                <img src="{{ Auth::user()->avatar_url ? (str_starts_with(Auth::user()->avatar_url, 'http') ? Auth::user()->avatar_url : asset('storage/' . Auth::user()->avatar_url)) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" 
                     alt="Avatar" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
            </div>
        </header>

        <main class="content-area">
            @if(session('success'))
                <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
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
    </script>
    @stack('scripts')
</body>
</html>
