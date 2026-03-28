<!-- Search/Filter Bar -->
<div class="filter-bar" id="filterBar">
    <div class="filter-row">
        <!-- Level Indicator (Anchor Tag) -->
        <x-level-indicator :selectedLevel="$selectedLevelGroup" />

        <div class="current-level-display">
            @if(isset($canonicalGrades) && count($canonicalGrades) > 0)
            @foreach($canonicalGrades as $grade)
            @php
            $isUnlocked = in_array($grade, $unlockedGrades ?? []);
            $userLevel = \App\Models\Level::where('slug', auth()->user()->grade)
            ->orWhere('title', auth()->user()->grade)
            ->first();

            // Active tab is either the specifically selected grade OR the user's current grade (if none selected)
            $isActive = false;
            if (isset($validSelectedGrade) && $validSelectedGrade) {
            $isActive = ($validSelectedGrade === $grade);
            } elseif ($userLevel) {
            $isActive = ($userLevel->title === $grade);
            }

            // Fallback abbreviate function if not defined
            if(!function_exists('abbreviateGrade')) {
            function abbreviateGrade($g) {
            $gStr = strtolower((string) $g);
            $parts = preg_split('/[- ]+/', $gStr);
            $lastPart = trim(end($parts));
            if (str_contains($gStr, 'primary')) return 'P' . $lastPart;
            if (str_contains($gStr, 'jhs')) return 'JHS' . $lastPart;
            if (str_contains($gStr, 'shs')) return 'SHS' . $lastPart;
            if (str_contains($gStr, 'level')) return 'L' . $lastPart;
            if (str_contains($gStr, 'year')) return 'Y' . $lastPart;
            return $g;
            }
            }
            @endphp
            <a href="{{ $isUnlocked ? route('quiz.index', ['grade' => $grade]) : '#' }}"
                class="grade-tab {{ $isUnlocked ? '' : 'locked' }} {{ $isActive ? 'active' : '' }}" {!! $isUnlocked ? ''
                : 'title="Complete current lessons to unlock ' . $grade . '"' !!}>
                @if(!$isUnlocked)
                <i class="fas fa-lock"></i>
                @endif
                <span class="grade-full-name">{{ $grade }}</span>
                <span class="grade-short-name">{{ abbreviateGrade($grade) }}</span>
            </a>
            @endforeach
            @endif
        </div>

        <!-- Mobile Search Toggle Button -->
        <button class="mobile-search-toggle" id="mobileSearchToggle">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17.5 17.5L12.5001 12.5M14.1667 8.33333C14.1667 11.555 11.555 14.1667 8.33333 14.1667C5.11167 14.1667 2.5 11.555 2.5 8.33333C2.5 5.11167 5.11167 2.5 8.33333 2.5C11.555 2.5 14.1667 5.11167 14.1667 8.33333Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <div class="search-container">
            <form action="{{ route('quiz.index') }}" method="GET" class="search-box" id="mobileSearchBox">
                <input type="text" name="search" class="search-input" placeholder="Search quizzes..."
                    id="quizSearchInput" value="{{ request('search') }}">
                <button type="submit" class="search-button">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <!-- Close button for mobile search -->
                <button type="button" class="search-close" id="searchClose">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Horizontal Filters (Context & Subjects) -->
<div class="subjects-filter-container">
    <!-- Row 1: Context Filter -->
    <div class="subjects-filter context-filter">
        <span class="filter-label">Category:</span>
        <div class="subject-chip {{ $context === 'all' ? 'active' : '' }}" data-context="all">
            <i class="fas fa-th-large"></i> All
        </div>
        @foreach($categories as $category)
        @php
        $catSlug = strtolower($category->slug ?? '');
        $isBece = str_contains($catSlug, 'bece');
        $isWassce = str_contains($catSlug, 'wassce');
        $levelGroup = $selectedLevelGroup ?? session('selected_level_group', Auth::user()->current_level_group ??
        'primary-lower');
        @endphp
        @if($catSlug !== 'normal' && ($isBece || ($isWassce && str_contains(strtolower($levelGroup), 'shs')) ||
        (!$isBece && !$isWassce)))
        <div class="subject-chip {{ $context === $category->slug ? 'active' : '' }}"
            data-context="{{ $category->slug }}">
            <i
                class="{{ $catSlug === 'bece' ? 'fas fa-graduation-cap' : ($catSlug === 'wassce' ? 'fas fa-university' : 'fas fa-book') }}"></i>
            {{ $category->name }}
        </div>
        @endif
        @endforeach
    </div>

    <!-- Row 2: Subjects Filter -->
    <div class="subjects-filter">
        <span class="filter-label">Subjects:</span>
        @php
        $subjectIcons = [
        'all' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>',
        'mathematics' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>',
        'science' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>',
        'english' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
        </svg>',
        'social-studies' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>',
        'history' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>',
        'chemistry' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>',
        'physics' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>',
        'biology' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>',
        'geography' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>',
        'art' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4 4 4 0 004-4V5z" />
        </svg>',
        'music' => '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
        </svg>',
        ];
        $defaultIcon = '<svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>';
        @endphp
        @foreach($subjects ?? [] as $subject)
        <div class="subject-chip {{ request('subject', 'all') === $subject['slug'] ? 'active' : '' }}"
            data-subject="{{ $subject['slug'] }}">
            {!! $subjectIcons[$subject['slug'] ?? ''] ?? $defaultIcon !!}
            {{ $subject['name'] ?? '' }}
        </div>
        @endforeach
    </div>
