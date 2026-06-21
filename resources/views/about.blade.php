@extends('layouts.app')

@section('title', 'About ShoutOutGH - Educating Through Entertainment')
@section('description', 'Learn about ShoutOutGH, Africa\'s leading edutainment platform. We transform learning into an engaging, enjoyable experience combining education with entertainment.')
@section('keywords', 'about ShoutOutGH, online education ghana, edutainment ghana, e-learning ghana, Emmanuel Kwadwo Boamah, education through entertainment')

@section('head')
    <!-- Preload critical LCP image -->
    <link rel="preload" as="image" href="{{ secure_asset('images/student-laptop-about.webp') }}" type="image/webp"
        fetchpriority="high">
    <link rel="preload" as="image" href="{{ secure_asset('images/student-laptop-about.jpg') }}" fetchpriority="high">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="About ShoutOutGH - Educating Through Entertainment">
    <meta property="og:description"
        content="ShoutOutGh is Africa's leading edutainment ecosystem, making learning a daily, enjoyable experience that empowers individuals and prepares youth for the future.">
    <meta property="og:image" content="{{ secure_asset('images/shoutoutgh-logo.png') }}">
    <meta property="og:url" content="{{ url('/about') }}">
    <meta property="og:type" content="website">
@endsection

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Modern Premium CSS Variables */
        :root {
            --primary-bg: #f8fafc;
            --section-bg: #ffffff;
            --accent-red: #E11E2D;
            /* ShoutOutGH Primary Red */
            --accent-blue: #008CFF;
            /* ShoutOutGH Primary Blue */
            --text-dark: #0f172a;
            --text-muted: #475569;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            --radius-xl: 1.5rem;
        }

        /* General Body Setup */
        body {
            background-color: var(--primary-bg);
            color: var(--text-dark);
            font-family: 'Inter', 'Work Sans', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Hero Section */
        .about-hero {
            position: relative;
            padding: 8rem 2rem 4rem;
            background: var(--section-bg);
            overflow: hidden;
            border-bottom: 1px solid #e2e8f0;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 2rem;
            align-items: center;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: left;
            animation: fadeUp 1s ease-out;
        }

        .hero-image-container {
            position: relative;
            z-index: 10;
            animation: fadeUp 1s ease-out 0.2s both;
            display: flex;
            justify-content: center;
        }

        .hero-image-container img {
            width: 100%;
            max-width: 600px;
            height: auto;
            object-fit: contain;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            filter: contrast(1.02);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }



        .hero-content h1 {
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 800;
            line-height: 1.1;
            font-family: 'Times New Roman', Times, serif;
            margin-bottom: 1.5rem;
            color: var(--accent-blue);
        }

        .hero-content p {
            font-size: clamp(1.1rem, 2vw, 1.25rem);
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        /* Introduction Section */
        .intro-section {
            padding: 6rem 2rem;
            background: var(--section-bg);
            position: relative;
            z-index: 20;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .intro-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .intro-text h2 {
            font-family: serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .intro-text h2 span {
            color: var(--accent-blue);
        }

        .intro-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .intro-image-wrapper {
            position: relative;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transform: perspective(1000px) rotateY(-5deg);
            transition: transform 0.5s ease;
        }

        .intro-image-wrapper:hover {
            transform: perspective(1000px) rotateY(0deg);
        }

        .intro-image-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        /* Vision & Mission Cards Section */
        .vision-mission-section {
            padding: 6rem 2rem;
            background: var(--primary-bg);
            position: relative;
        }

        [x-cloak] {
            display: none !important;
        }

        .philosophy-carousel {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            min-height: 250px;
            margin-top: 3rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .philosophy-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: var(--primary-bg);
        }

        .philosophy-slide h3 {
            font-family: serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .philosophy-slide p {
            font-size: 1.25rem;
            line-height: 1.8;
            color: var(--text-muted);
        }

        .philosophy-indicators {
            position: absolute;
            bottom: -2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            z-index: 10;
        }

        .philosophy-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #d1d7dc;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .philosophy-indicators button.active {
            background-color: var(--accent-blue);
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 6rem 0;
            background: var(--section-bg);
            overflow: hidden;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* Copied and adapted from home.blade.php */
        .testimonials-slider-wrapper {
            position: relative;
            padding: 0 1rem;
            max-width: 1200px;
            margin: 0 auto;
            overflow: hidden;
        }

        .testimonials-grid {
            display: flex;
            width: max-content;
            padding: 1.5rem 0;
            gap: 2rem;
            animation: scrollMarquee 40s linear infinite;
        }

        .testimonials-grid:hover {
            animation-play-state: paused;
        }

        @keyframes scrollMarquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-50% - 1rem)); }
        }

        .testimonial-card {
            flex: 0 0 400px;
            background: #ffffff;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            padding: 2.5rem;
            border: 1px solid #f1f5f9;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            white-space: normal;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .testimonial-content {
            margin-bottom: 2rem;
            position: relative;
        }

        .testimonial-content::before {
            content: '"';
            font-size: 4rem;
            color: #f1f5f9;
            position: absolute;
            top: -20px;
            left: -10px;
            font-family: serif;
            z-index: 0;
        }

        .testimonial-content p {
            font-style: italic;
            color: var(--text-muted);
            line-height: 1.7;
            position: relative;
            z-index: 1;
            font-size: 1.05rem;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-author img {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .author-info h4 {
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 0.25rem 0;
            font-size: 1.1rem;
        }

        .author-info span {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .slider-nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 3rem;
        }

        .slider-nav-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--section-bg);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .slider-nav-btn:hover {
            background-color: var(--accent-blue);
            color: #ffffff;
            border-color: var(--accent-blue);
            transform: scale(1.05);
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 2rem;
            background: var(--section-bg);
            border-bottom: 1px solid #e2e8f0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--accent-blue);
        }

        .stat-item p {
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
        }



        /* CTA Section */
        .cta-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, var(--accent-blue), #1e40af);
            text-align: center;
            color: #ffffff;
        }

        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2.5rem;
        }

        .cta-button {
            display: inline-block;
            background: var(--accent-red);
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(225, 30, 45, 0.4);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(225, 30, 45, 0.6);
            background: #b91c1c;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .intro-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .philosophy-carousel {
                min-height: 350px;
            }

            .philosophy-slide p {
                font-size: 1.1rem;
            }

            .testimonial-card {
                flex: 0 0 320px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .hero-container {
                display: flex;
                flex-direction: column-reverse;
                gap: 2rem;
                text-align: center;
                align-items: center;
            }

            .hero-content {
                text-align: center;
            }

            .hero-image-container {
                max-width: 400px;
                margin: 0 auto;
            }

            .about-hero {
                padding-top: 8rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .intro-text h2 {
                font-size: 2rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Educating Through Entertainment</h1>
                <p>
                    We are a Ghanaian EdTech platform dedicated to transforming how people learn. We believe learning should
                    be informative, engaging, relatable, and enjoyable.
                </p>
            </div>
            <div class="hero-image-container">
                <img src="{{ secure_asset('images/ghana_student_4.png') }}" alt="Ghanaian student learning">
            </div>
        </div>
    </section>

    <!-- Platform Numbers & Impact (Stats)
                                @if(isset($stats) && count($stats) > 0)
                                    <section class="stats-section">
                                        <div class="stats-grid">
                                            @foreach($stats as $stat)
                                                <div class="stat-item">
                                                    <h3>{{ $stat['value'] }}</h3>
                                                    <p>{{ $stat['label'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @endif -->

    <!-- Introduction Section -->
    <section class="intro-section">
        <div class="container intro-grid">
            <div class="intro-text">
                <h2>Beyond Traditional Learning</h2>
                <p>
                    ShoutOutGh is a Ghanaian Edutainment and Education Technology (EdTech) platform dedicated to
                    transforming how people learn by combining education with entertainment. We believe that learning should
                    not only be informative but also engaging, relatable, practical, and enjoyable.
                </p>
                <p>
                    Through short videos, storytelling, animations, real-life experiences, creative content, and digital
                    learning tools, ShoutOutGh makes education accessible and exciting for learners of all ages. Our
                    platform bridges the gap between traditional classroom learning and the modern digital world by
                    delivering knowledge in formats that capture attention and inspire action.
                </p>
                <p>
                    ShoutOutGh is more than an educational platform—it is Africa's leading edutainment ecosystem, a movement
                    to make learning a daily, enjoyable experience that empowers individuals, strengthens communities, and
                    prepares Africa's youth for the future.
                </p>
            </div>
            <div class="intro-image-wrapper">
                <picture>
                    <source srcset="{{ secure_asset('images/student-laptop-about.webp') }}" type="image/webp">
                    <img src="{{ secure_asset('images/student-laptop-about.jpg') }}" alt="Student using ShoutOutGH"
                        loading="lazy" decoding="async" width="600" height="400">
                </picture>
            </div>
        </div>
    </section>

    <!-- Vision, Mission, Tagline Section -->
    <section class="vision-mission-section">
        <div class="container">

            <div class="philosophy-carousel" x-data="{ activeSlide: 1, timer: null }"
                x-init="timer = setInterval(() => { activeSlide = activeSlide >= 3 ? 1 : activeSlide + 1 }, 5000)"
                @mouseenter="clearInterval(timer)"
                @mouseleave="timer = setInterval(() => { activeSlide = activeSlide >= 3 ? 1 : activeSlide + 1 }, 5000)">

                <!-- Vision Slide -->
                <div x-show="activeSlide === 1" x-transition.opacity.duration.500ms class="philosophy-slide">
                    <h3>Our Vision</h3>
                    <p>To become Africa's leading edutainment platform, transforming learning into an engaging and enjoyable
                        experience while empowering millions of people with knowledge, skills, and opportunities for
                        lifelong success.</p>
                </div>

                <!-- Mission Slide -->
                <div x-show="activeSlide === 2" x-transition.opacity.duration.500ms class="philosophy-slide" x-cloak>
                    <h3>Our Mission</h3>
                    <p>To educate, inspire, and empower learners through entertaining, practical, and accessible content
                        that promotes academic excellence, personal development, entrepreneurship, and lifelong learning.
                    </p>
                </div>

                <!-- Tagline Slide -->
                <div x-show="activeSlide === 3" x-transition.opacity.duration.500ms class="philosophy-slide" x-cloak>
                    <h3>Our Tagline</h3>
                    <p>"Educating Through Entertainment"</p>
                </div>

                <!-- Indicators -->
                <div class="philosophy-indicators">
                    <button
                        @click="activeSlide = 1; clearInterval(timer); timer = setInterval(() => { activeSlide = activeSlide >= 3 ? 1 : activeSlide + 1 }, 5000)"
                        :class="{'active': activeSlide === 1}" aria-label="Vision"></button>
                    <button
                        @click="activeSlide = 2; clearInterval(timer); timer = setInterval(() => { activeSlide = activeSlide >= 3 ? 1 : activeSlide + 1 }, 5000)"
                        :class="{'active': activeSlide === 2}" aria-label="Mission"></button>
                    <button
                        @click="activeSlide = 3; clearInterval(timer); timer = setInterval(() => { activeSlide = activeSlide >= 3 ? 1 : activeSlide + 1 }, 5000)"
                        :class="{'active': activeSlide === 3}" aria-label="Tagline"></button>
                </div>
            </div>
        </div>
    </section>


    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>What They Say About Us</h2>
                <p>Hear from our students and parents who have experienced the ShoutOutGH difference.</p>
            </div>

            @if(isset($testimonials) && count($testimonials) > 0)
                <div class="testimonials-slider-wrapper">
                    <div class="testimonials-grid">
                        <!-- First Set -->
                        @foreach($testimonials as $testimonial)
                            <article class="testimonial-card">
                                <div class="testimonial-content">
                                    <p>"{{ $testimonial['quote'] }}"</p>
                                </div>
                                <div class="testimonial-author">
                                    <img src="{{ secure_asset($testimonial['image']) }}" alt="Photo of {{ $testimonial['name'] }}"
                                        loading="lazy">
                                    <div class="author-info">
                                        <h4>{{ $testimonial['name'] }}</h4>
                                        <span>{{ $testimonial['role'] }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                        
                        <!-- Second Set for Seamless Looping -->
                        @foreach($testimonials as $testimonial)
                            <article class="testimonial-card" aria-hidden="true">
                                <div class="testimonial-content">
                                    <p>"{{ $testimonial['quote'] }}"</p>
                                </div>
                                <div class="testimonial-author">
                                    <img src="{{ secure_asset($testimonial['image']) }}" alt="Photo of {{ $testimonial['name'] }}"
                                        loading="lazy">
                                    <div class="author-info">
                                        <h4>{{ $testimonial['name'] }}</h4>
                                        <span>{{ $testimonial['role'] }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Transform Your Learning Experience?</h2>
            <p>Join learners across Africa who are discovering the power of education through entertainment.
                Start your journey today.</p>
            <a href="{{ route('signup') }}" class="cta-button">Join ShoutOutGh Now</a>
        </div>
    </section>

@endsection