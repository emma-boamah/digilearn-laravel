<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz Instructions - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-25);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
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
        }

        .back-button:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo img {
            height: 36px;
            width: auto;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background-color: var(--gray-100);
        }

        .notification-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-600);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        /* Main Content */
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 64px);
            padding: 2rem 1rem;
        }

        .instructions-container {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 3rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            max-width: 600px;
            width: 100%;
        }

        .instructions-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .instructions-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .instructions-subtitle {
            color: var(--gray-600);
            font-size: 1.125rem;
        }

        .quiz-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2.5rem;
            padding: 1.5rem;
            background-color: var(--gray-50);
            border-radius: 0.75rem;
        }

        .quiz-info-item {
            text-align: center;
        }

        .quiz-info-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .quiz-info-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .instructions-list {
            margin-bottom: 2.5rem;
        }

        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.75rem;
            border-left: 4px solid var(--primary-red);
        }

        .instruction-icon {
            width: 24px;
            height: 24px;
            color: var(--primary-red);
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .instruction-text {
            font-size: 1rem;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .start-button {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-hover));
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-md);
        }

        .start-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .start-button:active {
            transform: translateY(0);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .instructions-container {
                padding: 2rem 1.5rem;
            }

            .instructions-title {
                font-size: 1.75rem;
            }

            .instructions-subtitle {
                font-size: 1rem;
            }

            .quiz-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .instruction-item {
                padding: 0.75rem;
            }

            .instruction-text {
                font-size: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .instructions-container {
                padding: 1.5rem 1rem;
            }

            .quiz-info {
                padding: 1rem;
            }

            .start-button {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <button class="back-button" id="btnGoBack">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="sidebar-logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
            </div>
        </div>
        
        <div class="header-right">
            <button class="notification-btn">
                <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v0.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>
            
            <x-user-avatar :user="auth()->user()" :size="36" class="border-2 border-white" />
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="instructions-container">
            <div class="instructions-header">
                <h1 class="instructions-title">Quiz Guide</h1>
                <p class="instructions-subtitle">{{ $quiz['title'] ?? 'Living and Non Living organism' }}</p>
            </div>

            <div class="quiz-info">
                <div class="quiz-info-item">
                    <div class="quiz-info-label">Questions</div>
                    <div class="quiz-info-value">{{ $quiz['questions_count'] ?? '10' }}</div>
                </div>
                <div class="quiz-info-item">
                    <div class="quiz-info-label">Duration</div>
                    <div class="quiz-info-value">{{ $quiz['duration'] ?? '3 min' }}</div>
                </div>
                <div class="quiz-info-item">
                    <div class="quiz-info-label">Subject</div>
                    <div class="quiz-info-value">{{ $quiz['subject'] ?? 'Science' }}</div>
                </div>
            </div>

            <div class="instructions-list">
                <div class="instruction-item">
                    <svg class="instruction-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="instruction-text">You are to select, choose or type in the correct answer from the options provided where necessary.</div>
                </div>

                <div class="instruction-item">
                    <svg class="instruction-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="instruction-text">You cannot go back to a previous question once you have moved to the next question.</div>
                </div>

                <div class="instruction-item">
                    <svg class="instruction-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="instruction-text">You can skip a question and come back to it later, but the timer will continue.</div>
                </div>

                <div class="instruction-item">
                    <svg class="instruction-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="instruction-text">You will see your score immediately after submitting the quiz.</div>
                </div>

                <div class="instruction-item">
                    <svg class="instruction-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="instruction-text">You have a maximum of {{ $quiz['duration'] ?? '3 min' }} time allocated to the quiz</div>
                </div>
            </div>

            <div class="warning-card" style="margin-top:1rem; border-left:4px solid #ef4444; background:#fef2f2; padding:1rem; border-radius:.5rem;">
                <div style="display:flex; align-items:center; gap:.5rem; color:#991b1b; font-weight:600;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Anti‑Cheat Rules
                </div>
                <ul style="margin:.5rem 0 0 1.25rem; color:#7f1d1d;">
                    <li>No screenshots. Attempting to capture the screen will fail the quiz.</li>
                    <li>No copying or cutting content. Copy/Cut is disabled and flagged as a violation.</li>
                    <li>No switching tabs/windows. Leaving this tab will fail the quiz.</li>
                </ul>
            </div>

            <button class="start-button" id="btnStartQuiz">Start</button>
            <a href="{{ route('quiz.essay', $quiz['id']) }}" class="action-button secondary">
                <i class="fas fa-pen-nib"></i>
                Essay Questions
            </a>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', () => {
            const btnBack = document.getElementById('btnGoBack');
            if (btnBack) btnBack.addEventListener('click', () => window.history.back());

            const startBtn = document.getElementById('btnStartQuiz');
            if (startBtn) {
                startBtn.addEventListener('click', () => {
                    startBtn.disabled = true;
                    startBtn.textContent = 'Loading...';
                    setTimeout(() => {
                        window.location.href = `{{ route('quiz.take', $quiz['id']) }}`;
                    }, 300);
                });
            }
        });
    </script>
</body>
</html>
