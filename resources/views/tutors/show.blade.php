<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $tutor->name }} - Verified Tutor Profile | {{ config('app.name', 'DigiLearn') }}</title>
    <meta name="description" content="{{ $tutor->tutorProfile->tagline ?? 'Book 1-on-1 online tutoring sessions with ' . $tutor->name . ' on DigiLearn.' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root, [data-theme="light"] {
            --primary-blue: #2677B8;
            --primary-blue-hover: #1d6199;
            --accent-red: #E11E2D;
            --accent-green: #10b981;
            --bg-main: #f8fafc;
            --bg-surface: #ffffff;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
            --header-bg: rgba(255, 255, 255, 0.9);
        }

        [data-theme="dark"] {
            --primary-blue: #3b82f6;
            --primary-blue-hover: #2563eb;
            --accent-red: #ef4444;
            --accent-green: #10b981;
            --bg-main: #090d16;
            --bg-surface: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
            --header-bg: rgba(15, 23, 42, 0.9);
            color-scheme: dark;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Dedicated Header */
        .tutor-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--header-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 1.5rem;
        }

        .tutor-header-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .tutor-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .tutor-logo img {
            height: 42px;
            width: auto;
            object-fit: contain;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            background: rgba(38, 119, 184, 0.08);
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background: rgba(38, 119, 184, 0.15);
            transform: translateX(-2px);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .theme-toggle-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.2s;
        }

        .theme-toggle-btn:hover {
            color: var(--text-main);
            border-color: var(--primary-blue);
        }

        /* Container Layout */
        .tutor-profile-container {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
            width: 100%;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
        }

        @media (max-width: 1024px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .profile-card {
            background: var(--bg-card);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        /* Hero Card Header */
        .hero-banner {
            display: flex;
            align-items: flex-start;
            gap: 1.75rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .tutor-avatar {
            width: 110px;
            height: 110px;
            border-radius: 24px;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }

        .avatar-placeholder {
            width: 110px;
            height: 110px;
            border-radius: 24px;
            background: linear-gradient(135deg, #2677B8, #E11E2D);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.75rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .tutor-name {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .tutor-tagline {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 0.25rem;
        }

        .badges-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .badge-verified {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .badge-rate {
            background: rgba(38, 119, 184, 0.12);
            color: var(--primary-blue);
            border: 1px solid rgba(38, 119, 184, 0.25);
        }

        /* Subjects Section */
        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0.85rem;
            margin-top: 1rem;
        }

        .subject-item-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            padding: 1rem;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, border-color 0.2s;
        }

        .subject-item-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary-blue);
        }

        .subject-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        .subject-rate {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--accent-green);
        }

        /* Video Container */
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #000;
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Sticky Booking Widget */
        .booking-card {
            background: var(--bg-card);
            border-radius: 20px;
            border: 2px solid var(--primary-blue);
            padding: 1.75rem;
            box-shadow: 0 12px 35px -5px rgba(38, 119, 184, 0.15);
            position: sticky;
            top: 90px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-select:focus {
            border-color: var(--primary-blue);
        }

        .price-summary-box {
            background: rgba(38, 119, 184, 0.06);
            border: 1px solid rgba(38, 119, 184, 0.15);
            border-radius: 14px;
            padding: 1.25rem;
            margin: 1.5rem 0;
        }

        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .price-total {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-blue);
        }

        .submit-booking-btn {
            width: 100%;
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-booking-btn:hover {
            background: var(--primary-blue-hover);
            transform: translateY(-2px);
        }

        .slots-grid-box {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 0.5rem;
            margin-top: 0.35rem;
            max-height: 180px;
            overflow-y: auto;
            padding: 0.25rem;
        }

        .slot-btn {
            padding: 0.5rem 0.35rem;
            border: 1.5px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            font-family: inherit;
        }

        .slot-btn:hover:not(:disabled) {
            border-color: var(--primary-blue);
            background: rgba(38, 119, 184, 0.08);
        }

        .slot-btn.selected {
            border-color: var(--primary-blue);
            background: var(--primary-blue);
            color: white !important;
            box-shadow: 0 4px 10px rgba(38, 119, 184, 0.3);
        }

        .slot-btn:disabled, .slot-btn.unavailable {
            opacity: 0.45;
            cursor: not-allowed;
            background: var(--bg-main);
            text-decoration: line-through;
        }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) { }
        })();
    </script>
