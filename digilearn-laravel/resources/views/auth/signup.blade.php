<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - {{ config('app.name', 'DigiLearn') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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

            <form method="POST" action="{{ route('signup.submit') }}">
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
                    >
                    @error('email')
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

                <button type="submit" class="submit-btn">SIGN UP</button>
            </form>

            <div class="divider">or register with</div>

            <div class="social-login">
                <button class="social-btn google">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Google</span>
                </button>
                
                <button class="social-btn apple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M17.05 12.04C17.02 8.97 19.62 7.45 19.71 7.39C18.41 5.57 16.37 5.33 15.72 5.31C14.1 5.12 12.63 6.4 11.76 6.4C10.88 6.4 9.69 5.34 8.3 5.38C6.52 5.43 4.92 6.51 4.04 8.2C2.06 11.69 3.5 16.45 5.28 19C6.16 20.32 7.2 21.79 8.58 21.75C9.92 21.71 10.41 20.85 12.07 20.85C13.72 20.85 14.17 21.75 15.58 21.72C17.02 21.69 17.92 20.37 18.79 19.04C19.8 17.56 20.25 16.11 20.27 16.04C20.23 16.02 17.08 14.79 17.05 12.04Z" fill="white"/>
                        <path d="M14.74 3.78C15.44 2.9 15.95 1.64 15.79 0.39C14.69 0.44 13.36 1.13 12.63 2.01C11.98 2.79 11.37 4.1 11.56 5.31C12.78 5.41 13.99 4.69 14.74 3.78Z" fill="white"/>
                    </svg>
                    <span>Apple</span>
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
        
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(password, this);
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPassword, this);
        });
        
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
    </script>
</body>
</html>