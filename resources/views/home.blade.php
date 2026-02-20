@extends('layouts.app')

@section('title', 'ShoutOutGH - Online Learning Platform for Ghanaian Students | Online Education in Ghana')
@section('description', 'ShoutOutGH is Ghana\'s premier online learning platform offering interactive lessons, quizzes, and educational resources for students from primary to tertiary level. Access quality online education in Ghana.')
@section('keywords', 'online education in ghana, online school ghana, education ghana, school, e-learning ghana, digital learning platform, online classes ghana, ShoutOutGH, JHS lessons, SHS lessons, university courses ghana, BECE preparation, WASSCE preparation, free online school ghana')
@section('canonical', url('/'))

@section('head')
    <!-- JSON-LD Structured Data for SEO -->
    @php
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "EducationalOrganization",
        "name" => "ShoutOutGH",
        "description" => "Ghana's premier online learning platform offering interactive lessons, quizzes, and educational resources for students from primary to tertiary level.",
        "url" => url('/'),
        "logo" => secure_asset('images/shoutoutgh-logo.png'),
        "sameAs" => [
            "https://facebook.com/shoutoutgh",
            "https://twitter.com/shoutoutgh",
            "https://instagram.com/shoutoutgh"
        ],
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => "+233-207-646-203",
            "contactType" => "customer service",
            "availableLanguage" => "English"
        ],
        "offers" => [
            [
                "@type" => "EducationalOccupationalCredential",
                "name" => "Primary Education",
                "educationalLevel" => "Primary School",
                "provider" => [
                    "@type" => "EducationalOrganization",
                    "name" => "ShoutOutGH"
                ]
            ],
            [
                "@type" => "EducationalOccupationalCredential",
                "name" => "Junior High School",
                "educationalLevel" => "JHS",
                "provider" => [
                    "@type" => "EducationalOrganization",
                    "name" => "ShoutOutGH"
                ]
            ],
            [
                "@type" => "EducationalOccupationalCredential",
                "name" => "Senior High School",
                "educationalLevel" => "SHS",
                "provider" => [
                    "@type" => "EducationalOrganization",
                    "name" => "ShoutOutGH"
                ]
            ],
            [
                "@type" => "EducationalOccupationalCredential",
                "name" => "Tertiary Education",
                "educationalLevel" => "University Level",
                "provider" => [
                    "@type" => "EducationalOrganization",
                    "name" => "ShoutOutGH"
                ]
            ]
        ],
        "hasOfferCatalog" => [
            "@type" => "OfferCatalog",
            "name" => "Learning Resources",
            "itemListElement" => [
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Course",
                        "name" => "Interactive Lessons",
                        "description" => "Video lessons with interactive elements"
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "AssessmentEvent",
                        "name" => "Practice Quizzes",
                        "description" => "Multiple choice questions for assessment"
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "CreativeWork",
                        "name" => "Study Materials",
                        "description" => "PDF documents and presentation slides"
                    ]
                ]
            ]
        ]
    ];
    @endphp

    <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">
    {{ json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) }}
    </script>

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="ShoutOutGH - Online Learning Platform for Ghanaian Students">
    <meta property="og:description" content="Access quality online education in Ghana. Interactive lessons, quizzes, and study materials for Primary, JHS, SHS, and University students.">
    <meta property="og:image" content="{{ secure_asset('images/shoutoutgh-logo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ShoutOutGH">
    <meta property="og:locale" content="en_GH">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ShoutOutGH - Online Learning Platform for Ghanaian Students">
    <meta name="twitter:description" content="Access quality online education in Ghana. Interactive lessons, quizzes, and study materials for Primary, JHS, SHS, and University students.">
    <meta name="twitter:image" content="{{ secure_asset('images/shoutoutgh-logo.png') }}">
@endsection

