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
    
    <!-- MathLive for Mathematical Typography -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
    <script defer src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script type="module" nonce="{{ request()->attributes->get('csp_nonce') }}">
        import { renderMathInElement } from "https://unpkg.com/mathlive?module";
        window.renderMathInElement = renderMathInElement;
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

        /* =============================================
           THREE-MODE READING SWITCHER & WORKSPACES
           ============================================= */
        .mode-switcher-bar {
            display: flex;
            align-items: center;
            background: rgba(243, 244, 246, 0.85);
            border: 1px solid var(--gray-200);
            padding: 3px;
            border-radius: 9999px;
            gap: 2px;
            margin-left: auto;
            backdrop-filter: blur(8px);
        }

        .mode-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 0.95rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray-600);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            text-decoration: none;
        }

        .mode-tab-btn:hover:not(.active) {
            color: var(--gray-900);
            background: rgba(255, 255, 255, 0.7);
        }

        .mode-tab-btn.active {
            background: var(--white);
            color: var(--secondary-blue, #2677B8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .mode-tab-btn.active.acquisition-active {
            color: var(--secondary-blue, #2677B8);
        }

        .mode-tab-btn.active.application-active {
            color: #059669;
        }

        .mode-sub-badge {
            font-size: 0.625rem;
            padding: 0.12rem 0.45rem;
            border-radius: 9999px;
            background: var(--gray-200);
            color: var(--gray-700);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .mode-tab-btn.active.acquisition-active .mode-sub-badge {
            background: #e0f2fe;
            color: var(--secondary-blue, #2677B8);
        }

        .mode-tab-btn.active.application-active .mode-sub-badge {
            background: #d1fae5;
            color: #059669;
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
            border-right: 1px solid var(--gray-200);
            height: 100%;
            overflow-y: auto;
            padding: 2rem;
            background: var(--white);
        }

        .app-right-pane {
            height: 100%;
            overflow-y: auto;
            padding: 2rem;
            background: var(--gray-50);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .project-builder-panel {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .builder-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.125rem;
            font-weight: 700;
            color: #065f46;
            margin-bottom: 1rem;
        }

        .checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-100);
            font-size: 0.875rem;
            color: var(--gray-800);
        }

        .checklist-item input[type="checkbox"] {
            margin-top: 0.25rem;
            accent-color: #059669;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .builder-scratchpad {
            width: 100%;
            min-height: 240px;
            border: 1px solid var(--gray-300);
            border-radius: 0.75rem;
            padding: 1rem;
            font-family: inherit;
            font-size: 0.875rem;
            line-height: 1.6;
            background: var(--white);
            box-sizing: border-box;
            resize: vertical;
            margin-top: 0.75rem;
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
            <button class="mode-tab-btn active" data-mode="original" id="modeBtnOriginal" onclick="switchReadingMode('original')">
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
                            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.3; margin: 0;">{{ $document['title'] ?? 'Document Guide' }}</h2>
                            <span class="sq3r-badge" style="font-size: 0.65rem; font-weight: 800; background: #f1f5f9; color: #475569; padding: 0.2rem 0.6rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #e2e8f0; white-space: nowrap;">SQ3R Cognitive Framework</span>
                        </div>
                        <p style="font-size: 0.875rem; color: #475569; line-height: 1.6; margin-top: 0.6rem; margin-bottom: 1.25rem;">This active reading mode systematically guides your working memory through vocabulary pre-teaching, structured concept breakdowns, and real-time active recall checkpoints.</p>
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
                            <span style="font-size: 1.0625rem; font-weight: 800; color: #0f172a;">Pre-Reading Vocabulary & Glossary Matrix</span>
                        </div>
                        <p style="font-size: 0.8125rem; color: var(--gray-500); margin-bottom: 1.25rem;">Mastering these core technical terms beforehand frees up cognitive working memory for deep concept synthesis.</p>
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
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                    <div>
                        <h3 style="font-size: 1.0625rem; font-weight: 800; color: #0f172a;">Reference Documentation</h3>
                        <p style="font-size: 0.8125rem; color: var(--gray-500); margin-top: 0.2rem;">Just-In-Time formulas, code blocks, and implementation guides.</p>
                    </div>
                    <span style="background: #e0f2fe; color: #0284c7; font-size: 0.625rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; letter-spacing: 0.05em; border: 1px solid #bae6fd; text-transform: uppercase;">JITL Framework</span>
                </div>
                <div id="applicationDocsContainer">
                    <!-- Dynamic code snippets, formulas, and rule blocks -->
                </div>
            </div>

            <!-- Right Pane: Project Blueprint & Action Workspace -->
            <div class="app-right-pane">
                <!-- Project Goal & Specification -->
                <div class="project-builder-panel">
                    <div class="builder-heading" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                        <span style="font-size: 1.25rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-drafting-compass" style="color: var(--secondary-blue, #2677B8);"></i> Project Blueprint
                        </span>
                        <button class="export-blueprint-btn" id="exportBlueprintBtn" onclick="exportProjectBlueprint()" style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.85rem; border-radius: 0.5rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fas fa-download"></i> Export Blueprint (.md)
                        </button>
                    </div>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.6875rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Target Project / Objective</label>
                        <input type="text" id="projectGoalInput" placeholder="e.g. Building an interactive quiz app / Calculating physics simulation..." 
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-size: 0.875rem; box-sizing: border-box; background: var(--white); color: var(--gray-800);"
                               value="Implementation Plan for {{ $document['title'] ?? 'Document' }}">
                    </div>

                    <!-- Implementation Checklist -->
                    <div style="margin-top: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.8125rem; font-weight: 700; color: #334155;">Actionable Implementation Steps</span>
                            <span id="checklistProgressText" style="font-size: 0.6875rem; font-weight: 700; background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 0.15rem 0.5rem; border-radius: 4px;">0/4 Done</span>
                        </div>
                        <div id="applicationChecklistContainer">
                            <!-- Populated with interactive checkboxes -->
                        </div>
                    </div>

                    <!-- Live Notes & Code Scratchpad -->
                    <div style="margin-top: 1.5rem;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="fas fa-edit" style="color: var(--secondary-blue, #2677B8);"></i> Project Notes & Solution Scratchpad <span style="font-weight: 400; color: var(--gray-500); font-size: 0.75rem;">(Auto-saved)</span>
                        </label>
                        <textarea class="builder-scratchpad" id="projectScratchpad" placeholder="Draft your implementation notes, code snippets, database schemas, or solution design here..." style="width: 100%; min-height: 200px; border: 1px solid #cbd5e1; border-radius: 0.75rem; padding: 1rem; font-family: inherit; font-size: 0.875rem; line-height: 1.6; background: var(--white); box-sizing: border-box; resize: vertical;"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Toolbar (Only for Original Mode) -->
    <div class="bottom-toolbar" id="originalModeBottomToolbar">
        <button class="toolbar-btn" id="printDocBtn" title="Print Document">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
        </button>
        <button class="toolbar-btn" id="downloadDocBtn" title="Download Document">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <path d="m12 18-4-4h3V9h2v5h3l-4 4z"/>
            </svg>
        </button>
        <button class="toolbar-btn" id="zoomInBtn" title="Zoom In">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
                <line x1="11" y1="8" x2="11" y2="14"/>
            </svg>
        </button>
        <button class="toolbar-btn" id="zoomOutBtn" title="Zoom Out">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
        </button>
    </div>

    <!-- Scripts -->
    @if($type === 'ppt')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', function() {
                const slideThumbnails = document.querySelectorAll('.slide-thumbnail-vertical');
                const slideCards = document.querySelectorAll('.ppt-slide-card');
                const contentArea = document.getElementById('pptContentArea');
                
                slideThumbnails.forEach(thumbnail => {
                    thumbnail.addEventListener('click', function() {
                        const slideNumber = this.dataset.slide;
                        const targetCard = document.getElementById(`slide-${slideNumber}`);
                        if (targetCard) {
                            // Update active thumbnail
                            slideThumbnails.forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                            
                            targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                });

                // Intersection Observer to highlight current slide in sidebar on scroll
                const observerOptions = {
                    root: contentArea,
                    rootMargin: '-10% 0px -80% 0px',
                    threshold: 0
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const slideNumber = entry.target.dataset.slide;
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
                    
                    // Arrow key navigation
                    if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                        const activeThumbnail = document.querySelector('.slide-thumbnail-vertical.active');
                        const nextThumbnail = activeThumbnail?.nextElementSibling;
                        if (nextThumbnail && nextThumbnail.classList.contains('slide-thumbnail-vertical')) {
                            nextThumbnail.click();
                        }
                    }
                    
                    if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                        const activeThumbnail = document.querySelector('.slide-thumbnail-vertical.active');
                        const prevThumbnail = activeThumbnail?.previousElementSibling;
                        if (prevThumbnail && prevThumbnail.classList.contains('slide-thumbnail-vertical')) {
                            prevThumbnail.click();
                        }
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

                let pdfDoc = null;
                let currentScale = 1.25;
                const pageRenderTasks = {};

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

                        if (pdfThumbnailsList) pdfThumbnailsList.innerHTML = '';
                        if (pdfPagesContainer) pdfPagesContainer.innerHTML = '';

                        // Calculate optimal initial scale to match standard browser reading width (~950px - 1050px)
                        try {
                            const firstPage = await pdfDoc.getPage(1);
                            const unscaledViewport = firstPage.getViewport({ scale: 1.0 });
                            const containerWidth = contentArea.clientWidth || window.innerWidth;
                            const targetWidth = Math.min(1050, Math.max(720, containerWidth - 96));
                            currentScale = Math.min(2.0, Math.max(1.45, targetWidth / unscaledViewport.width));
                        } catch(err) {
                            currentScale = 1.6;
                        }

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
                                const targetCard = document.getElementById(`pdf-page-card-${pageNum}`);
                                if (targetCard) {
                                    document.querySelectorAll('.page-thumbnail').forEach(t => t.classList.remove('active'));
                                    thumbItem.classList.add('active');
                                    targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
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

                // Zoom controls
                const zoomInBtn = document.getElementById('zoomInBtn');
                const zoomOutBtn = document.getElementById('zoomOutBtn');

                if (zoomInBtn) {
                    zoomInBtn.addEventListener('click', () => {
                        if (currentScale < 3.5) {
                            currentScale = Math.min(3.5, currentScale + 0.25);
                            reRenderAllPages();
                        }
                    });
                }

                if (zoomOutBtn) {
                    zoomOutBtn.addEventListener('click', () => {
                        if (currentScale > 0.6) {
                            currentScale = Math.max(0.6, currentScale - 0.25);
                            reRenderAllPages();
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
                    if (e.key === 'ArrowDown' || e.key === 'PageDown') {
                        const activeThumbnail = document.querySelector('.page-thumbnail.active');
                        const nextThumbnail = activeThumbnail?.nextElementSibling;
                        if (nextThumbnail && nextThumbnail.classList.contains('page-thumbnail')) {
                            nextThumbnail.click();
                        }
                    }
                    if (e.key === 'ArrowUp' || e.key === 'PageUp') {
                        const activeThumbnail = document.querySelector('.page-thumbnail.active');
                        const prevThumbnail = activeThumbnail?.previousElementSibling;
                        if (prevThumbnail && prevThumbnail.classList.contains('page-thumbnail')) {
                            prevThumbnail.click();
                        }
                    }
                });
            });
        </script>
    @endif

    <!-- Three-Mode Switcher Controller & Dynamic Cognitive Scaffolding Script -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        window.docMeta = @json($document ?? []);
        window.docType = '{{ $type ?? "pdf" }}';

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
                btn.classList.remove('active', 'acquisition-active', 'application-active');
                if (mode === targetMode) {
                    btn.classList.add('active');
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

                let rawSections = [];
                let fullText = '';
                const docTitle = (window.docMeta && window.docMeta.title) ? window.docMeta.title : 'Document';

                // Case A: PPT / Presentation Document
                if (window.docType === 'ppt' && window.docMeta && Array.isArray(window.docMeta.slides) && window.docMeta.slides.length > 0) {
                    rawSections = window.docMeta.slides.map((slide, i) => {
                        let paras = [];
                        if (slide.subtitle) paras.push(slide.subtitle);
                        if (Array.isArray(slide.bullets) && slide.bullets.length > 0) {
                            paras.push(...slide.bullets);
                        } else if (Array.isArray(slide.content)) {
                            slide.content.forEach(c => { if (c.text) paras.push(c.text); });
                        } else if (typeof slide.content === 'string' && slide.content.trim()) {
                            paras.push(slide.content);
                        }
                        
                        if (paras.length === 0) {
                            paras.push(`Detailed theoretical concepts and breakdown for slide ${slide.number || (i + 1)}.`);
                        }

                        const slideText = (slide.title || '') + ' ' + paras.join(' ');
                        fullText += ' ' + slideText;

                        return {
                            id: `sec-${i + 1}`,
                            title: `${i + 1}. ${slide.title || 'Module ' + (i + 1)}`,
                            paragraphs: paras,
                            rawText: slideText
                        };
                    });
                }
                // Case B: PDF Document with PDF.js Text Layer
                else if (window.docType === 'pdf' && window.loadedPdfDoc) {
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

                // Clean fallback if no raw text extracted
                if (!rawSections || rawSections.length === 0) {
                    rawSections = [
                        {
                            id: 'sec-1',
                            title: `1. Foundational Overview of ${docTitle}`,
                            paragraphs: [
                                `This module explores the core principles, governing axioms, and foundational concepts introduced in ${docTitle}.`,
                                `Study the relationships between key elements to build an integrated conceptual understanding.`
                            ],
                            rawText: docTitle
                        },
                        {
                            id: 'sec-2',
                            title: `2. Methodologies & Analytical Frameworks`,
                            paragraphs: [
                                `Here we analyze the operations, structural relationships, and applied methodologies presented across the document.`,
                                `Examine the conditions, boundary variables, and transformation rules governing this system.`
                            ],
                            rawText: docTitle
                        },
                        {
                            id: 'sec-3',
                            title: `3. Practical Applications & Synthesis`,
                            paragraphs: [
                                `This section synthesizes the primary findings into concrete problem-solving strategies, case studies, and engineering implementations.`
                            ],
                            rawText: docTitle
                        }
                    ];
                }

                // Extract Vocabulary
                const vocabulary = this.extractVocabulary(fullText, docTitle, rawSections);

                // Enhance Sections with Inquiry & Checkpoints
                const enhancedSections = rawSections.slice(0, 5).map((sec, idx) => {
                    const cleanTitle = sec.rawTitle || sec.title.replace(/^\d+[\.\s]+/, '');
                    const inquiryPrompt = this.generateInquiryPrompt(cleanTitle);
                    const checkpoint = this.generateCheckpointPrompt(cleanTitle);

                    return {
                        id: sec.id,
                        title: sec.title,
                        rawTitle: cleanTitle,
                        prompt: inquiryPrompt,
                        paragraphs: sec.paragraphs.slice(0, 4),
                        checkpoint: checkpoint
                    };
                });

                // Extract Technical Rules, Formulas & Code
                const techDocs = this.extractTechnicalRules(fullText, docTitle, enhancedSections);

                // Generate Project Blueprint Checklist
                const checklist = [
                    `Deconstruct problem specifications and core axioms in ${enhancedSections[0]?.title.replace(/^\d+[\.\s]+/, '') || 'Module 1'}`,
                    `Implement computational rules and key principles from ${enhancedSections[1]?.title.replace(/^\d+[\.\s]+/, '') || 'Module 2'}`,
                    `Verify boundary constraints, limit behaviors, and validation cases`,
                    `Compile completed solution into the project blueprint specification`
                ];

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
                if (/indice|power|exponent|algebra|logarithm|calculus|polynomial|matrix|equation/i.test(lowerText)) {
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

                for (let pIdx = 0; pIdx < pageTexts.length; pIdx++) {
                    const page = pageTexts[pIdx];
                    // Check if this page is purely a Table of Contents
                    const isTOCPage = page.lines.some(l => metaTitlesRegex.test(l.trim())) && pIdx <= 1;

                    for (const line of page.lines) {
                        const trimmed = line.trim();
                        if (!trimmed || trimmed.length < 3) continue;

                        // Skip meta titles
                        if (metaTitlesRegex.test(trimmed)) continue;

                        // Skip lines that look like TOC dot leaders/page index on early pages
                        if (isTOCPage && (/\b\d+\.\s+.*?\s+\d+$/.test(trimmed) || /^\d+\s*$/.test(trimmed))) {
                            continue;
                        }

                        const isHeading = (
                            (trimmed.length < 65 && headingRegex.test(trimmed) && !trimmed.endsWith(',')) ||
                            (trimmed.length < 45 && /^[A-Z0-9\s:,\-\(\)\=\^\*\+]{4,45}$/.test(trimmed) && !trimmed.endsWith('.')) ||
                            (trimmed.length < 50 && /^[A-Z][a-zA-Z0-9\s:,\-\(\)\=\^\*\+]{3,45}$/.test(trimmed) && !trimmed.endsWith('.'))
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
                            if (trimmed.length > 30) {
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

                // If no clean headings were split, divide pages into cohesive sections
                if (sections.length < 2 && pageTexts.length > 0) {
                    const numChunks = Math.min(4, Math.max(2, Math.ceil(pageTexts.length / 4)));
                    const chunkSize = Math.ceil(pageTexts.length / numChunks);
                    const chunkedSections = [];

                    for (let c = 0; c < numChunks; c++) {
                        const chunkPages = pageTexts.slice(c * chunkSize, (c + 1) * chunkSize);
                        const chunkParas = [];
                        chunkPages.forEach(p => {
                            p.lines.filter(l => l.length > 40).forEach(l => chunkParas.push(this.formatMathText(l)));
                        });

                        const firstLine = chunkPages[0]?.lines[0] || `Module Part ${c + 1}`;
                        const titleText = (firstLine.length < 50 && firstLine.length > 4) ? this.formatMathText(firstLine) : `Module Part ${c + 1}: Foundational Concepts`;

                        chunkedSections.push({
                            id: `sec-${c + 1}`,
                            title: `${c + 1}. ${titleText}`,
                            rawTitle: titleText,
                            paragraphs: chunkParas.slice(0, 3),
                            rawText: chunkParas.join(' ')
                        });
                    }
                    return chunkedSections;
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
                const mathMatches = fullText.match(/[a-zA-Z0-9_\(\)\s]+\s*=\s*[a-zA-Z0-9_\+\-\*\/\^\(\)\s\.\,\\]{3,40}/g) || [];
                
                let formulaBlock = '';
                if (mathMatches.length > 0) {
                    formulaBlock = mathMatches.slice(0, 3).map((m, i) => `// Rule ${i + 1}: Equation\n${m.trim()};`).join('\n\n');
                } else {
                    const sec1 = sections[0]?.rawTitle || 'Primary Baseline';
                    const sec2 = sections[1]?.rawTitle || 'Transformation Model';
                    formulaBlock = `// Key Structural Rules for ${docTitle}\n` +
                                   `1. ${sec1}: Constant baseline verification\n` +
                                   `2. ${sec2}: Parameter bounds & constraints\n` +
                                   `3. System Rule: Equilibrium & functional invariance`;
                }

                let codeBlock = '';
                const codeMatches = fullText.match(/(?:function|def|class|SELECT|for|while)\s+[a-zA-Z0-9_]+\s*\([^\)]*\)\s*\{[^}]*\}/g);
                if (codeMatches && codeMatches.length > 0) {
                    codeBlock = codeMatches[0];
                } else {
                    const funcName = docTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_').substring(0, 20) || 'execute';
                    codeBlock = `function process_${funcName}(inputs, config = {}) {\n` +
                                `    // Validate boundary constraints\n` +
                                `    if (!inputs || inputs.length === 0) return null;\n` +
                                `    \n` +
                                `    // Execute core transformation\n` +
                                `    return inputs.map(item => item * (config.factor || 1.0));\n` +
                                `}`;
                }

                return {
                    formula: formulaBlock,
                    code: codeBlock,
                    note: `When implementing these rules in a computational context, it's crucial to handle edge cases, particularly when dealing with boundary parameters for ${docTitle}.`
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
                        <a href="#${sec.id}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; text-decoration: none; color: var(--gray-700); font-size: 0.8125rem; font-weight: 500; background: var(--gray-50); border: 1px solid var(--gray-200); transition: all 0.2s;">
                            <i class="fas fa-check-circle" style="color: var(--secondary-blue, #2677B8); font-size: 0.75rem;"></i>
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${sec.title}</span>
                        </a>
                    `).join('');
                }

                if (sectionsContainer && data.sections) {
                    sectionsContainer.innerHTML = data.sections.map((sec, idx) => {
                        const inquiryPrompt = DocumentCognitiveEngine.generateInquiryPrompt(sec.rawTitle || sec.title);
                        const checkpoint = DocumentCognitiveEngine.generateCheckpointPrompt(sec.rawTitle || sec.title);

                        return `
                            <div class="acquisition-section-card" id="${sec.id}">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">${sec.title}</h3>
                                    <span style="font-size: 0.6875rem; font-weight: 700; color: var(--secondary-blue, #2677B8); background: #eff6ff; border: 1px solid #bfdbfe; padding: 0.2rem 0.6rem; border-radius: 9999px;">Step ${idx + 1} of ${data.sections.length}</span>
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

                                <div style="display: flex; flex-direction: column; gap: 1rem; color: #334155; line-height: 1.8; font-size: 0.9375rem;">
                                    ${sec.paragraphs.map(p => `<p style="margin: 0;">${p}</p>`).join('')}
                                </div>

                                <!-- Retrieval Checkpoint Box (SQ3R Recite Step) -->
                                <div class="retrieval-checkpoint-box">
                                    <div class="checkpoint-title">
                                        <i class="fas fa-brain"></i>
                                        <span>Active Recall Checkpoint</span>
                                    </div>
                                    <p style="font-size: 0.8125rem; color: var(--gray-600); margin-bottom: 0.5rem;">${checkpoint}</p>
                                    <textarea class="checkpoint-input" id="recall-input-${idx}" placeholder="Type your self-explanation here to test your working memory..."></textarea>
                                    <button class="checkpoint-submit-btn" onclick="submitRecallCheck(${idx})">
                                        <i class="fas fa-check" style="margin-right: 0.25rem;"></i> Check Understanding
                                    </button>
                                    <div id="recall-feedback-${idx}" style="display: none; margin-top: 0.75rem; font-size: 0.8125rem; color: #047857; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i> Great explanation! Concept verified and added to mastery score.
                                    </div>
                                </div>
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
                }

                if (docsContainer && data.techDocs) {
                    docsContainer.innerHTML = `
                        <div style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <h4 style="font-size: 0.9375rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-th" style="color: var(--secondary-blue, #2677B8);"></i> Core Formula / Rule Matrix
                            </h4>
                            <div class="app-snippet-card" style="background: #0f172a; color: #e2e8f0; border-radius: 0.5rem; padding: 1.1rem; position: relative; font-family: monospace; font-size: 0.8125rem; line-height: 1.6;">
                                <button class="app-copy-btn" style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(255,255,255,0.15); border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.6875rem; cursor: pointer;" onclick="copySnippet(this, ${JSON.stringify(data.techDocs.formula)})">Copy</button>
                                <pre style="margin: 0; white-space: pre-wrap; font-family: inherit;">${data.techDocs.formula}</pre>
                            </div>
                        </div>

                        <div style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <h4 style="font-size: 0.9375rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-code" style="color: var(--secondary-blue, #2677B8);"></i> Code Implementation Reference (JS/PHP)
                            </h4>
                            <div class="app-snippet-card" style="background: #0f172a; color: #e2e8f0; border-radius: 0.5rem; padding: 1.1rem; position: relative; font-family: monospace; font-size: 0.8125rem; line-height: 1.6;">
                                <button class="app-copy-btn" style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(255,255,255,0.15); border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.6875rem; cursor: pointer;" onclick="copySnippet(this, ${JSON.stringify(data.techDocs.code)})">Copy</button>
                                <pre style="margin: 0; white-space: pre-wrap; font-family: inherit;">${data.techDocs.code}</pre>
                            </div>
                        </div>

                        <p style="font-size: 0.875rem; color: #475569; line-height: 1.6; margin-top: 1rem;">
                            ${data.techDocs.note}
                        </p>
                    `;
                }

                if (checklistContainer && data.checklist) {
                    const savedChecklist = JSON.parse(localStorage.getItem('digilearn_app_checklist_{{ $document['id'] ?? 'doc' }}') || '[]');
                    checklistContainer.innerHTML = data.checklist.map((item, idx) => {
                        const isChecked = savedChecklist.includes(idx);
                        return `
                            <label class="checklist-item" style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.65rem 0; border-bottom: 1px solid #f1f5f9; cursor: pointer;">
                                <input type="checkbox" onchange="updateChecklistProgress()" data-idx="${idx}" ${isChecked ? 'checked' : ''} style="margin-top: 0.25rem; accent-color: var(--secondary-blue, #2677B8); width: 16px; height: 16px; cursor: pointer;">
                                <span style="font-size: 0.875rem; color: ${isChecked ? '#64748b' : '#334155'}; font-weight: ${isChecked ? '500' : '600'}; ${isChecked ? 'text-decoration: line-through;' : ''}">${item}</span>
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

            // MathLive Render Call
            if (typeof window.renderMathInElement === 'function') {
                setTimeout(() => {
                    const mainPane = document.getElementById('acquisitionMainPane');
                    if (mainPane) window.renderMathInElement(mainPane);
                    const appPane = document.getElementById('applicationDocsContainer');
                    if (appPane) window.renderMathInElement(appPane);
                }, 80);
            }
        }

        // Global trigger called after PDF or document loads
        window.triggerCognitiveUpdate = function() {
            DocumentCognitiveEngine.cachedData = null;
            if (currentActiveMode !== 'original') {
                renderCognitiveWorkspace(currentActiveMode);
            }
        };

        // Active Recall submission score counter
        let answeredRecallChecks = 0;
        function submitRecallCheck(index) {
            const input = document.getElementById(`recall-input-${index}`);
            const feedback = document.getElementById(`recall-feedback-${index}`);
            if (input && input.value.trim().length > 3) {
                if (feedback) feedback.style.display = 'block';
                input.disabled = true;
                answeredRecallChecks = Math.min(3, answeredRecallChecks + 1);
                const score = Math.round((answeredRecallChecks / 3) * 100);
                const progressBar = document.getElementById('acquisitionProgressBar');
                const progressScore = document.getElementById('acquisitionProgressScore');
                if (progressBar) progressBar.style.width = `${score}%`;
                if (progressScore) progressScore.innerText = `${score}%`;
            } else {
                alert('Please type a brief explanation or summary before submitting.');
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
                const labelText = cb.nextElementSibling;
                if (labelText) {
                    if (cb.checked) {
                        labelText.style.textDecoration = 'line-through';
                        labelText.style.color = '#64748b';
                    } else {
                        labelText.style.textDecoration = 'none';
                        labelText.style.color = '#334155';
                    }
                }
            });
            const checkedIndices = Array.from(checked).map(cb => parseInt(cb.dataset.idx));
            localStorage.setItem('digilearn_app_checklist_{{ $document['id'] ?? 'doc' }}', JSON.stringify(checkedIndices));
        }

        function copySnippet(btn, code) {
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