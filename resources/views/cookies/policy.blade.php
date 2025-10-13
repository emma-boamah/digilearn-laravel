@extends('layouts.app')

@section('title', 'Cookie Policy - DigiLearn')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .policy-container {
        min-height: 100vh;
        background-color: var(--gray-50);
        padding: 3rem 0;
    }

    .policy-wrapper {
        max-width: 56rem;
        margin: 0 auto;
        padding: 0 1rem;
    }

    @media (min-width: 640px) {
        .policy-wrapper {
            padding: 0 1.5rem;
        }
    }

    @media (min-width: 1024px) {
        .policy-wrapper {
            padding: 0 2rem;
        }
    }

    .policy-card {
        background-color: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-300);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .policy-header {
        text-align: center;
    }

    .policy-icon {
        width: 4rem;
        height: 4rem;
        color: var(--secondary-blue);
        margin: 0 auto 1rem;
    }

    .policy-main-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .policy-subtitle {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .policy-date {
        font-size: 0.875rem;
        color: var(--gray-500);
    }

    .policy-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 1rem;
    }

    .policy-subsection-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 1rem;
    }

    .policy-text {
        color: var(--gray-600);
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .policy-alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: var(--border-radius-lg);
    }

    .alert-blue {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
    }

    .alert-yellow {
        background-color: #fefce8;
        border-left: 4px solid #facc15;
    }

    .alert-green {
        background-color: #f0fdf4;
        border-left: 4px solid #22c55e;
    }

    .alert-title {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .alert-blue .alert-title {
        color: #1e40af;
    }

    .alert-yellow .alert-title {
        color: #a16207;
    }

    .alert-green .alert-title {
        color: #166534;
    }

    .alert-text {
        font-size: 0.875rem;
    }

    .alert-blue .alert-text {
        color: #1e3a8a;
    }

    .alert-yellow .alert-text {
        color: #854d0e;
    }

    .alert-green .alert-text {
        color: #14532d;
    }

    .policy-list {
        list-style-type: disc;
        padding-left: 1.5rem;
        color: var(--gray-600);
        margin-bottom: 1rem;
    }

    .policy-list li {
        margin-bottom: 0.25rem;
    }

    .policy-note-box {
        background-color: var(--gray-50);
        padding: 1rem;
        border-radius: var(--border-radius-lg);
    }

    .policy-note-text {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .policy-link {
        color: var(--secondary-blue);
        text-decoration: none;
    }

    .policy-link:hover {
        text-decoration: underline;
    }

    .policy-contact-box {
        background-color: var(--gray-50);
        padding: 1rem;
        border-radius: var(--border-radius-lg);
    }

    .policy-contact-item {
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .policy-footer {
        text-align: center;
        margin-top: 2rem;
    }

    .policy-cta-button {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        background-color: var(--secondary-blue);
        color: var(--white);
        font-weight: 500;
        border-radius: var(--border-radius-lg);
        text-decoration: none;
        transition: all 0.2s;
    }

    .policy-cta-button:hover {
        background-color: var(--secondary-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.3);
    }

    .policy-category-list {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
</style>

<div class="policy-container">
    <div class="policy-wrapper">
         Header 
        <div class="policy-card">
            <div class="policy-header">
                <svg class="policy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h1 class="policy-main-title">Cookie Policy</h1>
                <p class="policy-subtitle">Learn about how we use cookies and how you can control them</p>
                <p class="policy-date">Last updated: {{ now()->format('F j, Y') }}</p>
            </div>
        </div>

         Introduction 
        <div class="policy-card">
            <h2 class="policy-section-title">What are Cookies?</h2>
            <p class="policy-text">
                Cookies are small text files that are stored on your device when you visit our website.
                They help us provide you with a better browsing experience by remembering your preferences
                and understanding how you use our site.
            </p>
            <p class="policy-text">
                This cookie policy explains what cookies we use, why we use them, and how you can control
                your cookie preferences.
            </p>
        </div>

         Cookie Categories 
        <div class="policy-category-list">
            @foreach($categories as $key => $description)
                <div class="policy-card">
                    <h3 class="policy-subsection-title">{{ ucfirst($key) }} Cookies</h3>

                    @if($key === 'preference')
                        <div class="policy-alert alert-blue">
                            <p class="alert-title">Required Cookies</p>
                            <p class="alert-text">
                                These cookies are essential for the website to function properly and cannot be disabled.
                            </p>
                        </div>
                    @elseif($key === 'analytics')
                        <div class="policy-alert alert-yellow">
                            <p class="alert-title">Optional Analytics Cookies</p>
                            <p class="alert-text">
                                These cookies help us understand how visitors use our website by collecting
                                information anonymously. You can choose to disable these cookies.
                            </p>
                        </div>
                    @elseif($key === 'consent')
                        <div class="policy-alert alert-green">
                            <p class="alert-title">Consent Management Cookies</p>
                            <p class="alert-text">
                                These cookies store your cookie preferences and cannot be disabled.
                            </p>
                        </div>
                    @endif

                    <p class="policy-text">{{ $description }}</p>

                    <h4 style="font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">Purpose:</h4>
                    <ul class="policy-list">
                        @if($key === 'preference')
                            <li>Remember your login status</li>
                            <li>Maintain your session security</li>
                            <li>Store essential website preferences</li>
                        @elseif($key === 'analytics')
                            <li>Track page views and user interactions</li>
                            <li>Understand which content is most popular</li>
                            <li>Identify technical issues and improve performance</li>
                        @elseif($key === 'consent')
                            <li>Remember your cookie preferences</li>
                            <li>Ensure compliance with privacy regulations</li>
                        @endif
                    </ul>

                    @if($key !== 'preference' && $key !== 'consent')
                        <div class="policy-note-box">
                            <p class="policy-note-text">
                                <strong>Note:</strong> You can change your preferences for {{ $key }} cookies at any time
                                by visiting our <a href="{{ route('cookies.settings') }}" class="policy-link">cookie settings page</a>.
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

         Third-party Cookies 
        <div class="policy-card">
            <h2 class="policy-section-title">Third-party Cookies</h2>
            <p class="policy-text">
                We may use third-party services that set their own cookies. These include:
            </p>
            <ul class="policy-list">
                <li><strong>Google Analytics:</strong> For website analytics and performance monitoring</li>
                <li><strong>Cloudflare:</strong> For website security and performance optimization</li>
                <li><strong>Social Media Platforms:</strong> For social sharing functionality</li>
            </ul>
        </div>

         Managing Cookies 
        <div class="policy-card">
            <h2 class="policy-section-title">Managing Your Cookies</h2>
            <p class="policy-text">
                You have several options for managing cookies:
            </p>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <h3 style="font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">Cookie Settings</h3>
                    <p class="policy-text">
                        Visit our <a href="{{ route('cookies.settings') }}" class="policy-link">cookie settings page</a>
                        to customize your cookie preferences.
                    </p>
                </div>

                <div>
                    <h3 style="font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">Browser Settings</h3>
                    <p class="policy-text">
                        You can also control cookies through your browser settings. Most browsers allow you to:
                    </p>
                    <ul class="policy-list">
                        <li>View what cookies are stored</li>
                        <li>Delete existing cookies</li>
                        <li>Block cookies from specific sites</li>
                        <li>Block all cookies</li>
                    </ul>
                </div>

                <div>
                    <h3 style="font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">Opt-out Links</h3>
                    <p class="policy-text">
                        For Google Analytics, you can opt-out by visiting:
                        <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" class="policy-link">
                            Google Analytics Opt-out
                        </a>
                    </p>
                </div>
            </div>
        </div>

         Contact Information 
        <div class="policy-card">
            <h2 class="policy-section-title">Contact Us</h2>
            <p class="policy-text">
                If you have any questions about our cookie policy or how we use cookies, please contact us:
            </p>
            <div class="policy-contact-box">
                <p class="policy-contact-item"><strong>Email:</strong> privacy@digilearn.com</p>
                <p class="policy-contact-item"><strong>Phone:</strong> +233-207-646-203</p>
                <p class="policy-contact-item"><strong>Address:</strong> Accra, Ghana</p>
            </div>
        </div>

         Footer Actions 
        <div class="policy-footer">
            <a href="{{ route('cookies.settings') }}" class="policy-cta-button">
                Manage Cookie Settings
            </a>
        </div>
    </div>
</div>
@endsection