<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>University Programs - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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

        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .program-card {
            background-color: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .program-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .program-thumbnail {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            position: relative;
        }

        .program-info {
            padding: 1.5rem;
        }

        .program-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .program-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .program-level {
            font-size: 0.75rem;
            color: var(--secondary-blue);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .program-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .program-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .program-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .program-stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-courses-btn {
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

        .view-courses-btn:hover {
            background-color: var(--primary-red-hover);
        }

        @media (max-width: 768px) {
            .programs-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .program-card {
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
                    <button class="back-button" onclick="history.back()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </button>
                    <h1 class="page-title">University Programs</h1>
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
            <div class="programs-grid">
                @foreach($programs as $program)
                <div class="program-card" onclick="window.location.href='{{ route('dashboard.university.program.courses', $program['id']) }}'">
                    <div class="program-thumbnail">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="program-info">
                        <div class="program-header">
                            <div>
                                <h3 class="program-name">{{ $program['name'] }}</h3>
                                <div class="program-level">{{ $program['level'] }}</div>
                            </div>
                        </div>
                        <p class="program-description">{{ $program['description'] }}</p>
                        <div class="program-meta">
                            <div class="program-stats">
                                <div class="program-stat">
                                    <i class="fas fa-book"></i>
                                    {{ $program['courses_count'] }} Courses
                                </div>
                                <div class="program-stat">
                                    <i class="fas fa-clock"></i>
                                    {{ $program['duration'] }}
                                </div>
                            </div>
                            <button class="view-courses-btn">View Courses</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
