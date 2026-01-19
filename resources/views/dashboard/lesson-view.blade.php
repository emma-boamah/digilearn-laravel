@extends('layouts.dashboard-components')

@section('title', (isset($course) ? $course->title : ($lesson['title'] ?? 'Lesson')) . ' - ' . config('app.name', 'ShoutOutGh'))

@section('head')
    <!-- Quill.js Rich Text Editor (Using multiple CDN fallbacks) -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Fallback if primary CDN fails
        if (typeof Quill === 'undefined') {
            console.log('Loading Quill fallback from Cloudflare');
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js';
            script.onload = () => console.log('Quill fallback loaded successfully');
            script.onerror = () => console.error('All Quill CDNs failed');
            document.head.appendChild(script);
        }
    </script>

    <!-- Additional Libraries for Enhanced Functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Vimeo Player API for hover-to-play functionality -->
    <script src="https://player.vimeo.com/api/player.js"></script>

    <!-- YouTube IFrame Player API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <!-- Mux Player for Mux video playback -->
    <script src="https://unpkg.com/@mux/mux-player"></script>

    <!-- Video Facade Manager for optimized video loading -->
    @vite('resources/js/video-facade.js')

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Initialize video facade with auto-play enabled for YouTube-like behavior
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof VideoFacadeManager !== 'undefined') {
                window.videoFacadeManager = new VideoFacadeManager({ autoPlay: true });
            }
        });
    </script>
