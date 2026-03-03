@php
    $isUniversity = isset($universityCourses);
    $item = $isUniversity ? $course : $lesson;
    $id = $item['id'];
    $encodedId = \App\Services\UrlObfuscator::encode($id);
    $quizId = $item['quiz_id'] ?? null;
    $encodedQuizId = $item['encoded_quiz_id'] ?? ($quizId ? \App\Services\UrlObfuscator::encode($quizId) : null);
@endphp

<x-video-facade
    videoId="{{ $id }}"
    videoSource="{{ $item['video_source'] ?? 'local' }}"
    vimeoId="{{ $item['vimeo_id'] ?? '' }}"
    externalVideoId="{{ $item['external_video_id'] ?? '' }}"
    muxPlaybackId="{{ $item['mux_playback_id'] ?? '' }}"
    thumbnail="{{ $item['thumbnail'] }}"
    :title="$item['title']"
    duration="{{ $item['duration'] }}"
    :levelDisplay="$item['level_display'] ?? ($isUniversity ? 'Course' : 'Level')"
    :subject="$item['subject']"
    data-subject="{{ $item['subject_slug'] }}"
    :instructor="$item['instructor']"
    year="{{ $item['year'] }}"
    lessonId="{{ $encodedId }}"
    courseId="{{ $isUniversity ? $encodedId : null }}"
    :showLevelBadge="true"
    :showDuration="true"
    :showPlayOverlay="true"
    :lazyLoad="true"
>
    @if($isUniversity)
        @if(isset($item['description']))
        <p class="course-description" style="font-size: 0.875rem; color: var(--gray-600); margin: 0.5rem 0;">
            {{ $item['description'] }}
        </p>
        @endif
        @if(isset($item['lessons_count']))
        <p class="course-lessons-count" style="font-size: 0.75rem; color: var(--secondary-blue); font-weight: 500;">
            {{ $item['lessons_count'] }} lessons • {{ $item['credit_hours'] ?? 3 }} credit hours
        </p>
        @endif
        <div class="lesson-actions">
            <a href="{{ route('dashboard.lesson.view', ['lessonId' => $encodedId, 'course_id' => $encodedId]) }}" class="lesson-action-btn primary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Start Course
            </a>
            <a href="{{ $encodedQuizId ? route('quiz.instructions', ['quizId' => $encodedQuizId]) : route('quiz.index') }}" class="lesson-action-btn secondary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Quiz
            </a>
        </div>
    @else
        <div class="lesson-actions">
            <a href="{{ route('dashboard.lesson.view', ['lessonId' => $encodedId]) }}" class="lesson-action-btn primary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Watch
            </a>
            <a href="{{ $encodedQuizId ? route('quiz.instructions', ['quizId' => $encodedQuizId]) : route('quiz.index') }}" class="lesson-action-btn secondary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Quiz
            </a>
        </div>
    @endif
</x-video-facade>
