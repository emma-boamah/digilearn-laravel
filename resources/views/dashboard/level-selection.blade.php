@extends('layouts.dashboard-simple')

@section('content')
    <!-- Back Button -->
    @php
        $referrer = request()->headers->get('referer');
        $isFromDigilearn = $referrer && str_contains($referrer, '/dashboard/digilearn');
        $isFromLessonView = $referrer && str_contains($referrer, '/dashboard/lesson/');
    @endphp

    @if(isset($isChanging) && $isChanging)
        <a href="{{ route('dashboard.main') }}" class="back-button" id="backToDashboard">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    @elseif($isFromDigilearn)
        <a href="{{ route('dashboard.digilearn') }}" class="back-button" id="backToDigilearn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to DigiLearn
        </a>
    @else
        <button class="back-button" id="backButton">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </button>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Level Selection Grid -->
            <div class="level-selection-grid">
                @foreach($levels as $level)
                    @php
                        $hasAccess = $accessInfo[$level['id']] ?? false;
                    @endphp
                    <div class="level-group-card {{ $hasAccess ? 'accessible' : 'upgrade-needed' }}">
                        <div class="level-header">
                            <h3 class="level-title">{{ $level['title'] }}</h3>
                            @if(!$hasAccess)
                                <div class="premium-badge">Premium</div>
                            @endif
                        </div>
                        <div class="level-image-container">
                            @if($level['id'] === 'jhs')
                                <img src="{{ asset('images/jhs.jpeg') }}" alt="JHS" class="level-jhs-image">
                            @elseif($level['id'] === 'shs')
                                <img src="{{ asset('images/SHS.png') }}" alt="SHS" class="level-shs-image">
                            @elseif($level['id'] === 'primary-upper')
                                <img src="{{ asset('images/g4-6.jpeg') }}" alt="Grade 4-6" class="level-g4-6-image">
                            @elseif($level['id'] === 'university')
                                <img src="{{ asset('images/university.jpeg') }}" alt="University" class="level-university-image">
                            @else
                                <div class="level-placeholder-image"></div>
                            @endif
                        </div>
                        <p class="level-description">{{ $level['description'] }}</p>

                        @if($hasAccess)
                            <!-- Form to select level group and go to digilearn -->
                            <form action="{{ route('dashboard.select-level-group', $level['id']) }}" method="POST">
                                @csrf
                                <button type="submit" class="level-select-btn">
                                    @if(session('selected_level_group') === $level['id'])
                                        Current Level
                                    @else
                                        Select
                                    @endif
                                </button>
                            </form>
                        @else
                            <!-- Allow clicking but redirect to pricing -->
                            <button type="button" class="upgrade-btn upgrade-trigger" data-level-title="{{ $level['title'] }}" data-level-id="{{ $level['id'] }}">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                Upgrade to Access
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">

        a {
            text-decoration: none;
        }

        /* New header structure styles */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0.5rem 0;
        }
        
        .header-logo, .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-title {
            text-align: center;
            padding: 0.75rem 0;
            color: #DC2626;
            font-size: 1.25rem;
            font-weight: 600;
            border-top: 1px solid #e5e7eb;
            margin-top: 0.5rem;
        }
        
        /* Existing level grid styles */
        .level-selection-grid {
            /* ... existing styles ... */
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .back-button {
                padding: 0.75rem 1rem;
                font-size: 1rem;
                width: 100%;
                justify-content: left;
                background: #f3f4f6;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .header {
                padding: 0.5rem 0;
            }
            
            .header-content {
                padding: 0.5rem 1rem;
            }
            
            .shoutout-logo img {
                height: 32px;
            }
            
            .notification-icon {
                width: 20px;
                height: 20px;
            }
            
            .user-avatar {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
        }
        .level-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .level-group-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }

        .level-group-card:hover:not(.disabled) {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .level-group-card.upgrade-needed {
            border: 2px solid #fbbf24; /* Yellow border for premium content */
            background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%);
            position: relative;
        }

        .level-group-card.upgrade-needed:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3);
            border-color: #f59e0b;
        }

        .level-group-card.accessible {
            border: 2px solid #eaf4faff; /* Green border for accessible content */
        }

        .level-group-card.accessible:hover {
            border-color: #dceef8ff;
        }

        .premium-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        .level-header {
            margin-bottom: 1rem;
        }

        .level-title {
            color: #DC2626;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .level-image-container {
            width: 100%;
            height: 160px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            overflow: hidden;
        }

        .level-placeholder-image {
            width: 100%;
            height: 100%;
            background-color: #A1A1AA;
            border-radius: 8px;
        }

        .level-jhs-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            object-position: 50% 30%;
        }

        .level-shs-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            object-position: 50% 10%;
        }

        .level-g4-6-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .level-university-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            object-position: 50% 40%;
        }

        .level-illustration {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-color: #F8FAFC;
            border-radius: 8px;
            border: 2px solid #E2E8F0;
        }

        .level-description {
            color: #6B7280;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        .level-select-btn {
            background-color: #2677B8;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            width: 100%;
            font-size: 1rem;
        }

        .level-select-btn:hover {
            background-color: #1e5a8a;
        }

        .upgrade-required {
            margin-top: 1rem;
        }

        .upgrade-btn {
            background-color: #dc2626;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            width: 100%;
            font-size: 1rem;
        }

        .upgrade-btn:hover {
            background-color: #b91c1c;
        }

        @media (max-width: 768px) {
            .level-selection-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1rem 0;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .level-selection-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .level-selection-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        // Handle back button navigation - check for any back button element
        const backButton = document.getElementById('backButton') ||
                          document.getElementById('backToDashboard') ||
                          document.getElementById('backToDigilearn');

        if (backButton) {
            console.log('Back button found:', backButton.id);
            // Only add click handler for actual button elements (not links)
            if (backButton.tagName.toLowerCase() === 'button') {
                backButton.addEventListener('click', function() {
                    console.log('Back button clicked');
                    window.history.back();
                });
            } else {
                console.log('Back button is a link, letting browser handle navigation');
            }
        } else {
            console.log('No back button found');
        }

        // Handle upgrade triggers
        const upgradeTriggers = document.querySelectorAll('.upgrade-trigger');
        upgradeTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                const levelTitle = this.getAttribute('data-level-title');
                const levelId = this.getAttribute('data-level-id');
                handleUpgradeRequired(levelTitle, levelId);
            });
        });
    });

    function redirectToPricing() {
        window.location.href = '{{ route("pricing") }}';
    }

    function handleUpgradeRequired(levelTitle, levelId) {
        // Find the button that was clicked
        const button = event.target.closest('.upgrade-trigger');
        const originalText = button.innerHTML;

        // Show loading state
        button.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>Redirecting...';
        button.disabled = true;

        // Add a subtle animation
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 100);

        // Log the upgrade attempt
        console.log('User attempting to access premium level:', levelTitle, 'ID:', levelId);

        // Track this interaction (could send to analytics)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'upgrade_prompt_shown', {
                level_title: levelTitle,
                level_id: levelId
            });
        }

        // Redirect to pricing after a brief delay
        setTimeout(() => {
            window.location.href = '{{ route("pricing") }}';
        }, 800);
    }
</script>
@endpush