@extends('layouts.app')

@section('content')
<style>
    /* Hero Section */
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
        background-image: url('{{ asset("images/hero-image.png") }}');
        background-size: cover;
        background-position: center;
        z-index: -1;
    }

    .pricing-hero h1 {
        font-size: 3rem;
        font-weight: 600;
        text-align: center;
    }

    /* Main Pricing Section */
    .pricing-main {
        padding: 4rem 0;
        background-color: var(--white);
    }

    .pricing-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .pricing-header h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .pricing-header p {
        color: var(--gray-600);
        font-size: 1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .pricing-cards {
        display: grid;
        gap: 2rem;
        max-width: 1050px;
        margin: 0 auto;
        justify-content: center;
    }

    @media (min-width: 48rem) {
        .pricing-cards {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .pricing-card {
        background-color: var(--white);
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        position: relative;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        width: 370px;
    }

    .pricing-card:hover {
        transform: translateY(-5px);
    }

    .pricing-badge {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.75rem 2rem;
        background-color: var(--primary-red);
        color: var(--white);
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        z-index: 10;
        min-width: 120px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .pricing-card-content {
        margin-top: 2rem;
    }

    .pricing-description {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .pricing-price {
        font-size: 2rem;
        font-weight: bold;
        color: var(--gray-900);
        margin-bottom: 2rem;
    }

    .pricing-features {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
        text-align: left;
    }

    .pricing-features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: var(--gray-700);
        font-size: 0.875rem;
    }

    .feature-disabled {
        color: #b0b0b0 !important;
        text-decoration: line-through;
        opacity: 0.7;
    }

    .pricing-features svg {
        color: var(--primary-red);
        flex-shrink: 0;
        width: 16px;
        height: 16px;
    }

    .pricing-btn {
        width: 100%;
        padding: 0.75rem 1.5rem;
        background-color: transparent;
        border: 1px solid var(--primary-red);
        color: var(--primary-red);
        border-radius: 0.375rem;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.15rem;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .pricing-btn:hover {
        background-color: var(--primary-red);
        color: var(--white);
    }
</style>

<!-- Hero Section -->
<section class="pricing-hero">
    <h1>Pricing</h1>
</section>

<!-- Main Pricing Section -->
<section class="pricing-main">
    <div class="container">
        <div class="pricing-header">
            <h2>Choose Your Learning Plan</h2>
            <p>
                Select your membership plan tailored to your needs. Customize your subscription for a seamless fit.
            </p>
        </div>

        <div class="pricing-cards">
            <!-- Essential Plan -->
            <div class="pricing-card">
                <div class="pricing-badge">Essential</div>
                <div class="pricing-card-content">
                    <p class="pricing-description">
                        Lorem ipsum dolor sit amet consectetur. Molesuada pretium commodo nulla in e
                    </p>
                    <div class="pricing-price">Ghc 50.00</div>
                    <ul class="pricing-features">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            DigiLearn
                        </li>
                        <li class="feature-disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Join a class/One time
                        </li>
                        <li class="feature-disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Learning Resources
                        </li>
                        <li class="feature-disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Sample Questions
                        </li>
                        <li class="feature-disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            24/7 service support
                        </li>
                    </ul>
                    <a href="#" class="pricing-btn">Get Started</a>
                </div>
            </div>

            <!-- Extra Tuition Plan -->
            <div class="pricing-card">
                <div class="pricing-badge">Extra Tuition</div>
                <div class="pricing-card-content">
                    <p class="pricing-description">
                        Lorem ipsum dolor sit amet consectetur. Molesuada pretium commodo nulla in e
                    </p>
                    <div class="pricing-price">Ghc 200.00</div>
                    <ul class="pricing-features">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            DigiLearn
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Join a class
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Learning Resources
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Personal class
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            24/7 service support
                        </li>
                    </ul>
                    <a href="#" class="pricing-btn">Get Started</a>
                </div>
            </div>

            <!-- Home School Plan -->
            <div class="pricing-card">
                <div class="pricing-badge">Home School</div>
                <div class="pricing-card-content">
                    <p class="pricing-description">
                        Lorem ipsum dolor sit amet consectetur. Molesuada pretium commodo nulla in e
                    </p>
                    <div class="pricing-price">Ghc 200.00</div>
                    <ul class="pricing-features">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            DigiLearn
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Personalized tuition
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Learning Resources
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Sample Questions
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            24/7 service support
                        </li>
                    </ul>
                    <a href="#" class="pricing-btn">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection