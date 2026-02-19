<!-- Search/Filter Bar -->
<div class="filter-bar">
    <div class="filter-row">
        <!-- Level Indicator (Anchor Tag) -->
        <x-level-indicator :selectedLevel="$selectedLevelGroup" />

        <form action="{{ route('quiz.index') }}" method="GET" class="search-box" id="mobileSearchBox">
            <input type="text" name="search" class="search-input" placeholder="Search quizzes..." id="quizSearchInput" value="{{ request('search') }}">
            <button type="submit" class="search-button">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<!-- Horizontal Subjects Filter -->
<div class="subjects-filter-container">
    <div class="subjects-filter">
        @foreach($subjects ?? [] as $subject)
            <div class="subject-chip {{ request('subject', 'all') === $subject['slug'] ? 'active' : '' }}" data-subject="{{ $subject['slug'] }}">
                <i class="{{ $subject['icon'] }}"></i>
                {{ $subject['name'] }}
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
        align-items: center;
        gap: 0.75rem;
        background-color: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(10px) saturate(160%);
        -webkit-backdrop-filter: blur(10px) saturate(160%);
        border-bottom: 1px solid var(--gray-200);
        box-sizing: border-box;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .youtube-sidebar.collapsed ~ .main-content .filter-bar {
        padding-left: calc(var(--sidebar-width-collapsed, 72px) + 0.75rem);
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        max-width: 100%;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        display: flex;
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
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        overflow: hidden;
        box-sizing: border-box;
        z-index: 997 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .youtube-sidebar.collapsed ~ .main-content .subjects-filter-container {
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

    .subject-chip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        background-color: var(--gray-100);
        border: 1px solid transparent;
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
        background-color: var(--secondary-blue);
        color: var(--white);
        border-color: var(--secondary-blue);
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

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .filter-bar {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            height: auto;
            position: relative !important;
            top: 0 !important;
            margin-top: 0;
        }

        .filter-row {
            flex-wrap: nowrap !important;
            gap: 0.5rem;
            justify-content: space-between;
        }

        .subjects-filter-container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            position: relative !important;
            top: 0 !important;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .search-box {
            min-width: 0; /* Allow shrinking */
            flex: 1;
        }
        
        /* Ensure level indicator doesn't grow too much */
        .level-container {
            flex-shrink: 0;
            width: auto;
        }
    }

    /* Dynamic Island / Safe Area Support */
    @supports (padding-top: env(safe-area-inset-top)) {
        .filter-bar {
            padding-top: calc(0.75rem + var(--safe-area-inset-top));
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Subject Chip filtering
        const chips = document.querySelectorAll('.subject-chip');
        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                const subject = this.dataset.subject;
                const url = new URL(window.location.href);
                
                if (subject === 'all') {
                    url.searchParams.delete('subject');
                } else {
                    url.searchParams.set('subject', subject);
                }
                
                // Reset search when changing subject or keep it? User might want to search within a subject.
                // Let's keep existing search if any
                
                window.location.href = url.toString();
            });
        });
    });
</script>
