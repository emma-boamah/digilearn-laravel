<!DOCTYPE html>
@php
    $currentLevelGroup = $selectedLevelGroup ?? session('selected_level_group', Auth::user()->current_level_group ?? 'primary-lower');
    $isPrimaryLevel = str_contains(strtolower($currentLevelGroup), 'primary') || str_contains(strtolower($currentLevelGroup), 'grade');
    $mainContentTopOffset = $isPrimaryLevel ? '205px' : '255px';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Library - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    
    @include('dashboard.documents.partials.styles')
</head>
<body>
    @include('dashboard.quiz.partials.sidebar-overlay')

    <div class="main-container">
        @include('components.dashboard-sidebar')
        @include('dashboard.quiz.partials.desktop-header')

        <!-- Main Content -->
        <main class="main-content">
            @include('dashboard.documents.partials.filter-bar')
            
            <!-- Content Section with Document Grid -->
            <div class="content-section" style="padding-top: 1.5rem;">
                <div class="content-grid">
                    @forelse($documents ?? [] as $doc)
                        @include('dashboard.documents.partials.document-card', ['doc' => $doc])
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <div style="background-color: var(--white); border-radius: 1rem; padding: 3rem; box-shadow: var(--shadow-sm);">
                                @if($requiresSubscription ?? false)
                                    <svg style="width: 80px; height: 80px; color: var(--primary-500, #ef4444); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">Unlock Premium Library Materials</h3>
                                    <p style="color: var(--gray-500); font-size: 1rem; margin-bottom: 1.5rem;">Subscribe to access textbooks, presentation slides, and notes for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'this level')) }}!</p>
                                    <a href="{{ route('pricing') }}" style="display: inline-block; background-color: var(--primary-500, #ef4444); color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: background-color 0.2s;">
                                        View Subscription Plans
                                    </a>
                                @else
                                    @if(request('search'))
                                        <svg style="width: 80px; height: 80px; color: var(--gray-400); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">No documents found for "{{ request('search') }}"</h3>
                                        <p style="color: var(--gray-500); font-size: 1rem;">We couldn't find any written documents matching your search in this level.</p>
                                        <a href="{{ route('dashboard.library') }}" style="display: inline-block; margin-top: 1rem; color: var(--secondary-blue, #2677B8); font-weight: 600; text-decoration: none;">
                                            Clear Search
                                        </a>
                                    @else
                                        <svg style="width: 80px; height: 80px; color: var(--gray-400); margin: 0 auto 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <h3 style="color: var(--gray-600); margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600;">No documents available yet</h3>
                                        <p style="color: var(--gray-500); font-size: 1rem;">Educational books and presentation slides for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'this level')) }} will appear here.</p>
                                        <p style="color: var(--gray-400); font-size: 0.875rem; margin-top: 0.5rem;">Check back later or try a different grade level.</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                @if(isset($documents) && $documents->hasPages())
                    <div style="margin-top: 2.5rem; display: flex; justify-content: center;">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    @include('dashboard.quiz.partials.scripts')
    @include('components.search-autocomplete')
</body>
</html>
