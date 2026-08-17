@php
    $accessLevel = is_object($lesson) ? ($lesson->access_info['level'] ?? 'full') : ($lesson['access_info']['level'] ?? 'full');
    $isPreview = $accessLevel === 'preview';

    $lessonId = is_object($lesson) ? $lesson->id : ($lesson['id'] ?? null);
    $encodedId = $lessonId ? \App\Services\UrlObfuscator::encode($lessonId) : '';
    $title = is_object($lesson) ? ($lesson->title ?? 'Lesson') : ($lesson['title'] ?? 'Lesson');
    $subject = is_object($lesson) ? ($lesson->subject->name ?? ($lesson->subject ?? 'General')) : ($lesson['subject'] ?? 'General');
    $instructor = is_object($lesson) ? ($lesson->instructor ?? ($lesson->uploader->name ?? 'Unknown')) : ($lesson['instructor'] ?? 'Unknown');
    $year = is_object($lesson) ? (isset($lesson->created_at) ? $lesson->created_at->format('Y') : ($lesson->year ?? '')) : ($lesson['year'] ?? '');
    $duration = is_object($lesson) ? ($lesson->duration ?? '') : ($lesson['duration'] ?? '');
    $videoUrl = is_object($lesson) ? ($lesson->video_url ?? '') : ($lesson['video_url'] ?? '');

    $videoSource = is_object($lesson) ? ($lesson->video_source ?? 'local') : ($lesson['video_source'] ?? 'local');
    $externalVideoId = is_object($lesson) ? ($lesson->external_video_id ?? '') : ($lesson['external_video_id'] ?? '');
    $vimeoId = is_object($lesson) ? ($lesson->vimeo_id ?? '') : ($lesson['vimeo_id'] ?? '');
    $muxPlaybackId = is_object($lesson) ? ($lesson->mux_playback_id ?? '') : ($lesson['mux_playback_id'] ?? '');
    $rawThumbnail = is_object($lesson) ? ($lesson->thumbnail ?? ($lesson->thumbnail_path ?? '')) : ($lesson['thumbnail'] ?? ($lesson['thumbnail_path'] ?? ''));

    // Dynamic resolution based on video source
    $thumbnailUrl = null;
    $fallbackUrl = '/placeholder.svg?height=104&width=180';

    if ($videoSource === 'youtube' && !empty($externalVideoId)) {
        $thumbnailUrl = "https://img.youtube.com/vi/{$externalVideoId}/maxresdefault.jpg";
        $fallbackUrl = "https://img.youtube.com/vi/{$externalVideoId}/hqdefault.jpg";
    } elseif ($videoSource === 'vimeo' && !empty($vimeoId)) {
        $thumbnailUrl = "https://vumbnail.com/{$vimeoId}.jpg";
    } elseif ($videoSource === 'mux' && !empty($muxPlaybackId)) {
        $thumbnailUrl = "https://image.mux.com/{$muxPlaybackId}/thumbnail.jpg";
    } elseif (!empty($rawThumbnail)) {
        if (\Illuminate\Support\Str::startsWith($rawThumbnail, ['http://', 'https://', '//'])) {
            $thumbnailUrl = $rawThumbnail;
        } else {
            $thumbnailUrl = secure_asset(ltrim($rawThumbnail, '/'));
        }
    }

    if (empty($thumbnailUrl)) {
        $thumbnailUrl = $fallbackUrl;
    }
@endphp

