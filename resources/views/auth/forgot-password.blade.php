<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ShoutOutGh</title>
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
            max-width: 500px; /* Smaller max-width for single column */
            margin: 0 auto;
            box-shadow: var(--shadow);
            border-radius: 16px;
            overflow: hidden;
            background: white;
            flex-direction: column;
        }

        .form-section {
            background: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: inherit;
            margin-bottom: 2rem;
        }

        .logo-image {
            height: 40px;
            object-fit: contain;
        }
        
        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .form-header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .form-header p {
            color: var(--gray);
            font-size: 1rem;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
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
            font-size: 1rem;
            z-index: 2;
            pointer-events: none;
        }
        
        input {
            width: 100%;
            padding: 14px 45px 14px 45px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--light);
        }
        
        input.error {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
            color: var(--gray);
            font-size: 1rem;
        }
        
        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--accent);
            font-size: 0.85rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            display: flex;
            gap: 10px;
            align-items: start;
        }
        
        .alert-success i {
             margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
            </a>
            
            <div class="form-header">
                <h2>Forgot Password?</h2>
                <p>No worries! Enter your email and we'll send you reset instructions.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
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
                
                <button type="submit" class="btn">Send Reset Link</button>
            </form>
            
            <div class="back-link">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
