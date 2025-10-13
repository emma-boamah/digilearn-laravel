@extends('layouts.dashboard-simple')

@section('content')
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="shoutout-logo">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                        </svg>
                        <div class="brand-section sidebar-logo">
                            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
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

            <h1 class="page-title">Dashboard</h1>
        </div>
    </header>

    <!-- Back Button (now above header) -->
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
                    <div class="level-group-card">
                        <div class="level-header">
                            <h3 class="level-title">{{ $level['title'] }}</h3>
                        </div>
                        <div class="level-image-container">
                            @if($level['has_illustration'] && $level['id'] === 'jhs')
                                <div class="level-illustration">
                                    <!-- Illustration matching the mockup -->
                                    <svg width="120" height="80" viewBox="0 0 120 80" fill="none">
                                        <!-- Computer/Chart illustration -->
                                        <rect x="20" y="15" width="80" height="50" rx="4" fill="#E5E7EB" stroke="#9CA3AF"/>
                                        <rect x="25" y="20" width="70" height="35" fill="#F3F4F6"/>
                                        <!-- Chart elements -->
                                        <rect x="30" y="35" width="8" height="15" fill="#3B82F6"/>
                                        <rect x="42" y="30" width="8" height="20" fill="#EF4444"/>
                                        <rect x="54" y="25" width="8" height="25" fill="#10B981"/>
                                        <circle cx="75" r="8" fill="#F59E0B"/>
                                        <path d="M67 40 L83 40 L75 32 Z" fill="#8B5CF6"/>
                                        <!-- Person figure -->
                                        <circle cx="85" cy="45" r="6" fill="#F97316"/>
                                        <rect x="82" y="52" width="6" height="12" fill="#F97316"/>
                                        <rect x="79" y="58" width="4" height="8" fill="#1F2937"/>
                                        <rect x="87" y="58" width="4" height="8" fill="#1F2937"/>
                                    </svg>
                                </div>
                            @else
                                <div class="level-placeholder-image"></div>
                            @endif
                        </div>
                        <p class="level-description">{{ $level['description'] }}</p>
                        
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
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <style>

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

        .level-group-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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
            background-color: #DC2626;
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
            background-color: #B91C1C;
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
    });
</script>
@endpush