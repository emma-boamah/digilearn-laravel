<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - ShoutOutGH | Free Online Education in Ghana</title>
    <meta name="description" content="Create a free ShoutOutGH account to access quality online education in Ghana. Interactive lessons, quizzes, and study materials for Primary, JHS, SHS, and University students.">
    <meta name="keywords" content="ShoutOutGH signup, register online education ghana, free online school ghana, create account, e-learning registration ghana">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="{{ url('/signup') }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Sign Up - ShoutOutGH | Free Online Education in Ghana">
    <meta property="og:description" content="Create a free ShoutOutGH account. Access quality online education for Ghanaian students.">
    <meta property="og:image" content="{{ secure_asset('images/shoutoutgh-logo.png') }}">
    <meta property="og:url" content="{{ url('/signup') }}">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #dc2626;
            --primary-red-hover: #b91c1c;
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-900: #111827;
            --green-400: #4ade80;
            --green-600: #059669;
            --red-500: #ef4444;
            --orange-500: #f97316;
            --yellow-500: #eab308;
            --font-family-sans: 'Figtree', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family-sans);
            background-color: var(--gray-100);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-x: auto;
        }

        .auth-container {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100vw;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 400px;
            overflow-x: auto;
        }

        .auth-form-container {
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            flex-shrink: 0;
            min-width: 180px;
            margin-bottom: 1.5rem;
            justify-content: center;
        }

        .logo-image {
            height: 47px;
            width: 100%;
            max-width: 500px;
            object-fit: contain;
        }

        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--gray-300);
        }

        .tab {
            flex: 1;
            padding: 0.75rem 1rem;
            text-align: center;
            text-decoration: none;
            color: var(--gray-500);
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .tab.active {
            color: var(--primary-red);
            border-bottom-color: var(--primary-red);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: var(--gray-50);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-input.error {
            border-color: var(--primary-red);
            background-color: #fef2f2;
        }

        .error-message {
            color: var(--primary-red);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Allow links in error messages */
        .error-message a {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .error-message a:hover {
            text-decoration: none;
        }

        /* For error summary */
        .error-list a {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .rules-toggle-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        .rules-toggle-btn {
            background: none;
            border: none;
            color: var(--primary-blue);
            font-size: 0.875rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .rules-toggle-btn:hover {
            background-color: var(--gray-100);
            text-decoration: none;
        }

        .password-rules.hidden {
            display: none;
        }

        .password-rules {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .password-rule {
            display: flex;
            align-items: center;
            margin-bottom: 0.3rem;
        }

        .rule-icon {
            margin-right: 0.5rem;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            transition: all 0.3s ease;
        }

        .rule-icon.valid {
            background-color: var(--green-600);
            color: white;
        }

        .rule-text {
            transition: all 0.3s ease;
        }

        .rule-text.valid {
            color: var(--green-600);
            font-weight: 500;
        }

        .password-strength {
            height: 4px;
            margin-top: 0.5rem;
            border-radius: 2px;
            background: var(--gray-300);
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }

        .strength-0 { width: 20%; background: var(--red-500); }
        .strength-1 { width: 40%; background: var(--orange-500); }
        .strength-2 { width: 60%; background: var(--yellow-500); }
        .strength-3 { width: 80%; background: var(--green-400); }
        .strength-4 { width: 100%; background: var(--green-600); }
        .strength-5 { width: 100%; background: var(--green-600); }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: var(--gray-500);
            background: none;
            border: none;
            font-size: 1.1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-group input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .checkbox-group label {
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .terms-link {
            color: var(--primary-blue);
            text-decoration: none;
        }

        .terms-link:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            background-color: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
        }

        .submit-btn:hover {
            background-color: var(--primary-blue-hover);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--gray-300);
        }

        .divider::before {
            margin-right: 1rem;
        }

        .divider::after {
            margin-left: 1rem;
        }

        .social-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--gray-300);
            background-color: var(--white);
            width: 100%;
            max-width: 200px;
        }

        .social-btn:hover {
            background-color: var(--gray-50);
        }

        .social-btn.google {
            background-color: #fff;
            color: var(--gray-900);
        }

        .social-btn.apple {
            background-color: #000;
            color: var(--white);
            border-color: #000;
        }

        .social-btn svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .auth-hero {
            position: relative;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e40af 100%);
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: var(--white);
            min-height: 400px;
        }

        .auth-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ secure_asset("images/auth-hero.png") }}');
            background-size: cover;
            background-position: center;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 10;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 400px;
            line-height: 1.6;
        }

        .hero-accent {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4rem;
            background-color: var(--primary-blue);
        }

        /* Phone Input Styles */
        .phone-input-container {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
        }

        .country-code-selector {
            position: relative;
            flex-shrink: 0;
        }

        .country-code-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background: var(--gray-50);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            min-width: 120px;
        }

        .country-code-btn:hover,
        .country-code-btn:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .country-flag {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        .country-code {
            font-weight: 500;
            color: var(--gray-700);
        }

        .country-code-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 100;
            max-height: 300px;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .country-code-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .country-search {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .country-search-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .country-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .country-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 0.875rem;
        }

        .country-option:hover {
            background: var(--gray-50);
        }

        .country-name {
            flex: 1;
            color: var(--gray-700);
        }

        .phone-number-input {
            flex: 1;
        }

        /* Privacy Notice */
        .privacy-notice {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .privacy-notice-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .privacy-notice-text {
            line-height: 1.5;
        }

        .privacy-notice-text a {
            color: var(--primary-blue);
            text-decoration: none;
        }

        .privacy-notice-text a:hover {
            text-decoration: underline;
        }

        /* Optional field indicator */
        .optional-indicator {
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: normal;
            margin-left: 0.5rem;
        }

        /* Skip option */
        .skip-phone {
            text-align: center;
            margin-top: 0.5rem;
        }

        .skip-phone-btn {
            background: none;
            border: none;
            color: var(--gray-500);
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: underline;
        }

        .skip-phone-btn:hover {
            color: var(--gray-700);
        }

        /* Rate limit error styles */
        .rate-limit-error {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .rate-limit-icon {
            color: var(--primary-red);
            flex-shrink: 0;
        }

        .rate-limit-icon svg {
            width: 20px;
            height: 20px;
        }

        .rate-limit-message {
            flex: 1;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .rate-limit-timer {
            width: 100%;
            height: 4px;
            background: rgba(220, 38, 38, 0.2);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .timer-progress {
            height: 100%;
            background: var(--primary-red);
            transition: width 1s linear;
        }

        /* Unified Auth Error Styles */
        .auth-error-container {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .auth-error-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #dc2626;
            font-weight: 600;
            flex-shrink: 0;
        }

        .auth-error-header svg {
            color: #dc2626;
            flex-shrink: 0;
        }

        .auth-error-message {
            color: #7f1d1d;
            margin: 0;
            line-height: 1.5;
        }

        /* Enhanced Error Summary Styles (for field validation only) */
        .error-summary {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .error-summary-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #dc2626;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .error-list {
            margin-left: 1.5rem;
            color: #7f1d1d;
        }

        .error-list li {
            margin-bottom: 0.25rem;
        }

        /* Button loading state */
        .btn-loading {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr;
            }
            
            .auth-hero {
                display: none;
            }
            
            .auth-form-container {
                padding: 2rem 1.5rem;
            }
            
            .social-login {
                flex-direction: column;
            }
            
            .social-btn {
                max-width: none;
            }

            .phone-input-container {
                flex-direction: row;
            }

            .country-code-selector {
                flex: 1;
            }

            .country-code-selector {
                width: 100%;
            }

            .country-code-btn {
                width: 100%;
                justify-content: space-between;
            }

            .phone-number-input {
                flex: 3;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Form Section -->
        <div class="auth-form-container">
            @error('rate_limit')
                <div class="rate-limit-error">
                    <div class="rate-limit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="rate-limit-message">
                        <strong>Hold on!</strong> {{ $message }}
                    </div>
                    <div class="rate-limit-timer">
                        <div class="timer-progress" data-seconds="{{ $error->retry_after ?? 60 }}"></div>
                    </div>
                </div>
            @enderror
            <div class="auth-header">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                </a>

                <div class="auth-tabs">
                    <a href="{{ route('login') }}" class="tab">Log In</a>
                    <a href="{{ route('signup') }}" class="tab active">Sign Up</a>
                </div>
            </div>

            <!-- Unified Auth Error Display -->
            @if ($errors->has('auth_error'))
            <div class="auth-error-container">
                <div class="auth-error-header">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Registration Error</span>
                </div>
                <p class="auth-error-message">{!! $errors->first('auth_error') !!}</p>
            </div>
            @endif

            <!-- Field Validation Errors Summary -->
            @if($errors->any() && !$errors->has('auth_error'))
            <div class="error-summary">
                <div class="error-summary-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Please fix the following errors:</span>
                </div>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                    <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('signup.submit') }}" id="signupForm">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input {{ $errors->has('name') ? 'error' : '' }}" 
                        value="{{ old('name') }}"
                        placeholder="Enter your full name"
                        required
                        autocomplete="name"
                    >
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input {{ $errors->has('email') ? 'error' : '' }}" 
                        value="{{ old('email') }}"
                        placeholder="Enter your email address"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="error-message">{!! $message !!}</div>
                    @enderror

                    @error('email_verification')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="country" class="form-label">Country</label>
                    <input 
                        type="text" 
                        id="country" 
                        name="country" 
                        class="form-input {{ $errors->has('country') ? 'error' : '' }}" 
                        value="{{ old('country') }}"
                        placeholder="Enter your country"
                        required
                    >
                    @error('country')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Phone Number Section with Privacy Notice -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        Phone Number 
                        <span class="optional-indicator">(Optional - for account security)</span>
                    </label>
                    
                    <!-- Privacy Notice -->
                    <div class="privacy-notice">
                        <div class="privacy-notice-header">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Why we ask for your phone number
                        </div>
                        <div class="privacy-notice-text">
                            Your phone number helps us secure your account with two-factor authentication and recover access if needed. 
                            We never share your number with third parties or use it for marketing. 
                            <a href="#" onclick="showPrivacyDetails()">Learn more about our privacy practices</a>.
                        </div>
                    </div>

                    <div class="phone-input-container">
                        <div class="country-code-selector">
                            <button type="button" class="country-code-btn" id="countryCodeBtn">
                                <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="country-flag" id="selectedFlag">
                                <span class="country-code" id="selectedCode">+233</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="country-code-dropdown" id="countryCodeDropdown">
                                <div class="country-search">
                                    <input type="text" placeholder="Search countries..." class="country-search-input" id="countrySearch">
                                </div>
                                <div class="country-list" id="countryList">
                                    <!-- Countries will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-input phone-number-input {{ $errors->has('phone') ? 'error' : '' }}" 
                            value="{{ old('phone') }}"
                            placeholder="24 123 4567"
                            autocomplete="tel"
                        >
                        <input type="hidden" id="country_code" name="country_code" value="+233">
                    </div>
                    
                    <div class="skip-phone">
                        <button type="button" class="skip-phone-btn" onclick="skipPhoneNumber()">
                            Skip for now - I'll add it later
                        </button>
                    </div>
                    
                    @error('phone')
                        <div class="error-message">{!! $message !!}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input {{ $errors->has('password') ? 'error' : '' }}" 
                        placeholder="Enter your password"
                        required
                    >
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="far fa-eye"></i>
                    </button>

                    <!-- Show password rules-toggle-btn -->
                    <div class="rules-toggle-container">
                        <button type="button" class="rules-toggle-btn" id="toggleRulesBtn">
                            <span>Show Password Rules</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>

                    <div class="password-rules hidden" id="passwordRules">
                        <div class="password-rule" id="rule-length">
                            <span class="rule-icon">!</span>
                            <span class="rule-text">At least 8 characters</span>
                        </div>
                        <div class="password-rule" id="rule-uppercase">
                            <span class="rule-icon">!</span>
                            <span class="rule-text">One uppercase letter</span>
                        </div>
                        <div class="password-rule" id="rule-lowercase">
                            <span class="rule-icon">!</span>
                            <span class="rule-text">One lowercase letter</span>
                        </div>
                        <div class="password-rule" id="rule-number">
                            <span class="rule-icon">!</span>
                            <span class="rule-text">One number</span>
                        </div>
                        <div class="password-rule" id="rule-symbol">
                            <span class="rule-icon">!</span>
                            <span class="rule-text">One symbol (@$!%*?&)</span>
                        </div>
                    </div>

                    <div class="password-strength">
                        <div class="strength-meter" id="strength-meter"></div>
                    </div>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-input" 
                        placeholder="Confirm your password"
                        required
                    >
                    <button type="button" class="password-toggle" id="toggleConfirmPassword">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">agree to <a href="#" class="terms-link">terms & conditions</a></label>
                </div>
                @error('terms')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button type="submit" class="submit-btn" id="signupBtn">
                    <span class="btn-text">SIGN UP</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Creating Account...
                    </span>
                </button>
            </form>

            <div class="divider">or sign up with</div>

            <div class="social-login">
                <a href="{{ route('auth.google', ['signup' => '1']) }}" class="social-btn google">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Sign up with Google</span>
                </a>

                <button class="social-btn apple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M17.05 12.04C17.02 8.97 19.62 7.45 19.71 7.39C18.41 5.57 16.37 5.33 15.72 5.31C14.1 5.12 12.63 6.4 11.76 6.4C10.88 6.4 9.69 5.34 8.3 5.38C6.52 5.43 4.92 6.51 4.04 8.2C2.06 11.69 3.5 16.45 5.28 19C6.16 20.32 7.2 21.79 8.58 21.75C9.92 21.71 10.41 20.85 12.07 20.85C13.72 20.85 14.17 21.75 15.58 21.72C17.02 21.69 17.92 20.37 18.79 19.04C19.8 17.56 20.25 16.11 20.27 16.04C20.23 16.02 17.08 14.79 17.05 12.04Z" fill="white"/>
                        <path d="M14.74 3.78C15.44 2.9 15.95 1.64 15.79 0.39C14.69 0.44 13.36 1.13 12.63 2.01C11.98 2.79 11.37 4.1 11.56 5.31C12.78 5.41 13.99 4.69 14.74 3.78Z" fill="white"/>
                    </svg>
                    <span>Sign up with Apple</span>
                </button>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="auth-hero">
            <div class="hero-content">
                <h1 class="hero-title">welcome<br>back</h1>
                <p class="hero-subtitle">
                    We have millions of teachers around the world who love to share knowledge.
                </p>
            </div>
            <div class="hero-accent"></div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Countries data
        const countries = [
            { name: 'Ghana', code: '+233', flag: 'gh' },
            { name: 'Nigeria', code: '+234', flag: 'ng' },
            { name: 'Kenya', code: '+254', flag: 'ke' },
            { name: 'South Africa', code: '+27', flag: 'za' },
            { name: 'United States', code: '+1', flag: 'us' },
            { name: 'United Kingdom', code: '+44', flag: 'gb' },
            { name: 'Canada', code: '+1', flag: 'ca' },
            { name: 'Australia', code: '+61', flag: 'au' },
            { name: 'Germany', code: '+49', flag: 'de' },
            { name: 'France', code: '+33', flag: 'fr' },
            { name: 'India', code: '+91', flag: 'in' },
            { name: 'China', code: '+86', flag: 'cn' },
            { name: 'Japan', code: '+81', flag: 'jp' },
            { name: 'Brazil', code: '+55', flag: 'br' },
            { name: 'Mexico', code: '+52', flag: 'mx' }
        ];

        // Focus on phone input if there's a phone error
        <?php if($errors->has('phone')): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const phoneInput = document.getElementById('phone');
                if (phoneInput) {
                    phoneInput.focus();
                    
                    // Expand the privacy notice for visibility
                    const privacyNotice = document.querySelector('.privacy-notice');
                    if (privacyNotice) {
                        privacyNotice.style.borderColor = 'var(--primary-red)';
                        privacyNotice.style.backgroundColor = 'rgba(220, 38, 38, 0.05)';
                    }
                    
                    // Highlight the skip button
                    const skipBtn = document.querySelector('.skip-phone-btn');
                    if (skipBtn) {
                        skipBtn.style.color = 'var(--primary-blue)';
                        skipBtn.style.fontWeight = '600';
                    }
                }
            });
        <?php endif; ?>

        // Password toggle functionality
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#password_confirmation');
        
        function togglePasswordVisibility(input, button) {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle eye icon
            const icon = button.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
        
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                togglePasswordVisibility(password, this);
            });
        }
        
        if (toggleConfirmPassword && confirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() {
                togglePasswordVisibility(confirmPassword, this);
            });
        }

        // Toggle password rules visibility
        const toggleRulesBtn = document.getElementById('toggleRulesBtn');
        const passwordRules = document.getElementById('passwordRules');

        if (toggleRulesBtn && passwordRules) {
            toggleRulesBtn.addEventListener('click', function() {
                passwordRules.classList.toggle('hidden');
                const isHidden = passwordRules.classList.contains('hidden');
                const span = this.querySelector('span');
                span.textContent = isHidden ? 'Show Password Rules' : 'Hide Password Rules';
                const icon = this.querySelector('i');
                icon.className = isHidden ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
            });
        }

        // Password strength validation
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Define validation checks
                const rules = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    symbol: /[@$!%*?&]/.test(password)
                };
                
                // Update UI for each rule
                Object.keys(rules).forEach(rule => {
                    const ruleElement = document.getElementById(`rule-${rule}`);
                    const icon = ruleElement.querySelector('.rule-icon');
                    const text = ruleElement.querySelector('.rule-text');
                    
                    if (rules[rule]) {
                        icon.textContent = '✓';
                        icon.classList.add('valid');
                        text.classList.add('valid');
                    } else {
                        icon.textContent = '!';
                        icon.classList.remove('valid');
                        text.classList.remove('valid');
                    }
                });

                const strength = calculateStrength(password);
                const meter = document.getElementById('strength-meter');
                meter.className = 'strength-meter';
                meter.classList.add(`strength-${strength}`);
            });
        }

        // Calculate password strength
        function calculateStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[@$!%*?&]/.test(password)) strength++;
            
            // Return 4 if strength is 5 to maintain compatibility
            return strength === 5 ? 4 : strength;
        }

        // Phone number functionality
        function initializePhoneInput() {
            const countryCodeBtn = document.getElementById('countryCodeBtn');
            const countryCodeDropdown = document.getElementById('countryCodeDropdown');
            const countryList = document.getElementById('countryList');
            const countrySearch = document.getElementById('countrySearch');
            const selectedFlag = document.getElementById('selectedFlag');
            const selectedCode = document.getElementById('selectedCode');
            const countryCodeInput = document.getElementById('country_code');

            if (!countryCodeBtn || !countryList) return;

            // Populate country list
            function populateCountries(filteredCountries = countries) {
                countryList.innerHTML = '';
                filteredCountries.forEach(country => {
                    const option = document.createElement('div');
                    option.className = 'country-option';
                    option.innerHTML = `
                        <img src="https://flagcdn.com/w20/${country.flag}.png" alt="${country.name}" class="country-flag">
                        <span class="country-name">${country.name}</span>
                        <span class="country-code">${country.code}</span>
                    `;
                    option.addEventListener('click', () => selectCountry(country));
                    countryList.appendChild(option);
                });
            }

            // Select country
            function selectCountry(country) {
                selectedFlag.src = `https://flagcdn.com/w20/${country.flag}.png`;
                selectedFlag.alt = country.name;
                selectedCode.textContent = country.code;
                countryCodeInput.value = country.code;
                countryCodeDropdown.classList.remove('active');
            }

            // Toggle dropdown
            countryCodeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                countryCodeDropdown.classList.toggle('active');
                if (countryCodeDropdown.classList.contains('active')) {
                    countrySearch.focus();
                }
            });

            // Search functionality
            if (countrySearch) {
                countrySearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filtered = countries.filter(country => 
                        country.name.toLowerCase().includes(searchTerm) ||
                        country.code.includes(searchTerm)
                    );
                    populateCountries(filtered);
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!countryCodeBtn.contains(e.target) && !countryCodeDropdown.contains(e.target)) {
                    countryCodeDropdown.classList.remove('active');
                }
            });

            // Auto-detect country based on user's location (optional)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // This would typically involve a geolocation API call
                    // For now, we'll default to Ghana
                    const defaultCountry = countries.find(c => c.flag === 'gh');
                    if (defaultCountry) {
                        selectCountry(defaultCountry);
                    }
                });
            }

            // Initialize with default countries
            populateCountries();
        }

        // Skip phone number functionality
        function skipPhoneNumber() {
            const phoneInput = document.getElementById('phone');
            const countryCodeInput = document.getElementById('country_code');
            const phoneContainer = phoneInput.closest('.form-group');
            
            // Clear phone inputs
            phoneInput.value = '';
            countryCodeInput.value = '';
            
            // Hide the phone section with animation
            phoneContainer.style.opacity = '0.5';
            phoneContainer.style.pointerEvents = 'none';
            
            // Show confirmation
            const skipBtn = phoneContainer.querySelector('.skip-phone-btn');
            skipBtn.textContent = '✓ Phone number skipped - you can add it later in settings';
            skipBtn.style.color = 'var(--success-green)';
            skipBtn.disabled = true;
        }

        // Privacy and terms functions
        function showPrivacyDetails() {
            alert('Privacy Details:\n\n• Your phone number is encrypted and stored securely\n• We only use it for account verification and security\n• You can remove it anytime from your profile settings\n• We never share your data with third parties\n• You can request data deletion at any time');
        }

        function showTerms() {
            alert('Terms of Service would be displayed in a modal or separate page.');
        }

        function showPrivacy() {
            alert('Privacy Policy would be displayed in a modal or separate page.');
        }

        // Social login functions
        function signUpWithGoogle() {
            // Implement Google OAuth
            alert('Google sign-up would be implemented here');
        }

        function signUpWithApple() {
            // Implement Apple OAuth
            alert('Apple sign-up would be implemented here');
        }
        
        // Initialize rate limit timers
        document.querySelectorAll('.timer-progress').forEach(timer => {
            const seconds = parseInt(timer.dataset.seconds);
            let remaining = seconds;

            const interval = setInterval(() => {
                remaining--;
                const percentage = (remaining / seconds) * 100;
                timer.style.width = `${percentage}%`;

                if (remaining <= 0) {
                    clearInterval(interval);
                    timer.closest('.rate-limit-error').remove();
                }
            }, 1000);
        });

        // Initialize phone input
        initializePhoneInput();

        // Form submission handler
        const signupForm = document.getElementById('signupForm');
        const signupBtn = document.getElementById('signupBtn');
        const btnText = signupBtn ? signupBtn.querySelector('.btn-text') : null;
        const btnLoading = signupBtn ? signupBtn.querySelector('.btn-loading') : null;

        // Reset button state on page load (in case of redirect with errors)
        if (signupBtn && btnText && btnLoading) {
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            signupBtn.disabled = false;
            signupBtn.style.opacity = '1';
        }

        if (signupForm && signupBtn && btnText && btnLoading) {
            signupForm.addEventListener('submit', function(e) {
                // Show loading state
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
                signupBtn.disabled = true;
                signupBtn.style.opacity = '0.7';

                // Add a timeout to re-enable the button in case of issues
                setTimeout(() => {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    signupBtn.disabled = false;
                    signupBtn.style.opacity = '1';
                }, 10000); // 10 seconds timeout
            });
        }

        // Set up periodic ping to keep session alive
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                fetch('/ping', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }
        }, 300000); // 5 minutes
    </script>

    <!-- Auto-scroll to errors on page load -->
    @if ($errors->any())
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            const errorElement = document.querySelector('.auth-error-container') ||
                               document.querySelector('.error-summary') ||
                               document.querySelector('.rate-limit-error') ||
                               document.querySelector('.error-message');
            if (errorElement) {
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
    @endif

    <!-- Force button reset if there are validation errors -->
    @if($errors->any())
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            const signupBtn = document.getElementById('signupBtn');
            const btnText = signupBtn ? signupBtn.querySelector('.btn-text') : null;
            const btnLoading = signupBtn ? signupBtn.querySelector('.btn-loading') : null;

            if (signupBtn && btnText && btnLoading) {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                signupBtn.disabled = false;
                signupBtn.style.opacity = '1';
            }
        });
    </script>
    @endif
</body>
</html>