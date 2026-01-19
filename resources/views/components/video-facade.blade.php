@php
    use Illuminate\Support\Str;
@endphp

@props([
    'videoId' => null,
    'videoSource' => 'local',
    'vimeoId' => null,
    'externalVideoId' => null,
    'muxPlaybackId' => null,
    'thumbnail' => null,
    'title' => '',
    'duration' => '',
    'levelDisplay' => '',
    'subject' => '',
    'instructor' => '',
    'year' => '',
    'lessonId' => null,
    'courseId' => null,
    'class' => 'lesson-card',
    'showLevelBadge' => true,
    'showDuration' => true,
    'showPlayOverlay' => true,
    'lazyLoad' => true
])

<div class="{{ $class }} video-facade-card hover-video-card"
     data-video-id="{{ $videoId }}"
     data-lesson-id="{{ $lessonId }}"
     data-course-id="{{ $courseId }}"
     data-video-source="{{ $videoSource }}"
     data-vimeo-id="{{ $vimeoId }}"
     data-external-video-id="{{ $externalVideoId }}"
     data-mux-playback-id="{{ $muxPlaybackId }}"
     data-title="{{ $title }}"
     data-thumbnail="{{ $thumbnail }}"
     data-subject="{{ $attributes->get('data-subject', '') }}"
     data-loaded="false"
     @if($lazyLoad) data-lazy="true" @endif>

    <div class="lesson-thumbnail">
        <!-- Static Thumbnail Image -->
        <img
            src="{{ $thumbnail ?: asset('images/placeholder.png') }}"
            alt="{{ $title }}"
            class="video-facade-thumbnail"
            loading="{{ $lazyLoad ? 'lazy' : 'eager' }}"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />

        <!-- Fallback when image fails to load -->
        <div class="thumbnail-fallback" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #E11E2D, #2677B8); align-items: center; justify-content: center; color: white; font-weight: 600;">
            <div style="text-align: center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="margin-bottom: 8px; opacity: 0.8;">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <div style="font-size: 14px;">{{ Str::limit($title, 20) }}</div>
            </div>
        </div>

        <!-- Video Preview Container (populated by JavaScript) -->
        <div class="video-preview"></div>

        <!-- Duration Badge -->
        @if($duration)
        <div class="lesson-duration">{{ $duration }}</div>
        @endif

        <!-- Level Badge -->
        @if($showLevelBadge && $levelDisplay)
        <div class="lesson-level-badge">{{ $levelDisplay }}</div>
        @endif

        <!-- Play Overlay -->
        @if($showPlayOverlay)
        <div class="play-overlay">
            <div class="play-button">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>
        </div>
        @endif
    </div>

    <div class="lesson-info">
        <h3 class="lesson-title">{{ $title }}</h3>
        <div class="lesson-meta">
            @if($subject)
            <span class="lesson-subject">({{ $subject }})</span>
            @endif
            @if($instructor && $year)
            <span>{{ $instructor }} | {{ $year }}</span>
            @endif
        </div>

        <!-- Course-specific content -->
        @if(isset($course) && $course)
        @if(isset($course['description']))
        <p class="course-description" style="font-size: 0.875rem; color: var(--gray-600); margin: 0.5rem 0;">
            {{ $course['description'] }}
        </p>
        @endif
        @if(isset($course['lessons_count']))
        <p class="course-lessons-count" style="font-size: 0.75rem; color: var(--secondary-blue); font-weight: 500;">
            {{ $course['lessons_count'] }} lessons • {{ $course['credit_hours'] ?? 3 }} credit hours
        </p>
        @endif
        @endif

        <!-- Action buttons -->
        @if(isset($slot))
        {{ $slot }}
        @else
        <div class="lesson-actions">
            @if(isset($course) && $course)
            <a href="{{ route('dashboard.lesson.view', ['lessonId' => \App\Services\UrlObfuscator::encode($videoId), 'course_id' => \App\Services\UrlObfuscator::encode($courseId ?? $videoId)]) }}" class="lesson-action-btn primary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Start Course
            </a>
            <a href="{{ $lesson['quiz_id'] ? route('quiz.instructions', ['quizId' => $lesson['encoded_quiz_id']]) : route('quiz.index') }}" class="lesson-action-btn secondary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Quiz
            </a>
            @else
            <a href="{{ route('dashboard.lesson.view', ['lessonId' => \App\Services\UrlObfuscator::encode($videoId)]) }}" class="lesson-action-btn primary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Watch
            </a>
            <a href="{{ route('quiz.index') }}" class="lesson-action-btn secondary">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Quiz
            </a>
            @endif
        </div>
        @endif
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
.video-facade-card {
    display: flex;
    flex-direction: column;
    background-color: var(--white);
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.lesson-thumbnail {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    flex: 1;
    min-height: 180px;
}

.lesson-duration {
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
    background-color: rgba(0, 0, 0, 0.8);
    color: var(--white);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    z-index: 10;
}

.lesson-level-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background-color: var(--secondary-blue);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    z-index: 10;
}

.play-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.2);
    opacity: 1;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: 5;
}

.video-facade-card.playing .play-overlay {
    opacity: 0;
}

.video-facade-card:not(.playing):hover .play-overlay {
    opacity: 1;
    background-color: rgba(0, 0, 0, 0.4);
}

.play-button {
    width: 60px;
    height: 60px;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-red);
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.video-facade-card:hover .play-button {
    background-color: var(--primary-red);
    color: var(--white);
    transform: scale(1.1);
}

.video-facade-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.video-facade-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
}

.video-facade-card.playing .video-facade-thumbnail {
    opacity: 0;
}

.video-facade-card.playing .play-overlay {
    opacity: 0;
}

/* Loading state */
.video-facade-card.loading .video-facade-thumbnail {
    filter: blur(2px);
}

.video-facade-card.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid var(--primary-red);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 2;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Error state */
.video-facade-card.error .video-facade-thumbnail {
    filter: grayscale(100%);
    opacity: 0.5;
}

.video-facade-card.error::before {
    content: '⚠️ Video unavailable';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    z-index: 2;
}

/* Lazy loading placeholder */
.video-facade-card[data-lazy="true"]:not(.loaded) .video-facade-thumbnail {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading-shimmer 1.5s infinite;
}

@keyframes loading-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.video-facade-card.loaded .video-facade-thumbnail {
    animation: none;
}

/* Preconnect hints for external video sources */
.video-facade-card[data-video-source="vimeo"] {
    /* Preconnect to Vimeo domains */
}

.video-facade-card[data-video-source="youtube"] {
    /* Preconnect to YouTube domains */
}

.video-facade-card[data-video-source="mux"] {
    /* Preconnect to Mux domains */
}

/* Optimized thumbnail loading */
.video-facade-thumbnail {
    transition: opacity 0.3s ease;
    will-change: opacity;
}

.video-facade-thumbnail.loaded {
    opacity: 1;
}

/* Reduced motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    .video-facade-card {
        transition: none;
    }

    .video-facade-card:hover {
        transform: none;
    }

    .video-facade-thumbnail {
        transition: none;
    }

    @keyframes loading-shimmer {
        0% { background-position: 0 0; }
        100% { background-position: 0 0; }
    }
}
</style>