<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - ShoutoutGH</title>

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

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
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
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

        .space-x-4 > * + * {
            margin-left: 1rem;
        }

        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }

        /* Sidebar styles */
        .sidebar {
            background-color: var(--gray-900);
            color: var(--white);
            width: 16rem;
            /* Made sidebar fixed position and full height */
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            padding: 1rem;
            transition: all 0.3s ease-in-out;
            z-index: 40;
        }

        .sidebar.collapsed {
            width: 4rem;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 4rem;
        }

        .sidebar-link-text {
            display: block;
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

        /* Navigation styles */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            color: var(--white);
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .nav-link:hover {
            background-color: var(--gray-800);
        }

        .nav-link.active {
            background-color: var(--primary-blue);
        }

        .nav-link i {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
        }

        /* Header styles */
        .header {
            background-color: var(--white);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--gray-200);
        }

        .header-content {
            max-width: 80rem;
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
            margin-left: 16rem;
            transition: margin-left 0.3s ease-in-out;
        }

        .content-wrapper {
            max-width: 80rem;
            margin: 0 auto;
            padding: 1.5rem;
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

        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-auto { margin-top: auto; }
        .mr-3 { margin-right: 0.75rem; }
        .ml-2 { margin-left: 0.5rem; }
        .ml-6 { margin-left: 1.5rem; }

        .p-2 { padding: 0.5rem; }
        .p-4 { padding: 1rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .pt-4 { padding-top: 1rem; }

        .w-5 { width: 1.25rem; }
        .w-6 { width: 1.5rem; }
        .w-8 { width: 2rem; }
        .h-5 { height: 1.25rem; }
        .h-6 { height: 1.5rem; }
        .h-8 { height: 2rem; }

        .rounded { border-radius: 0.25rem; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-full { border-radius: 9999px; }

        .border-t { border-top: 1px solid var(--gray-700); }

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
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar">
            <div class="flex items-center mb-8 justify-between">
                <div class="flex items-center">
                    <div class="logo">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold sidebar-link-text">ShoutoutGH Admin</h1>
                </div>
                <button id="sidebar-toggle" class="sidebar-toggle-btn">
                    <i class="fas fa-chevron-left sidebar-toggle-icon"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>

                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-link-text">Users</span>
                </a>

                <!-- Student Progress Management -->
                <a href="{{ route('admin.progress.overview') }}" class="nav-link {{ request()->routeIs('admin.progress*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span class="sidebar-link-text">Student Progress</span>
                </a>

                <!-- Added notifications link to sidebar -->
                <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span class="sidebar-link-text">Notifications</span>
                </a>

                <!-- Pricing Plans -->
                <a href="{{ route('admin.pricing.index') }}" class="nav-link {{ request()->routeIs('admin.pricing*') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i>
                    <span class="sidebar-link-text">Pricing Plans</span>
                </a>

                <!-- Simplified Contents Section -->
                <a href="{{ route('admin.contents.index') }}" class="nav-link {{ request()->routeIs('admin.contents*') ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i>
                    <span class="sidebar-link-text">Contents</span>
                </a>

                <a href="{{ route('admin.revenue') }}" class="nav-link {{ request()->routeIs('admin.revenue*') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i>
                    <span class="sidebar-link-text">Revenue Analytics</span>
                </a>

                <a href="#" class="nav-link">
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

                @if(Auth::user()->is_superuser)
                <a href="{{ route('admin.classes.create') }}" class="nav-link {{ request()->routeIs('admin.classes.create') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span class="sidebar-link-text">Create Class</span>
                </a>
                @endif
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

                            <!-- Notifications -->
                            <div class="dropdown" id="notificationDropdown">
                                <button id="notificationDropdownBtn" class="notification-btn">
                                    <i class="fas fa-bell w-6 h-6"></i>
                                    <span class="notification-badge"></span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-wide">
                                    <div class="notification-header">
                                        <strong>Recent Notifications</strong>
                                    </div>
                                    <a href="#" class="dropdown-item">
                                        <div class="notification-title">New user registered</div>
                                        <div class="notification-subtitle">john@example.com - 2 minutes ago</div>
                                    </a>
                                    <a href="#" class="dropdown-item">
                                        <div class="notification-title">Security alert</div>
                                        <div class="notification-subtitle">Multiple failed login attempts - 5 minutes ago</div>
                                    </a>
                                </div>
                            </div>

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
                @if(session('success'))
                    <div class="alert alert-success">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <ul class="error-list">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
        document.addEventListener('click', function(event) {
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
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('#sidebar-toggle');
            const mainContent = document.querySelector('.main-content');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-chevron-left');
                    icon.classList.toggle('fa-chevron-right');
                });

                // Adjust main content margin when sidebar toggles 
                if (sidebar.classList.contains('collapsed')) {
                    mainContent.style.marginLeft = '4rem';
                } else {
                    mainContent.style.marginLeft = '16rem';
                }
            }

            // Content dropdown functionality removed - now using simple navigation

            const notificationDropdownBtn = document.getElementById('notificationDropdownBtn');
            if (notificationDropdownBtn) {
                notificationDropdownBtn.addEventListener('click', function() {
                    toggleDropdown('notificationDropdown');
                });
            }

            const userDropdownBtn = document.getElementById('userDropdownBtn');
            if (userDropdownBtn) {
                userDropdownBtn.addEventListener('click', function() {
                    toggleDropdown('userDropdown');
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
    @stack('scripts')
<script src="/js/avatar-updater.js"></script>


<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Header upload modal functionality
    document.addEventListener('DOMContentLoaded', function() {
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

</body>
</html>
