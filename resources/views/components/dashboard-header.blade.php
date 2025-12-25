<!-- Dashboard Header Styles -->
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Top Header */
    .top-header {
        display: flex;
        align-items: center;
        height: 60px;
        background-color: rgba(255, 255, 255, 0.8); /* More transparent */
        backdrop-filter: blur(12px) saturate(180%); /* Enhanced glassmorphism */
        -webkit-backdrop-filter: blur(12px) saturate(180%); /* Safari support */
        border-bottom: 1px solid rgba(229, 231, 235, 0.6);
        position: fixed;
        top: 0;
        left: 0; /* Changed to 0 for full width */
        width: 100vw; /* Full viewport width */
        z-index: 999;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        /* padding-left handled in including files */
    }

    .youtube-sidebar.collapsed ~ .top-header {
        padding-left: var(--sidebar-width-collapsed) !important;
    }

    .header-left {
        display: flex;
        align-items: center;
        padding: 0 1.5rem;
        border-right: 1px solid var(--gray-200);
        height: 100%;
    }

    .header-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 0 1.5rem;
        gap: 1rem;
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

    .sidebar-logo img {
        height: 32px;
        width: auto;
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

    .notification-dropdown {
        position: relative;
    }

    .notification-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: var(--white);
        border-radius: 0.75rem;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--gray-200);
        width: 380px;
        max-width: 90vw;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }

    .notification-dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-header {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .notification-dropdown-menu .dropdown-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .notification-dropdown-menu .dropdown-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .mark-all-read {
        background: none;
        border: none;
        color: var(--primary-red);
        font-size: 0.875rem;
        cursor: pointer;
        font-weight: 500;
    }

    .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-100);
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background: var(--gray-50);
    }

    .notification-item.unread {
        background: rgba(59, 130, 246, 0.05);
    }

    .notification-item.unread:hover {
        background: rgba(59, 130, 246, 0.08);
    }

    .notification-content {
        flex: 1;
    }

    .notification-content p {
        margin: 0 0 0.25rem 0;
        font-size: 0.875rem;
        color: var(--gray-800);
        line-height: 1.4;
    }

    .notification-time {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .notification-icon {
        width: 20px;
        height: 20px;
        stroke-width: 2;
        border-radius: 50%;
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--gray-600);
    }

    .notification-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: var(--primary-red);
        color: var(--white);
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--white);
    }

    .dropdown-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        text-align: center;
    }

    .view-all {
        color: var(--primary-red);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .view-all:hover {
        text-decoration: underline;
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
        overflow-y: auto;
    }

    .youtube-sidebar::-webkit-scrollbar {
        width: 8px;
    }

    .youtube-sidebar::-webkit-scrollbar-thumb {
        background-color: rgba(232, 237, 253, 0.2);
        border-radius: 4px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .youtube-sidebar:hover::-webkit-scrollbar-thumb {
        opacity: 1;
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
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        height: auto;
        max-height: 100vh;
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
    }

    .youtube-sidebar.collapsed ~ .main-content {
        width: calc(100vw - var(--sidebar-width-collapsed));
        max-width: calc(100vw - var(--sidebar-width-collapsed));
        margin-left: var(--sidebar-width-collapsed);
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            padding: 0.5rem;
            height: auto;
        }

        .header-right {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-right: 0;
            padding-left: 0;
        }

        .main-content{
            margin-left: 0;
            max-width: 100vw;
        
        }
    }
</style>

<!-- Top Header -->
<div class="top-header">
    <div class="header-left">
        <!-- Hamburger for sidebar toggle (always visible) -->
        <button class="sidebar-toggle-btn" id="sidebarToggle">
            <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="sidebar-logo">
            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
        </div>
    </div>
    
    <!-- Dark Mode Toggle Script -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggledarkmodebutton');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;
    
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme') || 'light';
        setTheme(savedTheme);
    
        // Toggle theme on button click
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                const currentTheme = body.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                setTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }
    
        function setTheme(theme) {
            if (theme === 'dark') {
                body.setAttribute('data-theme', 'dark');
                if (themeIcon) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
                if (toggleButton) {
                    toggleButton.setAttribute('title', 'Switch to Light Mode');
                }
            } else {
                body.removeAttribute('data-theme');
                if (themeIcon) {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
                if (toggleButton) {
                    toggleButton.setAttribute('title', 'Switch to Dark Mode');
                }
            }
        }

        // User dropdown functionality
        const userDropdownToggle = document.getElementById('userDropdownToggle');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdownToggle && userDropdownMenu) {
            userDropdownToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                const isExpanded = userDropdownToggle.getAttribute('aria-expanded') === 'true';
                userDropdownToggle.setAttribute('aria-expanded', !isExpanded);
                userDropdownMenu.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownToggle.setAttribute('aria-expanded', 'false');
                    userDropdownMenu.classList.remove('active');
                }
            });

            // Close dropdown on escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && userDropdownMenu.classList.contains('active')) {
                    userDropdownToggle.setAttribute('aria-expanded', 'false');
                    userDropdownMenu.classList.remove('active');
                }
            });
        }
    });
    </script>

    <!-- Sidebar and User Dropdown Scripts -->
    <!-- <script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.youtube-sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (sidebar && sidebarToggle) {
            function toggleSidebar() {
                sidebar.classList.toggle('collapsed');
            }

            sidebarToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleSidebar();
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 769 && !sidebar.classList.contains('collapsed')) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.add('collapsed');
                    }
                }
            });
        }
    });
    </script> -->

    <div class="header-right">
        <!-- Dark Mode Toggle -->
        <button class="notification-btn" id="toggledarkmodebutton" title="Toggle Dark Mode">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>

        <button class="notification-btn" id="notificationButton">
            <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
        </button>

        <!-- Notification dropdown in the header -->
        <div class="notification-dropdown">
            <div class="notification-dropdown-menu" id="notificationDropdown">
                <div class="dropdown-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read">Mark all as read</button>
                </div>
                <div class="notification-list">
                    <!-- Notifications will be populated here -->
                    <div class="notification-item unread">
                        <div class="notification-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <p>New follower: John Doe started following you</p>
                            <span class="notification-time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="notification-content">
                            <p>Congratulations! You completed the Math challenge</p>
                            <span class="notification-time">1 day ago</span>
                        </div>
                    </div>
                </div>
                <div class="dropdown-footer">
                    <a href="{{ route('dashboard.notifications') }}" class="view-all">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- User Avatar Dropdown -->
        <div class="user-dropdown">
            <button class="user-avatar-btn" id="userDropdownToggle" aria-haspopup="true" aria-expanded="false">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="user-avatar">
                @else
                    <div class="user-avatar">
                        <span>{{ auth()->user()->getInitialsAttribute() }}</span>
                    </div>
                @endif
            </button>

            <div class="user-dropdown-menu" id="userDropdownMenu">
                <div class="user-dropdown-header">
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="user-dropdown-body">
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="dropdown-item-form">
                        @csrf
                        <button type="submit" class="dropdown-item logout-btn">
                            <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
