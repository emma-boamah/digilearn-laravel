@php
    $isUniversity = isset($universityCourses);
    $item = $isUniversity ? $course : $lesson;
    $id = $item['id'];
    $encodedCourseId = \App\Services\UrlObfuscator::encode($id);
    
    // For university, the lesson ID should be the first_lesson_id
    $lessonId = $isUniversity ? ($item['first_lesson_id'] ?? $id) : $id;
    $encodedLessonId = \App\Services\UrlObfuscator::encode($lessonId);
    
    $quizId = $item['quiz_id'] ?? null;
    $encodedQuizId = $item['encoded_quiz_id'] ?? ($quizId ? \App\Services\UrlObfuscator::encode($quizId) : null);
@endphp

@pushonce('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .dashboard-course-description {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin: 0.5rem 0;
    }
    .dashboard-course-lessons-count {
        font-size: 0.75rem;
        color: var(--secondary-blue);
        font-weight: 500;
    }
</style>
@endpushonce

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
    lessonId="{{ $encodedLessonId }}"
    courseId="{{ ($isUniversity && !($item['is_standalone_video'] ?? false)) ? $encodedCourseId : null }}"
    data-video-url="{{ $item['video_url'] ?? '' }}"
    data-selected-level="{{ $selectedLevelGroup ?? 'primary-lower' }}"
    :showLevelBadge="true"
    :showDuration="true"
    :showPlayOverlay="true"
    :lazyLoad="true"
    :isRestricted="($item['access_info']['level'] ?? 'full') === 'preview'"
    :upgradePrompt="$item['access_info']['upgrade_prompt'] ?? null"
    :categories="$item['categories'] ?? []"
>
    @if($isUniversity)
        @if(isset($item['description']))
        <p class="course-description dashboard-course-description">
            {{ $item['description'] }}
        </p>
        @endif
        @if(isset($item['lessons_count']))
        <p class="course-lessons-count dashboard-course-lessons-count">
            {{ $item['lessons_count'] }} lessons • {{ $item['credit_hours'] ?? 3 }} credit hours
        </p>
        @endif
        <div class="lesson-actions">
            <div class="action-icons-group">
                <button class="action-icon-btn save-btn" title="Save for later" data-course-id="{{ $encodedCourseId }}">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </button>
                @if($encodedQuizId)
                <a href="{{ route('quiz.instructions', ['quizId' => $encodedQuizId]) }}" class="quiz-primary-btn" title="Take Quiz">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Take Quiz</span>
                </a>
                @endif
            </div>
        </div>
    @else
        <div class="lesson-actions">
            {{-- Primary action is now clicking the card itself --}}
            <div class="action-icons-group">
                <button class="action-icon-btn save-btn" title="Save for later" data-lesson-id="{{ $encodedLessonId }}">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </button>
                @if($encodedQuizId)
                <a href="{{ route('quiz.instructions', ['quizId' => $encodedQuizId]) }}" class="quiz-primary-btn" title="Take Quiz">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Take Quiz</span>
                </a>
                @endif
            </div>
        </div>
    @endif
</x-video-facade>
