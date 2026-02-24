/**
 * Video Facade Component - Optimized Video Loading for Multiple Sources
 * Supports Vimeo, YouTube, Mux, and Local videos with lazy loading and preconnect hints
 */

class VideoFacadeManager {
    constructor(options = {}) {
        this.activePlayers = new Map();
        this.preconnectedDomains = new Set();
        this.intersectionObserver = null;
        this.hoverTimer = null;
        this.isMobile = this.detectMobile();
        this.autoPlayEnabled = options.autoPlay || false;
        this.autoPlayIntersectionObserver = null;
        this.init();
    }

    init() {
        this.setupIntersectionObserver();
        if (this.autoPlayEnabled) {
            this.setupAutoPlayIntersectionObserver();
        }
        this.bindEvents();
        this.preconnectExternalDomains();
    }

    detectMobile() {
        return 'ontouchstart' in window || window.innerWidth <= 768;
    }

    setupIntersectionObserver() {
        // Optimized lazy loading with Intersection Observer for data efficiency
        this.intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const card = entry.target;
                    // Load thumbnail when video is about to come into view
                    this.loadVideoThumbnail(card);
                    // Stop observing once loaded to prevent unnecessary checks
                    this.intersectionObserver.unobserve(card);
                }
            });
        }, {
            // Load videos 100px before they enter viewport for smoother experience
            // This prevents visible loading delays when scrolling
            rootMargin: '100px',
            // Only trigger when at least 10% of the element is visible
            threshold: 0.1
        });
    }

    setupAutoPlayIntersectionObserver() {
        // Auto-play videos when they come into view (YouTube-like behavior)
        this.autoPlayIntersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const card = entry.target;
                if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                    // Video is at least 50% visible, start auto-play
                    if (!card.dataset.loaded || card.dataset.loaded === 'false') {
                        this.activateAutoPlay(card);
                    }
                } else if (!entry.isIntersecting) {
                    // Video is out of view, pause it
                    this.pauseAutoPlay(card);
                }
            });
        }, {
            // Trigger when 50% of the video is visible
            threshold: 0.5,
            // Add some margin for smoother transitions
            rootMargin: '50px'
        });
    }

    bindEvents() {
        // Initialize immediately if DOM is already loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeCards();
            });
        } else {
            // DOM already loaded
            this.initializeCards();
        }

        // Handle dynamic content (like search results)
        document.addEventListener('videoCardsUpdated', () => {
            this.initializeCards();
        });

        // Auto-play main lesson video immediately if enabled
        if (this.autoPlayEnabled) {
            this.autoPlayMainLessonVideo();
        }
    }

    autoPlayMainLessonVideo() {
        // Skip auto-playing main lesson video - it's handled by manual JavaScript code
        // to prevent duplicate video player initialization
        console.log('Skipping auto-play for main lesson video - handled by manual code');
    }

    initializeCards() {
        // Look for both old and new class names for compatibility
        const cards = document.querySelectorAll('.video-facade-card, .hover-video-card');

        console.log('Found video cards:', cards.length);

        cards.forEach(card => {
            // Skip if already initialized
            if (card.dataset.facadeInitialized) return;

            card.dataset.facadeInitialized = 'true';

            console.log('Initializing card:', card.dataset.title);

            // Lazy loading setup (main lesson video should not be lazy loaded)
            if (card.dataset.lazy === 'true' && !card.classList.contains('lesson-main-video')) {
                this.intersectionObserver.observe(card);
            } else {
                this.loadVideoThumbnail(card);
            }

            // Auto-play setup if enabled (skip main lesson video as it auto-plays immediately)
            if (this.autoPlayEnabled && !card.classList.contains('lesson-main-video')) {
                this.autoPlayIntersectionObserver.observe(card);
            }

            // Event binding
            this.bindCardEvents(card);
        });
    }

    bindCardEvents(card) {
        const thumbnail = card.querySelector('.video-facade-thumbnail');

        if (this.isMobile) {
            // Mobile: Click to play
            card.addEventListener('click', (e) => {
                const actionBtn = e.target.closest('.lesson-action-btn');
                if (actionBtn) return; // Allow action buttons to handle their own navigation

                if (card.dataset.accessLevel === 'preview') {
                    e.preventDefault();
                    this.handleRestrictedAccess(card);
                    return;
                }

                e.preventDefault();
                this.handlePlayClick(card);
            });
        } else {
            // Desktop: Hover to preview
            card.addEventListener('mouseenter', () => {
                if (card.dataset.accessLevel === 'preview') return;

                this.hoverTimer = setTimeout(() => {
                    this.activatePreview(card);
                }, 250); // Debounce hover
            });

            card.addEventListener('mouseleave', () => {
                clearTimeout(this.hoverTimer);
                this.deactivatePreview(card);
            });
        }

        // Handle clicks on action buttons
        card.addEventListener('click', (e) => {
            const actionBtn = e.target.closest('.lesson-action-btn');
            if (actionBtn) {
                e.stopPropagation();
                // Let the link handle navigation
            }
        });
    }

    loadVideoThumbnail(card) {
        const thumbnail = card.querySelector('.video-facade-thumbnail');
        if (!thumbnail) return;

        // Mark as loaded to stop shimmer animation
        card.classList.add('loaded');

        // Preload thumbnail if not already loaded
        if (thumbnail.complete) {
            thumbnail.classList.add('loaded');
        } else {
            thumbnail.addEventListener('load', () => {
                thumbnail.classList.add('loaded');
            });
        }
    }

    handlePlayClick(card) {
        // On mobile, clicking should navigate to the lesson
        const lessonLink = card.querySelector('.lesson-action-btn.primary');
        if (lessonLink) {
            window.location.href = lessonLink.href;
        }
    }

    activatePreview(card) {
        if (card.dataset.loaded === 'true') return;

        // Kill any existing preview
        this.deactivateAllPreviews();

        const videoSource = card.dataset.videoSource;
        const preview = card.querySelector('.video-preview');

        if (!preview) return;

        // Show loading state
        card.classList.add('loading');

        try {
            switch (videoSource) {
                case 'vimeo':
                    this.loadVimeoPreview(card, preview);
                    break;
                case 'youtube':
                    this.loadYouTubePreview(card, preview);
                    break;
                case 'mux':
                    this.loadMuxPreview(card, preview);
                    break;
                case 'local':
                default:
                    this.loadLocalPreview(card, preview);
                    break;
            }
        } catch (error) {
            console.error('Error loading video preview:', error);
            card.classList.remove('loading');
            card.classList.add('error');
        }
    }

    deactivatePreview(card) {
        card.dataset.loaded = 'false';
        card.classList.remove('loading', 'playing');

        const preview = card.querySelector('.video-preview');
        if (preview) {
            preview.innerHTML = '';
        }

        // Stop any active player
        const videoId = card.dataset.videoId;
        if (this.activePlayers.has(videoId)) {
            const player = this.activePlayers.get(videoId);
            if (player.pause) player.pause();
            this.activePlayers.delete(videoId);
        }
    }

    deactivateAllPreviews() {
        document.querySelectorAll('.video-facade-card.playing').forEach(card => {
            this.deactivatePreview(card);
        });
    }

    activateAutoPlay(card) {
        if (card.dataset.loaded === 'true') return;

        // Kill any existing auto-play
        this.deactivateAllAutoPlay();

        const videoSource = card.dataset.videoSource;
        const preview = card.querySelector('.video-preview');

        if (!preview) return;

        // Show loading state
        card.classList.add('loading');

        try {
            switch (videoSource) {
                case 'vimeo':
                    this.loadVimeoAutoPlay(card, preview);
                    break;
                case 'youtube':
                    this.loadYouTubeAutoPlay(card, preview);
                    break;
                case 'mux':
                    this.loadMuxAutoPlay(card, preview);
                    break;
                case 'local':
                default:
                    this.loadLocalAutoPlay(card, preview);
                    break;
            }
        } catch (error) {
            console.error('Error loading auto-play video:', error);
            card.classList.remove('loading');
            card.classList.add('error');
        }
    }

    pauseAutoPlay(card) {
        card.dataset.loaded = 'false';
        card.classList.remove('loading', 'playing');

        const preview = card.querySelector('.video-preview');
        if (preview) {
            preview.innerHTML = '';
        }

        // Stop any active player
        const videoId = card.dataset.videoId;
        if (this.activePlayers.has(videoId)) {
            const player = this.activePlayers.get(videoId);
            if (player.pause) player.pause();
            this.activePlayers.delete(videoId);
        }
    }

    deactivateAllAutoPlay() {
        document.querySelectorAll('.video-facade-card.playing').forEach(card => {
            this.pauseAutoPlay(card);
        });
    }

    loadVimeoPreview(card, preview) {
        const vimeoId = card.dataset.vimeoId;
        if (!vimeoId) return;

        const iframe = document.createElement('iframe');
        // Optimized Vimeo embed parameters for data efficiency:
        // - autoplay=1: Auto-play when loaded
        // - muted=1: Start muted to allow autoplay
        // - background=1: Removes UI controls for background playback
        // - dnt=1: Do Not Track - prevents session data tracking
        // - preload=none: Don't preload video data
        iframe.src = `https://player.vimeo.com/video/${vimeoId}?autoplay=1&muted=1&background=1&dnt=1&preload=none`;
        iframe.allow = 'autoplay';
        iframe.loading = 'lazy';
        iframe.frameBorder = '0';
        iframe.className = 'csp-facade-player';

        preview.appendChild(iframe);

        // Use Vimeo API if available
        if (typeof Vimeo !== 'undefined') {
            const player = new Vimeo.Player(iframe);
            this.activePlayers.set(card.dataset.videoId, player);

            player.on('loaded', () => {
                card.classList.remove('loading');
                card.classList.add('playing');
                card.dataset.loaded = 'true';
            });

            player.on('error', () => {
                card.classList.remove('loading');
                card.classList.add('error');
            });
        } else {
            // Fallback without API
            iframe.onload = () => {
                card.classList.remove('loading');
                card.classList.add('playing');
                card.dataset.loaded = 'true';
            };
            iframe.onerror = () => {
                card.classList.remove('loading');
                card.classList.add('error');
            };
        }
    }

    loadYouTubePreview(card, preview) {
        const externalVideoId = card.dataset.externalVideoId;
        if (!externalVideoId) return;

        // Optimized YouTube embed parameters for data efficiency:
        // - autoplay=1: Auto-play when loaded (but only on user interaction)
        // - mute=1: Start muted to allow autoplay
        // - controls=0: Hide controls for cleaner look
        // - modestbranding=1: Minimal YouTube branding
        // - rel=0: Don't show related videos
        // - showinfo=0: Hide video info
        // - loop=1: Loop the video
        // - playlist: Required for loop to work
        // - enablejsapi=1: Enable JavaScript API
        // - iv_load_policy=3: Hide annotations
        // - fs=0: Disable fullscreen button
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${externalVideoId}?autoplay=1&mute=1&controls=0&modestbranding=1&rel=0&showinfo=0&loop=1&playlist=${externalVideoId}&enablejsapi=1&iv_load_policy=3&fs=0&origin=${window.location.origin}`;
        iframe.allow = 'autoplay; encrypted-media';
        iframe.loading = 'lazy';
        iframe.frameBorder = '0';
        iframe.className = 'csp-facade-player';

        preview.appendChild(iframe);

        iframe.onload = () => {
            card.classList.remove('loading');
            card.classList.add('playing');
            card.dataset.loaded = 'true';
        };

        iframe.onerror = () => {
            card.classList.remove('loading');
            card.classList.add('error');
        };

        // Store iframe reference for cleanup
        this.activePlayers.set(card.dataset.videoId, { element: iframe });
    }

    loadMuxPreview(card, preview) {
        const muxPlaybackId = card.dataset.muxPlaybackId;
        if (!muxPlaybackId) return;

        // Optimized Mux video loading for data efficiency:
        // - autoplay: Only when user interacts
        // - muted: Start muted to allow autoplay
        // - loop: Loop for preview effect
        // - playsInline: Prevent fullscreen on mobile
        // - preload: none to prevent automatic loading
        const video = document.createElement('video');
        video.src = `https://stream.mux.com/${muxPlaybackId}.m3u8`;
        video.autoplay = true;
        video.muted = true;
        video.loop = true;
        video.playsInline = true;
        video.preload = 'none'; // Don't preload video data until needed
        video.className = 'csp-facade-video-cover';

        preview.appendChild(video);

        video.onloadeddata = () => {
            card.classList.remove('loading');
            card.classList.add('playing');
            card.dataset.loaded = 'true';
        };

        video.onerror = () => {
            card.classList.remove('loading');
            card.classList.add('error');
        };

        this.activePlayers.set(card.dataset.videoId, video);
    }

    loadLocalPreview(card, preview) {
        // For local videos, we might not want to autoplay due to bandwidth
        // Instead, show a static preview or navigate to player
        card.classList.remove('loading');
        // Could add a subtle animation or just leave as static
    }

    loadVimeoAutoPlay(card, preview) {
        const vimeoId = card.dataset.vimeoId;
        if (!vimeoId) return;

        const iframe = document.createElement('iframe');
        iframe.src = `https://player.vimeo.com/video/${vimeoId}?autoplay=1&muted=1&background=1&dnt=1&loop=1`;
        iframe.allow = 'autoplay';
        iframe.loading = 'lazy';
        iframe.frameBorder = '0';
        iframe.className = 'csp-facade-player';

        preview.appendChild(iframe);

        if (typeof Vimeo !== 'undefined') {
            const player = new Vimeo.Player(iframe);
            this.activePlayers.set(card.dataset.videoId, player);

            player.on('loaded', () => {
                card.classList.remove('loading');
                card.classList.add('playing');
                card.dataset.loaded = 'true';
            });

            player.on('error', () => {
                card.classList.remove('loading');
                card.classList.add('error');
            });
        } else {
            iframe.onload = () => {
                card.classList.remove('loading');
                card.classList.add('playing');
                card.dataset.loaded = 'true';
            };
            iframe.onerror = () => {
                card.classList.remove('loading');
                card.classList.add('error');
            };
        }
    }

    loadYouTubeAutoPlay(card, preview) {
        const externalVideoId = card.dataset.externalVideoId;
        if (!externalVideoId) return;

        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${externalVideoId}?autoplay=1&mute=1&controls=0&modestbranding=1&rel=0&showinfo=0&loop=1&playlist=${externalVideoId}&enablejsapi=1&iv_load_policy=3&fs=0&origin=${window.location.origin}`;
        iframe.allow = 'autoplay; encrypted-media';
        iframe.loading = 'lazy';
        iframe.frameBorder = '0';
        iframe.className = 'csp-facade-player';

        preview.appendChild(iframe);

        iframe.onload = () => {
            card.classList.remove('loading');
            card.classList.add('playing');
            card.dataset.loaded = 'true';
        };

        iframe.onerror = () => {
            card.classList.remove('loading');
            card.classList.add('error');
        };

        this.activePlayers.set(card.dataset.videoId, { element: iframe });
    }

    loadMuxAutoPlay(card, preview) {
        const muxPlaybackId = card.dataset.muxPlaybackId;
        if (!muxPlaybackId) return;

        const video = document.createElement('video');
        video.src = `https://stream.mux.com/${muxPlaybackId}.m3u8`;
        video.autoplay = true;
        video.muted = true;
        video.loop = true;
        video.playsInline = true;
        video.preload = 'none';
        video.className = 'csp-facade-video-cover';

        preview.appendChild(video);

        video.onloadeddata = () => {
            card.classList.remove('loading');
            card.classList.add('playing');
            card.dataset.loaded = 'true';
        };

        video.onerror = () => {
            card.classList.remove('loading');
            card.classList.add('error');
        };

        this.activePlayers.set(card.dataset.videoId, video);
    }

    loadLocalAutoPlay(card, preview) {
        // For local videos, auto-play might not be desired due to bandwidth
        // Just show the thumbnail without auto-playing
        card.classList.remove('loading');
    }

    preconnectExternalDomains() {
        // Comprehensive preconnect hints for video optimization
        const preconnectDomains = [
            'https://player.vimeo.com',
            'https://www.youtube.com',
            'https://www.youtube-nocookie.com',
            'https://stream.mux.com',
            'https://image.mux.com',
            'https://vumbnail.com',
            'https://img.youtube.com'
        ];

        // DNS prefetch for additional performance
        const dnsPrefetchDomains = [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
            'https://cdnjs.cloudflare.com'
        ];

        // Add preconnect hints
        preconnectDomains.forEach(domain => {
            if (!this.preconnectedDomains.has(domain)) {
                const link = document.createElement('link');
                link.rel = 'preconnect';
                link.href = domain;
                link.crossOrigin = 'anonymous';
                document.head.appendChild(link);
                this.preconnectedDomains.add(domain);
            }
        });

        // Add DNS prefetch hints
        dnsPrefetchDomains.forEach(domain => {
            if (!this.preconnectedDomains.has(domain)) {
                const link = document.createElement('link');
                link.rel = 'dns-prefetch';
                link.href = domain;
                document.head.appendChild(link);
                this.preconnectedDomains.add(domain);
            }
        });
    }

    // Utility method to get video thumbnail URL
    getThumbnailUrl(card) {
        const videoSource = card.dataset.videoSource;
        const videoId = card.dataset.vimeoId || card.dataset.externalVideoId || card.dataset.muxPlaybackId;

        switch (videoSource) {
            case 'vimeo':
                return videoId ? `https://vumbnail.com/${videoId}.jpg` : null;
            case 'youtube':
                return videoId ? `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg` : null;
            case 'mux':
                return videoId ? `https://image.mux.com/${videoId}/thumbnail.jpg` : null;
            default:
                return card.dataset.thumbnail || null;
        }
    }

    // Cleanup method
    destroy() {
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect();
        }

        if (this.autoPlayIntersectionObserver) {
            this.autoPlayIntersectionObserver.disconnect();
        }

        this.deactivateAllPreviews();
        this.deactivateAllAutoPlay();

        if (this.hoverTimer) {
            clearTimeout(this.hoverTimer);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.videoFacadeManager = new VideoFacadeManager();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VideoFacadeManager;
}