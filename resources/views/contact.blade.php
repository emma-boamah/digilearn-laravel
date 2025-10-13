@extends('layouts.app')

@section('content')
    <style>
        /* Contact page specific styles */
        .contact-hero {
            position: relative;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #000;
            overflow: hidden;
        }

        .hero-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 500;
            color: var(--white);
        }

        .contact-section {
            padding: 0;
            background-color: var(--white);
        }

        .contact-card {
            background-color: var(--white);
            border-radius: 0;
            max-width: 100%;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .card-header {
            padding: 1.25rem;
            text-align: center;
            background-color: var(--primary-red);
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0;
            color: var(--white);
        }

        .card-content {
            display: grid;
            gap: 2rem;
            padding: 2rem;
            background-color: #f9f9f9;
        }

        @media (min-width: 48rem) {
            .card-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-item {
            background-color: #fff;
            border-radius: 0;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .contact-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .icon {
            background-color: #fff;
            padding: 0;
            flex-shrink: 0;
        }

        .icon svg {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--primary-red);
        }

        .text {
            flex: 1;
        }

        .text h3 {
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .text p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .text textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0;
            margin-bottom: 1rem;
            min-height: 80px;
            resize: vertical;
        }

        .text button {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            width: auto;
        }

        .contact-form {
            background-color: #fff;
            border-radius: 0;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .contact-form h3 {
            font-weight: 500;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0;
            font-size: 0.875rem;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .contact-form button {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            width: auto;
        }

        /* Map section */
        .map-section {
            margin-top: 2rem;
        }

        .map-container {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .map-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>

    <!-- Hero Section -->
    <section class="contact-hero">
        <img src="{{ secure_asset('images/contact-hero.jpg') }}" alt="Contact us" class="hero-image">
        <div class="hero-content">
            <h1>Contact us</h1>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-section">
        <div class="contact-card">
            <div class="card-header">
                <h2>Let's connect</h2>
            </div>

            <div class="card-content">
                <!-- Left Column - Contact Methods -->
                <div class="contact-methods">
                    <div class="contact-item">
                        <div class="contact-content">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="text">
                                <h3>Get feedback</h3>
                                <p>
                                    We have millions of teachers around the world as it helps us improve what you love to learn.
                                </p>
                                <form action="{{ route('feedback.submit') }}" method="POST">
                                    @csrf
                                    <textarea name="feedback" placeholder="Share your thoughts..."></textarea>
                                    <button type="submit">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-content">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div class="text">
                                <h3>(+233) 2400 20375</h3>
                                <p>
                                    We have a team of teachers around the world as it helps us provide the best for you. Our team is available 24/7 to help you.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-content">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="text">
                                <h3>shoutoutGh.edu.online</h3>
                                <p>
                                    We have a team of teachers around the world as it helps us provide the best for you. Our team is available 24/7 to help you with your questions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-content">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="text">
                                <h3>Accra-Ghana</h3>
                                <p>
                                    We have a team of teachers around the world as it helps us provide the best for you. Our team is based on ShoutOutGh.edu
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Contact Form -->
                <div class="contact-form">
                    <h3>Send a message</h3>
                    <form action="{{ route('contact.submit') }}" method="POST">
                        @csrf
                        <div class="form-group form-row">
                            <div>
                                <label for="firstName">First Name</label>
                                <input id="firstName" name="firstName" placeholder="John">
                            </div>
                            <div>
                                <label for="lastName">Last Name</label>
                                <input id="lastName" name="lastName" placeholder="Doe">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" placeholder="john@example.com">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" placeholder="+1 (123) 456-7890">
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" placeholder="How can we help you?"></textarea>
                        </div>

                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="map-container">
            <img src="{{ secure_asset('images/map.jpg') }}" alt="Office location map">
        </div>
    </section>
@endsection