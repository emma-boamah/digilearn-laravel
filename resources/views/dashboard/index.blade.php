@extends('layouts.dashboard')

@section('content')
    <!-- Top Header - Minimal with notifications and user profile -->
    <div class="top-header">
        <div class="header-left">
            <div class="brand-section">
                <span class="sidebar-brand">DigiLearn</span>
            </div>
        </div>
        
        <div class="header-right">
            <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
            </svg>
            
            <div class="header-divider"></div>
            
            <x-user-avatar :user="auth()->user()" :size="32" class="border-2 border-white" />
        </div>
    </div>
    
    <!-- Search/Filter Bar -->
    <div class="filter-bar">
        <div class="search-box">
            <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="search-input" placeholder="Search">
        </div>
        
        <!-- Custom Dropdown for Grade Levels -->
        <div class="custom-dropdown">
            <button class="dropdown-toggle">
                <span>Grade 1-3</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="dropdown-menu">
                <div class="dropdown-option">Grade 1-3</div>
                <div class="dropdown-option">Grade 4-6</div>
                <div class="dropdown-option">Grade 7-9</div>
                <div class="dropdown-option">High School</div> <!-- Changed to match screenshot -->
            </div>
        </div>
        
        <!-- Custom Dropdown for Subjects -->
        <div class="custom-dropdown">
            <button class="dropdown-toggle">
                <span>Subjects</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="dropdown-menu">
                <div class="dropdown-section">
                    <h4 class="section-header">Core Subjects</h4>
                    <div class="dropdown-option">
                        <svg xmlns="http://www.w3.org/2000/svg" class="subject-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Mathematics</span>
                    </div>
                    <div class="dropdown-option">
                        <svg xmlns="http://www.w3.org/2000/svg" class="subject-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span>Science</span>
                    </div>
                    <!-- Add other core subjects similarly -->
                </div>
                
                <div class="dropdown-section">
                    <h4 class="section-header">Electives</h4>
                    <div class="dropdown-option">
                        <svg xmlns="http://www.w3.org/2000/svg" class="subject-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span>English</span>
                    </div>
                    <!-- Add other electives similarly -->
                </div>
            </div>
        </div>
        
        <button class="filter-button question">Question</button>
        <button class="filter-button quiz">Quiz</button>
    </div>
    
    <!-- Hero Section with Video Background -->
    <div class="hero-section">
        <div class="hero-background">
            <video autoplay muted loop playsinline>
                <source src="{{ secure_asset('videos/hero-video.mp4') }}" type="video/mp4">
            </video>
        </div>
        <div class="hero-overlay">
            <div class="hero-content">
                <h1>Explore & Learn</h1>
                <p>at your own pace.</p>
            </div>
            <button class="hero-view-button">View</button>
        </div>
    </div>
    
    <!-- Content Section with Lessons Grid -->
    <div class="content-section">
        <div class="content-grid">
            @foreach($lessons as $lesson)
            <div class="lesson-card hover-video-card" data-video-id="lesson-video-{{ $lesson['id'] }}" data-encoded-id="{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}">
                <div class="lesson-thumbnail">
                    <!-- Video element for hover-to-play functionality -->
                    <video 
                        id="lesson-video-{{ $lesson['id'] }}" 
                        class="lesson-video" 
                        muted 
                        loop 
                        preload="metadata"
                        poster="{{ secure_asset($lesson['thumbnail']) }}"
                    >
                        <source src="{{ secure_asset($lesson['video_url']) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                    <!-- Fallback image if video fails to load -->
                    <img 
                        src="{{ secure_asset($lesson['thumbnail']) }}" 
                        alt="{{ $lesson['title'] }}" 
                        class="lesson-fallback-image"
                        style="display: none;"
                        onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Video+Lesson'"
                    >
                    
                    <div class="lesson-duration">{{ $lesson['duration'] }}</div>
                    
                    <!-- Play overlay that appears on hover -->
                    <div class="play-overlay">
                        <div class="play-button">
                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Loading indicator -->
                    <div class="video-loading" style="display: none;">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
                <div class="lesson-info">
                    <h3 class="lesson-title">{{ $lesson['title'] }}</h3>
                    <div class="lesson-meta">
                        <span class="lesson-subject">({{ $lesson['subject'] }})</span>
                        <span>{{ $lesson['instructor'] }} | {{ $lesson['year'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <style>
        /* Custom Subject drop down CSS */
        .custom-dropdown {
            position: relative;
            min-width: 120px;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--primary-red);
            font-size: 0.875rem;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .dropdown-toggle:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .dropdown-chevron {
            width: 16px;
            height: 16px;
            color: var(--primary-red);
            transition: transform 0.2s ease;
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            max-height: 300px;
            overflow-y: auto;
        }

        .custom-dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .custom-dropdown.open .dropdown-chevron {
            transform: rotate(180deg);
        }

        .dropdown-section {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .dropdown-section:last-child {
            border-bottom: none;
        }

        .section-header {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray-500);
            letter-spacing: 0.5px;
        }

        .dropdown-option {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .dropdown-option:hover {
            background-color: var(--gray-50);
        }

        .subject-icon {
            width: 18px;
            height: 18px;
            margin-right: 0.75rem;
            color: var(--gray-600);
        }

        /* YouTube-style hover video functionality */
        .lesson-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }
        
        .lesson-fallback-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        /* Play overlay styling */
        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 1;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        /* Hide play overlay when video is playing */
        .lesson-card.playing .play-overlay {
            opacity: 0;
        }
        
        /* Show play overlay on hover when not playing */
        .lesson-card:not(.playing):hover .play-overlay {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .play-button {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-red);
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .lesson-card:hover .play-button {
            background-color: var(--primary-red);
            color: var(--white);
            transform: scale(1.1);
        }
        
        /* Loading spinner */
        .video-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.7);
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Video error state */
        .lesson-card.video-error .lesson-video {
            display: none;
        }
        
        .lesson-card.video-error .lesson-fallback-image {
            display: block;
        }
        
        /* Smooth transitions */
        .lesson-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .lesson-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Ensure video doesn't interfere with click events */
        .lesson-video {
            pointer-events: none;
        }
        
        .lesson-card {
            cursor: pointer;
        }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // YouTube-style hover-to-play video functionality
        document.addEventListener('DOMContentLoaded', function() {
            const videoCards = document.querySelectorAll('.hover-video-card');
            let currentlyPlaying = null;
            
            videoCards.forEach(card => {
                const videoId = card.getAttribute('data-video-id');
                const video = document.getElementById(videoId);
                const loadingIndicator = card.querySelector('.video-loading');
                
                if (!video) return;
                
                // Handle video loading
                video.addEventListener('loadstart', function() {
                    loadingIndicator.style.display = 'flex';
                });
                
                video.addEventListener('canplay', function() {
                    loadingIndicator.style.display = 'none';
                });
                
                // Handle video errors
                video.addEventListener('error', function() {
                    console.log('Video failed to load:', videoId);
                    card.classList.add('video-error');
                    loadingIndicator.style.display = 'none';
                });
                
                // Play video on hover
                card.addEventListener('mouseenter', function() {
                    // Pause any currently playing video
                    if (currentlyPlaying && currentlyPlaying !== video) {
                        currentlyPlaying.pause();
                        currentlyPlaying.currentTime = 0;
                        currentlyPlaying.parentElement.parentElement.classList.remove('playing');
                    }
                    
                    if (video.paused && !card.classList.contains('video-error')) {
                        // Reset to beginning if it was at the end
                        if (video.currentTime === video.duration) {
                            video.currentTime = 0;
                        }
                        
                        // Play the video
                        const playPromise = video.play();
                        
                        if (playPromise !== undefined) {
                            playPromise
                                .then(() => {
                                    card.classList.add('playing');
                                    currentlyPlaying = video;
                                })
                                .catch(error => {
                                    console.log('Autoplay prevented:', error);
                                    // Fallback to showing image
                                    card.classList.add('video-error');
                                });
                        }
                    }
                });
                
                // Pause video when mouse leaves
                card.addEventListener('mouseleave', function() {
                    if (!video.paused) {
                        video.pause();
                        video.currentTime = 0; // Reset to beginning
                        card.classList.remove('playing');
                        if (currentlyPlaying === video) {
                            currentlyPlaying = null;
                        }
                    }
                });
                
                // Handle touch devices (mobile)
                card.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                }, { passive: false });
                    
                    if (video.paused && !card.classList.contains('video-error')) {
                        const playPromise = video.play();
                        if (playPromise !== undefined) {
                            playPromise
                                .then(() => {
                                    card.classList.add('playing');
                                    currentlyPlaying = video;
                                })
                                .catch(error => {
                                    console.log('Autoplay prevented on touch:', error);
                                    // On mobile, if autoplay fails, just open the lesson
                                    const encodedId = card.getAttribute('data-encoded-id');
                                    playLesson(encodedId);
                                });
                        }
                    } else if (!video.paused) {
                        video.pause();
                        video.currentTime = 0;
                        card.classList.remove('playing');
                        currentlyPlaying = null;
                    } else {
                        // If video error or other issues, just open the lesson
                        const encodedId = card.getAttribute('data-encoded-id');
                        playLesson(encodedId);
                    }
                });
                
                // Handle video end
                video.addEventListener('ended', function() {
                    this.currentTime = 0;
                    card.classList.remove('playing');
                    if (currentlyPlaying === video) {
                        currentlyPlaying = null;
                    }
                });
                
                // Handle click to play full lesson
                card.addEventListener('click', function(e) {
                    // Always navigate to lesson view when card is clicked
                    const encodedId = this.getAttribute('data-encoded-id');
                    playLesson(encodedId);
                });
            });
            
            // Pause all videos when user scrolls away or leaves page
            window.addEventListener('blur', function() {
                if (currentlyPlaying) {
                    currentlyPlaying.pause();
                    currentlyPlaying.currentTime = 0;
                    currentlyPlaying.parentElement.parentElement.classList.remove('playing');
                    currentlyPlaying = null;
                }
            });
            
            // Handle visibility change (when tab becomes inactive)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && currentlyPlaying) {
                    currentlyPlaying.pause();
                    currentlyPlaying.currentTime = 0;
                    currentlyPlaying.parentElement.parentElement.classList.remove('playing');
                    currentlyPlaying = null;
                }
            });

            // Custom dropdown functionality
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.closest('.custom-dropdown');
                    dropdown.classList.toggle('open');
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.custom-dropdown')) {
                    document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
                        dropdown.classList.remove('open');
                    });
                }
            });
            
            // Handle option selection
            document.querySelectorAll('.dropdown-option').forEach(option => {
                option.addEventListener('click', function() {
                    const text = this.querySelector('span').textContent;
                    const toggle = this.closest('.custom-dropdown').querySelector('.dropdown-toggle span');
                    toggle.textContent = text;
                    this.closest('.custom-dropdown').classList.remove('open');
                });
            });
        });
        
        function playLesson(encodedId) {
            console.log('Playing full lesson:', encodedId);
            // Redirect to the lesson view page
            window.location.href = `/dashboard/lesson/${encodedId}`;
        }
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }
        
        // Add mobile menu button for smaller screens
        if (window.innerWidth <= 768) {
            const topHeader = document.querySelector('.top-header');
            const menuButton = document.createElement('button');
            menuButton.innerHTML = `
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            `;
            menuButton.style.cssText = `
                position: absolute;
                top: 1rem;
                left: 1rem;
                background: none;
                border: none;
                cursor: pointer;
                color: var(--gray-600);
            `;
            menuButton.onclick = toggleSidebar;
            topHeader.appendChild(menuButton);
        }
    </script>
@endsection
