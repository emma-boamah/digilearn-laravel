@php
    $accessLevel = $lesson['access_info']['level'] ?? 'full';
    $isPreview = $accessLevel === 'preview';
@endphp

@pushonce('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .related-video-details {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
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
    }
</style>
@endpushonce

<div class="video-item related-video-item hover-video-card {{ $isPreview ? 'restricted-lesson' : '' }}" 
     data-href="/dashboard/lesson/{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}" 
     data-lesson-id="{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}" 
     data-video-id="{{ $lesson['id'] }}" 
     data-subject="{{ $lesson['subject'] ?? 'General' }}" 
     data-title="{{ $lesson['title'] ?? 'Lesson' }}" 
     data-video-source="{{ $lesson['video_source'] ?? 'local' }}" 
     data-vimeo-id="{{ $lesson['vimeo_id'] ?? '' }}" 
     data-external-video-id="{{ $lesson['external_video_id'] ?? '' }}" 
     data-mux-playback-id="{{ $lesson['mux_playback_id'] ?? '' }}" 
     data-loaded="false"
     data-access-level="{{ $accessLevel }}"
     @if($isPreview) data-upgrade-prompt="{{ json_encode($lesson['access_info']['upgrade_prompt'] ?? null) }}" @endif>
     
    <div class="video-thumbnail">
        <img src="{{ secure_asset($lesson['thumbnail'] ?? '') }}" alt="{{ $lesson['title'] ?? 'Lesson' }}"
             data-fallback="/placeholder.svg?height=78&width=140"
             loading="lazy">
        <div class="video-preview"></div>
        
        @if(!empty($lesson['level_display']))
            <div class="lesson-level-badge">{{ $lesson['level_display'] }}</div>
        @endif

        <!-- Category Badges -->
        @if(!empty($lesson['categories']))
            <div class="category-badges-container">
                @foreach($lesson['categories'] as $category)
                    @php
                        $slug = strtolower($category['slug'] ?? '');
                        $isBece = str_contains($slug, 'bece');
                        $isWassce = str_contains($slug, 'wassce');
                    @endphp
                    @if($isBece || $isWassce)
                        <div class="category-badge {{ $isBece ? 'bece-badge' : 'wassce-badge' }}">
                            {{ strtoupper($category['name']) }}
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
        
        @if(!empty($lesson['duration']) && ($lesson['video_source'] ?? 'local') !== 'none')
            <div class="lesson-duration">{{ $lesson['duration'] }}</div>
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
        <h4 class="video-title">{{ $lesson['title'] ?? 'Lesson' }}</h4>
        <p class="video-meta">{{ $lesson['instructor'] ?? 'Unknown' }} • {{ $lesson['year'] ?? '' }}</p>
        
        <div class="lesson-actions related-lesson-actions">
            <button class="action-icon-btn save-btn related-save-btn" title="Save for later" 
                    data-lesson-id="{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}"
                    data-title="{{ $lesson['title'] ?? '' }}"
                    data-subject="{{ $lesson['subject'] ?? 'General' }}"
                    data-instructor="{{ $lesson['instructor'] ?? '' }}"
                    data-year="{{ $lesson['year'] ?? '' }}"
                    data-thumbnail="{{ $lesson['thumbnail'] ?? '' }}"
                    data-duration="{{ $lesson['duration'] ?? '0:00' }}"
                    data-video-url="{{ $lesson['video_url'] ?? '' }}"
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