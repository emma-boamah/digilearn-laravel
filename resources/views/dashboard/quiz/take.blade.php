<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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

        /* Main Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Left Column Wrapper */
        .quiz-left-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            position: sticky;
            top: 80px;
            align-self: start;
        }

        /* Left Sidebar */
        .quiz-sidebar {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .quiz-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .timer-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .timer {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-hover));
            color: var(--white);
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            box-shadow: var(--shadow-md);
            min-width: 120px;
            text-align: center;
        }

        .timer.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            animation: pulse 1s infinite;
        }

        .timer.danger {
            background: linear-gradient(135deg, var(--primary-red), #dc2626);
            animation: pulse 0.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .questions-section {
            margin-bottom: 2rem;
        }

        .questions-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
            text-align: center;
        }

        .questions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }

        .question-number {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid var(--gray-200);
            background-color: var(--white);
            color: var(--gray-600);
        }

        .question-number:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
            transform: translateY(-1px);
        }

        .question-number.current {
            background-color: var(--gray-50);
            color: var(--gray-600);
            border: 2px solid;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 2px var(--secondary-blue);
        }

        .question-number.answered {
            background-color: var(--secondary-blue);
            color: var(--white);
            border-color: var(--secondary-blue);
        }

        .quiz-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .submit-quiz-btn {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-blue-hover));
            color: var(--white);
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .submit-quiz-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .submit-quiz-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Right Content - Updated for Image Questions */
        .quiz-content {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .question-label {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .answer-instruction {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-weight: 500;
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
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .options-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: var(--white);
        }

        .option:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        .option.selected {
            border-color: var(--primary-red);
            background-color: rgba(225, 30, 45, 0.05);
        }

        .option-letter {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--gray-200);
            color: var(--gray-700);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .option.selected .option-letter {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .option-text {
            font-size: 1rem;
            color: var(--gray-700);
            font-weight: 500;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: auto;
        }

        .nav-btn {
            padding: 0.875rem 1.5rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.75rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover {
            border-color: var(--gray-400);
            background-color: var(--gray-50);
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .nav-btn.primary {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        .nav-btn.primary:hover {
            background-color: var(--primary-red-hover);
            border-color: var(--primary-red-hover);
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .main-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1rem;
            }

            .quiz-sidebar {
                position: static;
                order: -1;
            }

            .questions-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        @media (max-width: 768px) {
            .quiz-sidebar {
                padding: 1.5rem;
            }

            .quiz-content {
                padding: 1.5rem;
            }

            .options-container {
                grid-template-columns: 1fr;
            }

            .navigation-buttons {
                flex-direction: column;
            }

            .questions-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .question-text {
                font-size: 1.125rem;
            }

            .question-image-container {
                max-height: 250px;
            }

            .question-image {
                max-height: 200px;
            }
        }

        @media (max-width: 480px) {
            .quiz-sidebar {
                padding: 1rem;
            }

            .quiz-content {
                padding: 1rem;
            }

            .timer {
                font-size: 1.25rem;
                padding: 0.75rem 1rem;
            }

            .questions-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .question-number {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }

            .question-image-container {
                max-height: 200px;
                padding: 0.25rem;
            }

            .question-image {
                max-height: 150px;
            }
        }

        /* Quiz Completion Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            z-index: 2001;
            width: 90%;
            max-width: 500px;
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal {
            transform: translate(-50%, -50%) scale(1);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .modal-btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn.primary {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .modal-btn.secondary {
            background-color: var(--gray-200);
            color: var(--gray-700);
        }

        .modal-btn:hover {
            transform: translateY(-1px);
        }

        /* Progress Overview */
        .progress-overview {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .progress-overview-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .progress-overview-header i {
            color: var(--secondary-blue);
            font-size: 1.1rem;
        }

        .progress-overview-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .progress-chart-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .progress-ring-container {
            position: relative;
            width: 140px;
            height: 140px;
        }

        .progress-ring-container svg {
            transform: rotate(-90deg);
            width: 140px;
            height: 140px;
        }

        .progress-ring-bg {
            fill: none;
            stroke: var(--gray-200);
            stroke-width: 10;
        }

        .progress-ring-fill {
            fill: none;
            stroke: var(--secondary-blue);
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
        }

        .progress-ring-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .progress-ring-percent {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .progress-ring-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 0.25rem;
        }

        .progress-stats {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .progress-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .progress-stat-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .progress-stat-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .progress-stat-dot.answered {
            background-color: #22c55e;
        }

        .progress-stat-dot.unanswered {
            background-color: var(--gray-300);
        }

        .progress-stat-dot.skipped {
            background-color: #f59e0b;
        }

        .progress-stat-value {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--gray-800);
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <button class="back-button" onclick="confirmExit()">
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

    <!-- Main Layout -->
    <div class="main-layout">
        <!-- Left Column -->
        <div class="quiz-left-column">
            <div class="quiz-sidebar">
                <h2 class="quiz-title">{{ $quiz['title'] ?? 'Introduction to Computer Hardware' }}</h2>
                
                <div class="timer-container">
                    <div class="timer" id="timer">--:--</div>
                </div>
                
                <div class="questions-section">
                    <h3 class="questions-title">Questions</h3>
                    <div class="questions-grid" id="questionsGrid">
                        <!-- Questions will be populated by JavaScript -->
                    </div>
                </div>
                
                <div class="quiz-actions">
                    <button class="submit-quiz-btn" id="submitQuizBtn" onclick="submitQuiz()">Submit Quiz</button>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="progress-overview" id="progressOverview">
                <div class="progress-overview-header">
                    <i class="fas fa-info-circle"></i>
                    <span class="progress-overview-title">Progress Overview</span>
                </div>

                <div class="progress-chart-wrapper">
                    <div class="progress-ring-container">
                        <svg viewBox="0 0 140 140">
                            <circle class="progress-ring-bg" cx="70" cy="70" r="56"/>
                            <circle class="progress-ring-fill" id="progressRingFill" cx="70" cy="70" r="56"
                                stroke-dasharray="351.858" stroke-dashoffset="351.858"/>
                        </svg>
                        <div class="progress-ring-text">
                            <div class="progress-ring-percent" id="progressPercent">0%</div>
                            <div class="progress-ring-label">Done</div>
                        </div>
                    </div>
                </div>

                <div class="progress-stats">
                    <div class="progress-stat-row">
                        <span class="progress-stat-label">
                            <span class="progress-stat-dot answered"></span>
                            Answered
                        </span>
                        <span class="progress-stat-value" id="answeredCount">0/0</span>
                    </div>
                    <div class="progress-stat-row">
                        <span class="progress-stat-label">
                            <span class="progress-stat-dot unanswered"></span>
                            Unanswered
                        </span>
                        <span class="progress-stat-value" id="unansweredCount">0/0</span>
                    </div>
                    <div class="progress-stat-row">
                        <span class="progress-stat-label">
                            <span class="progress-stat-dot skipped"></span>
                            Skipped
                        </span>
                        <span class="progress-stat-value" id="skippedCount">0/0</span>
                    </div>
                </div>
            </div>
        </div> <!-- /.quiz-left-column -->

        <!-- Right Content -->
        <div class="quiz-content">
            <div class="question-header">
                <span class="question-label" id="questionLabel">Question 1 of 5</span>
                <span class="answer-instruction">Select your answer</span>
            </div>
            
            <!-- Image Container - Only shown when question has image -->
            <div class="question-image-container" id="questionImageContainer" style="display: none;">
                <img id="questionImage" class="question-image" src="" alt="Question Image">
            </div>
            
            <div class="question-text" id="questionText">
                Identify the component highlighted in the image above.
            </div>
            
            <div class="options-container" id="optionsContainer">
                <!-- Options will be populated by JavaScript -->
            </div>
            
            <div class="navigation-buttons">
                <button class="nav-btn" id="prevBtn" onclick="previousQuestion()" disabled>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </button>
                <button class="nav-btn primary" id="nextBtn" onclick="nextQuestion()">
                    Next
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Quiz Completion Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Submit Quiz?</h3>
                <p class="modal-subtitle">Are you sure you want to submit your quiz? You cannot change your answers after submission.</p>
            </div>
            <div class="modal-actions">
                <button class="modal-btn secondary" onclick="closeModal()">Cancel</button>
                <button class="modal-btn primary" onclick="confirmSubmit()">Submit</button>
            </div>
        </div>
    </div>

    @php
        $defaultQuestions = [
            [
                'id' => 1,
                'question' => 'Identify the component highlighted in the image above.',
                'image' => secure_asset('images/computer-hardware-cpu.png'),
                'options' => [
                    'Central Processing Unit (CPU)',
                    'Random Access Memory (RAM)',
                    'Graphics Processing Unit (GPU)',
                    'Power Supply Unit (PSU)'
                ],
                'correct_answer' => 0
            ],
            [
                'id' => 2,
                'question' => 'What is the main function of RAM in a computer system?',
                'image' => null,
                'options' => [
                    'Permanent storage of files',
                    'Temporary data storage for active processes',
                    'Processing graphics and videos',
                    'Power distribution to components'
                ],
                'correct_answer' => 1
            ],
            [
                'id' => 3,
                'question' => 'Which component is shown in this image?',
                'image' => secure_asset('images/computer-hardware-gpu.png'),
                'options' => [
                    'Motherboard',
                    'Hard Disk Drive',
                    'Graphics Card',
                    'Network Interface Card'
                ],
                'correct_answer' => 2
            ],
            [
                'id' => 4,
                'question' => 'What does this component do?',
                'image' => secure_asset('images/computer-hardware-psu.png'),
                'options' => [
                    'Processes audio signals',
                    'Converts AC to DC power',
                    'Manages network connections',
                    'Cools the system components'
                ],
                'correct_answer' => 1
            ],
            [
                'id' => 5,
                'question' => 'Which component connects all other components together?',
                'image' => secure_asset('images/computer-hardware-motherboard.png'),
                'options' => [
                    'Power Supply',
                    'Motherboard',
                    'CPU',
                    'Case'
                ],
                'correct_answer' => 1
            ]
        ];
    @endphp

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Quiz data and state
        let currentQuestion = 0;
        let timeLimitMinutes = {{ $quiz['time_limit_minutes'] ?? 3 }};
        let timeRemaining = timeLimitMinutes * 60; 
        let answers = {};
        let timerInterval;

        // Updated questions array to include images
        const questions = @json($quiz['questions'] ?? $defaultQuestions);

        document.addEventListener('DOMContentLoaded', function() {
            initializeQuiz();
            startTimer();
        });

        function initializeQuiz() {
            renderQuestionsGrid();
            renderCurrentQuestion();
            updateNavigationButtons();
            updateProgressOverview();
        }

        function renderQuestionsGrid() {
            const grid = document.getElementById('questionsGrid');
            grid.innerHTML = '';
            
            questions.forEach((question, index) => {
                const questionBtn = document.createElement('div');
                questionBtn.className = 'question-number';
                questionBtn.textContent = index + 1;
                questionBtn.onclick = () => goToQuestion(index);
                
                if (index === currentQuestion) {
                    questionBtn.classList.add('current');
                } else if (answers[index] !== undefined) {
                    questionBtn.classList.add('answered');
                }
                
                grid.appendChild(questionBtn);
            });
        }

        function renderCurrentQuestion() {
            if (!questions || questions.length === 0) {
                console.log('No questions available');
                document.getElementById('questionText').textContent = 'No questions available for this quiz.';
                return;
            }

            const question = questions[currentQuestion];
            console.log('Rendering question:', question);

            // Update question label
            document.getElementById('questionLabel').textContent = `Question ${currentQuestion + 1} of ${questions.length}`;
            
            // Handle question image
            const imageContainer = document.getElementById('questionImageContainer');
            const questionImage = document.getElementById('questionImage');

            if (question && question.image) {
                // Show image container and set image source
                imageContainer.style.display = 'block';
                questionImage.src = question.image;
                questionImage.alt = `Question ${currentQuestion + 1} Image`;
            } else {
                // Hide image container if no image
                imageContainer.style.display = 'none';
            }

            // Set question text
            document.getElementById('questionText').textContent = question ? question.question : 'Question not available';

            // Render options
            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';

            if (question && question.options && Array.isArray(question.options)) {
                question.options.forEach((option, index) => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'option';
                    optionDiv.onclick = () => selectOption(index);

                    if (answers[currentQuestion] === index) {
                        optionDiv.classList.add('selected');
                    }

                    optionDiv.innerHTML = `
                        <div class="option-letter">${String.fromCharCode(65 + index)}</div>
                        <div class="option-text">${option}</div>
                    `;

                    optionsContainer.appendChild(optionDiv);
                });
            } else {
                console.log('No options available for this question');
                optionsContainer.innerHTML = '<p>No answer options available.</p>';
            }
        }

        function selectOption(optionIndex) {
            answers[currentQuestion] = optionIndex;
            renderCurrentQuestion();
            renderQuestionsGrid();
            updateNavigationButtons();
            updateProgressOverview();
        }

        function goToQuestion(questionIndex) {
            currentQuestion = questionIndex;
            renderCurrentQuestion();
            renderQuestionsGrid();
            updateNavigationButtons();
        }

        function previousQuestion() {
            if (currentQuestion > 0) {
                currentQuestion--;
                renderCurrentQuestion();
                renderQuestionsGrid();
                updateNavigationButtons();
            }
        }

        function nextQuestion() {
            if (currentQuestion < questions.length - 1) {
                currentQuestion++;
                renderCurrentQuestion();
                renderQuestionsGrid();
                updateNavigationButtons();
            } else {
                // If on last question, show submit modal
                submitQuiz();
            }
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitQuizBtn');

            prevBtn.disabled = currentQuestion === 0;

            // Allow submission even if not all questions are answered
            submitBtn.disabled = false;

            // Change next button to "Finish" on last question
            if (currentQuestion === questions.length - 1) {
                nextBtn.textContent = 'Finish';
                nextBtn.onclick = submitQuiz;
                nextBtn.disabled = false;
            } else {
                nextBtn.innerHTML = `
                    Next
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                `;
                nextBtn.onclick = nextQuestion;
                nextBtn.disabled = false;
            }

            // Update submit button state in real-time
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }

        function startTimer() {
            const timerElement = document.getElementById('timer');
            
            // Set initial display
            const initialMinutes = Math.floor(timeRemaining / 60);
            const initialSeconds = timeRemaining % 60;
            timerElement.textContent = `${initialMinutes}:${initialSeconds.toString().padStart(2, '0')}`;
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                // Change timer color based on time remaining
                if (timeRemaining <= 60) { // 1 minute or less
                    timerElement.className = 'timer danger';
                } else if (timeRemaining <= 180) { // 3 minutes or less
                    timerElement.className = 'timer warning';
                }
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    autoSubmitQuiz();
                }
            }, 1000);
        }

        function submitQuiz() {
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        function confirmSubmit() {
            clearInterval(timerInterval);

            // Calculate score
            let correctAnswers = 0;
            questions.forEach((question, index) => {
                const userAnswer = answers[index];
                if (userAnswer !== undefined && userAnswer === question.correct_answer) {
                    correctAnswers++;
                }
            });

            const totalQuestions = questions.length;
            const percentage = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;

            // Show loading state
            const submitBtn = document.querySelector('.modal-btn.primary');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;

            // Submit the quiz
            console.log('Starting quiz submission...');
            console.log('Submitting to:', `/quiz/{{ $quiz['id'] ?? '1' }}/submit`);
            console.log('Form data:', {
                answers: answers,
                time_spent: (timeLimitMinutes * 60) - timeRemaining
            });

            // Create a form element and submit it normally
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/quiz/{{ $quiz['id'] ?? '1' }}/submit`;
            form.setAttribute('data-quiz-form', 'true');

            // Add hidden inputs for the data
            const answersInput = document.createElement('input');
            answersInput.type = 'hidden';
            answersInput.name = 'answers';
            answersInput.value = JSON.stringify(answers);
            form.appendChild(answersInput);

            const timeSpentInput = document.createElement('input');
            timeSpentInput.type = 'hidden';
            timeSpentInput.name = 'time_spent';
            timeSpentInput.value = (timeLimitMinutes * 60) - timeRemaining;
            form.appendChild(timeSpentInput);

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);

            // Append to body and submit
            document.body.appendChild(form);
            console.log('Submitting form...');
            form.submit();
        }

        function autoSubmitQuiz() {
            alert('Time is up! Your quiz will be submitted automatically.');
            confirmSubmit();
        }

        function confirmExit() {
            if (confirm('Are you sure you want to exit the quiz? Your progress will be lost.')) {
                clearInterval(timerInterval);
                window.history.back();
            }
        }

        // Prevent accidental page refresh (only when quiz is active and not submitting)
        window.addEventListener('beforeunload', function(e) {
            if (timeRemaining > 0 && Object.keys(answers).length > 0 && !document.querySelector('.modal-overlay.active')) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                previousQuestion();
            } else if (e.key === 'ArrowRight') {
                nextQuestion();
            } else if (e.key >= '1' && e.key <= '4') {
                const optionIndex = parseInt(e.key) - 1;
                selectOption(optionIndex);
            } else if (e.key >= 'a' && e.key <= 'd') {
                const optionIndex = e.key.charCodeAt(0) - 97; // 'a' = 0, 'b' = 1, etc.
                selectOption(optionIndex);
            }
        });

        // Progress Overview
        // Track which questions have been visited (navigated to)
        let visitedQuestions = new Set([0]); // Start with first question visited

        function updateProgressOverview() {
            const total = questions.length;
            const answeredCount = Object.keys(answers).length;
            // Skipped = visited but not answered (excluding the current question)
            let skippedCount = 0;
            visitedQuestions.forEach(qi => {
                if (qi !== currentQuestion && answers[qi] === undefined) {
                    skippedCount++;
                }
            });
            const unansweredCount = total - answeredCount - skippedCount;
            const percent = total > 0 ? Math.round((answeredCount / total) * 100) : 0;

            // Update ring
            const circumference = 2 * Math.PI * 56; // r=56
            const offset = circumference - (percent / 100) * circumference;
            document.getElementById('progressRingFill').setAttribute('stroke-dashoffset', offset);

            // Update text
            document.getElementById('progressPercent').textContent = percent + '%';
            document.getElementById('answeredCount').textContent = answeredCount + '/' + total;
            document.getElementById('unansweredCount').textContent = unansweredCount + '/' + total;
            document.getElementById('skippedCount').textContent = skippedCount + '/' + total;
        }

        // Override goToQuestion to track visited questions
        const _originalGoToQuestion = goToQuestion;
        goToQuestion = function(questionIndex) {
            visitedQuestions.add(questionIndex);
            _originalGoToQuestion(questionIndex);
            updateProgressOverview();
        };

        const _originalNextQuestion = nextQuestion;
        nextQuestion = function() {
            _originalNextQuestion();
            visitedQuestions.add(currentQuestion);
            updateProgressOverview();
        };

        const _originalPreviousQuestion = previousQuestion;
        previousQuestion = function() {
            _originalPreviousQuestion();
            visitedQuestions.add(currentQuestion);
            updateProgressOverview();
        };
    </script>
    @include('dashboard.quiz.partials.anti-cheat')
</body>
</html>
