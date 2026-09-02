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
            --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
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
            -webkit-backdrop-filter: blur(12px);
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
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.875rem;
            text-decoration: none;
            padding: 0.5rem 1.1rem;
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
            grid-template-columns: 1fr 380px;
            gap: 2rem;
            align-items: start;
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
            padding: 1.75rem 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        /* Hero Banner Header */
        .hero-banner {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .tutor-avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
            background: var(--bg-surface);
        }

        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), #1e3a8a);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .tutor-name {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .tutor-tagline {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.975rem;
            margin-top: 0.2rem;
        }

        .badges-row {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-top: 0.65rem;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.775rem;
            font-weight: 700;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            letter-spacing: 0.02em;
        }

        .badge-rating {
            background: rgba(38, 119, 184, 0.08);
            color: var(--primary-blue);
            border: 1px solid rgba(38, 119, 184, 0.2);
        }

        .badge-verified {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-rate {
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        /* Horizontal Split Grid for Video + Description */
        .hero-split-grid {
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            gap: 1.75rem;
            align-items: start;
            margin-top: 1.5rem;
        }

        .hero-split-grid.no-video {
            grid-template-columns: 1fr;
        }

        .split-video-col {
            display: flex;
            flex-direction: column;
        }

        .split-bio-col {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid var(--border-color);
            background: #0f172a;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Section Headings with Professional Dark Icons */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            letter-spacing: -0.01em;
        }

        .section-title i {
            color: var(--text-main);
            font-size: 0.95rem;
            opacity: 0.85;
        }

        /* Compact About Preview */
        .bio-preview-text {
            color: var(--text-muted);
            font-size: 0.925rem;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .read-more-trigger-btn {
            background: none;
            border: none;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            padding: 0.4rem 0;
            margin-top: 0.4rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: gap 0.2s ease;
        }

        .read-more-trigger-btn:hover {
            color: var(--primary-blue-hover);
            gap: 0.6rem;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .hero-split-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        /* Subjects Grid */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 0.85rem;
            margin-top: 1rem;
        }

        .subject-item-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            padding: 1rem 1.15rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .subject-item-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary-blue);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .subject-name {
            font-weight: 700;
            font-size: 0.925rem;
            color: var(--text-main);
        }

        .subject-rate {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--primary-blue);
            background: rgba(38, 119, 184, 0.08);
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
        }

        /* Sticky Booking Widget */
        .booking-card {
            background: var(--bg-card);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 90px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.825rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.925rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        /* Enhanced Inline Calendar & Date Strip (italki style) */
        .inline-datepicker-wrap {
            background: #f8fafc; /* Soothing glare-free off-white */
            border: 1.5px solid var(--border-color);
            border-radius: 14px;
            padding: 0.9rem;
            margin-top: 0.35rem;
        }

        [data-theme="dark"] .inline-datepicker-wrap {
            background: #0f172a;
        }

        .cal-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .cal-month-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .cal-nav-group {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .cal-nav-btn {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .cal-nav-btn:hover:not(:disabled) {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            background: rgba(38, 119, 184, 0.08);
        }

        .cal-nav-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .cal-view-toggle {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary-blue);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .cal-view-toggle:hover {
            background: rgba(38, 119, 184, 0.08);
        }

        /* 7-Day Horizontal Strip View */
        .week-strip-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.35rem;
        }

        .day-chip-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 0.2rem 0.45rem 0.2rem;
            border-radius: 10px;
            border: 1.5px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
            outline: none;
        }

        .day-chip-btn:hover:not(:disabled) {
            border-color: rgba(38, 119, 184, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .day-chip-btn.is-selected {
            background: var(--primary-blue) !important;
            color: #ffffff !important;
            border-color: var(--primary-blue) !important;
            box-shadow: 0 4px 12px rgba(38, 119, 184, 0.35);
            transform: translateY(-1px);
        }

        .day-chip-btn.is-selected .day-chip-name {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .day-chip-btn.is-selected .day-chip-num {
            color: #ffffff !important;
        }

        .day-chip-btn.is-selected .avail-dot {
            background: #ffffff !important;
        }

        .day-chip-btn:disabled, .day-chip-btn.is-disabled {
            opacity: 0.35;
            cursor: not-allowed;
            background: transparent;
            border-color: transparent;
        }

        .day-chip-name {
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            line-height: 1;
            margin-bottom: 0.3rem;
        }

        .day-chip-num {
            font-size: 0.95rem;
            font-weight: 800;
            line-height: 1;
            color: var(--text-main);
        }

        .day-chip-indicator {
            height: 4px;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avail-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--primary-blue);
        }

        .today-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--accent-red);
        }

        /* Month Grid View */
        .month-cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.3rem;
            margin-top: 0.4rem;
        }

        .month-cal-header-day {
            text-align: center;
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            padding: 0.2rem 0;
        }

        .month-cal-cell {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
        }

        .month-cal-cell:hover:not(:disabled) {
            border-color: var(--primary-blue);
            background: rgba(38, 119, 184, 0.08);
        }

        .month-cal-cell.is-selected {
            background: var(--primary-blue) !important;
            color: #ffffff !important;
            font-weight: 800;
            border-color: var(--primary-blue) !important;
        }

        .month-cal-cell:disabled, .month-cal-cell.is-disabled {
            opacity: 0.3;
            cursor: not-allowed;
            background: transparent;
            border-color: transparent;
        }

        .month-cal-cell.is-today {
            border-color: rgba(225, 30, 45, 0.4);
        }

        .price-summary-box {
            background: rgba(38, 119, 184, 0.06);
            border: 1px solid rgba(38, 119, 184, 0.15);
            border-radius: 12px;
            padding: 1.15rem;
            margin: 1.5rem 0;
        }

        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .price-total {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary-blue);
        }

        .submit-booking-btn {
            width: 100%;
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 0.95rem;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(38, 119, 184, 0.25);
        }

        .submit-booking-btn:hover {
            background: var(--primary-blue-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(38, 119, 184, 0.35);
        }

        .slots-grid-box {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
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

        /* Modern Full Background Modal */
        .bio-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            animation: modalFadeIn 0.2s ease-out;
        }

        .bio-modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            width: 100%;
            max-width: 580px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .bio-modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: var(--bg-surface);
        }

        .bio-modal-close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .bio-modal-close-btn:hover {
            background: var(--border-color);
            color: var(--text-main);
        }

        .bio-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .bio-modal-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bio-modal-section-title i {
            color: var(--text-main);
            font-size: 0.9rem;
        }

        .bio-modal-text {
            color: var(--text-muted);
            font-size: 0.925rem;
            line-height: 1.7;
            white-space: pre-line;
        }

        .bio-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            background: var(--bg-surface);
        }

        .bio-modal-done-btn {
            background: var(--text-main);
            color: var(--bg-surface);
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: opacity 0.15s;
        }

        .bio-modal-done-btn:hover {
            opacity: 0.9;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideUp {
            from { transform: translateY(16px) scale(0.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
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
                <!-- Top Hero & Profile Card with Integrated Video -->
                <div class="profile-card">
                    <!-- 1. Header with Avatar & Details -->
                    <div class="hero-banner">
                        @if($tutor->tutorProfile && $tutor->tutorProfile->headshot_path)
                            <img src="{{ route('admin.tutors.document', ['id' => $tutor->tutorProfile->id, 'type' => 'headshot']) }}" alt="{{ $tutor->name }}" class="tutor-avatar-circle">
                        @elseif($tutor->avatar)
                            <img src="{{ asset('storage/' . $tutor->avatar) }}" alt="{{ $tutor->name }}" class="tutor-avatar-circle">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($tutor->name, 0, 1)) }}
                            </div>
                        @endif

                        <div style="flex: 1; min-width: 0;">
                            <h1 class="tutor-name">{{ $tutor->name }}</h1>
                            <div class="tutor-tagline">{{ $tutor->tutorProfile->tagline ?? 'Certified Professional Educator' }}</div>
                            
                            <div class="badges-row">
                                <!-- Single Star Rating -->
                                <span class="badge-pill badge-rating">
                                    <i class="fa-solid fa-star" style="color: var(--primary-blue);"></i> 5.0 ({{ 12 + ($tutor->id * 3) % 40 }})
                                </span>

                                @if($tutor->tutorProfile && $tutor->tutorProfile->is_approved)
                                    <span class="badge-pill badge-verified">
                                        <i class="fa-solid fa-shield-check"></i> Verified Tutor
                                    </span>
                                @endif

                                <span class="badge-pill badge-rate">
                                    <i class="fa-solid fa-tag"></i> {{ $tutor->tutorProfile->rate_range }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Horizontal Split Section: Video + About Me Side-by-Side -->
                    @php
                        $hasVideo = $tutor->tutorProfile && !empty($tutor->tutorProfile->intro_video_url);
                        $bioText = $tutor->tutorProfile->bio ?? 'Certified professional educator dedicated to delivering interactive, high-impact learning.';
                        $hasLongContent = strlen($bioText) > 160 || !empty($tutor->tutorProfile->qualifications);
                    @endphp

                    <div class="hero-split-grid {{ $hasVideo ? '' : 'no-video' }}">
                        @if($hasVideo)
                            <div class="split-video-col">
                                <h2 class="section-title">
                                    <i class="fa-solid fa-play"></i> Introduction Video
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

                        <div class="split-bio-col">
                            <h2 class="section-title">
                                <i class="fa-regular fa-user"></i> About Me
                            </h2>
                            <p class="bio-preview-text">
                                {{ $bioText }}
                            </p>

                            @if($hasLongContent)
                                <button type="button" class="read-more-trigger-btn" onclick="openBioModal()">
                                    Read full background <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Expertise Subjects & Rates Card -->
                <div class="profile-card">
                    <h2 class="section-title">
                        <i class="fa-solid fa-layer-group"></i> Expertise Domains & Rates
                    </h2>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Select any of the subjects below when scheduling your private 1-on-1 lesson.</p>

                    <div class="subjects-grid">
                        @forelse($tutor->tutorSubjects as $ts)
                            <div class="subject-item-card">
                                <div>
                                    <div class="subject-name">{{ $ts->subject->name ?? 'Subject' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">1-on-1 Interactive Session</div>
                                </div>
                                <div class="subject-rate">GHS {{ number_format($ts->hourly_rate, 2) }}/hr</div>
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; color: var(--text-muted); font-style: italic;">No specific subject rates configured.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Published Public Courses (If any) -->
                @if(isset($courses) && $courses->count() > 0)
                    <div class="profile-card">
                        <h2 class="section-title">
                            <i class="fa-solid fa-book-bookmark"></i> Published Courses
                        </h2>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">Explore structured on-demand video courses published by this tutor.</p>
                        
                        <div class="subjects-grid">
                            @foreach($courses as $course)
                                <div class="subject-item-card" style="flex-direction: column; align-items: flex-start; gap: 0.5rem;">
                                    <div class="subject-name">{{ $course->title }}</div>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);">{{ $course->videos->count() }} Lessons</div>
                                    <div class="subject-rate" style="margin-top: 0.25rem;">GHS {{ number_format($course->price ?? 0, 2) }}</div>
                                </div>
                            @endforeach
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
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <label class="form-label" style="margin-bottom: 0;">Select Date</label>
                                <button type="button" class="cal-view-toggle" id="cal_toggle_view_btn" onclick="toggleCalendarView()">
                                    <i class="fa-regular fa-calendar-days"></i> <span id="toggle_view_text">Month View</span>
                                </button>
                            </div>
                            <input type="hidden" name="booking_date" id="booking_date" value="" required>

                            <div class="inline-datepicker-wrap">
                                <!-- Calendar Nav Header -->
                                <div class="cal-header-bar">
                                    <div class="cal-month-title" id="cal_month_title">
                                        <i class="fa-regular fa-calendar" style="color: var(--primary-blue);"></i>
                                        <span id="cal_month_text">September 2026</span>
                                    </div>
                                    <div class="cal-nav-group">
                                        <button type="button" class="cal-nav-btn" id="cal_prev_btn" onclick="navigateCalendar(-1)" title="Previous">
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </button>
                                        <button type="button" class="cal-nav-btn" id="cal_next_btn" onclick="navigateCalendar(1)" title="Next">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- 7-Day Horizontal Strip (Default like italki) -->
                                <div id="week_strip_view" class="week-strip-grid">
                                    <!-- Populated via JavaScript -->
                                </div>

                                <!-- Month Grid View (Toggleable) -->
                                <div id="month_grid_view" style="display: none;">
                                    <div class="month-cal-grid" id="month_grid_cells">
                                        <!-- Populated via JavaScript -->
                                    </div>
                                </div>

                                <!-- Tutor Availability Legend -->
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.65rem; padding-top: 0.5rem; border-top: 1px dashed var(--border-color); font-size: 0.72rem; color: var(--text-muted);">
                                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                                        <span class="avail-dot" style="display: inline-block;"></span> Available
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                                        <span class="today-dot" style="display: inline-block;"></span> Today
                                    </div>
                                </div>
                            </div>
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
                                <span style="font-size: 0.875rem; font-weight: 600; color: var(--text-muted);">Total Credits Required:</span>
                                <span class="price-total" id="total_price_display">0.00</span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 0.65rem; margin-top: 0.65rem;">
                                <span>1 Credit = GHS 1.00</span>
                                @auth
                                    <span style="display: block; margin-top: 0.25rem;">
                                        Your Balance: <strong style="color: {{ auth()->user()->credit_balance > 0 ? '#10b981' : '#ef4444' }};">{{ number_format(auth()->user()->credit_balance, 2) }} Credits</strong>
                                        @if(auth()->user()->credit_balance < 10)
                                            <a href="{{ route('wallet.index') }}" style="display:inline-block; margin-left:0.5rem; font-size:0.78rem; color:var(--primary-blue); font-weight:600; text-decoration:underline;">+ Top Up</a>
                                        @endif
                                    </span>
                                @endauth
                            </div>
                        </div>

                        <button type="submit" class="submit-booking-btn">
                            <i class="fa-solid fa-calendar-check"></i> Confirm & Book Session
                        </button>

                        <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin-top: 1rem;">
                            <i class="fa-solid fa-shield-halved" style="margin-right: 0.2rem;"></i> Payment is held in secure Escrow until lesson completion.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Background & Bio Modal -->
    <div id="bioModal" class="bio-modal-backdrop" style="display: none;" onclick="closeBioModal(event)">
        <div class="bio-modal-card" onclick="event.stopPropagation()">
            <div class="bio-modal-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ $tutor->name }}</h3>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $tutor->tutorProfile->tagline ?? 'Verified Educator' }}</span>
                    </div>
                </div>
                <button type="button" class="bio-modal-close-btn" onclick="hideBioModal()" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="bio-modal-body">
                <!-- About Me -->
                <div>
                    <h4 class="bio-modal-section-title">
                        <i class="fa-regular fa-user"></i> Full Biography
                    </h4>
                    <p class="bio-modal-text">
                        {{ $tutor->tutorProfile->bio ?? 'No biography text provided.' }}
                    </p>
                </div>

                <!-- Qualifications -->
                @if($tutor->tutorProfile && $tutor->tutorProfile->qualifications)
                    <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                        <h4 class="bio-modal-section-title">
                            <i class="fa-solid fa-award"></i> Qualifications & Academic Background
                        </h4>
                        <p class="bio-modal-text">
                            {{ $tutor->tutorProfile->qualifications }}
                        </p>
                    </div>
                @endif

                <!-- Subjects -->
                @if($tutor->tutorSubjects && $tutor->tutorSubjects->count() > 0)
                    <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                        <h4 class="bio-modal-section-title">
                            <i class="fa-solid fa-layer-group"></i> Teaching Domains
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                            @foreach($tutor->tutorSubjects as $ts)
                                <span style="font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.75rem; border-radius: 6px; background: rgba(38, 119, 184, 0.08); color: var(--primary-blue);">
                                    {{ $ts->subject->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="bio-modal-footer">
                <button type="button" class="bio-modal-done-btn" onclick="hideBioModal()">Close</button>
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

        // Bio Modal Handlers
        function openBioModal() {
            const modal = document.getElementById('bioModal');
            if (modal) modal.style.display = 'flex';
        }

        function hideBioModal() {
            const modal = document.getElementById('bioModal');
            if (modal) modal.style.display = 'none';
        }

        function closeBioModal(e) {
            if (e.target && e.target.id === 'bioModal') {
                hideBioModal();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideBioModal();
        });

        // Inline Datepicker & Availability Schedule Engine (italki style)
        const tutorId = "{{ $tutor->id }}";
        const availableDaysOfWeek = {!! json_encode($availableDays ?? [0, 1, 2, 3, 4, 5, 6]) !!};
        const blockedDatesList = {!! json_encode($blockedDates ?? []) !!};

        let selectedDateStr = '';
        let weekOffset = 0; // 0 = current week (today onwards)
        let monthViewDate = new Date();
        let isMonthViewActive = false;

        function padZero(num) {
            return num < 10 ? '0' + num : '' + num;
        }

        function toIsoDateString(d) {
            const year = d.getFullYear();
            const month = padZero(d.getMonth() + 1);
            const day = padZero(d.getDate());
            return `${year}-${month}-${day}`;
        }

        function isDayAvailable(dateObj) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const target = new Date(dateObj);
            target.setHours(0, 0, 0, 0);

            // Past dates are not available
            if (target < today) return false;

            // Maximum 30 days ahead
            const maxDate = new Date(today);
            maxDate.setDate(maxDate.getDate() + 30);
            if (target > maxDate) return false;

            const iso = toIsoDateString(target);
            if (blockedDatesList.includes(iso)) return false;

            const dayOfWeek = target.getDay(); // 0 = Sun, 1 = Mon, ..., 6 = Sat
            return availableDaysOfWeek.includes(dayOfWeek);
        }

        function isTodayDate(dateObj) {
            const today = new Date();
            return dateObj.getFullYear() === today.getFullYear() &&
                   dateObj.getMonth() === today.getMonth() &&
                   dateObj.getDate() === today.getDate();
        }

        function renderCalendar() {
            const monthText = document.getElementById('cal_month_text');
            const prevBtn = document.getElementById('cal_prev_btn');
            const nextBtn = document.getElementById('cal_next_btn');

            if (isMonthViewActive) {
                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                monthText.textContent = `${monthNames[monthViewDate.getMonth()]} ${monthViewDate.getFullYear()}`;
                
                const today = new Date();
                prevBtn.disabled = (monthViewDate.getFullYear() === today.getFullYear() && monthViewDate.getMonth() <= today.getMonth());
                
                const maxMonth = new Date(today);
                maxMonth.setDate(maxMonth.getDate() + 30);
                nextBtn.disabled = (monthViewDate.getFullYear() > maxMonth.getFullYear() || 
                                   (monthViewDate.getFullYear() === maxMonth.getFullYear() && monthViewDate.getMonth() >= maxMonth.getMonth()));

                renderMonthGrid();
            } else {
                // Week Strip View
                const startDate = new Date();
                startDate.setDate(startDate.getDate() + (weekOffset * 7));
                
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 6);

                const monthNamesShort = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                if (startDate.getMonth() === endDate.getMonth()) {
                    monthText.textContent = `${monthNamesShort[startDate.getMonth()]} ${startDate.getFullYear()}`;
                } else {
                    monthText.textContent = `${monthNamesShort[startDate.getMonth()]} - ${monthNamesShort[endDate.getMonth()]} ${endDate.getFullYear()}`;
                }

                prevBtn.disabled = (weekOffset <= 0);
                nextBtn.disabled = (weekOffset >= 3); // max 4 weeks / 28-30 days

                renderWeekStrip();
            }
        }

        function renderWeekStrip() {
            const container = document.getElementById('week_strip_view');
            container.innerHTML = '';

            const dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for (let i = 0; i < 7; i++) {
                const dayDate = new Date();
                dayDate.setDate(dayDate.getDate() + (weekOffset * 7) + i);
                dayDate.setHours(0, 0, 0, 0);

                const iso = toIsoDateString(dayDate);
                const isAvail = isDayAvailable(dayDate);
                const isToday = isTodayDate(dayDate);
                const isSelected = (iso === selectedDateStr);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `day-chip-btn ${isSelected ? 'is-selected' : ''} ${!isAvail ? 'is-disabled' : ''}`;
                btn.disabled = !isAvail;
                btn.setAttribute('aria-label', `${dayNamesShort[dayDate.getDay()]}, ${dayDate.toLocaleDateString()}`);

                let indicatorHtml = '<div class="day-chip-indicator">';
                if (isToday) {
                    indicatorHtml += '<span class="today-dot" title="Today"></span>';
                } else if (isAvail) {
                    indicatorHtml += '<span class="avail-dot" title="Available"></span>';
                }
                indicatorHtml += '</div>';

                btn.innerHTML = `
                    <span class="day-chip-name">${dayNamesShort[dayDate.getDay()]}</span>
                    <span class="day-chip-num">${dayDate.getDate()}</span>
                    ${indicatorHtml}
                `;

                if (isAvail) {
                    btn.onclick = () => selectDate(iso);
                }

                container.appendChild(btn);
            }
        }

        function renderMonthGrid() {
            const container = document.getElementById('month_grid_cells');
            container.innerHTML = '';

            const dayHeaders = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
            dayHeaders.forEach(dh => {
                const headerEl = document.createElement('div');
                headerEl.className = 'month-cal-header-day';
                headerEl.textContent = dh;
                container.appendChild(headerEl);
            });

            const year = monthViewDate.getFullYear();
            const month = monthViewDate.getMonth();
            const firstDayIndex = new Date(year, month, 1).getDay();
            const totalDaysInMonth = new Date(year, month + 1, 0).getDate();

            // Leading empty padding cells
            for (let p = 0; p < firstDayIndex; p++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'month-cal-cell is-disabled';
                emptyCell.style.visibility = 'hidden';
                container.appendChild(emptyCell);
            }

            for (let d = 1; d <= totalDaysInMonth; d++) {
                const dayDate = new Date(year, month, d);
                const iso = toIsoDateString(dayDate);
                const isAvail = isDayAvailable(dayDate);
                const isToday = isTodayDate(dayDate);
                const isSelected = (iso === selectedDateStr);

                const cell = document.createElement('button');
                cell.type = 'button';
                cell.className = `month-cal-cell ${isSelected ? 'is-selected' : ''} ${!isAvail ? 'is-disabled' : ''} ${isToday ? 'is-today' : ''}`;
                cell.disabled = !isAvail;
                cell.innerHTML = `<span>${d}</span>`;

                if (isAvail && !isSelected) {
                    const dot = document.createElement('span');
                    dot.className = isToday ? 'today-dot' : 'avail-dot';
                    dot.style.marginTop = '2px';
                    cell.appendChild(dot);
                }

                if (isAvail) {
                    cell.onclick = () => selectDate(iso);
                }

                container.appendChild(cell);
            }
        }

        function selectDate(isoDate) {
            selectedDateStr = isoDate;
            const hiddenDateInput = document.getElementById('booking_date');
            if (hiddenDateInput) {
                hiddenDateInput.value = isoDate;
            }

            renderCalendar();
            fetchAvailableSlots();
        }

        function navigateCalendar(direction) {
            if (isMonthViewActive) {
                monthViewDate.setMonth(monthViewDate.getMonth() + direction);
            } else {
                weekOffset = Math.max(0, Math.min(3, weekOffset + direction));
            }
            renderCalendar();
        }

        function toggleCalendarView() {
            isMonthViewActive = !isMonthViewActive;
            const weekView = document.getElementById('week_strip_view');
            const monthView = document.getElementById('month_grid_view');
            const toggleText = document.getElementById('toggle_view_text');

            if (isMonthViewActive) {
                weekView.style.display = 'none';
                monthView.style.display = 'block';
                toggleText.textContent = 'Week View';
            } else {
                weekView.style.display = 'grid';
                monthView.style.display = 'none';
                toggleText.textContent = 'Month View';
            }

            renderCalendar();
        }

        // Price calculation & slot fetching scripts
        function onBookingDateOrDurationChange() {
            updateCalculatedPrice();
            fetchAvailableSlots();
        }

        function updateCalculatedPrice() {
            const subjectSelect = document.getElementById('subject_id');
            const durationSelect = document.getElementById('duration_hours');
            const priceDisplay = document.getElementById('total_price_display');
            
            if (subjectSelect && subjectSelect.selectedIndex > 0) {
                const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
                const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
                const hours = parseFloat(durationSelect.value) || 1;
                
                let total = rate * hours;
                priceDisplay.textContent = total.toFixed(2) + ' Credits';
            } else if (priceDisplay) {
                priceDisplay.textContent = '0.00 Credits';
            }
        }

        function fetchAvailableSlots() {
            const dateValue = selectedDateStr || document.getElementById('booking_date').value;
            const durationSelect = document.getElementById('duration_hours');
            const slotsContainer = document.getElementById('slots_container');
            const startTimeInput = document.getElementById('selected_start_time');

            if (startTimeInput) startTimeInput.value = '';

            if (!dateValue) {
                if (slotsContainer) {
                    slotsContainer.innerHTML = '<div style="font-size: 0.8rem; color: var(--text-muted); grid-column: 1 / -1;">Select a date above to view available time slots.</div>';
                }
                return;
            }

            if (slotsContainer) {
                slotsContainer.innerHTML = '<div style="font-size: 0.8rem; color: var(--text-muted); grid-column: 1 / -1;"><i class="fa-solid fa-spinner fa-spin" style="color: var(--primary-blue);"></i> Loading available time slots...</div>';
            }

            const url = `/tutors/schedule/api/slots/${tutorId}?date=${dateValue}&duration_hours=${durationSelect.value}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!data.is_available || !data.slots || data.slots.length === 0) {
                        const reason = data.reason || 'No available time slots on this date.';
                        slotsContainer.innerHTML = `<div style="font-size: 0.8rem; color: #ef4444; grid-column: 1 / -1;"><i class="fa-solid fa-circle-exclamation"></i> ${reason}</div>`;
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
                                if (startTimeInput) startTimeInput.value = slot.time;
                            };
                        }

                        slotsContainer.appendChild(btn);
                    });
                })
                .catch(err => {
                    console.error('Failed to load slots:', err);
                    if (slotsContainer) {
                        slotsContainer.innerHTML = '<div style="font-size: 0.8rem; color: #ef4444; grid-column: 1 / -1;">Unable to load time slots. Please try again.</div>';
                    }
                });
        }

        // Initialize inline datepicker on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Find first available date starting from today
            const checkDate = new Date();
            let firstFoundIso = '';
            for (let i = 0; i < 30; i++) {
                if (isDayAvailable(checkDate)) {
                    firstFoundIso = toIsoDateString(checkDate);
                    break;
                }
                checkDate.setDate(checkDate.getDate() + 1);
            }

            if (firstFoundIso) {
                selectedDateStr = firstFoundIso;
                const hiddenDateInput = document.getElementById('booking_date');
                if (hiddenDateInput) hiddenDateInput.value = firstFoundIso;
            } else {
                const today = new Date();
                selectedDateStr = toIsoDateString(today);
            }

            renderCalendar();
            updateCalculatedPrice();
            if (selectedDateStr) {
                fetchAvailableSlots();
            }
        });
    </script>
</body>
</html>

