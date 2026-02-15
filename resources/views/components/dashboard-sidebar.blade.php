<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .sidebar-scrollable {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0; /* Important for flex children to scroll */
        gap: 0.5rem; /* Ensuring consistent gap between sections */
    }

    /* Ensure the last section does not get cut off */
    .sidebar-section:last-child {
        padding-bottom: 1rem;
    }
</style>

<!-- YouTube-style Sidebar -->
<aside class="youtube-sidebar" id="youtubeSidebar">
    <div class="sidebar-content">
        <!-- A wrapper for scrollable content -->
         <div class="sidebar-scrollable">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main</div>
                <a href="{{ route('dashboard.main') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard.main') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2M8 5a2 2 0 000 4h8a2 2 0 000-4M8 5v0"/>
                    </svg>
                    <span class="sidebar-menu-text">Dashboard</span>
                    <div class="tooltip">Dashboard</div>
                </a>
                <a href="{{ route('dashboard.digilearn') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard.digilearn') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="sidebar-menu-text">Lessons</span>
                    <div class="tooltip">Lessons</div>
                </a>
                <a href="{{ route('quiz.index') }}" class="sidebar-menu-item {{ request()->routeIs('quiz.index') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-menu-text">Quiz</span>
                    <div class="tooltip">Quiz</div>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Learning</div>
                <a href="/dashboard/my-progress" class="sidebar-menu-item {{ request()->is('dashboard/my-progress') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="sidebar-menu-text">My Progress</span>
                    <div class="tooltip">My Progress</div>
                </a>
                <a href="/dashboard/saved-lessons" class="sidebar-menu-item {{ request()->is('dashboard/saved-lessons') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span class="sidebar-menu-text">Saved Lessons</span>
                    <div class="tooltip">Saved Lessons</div>
                </a>
                <a href="/dashboard/my-projects" class="sidebar-menu-item {{ request()->is('dashboard/my-projects') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="sidebar-menu-text">My Projects</span>
                    <div class="tooltip">My Projects</div>
                </a>
                <a href="{{ route('dashboard.notes') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard.notes') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="sidebar-menu-text">My Notes</span>
                    <div class="tooltip">My Notes</div>
                </a>
                <a href="{{ route('dashboard.join-class') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard.join-class') ? 'active' : '' }}">
                    <!-- Classroom SVG Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="5" width="18" height="12" rx="2" stroke-width="2" stroke="currentColor" fill="none"/>
                        <circle cx="8" cy="15" r="1.5" stroke-width="2" stroke="currentColor" fill="none"/>
                        <circle cx="12" cy="15" r="1.5" stroke-width="2" stroke="currentColor" fill="none"/>
                        <circle cx="16" cy="15" r="1.5" stroke-width="2" stroke="currentColor" fill="none"/>
                        <path d="M8 11v-2a2 2 0 1 1 4 0v2" stroke-width="2" stroke="currentColor" fill="none"/>
                    </svg>
                    <span>Join Your Class</span>
                    <span class="badge bg-red-500" id="classAlertBadge"></span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="{{ route('dashboard.notifications') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard.notifications') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="sidebar-menu-text">Notifications</span>
                    <div class="tooltip">Notifications</div>
                </a>
                <a href="{{ route('settings.billing') }}" class="sidebar-menu-item {{ request()->routeIs('settings.billing') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span class="sidebar-menu-text">Subscription & Billing</span>
                    <div class="tooltip">Subscription & Billing</div>
                </a>
            </div>
         </div>
    </div>
</aside>
