@extends('layouts.dashboard-simple')

@section('content')
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <div class="digilearn-logo">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                        </svg>
                    </div>
                    
                    <div class="shoutout-logo">
                        <div class="brand-section sidebar-logo">
                            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                        </div>
                    </div>
                </div>
                
                <div class="header-right">
                    <button class="notification-btn">
                        <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v0.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                    
                    <div class="header-divider"></div>
                    
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
        </div>
    </header>

    <!-- Selected Level Info -->
    <div style="background-color: var(--gray-50); padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: var(--gray-600); font-size: 0.875rem;">
                    Selected Level: <strong style="color: var(--primary-red);">{{ ucwords(str_replace('-', ' ', $selectedLevelGroup)) }}</strong>
                </span>
                <a href="{{ route('dashboard.change-level') }}" style="color: var(--secondary-blue); font-size: 0.875rem; text-decoration: none;">
                    Change Level
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="three-column-grid">
                <!-- DigiLearn Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="https://via.placeholder.com/400x200/9CA3AF/ffffff?text=DigiLearn" alt="DigiLearn">
                    </div>
                    <h3 class="card-title">DigiLearn</h3>
                    <p class="card-description">
                        Practical, Demonstrative, Educative, Informative and Edutainment lessons which aids students to understand topics and with ease 21st Century tutoring approach
                    </p>
                    <a href="{{ route('dashboard.digilearn') }}" class="card-button" style="display: inline-block; text-decoration: none; text-align: center;">
                        Start Lessons
                    </a>
                </div>

                <!-- Personalized Learning Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="https://via.placeholder.com/400x200/3B82F6/ffffff?text=Personalized+Learning" alt="Personalized Learning">
                    </div>
                    <h3 class="card-title">Personalized learning</h3>
                    <p class="card-description">
                        Learn at your own pace with our tutors and explore more educative videos on personalized learning. Get the chance to schedule time with tutors.
                    </p>
                    <a href="{{ route('dashboard.personalized') }}" class="card-button" style="display: inline-block; text-decoration: none; text-align: center;">
                        Start
                    </a>
                </div>

                <!-- Shop Section -->
                <div class="card">
                    <div class="card-image">
                        <img src="https://via.placeholder.com/400x200/3B82F6/ffffff?text=Shop" alt="Shop">
                    </div>
                    <h3 class="card-title">Shop</h3>
                    <p class="card-description">
                        Purchase all your student needs here. Items are affordable and drastically reduced to suit your financial equilibrium.
                    </p>
                    <a href="{{ route('dashboard.shop') }}" class="card-button" style="display: inline-block; text-decoration: none; text-align: center;">
                        Shop now
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
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

            // Debug function to test route
            function testDigiLearnRoute() {
                console.log('DigiLearn route:', '{{ route('dashboard.digilearn') }}');
                console.log('Current URL:', window.location.href);
            }
            
            // Call debug function
            testDigiLearnRoute();
        })

    </script>
@endsection