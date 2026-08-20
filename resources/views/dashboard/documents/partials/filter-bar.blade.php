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
                        $userLevel = \App\Models\Level::where('slug', auth()->user()->grade ?? '')
                            ->orWhere('title', auth()->user()->grade ?? '')
                            ->first();

                        $isActive = false;
                        if (isset($validSelectedGrade) && $validSelectedGrade) {
                            $isActive = ($validSelectedGrade === $grade);
                        } elseif ($userLevel) {
                            $isActive = ($userLevel->title === $grade);
                        }

                        if(!function_exists('abbreviateDocGrade')) {
                            function abbreviateDocGrade($g) {
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
                    <a href="{{ $isUnlocked ? route('dashboard.library', array_merge(request()->query(), ['grade' => $grade])) : '#' }}"
                        class="grade-tab {{ $isUnlocked ? '' : 'locked' }} {{ $isActive ? 'active' : '' }}" 
                        {!! $isUnlocked ? '' : 'title="Complete current lessons to unlock ' . $grade . '"' !!}>
                        @if(!$isUnlocked)
                            <i class="fas fa-lock"></i>
                        @endif
                        <span class="grade-full-name">{{ $grade }}</span>
                        <span class="grade-short-name">{{ abbreviateDocGrade($grade) }}</span>
                    </a>
                @endforeach
            @endif
        </div>

        <!-- Mobile Search Toggle Button -->
        <button class="mobile-search-toggle" id="mobileSearchToggle" type="button">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.5 17.5L12.5001 12.5M14.1667 8.33333C14.1667 11.555 11.555 14.1667 8.33333 14.1667C5.11167 14.1667 2.5 11.555 2.5 8.33333C2.5 5.11167 5.11167 2.5 8.33333 2.5C11.555 2.5 14.1667 5.11167 14.1667 8.33333Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <div class="search-container" data-search-domain="documents" data-level-group="{{ $selectedLevelGroup ?? '' }}">
            <form action="{{ route('dashboard.library') }}" method="GET" class="search-box" id="mobileSearchBox">
                @if(request('grade'))
                    <input type="hidden" name="grade" value="{{ request('grade') }}">
                @endif
                @if(request('context') && request('context') !== 'all')
                    <input type="hidden" name="context" value="{{ request('context') }}">
                @endif
                @if(request('subject') && request('subject') !== 'all')
                    <input type="hidden" name="subject" value="{{ request('subject') }}">
                @endif
                @if(request('format') && request('format') !== 'all')
                    <input type="hidden" name="format" value="{{ request('format') }}">
                @endif

                <input type="text" name="search" class="search-input" placeholder="Search documents & books..."
                    id="docSearchInput" value="{{ request('search') }}" autocomplete="off">
                <button type="submit" class="search-button">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button type="button" class="search-close" id="searchClose">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Horizontal Filters (Context, Subjects & Formats) -->
<div class="subjects-filter-container">
    @php
        $currentLevelGroup = $selectedLevelGroup ?? session('selected_level_group', Auth::user()->current_level_group ?? 'primary-lower');
        $isPrimaryLevel = str_contains(strtolower($currentLevelGroup), 'primary') || str_contains(strtolower($currentLevelGroup), 'grade');
    @endphp

    <!-- Row 1: Context Filter -->
    @if(!$isPrimaryLevel)
    <div class="subjects-filter context-filter">
        <span class="filter-label">Category:</span>
        <a href="{{ route('dashboard.library', array_merge(request()->except('context', 'page'), ['context' => 'all'])) }}" 
           class="subject-chip {{ ($context ?? 'all') === 'all' ? 'active' : '' }}" style="text-decoration: none;">
            <i class="fas fa-th-large"></i> All
        </a>
        @foreach($categories as $category)
            @php
                $catSlug = strtolower($category->slug ?? '');
                $isBece = str_contains($catSlug, 'bece');
                $isWassce = str_contains($catSlug, 'wassce');
                $levelGroup = $currentLevelGroup;
                $isCurrentContext = ($context ?? '') === ($category->slug ?? $category->name);
            @endphp
            @if($catSlug !== 'normal' && (($isBece && (str_contains(strtolower($levelGroup), 'jhs') || str_contains(strtolower($levelGroup), 'shs'))) || ($isWassce && str_contains(strtolower($levelGroup), 'shs')) || (!$isBece && !$isWassce)))
            <a href="{{ route('dashboard.library', array_merge(request()->except('context', 'page'), ['context' => $category->slug ?? $category->name])) }}" 
               class="subject-chip {{ $isCurrentContext ? 'active' : '' }}" style="text-decoration: none;">
                @if($isBece || $isWassce)
                    <i class="fas fa-graduation-cap"></i>
                @else
                    <i class="fas fa-tag"></i>
                @endif
                {{ $category->name }}
            </a>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Row 2: Subjects Filter (Only shows subjects with actual documents) -->
    @if(isset($subjects) && $subjects->count() > 0)
    <div class="subjects-filter">
        <span class="filter-label">Subjects:</span>
        <a href="{{ route('dashboard.library', array_merge(request()->except('subject', 'page'), ['subject' => 'all'])) }}"
           class="subject-chip {{ empty($subjectId) || $subjectId === 'all' ? 'active' : '' }}" style="text-decoration: none;">
            <i class="fas fa-inbox"></i> All
        </a>
        @foreach($subjects as $subject)
            @php
                $isCurrentSubject = ($subjectId ?? '') == $subject->id || ($subjectId ?? '') == $subject->name;
                $slug = strtolower(trim($subject->name));
                $iconClass = 'fa-book';
                if (str_contains($slug, 'math')) $iconClass = 'fa-calculator';
                elseif (str_contains($slug, 'science') || str_contains($slug, 'physic') || str_contains($slug, 'chem') || str_contains($slug, 'bio')) $iconClass = 'fa-flask';
                elseif (str_contains($slug, 'english') || str_contains($slug, 'literat')) $iconClass = 'fa-book-open';
                elseif (str_contains($slug, 'social') || str_contains($slug, 'civic') || str_contains($slug, 'gov')) $iconClass = 'fa-globe-africa';
                elseif (str_contains($slug, 'history')) $iconClass = 'fa-landmark';
                elseif (str_contains($slug, 'ict') || str_contains($slug, 'comput') || str_contains($slug, 'tech')) $iconClass = 'fa-laptop-code';
                elseif (str_contains($slug, 'french') || str_contains($slug, 'language')) $iconClass = 'fa-language';
                elseif (str_contains($slug, 'account') || str_contains($slug, 'business') || str_contains($slug, 'econ')) $iconClass = 'fa-chart-line';
                elseif (str_contains($slug, 'geograph')) $iconClass = 'fa-map-marked-alt';
            @endphp
            <a href="{{ route('dashboard.library', array_merge(request()->except('subject', 'page'), ['subject' => $subject->id])) }}"
               class="subject-chip {{ $isCurrentSubject ? 'active' : '' }}" style="text-decoration: none;">
                <i class="fas {{ $iconClass }}"></i>
                {{ $subject->name }}
            </a>
        @endforeach
    </div>
    @endif
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
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
        padding-left: calc(var(--sidebar-width-collapsed, 80px) + 0.75rem);
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
        gap: 0.75rem;
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
        box-sizing: border-box;
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
        padding-left: calc(var(--sidebar-width-collapsed, 80px) + 0.75rem);
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
        text-decoration: none;
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

    /* Dark Theme */
    [data-theme="dark"] .filter-bar {
        background-color: #000000;
        border-color: var(--border-color);
    }

    [data-theme="dark"] .subjects-filter-container {
        background-color: #16181c;
        border-color: var(--border-color);
    }

    [data-theme="dark"] .grade-tab {
        background-color: #202327;
        border-color: #2f3336;
        color: #e2e8f0;
    }

    [data-theme="dark"] .grade-tab:hover:not(.locked) {
        border-color: var(--secondary-blue);
        color: #60a5fa;
        background: rgba(38, 119, 184, 0.15);
    }

    [data-theme="dark"] .grade-tab.active {
        background: rgba(38, 119, 184, 0.15);
        border-color: var(--secondary-blue);
        color: #60a5fa;
    }

    [data-theme="dark"] .grade-tab.locked {
        background-color: #16181c;
        border-color: #2f3336;
    }

    [data-theme="dark"] .subject-chip {
        background-color: #202327;
        color: #e2e8f0;
        border-color: transparent;
    }

    [data-theme="dark"] .subject-chip:hover {
        background-color: #2f3336;
        color: #60a5fa;
    }

    [data-theme="dark"] .subject-chip.active {
        background: rgba(38, 119, 184, 0.15);
        border-color: var(--secondary-blue);
        color: #60a5fa;
    }

    [data-theme="dark"] .filter-label {
        color: #a4b1cd;
    }

    [data-theme="dark"] .search-input {
        background-color: #16181c;
        border-color: #2f3336;
        color: #ffffff;
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        // Mobile Search UI Logic
        const mobileSearchToggle = document.getElementById('mobileSearchToggle');
        const searchInput = document.getElementById('docSearchInput');
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
                    searchInput.value = '';
                    const url = new URL(window.location.href);
                    url.searchParams.delete('search');
                    window.location.href = url.toString();
                } else {
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
