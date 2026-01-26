<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ShoutOutGh</title>
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
            max-width: 500px;
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
        
        .error-message {
            color: var(--accent);
            font-size: 0.85rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
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
            z-index: 3;
        }

        .password-toggle:hover {
            color: var(--primary);
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
                <h2>Reset Password</h2>
                <p>Create a new strong password for your account.</p>
            </div>
            
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="New password" 
                            required 
                            autofocus
                            class="{{ $errors->has('password') ? 'error' : '' }}"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="far fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Confirm new password" 
                            required 
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                            <i class="far fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn">Reset Password</button>
            </form>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
