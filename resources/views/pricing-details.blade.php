@extends('layouts.app')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">

    li {
        list-style: none;
    }
    
    /* Pricing page specific styles */
    .pricing-hero {
        position: relative;
        height: 300px;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
    }

    .pricing-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url('{{ secure_asset("images/hero-image.png") }}');
        background-size: cover;
        background-position: center;
        z-index: -1;
    }

    .pricing-hero h1 {
        font-size: 3rem;
        font-weight: 600;
        text-align: center;
    }

    .pricing-content {
        padding: 4rem 0;
        background-color: var(--white);
    }

    .pricing-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .pricing-header h2 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .pricing-header p {
        color: var(--gray-600);
        font-size: 1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .pricing-tabs {
        display: flex;
        justify-content: center;
        margin: 2rem 0;
        gap: 0;
        background-color: var(--gray-100);
        border-radius: 0.5rem;
        padding: 0.25rem;
        max-width: 400px;
        margin: 2rem auto;
    }

    .pricing-tab {
        flex: 1;
        padding: 0.75rem 1rem;
        text-align: center;
        text-decoration: none;
        color: var(--gray-600);
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .pricing-tab.active {
        background-color: var(--secondary-blue);
        color: var(--white);
    }

    .pricing-tab:hover:not(.active) {
        background-color: var(--gray-200);
    }

    .pricing-plan-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .pricing-plan-wrapper {
        background-color: var(--white);
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: none;
    }

    .pricing-plan-wrapper.active {
        display: block;
    }

    .pricing-plan-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
    }

    @media (min-width: 768px) {
        .pricing-plan-grid {
            grid-template-columns: 2fr 1fr;
        }
    }

    .plan-features-section {
        padding: 2rem;
        border-right: 1px solid #e5e7eb;
    }

    .plan-duration-section {
        padding: 2rem;
        background-color: var(--gray-50);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .duration-option {
        display: flex;
        align-items: center;
        padding: 1rem;
        background-color: var(--white);
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .duration-option:hover {
        border-color: var(--secondary-blue-hover);
    }

    .duration-radio {
        margin-right: 1rem;
        width: 1.25rem;
        height: 1.25rem;
        accent-color: var(--secondary-blue);
    }

    .duration-label-wrapper {
        flex: 1;
        cursor: pointer;
    }

    .duration-info {
        flex: 1;
    }

    .duration-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 0.25rem;
    }

    .duration-price {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .purchase-btn-main {
        width: 100%;
        background-color: var(--secondary-blue);
        color: var(--white);
        border: none;
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 1rem;
    }

    .purchase-btn-main:hover {
        background-color: var(--secondary-blue-hover);
    }

    .duration-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Full-screen mobile modal with iOS safe area support */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .mobile-pricing-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--white);
        z-index: 1000;
        overflow: hidden;
        flex-direction: column;
        animation: slideUp 0.4s cubic-bezier(0.32, 0.08, 0.24, 1);
    }

    @keyframes slideUp {
        from {
            transform: translateY(100vh);
        }
        to {
            transform: translateY(0);
        }
    }

    .modal-overlay.active,
    .mobile-pricing-modal.active {
        display: flex;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: max(1.5rem, env(safe-area-inset-top)) 1.5rem 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        flex-shrink: 0;
        background: var(--white);
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--gray-600);
        transition: color 0.2s ease;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .modal-close-btn:hover {
        color: var(--gray-900);
    }

    .modal-tabs {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        background: var(--white);
        flex-shrink: 0;
    }

    .modal-tab {
        flex: 1;
        padding: 1rem;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 500;
        color: var(--gray-600);
        transition: all 0.2s ease;
        position: relative;
        font-size: 0.95rem;
        text-align: center;
    }

    .modal-tab.active {
        color: var(--secondary-blue);
    }

    .modal-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: var(--secondary-blue);
    }

    /* Content scrolls with proper safe area spacing */
    .modal-content {
        flex: 1;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding: 1.5rem;
        padding-right: max(1.5rem, env(safe-area-inset-right));
        padding-left: max(1.5rem, env(safe-area-inset-left));
        padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
    }

    .modal-tab-content {
        display: none;
    }

    .modal-tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-feature-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .modal-feature-icon {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--gray-50);
    }

    .modal-feature-icon.tuition {
        background-color: #fee2e2;
        color: var(--primary-red);
    }

    .modal-feature-icon.resources {
        background-color: #dbeafe;
        color: #2677B8;
    }

    .modal-feature-icon.sessions {
        background-color: #f3e8ff;
        color: #8b5cf6;
    }

    .modal-feature-icon.support {
        background-color: #dcfce7;
        color: #22c55e;
    }

    .modal-feature-title {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .modal-feature-description {
        font-size: 0.8rem;
        color: var(--gray-600);
        line-height: 1.4;
    }

    .modal-duration-option {
        display: flex;
        align-items: center;
        padding: 1rem;
        background-color: var(--gray-50);
        border-radius: 0.5rem;
        border: 1px solid #c1d3f8ff;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 0.75rem;
    }

    .modal-duration-option:hover {
        border-color: var(--secondary-blue);
        background-color: #b0cef8ff;
    }

    .modal-duration-option input[type="radio"] {
        margin-right: 0.75rem;
        width: 1.25rem;
        height: 1.25rem;
        accent-color: var(--secondary-blue);
        cursor: pointer;
        flex-shrink: 0;
    }

    .modal-duration-info {
        flex: 1;
    }

    .modal-duration-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 0.25rem;
    }

    .modal-duration-price {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    /* Footer with iOS safe area insets */
    .modal-footer {
        padding: 1rem max(1.5rem, env(safe-area-inset-right)) max(1rem, env(safe-area-inset-bottom)) max(1.5rem, env(safe-area-inset-left));
        border-top: 1px solid #e5e7eb;
        background: var(--gray-50);
        flex-shrink: 0;
    }

    .modal-purchase-btn {
        width: 100%;
        background-color: var(--secondary-blue);
        color: var(--white);
        border: none;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-purchase-btn:hover {
        background-color: var(--primary-red-hover);
    }

    .strikethrough-price {
        text-decoration: line-through;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .login-link {
        display: inline-block;
        text-decoration: none;
    }

    .modal-login-link {
        display: block;
        text-align: center;
        text-decoration: none;
    }

    .modal-duration-label-wrapper {
        cursor: pointer;
        display: block;
    }

    /* Mobile: Hide desktop elements, show modal as main UI */
    @media (max-width: 768px) {
        .pricing-plan-wrapper {
            display: none !important;
        }

        .pricing-header {
            margin-bottom: 2rem;
        }

        .pricing-hero h1 {
            font-size: 2rem;
        }

        .open-modal-btn {
            display: none;
        }

        .mobile-pricing-modal {
            display: flex !important;
            position: relative;
            height: 100vh;
            width: 100vw;
            top: auto;
            left: auto;
            right: auto;
            bottom: auto;
            z-index: auto;
            animation: none;
        }

        .modal-overlay {
            display: none !important;
        }

        .modal-close-btn {
            display: none;
        }

        .modal-header {
            padding: max(1.5rem, env(safe-area-inset-top)) 1.5rem 1rem 1.5rem;
            padding-right: max(1.5rem, env(safe-area-inset-right));
            padding-left: max(1.5rem, env(safe-area-inset-left));
        }

        .modal-content {
            padding: 1.5rem;
            padding-right: max(1.5rem, env(safe-area-inset-right));
            padding-left: max(1.5rem, env(safe-area-inset-left));
            padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
        }

        .modal-footer {
            padding: 1rem max(1.5rem, env(safe-area-inset-right)) max(1rem, env(safe-area-inset-bottom)) max(1.5rem, env(safe-area-inset-left));
        }

        .plan-features-section {
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .plan-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .duration-option {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .purchase-btn-main {
            width: 100%;
            margin-left: 0;
        }

        .pricing-tabs {
            flex-direction: column;
            max-width: 200px;
        }
    }

    /* Desktop: Hide the View Pricing Details button */
    @media (min-width: 769px) {
        .open-modal-btn {
            display: none;
        }
    }

    .pricing-footer {
        background-color: var(--gray-50);
        padding: 3rem 0;
        margin-top: 4rem;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .footer-links {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .footer-links a {
        color: var(--gray-600);
        text-decoration: none;
        font-size: 0.875rem;
    }

    .footer-links a:hover {
        color: var(--gray-900);
    }

    .footer-social {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .social-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .social-icon.twitter {
        background-color: #1da1f2;
    }

    .social-icon.linkedin {
        background-color: #0077b5;
    }

    .social-icon.instagram {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .social-icon svg {
        width: 1rem;
        height: 1rem;
        color: var(--white);
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-logo-icon {
        width: 2rem;
        height: 2rem;
        background-color: var(--primary-red);
        border-radius: 0.25rem;
    }

    .footer-logo-text {
        font-size: 1.25rem;
        font-weight: bold;
        color: var(--gray-900);
    }

    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- Hero Section -->
<section class="pricing-hero">
    <h1>Pricing</h1>
</section>

<!-- Pricing Content -->
<section class="pricing-content">
    <div class="container">
        <div class="pricing-header">
            <h2>Choose Your Learning Plan</h2>
            <p>Select your membership plan tailored to your needs. Customize your subscription for a seamless fit.</p>
        </div>

        <!-- Pricing Tabs (Desktop) -->
        <div class="pricing-tabs">
            @foreach($plans as $plan)
                <button class="pricing-tab {{ $loop->first ? 'active' : '' }}" data-plan="{{ $plan->slug }}">{{ $plan->name }}</button>
            @endforeach
        </div>

        <!-- Desktop Pricing Plans -->
        <div class="pricing-plan-container">
            @foreach($plans as $plan)
                <div id="{{ $plan->slug }}-plan" class="pricing-plan-wrapper {{ $loop->first ? 'active' : '' }}">
                    <div class="pricing-plan-grid">
                        <div class="plan-features-section">
                            <div class="plan-header">
                                <div class="plan-name">{{ $plan->name }}</div>
                            </div>

                            <ul class="plan-features">
                                @if(is_array($plan->features))
                                    @foreach($plan->features as $index => $feature)
                                        <li>
                                            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                            <div class="feature-text">
                                                <div class="feature-title">{{ $feature }}</div>
                                                <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <li>
                                        <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        <div class="feature-text">
                                            <div class="feature-title">{{ $plan->features }}</div>
                                            <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="plan-duration-section">
                            <form class="duration-form">
                                <div class="duration-option">
                                    <input type="radio" id="{{ $plan->slug }}-month" name="{{ $plan->slug }}-duration" value="month" class="duration-radio" checked>
                                    <label for="{{ $plan->slug }}-month" class="duration-label-wrapper">
                                        <div class="duration-info">
                                            <div class="duration-label">1 month</div>
                                            <div class="duration-price">
                                                {{ $plan->getFormattedPriceForDuration('month') }}
                                                @if($plan->getPriceForDuration('month') !== $plan->price)
                                                    <span class="strikethrough-price">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @foreach($plan->discount_tiers ?? [] as $tier)
                                    @php
                                        $months = $tier['duration_months'] ?? 0;
                                        $durationKey = $months . 'month';
                                        $discountedPrice = $plan->getFormattedPriceForDuration($durationKey);
                                        $originalPrice = $plan->currency . ' ' . number_format($plan->price * $months, 2);
                                    @endphp
                                    <div class="duration-option">
                                        <input type="radio" id="{{ $plan->slug }}-{{ $durationKey }}" name="{{ $plan->slug }}-duration" value="{{ $durationKey }}" class="duration-radio">
                                        <label for="{{ $plan->slug }}-{{ $durationKey }}" class="duration-label-wrapper">
                                            <div class="duration-info">
                                                <div class="duration-label">{{ $months }} months</div>
                                                <div class="duration-price">
                                                    {{ $discountedPrice }}
                                                    @if($discountedPrice !== $originalPrice)
                                                        <span style="text-decoration: line-through; color: #6b7280; font-size: 0.875rem;">{{ $originalPrice }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                                @auth
                                    <button type="submit" class="purchase-btn-main" onclick="initiatePayment(this)">Purchase</button>
                                @else
                                    <a href="{{ route('login') }}" class="purchase-btn-main login-link">Login to Purchase</a>
                                @endauth
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Mobile: Open Modal Button -->
        <div class="container" style="text-align: center; margin-top: 1rem;">
            <button class="open-modal-btn" data-plan="{{ $plans->first()->slug ?? '' }}">View Pricing Details</button>
        </div>
    </div>
</section>

<!-- Mobile Modal Overlay & Full-Screen Modal -->
<div class="modal-overlay" id="pricingModal"></div>
<div class="mobile-pricing-modal" id="pricingWizard">
    <div class="modal-header">
        <h2 class="modal-title">Pricing Details</h2>
        <button class="modal-close-btn" id="modalCloseBtn">&times;</button>
    </div>

    <div class="modal-tabs">
        <button class="modal-tab active" data-tab="details">Details</button>
        <button class="modal-tab" data-tab="pricing">Duration & Pricing</button>
    </div>

    <div class="modal-content">
        <!-- Details Tab -->
        <div id="details" class="modal-tab-content active">
            <div id="modal-features-container">
                <!-- Features populated by JS -->
            </div>
        </div>

        <!-- Pricing Tab -->
        <div id="pricing" class="modal-tab-content">
            <form class="modal-pricing-form" id="modal-pricing-container">
                <!-- Duration options populated by JS -->
            </form>
        </div>
    </div>

    <div class="modal-footer">
        @auth
            <button class="modal-purchase-btn" onclick="initiateModalPayment()">Next: View Pricing</button>
        @else
            <a href="{{ route('login') }}" class="modal-purchase-btn modal-login-link">Login to Purchase</a>
        @endauth
    </div>
</div>


<script src="https://js.paystack.co/v1/inline.js"></script>
@php
$plansJson = $plans->map(function($plan) {
    $formattedPrices = [
        'month' => $plan->getFormattedPriceForDuration('month'),
    ];
    if ($plan->discount_tiers) {
        foreach ($plan->discount_tiers as $tier) {
            $months = $tier['duration_months'] ?? 0;
            $durationKey = $months . 'month';
            $formattedPrices[$durationKey] = $plan->getFormattedPriceForDuration($durationKey);
        }
    }
    return [
        'slug' => $plan->slug,
        'name' => $plan->name,
        'features' => $plan->features,
        'formatted_prices' => $formattedPrices,
        'discount_tiers' => $plan->discount_tiers,
    ];
});
@endphp
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    const plansData = @json($plansJson);
    let currentPlanSlug = '{{ $plans->first()->slug ?? "" }}';

    function updateModalContent(planSlug) {
        currentPlanSlug = planSlug;
        const plan = plansData.find(p => p.slug === planSlug);
        if (!plan) return;

        // Update features
        let featuresHtml = '';
        if (Array.isArray(plan.features)) {
            plan.features.forEach(feature => {
                featuresHtml += `
                    <div class="modal-feature-item">
                        <div class="modal-feature-icon tuition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2L2 7v3c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="modal-feature-title">${feature}</div>
                            <div class="modal-feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                        </div>
                    </div>
                `;
            });
        } else {
            featuresHtml = `
                <div class="modal-feature-item">
                    <div class="modal-feature-icon tuition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L2 7v3c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="modal-feature-title">${plan.features}</div>
                        <div class="modal-feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                    </div>
                </div>
            `;
        }
        document.getElementById('modal-features-container').innerHTML = featuresHtml;

        // Update pricing
        let pricingHtml = `
            <div class="modal-duration-option">
                <input type="radio" id="modal-month" name="modal-duration" value="month" class="modal-duration-radio" checked>
                <div class="modal-duration-info">
                    <label for="modal-month" class="modal-duration-label-wrapper">
                        <div class="modal-duration-label">1 month</div>
                        <div class="modal-duration-price">${plan.formatted_prices ? plan.formatted_prices.month : 'N/A'}</div>
                    </label>
                </div>
            </div>
        `;

        if (plan.discount_tiers) {
            plan.discount_tiers.forEach(tier => {
                const months = tier.duration_months || 0;
                const durationKey = months + 'month';
                const price = plan.formatted_prices ? plan.formatted_prices[durationKey] : 'N/A';
                pricingHtml += `
                    <div class="modal-duration-option">
                        <input type="radio" id="modal-${durationKey}" name="modal-duration" value="${durationKey}" class="modal-duration-radio">
                        <div class="modal-duration-info">
                            <label for="modal-${durationKey}" class="modal-duration-label-wrapper">
                                <div class="modal-duration-label">${months} months</div>
                                <div class="modal-duration-price">${price}</div>
                            </label>
                        </div>
                    </div>
                `;
            });
        }

        document.getElementById('modal-pricing-container').innerHTML = pricingHtml;
    }

    // Pricing tab functionality (Desktop)
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.pricing-tab');
        const plans = document.querySelectorAll('.pricing-plan-wrapper');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                plans.forEach(plan => plan.style.display = 'none');
                const planId = this.getAttribute('data-plan') + '-plan';
                document.getElementById(planId).style.display = 'block';
                updateModalContent(this.getAttribute('data-plan'));
            });
        });

        // Initialize modal content for first plan
        updateModalContent(currentPlanSlug);

        const modal = document.getElementById('pricingWizard');
        const overlay = document.getElementById('pricingModal');
        const closeBtn = document.getElementById('modalCloseBtn');
        const openBtn = document.querySelector('.open-modal-btn');
        const modalTabs = document.querySelectorAll('.modal-tab');

        // Open modal
        openBtn.addEventListener('click', function() {
            modal.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close modal
        function closeModal() {
            modal.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);

        // Modal tab switching
        modalTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                modalTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.modal-tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                document.getElementById(targetTab).classList.add('active');
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    });

    // Payment initiation (Desktop)
    function initiatePayment(button) {
        const form = button.closest('form');
        const planId = form.closest('.pricing-plan-wrapper').id.replace('-plan', '');
        const duration = form.querySelector('input[name*="-duration"]:checked').value;

        button.disabled = true;
        button.textContent = 'Processing...';

        fetch('/payment/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                plan_id: planId,
                duration: duration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.authorization_url;
            } else {
                alert(data.message || 'Payment initiation failed');
                button.disabled = false;
                button.textContent = 'Purchase';
            }
        })
        .catch(error => {
            console.error('Payment error:', error);
            alert('An error occurred. Please try again.');
            button.disabled = false;
            button.textContent = 'Purchase';
        });
    }

    function initiateModalPayment() {
        const duration = document.querySelector('.modal-pricing-form input[name="modal-duration"]:checked').value;
        const button = document.querySelector('.modal-purchase-btn');

        button.disabled = true;
        button.textContent = 'Processing...';

        fetch('/payment/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                plan_id: currentPlanSlug,
                duration: duration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.authorization_url;
            } else {
                alert(data.message || 'Payment initiation failed');
                button.disabled = false;
                button.textContent = 'Purchase';
            }
        })
        .catch(error => {
            console.error('Payment error:', error);
            alert('An error occurred. Please try again.');
            button.disabled = false;
            button.textContent = 'Purchase';
        });
    }
</script>
@endsection
