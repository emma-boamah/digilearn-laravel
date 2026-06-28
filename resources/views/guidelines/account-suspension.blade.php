@extends('layouts.app')

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Performance optimized styles with Dark Mode & Mobile SafeArea */
        .guidelines-page {
            background-color: var(--bg-main);
            color: var(--text-main);
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            padding-bottom: calc(4rem + env(safe-area-inset-bottom, 0px));
            padding-left: env(safe-area-inset-left, 0px);
            padding-right: env(safe-area-inset-right, 0px);
        }

        .guidelines-header {
            padding: calc(4rem + env(safe-area-inset-top, 0px)) 2rem 2rem;
            text-align: center;
        }

        .guidelines-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .guidelines-header p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            max-width: 1200px; 
            margin: 0 auto;
            padding: 0 2rem;
        }

        .guidelines-document {
            background-color: var(--bg-surface);
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            margin-top: 1rem;
        }

        [data-theme="dark"] .guidelines-document {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border-color: var(--gray-800);
        }

        .guidelines-section {
            margin-bottom: 3.5rem;
        }

        .guidelines-section:last-child {
            margin-bottom: 0;
        }

        .guidelines-section h2 {
            color: var(--text-main);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        [data-theme="dark"] .guidelines-section h2 {
            border-bottom-color: var(--gray-800);
        }

        .guidelines-section h2 i {
            color: var(--primary-red);
            font-size: 1.25rem;
        }

        .guidelines-section p {
            margin-bottom: 1.25rem;
            font-size: 1.05rem;
            color: var(--text-muted);
        }

        .guidelines-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem 0;
        }

        .guidelines-list li {
            margin-bottom: 0.75rem;
            padding-left: 1.75rem;
            position: relative;
            color: var(--text-main);
            font-size: 1.05rem;
        }

        .guidelines-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 10px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--accent); /* Blue accent */
        }
        
        .guidelines-list.issue-list li::before {
            background-color: var(--primary-red); /* Red accent */
        }

        .contact-block {
            background-color: var(--bg-main);
            border-left: 4px solid var(--accent);
            padding: 2rem;
            border-radius: 0 8px 8px 0;
            margin-top: 3rem;
        }

        .contact-block h3 {
            margin-top: 0;
            color: var(--text-main);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .contact-block p {
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        .contact-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .contact-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background-color: var(--accent);
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.15s ease;
            font-size: 0.95rem;
        }

        .contact-link:hover {
            background-color: var(--secondary-blue-hover);
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .guidelines-header h1 {
                font-size: 2rem;
            }
            .guidelines-document {
                padding: 1.5rem;
            }
            .contact-block {
                padding: 1.5rem;
            }
            .contact-links {
                flex-direction: column;
            }
            .contact-link {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }
        }
    </style>

    <div class="guidelines-page">
        <!-- Minimal Header -->
        <header class="guidelines-header">
            <div class="container">
                <h1>Account Suspension Guidelines</h1>
                <p>Understanding our policies and how to maintain a positive learning environment</p>
            </div>
        </header>

        <!-- Guidelines Content -->
        <section class="guidelines-content">
            <div class="container">
                <div class="guidelines-document">
                    
                    <!-- Reasons for Suspension -->
                    <div class="guidelines-section">
                        <h2><i class="fas fa-ban"></i> Reasons for Account Suspension</h2>
                        <p>Accounts may be suspended to maintain a safe and positive learning environment for all users. Common reasons include:</p>
                        <ul class="guidelines-list issue-list">
                            <li>Violation of community guidelines or terms of service</li>
                            <li>Sharing inappropriate or harmful content</li>
                            <li>Academic dishonesty or cheating in assessments</li>
                            <li>Harassment or disrespectful behavior towards other users</li>
                            <li>Multiple failed login attempts or security concerns</li>
                            <li>Non-payment of subscription fees</li>
                            <li>Use of unauthorized third-party tools during quizzes</li>
                        </ul>
                    </div>

                    <!-- What Happens During Suspension -->
                    <div class="guidelines-section">
                        <h2><i class="fas fa-pause-circle"></i> What Happens During Suspension</h2>
                        <p>When your account is suspended, you will experience the following restrictions on the platform:</p>
                        <ul class="guidelines-list issue-list">
                            <li>Unable to access the learning platform and dashboard</li>
                            <li>Cannot view or download course materials</li>
                            <li>Quiz and assessment access is blocked</li>
                            <li>Community features and discussions become unavailable</li>
                            <li>Progress tracking is temporarily paused</li>
                        </ul>
                    </div>

                    <!-- Appeal Process -->
                    <div class="guidelines-section">
                        <h2><i class="fas fa-file-signature"></i> How to Appeal Your Suspension</h2>
                        <p>If you believe your suspension was made in error or you would like to discuss reinstatement, follow these steps to appeal:</p>
                        <ul class="guidelines-list">
                            <li><strong>Review the suspension reason:</strong> Check your email or account notifications for details about why your account was suspended.</li>
                            <li><strong>Gather evidence:</strong> Prepare any relevant information that may help explain the situation or demonstrate your commitment to our community guidelines.</li>
                            <li><strong>Contact support:</strong> Reach out to our support team using one of the methods below with "Account Suspension Appeal" in the subject line.</li>
                            <li><strong>Wait for review:</strong> Our team will review your appeal within 3-5 business days and respond with a decision.</li>
                            <li><strong>Follow up if needed:</strong> If you haven't heard back within a week, feel free to follow up politely.</li>
                        </ul>
                    </div>

                    <!-- Prevention Tips -->
                    <div class="guidelines-section">
                        <h2><i class="fas fa-shield-alt"></i> How to Avoid Account Suspension</h2>
                        <p>Follow these best practices to maintain a positive and active account status:</p>
                        <ul class="guidelines-list">
                            <li>Always follow our community guidelines and terms of service</li>
                            <li>Be respectful and supportive in discussions and comments</li>
                            <li>Complete assessments honestly and independently</li>
                            <li>Keep your account information secure and up-to-date</li>
                            <li>Report any suspicious activity or inappropriate content</li>
                            <li>Stay current with subscription payments if applicable</li>
                            <li>Use the platform as intended for educational purposes</li>
                        </ul>
                    </div>

                    <!-- Contact Information -->
                    <div class="contact-block">
                        <h3>Need Help with Your Account?</h3>
                        <p>Our support team is here to assist you with any questions about account suspension or reinstatement.</p>
                        <div class="contact-links">
                            <a href="{{ route('contact') }}" class="contact-link">
                                <i class="fas fa-question-circle"></i>
                                General Contact
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </section>
    </div>
@endsection