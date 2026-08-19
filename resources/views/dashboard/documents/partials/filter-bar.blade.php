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
        <button class="mobile-search-toggle" id="mobileSearchToggle">
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

    <!-- Row 2: Subjects Filter -->
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
</div>

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
