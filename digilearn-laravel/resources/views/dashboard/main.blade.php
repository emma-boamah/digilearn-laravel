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
                            <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                        </div>
                    </div>
                </div>
                
                <div class="header-right">
                    <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                    </svg>
                    
                    <div class="header-divider"></div>
                    
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
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
                    Selected Level: <strong style="color: var(--primary-red);">{{ ucwords(str_replace('-', ' ', $selectedLevel)) }}</strong>
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
                    <button class="card-button" onclick="window.location.href='{{ route('dashboard.digilearn') }}'">
                        Start Lessons
                    </button>
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
                    <button class="card-button" onclick="window.location.href='{{ route('dashboard.personalized') }}'">
                        Start
                    </button>
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
                    <button class="card-button" onclick="window.location.href='{{ route('dashboard.shop') }}'">
                        Shop now
                    </button>
                </div>
            </div>
        </div>
    </main>
@endsection
