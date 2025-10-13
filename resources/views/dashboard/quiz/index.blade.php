<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quizzes - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @include('dashboard.quiz.partials.styles')
</head>
<body>
    <div class="main-container">
        @include('dashboard.quiz.partials.sidebar')
        @include('dashboard.quiz.partials.sidebar-overlay')

        <!-- Main Content -->
        <main class="main-content">
            @include('dashboard.quiz.partials.mobile-header')
            @include('dashboard.quiz.partials.desktop-header')
            @include('dashboard.quiz.partials.filter-bar')
            @include('dashboard.quiz.partials.hero-section')
            
            <!-- Content Section with Quiz Grid -->
            <div class="content-section">
                <div class="content-grid">
                    @forelse($quizzes ?? [] as $quiz)
                        @include('dashboard.quiz.partials.quiz-card', ['quiz' => $quiz])
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <div style="background-color: var(--white); border-radius: 1rem; padding: 3rem; box-shadow: var(--shadow-sm);">
                                <svg style="width: 80px; height: 80px; color: var(--gray-400); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">No quizzes available</h3>
                                <p style="color: var(--gray-500); font-size: 1rem;">Quizzes for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'this level')) }} are coming soon!</p>
                                <p style="color: var(--gray-400); font-size: 0.875rem; margin-top: 0.5rem;">Check back later or try a different grade level.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    @include('dashboard.quiz.partials.scripts')
</body>
</html>
