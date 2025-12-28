<div class="quiz-card" data-quiz-id="{{ $quiz['id'] }}">
    <!-- Quiz Header with Icon and Badge -->
    <div class="quiz-header">
        <div class="quiz-icon-container">
            <svg class="quiz-main-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="quiz-level-badge">{{ $quiz['level_display'] ?? 'Level 1' }}</div>
        <div class="quiz-difficulty">
            @php
                $difficulty = $quiz['difficulty'] ?? 'medium';
                $difficultyColor = match($difficulty) {
                    'easy' => '#10B981',
                    'medium' => '#F59E0B', 
                    'hard' => '#EF4444',
                    default => '#6B7280'
                };
            @endphp
            <span class="difficulty-dot" style="background-color: {{ $difficultyColor }}"></span>
            {{ ucfirst($difficulty) }}
        </div>
    </div>

    <!-- Quiz Content -->
    <div class="quiz-content">
        <h3 class="quiz-title">{{ $quiz['title'] }}</h3>
        <p class="quiz-description">{{ $quiz['description'] ?? 'Test your knowledge with this comprehensive quiz covering key concepts and practical applications.' }}</p>
        
        <!-- Quiz Stats -->
        <div class="quiz-stats">
            <div class="stat-item">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $quiz['questions_count'] ?? '10' }} Questions</span>
            </div>
            <div class="stat-item">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $quiz['time_limit_minutes'] ? $quiz['time_limit_minutes'] . ' min' : '15 min' }}</span>
            </div>
            <div class="stat-item">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>{{ number_format($quiz['total_attempts_count'] ?? 0) }} attempts</span>
            </div>
            <div class="stat-item">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.168 18.477 18.582 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span class="quiz-subject">{{ $quiz['subject'] ?? 'General' }}</span>
            </div>
        </div>

        <!-- Reviews Link -->
        @if(($quiz['total_ratings'] ?? 0) > 0)
        <div class="quiz-reviews-link">
            <a href="#" onclick="openReviewsModal({{ $quiz['id'] }}); return false;" class="reviews-link">
                <i class="fas fa-star"></i>
                See reviews
            </a>
        </div>
        @endif

        <!-- Quiz Progress (only if user has attempted) -->
        @if(($quiz['attempts_count'] ?? 0) > 0 && ($quiz['user_progress'] ?? 0) > 0)
        <div class="quiz-progress">
            <div class="progress-label">
                <span>Your Progress</span>
                <span>{{ $quiz['user_progress'] }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $quiz['user_progress'] }}%"></div>
            </div>
        </div>
        @endif

        <!-- Quiz Actions -->
        <div class="quiz-actions">
            <button class="quiz-start-btn">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.01M15 10h1.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @if(isset($quiz['user_progress']) && $quiz['user_progress'] == 100)
                    Retake Quiz
                @elseif(isset($quiz['user_progress']) && $quiz['user_progress'] > 0)
                    Continue Quiz
                @else
                    Start Quiz
                @endif
            </button>
            @if(($quiz['attempts_count'] ?? 0) > 0)
                <button class="quiz-preview-btn">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    Revise Quiz
                </button>
            @endif
        </div>
    </div>

    <!-- Quiz Footer -->
    <div class="quiz-footer">
        <div class="quiz-meta">
            <span class="quiz-attempts">
                @if(isset($quiz['attempts_count']) && $quiz['attempts_count'] > 0)
                    {{ $quiz['attempts_count'] }} attempt{{ $quiz['attempts_count'] > 1 ? 's' : '' }}
                @else
                    Not attempted
                @endif
            </span>
            <span class="quiz-rating">
                @if(isset($quiz['user_rating']) && $quiz['user_rating'] > 0)
                    <div class="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="star {{ $i <= $quiz['user_rating'] ? 'filled' : '' }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        @endfor
                    </div>
                @else
                    <span class="no-rating">Not rated</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Reviews Modal -->
    <div class="reviews-modal-overlay" id="reviewsModal-{{ $quiz['id'] }}">
        <div class="reviews-modal">
            <div class="reviews-modal-header">
                <h3>Reviews for {{ $quiz['title'] }}</h3>
                <button class="reviews-modal-close" onclick="closeReviewsModal({{ $quiz['id'] }})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="reviews-modal-content" id="reviewsContent-{{ $quiz['id'] }}">
                <div class="reviews-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading reviews...</p>
                </div>
            </div>
        </div>
    </div>
</div>
