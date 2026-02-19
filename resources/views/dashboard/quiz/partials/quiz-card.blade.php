<div class="quiz-card minimalist-quiz-card" data-quiz-id="{{ $quiz['id'] }}" data-encoded-quiz-id="{{ $quiz['encoded_id'] }}">
    <!-- Card Top Section: Icon + Title + Rating -->
    <div class="card-top">
        <div class="icon-container">
            <svg class="quiz-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        
        <div class="title-section">
            <h3 class="card-title" title="{{ $quiz['title'] }}">{{ $quiz['title'] }}</h3>
            
            <!-- Rating Stars -->
            <div class="rating-stars">
                @php
                    $avgRating = $quiz['average_rating'] ?? 0;
                    $fullStars = floor($avgRating);
                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                @endphp
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $fullStars)
                        <i class="fas fa-star star filled"></i>
                    @elseif($hasHalfStar && $i == $fullStars + 1)
                        <i class="fas fa-star-half-alt star filled"></i>
                    @else
                        <i class="far fa-star star"></i>
                    @endif
                @endfor
            </div>
        </div>
    </div>

    <!-- Card Meta: Time, Questions, Difficulty -->
    <div class="card-meta">
        <div class="meta-item">
            <i class="far fa-clock"></i>
            <span>{{ $quiz['time_limit_minutes'] ? $quiz['time_limit_minutes'] . ' mins' : '15 mins' }}</span>
        </div>
        <div class="meta-item">
            <i class="far fa-question-circle"></i>
            <span>{{ $quiz['questions_count'] ?? '0' }} {{ Str::plural('question', $quiz['questions_count'] ?? 0) }}</span>
        </div>
        
        @php
            $difficulty = $quiz['difficulty'] ?? 'medium';
            $difficultyColor = match($difficulty) {
                'easy' => '#10B981',
                'medium' => '#F59E0B', 
                'hard' => '#EF4444',
                default => '#6B7280'
            };
        @endphp
        <div class="meta-item difficulty">
            <i class="fas fa-signal" style="color: {{ $difficultyColor }}"></i>
            <span>{{ ucfirst($difficulty) }}</span>
        </div>
    </div>

    <!-- Action Button -->
    <div class="card-action">
        <button class="start-btn quiz-start-btn">
            <i class="fas fa-play"></i>
            @if(isset($quiz['user_progress']) && $quiz['user_progress'] == 100)
                Retake Quiz
            @elseif(isset($quiz['user_progress']) && $quiz['user_progress'] > 0)
                Continue
            @else
                Start Quiz
            @endif
        </button>
    </div>
</div>
