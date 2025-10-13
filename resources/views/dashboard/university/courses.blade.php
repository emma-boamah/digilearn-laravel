<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $program['name'] }} Courses - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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

        .program-info {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            background-color: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .course-thumbnail {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2rem;
        }

        .course-info {
            padding: 1.5rem;
        }

        .course-header {
            margin-bottom: 1rem;
        }

        .course-code {
            font-size: 0.75rem;
            color: var(--secondary-blue);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .course-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0.25rem 0;
        }

        .course-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .course-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .course-stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-lessons-btn {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .view-lessons-btn:hover {
            background-color: var(--primary-red-hover);
        }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .course-card {
                margin: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <button class="back-button" id="backToProgramsButton">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Programs
                    </button>
                    <h1 class="page-title">{{ $program['name'] }} Courses</h1>
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
            <div class="program-info">
                <h2>{{ $program['name'] }}</h2>
                <p>{{ $program['description'] }}</p>
            </div>

            <div class="courses-grid">
                @foreach($courses as $course)
                <div class="course-card" data-course-id="{{ $course['id'] }}">
                    <div class="course-thumbnail">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="course-info">
                        <div class="course-header">
                            <div class="course-code">{{ $course['code'] }}</div>
                            <h3 class="course-name">{{ $course['name'] }}</h3>
                        </div>
                        <p class="course-description">{{ $course['description'] }}</p>
                        <div class="course-meta">
                            <div class="course-stats">
                                <div class="course-stat">
                                    <i class="fas fa-play-circle"></i>
                                    {{ $course['lessons_count'] }} Lessons
                                </div>
                                <div class="course-stat">
                                    <i class="fas fa-credit-card"></i>
                                    {{ $course['credit_hours'] }} Credits
                                </div>
                            </div>
                            <button class="view-lessons-btn" data-course-id="{{ $course['id'] }}">View Lessons</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Handle back to programs button
            const backButton = document.getElementById('backToProgramsButton');
            if (backButton) {
                backButton.addEventListener('click', function() {
                    window.location.href = '{{ route("dashboard.university.programs") }}';
                });
            }

            // Handle course card clicks
            const courseCards = document.querySelectorAll('.course-card');
            courseCards.forEach(function(card) {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking the button directly
                    if (e.target.classList.contains('view-lessons-btn')) {
                        return;
                    }
                    
                    const courseId = this.getAttribute('data-course-id');
                    if (courseId) {
                        window.location.href = '{{ route("dashboard.university.course.lessons", ":courseId") }}'.replace(':courseId', courseId);
                    }
                });
            });

            // Handle view lessons button clicks
            const viewLessonsButtons = document.querySelectorAll('.view-lessons-btn');
            viewLessonsButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent card click
                    
                    const courseId = this.getAttribute('data-course-id');
                    if (courseId) {
                        window.location.href = '{{ route("dashboard.university.course.lessons", ":courseId") }}'.replace(':courseId', courseId);
                    }
                });
            });
        });
    </script>
</body>
</html>
