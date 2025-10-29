<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Progress - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --success-green: #10b981;
            --warning-yellow: #f59e0b;
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
        }

        /* Header */
        .header {
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: var(--gray-600);
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1rem;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Progress Overview Cards */
        .progress-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .progress-card:hover {
            box-shadow: var(--shadow-md);
        }

        .progress-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .progress-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .progress-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .progress-card-icon.lessons {
            background-color: var(--secondary-blue);
        }

        .progress-card-icon.quizzes {
            background-color: var(--primary-red);
        }

        .progress-card-icon.overall {
            background-color: var(--success-green);
        }

        .progress-card-icon.level {
            background-color: var(--warning-yellow);
        }

        .progress-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .progress-label {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Progress Bars */
        .progress-bar-container {
            margin-top: 1rem;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-red), var(--secondary-blue));
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-percentage {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-top: 0.5rem;
        }

        /* Level Progression Section */
        .level-progression {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            margin-bottom: 2rem;
        }

        .level-progression.eligible {
            border-color: var(--success-green);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02));
        }

        .level-progression-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .level-progression-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .progression-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .progression-status.eligible {
            background-color: var(--success-green);
            color: var(--white);
        }

        .progression-status.not-eligible {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }

        .level-path {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .level-box {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-align: center;
            min-width: 120px;
        }

        .level-box.current {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .level-box.next {
            background-color: var(--gray-100);
            color: var(--gray-600);
            border: 2px dashed var(--gray-300);
        }

        .level-box.next.eligible {
            background-color: var(--success-green);
            color: var(--white);
            border: 2px solid var(--success-green);
        }

        .arrow-icon {
            color: var(--gray-400);
            font-size: 1.5rem;
        }

        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
        }

        .requirement-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.75rem;
        }

        .requirement-icon.met {
            background-color: var(--success-green);
        }

        .requirement-icon.not-met {
            background-color: var(--gray-400);
        }

        .requirement-text {
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        /* Activity Sections */
        .activity-section {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
        }

        .activity-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .activity-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .activity-item:hover {
            background-color: var(--gray-100);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            flex-shrink: 0;
        }

        .activity-icon.lesson {
            background-color: var(--secondary-blue);
        }

        .activity-icon.quiz {
            background-color: var(--primary-red);
        }

        .activity-content {
            flex: 1;
        }

        .activity-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .activity-meta {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .activity-score {
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .activity-score.passed {
            background-color: var(--success-green);
            color: var(--white);
        }

        .activity-score.failed {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .activity-score.completed {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .header {
                padding: 1rem;
            }

            .progress-overview {
                grid-template-columns: 1fr;
            }

            .level-path {
                flex-direction: column;
                text-align: center;
            }

            .arrow-icon {
                transform: rotate(90deg);
            }

            .requirements-grid {
                grid-template-columns: 1fr;
            }

            .activity-item {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }
        }

        /* Animations */
        @keyframes progressFill {
            from { width: 0%; }
            to { width: var(--progress-width); }
        }

        .progress-bar-fill {
            animation: progressFill 1s ease-out;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-500);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        .empty-state-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        .empty-state-description {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <button class="back-button" onclick="history.back()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h1 class="page-title">My Progress</h1>
            </div>
            <div class="user-avatar">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Progress Overview Cards -->
        <div class="progress-overview">
            <div class="progress-card">
                <div class="progress-card-header">
                    <h3 class="progress-card-title">Lessons Progress</h3>
                    <div class="progress-card-icon lessons">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value">{{ $progress->completed_lessons ?? 0 }}/{{ $progress->total_lessons_in_level ?? 0 }}</div>
                <div class="progress-label">Lessons Completed</div>
                <div class="progress-bar-container">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="--progress-width: {{ $progress->total_lessons_in_level > 0 ? ($progress->completed_lessons / $progress->total_lessons_in_level) * 100 : 0 }}%; width: {{ $progress->total_lessons_in_level > 0 ? ($progress->completed_lessons / $progress->total_lessons_in_level) * 100 : 0 }}%;"></div>
                    </div>
                    <div class="progress-percentage">{{ $progress->total_lessons_in_level > 0 ? round(($progress->completed_lessons / $progress->total_lessons_in_level) * 100, 1) : 0 }}% Complete</div>
                </div>
            </div>

            <div class="progress-card">
                <div class="progress-card-header">
                    <h3 class="progress-card-title">Quiz Performance</h3>
                    <div class="progress-card-icon quizzes">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value">{{ $progress->completed_quizzes ?? 0 }}/{{ $progress->total_quizzes_in_level ?? 0 }}</div>
                <div class="progress-label">Quizzes Passed</div>
                <div class="progress-bar-container">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="--progress-width: {{ $progress->total_quizzes_in_level > 0 ? ($progress->completed_quizzes / $progress->total_quizzes_in_level) * 100 : 0 }}%; width: {{ $progress->total_quizzes_in_level > 0 ? ($progress->completed_quizzes / $progress->total_quizzes_in_level) * 100 : 0 }}%;"></div>
                    </div>
                    <div class="progress-percentage">{{ round($progress->average_quiz_score ?? 0, 1) }}% Average Score</div>
                </div>
            </div>

            <div class="progress-card">
                <div class="progress-card-header">
                    <h3 class="progress-card-title">Overall Progress</h3>
                    <div class="progress-card-icon overall">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value">{{ round($progress->completion_percentage ?? 0, 1) }}%</div>
                <div class="progress-label">Level Completion</div>
                <div class="progress-bar-container">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="--progress-width: {{ $progress->completion_percentage ?? 0 }}%; width: {{ $progress->completion_percentage ?? 0 }}%;"></div>
                    </div>
                    <div class="progress-percentage">{{ $progress->level_completed ? 'Level Completed!' : 'In Progress' }}</div>
                </div>
            </div>

            <div class="progress-card">
                <div class="progress-card-header">
                    <h3 class="progress-card-title">Current Level</h3>
                    <div class="progress-card-icon level">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value">{{ ucwords(str_replace('-', ' ', $currentLevel)) }}</div>
                <div class="progress-label">Learning Level</div>
                <div class="progress-bar-container">
                    <div class="progress-percentage">
                        @if($progress->level_started_at)
                            Started {{ $progress->level_started_at->diffForHumans() }}
                        @else
                            Just started
                        @endif
                    </div>
                </div>
            </div>

            <div class="progress-card">
                <div class="progress-card-header">
                    <h3 class="progress-card-title">Time Invested</h3>
                    <div class="progress-card-icon" style="background-color: var(--warning-yellow); color: var(--gray-700);">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value">{{ $analytics['engagement']['time_spent_formatted'] ?? '0m' }}</div>
                <div class="progress-label">Total Time Spent</div>
                <div class="progress-bar-container">
                    <div class="progress-percentage">
                        Active for {{ $analytics['level_info']['duration'] ?? 'Just started' }}
                    </div>
                </div>
            </div>

            <div class="progress-card">
                <div class="progress-card-header">
                    <h3 class="progress-card-title">Learning Streak</h3>
                    <div class="progress-card-icon" style="background-color: var(--primary-red);">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                            <path d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value">{{ $analytics['engagement']['current_streak'] ?? 0 }}</div>
                <div class="progress-label">Day Streak</div>
                <div class="progress-bar-container">
                    <div class="progress-percentage">
                        Best: {{ $analytics['engagement']['longest_streak'] ?? 0 }} days
                    </div>
                </div>
            </div>
        </div>

        <!-- Level Progression Section -->
        <div class="level-progression {{ $progressionStatus['eligible'] ? 'eligible' : '' }}">
            <div class="level-progression-header">
                <h2 class="level-progression-title">Level Progression</h2>
                <div class="progression-status {{ $progressionStatus['eligible'] ? 'eligible' : 'not-eligible' }}">
                    @if($progressionStatus['eligible'])
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ready to Progress!
                    @else
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Keep Learning
                    @endif
                </div>
            </div>

            <div class="level-path">
                <div class="level-box current">
                    <div>Current Level</div>
                    <div>{{ ucwords(str_replace('-', ' ', $progressionStatus['current_level'])) }}</div>
                </div>
                <div class="arrow-icon">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 7l5 5-5 5M6 12h12"/>
                    </svg>
                </div>
                <div class="level-box next {{ $progressionStatus['eligible'] ? 'eligible' : '' }}">
                    <div>Next Level</div>
                    <div>{{ $progressionStatus['next_level'] ? ucwords(str_replace('-', ' ', $progressionStatus['next_level'])) : 'Final Level' }}</div>
                </div>
            </div>

            @if($progressionStatus['next_level'])
            <div class="requirements-grid">
                <div class="requirement-item">
                    <div class="requirement-icon {{ $progressionStatus['progress_data']['lesson_completion_rate'] >= 80 ? 'met' : 'not-met' }}">
                        @if($progressionStatus['progress_data']['lesson_completion_rate'] >= 80)
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <div class="requirement-text">
                        <strong>{{ round($progressionStatus['progress_data']['lesson_completion_rate'], 1) }}%</strong> lessons completed
                        <br><small>Need 80% to progress</small>
                    </div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon {{ $progressionStatus['progress_data']['quiz_completion_rate'] >= 70 ? 'met' : 'not-met' }}">
                        @if($progressionStatus['progress_data']['quiz_completion_rate'] >= 70)
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <div class="requirement-text">
                        <strong>{{ round($progressionStatus['progress_data']['quiz_completion_rate'], 1) }}%</strong> quizzes passed
                        <br><small>Need 70% to progress</small>
                    </div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon {{ $progressionStatus['progress_data']['average_score'] >= 70 ? 'met' : 'not-met' }}">
                        @if($progressionStatus['progress_data']['average_score'] >= 70)
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <div class="requirement-text">
                        <strong>{{ round($progressionStatus['progress_data']['average_score'], 1) }}%</strong> average quiz score
                        <br><small>Need 70% to progress</small>
                    </div>
                </div>
            </div>
            @else
            <div style="text-align: center; padding: 2rem; color: var(--gray-600);">
                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24" style="margin-bottom: 1rem; color: var(--warning-yellow);">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <h3 style="margin-bottom: 0.5rem; color: var(--gray-700);">Congratulations!</h3>
                <p>You've reached the highest level available. Keep practicing to maintain your skills!</p>
            </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <!-- Recent Lessons -->
            <div class="activity-section">
                <div class="activity-header">
                    <h3 class="activity-title">Recent Lessons</h3>
                </div>
                @if($recentLessons && $recentLessons->count() > 0)
                    <div class="activity-list">
                        @foreach($recentLessons as $lesson)
                        <div class="activity-item">
                            <div class="activity-icon lesson">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <div class="activity-name">{{ $lesson->lesson_title }}</div>
                                <div class="activity-meta">{{ $lesson->lesson_subject }} • {{ $lesson->last_watched_at->diffForHumans() }}</div>
                            </div>
                            <div class="activity-score {{ $lesson->fully_completed ? 'completed' : '' }}">
                                {{ $lesson->fully_completed ? 'Completed' : round($lesson->completion_percentage, 1) . '%' }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <div class="empty-state-title">No lessons yet</div>
                        <div class="empty-state-description">Start watching lessons to see your progress here</div>
                    </div>
                @endif
            </div>

            <!-- Recent Quizzes -->
            <div class="activity-section">
                <div class="activity-header">
                    <h3 class="activity-title">Recent Quizzes</h3>
                </div>
                @if($recentQuizzes && $recentQuizzes->count() > 0)
                    <div class="activity-list">
                        @foreach($recentQuizzes as $quiz)
                        <div class="activity-item">
                            <div class="activity-icon quiz">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <div class="activity-name">{{ $quiz->quiz_title }}</div>
                                <div class="activity-meta">{{ $quiz->quiz_subject }} • {{ $quiz->completed_at->diffForHumans() }}</div>
                            </div>
                            <div class="activity-score {{ $quiz->passed ? 'passed' : 'failed' }}">
                                {{ round($quiz->score_percentage, 1) }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <div class="empty-state-title">No quizzes yet</div>
                        <div class="empty-state-description">Take quizzes to test your knowledge and track progress</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Achievements & Milestones -->
        @if(isset($analytics['milestones']) && count($analytics['milestones']) > 0)
        <div class="activity-section">
            <div class="activity-header">
                <h3 class="activity-title">Achievements & Milestones</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                @foreach($analytics['milestones'] as $milestone)
                <div class="activity-item" style="text-align: center; padding: 1.5rem;">
                    <div class="activity-icon" style="width: 60px; height: 60px; margin: 0 auto 1rem; font-size: 1.5rem;">
                        {{ $milestone['icon'] }}
                    </div>
                    <div class="activity-content">
                        <div class="activity-name" style="font-weight: 700; color: var(--gray-900);">
                            {{ $milestone['title'] }}
                        </div>
                        <div class="activity-meta" style="color: var(--gray-600);">
                            @if($milestone['type'] === 'lessons')
                                Completed {{ $milestone['count'] }} lessons
                            @elseif($milestone['type'] === 'quizzes')
                                @if($milestone['count'] >= 80)
                                    Achieved {{ $milestone['count'] }}% average score
                                @else
                                    Passed {{ $milestone['count'] }} quizzes
                                @endif
                            @elseif($milestone['type'] === 'streak')
                                {{ $milestone['count'] }}-day learning streak
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Progression History -->
        @if($progressionHistory && $progressionHistory->count() > 0)
        <div class="activity-section">
            <div class="activity-header">
                <h3 class="activity-title">Progression History</h3>
            </div>
            <div class="activity-list">
                @foreach($progressionHistory as $progression)
                <div class="activity-item">
                    <div class="activity-icon" style="background-color: var(--success-green);">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 7l5 5-5 5M6 12h12"/>
                        </svg>
                    </div>
                    <div class="activity-content">
                        <div class="activity-name">
                            Progressed from {{ ucwords(str_replace('-', ' ', $progression->from_level)) }}
                            to {{ ucwords(str_replace('-', ' ', $progression->to_level)) }}
                        </div>
                        <div class="activity-meta">
                            {{ $progression->progressed_at->format('M j, Y') }} •
                            {{ $progression->lessons_completed }} lessons •
                            {{ $progression->quizzes_passed }} quizzes passed
                        </div>
                    </div>
                    <div class="activity-score passed">
                        {{ round($progression->final_score, 1) }}%
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Check for progression eligibility periodically
            checkProgressionStatus();
            
            // Set up periodic checks (every 30 seconds)
            setInterval(checkProgressionStatus, 30000);
        });

        function checkProgressionStatus() {
            const currentLevel = '{{ $currentLevel }}';
            
            fetch(`/dashboard/progress/check/${currentLevel}`)
                .then(response => response.json())
                .then(data => {
                    if (data.eligible && !document.querySelector('.progression-status.eligible')) {
                        // Show progression notification
                        showProgressionNotification(data);
                    }
                })
                .catch(error => {
                    console.error('Error checking progression status:', error);
                });
        }

        function showProgressionNotification(data) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, var(--success-green), #059669);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.75rem;
                box-shadow: var(--shadow-xl);
                z-index: 1000;
                max-width: 300px;
                animation: slideIn 0.3s ease;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Level Up Available!</div>
                        <div style="font-size: 0.875rem; opacity: 0.9;">${data.message}</div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
            
            // Add click to dismiss
            notification.addEventListener('click', () => {
                notification.remove();
            });
        }

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
