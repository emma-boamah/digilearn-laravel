<!-- Mobile Header -->
<div class="mobile-header">
    <div class="mobile-header-content">
        <div class="mobile-header-left">
            <button class="mobile-hamburger" id="mobileSidebarToggle">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" 
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="mobile-logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                <span class="sidebar-brand">Quiz</span>
            </div>
        </div>
        <div class="mobile-header-right">
            <button class="notification-btn">
                <svg class="notification-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v0.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
            <x-user-avatar :user="auth()->user()" :size="32" class="border-2 border-white" />
        </div>
    </div>
</div>
