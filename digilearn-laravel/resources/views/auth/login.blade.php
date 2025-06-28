<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShoutOutGh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        }
        
        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        input {
            width: 100%;
            padding: 16px 16px 16px 52px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background-color: var(--light);
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
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
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
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 20px;
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
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                </a>
                
                <div class="auth-tabs">
                    <a href="{{ route('login') }}" class="tab active">Log In</a>
                    <a href="{{ route('signup') }}" class="tab">Sign Up</a>
                </div>
                
                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="form-group">
                        <label for="email">Username or email</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="Enter your username or email"
                                required
                            >
                        </div>
                        <!-- Error message placeholder -->
                        <div class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Please enter a valid email address</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                        </div>
                        <!-- Error message placeholder -->
                        <div class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Password must be at least 8 characters</span>
                        </div>
                    </div>
                    
                    <div class="options">
                        <div class="remember">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn">Log In</button>
                    
                    <div class="divider">
                        <span>or continue with</span>
                    </div>
                    
                    <div class="social-login">
                        <div class="social-btn google">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="social-btn apple">
                            <i class="fab fa-apple"></i>
                        </div>
                    </div>
                    
                    <div class="signup-link">
                        Don't have an account? <a href="{{ route('signup') }}">Sign Up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const errorMessages = document.querySelectorAll('.error-message');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                
                // Clear previous errors
                errorMessages.forEach(el => el.style.display = 'none');
                emailInput.style.borderColor = '';
                passwordInput.style.borderColor = '';
                
                // Validate email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailInput.value.trim()) {
                    showError(emailInput, 0, 'Email is required');
                    isValid = false;
                } else if (!emailRegex.test(emailInput.value)) {
                    showError(emailInput, 0, 'Please enter a valid email address');
                    isValid = false;
                }
                
                // Validate password
                if (!passwordInput.value) {
                    showError(passwordInput, 1, 'Password is required');
                    isValid = false;
                } else if (passwordInput.value.length < 8) {
                    showError(passwordInput, 1, 'Password must be at least 8 characters');
                    isValid = false;
                }
                
                if (isValid) {
                    // Simulate form submission
                    showLoading();
                    setTimeout(() => {
                        alert('Login successful! Redirecting to dashboard...');
                        form.submit();
                    }, 1500);
                }
            });
            
            function showError(input, index, message) {
                input.style.borderColor = '#ef4444';
                errorMessages[index].style.display = 'flex';
                errorMessages[index].querySelector('span').textContent = message;
                input.focus();
            }
            
            function showLoading() {
                const btn = document.querySelector('.btn');
                const originalText = btn.textContent;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                btn.disabled = true;
                
                // Revert after 3 seconds for demo
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 3000);
            }
            
            // Simulate rate limit error for demo
            setTimeout(() => {
                const rateLimitError = document.createElement('div');
                rateLimitError.className = 'error-message';
                rateLimitError.style.marginBottom = '20px';
                rateLimitError.style.display = 'flex';
                rateLimitError.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Too many login attempts. Please try again in 2 minutes.</span>
                `;
                form.prepend(rateLimitError);
            }, 2000);
        });
    </script>
</body>
</html>