</div>

<style>
    :root {
        --safe-area-inset-top: env(safe-area-inset-top, 0px);
    }

    /* Search/Filter Bar */
    .filter-bar {
        position: fixed !important;
        top: calc(60px + var(--safe-area-inset-top)) !important;
        left: 0;
        width: 100vw;
        padding-left: calc(var(--sidebar-width-expanded, 240px) + 0.75rem);
        padding-right: 0.75rem;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        z-index: 998 !important;
        display: flex;
        flex-direction: column;
        /* Stack row 1 and search on mobile if needed, but here we use it for layout consistency */
        gap: 0.75rem;
        background-color: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(10px) saturate(160%);
        -webkit-backdrop-filter: blur(10px) saturate(160%);
        border-bottom: 1px solid var(--gray-200);
        box-sizing: border-box;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .youtube-sidebar.collapsed~.main-content .filter-bar {
        padding-left: calc(var(--sidebar-width-collapsed, 72px) + 0.75rem);
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        max-width: 100%;
    }

    .search-container {
        position: relative;
        flex: 1;
        min-width: 200px;
        display: flex;
    }

    .search-box {
        position: relative;
        width: 100%;
        display: flex;
    }

    .current-level-display {
        display: inline-flex;
        align-items: center;
        gap: 1.25rem;
        margin-left: 0.5rem;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 2px 4px;
    }

    .current-level-display::-webkit-scrollbar {
        display: none;
    }

    .grade-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--gray-100);
        border: 2px solid var(--gray-200);
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
        height: 40px;
        box-sizing: border-box;
    }

    .grade-tab:hover:not(.locked) {
        border-color: var(--secondary-blue);
        color: var(--secondary-blue);
        background: rgba(38, 119, 184, 0.05);
    }

    .grade-tab.active {
        background: rgba(38, 119, 184, 0.05);
        border-color: var(--secondary-blue);
        color: var(--secondary-blue);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.2);
    }

    .grade-tab.locked {
        opacity: 0.6;
        background-color: var(--gray-50);
        cursor: not-allowed;
        pointer-events: none;
    }

    .grade-tab i {
        font-size: 0.75rem;
    }

    .grade-tab.locked i {
        color: var(--gray-400);
    }

    /* Responsive grade name handling */
    .grade-full-name {
        display: inline;
    }

    .grade-short-name {
        display: none;
    }

    .search-input {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        width: 100%;
        font-size: 0.875rem;
        padding-right: 3.5rem;
        background: var(--white);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--secondary-blue);
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    }

    .search-button {
        position: absolute;
        right: 1px;
        top: 1px;
        height: calc(100% - 2px);
        width: 2.5rem;
        background-color: var(--secondary-blue, #2677B8);
        border: none;
        border-top-right-radius: 0.4rem;
        border-bottom-right-radius: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .search-button:hover {
        background-color: var(--secondary-blue-hover, #1e5a8a);
    }

    .search-icon {
        color: white;
        stroke: currentColor;
    }

    .mobile-search-toggle {
        display: none;
        background: var(--secondary-blue);
        border: none;
        border-radius: 0.5rem;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--white);
        align-items: center;
        justify-content: center;
    }

    .search-close {
        display: none;
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0.5rem;
        cursor: pointer;
        color: var(--gray-400);
        z-index: 5;
    }

    .search-close:hover {
        color: var(--gray-600);
    }

    /* Horizontal Subjects Filter */
    .subjects-filter-container {
        position: fixed !important;
        left: 0 !important;
        top: calc(116px + var(--safe-area-inset-top)) !important;
        width: 100vw !important;
        background-color: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding-left: calc(var(--sidebar-width-expanded, 240px) + 0.75rem);
        padding-right: 1rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        overflow: hidden;
        box-sizing: border-box;
        z-index: 997 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .youtube-sidebar.collapsed~.main-content .subjects-filter-container {
        padding-left: calc(var(--sidebar-width-collapsed, 72px) + 0.75rem);
    }

    .subjects-filter {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 0.5rem 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scroll-behavior: smooth;
        width: 100%;
    }

    .subjects-filter::-webkit-scrollbar {
        display: none;
    }

    .filter-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        white-space: nowrap;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .subject-chip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        background-color: var(--gray-100);
        border: 2px solid transparent;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .subject-chip i {
        font-size: 0.875rem;
    }

    .subject-chip:hover {
        background-color: var(--gray-200);
        color: var(--secondary-blue);
    }

    .subject-chip.active {
        background: rgba(38, 119, 184, 0.05);
        border-color: var(--secondary-blue);
        color: var(--secondary-blue);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.2);
    }

    .filter-button {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .filter-button.question {
        background-color: var(--primary-red);
        color: var(--white);
    }

    .filter-button:hover {
        opacity: 0.9;
    }

    [data-theme="dark"] .filter-bar {
        background-color: #000000;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .filter-bar {
            position: fixed !important;
            top: calc(60px + var(--safe-area-inset-top)) !important;
            left: 0 !important;
            width: 100vw !important;
            height: 56px !important;
            padding: 0 0.75rem !important;
            gap: 0.5rem !important;
            overflow: hidden !important;
            z-index: 998 !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
        }

        .filter-row {
            flex-wrap: nowrap !important;
            gap: 0.5rem !important;
            justify-content: flex-start !important;
            width: 100% !important;
            height: 100% !important;
        }

        .subjects-filter-container {
            left: 0 !important;
            width: 100vw !important;
            padding: 0.5rem 0 !important;
            height: auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            position: fixed !important;
            top: calc(116px + var(--safe-area-inset-top)) !important;
            z-index: 997 !important;
            gap: 0 !important;
            background-color: var(--white);
        }

        .search-container {
            display: none;
        }

        .mobile-search-toggle {
            display: flex;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            padding: 0.75rem !important;
        }

        #filterBar.search-active .search-container {
            display: flex;
            position: absolute;
            inset: 0;
            z-index: 10;
            background: var(--white);
            padding: 0.5rem 0.75rem;
            align-items: center;
        }

        #filterBar.search-active .search-box {
            background: var(--gray-100);
            border-radius: 0.5rem;
            width: 100%;
            display: flex;
        }

        #filterBar.search-active .search-close {
            display: flex;
        }

        #filterBar.search-active .level-container,
        #filterBar.search-active .current-level-display,
        #filterBar.search-active .mobile-search-toggle {
            display: none;
        }

        .search-box {
            min-width: 0;
            /* Allow shrinking */
            flex: 1;
        }

        /* Ensure level indicator doesn't grow too much */
        .level-container {
            flex-shrink: 0;
            width: auto;
        }

        .current-level-display {
            flex: 1 !important;
            min-width: 0 !important;
            overflow-x: auto !important;
            white-space: nowrap !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            padding: 0 !important;
            height: 100% !important;
            -webkit-overflow-scrolling: touch;
        }

        .grade-tab {
            padding: 0.5rem 0.75rem;
            height: 36px;
        }

        .grade-full-name {
            display: none;
        }

        .grade-short-name {
            display: inline;
        }
    }

    /* Dynamic Island / Safe Area Support */
    @supports (padding-top: env(safe-area-inset-top)) {
        .filter-bar {
            padding-top: calc(0.75rem + var(--safe-area-inset-top));
        }
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        // Context Filter (Row 1)
        const contextChips = document.querySelectorAll('.context-filter .subject-chip');
        contextChips.forEach(chip => {
            chip.addEventListener('click', function () {
                const context = this.getAttribute('data-context');
                const url = new URL(window.location.href);

                if (context === 'all') {
                    url.searchParams.delete('context');
                } else {
                    url.searchParams.set('context', context);
                }

                // Reset subject filter when context changes
                url.searchParams.delete('subject');
                window.location.href = url.toString();
            });
        });

        // Subject Chip filtering (Row 2)
        const subjectChips = document.querySelectorAll('.subjects-filter:not(.context-filter) .subject-chip');
        subjectChips.forEach(chip => {
            chip.addEventListener('click', function () {
                const subject = this.dataset.subject;
                const url = new URL(window.location.href);

                if (subject === 'all') {
                    url.searchParams.delete('subject');
                } else {
                    url.searchParams.set('subject', subject);
                }

                window.location.href = url.toString();
            });
        });

        // Mobile Search UI Logic
        const mobileSearchToggle = document.getElementById('mobileSearchToggle');
        const searchInput = document.getElementById('quizSearchInput');
        const searchClose = document.getElementById('searchClose');
        const filterBar = document.getElementById('filterBar');

        if (mobileSearchToggle && filterBar && searchInput) {
            mobileSearchToggle.addEventListener('click', function () {
                filterBar.classList.add('search-active');
                setTimeout(() => {
                    searchInput.focus();
                }, 100);
            });
        }

        if (searchClose && searchInput && filterBar) {
            searchClose.addEventListener('click', function () {
                if (searchInput.value) {
                    // Clear input and reload if there was search content
                    searchInput.value = '';
                    const url = new URL(window.location.href);
                    url.searchParams.delete('search');
                    window.location.href = url.toString();
                } else {
                    // Just close the search if input is empty
                    filterBar.classList.remove('search-active');
                }
            });
        }

        // Handle Escape key to close search
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && filterBar && filterBar.classList.contains('search-active')) {
                filterBar.classList.remove('search-active');
            }
        });
    });
</script>