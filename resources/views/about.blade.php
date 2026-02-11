@extends('layouts.app')

@section('title', 'About ShoutOutGH - Online Education Platform in Ghana')
@section('description', 'Learn about ShoutOutGH, Ghana\'s premier digital learning platform created by Emmanuel Kwadwo Boamah. Transforming education and making quality learning accessible to every Ghanaian student.')
@section('keywords', 'about ShoutOutGH, online education ghana, digital learning platform ghana, e-learning ghana, ghana education platform, Emmanuel Kwadwo Boamah')

@section('head')
    <!-- Preload critical LCP image -->
    <link rel="preload" as="image" href="{{ secure_asset('images/student-laptop-about.webp') }}" type="image/webp" fetchpriority="high">
    <link rel="preload" as="image" href="{{ secure_asset('images/student-laptop-about.jpg') }}" fetchpriority="high">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="About ShoutOutGH - Online Education Platform in Ghana">
    <meta property="og:description" content="Learn about ShoutOutGH, Ghana's premier digital learning platform. Transforming education and making quality learning accessible to every Ghanaian student.">
    <meta property="og:image" content="{{ secure_asset('images/shoutoutgh-logo.png') }}">
    <meta property="og:url" content="{{ url('/about') }}">
    <meta property="og:type" content="website">
@endsection

@section('content')
    <style>
        /* Reset body padding for about page */
        body {
            padding-top: 0;
        }

        /* Hero Section */
        .about-hero {
            position: relative;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50, #34495e);
        }

        .hero-content {
            text-align: center;
            color: var(--white);
            max-width: 800px;
            padding: 0 2rem;
            margin-top: 2rem;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 1rem;
            letter-spacing: 0.02em;
        }

        .hero-content p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Image Section */
        .image-section {
            background-color: var(--white);
            padding: 3rem 0;
            display: flex;
            justify-content: center;
            height: 540px;
        }

        .student-image {
            max-width: 1200px;
            width: 100%;
            height: auto;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Main Content Section */
        .main-content {
            background-color: var(--white);
            padding: 3rem 0;
        }

        .content-container {
            max-width: 1138px;
            margin: 0 auto;
            padding: 0 2rem;
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.28);
            padding: 3rem;
        }

        .content-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: flex-start;
        }

        /* Left Content - ShoutOutGh Section */
        .left-content {
            padding-right: 2rem;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-red);
            margin-bottom: 1.5rem;
            letter-spacing: 0.02em;
        }

        .description-text {
            color: var(--gray-600);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .cta-button {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .cta-button:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-1px);
        }

        /* Right Content - Stats Section */
        .right-content {
            padding-left: 2rem;
            border-left: 1px solid #e5e7eb;
        }

        .stats-intro {
            font-size: 1.125rem;
            color: var(--secondary-blue);
            margin-bottom: 2rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .stats-container {
            display: flex;
            gap: 3rem;
        }

        .stat-item {
            text-align: left;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-red);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-top: 130px;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .content-container {
                margin: 0 1rem;
                padding: 2rem;
            }

            .content-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .left-content,
            .right-content {
                padding: 0;
            }

            .right-content {
                border-left: none;
                border-top: 1px solid #e5e7eb;
                padding-top: 2rem;
            }

            .stats-container {
                justify-content: center;
                gap: 2rem;
            }

            .brand-title {
                font-size: 1.75rem;
            }

            .stat-number {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .image-section {
                padding: 2rem 1rem;
            }

            .student-image {
                max-width: 100%;
            }

            .stats-container {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-content">
            <h1>About Us</h1>
            <p>
                We are on a mission to transform education and make quality learning accessible to everyone, everywhere.
            </p>
        </div>
    </section>

    <!-- Image Section -->
    <section class="image-section">
        <picture>
            <!-- WebP format for modern browsers -->
            <source srcset="{{ secure_asset('images/student-laptop-about.webp') }}" type="image/webp">
            <!-- Fallback to JPEG -->
            <img src="{{ secure_asset('images/student-laptop-about.jpg') }}"
                 alt="Student with laptop"
                 class="student-image"
                 loading="eager"
                 decoding="async"
                 width="1200"
                 height="540"
                 fetchpriority="high">
        </picture>
    </section>

    <!-- Main Content Section -->
    <section class="main-content">
        <div class="content-container">
            <div class="content-layout">
                <!-- Left Content - ShoutOutGh Info -->
                <div class="left-content">
                    <h2 class="brand-title">ShoutOutGh</h2>
                    <p class="description-text">
                        ShoutOutGh is a digital learning platform created by Emmanuel Kwadwo Boamah. We are on a mission to transform education and make quality learning accessible to everyone, everywhere. Our platform provides innovative tools and resources to enhance the learning experience for students and educators alike.
                    </p>
                    <a href="#" class="cta-button">Learn More</a>
                </div>

                <!-- Right Content - Statistics -->
                <div class="right-content">
                    <p class="stats-intro">We are currently having over</p>
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-number">206K</div>
                            <div class="stat-label">Students</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">2M</div>
                            <div class="stat-label">Tutors</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection