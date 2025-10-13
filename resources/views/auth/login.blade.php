<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShoutOutGh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --accent: #dc2626;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --light-gray: #e2e8f0;
            --border: #cbd5e1;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        body {
            background-color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: var(--dark);
            background-image: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            box-shadow: var(--shadow);
            border-radius: 16px;
            overflow: hidden;
            background: white;
        }
        
        .hero-section {
            flex: 1;
            background: linear-gradient(rgba(30, 41, 59, 0.85), rgba(30, 41, 59, 0.9)), url('https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80') center/cover;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
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
        
        .hero-content {
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
            max-width: 500px;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .form-section {
            flex: 1;
            background: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-container {
            max-width: 450px;
            margin: 0 auto;
            width: 100%;
        }
        
        .form-header {
            margin-bottom: 2.5rem;
            text-align: center;
        }
        
        .form-header h2 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: var(--dark);
            background: linear-gradient(90deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-header p {
            color: var(--gray);
            font-size: 1.1rem;
            margin-top: 10px;
        }
        
        .auth-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .tab {
            padding: 1rem 2rem;
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            font-size: 1.1rem;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }
        
        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.7rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 1rem;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group i.input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
            z-index: 2;
            pointer-events: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }
        
        .password-toggle:hover {
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .password-toggle:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .password-toggle i {
            font-size: 1rem;
            pointer-events: none;
        }
        
        input {
            width: 100%;
            padding: 16px 52px 16px 52px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background-color: var(--light);
            position: relative;
            z-index: 1;
        }
        
        input.error {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.8rem;
            font-size: 0.95rem;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .remember input {
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
            padding: 0;
        }
        
        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }
        
        .btn {
            width: 100%;
            padding: 17px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.8rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }
        
        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin-bottom: 1.8rem;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: var(--light-gray);
        }
        
        .divider span {
            padding: 0 1.2rem;
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .social-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: 1px solid var(--border);
            text-decoration-line: none;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .social-btn.google {
            color: #DB4437;
            background: white;
        }
        
        .social-btn.apple {
            color: #000;
            background: white;
        }
        
        .social-btn i {
            font-size: 1.4rem;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 2.5rem;
            color: var(--gray);
            font-size: 1rem;
        }
        
        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: all 0.2s ease;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--accent);
            font-size: 0.9rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .error-message i {
            font-size: 1rem;
        }
        
        .rate-limit-error {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .rate-limit-icon {
            flex-shrink: 0;
            width: 1.75rem;
            height: 1.75rem;
            color: #dc2626;
        }
        
        .rate-limit-message {
            flex: 1;
            color: #7f1d1d;
        }
        
        .rate-limit-message strong {
            font-weight: 600;
        }
        
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                max-width: 600px;
            }
            
            .hero-section {
                padding: 30px 20px;
                border-radius: 16px 16px 0 0;
            }
            
            .form-section {
                padding: 40px 30px;
                border-radius: 0 0 16px 16px;
            }
            
            .logo {
                margin-bottom: 30px;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .hero-section {
                display: none;
            }
            
            .tab {
                padding: 1rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="logo">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                </a>
            </div>
            
            <div class="hero-content">
                <h1>Welcome back</h1>
                <p>We have millions of teachers around the world who love to share knowledge.</p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">2M+</div>
                        <div class="stat-label">Teachers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Section -->
        <div class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                    </a>
                    <p>Enter your email and password to continue</p>
                </div>
                
                <div class="auth-tabs">
                    <a href="{{ route('login') }}" class="tab active">Log In</a>
                    <a href="{{ route('signup') }}" class="tab">Sign Up</a>
                </div>
                
                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                id="email"
                                autocomplete="email"
                                name="email" 
                                placeholder="Enter your email address"
                                value="{{ old('email') }}"
                                required
                                class="{{ $errors->has('email') ? 'error' : '' }}"
                            >
                        </div>
                        @error('email')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                id="password"
                                autocomplete="current-password"
                                name="password" 
                                placeholder="Enter your password"
                                required
                                class="{{ $errors->has('password') ? 'error' : '' }}"
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="far fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </div>
                        @enderror
                    </div>
                    
                    <div class="options">
                        <div class="remember">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn" id="loginBtn">Log In</button>
                    
                    <div class="divider">
                        <span>or continue with</span>
                    </div>
                    
                    <div class="social-login">
                        <a href="{{ route('auth.google') }}" type="button" class="social-btn google">
                            <i class="fab fa-google"></i>
                        </a>
                        <button type="button" class="social-btn apple">
                            <i class="fab fa-apple"></i>
                        </button>
                    </div>
                    
                    <div class="signup-link">
                        Don't have an account? <a href="{{ route('signup') }}">Sign Up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (togglePassword && passwordInput && toggleIcon) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Toggle password visibility
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    
                    // Update icon
                    if (isPassword) {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                    
                    // Keep focus on password input
                    passwordInput.focus();
                });
                
                // Prevent form submission when clicking toggle
                togglePassword.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                });
            }
            
            // Form submission loading state
            const form = document.querySelector('form');
            const loginBtn = document.getElementById('loginBtn');
            
            if (form && loginBtn) {
                form.addEventListener('submit', function() {
                    const originalText = loginBtn.innerHTML;
                    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                    loginBtn.disabled = true;
                    
                    // Revert after 10 seconds in case of slow response
                    setTimeout(() => {
                        loginBtn.innerHTML = originalText;
                        loginBtn.disabled = false;
                    }, 10000);
                });
            }

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
        });
    </script>
</body>
</html>