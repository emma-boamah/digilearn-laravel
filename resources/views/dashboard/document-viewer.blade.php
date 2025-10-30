<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($document) && is_array($document) && count($document) === 1 ? $document[0]['title'] : (isset($document['title']) ? $document['title'] : 'Documents') }} - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            min-height: 100vh;
        }

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

        /* Navigation Bar */
        .nav-bar {
            background-color: var(--gray-200);
            padding: 1rem 1.5rem;
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

        /* Main Content */
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 140px);
            padding: 2rem;
        }

        .documents-grid {
            max-width: 1200px;
            width: 100%;
            padding: 2rem;
        }

        .documents-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .documents-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .documents-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .document-card-small {
            background-color: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: all 0.2s ease;
        }

        .document-card-small:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .document-icon-small {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
            font-weight: 700;
        }

        .document-icon-small.pdf {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }

        .document-icon-small.ppt {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .document-title-small {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .document-meta-small {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .action-button-small {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            width: 100%;
        }

        .action-button-small.pdf {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .action-button-small.pdf:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-button-small.ppt {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .action-button-small.ppt:hover {
            background-color: #1e5a8a;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Single Document Card */
        .document-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 3rem 2rem;
            box-shadow: var(--shadow-lg);
            text-align: center;
            max-width: 450px;
            width: 100%;
        }

        .document-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--white);
            font-weight: 700;
            position: relative;
            overflow: hidden;
        }

        .document-icon.pdf {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }

        .document-icon.ppt {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .document-icon::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 30px;
            height: 30px;
            background-color: rgba(255, 255, 255, 0.2);
            clip-path: polygon(100% 0, 0 0, 100% 100%);
        }

        .document-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .document-subtitle {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .document-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            flex-wrap: wrap;
        }

        .file-type {
            color: var(--gray-600);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .read-only-badge {
            background-color: var(--gray-100);
            color: var(--gray-700);
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .instructor-info {
            background-color: var(--gray-50);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }

        .instructor-label {
            color: var(--gray-500);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .instructor-name {
            color: var(--gray-900);
            font-weight: 600;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            min-width: 180px;
        }

        .action-button.pdf {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .action-button.pdf:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-button.ppt {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .action-button.ppt:hover {
            background-color: #1e5a8a;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-button svg {
            width: 18px;
            height: 18px;
        }

        /* File Stats */
        .file-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.75rem;
        }

        .file-stat {
            text-align: center;
        }

        .file-stat-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .file-stat-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
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

            .main-content {
                padding: 1rem;
                min-height: calc(100vh - 120px);
            }

            .document-card {
                padding: 2rem 1.5rem;
            }

            .document-icon {
                width: 100px;
                height: 100px;
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .document-title {
                font-size: 1.125rem;
            }

            .action-button {
                padding: 0.875rem 2rem;
                font-size: 0.8125rem;
                min-width: 160px;
            }

            .file-stats {
                gap: 1rem;
            }

            .document-meta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .documents-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .header-left .logo-text {
                display: none;
            }

            .document-card {
                padding: 1.5rem 1rem;
            }

            .document-icon {
                width: 80px;
                height: 80px;
                font-size: 1.5rem;
            }

            .action-button {
                width: 100%;
                min-width: auto;
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
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="nav-bar">
        <button class="back-button" onclick="history.back()">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @php
            // Normalize $document into an array of documents
            $documents = is_array($document)
                ? (isset($document[0]) ? $document : [$document])  // wrap single assoc doc into array
                : [];
            $documentCount = count($documents);
        @endphp

        @if($documentCount === 0)
            <!-- No documents found -->
            <div class="document-card">
                <div class="document-icon {{ $type }}">
                    {{ strtoupper($type) }}
                </div>
                <h1 class="document-title">No Documents Found</h1>
                <p class="document-subtitle">{{ $lesson['subject'] ?? 'Subject' }} • {{ $lesson['year'] ?? '2025' }}</p>
                <p>No {{ strtoupper($type) }} documents are available for this lesson.</p>
            </div>
        @elseif($documentCount === 1)
            <!-- Single document - card design -->
            @php $singleDoc = $documents[0]; @endphp
            <div class="document-card">
                <div class="document-icon {{ $type }}">
                    {{ strtoupper($type) }}
                </div>

                <h1 class="document-title">{{ $singleDoc['title'] ?? 'Document Title' }}</h1>
                <p class="document-subtitle">{{ $lesson['subject'] ?? 'Subject' }} • {{ $lesson['year'] ?? '2025' }}</p>

                <div class="instructor-info">
                    <div class="instructor-label">Attached by</div>
                    <div class="instructor-name">{{ $singleDoc['attached_by'] ?? $lesson['instructor'] ?? 'Instructor' }}</div>
                </div>

                <div class="document-meta">
                    <div class="file-type">
                        <i class="fas fa-file-{{ $type === 'pdf' ? 'pdf' : 'powerpoint' }}"></i>
                        {{ $singleDoc['file_type'] ?? strtoupper($type) }}
                    </div>
                    <div class="read-only-badge">
                        <i class="fas fa-eye"></i> View Only
                    </div>
                </div>

                @if(isset($singleDoc['pages']) || isset($singleDoc['slides']))
                <div class="file-stats">
                    @if($type === 'pdf' && isset($singleDoc['pages']))
                        <div class="file-stat">
                            <div class="file-stat-value">{{ $singleDoc['pages'] }}</div>
                            <div class="file-stat-label">Pages</div>
                        </div>
                    @elseif($type === 'ppt' && isset($singleDoc['slides']))
                        <div class="file-stat">
                            <div class="file-stat-value">{{ $singleDoc['slides'] }}</div>
                            <div class="file-stat-label">Slides</div>
                        </div>
                    @endif
                    <div class="file-stat">
                        <div class="file-stat-value">{{ $singleDoc['file_size'] ?? 'N/A' }}</div>
                        <div class="file-stat-label">File Size</div>
                    </div>
                </div>
                @endif

                <a href="{{ route('dashboard.lesson.document.content', [
                    'lessonId' => $lesson['id'],
                    'type' => $type,
                    'docId' => $singleDoc['id'] ?? 1
                ]) }}" class="action-button {{ $type }}">
                    <i class="fas fa-eye"></i> View {{ $type === 'pdf' ? 'Document' : 'Presentation' }}
                </a>
            </div>
        @else
            <!-- Multiple documents - grid layout -->
            <div class="documents-grid">
                <div class="documents-header">
                    <h2>{{ ucfirst($type) }} Documents ({{ $documentCount }})</h2>
                    <p>{{ $lesson['subject'] ?? 'Subject' }} • {{ $lesson['year'] ?? '2025' }}</p>
                </div>
                <div class="documents-container">
                    @foreach($documents as $index => $doc)
                    <div class="document-card-small">
                        <div class="document-icon-small {{ $type }}">
                            {{ strtoupper($type) }}
                        </div>
                        <h3 class="document-title-small">{{ $doc['title'] ?? 'Document Title' }}</h3>

                        <div class="document-meta-small">
                            <span>{{ $doc['file_size'] ?? 'N/A' }}</span>
                            <span>
                                @if($type === 'pdf' && isset($doc['pages']))
                                    {{ $doc['pages'] }} pages
                                @elseif($type === 'ppt' && isset($doc['slides']))
                                    {{ $doc['slides'] }} slides
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>

                        <a href="{{ route('dashboard.lesson.document.content', [
                            'lessonId' => $lesson['id'],
                            'type' => $type,
                            'docId' => $doc['id'] ?? ($index + 1)
                        ]) }}" class="action-button-small {{ $type }}">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>


    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for action buttons
            const actionButtons = document.querySelectorAll('.action-button, .action-button-small');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Add loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                    
                    // Reset after navigation (in case of issues)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 3000);
                });
            });

            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    history.back();
                }
            });
        });
    </script>
</body>
</html>
