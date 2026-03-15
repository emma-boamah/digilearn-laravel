@php
    $accessLevel = $lesson['access_info']['level'] ?? 'full';
    $isPreview = $accessLevel === 'preview';
@endphp

<div class="related-lesson-card {{ $isPreview ? 'preview-card' : '' }}"
     data-lesson-id="{{ $lesson['id'] }}"
     @if($isPreview)
        data-upgrade-prompt="{{ json_encode($lesson['access_info']['upgrade_prompt']) }}"
     @endif>
     
    <div class="lesson-thumbnail">
        <img src="{{ $lesson['thumbnail'] }}" alt="{{ $lesson['title'] }}"
             data-fallback="/placeholder.svg?height=78&width=140"
             loading="lazy">
        
        @if($isPreview)
            <div class="premium-overlay">
                <i class="fas fa-lock"></i>
                <span>Premium</span>
                <div class="upgrade-hint" x-show="false" x-transition>
                    <i class="fas fa-crown"></i>
                    <span>Upgrade to access</span>
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
    
    <div class="lesson-details">
        <h4 class="lesson-title" title="{{ $lesson['title'] }}">
            {{ \Illuminate\Support\Str::limit($lesson['title'], 60) }}
        </h4>
        <p class="lesson-meta">
            {{ $lesson['instructor'] ?? 'Unknown' }} • {{ $lesson['duration'] ?? 'Unknown' }}
        </p>
        <div class="lesson-tags">
            <span class="subject-tag" title="Subject">{{ $lesson['subject'] ?? 'General' }}</span>
            <span class="difficulty-tag" title="Difficulty">{{ $lesson['level_display'] ?? $lesson['level'] }}</span>
            @if($lesson['views'] > 0)
                <span class="views-tag" title="Views">{{ number_format($lesson['views']) }} views</span>
            @endif
        </div>
        
        @if(isset($lesson['related_score']) && request()->get('debug') == 'true')
            <div class="related-score-debug">
                <small>Score: {{ number_format($lesson['related_score'], 2) }}</small>
            </div>
        @endif
    </div>
    
    @if($isPreview)
        <div class="upgrade-prompt">
            <button class="btn-upgrade upgrade-modal-trigger" 
                    data-plan-slug="{{ $lesson['access_info']['required_plan_slug'] ?? 'essential' }}">
                <i class="fas fa-crown"></i>
                {{ $lesson['access_info']['upgrade_prompt']['cta_text'] }}
            </button>
            <small class="upgrade-note">
                Required: {{ $lesson['access_info']['required_plan'] }}
            </small>
        </div>
    @else
        <div class="lesson-actions">
            <a href="/dashboard/lesson/{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}" 
               class="btn-primary lesson-link"
               title="Watch {{ \Illuminate\Support\Str::limit($lesson['title'], 30) }}">
                <i class="fas fa-play"></i>
                Watch Lesson
            </a>
        </div>
    @endif
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