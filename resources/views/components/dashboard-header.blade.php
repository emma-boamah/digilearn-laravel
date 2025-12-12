<!-- Dashboard Header Styles -->
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Top Header */
    .top-header {
        display: flex;
        align-items: center;
        height: 60px;
        background-color: var(--white);
        border-bottom: 1px solid var(--gray-200);
        position: sticky;
        top: 0;
        z-index: 999;
        backdrop-filter: blur(8px);
        background-color: rgba(255, 255, 255, 0.95);
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
        overflow: hidden;
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
        height: calc(100vh - 64px);
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
        overflow-x: hidden;
    }

    .youtube-sidebar.collapsed ~ .main-content {
        width: calc(100vw - var(--sidebar-width-collapsed));
        max-width: calc(100vw - var(--sidebar-width-collapsed));
        margin-left: var(--sidebar-width-collapsed);
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
    });
    </script>

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

        <x-user-avatar :user="auth()->user()" :size="36" class="border-2 border-white"/>
    </div>
</div>