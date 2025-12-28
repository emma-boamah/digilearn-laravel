<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - ShoutOutGh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --accent: #dc2626;
            --warning: #f59e0b;
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
            background: linear-gradient(rgba(245, 158, 11, 0.85), rgba(245, 158, 11, 0.9)), url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80') center/cover;
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
            background: linear-gradient(90deg, var(--warning), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-header p {
            color: var(--gray);
            font-size: 1.1rem;
            margin-top: 10px;
        }

        .suspension-notice {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .suspension-icon {
            font-size: 3rem;
            color: #f59e0b;
            margin-bottom: 1rem;
        }

        .suspension-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 1rem;
        }

        .suspension-message {
            color: #78350f;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .suspension-reason {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .reason-label {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.5rem;
        }

        .reason-text {
            color: #78350f;
            font-style: italic;
        }

        .help-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 2rem;
        }

        .help-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .help-options {
            display: grid;
            gap: 1rem;
        }

        .help-option {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 16px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }

        .help-option:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .help-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .help-content h3 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .help-content p {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.4;
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(90deg, var(--gray), #475569);
            box-shadow: 0 4px 10px rgba(100, 116, 139, 0.3);
        }

        .btn-secondary:hover {
            box-shadow: 0 6px 15px rgba(100, 116, 139, 0.4);
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
            transition: all 0.2s ease;
        }

        .back-link a:hover {
            text-decoration: underline;
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
            .hero-section {
                display: none;
            }

            .help-option {
                flex-direction: column;
                text-align: center;
            }

            .help-icon {
                align-self: center;
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
                <h1>Account Access</h1>
                <p>Your account has been temporarily restricted. We're here to help you get back on track.</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" class="logo-image">
                    </a>
                    <p>Account suspension notice</p>
                </div>

                <div class="suspension-notice">
                    <div class="suspension-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="suspension-title">Account Suspended</h3>
                    <p class="suspension-message">
                        Your account has been suspended and you cannot access the platform at this time.
                        This suspension helps maintain a safe and positive learning environment for all users.
                    </p>

                    @if(isset($suspensionReason) && !empty($suspensionReason))
                    <div class="suspension-reason">
                        <div class="reason-label">Reason for suspension:</div>
                        <div class="reason-text">{{ $suspensionReason }}</div>
                    </div>
                    @endif
                </div>

                <div class="help-section">
                    <h3 class="help-title">
                        <i class="fas fa-life-ring"></i>
                        How to Get Help
                    </h3>

                    <div class="help-options">
                        <a href="mailto:support@shoutoutgh.com?subject=Account Suspension Appeal&body=Dear Support Team,%0A%0AMy account ({{ $userEmail ?? 'email not provided' }}) has been suspended. I would like to appeal this suspension.%0A%0APlease provide details about my suspension and how I can resolve this issue.%0A%0AThank you." class="help-option">
                            <div class="help-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="help-content">
                                <h3>Email Support</h3>
                                <p>Contact our support team to appeal your suspension and get personalized assistance.</p>
                            </div>
                        </a>

                        <a href="tel:+233123456789" class="help-option">
                            <div class="help-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="help-content">
                                <h3>Call Support</h3>
                                <p>Speak directly with our support team for immediate assistance with your account.</p>
                            </div>
                        </a>

                        <a href="{{ route('contact') }}" class="help-option">
                            <div class="help-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="help-content">
                                <h3>FAQ & Guidelines</h3>
                                <p>Review our community guidelines and frequently asked questions about account suspensions.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <a href="{{ route('login') }}" class="btn btn-secondary">Back to Login</a>

                <div class="back-link">
                    <a href="{{ route('home') }}">← Return to Homepage</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>