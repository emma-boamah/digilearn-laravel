<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - ShoutOutGh</title>
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
        }
        
        .form-section {
            flex: 1;
            background: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
        
        input {
            width: 100%;
            padding: 16px 52px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1.2rem;
            letter-spacing: 2px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: var(--light);
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
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }
        
        .error-message {
            color: var(--accent);
            font-size: 0.9rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 0.5rem 0.75rem;
            background-color: rgba(220, 38, 38, 0.05);
            border-radius: 6px;
            border-left: 3px solid var(--accent);
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.95rem;
        }

        .back-link a:hover {
            color: var(--dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <div class="form-header">
                <h2>Check your email</h2>
                <p>We sent a verification code to <br><strong>{{ session('otp_email') }}</strong></p>
            </div>
            
            <form method="POST" action="{{ route('verify-otp.submit') }}">
                @csrf
                
                <div class="form-group">
                    <label for="otp">Verification Code</label>
                    <div class="input-group">
                        <i class="fas fa-key input-icon"></i>
                        <input 
                            type="text" 
                            id="otp"
                            name="otp" 
                            placeholder="123456"
                            maxlength="6"
                            required
                            autofocus
                        >
                    </div>
                    @if ($errors->has('otp'))
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first('otp') }}</span>
                    </div>
                    @endif
                </div>
                
                <button type="submit" class="btn">Verify & Create Account</button>
            </form>
            
            <div class="back-link">
                <a href="{{ route('signup') }}">Start over</a>
            </div>
        </div>
    </div>
</body>
</html>
