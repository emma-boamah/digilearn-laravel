@extends('layouts.app')

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Scoped styles for the For Schools landing page */
        .b2b-hero {
            padding: 120px 0 80px 0;
            background-color: var(--bg-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .b2b-logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .b2b-title {
            font-size: 3.5rem;
            line-height: 1.1;
            font-weight: 700;
            max-width: 800px;
            margin-bottom: 24px;
            color: var(--text-main);
        }

        .b2b-subtitle {
            font-size: 1.25rem;
            line-height: 1.6;
            color: var(--text-muted);
            max-width: 700px;
            margin-bottom: 48px;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }

        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .b2b-title {
                font-size: 2.5rem;
            }
        }

        .pricing-card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .pricing-card.popular {
            border-top: 6px solid var(--secondary-blue);
        }

        .pricing-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .plan-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .plan-target {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .plan-billing {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .plan-badge {
            background-color: var(--secondary-blue);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 16px;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 40px 0;
            flex-grow: 1;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 0.95rem;
            color: var(--text-main);
            line-height: 1.5;
        }

        .feature-icon {
            color: var(--secondary-blue);
            margin-top: 2px;
        }

        .btn-block {
            width: 100%;
            text-align: center;
            justify-content: center;
            padding: 14px;
            font-size: 1.1rem;
        }
    </style>

    <div class="b2b-hero container">
        <div class="b2b-logo-container">
            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh" style="height: 35px;">
            <span>for Schools</span>
        </div>

        <h1 class="b2b-title">Choose a scalable plan that fits your school's goals</h1>

        <p class="b2b-subtitle">
            Develop in-demand skills with world-class content to boost student performance, retain top teachers, and drive
            growth across your institution.
        </p>

        <div class="pricing-grid">
            <!-- Team Plan -->
            <div class="pricing-card popular">
                <div class="pricing-header">
                    <h2 class="plan-name">School Pro</h2>
                    <p class="plan-target">For up to 499 Students</p>

                    <div class="plan-price">GH₵ 25<span
                            style="font-size: 1rem; margin-left: 5px; font-weight: normal; color: var(--text-muted)">/term</span>
                    </div>
                    <p class="plan-billing">per student, billed termly</p>

                    <div class="plan-badge">Save 40% on Bulk Subscriptions</div>
                </div>

                <ul class="plan-features">
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Access catalog of 1,000+ interactive lessons and quizzes</span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Custom branded portal (e.g., yourschool.shoutoutgh.com)</span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Teacher Gradebooks & student progress analytics</span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Automated CBT (Computer Based Testing) system</span>
                    </li>
                </ul>

                <a href="{{ route('school.register', ['plan' => 'school-pro']) }}"
                    class="btn btn-primary btn-block">Register School</a>
            </div>

            <!-- Enterprise Plan -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h2 class="plan-name">Enterprise</h2>
                    <p class="plan-target">For 500+ Students</p>

                    <div class="plan-price" style="font-size: 2rem; margin-top: 15px;">Tailored Pricing</div>
                    <p class="plan-billing" style="margin-top: 15px;">Customized to your institution</p>
                    <div style="height: 38px; margin-top: 16px;"></div> <!-- Spacer to align with badge -->
                </div>

                <ul class="plan-features">
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span><strong>Everything in School Pro, plus:</strong></span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Ability to upload private video content & documents</span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>AI-powered assessment generation from own materials</span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Dedicated Customer Success Manager</span>
                    </li>
                    <li class="feature-item">
                        <svg class="feature-icon" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>API integrations with your existing School Management System</span>
                    </li>
                </ul>

                <a href="{{ route('contact') }}" class="btn btn-primary btn-block"
                    style="background-color: var(--bg-surface); border: 1px solid var(--secondary-blue); color: var(--secondary-blue);">Contact
                    Sales</a>
            </div>
        </div>
    </div>

@endsection