<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'DigiLearn') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <style>
        :root {
            --primary-red: #dc2626;
            --primary-red-hover: #b91c1c;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-900: #111827;
            --font-family-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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
        }

        .auth-container {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        .auth-hero {
            position: relative;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
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
            background-image: url('{{ asset("images/auth-hero.png") }}');
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
        }

        .hero-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .hero-accent {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4rem;
            background-color: var(--primary-red);
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
        }

        .tab {
            flex: 1;
            padding: 0.75rem 1rem;
            text-align: center;
            text-decoration: none;
            color: var(--gray-500);
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab.active {
            color: var(--primary-red);
            border-bottom-color: var(--primary-red);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s ease;
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

        .forgot-password {
            text-align: right;
            margin-bottom: 1.5rem;
        }

        .forgot-password a {
            color: var(--primary-red);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
        }

        .submit-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .social-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .social-btn {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            border: 1px solid var(--gray-300);
            background-color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .social-btn:hover {
            background-color: var(--gray-50);
        }

        .social-btn.google {
            background-color: #4285f4;
            border-color: #4285f4;
        }

        .social-btn.facebook {
            background-color: #1877f2;
            border-color: #1877f2;
        }

        .social-btn svg {
            width: 1.25rem;
            height: 1.25rem;
            color: var(--gray-600);
        }

        .social-btn.google svg,
        .social-btn.facebook svg {
            color: var(--white);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Hero Section -->
        <div class="auth-hero">
            <div class="hero-content">
                <h1 class="hero-title">welcome<br>back</h1>
                <p class="hero-subtitle">
                    We're so excited to see you again!<br>
                    Let's pick up where we left off.
                </p>
            </div>
            <div class="hero-accent"></div>
        </div>

        <!-- Form Section -->
        <div class="auth-form-container">
            <div class="auth-header">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                </a>
                <div class="auth-tabs">
                    <a href="{{ route('login') }}" class="tab active">Log In</a>
                    <a href="{{ route('signup') }}" class="tab">Sign Up</a>
                </div>
            </div>

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Username or email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input {{ $errors->has('email') ? 'error' : '' }}" 
                        value="{{ old('email') }}"
                        placeholder="Enter your email address"
                        required
                    >
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
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
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="forgot-password">
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="submit-btn">Log In</button>
            </form>

            <div class="social-login">
                <button class="social-btn google">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </button>
                <button class="social-btn facebook">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
