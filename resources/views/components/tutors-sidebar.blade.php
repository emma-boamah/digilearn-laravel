<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .sidebar-scrollable {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        gap: 0.5rem;
        width: 100%;
        overflow: hidden;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .youtube-sidebar.collapsed .sidebar-scrollable {
        width: var(--sidebar-width-collapsed, 72px);
        overflow: hidden;
    }

    .sidebar-section:last-child {
        padding-bottom: 1rem;
    }
</style>

<aside class="youtube-sidebar" id="youtubeSidebar">
    <div class="sidebar-content">
        <div class="sidebar-scrollable">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main</div>
                <a href="{{ route('home', ['show_home' => 'true']) }}" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="sidebar-menu-text">Home</span>
                    <div class="tooltip">Home</div>
                </a>
                <a href="{{ route('dashboard.main') }}"
                    class="sidebar-menu-item {{ request()->routeIs('dashboard.main') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2M8 5a2 2 0 000 4h8a2 2 0 000-4M8 5v0" />
                    </svg>
                    <span class="sidebar-menu-text">Learning Hub</span>
                    <div class="tooltip">Learning Hub</div>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Personalized</div>
                <a href="{{ route('tutors.index') }}"
                    class="sidebar-menu-item {{ request()->routeIs('tutors.index') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="sidebar-menu-text">Find Tutors</span>
                    <div class="tooltip">Find Tutors</div>
                </a>
                @if(!auth()->user()->tutorProfile)
                    <a href="{{ route('tutors.apply') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.apply') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="sidebar-menu-text">Become a Tutor</span>
                        <div class="tooltip">Become a Tutor</div>
                    </a>
                @else
                    <a href="{{ route('tutors.dashboard') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.dashboard') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="sidebar-menu-text">Tutor Overview</span>
                        <div class="tooltip">Tutor Overview</div>
                    </a>
                    <a href="{{ route('tutors.schedule.calendar') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.schedule.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="sidebar-menu-text">Schedule & Calendar</span>
                        <div class="tooltip">Schedule & Calendar</div>
                    </a>
                    <a href="{{ route('tutors.bookings.index') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.bookings.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="sidebar-menu-text">Bookings Manager</span>
                        <div class="tooltip">Bookings Manager</div>
                    </a>
                    <a href="{{ route('tutors.content.index') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.content.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="sidebar-menu-text">Content Studio</span>
                        <div class="tooltip">Content Studio</div>
                    </a>
                    <a href="{{ route('tutors.earnings.index') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.earnings.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 8v2m0-6c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="sidebar-menu-text">Wallet & Earnings</span>
                        <div class="tooltip">Wallet & Earnings</div>
                    </a>
                    <a href="{{ route('tutors.analytics.index') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.analytics.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="sidebar-menu-text">Analytics</span>
                        <div class="tooltip">Analytics</div>
                    </a>
                    <a href="{{ route('tutors.profile.settings') }}"
                        class="sidebar-menu-item {{ request()->routeIs('tutors.profile.settings') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="sidebar-menu-text">Profile Settings</span>
                        <div class="tooltip">Profile Settings</div>
                    </a>
                @endif
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="{{ route('settings.billing') }}"
                    class="sidebar-menu-item {{ request()->routeIs('settings.billing') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="sidebar-menu-text">Subscription & Billing</span>
                    <div class="tooltip">Subscription & Billing</div>
                </a>
            </div>
        </div>
    </div>
</aside>
