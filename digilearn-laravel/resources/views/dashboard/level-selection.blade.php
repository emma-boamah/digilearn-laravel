@extends('layouts.dashboard-simple')

@section('content')
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    @if(isset($isChanging) && $isChanging)
                        <button class="back-button" id="backToDashboard">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Dashboard
                        </button>
                    @else
                        <button class="back-button" id="backButton">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back
                        </button>
                    @endif
                    
                    <div class="shoutout-logo">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                        </svg>
                        <div class="brand-section sidebar-logo">
                            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                        </div>
                    </div>
                </div>
                
                <h1 class="page-title">
                    @if(isset($isChanging) && $isChanging)
                        Change Level
                    @else
                        Select Your Level
                    @endif
                </h1>
                
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

    @if(isset($isChanging) && $isChanging)
        <!-- Current Level Info -->
        <div style="background-color: var(--gray-50); padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
            <div class="container">
                <div style="text-align: center;">
                    <span style="color: var(--gray-600); font-size: 0.875rem;">
                        Current Level: <strong style="color: var(--primary-red);">{{ ucwords(str_replace('-', ' ', session('selected_level', 'None'))) }}</strong>
                    </span>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @if(!isset($isChanging) || !$isChanging)
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">
                        Welcome to DigiLearn!
                    </h2>
                    <p style="color: var(--gray-600); font-size: 1.125rem;">
                        Please select your education level to get started with personalized learning content.
                    </p>
                </div>
            @endif

            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="four-column-grid">
                @foreach($levels as $level)
                <div class="card level-card" style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-image">
                        @if($level['has_illustration'])
                            <img src="https://via.placeholder.com/400x200/3B82F6/ffffff?text=Educational+Illustration" alt="{{ $level['title'] }}">
                        @else
                            <img src="https://via.placeholder.com/400x200/9CA3AF/ffffff?text={{ urlencode($level['title']) }}" alt="{{ $level['title'] }}">
                        @endif
                    </div>
                    <h3 class="card-title">{{ $level['title'] }}</h3>
                    <p class="card-description">{{ $level['description'] }}</p>
                    <form action="{{ route('dashboard.select-level', $level['id']) }}" method="POST">
                        @csrf
                        <button type="submit" class="card-button">
                            @if(session('selected_level') === $level['id'])
                                Current Level
                            @else
                                Select
                            @endif
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </main>

    <style>
        /* Added CSS for button hover effect */
        .card-button {
            background-color: var(--primary-red);
            transition: background-color 0.2s ease;
        }
        
        .card-button:hover {
            background-color: var(--primary-red-hover);
        }
        
        .level-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .four-column-grid {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .four-column-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .four-column-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        // Handle back button navigation
        const backButton = document.getElementById('backButton');
        if (backButton) {
            backButton.addEventListener('click', function() {
                window.history.back();
            });
        }

        // Handle dashboard back navigation
        const backToDashboard = document.getElementById('backToDashboard');
        if (backToDashboard) {
            backToDashboard.addEventListener('click', function() {
                window.location.href = '{{ route("dashboard.main") }}';
            });
        }

        // Add visual feedback to level cards
        const levelCards = document.querySelectorAll('.level-card');
        levelCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Only add effect if clicking the card itself (not the button)
                if (!e.target.closest('.card-button')) {
                    this.style.transform = 'scale(0.98)';
                    this.style.opacity = '0.9';
                    
                    setTimeout(() => {
                        this.style.transform = '';
                        this.style.opacity = '';
                    }, 200);
                }
            });
        });
    });
</script>
@endpush