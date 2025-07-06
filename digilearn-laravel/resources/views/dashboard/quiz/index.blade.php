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
                    @forelse($quizzes as $quiz)
                        @include('dashboard.quiz.partials.quiz-card', ['quiz' => $quiz])
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No quizzes available</h3>
                            <p style="color: var(--gray-500);">Quizzes for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup)) }} are coming soon!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    @include('dashboard.quiz.partials.scripts')
</body>
</html>
