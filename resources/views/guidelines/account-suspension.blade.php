@extends('layouts.app')

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Account Suspension Guidelines Styles */
        .guidelines-hero {
            position: relative;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            text-align: center;
        }

        .guidelines-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .guidelines-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .guidelines-content {
            padding: 4rem 0;
            background: #f8fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .guidelines-grid {
            display: grid;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 768px) {
            .guidelines-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .guidelines-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .guidelines-section h2 {
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guidelines-section h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: #f59e0b;
            border-radius: 2px;
        }

        .guidelines-list {
            list-style: none;
            padding: 0;
        }

        .guidelines-list li {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .guidelines-list li::before {
            content: '•';
            color: #f59e0b;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .appeal-process {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .appeal-process h3 {
            color: #92400e;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .appeal-process ol {
            padding-left: 1.5rem;
        }

        .appeal-process li {
            margin-bottom: 0.5rem;
            color: #78350f;
        }

        .contact-info {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }

        .contact-info h3 {
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .contact-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .contact-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .contact-link:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .prevention-tips {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #10b981;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .prevention-tips h3 {
            color: #065f46;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .prevention-tips ul {
            padding-left: 1.5rem;
        }

        .prevention-tips li {
            margin-bottom: 0.5rem;
            color: #047857;
        }

        .back-link {
            text-align: center;
            margin-top: 3rem;
        }

        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .back-link a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
    </style>

    <!-- Hero Section -->
    <section class="guidelines-hero">
        <div>
            <h1>Account Suspension Guidelines</h1>
            <p>Understanding our policies and how to maintain a positive learning environment</p>
        </div>
    </section>

    <!-- Guidelines Content -->
    <section class="guidelines-content">
        <div class="container">
            <div class="guidelines-grid">
                <!-- Reasons for Suspension -->
                <div class="guidelines-section">
                    <h2>🚫 Reasons for Account Suspension</h2>
                    <p>Accounts may be suspended to maintain a safe and positive learning environment for all users. Common reasons include:</p>
                    <ul class="guidelines-list">
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
                    <h2>⏸️ What Happens During Suspension</h2>
                    <p>When your account is suspended, you will experience the following:</p>
                    <ul class="guidelines-list">
                        <li>Unable to access the learning platform</li>
                        <li>Cannot view or download course materials</li>
                        <li>Quiz and assessment access is blocked</li>
                        <li>Community features become unavailable</li>
                        <li>Progress tracking is temporarily paused</li>
                    </ul>
                </div>
            </div>

            <!-- Appeal Process -->
            <div class="appeal-process">
                <h3>📝 How to Appeal Your Suspension</h3>
                <p>If you believe your suspension was made in error or you would like to discuss reinstatement, follow these steps:</p>
                <ol>
                    <li><strong>Review the suspension reason:</strong> Check your email or account notifications for details about why your account was suspended.</li>
                    <li><strong>Gather evidence:</strong> Prepare any relevant information that may help explain the situation or demonstrate your commitment to our community guidelines.</li>
                    <li><strong>Contact support:</strong> Reach out to our support team using one of the methods below with "Account Suspension Appeal" in the subject line.</li>
                    <li><strong>Wait for review:</strong> Our team will review your appeal within 3-5 business days and respond with a decision.</li>
                    <li><strong>Follow up if needed:</strong> If you haven't heard back within a week, feel free to follow up politely.</li>
                </ol>
            </div>

            <!-- Prevention Tips -->
            <div class="prevention-tips">
                <h3>✅ How to Avoid Account Suspension</h3>
                <p>Follow these guidelines to maintain a positive account status:</p>
                <ul>
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
            <div class="contact-info">
                <h3>Need Help with Your Account?</h3>
                <p>Our support team is here to assist you with any questions about account suspension or reinstatement.</p>
                <div class="contact-links">
                    <a href="mailto:support@shoutoutgh.com?subject=Account Suspension Appeal&body=Dear Support Team,%0A%0AMy account has been suspended. I would like to appeal this suspension.%0A%0APlease provide details about my suspension and how I can resolve this issue.%0A%0AThank you." class="contact-link">
                        <i class="fas fa-envelope"></i>
                        Email Support
                    </a>
                    <a href="tel:+233123456789" class="contact-link">
                        <i class="fas fa-phone"></i>
                        Call Support
                    </a>
                    <a href="{{ route('contact') }}" class="contact-link">
                        <i class="fas fa-question-circle"></i>
                        General Contact
                    </a>
                </div>
            </div>

            <!-- Back Link -->
            <div class="back-link">
                <a href="{{ route('home') }}">← Return to Homepage</a>
            </div>
        </div>
    </section>
@endsection