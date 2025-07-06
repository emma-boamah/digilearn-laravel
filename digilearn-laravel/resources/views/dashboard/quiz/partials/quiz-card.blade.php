<div class="quiz-card" data-quiz-id="{{ $quiz['id'] }}">
    <div class="quiz-thumbnail">
        <div class="quiz-icon-overlay">
            <svg class="quiz-icon" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="quiz-duration">{{ $quiz['duration'] }}</div>
        <div class="quiz-level-badge">{{ $quiz['level_display'] }}</div>
    </div>
    <div class="quiz-info">
        <h3 class="quiz-title">{{ $quiz['title'] }}</h3>
        <div class="quiz-meta">
            <span class="quiz-subject">({{ $quiz['subject'] }})</span>
            <span>{{ $quiz['questions_count'] }} Questions</span>
        </div>
        <div class="quiz-actions">
            <a href="{{ route('quiz.instructions', ['quizId' => $quiz['id']]) }}" class="quiz-open-btn">Open</a>
        </div>
    </div>
</div>
