<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz Results - {{ trim(str_replace('Quiz for:', '', $quiz['title'] ?? 'Quiz')) }} - {{ config(
    'app.name',
    'ShoutOutGh'
) }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,701&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
    <script defer src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script type="module" nonce="{{ request()->attributes->get('csp_nonce') }}">
        import { renderMathInElement } from "https://unpkg.com/mathlive?module";
        window.renderMathInElement = renderMathInElement;
    </script>

    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Clean Student Math Display */
        math-field {
            font-size: 1.1rem;
            background: transparent;
            display: inline-block;
            border: none;
            outline: none;
            cursor: default;
            pointer-events: none;
        }

        math-field::part(virtual-keyboard-toggle),
        math-field::part(menu-toggle) {
            display: none !important;
        }

        :root {
            --primary-blue: #1D4ED8;
            --primary-blue-hover: #1E40AF;
            --primary-blue-light: #EFF6FF;
            --primary-blue-border: #BFDBFE;
            --success-green: #1D4ED8; /* Unified to Blue per palette requirements */
            --success-green-light: #EFF6FF;
            --error-red: #DC2626;
            --error-red-light: #FEF2F2;
            --error-red-border: #FECACA;
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
            --sidebar-width-expanded: 280px;
            --sidebar-width-collapsed: 80px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        [data-theme="dark"] {
            --bg-main: #000000;
            --bg-surface: #16181c;
            --text-main: #ffffff;
            --text-muted: #71767b;
            --border-color: #2f3336;
            --header-bg: rgba(22, 24, 28, 0.8);
            --filter-bg: rgba(22, 24, 28, 0.75);
            --accent: #DC2626;
            color-scheme: dark;

            /* Overrides for hardcoded grays */
            --gray-25: #000000;
            --gray-50: #16181c;
            --gray-100: #202327;
            --gray-200: #2f3336;
            --gray-300: #3e4144;
            --gray-400: #71767b;
            --gray-500: #8b98a5;
            --gray-600: #a4b1cd;
            --gray-700: #e2e8f0;
            --gray-800: #f1f5f9;
            --gray-900: #ffffff;
            --white: #16181c;
        }

        html {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: clip; /* Fix: 'clip' instead of 'hidden' preserves position: sticky! */
            -webkit-overflow-scrolling: touch;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
            /* iOS Safe Area Padding */
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Layout Container */
        .app-container {
            display: flex;
            min-height: 100vh;
            padding-top: 60px;
            /* Space for the fixed header */
        }

        /* YouTube-style Sidebar */
        .app-container .youtube-sidebar {
            width: var(--sidebar-width-expanded);
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            position: fixed;
            top: 0;
            margin-top: 60px;
            height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            z-index: 90;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            /* Safe Area support */
            padding-left: env(safe-area-inset-left);
        }

        .app-container .youtube-sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }


        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
            overflow-x: hidden;
            /* Prevent horizontal scroll */
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            padding: 0.5rem 1.5rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }

        .youtube-sidebar.collapsed .nav-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
            position: relative;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .youtube-sidebar.collapsed .nav-item {
            padding: 0.75rem;
            justify-content: center;
            gap: 0;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border-left: none;
        }

        .nav-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
            border-left-color: var(--gray-300);
        }

        .youtube-sidebar.collapsed .nav-item:hover {
            border-left-color: transparent;
        }

        .nav-item.active {
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
            border-left-color: var(--primary-blue);
            font-weight: 600;
        }

        .youtube-sidebar.collapsed .nav-item.active {
            border-left-color: transparent;
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .nav-item i {
            width: 20px;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .nav-item span {
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .nav-item span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Tooltip for collapsed state */
        .nav-item .tooltip {
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--gray-800);
            color: var(--white);
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 1001;
            pointer-events: none;
        }

        .youtube-sidebar.collapsed .nav-item:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .exit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-blue);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }

        .exit-btn:hover {
            background-color: var(--primary-blue-hover);
        }

        /* Main Content */
        .app-content {
            flex: 1;
            margin-left: var(--sidebar-width-expanded);
            padding: 2rem 3rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .youtube-sidebar.collapsed~.app-content {
            margin-left: var(--sidebar-width-collapsed);
        }

        .content-header {
            margin-bottom: 1.5rem;
        }

        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }

        .breadcrumbs .active {
            color: var(--primary-blue);
            font-weight: 500;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.35rem;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 0.95rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.25rem 1rem;
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            min-width: 140px;
        }

        .stat-circle {
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }

        .stat-circle svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .stat-circle .circle-bg {
            fill: none;
            stroke: var(--gray-200);
            stroke-width: 3.8;
        }

        .stat-circle .circle {
            fill: none;
            stroke: var(--primary-blue);
            stroke-width: 3.8;
            stroke-linecap: round;
        }

        .stat-circle .percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-icon-wrapper.success {
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
        }

        .stat-icon-wrapper.error {
            background-color: var(--error-red-light);
            color: var(--error-red);
        }

        .stat-icon-wrapper.warning {
            background-color: var(--gray-100);
            color: var(--gray-600);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            display: block;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-900);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Accuracy Card Specific Styles */
        .accuracy-card {
            text-align: left;
            align-items: flex-start;
            padding: 1.5rem 2rem;
            min-height: 140px;
        }

        .accuracy-card .stat-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .accuracy-card .accuracy-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .accuracy-card .accuracy-icon {
            font-size: 1.5rem;
            color: var(--primary-blue);
        }

        .accuracy-card .accuracy-value-wrapper {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .accuracy-card .accuracy-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-900);
        }

        .accuracy-card .accuracy-total {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-400);
        }

        .accuracy-card .accuracy-status {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .accuracy-card .skipped-text {
            font-size: 0.875rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        /* Question ReviewSection */
        .review-section {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .review-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .review-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .all-correct-badge {
            background-color: var(--success-green-light);
            color: var(--success-green);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        /* Question Carousel */
        .question-card {
            padding: 2.5rem;
            display: none;
        }

        .question-card.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .question-count {
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.correct {
            background-color: var(--success-green-light);
            color: var(--success-green);
        }

        .status-badge.incorrect {
            background-color: var(--error-red-light);
            color: var(--error-red);
        }

        .status-badge.skipped {
            background-color: var(--gray-100);
            color: var(--gray-600);
        }

        /* Image Container */
        .question-image-container {
            margin-bottom: 1.5rem;
            text-align: center;
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
            padding: 0.5rem;
            max-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .question-image {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-sm);
            transition: transform 0.3s ease;
        }

        .question-image:hover {
            transform: scale(1.01);
        }

        .question-text {
            font-size: 1.25rem;
            font-weight: 400;
            color: var(--gray-900);
            margin-bottom: 2rem;
            line-height: 1.4;
        }

        /* Table Styling */
        /* Inline Image Styling */
        .question-text img, .preamble-box img, .option-text img, .option-item img, .sample-answer-box img {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 0.5rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .question-text table, .preamble-box table, .option-text table, .option-item table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.95rem;
            background-color: var(--white);
            border: 1px solid var(--gray-300);
        }

        .question-text th, .preamble-box th, .option-text th, .option-item th {
            border: 1px solid var(--gray-300);
            padding: 0.75rem 1rem;
            text-align: left;
            background-color: var(--gray-100);
            font-weight: 600;
            color: var(--gray-800);
        }

        .question-text td, .preamble-box td, .option-text td, .option-item td {
            border: 1px solid var(--gray-300);
            padding: 0.75rem 1rem;
            color: var(--gray-700);
        }

        [data-theme="dark"] .question-text table,
        [data-theme="dark"] .preamble-box table,
        [data-theme="dark"] .option-text table,
        [data-theme="dark"] .option-item table {
            background-color: var(--gray-50);
            border-color: var(--gray-300);
        }

        [data-theme="dark"] .question-text th,
        [data-theme="dark"] .preamble-box th,
        [data-theme="dark"] .option-text th,
        [data-theme="dark"] .option-item th {
            background-color: var(--gray-100);
            border-color: var(--gray-300);
            color: var(--gray-800);
        }

        [data-theme="dark"] .question-text td,
        [data-theme="dark"] .preamble-box td,
        [data-theme="dark"] .option-text td,
        [data-theme="dark"] .option-item td {
            border-color: var(--gray-300);
            color: var(--gray-700);
        }

        .question-layout {
            display: flex;
            gap: 2.5rem;
        }

        .question-media {
            flex: 1.2;
        }

        .question-media img {
            width: 100%;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: var(--shadow-md);
        }

        .options-list {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .option-item {
            padding: 1rem 1.25rem;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            color: var(--gray-700);
            position: relative;
        }

        .option-label {
            width: 28px;
            height: 28px;
            background-color: var(--gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .option-item.correct {
            border-color: var(--success-green);
            background-color: var(--white);
        }

        .option-item.correct .option-label {
            background-color: var(--success-green);
            color: white;
        }

        .option-item.incorrect {
            border-color: var(--error-red);
        }

        .option-item.user-choice.correct {
            background-color: var(--success-green-light);
        }

        .option-item.user-choice.incorrect {
            background-color: var(--error-red-light);
        }

        .check-icon {
            margin-left: auto;
            color: var(--success-green);
            font-size: 1.125rem;
        }


        /* Navigation Footer */
        .review-footer {
            padding: 1.5rem 2rem;
            background-color: var(--gray-25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--gray-100);
        }

        [data-theme="dark"] .review-footer {
            background-color: #16181C;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            white-space: nowrap;
            /* Prevent line breaks */
        }

        .nav-btn.prev {
            background: var(--white);
            border-color: var(--gray-200);
            color: var(--gray-900);
        }

        [data-theme="dark"] .nav-btn.prev {
            color: #ffffff;
        }

        .nav-btn.prev:hover:not(:disabled) {
            background-color: var(--gray-50);
        }

        .nav-btn.next {
            background: var(--primary-blue);
            color: var(--white);
        }

        .nav-btn.next:hover:not(:disabled) {
            background-color: var(--primary-blue-hover);
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }



        /* Bottom Actions */
        .bottom-actions {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 3rem;
            flex-wrap: nowrap;
            /* Force single row */
        }

        .btn-action {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
            /* Prevent text wrapping inside buttons */
        }

        .btn-action.outline {
            background: transparent;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-action.outline:hover {
            background-color: var(--primary-blue-light);
        }

        .btn-action.dark {
            background: #111827;
            color: white;
        }

        .btn-action.dark:hover {
            background: #1F2937;
        }

        /* Footer */
        .app-footer {
            text-align: center;
            color: var(--gray-400);
            font-size: 0.75rem;
            padding-bottom: 2rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .youtube-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
                width: var(--sidebar-width-expanded);
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            .app-content {
                margin-left: 0;
                padding: 1rem;
                padding-left: calc(1rem + env(safe-area-inset-left));
                padding-right: calc(1rem + env(safe-area-inset-right));
            }

            .youtube-sidebar.collapsed~.app-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .question-layout {
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .stat-card {
                padding: 1rem 0.75rem;
                flex: 1 1 calc(50% - 0.5rem);
                min-width: 120px;
            }

            .stat-circle {
                width: 60px;
                height: 60px;
            }

            .stat-circle .percentage {
                font-size: 1rem;
            }

            .stat-icon-wrapper {
                width: 36px;
                height: 36px;
                font-size: 1.25rem;
            }

            .stat-value {
                font-size: 1.125rem;
            }

            .stat-label {
                font-size: 0.65rem;
            }

            .content-header {
                text-align: center;
            }

            .breadcrumbs {
                justify-content: center;
            }

            .bottom-actions {
                flex-direction: row;
                align-items: center;
                gap: 0.75rem;
                /* Reduced gap */
            }

            .btn-action {
                padding: 0.625rem 1rem;
                /* Reduced padding */
                font-size: 0.875rem;
                /* Smaller font */
                gap: 0.5rem;
            }

            /* Carousel Nav Spacing */
            .review-footer {
                padding: 1rem;
                gap: 0.75rem;
            }

            .nav-btn {
                padding: 0.5rem 0.875rem;
                font-size: 0.8125rem;
            }

            .nav-btn .nav-btn-text-extra {
                display: none;
            }

            .review-footer {
                padding: 1rem 1.25rem;
                gap: 0.75rem;
            }

            .nav-btn {
                padding: 0.625rem 1rem;
                font-size: 0.8125rem;
                gap: 0.375rem;
            }
        }

        /* Existing Confetti/Modal Styles (Simplified for now, can add back fuller versions) */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            z-index: 1000;
        }

        /* Generic Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        /* Preamble Styling for Results */
        .preamble-box {
            background-color: var(--gray-50);
            border-left: 4px solid var(--primary-blue);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--gray-700);
            line-height: 1.6;
            border: 1px solid var(--gray-200);
            border-left-width: 4px;
        }

        .preamble-header {
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: var(--white);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            position: relative;
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1) translateY(0);
        }

        /* Quiz Rating Modal */
        .rating-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .rating-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .rating-modal {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            position: relative;
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
        }

        .rating-modal-overlay.active .rating-modal {
            transform: scale(1) translateY(0);
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--gray-400);
        }

        .stars-container {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1.5rem 0;
        }

        .star-label {
            font-size: 2rem;
            color: var(--gray-200);
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-label:hover,
        .star-label.filled {
            color: #F59E0B;
        }

        .rating-stars-input input {
            display: none;
        }

        .rating-review textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            resize: none;
            height: 100px;
            margin-bottom: 1rem;
        }

        .rating-actions {
            display: flex;
            gap: 1rem;
        }

        .rating-actions button {
            flex: 1;
            padding: 0.75rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }

        .submit-rating {
            background: var(--primary-blue);
            color: white;
        }

        .skip-rating {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        /* Answer Visibility Toggle - Localized to Question Card */
        .question-card.answers-hidden > .sample-answer-box,
        .question-card.answers-hidden .essay-shared-content-box .sample-answer-box {
            display: none !important;
        }

        .question-card.answers-hidden .option-item.correct:not(.user-choice) {
            border-color: var(--gray-200) !important;
            background: var(--white) !important;
        }

        .question-card.answers-hidden .option-item.correct .check-icon {
            display: none !important;
        }

        .question-card.answers-hidden .option-item.correct.user-choice {
            border-color: var(--primary-blue);
            background: var(--primary-blue-light);
        }

        /* Local Reveal Toggle */
        .reveal-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .reveal-toggle:hover {
            background: var(--gray-50);
            border-color: var(--primary-blue);
            color: var(--primary-blue);
        }

        .reveal-toggle.active {
            background: var(--primary-blue-light);
            color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .reveal-toggle i {
            font-size: 0.8rem;
        }

        /* Sub-question and sub-part specific hidden states */
        .sub-review-item.answer-hidden > .sample-answer-box,
        .sub-part-review-item.answer-hidden > .sample-answer-box {
            display: none !important;
        }

        /* Sample Answer Box Styling - Simple, Clean & Perfect */
        .sample-answer-box {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-left: 4px solid var(--primary-blue) !important;
            border-radius: 10px;
            padding: 1.1rem 1.35rem;
            margin-top: 0.85rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            animation: fadeInAnswer 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeInAnswer {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
            padding: 0.5rem 1rem;
            background: var(--gray-100);
            border-radius: 99px;
            transition: all 0.2s ease;
        }

        .toggle-wrapper:hover {
            background: var(--gray-200);
        }

        .toggle-switch {
            width: 36px;
            height: 20px;
            background: var(--gray-400);
            border-radius: 99px;
            position: relative;
            transition: all 0.2s ease;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .toggle-active .toggle-switch {
            background: var(--primary-blue);
        }

        .toggle-active .toggle-switch::after {
            left: 18px;
        }

        .toggle-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* Quick Navigation Styles */
        .review-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
            align-items: start;
        }

        .quick-nav-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            padding: 1.25rem;
            position: sticky;
            top: 80px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            z-index: 10;
        }

        .quick-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .quick-nav-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .quick-nav-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary-blue);
            background-color: var(--primary-blue-light);
            border: 1px solid var(--primary-blue-border);
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
        }

        .quick-nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.65rem;
            margin-bottom: 1.25rem;
        }

        .nav-box {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1.5px solid var(--gray-200);
            color: var(--gray-600);
            background-color: var(--gray-50);
        }

        .nav-box:hover {
            transform: translateY(-1px);
            border-color: var(--primary-blue);
        }

        .nav-box.correct {
            background-color: var(--primary-blue-light);
            color: var(--primary-blue);
            border-color: var(--primary-blue-border);
        }

        .nav-box.incorrect {
            background-color: var(--error-red-light);
            color: var(--error-red);
            border-color: var(--error-red-border);
        }

        .nav-box.skipped {
            background-color: var(--gray-100);
            color: var(--gray-500);
            border-color: var(--gray-200);
        }

        .nav-box.active {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(29, 78, 216, 0.2);
            font-weight: 800;
        }

        .nav-box.active.correct {
            background-color: var(--primary-blue);
            color: #ffffff;
            border-color: var(--primary-blue);
        }

        .nav-box.active.incorrect {
            background-color: var(--error-red);
            color: #ffffff;
            border-color: var(--error-red);
        }

        .nav-box.active.skipped {
            background-color: var(--gray-800);
            color: #ffffff;
            border-color: var(--gray-800);
        }

        .quick-nav-legend {
            display: flex;
            justify-content: center;
            gap: 0.85rem;
            font-size: 0.75rem;
            color: var(--gray-500);
            padding-top: 0.85rem;
            border-top: 1px dashed var(--gray-200);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .legend-dot.correct {
            background-color: var(--primary-blue);
        }

        .legend-dot.incorrect {
            background-color: var(--error-red);
        }

        .legend-dot.skipped {
            background-color: var(--gray-400);
        }

        @media (max-width: 1024px) {
            .review-layout {
                grid-template-columns: 1fr;
            }

            .quick-nav-card {
                position: static;
                order: -1;
                /* Show Quick Nav above question on mobile? Or below? 
                              User said "left side", implies desktop. On mobile, usually top or 
                              bottom. Let's keep it normal flow (top) or allow stickiness. 
                           */
        /* Section Tabs for Mixed and Essay Quizzes */
        .section-tabs-wrapper {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 0.5rem;
            overflow-x: auto;
        }

        .section-tab-btn {
            background: transparent;
            border: none;
            padding: 0.6rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--gray-500);
            cursor: pointer;
            border-radius: 9999px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-tab-btn:hover {
            color: var(--primary-blue);
            background: rgba(38, 119, 184, 0.08);
        }

        .section-tab-btn.active {
            color: #ffffff;
            background: var(--primary-blue);
            box-shadow: 0 4px 10px rgba(38, 119, 184, 0.3);
        }

        .section-tab-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.08);
        }

        .section-tab-btn.active .section-tab-badge {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        .stat-card.assessment-driver-card {
            border-left: 4px solid var(--primary-blue);
        }

        .marks-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 800;
        }

        .marks-pill.full {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }

        .marks-pill.partial {
            background: #F3F4F6;
            color: #374151;
            border: 1px solid #D1D5DB;
        }

        .marks-pill.zero {
            background: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .marks-pill.pending {
            background: #F3F4F6;
            color: #6B7280;
            border: 1px solid #E5E7EB;
        }
    </style>
</head>

<body>
    @include('components.dashboard-header')
    <div class="app-container">
        @php
            $isEssayQuiz = collect($questions)->contains('type', 'essay');
            $retakeRoute = $isEssayQuiz ? 'quiz.essay' : 'quiz.take';
        @endphp
        <!-- Sidebar -->
        <aside class="youtube-sidebar" id="youtubeSidebar">
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-title">RESULTS OVERVIEW</div>
                    <a href="#" class="nav-item active">
                        <i class="fas fa-chart-pie"></i>
                        <span>Performance Summary</span>
                        <div class="tooltip">Performance Summary</div>
                    </a>
                    <a href="{{ route($retakeRoute, $quiz['encoded_id']) }}" class="nav-item">
                        <i class="fas fa-redo"></i>
                        <span>Retake Quiz</span>
                        <div class="tooltip">Retake Quiz</div>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="nav-item">
                        <i class="fas fa-th-large"></i>
                        <span>More Quizzes</span>
                        <div class="tooltip">More Quizzes</div>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-title">ACCOUNT</div>
                    <a href="{{ route('dashboard.main') }}" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Back to Dashboard</span>
                        <div class="tooltip">Back to Dashboard</div>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="{{ route('quiz.index') }}" class="exit-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Exit Review</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-content">
            <div class="content-header">
                <div class="breadcrumbs">
                    <span>{{ $quiz['subject'] ?? 'Library' }}</span>
                    <i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i>
                    <span class="active">Results Review</span>
                </div>
                <h1 class="page-title">Quiz Results: {{ trim(str_replace('Quiz for:', '', $quiz['title'])) }}</h1>
                <p class="page-subtitle">
                    @if($percentage >= 80)
                        Excellent work! You've mastered the foundational components of this topic.
                    @elseif($percentage >= 50)
                        Good job! You've grasped most concepts, but there's room for improvement.
                    @else
                        Keep practicing! Review the materials and try again to improve your score.
                    @endif
                </p>
            </div>

            <!-- Performance Cards -->
            @php
                $displayQuestions = collect($questions);
                $hasMcq = $displayQuestions->contains('type', 'mcq');
                $hasEssay = $displayQuestions->contains('type', 'essay');
                $isMixed = $hasMcq && $hasEssay;

                $mcqQuestions = $displayQuestions->filter(fn($q) => ($q['type'] ?? 'mcq') === 'mcq');
                $essayQuestions = $displayQuestions->filter(fn($q) => ($q['type'] ?? 'mcq') === 'essay');

                $skippedCount = $displayQuestions->filter(fn($q) => $q['user_answer'] === null)->count();
                $correctCount = $displayQuestions->filter(fn($q) => $q['user_correct'] === true)->count();
                $totalCount = $displayQuestions->count();

                // Compute marks for essay
                $marksArray = is_array($grading) && isset($grading['marks']) ? $grading['marks'] : [];
                $totalMarksEarned = $grading['total_earned'] ?? 0;
                $totalMarksPossible = $grading['total_possible'] ?? 0;

                if ($totalMarksEarned == 0 && !empty($marksArray)) {
                    foreach ($marksArray as $m) {
                        $totalMarksEarned += (float) $m;
                    }
                }

                $gradedBy = $attempt->graded_by ?? ($grading['grading_driver'] ?? null);
                if ($gradedBy === 'gemini') $gradedBy = 'Gemini AI';
                elseif ($gradedBy === 'openai') $gradedBy = 'OpenAI';
                elseif ($gradedBy === 'local') $gradedBy = 'Keyword Match';
            @endphp

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-circle">
                        <svg viewBox="0 0 36 36">
                            <path class="circle-bg"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="circle" stroke-dasharray="{{ $percentage }}, 100"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="percentage">{{ $percentage }}%</div>
                    </div>
                    <span class="stat-label">OVERALL SCORE</span>
                </div>

                @if($hasEssay && !$hasMcq)
                    <!-- Pure Essay Quiz: Show Marks Earned -->
                    <div class="stat-card accuracy-card">
                        <div class="stat-header">
                            <span class="accuracy-title">Marks Earned</span>
                            <i class="fas fa-pen-nib accuracy-icon" style="color: var(--primary-blue);"></i>
                        </div>
                        <div class="accuracy-value-wrapper">
                            <span class="accuracy-value">
                                @if($totalMarksPossible > 0)
                                    {{ round($totalMarksEarned, 1) }} / {{ $totalMarksPossible }}
                                @else
                                    {{ $correctCount }} / {{ $totalCount }}
                                @endif
                            </span>
                            <span class="accuracy-status">
                                @if(($attempt->status ?? 'completed') === 'pending')
                                    Pending Review
                                @else
                                    Total Marks
                                @endif
                            </span>
                        </div>
                        <p class="skipped-text" style="color: var(--gray-500);">
                            <i class="fas fa-file-alt"></i> {{ $essayQuestions->count() }} {{ Str::plural('Essay Question', $essayQuestions->count()) }}
                        </p>
                    </div>
                @else
                    <!-- MCQ or Mixed Quiz: Show Question Accuracy -->
                    <div class="stat-card accuracy-card">
                        <div class="stat-header">
                            <span class="accuracy-title">{{ $isMixed ? 'Section Overview' : 'Accuracy' }}</span>
                            <i class="far fa-check-circle accuracy-icon" style="color: var(--primary-blue);"></i>
                        </div>
                        <div class="accuracy-value-wrapper">
                            <span class="accuracy-value">{{ $correctCount }}/{{ $totalCount }}</span>
                            <span class="accuracy-status">
                                @if(($attempt->status ?? 'completed') === 'pending' && $hasEssay)
                                    Essays Pending
                                @else
                                    {{ $isMixed ? 'Questions Passed' : 'Correct' }}
                                @endif
                            </span>
                        </div>
                        @if($isMixed)
                            <p class="skipped-text" style="color: var(--gray-500);">
                                {{ $mcqQuestions->count() }} MCQs &bull; {{ $essayQuestions->count() }} Essays
                            </p>
                        @elseif($skippedCount > 0)
                            <p class="skipped-text" style="color: var(--gray-500);">You skipped {{ $skippedCount }} {{ Str::plural('question', $skippedCount) }}</p>
                        @endif
                    </div>
                @endif

                @if(!empty($gradedBy) || ($attempt->status ?? null) === 'pending')
                    <div class="stat-card assessment-driver-card">
                        <div class="stat-header">
                            <span class="accuracy-title">Evaluation</span>
                            <i class="fas fa-clipboard-check accuracy-icon" style="color: var(--primary-blue);"></i>
                        </div>
                        <div class="accuracy-value-wrapper" style="margin-top: 0.25rem;">
                            <span class="accuracy-value" style="font-size: 1.15rem; font-weight: 800; color: var(--primary-blue);">
                                {{ $gradedBy ?? 'AI Assessor' }}
                            </span>
                            <span class="accuracy-status">
                                @if(($attempt->status ?? 'completed') === 'graded')
                                    <span style="color: var(--primary-blue); font-weight: 700;"><i class="fas fa-check-circle"></i> Graded</span>
                                @else
                                    <span style="color: var(--gray-600); font-weight: 600;"><i class="fas fa-clock"></i> In Review</span>
                                @endif
                            </span>
                        </div>
                        <p class="skipped-text" style="color: var(--gray-500); font-size: 0.75rem;">
                            Standard marking rubric applied
                        </p>
                    </div>
                @endif

                <div class="stat-card">
                    <div class="stat-icon-wrapper" style="background-color: var(--primary-blue-light); color: var(--primary-blue); margin-top: 5px;">
                        <i class="far fa-clock"></i>
                    </div>
                    <span class="stat-value">
                        @if($timeTaken > 0)
                            {{ floor($timeTaken / 60) }}m {{ str_pad($timeTaken % 60, 2, '0', STR_PAD_LEFT) }}s
                        @else
                            --:--
                        @endif
                    </span>
                    <span class="stat-label">Duration</span>
                </div>
            </div>



            <!-- Review Layout Grid -->
            <div class="review-layout">
                <!-- Quick Navigation Sidebar -->
                <div class="quick-nav-card">
                    <div class="quick-nav-header">
                        <div class="quick-nav-title">Quick Navigation</div>
                        <div class="quick-nav-count">{{ $total }} Questions</div>
                    </div>

                    <div class="quick-nav-grid">
                        @foreach($questions as $index => $q)
                            @php
                                $statusClass = 'skipped';
                                if ($q['user_correct']) {
                                    $statusClass = 'correct';
                                } elseif ($q['user_answer'] !== null) {
                                    $statusClass = 'incorrect';
                                }
                                $qTypeNav = $q['type'] ?? 'mcq';
                            @endphp
                            <div class="nav-box {{ $statusClass }} {{ $index == 0 ? 'active' : '' }}"
                                id="nav-box-{{ $index }}" data-qidx="{{ $index }}" data-qtype="{{ $qTypeNav }}">
                                {{ $index + 1 }}
                            </div>
                        @endforeach
                    </div>

                    <div class="quick-nav-legend">
                        <div class="legend-item">
                            <div class="legend-dot correct"></div> Passed
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot incorrect"></div> Incorrect
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot skipped"></div> Skipped
                        </div>
                    </div>
                </div>

                <!-- Question Review Section (Existing) -->
                <div class="review-section" style="margin-bottom: 0;">
                    <!-- Remove bottom margin since grid handles gap -->
                    <div class="review-header">
                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <h2 class="review-title">Question Review</h2>
                                @if($percentage == 100)
                                    <span class="all-correct-badge">
                                        <i class="fas fa-check"></i> ALL CORRECT
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isMixed)
                        <!-- Section Tabs for Mixed Quizzes -->
                        <div class="section-tabs-wrapper">
                            <button type="button" class="section-tab-btn active" data-section="all">
                                <i class="fas fa-layer-group"></i> All Questions <span class="section-tab-badge">{{ $totalCount }}</span>
                            </button>
                            <button type="button" class="section-tab-btn" data-section="mcq">
                                <i class="fas fa-list-ul"></i> Section A: Multiple Choice <span class="section-tab-badge">{{ $mcqQuestions->count() }}</span>
                            </button>
                            <button type="button" class="section-tab-btn" data-section="essay">
                                <i class="fas fa-pen-nib"></i> Section B: Essay Questions <span class="section-tab-badge">{{ $essayQuestions->count() }}</span>
                            </button>
                        </div>
                    @endif

                    <div class="questions-carousel" id="questionsContainer">
                        @php
                            $sanitizeMath = function($html) {
                                if (!$html) return $html;
                                $html = str_replace('contenteditable="true"', 'contenteditable="false" read-only', $html);
                                $html = str_replace('tabindex="0"', 'tabindex="-1"', $html);
                                return $html;
                            };
                        @endphp
                        @foreach($questions as $index => $question)
                            @php
                                $thisQType = $question['type'] ?? 'mcq';
                            @endphp
                            <div class="question-card answers-hidden {{ $index == 0 ? 'active' : '' }}" id="question-{{ $index }}" data-qtype="{{ $thisQType }}">
                                <div class="question-info">
                                    <div class="header-main-info" style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span class="question-count">QUESTION {{ $index + 1 }} OF {{ $total }}</span>
                                            @if($thisQType === 'essay')
                                                <span style="font-size: 0.7rem; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 2px 8px; border-radius: 999px;">ESSAY / WRITTEN</span>
                                            @else
                                                <span style="font-size: 0.7rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 999px;">MULTIPLE CHOICE</span>
                                            @endif
                                        </div>
                                        @if(($question['type'] ?? 'mcq') === 'essay')
                                            @if(($attempt->status ?? 'completed') === 'pending')
                                                <span style="font-size: 0.7rem; font-weight: 700; color: var(--gray-600); background: var(--gray-100); border: 1px solid var(--gray-200); padding: 2px 8px; border-radius: 4px; display: inline-block; width: fit-content;">PENDING GRADING</span>
                                            @elseif(($attempt->status ?? 'completed') === 'graded')
                                                <span style="font-size: 0.7rem; font-weight: 700; color: var(--primary-blue); background: var(--primary-blue-light); border: 1px solid var(--primary-blue-border); padding: 2px 8px; border-radius: 4px; display: inline-block; width: fit-content;">GRADED</span>
                                            @endif
                                        @endif
                                    </div>
                                    @php
                                        $isSkipped = $question['user_answer'] === null;
                                    @endphp
                                    @php
                                        $isEssay = ($question['type'] ?? 'mcq') === 'essay';
                                        $isPending = ($attempt->status ?? 'completed') === 'pending';

                                        // Compute marks for this specific question
                                        $thisQMarks = 0;
                                        $thisQPossible = 0;
                                        if ($isEssay) {
                                            if (!empty($question['sub_questions'])) {
                                                foreach ($question['sub_questions'] as $sIdx => $sub) {
                                                    if (!empty($sub['has_sub_parts']) && !empty($sub['sub_parts'])) {
                                                        foreach ($sub['sub_parts'] as $spIdx => $sp) {
                                                            $thisQMarks += (float) ($grading['marks']["{$index}_{$sIdx}_{$spIdx}"] ?? 0);
                                                            $thisQPossible += ($sp['points'] ?? 1);
                                                        }
                                                    } else {
                                                        $thisQMarks += (float) ($grading['marks']["{$index}_{$sIdx}"] ?? 0);
                                                        $thisQPossible += ($sub['points'] ?? 1);
                                                    }
                                                }
                                            } else {
                                                $thisQMarks = (float) ($grading['marks'][$index] ?? 0);
                                                $thisQPossible = ($question['points'] ?? 10);
                                            }
                                        }

                                        $badgeClass = $question['user_correct'] ? 'correct' : ($isSkipped ? 'skipped' : ($isEssay && $isPending ? 'warning' : 'incorrect'));
                                        $badgeIcon = $question['user_correct'] ? 'check-circle' : ($isSkipped ? 'minus-circle' : ($isEssay && $isPending ? 'hourglass-half' : 'times-circle'));
                                        $badgeText = $question['user_correct'] ? 'Correct' : ($isSkipped ? 'Skipped' : ($isEssay && $isPending ? 'Submitted' : 'Incorrect'));
                                    @endphp
                                    <div class="review-badges-row" style="display: flex; align-items: center; gap: 0.75rem;">
                                        @if($isEssay && !$isPending && $thisQPossible > 0)
                                            @php
                                                $ratio = $thisQMarks / $thisQPossible;
                                                $pillClass = $ratio >= 0.8 ? 'full' : ($ratio >= 0.4 ? 'partial' : 'zero');
                                            @endphp
                                            <span class="marks-pill {{ $pillClass }}">
                                                <i class="fas fa-award"></i> {{ round($thisQMarks, 1) }} / {{ $thisQPossible }} Marks
                                            </span>
                                        @else
                                            <span class="status-badge {{ $badgeClass }}" 
                                                  style="{{ $badgeClass === 'warning' ? 'background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2);' : '' }}">
                                                <i class="fas fa-{{ $badgeIcon }}"></i>
                                                {{ $badgeText }}
                                            </span>
                                        @endif

                                        @if(($question['type'] ?? 'mcq') === 'mcq')
                                            <button class="reveal-toggle" data-target-q="{{ $index }}">
                                                <i class="far fa-eye"></i> <span>Reveal Answer</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($question['preamble']))
                                    <div class="preamble-box">
                                        <div class="preamble-header">
                                            <i class="fas fa-align-left"></i> Preamble / Context
                                        </div>
                                        <div class="preamble-text">{!! $sanitizeMath($question['preamble']) !!}</div>
                                    </div>
                                @endif

                                @php
                                    $qType = $question['type'] ?? 'mcq';
                                    $hasMainContent = !empty(trim(strip_tags($question['question'] ?? '')));
                                    $hasSubQuestions = !empty($question['sub_questions']) && count($question['sub_questions']) > 0;
                                @endphp

                                @if($question['image'])
                                    <div class="question-image-container">
                                        <img src="{{ $question['image'] }}" class="question-image" alt="Question illustration">
                                    </div>
                                @endif

                                @if($qType === 'essay')
                                    <div class="essay-shared-content-box" style="background: var(--primary-blue-light); border: 1px solid var(--primary-blue-border); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem;">
                                        @if($hasMainContent || !$hasSubQuestions)
                                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 0.75rem;">
                                                {{ $hasSubQuestions ? 'Shared Content / Instructions' : 'Question Text' }}
                                            </div>
                                            <h3 class="question-text" style="margin-bottom: 0;">{!! $sanitizeMath($question['question']) !!}</h3>
                                        @endif

                                        @if(!empty($question['correct_answer']))
                                            <div style="margin-top: 1.5rem; border-top: 1px dashed var(--primary-blue-border); padding-top: 1.5rem;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--primary-blue);">{{ $hasSubQuestions ? 'SHARED CONTENT REFERENCE ANSWER' : 'REFERENCE ANSWER (SAMPLE)' }}</span>
                                                    <button class="reveal-toggle" data-target-q="{{ $index }}">
                                                        <i class="far fa-eye"></i> <span>Reveal Answer</span>
                                                    </button>
                                                </div>
                                                <div class="sample-answer-box">
                                                    <div class="sample-answer-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200);">
                                                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.45rem;">
                                                            <i class="fas fa-check-circle" style="color: var(--primary-blue);"></i> Model Answer / Marking Scheme
                                                        </span>
                                                        <span style="font-size: 0.7rem; font-weight: 600; color: var(--gray-500); background: var(--white); border: 1px solid var(--gray-200); padding: 2px 8px; border-radius: 6px;">
                                                            Instructor Reference
                                                        </span>
                                                    </div>
                                                    <div class="sample-content" style="font-size: 1.05rem; line-height: 1.7; color: var(--gray-800);">
                                                        {!! $sanitizeMath($question['correct_answer']) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    @if($hasMainContent || !$hasSubQuestions)
                                        <h3 class="question-text">{!! $sanitizeMath($question['question']) !!}</h3>
                                    @endif
                                @endif

                                <div class="question-layout">
                                    @if($qType === 'mcq')
                                        <div class="options-list">
                                            @php $labels = ['A', 'B', 'C', 'D', 'E']; @endphp
                                            @foreach($question['options'] as $optIndex => $option)
                                                <div class="option-item 
                                                                    {{ $optIndex === $question['correct_answer'] ? 'correct' : '' }}
                                                                    {{ $optIndex === $question['user_answer'] ? 'user-choice' : '' }}
                                                                    {{ $optIndex === $question['user_answer'] && !$question['user_correct'] ? 'incorrect' : '' }}
                                                                ">
                                                    <div class="option-label">{{ $labels[$optIndex] }}</div>
                                                    <span class="option-text">{!! $sanitizeMath($option) !!}</span>

                                                    @if($optIndex == $question['correct_answer'])
                                                        <i class="fas fa-check-circle check-icon"></i>
                                                    @endif
                                                    @if($optIndex === $question['user_answer'] && !$question['user_correct'])
                                                        <i class="fas fa-times-circle"
                                                            style="margin-left: auto; color: var(--error-red);"></i>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <!-- Essay Results -->
                                        <div class="essay-results-wrapper" style="width: 100%;">
                                            @if($hasSubQuestions)
                                                <div class="sub-questions-review" style="display: flex; flex-direction: column; gap: 1.5rem;">
                                                    @foreach($question['sub_questions'] as $sIdx => $sub)
                                                        @if(isset($sub['has_sub_parts']) && $sub['has_sub_parts'] && !empty($sub['sub_parts']))
                                                            <div class="sub-review-item" style="border-left: 3px solid var(--primary-blue); padding-left: 1.5rem; margin-bottom: 2rem;">
                                                                <div class="sub-q-header" style="font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem;">
                                                                    @if(!$hasMainContent && $sIdx === 0)
                                                                        {{ $index + 1 }}{{ $sub['label'] }})
                                                                    @else
                                                                        {{ $sub['label'] }})
                                                                    @endif
                                                                </div>
                                                                <div class="sub-q-text" style="font-size: 1rem; color: var(--gray-700); margin-bottom: 1rem;">{!! $sanitizeMath($sub['text']) !!}</div>
                                                                
                                                                <div class="sub-parts-review" style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1rem; padding-left: 1rem; border-left: 2px solid var(--gray-200);">
                                                                    @foreach($sub['sub_parts'] as $spIdx => $sp)
                                                                        <div class="sub-part-review-item answer-hidden" id="sub-{{ $index }}-{{ $sIdx }}-{{ $spIdx }}">
                                                                            <div class="sub-q-header" style="font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                                                                                <div>
                                                                                    <span style="font-style: italic;">{{ $sp['label'] }})</span>
                                                                                    <span style="font-size: 0.75rem; color: var(--gray-500); margin-left: 0.5rem;">[{{ $sp['points'] }} Marks]</span>
                                                                                </div>
                                                                                
                                                                                @php
                                                                                    $subSample = $sp['sample_answer'] ?? null;
                                                                                @endphp
                                                                                @if(!empty($subSample))
                                                                                    <button class="reveal-toggle" data-target-sub="{{ $index }}-{{ $sIdx }}-{{ $spIdx }}">
                                                                                        <i class="far fa-eye"></i> <span>Sample Answer</span>
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                            <div class="sub-q-text" style="font-size: 1rem; color: var(--gray-700); margin-bottom: 1rem;">{!! $sanitizeMath($sp['text']) !!}</div>
                                                                            
                                                                            <div class="user-response-box" style="background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
                                                                                <div style="font-size: 0.75rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                                                                    <span><i class="fas fa-user-pen"></i> Your Response</span>
                                                                                    @php
                                                                                        $awarded = $grading['marks']["{$index}_{$sIdx}_{$spIdx}"] ?? null;
                                                                                    @endphp
                                                                                    @if($awarded !== null)
                                                                                        <span style="color: var(--primary-blue); font-weight: 800;">{{ $awarded }} / {{ $sp['points'] }} Marks</span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="response-content" style="font-size: 1rem; line-height: 1.6; color: var(--gray-900);">
                                                                                    @php
                                                                                        $ans = $question['user_answer'] ?? null;
                                                                                        $subAns = is_array($ans) && isset($ans[$sIdx]) && is_array($ans[$sIdx]) && isset($ans[$sIdx][$spIdx]) ? $ans[$sIdx][$spIdx] : null;
                                                                                    @endphp
                                                                                    @if($subAns)
                                                                                        {!! $sanitizeMath($subAns) !!}
                                                                                    @else
                                                                                        <span style="color: var(--gray-400); font-style: italic;">No response provided.</span>
                                                                                    @endif
                                                                                </div>
                                                                                @if(!empty($grading['feedback']["{$index}_{$sIdx}_{$spIdx}"]))
                                                                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--gray-200); font-size: 0.875rem; color: var(--primary-blue);">
                                                                                        <i class="fas fa-comment-dots mr-1"></i> <strong>Instructor Feedback:</strong> {{ $grading['feedback']["{$index}_{$sIdx}_{$spIdx}"] }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                            
                                                                            @if(!empty($subSample))
                                                                                <div class="sample-answer-box">
                                                                                    <div class="sample-answer-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200);">
                                                                                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.45rem;">
                                                                                            <i class="fas fa-check-circle" style="color: var(--primary-blue);"></i> Model Answer / Marking Scheme
                                                                                        </span>
                                                                                        <span style="font-size: 0.7rem; font-weight: 600; color: var(--gray-500); background: var(--white); border: 1px solid var(--gray-200); padding: 2px 8px; border-radius: 6px;">
                                                                                            Instructor Reference
                                                                                        </span>
                                                                                    </div>
                                                                                    <div class="sample-content" style="font-size: 0.975rem; line-height: 1.65; color: var(--gray-800);">
                                                                                        {!! $sanitizeMath($subSample) !!}
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="sub-review-item answer-hidden" id="sub-{{ $index }}-{{ $sIdx }}" style="border-left: 3px solid var(--primary-blue); padding-left: 1.5rem; margin-bottom: 2rem;">
                                                                <div class="sub-q-header" style="font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                                                                    <div>
                                                                        @if(!$hasMainContent && $sIdx === 0)
                                                                            {{ $index + 1 }}{{ $sub['label'] }})
                                                                        @else
                                                                            {{ $sub['label'] }})
                                                                        @endif
                                                                        <span style="font-size: 0.75rem; color: var(--gray-500); margin-left: 0.5rem;">[{{ $sub['points'] }} Marks]</span>
                                                                    </div>
                                                                    
                                                                    @php
                                                                        $subSample = $sub['sample_answer'] ?? ($sub['correct_answer'] ?? null);
                                                                    @endphp
                                                                    @if(!empty($subSample))
                                                                        <button class="reveal-toggle" data-target-sub="{{ $index }}-{{ $sIdx }}">
                                                                            <i class="far fa-eye"></i> <span>Sample Answer</span>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                                <div class="sub-q-text" style="font-size: 1rem; color: var(--gray-700); margin-bottom: 1rem;">{!! $sanitizeMath($sub['text']) !!}</div>
                                                                
                                                                <div class="user-response-box" style="background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
                                                                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                                                        <span><i class="fas fa-user-pen"></i> Your Response</span>
                                                                        @php
                                                                            $awarded = $grading['marks']["{$index}_{$sIdx}"] ?? null;
                                                                        @endphp
                                                                        @if($awarded !== null)
                                                                            <span style="color: var(--primary-blue); font-weight: 800;">{{ $awarded }} / {{ $sub['points'] }} Marks</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="response-content" style="font-size: 1rem; line-height: 1.6; color: var(--gray-900);">
                                                                        @php
                                                                            $ans = $question['user_answer'] ?? null;
                                                                            $subAns = is_array($ans) && isset($ans[$sIdx]) ? $ans[$sIdx] : (is_string($ans) ? $ans : null);
                                                                        @endphp
                                                                        @if($subAns)
                                                                            {!! $sanitizeMath($subAns) !!}
                                                                        @else
                                                                            <span style="color: var(--gray-400); font-style: italic;">No response provided.</span>
                                                                        @endif
                                                                    </div>
                                                                    @if(!empty($grading['feedback']["{$index}_{$sIdx}"]))
                                                                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--gray-200); font-size: 0.875rem; color: var(--primary-blue);">
                                                                            <i class="fas fa-comment-dots mr-1"></i> <strong>Instructor Feedback:</strong> {{ $grading['feedback']["{$index}_{$sIdx}"] }}
                                                                        </div>
                                                                    @endif
    
                                                                    @if(!empty($grading['strengths']["{$index}_{$sIdx}"]) || !empty($grading['weaknesses']["{$index}_{$sIdx}"]))
                                                                        <div class="ai-insights-box" style="margin-top: 1rem; padding: 1rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px;">
                                                                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                                                                <i class="fas fa-chart-line"></i> Performance Insights
                                                                            </div>
                                                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                                                @if(!empty($grading['strengths']["{$index}_{$sIdx}"]))
                                                                                    <div style="font-size: 0.875rem;">
                                                                                        <div style="color: var(--primary-blue); font-weight: 700; margin-bottom: 0.25rem;"><i class="fas fa-check-circle"></i> Strengths</div>
                                                                                        <div style="color: var(--gray-700);">{{ $grading['strengths']["{$index}_{$sIdx}"] }}</div>
                                                                                    </div>
                                                                                @endif
                                                                                @if(!empty($grading['weaknesses']["{$index}_{$sIdx}"]))
                                                                                    <div style="font-size: 0.875rem;">
                                                                                        <div style="color: var(--error-red); font-weight: 700; margin-bottom: 0.25rem;"><i class="fas fa-times-circle"></i> Weaknesses</div>
                                                                                        <div style="color: var(--gray-700);">{{ $grading['weaknesses']["{$index}_{$sIdx}"] }}</div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
    
                                                                <!-- Sub-Question Sample Answer -->
                                                                @if(!empty($subSample))
                                                                    <div class="sample-answer-box">
                                                                        <div class="sample-answer-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200);">
                                                                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.45rem;">
                                                                                <i class="fas fa-check-circle" style="color: var(--primary-blue);"></i> Model Answer / Marking Scheme
                                                                            </span>
                                                                            <span style="font-size: 0.7rem; font-weight: 600; color: var(--gray-500); background: var(--white); border: 1px solid var(--gray-200); padding: 2px 8px; border-radius: 6px;">
                                                                                Instructor Reference
                                                                            </span>
                                                                        </div>
                                                                        <div class="sample-content" style="font-size: 0.975rem; line-height: 1.65; color: var(--gray-800);">
                                                                            {!! $sanitizeMath($subSample) !!}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="user-response-box" style="background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                                                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                                        <span><i class="fas fa-user-pen"></i> Your Response</span>
                                                        @php
                                                            $awarded = $grading['marks'][$index] ?? null;
                                                        @endphp
                                                        @if($awarded !== null)
                                                            <span style="color: var(--primary-blue); font-weight: 800;">{{ $awarded }} / {{ $question['points'] ?? 10 }} Marks</span>
                                                        @endif
                                                    </div>
                                                    <div class="response-content" style="font-size: 1.125rem; line-height: 1.7; color: var(--gray-900);">
                                                        @if($question['user_answer'])
                                                            {!! $sanitizeMath($question['user_answer']) !!}
                                                        @else
                                                            <span style="color: var(--gray-400); font-style: italic;">No response provided.</span>
                                                        @endif
                                                    </div>
                                                    @if(!empty($grading['feedback'][$index]))
                                                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--gray-200); font-size: 0.875rem; color: var(--primary-blue);">
                                                            <i class="fas fa-comment-dots mr-1"></i> <strong>Instructor Feedback:</strong> {{ $grading['feedback'][$index] }}
                                                        </div>
                                                    @endif

                                                    @if(!empty($grading['strengths'][$index]) || !empty($grading['weaknesses'][$index]))
                                                        <div class="ai-insights-box" style="margin-top: 1rem; padding: 1rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px;">
                                                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                                                <i class="fas fa-chart-line"></i> Performance Insights
                                                            </div>
                                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                                @if(!empty($grading['strengths'][$index]))
                                                                    <div style="font-size: 0.875rem;">
                                                                        <div style="color: var(--primary-blue); font-weight: 700; margin-bottom: 0.25rem;"><i class="fas fa-check-circle"></i> Strengths</div>
                                                                        <div style="color: var(--gray-700);">{{ $grading['strengths'][$index] }}</div>
                                                                    </div>
                                                                @endif
                                                                @if(!empty($grading['weaknesses'][$index]))
                                                                    <div style="font-size: 0.875rem;">
                                                                        <div style="color: var(--error-red); font-weight: 700; margin-bottom: 0.25rem;"><i class="fas fa-times-circle"></i> Weaknesses</div>
                                                                        <div style="color: var(--gray-700);">{{ $grading['weaknesses'][$index] }}</div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif


                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="review-footer">
                        <button class="nav-btn prev" id="prevBtn" onclick="navigateQuestion(-1)" disabled>
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>


                        <button class="nav-btn next" id="nextBtn" onclick="navigateQuestion(1)">
                            Next<span class="nav-btn-text-extra"> Question</span> <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="bottom-actions">
                <a href="{{ route($retakeRoute, $quiz['encoded_id']) }}" class="btn-action outline">
                    <i class="fas fa-redo"></i> Retake Quiz
                </a>
                <button class="btn-action dark" onclick="openShareModal()">
                    <i class="fas fa-share-alt"></i> Share Results
                </button>
            </div>

            <footer class="app-footer">
                <p>&copy; {{ date('Y') }} ShoutOutGh. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <!-- Share Modal -->
    <div class="modal-overlay" id="shareModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeShareModal()">×</button>
            <h3 style="font-size: 1.5rem; text-align: center; margin-bottom: 2rem;">Share Results</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <a href="#" onclick="shareToX()"
                    style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; text-decoration: none; color: inherit;">
                    <i class="fab fa-x-twitter" style="font-size: 2rem; color: #000000;"></i>
                    <span style="font-size: 0.875rem;">Share on X</span>
                </a>
                <a href="#" onclick="shareToWhatsApp()"
                    style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; text-decoration: none; color: inherit;">
                    <i class="fab fa-whatsapp" style="font-size: 2rem; color: #25D366;"></i>
                    <span style="font-size: 0.875rem;">WhatsApp</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quiz Rating Modal -->
    @if(!$hasRated)
        <div class="rating-modal-overlay" id="ratingModal">
            <div class="rating-modal">
                <button class="modal-close" onclick="closeRatingModal()">×</button>
                <div style="text-align: center; margin-bottom: 1rem;">
                    <i class="fas fa-star" style="font-size: 2.5rem; color: #F59E0B; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; font-weight: 700;">How was this quiz?</h3>
                    <p style="color: var(--gray-600); font-size: 0.875rem;">Your feedback helps us improve!</p>
                </div>

                <form id="ratingForm" method="POST" action="{{ route('quiz.rate', $quiz['encoded_id']) }}">
                    @csrf
                    <div class="stars-container">
                        @for($i = 1; $i <= 5; $i++) <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}"
                                required>
                            <label for="star{{ $i }}" class="star-label"><i class="fas fa-star"></i></label>
                        @endfor
                    </div>

                    <div class="rating-review">
                        <textarea name="review" placeholder="Share your thoughts (optional)..." maxlength="500"></textarea>
                    </div>

                    <div class="rating-actions">
                        <button type="button" class="skip-rating" onclick="closeRatingModal()">Maybe Later</button>
                        <button type="submit" class="submit-rating">Submit Rating</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        let currentQuestion = 0;
        const totalQuestions = {{ count($questions) }};
        let currentSectionFilter = 'all';

        function filterQuizSection(section, btn) {
            currentSectionFilter = section;
            
            // Update active tab styling
            document.querySelectorAll('.section-tab-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            // Filter quick navigation buttons
            let firstVisibleIndex = null;
            document.querySelectorAll('.nav-box').forEach(nav => {
                const qType = nav.getAttribute('data-qtype');
                const isMatch = (section === 'all' || qType === section);
                nav.style.display = isMatch ? 'flex' : 'none';
                
                if (isMatch && firstVisibleIndex === null) {
                    const idxAttr = nav.getAttribute('data-qidx');
                    firstVisibleIndex = idxAttr !== null ? parseInt(idxAttr) : parseInt(nav.id.replace('nav-box-', ''));
                }
            });

            // Jump to the first question in the selected section
            if (firstVisibleIndex !== null) {
                goToQuestion(firstVisibleIndex);
            }
        }

        function navigateQuestion(direction) {
            let nextIndex = currentQuestion + direction;
            
            // If filtering, find next visible question in filter
            if (currentSectionFilter !== 'all') {
                while (nextIndex >= 0 && nextIndex < totalQuestions) {
                    const nextCard = document.getElementById(`question-${nextIndex}`);
                    if (nextCard && nextCard.getAttribute('data-qtype') === currentSectionFilter) {
                        break;
                    }
                    nextIndex += direction;
                }
            }

            if (nextIndex >= 0 && nextIndex < totalQuestions) {
                goToQuestion(nextIndex);
            }
        }

        function goToQuestion(index) {
            if (index < 0 || index >= totalQuestions) return;

            // Remove active class from old elements
            const oldCard = document.getElementById(`question-${currentQuestion}`);
            if (oldCard) oldCard.classList.remove('active');
            const oldNav = document.getElementById(`nav-box-${currentQuestion}`);
            if (oldNav) oldNav.classList.remove('active');

            currentQuestion = index;

            // Add active class to new elements
            const newCard = document.getElementById(`question-${currentQuestion}`);
            if (newCard) newCard.classList.add('active');
            const newNav = document.getElementById(`nav-box-${currentQuestion}`);
            if (newNav) {
                newNav.classList.add('active');
                newNav.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
            }

            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            // Update buttons considering section filter
            if (currentSectionFilter === 'all') {
                if (prevBtn) prevBtn.disabled = currentQuestion === 0;
                if (nextBtn) {
                    if (currentQuestion === totalQuestions - 1) {
                        nextBtn.innerHTML = 'Finish Review <i class="fas fa-check"></i>';
                    } else {
                        nextBtn.innerHTML = 'Next<span class="nav-btn-text-extra"> Question</span> <i class="fas fa-arrow-right"></i>';
                    }
                }
            } else {
                // Check if there's any prior question in this section
                let hasPrev = false;
                for (let i = currentQuestion - 1; i >= 0; i--) {
                    const card = document.getElementById(`question-${i}`);
                    if (card && card.getAttribute('data-qtype') === currentSectionFilter) {
                        hasPrev = true;
                        break;
                    }
                }
                if (prevBtn) prevBtn.disabled = !hasPrev;

                // Check if there's any next question in this section
                let hasNext = false;
                for (let i = currentQuestion + 1; i < totalQuestions; i++) {
                    const card = document.getElementById(`question-${i}`);
                    if (card && card.getAttribute('data-qtype') === currentSectionFilter) {
                        hasNext = true;
                        break;
                    }
                }
                if (nextBtn) {
                    if (!hasNext) {
                        nextBtn.innerHTML = 'Finish Section <i class="fas fa-check"></i>';
                    } else {
                        nextBtn.innerHTML = 'Next<span class="nav-btn-text-extra"> in Section</span> <i class="fas fa-arrow-right"></i>';
                    }
                }
            }

            // Sync math fields to read-only reliably without race conditions
            if (window.customElements) {
                customElements.whenDefined('math-field').then(() => {
                    document.querySelectorAll('math-field').forEach(mf => {
                        mf.readOnly = true;
                        mf.removeAttribute('contenteditable');
                        mf.removeAttribute('tabindex');
                    });
                });
            }

            // Render static LaTeX equations inside the container if any
            if (window.renderMathInElement) {
                const texConfig = {
                    TeX: {
                        delimiters: {
                            inline: [['$', '$'], ['\\(', '\\)']],
                            display: [['$$', '$$'], ['\\[', '\\]']]
                        }
                    }
                };
                const container = document.getElementById('questionsContainer');
                if (container) {
                    window.renderMathInElement(container, texConfig);
                }
            }
        }

        function toggleLocalAnswer(index, btn) {
            const card = document.getElementById(`question-${index}`);
            if (!card) return;
            const isHidden = card.classList.toggle('answers-hidden');
            
            if (btn) {
                const icon = btn.querySelector('i');
                const span = btn.querySelector('span');
                btn.classList.toggle('active', !isHidden);
                if (icon) icon.className = isHidden ? 'far fa-eye' : 'far fa-eye-slash';
                if (span) span.textContent = isHidden ? 'Reveal Answer' : 'Hide Answer';
            }

            if (!isHidden && window.renderMathInElement) {
                const sampleBox = card.querySelector('.sample-answer-box');
                if (sampleBox) {
                    window.renderMathInElement(sampleBox, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '\\[', right: '\\]', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\(', right: '\\)', display: false}
                        ]
                    });
                }
            }
        }

        function toggleSubAnswer(idStr, btn) {
            const item = document.getElementById(`sub-${idStr}`);
            if (!item) return;
            const isHidden = item.classList.toggle('answer-hidden');
            
            if (btn) {
                const icon = btn.querySelector('i');
                const span = btn.querySelector('span');
                btn.classList.toggle('active', !isHidden);
                if (icon) icon.className = isHidden ? 'far fa-eye' : 'far fa-eye-slash';
                if (span) span.textContent = isHidden ? 'Sample Answer' : 'Hide Answer';
            }

            if (!isHidden && window.renderMathInElement) {
                const sampleBox = item.querySelector('.sample-answer-box');
                if (sampleBox) {
                    window.renderMathInElement(sampleBox, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '\\[', right: '\\]', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\(', right: '\\)', display: false}
                        ]
                    });
                }
            }
        }

        function openShareModal() { document.getElementById('shareModal')?.classList.add('active'); }
        function closeShareModal() { document.getElementById('shareModal')?.classList.remove('active'); }

        function shareToX() {
            const score = "{{ $correctCount }}/{{ $totalCount }}";
            const quizTitle = "{{ trim(str_replace('Quiz for:', '', $quiz['title'])) }}";
            const text = `I just scored ${score} on the "${quizTitle}" quiz on ShoutOutGh! 🚀 Can you beat my score? Check it out here:`;
            const url = window.location.href;
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
        }

        function shareToWhatsApp() {
            const score = "{{ $correctCount }}/{{ $totalCount }}";
            const quizTitle = "{{ trim(str_replace('Quiz for:', '', $quiz['title'])) }}";
            const text = `I just scored ${score} on the "${quizTitle}" quiz on ShoutOutGh! 🚀 Can you beat my score?\n\nCheck it out here: ${window.location.href}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        function openRatingModal() { document.getElementById('ratingModal')?.classList.add('active'); }
        function closeRatingModal() { document.getElementById('ratingModal')?.classList.remove('active'); }

        // Expose functions globally on window
        window.filterQuizSection = filterQuizSection;
        window.navigateQuestion = navigateQuestion;
        window.goToQuestion = goToQuestion;
        window.toggleLocalAnswer = toggleLocalAnswer;
        window.toggleSubAnswer = toggleSubAnswer;
        window.openShareModal = openShareModal;
        window.closeShareModal = closeShareModal;
        window.shareToX = shareToX;
        window.shareToWhatsApp = shareToWhatsApp;
        window.openRatingModal = openRatingModal;
        window.closeRatingModal = closeRatingModal;

        @if ($percentage >= 80)
            function createConfetti() {
                for (let i = 0; i < 50; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.top = '-10px';
                    confetti.style.backgroundColor = ['#1D4ED8', '#2563EB', '#60A5FA', '#93C5FD'][Math.floor(Math.random() * 4)];
                    confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                    document.body.appendChild(confetti);

                    const animation = confetti.animate([
                        { transform: `translate3d(0, 0, 0) rotate(0deg)`, opacity: 1 },
                        { transform: `translate3d(${(Math.random() - 0.5) * 200}px, 100vh, 0) rotate(${Math.random() * 3000}deg)`, opacity: 0 }
                    ], {
                        duration: Math.random() * 3000 + 2000,
                        easing: 'cubic-bezier(0, .9, .57, 1)'
                    });

                    animation.onfinish = () => confetti.remove();
                }
            }
        @endif

        document.addEventListener('DOMContentLoaded', () => {
            // Navigation button listeners (CSP-safe)
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    navigateQuestion(-1);
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    navigateQuestion(1);
                });
            }

            // Quick navigation boxes
            document.querySelectorAll('.nav-box').forEach(box => {
                box.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = parseInt(box.getAttribute('data-qidx'));
                    if (!isNaN(idx)) goToQuestion(idx);
                });
            });

            // Section tabs
            document.querySelectorAll('.section-tab-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    filterQuizSection(btn.getAttribute('data-section'), btn);
                });
            });

            // Reveal and Sample Answer buttons
            document.querySelectorAll('.reveal-toggle').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetSub = btn.getAttribute('data-target-sub');
                    const targetQ = btn.getAttribute('data-target-q');
                    if (targetSub) {
                        toggleSubAnswer(targetSub, btn);
                    } else if (targetQ !== null) {
                        toggleLocalAnswer(parseInt(targetQ), btn);
                    }
                });
            });

            // Rating Star logic
            document.querySelectorAll('.star-label').forEach((label, idx) => {
                label.addEventListener('click', () => {
                    document.querySelectorAll('.star-label').forEach((l, i) => {
                        l.classList.toggle('filled', i <= idx);
                    });
                });
            });

            // Rating Form Submission
            const ratingForm = document.getElementById('ratingForm');
            if (ratingForm) {
                ratingForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('.submit-rating');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerText = 'Submitting...';
                    }

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                closeRatingModal();
                                alert('Thank you for your feedback!');
                            }
                        })
                        .catch(() => {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerText = 'Submit Rating';
                            }
                        });
                });
            }

            // Sidebar Toggle Logic
            const sidebar = document.getElementById('youtubeSidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileToggle = document.getElementById('mobileSidebarToggle');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.toggle('mobile-open');
                    } else {
                        sidebar.classList.toggle('collapsed');
                    }
                });
            }

            // Close mobile sidebar on click outside
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024 && sidebar) {
                    if (!sidebar.contains(e.target) && !mobileToggle?.contains(e.target) && !sidebarToggle?.contains(e.target)) {
                        sidebar.classList.remove('mobile-open');
                    }
                }
            });

            // Confetti effect for 80%+
            @if ($percentage >= 80)
                createConfetti();
                @if (!$hasRated)
                    setTimeout(openRatingModal, 2000);
                @endif
            @endif

            // Initialize question 0 state
            goToQuestion(0);
        });
    </script>
    @include('dashboard.quiz.partials.lightbox')
</body>

</html>