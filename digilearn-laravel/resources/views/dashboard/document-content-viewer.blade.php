<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $document['title'] ?? 'Document' }} - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if($type === 'ppt')
    <!-- Reveal.js CSS for PPT -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/dist/reveal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/dist/theme/white.css">
    @endif

    <style>
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --white: #ffffff;
            --gray-25: #fcfcfd;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
        }

        /* PPT-specific Reveal.js customizations */
        @if($type === 'ppt')
        .reveal {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
        }
        
        .reveal .slides section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            color: var(--gray-900);
        }
        
        .reveal .slides section.title-slide {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            color: white;
        }
        
        .reveal .slides section.definition-slide {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
        }
        
        .reveal .slides section.list-slide {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
        }

        .reveal .slide-content {
            outline: none;
            border-radius: 0.5rem;
            padding: 1rem;
            min-height: 100px;
        }
        @endif

        /* Header */
        .header {
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            align-items: center;
            height: 60px;
        }

        .header-left {
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-right: 1px solid var(--gray-200);
            height: 100%;
        }

        .hamburger-menu {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-right: 1rem;
        }

        .hamburger-menu:hover {
            background-color: var(--gray-100);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .logo-text {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-red);
            letter-spacing: -0.025em;
        }

        .header-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .shoutout-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shoutout-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .shoutout-tagline {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .nav-bar {
            background-color: var(--gray-200);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: var(--white);
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .back-button:hover {
            background-color: var(--gray-50);
            box-shadow: var(--shadow-md);
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .document-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .view-only-badge {
            background-color: var(--gray-100);
            color: var(--gray-700);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .main-layout {
            display: flex;
            height: calc(100vh - 140px);
            background-color: var(--gray-100);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: var(--gray-200);
            border-right: 1px solid var(--gray-300);
            overflow-y: auto;
            padding: 1rem;
        }

        .sidebar-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .page-thumbnail {
            background-color: var(--white);
            border: 2px solid transparent;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .page-thumbnail:hover {
            border-color: var(--secondary-blue);
            box-shadow: var(--shadow-md);
        }

        .page-thumbnail.active {
            border-color: var(--secondary-blue);
            background-color: #eff6ff;
        }

        .page-number {
            font-size: 0.75rem;
            color: var(--secondary-blue);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-preview {
            width: 100%;
            height: 120px;
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: var(--gray-500);
            overflow: hidden;
            position: relative;
        }

        .page-preview-text {
            padding: 0.5rem;
            font-size: 0.625rem;
            line-height: 1.2;
            text-align: left;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 8;
            line-clamp: 8;
            -webkit-box-orient: vertical;
        }

        .slide-thumbnail {
            background-color: var(--white);
            border: 2px solid transparent;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .slide-thumbnail:hover {
            border-color: var(--secondary-blue);
            box-shadow: var(--shadow-md);
        }

        .slide-thumbnail.active {
            border-color: var(--secondary-blue);
        }

        .slide-number {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            color: var(--gray-600);
            font-weight: 600;
            background-color: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        .slide-preview {
            height: 100px;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        .slide-preview.definition {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }

        .slide-preview.list {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .slide-preview img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
        }

        .slide-preview-content {
            position: relative;
            z-index: 1;
        }

        .content-area {
            flex: 1;
            background-color: var(--gray-100);
            overflow-y: auto;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .pdf-page {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            max-width: 800px;
            width: 100%;
            min-height: 600px;
            padding: 3rem;
            position: relative;
        }

        .pdf-page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .pdf-page-title {
            font-size: 0.875rem;
            color: var(--secondary-blue);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .pdf-page-content {
            font-size: 0.875rem;
            line-height: 1.8;
            color: var(--gray-800);
            text-align: justify;
            columns: 1;
            column-gap: 2rem;
        }

        .pdf-page-content h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 1.5rem 0 1rem 0;
            break-after: avoid;
        }

        .pdf-page-content p {
            margin-bottom: 1rem;
            break-inside: avoid;
        }

        .pdf-page-content ul, .pdf-page-content ol {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .pdf-page-content li {
            margin-bottom: 0.25rem;
        }

        .pdf-page-content strong {
            font-weight: 600;
        }

        .pdf-page-content em {
            font-style: italic;
        }

        .pdf-page-number {
            position: absolute;
            bottom: 1rem;
            right: 1.5rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .ppt-slide {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            max-width: 900px;
            width: 100%;
            min-height: 500px;
            color: var(--white);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ppt-slide.definition {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }

        .ppt-slide.list {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .ppt-slide-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.2;
        }

        .ppt-slide-content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 3rem;
            width: 100%;
        }

        .ppt-slide-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .ppt-slide-subtitle {
            font-size: 1.25rem;
            font-weight: 500;
            opacity: 0.9;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .ppt-slide-text {
            font-size: 1.5rem;
            font-weight: 500;
            line-height: 1.4;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .ppt-slide-list {
            list-style: none;
            font-size: 1.25rem;
            text-align: left;
            max-width: 400px;
            margin: 0 auto;
        }

        .ppt-slide-list li {
            margin-bottom: 0.75rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .ppt-slide-list li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--white);
            font-weight: bold;
            font-size: 1.5rem;
        }

        .bottom-toolbar {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 2rem;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        .toolbar-btn {
            background: none;
            border: none;
            color: var(--white);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toolbar-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .toolbar-btn.primary {
            background-color: var(--secondary-blue);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .toolbar-btn.primary:hover {
            background-color: #1e5a8a;
        }

        .hidden {
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 240px;
            }

            .header-content {
                height: 56px;
            }

            .header-left,
            .header-right {
                padding: 0 1rem;
            }

            .logo-text {
                font-size: 1rem;
            }

            .shoutout-text {
                font-size: 0.875rem;
            }

            .shoutout-tagline {
                display: none;
            }

            .nav-bar {
                padding: 0.75rem 1rem;
            }

            .content-area {
                padding: 1rem;
            }

            .pdf-page {
                padding: 2rem 1.5rem;
            }

            .ppt-slide-content {
                padding: 2rem 1.5rem;
            }

            .ppt-slide-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .main-layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: 200px;
                overflow-x: auto;
                overflow-y: hidden;
                display: flex;
                gap: 1rem;
                padding: 1rem;
            }

            .page-thumbnail,
            .slide-thumbnail {
                min-width: 150px;
                margin-bottom: 0;
            }

            .content-area {
                height: calc(100vh - 340px);
            }

            .header-left .logo-text {
                display: none;
            }

            .document-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <button class="hamburger-menu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <a href="{{ route('dashboard.main') }}" class="logo">
                    <span class="logo-text">DigiLearn</span>
                </a>
            </div>
            
            <div class="header-right">
                <div class="shoutout-logo">
                    <div>
                        <div class="shoutout-text">ShoutOutGh</div>
                        <div class="shoutout-tagline">Educating through Entertainment</div>
                    </div>
                </div>
                
                <div class="user-menu">
                    <x-user-avatar :user="auth()->user()" :size="32" class="border-2 border-white" />
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="nav-bar">
        <div class="nav-left">
            <button class="back-button" onclick="history.back()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="document-info">
                <div class="document-title">{{ $document['title'] ?? 'Document Title' }}</div>
                <div class="view-only-badge">
                    <i class="fas fa-eye"></i>
                    View Only
                </div>
            </div>
        </div>
    </div>

    <!-- Main Layout -->
    <div class="main-layout">
        @if($type === 'ppt')
            <!-- PPT with Reveal.js -->
            <div class="reveal">
                <div class="slides">
                    @foreach($document['slides'] as $index => $slide)
                    <section class="{{ $slide['type'] ?? '' }}-slide" data-slide-id="{{ $slide['number'] }}">
                        @if(isset($slide['background_image']))
                            <div class="slide-background" style="background-image: url('{{ $slide['background_image'] }}'); opacity: 0.2;"></div>
                        @endif
                        
                        <div class="slide-content" data-slide-id="{{ $slide['number'] }}">
                            @if($slide['type'] === 'title')
                                <h1>{{ $slide['title'] }}</h1>
                                <h2>{{ $slide['subtitle'] }}</h2>
                            @elseif($slide['type'] === 'definition')
                                <h2>{{ $slide['title'] }}</h2>
                                <p>{{ $slide['content'] }}</p>
                            @elseif($slide['type'] === 'list')
                                <h2>{{ $slide['title'] }}</h2>
                                <ul>
                                    @foreach($slide['content'] as $item)
                                    <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </section>
                    @endforeach
                </div>
            </div>
        @else
            <!-- PDF Layout -->
            <div class="sidebar">
                <div class="sidebar-title">Pages ({{ count($document['pages']) }})</div>
                @foreach($document['pages'] as $index => $page)
                <div class="page-thumbnail {{ $index === 0 ? 'active' : '' }}" data-page="{{ $page['number'] }}">
                    <div class="page-number">Page {{ $page['number'] }}</div>
                    <div class="page-preview">
                        <div class="page-preview-text">{{ Str::limit($page['content'], 100) }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="content-area">
                @foreach($document['pages'] as $index => $page)
                <div class="pdf-page {{ $index === 0 ? '' : 'hidden' }}" data-page="{{ $page['number'] }}">
                    <div class="pdf-page-header">
                        <div class="pdf-page-title">{{ $page['title'] }}</div>
                    </div>
                    
                    <div class="pdf-page-content">
                        {!! nl2br(e($page['content'])) !!}
                    </div>
                    
                    <div class="pdf-page-number">{{ $page['number'] }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Bottom Toolbar -->
    <div class="bottom-toolbar">
        <button class="toolbar-btn" title="Print Document">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
        </button>
        <button class="toolbar-btn" title="Download Document">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <path d="m12 18-4-4h3V9h2v5h3l-4 4z"/>
            </svg>
        </button>
        <button class="toolbar-btn" title="Zoom In">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
                <line x1="11" y1="8" x2="11" y2="14"/>
            </svg>
        </button>
        <button class="toolbar-btn" title="Zoom Out">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
        </button>
    </div>

    <!-- Scripts -->
    @if($type === 'ppt')
        <!-- Reveal.js Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/dist/reveal.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/plugin/notes/notes.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/plugin/markdown/markdown.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/plugin/highlight/highlight.js"></script>

        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            // Initialize Reveal.js for PPT (View Only)
            Reveal.initialize({
                hash: true,
                controls: true,
                progress: true,
                center: true,
                transition: 'slide',
                plugins: [ RevealNotes, RevealMarkdown, RevealHighlight ]
            });

            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    history.back();
                }
            });
        </script>
    @else
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', function() {
                // Page navigation functionality
                const pageThumbnails = document.querySelectorAll('.page-thumbnail');
                const pdfPages = document.querySelectorAll('.pdf-page');
                
                pageThumbnails.forEach(thumbnail => {
                    thumbnail.addEventListener('click', function() {
                        const pageNumber = this.dataset.page;
                        
                        // Update active thumbnail
                        pageThumbnails.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Show corresponding page
                        pdfPages.forEach(page => {
                            if (page.dataset.page === pageNumber) {
                                page.classList.remove('hidden');
                                page.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            } else {
                                page.classList.add('hidden');
                            }
                        });
                    });
                });

                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        history.back();
                    }
                    
                    // Arrow key navigation
                    if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                        const activeThumbnail = document.querySelector('.page-thumbnail.active');
                        const nextThumbnail = activeThumbnail?.nextElementSibling;
                        if (nextThumbnail) {
                            nextThumbnail.click();
                        }
                    }
                    
                    if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                        const activeThumbnail = document.querySelector('.page-thumbnail.active');
                        const prevThumbnail = activeThumbnail?.previousElementSibling;
                        if (prevThumbnail) {
                            prevThumbnail.click();
                        }
                    }
                });

                // Toolbar functionality
                const printBtn = document.querySelector('[title="Print Document"]');
                const downloadBtn = document.querySelector('[title="Download Document"]');
                
                if (printBtn) {
                    printBtn.addEventListener('click', () => {
                        window.print();
                    });
                }
                
                if (downloadBtn) {
                    downloadBtn.addEventListener('click', () => {
                        // This would typically trigger a download
                        showNotification('Download functionality would be implemented here', 'info');
                    });
                }

                function showNotification(message, type) {
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: ${type === 'success' ? 'var(--secondary-blue)' : type === 'error' ? 'var(--primary-red)' : 'var(--gray-700)'};
                        color: white;
                        padding: 1rem 1.5rem;
                        border-radius: 0.5rem;
                        font-weight: 600;
                        z-index: 10000;
                        transform: translateX(100px);
                        opacity: 0;
                        transition: all 0.3s ease;
                    `;
                    notification.textContent = message;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.transform = 'translateX(0)';
                        notification.style.opacity = '1';
                    }, 100);

                    setTimeout(() => {
                        notification.style.transform = 'translateX(100px)';
                        notification.style.opacity = '0';
                        setTimeout(() => notification.remove(), 300);
                    }, 3000);
                }
            });
        </script>
    @endif
</body>
</html>