@section('content')

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
    html, body {
        width: 100%;
        max-width: 100vw;
        overflow-x: hidden;
    }
    /* Mobile-first responsive typography */
    .journey-title {
        font-size: 2.5rem;
        font-weight: 400;
        line-height: 1.2;
        color: var(--white);
        margin-bottom: 1rem;
    }

    .journey-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 2rem;
        font-family: 'Work Sans', sans-serif;
    }

    .hero-title {
        font-family: 'Work Sans', sans-serif;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .hero-title-main {
        font-size: clamp(2rem, 10vw, 5rem);
        color: #008CFF;
        display: block;
        margin-bottom: 0.75rem; /* Space between main and sub title */
        line-height: 1.2;
        padding-bottom: 0.1em;
    }

    .hero-title-sub {
        font-size: clamp(1.2rem, 6vw, 2.5rem);
        line-height: 1.3;
        font-weight: 500;
        margin-top: 0.5rem;
        color: #f1f3f6ff;
        display: block;
    }

    .hr {
        border: none;
        height: 1px;
        background-color: #e5e7eb;
        margin-bottom: 2rem;
    }

    .tools-on-shoutout {
        color: var(--primary-red);
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .discover-innovative-features{
        color: var(--gray-600);
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    /* Tablet sizes */
    @media (min-width: 768px) {
        .journey-title {
            font-size: 3.5rem;
        }
        .journey-subtitle {
            font-size: 1.25rem;
        }
    /* Tablet & Desktop overrides removed to favor clamp() behavior */
    }

    /* Large desktop */
    @media (min-width: 1280px) {
        .journey-title {
            font-size: 5.625rem; /* 90px */
        }
    }
        /* Hero Section */
        .hero {
            position: relative;
            /* Default for desktop */
            height: 100vh;
        }

        @media (max-width: 768px) {
            .hero {
                /* Adjust for fixed header and safe area on mobile */
                height: calc(100vh - (120px + var(--safe-area-inset-top)));
                min-height: 500px; /* Ensure reasonable minimum height */
            }
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: var(--gray-900);
        }

        .hero-background video {
            position: absolute;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            object-fit: cover;
            opacity: 0.5;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .hero-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4rem;
            padding-top: 0;
            margin: 0;
            width: 100%;
            max-width: none;
        }

        .hero-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 98vw;
            width: 100%;
            text-align: center;
            padding: 5rem 1rem 0;
            box-sizing: border-box;
        }

        .hero-title {
            letter-spacing: -0.02em;
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.1;
            margin-bottom: 2rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem; /* Adjusted for smaller screens */
            }

            .hero-title-emphasis {
                font-size: 3rem; /* Adjusted for smaller screens */
            }

            .hero-btn {
                padding: 1rem 1.5rem;
                font-size: 1.25rem; /* Adjusted for smaller screens */
            }

            .hero-content {
                padding: 0 2rem;
            }

            /* Why Choose Section */
            .why-choose {
                flex-direction: column;
            }

            .why-choose-videos {
                order: -1; /* Move videos below text on smaller screens */
                margin-bottom: 2rem;
            }

            .feature-list li {
                font-size: 1rem; /* Adjusted for smaller screens */
            }

            /* Learning Resources Buttons - Fixed */
            .resources-red-section {
                height: auto; /* Remove fixed height */
                padding: 2rem 0; /* Add proper padding */
            }

            .resources-buttons-container {
                padding: 0 1rem; /* Add side padding */
            }

            /* Study Journey Section */
            .study-journey-text h2 {
                font-size: 2.5rem; /* Adjusted for smaller screens */
                line-height: 1.2;
            }

            /* Pricing Section */
            .pricing-header-content-new {
                flex-direction: column;
                text-align: center;
            }

            .pricing-image-right {
                margin: 2rem auto 0;
            }

            .pricing-grid {
                grid-template-columns: 1fr; /* Single column on smaller screens */
            }

            /* Learning Resources Section */

            .resources-main-title {
                font-size: 2.5rem; /* Adjusted for smaller screens */
                width: 100%;
            }

            .resources-content-layout {
                flex-direction: column;
                text-align: center;
            }

            /* Learning Resources Buttons */
            .resources-buttons-grid {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 9vw;
            }
            .hero-title-main {
                /* Extra small screen adjustment */
            }
            .hero-title-sub {
                font-size: 1.4rem;
                margin-top: 0.25rem;
            }

            .hero-content {
                padding: 0 0.75rem;
            }
        }

        @media (min-width: 1600px) {
            .hero-title {
                font-size: 6rem; /* Max size for large screens */
            }
        }

        .hero-title-emphasis {
            display: block;
            font-size: 1.2em;
            margin-top: 1rem;
        }

        .hero-description {
            max-width: 28rem;
            display: flex;
            justify-content: center;
        }

        .hero-btn {
            /* width: 14rem; */
            background-color: var(--secondary-blue);
            color: var(--white);
            margin-top: 1.5rem;
            padding: 1.25rem 3rem;
            border-radius: var(--border-radius-full);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .hero-btn:hover {
            background-color: var(--secondary-blue-hover);
        }

        .hero-btn svg {
            width: 1rem;
            height: 1rem;
            margin-left: 0.5rem;
        }

        
        /* Section styles */
        .section {
            padding: 2rem 0;
        }

        .bg-white {
            background-color: var(--white);
        }

        .bg-gray-50 {
            background-color: var(--gray-50);
        }

        .bg-red {
            background-color: var(--primary-red);
        }

        /* Grid system */
        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(1, 1fr);
        }

        @media (min-width: 48rem) {
            .grid-cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .grid-cols-3 {
            grid-template-columns: repeat(1, 1fr);
        }

        @media (min-width: 48rem) {
            .grid-cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (min-width: 48rem) {
            .grid-cols-4 {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Typography */
        .section-title {
            font-size: 2.25rem;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 3rem;
        }

        .text-center {
            text-align: center;
        }

        .text-3xl {
            font-size: 3.825rem;
            line-height: 2.25rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .mb-6 {
            margin-bottom: 3.5rem;
        }

        .text-white {
            color: var(--white);
        }

        /* Why Choose section */
        .why-choose {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            align-items: center;
        }

        @media (min-width: 48rem) {
            .why-choose {
                flex-direction: row;
            }
        }

        .why-choose-text {
            flex: 2;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            display: flex;
            font-size: 1.4rem;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .feature-list svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.25rem;
            color: var(--secondary-blue);
            flex-shrink: 0;
        }

        .why-choose-images {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .image-container {
            position: relative;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            height: 300px;
        }

        .image-container img {
            border-radius: var(--border-radius-lg);
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        /* Video Grid Styles */
        .why-choose-videos {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .video-container {
            position: relative;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            aspect-ratio: 16 / 9;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .youtube-style-video-container {
            position: relative;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            aspect-ratio: 16 / 9;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .video-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--border-radius-lg);
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .video-container:hover .video-overlay {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.8), rgba(220, 38, 38, 0.6));
        }

        .video-play-button {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .video-container:hover .video-play-button {
            background-color: var(--white);
            transform: scale(1.1);
        }

        .video-play-button svg {
            color: var(--white);
            margin-left: 4px;
        }

        .video-container:hover .video-play-button svg {
            color: var(--primary-red);
        }

        .video-info h3 {
            color: var(--white);
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .video-info p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.4;
            max-width: 500px;
        }

        /* Playing state styles */
        .video-container.playing .video-overlay {
            opacity: 0;
            pointer-events: none;
        }

        .video-container.playing video {
            border-radius: var(--border-radius-lg);
        }

        /* Video Selector */
        .video-selector {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }

        .video-option {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: var(--border-radius-md);
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            width: 45%;
        }

        .video-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .video-option.active {
            border-color: var(--primary-red);
            background-color: rgba(225, 30, 45, 0.05);
        }

        .option-thumbnail {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
        }

        .option-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .option-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            text-align: left;
        }

        .option-info p {
            font-size: 0.9rem;
            color: var(--gray-600);
            margin: 0;
            text-align: left;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .video-container {
                height: 300px;
            }
            
            .video-info h3 {
                font-size: 1.5rem;
            }
            
            .video-info p {
                font-size: 1rem;
            }
            
            .video-selector {
                flex-direction: column;
            }
            
            .video-option {
                width: 100%;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 48rem) {
            .video-container {
                height: 250px;
            }
            
            .video-play-button {
                width: 60px;
                height: 60px;
            }
            
            .video-play-button svg {
                width: 24px;
                height: 24px;
            }
            
            .video-info h3 {
                font-size: 1.25rem;
            }
            
            .video-info p {
                font-size: 0.875rem;
            }
        }

        /* CTA Section */
        .cta-section {
            padding: 4rem 0;
        }

        .cta-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .cta-text {
            flex: 1;
            min-width: 300px;
        }

        .cta-text h2 {
            font-size: 3.825rem;
            font-weight: 400;
            line-height: 1.2;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .cta-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.125rem;
        }

        .cta-button {
            flex-shrink: 0;
        }

        /* Course Categories */
        .course-categories-section {
            padding: 4rem 0;
        }

        .course-categories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .course-categories-text {
            flex: 1;
        }

        .course-categories-image {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }

        .course-categories-image img {
            max-width: 300px;
            border-radius: var(--border-radius-lg);
        }

        .course-categories {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (min-width: 48rem) {
            .course-categories {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .video-card {
            position: relative;
            height: 200px;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            cursor: pointer;
            transition: transform var(--transition-duration) ease;
        }

        .video-card:hover {
            transform: translateY(-5px);
        }

        .video-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            padding: 2rem 1rem 1rem;
        }

        .video-overlay h3 {
            color: var(--white);
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .play-button svg {
            width: 20px;
            height: 20px;
            color: var(--white);
        }

        .video-card:hover .play-button {
            background-color: var(--primary-red);
        }

        /* YouTube-style Video Container */
        .youtube-style-video-container {
            position: relative;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background-color: #000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }

        .youtube-style-video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        /* All Learning Resources */
        .resources-section {
            position: relative;
            padding: 0;
        }

        .resources-background {
            position: relative;
            height: 400px;
            overflow: hidden;
        }

        .resources-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .resources-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4));
            display: flex;
            align-items: center;
            padding: 0 2rem;
        }

        .resources-content {
            color: var(--white);
            max-width: 600px;
        }

        .resources-content h2 {
            font-size: 2.5rem;
            font-weight: 400;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        @media (min-width: 48rem) {
            .resources-content h2 {
                font-size: 3rem;
            }
        }

        .resources-content p {
            font-size: 1.125rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .resources-cta-btn {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background-color: var(--primary-red);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius-full);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .resources-cta-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .resources-red-section {
            background-color: var(--primary-red);
            padding: 2rem 0;
            height: auto;
        }

        .resources-buttons-container {
            text-align: center;
            margin-top: 4rem;
            padding: 0 1rem;
        }

        /* Pricing Section - Updated to match screenshot exactly */
        .pricing-section {
            padding: 4rem 0;
            background-color: var(--gray-50);
        }

        /* New pricing header layout */
        .pricing-header-new {
            margin-bottom: 4rem;
        }

        .pricing-header-content-new {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-text-center {
            flex: 1;
            text-align: center;
        }

        .pricing-main-title {
            font-size: 3rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1rem;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }

        .pricing-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .pricing-monthly-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .monthly-btn {
            background-color: #2677B8;
            color: var(--white);
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius-full);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .monthly-btn:hover {
            background-color: #1e40af;
        }

        .save-text {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .pricing-image-right {
            flex-shrink: 0;
            max-width: 300px;
        }

        .pricing-image-right img {
            width: 100%;
            height: auto;
            border-radius: var(--border-radius-lg);
        }

        /* Pricing cards grid */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Center single pricing card when only one plan is active */
        .pricing-grid.single-card {
            display: flex;
            justify-content: center;
        }

        .pricing-grid.single-card .pricing-card {
            max-width: 370px;
            width: 100%;
        }

        .feature-disabled {
            color: #b0b0b0 !important;
            text-decoration: line-through;
            opacity: 0.7;
        }

        @media (min-width: 48rem) {
            .pricing-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            /* Reset centering on larger screens with multiple cards */
            .pricing-grid:not(:has(.pricing-card:only-child)) {
                justify-items: start;
            }

            .pricing-grid:has(.pricing-card:only-child) .pricing-card {
                max-width: none;
                width: auto;
            }
        }

        .pricing-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            border: 1px solid #e5e7eb;
        }

        .pricing-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.75rem 2rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(38, 138, 220, 1);
            z-index: 10;
            white-space: nowrap;
            border: 2px solid var(--white);
            min-width: 120px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pricing-badge.essential {
            background-color: var(--secondary-blue);
        }

        .pricing-badge.extra-tuition {
            background-color: var(--secondary-blue);
        }

        .pricing-badge.home-school {
            background-color: var(--secondary-blue);
        }

        .pricing-body {
            padding: 2.5rem 2rem 2rem 2rem; /* Increased top padding */
            max-width: 370px;
            width: auto;
        }

        .pricing-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .pricing-price-section {
            margin-bottom: 2rem;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--gray-900);
            line-height: 1;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem 0;
        }

        .pricing-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .pricing-features svg {
            color: var(--primary-red);
            flex-shrink: 0;
        }

        .pricing-btn-new {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--secondary-blue);
            color: var(--secondary-blue);
            background-color: transparent;
            border-radius: var(--border-radius-md);
            font-weight: 500;
            font-size: 1.15rem;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .pricing-btn-new:hover {
            background-color: rgba(198, 222, 250, 1);
            color: var(--secondary-blue-hover);
        }

        /* Responsive adjustments */
        @media (max-width: 48rem) {
            .pricing-header-content-new {
                flex-direction: column;
                /* text-align: center; */
                gap: 2rem;
            }
            
            .pricing-main-title {
                font-size: 2rem;
            }
            
            .pricing-image-right {
                max-width: 250px;
            }
        }

        @media (max-width: 48rem) {
            .resources-cta-btn {
                position: static;
                margin-top: 1rem;
            }
            
            .resources-btn {
                width: 100% !important;
                padding: 0.85rem 1rem !important;
                font-size: 0.95rem !important;
                border-radius: 12px !important;
                min-width: unset !important;
                box-shadow: 0 4px 10px rgba(0,0,0,0.08) !important;
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            }
        }

        /* Testimonials */
        .testimonials-slider-wrapper {
            position: relative;
            padding: 0 1rem;
            margin-bottom: 2rem;
        }

        .testimonials-grid {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 1.5rem 0.5rem;
            gap: 1.5rem;
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
            scroll-behavior: smooth;
            flex-wrap: nowrap;
        }

        .testimonials-grid::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }

        @media (min-width: 48rem) {
            .testimonials-slider-wrapper {
                padding: 0;
            }
            .testimonials-grid {
                padding: 2rem 0;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
            }
        }

        @media (min-width: 64rem) {
            .testimonials-grid {
                padding: 2rem 0;
            }
        }

        .testimonial-card {
            flex: 0 0 calc(90% - 1.5rem); /* Default for mobile */
            scroll-snap-align: center;
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        @media (min-width: 48rem) {
            .testimonial-card {
                flex: 0 0 400px;
                background-color: var(--red);
                box-shadow: var(--shadow-lg);
                border: 1px solid rgb(235, 229, 229);
            }
        }

        /* Slider Navigation Buttons */
        .slider-nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 48rem) {
            .slider-nav {
                display: flex; /* Ensure visible on desktop */
                justify-content: center;
                margin-top: 2rem;
            }
        }

        .slider-nav-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-600);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .slider-nav-btn:hover {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        .slider-nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .testimonial-content {
            margin-bottom: 1.5rem;
        }

        .testimonial-content p {
            font-style: italic;
            color: var(--gray-900);
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-author img {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-info h4 {
            font-weight: 600;
            color: var(--secondary-blue);
            margin-bottom: 0.25rem;
        }

        .author-info span {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        /* FAQ Section */
        .faq-section {
            padding: 4rem 0;
        }

        .faq-grid {
            display: grid;
            gap: 2rem;
        }

        @media (min-width: 48rem) {
            .faq-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .faq-item h3 {
            color: var(--white);
            font-size: 1.25rem;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .faq-item p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        /* Study Journey Section */
        .study-journey-section {
            background-color: var(--primary-red);
            padding: 3rem 0;
        }

        .study-journey-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .study-journey-text {
            flex: 1;
            min-width: 300px;
        }

        .study-journey-text h2 {
            font-size: 2.5rem;
            font-weight: 400;
            line-height: 1.2;
            color: var(--white);
            margin-bottom: 1rem;
        }

        @media (min-width: 48rem) {
            .study-journey-text h2 {
                font-size: 3rem;
            }
        }

        .study-journey-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.35rem;
            line-height: 1.6;
        }

        .study-journey-button {
            flex-shrink: 0;
        }

        /* Learning Goals Section */
        .learning-goals-section {
            background-color: var(--white);
            padding: 4rem 0;
        }

        .learning-goals-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            align-items: center;
        }

        @media (min-width: 48rem) {
            .learning-goals-content {
                grid-template-columns: 1fr 1fr;
            }
        }

        .learning-goals-image img {
            width: 100%;
            height: auto;
            border-radius: var(--border-radius-lg);
            object-fit: cover;
        }

        .learning-goals-text h3 {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.4;
            color: var(--gray-900);
            margin-bottom: 1rem;
        }

        @media (min-width: 48rem) {
            .learning-goals-text h3 {
                font-size: 1.75rem;
            }
        }

        .learning-goals-text p {
            color: var(--gray-600);
            font-size: 1.3rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Course Videos Section - Updated with hover-to-play functionality */
        .course-videos-section {
            background-color: var(--gray-50);
            padding: 4rem 0;
        }

        .course-videos-title {
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            color: var(--gray-900);
            margin-bottom: 3rem;
        }

        .course-videos-grid {
            display: grid;
            grid-template-columns: 1fr; /* Single column by default on Mobile */
            gap: 1.5rem;
        }

        @media (min-width: 48rem) {
            .course-videos-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .hover-video-card {
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            cursor: pointer;
            transition: transform var(--transition-duration) ease;
            background: #000;
            position: relative;
        }

        .hover-video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .hover-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        .hover-video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
            transition: opacity 0.3s ease;
        }

        .hover-video-card:hover .hover-video-overlay {
            opacity: 0.3;
        }

        .hover-video-card.playing .hover-video-overlay {
            opacity: 0;
        }

        .play-button {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .play-button svg {
            width: 20px;
            height: 20px;
            color: var(--white);
            margin-left: 2px;
        }

        .hover-video-card:hover .play-button {
            background-color: var(--primary-red);
            transform: scale(1.1);
        }

        .hover-video-overlay h3 {
            color: var(--white);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .hover-video-overlay p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            margin: 0;
        }

        /* Video loading state */
        .hover-video-card.loading .hover-video {
            opacity: 0.7;
        }

     /* All Learning Resources - Custom container for exact screenshot match */
        .resources-section {
            position: relative;
            padding: 0;
        }

        .resources-background {
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .resources-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .resources-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4));
            display: flex;
            align-items: center;
            padding: 0;
        }

        /* Custom container without max-width restrictions */
        .resources-custom-container {
            width: 100%;
            padding: 0 4rem;
        }

        .resources-content-layout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 4rem;
        }

        .resources-text-content {
            flex: 1;
            max-width: none; /* Remove width restrictions */
        }

        .resources-main-title {
            font-size: 5.5rem;
            font-weight: 300;
            line-height: 1.0;
            color: var(--white);
            margin-bottom: 1.5rem;
            letter-spacing: 0.02em;
            width: 70%; /* Control width to force natural 2-line break */
            word-spacing: 0.05em;
        }

        .resources-subtitle {
            color: var(--white);
            font-size: 1.125rem;
            font-weight: 400;
            margin: 0;
            opacity: 0.9;
            letter-spacing: 0.05em;
        }

        .resources-cta-section {
            flex-shrink: 0;
            margin-right: 2rem;
        }

        .resources-cta-btn-new {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 1.2rem 3.5rem;
            border-radius: var(--border-radius-lg);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.125rem;
            transition: all 0.3s ease;
            display: inline-block;
            min-width: 220px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .resources-cta-btn-new:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        }

        .resources-buttons-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto 2rem;
        }

        .resources-btn {
            flex: 1;
            min-width: fit-content;
            padding: 1rem 2rem;
            background-color: var(--white);
            color: var(--primary-red);
            border-radius: var(--border-radius-full);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .resources-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--gray-200), #c1121f);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .resources-btn:hover::before {
            opacity: 1;
        }

        .resources-btn:hover {
            background-color: var(--gray-100);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .explore-all-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .explore-all-text {
            color: var(--white);
            font-size: 1.7rem;
            margin: 0;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Ultra-modern animation for explore arrow */
        .explore-arrow {
            animation: float 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .explore-arrow:hover {
            transform: translateY(2px);
        }

        .explore-arrow svg {
            width: 20px;
            height: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 64rem) {
            .resources-custom-container {
                padding: 0 2rem;
            }
            
            .resources-content-layout {
                flex-direction: column;
                text-align: center;
                gap: 2rem;
            }
            
            .resources-main-title {
                font-size: 4rem;
                width: 80%;
            }
            
            .resources-cta-section {
                margin-right: 0;
            }
        }

        @media (min-width: 480px) and (max-width: 767px) {
            .resources-buttons-grid {
                grid-template-columns: repeat(3, 1fr);
                max-width: 600px;
            }
        }

        @media (max-width: 48rem) {
            .resources-custom-container {
                padding: 0 1rem;
            }
            
            .resources-main-title {
                font-size: 3rem;
                width: 90%;
            }
            
            .resources-subtitle {
                font-size: 1rem;
            }

            .resources-btn:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 6px 15px rgba(0,0,0,0.12) !important;
            }

            .explore-all-section {
                flex-direction: row !important;
                justify-content: center;
                gap: 0.75rem !important;
                margin-top: 1rem;
            }

            .explore-all-text {
                font-size: 1.25rem !important;
            }

            .explore-arrow svg {
                width: 24px !important;
                height: 24px !important;
            }
        }

        /* Pricing Section - Updated */
        .pricing-section {
            padding: 4rem 0;
            background-color: var(--gray-50);
        }

        .pricing-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            gap: 2rem;
        }

        @media (max-width: 48rem) {
            .pricing-header-content {
                flex-direction: column;
                text-align: center;
            }
        }

        .pricing-header-text {
            flex: 1;
            max-width: 600px;
        }

        .pricing-header-text h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--gray-900);
        }

        @media (min-width: 48rem) {
            .pricing-header-text h2 {
                font-size: 2.5rem;
            }
        }

        .pricing-header-text p {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.6;
        }

        .pricing-header-image {
            flex-shrink: 0;
            max-width: 300px;
        }

        .pricing-header-image img {
            width: 100%;
            height: auto;
            border-radius: var(--border-radius-lg);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 20px; /* Add padding to accommodate floating badges */
        }

        @media (min-width: 48rem) {
            .pricing-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .pricing-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: visible; /* Changed from hidden to visible */
            position: relative;
            border: 1px solid #e5e7eb;
            margin-top: 20px; /* Add margin to accommodate floating badge */
        }

        .pricing-header {
            margin: 2rem;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--gray-900);
            line-height: 1;
        }

        .price-period {
            color: var(--gray-500);
            font-size: 0.875rem;
            display: block;
            margin-top: 0.25rem;
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
            margin-bottom: 1rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .pricing-features svg {
            color: var(--primary-red);
            flex-shrink: 0;
        }

        @media (max-width: 48rem) {
            .resources-cta-btn {
                position: static;
                margin-top: 1rem;
            }
        }


        /* FAQ Section - Reduced sizes for better proportions */
        .faq-section {
            padding: 2rem 0;
            background-color: var(--primary-red);
        }

        .faq-main-title {
            color: var(--white);
            font-size: 3rem;
            font-weight: 400;
            text-align: left;
            margin-bottom: 2rem;
            letter-spacing: 0.05em;
        }

        .faq-list {
            max-width: 1000px;
            margin: 0;
            position: relative;
            padding-left: 2rem;
        }

        /* Single vertical line for the entire FAQ section - shorter */
        .faq-list::before {
            content: '';
            position: absolute;
            max-height: 5rem;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--white);
        }

        .faq-item-new {
            margin-bottom: 2rem;
        }

        .faq-content {
            padding-left: 0.75rem;
        }

        .faq-question {
            color: var(--white);
            font-size: 1.25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .faq-divider {
            width: 100%;
            height: 1.5px;
            background-color: var(--white);
            margin-bottom: 1rem;
            opacity: 0.2;
        }

        .faq-answer {
            color: var(--white);
            font-size: 1rem;
            line-height: 1.5;
            margin: 0;
            opacity: 0.95;
            max-width: 800px;
        }

        /* Responsive adjustments */
        @media (max-width: 48rem) {
            .faq-section {
                padding: 2rem 0;
            }
            
            .faq-main-title {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            
            .faq-question {
                font-size: 1.1rem;
            }
            
            .faq-answer {
                font-size: 0.9rem;
            }
            
            .faq-list {
                padding-left: 1rem;
            }
            
            .faq-item-new {
                margin-bottom: 1.5rem;
            }
            
            .faq-content {
                padding-left: 0.5rem;
            }
        }

        /* Newsletter */
        .newsletter-signup {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
            padding: 3rem;
            background-color: var(--gray-50);
            border-radius: var(--border-radius-lg);
        }

        /* Modern responsive techniques for Why Choose section */
        @media (max-width: 768px) {
            .why-choose {
            flex-direction: column-reverse; /* Better content flow on mobile */
            gap: 1.5rem;
            }
            
            .why-choose-videos {
            width: 100%;
            margin-bottom: 1rem;
            }
            
            .youtube-style-video-container {
            height: 250px !important; /* Better aspect ratio for mobile */
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            }
            
            .why-choose-text h2 {
            font-size: 1.8rem !important; /* Proper mobile font size */
            line-height: 1.3;
            margin-bottom: 1.2rem;
            }
            
            .feature-list li {
            font-size: 1.05rem !important; /* Better readability */
            align-items: flex-start;
            margin-bottom: 1.2rem;
            }
            
            .feature-list svg {
            margin-top: 0.3rem; /* Better icon alignment */
            }
            
            /* Modern touch targets */
            .feature-list span {
            display: inline-block;
            padding: 4px 0;
            }
        }

        /* Ultra-modern fluid typography */
        .why-choose-text h2 {
            font-size: clamp(1.8rem, 5vw, 2.25rem);
        }
        
        .feature-list li {
            font-size: clamp(1rem, 1.2vw, 1.4rem);
        }

        /* Enhanced mobile interaction */
        .feature-list li {
            transition: transform 0.3s ease;
        }
        
        .feature-list li:active {
            transform: scale(0.98);
        }

        /* Modern pulse + shimmer animation for resource buttons */
        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(220,38,38, 0.25), 0 2px 10px rgba(0,0,0,0.08);
            }
            50% {
                box-shadow: 0 0 16px 6px rgba(220,38,38, 0.35), 0 2px 20px rgba(0,0,0,0.12);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        .resources-btn {
            position: relative;
            overflow: hidden;
            animation: pulseGlow 2.5s infinite;
            background: linear-gradient(90deg, #fff 0%, #ffe5e5 50%, #fff 100%);
            background-size: 200% 100%;
            transition: transform 0.2s cubic-bezier(.4,2,.6,1), box-shadow 0.2s;
        }

        .resources-btn::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(220,38,38,0.12) 50%, transparent 70%);
            background-size: 200% 100%;
            animation: shimmer 2.2s infinite linear;
            pointer-events: none;
            z-index: 2;
        }

        .resources-btn:hover, .resources-btn:focus {
            transform: scale(1.06) rotate(-1deg);
            box-shadow: 0 0 24px 6px rgba(220,38,38,0.25), 0 4px 20px rgba(0,0,0,0.12);
            background: linear-gradient(90deg, #fff 0%, #ffd6d6 50%, #fff 100%);
        }

        .resources-btn:active {
            transform: scale(0.98);
        }
    </style>

    <!-- Hero Section -->
    <header class="hero" role="banner" aria-labelledby="hero-title">
        <div class="hero-background">
            <video preload="auto" loading="lazy" autoplay muted loop playsinline poster="{{ secure_asset('images/hero-image.png') }}" aria-hidden="true">
                <source src="{{ secure_asset('videos/hero-video.mp4') }}" type="video/mp4">
                <source src="{{ secure_asset('videos/hero-video.webm') }}" type="video/webm">
                Your browser does not support the video tag.
            </video>
            <div class="hero-overlay"></div>
        </div>

        <div class="hero-content">
            <div class="hero-text">
                <h1 id="hero-title" class="hero-title">
                    <span class="hero-title-main">Video lessons for every level</span>
                    <span class="hero-title-sub">Pass exams and build skills</span>
                </h1>
                <!-- <div class="hero-description">
                    <p class="journey-subtitle" style="max-width: 800px; font-size: clamp(0.9rem, 2.5vw, 1.125rem); line-height: 1.5; margin-bottom: 2rem;">
                        Professional educational content designed to help African students and professionals excel in their careers and academic journeys.
                    </p>
                </div> -->
                <div class="hero-actions">
                    <a href="{{ route('signup') }}" class="hero-btn" style="background-color: #008cffff; color: #f9fbfcff; font-weight: 700; font-family: 'Work Sans', sans-serif; text-transform: uppercase; letter-spacing: 0.05em; border: none; box-shadow: 0 4px 14px 0 rgba(0, 136, 255, 0.39);">
                        Get Started
                    </a>
                </div>

                <div class="trusted-badge" style="margin-top: 3rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem; color: rgba(255, 255, 255, 0.6); font-family: 'Work Sans', sans-serif; font-size: clamp(0.65rem, 3vw, 0.875rem); font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; width: 100%;">
                    <div style="height: 1px; flex: 1; max-width: 40px; background: rgba(255, 255, 255, 0.3);"></div>
                    <span style="white-space: nowrap;">Trusted by 50,000+ Learners</span>
                    <div style="height: 1px; flex: 1; max-width: 40px; background: rgba(255, 255, 255, 0.3);"></div>
                </div>
            </div>
        </div>
    </header>

    <main role="main">
        <!-- Why Choose ShoutOutGH -->
        <section class="section bg-white" aria-labelledby="why-choose-title" itemscope itemtype="https://schema.org/Service">
            <div class="container">
                <div class="why-choose">
                    <div class="why-choose-text">
                        <p class="tools-on-shoutout">Tools on ShoutoutGh</p>
                        <h2 id="why-choose-title" class="text-3xl font-bold mb-6" itemprop="name">Why Choose ShoutOutGH?</h2>
                        <p class="discover-innovative-features" itemprop="description">
                            Discover innovative features, designed to simplify your study process and maximize your academic success.
                        </p>
                        <hr class="hr">
                        <ul class="feature-list" itemscope itemtype="https://schema.org/ItemList">
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <meta itemprop="position" content="1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span itemprop="name">Access a library of curated resources tailored to your needs</span>
                            </li>
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <meta itemprop="position" content="2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span itemprop="name">Get interactive tools to enhance your understanding of complex topics</span>
                            </li>
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <meta itemprop="position" content="3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span itemprop="name">Learn at your own convenient time on our platform</span>
                            </li>
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <meta itemprop="position" content="4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span itemprop="name">Make personal note edit and save on your preferences</span>
                            </li>
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <meta itemprop="position" content="4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span itemprop="name">Test your knowledge with interactive quizzes</span>
                            </li>
                        </ul>
                    </div>
                    <div class="why-choose-videos">
                        <div class="youtube-style-video-container">
                            <video
                                preload="auto"
                                loading="lazy"
                                autoplay
                                id="whyChooseVideo"
                                muted
                                loop
                                playsinline
                                preload="metadata"
                                poster="{{ secure_asset('images/student-focus.png') }}"
                                aria-label="Video demonstration of ShoutOutGH's interactive learning features"
                            >
                                <source src="{{ secure_asset('videos/personalized-study.mp4') }}" type="video/mp4">
                                <track kind="captions" src="" srclang="en" label="English captions">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Start Your Study Journey CTA -->
        <!-- <section class="study-journey-section" aria-labelledby="journey-title">
            <div class="container">
                <div class="study-journey-content">
                    <div class="study-journey-text">
                        <h2 id="journey-title" class="journey-title">Start Your Study<br>Journey Today</h2>
                        <p class="journey-subtitle">Join thousands of students who are achieving their goals with ShoutOutGH.<br>Signup now and unlock your potentials</p>
                    </div>
                    <div class="study-journey-button">
                        <a href="{{ route('signup') }}" class="btn btn-white" aria-label="Sign up for ShoutOutGH to start your learning journey">Sign up now</a>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- Learning Goals Section -->
        <!-- <section class="learning-goals-section" aria-labelledby="learning-goals-title">
            <div class="container">
                <div class="learning-goals-content">
                    <div class="learning-goals-image">
                        <img src="{{ secure_asset('images/team-learning.png') }}" alt="Students collaborating and learning together on ShoutOutGH platform" loading="lazy">
                    </div>
                    <div class="learning-goals-text">
                        <h3 id="learning-goals-title">We have quizzes that questions and answer choice on all levels, from grade one to tertiary level.</h3>
                        <p>All resources based on the level selected and all on the platform.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary" aria-label="Get started with learning on ShoutOutGH">Get Started</a>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- Course Videos Section -->
        <!-- <section class="course-videos-section" aria-labelledby="course-videos-title">
            <div class="container">
                <h2 id="course-videos-title" class="course-videos-title">Explore Over 1M Learning Videos on ShoutOutGH</h2>
                <div class="course-videos-grid" role="list" aria-label="Course categories">
                    @foreach($courseCategories as $index => $category)
                        <article class="hover-video-card" data-video-id="course-video-{{ $index }}" role="listitem" itemscope itemtype="https://schema.org/VideoObject">
                            <meta itemprop="name" content="{{ $category['title'] }} Learning Videos">
                            <meta itemprop="description" content="Educational videos for {{ $category['title'] }} students on ShoutOutGH">
                            <meta itemprop="thumbnailUrl" content="{{ secure_asset($category['img']) }}">
                            <meta itemprop="uploadDate" content="2024-01-01">
                            <meta itemprop="duration" content="PT3M">
                            <meta itemprop="interactionStatistic" content="1000+ views">
                            <video
                                id="course-video-{{ $index }}"
                                class="hover-video"
                                muted
                                loop
                                poster="{{ secure_asset($category['img']) }}"
                                preload="metadata"
                                aria-label="Preview video for {{ $category['title'] }} courses"
                            >
                                <source src="{{ secure_asset($category['preview_video']) }}" type="video/mp4">
                                <track kind="captions" src="" srclang="en" label="English captions">
                                Your browser does not support the video tag.
                            </video>
                            <div class="hover-video-overlay">
                                <div class="play-button" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </div>
                                <h3 itemprop="headline">{{ $category['title'] }}</h3>
                                <p>Learn more</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section> -->

    <!-- All Learning Resources -->
    <!-- <section class="resources-section">
        <div class="resources-background">
            <img src="{{ secure_asset('images/resources-bg.png') }}" alt="Learning resources" class="resources-image">
            <div class="resources-overlay">
                <div class="resources-custom-container">
                    <div class="resources-content-layout">
                        <div class="resources-text-content">
                            <h2 class="resources-main-title">All learning resources available for you.</h2>
                            <p class="resources-subtitle">At your own comfort with just a click >>>>>></p>
                        </div>
                        <div class="resources-cta-section">
                            <a href="{{ route('login') }}" class="resources-cta-btn-new">Join us now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="resources-red-section">
            <div class="container">
                <div class="resources-buttons-container">
                    <div class="resources-buttons-grid">
                        <a href="#" class="resources-btn">Lesson videos</a>
                        <a href="#" class="resources-btn">Quizzes</a>
                        <a href="#" class="resources-btn">PDF</a>
                        <a href="#" class="resources-btn">PPT</a>
                        <a href="#" class="resources-btn">Past Question</a>
                    </div>
                    <div class="explore-all-section">
                        <p class="explore-all-text">Explore all</p>
                        <div class="explore-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

        <!-- Pricing Plans -->
        <section class="pricing-section bg-white" aria-labelledby="pricing-title" itemscope itemtype="https://schema.org/Service">
            <div class="container">
                <!-- Pricing Header -->
                <header class="pricing-header-new">
                    <div class="pricing-header-content-new">
                        <div class="pricing-text-center">
                            <h2 id="pricing-title" class="pricing-main-title" itemprop="name">Choose Your Learning Plan</h2>
                            <p class="pricing-subtitle">Select your membership plan tailored to your needs.<br>customize your subscription for a seamless fit.</p>
                            <div class="pricing-monthly-section">
                                <button class="monthly-btn" aria-label="Switch to monthly billing">Annually</button>
                                <span class="save-text">save more than 10%</span>
                            </div>
                        </div>
                        <div class="pricing-image-right">
                            <img src="{{ secure_asset('images/student-laptop.png') }}" alt="Student learning on laptop with ShoutOutGH platform" loading="lazy">
                        </div>
                    </div>
                </header>

                <!-- Pricing Cards -->
                <div class="pricing-grid {{ $pricingPlans->count() === 1 ? 'single-card' : '' }}" @if($pricingPlans->count() > 0) role="list" aria-label="Available pricing plans" @endif>
                    @forelse($pricingPlans as $plan)
                        <article class="pricing-card" role="listitem" itemscope itemtype="https://schema.org/Product">
                            <meta itemprop="name" content="{{ $plan->name }} Plan">
                            <meta itemprop="description" content="{{ $plan->description ?? 'Comprehensive learning package with access to all platform features.' }}">
                            <div class="pricing-badge {{ strtolower(str_replace(' ', '-', $plan->name)) }}">{{ $plan->name }}</div>
                            <div class="pricing-body">
                                <p class="pricing-description">{{ $plan->description ?? 'Comprehensive learning package with access to all platform features.' }}</p>
                                <div class="pricing-price-section" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                    <meta itemprop="priceCurrency" content="{{ $plan->currency }}">
                                    <meta itemprop="price" content="{{ $plan->price }}">
                                    <span class="price">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                                </div>
                                <ul class="pricing-features" itemscope itemtype="https://schema.org/ItemList">
                                    {{-- The issue is in this nested conditional logic --}}
                                    @if(!empty($plan->features) && is_array($plan->features))
                                        @foreach($plan->features as $index => $feature)
                                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                                <meta itemprop="position" content="{{ $index + 1 }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                </svg>
                                                <span itemprop="name">{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    @else
                                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                            <meta itemprop="position" content="1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                            <span itemprop="name">Access to {{ $plan->name }} features</span>
                                        </li>
                                    @endif
                                </ul>
                                <a href="{{ route('pricing-details') }}" class="pricing-btn-new" aria-label="Get started with {{ $plan->name }} plan">Get Started</a>
                            </div>
                        </article>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;" role="status" aria-live="polite">
                            <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No pricing plans available</h3>
                            <p style="color: var(--gray-500);">Pricing plans are being configured. Please check back later.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="section bg-gray-50" aria-labelledby="testimonials-title">
            <div class="container">
                <h2 id="testimonials-title" class="section-title text-center">What our students say</h2>
                <div class="testimonials-slider-wrapper" x-data="{ 
                    scrollNext() { $refs.slider.scrollBy({ left: $refs.slider.offsetWidth * 0.8, behavior: 'smooth' }) },
                    scrollPrev() { $refs.slider.scrollBy({ left: -$refs.slider.offsetWidth * 0.8, behavior: 'smooth' }) }
                }">
                    <div class="testimonials-grid" role="list" aria-label="Student testimonials" x-ref="slider">
                        @foreach($testimonials as $testimonial)
                            <article class="testimonial-card" role="listitem" itemscope itemtype="https://schema.org/Review">
                                <meta itemprop="reviewRating" content="5">
                                <div class="testimonial-content" itemprop="reviewBody">
                                    <p>"{{ $testimonial['quote'] }}"</p>
                                </div>
                                <div class="testimonial-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
                                    <img src="{{ secure_asset($testimonial['image']) }}" alt="Photo of {{ $testimonial['name'] }}" loading="lazy" itemprop="image">
                                    <div class="author-info">
                                        <h4 itemprop="name">{{ $testimonial['name'] }}</h4>
                                        <span itemprop="jobTitle">{{ $testimonial['role'] }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Slider Navigation -->
                    <div class="slider-nav">
                        <button class="slider-nav-btn" @click="scrollPrev()" aria-label="Previous testimonial">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button class="slider-nav-btn" @click="scrollNext()" aria-label="Next testimonial">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section bg-red" aria-labelledby="faq-title">
            <div class="container">
                <h2 id="faq-title" class="faq-main-title">FAQ</h2>
                <div class="faq-list" itemscope itemtype="https://schema.org/FAQPage">
                    @foreach($faqs as $faq)
                        <div class="faq-item-new" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <div class="faq-content">
                                <h3 class="faq-question" itemprop="name">{{ strtoupper($faq['question']) }}</h3>
                                <div class="faq-divider"></div>
                                <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                    <p class="faq-answer" itemprop="text">{!! $faq['answer'] !!}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        let currentlyPlaying = null;

        function toggleVideo(videoId) {
            const video = document.getElementById(videoId);
            const container = video.parentElement;
            const overlay = document.getElementById('overlay' + videoId.slice(-1));
            
            if (currentlyPlaying && currentlyPlaying !== video) {
                // Pause currently playing video
                currentlyPlaying.pause();
                currentlyPlaying.currentTime = 0;
                currentlyPlaying.parentElement.classList.remove('playing');
            }
            
            if (video.paused) {
                video.play();
                container.classList.add('playing');
                currentlyPlaying = video;
            } else {
                video.pause();
                video.currentTime = 0;
                container.classList.remove('playing');
                currentlyPlaying = null;
            }
        }

        // Handle video end
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('.video-container video');
            videos.forEach(video => {
                video.addEventListener('ended', function() {
                    this.currentTime = 0;
                    this.parentElement.classList.remove('playing');
                    currentlyPlaying = null;
                });
            });
        });

        // YouTube-style hover-to-play functionality
        document.addEventListener('DOMContentLoaded', function() {
            const videoContainer = document.querySelector('.youtube-style-video-container');
            const video = document.getElementById('whyChooseVideo');
            
            // Play video on hover
            videoContainer.addEventListener('mouseenter', function() {
                if (video.paused) {
                    video.play().catch(e => {
                        console.log('Autoplay prevented:', e);
                    });
                }
            });
            
            // Pause video when mouse leaves
            videoContainer.addEventListener('mouseleave', function() {
                if (!video.paused) {
                    video.pause();
                    // Reset to beginning
                    video.currentTime = 0;
                }
            });
            
            // Handle touch devices
            videoContainer.addEventListener('click', function() {
                if (video.paused) {
                    video.play().catch(e => console.log('Autoplay prevented on touch:', e));
                } else {
                    video.pause();
                }
            });
            
            // Handle video end
            video.addEventListener('ended', function() {
                this.currentTime = 0;
            });
        });
    </script>
@endsection
