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
    
    <!-- KaTeX & MathLive for Mathematical Typography -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
    <script src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        window.renderMathInContainer = function(element) {
            if (!element) return;
            if (typeof renderMathInElement === 'function') {
                try {
                    renderMathInElement(element, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '\\[', right: '\\]', display: true },
                            { left: '\\(', right: '\\)', display: false },
                            { left: '$', right: '$', display: false }
                        ],
                        throwOnError: false
                    });
                } catch(e) { console.warn('KaTeX render error:', e); }
            }
        };
    </script>
    
    <!-- Anti-FOUC Sensory & Dark Mode Initialization -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            const savedTheme = localStorage.getItem('theme') || localStorage.getItem('user-theme');
            const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    
    @if($type === 'ppt')
    <!-- Reveal.js CSS for PPT -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/dist/reveal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@4.3.1/dist/theme/white.css">
    @elseif($type === 'pdf')
    <!-- PDF.js for rendering uploaded PDFs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    @endif

    <style>
        :root,
        :root[data-theme="light"] {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --bg-app: #FAF9F6; /* Autism-friendly soft cream canvas (avoiding pure #ffffff) */
            --bg-surface: #FAF9F6;
            --text-main: #22252A; /* Muted charcoal contrast layer */
            --card-surface: #F0F4F8; /* Calming soft card surface */
            --white: #FAF9F6; /* Sensory-safe soft off-white */
            --gray-25: #F8F7F4;
            --gray-50: #F4F3F0;
            --gray-100: #EAE9E4;
            --gray-200: #DDDCD6;
            --gray-300: #C8C7C0;
            --gray-400: #8C8F94;
            --gray-500: #62666D;
            --gray-600: #4B4E55;
            --gray-700: #34373D;
            --gray-800: #22252A;
            --gray-900: #181A1E;
            --border-color: #E2E1DA;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        }

        :root[data-theme="dark"] {
            --primary-red: #EF4444;
            --primary-red-hover: #DC2626;
            --secondary-blue: #38BDF8;
            --bg-app: #121212; /* Midnight theme backdrop */
            --bg-surface: #1E1E1E;
            --text-main: #F5F5F5; /* High visibility muted gray typography */
            --card-surface: #1E1E1E; /* Dark card surface separation */
            --white: #1E1E1E;
            --gray-25: #161616;
            --gray-50: #1A1A1A;
            --gray-100: #121212;
            --gray-200: #2A2A2A;
            --gray-300: #3A3A3A;
            --gray-400: #71767B;
            --gray-500: #9CA3AF;
            --gray-600: #D1D5DB;
            --gray-700: #E5E7EB;
            --gray-800: #F3F4F6;
            --gray-900: #F5F5F5;
            --border-color: #2F3336;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.4);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.6);
            color-scheme: dark;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-app);
            color: var(--text-main);
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
            padding-top: 60px; /* Offset for fixed .top-header so .nav-bar is fully visible */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* PPT-specific Gamma-style customizations */
        @if($type === 'ppt')
        .slide-thumbnail-vertical {
            background-color: var(--white);
            border: 2px solid transparent;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .slide-thumbnail-vertical:hover {
            border-color: var(--secondary-blue);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .slide-thumbnail-vertical.active {
            border-color: var(--secondary-blue);
            background-color: #eff6ff;
        }

        .slide-preview-text-vertical {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-800);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ppt-slide-card {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            margin-bottom: 2.5rem;
            max-width: 900px;
            width: 100%;
            padding: 3rem;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .ppt-slide-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
        }

        .ppt-slide-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .ppt-slide-card-number {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .ppt-slide-card-tag {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            text-transform: uppercase;
        }

        .ppt-slide-card-tag.title {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .ppt-slide-card-tag.definition {
            background-color: #f0fdf4;
            color: #15803d;
        }

        .ppt-slide-card-tag.list {
            background-color: #faf5ff;
            color: #7e22ce;
        }

        .ppt-slide-card-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.25;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
        }

        .ppt-slide-card-subtitle {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--secondary-blue);
            margin-bottom: 2rem;
        }

        .ppt-slide-card-content {
            font-size: 1.125rem;
            line-height: 1.75;
            color: var(--gray-700);
        }

        .ppt-slide-card-text {
            white-space: pre-wrap;
        }

        .ppt-slide-card-list {
            list-style: none;
            padding-left: 0;
        }

        .ppt-slide-card-list li {
            position: relative;
            padding-left: 1.75rem;
            margin-bottom: 0.75rem;
        }

        .ppt-slide-card-list li.bullet::before {
            content: "";
            position: absolute;
            left: 0.5rem;
            top: 0.75rem;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--secondary-blue);
        }

        .ppt-slide-card-list li.plain {
            padding-left: 0;
            margin-top: 1rem;
            font-weight: 500;
            color: var(--gray-800);
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
            gap: 1rem;
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
            color: var(--gray-700);
        }

        .hamburger-menu:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
        }

        .brand-logo-img {
            height: 38px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .header-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 1.5rem;
            gap: 0.75rem;
        }

        .header-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            position: relative;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .header-action-btn:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .header-notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: var(--primary-red, #ef4444);
            color: #ffffff;
            border-radius: 9999px;
            font-size: 0.625rem;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--white);
        }

        .user-menu {
            display: flex;
            align-items: center;
            margin-left: 0.25rem;
            text-decoration: none;
        }

        .nav-bar {
            background-color: var(--card-surface);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 50;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            cursor: pointer;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .back-button:hover {
            background-color: var(--gray-100);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .document-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .view-only-badge {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 0.2rem 0.65rem;
            border-radius: 1rem;
            font-size: 0.6875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            letter-spacing: 0.04em;
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
            padding: 2.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .pdf-page {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            max-width: 950px;
            width: 100%;
            min-height: 600px;
            padding: 3rem;
            position: relative;
        }

        .pdf-page-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 2.5rem;
            max-width: 1050px;
            width: fit-content;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .pdf-page-card:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .pdf-canvas-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--white);
            width: 100%;
        }

        .pdf-page-canvas {
            display: block;
            max-width: 100%;
            height: auto;
        }

        .page-thumb-canvas {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .pdf-page-number-badge {
            position: absolute;
            bottom: 12px;
            right: 16px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-600);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid var(--gray-200);
            pointer-events: none;
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

        /* Chrome-Style Floating Document Navigation & Zoom Toolbar */
        .bottom-toolbar {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 9999px;
            padding: 0.35rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.2s ease;
            user-select: none;
        }

        .bottom-toolbar:hover {
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.45);
            border-color: rgba(255, 255, 255, 0.22);
        }

        .toolbar-section {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .toolbar-separator {
            width: 1px;
            height: 18px;
            background: rgba(255, 255, 255, 0.18);
            margin: 0 0.3rem;
        }

        .toolbar-btn {
            background: transparent;
            border: none;
            color: #e2e8f0;
            cursor: pointer;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            transition: all 0.15s ease;
            padding: 0;
        }

        .toolbar-btn:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .toolbar-btn:active {
            transform: scale(0.92);
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

        .page-input-container {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 0.375rem;
            padding: 0.15rem 0.45rem;
            color: #f1f5f9;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .page-number-input {
            width: 2.2rem;
            background: transparent;
            border: none;
            outline: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8125rem;
            text-align: center;
            font-family: inherit;
            padding: 0;
            margin: 0;
            -moz-appearance: textfield;
        }

        .page-number-input::-webkit-outer-spin-button,
        .page-number-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .page-number-input:focus {
            background: rgba(56, 189, 248, 0.25);
            border-radius: 0.25rem;
            color: #38bdf8;
        }

        .page-divider {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.75rem;
        }

        .total-pages-display {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.8125rem;
        }

        .zoom-level-badge {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 2.75rem;
            text-align: center;
        }

        @media (max-width: 640px) {
            .toolbar-separator, .toolbar-section:nth-child(3), .toolbar-section:nth-child(5) {
                display: none;
            }
            .bottom-toolbar {
                bottom: 1rem;
            }
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

        /* =============================================
           THREE-MODE READING SWITCHER (SEGMENTED CONTROL)
           ============================================= */
        .mode-switcher-bar {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            background: rgba(241, 245, 249, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 4px;
            border-radius: 24px;
            border: 1px solid rgba(203, 213, 225, 0.8);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04), inset 0 1px 2px rgba(255, 255, 255, 0.9);
            margin-left: auto;
            position: relative;
            user-select: none;
            gap: 3px;
            z-index: 10;
        }

        .mode-tab-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            text-decoration: none;
            outline: none;
        }

        .mode-tab-btn:hover:not(.active) {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.6);
        }

        /* 1. Original Tab Active State (Calm Slate Blue) */
        .mode-tab-btn.active.original-active,
        .mode-tab-btn.active:not(.acquisition-active):not(.application-active) {
            background: #ffffff;
            color: var(--secondary-blue, #2677B8);
            box-shadow: 0 2px 8px rgba(38, 119, 184, 0.15), 0 1px 3px rgba(0, 0, 0, 0.04);
            font-weight: 700;
        }

        .mode-tab-btn.active.original-active i,
        .mode-tab-btn.active:not(.acquisition-active):not(.application-active) i {
            color: var(--secondary-blue, #2677B8);
        }

        /* 2. Acquisition Tab Active State (Glowing Warm Amber / Deep Orange) */
        .mode-tab-btn.active.acquisition-active {
            background: #ffffff;
            color: #c2410c;
            box-shadow: 0 3px 12px rgba(234, 88, 12, 0.18), 0 1px 3px rgba(0, 0, 0, 0.04);
            font-weight: 700;
        }

        .mode-tab-btn.active.acquisition-active i {
            color: #ea580c;
        }

        /* 3. Application Tab Active State (Rewarding Emerald Green) */
        .mode-tab-btn.active.application-active {
            background: #ffffff;
            color: #047857;
            box-shadow: 0 3px 12px rgba(5, 150, 105, 0.18), 0 1px 3px rgba(0, 0, 0, 0.04);
            font-weight: 700;
        }

        .mode-tab-btn.active.application-active i {
            color: #059669;
        }

        /* Micro-Copy Sub-Capsule Badges */
        .mode-sub-badge {
            font-size: 0.625rem;
            font-weight: 800;
            padding: 0.18rem 0.5rem;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: all 0.25s ease;
            line-height: 1;
        }

        .mode-tab-btn:not(.active) .mode-sub-badge {
            background: #e2e8f0;
            color: #64748b;
        }

        .mode-tab-btn.active.acquisition-active .mode-sub-badge {
            background: #ffedd5;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .mode-tab-btn.active.application-active .mode-sub-badge {
            background: #d1fae5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        /* Dark Mode Segmented Control */
        [data-theme="dark"] .mode-switcher-bar {
            background: rgba(30, 30, 30, 0.95);
            border-color: rgba(60, 64, 70, 0.8);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.05);
        }

        [data-theme="dark"] .mode-tab-btn {
            color: #9ca3af;
        }

        [data-theme="dark"] .mode-tab-btn:hover:not(.active) {
            color: #f3f4f6;
            background: rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .mode-tab-btn.active.original-active,
        [data-theme="dark"] .mode-tab-btn.active:not(.acquisition-active):not(.application-active) {
            background: #262626;
            color: #38bdf8;
            box-shadow: 0 2px 10px rgba(56, 189, 248, 0.2);
        }

        [data-theme="dark"] .mode-tab-btn.active.acquisition-active {
            background: #262626;
            color: #fb923c;
            box-shadow: 0 3px 12px rgba(251, 146, 60, 0.25);
        }

        [data-theme="dark"] .mode-tab-btn.active.application-active {
            background: #262626;
            color: #34d399;
            box-shadow: 0 3px 12px rgba(52, 211, 153, 0.25);
        }

        [data-theme="dark"] .mode-sub-badge {
            background: #374151;
            color: #d1d5db;
        }

        [data-theme="dark"] .mode-tab-btn.active.acquisition-active .mode-sub-badge {
            background: #431407;
            color: #fdba74;
            border: 1px solid #7c2d12;
        }

        [data-theme="dark"] .mode-tab-btn.active.application-active .mode-sub-badge {
            background: #022c22;
            color: #6ee7b7;
            border: 1px solid #065f46;
        }

        /* Responsive Navbar & Segmented Control */
        @media (max-width: 820px) {
            .nav-bar {
                flex-wrap: wrap;
                gap: 0.75rem;
                height: auto;
                padding: 0.75rem 1rem;
            }

            .mode-switcher-bar {
                width: 100%;
                justify-content: space-between;
                margin-left: 0;
            }

            .mode-tab-btn {
                flex: 1;
                justify-content: center;
                padding: 0.4rem 0.5rem;
                font-size: 0.75rem;
                gap: 0.35rem;
            }

            .mode-sub-badge {
                font-size: 0.5625rem;
                padding: 0.12rem 0.35rem;
            }
        }

        /* Workspaces Controller */
        .mode-workspace {
            display: none;
            width: 100%;
            height: calc(100vh - 124px);
            height: calc(100dvh - 124px);
            overflow: hidden;
        }

        .mode-workspace.active {
            display: flex;
        }

        /* Mode 2: Acquisition Mode */
        .acquisition-layout {
            display: flex;
            width: 100%;
            height: 100%;
            background-color: var(--gray-50);
            overflow: hidden;
        }

        .acquisition-sidebar {
            width: 300px;
            min-width: 300px;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .acquisition-main-pane {
            flex: 1;
            overflow-y: auto;
            padding: 2.5rem 2rem 5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .acquisition-card-container {
            max-width: 860px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Hero Banner - White Glassmorphism with subtle black edge blend */
        .cognitive-banner {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-top: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: 1.25rem;
            padding: 1.75rem 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
            position: relative;
            color: var(--gray-900);
        }

        .cognitive-banner h2 {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .cognitive-banner p {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.6;
        }

        .sq3r-pills-row {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .sq3r-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sq3r-pill.active {
            background: #eff6ff;
            border: 1.5px solid var(--secondary-blue, #2677B8);
            color: var(--secondary-blue, #2677B8);
            font-weight: 800;
        }

        .glossary-matrix-section {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 1.25rem;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .section-header-title {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1rem;
        }

        .glossary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 0.875rem;
        }

        .glossary-card {
            background: var(--white);
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .glossary-card:hover {
            border-color: var(--secondary-blue, #2677B8);
            background: #f0f7ff;
        }

        .glossary-term {
            font-weight: 700;
            font-size: 0.875rem;
            color: var(--secondary-blue, #2677B8);
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .glossary-definition {
            font-size: 0.78125rem;
            color: #475569;
            line-height: 1.45;
        }

        .acquisition-section-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 1.25rem;
            padding: 2.25rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            line-height: 1.85;
            color: var(--gray-800);
            font-size: 0.9375rem;
        }

        .inquiry-focus-box {
            background: #f0f7ff;
            border: 1px solid #bae6fd;
            border-radius: 0.75rem;
            padding: 1.1rem 1.35rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1.75rem;
        }

        .inquiry-icon-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0f2fe;
            color: var(--secondary-blue, #2677B8);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        .inquiry-label {
            font-size: 0.6875rem;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
        }

        .inquiry-question {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #0369a1;
            line-height: 1.4;
        }

        /* Retrieval Checkpoint - Glassmorphism Card with Blue Accent */
        .retrieval-checkpoint-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(38, 119, 184, 0.25);
            border-top: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 1.75rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        }

        .checkpoint-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--secondary-blue, #2677B8);
            margin-bottom: 0.5rem;
        }

        .checkpoint-input {
            width: 100%;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-family: inherit;
            resize: vertical;
            min-height: 85px;
            background: var(--white);
            box-sizing: border-box;
            margin: 0.75rem 0;
        }

        .checkpoint-submit-btn {
            background: var(--secondary-blue, #2677B8);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .checkpoint-submit-btn:hover {
            background: #1d6199;
        }

        /* Dark Theme Support */
        [data-theme="dark"] .cognitive-banner,
        [data-theme="dark"] .retrieval-checkpoint-box {
            background: rgba(30, 41, 59, 0.75);
            border-color: rgba(255, 255, 255, 0.12);
            color: var(--text-main);
        }

        [data-theme="dark"] .cognitive-banner h2 {
            color: #f1f5f9;
        }

        [data-theme="dark"] .cognitive-banner p {
            color: #cbd5e1;
        }

        [data-theme="dark"] .sq3r-pill {
            background: rgba(38, 119, 184, 0.2);
            border-color: rgba(38, 119, 184, 0.4);
            color: #60a5fa;
        }

        /* Mode 3: Application Mode */
        .application-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            height: 100%;
            background-color: var(--gray-50);
            overflow: hidden;
        }

        .app-left-pane {
            border-right: 1px solid var(--border-color);
            height: 100%;
            overflow-y: auto;
            padding: 2rem;
            background: var(--bg-surface);
        }

        .app-right-pane {
            height: 100%;
            overflow-y: auto;
            padding: 2rem;
            background: var(--bg-app);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .app-doc-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1.35rem;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .app-card-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .app-prose {
            font-size: 0.9rem;
            color: var(--text-main);
            line-height: 1.7;
        }

        .app-prose p {
            margin: 0 0 0.85rem 0;
        }

        .app-prose p:last-child {
            margin-bottom: 0;
        }

        .app-rule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 0.85rem;
        }

        .app-rule-card {
            background: var(--bg-app);
            border: 1px solid var(--border-color);
            border-radius: 0.625rem;
            padding: 0.85rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .app-rule-card:hover {
            border-color: var(--secondary-blue, #2677B8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .app-rule-name {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .app-rule-math {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main);
            padding: 0.25rem 0;
        }

        .app-rule-desc {
            font-size: 0.775rem;
            color: var(--text-muted);
            line-height: 1.45;
            margin-top: 0.15rem;
        }

        .app-worked-box {
            background: var(--bg-app);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem 1.15rem;
            margin-bottom: 0.85rem;
        }

        .app-problem-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--secondary-blue, #2677B8);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }

        .app-problem-statement {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.85rem;
            line-height: 1.5;
        }

        .app-step-list {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            margin-bottom: 0.85rem;
        }

        .app-step-item {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.845rem;
            color: var(--text-main);
            line-height: 1.55;
        }

        .app-step-num {
            flex-shrink: 0;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
            background: rgba(38, 119, 184, 0.12);
            color: var(--secondary-blue, #2677B8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6875rem;
            font-weight: 700;
            margin-top: 0.15rem;
        }

        .app-solution-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.95rem;
            background: rgba(38, 119, 184, 0.08);
            border: 1px solid rgba(38, 119, 184, 0.25);
            border-radius: 0.5rem;
            color: var(--secondary-blue, #2677B8);
            font-size: 0.875rem;
            font-weight: 700;
        }

        .app-solution-pill i {
            color: var(--secondary-blue, #2677B8);
            font-size: 0.95rem;
        }

        .app-solution-pill .solution-content {
            font-weight: 600;
        }

        .app-tips-container {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .app-tip-row {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            padding: 0.65rem 0.85rem;
            background: var(--bg-app);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            color: var(--text-main);
            line-height: 1.5;
        }

        .app-tip-row i {
            color: #D97706;
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .app-snippet-card {
            background: #0b1120;
            color: #e2e8f0;
            border: 1px solid #1e293b;
            border-radius: 0.625rem;
            padding: 1.1rem;
            position: relative;
            font-family: 'JetBrains Mono', 'Fira Code', ui-monospace, monospace;
            font-size: 0.8125rem;
            line-height: 1.65;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .app-copy-btn {
            position: absolute;
            top: 0.625rem;
            right: 0.625rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            padding: 0.25rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.6875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .app-copy-btn:hover {
            background: rgba(56, 189, 248, 0.3);
            color: #38bdf8;
        }

        .app-doc-note {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-top: 1rem;
        }

        .project-builder-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
        }

        .builder-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.125rem;
            font-weight: 700;
            color: #059669;
            margin-bottom: 1rem;
        }

        .target-goal-input {
            width: 100%;
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.65rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s ease;
        }

        /* Actionable Implementation Steps Checklist */
        .checklist-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 1rem;
            border-radius: 0.625rem;
            border: 1px solid transparent;
            margin-bottom: 0.4rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
        }

        .checklist-item:hover {
            background: var(--gray-100);
        }

        .checklist-item input[type="checkbox"] {
            accent-color: #059669;
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .checklist-item span {
            font-size: 0.875rem;
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        /* Action Step Glow Border Completion Effect */
        @keyframes border-glow-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.4);
                border-color: rgba(56, 189, 248, 0.8);
            }
            50% {
                box-shadow: 0 0 12px 3px rgba(56, 189, 248, 0.25);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(56, 189, 248, 0);
                border-color: rgba(63, 65, 71, 0.9);
            }
        }

        .checklist-item.is-completed {
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.08) 0%, rgba(56, 189, 248, 0.02) 100%);
            border: 1px solid rgba(56, 189, 248, 0.35);
            animation: border-glow-pulse 0.8s ease-out forwards;
        }

        .checklist-item.is-completed span {
            color: var(--text-muted);
            text-decoration: line-through;
        }

        .builder-scratchpad {
            width: 100%;
            min-height: 240px;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem;
            font-family: inherit;
            font-size: 0.875rem;
            line-height: 1.6;
            background: var(--bg-surface);
            color: var(--text-main);
            box-sizing: border-box;
            resize: vertical;
            margin-top: 0.75rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .export-blueprint-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #059669;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .export-blueprint-btn:hover {
            background: #047857;
        }

        /* Dark Mode High-Contrast & Depth Overrides */
        [data-theme="dark"] .sidebar {
            background: rgba(18, 18, 18, 0.75);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .page-thumbnail,
        [data-theme="dark"] .slide-thumbnail-vertical {
            background: #1e1e1e;
            border-color: #2f3336;
        }

        [data-theme="dark"] .page-thumbnail.active,
        [data-theme="dark"] .slide-thumbnail-vertical.active {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
        }

        [data-theme="dark"] .application-layout {
            background-color: #121212;
        }

        [data-theme="dark"] .app-left-pane {
            background: #121212;
            border-right: 1px solid #2f3336;
        }

        [data-theme="dark"] .app-right-pane {
            background: #121212;
        }

        [data-theme="dark"] .app-doc-card {
            background: #1e1e1e;
            border-color: #3f4147;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        [data-theme="dark"] .app-card-title {
            color: #38bdf8; /* Desaturated high-visibility cyan */
        }

        [data-theme="dark"] .app-snippet-card {
            background: #0b1120;
            border-color: #273549;
        }

        [data-theme="dark"] .app-doc-note {
            color: #e2e8f0;
            opacity: 0.88;
        }

        [data-theme="dark"] .project-builder-panel {
            background: #1e1e1e;
            border-color: #3f4147;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        }

        [data-theme="dark"] .builder-heading {
            color: #34d399;
        }

        [data-theme="dark"] .target-goal-input {
            background: #16181c;
            border-color: #3f4147;
            color: #f1f5f9;
        }

        [data-theme="dark"] .target-goal-input:focus {
            border-color: #38bdf8;
        }

        [data-theme="dark"] .checklist-item:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        [data-theme="dark"] .checklist-item span {
            color: #e2e8f0;
        }

        [data-theme="dark"] .checklist-item.is-completed span {
            color: #94a3b8;
        }

        [data-theme="dark"] .builder-scratchpad {
            background: #16181c;
            border-color: #3f4147;
            color: #f1f5f9;
        }

        [data-theme="dark"] .builder-scratchpad:focus {
            border-color: #38bdf8;
        }

        /* Acquisition Dark Mode Overrides */
        [data-theme="dark"] .acquisition-layout {
            background-color: #121212;
        }

        [data-theme="dark"] .acquisition-sidebar {
            background: #16181c;
            border-color: #2f3336;
        }

        [data-theme="dark"] .acquisition-section-card {
            background: #1e1e1e;
            border-color: #3f4147;
            color: #e2e8f0;
        }

        [data-theme="dark"] .acquisition-section-card h3 {
            color: #38bdf8 !important;
        }

        [data-theme="dark"] .inquiry-focus-box {
            background: rgba(14, 116, 144, 0.15);
            border-color: rgba(56, 189, 248, 0.3);
        }

        [data-theme="dark"] .inquiry-question {
            color: #7dd3fc;
        }

        [data-theme="dark"] .glossary-matrix-section {
            background: #1e1e1e;
            border-color: #3f4147;
        }

        [data-theme="dark"] .glossary-card {
            background: #16181c;
            border-color: #2f3336;
        }

        [data-theme="dark"] .glossary-term {
            color: #38bdf8;
        }

        [data-theme="dark"] .glossary-definition {
            color: #cbd5e1;
        }

        [data-theme="dark"] .checkpoint-input {
            background: #16181c;
            border-color: #3f4147;
            color: #f1f5f9;
        }

        /* Typography & Headers Support for Light/Dark Themes */
        .app-pane-title {
            font-size: 1.0625rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }

        [data-theme="dark"] .app-pane-title {
            color: #38bdf8;
        }

        .app-pane-subtitle {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        [data-theme="dark"] .app-pane-subtitle {
            color: #cbd5e1;
        }

        .blueprint-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        [data-theme="dark"] .blueprint-title {
            color: #38bdf8;
        }

        .app-section-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
        }

        [data-theme="dark"] .app-section-label {
            color: #94a3b8;
        }

        .app-section-subtitle {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        [data-theme="dark"] .app-section-subtitle {
            color: #e2e8f0;
        }

        .app-progress-badge {
            font-size: 0.6875rem;
            font-weight: 700;
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
        }

        [data-theme="dark"] .app-progress-badge {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.3);
        }

        .jitl-badge {
            background: #e0f2fe;
            color: #0284c7;
            font-size: 0.625rem;
            font-weight: 800;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            letter-spacing: 0.05em;
            border: 1px solid #bae6fd;
            text-transform: uppercase;
        }

        [data-theme="dark"] .jitl-badge {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.3);
        }

        .export-blueprint-btn-styled {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            color: #0284c7;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.4rem 0.85rem;
            border-radius: 0.5rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .export-blueprint-btn-styled:hover {
            background: #bae6fd;
        }

        [data-theme="dark"] .export-blueprint-btn-styled {
            background: rgba(56, 189, 248, 0.15);
            border-color: rgba(56, 189, 248, 0.35);
            color: #38bdf8;
        }

        [data-theme="dark"] .export-blueprint-btn-styled:hover {
            background: rgba(56, 189, 248, 0.25);
        }

        @media (max-width: 900px) {
            .application-layout {
                grid-template-columns: 1fr;
                height: auto;
            }
            .mode-switcher-bar {
                margin-left: 0;
                width: 100%;
                justify-content: center;
            }
            .acquisition-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Top Platform Header (Full working notifications, dark mode, and user dropdown) -->
    @include('components.dashboard-header', ['logoRoute' => 'dashboard.digilearn'])

    <!-- Navigation Bar with 3-Mode Switcher -->
    <div class="nav-bar">
        <div class="nav-left">
            <button class="back-button" onclick="history.back()" title="Go Back">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="document-info">
                <div class="document-title">{{ $document['title'] ?? 'Document Title' }}</div>
                <div class="view-only-badge">
                    <i class="fas fa-book-open"></i>
                    {{ strtoupper($type ?? 'PDF') }}
                </div>
            </div>
        </div>

        <!-- Mode Switcher Tabs -->
        <div class="mode-switcher-bar" id="modeSwitcherBar">
            <button class="mode-tab-btn active original-active" data-mode="original" id="modeBtnOriginal" onclick="switchReadingMode('original')">
                <i class="fas fa-file-alt"></i>
                <span>Original</span>
            </button>
            <button class="mode-tab-btn" data-mode="acquisition" id="modeBtnAcquisition" onclick="switchReadingMode('acquisition')">
                <i class="fas fa-brain"></i>
                <span>Acquisition</span>
                <span class="mode-sub-badge">Active Recall</span>
            </button>
            <button class="mode-tab-btn" data-mode="application" id="modeBtnApplication" onclick="switchReadingMode('application')">
                <i class="fas fa-hammer"></i>
                <span>Application</span>
                <span class="mode-sub-badge">Build Mode</span>
            </button>
        </div>
    </div>

    <!-- WORKSPACE 1: Original Mode (Native View) -->
    <div class="mode-workspace active" id="modeWorkspaceOriginal">
        <div class="main-layout" style="width: 100%; height: 100%;">
            @if($type === 'ppt')
                <!-- PPT Layout (Gamma style - Vertical Scrolling) -->
                <div class="sidebar">
                    <div class="sidebar-title">Slides ({{ count($document['slides']) }})</div>
                    @foreach($document['slides'] as $index => $slide)
                    <div class="slide-thumbnail-vertical {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $slide['number'] }}">
                        <div class="page-number">Slide {{ $slide['number'] }}</div>
                        <div class="slide-preview-text-vertical">{{ $slide['title'] ?: 'Slide ' . $slide['number'] }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="content-area" id="pptContentArea">
                    @foreach($document['slides'] as $index => $slide)
                    <div class="ppt-slide-card" id="slide-{{ $slide['number'] }}" data-slide="{{ $slide['number'] }}">
                        <div class="ppt-slide-card-header">
                            <span class="ppt-slide-card-number">Slide {{ $slide['number'] }} of {{ count($document['slides']) }}</span>
                            @if(isset($slide['type']))
                                <span class="ppt-slide-card-tag {{ $slide['type'] }}">{{ ucfirst($slide['type']) }}</span>
                            @endif
                        </div>
                        
                        <div class="ppt-slide-card-body">
                            <h2 class="ppt-slide-card-title">{{ $slide['title'] }}</h2>
                            
                            @if(isset($slide['subtitle']) && $slide['subtitle'])
                                <h3 class="ppt-slide-card-subtitle">{{ $slide['subtitle'] }}</h3>
                            @endif

                            <div class="ppt-slide-card-content">
                                @if(is_array($slide['content']))
                                    <ul class="ppt-slide-card-list">
                                        @foreach($slide['content'] as $item)
                                            @if(is_array($item))
                                                <li class="{{ $item['is_bullet'] ? 'bullet' : 'plain' }}">{{ $item['text'] }}</li>
                                            @else
                                                <li>{{ $item }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @elseif($slide['content'])
                                    <p class="ppt-slide-card-text">{!! nl2br(e($slide['content'])) !!}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- PDF Layout -->
                <div class="sidebar" id="pdfSidebar">
                    <div class="sidebar-title">Pages (<span id="pdfPageCountHeader">{{ $document['pages_count'] ?? count($document['pages'] ?? []) }}</span>)</div>
                    <div id="pdfThumbnailsList">
                        @foreach($document['pages'] ?? [] as $index => $page)
                        <div class="page-thumbnail {{ $index === 0 ? 'active' : '' }}" data-page="{{ $page['number'] }}" id="thumb-page-{{ $page['number'] }}">
                            <div class="page-number">Page {{ $page['number'] }}</div>
                            <div class="page-preview" id="thumb-preview-{{ $page['number'] }}">
                                <canvas id="thumb-canvas-{{ $page['number'] }}" class="page-thumb-canvas"></canvas>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="content-area" id="pdfContentArea">
                    <div id="pdfLoadingState" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; color: var(--gray-600);">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--secondary-blue); margin-bottom: 1rem;"></i>
                        <p style="font-weight: 500;">Loading PDF document...</p>
                    </div>
                    <div id="pdfPagesContainer" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                        <!-- Dynamic PDF page canvases rendered by PDF.js -->
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- WORKSPACE 2: Acquisition Mode (Cognitive / SQ3R Active Learning) -->
    <div class="mode-workspace" id="modeWorkspaceAcquisition">
        <div class="acquisition-layout">
            <!-- Sidebar: Chapter Outline & Concept Progress -->
            <div class="acquisition-sidebar">
                <div>
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem;">Study Progress</h4>
                    <div style="background: var(--gray-200); border-radius: 9999px; height: 8px; width: 100%; overflow: hidden;">
                        <div id="acquisitionProgressBar" style="background: var(--secondary-blue, #2677B8); height: 100%; width: 25%; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--gray-500); margin-top: 0.35rem;">
                        <span>Mastery Score</span>
                        <span id="acquisitionProgressScore">25%</span>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 0.8125rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Chapter Outline</h4>
                    <div id="acquisitionOutlineList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <!-- Populated by JavaScript from document structure -->
                    </div>
                </div>
            </div>

            <!-- Main Reading Pane -->
            <div class="acquisition-main-pane" id="acquisitionMainPane">
                <div class="acquisition-card-container">
                    <!-- Survey Hero Banner (SQ3R Step 1) -->
                    <div class="cognitive-banner">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                            <h2 style="font-size: 1.35rem; font-weight: 800; line-height: 1.3; margin: 0;">{{ $document['title'] ?? 'Document Guide' }}</h2>
                            <span class="sq3r-badge" style="font-size: 0.65rem; font-weight: 800; background: var(--bg-surface); color: var(--text-muted); padding: 0.2rem 0.6rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid var(--border-color); white-space: nowrap;">SQ3R Cognitive Framework</span>
                        </div>
                        <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6; margin-top: 0.6rem; margin-bottom: 1.25rem;">This active reading mode systematically guides your working memory through vocabulary pre-teaching, structured concept breakdowns, and real-time active recall checkpoints.</p>
                        <div class="sq3r-pills-row">
                            <span class="sq3r-pill"><i class="fas fa-search" style="font-size: 0.7rem; margin-right: 0.35rem;"></i> 1. Survey</span>
                            <span class="sq3r-pill"><i class="fas fa-clock" style="font-size: 0.7rem; margin-right: 0.35rem;"></i> 2. Question</span>
                            <span class="sq3r-pill active"><i class="fas fa-book-open" style="font-size: 0.7rem; margin-right: 0.35rem;"></i> 3. Read</span>
                            <span class="sq3r-pill"><i class="fas fa-users" style="font-size: 0.7rem; margin-right: 0.35rem;"></i> 4. Recite</span>
                            <span class="sq3r-pill"><i class="fas fa-clipboard-check" style="font-size: 0.7rem; margin-right: 0.35rem;"></i> 5. Review</span>
                        </div>
                    </div>

                    <!-- Vocabulary Pre-Teaching Matrix (Minimizes Decoding Friction) -->
                    <div class="glossary-matrix-section">
                        <div class="section-header-title">
                            <span style="font-size: 1.125rem; font-weight: 900; color: var(--secondary-blue, #2677B8); margin-right: 0.25rem;">AZ</span>
                            <span style="font-size: 1.0625rem; font-weight: 800; color: var(--text-main);">Pre-Reading Vocabulary & Glossary Matrix</span>
                        </div>
                        <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1.25rem;">Mastering these core technical terms beforehand frees up cognitive working memory for deep concept synthesis.</p>
                        <div class="glossary-grid" id="acquisitionGlossaryGrid">
                            <!-- Populated dynamically or fallback -->
                        </div>
                    </div>

                    <!-- Structured Reading Sections Container -->
                    <div id="acquisitionSectionsContainer" style="display: flex; flex-direction: column; gap: 2rem;">
                        <!-- Sections with Predictive Inquiry & Active Recall Checkpoints rendered dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WORKSPACE 3: Application Mode (Build / Production Workspace) -->
    <div class="mode-workspace" id="modeWorkspaceApplication">
        <div class="application-layout">
            <!-- Left Pane: Technical Documentation & Extracted Blocks -->
            <div class="app-left-pane">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <h3 class="app-pane-title">Reference Documentation</h3>
                        <p class="app-pane-subtitle">Just-In-Time formulas, code blocks, and implementation guides.</p>
                    </div>
                    <span class="jitl-badge">JITL Framework</span>
                </div>
                <div id="applicationDocsContainer">
                    <!-- Dynamic code snippets, formulas, and rule blocks -->
                </div>
            </div>

            <!-- Right Pane: Project Blueprint & Action Workspace -->
            <div class="app-right-pane">
                <!-- Project Goal & Specification -->
                <div class="project-builder-panel">
                    <div class="builder-heading">
                        <span class="blueprint-title">
                            <i class="fas fa-drafting-compass" style="color: var(--secondary-blue, #2677B8);"></i> Project Blueprint
                        </span>
                        <button class="export-blueprint-btn-styled" id="exportBlueprintBtn" onclick="exportProjectBlueprint()">
                            <i class="fas fa-download"></i> Export Blueprint (.md)
                        </button>
                    </div>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <label class="app-section-label">Target Project / Objective</label>
                        <input type="text" id="projectGoalInput" class="target-goal-input" placeholder="e.g. Building an interactive quiz app / Calculating physics simulation..." 
                               value="Implementation Plan for {{ $document['title'] ?? 'Document' }}">
                    </div>

                    <!-- Implementation Checklist -->
                    <div style="margin-top: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span class="app-section-subtitle">Actionable Implementation Steps</span>
                            <span id="checklistProgressText" class="app-progress-badge">0/4 Done</span>
                        </div>
                        <div id="applicationChecklistContainer">
                            <!-- Populated with interactive checkboxes -->
                        </div>
                    </div>

                    <!-- Live Notes & Code Scratchpad -->
                    <div style="margin-top: 1.5rem;">
                        <label class="app-section-subtitle" style="margin-bottom: 0.4rem;">
                            <i class="fas fa-edit" style="color: var(--secondary-blue, #2677B8);"></i> Project Notes & Solution Scratchpad <span style="font-weight: 400; color: var(--text-muted); font-size: 0.75rem;">(Auto-saved)</span>
                        </label>
                        <textarea class="builder-scratchpad" id="projectScratchpad" placeholder="Draft your implementation notes, code snippets, database schemas, or solution design here..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Toolbar (Only for Original Mode - Chrome Style Page Navigation & Controls) -->
    <div class="bottom-toolbar" id="originalModeBottomToolbar">
        <!-- Page Navigation with Direct Input -->
        <div class="toolbar-section">
            <button class="toolbar-btn" id="prevPageBtn" title="Previous Page (Up / Left Arrow)" aria-label="Previous Page">
                <i class="fas fa-chevron-up"></i>
            </button>
            <div class="page-input-container">
                <input type="text" inputmode="numeric" pattern="[0-9]*" class="page-number-input" id="pageNumberInput" value="1" title="Type page number and press Enter" aria-label="Current Page">
                <span class="page-divider">/</span>
                <span class="total-pages-display" id="totalPagesDisplay">{{ $document['pages_count'] ?? count($document['pages'] ?? $document['slides'] ?? []) ?: '1' }}</span>
            </div>
            <button class="toolbar-btn" id="nextPageBtn" title="Next Page (Down / Right Arrow)" aria-label="Next Page">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>

        <div class="toolbar-separator"></div>

        <!-- Zoom Controls -->
        <div class="toolbar-section">
            <button class="toolbar-btn" id="zoomOutBtn" title="Zoom Out" aria-label="Zoom Out">
                <i class="fas fa-minus"></i>
            </button>
            <span class="zoom-level-badge" id="zoomLevelDisplay" title="Current Zoom Level">100%</span>
            <button class="toolbar-btn" id="zoomInBtn" title="Zoom In" aria-label="Zoom In">
                <i class="fas fa-plus"></i>
            </button>
            <button class="toolbar-btn" id="fitWidthBtn" title="Fit to Width" aria-label="Fit to Width">
                <i class="fas fa-arrows-alt-h"></i>
            </button>
        </div>

        <div class="toolbar-separator"></div>

        <!-- Actions -->
        <div class="toolbar-section">
            <button class="toolbar-btn" id="printDocBtn" title="Print Document" aria-label="Print">
                <i class="fas fa-print"></i>
            </button>
            <button class="toolbar-btn" id="downloadDocBtn" title="Download Document" aria-label="Download">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    @if($type === 'ppt')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', function() {
                const slideThumbnails = document.querySelectorAll('.slide-thumbnail-vertical');
                const slideCards = document.querySelectorAll('.ppt-slide-card');
                const contentArea = document.getElementById('pptContentArea');
                const pageNumberInput = document.getElementById('pageNumberInput');
                const totalPagesDisplay = document.getElementById('totalPagesDisplay');
                const prevPageBtn = document.getElementById('prevPageBtn');
                const nextPageBtn = document.getElementById('nextPageBtn');
                const totalSlides = slideCards.length || {{ count($document['slides'] ?? []) }};
                
                if (totalPagesDisplay) totalPagesDisplay.textContent = totalSlides;
                if (pageNumberInput) pageNumberInput.value = '1';

                function goToSlide(slideNum) {
                    const validNum = Math.max(1, Math.min(totalSlides, parseInt(slideNum, 10) || 1));
                    const targetCard = document.getElementById(`slide-${validNum}`);
                    if (targetCard) {
                        slideThumbnails.forEach(t => t.classList.remove('active'));
                        const matchingThumb = document.querySelector(`.slide-thumbnail-vertical[data-slide="${validNum}"]`);
                        if (matchingThumb) matchingThumb.classList.add('active');
                        targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    if (pageNumberInput) pageNumberInput.value = validNum;
                }

                slideThumbnails.forEach(thumbnail => {
                    thumbnail.addEventListener('click', function() {
                        const slideNumber = this.dataset.slide;
                        goToSlide(slideNumber);
                    });
                });

                if (pageNumberInput) {
                    pageNumberInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            goToSlide(this.value);
                            this.blur();
                        }
                    });
                    pageNumberInput.addEventListener('blur', function() {
                        goToSlide(this.value);
                    });
                }

                if (prevPageBtn) {
                    prevPageBtn.addEventListener('click', () => {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToSlide(curr - 1);
                    });
                }

                if (nextPageBtn) {
                    nextPageBtn.addEventListener('click', () => {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToSlide(curr + 1);
                    });
                }

                // Intersection Observer to highlight current slide and update page input on scroll
                const observerOptions = {
                    root: contentArea,
                    rootMargin: '-10% 0px -80% 0px',
                    threshold: 0
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const slideNumber = entry.target.dataset.slide;
                            if (pageNumberInput && document.activeElement !== pageNumberInput) {
                                pageNumberInput.value = slideNumber;
                            }
                            slideThumbnails.forEach(t => {
                                if (t.dataset.slide === slideNumber) {
                                    t.classList.add('active');
                                    t.scrollIntoView({ behavior: 'nearest', block: 'only' });
                                } else {
                                    t.classList.remove('active');
                                }
                            });
                        }
                    });
                }, observerOptions);

                slideCards.forEach(card => observer.observe(card));

                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        history.back();
                    }
                    if (document.activeElement === pageNumberInput) return;
                    
                    // Arrow key navigation
                    if (e.key === 'ArrowDown' || e.key === 'ArrowRight' || e.key === 'PageDown') {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToSlide(curr + 1);
                    }
                    
                    if (e.key === 'ArrowUp' || e.key === 'ArrowLeft' || e.key === 'PageUp') {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToSlide(curr - 1);
                    }
                });
            });
        </script>
    @else
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', function() {
                const pdfUrl = "{{ $document['file_url'] ?? asset('storage/' . ($document['file_path'] ?? '')) }}";
                const pdfPagesContainer = document.getElementById('pdfPagesContainer');
                const pdfLoadingState = document.getElementById('pdfLoadingState');
                const pdfThumbnailsList = document.getElementById('pdfThumbnailsList');
                const pdfPageCountHeader = document.getElementById('pdfPageCountHeader');
                const contentArea = document.getElementById('pdfContentArea');
                const pageNumberInput = document.getElementById('pageNumberInput');
                const totalPagesDisplay = document.getElementById('totalPagesDisplay');
                const prevPageBtn = document.getElementById('prevPageBtn');
                const nextPageBtn = document.getElementById('nextPageBtn');
                const zoomInBtn = document.getElementById('zoomInBtn');
                const zoomOutBtn = document.getElementById('zoomOutBtn');
                const fitWidthBtn = document.getElementById('fitWidthBtn');
                const zoomLevelDisplay = document.getElementById('zoomLevelDisplay');

                let pdfDoc = null;
                let currentScale = 1.25;
                let baseScale = 1.25;
                const pageRenderTasks = {};

                function updateZoomBadge() {
                    if (zoomLevelDisplay) {
                        const percent = Math.round((currentScale / baseScale) * 100);
                        zoomLevelDisplay.textContent = `${percent}%`;
                    }
                }

                function goToPage(pageNum) {
                    if (!pdfDoc) return;
                    const validNum = Math.max(1, Math.min(pdfDoc.numPages, parseInt(pageNum, 10) || 1));
                    const targetCard = document.getElementById(`pdf-page-card-${validNum}`);
                    if (targetCard) {
                        document.querySelectorAll('.page-thumbnail').forEach(t => t.classList.remove('active'));
                        const matchingThumb = document.getElementById(`thumb-page-${validNum}`);
                        if (matchingThumb) {
                            matchingThumb.classList.add('active');
                            matchingThumb.scrollIntoView({ behavior: 'nearest', block: 'only' });
                        }
                        targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    if (pageNumberInput) pageNumberInput.value = validNum;
                }

                if (typeof pdfjsLib !== 'undefined') {
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    loadPdfDocument();
                } else {
                    if (pdfLoadingState) {
                        pdfLoadingState.innerHTML = '<p style="color: var(--primary-red); font-weight: 500;">PDF viewer library could not be loaded.</p>';
                    }
                }

                async function loadPdfDocument() {
                    try {
                        const loadingTask = pdfjsLib.getDocument(pdfUrl);
                        pdfDoc = await loadingTask.promise;
                        window.loadedPdfDoc = pdfDoc;

                        // If user opened page directly in Acquisition or Application mode, refresh cognitive engine
                        if (typeof window.triggerCognitiveUpdate === 'function') {
                            window.triggerCognitiveUpdate();
                        }

                        if (pdfLoadingState) pdfLoadingState.style.display = 'none';
                        if (pdfPageCountHeader) pdfPageCountHeader.textContent = pdfDoc.numPages;
                        if (totalPagesDisplay) totalPagesDisplay.textContent = pdfDoc.numPages;
                        if (pageNumberInput) {
                            pageNumberInput.value = '1';
                            pageNumberInput.setAttribute('max', pdfDoc.numPages);
                        }

                        if (pdfThumbnailsList) pdfThumbnailsList.innerHTML = '';
                        if (pdfPagesContainer) pdfPagesContainer.innerHTML = '';

                        // Calculate optimal initial scale to match standard browser reading width (~950px - 1050px)
                        try {
                            const firstPage = await pdfDoc.getPage(1);
                            const unscaledViewport = firstPage.getViewport({ scale: 1.0 });
                            const containerWidth = contentArea.clientWidth || window.innerWidth;
                            const targetWidth = Math.min(1050, Math.max(720, containerWidth - 96));
                            currentScale = Math.min(2.0, Math.max(1.45, targetWidth / unscaledViewport.width));
                            baseScale = currentScale;
                        } catch(err) {
                            currentScale = 1.6;
                            baseScale = 1.6;
                        }
                        updateZoomBadge();

                        // Build thumbnail list and main page elements
                        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                            // Sidebar thumbnail
                            const thumbItem = document.createElement('div');
                            thumbItem.className = `page-thumbnail ${pageNum === 1 ? 'active' : ''}`;
                            thumbItem.dataset.page = pageNum;
                            thumbItem.id = `thumb-page-${pageNum}`;
                            thumbItem.innerHTML = `
                                <div class="page-number">Page ${pageNum}</div>
                                <div class="page-preview" id="thumb-preview-${pageNum}">
                                    <canvas id="thumb-canvas-${pageNum}" class="page-thumb-canvas"></canvas>
                                </div>
                            `;
                            thumbItem.addEventListener('click', function() {
                                goToPage(pageNum);
                            });
                            pdfThumbnailsList.appendChild(thumbItem);

                            // Main page card
                            const pageCard = document.createElement('div');
                            pageCard.className = 'pdf-page-card';
                            pageCard.id = `pdf-page-card-${pageNum}`;
                            pageCard.dataset.page = pageNum;
                            pageCard.innerHTML = `
                                <div class="pdf-canvas-wrapper" id="pdf-wrapper-${pageNum}">
                                    <canvas id="page-canvas-${pageNum}" class="pdf-page-canvas"></canvas>
                                </div>
                                <div class="pdf-page-number-badge">${pageNum} / ${pdfDoc.numPages}</div>
                            `;
                            pdfPagesContainer.appendChild(pageCard);
                        }

                        // Render pages and thumbnails
                        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                            renderPage(pageNum);
                            renderThumbnail(pageNum);
                        }

                        setupIntersectionObserver();
                    } catch (error) {
                        console.error('Error loading PDF:', error);
                        if (pdfLoadingState) {
                            pdfLoadingState.innerHTML = `
                                <div style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; color: var(--primary-red); margin-bottom: 1rem;"></i>
                                    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Could not load document preview</h3>
                                    <p style="color: var(--gray-600); margin-bottom: 1.5rem;">You can download or open the original document directly.</p>
                                    <a href="${pdfUrl}" target="_blank" download class="toolbar-btn primary" style="display: inline-flex; text-decoration: none; align-items: center; justify-content: center; gap: 0.5rem;">
                                        <i class="fas fa-download"></i> Download PDF
                                    </a>
                                </div>
                            `;
                        }
                    }
                }

                async function renderPage(pageNum) {
                    try {
                        const page = await pdfDoc.getPage(pageNum);
                        const canvas = document.getElementById(`page-canvas-${pageNum}`);
                        if (!canvas) return;

                        const dpr = window.devicePixelRatio || 1;
                        const viewport = page.getViewport({ scale: currentScale * dpr });
                        const context = canvas.getContext('2d');

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        canvas.style.width = `${viewport.width / dpr}px`;
                        canvas.style.height = `${viewport.height / dpr}px`;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };

                        if (pageRenderTasks[pageNum]) {
                            pageRenderTasks[pageNum].cancel();
                        }

                        const renderTask = page.render(renderContext);
                        pageRenderTasks[pageNum] = renderTask;
                        await renderTask.promise;
                    } catch (e) {
                        if (e.name !== 'RenderingCancelledException') {
                            console.error(`Error rendering page ${pageNum}:`, e);
                        }
                    }
                }

                async function renderThumbnail(pageNum) {
                    try {
                        const page = await pdfDoc.getPage(pageNum);
                        const canvas = document.getElementById(`thumb-canvas-${pageNum}`);
                        if (!canvas) return;

                        const viewport = page.getViewport({ scale: 0.35 });
                        const context = canvas.getContext('2d');

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        await page.render(renderContext).promise;
                    } catch (e) {
                        console.error(`Error rendering thumb ${pageNum}:`, e);
                    }
                }

                function reRenderAllPages() {
                    if (!pdfDoc) return;
                    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                        renderPage(pageNum);
                    }
                }

                function setupIntersectionObserver() {
                    const pageCards = document.querySelectorAll('.pdf-page-card');
                    const thumbnails = document.querySelectorAll('.page-thumbnail');

                    const observerOptions = {
                        root: contentArea,
                        rootMargin: '-15% 0px -70% 0px',
                        threshold: 0
                    };

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const pageNum = entry.target.dataset.page;
                                if (pageNumberInput && document.activeElement !== pageNumberInput) {
                                    pageNumberInput.value = pageNum;
                                }
                                thumbnails.forEach(t => {
                                    if (t.dataset.page === pageNum) {
                                        t.classList.add('active');
                                        t.scrollIntoView({ behavior: 'nearest', block: 'only' });
                                    } else {
                                        t.classList.remove('active');
                                    }
                                });
                            }
                        });
                    }, observerOptions);

                    pageCards.forEach(card => observer.observe(card));
                }

                // Page Number Input Event Listeners
                if (pageNumberInput) {
                    pageNumberInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            goToPage(this.value);
                            this.blur();
                        }
                    });
                    pageNumberInput.addEventListener('blur', function() {
                        goToPage(this.value);
                    });
                }

                // Previous and Next Page Buttons
                if (prevPageBtn) {
                    prevPageBtn.addEventListener('click', () => {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToPage(curr - 1);
                    });
                }

                if (nextPageBtn) {
                    nextPageBtn.addEventListener('click', () => {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToPage(curr + 1);
                    });
                }

                // Zoom controls
                if (zoomInBtn) {
                    zoomInBtn.addEventListener('click', () => {
                        if (currentScale < 3.5) {
                            currentScale = Math.min(3.5, currentScale + 0.25);
                            reRenderAllPages();
                            updateZoomBadge();
                        }
                    });
                }

                if (zoomOutBtn) {
                    zoomOutBtn.addEventListener('click', () => {
                        if (currentScale > 0.6) {
                            currentScale = Math.max(0.6, currentScale - 0.25);
                            reRenderAllPages();
                            updateZoomBadge();
                        }
                    });
                }

                // Fit to Width control
                if (fitWidthBtn) {
                    fitWidthBtn.addEventListener('click', async () => {
                        if (!pdfDoc) return;
                        try {
                            const firstPage = await pdfDoc.getPage(1);
                            const unscaledViewport = firstPage.getViewport({ scale: 1.0 });
                            const containerWidth = contentArea.clientWidth || window.innerWidth;
                            const targetWidth = Math.max(600, containerWidth - 96);
                            currentScale = Math.min(2.5, Math.max(0.8, targetWidth / unscaledViewport.width));
                            reRenderAllPages();
                            updateZoomBadge();
                        } catch(e) {
                            console.error('Fit to width error:', e);
                        }
                    });
                }

                // Download functionality
                const downloadBtn = document.getElementById('downloadDocBtn');
                if (downloadBtn) {
                    downloadBtn.addEventListener('click', () => {
                        const link = document.createElement('a');
                        link.href = pdfUrl;
                        link.download = "{{ $document['title'] ?? 'document' }}.pdf";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                }

                // Print functionality
                const printBtn = document.getElementById('printDocBtn');
                if (printBtn) {
                    printBtn.addEventListener('click', () => {
                        const printIframe = document.createElement('iframe');
                        printIframe.style.position = 'fixed';
                        printIframe.style.right = '0';
                        printIframe.style.bottom = '0';
                        printIframe.style.width = '0';
                        printIframe.style.height = '0';
                        printIframe.style.border = '0';
                        printIframe.src = pdfUrl;
                        document.body.appendChild(printIframe);
                        printIframe.onload = function() {
                            try {
                                printIframe.contentWindow.focus();
                                printIframe.contentWindow.print();
                            } catch(e) {
                                window.print();
                            }
                        };
                    });
                }

                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        history.back();
                    }
                    if (document.activeElement === pageNumberInput) return;

                    if (e.key === 'ArrowDown' || e.key === 'ArrowRight' || e.key === 'PageDown') {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToPage(curr + 1);
                    }
                    if (e.key === 'ArrowUp' || e.key === 'ArrowLeft' || e.key === 'PageUp') {
                        const curr = parseInt(pageNumberInput?.value, 10) || 1;
                        goToPage(curr - 1);
                    }
                });
            });
        </script>
    @endif

    <!-- Three-Mode Switcher Controller & Dynamic Cognitive Scaffolding Script -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        window.docMeta = @json($document ?? []);
        window.docType = '{{ $type ?? "pdf" }}';
        window.preloadedCognitiveData = @json($preloadedCognitiveData ?? null);

        // 1. Reading Mode Switcher Function
        let currentActiveMode = 'original';
        function switchReadingMode(targetMode) {
            const validModes = ['original', 'acquisition', 'application'];
            if (!validModes.includes(targetMode)) targetMode = 'original';
            currentActiveMode = targetMode;

            // Update Tab UI
            const modeTabs = document.querySelectorAll('.mode-tab-btn');
            modeTabs.forEach(btn => {
                const mode = btn.dataset.mode;
                btn.classList.remove('active', 'original-active', 'acquisition-active', 'application-active');
                if (mode === targetMode) {
                    btn.classList.add('active');
                    if (targetMode === 'original') btn.classList.add('original-active');
                    if (targetMode === 'acquisition') btn.classList.add('acquisition-active');
                    if (targetMode === 'application') btn.classList.add('application-active');
                }
            });

            // Update Workspace Visibility
            const workspaces = document.querySelectorAll('.mode-workspace');
            workspaces.forEach(ws => ws.classList.remove('active'));
            
            const targetWorkspace = document.getElementById(`modeWorkspace${targetMode.charAt(0).toUpperCase() + targetMode.slice(1)}`);
            if (targetWorkspace) {
                targetWorkspace.classList.add('active');
            }

            // Bottom toolbar visibility
            const bottomToolbar = document.getElementById('originalModeBottomToolbar');
            if (bottomToolbar) {
                bottomToolbar.style.display = (targetMode === 'original') ? 'flex' : 'none';
            }

            // Persist preference in localStorage & URL params
            localStorage.setItem('digilearn_reading_mode', targetMode);
            const url = new URL(window.location);
            url.searchParams.set('mode', targetMode);
            window.history.replaceState({}, '', url);

            // Trigger dynamic analysis
            renderCognitiveWorkspace(targetMode);
        }

        // 2. Dynamic Document Cognitive Engine (Real-Time Heuristic Synthesizer)
        const DocumentCognitiveEngine = {
            cachedData: null,
            isAnalyzing: false,

            async analyzeDocument() {
                if (this.cachedData) return this.cachedData;
                this.isAnalyzing = true;

                const docTitle = (window.docMeta && window.docMeta.title) ? window.docMeta.title : 'Document';
                const docId = '{{ $document['id'] ?? '' }}';

                // 1. Instant pre-computed database payload check (0ms latency, synthesized by background job!)
                if (window.preloadedCognitiveData && Array.isArray(window.preloadedCognitiveData.sections) && window.preloadedCognitiveData.sections.length > 0) {
                    const parsed = window.preloadedCognitiveData;
                    let techDocs = parsed.techDocs || parsed.tech_rules || null;
                    const localTech = this.extractTechnicalRules(parsed.docTitle || docTitle, parsed.docTitle || docTitle, parsed.sections || []);
                    parsed.techDocs = {
                        conceptBrief: parsed.techDocs?.conceptBrief || parsed.techDocs?.concept_brief || localTech.conceptBrief,
                        formulaRules: (techDocs && Array.isArray(techDocs.formulaRules) && techDocs.formulaRules.length > 0) 
                            ? techDocs.formulaRules 
                            : (techDocs?.formula_rules || localTech.formulaRules),
                        workedExample: parsed.techDocs?.workedExample || parsed.techDocs?.worked_example || localTech.workedExample,
                        practicalTips: (techDocs && Array.isArray(techDocs.practicalTips) && techDocs.practicalTips.length > 0) 
                            ? techDocs.practicalTips 
                            : (techDocs?.practical_tips || localTech.practicalTips),
                        formula: (techDocs && techDocs.formula && techDocs.formula.length > 10) 
                            ? techDocs.formula 
                            : localTech.formula,
                        code: (techDocs && techDocs.code && techDocs.code.length > 20) 
                            ? techDocs.code 
                            : localTech.code,
                        note: (techDocs && techDocs.note) 
                            ? techDocs.note 
                            : localTech.note
                    };
                    if (docId) {
                        try {
                            localStorage.setItem('digilearn_doc_cognitive_' + docId, JSON.stringify(parsed));
                        } catch (e) {}
                    }
                    this.cachedData = parsed;
                    this.isAnalyzing = false;
                    return this.cachedData;
                }

                // 2. Instant local storage cache check (<1ms)
                if (docId) {
                    try {
                        const localCache = localStorage.getItem('digilearn_doc_cognitive_' + docId);
                        if (localCache) {
                            const parsed = JSON.parse(localCache);
                            if (parsed && Array.isArray(parsed.sections) && parsed.sections.length > 0) {
                                // Ensure techDocs is fully populated even with legacy/partial cache
                                let techDocs = parsed.techDocs || parsed.tech_rules || null;
                                const localTech = this.extractTechnicalRules(parsed.docTitle || docTitle, parsed.docTitle || docTitle, parsed.sections || []);
                                parsed.techDocs = {
                                    conceptBrief: parsed.techDocs?.conceptBrief || parsed.techDocs?.concept_brief || localTech.conceptBrief,
                                    formulaRules: (techDocs && Array.isArray(techDocs.formulaRules) && techDocs.formulaRules.length > 0) 
                                        ? techDocs.formulaRules 
                                        : (techDocs?.formula_rules || localTech.formulaRules),
                                    workedExample: parsed.techDocs?.workedExample || parsed.techDocs?.worked_example || localTech.workedExample,
                                    practicalTips: (techDocs && Array.isArray(techDocs.practicalTips) && techDocs.practicalTips.length > 0) 
                                        ? techDocs.practicalTips 
                                        : (techDocs?.practical_tips || localTech.practicalTips),
                                    formula: (techDocs && techDocs.formula && techDocs.formula.length > 10) 
                                        ? techDocs.formula 
                                        : localTech.formula,
                                    code: (techDocs && techDocs.code && techDocs.code.length > 20) 
                                        ? techDocs.code 
                                        : localTech.code,
                                    note: (techDocs && techDocs.note) 
                                        ? techDocs.note 
                                        : localTech.note
                                };
                                this.cachedData = parsed;
                                this.isAnalyzing = false;
                                return this.cachedData;
                            }
                        }
                    } catch (e) {
                        console.warn('Local storage cognitive cache read notice:', e);
                    }
                }

                let rawSections = [];
                let fullText = '';

                // Extract Text from presentation or PDF
                if (window.docType === 'ppt' && window.docMeta && Array.isArray(window.docMeta.slides) && window.docMeta.slides.length > 0) {
                    window.docMeta.slides.forEach((slide, i) => {
                        let paras = [];
                        if (slide.subtitle) paras.push(slide.subtitle);
                        if (Array.isArray(slide.bullets)) paras.push(...slide.bullets);
                        const slideText = (slide.title || '') + ' ' + paras.join(' ');
                        fullText += slideText + '\n';
                    });
                } else if (window.docType === 'pdf' && window.loadedPdfDoc) {
                    try {
                        const pdf = window.loadedPdfDoc;
                        const maxPagesToScan = Math.min(pdf.numPages, 30);
                        const pageTexts = [];

                        for (let p = 1; p <= maxPagesToScan; p++) {
                            const page = await pdf.getPage(p);
                            const textContent = await page.getTextContent();
                            
                            let pageLines = [];
                            let curLine = '';
                            let lastY = null;

                            for (const item of textContent.items) {
                                if (!item.str) continue;
                                if (lastY !== null && Math.abs(item.transform[5] - lastY) > 6) {
                                    if (curLine.trim()) pageLines.push(curLine.trim());
                                    curLine = item.str;
                                } else {
                                    curLine += (curLine ? ' ' : '') + item.str;
                                }
                                lastY = item.transform[5];
                            }
                            if (curLine.trim()) pageLines.push(curLine.trim());
                            
                            const pageJoined = pageLines.join(' ');
                            fullText += ' ' + pageJoined;
                            pageTexts.push({ pageNum: p, lines: pageLines, text: pageJoined });
                        }

                        rawSections = this.groupPdfIntoSections(pageTexts, docTitle);
                    } catch (err) {
                        console.warn('PDF text extraction notice:', err);
                    }
                }

                // 2. Try Backend AI Synthesis (Gemini AI for deep comprehension / DB cached)
                if (docId) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch(`/dashboard/document/${docId}/synthesize`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                extracted_text: fullText,
                                slides: (window.docType === 'ppt' && window.docMeta) ? window.docMeta.slides : []
                            })
                        });

                        if (response.ok) {
                            const resJson = await response.json();
                            if (resJson.success && resJson.data && Array.isArray(resJson.data.sections) && resJson.data.sections.length > 0) {
                                const enhancedSections = resJson.data.sections.map((s, idx) => ({
                                    id: s.id || `sec-${idx + 1}`,
                                    title: this.formatMathText(s.title),
                                    rawTitle: s.title,
                                    prompt: s.inquiry_focus || this.generateInquiryPrompt(s.title),
                                    paragraphs: (Array.isArray(s.paragraphs) ? s.paragraphs : [s.paragraphs]).map(p => this.formatMathText(p)),
                                    has_checkpoint: s.has_checkpoint !== false,
                                    checkpoint: s.checkpoint_prompt || this.generateCheckpointPrompt(s.title)
                                }));

                                let techDocs = resJson.data.tech_rules;
                                const localTech = this.extractTechnicalRules(fullText + ' ' + (techDocs?.formula || ''), docTitle, enhancedSections);
                                techDocs = {
                                    formulaRules: (techDocs && Array.isArray(techDocs.formulaRules) && techDocs.formulaRules.length > 0) 
                                        ? techDocs.formulaRules 
                                        : localTech.formulaRules,
                                    formula: (techDocs && techDocs.formula && techDocs.formula.length > 10) 
                                        ? techDocs.formula 
                                        : localTech.formula,
                                    code: (techDocs && techDocs.code && techDocs.code.length > 20) 
                                        ? techDocs.code 
                                        : localTech.code,
                                    note: (techDocs && techDocs.note) 
                                        ? techDocs.note 
                                        : localTech.note
                                };

                                this.cachedData = {
                                    docTitle: resJson.data.docTitle || docTitle,
                                    vocabulary: (resJson.data.vocabulary && resJson.data.vocabulary.length > 0) ? resJson.data.vocabulary : this.extractVocabulary(fullText, docTitle, enhancedSections),
                                    sections: enhancedSections,
                                    techDocs: techDocs,
                                    checklist: (resJson.data.checklist && resJson.data.checklist.length > 0) ? resJson.data.checklist : [
                                        `Deconstruct core axioms and problem specifications in ${enhancedSections[0]?.rawTitle || 'Module 1'}`,
                                        `Implement mathematical and computational rules from ${enhancedSections[1]?.rawTitle || 'Module 2'}`,
                                        `Verify boundary constraints, limit behaviors, and validation test cases`,
                                        `Compile completed solution into the project blueprint specification`
                                    ]
                                };

                                // Save to localStorage for instant future loads
                                try {
                                    localStorage.setItem('digilearn_doc_cognitive_' + docId, JSON.stringify(this.cachedData));
                                } catch(lsEx) {}

                                this.isAnalyzing = false;
                                return this.cachedData;
                            }
                        }
                    } catch (aiErr) {
                        console.warn('AI Document synthesis notice (falling back to dynamic local parser):', aiErr);
                    }
                }

                // 2. Client-Side Semantic Parser Fallback (No AI)
                if (!rawSections || rawSections.length === 0) {
                    rawSections = [
                        {
                            id: 'sec-1',
                            title: `1. Foundational Overview of ${docTitle}`,
                            rawTitle: `Foundational Overview of ${docTitle}`,
                            paragraphs: [
                                `This module explores the core principles, governing axioms, and foundational concepts introduced in ${docTitle}.`,
                                `Study the relationships between key elements to build an integrated conceptual understanding.`
                            ],
                            rawText: docTitle
                        },
                        {
                            id: 'sec-2',
                            title: `2. Methodologies & Analytical Frameworks`,
                            rawTitle: `Methodologies & Analytical Frameworks`,
                            paragraphs: [
                                `Here we analyze the operations, structural relationships, and applied methodologies presented across the document.`,
                                `Examine the conditions, boundary variables, and transformation rules governing this system.`
                            ],
                            rawText: docTitle
                        },
                        {
                            id: 'sec-3',
                            title: `3. Practical Applications & Synthesis`,
                            rawTitle: `Practical Applications & Synthesis`,
                            paragraphs: [
                                `This section synthesizes the primary findings into concrete problem-solving strategies, case studies, and engineering implementations.`
                            ],
                            rawText: docTitle
                        }
                    ];
                }

                // Extract Vocabulary
                const vocabulary = this.extractVocabulary(fullText, docTitle, rawSections);

                // Enhance Sections Dynamically (No hardcoded 5-step limit)
                const enhancedSections = rawSections.map((sec, idx) => {
                    const cleanTitle = sec.rawTitle || sec.title.replace(/^\d+[\.\s]+/, '');
                    const inquiryPrompt = this.generateInquiryPrompt(cleanTitle);
                    const isIntro = /intro|overview|background|getting started/i.test(cleanTitle) && idx === 0;
                    const checkpoint = this.generateCheckpointPrompt(cleanTitle);

                    return {
                        id: sec.id,
                        title: sec.title,
                        rawTitle: cleanTitle,
                        prompt: inquiryPrompt,
                        paragraphs: sec.paragraphs.slice(0, 4),
                        has_checkpoint: !isIntro,
                        checkpoint: checkpoint
                    };
                });

                // Extract Technical Rules, Formulas & Code
                const techDocs = this.extractTechnicalRules(fullText, docTitle, enhancedSections);

                // Generate Project Blueprint Checklist
                const lowerCheckText = (fullText + ' ' + docTitle).toLowerCase();
                let checklist = [];
                if (/set\s*theory|subset|powerset|cardinality|venn|relation|function|ashlock/i.test(lowerCheckText) || /set/i.test(docTitle)) {
                    checklist = [
                        `Implement Set membership and subset validation algorithms`,
                        `Build union, intersection, and relative complement utilities`,
                        `Generate the power set P(S) with O(2^n) exponential time checks`,
                        `Apply De Morgan's laws for relational database / query logic`
                    ];
                } else if (/indice|power|exponent/i.test(lowerCheckText) || /indice|power/i.test(docTitle)) {
                    checklist = [
                        `Master the basic notation of base and index in expressions like a^n`,
                        `Apply the multiplication law to combine powers with identical bases`,
                        `Utilize the division law and subtraction principle for fractional index terms`,
                        `Evaluate zero, negative, and fractional indices accurately in problem-solving`
                    ];
                } else {
                    checklist = [
                        `Deconstruct problem specifications and core axioms in ${enhancedSections[0]?.rawTitle || 'Module 1'}`,
                        `Implement computational rules and key principles from ${enhancedSections[1]?.rawTitle || 'Module 2'}`,
                        `Verify boundary constraints, limit behaviors, and validation cases`,
                        `Compile completed solution into the project blueprint specification`
                    ];
                }

                this.cachedData = {
                    docTitle: docTitle,
                    vocabulary: vocabulary,
                    sections: enhancedSections,
                    techDocs: techDocs,
                    checklist: checklist
                };

                this.isAnalyzing = false;
                return this.cachedData;
            },

            extractVocabulary(fullText, docTitle, sections) {
                const terms = [];
                const lowerText = (fullText + ' ' + docTitle).toLowerCase();

                // 1. Domain-Specific Curated Glossary Database
                const domainKnowledgeBase = {
                    sets: [
                        { term: 'Set Definition', def: 'A well-defined mathematical collection of distinct objects or elements (e.g. \\( S = \\{x \\mid x > 0\\} \\)).' },
                        { term: 'Subset Relation', def: 'A set A is a subset of B (\\( A \\subseteq B \\)) if and only if every element of A is also in B.' },
                        { term: 'Power Set', def: 'The set of all subsets of S (\\( \\mathcal{P}(S) \\)), having cardinality \\( |\\mathcal{P}(S)| = 2^{|S|} \\).' },
                        { term: 'Union & Intersection', def: 'Union (\\( A \\cup B \\)) pools elements together; Intersection (\\( A \\cap B \\)) contains common elements only.' },
                        { term: "De Morgan's Laws", def: 'The complement of a union is the intersection of complements: \\( (A \\cup B)^c = A^c \\cap B^c \\).' },
                        { term: 'Russell’s Paradox', def: 'The contradiction arising from naive set theory when defining the set of all sets that do not contain themselves.' }
                    ],
                    indices: [
                        { term: 'Base Number', def: 'The core number or variable that is repeatedly multiplied by itself according to the index or exponent.' },
                        { term: 'Index / Exponent', def: 'The superscript power showing how many times the base number is multiplied by itself.' },
                        { term: 'Multiplication Law', def: 'When multiplying powers with identical bases, the exponents are added together (\\( a^m \\times a^n = a^{m+n} \\)).' },
                        { term: 'Negative Exponent', def: 'Represents the reciprocal of that base raised to the positive power (\\( a^{-n} = \\frac{1}{a^n} \\)).' },
                        { term: 'Fractional Power', def: 'Represents root operations where the denominator indicates the root index (\\( a^{1/n} = \\sqrt[n]{a} \\)).' },
                        { term: 'Zero Power Rule', def: 'Any non-zero base raised to the power of zero is mathematically defined as 1 (\\( a^0 = 1 \\)).' }
                    ],
                    physics: [
                        { term: 'Atomic Hypothesis', def: 'The foundational concept that all matter is composed of moving particles that attract and repel one another.' },
                        { term: 'Conservation of Energy', def: 'A fundamental physical law stating that energy cannot be created or destroyed, only transformed.' },
                        { term: 'Kinetic Energy', def: 'The energy possessed by an object due to its motion, proportional to mass and velocity squared.' },
                        { term: 'Potential Energy', def: 'Stored energy within a physical system based on position, configuration, or gravitational state.' },
                        { term: 'Thermodynamics', def: 'The branch of physics that deals with heat, work, temperature, and energy transformations.' }
                    ],
                    computing: [
                        { term: 'Algorithmic Complexity', def: 'The quantitative measurement of computational time and memory required as input sizes grow.' },
                        { term: 'Data Structure', def: 'A specialized format for organizing, processing, and accessing stored data efficiently.' },
                        { term: 'Recursion', def: 'A computational technique where a function solves a problem by calling copies of itself.' },
                        { term: 'Control Flow', def: 'The sequential order in which individual statements and instructions are executed by a processor.' }
                    ],
                    biology: [
                        { term: 'Cellular Homeostasis', def: 'The biological mechanism through which an organism maintains a stable internal environment.' },
                        { term: 'Metabolic Pathway', def: 'A linked series of chemical reactions catalyzed by enzymes inside living cells.' },
                        { term: 'Chemical Equilibrium', def: 'The state in which both reactants and products are present in concentrations with no further tendency to change.' }
                    ]
                };

                // Check domain matches
                let matchedDomainTerms = null;
                if (/set\s*theory|subset|powerset|cardinality|venn|relation|function|injection|surjection|bijection|ashlock/i.test(lowerText) || /set/i.test(docTitle)) {
                    matchedDomainTerms = domainKnowledgeBase.sets;
                } else if (/indice|power|exponent|algebra|logarithm|calculus|polynomial|matrix|equation/i.test(lowerText)) {
                    matchedDomainTerms = domainKnowledgeBase.indices;
                } else if (/physics|feynman|atom|mechanic|quantum|energy|gravity|velocity|force|thermo/i.test(lowerText)) {
                    matchedDomainTerms = domainKnowledgeBase.physics;
                } else if (/algorithm|code|comput|software|program|database|python|javascript|data structure/i.test(lowerText)) {
                    matchedDomainTerms = domainKnowledgeBase.computing;
                } else if (/biology|cell|organism|chem|reaction|ecosystem|molecule|genetics/i.test(lowerText)) {
                    matchedDomainTerms = domainKnowledgeBase.biology;
                }

                if (matchedDomainTerms) {
                    matchedDomainTerms.forEach(item => {
                        if (terms.length < 4) {
                            terms.push(item);
                        }
                    });
                }

                // 2. Strict Stopwords and Junk Phrases Filter
                const invalidPrefixes = /^(suppose|we|let|if|this|that|tell|tells|when|then|consider|here|notice|example|assume|have|had|want|can|will|are|is|for|from|with|and|or|such|what|where|why|how|to|in|on|at|by|an|a|the)\b/i;

                // 3. Dynamic Text Extraction with Strict Validation
                if (terms.length < 4) {
                    sections.forEach(sec => {
                        if (terms.length >= 4) return;
                        const candidate = sec.title.replace(/^\d+[\.\s]*/, '').replace(/Overview|Module|Chapter|Part|Section/gi, '').trim();
                        if (candidate.length > 3 && candidate.length < 32 && !invalidPrefixes.test(candidate)) {
                            if (!terms.some(t => t.term.toLowerCase() === candidate.toLowerCase())) {
                                const samplePara = sec.paragraphs[0] || `Foundational concept governing ${candidate} in this module.`;
                                const cleanDef = samplePara.length > 130 ? samplePara.substring(0, 130) + '...' : samplePara;
                                terms.push({
                                    term: candidate,
                                    def: cleanDef
                                });
                            }
                        }
                    });
                }

                // 4. Default Clean Academic Terms
                const fallbackAcademicTerms = [
                    { term: 'Core Axiom', def: 'The foundational premise or underlying governing law established throughout this topic.' },
                    { term: 'Analytical Model', def: 'A structured theoretical framework connecting core observations to quantitative outcomes.' },
                    { term: 'Boundary Condition', def: 'The parameters and constraints that determine where specific rules and behaviors remain valid.' },
                    { term: 'Applied Synthesis', def: 'The practical integration of theoretical principles to solve practical challenges.' }
                ];

                while (terms.length < 4) {
                    terms.push(fallbackAcademicTerms[terms.length]);
                }

                return terms.slice(0, 4);
            },

            formatMathText(str) {
                if (!str) return '';
                let out = str;
                // Strip trailing TOC page numbers like " ... 3"
                out = out.replace(/\s+\d+$/, '');
                // Clean mathematical patterns into LaTeX
                out = out.replace(/a\s*m\s*[x×*]\s*a\s*n\s*=\s*a\s*m\s*\+\s*n/gi, '\\( a^m \\times a^n = a^{m+n} \\)');
                out = out.replace(/\(\s*a\s*m\s*\)\s*n\s*=\s*a\s*mn/gi, '\\( (a^m)^n = a^{mn} \\)');
                out = out.replace(/a\s*m\s*[\/÷]\s*a\s*n\s*=\s*a\s*m\s*-\s*n/gi, '\\( \\frac{a^m}{a^n} = a^{m-n} \\)');
                out = out.replace(/a\s*0\s*=\s*1/gi, '\\( a^0 = 1 \\)');
                out = out.replace(/a\s*-\s*m\s*=\s*1\s*\/\s*a\s*m/gi, '\\( a^{-m} = \\frac{1}{a^m} \\)');
                out = out.replace(/a\s*1\s*\/\s*n\s*=\s*/gi, '\\( a^{1/n} = \\sqrt[n]{a} \\)');
                return out;
            },

            groupPdfIntoSections(pageTexts, docTitle) {
                const sections = [];
                let currentSection = null;
                let secCounter = 1;
                const headingRegex = /^(?:chapter|unit|section|lesson|part|\d+\.|\b[IVXLCDM]+\.)\s+/i;
                const metaTitlesRegex = /^(?:contents|table of contents|index|appendix|references|bibliography|preface)\b/i;
                const noiseHeadingRegex = /^(?:proof|q\.e\.d|theorem|lemma|corollary|definition|example|exercise|problems|then|therefore|note|remark|solution|case|where|let|and|so|figure|table)\b|^[a-zA-Z0-9,\.:;\-\s]{1,4}$/i;

                for (let pIdx = 0; pIdx < pageTexts.length; pIdx++) {
                    const page = pageTexts[pIdx];
                    const isTOCPage = page.lines.some(l => metaTitlesRegex.test(l.trim())) && pIdx <= 1;

                    for (const line of page.lines) {
                        const trimmed = line.trim();
                        if (!trimmed || trimmed.length < 4) continue;

                        // Skip meta titles and TOC index lines
                        if (metaTitlesRegex.test(trimmed)) continue;
                        if (isTOCPage && (/\b\d+\.\s+.*?\s+\d+$/.test(trimmed) || /^\d+\s*$/.test(trimmed))) continue;

                        const isNoise = noiseHeadingRegex.test(trimmed);
                        const isHeading = !isNoise && (
                            (trimmed.length > 7 && trimmed.length < 65 && headingRegex.test(trimmed) && !trimmed.endsWith(',')) ||
                            (trimmed.length > 8 && trimmed.length < 50 && /^[A-Z][A-Z0-9\s:,\-\(\)\=\^\*\+]{6,50}$/.test(trimmed) && !trimmed.endsWith('.')) ||
                            (trimmed.length > 8 && trimmed.length < 55 && /^[A-Z][a-zA-Z0-9\s:,\-\(\)\=\^\*\+]{6,50}$/.test(trimmed) && !trimmed.endsWith('.'))
                        );

                        if (isHeading && (!currentSection || currentSection.paragraphs.length >= 1)) {
                            if (currentSection && currentSection.paragraphs.length > 0) {
                                sections.push(currentSection);
                                secCounter++;
                            }
                            const cleanHeading = this.formatMathText(trimmed.replace(/^\d+[\.\s]*/, ''));
                            currentSection = {
                                id: `sec-${secCounter}`,
                                title: `${secCounter}. ${cleanHeading}`,
                                rawTitle: cleanHeading,
                                paragraphs: [],
                                rawText: trimmed
                            };
                        } else {
                            if (!currentSection) {
                                currentSection = {
                                    id: `sec-${secCounter}`,
                                    title: `${secCounter}. ${docTitle} Overview`,
                                    rawTitle: `${docTitle} Overview`,
                                    paragraphs: [],
                                    rawText: ''
                                };
                            }
                            if (trimmed.length > 25) {
                                const formattedPara = this.formatMathText(trimmed);
                                currentSection.paragraphs.push(formattedPara);
                                currentSection.rawText += ' ' + trimmed;
                            }
                        }
                    }
                }

                if (currentSection && currentSection.paragraphs.length > 0) {
                    sections.push(currentSection);
                }

                // If fragmented into too many sections (> 7) or too few (< 2), intelligently merge into 4-6 balanced chapters
                if (sections.length > 7 || sections.length < 2) {
                    const targetCount = Math.min(6, Math.max(3, Math.ceil(pageTexts.length / 4)));
                    const chunkSize = Math.ceil(pageTexts.length / targetCount);
                    const balancedSections = [];

                    for (let c = 0; c < targetCount; c++) {
                        const chunkPages = pageTexts.slice(c * chunkSize, (c + 1) * chunkSize);
                        const chunkParas = [];
                        chunkPages.forEach(p => {
                            p.lines.filter(l => l.length > 35 && !noiseHeadingRegex.test(l.trim())).forEach(l => chunkParas.push(this.formatMathText(l)));
                        });

                        const firstValidHeading = sections[c]?.rawTitle || `Core Concepts - Part ${c + 1}`;
                        const titleText = (firstValidHeading.length > 6 && !noiseHeadingRegex.test(firstValidHeading)) ? firstValidHeading : `${docTitle} Part ${c + 1}`;

                        balancedSections.push({
                            id: `sec-${c + 1}`,
                            title: `${c + 1}. ${titleText}`,
                            rawTitle: titleText,
                            paragraphs: chunkParas.slice(0, 3),
                            rawText: chunkParas.join(' ')
                        });
                    }
                    return balancedSections;
                }

                return sections;
            },

            generateInquiryPrompt(title) {
                const lower = (title || '').toLowerCase();
                if (lower.includes('intro') || lower.includes('overview') || lower.includes('getting started')) {
                    return 'What core concepts and foundational principles are introduced in this overview?';
                }
                if (lower.includes('first rule') || lower.includes('second rule') || lower.includes('third rule') || lower.includes('fourth rule') || lower.includes('fifth rule') || lower.includes('rule') || lower.includes('law') || lower.includes('theorem')) {
                    return `Why does this mathematical rule work, and how does it simplify complex calculations?`;
                }
                if (lower.includes('negative') || lower.includes('fractional') || lower.includes('power') || lower.includes('indice') || lower.includes('exponent')) {
                    return `How do negative or fractional exponents translate into standard fractions and root notations?`;
                }
                if (lower.includes('atom') || lower.includes('energy') || lower.includes('physics') || lower.includes('motion')) {
                    return `What fundamental physical laws and experimental observations validate ${title}?`;
                }
                return `What are the essential mechanisms governing ${title}, and why are they critical to understanding this subject?`;
            },

            generateCheckpointPrompt(title) {
                const lower = (title || '').toLowerCase();
                if (lower.includes('intro') || lower.includes('overview')) {
                    return 'Self-Explanation Checkpoint: In your own words, summarize what this topic covers and why these concepts are important.';
                }
                if (lower.includes('rule') || lower.includes('law') || lower.includes('theorem') || lower.includes('power')) {
                    return 'Active Recall Checkpoint: Explain the algebraic logic behind this rule and how to apply it when simplifying identical bases.';
                }
                if (lower.includes('negative') || lower.includes('fractional')) {
                    return 'Active Recall Checkpoint: Explain how you would convert a negative or fractional exponent into a standard fraction or root notation.';
                }
                return `Self-Explanation Checkpoint: In your own words, summarize the core principle and key takeaways of "${title}".`;
            },

            extractTechnicalRules(fullText, docTitle, sections) {
                const lowerText = (fullText + ' ' + docTitle).toLowerCase();
                let conceptBrief = '';
                let formulaRules = [];
                let workedExample = null;
                let practicalTips = [];
                let formulaBlock = '';
                let codeBlock = '';
                let note = '';

                if (/set\s*theory|subset|powerset|cardinality|venn|relation|function|injection|surjection|bijection|ashlock/i.test(lowerText) || /set/i.test(docTitle)) {
                    conceptBrief = `Set theory forms the fundamental language of modern mathematics and computational logic. A set is a well-defined collection of distinct objects, known as elements.

By defining operations such as unions (combining elements), intersections (finding shared elements), complements (excluding elements), and power sets (all possible subsets), set theory allows us to model relationships, build database query logic, design probabilistic sample spaces, and structure complex systems.`;

                    formulaRules = [
                        { name: 'Intersection Rule', latex: 'S \\cap T = \\{x : (x \\in S) \\land (x \\in T)\\}', description: 'Contains only elements belonging simultaneously to both set S and set T.' },
                        { name: 'Union Rule', latex: 'S \\cup T = \\{x : (x \\in S) \\lor (x \\in T)\\}', description: 'Combines all distinct elements from both set S and set T.' },
                        { name: 'Complement Rule', latex: 'S^c = \\{x : (x \\in U) \\land (x \\notin S)\\}', description: 'All elements in the universal set U that do not belong to S.' },
                        { name: 'Set Difference', latex: 'S \\setminus T = \\{x : x \\in S \\land x \\notin T\\}', description: 'Elements in S after subtracting any overlapping members of T.' },
                        { name: 'Subset Criterion', latex: 'A \\subseteq B \\iff (\\forall x, x \\in A \\implies x \\in B)', description: 'Every element of set A is also contained within set B.' },
                        { name: 'Power Set Cardinality', latex: '|\\mathcal{P}(S)| = 2^{|S|}', description: 'The number of all possible subsets for a set with n elements is exactly 2^n.' },
                        { name: "De Morgan's Laws", latex: '(S \\cup T)^c = S^c \\cap T^c', description: 'The complement of a union equals the intersection of the individual complements.' }
                    ];

                    workedExample = {
                        title: 'Evaluating Composite Set Operations',
                        problem: 'Given universal set \\( U = \\{1, 2, 3, 4, 5, 6, 7, 8\\} \\), \\( A = \\{2, 4, 6, 8\\} \\), and \\( B = \\{4, 5, 6, 7\\} \\), find \\( (A \\cup B)^c \\) and \\( A \\setminus B \\).',
                        steps: [
                            'Find the union \\( A \\cup B \\): Combine all unique elements \\( \\{2, 4, 6, 8\\} \\cup \\{4, 5, 6, 7\\} = \\{2, 4, 5, 6, 7, 8\\} \\).',
                            'Compute the complement \\( (A \\cup B)^c \\): Identify elements in \\( U \\) not in the union \\( \\implies \\{1, 3\\} \\).',
                            'Compute the relative difference \\( A \\setminus B \\): Take set \\( A \\) and remove any elements that appear in \\( B \\) (remove 4 and 6) \\( \\implies \\{2, 8\\} \\).'
                        ],
                        solution: '(A \\cup B)^c = \\{1, 3\\} \\quad \\text{and} \\quad A \\setminus B = \\{2, 8\\}'
                    };

                    practicalTips = [
                        'Always verify the scope of the Universal Set U before evaluating complements.',
                        'The empty set ∅ is a subset of every set, and every set is a subset of itself.',
                        'De Morgan\'s laws are essential for simplifying complex boolean logic and database filtering conditions.'
                    ];

                    formulaBlock = formulaRules.map((r, i) => `${i + 1}. ${r.name}: ${r.latex};`).join('\n');
                    codeBlock = `class SetOperations {\n` +
                                `    static union(s, t) { return new Set([...s, ...t]); }\n` +
                                `    static intersection(s, t) { return new Set([...s].filter(x => t.has(x))); }\n` +
                                `    static difference(s, t) { return new Set([...s].filter(x => !t.has(x))); }\n` +
                                `    static isSubset(s, t) { return [...s].every(x => t.has(x)); }\n` +
                                `}`;
                    note = `Always declare the universal set U before evaluating relative complements or testing boundary predicates.`;

                } else if (/physics|feynman|six.easy|mechanic|gravity|gravitation|quantum|energy|kinetic|potential|velocity|force|thermo|particle/i.test(lowerText)) {
                    conceptBrief = `Classical mechanics and modern physics explore the governing rules of matter, force, and energy across space and time.

From Newton's laws of motion to the universal inverse-square law of gravitation, physical interactions are underpinned by foundational conservation laws: total energy and momentum remain constant in closed systems. Developing an intuitive grasp of how potential energy transforms into kinetic work allows us to analyze everything from falling apples to orbital mechanics.`;

                    formulaRules = [
                        { name: 'Inverse Square Law of Gravitation', latex: 'F = G \\frac{m_1 m_2}{r^2}', description: 'Gravitational attraction between two masses drops with the square of distance.' },
                        { name: 'Conservation of Mechanical Energy', latex: 'E_{\\text{total}} = KE + PE = \\text{constant}', description: 'In an isolated conservative system, total mechanical energy is strictly conserved.' },
                        { name: 'Kinetic Energy Formula', latex: 'KE = \\frac{1}{2} m v^2', description: 'Energy possessed by an object due to its motion and velocity.' },
                        { name: 'Gravitational Potential Energy', latex: 'PE = m g h', description: 'Stored energy relative to height in a uniform gravitational field.' },
                        { name: "Newton's Second Law", latex: 'F = m a = \\frac{dp}{dt}', description: 'Force equals mass times acceleration (rate of change of linear momentum).' }
                    ];

                    workedExample = {
                        title: 'Energy Conservation in Gravitational Free Fall',
                        problem: 'A 5 kg mass is released from rest at a height of 45 meters. Calculate its impact velocity (use \\( g = 9.8 \\text{ m/s}^2 \\), ignore air resistance).',
                        steps: [
                            'State conservation law: \\( PE_{\\text{initial}} + KE_{\\text{initial}} = PE_{\\text{final}} + KE_{\\text{final}} \\).',
                            'Calculate initial potential energy: \\( PE_i = m g h = (5)(9.8)(45) = 2205 \\text{ J} \\) with \\( KE_i = 0 \\).',
                            'Equate to final kinetic energy at ground (\\( PE_f = 0 \\)): \\( \\frac{1}{2} m v^2 = 2205 \\implies v = \\sqrt{\\frac{2 \\times 2205}{5}} = \\sqrt{882} \\approx 29.7 \\text{ m/s} \\).'
                        ],
                        solution: 'v = \\sqrt{2gh} \\approx 29.7 \\text{ m/s}'
                    };

                    practicalTips = [
                        'Always ensure dimensional consistency across SI base units (meters, kilograms, seconds, Newtons, Joules).',
                        'Check whether non-conservative forces (like friction or air resistance) do work before assuming strict mechanical energy conservation.',
                        'Vectors like velocity and force require defining a clear directional coordinate system (e.g. upward positive).'
                    ];

                    formulaBlock = formulaRules.map((r, i) => `${i + 1}. ${r.name}: ${r.latex};`).join('\n');
                    codeBlock = `class PhysicsMechanics {\n` +
                                `    static gravitationalForce(m1, m2, r, G = 6.6743e-11) { return G * (m1 * m2) / Math.pow(r, 2); }\n` +
                                `    static kineticEnergy(m, v) { return 0.5 * m * Math.pow(v, 2); }\n` +
                                `    static potentialEnergy(m, h, g = 9.8) { return m * g * h; }\n` +
                                `}`;
                    note = `Ensure correct unit conversion between kilometers and meters when computing gravitational interactions.`;

                } else if (/indice|power|exponent|algebra|root|logarithm/i.test(lowerText) || /indice|power/i.test(docTitle)) {
                    conceptBrief = `Indices (also called exponents or powers) provide a compact algebraic shorthand for repeated multiplication. Instead of writing long chains like \\( 2 \\times 2 \\times 2 \\times 2 \\), we write \\( 2^4 \\).

Understanding the fundamental rules of indices allows us to simplify complex algebraic expressions, scale calculations easily, manage extremely large or small numbers in scientific notation, and model exponential growth and decay in finance, biology, and computer science.`;

                    formulaRules = [
                        { name: 'Multiplication Rule', latex: 'a^m \\times a^n = a^{m+n}', description: 'When multiplying terms with the same base, add their exponents.' },
                        { name: 'Division Rule', latex: '\\frac{a^m}{a^n} = a^{m-n}', description: 'When dividing terms with the same base, subtract the denominator exponent.' },
                        { name: 'Power of a Power Rule', latex: '(a^m)^n = a^{m \\cdot n}', description: 'When raising a power to another power, multiply the exponents.' },
                        { name: 'Zero Exponent Rule', latex: 'a^0 = 1 \\quad (a \\neq 0)', description: 'Any non-zero quantity raised to the power of zero equals 1.' },
                        { name: 'Negative Exponent Rule', latex: 'a^{-m} = \\frac{1}{a^m}', description: 'A negative exponent represents the reciprocal of the positive power.' },
                        { name: 'Fractional Exponent Rule', latex: 'a^{1/n} = \\sqrt[n]{a}', description: 'A unit fraction exponent represents taking the n-th root.' },
                        { name: 'Composite Fractional Rule', latex: 'a^{m/n} = (\\sqrt[n]{a})^m = \\sqrt[n]{a^m}', description: 'Take the root first (denominator), then raise to the power (numerator).' }
                    ];

                    workedExample = {
                        title: 'Simplifying a Rational Exponent Expression',
                        problem: 'Simplify the algebraic expression: \\( \\frac{(4x^3 y^2)^2 \\cdot x^{-2} y}{2 x^4 y^3} \\)',
                        steps: [
                            'Distribute the outer power across numerator factors: \\( (4x^3 y^2)^2 = 4^2 x^{3 \\cdot 2} y^{2 \\cdot 2} = 16 x^6 y^4 \\).',
                            'Combine like bases in numerator using multiplication rule: \\( 16 x^6 y^4 \\cdot x^{-2} y^1 = 16 x^{6 + (-2)} y^{4 + 1} = 16 x^4 y^5 \\).',
                            'Divide by denominator using division rule: \\( \\frac{16 x^4 y^5}{2 x^4 y^3} = \\left(\\frac{16}{2}\\right) x^{4 - 4} y^{5 - 3} = 8 x^0 y^2 = 8(1)y^2 = 8y^2 \\).'
                        ],
                        solution: '8y^2'
                    };

                    practicalTips = [
                        'Index laws ONLY apply when the base is identical (e.g. 2^3 · 3^2 cannot be combined into 6^5).',
                        'Distinguish between negative bases and negative exponents: (-2)^4 = 16 (positive), but 2^(-4) = 1/16 (positive fraction).',
                        'For composite fractional powers like 27^(2/3), take the root first: (³√27)^2 = 3^2 = 9 for easier mental arithmetic.'
                    ];

                    formulaBlock = formulaRules.map((r, i) => `${i + 1}. ${r.name}: ${r.latex};`).join('\n');
                    codeBlock = `class IndexAlgebra {\n` +
                                `    static multiply(base, m, n) { return Math.pow(base, m + n); }\n` +
                                `    static divide(base, m, n) { return Math.pow(base, m - n); }\n` +
                                `    static powerOfPower(base, m, n) { return Math.pow(base, m * n); }\n` +
                                `    static negativePower(base, n) { return 1 / Math.pow(base, n); }\n` +
                                `    static nthRoot(base, n) { return Math.pow(base, 1 / n); }\n` +
                                `}`;
                    note = `Base a cannot be zero when dealing with negative exponents or denominators in division.`;

                } else {
                    // General / Dynamic Synthesis for Any Document
                    const sec1 = sections[0]?.rawTitle || sections[0]?.title || 'Core Foundations';
                    const sec2 = sections[1]?.rawTitle || sections[1]?.title || 'Operational Methods';
                    const sec3 = sections[2]?.rawTitle || sections[2]?.title || 'Advanced Synthesis';

                    conceptBrief = `This guide breaks down the core concepts and operational frameworks introduced in "${docTitle}".

By establishing foundational principles in ${sec1}, analyzing transformation rules in ${sec2}, and synthesizing application patterns in ${sec3}, you can systematically apply these concepts to real-world problem solving and blueprint implementation.`;

                    formulaRules = [
                        { name: 'Foundational Baseline', latex: `\\text{Model}(x) \\to ${sec1.replace(/[^a-zA-Z0-9\s]/g, '')}`, description: 'Baseline axioms and initial parameter conditions.' },
                        { name: 'Operational Rule', latex: `\\Delta S \\ge 0 \\implies \\text{Invariant}`, description: 'Governing constraint and process rules.' },
                        { name: 'Equilibrium Condition', latex: `\\sum \\text{Factors} = \\text{Target}`, description: 'Verification balance across system components.' }
                    ];

                    workedExample = {
                        title: `Applied Problem Walkthrough for ${docTitle}`,
                        problem: `Analyze a primary scenario governed by ${sec1} and determine the optimal outcome according to ${sec2}.`,
                        steps: [
                            `Step 1: Identify key baseline inputs and initial constraints from ${sec1}.`,
                            `Step 2: Apply the governing transformation rules outlined in ${sec2}.`,
                            `Step 3: Verify boundary limits, edge cases, and cross-validate against ${sec3}.`
                        ],
                        solution: `Validated blueprint specification for ${docTitle}`
                    };

                    practicalTips = [
                        `Always verify input boundary conditions before executing core transformation steps.`,
                        `Cross-check results against initial axioms to avoid cumulative calculation drift.`,
                        `Document key assumptions clearly when transferring findings to your project blueprint.`
                    ];

                    formulaBlock = `// Key Structural Framework for ${docTitle}\n1. ${sec1}: Baseline Verification\n2. ${sec2}: Operational Transformation\n3. ${sec3}: Verification & Synthesis`;
                    codeBlock = `// Implementation logic for ${docTitle}\nfunction executeAnalysis(inputs) {\n    return inputs.filter(item => item !== null);\n}`;
                    note = `Handle parameter boundaries and edge conditions carefully when applying ${docTitle} rules.`;
                }

                return {
                    conceptBrief: conceptBrief,
                    formulaRules: formulaRules,
                    workedExample: workedExample,
                    practicalTips: practicalTips,
                    formula: formulaBlock,
                    code: codeBlock,
                    note: note
                };
            }
        };

        // 3. Render Cognitive Workspaces dynamically
        async function renderCognitiveWorkspace(targetMode) {
            if (targetMode === 'original') return;

            const data = await DocumentCognitiveEngine.analyzeDocument();

            // Populate Acquisition Mode
            if (targetMode === 'acquisition') {
                const glossaryGrid = document.getElementById('acquisitionGlossaryGrid');
                const sectionsContainer = document.getElementById('acquisitionSectionsContainer');
                const outlineList = document.getElementById('acquisitionOutlineList');

                if (glossaryGrid && data.vocabulary) {
                    glossaryGrid.innerHTML = data.vocabulary.map(item => `
                        <div class="glossary-card">
                            <div class="glossary-term"><i class="fas fa-bookmark" style="font-size: 0.75rem; margin-right: 0.35rem;"></i>${item.term}</div>
                            <div class="glossary-definition">${item.def}</div>
                        </div>
                    `).join('');
                }

                if (outlineList && data.sections) {
                    outlineList.innerHTML = data.sections.map((sec, idx) => `
                        <a href="#${sec.id}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; text-decoration: none; color: var(--text-main); font-size: 0.8125rem; font-weight: 500; background: var(--bg-surface); border: 1px solid var(--border-color); transition: all 0.2s;">
                            <i class="fas fa-check-circle" style="color: var(--secondary-blue, #2677B8); font-size: 0.75rem;"></i>
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${sec.title}</span>
                        </a>
                    `).join('');
                }

                if (sectionsContainer && data.sections) {
                    sectionsContainer.innerHTML = data.sections.map((sec, idx) => {
                        const inquiryPrompt = sec.prompt || DocumentCognitiveEngine.generateInquiryPrompt(sec.rawTitle || sec.title);
                        const showCheckpoint = sec.has_checkpoint !== false;
                        const checkpointText = sec.checkpoint || DocumentCognitiveEngine.generateCheckpointPrompt(sec.rawTitle || sec.title);

                        return `
                            <div class="acquisition-section-card" id="${sec.id}">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0;">${sec.title}</h3>
                                    <span style="font-size: 0.6875rem; font-weight: 700; color: var(--secondary-blue, #2677B8); background: var(--bg-app); border: 1px solid var(--border-color); padding: 0.2rem 0.6rem; border-radius: 9999px;">Step ${idx + 1} of ${data.sections.length}</span>
                                </div>
                                
                                <div class="inquiry-focus-box">
                                    <div class="inquiry-icon-badge">
                                        <i class="fas fa-lightbulb"></i>
                                    </div>
                                    <div>
                                        <div class="inquiry-label">Inquiry Focus</div>
                                        <div class="inquiry-question">${inquiryPrompt}</div>
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 1rem; color: var(--text-main); line-height: 1.8; font-size: 0.9375rem;">
                                    ${sec.paragraphs.map(p => `<p style="margin: 0;">${p}</p>`).join('')}
                                </div>

                                ${showCheckpoint ? `
                                <!-- Retrieval Checkpoint Box (SQ3R Recite Step) -->
                                <div class="retrieval-checkpoint-box">
                                    <div class="checkpoint-title">
                                        <i class="fas fa-brain"></i>
                                        <span>Active Recall Checkpoint</span>
                                    </div>
                                    <p class="checkpoint-prompt-text" style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 0.5rem;">${checkpointText}</p>
                                    <textarea class="checkpoint-input" id="recall-input-${idx}" placeholder="Type your self-explanation here to test your working memory..."></textarea>
                                    <button class="checkpoint-submit-btn" id="recall-btn-${idx}" onclick="submitRecallCheck(${idx})">
                                        <i class="fas fa-check" style="margin-right: 0.25rem;"></i> Check Understanding
                                    </button>
                                    <div id="recall-feedback-${idx}" style="display: none; margin-top: 0.75rem;"></div>
                                </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('');
                }
            }

            // Populate Application Mode
            if (targetMode === 'application') {
                const docsContainer = document.getElementById('applicationDocsContainer');
                const checklistContainer = document.getElementById('applicationChecklistContainer');
                const scratchpad = document.getElementById('projectScratchpad');
                const projectGoalInput = document.getElementById('projectGoalInput');

                if (projectGoalInput && !projectGoalInput.dataset.modified) {
                    projectGoalInput.value = `Implementation Plan for ${data.docTitle}`;
                    projectGoalInput.className = 'target-goal-input';
                }

                if (docsContainer) {
                    if (!data.techDocs || !data.techDocs.conceptBrief || !data.techDocs.workedExample) {
                        const localTech = DocumentCognitiveEngine.extractTechnicalRules(data.docTitle || '', data.docTitle || '', data.sections || []);
                        data.techDocs = {
                            conceptBrief: data.techDocs?.conceptBrief || data.techDocs?.concept_brief || localTech.conceptBrief,
                            formulaRules: (data.techDocs && Array.isArray(data.techDocs.formulaRules) && data.techDocs.formulaRules.length > 0) ? data.techDocs.formulaRules : (data.techDocs?.formula_rules || localTech.formulaRules),
                            workedExample: data.techDocs?.workedExample || data.techDocs?.worked_example || localTech.workedExample,
                            practicalTips: (data.techDocs && Array.isArray(data.techDocs.practicalTips) && data.techDocs.practicalTips.length > 0) ? data.techDocs.practicalTips : (data.techDocs?.practical_tips || localTech.practicalTips),
                            formula: (data.techDocs && data.techDocs.formula && data.techDocs.formula.length > 10) ? data.techDocs.formula : localTech.formula,
                            code: (data.techDocs && data.techDocs.code && data.techDocs.code.length > 20) ? data.techDocs.code : localTech.code,
                            note: (data.techDocs && data.techDocs.note) ? data.techDocs.note : localTech.note
                        };
                    }

                    const t = data.techDocs;
                    const conceptBriefParagraphs = t.conceptBrief ? t.conceptBrief.split('\n\n').filter(p => p.trim().length > 0) : [];
                    const hasFormulaCards = t.formulaRules && t.formulaRules.length > 0;
                    const hasWorkedExample = t.workedExample && t.workedExample.problem;
                    const hasTips = t.practicalTips && t.practicalTips.length > 0;

                    docsContainer.innerHTML = `
                        <!-- 1. Conceptual Overview & Mental Model -->
                        ${conceptBriefParagraphs.length > 0 ? `
                        <div class="app-doc-card">
                            <h4 class="app-card-title">
                                <i class="fas fa-book-open" style="color: var(--secondary-blue, #38BDF8);"></i> Conceptual Overview & Mental Model
                            </h4>
                            <div class="app-prose">
                                ${conceptBriefParagraphs.map(p => `<p>${p}</p>`).join('')}
                            </div>
                        </div>
                        ` : ''}

                        <!-- 2. Core Principles & Governing Rules Matrix -->
                        ${hasFormulaCards ? `
                        <div class="app-doc-card">
                            <h4 class="app-card-title">
                                <i class="fas fa-layer-group" style="color: var(--secondary-blue, #38BDF8);"></i> Core Principles & Governing Rules
                            </h4>
                            <div class="app-rule-grid">
                                ${t.formulaRules.map(r => `
                                    <div class="app-rule-card">
                                        <div class="app-rule-name">${r.name}</div>
                                        <div class="app-rule-math">\\( ${r.latex || r.rule || ''} \\)</div>
                                        ${r.description ? `<div class="app-rule-desc">${r.description}</div>` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}

                        <!-- 3. Step-by-Step Worked Walkthrough -->
                        ${hasWorkedExample ? `
                        <div class="app-doc-card">
                            <h4 class="app-card-title">
                                <i class="fas fa-graduation-cap" style="color: var(--secondary-blue, #38BDF8);"></i> ${t.workedExample.title || 'Step-by-Step Worked Application'}
                            </h4>
                            <div class="app-worked-box">
                                <div class="app-problem-label"><i class="fas fa-question-circle" style="margin-right: 0.3rem;"></i>Challenge Statement</div>
                                <div class="app-problem-statement">${t.workedExample.problem}</div>
                                
                                ${Array.isArray(t.workedExample.steps) && t.workedExample.steps.length > 0 ? `
                                <div class="app-step-list">
                                    ${t.workedExample.steps.map((step, sIdx) => `
                                        <div class="app-step-item">
                                            <div class="app-step-num">${sIdx + 1}</div>
                                            <div>${DocumentCognitiveEngine.formatMathText(step)}</div>
                                        </div>
                                    `).join('')}
                                </div>
                                ` : ''}

                                ${t.workedExample.solution ? `
                                <div class="app-solution-pill">
                                    <i class="fas fa-check-circle"></i>
                                    <span class="solution-content">Result: \\( ${t.workedExample.solution.replace(/^\\\(|\\\)$/g, '').trim()} \\)</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}

                        <!-- 4. Key Insights & Common Pitfalls -->
                        ${hasTips ? `
                        <div class="app-doc-card">
                            <h4 class="app-card-title">
                                <i class="fas fa-lightbulb" style="color: #D97706;"></i> Key Insights & Common Pitfalls
                            </h4>
                            <div class="app-tips-container">
                                ${t.practicalTips.map(tip => `
                                    <div class="app-tip-row">
                                        <i class="fas fa-chevron-circle-right"></i>
                                        <span>${tip}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}

                        <!-- 5. Practical Implementation / Method Reference -->
                        ${t.code && t.code.length > 20 ? `
                        <div class="app-doc-card">
                            <h4 class="app-card-title">
                                <i class="fas fa-code" style="color: var(--secondary-blue, #38BDF8);"></i> Practical Implementation Reference
                            </h4>
                            <div class="app-snippet-card">
                                <button class="app-copy-btn" onclick="copySnippet(this)">Copy Code</button>
                                <pre style="margin: 0; white-space: pre-wrap; font-family: inherit;">${t.code}</pre>
                            </div>
                            ${t.note ? `<p class="app-doc-note">${t.note}</p>` : ''}
                        </div>
                        ` : ''}
                    `;
                }

                if (checklistContainer && data.checklist) {
                    const savedChecklist = JSON.parse(localStorage.getItem('digilearn_app_checklist_{{ $document['id'] ?? 'doc' }}') || '[]');
                    checklistContainer.innerHTML = data.checklist.map((item, idx) => {
                        const isChecked = savedChecklist.includes(idx);
                        return `
                            <label class="checklist-item ${isChecked ? 'is-completed' : ''}" id="checklist-row-${idx}">
                                <input type="checkbox" onchange="updateChecklistProgress()" data-idx="${idx}" ${isChecked ? 'checked' : ''}>
                                <span>${item}</span>
                            </label>
                        `;
                    }).join('');
                    updateChecklistProgress();
                }
                if (scratchpad) {
                    const savedNotes = localStorage.getItem('digilearn_scratchpad_{{ $document['id'] ?? 'doc' }}');
                    if (savedNotes) {
                        scratchpad.value = savedNotes;
                    }
                    scratchpad.addEventListener('input', function() {
                        localStorage.setItem('digilearn_scratchpad_{{ $document['id'] ?? 'doc' }}', this.value);
                    });
                }
            }

            // Math Rendering Call via KaTeX / MathLive
            setTimeout(() => {
                const mainPane = document.getElementById('acquisitionMainPane');
                const appPane = document.getElementById('applicationDocsContainer');
                if (window.renderMathInContainer) {
                    if (mainPane) window.renderMathInContainer(mainPane);
                    if (appPane) window.renderMathInContainer(appPane);
                }
            }, 60);
        }

        // Global trigger called after PDF or document loads
        window.triggerCognitiveUpdate = function() {
            DocumentCognitiveEngine.cachedData = null;
            if (currentActiveMode !== 'original') {
                renderCognitiveWorkspace(currentActiveMode);
            }
        };

        // Active Recall — freeCodeCamp-style progressive hint system
        let answeredRecallChecks = 0;
        const recallAttempts = {};  // Track attempts per checkpoint index
        const totalCheckpoints = () => document.querySelectorAll('.retrieval-checkpoint-box').length || 3;

        async function submitRecallCheck(index) {
            const input = document.getElementById(`recall-input-${index}`);
            const feedback = document.getElementById(`recall-feedback-${index}`);
            const btn = document.getElementById(`recall-btn-${index}`);
            const box = input ? input.closest('.retrieval-checkpoint-box') : null;

            if (!input || input.value.trim().length < 3) {
                alert('Please type a brief explanation or summary before submitting.');
                return;
            }

            // Track attempt count for this specific checkpoint
            if (!recallAttempts[index]) recallAttempts[index] = 0;
            recallAttempts[index]++;
            const attempt = recallAttempts[index];

            const answer = input.value.trim();
            const questionEl = box ? box.querySelector('.checkpoint-prompt-text') : null;
            const sectionEl = box ? box.closest('.acquisition-section-card')?.querySelector('h3') : null;
            const question = questionEl ? questionEl.innerText.trim() : 'Active Recall Checkpoint';
            const sectionTitle = sectionEl ? sectionEl.innerText.trim() : '';
            const docTitle = document.querySelector('.document-title')?.innerText || document.title || 'Document';

            // Show loading state
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:0.25rem;"></i> Checking...';
            }
            input.disabled = true;

            if (feedback) {
                feedback.style.display = 'block';
                feedback.innerHTML = `
                    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.6rem 0.75rem;border-radius:0.5rem;background:var(--bg-surface);border:1px solid var(--border-color);">
                        <i class="fas fa-spinner fa-spin" style="color:var(--secondary-blue,#38BDF8);font-size:0.8rem;"></i>
                        <span style="font-size:0.8rem;color:var(--text-muted);">Checking your understanding...</span>
                    </div>`;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/dashboard/document/evaluate-recall', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question: question,
                        answer: answer,
                        attempt: attempt,
                        doc_title: docTitle,
                        section_title: sectionTitle
                    })
                });

                const resJson = await response.json();

                if (resJson.data) {
                    const d = resJson.data;
                    const isCorrect = d.is_correct === true;
                    const message = d.message || '';

                    if (isCorrect) {
                        // ✅ CORRECT — show encouragement, lock the checkpoint
                        if (feedback) {
                            feedback.innerHTML = `
                                <div style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.75rem;border-radius:0.5rem;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.2);">
                                    <i class="fas fa-check-circle" style="color:#059669;font-size:1.1rem;margin-top:0.1rem;flex-shrink:0;"></i>
                                    <div>
                                        <div style="font-weight:700;font-size:0.8125rem;color:#059669;margin-bottom:0.2rem;">You got it!</div>
                                        <p style="font-size:0.8rem;color:var(--text-main);line-height:1.55;margin:0;">${message}</p>
                                    </div>
                                </div>`;
                        }
                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-check" style="margin-right:0.25rem;"></i> Completed';
                            btn.disabled = true;
                            btn.style.opacity = '0.6';
                        }
                        input.disabled = true;

                        // Update mastery progress
                        answeredRecallChecks = Math.min(totalCheckpoints(), answeredRecallChecks + 1);
                        const mastery = Math.round((answeredRecallChecks / totalCheckpoints()) * 100);
                        const progressBar = document.getElementById('acquisitionProgressBar');
                        const progressScore = document.getElementById('acquisitionProgressScore');
                        if (progressBar) progressBar.style.width = `${Math.min(mastery, 100)}%`;
                        if (progressScore) progressScore.innerText = `${Math.min(mastery, 100)}%`;

                    } else {
                        // ❌ INCORRECT — show progressive hint
                        let icon, iconColor, headerText, bgColor, borderColor;

                        if (attempt <= 1) {
                            icon = 'fa-lightbulb'; iconColor = '#D97706'; headerText = 'Not quite — here\'s a hint';
                            bgColor = 'rgba(217,119,6,0.06)'; borderColor = 'rgba(217,119,6,0.2)';
                        } else if (attempt === 2) {
                            icon = 'fa-search'; iconColor = '#2677B8'; headerText = 'Getting closer — think about this';
                            bgColor = 'rgba(38,119,184,0.06)'; borderColor = 'rgba(38,119,184,0.2)';
                        } else {
                            icon = 'fa-book-open'; iconColor = '#7C3AED'; headerText = 'Here\'s how it works';
                            bgColor = 'rgba(124,58,237,0.06)'; borderColor = 'rgba(124,58,237,0.2)';
                        }

                        if (feedback) {
                            feedback.innerHTML = `
                                <div style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.75rem;border-radius:0.5rem;background:${bgColor};border:1px solid ${borderColor};">
                                    <i class="fas ${icon}" style="color:${iconColor};font-size:1.1rem;margin-top:0.1rem;flex-shrink:0;"></i>
                                    <div>
                                        <div style="font-weight:700;font-size:0.8125rem;color:${iconColor};margin-bottom:0.2rem;">${headerText}</div>
                                        <p style="font-size:0.8rem;color:var(--text-main);line-height:1.55;margin:0;">${message}</p>
                                    </div>
                                </div>`;
                        }

                        // Re-enable for retry
                        input.disabled = false;
                        input.focus();
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-redo" style="margin-right:0.25rem;"></i> Try Again';
                        }

                        // After 3+ attempts, also progress mastery
                        if (attempt >= 3) {
                            answeredRecallChecks = Math.min(totalCheckpoints(), answeredRecallChecks + 1);
                            const mastery = Math.round((answeredRecallChecks / totalCheckpoints()) * 100);
                            const progressBar = document.getElementById('acquisitionProgressBar');
                            const progressScore = document.getElementById('acquisitionProgressScore');
                            if (progressBar) progressBar.style.width = `${Math.min(mastery, 100)}%`;
                            if (progressScore) progressScore.innerText = `${Math.min(mastery, 100)}%`;
                        }
                    }
                } else {
                    throw new Error(resJson.error || 'Evaluation response error');
                }

            } catch (err) {
                console.warn('Recall evaluation notice:', err);
                
                // Intelligent client fallback
                const words = answer.split(/\s+/).filter(w => w.length > 0).length;
                const isAcceptable = words >= 8;
                
                if (feedback) {
                    if (isAcceptable) {
                        feedback.innerHTML = `
                            <div style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.75rem;border-radius:0.5rem;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.2);">
                                <i class="fas fa-check-circle" style="color:#059669;font-size:1.1rem;margin-top:0.1rem;flex-shrink:0;"></i>
                                <div>
                                    <div style="font-weight:700;font-size:0.8125rem;color:#059669;margin-bottom:0.2rem;">Good response!</div>
                                    <p style="font-size:0.8rem;color:var(--text-main);line-height:1.55;margin:0;">Your explanation touches on the key aspects of this section. Review the module text above to ensure complete mastery.</p>
                                </div>
                            </div>`;
                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-check" style="margin-right:0.25rem;"></i> Completed';
                            btn.disabled = true;
                            btn.style.opacity = '0.6';
                        }
                        input.disabled = true;
                        answeredRecallChecks = Math.min(totalCheckpoints(), answeredRecallChecks + 1);
                        const mastery = Math.round((answeredRecallChecks / totalCheckpoints()) * 100);
                        const progressBar = document.getElementById('acquisitionProgressBar');
                        const progressScore = document.getElementById('acquisitionProgressScore');
                        if (progressBar) progressBar.style.width = `${Math.min(mastery, 100)}%`;
                        if (progressScore) progressScore.innerText = `${Math.min(mastery, 100)}%`;
                    } else {
                        feedback.innerHTML = `
                            <div style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.75rem;border-radius:0.5rem;background:rgba(217,119,6,0.06);border:1px solid rgba(217,119,6,0.2);">
                                <i class="fas fa-lightbulb" style="color:#D97706;font-size:1.1rem;margin-top:0.1rem;flex-shrink:0;"></i>
                                <div>
                                    <div style="font-weight:700;font-size:0.8125rem;color:#D97706;margin-bottom:0.2rem;">Not quite — here's a hint</div>
                                    <p style="font-size:0.8rem;color:var(--text-main);line-height:1.55;margin:0;">Try elaborating a bit more on how this specific concept or formula is applied in this section.</p>
                                </div>
                            </div>`;
                        input.disabled = false;
                        input.focus();
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-redo" style="margin-right:0.25rem;"></i> Try Again';
                        }
                    }
                }
            }
        }

        function updateChecklistProgress() {
            const checkboxes = document.querySelectorAll('#applicationChecklistContainer input[type="checkbox"]');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            const progressText = document.getElementById('checklistProgressText');
            if (progressText) {
                progressText.innerText = `${checked.length}/${checkboxes.length} Done`;
            }
            checkboxes.forEach(cb => {
                const parentRow = cb.closest('.checklist-item');
                if (parentRow) {
                    parentRow.classList.toggle('is-completed', cb.checked);
                }
            });
            const checkedIndices = Array.from(checked).map(cb => parseInt(cb.dataset.idx));
            localStorage.setItem('digilearn_app_checklist_{{ $document['id'] ?? 'doc' }}', JSON.stringify(checkedIndices));
        }

        function copySnippet(btn) {
            const card = btn.closest('.app-snippet-card');
            const pre = card ? card.querySelector('pre') : null;
            const code = pre ? pre.innerText : '';
            if (!code) return;
            navigator.clipboard.writeText(code).then(() => {
                const originalText = btn.innerText;
                btn.innerText = 'Copied!';
                setTimeout(() => btn.innerText = originalText, 1800);
            });
        }

        // Export Project Blueprint as Markdown (.md)
        function exportProjectBlueprint() {
            const goal = document.getElementById('projectGoalInput')?.value || 'Project Blueprint';
            const notes = document.getElementById('projectScratchpad')?.value || 'No notes added.';
            const checkboxes = document.querySelectorAll('#applicationChecklistContainer input[type="checkbox"]');
            
            let checklistMd = '';
            checkboxes.forEach((cb, idx) => {
                const text = cb.nextElementSibling?.innerText || `Task ${idx + 1}`;
                checklistMd += `- [${cb.checked ? 'x' : ' '}] ${text}\n`;
            });

            const content = `# Project Blueprint: ${goal}\n\n` +
                            `**Source Document:** {{ addslashes($document['title'] ?? 'Document') }}\n` +
                            `**Date:** ${new Date().toLocaleDateString()}\n\n` +
                            `## Implementation Checklist\n\n${checklistMd}\n\n` +
                            `## Project Solution Notes & Implementation\n\n${notes}\n`;

            const blob = new Blob([content], { type: 'text/markdown;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${goal.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-blueprint.md`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        window.toggleViewerSidebar = function() {
            const sidebar = document.querySelector('.sidebar') || document.querySelector('.acquisition-sidebar') || document.getElementById('acquisitionSidebar');
            if (sidebar) {
                const isHidden = window.getComputedStyle(sidebar).display === 'none';
                sidebar.style.display = isHidden ? 'flex' : 'none';
            }
        };

        // Initialize mode on page load from URL or localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const initialMode = urlParams.get('mode') || localStorage.getItem('digilearn_reading_mode') || 'original';
            if (initialMode !== 'original') {
                switchReadingMode(initialMode);
            }
        });
    </script>
</body>
</html>