</head>

<body>
    <!-- Dedicated Tutor Page Header -->
    <header class="tutor-header">
        <div class="tutor-header-inner">
            <a href="{{ route('home') }}" class="tutor-logo">
                <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="DigiLearn">
            </a>

            <a href="{{ route('tutors.index') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i> Browse Marketplace
            </a>

            <div class="header-actions">
                <button id="themeToggleBtn" class="theme-toggle-btn" aria-label="Toggle Theme">
                    <i class="fas fa-moon" id="themeIconDark"></i>
                    <i class="fas fa-sun" id="themeIconLight" style="display: none;"></i>
                </button>

                @auth
                    <a href="{{ route('dashboard.main') }}" class="back-btn" style="background: var(--accent-red); color: white;">
                        <i class="fas fa-graduation-cap"></i> Learning Hub
                    </a>
                @else
                    <a href="{{ route('login') }}" class="back-btn">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Tutor Profile Container -->
    <div class="tutor-profile-container">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; border: 1px solid #a7f3d0; font-weight: 600;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; border: 1px solid #fca5a5; font-weight: 600;">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <div class="profile-grid">
            <!-- Left Main Column -->
            <div>
                <!-- Hero & About Profile Card -->
                <div class="profile-card">
                    <div class="hero-banner">
                        @if($tutor->tutorProfile && $tutor->tutorProfile->headshot_path)
                            <img src="{{ route('admin.tutors.document', ['id' => $tutor->tutorProfile->id, 'type' => 'headshot']) }}" alt="{{ $tutor->name }}" class="tutor-avatar">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($tutor->name, 0, 1)) }}
                            </div>
                        @endif

                        <div style="flex: 1;">
                            <h1 class="tutor-name">{{ $tutor->name }}</h1>
                            <div class="tutor-tagline">{{ $tutor->tutorProfile->tagline ?? 'Certified Professional Educator' }}</div>
                            
                            <div class="badges-row">
                                @if($tutor->tutorProfile && $tutor->tutorProfile->is_approved)
                                    <span class="badge-pill badge-verified">
                                        <i class="fas fa-shield-check"></i> Verified Tutor
                                    </span>
                                @endif
                                <span class="badge-pill badge-rate">
                                    <i class="fas fa-tag"></i> {{ $tutor->tutorProfile->rate_range }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- About Me -->
                    <div style="margin-top: 2rem;">
                        <h2 class="section-title">
                            <i class="fas fa-user-circle" style="color: var(--primary-blue);"></i> About Me
                        </h2>
                        <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; white-space: pre-line;">{{ $tutor->tutorProfile->bio ?? 'No biography provided.' }}</p>
                    </div>

                    <!-- Qualifications -->
                    <div style="margin-top: 2rem;">
                        <h2 class="section-title">
                            <i class="fas fa-graduation-cap" style="color: var(--accent-red);"></i> Qualifications & Academic Background
                        </h2>
                        <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; white-space: pre-line;">{{ $tutor->tutorProfile->qualifications ?? 'No qualification text provided.' }}</p>
                    </div>
                </div>

                <!-- Expertise Subjects Card -->
                <div class="profile-card">
                    <h2 class="section-title">
                        <i class="fas fa-book-open" style="color: var(--accent-green);"></i> Expertise Domains & Rates
                    </h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Select any of the subjects below when scheduling your private 1-on-1 lesson.</p>

                    <div class="subjects-grid">
                        @forelse($tutor->tutorSubjects as $ts)
                            <div class="subject-item-card">
                                <div>
                                    <div class="subject-name">{{ $ts->subject->name ?? 'Subject' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Interactive Session</div>
                                </div>
                                <div class="subject-rate">GHS {{ number_format($ts->hourly_rate, 2) }}/hr</div>
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; color: var(--text-muted); font-style: italic;">No specific subject rates configured.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Intro Video Card (If available) -->
                @if($tutor->tutorProfile && $tutor->tutorProfile->intro_video_url)
                    <div class="profile-card">
                        <h2 class="section-title">
                            <i class="fas fa-circle-play" style="color: #8b5cf6;"></i> Introduction Video
                        </h2>
                        <div class="video-wrapper">
                            @php
                                $videoUrl = $tutor->tutorProfile->intro_video_url;
                                if (str_contains($videoUrl, 'youtube.com/watch?v=')) {
                                    $embedUrl = str_replace('watch?v=', 'embed/', $videoUrl);
                                } elseif (str_contains($videoUrl, 'youtu.be/')) {
                                    $embedUrl = str_replace('youtu.be/', 'youtube.com/embed/', $videoUrl);
                                } else {
                                    $embedUrl = $videoUrl;
                                }
                            @endphp
                            <iframe src="{{ $embedUrl }}" title="Tutor Introduction Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Sticky Booking Widget -->
            <div>
                <div class="booking-card">
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">Book a 1-on-1 Session</h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Schedule a live private session with {{ explode(' ', $tutor->name)[0] }}.</p>

                    @if(session('error'))
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.75rem; padding: 0.875rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #991b1b; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span style="flex: 1;">{{ session('error') }}</span>
                            <a href="{{ route('wallet.index') }}" style="color: #2563eb; font-weight: 700; text-decoration: underline; white-space: nowrap;">Top Up Now →</a>
                        </div>
                    @endif

                    <form action="{{ route('bookings.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tutor_id" value="{{ $tutor->id }}">

                        <div class="form-group">
                            <label for="subject_id" class="form-label">Select Subject</label>
                            <select name="subject_id" id="subject_id" class="form-select" required onchange="updateCalculatedPrice()">
                                <option value="" disabled selected>Choose subject...</option>
                                @foreach($tutor->tutorSubjects as $ts)
                                    <option value="{{ $ts->subject->id }}" data-rate="{{ $ts->hourly_rate }}">
                                        {{ $ts->subject->name }} (GHS {{ number_format($ts->hourly_rate, 2) }}/hr)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="duration_hours" class="form-label">Lesson Duration</label>
                            <select name="duration_hours" id="duration_hours" class="form-select" required onchange="onBookingDateOrDurationChange()">
                                <option value="1" selected>1 Hour</option>
                                <option value="2">2 Hours</option>
                                <option value="3">3 Hours</option>
                                <option value="4">4 Hours</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="booking_date" class="form-label">Select Date</label>
                            <input type="date" name="booking_date" id="booking_date" class="form-select" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+30 days')) }}" required onchange="onBookingDateOrDurationChange()">
                        </div>

                        <div class="form-group" id="time_slots_group">
                            <label class="form-label">Available Time Slots</label>
                            <input type="hidden" name="start_time" id="selected_start_time" required>
                            <div id="slots_container" class="slots-grid-box">
                                <div style="font-size: 0.8rem; color: var(--text-muted); grid-column: 1 / -1;">Select a date above to view available time slots.</div>
                            </div>
                        </div>

                        <!-- Price Calculation Display Box -->
                        <div class="price-summary-box">
                            <div class="price-row">
                                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted);">Total Credits Required:</span>
                                <span class="price-total" id="total_price_display">0.00</span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 0.65rem; margin-top: 0.65rem;">
                                <span>1 Credit = GHS 1.00</span>
                                @auth
                                    <span style="display: block; margin-top: 0.25rem;">
                                        Your Balance: <strong style="color: {{ auth()->user()->credit_balance > 0 ? '#10b981' : '#ef4444' }};">{{ number_format(auth()->user()->credit_balance, 2) }} Credits</strong>
                                        @if(auth()->user()->credit_balance < 10)
                                            <a href="{{ route('wallet.index') }}" style="display:inline-block; margin-left:0.5rem; font-size:0.78rem; color:#2677B8; font-weight:600; text-decoration:underline;">+ Top Up</a>
                                        @endif
                                    </span>
                                @endauth
                            </div>
                        </div>

                        <button type="submit" class="submit-booking-btn">
                            <i class="fas fa-calendar-check"></i> Confirm & Book Session
                        </button>

                        <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin-top: 1rem;">
                            <i class="fas fa-lock" style="margin-right: 0.2rem;"></i> Payment is held in secure Escrow until lesson completion.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dedicated Footer -->
    @include('layouts.footer')

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Theme Toggle Script
        document.addEventListener('DOMContentLoaded', function () {
            const themeBtn = document.getElementById('themeToggleBtn');
            const iconDark = document.getElementById('themeIconDark');
            const iconLight = document.getElementById('themeIconLight');

            function updateThemeIcon() {
                if (document.documentElement.getAttribute('data-theme') === 'dark') {
                    iconDark.style.display = 'none';
                    iconLight.style.display = 'block';
                } else {
                    iconDark.style.display = 'block';
                    iconLight.style.display = 'none';
                }
            }

            if (themeBtn) {
                updateThemeIcon();
                themeBtn.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    try {
                        localStorage.setItem('theme', newTheme);
                    } catch (e) { }
                    updateThemeIcon();
                });
            }
        });

        // Price calculation & slot fetching scripts
        function onBookingDateOrDurationChange() {
            updateCalculatedPrice();
            fetchAvailableSlots();
        }

        function updateCalculatedPrice() {
            const subjectSelect = document.getElementById('subject_id');
            const durationSelect = document.getElementById('duration_hours');
            const priceDisplay = document.getElementById('total_price_display');
            
            if (subjectSelect.selectedIndex > 0) {
                const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
                const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
                const hours = parseFloat(durationSelect.value) || 1;
                
                let total = rate * hours;
                priceDisplay.textContent = total.toFixed(2) + ' Credits';
            } else {
                priceDisplay.textContent = '0.00 Credits';
            }
        }

        function fetchAvailableSlots() {
            const dateInput = document.getElementById('booking_date');
            const durationSelect = document.getElementById('duration_hours');
            const slotsContainer = document.getElementById('slots_container');
            const startTimeInput = document.getElementById('selected_start_time');

            startTimeInput.value = '';

            if (!dateInput.value) {
                slotsContainer.innerHTML = '<div style="font-size: 0.8rem; color: var(--text-muted); grid-column: 1 / -1;">Select a date above to view available time slots.</div>';
                return;
            }

            slotsContainer.innerHTML = '<div style="font-size: 0.8rem; color: var(--text-muted); grid-column: 1 / -1;"><i class="fas fa-spinner fa-spin"></i> Loading available time slots...</div>';

            const tutorId = "{{ $tutor->id }}";
            const url = `/tutors/schedule/api/slots/${tutorId}?date=${dateInput.value}&duration_hours=${durationSelect.value}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!data.is_available || !data.slots || data.slots.length === 0) {
                        const reason = data.reason || 'No available time slots on this date.';
                        slotsContainer.innerHTML = `<div style="font-size: 0.8rem; color: #ef4444; grid-column: 1 / -1;"><i class="fas fa-exclamation-circle"></i> ${reason}</div>`;
                        return;
                    }

                    slotsContainer.innerHTML = '';
                    data.slots.forEach(slot => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'slot-btn' + (slot.available ? '' : ' unavailable');
                        btn.disabled = !slot.available;
                        btn.innerHTML = `<span>${slot.label}</span>`;

                        if (slot.available) {
                            btn.onclick = function() {
                                document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                                btn.classList.add('selected');
                                startTimeInput.value = slot.time;
                            };
                        }

                        slotsContainer.appendChild(btn);
                    });
                })
                .catch(err => {
                    console.error('Failed to load slots:', err);
                    slotsContainer.innerHTML = '<div style="font-size: 0.8rem; color: #ef4444; grid-column: 1 / -1;">Unable to load time slots. Please try again.</div>';
                });
        }
    </script>
</body>
</html>
