<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz Results - {{ $quiz['title'] ?? 'Quiz' }} - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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
            --success-green: #10b981;
            --warning-yellow: #f59e0b;
            --error-red: #ef4444;
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
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
            padding: 1rem;
        }

        .results-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 1024px) {
            .results-wrapper {
                grid-template-columns: 2fr 1fr;
            }
        }

        /* Header Section */
        .results-header {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .results-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-red), var(--secondary-blue));
        }

        .quiz-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .quiz-subtitle {
            color: var(--gray-600);
            margin-bottom: 2rem;
        }

        /* Score Display */
        .score-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .score-circle {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .score-circle svg {
            transform: rotate(-90deg);
            width: 100%;
            height: 100%;
        }

        .score-circle-bg {
            fill: none;
            stroke: var(--gray-200);
            stroke-width: 8;
        }

        .score-circle-progress {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dasharray 2s ease-in-out;
        }

        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .score-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .score-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.25rem;
        }

        .score-details {
            text-align: left;
        }

        .score-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .score-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .score-icon.correct {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }

        .score-icon.incorrect {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-red);
        }

        .score-icon.time {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-yellow);
        }

        .score-info h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .score-info p {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        /* Performance Badge */
        .performance-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .performance-badge.excellent {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }

        .performance-badge.good {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-yellow);
        }

        .performance-badge.needs-improvement {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-red);
        }

        /* Question Review Section */
        .question-review {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            grid-column: 1 / -1;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .toggle-button {
            background: var(--gray-100);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .toggle-button:hover {
            background: var(--gray-200);
        }

        .toggle-button.active {
            background: var(--primary-red);
            color: var(--white);
        }

        .questions-list {
            display: none;
        }

        .questions-list.active {
            display: block;
        }

        .question-item {
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .question-item:hover {
            box-shadow: var(--shadow-md);
        }

        .question-header {
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }

        .question-number {
            font-weight: 600;
            color: var(--gray-700);
        }

        .question-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .question-status.correct {
            color: var(--success-green);
        }

        .question-status.incorrect {
            color: var(--error-red);
        }

        .question-content {
            padding: 1.5rem;
            display: none;
        }

        .question-content.active {
            display: block;
        }

        .question-text {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--gray-900);
        }

        .answer-option {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .answer-option.correct {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success-green);
            color: var(--success-green);
        }

        .answer-option.incorrect {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--error-red);
            color: var(--error-red);
        }

        .answer-option.user-selected {
            font-weight: 600;
        }

        .answer-option.user-selected::before {
            content: '→ ';
            font-weight: 700;
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Stats Card */
        .stats-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-red);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Actions Card */
        .actions-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 0.75rem;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .action-button:last-child {
            margin-bottom: 0;
        }

        .action-button.primary {
            background: var(--primary-red);
            color: var(--white);
        }

        .action-button.primary:hover {
            background: var(--primary-red-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-button.secondary {
            background: var(--secondary-blue);
            color: var(--white);
        }

        .action-button.secondary:hover {
            background: var(--secondary-blue-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-button.outline {
            background: var(--white);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .action-button.outline:hover {
            background: var(--gray-50);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Recommendations Card */
        .recommendations-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .recommendation-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .recommendation-item:last-child {
            margin-bottom: 0;
        }

        .recommendation-icon {
            width: 20px;
            height: 20px;
            color: var(--secondary-blue);
            margin-top: 0.125rem;
        }

        .recommendation-text {
            font-size: 0.875rem;
            color: var(--gray-700);
            line-height: 1.5;
        }

        /* Share Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.25rem;
        }

        .share-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .share-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            background: var(--white);
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .share-button:hover {
            background: var(--gray-50);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .share-icon {
            width: 24px;
            height: 24px;
        }

        .share-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes confetti {
            0% { transform: translateY(0) rotateZ(0deg); opacity: 1; }
            100% { transform: translateY(-1000px) rotateZ(720deg); opacity: 0; }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: var(--primary-red);
            animation: confetti 3s ease-out infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
            }

            .results-wrapper {
                gap: 1rem;
            }

            .results-header,
            .question-review,
            .stats-card,
            .actions-card,
            .recommendations-card {
                padding: 1.5rem;
            }

            .score-section {
                flex-direction: column;
                gap: 1.5rem;
            }

            .score-circle {
                width: 120px;
                height: 120px;
            }

            .score-number {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .share-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="results-wrapper">
        <!-- Main Results Section -->
        <div class="main-content">
            <!-- Header -->
            <div class="results-header animate-fade-in-up">
                <h1 class="quiz-title">{{ $quiz['title'] ?? 'Quiz Results' }}</h1>
                <p class="quiz-subtitle">{{ $quiz['subject'] ?? 'General Knowledge' }} • {{ $duration }} • {{ $quiz['difficulty'] ?? 'Medium' }} Level</p>
                
                <div class="score-section">
                    <div class="score-circle">
                        <svg viewBox="0 0 100 100">
                            <circle class="score-circle-bg" cx="50" cy="50" r="45"></circle>
                            <circle 
                                class="score-circle-progress" 
                                cx="50" 
                                cy="50" 
                                r="45"
                                stroke="{{ $percentage >= 80 ? 'var(--success-green)' : ($percentage >= 60 ? 'var(--warning-yellow)' : 'var(--error-red)') }}"
                                stroke-dasharray="0 283"
                                data-percentage="{{ $percentage }}"
                            ></circle>
                        </svg>
                        <div class="score-text">
                            <div class="score-number">{{ $percentage }}%</div>
                            <div class="score-label">Score</div>
                        </div>
                    </div>
                    
                    <div class="score-details">
                        <div class="score-item">
                            <div class="score-icon correct">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="score-info">
                                <h3>{{ $score }} Correct</h3>
                                <p>Out of {{ $total }} questions</p>
                            </div>
                        </div>
                        
                        <div class="score-item">
                            <div class="score-icon incorrect">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="score-info">
                                <h3>{{ $total - $score }} Incorrect</h3>
                                <p>Need more practice</p>
                            </div>
                        </div>
                        
                        <div class="score-item">
                            <div class="score-icon time">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="score-info">
                                <h3>{{ $duration }}</h3> {{-- Changed this line --}}
                                <p>Time allocated</p> {{-- Changed text --}}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="performance-badge {{ $percentage >= 80 ? 'excellent' : ($percentage >= 60 ? 'good' : 'needs-improvement') }}">
                    <i class="fas fa-{{ $percentage >= 80 ? 'trophy' : ($percentage >= 60 ? 'thumbs-up' : 'book') }}"></i>
                    @if($percentage >= 80)
                        Excellent Performance!
                    @elseif($percentage >= 60)
                        Good Job!
                    @else
                        Keep Practicing!
                    @endif
                </div>
            </div>
            
            <!-- Question Review -->
            <div class="question-review animate-fade-in-up">
                <div class="section-header">
                    <h2 class="section-title">Question Review</h2>
                    <button class="toggle-button" id="toggleReview">
                        <i class="fas fa-eye"></i> Show Details
                    </button>
                </div>
                
                <div class="questions-list" id="questionsList">
                    @if(isset($questions) && is_array($questions))
                            @foreach($questions as $index => $question)
                            <div class="question-item">
                                <div class="question-header" onclick="toggleQuestion({{ $index }})">
                                    <span class="question-number">Question {{ $index + 1 }}</span>
                                    <div class="question-status {{ $question['user_correct'] ? 'correct' : 'incorrect' }}">
                                        <i class="fas fa-{{ $question['user_correct'] ? 'check-circle' : 'times-circle' }}"></i>
                                        {{ $question['user_correct'] ? 'Correct' : 'Incorrect' }}
                                    </div>
                                </div>
                                <div class="question-content" id="question-{{ $index }}">
                                    <div class="question-text">{{ $question['question'] }}</div>
                                    @foreach($question['options'] as $optionIndex => $option)
                                        <div class="answer-option
                                            {{ $optionIndex == $question['correct_answer'] ? 'correct' : '' }}
                                            {{ $optionIndex == $question['user_answer'] ? 'user-selected' : '' }}
                                            {{ $optionIndex == $question['correct_answer'] && $optionIndex == $question['user_answer'] ? 'correct user-selected' : '' }}
                                            {{ $optionIndex != $question['correct_answer'] && $optionIndex == $question['user_answer'] ? 'incorrect user-selected' : '' }}
                                        ">
                                            {{ $option }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-center text-gray-500">Question details not available for review.</p>
                        @endif
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Stats Card -->
            <div class="stats-card animate-fade-in-up">
                <h3 class="section-title">Performance Stats</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $percentage }}%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $duration }}</div> {{-- Changed this line --}}
                        <div class="stat-label">Duration</div> {{-- Changed text --}}
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $rank ?? '12th' }}</div>
                        <div class="stat-label">Rank</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $streak ?? '3' }}</div>
                        <div class="stat-label">Streak</div>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="actions-card animate-fade-in-up">
                <h3 class="section-title">Actions</h3>
                <a href="{{ route('quiz.take', $quiz['id'] ?? 1) }}" class="action-button primary">
                    <i class="fas fa-redo"></i>
                    Retake Quiz
                </a>
                <a href="{{ route('quiz.index') }}" class="action-button secondary">
                    <i class="fas fa-list"></i>
                    More Quizzes
                </a>
                <button class="action-button outline" onclick="openShareModal()">
                    <i class="fas fa-share"></i>
                    Share Results
                </button>
                <a href="{{ route('dashboard.digilearn') }}" class="action-button outline">
                    <i class="fas fa-video"></i>
                    Watch Lessons
                </a>
            </div>
            
            <!-- Recommendations Card -->
            <div class="recommendations-card animate-fade-in-up">
                <h3 class="section-title">Recommendations</h3>
                @if($percentage >= 80)
                    <div class="recommendation-item">
                        <i class="fas fa-star recommendation-icon"></i>
                        <div class="recommendation-text">
                            Excellent work! Try a harder difficulty level to challenge yourself further.
                        </div>
                    </div>
                    <div class="recommendation-item">
                        <i class="fas fa-trophy recommendation-icon"></i>
                        <div class="recommendation-text">
                            Consider taking the advanced quiz in this subject area.
                        </div>
                    </div>
                @elseif($percentage >= 60)
                    <div class="recommendation-item">
                        <i class="fas fa-book recommendation-icon"></i>
                        <div class="recommendation-text">
                            Good progress! Review the topics you missed and try again.
                        </div>
                    </div>
                    <div class="recommendation-item">
                        <i class="fas fa-video recommendation-icon"></i>
                        <div class="recommendation-text">
                            Watch related video lessons to strengthen your understanding.
                        </div>
                    </div>
                @else
                    <div class="recommendation-item">
                        <i class="fas fa-study recommendation-icon"></i>
                        <div class="recommendation-text">
                            Focus on studying the fundamentals before retaking this quiz.
                        </div>
                    </div>
                    <div class="recommendation-item">
                        <i class="fas fa-users recommendation-icon"></i>
                        <div class="recommendation-text">
                            Consider joining study groups or getting help from a tutor.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Share Modal -->
    <div class="modal-overlay" id="shareModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Share Your Results</h3>
                <button class="modal-close" onclick="closeShareModal()">×</button>
            </div>
            <div class="share-options">
                <a href="#" class="share-button" onclick="shareToTwitter()">
                    <i class="fab fa-twitter share-icon" style="color: #1DA1F2;"></i>
                    <span class="share-label">Twitter</span>
                </a>
                <a href="#" class="share-button" onclick="shareToFacebook()">
                    <i class="fab fa-facebook share-icon" style="color: #4267B2;"></i>
                    <span class="share-label">Facebook</span>
                </a>
                <a href="#" class="share-button" onclick="shareToWhatsApp()">
                    <i class="fab fa-whatsapp share-icon" style="color: #25D366;"></i>
                    <span class="share-label">WhatsApp</span>
                </a>
                <a href="#" class="share-button" onclick="copyLink()">
                    <i class="fas fa-link share-icon" style="color: #6B7280;"></i>
                    <span class="share-label">Copy Link</span>
                </a>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Animate score circle
            animateScoreCircle();
            
            // Add confetti for excellent scores
            @if($percentage >= 80)
                createConfetti();
            @endif
            
            // Initialize question review toggle
            initializeQuestionReview();
        });

        function animateScoreCircle() {
            const circle = document.querySelector('.score-circle-progress');
            const percentage = circle.getAttribute('data-percentage');
            const circumference = 2 * Math.PI * 45; // radius = 45
            const offset = circumference - (percentage / 100) * circumference;
            
            setTimeout(() => {
                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = offset;
            }, 500);
        }

        function createConfetti() {
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.backgroundColor = ['#E11E2D', '#2677B8', '#10b981', '#f59e0b'][Math.floor(Math.random() * 4)];
                    confetti.style.animationDelay = Math.random() * 3 + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.remove();
                    }, 3000);
                }, i * 100);
            }
        }

        function initializeQuestionReview() {
            const toggleButton = document.getElementById('toggleReview');
            const questionsList = document.getElementById('questionsList');
            
            toggleButton.addEventListener('click', function() {
                questionsList.classList.toggle('active');
                const isActive = questionsList.classList.contains('active');
                
                this.innerHTML = isActive 
                    ? '<i class="fas fa-eye-slash"></i> Hide Details'
                    : '<i class="fas fa-eye"></i> Show Details';
                
                this.classList.toggle('active', isActive);
            });
        }

        function toggleQuestion(index) {
            const content = document.getElementById(`question-${index}`);
            content.classList.toggle('active');
        }

        function openShareModal() {
            document.getElementById('shareModal').classList.add('active');
        }

        function closeShareModal() {
            document.getElementById('shareModal').classList.remove('active');
        }

        function shareToTwitter() {
            const text = `I just scored {{ $percentage }}% on the {{ $quiz['title'] ?? 'quiz' }}! 🎉`;
            const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(window.location.href)}`;
            window.open(url, '_blank');
        }

        function shareToFacebook() {
            const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`;
            window.open(url, '_blank');
        }

        function shareToWhatsApp() {
            const text = `I just scored {{ $percentage }}% on the {{ $quiz['title'] ?? 'quiz' }}! Check it out: ${window.location.href}`;
            const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
            window.open(url, '_blank');
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
                closeShareModal();
            });
        }

        // Close modal when clicking outside
        document.getElementById('shareModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeShareModal();
            }
        });
    </script>
</body>
</html>
