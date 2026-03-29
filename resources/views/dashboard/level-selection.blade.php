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
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Back to Dashboard
</a>
@elseif($isFromDigilearn)
<a href="{{ route('dashboard.digilearn') }}" class="back-button" id="backToDigilearn">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Back to DigiLearn
</a>
@else
<button class="back-button" id="backButton">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Back
</button>
@endif

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        @if($errors->any())
        <div
            style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('success'))
        <div
            style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
        @endif

        <!-- Level Selection Grid -->
        <div class="level-selection-grid">
            @foreach($levels as $level)
            @php
            $hasAccess = $accessInfo[$level['id']] ?? false;
            @endphp
            @if($hasAccess)
            {{-- Accessible card with individual grade selection --}}
            <div class="level-group-card accessible">
                <div class="level-header">
                    <h3 class="level-title">{{ $level['title'] }}</h3>
                    @if(session('selected_level_group') === $level['id'])
                    <span class="current-level-badge">Current</span>
                    @endif
                </div>
                <div class="level-image-container">
                    @if($level['id'] === 'jhs')
                    <img src="{{ asset('images/jhs.jpeg') }}" alt="JHS" class="level-jhs-image">
                    @elseif($level['id'] === 'shs')
                    <img src="{{ asset('images/SHS.png') }}" alt="SHS" class="level-shs-image">
                    @elseif($level['id'] === 'primary-upper')
                    <img src="{{ asset('images/g4-6.jpeg') }}" alt="Grade 4-6" class="level-g4-6-image">
                    @elseif($level['id'] === 'primary-lower')
                    <img src="{{ asset('images/grade 1-3U.jpeg') }}" alt="Grade 1-3" class="level-g1-3-image">
                    @elseif($level['id'] === 'university')
                    <img src="{{ asset('images/university.jpeg') }}" alt="University" class="level-university-image">
                    @else
                    <div class="level-placeholder-image"></div>
                    @endif
                </div>
                <p class="level-description">{{ $level['description'] }}</p>

                <div class="card-footer">
                    <button type="button" class="enter-group-btn"
                        onclick="handleGroupEntry('{{ $level['id'] }}', '{{ $level['title'] }}', {{ json_encode($level['levels'] ?? $level['years'] ?? []) }}, '{{ Auth::user()->grade }}')">
                        <span>Enter Group</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                    <form id="form-{{ $level['id'] }}"
                        action="{{ route('dashboard.select-level-group', $level['id']) }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
            @else
            {{-- Non-accessible card: has upgrade button --}}
            <div class="level-group-card explore-more">
                <div class="level-header">
                    <h3 class="level-title">{{ $level['title'] }}</h3>
                </div>
                <div class="level-image-container">
                    @if($level['id'] === 'jhs')
                    <img src="{{ asset('images/jhs.jpeg') }}" alt="JHS" class="level-jhs-image">
                    @elseif($level['id'] === 'shs')
                    <img src="{{ asset('images/SHS.png') }}" alt="SHS" class="level-shs-image">
                    @elseif($level['id'] === 'primary-upper')
                    <img src="{{ asset('images/g4-6.jpeg') }}" alt="Grade 4-6" class="level-g4-6-image">
                    @elseif($level['id'] === 'primary-lower')
                    <img src="{{ asset('images/grade 1-3U.jpeg') }}" alt="Grade 1-3" class="level-g1-3-image">
                    @elseif($level['id'] === 'university')
                    <img src="{{ asset('images/university.jpeg') }}" alt="University" class="level-university-image">
                    @else
                    <div class="level-placeholder-image"></div>
                    @endif
                </div>
                <p class="level-description">{{ $level['description'] }}</p>

                @if(strtolower($currentPlanName) === 'essential' && $level['id'] === 'shs')
                @php
                $essentialPlusPlan = $pricingPlans->where('slug', 'essential-plus')->first();
                @endphp
                <div class="pricing-options">
                    @if($essentialPlusPlan)
                    <button type="button" class="price-badge open-pricing-modal"
                        data-plan-slug="{{ $essentialPlusPlan->slug }}" style="width: 100%; cursor: pointer;">
                        Explore this category
                    </button>
                    @else
                    <button type="button" class="upgrade-btn open-pricing-modal" data-plan-slug="essential-plus">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="upgrade-icon">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Upgrade to Essential Plus
                    </button>
                    @endif
                </div>
                @elseif(in_array(strtolower($currentPlanName), ['essential', 'essential plus']) && $level['id'] ===
                'university')
                @php
                $essentialProPlan = $pricingPlans->where('slug', 'essential-pro')->first();
                @endphp
                <div class="pricing-options">
                    @if($essentialProPlan)
                    <button type="button" class="price-badge open-pricing-modal"
                        data-plan-slug="{{ $essentialProPlan->slug }}" style="width: 100%; cursor: pointer;">
                        Explore this category
                    </button>
                    @else
                    <button type="button" class="upgrade-btn open-pricing-modal" data-plan-slug="essential-pro">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="upgrade-icon">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Upgrade to Essential Pro
                    </button>
                    @endif
                </div>
                @elseif(!$hasActiveSubscription)
                @php
                $targetSlug = match($level['id']) {
                'primary-lower', 'primary-upper', 'jhs' => 'essential',
                'shs' => 'essential-plus',
                'university' => 'essential-pro',
                default => 'essential'
                };
                $targetPlan = $pricingPlans->where('slug', $targetSlug)->first();
                @endphp
                <div class="pricing-options">
                    @if($targetPlan)
                    <button type="button" class="price-badge open-pricing-modal"
                        data-plan-slug="{{ $targetPlan->slug }}" style="width: 100%; cursor: pointer;">
                        Explore this category
                    </button>
                    @else
                    @foreach($pricingPlans as $plan)
                    <button type="button" class="price-badge open-pricing-modal" data-plan-slug="{{ $plan->slug }}"
                        style="width: 100%; cursor: pointer;">
                        Explore this category
                    </button>
                    @endforeach
                    @endif
                </div>
                @else
                <button type="button" class="upgrade-btn upgrade-trigger" data-level-title="{{ $level['title'] }}"
                    data-level-id="{{ $level['id'] }}">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" class="upgrade-icon">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path
                            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                    Explore This Level
                </button>
                @endif
            </div>
            @endif
            @endforeach
        </div>
    </div>
