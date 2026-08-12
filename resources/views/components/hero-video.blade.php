@props([
    'title' => null,
    'subtitle' => null,
])

@php
    $banners = \App\Models\HeroBanner::active()->ordered()->get();

    // Fallback if database table is empty
    if ($banners->isEmpty()) {
        $banners = collect([
            (object)[
                'id' => 1,
                'title' => $title ?? 'Explore & Learn',
                'subtitle' => $subtitle ?? 'at your own pace with interactive lessons.',
                'media_type' => 'video',
                'media_url' => asset('videos/hero-video.mp4'),
                'badge_text' => 'FEATURED',
                'cta_text' => 'Watch Now',
                'cta_url' => route('dashboard.digilearn'),
            ],
            (object)[
                'id' => 2,
                'title' => 'Afrimarker Showcase',
                'subtitle' => 'Enhancing learning through innovative digital tools.',
                'media_type' => 'image',
                'media_url' => asset('storage/hero_banners/afrimarker_add.jpeg'),
                'badge_text' => 'STAFF PICK',
                'cta_text' => 'Learn More',
                'cta_url' => route('dashboard.main'),
            ]
        ]);
    }
@endphp

<!-- Vimeo-Style Hero Carousel Outer Container -->
<div class="hero-container-outer">
    <div class="hero-carousel-card" id="heroCarouselCard">
        
        <!-- Slide Items -->
        <div class="hero-slides-wrapper">
            @foreach($banners as $index => $banner)
                <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-slide-index="{{ $index }}">
                    
                    <!-- Background Media (Image or Video) -->
                    <div class="hero-media-container">
                        @if($banner->media_type === 'video')
                            <video autoplay muted loop playsinline class="hero-media-element">
                                <source src="{{ $banner->media_url }}" type="video/mp4">
                            </video>
                        @else
                            <img src="{{ $banner->media_url }}" alt="{{ $banner->title }}" class="hero-media-element">
                        @endif
                        <div class="hero-media-overlay"></div>
                    </div>

                    <!-- Overlay Content (Vimeo-Style) -->
                    <div class="hero-slide-content">
                        @if($banner->badge_text)
                            <div class="hero-badge">
                                <i class="fas fa-bookmark" style="margin-right: 0.35rem; font-size: 0.75rem;"></i>
                                {{ strtoupper($banner->badge_text) }}
                            </div>
                        @endif

                        @if($banner->title)
                            <h1 class="hero-slide-title">{{ $banner->title }}</h1>
                        @endif

                        @if($banner->subtitle)
                            <p class="hero-slide-subtitle">{{ $banner->subtitle }}</p>
                        @endif

                        @if($banner->cta_text && $banner->cta_url)
                            <a href="{{ $banner->cta_url }}" class="hero-cta-btn">
                                <i class="fas fa-play" style="font-size: 0.85rem;"></i>
                                <span>{{ $banner->cta_text }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($banners->count() > 1)
            <!-- Side Navigation Arrows -->
            <button type="button" class="hero-nav-arrow prev" id="heroPrevBtn" aria-label="Previous Slide">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button type="button" class="hero-nav-arrow next" id="heroNextBtn" aria-label="Next Slide">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Pagination Dots -->
            <div class="hero-dots-container" id="heroDotsContainer">
                @foreach($banners as $index => $banner)
                    <button type="button" class="hero-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif

    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .hero-container-outer {
        width: 100%;
        padding: 0.85rem 1.5rem 0.35rem;
        box-sizing: border-box;
    }

    /* Vimeo-Style Curved Hero Carousel Card */
    .hero-carousel-card {
        position: relative;
        width: 100%;
        min-height: 260px;
        max-height: 340px;
        aspect-ratio: 21 / 7;
        border-radius: 1.5rem;
        overflow: hidden;
        background: #0f172a;
        box-shadow: 
            0 20px 45px -10px rgba(0, 0, 0, 0.35),
            0 8px 20px -6px rgba(0, 0, 0, 0.2);
    }

    .hero-slides-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
    }

    /* Individual Slide */
    .hero-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
    }

    .hero-slide.active {
        opacity: 1;
        pointer-events: auto;
        z-index: 1;
    }

    /* Media & Gradient Overlay */
    .hero-media-container {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .hero-media-element {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hero-media-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, 
            rgba(15, 23, 42, 0.92) 0%, 
            rgba(15, 23, 42, 0.75) 45%, 
            rgba(15, 23, 42, 0.25) 85%, 
            rgba(15, 23, 42, 0.1) 100%
        );
    }

    /* Overlay Content Typography */
    .hero-slide-content {
        position: relative;
        z-index: 2;
        padding: 2.25rem 3rem;
        max-width: 580px;
        color: #ffffff;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
    }

    .hero-slide-title {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.025em;
        color: #ffffff;
        margin: 0 0 0.6rem 0;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }

    .hero-slide-subtitle {
        font-size: clamp(0.9rem, 1.2vw, 1.05rem);
        color: rgba(255, 255, 255, 0.85);
        margin: 0 0 1.25rem 0;
        line-height: 1.45;
        font-weight: 400;
        max-width: 480px;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
    }

    .hero-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        color: #0f172a;
        font-weight: 800;
        font-size: 0.9rem;
        padding: 0.65rem 1.4rem;
        border-radius: 9999px;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .hero-cta-btn:hover {
        background: #f8fafc;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        color: #0284c7;
    }

    /* Vimeo-Style Side Navigation Arrows */
    .hero-nav-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.25s ease;
        opacity: 0;
    }

    .hero-carousel-card:hover .hero-nav-arrow {
        opacity: 0.85;
    }

    .hero-nav-arrow:hover {
        opacity: 1 !important;
        background: rgba(15, 23, 42, 0.8);
        transform: translateY(-50%) scale(1.1);
        border-color: rgba(255, 255, 255, 0.6);
    }

    .hero-nav-arrow.prev { left: 1.25rem; }
    .hero-nav-arrow.next { right: 1.25rem; }

    /* Vimeo-Style Bottom Pagination Dots */
    .hero-dots-container {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .hero-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.4);
        border: none;
        padding: 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .hero-dot:hover {
        background: rgba(255, 255, 255, 0.8);
    }

    .hero-dot.active {
        width: 1.4rem;
        background: #ffffff;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
    }

    /* Mobile Adjustments */
    @media (max-width: 768px) {
        .hero-container-outer {
            padding: 0.75rem 0.75rem 0.25rem;
        }

        .hero-carousel-card {
            border-radius: 1.25rem;
            min-height: 220px;
            aspect-ratio: auto;
        }

        .hero-slide-content {
            padding: 1.5rem 1.5rem 2.25rem;
        }

        .hero-nav-arrow {
            display: none; /* Rely on dots and swipe on mobile */
        }
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        const carousel = document.getElementById('heroCarouselCard');
        if (!carousel) return;

        const slides = carousel.querySelectorAll('.hero-slide');
        const dots = carousel.querySelectorAll('.hero-dot');
        const prevBtn = document.getElementById('heroPrevBtn');
        const nextBtn = document.getElementById('heroNextBtn');

        if (slides.length <= 1) return;

        let currentIndex = 0;
        let slideTimer = null;

        function goToSlide(index) {
            slides[currentIndex].classList.remove('active');
            if (dots[currentIndex]) dots[currentIndex].classList.remove('active');

            currentIndex = (index + slides.length) % slides.length;

            slides[currentIndex].classList.add('active');
            if (dots[currentIndex]) dots[currentIndex].classList.add('active');
        }

        function nextSlide() {
            goToSlide(currentIndex + 1);
        }

        function prevSlide() {
            goToSlide(currentIndex - 1);
        }

        function startAutoPlay() {
            stopAutoPlay();
            slideTimer = setInterval(nextSlide, 6000);
        }

        function stopAutoPlay() {
            if (slideTimer) clearInterval(slideTimer);
        }

        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoPlay(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoPlay(); });

        dots.forEach(dot => {
            dot.addEventListener('click', (e) => {
                const targetIndex = parseInt(e.target.dataset.slide);
                goToSlide(targetIndex);
                startAutoPlay();
            });
        });

        carousel.addEventListener('mouseenter', stopAutoPlay);
        carousel.addEventListener('mouseleave', startAutoPlay);

        startAutoPlay();
    });
</script>