@pushonce('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .related-video-item {
        display: flex;
        gap: 1rem;
        padding: 0.875rem;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        align-items: flex-start;
    }
    .related-video-item:hover {
        background-color: var(--gray-50);
    }
    .related-video-item .video-thumbnail {
        width: 180px;
        height: 104px;
        min-width: 180px;
        aspect-ratio: 16 / 9;
        border-radius: 0.625rem;
        position: relative;
        overflow: hidden;
        background-color: var(--gray-200);
        flex-shrink: 0;
        cursor: pointer;
    }
    .related-video-item .video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
    .related-video-item .video-preview {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none !important;
        background-color: transparent;
        overflow: hidden;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .related-video-item.playing .video-preview,
    .related-video-item .video-preview:not(:empty) {
        opacity: 1;
        background-color: #000;
    }
    .related-video-item .video-preview iframe,
    .related-video-item .video-preview video {
        pointer-events: none !important;
    }
    .related-video-details {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        min-width: 0;
        min-height: 104px;
    }
    .related-lesson-actions {
        margin-top: auto;
        padding-top: 0.5rem;
        display: flex;
        justify-content: flex-start;
    }
    .related-save-btn {
        margin-left: 0 !important;
    }
    .lesson-duration {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        background-color: rgba(0, 0, 0, 0.8);
        color: #ffffff;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        z-index: 10;
        pointer-events: none;
    }
    .category-badges-container,
    .category-badge,
    .premium-badge,
    .premium-lock-overlay,
    .play-overlay {
        pointer-events: none !important;
    }
</style>
@endpushonce

<div class="video-item related-video-item hover-video-card {{ $isPreview ? 'restricted-lesson' : '' }}" 
     data-href="/dashboard/lesson/{{ $encodedId }}" 
     data-lesson-id="{{ $encodedId }}" 
     data-video-id="{{ $lessonId }}" 
     data-subject="{{ $subject }}" 
     data-title="{{ $title }}" 
     data-video-source="{{ $videoSource }}" 
     data-vimeo-id="{{ $vimeoId }}" 
     data-external-video-id="{{ $externalVideoId }}" 
     data-mux-playback-id="{{ $muxPlaybackId }}" 
     data-thumbnail="{{ $thumbnailUrl }}"
     data-loaded="false"
     data-access-level="{{ $accessLevel }}"
     @if($isPreview) data-upgrade-prompt="{{ json_encode(is_object($lesson) ? ($lesson->access_info['upgrade_prompt'] ?? null) : ($lesson['access_info']['upgrade_prompt'] ?? null)) }}" @endif>
     
    <div class="video-thumbnail" style="cursor: pointer;">
        <img src="{{ $thumbnailUrl }}" alt="{{ $title }}"
             data-fallback="{{ $fallbackUrl }}"
             loading="lazy">
        <div class="video-preview"></div>

        <!-- Category Badges -->
        @php
            $categories = is_object($lesson) ? ($lesson->categories ?? []) : ($lesson['categories'] ?? []);
        @endphp
        @if(!empty($categories))
            <div class="category-badges-container">
                @foreach($categories as $category)
                    @php
                        $catName = is_object($category) ? $category->name : ($category['name'] ?? '');
                        $slug = strtolower(is_object($category) ? $category->slug : ($category['slug'] ?? ''));
                        $isBece = str_contains($slug, 'bece');
                        $isWassce = str_contains($slug, 'wassce');
                        $levelGroup = $selectedLevelGroup ?? session('selected_level_group', Auth::user()->current_level_group ?? 'primary-lower');
                    @endphp
                    @if(($isBece && (str_contains(strtolower($levelGroup), 'jhs') || str_contains(strtolower($levelGroup), 'shs'))) || ($isWassce && str_contains(strtolower($levelGroup), 'shs')))
                        <div class="category-badge {{ $isBece ? 'bece-badge' : 'wassce-badge' }}">
                            {{ strtoupper($catName) }}
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
        
        @if(!empty($duration) && $videoSource !== 'none')
            <div class="lesson-duration">{{ $duration }}</div>
        @endif
        
        @if($isPreview)
            <div class="premium-badge">Upgrade</div>
            <div class="premium-lock-overlay">
                <div class="lock-icon-circle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
            </div>
        @else
            <div class="play-overlay">
                <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
            </div>
        @endif
    </div>
    
    <div class="video-details related-video-details">
        <h4 class="video-title">{{ $title }}</h4>
        <p class="video-meta">{{ $instructor }} • {{ $year }}</p>
        
        <div class="lesson-actions related-lesson-actions">
            <button class="action-icon-btn save-btn related-save-btn" title="Save for later" 
                    data-lesson-id="{{ $encodedId }}"
                    data-title="{{ $title }}"
                    data-subject="{{ $subject }}"
                    data-instructor="{{ $instructor }}"
                    data-year="{{ $year }}"
                    data-thumbnail="{{ $thumbnailUrl }}"
                    data-duration="{{ $duration ?? '0:00' }}"
                    data-video-url="{{ $videoUrl }}"
                    data-selected-level="{{ $selectedLevelGroup ?? 'primary-lower' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('click', function(e) {
            const upgradeBtn = e.target.closest('.upgrade-modal-trigger');
            if (upgradeBtn) {
                e.preventDefault();
                const planSlug = upgradeBtn.dataset.planSlug || 'essential';
                if (window.openUpgradeModal) {
                    window.openUpgradeModal(planSlug);
                } else {
                    window.location.href = '/pricing';
                }
            }
        });

        // Global image error handler for data-fallback
        if (!window.hasImageFallbackHandler) {
            document.addEventListener('error', function (e) {
                if (e.target && e.target.tagName && e.target.tagName.toLowerCase() === 'img') {
                    const fallback = e.target.getAttribute('data-fallback');
                    if (fallback && e.target.src !== fallback) {
                        e.target.src = fallback;
                        e.target.removeAttribute('data-fallback');
                    }
                }
            }, true);
            window.hasImageFallbackHandler = true;
        }
    </script>
    @endpush
@endonce