</main>

@include('partials._upgrade_modal')

<!-- Admission Modal -->
<div id="admissionModal" class="modal-backdrop" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-icon-wrapper">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <h2 class="modal-title">Welcome to <span id="modalGroupName">...</span></h2>
                <p class="modal-subtitle">Pick your starting grade to begin your journey.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="grade-selection-grid" id="modalGradeGrid">
            <!-- Grades will be populated here by JS -->
        </div>

        <div class="modal-footer">
            <p class="footer-note">Selecting a grade unlocks all previous materials in this level.</p>
        </div>
    </div>
</div>
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

    .header-logo,
    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-title {
        text-align: center;
        padding: 0.75rem 0;
        color: var(--primary-red);
        font-size: 1.25rem;
        font-weight: 600;
        border-top: 1px solid var(--border-color);
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
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
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

    /* Form wrapper for clickable cards */
    .level-group-card-form {
        display: contents;
    }

    .level-group-card {
        background: var(--bg-surface);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-align: center;
    }

    .level-group-card:hover:not(.disabled) {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* Clickable card button styles */
    .level-group-card.clickable-card {
        width: 100%;
        font-family: inherit;
        font-size: inherit;
        cursor: pointer;
        display: block;
        position: relative;
    }

    /* Chevron arrow for clickable cards */
    .card-chevron {
        position: absolute;
        right: 1rem;
        top: 85%;
        transform: translateY(-50%);
        color: #2677B8;
        opacity: 0.6;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .level-group-card.clickable-card:hover .card-chevron {
        opacity: 1;
        transform: translateY(-50%) translateX(4px);
    }

    .level-group-card.accessible {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .level-group-card.accessible:hover {
        border-color: var(--secondary-blue);
    }

    .card-footer {
        margin-top: auto;
        border-top: 1px solid var(--border-color);
        padding-top: 1.25rem;
    }

    .enter-group-btn {
        width: 100%;
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .enter-group-btn:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-container {
        background: var(--bg-surface);
        border-radius: 20px;
        width: 100%;
        max-width: 500px;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--border-color);
        overflow: hidden;
        animation: modalSlide 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .modal-header {
        padding: 2rem 2rem 1.5rem;
        text-align: center;
        position: relative;
    }

    .modal-icon-wrapper {
        width: 56px;
        height: 56px;
        background: var(--gray-100);
        color: var(--secondary-blue);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }

    .modal-subtitle {
        color: var(--text-muted);
        font-size: 0.9375rem;
    }

    .modal-close {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        padding: 0.5rem;
        color: #94a3b8;
        border: none;
        background: none;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: var(--white);
        color: #475569;
    }

    .grade-selection-grid {
        padding: 0 2rem 2rem;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .grade-tile {
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        padding: 1.25rem;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
    }

    .grade-tile:hover {
        border-color: var(--secondary-blue);
        background: var(--bg-surface);
        transform: scale(1.02);
    }

    .grade-tile .grade-name {
        font-weight: 700;
        color: var(--text-main);
        font-size: 1.125rem;
    }

    .grade-tile .grade-desc {
        font-size: 0.75rem;
        color: #64748b;
    }

    .modal-footer {
        padding: 1.25rem 2rem;
        background: var(--bg-main);
        border-top: 1px solid var(--border-color);
        text-align: center;
    }

    .footer-note {
        font-size: 0.75rem;
        color: #94a3b8;
        font-style: italic;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes modalSlide {
        from {
            transform: translateY(30px) scale(0.95);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    @media (max-width: 480px) {
        .grade-selection-grid {
            grid-template-columns: 1fr;
        }
    }

    .mt-4 {
        margin-top: 1rem;
    }

    /* Current level badge */
    .current-level-badge {
        display: inline-block;
        background: #2677B8;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        margin-left: 0.5rem;
        vertical-align: middle;
    }

    .level-group-card.explore-more {
        background: var(--bg-surface);
        position: relative;
    }

    .level-group-card.explore-more:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--secondary-blue);
    }

    .upgrade-icon {
        margin-right: 0.5rem;
    }

    .level-header {
        margin-bottom: 1rem;
    }

    .level-title {
        color: var(--primary-red);
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

    .level-g1-3-image {
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
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        line-height: 1.4;
    }



    .upgrade-btn {
        background-color: #3b82f6;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
        font-size: 1rem;
        box-shadow: 0 1px 3px rgba(59, 130, 246, 0.3);
    }

    .upgrade-btn:hover {
        background-color: #2563eb;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        transform: translateY(-1px);
    }

    .upgrade-btn:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
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

    /* Pricing Options Styles */
    .pricing-options {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    .price-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1rem;
        background: var(--bg-main);
        border: 2px solid var(--secondary-blue);
        color: var(--text-main);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .price-badge:hover {
        background: var(--bg-surface);
        border-color: var(--secondary-blue);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }
</style>

@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        // Handle back button navigation
        const backButton = document.getElementById('backButton') ||
            document.getElementById('backToDashboard') ||
            document.getElementById('backToDigilearn');

        if (backButton && backButton.tagName.toLowerCase() === 'button') {
            backButton.addEventListener('click', () => window.history.back());
        }

        // Handle upgrade triggers
        document.querySelectorAll('.upgrade-trigger').forEach(trigger => {
            trigger.addEventListener('click', function () {
                handleUpgradeRequired(this.getAttribute('data-level-title'), this.getAttribute('data-level-id'));
            });
        });

        // Handle upgrade modal trigger
        document.querySelectorAll('.open-pricing-modal').forEach(trigger => {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                const planSlug = this.getAttribute('data-plan-slug');
                openUpgradeModal(planSlug);
            });
        });
    });

    function redirectToPricing() {
        window.location.href = '{{ route("pricing") }}';
    }

    function handleUpgradeRequired(levelTitle, levelId) {
        const button = event.target.closest('.upgrade-trigger');
        button.innerHTML = 'Loading...';
        button.disabled = true;

        if (typeof gtag !== 'undefined') {
            gtag('event', 'upgrade_prompt_shown', {
                level_title: levelTitle,
                level_id: levelId
            });
        }

        setTimeout(() => {
            window.location.href = '{{ route("pricing") }}';
        }, 800);
    }

    // Modal logic is now handled in partials._upgrade_modal

    // Admission Modal Logic
    const admissionModal = document.getElementById('admissionModal');
    const gradeGrid = document.getElementById('modalGradeGrid');
    const groupNameSpan = document.getElementById('modalGroupName');

    function handleGroupEntry(groupId, groupTitle, grades, currentGrade) {
        const hasSpecificGradeInGroup = grades.some(g => g.id === currentGrade);

        if (hasSpecificGradeInGroup) {
            document.getElementById('form-' + groupId).submit();
            return;
        }

        openAdmissionModal(groupId, groupTitle, grades);
    }

    function openAdmissionModal(groupId, groupTitle, grades) {
        groupNameSpan.textContent = groupTitle;
        gradeGrid.innerHTML = '';

        grades.forEach(grade => {
            const tile = document.createElement('div');
            tile.className = 'grade-tile';
            tile.innerHTML = `<span class="grade-name">${grade.title}</span><span class="grade-desc">Click to select</span>`;
            tile.onclick = () => selectGrade(groupId, grade.id);
            gradeGrid.appendChild(tile);
        });

        admissionModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        admissionModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function selectGrade(groupId, gradeId) {
        const form = document.getElementById('form-' + groupId);
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'grade';
        input.value = gradeId;
        form.appendChild(input);
        form.submit();
    }

    window.onclick = function (event) {
        if (event.target == admissionModal) {
            closeModal();
        }
        if (event.target == upgradeModal) {
            closeUpgradeModal();
        }
    }
</script>
@endpush