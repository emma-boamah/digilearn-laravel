<!-- Hero Section with Video Background -->
<div class="hero-section">
    <div class="hero-background">
        <video autoplay muted loop playsinline>
            <source src="{{ secure_asset('videos/hero-video.mp4') }}" type="video/mp4">
        </video>
    </div>
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>Test Your Knowledge</h1>
            <p>Challenge yourself with our interactive quizzes.</p>
        </div>
    </div>
</div>

<style>
    /* Hero Section */
    .hero-section {
        position: relative;
        height: 300px;
        overflow: hidden;
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .hero-background video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 2rem;
    }

    .hero-content h1 {
        font-size: 3rem;
        font-weight: 400;
        color: var(--white);
        line-height: 1.2;
    }

    .hero-content p {
        font-size: 1.5rem;
        color: var(--white);
        margin-top: 0.5rem;
        opacity: 0.9;
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .hero-section {
            height: 200px;
        }

        .hero-content h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .hero-content p {
            font-size: 16px;
            margin-bottom: 16px;
        }

        .hero-view-button {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .hero-overlay {
            padding: 0 20px;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
    }
</style>