@endsection

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --white: #ffffff;
            --gray-25: #fcfcfd;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        /* Enhanced sticky video styles with scroll-triggered transitions */
        .sticky-video-section {
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Search/Filter Bar */
        /* Updated filter bar to span full width with glassmorphism and proper positioning */
        .filter-bar {
            position: fixed !important;
            top: 60px !important; /* Directly below the header */
            left: 0; /* Start from left edge for full width */
            width: 100vw; /* Full viewport width */
            padding-left: calc(var(--sidebar-width-expanded) + 0.75rem); /* Account for sidebar */
            padding-right: 0.75rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            z-index: 998 !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: rgba(255, 255, 255, 0.75); /* Transparent for glassmorphism */
            backdrop-filter: blur(10px) saturate(160%); /* Glassmorphism effect */
            -webkit-backdrop-filter: blur(10px) saturate(160%); /* Safari support */
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
            overflow-x: hidden;
            overflow-y: hidden;
            max-width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none; /* Allow clicks to pass through glassmorphism layer */
        }

        .filter-bar * {
            pointer-events: auto; /* Restore pointer events for interactive elements */
        }

        .youtube-sidebar.collapsed ~ .filter-bar {
            padding-left: calc(var(--sidebar-width-collapsed) + 0.75rem);
        }

        /* Mobile: Reduce padding and gap on filter bar */
        @media (max-width: 768px) {
            .filter-bar {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                gap: 0.5rem;
                flex-wrap: nowrap;
            }

            .youtube-sidebar.collapsed ~ .filter-bar {
                padding-left: 0.5rem;
            }
        }

        .back-button {
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .back-button:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        /* Mobile: Compact back button */
        @media (max-width: 768px) {
            .back-button {
                padding: 0.5rem;
            }

            .back-button svg {
                width: 18px;
                height: 18px;
            }
        }

        /* Made search box fully responsive with flexible sizing */
        .search-box {
            position: relative;
            flex: 1;
            max-width: 500px;
            min-width: 0; /* changed from 300px to allow fully flexibility */
            width: 100%;
        }

        /* Mobile: Search toggle button */
        .search-toggle-btn {
            display: none;
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .search-toggle-btn:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        /* Mobile: Compact search box */
        @media (max-width: 768px) {
            .filter-bar.search-active {
                flex-wrap: wrap;
            }

            /* Show search toggle button on mobile */
            .search-toggle-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Hide search box by default on mobile */
            .search-box {
                display: none;
                max-width: none;
                min-width: 0;
                width: 100%;
                order: 10;
                flex: 1 1 100%;
            }

            /* Show search box when search is active */
            .filter-bar.search-active .search-box {
                display: flex;
            }

            /* Hide level indicator and quiz when search active */
            .filter-bar.search-active .level-container,
            .filter-bar.search-active .quiz-container {
                display: none;
            }

            .search-input {
                padding: 0.75rem 0.75rem 0.75rem 2.5rem;
                font-size: 0.75rem;
            }

            .search-icon {
                left: 0.75rem;
            }
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            z-index: 1;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 0.875rem;
            background-color: var(--gray-50);
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-red);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(225, 30, 45, 0.1);
        }

        .filter-dropdown {
            position: relative;
        }

        .dropdown-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            min-width: 120px;
            transition: all 0.2s ease;
        }

        .dropdown-button:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        .dropdown-chevron {
            width: 16px;
            height: 16px;
            color: var(--gray-500);
        }

        .filter-button {
            padding: 0.875rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        /* Mobile: Compact quiz button */
        @media (max-width: 768px) {
            .filter-button {
                padding: 0.75rem 1rem;
                font-size: 0.75rem;
                flex-shrink: 0;
            }
        }

        .filter-button:hover {
            border-color: var(--primary-red);
            color: var(--primary-red);
        }

        .filter-button.active {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        /* Enhanced Main Layout - YouTube-like */
        .lesson-page {
            display: grid;
            grid-template-columns: 1fr minmax(300px, 400px);
            gap: 1rem;
            padding: 1rem 1.5rem;
            max-width: 100%;
            margin: 0;
            margin-top: 130px;
            overflow-x: hidden;
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lesson-main {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-width: 0;
        }

        .lesson-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            min-width: 0;
            max-width: 400px;
            position: relative;
            z-index: 0;
        }

        /* Prevent unwanted dark backgrounds on hover */
        .lesson-page-content:hover,
        .lesson-page:hover,
        .left-content:hover,
        .lesson-sidebar:hover {
            background-color: transparent !important;
        }

        /* Enhanced Left Content */
        .left-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-width: 0; /* Prevents Overflow */
        }

        /* Enhanced Video Container with smooth transitions */
        .video-container {
            position: relative;
            aspect-ratio: 16/9;
            background-color: #000;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            max-width: 100%;
        }

        .video-container:hover {
            box-shadow: var(--shadow-xl);
        }

        .video-player {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            padding-bottom: 0 !important;
        }

        /* Enhanced Lesson Info Card with smooth transitions */
        .lesson-info-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lesson-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            line-height: 1.3;
            letter-spacing: -0.025em;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            word-wrap: break-word;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lesson-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lesson-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .lesson-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-btn-primary {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-blue-hover));
            color: var(--white);
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .action-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-btn-secondary {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background-color: var(--white);
            color: var(--gray-700);
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn-secondary:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        /* Enhanced Comments Section */
        .comments-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .comments-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .comments-header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .comments-count {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .comments-dropdown {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-600);
            padding: 0.25rem;
        }

        .comments-dropdown .dropdown-icon {
            transition: transform 0.3s ease;
        }

        .comments-dropdown.open .dropdown-icon {
            transform: rotate(180deg);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .header-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .save-header-btn {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
        }

        .save-header-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .share-header-btn {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
        }

        .share-header-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .comment-input-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: flex-start;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            flex-shrink: 0;
            font-size: 0.875rem;
            box-shadow: var(--shadow-sm);
        }

        .comment-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            resize: none;
            min-height: 40px;
            font-family: inherit;
            transition: all 0.2s ease;
            background-color: var(--gray-50);
            color: var(--gray-500);
        }

        .comment-input:focus {
            outline: none;
            border-color: var(--primary-red);
            background-color: var(--white);
            color: var(--gray-900);
        }

        .comment-submit-btn {
            display: none;
        }

        /* Comments List - Updated */
        .comment {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 0;
            background-color: transparent;
            border: none;
            border-radius: 0;
        }

        .comment-content {
            flex: 1;
            min-width: 0; /* Prevents Overflow */
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }

        .comment-author {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
        }

        .comment-time {
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .comment-text {
            color: var(--gray-700);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
            word-wrap: break-word;
        }

        .comment-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .comment-action {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0.25rem 0;
            transition: all 0.2s ease;
        }

        .comment-action:hover {
            color: var(--gray-700);
        }

        .comment-like-count {
            margin-left: 0.25rem;
        }

        .comment-action.active {
            color: var(--primary-red);
            font-weight: 600;
        }

        .comment-action.active i {
            color: var(--primary-red);
        }

        /* Enhanced Right Sidebar */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            min-width: 0; /* changed from 300px to prevent overflow */
            max-width: 400px;
        }

        /* Enhanced Action Buttons - Updated to match screenshot */
        .action-buttons-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border: 2px solid var(--secondary-blue);
            color: var(--gray-600);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            min-width: 0; /* Prevents overflow */
        }

        .action-btn:hover {
            border-color: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Document availability indicator */
        .document-indicator {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 16px;
            height: 16px;
            background-color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .action-btn {
            position: relative;
        }

        /* Enhanced Notes Section - Updated to match screenshot */
        .notes-section {
            background-color: var(--gray-100);
            border-radius: 1rem;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .existing-note {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .existing-note h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .existing-note p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0;
        }

        .add-notes-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: 2px solid var(--secondary-blue);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .add-notes-btn:hover {
            background-color: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .add-notes-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Active state for notes button */
        .add-notes-btn.active {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
            color: var(--white);
        }

        .add-notes-btn.active:hover {
            background-color: var(--primary-red-hover);
            border-color: var(--primary-red-hover);
        }

        /* Transition for button state changes */
        .add-notes-btn {
            transition: all 0.3s ease;
        }

        /* Enhanced Rich Text Editor - Modern Implementation */
        .notes-editor-section {
            background-color: var(--white);
            border: 3px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative;
            z-index: 1;
        }

        .notes-editor-section.hidden {
            display: none !important;
        }

        .notes-editor-container {
            display: flex;
            flex-direction: column;
            min-height: 500px;
        }

        .notes-editor-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background-color: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        .notes-header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .notes-character-count {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .notes-character-count.warning {
            color: #f59e0b;
        }

        .notes-character-count.error {
            color: #ef4444;
        }

        .notes-title-input {
            flex: 1;
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            background-color: var(--white);
            color: var(--gray-900);
            transition: all 0.2s ease;
        }

        .notes-title-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        .notes-title-input::placeholder {
            color: var(--gray-500);
            font-weight: 500;
        }

        .notes-editor-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
            width: 100%;
        }

        .update-mode-selector {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
        }

        .update-mode-selector label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            cursor: pointer;
        }

        .update-mode-selector input[type="radio"] {
            width: 16px;
            height: 16px;
            accent-color: var(--secondary-blue);
        }

        .notes-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .notes-action-btn:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
        }

        .notes-action-btn.save {
            color: var(--gray-400);
            border-color: var(--secondary-blue);
        }

        .notes-action-btn.save:hover {
            background-color: var(--gray-200);
            color: var(--secondary-blue);
        }

        .notes-action-btn.delete {
            color: var(--gray-400);
            border-color: var(--primary-red);
        }

        .notes-action-btn.delete:hover {
            background-color: var(--gray-200);
            color: var(--primary-red);
        }

        .notes-action-btn.export {
            color: var(--gray-400);
            border-color: #10b981;
        }

        .notes-action-btn.export:hover {
            background-color:var(--gray-200);
            color: #10b981;
        }

        .notes-action-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Quill Editor Customization */
        .notes-editor-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        #notesQuillEditor {
            flex: 1;
            min-height: 400px;
            display: block !important;
            visibility: visible !important;
        }

        /* Ensure Quill elements are visible */
        .ql-toolbar {
            display: block !important;
            visibility: visible !important;
        }

        .ql-container {
            display: block !important;
            visibility: visible !important;
        }

        .ql-editor {
            display: block !important;
            visibility: visible !important;
        }

        /* Consolidated Quill styles */
        .ql-toolbar {
            border: none !important;
            border-bottom: 1px solid var(--gray-200) !important;
            background-color: var(--gray-50) !important;
            padding: 1rem 1.25rem !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Fix Quill Editor Container */
        #notesWrapper {
            background-color: var(--white);
            border: 3px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            display: none; /* Hidden by default */
        }

        #notesWrapper.active {
            display: block;
        }

        #notes-editor-container {
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }

        /* Ensure Quill toolbar doesn't overlap */
        .ql-toolbar {
            position: relative !important;
            z-index: 100 !important;
            background: var(--white) !important;
            border: 1px solid var(--gray-200) !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 0.5rem !important;
        }

        .ql-container {
            position: relative !important;
            z-index: 50 !important;
            min-height: 250px !important;
            max-height: 400px !important;
            overflow-y: auto !important;
            border: 1px solid var(--gray-200) !important;
            border-top: none !important;
            border-radius: 0 0 0.5rem 0.5rem !important;
        }

        .ql-editor {
            min-height: 250px !important;
        }

        /* Fix Save Button positioning */
        #saveNotesBtn {
            margin: 1rem auto;
            display: block;
            padding: 0.75rem 2rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #saveNotesBtn:hover {
            background-color: var(--secondary-blue-hover);
        }

        /* Ensure Related Videos Card has proper spacing */
        .related-videos-card {
            margin-top: 1.5rem !important;
            position: relative;
            z-index: 10;
        }

        .ql-container {
            border: none !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
            font-size: 0.9375rem !important;
            min-height: 400px !important;
            background-color: var(--white) !important;
            line-height: 1.6 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            min-height: 150px;
        }

        .ql-editor {
            padding: 1.5rem !important;
            min-height: 400px !important;
            line-height: 1.7 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .ql-editor.ql-blank::before {
            color: var(--gray-500) !important;
            font-style: normal !important;
            left: 1.5rem !important;
            opacity: 1 !important;
        }

        .ql-stroke {
            stroke: var(--gray-600) !important;
        }

        .ql-fill {
            fill: var(--gray-600) !important;
        }

        .ql-picker-label {
            color: var(--gray-700) !important;
        }

        .ql-toolbar button {
            border-radius: 0.375rem !important;
            margin: 0 0.125rem;
            transition: all 0.2s ease !important;
            height: auto;
        }

        .ql-toolbar button:hover {
            background-color: var(--gray-200) !important;
            color: var(--white) !important;
        }

        .ql-toolbar button.ql-active {
            background-color: var(--gray-400) !important;
            color: var(--white) !important;
        }

        .ql-toolbar .ql-picker {
            border-radius: 0.375rem !important;
        }

        .ql-toolbar .ql-picker-label {
            border-radius: 0.375rem !important;
            transition: all 0.2s ease !important;
        }

        .ql-toolbar .ql-picker-label:hover {
            background-color: var(--gray-200) !important;
            color: var(--secondary-blue) !important;
        }

        /* Enhanced Related Videos */
        .related-videos-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .related-videos-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .video-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .video-item:hover {
            background-color: var(--gray-50);
        }

        .video-item:last-child {
            margin-bottom: 0;
        }

        .video-thumbnail {
            width: 168px;
            height: 94px;
            border-radius: 0.5rem;
            object-fit: cover;
            flex-shrink: 0;
            background-color: var(--gray-200);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 0; /* Prevents overflow */
        }

        .video-info-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-wrap: break-word;
        }

        .video-info-channel {
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .video-info-meta {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none; /* Prevents interferance with other elements */
        }

        /* Only show play overlay when hovering over video-item or video-thumbnail */
        .video-item:hover .play-overlay,
        .video-thumbnail:hover .play-overlay {
            opacity: 1;
        }

        .play-icon {
            width: 28px;
            height: 28px;
            color: var(--white);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .video-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }

        .video-title {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .video-menu {
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.5rem;
            align-self: flex-start;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .video-menu:hover {
            color: var(--gray-600);
            background-color: var(--gray-100);
        }

        .hidden {
            display: none;
        }

        /* Share Modal */
        .share-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .share-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .share-modal {
            transform: scale(0.9);
            background-color: var(--white);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            z-index: 2001;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .share-modal-overlay.active .share-modal {
            transform: scale(1);
        }

        .share-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .share-modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .share-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: var(--gray-500);
            transition: all 0.2s ease;
        }

        .share-modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .share-platforms {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .share-platform {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .share-platform:hover .share-platform-icon {
            transform: scale(1.1);
        }

        .share-platform-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .share-platform-icon:hover {
            box-shadow: var(--shadow-md);
        }

        .share-platform-icon.whatsapp {
            background-color: #25D366;
        }

        .share-platform-icon.facebook {
            background-color: #1877F2;
        }

        .share-platform-icon.email {
            background-color: var(--gray-400);
        }

        .share-platform-icon.instagram {
            background: linear-gradient(45deg, #F56040, #E1306C, #C13584, #833AB4);
        }

        .share-platform-icon.twitter {
            background-color: var(--white);
            color: #1DA1F2;
            border: 2px solid var(--gray-200);
        }

        .share-platform-name {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .share-url-container {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.75rem;
            border: 2px solid var(--gray-200);
        }

        .share-url-input {
            flex: 1;
            background: none;
            border: none;
            font-size: 0.875rem;
            color: var(--gray-700);
            min-width: 0;
        }

        .share-url-input:focus {
            outline: none;
        }

        .share-copy-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .share-copy-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .share-copy-btn.copied {
            background-color: #10b981;
        }

        /* Notes Title Modal */
        .notes-title-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .notes-title-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .notes-title-modal {
            transform: scale(0.9) translateY(20px);
            background-color: var(--white);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .notes-title-modal-overlay.active .notes-title-modal {
            transform: scale(1) translateY(0);
        }

        .notes-title-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .notes-title-modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .notes-title-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: var(--gray-500);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .notes-title-modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .notes-title-modal-body {
            padding: 2rem;
        }

        .notes-title-modal-description {
            font-size: 1rem;
            color: var(--gray-700);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .notes-title-input-group {
            margin-bottom: 1.5rem;
        }

        .notes-title-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            background-color: var(--white);
            color: var(--gray-900);
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }

        .notes-title-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        .notes-title-input::placeholder {
            color: var(--gray-500);
            font-weight: 400;
        }

        .notes-title-suggestions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .notes-title-suggestion-btn {
            padding: 0.5rem 1rem;
            background-color: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notes-title-suggestion-btn:hover {
            background-color: var(--gray-200);
            border-color: var(--gray-300);
        }

        .notes-title-modal-tip {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .notes-title-modal-tip svg {
            color: #22c55e;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .notes-title-modal-footer {
            padding: 1.5rem 2rem 2rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .notes-title-modal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .notes-title-modal-btn.secondary {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }

        .notes-title-modal-btn.secondary:hover {
            background-color: var(--gray-200);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .notes-title-modal-btn.primary {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-blue-hover));
            color: var(--white);
        }

        .notes-title-modal-btn.primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Notes Education Modal */
        .notes-education-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .notes-education-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .notes-education-modal {
            transform: scale(0.9) translateY(20px);
            background-color: var(--white);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .notes-education-modal-overlay.active .notes-education-modal {
            transform: scale(1) translateY(0);
        }

        .notes-education-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .notes-education-modal-icon {
            color: var(--secondary-blue);
            background-color: rgba(38, 119, 184, 0.1);
            border-radius: 50%;
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notes-education-modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 1.5rem;
            flex: 1;
        }

        .notes-education-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: var(--gray-500);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .notes-education-modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .notes-education-modal-body {
            padding: 2rem;
        }

        .notes-education-modal-description {
            font-size: 1rem;
            color: var(--gray-700);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .notes-education-modal-options {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .notes-education-option {
            padding: 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            background-color: var(--gray-50);
            transition: all 0.2s ease;
        }

        .notes-education-option:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(38, 119, 184, 0.05);
        }

        .notes-education-option-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .notes-education-option-header input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--secondary-blue);
        }

        .notes-education-option-header strong {
            font-size: 1rem;
            color: var(--gray-900);
        }

        .notes-education-option p {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.5;
            margin: 0;
        }

        .notes-education-modal-tip {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .notes-education-modal-tip svg {
            color: #22c55e;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .notes-education-modal-tip kbd {
            background-color: var(--gray-200);
            border: 1px solid var(--gray-300);
            border-radius: 0.25rem;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
            color: var(--gray-700);
            font-family: monospace;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            margin: 0 0.125rem;
        }

        .notes-education-modal-footer {
            padding: 1.5rem 2rem 2rem;
            border-top: 1px solid var(--gray-200);
            text-align: center;
        }

        .notes-education-modal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-blue-hover));
            color: var(--white);
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .notes-education-modal-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Loading and Success States */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background-color: #10b981;
            color: var(--white);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 1rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Course Content Styles */
        .course-content-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .course-header-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .course-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            line-height: 1.3;
            letter-spacing: -0.025em;
        }

        .course-description {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .course-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .course-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .course-stats {
            display: flex;
            gap: 2rem;
            justify-content: center;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-red);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .course-tabs {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
        }

        .tab-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .tab-btn:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
        }

        .tab-btn.active {
            color: var(--primary-red);
            background-color: rgba(225, 30, 45, 0.05);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--primary-red);
        }

        .tab-content {
            display: none;
            padding: 2rem;
            min-height: 400px;
        }

        .tab-content.active {
            display: block;
        }

        .course-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            background-color: var(--white);
        }

        .course-item:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--gray-300);
        }

        .item-thumbnail {
            position: relative;
            width: 200px;
            height: 112px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-icon {
            width: 64px;
            height: 64px;
            background-color: var(--gray-100);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            flex-shrink: 0;
        }

        .video-item .item-icon,
        .document-item .item-icon,
        .quiz-item .item-icon {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .document-item .item-icon {
            background-color: #10b981;
        }

        .quiz-item .item-icon {
            background-color: #7c3aed;
        }

        .item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .item-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .item-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .item-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .item-status.approved {
            color: #10b981;
            font-weight: 500;
        }

        .item-status.pending {
            color: #f59e0b;
            font-weight: 500;
        }

        .item-status.rejected {
            color: #ef4444;
            font-weight: 500;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .action-btn-small {
            padding: 0.5rem 1rem;
            border: 2px solid var(--secondary-blue);
            background-color: var(--white);
            color: var(--secondary-blue);
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn-small:hover {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .action-btn-small.primary {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .action-btn-small.primary:hover {
            background-color: var(--secondary-blue-hover);
        }

        .empty-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            text-align: center;
            color: var(--gray-500);
        }

        .empty-tab svg {
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-tab h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-tab p {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Collapsible Sidebar */
        .collapsible-sidebar {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .collapsible-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            background-color: var(--white);
            transition: all 0.2s ease;
        }

        .collapsible-header:hover {
            background-color: var(--gray-50);
        }

        .collapsible-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .collapsible-chevron {
            width: 20px;
            height: 20px;
            color: var(--gray-500);
            transition: transform 0.3s ease;
        }

        .collapsible-header.open .collapsible-chevron {
            transform: rotate(180deg);
        }

        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .collapsible-content.open {
            max-height: 2000px;
        }

        .collapsible-body {
            padding: 0 1.5rem 1.5rem;
        }       


        /* Enhanced Responsive Design */
        @media (max-width: 1200px) {
            .lesson-page {
                grid-template-columns: 1fr 350px;
                gap: 1.5rem;
            }
        }

        @media (max-width: 1024px) {
            .lesson-page {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .lesson-sidebar {
                order: 0; /* Reset to natural order */
                max-width: 100%;
            }

            /* Stack action buttons vertically */
            .action-buttons-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .comments-card{
                display: block;
                order: 2;
            }

            .notes-editor-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .notes-editor-actions {
                margin-left: 0;
                justify-content: center;
            }
        }

        /* FIXED: Mobile responsive with NATURAL BLOCK FLOW - No overlapping! */
        @media (max-width: 768px) {
            * {
                -webkit-tap-highlight-color: transparent;
            }
            body {
                width: 100%;
                max-width: 100vw;
                overflow-x: hidden;
                margin: 0;
                padding: 0;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background-color: #f9fafb;
                font-size: 16px; /* Prevents iOS zoom on input focus */
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                /* Enable smooth scrolling */
                scroll-behavior: smooth;
                
            }

            button, a, input {
                min-height: 44px; /* Apple's recommended touch target */
                min-width: 44px;
            }
            .lesson-page {
                display: flex;
                flex-direction: column;
                padding: 0.75rem;
                margin: 0;
                margin-top: 130px;
                gap: 1.5rem;
                overflow: hidden;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                padding-top: 80px; /* Account for header height + some spacing */
            }
            
            .filter-bar {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .search-box {
                min-width: 0;
                max-width: 100%;
                flex: 1 1 auto;
            }

            .dropdown-button,
            .filter-button {
                padding: 0.75rem 1rem;
                font-size: 0.8125rem;
                min-width: auto;
            }
            
            /* FIXED: Natural sticky positioning - no overlapping */
            .sticky-video-section {
                position: relative;
                width: 100%;
                max-width: 100%;
            }

            /* FIXED: Simple compact state - video only at bottom-right */
            .sticky-video-section.compact {
                position: fixed;
                bottom: 1.5rem;
                right: 1.5rem;
                width: 300px;
                height: auto;
                z-index: 1000;
                box-shadow: var(--shadow-xl);
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .sticky-video-section.compact .video-container {
                aspect-ratio: 16/9;
                width: 100%;
                height: auto;
                border-radius: 0.75rem;
                overflow: hidden;
                transform: none;
            }

            /* FIXED: Hide lesson info completely in compact mode */
            .sticky-video-section.compact .lesson-info-card {
                display: none;
            }
            
            .lesson-title {
                font-size: 1.25rem;
                line-height: 1.4;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .lesson-info-card {
                padding: 1.25rem;
            }

            /* Adjust lesson actions */
            .lesson-actions {
                flex-direction: column !important;
                gap: 0.75rem;
            }

            .action-btn-primary, 
            .action-btn-secondary {
                width: 100%;
                justify-content: center;
                padding: 0.875rem 1.25rem;;
            }

            .action-buttons-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
                margin: 1rem 0 1.5rem;
            }

            .action-btn {
                flex-direction: column;
                padding: 0.75rem 0.5rem;
                font-size: 0.75rem;
                gap: 0.25rem;
            }

            .action-btn svg {
                width: 20px;
                height: 20px;
            }

            /* FIXED: Comments section follows naturally - no overlap! */
            .comments-card {
                display: block;
                padding: 1.25rem;
            }

            .comments-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .comment-input-container {
                gap: 0.75rem;
            }

            .comment-avatar {
                width: 36px;
                height: 36px;
                font-size: 0.8125rem;
            }

            .related-videos-card {
                padding: 1.25rem;
            }

            /* Related videos follow naturally */
            .related-videos-card {
                margin-top: 1rem;
            }

            /* Hide the right sidebar container */
            .lesson-sidebar {
                display: contents; /* Children become direct grid children */
                width: 100%;
                min-width: unset;
                max-width: 100%;
            }

            .collapsible-sidebar {
                width: 100%;
            }

            .video-thumbnail {
                width: 120px;
                height: 68px;
            }

            .video-info-title {
                font-size: 0.8125rem;
                -webkit-line-clamp: 3;
            }

            /* Video first */
            .video-container {
                border-radius: 0.5rem;
                width: 100%;
                max-width: 100%;
            }

            .share-modal {
                padding: 1.5rem;
                margin: 1rem;
                width: calc(100% - 2rem);
                max-width: 100%;
            }
            
            .share-platforms {
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
            }
            
            .share-platform-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .share-url-container {
                flex-direction: column;
                gap: 0.75rem;
                padding: 0.875rem;
            }
            
            .share-url-input {
                width: 100%;
                text-align: center;
            }
            .share-copy-btn {
                width: 100%;
                justify-content: center;
            }

            .notes-editor-section {
                border-width: 2px;
            }

            .notes-section {
                padding: 2rem 1rem;
            }

            .notes-editor-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .notes-action-btn {
                width: 100%;
                justify-content: center;
            }

            .notes-title-input {
                font-size: 0.9375rem;
            }

            .ql-container {
                min-height: 300px;
            }

            .ql-editor {
                padding: 1rem;
                min-height: 300px;
            }

            .ql-toolbar {
                padding: 0.75rem 1rem;
            }

            /* Fix Quill editor on mobile */
            #notesWrapper {
                margin: 1rem 0;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            #notes-editor-container {
                min-height: 250px;
            }

            .ql-container {
                min-height: 200px !important;
                max-height: 300px !important;
            }

            .ql-toolbar {
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            /* Ensure save button is visible */
            #saveNotesBtn {
                position: relative;
                z-index: 100;
                margin: 1rem auto;
                width: 90%;
            }

            /* Ensure related videos don't overlap */
            .related-videos-card {
                margin-top: 2rem !important;
                clear: both;
                position: relative;
                z-index: 5;
            }
        }

        @media (max-width: 480px) {
            .lesson-page {
                padding: 0.5rem;
            }

            .filter-bar {
                padding: 0.5rem;
            }

            .lesson-title {
                font-size: 1.125rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .lesson-info-card {
                padding: 1rem;
            }

            .lesson-meta {
                gap: 0.5rem;
            }

            .lesson-meta-item {
                padding: 0.375rem 0.75rem;
                font-size: 0.8125rem;
            }
            
            .lesson-actions {
                flex-direction: column;
            }

            .action-btn-primary,
            .action-btn-secondary {
                justify-content: center;
            }

            .comments-card {
                padding: 1rem;
            }

            .comment {
                padding: 0;
                margin-bottom: 1.25rem;
            }

            .comment-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.75rem;
            }


            .action-buttons-grid {
                gap: 0.375rem;
            }

            .action-btn {
                padding: 0.625rem 0.375rem;
                font-size: 0.6875rem;
            }

            .action-btn svg {
                width: 18px;
                height: 18px;
            }
            /* Adjust video item layout */
            .video-item {
                flex-direction: column;
                padding: 0.75rem;
            }

            .video-thumbnail {
                width: 100%;
                height: auto;
                aspect-ratio: 16/9;
            }

            .video-info-title {
                -webkit-line-clamp: 2;
            }

            .related-videos-card {
                padding: 1rem;
            }

            .share-platforms {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }

            .share-platform-icon {
                width: 45px;
                height: 45px;
                font-size: 1.125rem;
            }

            .notes-section {
                padding: 1.5rem 0.75rem;
            }

            /* FIXED: Smaller compact video for very small screens */
            .sticky-video-section.compact {
                width: 250px;
                bottom: 1rem;
                right: 1rem;
            }

            .sticky-video-section.compact .video-container {
                transform: none;
                margin-bottom: 0;
            }
        }

        /* Landscape mobile orientation fix */
        @media (max-width: 900px) and (max-height: 500px) {
            .video-container {
                aspect-ratio: 21/9;
            }

            .lesson-info-card {
                padding: 1rem;
            }

            .lesson-title {
                font-size: 1.125rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        /* ===== LEVEL MODAL STYLES (From DigiLearn) ===== */
        #level-modal-toggle {
            display: none;
        }

        .level-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        #level-modal-toggle:checked ~ .level-modal {
            display: flex;
        }

        .level-modal {
            pointer-events: none;
        }

        #level-modal-toggle:checked ~ .level-modal {
            pointer-events: auto;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #level-modal-toggle:checked ~ .level-modal .modal-overlay {
            opacity: 1;
        }

        .modal-content {
            position: relative;
            background-color: var(--white);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-xl);
            padding: 2rem;
            z-index: 1000;
            min-width: 300px;
            max-width: 450px;
            animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(0);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #level-modal-toggle:not(:checked) ~ .level-modal .modal-content {
            animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(20px);
        }

        @keyframes slideDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(20px);
            }
        }

        .modal-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 0 1.5rem 0;
            line-height: 1.3;
        }

        .level-option {
            display: block;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            background-color: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            color: var(--gray-700);
            font-size: 0.9375rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }

        .level-option:hover {
            background-color: var(--white);
            border-color: var(--secondary-blue);
            color: var(--secondary-blue);
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }

        .level-option:active {
            transform: translateX(2px);
        }

        .level-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .level-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            background-color: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            min-width: 140px;
        }

        /* Mobile: Compact level indicator */
        @media (max-width: 768px) {
            .level-indicator {
                padding: 0.75rem 0.75rem;
                font-size: 0.7rem;
                min-width: 80px;
                gap: 0.25rem;
            }

            .level-indicator::after {
                font-size: 0.5rem;
                margin-left: 0.25rem;
            }
        }

        .level-indicator:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(38, 119, 184, 0.05);
            color: var(--secondary-blue);
            box-shadow: var(--shadow-sm);
        }

        .level-indicator::after {
            content: '▼';
            font-size: 0.65rem;
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        #level-modal-toggle:checked ~ * .level-indicator::after {
            transform: rotate(180deg);
        }

        /* ===== END LEVEL MODAL STYLES ===== */
    </style>

    @php
        $selectedLevel = $lesson['level_group'] ?? 'primary-lower';
    @endphp

    <!-- Enhanced Filter Bar - Matching DigiLearn Layout -->
    <div class="filter-bar" id="filterBar">
        <button class="back-button" id="backButton">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <!-- Level Indicator (Left) -->
        <div class="level-container">
            <label for="level-modal-toggle" class="level-indicator">
                Grade: {{ $selectedLevel ? ([
                    'primary-lower' => 'Grade 1-3',
                    'primary-upper' => 'Grade 4-6',
                    'jhs' => 'Grade 7-9',
                    'shs' => 'Grade 10-12',
                    'university' => 'University'
                ][$selectedLevel] ?? ucwords(str_replace('-', ' ', $selectedLevel))) : 'Grade 1-3' }}
            </label>
        </div>

        <!-- Search Box (Middle) -->
        <div class="search-box" id="searchBox">
            <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="search-input" id="searchInput" placeholder="Search lessons, subjects, or topics...">
        </div>

        <!-- Search Toggle Button (Mobile only) -->
        <button class="search-toggle-btn" id="searchToggleBtn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>

        <!-- Quiz Button (Right) -->
        <div class="quiz-container">
            <a href="{{ route('quiz.index') }}" class="filter-button quiz">Quiz</a>
        </div>
    </div>

    <!-- Level Modal (Keep this for level selection) -->
    <input type="checkbox" id="level-modal-toggle" style="display: none;">
    <div class="level-modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <h3>Select Grade</h3>
            <a href="{{ route('dashboard.level-group', ['groupId' => 'primary-lower']) }}" class="level-option">Grade 1-3</a>
            <a href="{{ route('dashboard.level-group', ['groupId' => 'primary-upper']) }}" class="level-option">Grade 4-6</a>
            <a href="{{ route('dashboard.level-group', ['groupId' => 'jhs']) }}" class="level-option">Grade 7-9</a>
            <a href="{{ route('dashboard.level-group', ['groupId' => 'shs']) }}" class="level-option">Grade 10-12</a>
            <a href="{{ route('dashboard.level-group', ['groupId' => 'university']) }}" class="level-option">University</a>
        </div>
    </div>

    <!-- Enhanced Main Layout -->
    <div class="lesson-page">
        <!-- Main Content Area -->
        <main class="lesson-main">
            <!-- Enhanced Left Content -->
            <div class="left-content">
            @if(isset($course))
                <!-- Course Content with Tabs -->
                <div class="course-content-section">
                    <!-- Course Header -->
                    <div class="course-header-card">
                        <h1 class="course-title">{{ $course->title }}</h1>
                        <p class="course-description">{{ $course->description }}</p>

                        <div class="course-meta">
                            <div class="course-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ $course->subject }}
                            </div>
                            <div class="course-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $course->creator->name }}
                            </div>
                            <div class="course-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 9a2 2 0 002 2h6a2 2 0 002-2l-2-9"/>
                                </svg>
                                {{ $course->grade_level }}
                            </div>
                        </div>

                        <div class="course-stats">
                            <div class="stat-item">
                                <span class="stat-number">{{ $course->videos->count() }}</span>
                                <span class="stat-label">Videos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">{{ $course->documents->count() }}</span>
                                <span class="stat-label">Documents</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">{{ $course->quizzes->count() }}</span>
                                <span class="stat-label">Quizzes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Course Content Tabs -->
                    <div class="course-tabs">
                        <div class="tab-buttons">
                            <button class="tab-btn active" data-tab="videos">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Videos ({{ $course->videos->count() }})
                            </button>
                            <button class="tab-btn" data-tab="documents">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14,2 14,8 20,8"/>
                                </svg>
                                Documents ({{ $course->documents->count() }})
                            </button>
                            <button class="tab-btn" data-tab="quizzes">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Quizzes ({{ $course->quizzes->count() }})
                            </button>
                        </div>

                        <!-- Videos Tab -->
                        <div class="tab-content active" id="videos-tab">
                            @forelse($course->videos as $video)
                            <div class="course-item video-item">
                                <div class="item-thumbnail">
                                    <video class="item-video" muted preload="metadata" poster="{{ secure_asset($video->getThumbnailUrl()) }}">
                                        <source src="{{ secure_asset($video->video_path) }}" type="video/mp4">
                                    </video>
                                    <div class="play-overlay">
                                        <div class="play-button">
                                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="item-duration">{{ $video->duration_seconds ? floor($video->duration_seconds / 60) . ':' . str_pad($video->duration_seconds % 60, 2, '0', STR_PAD_LEFT) : 'N/A' }}</div>
                                </div>
                                <div class="item-info">
                                    <h4 class="item-title">{{ $video->title }}</h4>
                                    <p class="item-description">{{ Str::limit($video->description, 100) }}</p>
                                    <div class="item-meta">
                                        <span class="item-views">{{ number_format($video->views ?? 0) }} views</span>
                                        <span class="item-status {{ $video->status }}">{{ ucfirst($video->status) }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="empty-tab">
                                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <h3>No videos in this course</h3>
                                <p>Videos will be added to this course soon.</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-content" id="documents-tab">
                            @forelse($course->documents as $document)
                            <div class="course-item document-item">
                                <div class="item-icon">
                                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                    </svg>
                                </div>
                                <div class="item-info">
                                    <h4 class="item-title">{{ $document->title }}</h4>
                                    <p class="item-description">{{ Str::limit($document->description, 100) }}</p>
                                    <div class="item-meta">
                                        <span class="item-size">{{ $document->getFormattedFileSize() }}</span>
                                        <span class="item-type">{{ strtoupper($document->file_type ?? 'PDF') }}</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="{{ route('dashboard.lesson.document', ['lessonId' => \App\Services\UrlObfuscator::encode($document->id), 'type' => 'pdf']) }}" class="action-btn-small">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 3v4a1 1 0 001 1h4"/>
                                            <path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                            <path d="M9 9h6"/>
                                            <path d="M9 13h6"/>
                                            <path d="M9 17h6"/>
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="empty-tab">
                                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14,2 14,8 20,8"/>
                                </svg>
                                <h3>No documents in this course</h3>
                                <p>Documents will be added to this course soon.</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- Quizzes Tab -->
                        <div class="tab-content" id="quizzes-tab">
                            @forelse($course->quizzes as $quiz)
                            <div class="course-item quiz-item">
                                <div class="item-icon">
                                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="item-info">
                                    <h4 class="item-title">{{ $quiz->title }}</h4>
                                    <p class="item-description">{{ Str::limit($quiz->description, 100) }}</p>
                                    <div class="item-meta">
                                        <span class="item-questions">{{ $quiz->questions_count ?? 0 }} questions</span>
                                        <span class="item-subject">{{ $quiz->subject }}</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="{{ route('quiz.take', ['quizId' => $quiz->id]) }}" class="action-btn-small primary">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14.23 12.004a2.236 2.236 0 0 0 .53-1.482 2.236 2.236 0 0 0-.53-1.482l-4.23-2.882a2.236 2.236 0 0 0-2.46 0L3.31 9.04a2.236 2.236 0 0 0 0 3.848l4.23 2.882a2.236 2.236 0 0 0 2.46 0l4.23-2.882z"/>
                                        </svg>
                                        Take Quiz
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="empty-tab">
                                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3>No quizzes in this course</h3>
                                <p>Quizzes will be added to this course soon.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <!-- Sticky Video Section with scroll-triggered animations -->
                <div class="sticky-video-section" id="stickyVideoSection">
                    <!-- Enhanced Video Player using VideoFacadeManager -->
                    <div class="video-container">
                        @if(isset($lesson) && $lesson instanceof \App\Models\Video)
                            @php
                                $videoUrl = $lesson->getVideoUrl();
                                $embedHtml = $lesson->getEmbedHtml();
                                Log::info('Video Debug - Model Instance', [
                                    'lesson_id' => $lesson->id,
                                    'video_url' => $videoUrl,
                                    'embed_html_length' => strlen($embedHtml),
                                    'video_source' => $lesson->video_source,
                                    'status' => $lesson->status,
                                    'mux_playback_id' => $lesson->mux_playback_id ?? 'none',
                                    'vimeo_id' => $lesson->vimeo_id ?? 'none',
                                    'video_path' => $lesson->video_path,
                                    'temp_file_path' => $lesson->temp_file_path,
                                    'is_temp_expired' => $lesson->isTempExpired()
                                ]);
                            @endphp
                            @if($embedHtml)
                                <!-- Video Player Container for Main Lesson Video -->
                                <div id="lesson-video-player" class="video-facade-card lesson-main-video"
                                     data-video-id="{{ $lesson->id }}"
                                     data-video-source="{{ $lesson->video_source }}"
                                     data-vimeo-id="{{ $lesson->vimeo_id }}"
                                     data-external-video-id="{{ $lesson->external_video_id }}"
                                     data-mux-playback-id="{{ $lesson->mux_playback_id }}"
                                     data-video-path="{{ $lesson->video_path }}"
                                     data-title="{{ $lesson->title }}"
                                     data-lazy="false"
                                     style="width: 100%; height: 100%; position: relative;">

                                    <!-- Video Thumbnail (Poster) -->
                                    <div class="video-facade-thumbnail" style="width: 100%; height: 100%; background-color: #000;">
                                        @if($lesson->video_source === 'youtube')
                                            <img src="https://img.youtube.com/vi/{{ $lesson->external_video_id }}/maxresdefault.jpg"
                                                 alt="{{ $lesson->title }}"
                                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                                 onerror="this.src='https://img.youtube.com/vi/{{ $lesson->external_video_id }}/hqdefault.jpg'">
                                        @elseif($lesson->video_source === 'vimeo')
                                            <img src="https://vumbnail.com/{{ $lesson->vimeo_id }}.jpg"
                                                 alt="{{ $lesson->title }}"
                                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                                 onerror="this.src='/placeholder.svg?height=315&width=560'">
                                        @elseif($lesson->video_source === 'mux')
                                            <img src="https://image.mux.com/{{ $lesson->mux_playback_id }}/thumbnail.jpg"
                                                 alt="{{ $lesson->title }}"
                                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                                 onerror="this.src='/placeholder.svg?height=315&width=560'">
                                        @else
                                            <img src="{{ secure_asset($lesson->getThumbnailUrl()) }}"
                                                 alt="{{ $lesson->title }}"
                                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                                 onerror="this.src='/placeholder.svg?height=315&width=560'">
                                        @endif
                                    </div>

                                    <!-- Video Preview Container (for hover-to-play) -->
                                    <div class="video-preview" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 2;"></div>

                                    <!-- Play Overlay -->
                                    <div class="play-overlay" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 3; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.3); opacity: 0; transition: opacity 0.2s ease;">
                                        <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24" style="width: 64px; height: 64px; color: white; filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));">
                                            <polygon points="5 3 19 12 5 21 5 3"/>
                                        </svg>
                                    </div>

                                    <!-- Debug Info (optional) -->
                                    <div id="video-debug-info" style="background: rgba(0,0,0,0.8); color: white; padding: 8px; position: absolute; top: 10px; left: 10px; font-size: 11px; border-radius: 4px; z-index: 10; display: none;">
                                        <strong>Debug:</strong><br>
                                        ID: {{ $lesson->id }}<br>
                                        Source: {{ $lesson->video_source }}<br>
                                        Status: {{ $lesson->status }}
                                    </div>
                                </div>
                            @else
                                <div style="background: #ffebee; padding: 20px; border: 1px solid #f44336; border-radius: 5px;">
                                    <strong>Error:</strong> No embed HTML generated for this video.
                                    <br>Video Source: {{ $lesson->video_source }}
                                    <br>Status: {{ $lesson->status }}
                                </div>
                            @endif
                        @else
                            @php
                                $videoSrc = secure_asset($lesson['video_url'] ?? '');
                                $posterSrc = secure_asset($lesson['thumbnail'] ?? '');
                                \Log::info('Video Debug - Array Data', [
                                    'lesson_id' => $lesson['id'] ?? 'unknown',
                                    'video_url_raw' => $lesson['video_url'] ?? 'none',
                                    'thumbnail_raw' => $lesson['thumbnail'] ?? 'none',
                                    'video_src_secure' => $videoSrc,
                                    'poster_src_secure' => $posterSrc,
                                    'lesson_keys' => array_keys($lesson)
                                ]);
                            @endphp
                            @if(!empty($lesson['video_url']))
                                <!-- Video Player Container for Array-based Lesson Video -->
                                <div id="lesson-video-player" class="video-facade-card lesson-main-video"
                                     data-video-id="{{ $lesson['id'] ?? 'unknown' }}"
                                     data-video-source="local"
                                     data-video-path="{{ $lesson['video_url'] ?? '' }}"
                                     data-title="{{ $lesson['title'] ?? 'Lesson Video' }}"
                                     data-lazy="false"
                                     style="width: 100%; height: 100%; position: relative;">

                                    <!-- Video Thumbnail (Poster) -->
                                    <div class="video-facade-thumbnail" style="width: 100%; height: 100%; background-color: #000;">
                                        <img src="{{ $posterSrc }}"
                                             alt="{{ $lesson['title'] ?? 'Lesson Video' }}"
                                             style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                             onerror="this.src='/placeholder.svg?height=315&width=560'">
                                    </div>

                                    <!-- Video Preview Container -->
                                    <div class="video-preview" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 2;"></div>

                                    <!-- Play Overlay -->
                                    <div class="play-overlay" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 3; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, 0.3); opacity: 0; transition: opacity 0.2s ease;">
                                        <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24" style="width: 64px; height: 64px; color: white; filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));">
                                            <polygon points="5 3 19 12 5 21 5 3"/>
                                        </svg>
                                    </div>
                                </div>
                            @else
                                <div style="background: #ffebee; padding: 20px; border: 1px solid #f44336; border-radius: 5px; text-align: center;">
                                    <strong>Video Unavailable</strong>
                                    <br>This video is currently being processed or is unavailable.
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Enhanced Lesson Info -->
                    <div class="lesson-info-card">
                        <h1 class="lesson-title">{{ $lesson['title'] ?? 'Living and Non Living organism' }}</h1>

                        <div class="lesson-meta">
                            <div class="lesson-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ $lesson['subject'] ?? 'Science' }}
                            </div>
                            <div class="lesson-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $lesson['instructor'] ?? 'Prof. Aboagye' }}
                            </div>
                            <div class="lesson-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 9a2 2 0 002 2h6a2 2 0 002-2l-2-9"/>
                                </svg>
                                {{ $lesson['year'] ?? '2022' }}
                            </div>
                        </div>

                        <div class="lesson-actions">
                            <button class="action-btn-primary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                                Save Lesson
                            </button>
                            <button class="action-btn-secondary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="18" cy="5" r="3"/>
                                    <circle cx="6" cy="12" r="3"/>
                                    <circle cx="18" cy="19" r="3"/>
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                                </svg>
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Enhanced Comments Section -->
            <div class="comments-card">
                <div class="comments-header">
                    <div class="comments-header-left">
                        <span class="comments-count">
                            <span id="commentsCount">0</span> Comments
                            <button class="comments-dropdown" id="commentsToggleBtn">
                                <svg class="dropdown-icon" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>

                <div class="comment-input-container">
                    <div class="comment-avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                    <input type="text" class="comment-input" id="commentInput" placeholder="Add a comment..." />
                    <button class="comment-submit-btn" id="commentSubmitBtn">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>

                <div class="comments-list" id="commentsList">
                    <div class="loading-comments" id="loadingComments">
                        <div class="loading-spinner"></div>
                        <span>Loading comments...</span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Enhanced Right Sidebar -->
        <aside class="lesson-sidebar">
            <!-- Enhanced Action Buttons -->
            <div class="action-buttons-grid">
                <button class="action-btn">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Test
                </button>
                <button class="action-btn action-navigate-btn" data-href="{{ route('dashboard.lesson.document', ['lessonId' => \App\Services\UrlObfuscator::encode($lesson['id']), 'type' => 'pdf']) }}" data-document-type="pdf">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                    </svg>
                    Document
                    <div class="document-indicator" id="pdf-indicator" style="display: none;">
                        <i class="fas fa-check"></i>
                    </div>
                </button>
                <button class="action-btn action-navigate-btn" data-href="{{ route('dashboard.lesson.document', ['lessonId' => \App\Services\UrlObfuscator::encode($lesson['id']), 'type' => 'ppt']) }}" data-document-type="ppt">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="9" x2="15" y2="9"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                    </svg>
                    PPT
                    <div class="document-indicator" id="ppt-indicator" style="display: none;">
                        <i class="fas fa-check"></i>
                    </div>
                </button>
            </div>

            <!-- Enhanced Notes Section -->
            <div class="notes-section">
                @if(isset($note) && $note)
                    <div class="existing-note">
                        <h4>{{ $note->title ?: 'My Notes' }}</h4>
                        <p>Last updated: {{ $note->formatted_updated_at }}</p>
                    </div>
                @endif
                <button class="add-notes-btn" id="addNotesBtn">
                    {{ isset($note) && $note ? 'Edit notes' : 'Add notes' }}
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </button>
            </div>

            <!-- Notes Title Modal -->
            <div class="notes-title-modal-overlay" id="notesTitleModalOverlay">
                <div class="notes-title-modal">
                    <div class="notes-title-modal-header">
                        <h3 class="notes-title-modal-title">Name Your Notes</h3>
                        <button class="notes-title-modal-close" id="notesTitleModalClose">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="notes-title-modal-body">
                        <p class="notes-title-modal-description">
                            Give your notes a custom title to help you organize and find them later.
                        </p>

                        <div class="notes-title-input-group">
                            <input type="text" class="notes-title-input" id="notesTitleInput" placeholder="Enter notes title..." maxlength="255">
                            <div class="notes-title-suggestions">
                                <button class="notes-title-suggestion-btn" data-title="{{ Str::slug($lesson['title'] ?? '') }}">
                                    Use video title: "{{ Str::limit($lesson['title'] ?? 'Lesson', 30) }}"
                                </button>
                            </div>
                        </div>

                        <div class="notes-title-modal-tip">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>You can change this title anytime by editing your notes.</span>
                        </div>
                    </div>

                    <div class="notes-title-modal-footer">
                        <button class="notes-title-modal-btn secondary" id="notesTitleModalSkip">
                            Skip & Continue
                        </button>
                        <button class="notes-title-modal-btn primary" id="notesTitleModalContinue">
                            Continue with Title
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notes Wrapper (hidden by default) -->
            <div id="notesWrapper" style="display: none; margin-top: 1rem;">
                <!-- Update Mode Selector -->
                <div class="update-mode-selector">
                    <label>
                        <input type="radio" name="update_mode" value="replace" checked>
                        Replace existing notes
                    </label>
                    <label>
                        <input type="radio" name="update_mode" value="append">
                        Add to existing notes
                    </label>
                </div>

                <!-- Editor Container -->
                <div id="notes-editor-container">
                    <!-- Quill will create its own toolbar inside this div -->
                    <div id="notes-editor"></div>
                </div>

                <!-- Save Button -->
                <button id="saveNotesBtn" class="btn btn-success mt-2">
                    Save Notes
                </button>
            </div>

            <!-- Enhanced Related Videos -->
            <div class="related-videos-card">
                <h3 class="related-videos-title">Related Lessons</h3>
                
                @if(isset($relatedLessons))
                    @foreach($relatedLessons as $relatedLesson)
                    <div class="video-item related-video-item hover-video-card" data-href="/dashboard/lesson/{{ \App\Services\UrlObfuscator::encode($relatedLesson['id']) }}" data-lesson-id="{{ \App\Services\UrlObfuscator::encode($relatedLesson['id']) }}" data-video-id="{{ $relatedLesson['id'] }}" data-subject="{{ $relatedLesson['subject'] ?? 'General' }}" data-title="{{ $relatedLesson['title'] ?? 'Lesson' }}" data-video-source="{{ $relatedLesson['video_source'] ?? 'local' }}" data-vimeo-id="{{ $relatedLesson['vimeo_id'] ?? '' }}" data-external-video-id="{{ $relatedLesson['external_video_id'] ?? '' }}" data-mux-playback-id="{{ $relatedLesson['mux_playback_id'] ?? '' }}" data-loaded="false">
                        <div class="video-thumbnail">
                            <img src="{{ secure_asset($relatedLesson['thumbnail'] ?? '') }}" alt="{{ $relatedLesson['title'] ?? 'Lesson' }}"
                                 onerror="this.src='/placeholder.svg?height=78&width=140'">
                            <div class="video-preview"></div>
                            <div class="play-overlay">
                                <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="video-details">
                            <h4 class="video-title">{{ $relatedLesson['title'] ?? 'Living and non-living organisms' }}</h4>
                            <p class="video-meta">{{ $relatedLesson['instructor'] ?? 'Prof. Aboagye' }} • {{ $relatedLesson['year'] ?? '2022' }}</p>
                        </div>
                        <div class="video-menu">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="1"/>
                                <circle cx="19" cy="12" r="1"/>
                                <circle cx="5" cy="12" r="1"/>
                            </svg>
                        </div>
                    </div>
                    @endforeach
                @else
                    <!-- Sample related videos for demo -->
                    @for($i = 1; $i <= 8; $i++)
                    <div class="video-item hover-video-card" data-lesson-id="demo-{{ $i }}" data-video-id="demo-{{ $i }}" data-subject="Science" data-title="Living and non-living organisms" data-video-source="vimeo" data-vimeo-id="76979871" data-external-video-id="" data-mux-playback-id="" data-loaded="false">
                        <div class="video-thumbnail">
                            <img src="/placeholder.svg?height=78&width=140" alt="Related Lesson {{ $i }}">
                            <div class="video-preview"></div>
                            <div class="play-overlay">
                                <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="video-details">
                            <h4 class="video-title">Living and non-living organisms</h4>
                            <p class="video-meta">Prof. Aboagye • 2022</p>
                        </div>
                        <div class="video-menu">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="1"/>
                                <circle cx="19" cy="12" r="1"/>
                                <circle cx="5" cy="12" r="1"/>
                            </svg>
                        </div>
                    </div>
                    @endfor
                @endif
            </div>
        </div>
    </div>
    </div>

    <!-- Notes Update Mode Education Modal -->
    <div class="notes-education-modal-overlay" id="notesEducationModalOverlay">
        <div class="notes-education-modal">
            <div class="notes-education-modal-header">
                <div class="notes-education-modal-icon">
                    <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <h3 class="notes-education-modal-title">Smart Notes Saving</h3>
                <button class="notes-education-modal-close" id="notesEducationModalClose">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="notes-education-modal-body">
                <p class="notes-education-modal-description">
                    Choose how you want to save your notes for this lesson:
                </p>

                <div class="notes-education-modal-options">
                    <div class="notes-education-option">
                        <div class="notes-education-option-header">
                            <input type="radio" disabled checked>
                            <strong>Replace existing notes</strong>
                        </div>
                        <p>Completely replace your current notes with new content. Use this when you want to start fresh.</p>
                    </div>

                    <div class="notes-education-option">
                        <div class="notes-education-option-header">
                            <input type="radio" disabled>
                            <strong>Add to existing notes</strong>
                        </div>
                        <p>Append new content to your existing notes. Perfect for adding more thoughts or continuing where you left off.</p>
                    </div>
                </div>

                <div class="notes-education-modal-tip">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span><strong>Pro tip:</strong> You can always edit your notes later, and this choice only affects how new content is added. Use <kbd>Ctrl+S</kbd> to quickly save!</span>
                </div>
            </div>

            <div class="notes-education-modal-footer">
                <button class="notes-education-modal-btn" id="notesEducationModalGotIt">
                    Got it, let's start writing!
                </button>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="share-modal-overlay" id="shareModalOverlay">
        <div class="share-modal">
            <div class="share-modal-header">
                <h3 class="share-modal-title">share</h3>
                <button class="share-modal-close" id="shareModalClose">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="share-platforms">
                <div class="share-platform" data-platform="whatsapp">
                    <div class="share-platform-icon whatsapp">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.346"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">WhatsApp</span>
                </div>
                
                <div class="share-platform" data-platform="facebook">
                    <div class="share-platform-icon facebook">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Facebook</span>
                </div>
                
                <div class="share-platform" data-platform="email">
                    <div class="share-platform-icon email">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Email</span>
                </div>
                
                <div class="share-platform" data-platform="instagram">
                    <div class="share-platform-icon instagram">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Instagram</span>
                </div>
                
                <div class="share-platform" data-platform="twitter">
                    <div class="share-platform-icon twitter">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Twitter</span>
                </div>
            </div>
            
            <div class="share-url-container">
                <input type="text" class="share-url-input" id="shareUrlInput" readonly>
                <button class="share-copy-btn" id="shareCopyBtn">Copy</button>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Video Player Initialization for Lesson View
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.querySelector('.video-facade-card');
            if (!card) return;

            let source = card.dataset.videoSource;
            const container = document.getElementById('lesson-video-player');

            if (!container) return;

            // Detect actual video type from URL if source is 'local' but path looks external
            if (source === 'local') {
                const videoPath = card.dataset.videoPath || '';
                if (videoPath.includes('youtube.com') || videoPath.includes('youtu.be')) {
                    source = 'youtube';
                    // Extract video ID from URL
                    const youtubeMatch = videoPath.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
                    if (youtubeMatch) {
                        card.dataset.externalVideoId = youtubeMatch[1];
                    }
                } else if (videoPath.includes('vimeo.com')) {
                    source = 'vimeo';
                    // Extract video ID from URL
                    const vimeoMatch = videoPath.match(/vimeo\.com\/(?:video\/)?(\d+)/);
                    if (vimeoMatch) {
                        card.dataset.vimeoId = vimeoMatch[1];
                    }
                }
            }

            if (source === 'local') {
                container.innerHTML = `
                    <video controls autoplay playsinline style="width:100%;height:auto">
                        <source src="${card.dataset.videoPath}" type="video/mp4">
                    </video>
                `;
            }

            if (source === 'vimeo') {
                container.innerHTML = `
                    <iframe
                        src="https://player.vimeo.com/video/${card.dataset.vimeoId}?autoplay=1&muted=0"
                        frameborder="0"
                        allow="autoplay; fullscreen"
                        allowfullscreen
                        style="width:100%;height:100%;aspect-ratio:16/9"
                    ></iframe>
                `;
            }

            if (source === 'youtube') {
                container.innerHTML = `
                    <iframe
                        src="https://www.youtube.com/embed/${card.dataset.externalVideoId}?autoplay=1&mute=0&rel=0&modestbranding=1&showinfo=0&iv_load_policy=3&fs=1"
                        frameborder="0"
                        allow="autoplay; fullscreen; encrypted-media"
                        allowfullscreen
                        style="width:100%;height:100%;aspect-ratio:16/9"
                    ></iframe>
                `;
            }

            if (source === 'mux') {
                container.innerHTML = `
                    <mux-player
                        playback-id="${card.dataset.muxPlaybackId}"
                        autoplay
                        controls
                        style="width:100%;aspect-ratio:16/9">
                    </mux-player>
                `;
            }
        });

        // Global variables for the rich text editor
        const defaultNoteTitle = '{{ \Illuminate\Support\Str::slug($lesson["title"] ?? "") }}';
        let notesQuill = null;

        // Global constants and functions for the rich text editor
        const MAX_CHARS = 1000;

        // Global function for character count updates
        function updateCharCount() {
            if (!quillEditor || !document.querySelector('.notes-character-count')) return;

            const text = quillEditor.getText();
            const charCount = text.length;
            const charCountElement = document.querySelector('.notes-character-count');

            charCountElement.textContent = `${charCount}/${MAX_CHARS}`;

            // Update styling based on character count
            charCountElement.classList.remove('warning', 'error');

            if (charCount > MAX_CHARS * 0.9) {
                charCountElement.classList.add('warning');
            }
            if (charCount > MAX_CHARS) {
                charCountElement.classList.add('error');
            }
        }

        // Scroll tracking variables for mobile video section
        let lastScrollY = 0;
        let isScrollingDown = false;
        let scrollThreshold = 130; // Pixels to scroll before triggering compact mode (matches filter bar height)

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, checking Quill availability...');
            console.log('CSP Nonce available:', !!document.querySelector('meta[name="csp-nonce"]'));
            console.log('CSRF Token available:', !!document.querySelector('meta[name="csrf-token"]'));


            // Check if Quill scripts are present in DOM
            const quillScripts = document.querySelectorAll('script[src*="quill"]');
            console.log('Quill script tags found:', quillScripts.length);
            quillScripts.forEach((script, index) => {
                console.log(`Quill script ${index + 1}:`, script.src, 'loaded:', script.complete);
            });

            // Check if Quill is loaded before initializing
            if (typeof Quill === 'undefined') {
                console.warn('Quill.js not loaded yet, waiting...');

                // Try loading Quill manually as a test
                console.log('Attempting manual Quill load...');
                const manualScript = document.createElement('script');
                manualScript.src = 'https://cdn.quilljs.com/1.3.7/quill.min.js';
                manualScript.onload = () => {
                    console.log('Manual Quill load successful');
                    initializeAll();
                };
                manualScript.onerror = () => {
                    console.error('Manual Quill load failed');
                    alert('The notes editor failed to load. Please refresh the page.');
                };
                document.head.appendChild(manualScript);

                return;
            } else {
                console.log('Quill.js already loaded, version:', Quill.version || 'unknown');
                initializeAll();
            }
        });

        // Test basic Quill functionality (removed to prevent orphaned toolbars)

        // Initialize Mobile Search Toggle
        function initializeSearchToggle() {
            const filterBar = document.getElementById('filterBar');
            const searchToggleBtn = document.getElementById('searchToggleBtn');
            const searchInput = document.getElementById('searchInput');
            const searchBox = document.getElementById('searchBox');

            if (!searchToggleBtn) return;

            // Toggle search on button click
            searchToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterBar.classList.toggle('search-active');
                
                // Focus search input when opened
                if (filterBar.classList.contains('search-active')) {
                    setTimeout(() => {
                        searchInput?.focus();
                    }, 100);
                }
            });

            // Close search when clicking outside
            document.addEventListener('click', (e) => {
                if (filterBar.classList.contains('search-active')) {
                    const isClickInSearchBox = searchBox?.contains(e.target);
                    const isClickOnToggle = searchToggleBtn.contains(e.target);
                    
                    if (!isClickInSearchBox && !isClickOnToggle) {
                        filterBar.classList.remove('search-active');
                    }
                }
            });

            // Close search on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && filterBar.classList.contains('search-active')) {
                    filterBar.classList.remove('search-active');
                }
            });
        }

        // Initialize Level Modal with proper close handlers
        function initializeLevelModal() {
            const levelModalToggle = document.getElementById('level-modal-toggle');
            const modalOverlay = document.querySelector('.modal-overlay');
            const levelIndicator = document.querySelector('.level-indicator');

            // Close on overlay click
            if (modalOverlay && levelModalToggle) {
                modalOverlay.addEventListener('click', (e) => {
                    if (e.target === modalOverlay) {
                        levelModalToggle.checked = false;
                    }
                });
            }

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && levelModalToggle && levelModalToggle.checked) {
                    levelModalToggle.checked = false;
                }
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                const modalContent = document.querySelector('.modal-content');
                const isClickInsideModal = modalContent?.contains(e.target);
                const isClickOnToggle = levelIndicator?.contains(e.target);
                if (!isClickInsideModal && !isClickOnToggle && levelModalToggle && levelModalToggle.checked) {
                    levelModalToggle.checked = false;
                }
            });

            // Close modal when selecting a level option
            document.querySelectorAll('.level-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (levelModalToggle) {
                        levelModalToggle.checked = false;
                    }
                    // The href will navigate after a short delay to allow animation
                    const href = option.getAttribute('href');
                    if (href) {
                        setTimeout(() => {
                            window.location.href = href;
                        }, 150);
                    }
                });
            });
        }

        function initializeAll() {
            // Initialize all functionality
            initializeFilters();
            initializeComments();
            initializeActionButtons();
            initializeNotesEditor();
            initializeShareModal();
            initializeSaveLesson();
            initializeSearch();
            initializeVideoItems();
            initializeVideoCards(); // Add hover-to-play functionality
            initializeKeyboardShortcuts();
            initializeNavigation();
            initializeCommentsToggle();
            initializeMobileVideoScroll();
            initializeCourseTabs();
            checkDocumentAvailability();
            initializeVideoProgressTracking();
            initializeLevelModal(); // Initialize level modal handlers
            initializeSearchToggle(); // Initialize mobile search toggle
        }

        // Optimized hover-to-play functionality following YouTube/Netflix pattern
        function initializeVideoCards() {
            let hoverTimer = null;
            let activePlayer = null;
            let hasPreconnected = false;

            // Mobile detection
            const isMobile = 'ontouchstart' in window;

            document.querySelectorAll('.hover-video-card').forEach(card => {
                const videoPreview = card.querySelector('.video-preview');

                if (isMobile) {
                    // Mobile: use click instead of hover
                    card.addEventListener('click', function(e) {
                        // Don't trigger if clicking on action buttons
                        if (e.target.closest('.lesson-action-btn')) return;

                        e.preventDefault();
                        if (card.dataset.loaded === 'false') {
                            activatePreview(card);
                        }
                    });
                } else {
                    // Desktop: hover with debounce
                    card.addEventListener('mouseenter', () => {
                        hoverTimer = setTimeout(() => {
                            activatePreview(card);
                        }, 250); // prevents accidental hover
                    });

                    card.addEventListener('mouseleave', () => {
                        clearTimeout(hoverTimer);
                        deactivatePreview(card);
                    });
                }
            });

            function activatePreview(card) {
                if (card.dataset.loaded === 'true') return;

                // Kill previous preview
                if (activePlayer) {
                    activePlayer.pause();
                    activePlayer = null;
                    document.querySelectorAll('.video-preview').forEach(p => p.innerHTML = '');
                    document.querySelectorAll('.hover-video-card').forEach(c => c.dataset.loaded = 'false');
                }

                const videoId = card.dataset.vimeoId || card.dataset.externalVideoId;
                const videoSource = card.dataset.videoSource;
                const preview = card.querySelector('.video-preview');

                if (!videoId) return; // No video to preview

                // Preconnect to Vimeo on first hover
                if (!hasPreconnected && (videoSource === 'vimeo' || videoSource === 'youtube')) {
                    const link = document.createElement('link');
                    link.rel = 'preconnect';
                    link.href = videoSource === 'vimeo' ? 'https://player.vimeo.com' : 'https://www.youtube.com';
                    document.head.appendChild(link);
                    hasPreconnected = true;
                }

                if (videoSource === 'vimeo') {
                    const iframe = document.createElement('iframe');
                    iframe.src = `https://player.vimeo.com/video/${videoId}?autoplay=1&muted=1&background=1`;
                    iframe.allow = 'autoplay';
                    iframe.loading = 'lazy';
                    iframe.frameBorder = '0';
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.position = 'absolute';
                    iframe.style.top = '0';
                    iframe.style.left = '0';

                    preview.appendChild(iframe);

                    activePlayer = new Vimeo.Player(iframe);
                } else if (videoSource === 'youtube') {
                    const iframe = document.createElement('iframe');
                    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&controls=0&modestbranding=1&rel=0&showinfo=0&loop=1&playlist=${videoId}`;
                    iframe.allow = 'autoplay';
                    iframe.loading = 'lazy';
                    iframe.frameBorder = '0';
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.position = 'absolute';
                    iframe.style.top = '0';
                    iframe.style.left = '0';

                    preview.appendChild(iframe);

                    // For YouTube, we can't easily get a player instance, so we'll handle cleanup differently
                    activePlayer = { pause: () => {}, element: iframe };
                }

                card.dataset.loaded = 'true';
            }

            function deactivatePreview(card) {
                card.dataset.loaded = 'false';

                const preview = card.querySelector('.video-preview');
                preview.innerHTML = ''; // destroy iframe

                if (activePlayer) {
                    activePlayer.pause();
                    activePlayer = null;
                }
            }

            // Handle lesson link clicks
            document.addEventListener('click', function(e) {
                if (e.target.closest('.lesson-link')) {
                    e.preventDefault();
                    const link = e.target.closest('.lesson-link');
                    const lessonId = link.getAttribute('data-lesson-id');
                    if (lessonId) {
                        window.location.href = `/dashboard/lesson/${lessonId}`;
                    }
                }
            });

            // Set periodic ping to keep session alive
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    fetch('/ping', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                }
            }, 300000); // 5 minutes
        }

        // Enhanced mobile video scroll functionality
        function initializeMobileVideoScroll() {
            if (window.innerWidth <= 768) {
                const stickyVideoSection = document.getElementById('stickyVideoSection');

                window.addEventListener('scroll', function() {
                    const currentScrollY = window.scrollY;
                    isScrollingDown = currentScrollY > lastScrollY;

                    // Add compact class when scrolling down past threshold
                    if (currentScrollY > scrollThreshold && isScrollingDown) {
                        stickyVideoSection.classList.add('compact');
                    }
                    // Remove compact class when scrolling back to top
                    else if (currentScrollY <= scrollThreshold) {
                        stickyVideoSection.classList.remove('compact');
                    }

                    lastScrollY = currentScrollY;
                }, { passive: true });
            }

            // Re-initialize on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    const stickyVideoSection = document.getElementById('stickyVideoSection');
                    stickyVideoSection.classList.remove('compact');
                }
            });
        }


        // Enhanced filter functionality
        function initializeFilters() {
            const filterButtons = document.querySelectorAll('.filter-button');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }

        // Enhanced comment interactions
        function initializeComments() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';
            const commentInput = document.getElementById('commentInput');
            const commentSubmitBtn = document.getElementById('commentSubmitBtn');

            // Load comments on page load
            loadComments();
    
            // Initialize real-time comment broadcasting
            initializeCommentBroadcasting();

            // Show submit button when typing
            if (commentInput) {
                commentInput.addEventListener('input', function() {
                    if (this.value.trim().length > 0) {
                        commentSubmitBtn.style.display = 'block';
                    } else {
                        commentSubmitBtn.style.display = 'none';
                    }
                });

                // Submit comment on Enter key
                commentInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        submitComment();
                    }
                });
            }

            // Submit comment button click
            if (commentSubmitBtn) {
                commentSubmitBtn.addEventListener('click', submitComment);
            }
        }

        // Load comments from server
        function loadComments() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';
            const commentsList = document.getElementById('commentsList');
            const loadingComments = document.getElementById('loadingComments');

            console.log('Loading comments for lesson ID:', lessonId);

            if (!lessonId) {
                console.error('No lesson ID available for comments');
                return;
            }

            // Check CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            console.log('CSRF token available:', !!csrfToken, 'Value:', csrfToken ? csrfToken.getAttribute('content').substring(0, 10) + '...' : 'none');

            const requestUrl = `/dashboard/lesson/${lessonId}/comments`;
            console.log('Making request to:', requestUrl);

            fetch(requestUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    console.log('Comments response status:', response.status);
                    console.log('Comments response headers:', Object.fromEntries(response.headers.entries()));
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Comments data received:', data);
                    if (data.success) {
                        renderComments(data.comments);
                        document.getElementById('commentsCount').textContent = data.total_count;
                        console.log('Comments loaded successfully, count:', data.total_count);
                    } else {
                        console.error('Comments API returned error:', data);
                        commentsList.innerHTML = '<div class="error-message">Failed to load comments: ' + (data.message || 'Unknown error') + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                    console.error('Error details:', {
                        message: error.message,
                        stack: error.stack,
                        lessonId: lessonId,
                        url: requestUrl
                    });
                    commentsList.innerHTML = '<div class="error-message">Failed to load comments: ' + error.message + '</div>';
                });
        }

        // Render comments in the UI
        function renderComments(comments) {
            const commentsList = document.getElementById('commentsList');

            if (comments.length === 0) {
                commentsList.innerHTML = '<div class="no-comments">No comments yet. Be the first to comment!</div>';
                return;
            }

            const commentsHtml = comments.map(comment => `
                <div class="comment" data-comment-id="${comment.id}">
                    <div class="comment-avatar">${comment.user.avatar_initial}</div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author">${comment.user.name}</span>
                            <span class="comment-time">${comment.time_ago}</span>
                        </div>
                        <p class="comment-text">${comment.content}</p>
                        <div class="comment-actions">
                            <button class="comment-action like-btn ${comment.user_action === 'like' ? 'active' : ''}" data-action="like" data-comment-id="${comment.id}">
                                <i class="fas fa-thumbs-up"></i>
                                <span class="comment-like-count">${comment.likes_count}</span>
                            </button>
                            <button class="comment-action dislike-btn ${comment.user_action === 'dislike' ? 'active' : ''}" data-action="dislike" data-comment-id="${comment.id}">
                                <i class="fas fa-thumbs-down"></i>
                                <span class="comment-dislike-count">${comment.dislikes_count}</span>
                            </button>
                            <button class="comment-action reply-btn" data-comment-id="${comment.id}">
                                Reply
                            </button>
                        </div>
                        ${comment.replies && comment.replies.length > 0 ? `
                            <div class="comment-replies">
                                ${comment.replies.map(reply => `
                                    <div class="comment reply" data-comment-id="${reply.id}">
                                        <div class="comment-avatar">${reply.user.avatar_initial}</div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <span class="comment-author">${reply.user.name}</span>
                                                <span class="comment-time">${reply.time_ago}</span>
                                            </div>
                                            <p class="comment-text">${reply.content}</p>
                                            <div class="comment-actions">
                                                <button class="comment-action like-btn ${reply.user_action === 'like' ? 'active' : ''}" data-action="like" data-comment-id="${reply.id}">
                                                    <i class="fas fa-thumbs-up"></i>
                                                    <span class="comment-like-count">${reply.likes_count}</span>
                                                </button>
                                                <button class="comment-action dislike-btn ${reply.user_action === 'dislike' ? 'active' : ''}" data-action="dislike" data-comment-id="${reply.id}">
                                                    <i class="fas fa-thumbs-down"></i>
                                                    <span class="comment-dislike-count">${reply.dislikes_count}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `).join('');

            commentsList.innerHTML = commentsHtml;

            // Add event listeners for comment actions
            attachCommentEventListeners();
        }

        // Submit a new comment
        function submitComment(parentId = null) {
            const commentInput = document.getElementById('commentInput');
            const commentText = commentInput.value.trim();
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!commentText || !lessonId) return;

            const submitBtn = document.getElementById('commentSubmitBtn');
            const originalContent = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML = '<div class="loading-spinner"></div>';
            submitBtn.disabled = true;

            fetch(`/dashboard/lesson/${lessonId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    comment: commentText,
                    parent_id: parentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentInput.value = '';
                    submitBtn.style.display = 'none';
                    loadComments(); // Reload comments to show the new one
                    showSuccessMessage('Comment posted successfully!');
                } else {
                    alert(data.message || 'Failed to post comment');
                }
            })
            .catch(error => {
                console.error('Error posting comment:', error);
                alert('Failed to post comment. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            });
        }

        // Attach event listeners to comment actions
        function attachCommentEventListeners() {
            // Like/dislike buttons
            document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentId = this.dataset.commentId;
                    const action = this.dataset.action;
                    const commentElement = this.closest('.comment');
                    const likeBtn = commentElement.querySelector('.like-btn');
                    const dislikeBtn = commentElement.querySelector('.dislike-btn');

                    fetch(`/dashboard/comment/${commentId}/like`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ action: action })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the counts
                            const likeCount = likeBtn.querySelector('.comment-like-count');
                            const dislikeCount = dislikeBtn.querySelector('.comment-dislike-count');

                            if (likeCount) likeCount.textContent = data.likes_count;
                            if (dislikeCount) dislikeCount.textContent = data.dislikes_count;

                            // Update active states based on user action
                            likeBtn.classList.toggle('active', data.user_action === 'like');
                            dislikeBtn.classList.toggle('active', data.user_action === 'dislike');

                            // Visual feedback
                            this.style.transform = 'scale(1.1)';
                            setTimeout(() => {
                                this.style.transform = '';
                            }, 200);
                        }
                    })
                    .catch(error => console.error('Error updating comment:', error));
                });
            });

            // Reply buttons
            document.querySelectorAll('.reply-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentId = this.dataset.commentId;
                    const commentInput = document.getElementById('commentInput');

                    // Focus on input and set placeholder
                    commentInput.placeholder = 'Reply to this comment...';
                    commentInput.focus();

                    // Store parent comment ID for reply
                    commentInput.dataset.parentId = commentId;

                    // Update submit handler to include parent ID
                    const submitBtn = document.getElementById('commentSubmitBtn');
                    submitBtn.onclick = () => submitComment(commentId);
                });
            });
        }

        // Initialize real-time comment broadcasting
        function initializeCommentBroadcasting() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!lessonId || typeof Echo === 'undefined') {
                console.log('Broadcasting not available or lesson ID missing');
                return;
            }

            // Listen for new comments on this lesson
            Echo.channel(`lesson.${lessonId}`)
                .listen('.comment.created', (e) => {
                    console.log('New comment received:', e);

                    // Only add the comment if it's not from the current user
                    // (since they already see their own comment after posting)
                    const currentUserId = '{{ auth()->id() }}';
                    if (e.comment.user.id != currentUserId) {
                        // Add the new comment to the UI
                        addRealTimeComment(e.comment);

                        // Update comment count
                        const commentsCount = document.getElementById('commentsCount');
                        if (commentsCount) {
                            const currentCount = parseInt(commentsCount.textContent) || 0;
                            commentsCount.textContent = currentCount + 1;
                        }

                        // Show success notification
                        showSuccessMessage('New comment added!');
                    }
                });
        }

        // Add a real-time comment to the UI
        function addRealTimeComment(commentData) {
            const commentsList = document.getElementById('commentsList');

            // Remove "no comments" message if it exists
            const noCommentsMsg = commentsList.querySelector('.no-comments');
            if (noCommentsMsg) {
                noCommentsMsg.remove();
            }

            // Create new comment element
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
            commentElement.setAttribute('data-comment-id', commentData.id);

            commentElement.innerHTML = `
                <div class="comment-avatar">${commentData.user.avatar_initial}</div>
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="comment-author">${commentData.user.name}</span>
                        <span class="comment-time">${commentData.time_ago}</span>
                    </div>
                    <p class="comment-text">${commentData.content}</p>
                    <div class="comment-actions">
                        <button class="comment-action like-btn" data-action="like" data-comment-id="${commentData.id}">
                            <i class="fas fa-thumbs-up"></i>
                            <span class="comment-like-count">${commentData.likes_count}</span>
                        </button>
                        <button class="comment-action dislike-btn" data-action="dislike" data-comment-id="${commentData.id}">
                            <i class="fas fa-thumbs-down"></i>
                            <span class="comment-dislike-count">${commentData.dislikes_count}</span>
                        </button>
                        <button class="comment-action reply-btn" data-comment-id="${commentData.id}">
                            Reply
                        </button>
                    </div>
                </div>
            `;

            // Add to the top of the comments list
            const firstComment = commentsList.querySelector('.comment');
            if (firstComment) {
                commentsList.insertBefore(commentElement, firstComment);
            } else {
                commentsList.appendChild(commentElement);
            }

            // Re-attach event listeners for the new comment
            attachCommentEventListeners();
        }

        function initializeCommentsToggle() {
            const commentsToggleBtn = document.getElementById('commentsToggleBtn');
            const commentsList = document.getElementById('commentsList');

            function updateCommentsVisibility() {
                if (window.innerWidth <= 768) {
                    commentsList.classList.add('hidden');
                    commentsToggleBtn.classList.add('open');
                } else {
                    commentsList.classList.remove('hidden');
                    commentsToggleBtn.classList.remove('open');
                }
            }
            
            if (commentsToggleBtn && commentsList) {
                // Set initial state
                updateCommentsVisibility();

                // Update on resize
                window.addEventListener('resize', updateCommentsVisibility);

                commentsToggleBtn.addEventListener('click', function() {
                    this.classList.toggle('open');
                    commentsList.classList.toggle('hidden');
                });
            }
        }

        // Check document availability and show indicators
        function checkDocumentAvailability() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!lessonId) return;

            // Check PDF documents
            fetch(`/dashboard/lesson/${lessonId}/document/pdf`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const pdfIndicator = document.getElementById('pdf-indicator');
                if (data.exists && pdfIndicator) {
                    pdfIndicator.style.display = 'flex';
                }
            })
            .catch(error => console.error('Error checking PDF availability:', error));

            // Check PPT documents
            fetch(`/dashboard/lesson/${lessonId}/document/ppt`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const pptIndicator = document.getElementById('ppt-indicator');
                if (data.exists && pptIndicator) {
                    pptIndicator.style.display = 'flex';
                }
            })
            .catch(error => console.error('Error checking PPT availability:', error));
        }

        // Enhanced action button functionality
        function initializeActionButtons() {
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    console.log('Action clicked:', action);
                    
                    // Add loading state
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<div class="loading-spinner"></div>';
                    
                    setTimeout(() => {
                        this.innerHTML = originalContent;
                    }, 1000);
                    
                    switch(action) {
                        case 'Test':
                            // Handle test functionality
                            break;
                        case 'Document':
                            // Handle document functionality
                            break;
                        case 'PPT':
                            // Handle PPT functionality
                            break;
                    }
                });
            });

            // Navigation buttons - Check if documents exist before navigating
            document.querySelectorAll('.action-navigate-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.dataset.href;
                    const buttonText = this.textContent.trim().toLowerCase();

                    // Check if this is a document or PPT button
                    if (buttonText.includes('document') || buttonText.includes('ppt')) {
                        // Make AJAX call to check if documents exist
                        fetch(href, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error === 'level_required') {
                                // Level selection required, redirect to level selection
                                window.location.href = '/dashboard/level-selection';
                            } else if (data.exists) {
                                // Documents exist, proceed with navigation
                                window.location.href = href;
                            } else {
                                // No documents found, show message
                                showNoDocumentsMessage(buttonText.includes('ppt') ? 'PPT' : 'Document');
                            }
                        })
                        .catch(error => {
                            console.error('Error checking documents:', error);
                            // On error, show message instead of redirecting
                            showNoDocumentsMessage(buttonText.includes('ppt') ? 'PPT' : 'Document');
                        });
                    } else {
                        // For other buttons, proceed normally
                        window.location.href = href;
                    }
                });
            });
        }

        // Add to initializeVideoItems()
        function initializeVideoItems() {
            // Related video items
            document.querySelectorAll('.related-video-item').forEach(item => {
                item.addEventListener('click', function() {
                const url = this.dataset.href;
                // Visual feedback
                this.style.opacity = '0.7';
                this.style.transform = 'scale(0.98)';
                // Navigation
                setTimeout(() => window.location.href = url, 200);
                });
            });
        }

        // Add new function for back button
        function initializeNavigation() {
            document.getElementById('backButton').addEventListener('click', () => {
                history.back();
            });
        }

        // Enhanced Rich Text Editor with Quill.js
        function initializeNotesEditor() {
            const addNotesBtn = document.getElementById('addNotesBtn');
            const notesWrapper = document.getElementById('notesWrapper');
            const saveNotesBtn = document.getElementById('saveNotesBtn');
            const notesTitleModalOverlay = document.getElementById('notesTitleModalOverlay');
            const notesTitleModalClose = document.getElementById('notesTitleModalClose');
            const notesTitleModalSkip = document.getElementById('notesTitleModalSkip');
            const notesTitleModalContinue = document.getElementById('notesTitleModalContinue');
            const notesTitleInput = document.getElementById('notesTitleInput');
            const notesTitleSuggestionBtns = document.querySelectorAll('.notes-title-suggestion-btn');
            const notesEducationModalOverlay = document.getElementById('notesEducationModalOverlay');
            const notesEducationModalClose = document.getElementById('notesEducationModalClose');
            const notesEducationModalGotIt = document.getElementById('notesEducationModalGotIt');
            let notesQuill = null;
            let isEditorOpen = false;
            let hasExistingNotes = false;
            let hasCustomTitle = false;
            let hasSeenTitleModal = localStorage.getItem('notesTitleModalShown') === 'true';
            let hasShownEducationModal = localStorage.getItem('notesEducationModalShown') === 'true';
            let pendingTitle = null;

            // Update button appearance based on state
            function updateButtonState() {
                if (isEditorOpen) {
                    addNotesBtn.innerHTML = `
                        Close notes editor
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    `;
                    addNotesBtn.classList.add('active');
                } else {
                    const buttonText = hasExistingNotes ? 'Edit notes' : 'Add notes';
                    addNotesBtn.innerHTML = `
                        ${buttonText}
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    `;
                    addNotesBtn.classList.remove('active');
                }
            }

            // Load existing notes from server
            function loadExistingNotes() {
                const lessonId = '{{ $lesson["id"] ?? "" }}';
                if (!lessonId) return Promise.resolve(null);

                return fetch(`/dashboard/lesson/${lessonId}/user-notes`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.note) {
                        hasExistingNotes = true;
                        hasCustomTitle = data.note.title !== defaultNoteTitle;
                        return data.note;
                    }
                    hasExistingNotes = false;
                    hasCustomTitle = false;
                    return null;
                })
                .catch(error => {
                    console.error('Error loading existing notes:', error);
                    hasExistingNotes = false;
                    return null;
                });
            }

            // Initialize Quill editor
            function initNotesEditor() {
                if (notesQuill) {
                    // Editor already exists, just show it
                    notesWrapper.style.display = 'block';
                    return Promise.resolve();
                }

                return new Promise((resolve) => {
                    // Initialize Quill with a simple toolbar
                    notesQuill = new Quill('#notes-editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        },
                        placeholder: 'Write your notes here...',
                        bounds: '#notes-editor-container'
                    });

                    console.log('Quill editor initialized');

                    // Load existing notes into the editor
                    loadExistingNotes().then(existingNote => {
                        if (existingNote && existingNote.content) {
                            notesQuill.root.innerHTML = existingNote.content;
                            hasExistingNotes = true;
                            // Set the title in the title input
                            const titleInput = document.querySelector('.notes-title-input');
                            if (titleInput && existingNote.title) {
                                titleInput.value = existingNote.title;
                            }
                        } else {
                            hasExistingNotes = false;
                            // Set default title if creating new notes
                            if (pendingTitle) {
                                const titleInput = document.querySelector('.notes-title-input');
                                if (titleInput) {
                                    titleInput.value = pendingTitle;
                                }
                                pendingTitle = null;
                            }
                        }
                        updateButtonState();
                        resolve();
                    });
                });
            }

            // Destroy Quill editor
            function destroyNotesEditor() {
                if (notesQuill) {
                    // Get the container element
                    const editorContainer = document.getElementById('notes-editor');
                    if (editorContainer) {
                        // Remove all child elements (Quill adds its own structure)
                        while (editorContainer.firstChild) {
                            editorContainer.removeChild(editorContainer.firstChild);
                        }
                        // Reset the container
                        editorContainer.innerHTML = '<div></div>';
                    }
                    notesQuill = null;
                }
            }

            // Clear all notes
            function clearAllNotes() {
                if (notesQuill) {
                    notesQuill.setContents([]);
                    hasExistingNotes = false;
                    updateButtonState();
                    showSuccessMessage('Notes cleared. You can now start fresh!');
                }
            }

            // Show title modal
            function showTitleModal() {
                notesTitleInput.value = '';
                notesTitleModalOverlay.classList.add('active');
                setTimeout(() => notesTitleInput.focus(), 300);
                // Mark as seen
                localStorage.setItem('notesTitleModalShown', 'true');
                hasSeenTitleModal = true;
            }

            // Hide title modal
            function hideTitleModal() {
                notesTitleModalOverlay.classList.remove('active');
            }

            // Show education modal
            function showEducationModal() {
                if (!hasShownEducationModal) {
                    notesEducationModalOverlay.classList.add('active');
                    hasShownEducationModal = true;
                    localStorage.setItem('notesEducationModalShown', 'true');
                }
            }

            // Hide education modal
            function hideEducationModal() {
                notesEducationModalOverlay.classList.remove('active');
            }

            // Toggle editor visibility
            function toggleNotesEditor() {
                isEditorOpen = !isEditorOpen;

                if (isEditorOpen) {
                    if (hasExistingNotes && hasCustomTitle) {
                        // Show editor directly for existing notes with custom title
                        initNotesEditor().then(() => {
                            notesWrapper.style.display = 'block';
                            // Show education modal for first-time users
                            setTimeout(() => {
                                showEducationModal();
                            }, 500);
                        });
                    } else if (!hasSeenTitleModal) {
                        // Show title modal for first time
                        showTitleModal();
                    } else {
                        // Show editor, user has seen modal before
                        initNotesEditor().then(() => {
                            notesWrapper.style.display = 'block';
                            // Show education modal for first-time users
                            setTimeout(() => {
                                showEducationModal();
                            }, 500);
                        });
                    }
                } else {
                    // Hide editor
                    notesWrapper.style.display = 'none';
                    // Don't destroy the editor, just hide it
                    // This preserves the content for next time
                }

                updateButtonState();
            }

            // Handle title modal suggestion buttons
            notesTitleSuggestionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const suggestedTitle = this.dataset.title;
                    notesTitleInput.value = suggestedTitle;
                });
            });

            // Handle title modal skip button
            notesTitleModalSkip.addEventListener('click', function() {
                pendingTitle = '{{ Str::slug($lesson["title"] ?? "notes") }}'; // Use slug as fallback
                hideTitleModal();
                proceedToEditor();
            });

            // Handle title modal continue button
            notesTitleModalContinue.addEventListener('click', function() {
                const title = notesTitleInput.value.trim();
                if (title) {
                    pendingTitle = title;
                } else {
                    pendingTitle = '{{ Str::slug($lesson["title"] ?? "notes") }}'; // Use slug as fallback
                }
                hideTitleModal();
                proceedToEditor();
            });

            // Proceed to editor after title selection
            function proceedToEditor() {
                isEditorOpen = true;
                initNotesEditor().then(() => {
                    notesWrapper.style.display = 'block';
                    // Show education modal for first-time users
                    setTimeout(() => {
                        showEducationModal();
                    }, 500);
                    // Focus on the editor after modal interaction
                    setTimeout(() => {
                        if (notesQuill && !hasShownEducationModal) {
                            notesQuill.focus();
                        }
                    }, 100);
                });
                updateButtonState();
            }

            // Handle title modal close button
            notesTitleModalClose.addEventListener('click', function() {
                hideTitleModal();
                // Don't proceed to editor if cancelled
                isEditorOpen = false;
                updateButtonState();
            });

            // Close title modal on overlay click
            notesTitleModalOverlay.addEventListener('click', function(e) {
                if (e.target === notesTitleModalOverlay) {
                    hideTitleModal();
                    isEditorOpen = false;
                    updateButtonState();
                }
            });

            // Close title modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && notesTitleModalOverlay.classList.contains('active')) {
                    hideTitleModal();
                    isEditorOpen = false;
                    updateButtonState();
                }
            });

            // Initialize button state
            updateButtonState();

            // Handle button click
            addNotesBtn.addEventListener('click', toggleNotesEditor);

            // Handle education modal events
            if (notesEducationModalClose) {
                notesEducationModalClose.addEventListener('click', hideEducationModal);
            }

            if (notesEducationModalGotIt) {
                notesEducationModalGotIt.addEventListener('click', function() {
                    hideEducationModal();
                    // Focus on the editor after closing modal
                    setTimeout(() => {
                        if (notesQuill) {
                            notesQuill.focus();
                        }
                    }, 300);
                });
            }

            // Close modal when clicking overlay
            if (notesEducationModalOverlay) {
                notesEducationModalOverlay.addEventListener('click', function(e) {
                    if (e.target === notesEducationModalOverlay) {
                        hideEducationModal();
                        // Focus on the editor after closing modal
                        setTimeout(() => {
                            if (notesQuill) {
                                notesQuill.focus();
                            }
                        }, 300);
                    }
                });
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && notesEducationModalOverlay.classList.contains('active')) {
                    hideEducationModal();
                    // Focus on the editor after closing modal
                    setTimeout(() => {
                        if (notesQuill) {
                            notesQuill.focus();
                        }
                    }, 300);
                }
            });

            // Add clear all button to the notes editor actions
            const notesEditorActions = document.querySelector('.notes-editor-actions');
            if (notesEditorActions) {
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'notes-action-btn clear';
                clearBtn.innerHTML = `
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Clear All
                `;
                clearBtn.addEventListener('click', clearAllNotes);
                notesEditorActions.appendChild(clearBtn);
            }

            // Save notes handler
            saveNotesBtn.addEventListener('click', () => {
                if (!notesQuill) {
                    alert('Please open the notes editor first.');
                    return;
                }

                const content = notesQuill.root.innerHTML;
                const text = notesQuill.getText().trim();
                const titleInput = document.querySelector('.notes-title-input');
                const title = titleInput ? titleInput.value.trim() : '';

                if (!text) {
                    alert('Please write some notes before saving.');
                    return;
                }

                // Show loading state
                const originalText = saveNotesBtn.textContent;
                saveNotesBtn.innerHTML = '<div class="loading-spinner"></div> Saving...';
                saveNotesBtn.disabled = true;

                // Get selected update mode
                const updateMode = document.querySelector('input[name="update_mode"]:checked').value;

                fetch('/dashboard/lesson/{{ $lesson["id"] ?? "" }}/user-notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        title: title || '{{ Str::slug($lesson["title"] ?? "notes") }}', // Use title or fallback to slug
                        content: content,
                        update_mode: updateMode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hasExistingNotes = true; // Now we have notes saved
                        updateButtonState();
                        showSuccessMessage('Notes saved successfully!');
                        // Optionally hide the editor after saving
                        // toggleNotesEditor();
                    } else {
                        // Handle validation errors
                        if (data.errors && data.errors.title) {
                            alert('Title Error: ' + data.errors.title);
                        } else {
                            alert('Error saving notes: ' + (data.message || 'Unknown error'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving notes:', error);
                    alert('Failed to save notes. Please try again.');
                })
                .finally(() => {
                    // Restore button state
                    saveNotesBtn.textContent = originalText;
                    saveNotesBtn.disabled = false;
                });
            });

            // Clean up on page unload (optional)
            window.addEventListener('beforeunload', () => {
                if (notesQuill) {
                    // Save notes automatically before leaving?
                    // You could add auto-save functionality here
                }
            });
        }

        // Share Modal Functionality
        function initializeShareModal() {
            const shareModalOverlay = document.getElementById('shareModalOverlay');
            const shareModalClose = document.getElementById('shareModalClose');
            const shareUrlInput = document.getElementById('shareUrlInput');
            const shareCopyBtn = document.getElementById('shareCopyBtn');
            const shareButtons = document.querySelectorAll('.action-btn-secondary, .share-header-btn');

            function openShareModal() {
                shareModalOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Set the current page URL
                shareUrlInput.value = window.location.href;
            }

            function closeShareModal() {
                shareModalOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            // Open modal when share buttons are clicked
            shareButtons.forEach(button => {
                if (button.textContent.toLowerCase().includes('share')) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        openShareModal();
                    });
                }
            });

            // Close modal events
            shareModalClose.addEventListener('click', closeShareModal);
            shareModalOverlay.addEventListener('click', function(e) {
                if (e.target === shareModalOverlay) {
                    closeShareModal();
                }
            });

            // Keyboard close
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && shareModalOverlay.classList.contains('active')) {
                    closeShareModal();
                }
            });

            // Copy URL functionality
            shareCopyBtn.addEventListener('click', function() {
                shareUrlInput.select();
                shareUrlInput.setSelectionRange(0, 99999); // For mobile devices
                
                try {
                    document.execCommand('copy');
                    
                    // Show success feedback
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    this.classList.add('copied');
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy: ', err);
                }
            });

            // Social platform sharing
            const sharePlatforms = document.querySelectorAll('.share-platform');
            sharePlatforms.forEach(platform => {
                platform.addEventListener('click', function() {
                    const platformType = this.dataset.platform;
                    const url = encodeURIComponent(shareUrlInput.value);
                    const title = encodeURIComponent(document.title);
                    
                    let shareUrl = '';
                    
                    switch(platformType) {
                        case 'whatsapp':
                            shareUrl = `https://wa.me/?text=${title}%20${url}`;
                            break;
                        case 'facebook':
                            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                            break;
                        case 'twitter':
                            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                            break;
                        case 'email':
                            shareUrl = `mailto:?subject=${title}&body=${url}`;
                            break;
                        case 'instagram':
                            // Instagram doesn't support direct URL sharing, so we'll copy to clipboard
                            shareUrlInput.select();
                            document.execCommand('copy');
                            alert('Link copied! You can now paste it in Instagram.');
                            return;
                    }
                    
                    if (shareUrl) {
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                });
            });
        }

        // Lesson data is now handled within the video progress tracking functions

        // Enhanced save lesson functionality
        function initializeSaveLesson() {
            const saveButton = document.querySelector('.action-btn-primary');

            if (!saveButton || !saveButton.textContent.includes('Save Lesson')) {
                console.log('Save button not found or not visible');
                return;
            }

            const lessonId = '{{ $lesson["id"] ?? "" }}';

            // Validate lesson ID exists
            if (!lessonId) {
                console.error('No lesson ID available for save functionality');
                saveButton.style.display = 'none';
                return;
            }

            console.log('Save lesson functionality initialized for lesson:', lessonId);

            // Check saved status on page load
            fetch(`/dashboard/lesson/${lessonId}/check-saved`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Save status check response:', data);
                updateSaveButton(saveButton, data.saved);
            })
            .catch(error => console.error('Error checking save status:', error));

            // Add click event listener
            saveButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Save button clicked');

                const isSaved = this.dataset.saved === 'true';
                const originalContent = this.innerHTML;

                // Show loading state
                this.innerHTML = '<div class="loading-spinner"></div> ' + (isSaved ? 'Removing...' : 'Saving...');
                this.disabled = true;

                const url = isSaved ?
                    `/dashboard/lesson/${lessonId}/unsave` :
                    `/dashboard/lesson/${lessonId}/save`;

                const method = isSaved ? 'DELETE' : 'POST';

                // Prepare lesson data from page
                const lessonData = {
                    lesson_title: "{{ $lesson['title'] ?? 'Unknown Lesson' }}",
                    lesson_subject: "{{ $lesson['subject'] ?? 'General' }}",
                    lesson_instructor: "{{ $lesson['instructor'] ?? 'Unknown' }}",
                    lesson_year: "{{ $lesson['year'] ?? date('Y') }}",
                    lesson_duration: "{{ $lesson['total_duration'] ?? 300 }}",
                    lesson_thumbnail: "{{ $lesson['thumbnail'] ?? '' }}",
                    lesson_video_url: "{{ $lesson['video_url'] ?? '' }}",
                    selected_level: "{{ $selectedLevel ?? 'primary-lower' }}"
                };

                console.log('Saving lesson with data:', lessonData);

                const requestData = isSaved ? {} : lessonData;

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    console.log('Save response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Save response data:', data);
                    if (data.success) {
                        updateSaveButton(this, data.saved);
                        showSuccessMessage(data.message || (isSaved ? 'Lesson removed from saved' : 'Lesson saved successfully'));
                    } else {
                        this.innerHTML = originalContent;
                        this.disabled = false;
                        alert(data.message || 'An error occurred while saving');
                    }
                })
                .catch(error => {
                    console.error('Save lesson error:', error);
                    this.innerHTML = originalContent;
                    this.disabled = false;
                    alert('Failed to save lesson. Please try again. Error: ' + error.message);
                });
            });
        }

        function updateSaveButton(button, isSaved) {
            if (isSaved) {
                button.innerHTML = `
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    Saved
                `;
                button.style.backgroundColor = '#10b981';
                button.dataset.saved = 'true';
            } else {
                button.innerHTML = `
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    Save Lesson
                `;
                button.style.backgroundColor = 'var(--primary-red)';
                button.dataset.saved = 'false';
            }
            button.disabled = false;
        }

        // Enhanced search functionality
        function initializeSearch() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const query = this.value.trim();
                        if (query.length > 2) {
                            console.log('Searching for:', query);
                            // Implement search functionality here
                        }
                    }, 300);
                });
            }
        }

        // Keyboard shortcuts
        function initializeKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + S to save notes (when notes editor is open)
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    const notesWrapper = document.getElementById('notesWrapper');
                    const saveNotesBtn = document.getElementById('saveNotesBtn');
                    if (notesWrapper && notesWrapper.style.display !== 'none' && saveNotesBtn) {
                        saveNotesBtn.click();
                        showSuccessMessage('Notes saved with Ctrl+S! 💾');
                    }
                }

                // Ctrl/Cmd + E to export notes
                if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                    e.preventDefault();
                    if (!document.getElementById('notesEditorSection').classList.contains('hidden')) {
                        document.getElementById('exportNotesBtn').click();
                    }
                }

                // Ctrl/Cmd + N to toggle notes editor
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    document.getElementById('addNotesBtn').click();
                }
            });
        }

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                ${message}
            `;

            document.body.appendChild(successDiv);

            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        function showNoDocumentsMessage(type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'no-documents-message';
            messageDiv.innerHTML = `
                <i class="fas fa-info-circle"></i>
                No ${type.toLowerCase()} documents are currently attached to this video lesson.
            `;

            // Style the message
            messageDiv.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: var(--white);
                border: 2px solid var(--gray-200);
                border-radius: 1rem;
                padding: 2rem;
                box-shadow: var(--shadow-xl);
                z-index: 2000;
                max-width: 400px;
                text-align: center;
                font-size: 0.875rem;
                color: var(--gray-700);
                animation: slideIn 0.3s ease;
            `;

            // Add close button
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.style.cssText = `
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: none;
                border: none;
                cursor: pointer;
                color: var(--gray-500);
                font-size: 1.25rem;
            `;
            closeBtn.onclick = () => messageDiv.remove();

            messageDiv.appendChild(closeBtn);
            document.body.appendChild(messageDiv);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 5000);
        }

        // IndexedDB Notes Manager for offline-first functionality
        class NotesManager {
            constructor() {
                this.dbName = 'DigiLearnNotes';
                this.version = 1;
                this.db = null;
                this.lessonId = '{{ $lesson["id"] ?? "" }}';
                this.userId = '{{ auth()->id() }}';
                this.syncInterval = null;
                this.isOnline = navigator.onLine;
                this.pendingSyncs = new Set();
            }

            async init() {
                try {
                    this.db = await this.openDB();
                    this.setupOnlineOfflineListeners();
                    this.startPeriodicSync();
                    console.log('NotesManager initialized');
                } catch (error) {
                    console.error('Failed to initialize NotesManager:', error);
                    // Fallback to localStorage
                    this.useLocalStorage = true;
                }
            }

            async openDB() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(this.dbName, this.version);

                    request.onerror = () => {
                        console.warn('IndexedDB not available, falling back to localStorage');
                        this.useLocalStorage = true;
                        reject(new Error('IndexedDB not available'));
                    };

                    request.onsuccess = (event) => {
                        resolve(event.target.result);
                    };

                    request.onupgradeneeded = (event) => {
                        const db = event.target.result;

                        // Create notes store
                        if (!db.objectStoreNames.contains('notes')) {
                            const notesStore = db.createObjectStore('notes', { keyPath: 'id' });
                            notesStore.createIndex('lessonId', 'lessonId', { unique: false });
                            notesStore.createIndex('userId', 'userId', { unique: false });
                            notesStore.createIndex('lastModified', 'lastModified', { unique: false });
                        }

                        // Create sync queue store
                        if (!db.objectStoreNames.contains('syncQueue')) {
                            const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
                            syncStore.createIndex('lessonId', 'lessonId', { unique: false });
                            syncStore.createIndex('timestamp', 'timestamp', { unique: false });
                        }
                    };
                });
            }

            setupOnlineOfflineListeners() {
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    console.log('Back online - syncing notes');
                    this.syncWithServer();
                });

                window.addEventListener('offline', () => {
                    this.isOnline = false;
                    console.log('Gone offline - notes will be cached locally');
                });
            }

            startPeriodicSync() {
                // Sync every 30 seconds when online
                this.syncInterval = setInterval(() => {
                    if (this.isOnline && !this.useLocalStorage) {
                        this.syncWithServer();
                    }
                }, 30000);
            }

            async saveNoteLocally(noteData) {
                // Validate character limit
                const MAX_CHARS = 1000;
                const textContent = this.extractTextFromHTML(noteData.content || '');
                if (textContent.length > MAX_CHARS) {
                    throw new Error(`Note content exceeds ${MAX_CHARS} character limit. Current: ${textContent.length}`);
                }

                if (this.useLocalStorage) {
                    const key = `note_${this.lessonId}`;
                    localStorage.setItem(key, JSON.stringify({
                        ...noteData,
                        lastModified: Date.now(),
                        synced: false
                    }));
                    return;
                }

                const note = {
                    id: `${this.userId}_${this.lessonId}`,
                    userId: this.userId,
                    lessonId: this.lessonId,
                    title: noteData.title || '',
                    content: noteData.content || '',
                    lastModified: Date.now(),
                    synced: false,
                    version: Date.now() // For conflict resolution
                };

                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readwrite');
                    const store = transaction.objectStore('notes');
                    const request = store.put(note);

                    request.onsuccess = () => {
                        console.log('Note saved locally');
                        resolve(note);
                    };

                    request.onerror = () => {
                        console.error('Failed to save note locally');
                        reject(new Error('Failed to save locally'));
                    };
                });
            }

            extractTextFromHTML(html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                return tempDiv.textContent || tempDiv.innerText || '';
            }

            async loadNoteLocally() {
                if (this.useLocalStorage) {
                    const key = `note_${this.lessonId}`;
                    const data = localStorage.getItem(key);
                    return data ? JSON.parse(data) : null;
                }

                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readonly');
                    const store = transaction.objectStore('notes');
                    const request = store.get(`${this.userId}_${this.lessonId}`);

                    request.onsuccess = () => {
                        resolve(request.result || null);
                    };

                    request.onerror = () => {
                        reject(new Error('Failed to load note locally'));
                    };
                });
            }

            async syncWithServer() {
                if (!this.isOnline || this.useLocalStorage) return;

                try {
                    // Get all unsynced notes
                    const unsyncedNotes = await this.getUnsyncedNotes();

                    for (const note of unsyncedNotes) {
                        await this.syncNoteToServer(note);
                    }

                    // Load latest from server to check for conflicts
                    await this.loadFromServer();

                } catch (error) {
                    console.error('Sync failed:', error);
                }
            }

            async getUnsyncedNotes() {
                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readonly');
                    const store = transaction.objectStore('notes');
                    const index = store.index('lastModified');
                    const request = index.openCursor();
                    const unsynced = [];

                    request.onsuccess = (event) => {
                        const cursor = event.target.result;
                        if (cursor) {
                            if (!cursor.value.synced) {
                                unsynced.push(cursor.value);
                            }
                            cursor.continue();
                        } else {
                            resolve(unsynced);
                        }
                    };

                    request.onerror = () => reject(new Error('Failed to get unsynced notes'));
                });
            }

            async syncNoteToServer(note) {
                try {
                    const response = await fetch(`/dashboard/lesson/${this.lessonId}/user-notes`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            title: note.title,
                            content: note.content
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Mark as synced
                        await this.markNoteSynced(note.id);
                        console.log('Note synced to server');
                    }
                } catch (error) {
                    console.error('Failed to sync note:', error);
                    // Will retry on next sync
                }
            }

            async markNoteSynced(noteId) {
                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readwrite');
                    const store = transaction.objectStore('notes');
                    const request = store.get(noteId);

                    request.onsuccess = () => {
                        const note = request.result;
                        if (note) {
                            note.synced = true;
                            const updateRequest = store.put(note);
                            updateRequest.onsuccess = () => resolve();
                            updateRequest.onerror = () => reject(new Error('Failed to mark synced'));
                        } else {
                            resolve();
                        }
                    };

                    request.onerror = () => reject(new Error('Failed to get note for sync'));
                });
            }

            async loadFromServer() {
                try {
                    const response = await fetch(`/dashboard/lesson/${this.lessonId}/user-notes`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.success && data.note) {
                        const serverNote = data.note;
                        const localNote = await this.loadNoteLocally();

                        // Check for conflicts
                        if (localNote && localNote.lastModified > new Date(serverNote.updated_at).getTime()) {
                            // Local is newer, keep local but mark for sync
                            console.log('Local note is newer, keeping local version');
                        } else {
                            // Server is newer or same, update local
                            await this.saveNoteLocally({
                                title: serverNote.title || '',
                                content: serverNote.content || '',
                                synced: true
                            });
                            console.log('Updated local note from server');
                        }
                    }
                } catch (error) {
                    console.error('Failed to load from server:', error);
                }
            }

            destroy() {
                if (this.syncInterval) {
                    clearInterval(this.syncInterval);
                }
                if (this.db) {
                    this.db.close();
                }
            }
        }

        // Course tabs functionality
        function initializeCourseTabs() {
            console.log('testcoursecards')
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.dataset.tab;

                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Hide all tab contents
                    tabContents.forEach(content => content.classList.remove('active'));
                    // Show selected tab content
                    const targetContent = document.getElementById(tabName + '-tab');
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        }

        // Video progress tracking functionality
        let videoProgressTracker = null;

        // Initialize video progress tracking
        function initializeVideoProgressTracking() {
            console.log('=== Starting Video Progress Tracking Initialization ===');

            // Get lesson data from page - INCLUDING DURATION
            const lessonId = '{{ $lesson["id"] ?? "" }}';
            const lessonTitle = '{{ $lesson["title"] ?? "Unknown" }}';
            const lessonSubject = '{{ $lesson["subject"] ?? "General" }}';
            const lessonLevel = '{{ $lesson["level"] ?? "unknown" }}';
            const lessonLevelGroup = '{{ $lesson["level_group"] ?? "primary-lower" }}';
            
            // CRITICAL: Get actual duration from backend
            const totalDuration = parseInt('{{ $lesson["total_duration"] ?? 300 }}') || 300;
            
            console.log('Lesson Data with Duration:', {
                lessonId,
                lessonTitle,
                lessonSubject,
                lessonLevel,
                lessonLevelGroup,
                totalDuration
            });

            if (!lessonId) {
                console.warn('No lesson ID found - skipping progress tracking');
                return;
            }

            // Find video element
            let videoElement = null;
            const selectors = [
                '.video-container video',
                '.video-container iframe',
                '.video-player video',
                '.video-player iframe',
                'video[controls]',
                'iframe[src*="youtube.com"]',
                'iframe[src*="vimeo.com"]',
                'iframe[src*="mux.com"]'
            ];

            for (const selector of selectors) {
                const found = document.querySelector(selector);
                if (found) {
                    videoElement = found;
                    console.log('Found video element with selector:', selector);
                    break;
                }
            }

            if (!videoElement) {
                console.warn('No video element found with any selector');
                return;
            }

            console.log('Video element found:', {
                tagName: videoElement.tagName,
                src: videoElement.src || videoElement.getAttribute('src'),
                type: videoElement.getAttribute('type') || 'iframe'
            });

            // Initialize tracker with proper error handling
            try {
                const lessonData = {
                    id: lessonId,
                    title: lessonTitle,
                    subject: lessonSubject,
                    level: lessonLevel,
                    level_group: lessonLevelGroup
                };

                videoProgressTracker = new VideoProgressTracker(
                    videoElement,
                    lessonId,
                    lessonData,
                    totalDuration  // Pass actual duration here
                );
                videoProgressTracker.init();
                console.log('VideoProgressTracker initialized successfully');
            } catch (error) {
                console.error('Failed to initialize VideoProgressTracker:', error);
            }
        }

        class VideoProgressTracker {
            constructor(videoElement, lessonId, lessonData, providedDuration) {
                this.videoElement = videoElement;
                this.lessonId = lessonId;
                this.lessonData = lessonData;
                this.watchTime = 0;
                this.lastUpdateTime = null;
                this.totalDuration = providedDuration || 300; // Use provided duration
                this.progressInterval = null;
                this.isTracking = false;
                this.isPlaying = false;
                this.lastReportedProgress = 0;
                this.csrfToken = this.getCsrfToken();
                this.reportThreshold = 10; // Report every 10 seconds
                this.lastReportTime = 0;
                this.youtubePlayer = null; // For YouTube API player instance

                console.log('VideoProgressTracker constructor:', {
                    videoElement: this.videoElement.tagName,
                    lessonId: this.lessonId,
                    providedDuration: providedDuration,
                    totalDuration: this.totalDuration,
                    csrfTokenAvailable: !!this.csrfToken
                });
            }

            getCsrfToken() {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) {
                    console.warn('CSRF token not found in meta tags');
                }
                return token;
            }

            init() {
                console.log('Initializing VideoProgressTracker with duration:', this.totalDuration);

                if (this.videoElement.tagName === 'VIDEO') {
                    console.log('Detected HTML5 video element');
                    this.initHTML5Video();
                } else if (this.videoElement.tagName === 'IFRAME') {
                    console.log('Detected iframe element');
                    this.initIframeVideo();
                    // IMPORTANT: Don't call startPeriodicTracking() here!
                    // It will be called only when video actually starts playing
                }

                // Track page visibility
                document.addEventListener('visibilitychange', () => {
                    console.log('Page visibility changed:', {
                        hidden: document.hidden,
                        isPlaying: this.isPlaying
                    });

                    if (document.hidden) {
                        this.pauseTracking();
                    } else if (this.isPlaying) {
                        this.resumeTracking();
                    }
                });

                // Report progress before unload
                window.addEventListener('beforeunload', () => {
                    console.log('Page unloading - reporting final progress');
                    if (this.watchTime > 0) {
                        this.reportProgress(true);
                    }
                });

                console.log('VideoProgressTracker initialization complete');
            }

            initHTML5Video() {
                console.log('Setting up HTML5 video tracking');

                this.videoElement.addEventListener('loadedmetadata', () => {
                    const elementDuration = this.videoElement.duration;
                    console.log('Video metadata loaded:', {
                        elementDuration: elementDuration,
                        providedDuration: this.totalDuration,
                        usingDuration: elementDuration || this.totalDuration
                    });

                    // Use element duration if available and different from provided
                    if (elementDuration && elementDuration > 0) {
                        this.totalDuration = elementDuration;
                    }
                });

                // ONLY track on actual play event
                this.videoElement.addEventListener('play', () => {
                    console.log('✓ Video started playing - STARTING TRACKER');
                    this.isPlaying = true;
                    this.startTracking();
                });

                this.videoElement.addEventListener('pause', () => {
                    console.log('Video paused - STOPPING TRACKER');
                    this.isPlaying = false;
                    this.pauseTracking();
                    this.reportProgress();
                });

                this.videoElement.addEventListener('ended', () => {
                    console.log('Video ended - reporting final progress');
                    this.isPlaying = false;
                    this.pauseTracking();
                    this.reportProgress(true);
                });

                this.videoElement.addEventListener('timeupdate', () => {
                    if (this.isTracking && this.isPlaying) {
                        const currentTime = Date.now();

                        if (this.lastUpdateTime) {
                            const timeDiff = (currentTime - this.lastUpdateTime) / 1000;
                            this.watchTime += timeDiff;
                        }

                        this.lastUpdateTime = currentTime;

                        // Report periodically
                        if (currentTime - this.lastReportTime >= (this.reportThreshold * 1000)) {
                            this.reportProgress();
                            this.lastReportTime = currentTime;
                        }
                    }
                });
            }

            initIframeVideo() {
                console.log('Setting up iframe video tracking with duration:', this.totalDuration);

                const iframeSrc = this.videoElement.getAttribute('src') || '';

                console.log('Iframe source detected:', iframeSrc);

                if (iframeSrc.includes('vimeo.com')) {
                    this.initVimeoTracking();
                } else if (iframeSrc.includes('youtube.com') || iframeSrc.includes('youtu.be')) {
                    this.initYouTubeTracking();
                } else if (iframeSrc.includes('mux.com')) {
                    this.initMuxTracking();
                } else {
                    console.warn('Unknown iframe video source, using periodic tracking');
                    this.initGenericIframeTracking();
                }
            }

            initVimeoTracking() {
                console.log('Setting up Vimeo API tracking');

                try {
                    // Use Vimeo Player API
                    const player = new Vimeo.Player(this.videoElement);

                    player.on('play', () => {
                        console.log('✓ Vimeo video PLAYING - STARTING TRACKER');
                        this.isPlaying = true;
                        this.startTracking();
                    });

                    player.on('pause', () => {
                        console.log('Vimeo video PAUSED - STOPPING TRACKER');
                        this.isPlaying = false;
                        this.pauseTracking();
                        this.reportProgress();
                    });

                    player.on('ended', () => {
                        console.log('Vimeo video ENDED - reporting final progress');
                        this.isPlaying = false;
                        this.pauseTracking();
                        this.reportProgress(true);
                    });

                    player.on('timeupdate', (data) => {
                        if (this.isTracking && this.isPlaying) {
                            const currentTime = Date.now();

                            if (this.lastUpdateTime) {
                                const timeDiff = (currentTime - this.lastUpdateTime) / 1000;
                                this.watchTime += timeDiff;
                            }

                            this.lastUpdateTime = currentTime;

                            // Report periodically
                            if (currentTime - this.lastReportTime >= (this.reportThreshold * 1000)) {
                                console.log('Vimeo timeupdate:', {
                                    currentSeconds: data.seconds,
                                    duration: data.duration,
                                    watchTime: this.watchTime
                                });
                                this.reportProgress();
                                this.lastReportTime = currentTime;
                            }
                        }
                    });

                    // Get actual duration from Vimeo
                    player.getDuration().then(duration => {
                        console.log('Vimeo actual duration:', duration);
                        this.totalDuration = duration;
                    }).catch(error => {
                        console.error('Error getting Vimeo duration:', error);
                    });

                } catch (error) {
                    console.error('Vimeo API initialization failed:', error);
                    console.log('Falling back to periodic tracking');
                    this.initGenericIframeTracking();
                }
            }

            initYouTubeTracking() {
                console.log('Setting up YouTube IFrame API tracking');

                // Extract video ID from iframe src
                const src = this.videoElement.getAttribute('src') || '';
                const videoIdMatch = src.match(/embed\/([^?&]+)/);
                const videoId = videoIdMatch ? videoIdMatch[1] : null;

                if (!videoId) {
                    console.error('Could not extract YouTube video ID');
                    this.initGenericIframeTracking();
                    return;
                }

                console.log('YouTube video ID:', videoId);

                // CRITICAL: Replace the iframe with a div container for YouTube API
                const videoContainer = this.videoElement.parentElement;
                const playerDiv = document.createElement('div');
                playerDiv.id = 'youtube-player-' + videoId; // Unique ID for this player
                playerDiv.style.width = '100%';
                playerDiv.style.height = '100%';
                playerDiv.style.position = 'absolute';
                playerDiv.style.top = '0';
                playerDiv.style.left = '0';

                // Replace the iframe with the div
                this.videoElement.parentElement.replaceChild(playerDiv, this.videoElement);
                console.log('Replaced iframe with player div');

                // Wait for YouTube IFrame API to be ready
                const checkYTReady = () => {
                    if (typeof YT !== 'undefined' && YT.Player) {
                        console.log('YouTube API ready, creating player');
                        this.createYouTubePlayer(videoId, playerDiv.id);
                    } else {
                        console.log('Waiting for YouTube API...');
                        setTimeout(checkYTReady, 100);
                    }
                };

                checkYTReady();
            }

            createYouTubePlayer(videoId, containerId) {
                console.log('Creating YouTube player for video:', videoId, 'in container:', containerId);

                try {
                    const player = new YT.Player(containerId, {
                        width: '100%',
                        height: '100%',
                        videoId: videoId, // Use videoId parameter instead of relying on iframe
                        playerVars: {
                            autoplay: 0, // Don't autoplay
                            controls: 1,
                            modestbranding: 1,
                            rel: 0,
                            showinfo: 0,
                            fs: 1,
                            iv_load_policy: 3
                        },
                        events: {
                            'onReady': (event) => {
                                console.log('✓ YouTube player ready');
                                // Get actual duration
                                const duration = event.target.getDuration();
                                if (duration > 0) {
                                    this.totalDuration = duration;
                                    console.log('YouTube actual duration:', duration);
                                }
                            },
                            'onStateChange': (event) => {
                                // -1 (unstarted), 0 (ended), 1 (playing), 2 (paused), 3 (buffering), 5 (cued)
                                const state = event.data;
                                const stateNames = {
                                    '-1': 'unstarted',
                                    '0': 'ended',
                                    '1': 'playing',
                                    '2': 'paused',
                                    '3': 'buffering',
                                    '5': 'cued'
                                };

                                console.log('YouTube state changed to:', stateNames[state] + ' (' + state + ')');

                                if (state === YT.PlayerState.PLAYING) {
                                    console.log('✓ YouTube video PLAYING - STARTING TRACKER');
                                    this.isPlaying = true;
                                    this.startTracking();
                                } else if (state === YT.PlayerState.PAUSED) {
                                    console.log('YouTube video PAUSED - STOPPING TRACKER');
                                    this.isPlaying = false;
                                    this.pauseTracking();
                                    this.reportProgress();
                                } else if (state === YT.PlayerState.ENDED) {
                                    console.log('YouTube video ENDED - reporting final progress');
                                    this.isPlaying = false;
                                    this.pauseTracking();
                                    this.reportProgress(true);
                                }
                            },
                            'onError': (event) => {
                                console.error('YouTube player error:', event.data);
                                // Fallback to generic tracking on error
                                this.initGenericIframeTracking();
                            }
                        }
                    });

                    // Store player instance for periodic checks
                    this.youtubePlayer = player;

                    // Add periodic progress check for YouTube
                    setInterval(() => {
                        if (this.youtubePlayer && this.isPlaying && this.isTracking) {
                            try {
                                const currentTime = this.youtubePlayer.getCurrentTime();
                                const duration = this.youtubePlayer.getDuration();

                                if (duration > 0) {
                                    this.totalDuration = duration;
                                }

                                const currentTime_Date = Date.now();

                                if (this.lastUpdateTime) {
                                    const timeDiff = (currentTime_Date - this.lastUpdateTime) / 1000;
                                    this.watchTime += timeDiff;
                                }

                                this.lastUpdateTime = currentTime_Date;

                                // Report periodically
                                if (currentTime_Date - this.lastReportTime >= (this.reportThreshold * 1000)) {
                                    console.log('YouTube progress:', {
                                        currentSeconds: Math.round(currentTime),
                                        duration: Math.round(duration),
                                        watchTime: this.watchTime,
                                        completion: ((this.watchTime / duration) * 100).toFixed(1) + '%'
                                    });
                                    this.reportProgress();
                                    this.lastReportTime = currentTime_Date;
                                }
                            } catch (error) {
                                console.error('Error updating YouTube progress:', error);
                            }
                        }
                    }, 1000); // Check every second

                    console.log('YouTube player created successfully');

                } catch (error) {
                    console.error('YouTube player initialization failed:', error);
                    console.log('Falling back to generic iframe tracking');
                    this.initGenericIframeTracking();
                }
            }

            initMuxTracking() {
                console.log('Setting up Mux tracking');

                // Mux uses standard video events if loaded correctly
                // Check if it has a video element inside
                const innerVideo = this.videoElement.querySelector('video');

                if (innerVideo) {
                    // Use HTML5 tracking for the inner video
                    this.videoElement = innerVideo;
                    this.initHTML5Video();
                } else {
                    console.log('Mux iframe without inner video, using periodic tracking');
                    this.initGenericIframeTracking();
                }
            }

            initGenericIframeTracking() {
                console.log('Setting up generic iframe tracking (periodic method)');

                // For unknown iframe sources, use periodic tracking
                // but only when page is visible and user has interacted
                let isUserFocused = false;

                this.videoElement.addEventListener('mouseenter', () => {
                    isUserFocused = true;
                    if (!this.isPlaying) {
                        console.log('User focused on video iframe');
                        this.isPlaying = true;
                        this.startTracking();
                        this.startPeriodicTracking();
                    }
                });

                this.videoElement.addEventListener('mouseleave', () => {
                    isUserFocused = false;
                });

                // Detect via page focus (user clicking in iframe triggers focus)
                this.videoElement.addEventListener('focus', () => {
                    if (!this.isPlaying) {
                        console.log('Video iframe gained focus - assuming playback');
                        this.isPlaying = true;
                        this.startTracking();
                        this.startPeriodicTracking();
                    }
                });

                // Listen for visibility changes
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden && this.isPlaying && !this.progressInterval) {
                        this.startPeriodicTracking();
                    }
                });
            }

            startPeriodicTracking() {
                // Only start if we've confirmed the video is playing
                if (!this.isPlaying) {
                    console.log('Not starting periodic tracking - video not playing yet');
                    return;
                }

                console.log('Starting periodic tracking with duration:', this.totalDuration);
                this.progressInterval = setInterval(() => {
                    if (!document.hidden && this.isPlaying) {
                        this.watchTime += 5;
                        console.log('Periodic watch time update:', {
                            watchTime: this.watchTime,
                            totalDuration: this.totalDuration,
                            isPlaying: this.isPlaying,
                            completion: ((this.watchTime / this.totalDuration) * 100).toFixed(1) + '%'
                        });
                        this.reportProgress();
                    } else if (document.hidden) {
                        console.log('Page hidden, pausing periodic tracking');
                        if (this.progressInterval) {
                            clearInterval(this.progressInterval);
                            this.progressInterval = null;
                        }
                    }
                }, 5000);
            }

            startTracking() {
                if (!this.isTracking && this.isPlaying) {
                    this.isTracking = true;
                    this.lastUpdateTime = Date.now();
                    this.lastReportTime = Date.now();
                    console.log('✓✓ Started tracking video progress with duration:', this.totalDuration);
                }
            }

            pauseTracking() {
                this.isTracking = false;
                console.log('Paused tracking video progress');
            }

            resumeTracking() {
                if (this.isPlaying && !this.isTracking) {
                    this.startTracking();
                    console.log('Resumed tracking video progress');
                }
            }

            async reportProgress(forceComplete = false) {
                if (this.watchTime < 1 && !forceComplete) {
                    return; // Don't report if less than 1 second watched
                }

                try {
                    const progressData = {
                        watch_time: Math.floor(this.watchTime),
                        total_duration: this.totalDuration, // Use actual duration
                        lesson_data: this.lessonData
                    };

                    console.log('Reporting progress to backend:', progressData);

                    const response = await fetch(`/dashboard/lesson/${this.lessonId}/progress`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(progressData)
                    });

                    const result = await response.json();

                    console.log('Progress report response:', result);

                    if (result.success) {
                        this.lastReportedProgress = result.completion_percentage || 0;
                        this.watchTime = 0; // Reset after successful report
                        this.lastUpdateTime = Date.now();

                        if (result.fully_completed) {
                            console.log('🎉 Lesson completed!');
                            if (typeof showSuccessMessage === 'function') {
                                showSuccessMessage('Lesson completed! 🎉');
                            }

                            // CRITICAL: Refresh progress dashboard after lesson completes
                            this.refreshProgressDashboard();
                        }
                    } else {
                        console.error('Failed to report progress:', result);
                    }
                } catch (error) {
                    console.error('Error reporting progress:', error);
                }
            }

            // Add this new method to refresh the dashboard
            refreshProgressDashboard() {
                console.log('Refreshing progress dashboard...');

                // Send signal to parent window if in iframe
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'PROGRESS_UPDATED',
                        lessonId: this.lessonId
                    }, '*');
                }

                // Also try direct refresh of my-progress page if it's open
                try {
                    fetch('/dashboard/my-progress/refresh', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Progress dashboard refresh successful');
                        // Trigger custom event for other pages to listen to
                        window.dispatchEvent(new CustomEvent('progressUpdated', { detail: data }));
                    })
                    .catch(error => console.error('Error refreshing dashboard:', error));
                } catch (error) {
                    console.error('Dashboard refresh error:', error);
                }
            }

            destroy() {
                this.pauseTracking();
                if (this.watchTime > 0) {
                    this.reportProgress(true);
                }
            }
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - initializing video progress tracking');
            initializeVideoProgressTracking();
        });

        // Also try to initialize after a short delay
        window.addEventListener('load', function() {
            console.log('Window loaded - checking video progress tracking');
            if (!videoProgressTracker) {
                setTimeout(() => {
                    initializeVideoProgressTracking();
                }, 1000);
            }
        });

        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endsection
