@props([
    'title' => 'Explore & Learn',
    'subtitle' => 'at your own pace.',
    'video' => 'videos/hero-video.mp4',
])

<!-- Floating Glassmorphism Hero Container -->
<div class="hero-container-outer">
    <div class="hero-section">
        <!-- Ambient Glowing Orbs for Glass Refraction & Separation -->
        <div class="glass-orb glass-orb-1"></div>
        <div class="glass-orb glass-orb-2"></div>
        <div class="glass-orb glass-orb-3"></div>

        <div class="hero-split-container">
            <!-- Left Side: Title & Subtitle -->
            <div class="hero-text-content">
                <h1 class="hero-title">{{ $title }}</h1>
                <p class="hero-subtitle">{{ $subtitle }}</p>
            </div>

            <!-- Right Side: Video Showcase Glass Card -->
            <div class="hero-video-wrapper">
                <div class="hero-video-card">
                    <video autoplay muted loop playsinline class="hero-video-element">
                        <source src="{{ secure_asset($video) }}" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hero-container-outer {
        width: 100%;
        padding: 0.85rem 1.5rem 0.35rem;
        box-sizing: border-box;
    }

    /* Floating Glassmorphism Hero Card with Strong Background Separation */
    .hero-section {
        position: relative;
        width: 100%;
        box-sizing: border-box;
        border-radius: 1.5rem;
        background: linear-gradient(135deg, rgba(225, 30, 45, 0.08) 0%, rgba(240, 244, 252, 0.95) 45%, rgba(38, 119, 184, 0.12) 100%);
        backdrop-filter: blur(24px) saturate(190%);
        -webkit-backdrop-filter: blur(24px) saturate(190%);
        border: 1.5px solid rgba(225, 30, 45, 0.14);
        box-shadow: 
            0 15px 35px -10px rgba(38, 119, 184, 0.16),
            0 5px 15px -5px rgba(225, 30, 45, 0.08),
            inset 0 1px 2px rgba(255, 255, 255, 0.95),
            inset 0 -1px 2px rgba(0, 0, 0, 0.03);
        padding: 1.5rem 2.25rem;
        overflow: hidden;
    }

    [data-theme="dark"] .hero-section {
        background: linear-gradient(135deg, rgba(225, 30, 45, 0.18) 0%, rgba(18, 20, 26, 0.92) 50%, rgba(38, 119, 184, 0.2) 100%);
        border: 1.5px solid rgba(255, 255, 255, 0.15);
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.6),
            inset 0 1px 1px rgba(255, 255, 255, 0.18),
            inset 0 -1px 1px rgba(0, 0, 0, 0.4);
    }

    /* Vibrant Ambient Background Glow Orbs */
    .glass-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(50px);
        opacity: 0.8;
        pointer-events: none;
        z-index: 0;
    }

    .glass-orb-1 {
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(225, 30, 45, 0.45) 0%, rgba(225, 30, 45, 0) 70%);
        top: -90px;
        left: -60px;
    }

    .glass-orb-2 {
        width: 340px;
        height: 340px;
        background: radial-gradient(circle, rgba(38, 119, 184, 0.45) 0%, rgba(38, 119, 184, 0) 70%);
        bottom: -110px;
        right: 8%;
    }

    .glass-orb-3 {
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(147, 51, 234, 0.35) 0%, rgba(147, 51, 234, 0) 70%);
        top: 15%;
        left: 42%;
    }

    [data-theme="dark"] .glass-orb {
        opacity: 0.85;
    }

    .hero-split-container {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        max-width: 1100px;
        margin: 0 auto;
    }

    .hero-text-content {
        flex: 1;
        min-width: 260px;
    }

    .hero-title {
        font-size: clamp(1.65rem, 2.5vw, 2.25rem);
        font-weight: 800;
        color: var(--text-main, #111827);
        line-height: 1.2;
        letter-spacing: -0.025em;
        margin: 0 0 0.4rem 0;
    }

    [data-theme="dark"] .hero-title {
        color: #ffffff;
    }

    .hero-subtitle {
        font-size: clamp(0.95rem, 1.2vw, 1.1rem);
        color: #4B5563; /* High contrast Tailwind gray-600 */
        margin: 0;
        line-height: 1.45;
        font-weight: 500;
    }

    [data-theme="dark"] .hero-subtitle {
        color: #D1D5DB; /* High contrast Tailwind gray-300 */
    }

    /* Video & Logo Container Size Control */
    .hero-video-wrapper {
        flex: 0 0 auto;
        width: 100%;
        max-width: 320px;
    }

    .hero-video-card {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        max-height: 180px;
        border-radius: 1.15rem;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1.5px solid rgba(255, 255, 255, 0.95);
        box-shadow: 
            0 12px 28px -8px rgba(0, 0, 0, 0.12),
            0 4px 10px -3px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hero-video-card:hover {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 
            0 18px 34px -10px rgba(0, 0, 0, 0.16),
            0 8px 16px -4px rgba(0, 0, 0, 0.08);
    }

    [data-theme="dark"] .hero-video-card {
        background: rgba(22, 24, 28, 0.85);
        border-color: rgba(255, 255, 255, 0.22);
        box-shadow: 0 18px 32px -10px rgba(0, 0, 0, 0.6);
    }

    .hero-video-element {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .hero-container-outer {
            padding: 0.75rem 0.75rem 0.25rem;
        }

        .hero-section {
            border-radius: 1.25rem;
            padding: 1.35rem 1.25rem;
        }

        .hero-split-container {
            flex-direction: column;
            text-align: center;
            gap: 1.15rem;
        }

        .hero-text-content {
            min-width: 100%;
        }

        .hero-video-wrapper {
            width: 100%;
            max-width: 300px;
        }
    }
</style>
