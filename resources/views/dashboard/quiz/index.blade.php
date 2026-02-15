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
    @include('dashboard.quiz.partials.sidebar-overlay') <!-- Move outside main-container -->

    <div class="main-container">
        @include('components.dashboard-sidebar')
        @include('dashboard.quiz.partials.desktop-header')

        <!-- Main Content -->
        <main class="main-content">
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
                                @if($requiresSubscription ?? false)
                                    <svg style="width: 80px; height: 80px; color: var(--primary-500); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">Unlock Premium Quizzes</h3>
                                    <p style="color: var(--gray-500); font-size: 1rem; margin-bottom: 1.5rem;">Subscribe to access quizzes for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'this level')) }} and many more!</p>
                                    <a href="{{ route('pricing') }}" style="display: inline-block; background-color: var(--primary-500); color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: background-color 0.2s;">
                                        View Subscription Plans
                                    </a>
                                @else
                                @if(request('search'))
                                    <svg style="width: 80px; height: 80px; color: var(--gray-400); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">No results found for "{{ request('search') }}"</h3>
                                    <p style="color: var(--gray-500); font-size: 1rem;">We couldn't find any quizzes matching your search in the current level group.</p>
                                    <a href="{{ route('quiz.index') }}" style="display: inline-block; margin-top: 1rem; color: var(--secondary-blue, #2677B8); font-weight: 600; text-decoration: none;">
                                        Clear Search
                                    </a>
                                @else
                                    <svg style="width: 80px; height: 80px; color: var(--gray-400); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">No quizzes available</h3>
                                    <p style="color: var(--gray-500); font-size: 1rem;">Quizzes for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'this level')) }} are coming soon!</p>
                                    <p style="color: var(--gray-400); font-size: 0.875rem; margin-top: 0.5rem;">Check back later or try a different grade level.</p>
                                @endif
                                @endif
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
