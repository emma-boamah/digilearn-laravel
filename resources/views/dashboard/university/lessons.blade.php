<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $course['name'] }} Lessons - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Same base styles as courses.blade.php */
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
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
            background-color: var(--gray-25);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .header {
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: none;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-red);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .main-content {
            padding: 2rem 0;
        }

        .course-info {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .lessons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .lesson-card {
            background-color: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .lesson-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .lesson-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2rem;
        }

        .lesson-duration {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            background-color: rgba(0, 0, 0, 0.8);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .lesson-info {
            padding: 1.25rem;
        }

        .lesson-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lesson-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .lesson-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .lesson-instructor {
            color: var(--secondary-blue);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .lessons-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <button class="back-button" id="backToCoursesButton">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Courses
                    </button>
                    <h1 class="page-title">{{ $course['name'] }} Lessons</h1>
                </div>
                
                <div class="header-right">
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="course-info">
                <h2>{{ $course['name'] }} ({{ $course['code'] }})</h2>
                <p>{{ $course['description'] }}</p>
                <p><strong>Instructor:</strong> {{ $course['instructor'] }}</p>
                <p><strong>Credit Hours:</strong> {{ $course['credit_hours'] }}</p>
            </div>

            <div class="lessons-grid">
                @forelse($lessons as $lesson)
                <div class="lesson-card" data-lesson-id="{{ $lesson['id'] }}" data-encoded-id="{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}">
                    <div class="lesson-thumbnail">
                        <i class="fas fa-play-circle"></i>
                        <div class="lesson-duration">{{ $lesson['duration'] }}</div>
                    </div>
                    <div class="lesson-info">
                        <h3 class="lesson-title">{{ $lesson['title'] }}</h3>
                        <p class="lesson-description">{{ $lesson['description'] }}</p>
                        <div class="lesson-meta">
                            <span class="lesson-instructor">{{ $lesson['instructor'] }}</span>
                            <span>Week {{ $lesson['week'] }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No lessons available</h3>
                    <p style="color: var(--gray-500);">Lessons for {{ $course['name'] }} are coming soon!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Handle back to courses button
            const backButton = document.getElementById('backToCoursesButton');
            if (backButton) {
                backButton.addEventListener('click', function() {
                    history.back();
                });
            }

            // Handle lesson card clicks
            const lessonCards = document.querySelectorAll('.lesson-card');
            lessonCards.forEach(function(card) {
                card.addEventListener('click', function() {
                    const encodedId = this.getAttribute('data-encoded-id');
                    if (encodedId) {
                        window.location.href = '{{ route("dashboard.lesson.view", ":lessonId") }}'.replace(':lessonId', encodedId);
                    }
                });
            });
        });
    </script>
</body>
</html>
