@extends('layouts.app')

@section('title', 'Contact ShoutOutGH - Get in Touch With Us')
@section('description', 'Contact ShoutOutGH for questions about our online education platform. Reach us by phone, email,
or our contact form. Based in Accra, Ghana.')
@section('keywords', 'contact ShoutOutGH, online education support ghana, e-learning help ghana, ShoutOutGH phone
number, ShoutOutGH email')

@section('head')
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Contact ShoutOutGH - Get in Touch With Us">
    <meta property="og:description"
        content="Contact ShoutOutGH for questions about our online education platform in Ghana. We're here to help.">
    <meta property="og:image" content="{{ secure_asset('images/shoutoutgh-logo.png') }}">
    <meta property="og:url" content="{{ url('/contact') }}">
    <meta property="og:type" content="website">
@endsection

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Contact page specific styles */
        .contact-hero {
            position: relative;
            padding: 3rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(38, 119, 184, 0.08);
            text-align: center;
        }

        .hero-content {
            max-width: 700px;
            margin: 0 auto;
            padding: 0 0.5rem;
        }

        .hero-content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
        }

        .hero-content p {
            font-size: 0.95rem;
            color: var(--gray-600);
            margin: 0;
            line-height: 1.6;
        }

        .contact-section {
            padding: 2.5rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-card {
            background-color: transparent;
            border-radius: 0;
            max-width: 100%;
            margin: 0 auto;
        }

        .card-content {
            padding: 0;
            background-color: transparent;
        }

        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            flex: 1;
        }

        @media (min-width: 48rem) {
            .contact-hero {
                padding: 4.5rem 2rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.05rem;
            }

            .contact-section {
                padding: 3.5rem 2rem;
            }

            .contact-methods {
                flex-direction: row;
                gap: 2rem;
                align-items: start;
            }
        }

        .contact-item {
            background-color: #fff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .contact-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .contact-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .icon {
            background-color: #fff;
            padding: 0;
            flex-shrink: 0;
        }

        .icon svg {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--secondary-blue);
        }

        .text {
            flex: 1;
        }

        .text h3 {
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .text p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .text textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            min-height: 80px;
            resize: vertical;
        }

        .text button {
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            width: auto;
        }



        /* Dark mode overrides */
        [data-theme="dark"] .contact-hero {
            background-color: rgba(38, 119, 184, 0.15);
        }

        [data-theme="dark"] .hero-content h1 {
            color: #ffffff !important;
        }

        [data-theme="dark"] .hero-content p {
            color: var(--text-muted) !important;
        }

        [data-theme="dark"] .contact-section,
        [data-theme="dark"] .contact-card {
            background-color: var(--bg-main) !important;
        }

        [data-theme="dark"] .card-content {
            background-color: var(--bg-main) !important;
        }

        [data-theme="dark"] .contact-item {
            background-color: var(--bg-surface) !important;
            border-color: var(--border-color) !important;
            box-shadow: none !important;
        }

        [data-theme="dark"] .icon {
            background-color: transparent !important;
        }

        [data-theme="dark"] .text textarea {
            background-color: var(--bg-main) !important;
            border-color: transparent !important;
            color: var(--text-main) !important;
        }

        [data-theme="dark"] .success-msg,
        [data-theme="dark"] .error-msg {
            background-color: var(--bg-surface) !important;
            border-color: transparent !important;
            color: #ffffff !important;
        }

        /* Auth Prompt Styles */
        .auth-prompt {
            background: rgba(255, 255, 255, 0.8);
            border: 1px dashed #d1d5db;
            padding: 1rem;
            text-align: center;
            border-radius: 0;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }

        .auth-prompt-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .auth-prompt-icon {
            display: none; /* Hide icon in compact version */
        }

        .auth-prompt p {
            font-size: 0.85rem;
            color: var(--gray-900);
            max-width: 100%;
            margin: 0 0 0.75rem;
            line-height: 1.4;
        }

        .auth-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            width: 100%;
        }

        .btn-auth {
            flex: 1;
            padding: 0.6rem 0;
            border-radius: 0;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }

        .btn-login {
            background-color: var(--secondary-blue);
            color: white;
        }

        .btn-login:hover {
            background-color: #1a4da1;
            transform: translateY(-1px);
        }

        .btn-signup {
            background-color: transparent;
            color: var(--secondary-blue);
            border: 1px solid var(--secondary-blue);
        }

        .btn-signup:hover {
            background-color: rgba(30, 64, 175, 0.05);
            transform: translateY(-1px);
        }

        [data-theme="dark"] .auth-prompt {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .auth-prompt-icon {
            background-color: var(--bg-main);
            color: var(--gray-50);
        }

        [data-theme="dark"] .auth-prompt p {
            color: var(--text-main);
        }

        [data-theme="dark"] .btn-signup {
            color: var(--black);
            border-color: var(--gray-600);
        }

        [data-theme="dark"] .btn-signup:hover {
            background-color: var(--white);
            color: var(--bg-main);
        }
    </style>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="hero-content">
            <h1>Contact Us</h1>
            <p>Have questions? The quickest way to get in touch with us is using the contact information below.</p>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-section">
        <div class="contact-card">

            @if(session('success'))
                <div class="success-msg"
                    style="padding: 1rem; margin: 1rem; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; border-radius: 0;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-msg"
                    style="padding: 1rem; margin: 1rem; background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 0;">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="error-msg"
                    style="padding: 1rem; margin: 1rem; background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 0;">
                    <ul style="margin: 0; padding-left: 1rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-content">
                <!-- Contact Methods - Masonry Columns -->
                <div class="contact-methods">
                    <!-- Left Column -->
                    <div class="contact-column">
                        <div class="contact-item">
                            <div class="contact-content">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                        </path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="text">
                                    <h3>Get feedback</h3>
                                    <p>
                                        We have millions of teachers around the world as it helps us improve what you love to
                                        learn.
                                    </p>
                                    @auth
                                        <form action="{{ route('feedback.submit') }}" method="POST">
                                            @csrf
                                            <textarea name="feedback" placeholder="Share your thoughts..."></textarea>
                                            <button type="submit">Submit</button>
                                        </form>
                                    @else
                                        <textarea name="feedback" placeholder="Share your thoughts..."></textarea>
                                        <div class="auth-prompt">
                                            <div class="auth-prompt-content">
                                                <p>Please log in or create an account to send a feedback.</p>
                                                <div class="auth-buttons">
                                                    <a href="{{ route('login', ['redirect_to' => url()->current()]) }}" class="btn-auth btn-login">Login</a>
                                                    <a href="{{ route('signup', ['redirect_to' => url()->current()]) }}" class="btn-auth btn-signup">Sign Up</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endauth
                                </div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-content">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                        </path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="text">
                                    <h3>shoutoutgh.com</h3>
                                    <p>
                                        We have a team of teachers around the world as it helps us provide the best for you. Our
                                        team is available 24/7 to help you with your questions.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="contact-column">
                        <div class="contact-item">
                            <div class="contact-content">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text">
                                    <h3>(+233) 546 994383</h3>
                                    <p>
                                        We have a team of teachers around the world as it helps us provide the best for you. Our
                                        team is available 24/7 to help you.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-content">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="text">
                                    <h3>Accra-Ghana</h3>
                                    <p>
                                        We have a team of teachers around the world as it helps us provide the best for you. Contact our
                                        team at contact@shoutoutgh.com
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


@endsection

@push('scripts')
     <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.success-msg, .error-msg');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 1s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 1000);
                }, 5000);
            });
        });
    </script>
@endpush
