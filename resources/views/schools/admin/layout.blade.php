<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $school->name }} Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --accent: #E11E2D;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --sidebar-w: 260px;
            --success: #059669;
            --warning: #d97706;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* Sidebar */
        .sa-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sa-sidebar-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .sa-school-identity {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sa-school-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid var(--border);
        }

        .sa-school-logo-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sa-school-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        .sa-school-badge {
            font-size: 0.7rem;
            color: var(--success);
            font-weight: 500;
        }

        .sa-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .sa-nav-section {
            margin-bottom: 24px;
        }

        .sa-nav-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .sa-nav-item:hover {
            background: rgba(37, 99, 235, 0.06);
            color: var(--primary);
        }

        .sa-nav-item.active {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            font-weight: 600;
        }

        .sa-nav-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sa-sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
        }

        .sa-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sa-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .sa-user-name {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .sa-user-role {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Main Content */
        .sa-main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sa-topbar {
            height: 64px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .sa-topbar-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .sa-topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sa-content {
            padding: 32px;
        }

        /* Mobile Toggle */
        .sa-mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            color: var(--text);
        }

        .sa-mobile-toggle:hover {
            background: var(--bg);
        }

        .sa-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 99;
        }

        /* Buttons */
        .sa-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .sa-btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .sa-btn-primary:hover {
            background: var(--primary-dark);
        }

        .sa-btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .sa-btn-outline:hover {
            background: var(--bg);
        }

        .sa-btn-danger {
            background: transparent;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .sa-btn-danger:hover {
            background: #fef2f2;
        }

        .sa-btn-sm {
            padding: 6px 14px;
            font-size: 0.8rem;
        }

        /* Alert Messages */
        .sa-alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .sa-alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .sa-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sa-sidebar {
                transform: translateX(-100%);
            }

            .sa-sidebar.open {
                transform: translateX(0);
            }

            .sa-overlay.open {
                display: block;
            }

            .sa-main {
                margin-left: 0;
            }

            .sa-mobile-toggle {
                display: flex;
            }

            .sa-content {
                padding: 20px 16px;
            }

            .sa-topbar {
                padding: 0 16px;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Mobile Overlay -->
    <div class="sa-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sa-sidebar" id="sidebar">
        <div class="sa-sidebar-header">
            <div class="sa-school-identity">
                @if($school->logo)
                    <img src="{{ secure_asset('storage/' . $school->logo) }}" alt="{{ $school->name }}"
                        class="sa-school-logo">
                @else
                    <div class="sa-school-logo-placeholder">{{ substr($school->name, 0, 1) }}</div>
                @endif
                <div>
                    <div class="sa-school-name">{{ $school->name }}</div>
                    <div class="sa-school-badge">
                        <i class="fas fa-circle" style="font-size: 6px; margin-right: 4px;"></i>
                        {{ ucfirst($school->status) }}
                    </div>
                </div>
            </div>
        </div>

        <nav class="sa-nav">
            @if(auth()->user()->hasRole('school-admin'))
            <div class="sa-nav-section">
                <div class="sa-nav-label">Overview</div>
                <a href="{{ route('school.admin.dashboard') }}"
                    class="sa-nav-item {{ request()->routeIs('school.admin.dashboard') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>
            </div>
            @endif

            <div class="sa-nav-section">
                <div class="sa-nav-label">Content</div>
                <a href="{{ route('school.studio.index') }}"
                    class="sa-nav-item {{ request()->routeIs('school.studio.*') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg>
                    Content Studio
                </a>
            </div>

            @if(auth()->user()->hasRole('school-admin'))
            <div class="sa-nav-section">
                <div class="sa-nav-label">Management</div>
                <a href="{{ route('school.admin.users') }}"
                    class="sa-nav-item {{ request()->routeIs('school.admin.users*') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Users
                </a>
                <a href="{{ route('school.admin.users.invite') }}"
                    class="sa-nav-item {{ request()->routeIs('school.admin.users.invite') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                    Invite User
                </a>
            </div>

            <div class="sa-nav-section">
                <div class="sa-nav-label">Configuration</div>
                <a href="{{ route('school.admin.academic.setup') }}" class="sa-nav-item {{ request()->routeIs('school.admin.academic.setup') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Academic Setup
                </a>
                <a href="{{ route('school.admin.settings') }}" class="sa-nav-item {{ request()->routeIs('school.admin.settings') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Settings
                </a>
                <a href="{{ route('school.admin.billing') }}" class="sa-nav-item {{ request()->routeIs('school.admin.billing*') ? 'active' : '' }}">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Billing & Subscription
                </a>
            </div>
            @endif

            <div class="sa-nav-section" style="margin-top: auto;">
                <a href="{{ route('home') }}" class="sa-nav-item">
                    <svg class="sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                    </svg>
                    Back to ShoutOutGH
                </a>
            </div>
        </nav>

        <div class="sa-sidebar-footer">
            <div class="sa-user-info">
                <div class="sa-user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <div>
                    <div class="sa-user-name">{{ auth()->user()->name }}</div>
                    <div class="sa-user-role">
                        {{ auth()->user()->hasRole('school-admin') ? 'School Admin' : 'Teacher' }}
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="sa-main">
        <div class="sa-topbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="sa-mobile-toggle" onclick="toggleSidebar()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="sa-topbar-title">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="sa-topbar-actions">
                @yield('topbar-actions')
            </div>
        </div>

        <div class="sa-content">
            @if(session('success'))
                <div class="sa-alert sa-alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="sa-alert sa-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }
    </script>
    @yield('scripts')
</body>

</html>