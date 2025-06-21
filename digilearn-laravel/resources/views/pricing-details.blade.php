@extends('layouts.app')

@section('content')
<style>

    li {
        list-style: none;
    }
    /* Pricing page specific styles */
    .pricing-hero {
        position: relative;
        height: 300px;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
    }

    .pricing-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url('{{ secure_asset("images/hero-image.png") }}');
        background-size: cover;
        background-position: center;
        z-index: -1;
    }

    .pricing-hero h1 {
        font-size: 3rem;
        font-weight: 600;
        text-align: center;
    }

    .pricing-content {
        padding: 4rem 0;
        background-color: var(--white);
    }

    .pricing-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .pricing-header h2 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .pricing-header p {
        color: var(--gray-600);
        font-size: 1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .pricing-tabs {
        display: flex;
        justify-content: center;
        margin: 2rem 0;
        gap: 0;
        background-color: var(--gray-100);
        border-radius: 0.5rem;
        padding: 0.25rem;
        max-width: 400px;
        margin: 2rem auto;
    }

    .pricing-tab {
        flex: 1;
        padding: 0.75rem 1rem;
        text-align: center;
        text-decoration: none;
        color: var(--gray-600);
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .pricing-tab.active {
        background-color: var(--primary-red);
        color: var(--white);
    }

    .pricing-tab:hover:not(.active) {
        background-color: var(--gray-200);
    }

    .pricing-plan-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .pricing-plan-wrapper {
        background-color: var(--white);
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .pricing-plan-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
    }

    @media (min-width: 768px) {
        .pricing-plan-grid {
            grid-template-columns: 2fr 1fr;
        }
    }

    .plan-features-section {
        padding: 2rem;
        border-right: 1px solid #e5e7eb;
    }

    .plan-duration-section {
        padding: 2rem;
        background-color: var(--gray-50);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .duration-option {
        display: flex;
        align-items: center;
        padding: 1rem;
        background-color: var(--white);
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .duration-option:hover {
        border-color: var(--primary-red);
    }

    .duration-radio {
        margin-right: 1rem;
        width: 1.25rem;
        height: 1.25rem;
        accent-color: var(--primary-red);
    }

    .duration-label-wrapper {
        flex: 1;
        cursor: pointer;
    }

    .duration-info {
        flex: 1;
    }

    .duration-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 0.25rem;
    }

    .duration-price {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .purchase-btn-main {
        width: 100%;
        background-color: var(--primary-red);
        color: var(--white);
        border: none;
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 1rem;
    }

    .purchase-btn-main:hover {
        background-color: var(--primary-red-hover);
    }

    .duration-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .plan-features-section {
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .duration-option {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .purchase-btn {
            width: 100%;
            margin-left: 0;
        }
    }

    .pricing-footer {
        background-color: var(--gray-50);
        padding: 3rem 0;
        margin-top: 4rem;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .footer-links {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .footer-links a {
        color: var(--gray-600);
        text-decoration: none;
        font-size: 0.875rem;
    }

    .footer-links a:hover {
        color: var(--gray-900);
    }

    .footer-social {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .social-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .social-icon.twitter {
        background-color: #1da1f2;
    }

    .social-icon.linkedin {
        background-color: #0077b5;
    }

    .social-icon.instagram {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .social-icon svg {
        width: 1rem;
        height: 1rem;
        color: var(--white);
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-logo-icon {
        width: 2rem;
        height: 2rem;
        background-color: var(--primary-red);
        border-radius: 0.25rem;
    }

    .footer-logo-text {
        font-size: 1.25rem;
        font-weight: bold;
        color: var(--gray-900);
    }

    @media (max-width: 768px) {
        .pricing-tabs {
            flex-direction: column;
            max-width: 200px;
        }
        
        .footer-content {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- Hero Section -->
<section class="pricing-hero">
    <h1>Pricing</h1>
</section>

<!-- Pricing Content -->
<section class="pricing-content">
    <div class="container">
        <div class="pricing-header">
            <h2>Dhoose Your Learning Plan</h2>
            <p>Select your membership plan tailored to your needs. Customize your subscription for a seamless fit.</p>
        </div>

        <!-- Pricing Tabs -->
        <div class="pricing-tabs">
            <button class="pricing-tab active" data-plan="essential">Essential</button>
            <button class="pricing-tab" data-plan="extra-tuition">Extra Tuition</button>
            <button class="pricing-tab" data-plan="home-sch">Home Sch</button>
        </div>

        <!-- Pricing Plans with Duration Options -->
        <div class="pricing-plan-container">
            <!-- Essential Plan -->
            <div id="essential-plan" class="pricing-plan-wrapper">
                <div class="pricing-plan-grid">
                    <div class="plan-features-section">
                        <div class="plan-header">
                            <div class="plan-name">{{ $pricingPlans['essential']['name'] }}</div>
                        </div>

                        <ul class="plan-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">DigiLearn</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="plan-duration-section">
                        <form class="duration-form">
                            <div class="duration-option">
                                <input type="radio" id="essential-trial" name="essential-duration" value="trial" class="duration-radio">
                                <label for="essential-trial" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">7 days free trial</div>
                                        <div class="duration-price">Free</div>
                                    </div>
                                </label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="essential-month" name="essential-duration" value="month" class="duration-radio" checked>
                                <label for="essential-month" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">1 month</div>
                                        <div class="duration-price">Ghs 50.00</div>
                                    </div>
                                </label>
                            </div>
                            <button type="submit" class="purchase-btn-main">Purchase</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Extra Tuition Plan -->
            <div id="extra-tuition-plan" class="pricing-plan-wrapper" style="display: none;">
                <div class="pricing-plan-grid">
                    <div class="plan-features-section">
                        <div class="plan-header">
                            <div class="plan-name">{{ $pricingPlans['extra-tuition']['name'] }}</div>
                        </div>

                        <ul class="plan-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">DigiLearn</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">Join a class</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">24hr support service</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">Assessment</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">Learning Resources</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="plan-duration-section">
                        <form class="duration-form">
                            <div class="duration-option">
                                <input type="radio" id="extra-tuition-trial" name="extra-tuition-duration" value="trial" class="duration-radio">
                                <label for="extra-tuition-trial" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">7 days free trial</div>
                                        <div class="duration-price">Free</div>
                                    </div>
                                </label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="extra-tuition-month" name="extra-tuition-duration" value="month" class="duration-radio" checked>
                                <label for="extra-tuition-month" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">1 month</div>
                                        <div class="duration-price">Ghs 200.00</div>
                                    </div>
                                </label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="extra-tuition-3month" name="extra-tuition-duration" value="3month" class="duration-radio">
                                <label for="extra-tuition-3month" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">3 months</div>
                                        <div class="duration-price">Ghs 500.00</div>
                                    </div>
                                </label>
                            </div>
                            <button type="submit" class="purchase-btn-main">Purchase</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Home School Plan -->
            <div id="home-sch-plan" class="pricing-plan-wrapper" style="display: none;">
                <div class="pricing-plan-grid">
                    <div class="plan-features-section">
                        <div class="plan-header">
                            <div class="plan-name">{{ $pricingPlans['home-sch']['name'] }}</div>
                        </div>

                        <ul class="plan-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">DigiLearn</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">Join a class</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">24hr support service</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">Personalized Tuition (1 session)</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div class="feature-text">
                                    <div class="feature-title">Learning Resources</div>
                                    <div class="feature-description">Access to unlimited learning materials such as demonstration videos, interactive videos presenting various subjects, learning objectives, study guides</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="plan-duration-section">
                        <form class="duration-form">
                            <div class="duration-option">
                                <input type="radio" id="home-sch-trial" name="home-sch-duration" value="trial" class="duration-radio">
                                <label for="home-sch-trial" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">7 days free trial</div>
                                        <div class="duration-price">Free</div>
                                    </div>
                                </label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="home-sch-month" name="home-sch-duration" value="month" class="duration-radio" checked>
                                <label for="home-sch-month" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">1 month</div>
                                        <div class="duration-price">Ghs 200.00</div>
                                    </div>
                                </label>
                            </div>
                            <div class="duration-option">
                                <input type="radio" id="home-sch-3month" name="home-sch-duration" value="3month" class="duration-radio">
                                <label for="home-sch-3month" class="duration-label-wrapper">
                                    <div class="duration-info">
                                        <div class="duration-label">3 months</div>
                                        <div class="duration-price">Ghs 600.00</div>
                                    </div>
                                </label>
                            </div>
                            <button type="submit" class="purchase-btn-main">Purchase</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer
<section class="pricing-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-links">
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a href="{{ route('pricing') }}">Pricing</a>
                <a href="#">Features</a>
                <a href="#">FAQ</a>
                <a href="#">T & C</a>
                <a href="#">Support</a>
                <a href="#">Docs</a>
            </div>
            
            <div class="footer-logo">
                <div class="footer-logo-icon"></div>
                <span class="footer-logo-text">DigiLearn</span>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <div class="footer-social">
                <div class="social-icon twitter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                    </svg>
                </div>
                <div class="social-icon linkedin">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                        <rect x="2" y="9" width="4" height="12"></rect>
                        <circle cx="4" cy="4" r="2"></circle>
                    </svg>
                </div>
                <div class="social-icon instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section> -->

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Pricing tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.pricing-tab');
        const plans = document.querySelectorAll('.pricing-plan-wrapper');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all plans
                plans.forEach(plan => plan.style.display = 'none');
                
                // Show selected plan
                const planId = this.getAttribute('data-plan') + '-plan';
                document.getElementById(planId).style.display = 'block';
            });
        });
    });
</script>
@endsection
