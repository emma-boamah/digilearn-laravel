<!-- Search/Filter Bar -->
<div class="filter-bar">
    <div class="search-box" id="mobileSearchBox">
        <input type="text" class="search-input" placeholder="Search quizzes...">
        <button class="search-button">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>
    </div>
    
    <!-- Custom levels dropdown -->
    <div class="custom-dropdown">
        <button class="dropdown-toggle">
            <span>{{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'Grade 1-3')) }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="dropdown-menu">
            <div class="dropdown-option" data-level="grade-1-3">Grade 1-3</div>
            <div class="dropdown-option" data-level="grade-4-6">Grade 4-6</div>
            <div class="dropdown-option" data-level="grade-7-9">Grade 7-9</div>
            <div class="dropdown-option" data-level="high-school">High School</div>
        </div>
    </div>
    
    <!-- Custom subjects dropdown -->
    <div class="custom-dropdown">
        <button class="dropdown-toggle">
            <span>Subjects</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="dropdown-menu">
            <div class="dropdown-section">
                <h4 class="section-header">Core Subjects</h4>
                <div class="dropdown-option" data-subject="mathematics">
                    <svg xmlns="http://www.w3.org/2000/svg" class="subject-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Mathematics</span>
                </div>
                <div class="dropdown-option" data-subject="science">
                    <svg xmlns="http://www.w3.org/2000/svg" class="subject-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span>Science</span>
                </div>
            </div>
            
            <div class="dropdown-section">
                <h4 class="section-header">Electives</h4>
                <div class="dropdown-option" data-subject="english">
                    <svg xmlns="http://www.w3.org/2000/svg" class="subject-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    <span>English</span>
                </div>
            </div>
        </div>
    </div>
    
    <button class="filter-button question">Question</button>
    <a href="{{ route('quiz.index') }}" class="filter-button quiz">Quiz</a>
</div>

<style>
    /* Search/Filter Bar */
    .filter-bar {
        display: flex;
        align-items: center;
        margin-top: 60px;
        gap: 0.75rem;
        padding: 0.75rem;
        background-color: var(--white);
        border-bottom: 1px solid var(--gray-200);
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 300px;
        display: flex;
    }

    .search-input {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        width: 100%;
        font-size: 0.875rem;
        padding-right: 3.5rem;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
    }

    .search-button {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 2.5rem;
        background-color: var(--primary-red);
        border: none;
        border-top-right-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .search-button:hover {
        background-color: #c41e2a;
    }

    .search-icon {
        color: white;
        stroke: currentColor;
    }

    /* Custom Dropdown Styles */
    .custom-dropdown {
        position: relative;
        min-width: 120px;
    }

    .dropdown-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        background-color: var(--white);
        color: var(--primary-red);
        font-size: 0.875rem;
        cursor: pointer;
        width: 100%;
        text-align: left;
    }

    .dropdown-toggle:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
    }

    .dropdown-chevron {
        width: 16px;
        height: 16px;
        color: var(--primary-red);
        transition: transform 0.2s ease;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background-color: var(--white);
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 100;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        max-height: 60vh;
        overflow-y: auto;
    }

    .custom-dropdown.open .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .custom-dropdown.open .dropdown-chevron {
        transform: rotate(180deg);
    }

    .dropdown-section {
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .dropdown-section:last-child {
        border-bottom: none;
    }

    .section-header {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--gray-500);
        letter-spacing: 0.5px;
    }

    .dropdown-option {
        display: flex;
        align-items: center;
        color: var(--gray-700);
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .dropdown-option:hover {
        background-color: var(--gray-50);
    }

    .subject-icon {
        width: 18px;
        height: 18px;
        margin-right: 0.75rem;
        color: var(--gray-600);
    }

    .filter-button {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .filter-button.question {
        background-color: var(--primary-red);
        color: var(--white);
    }

    .filter-button.quiz {
        background-color: var(--secondary-blue);
        color: var(--white);
    }

    .filter-button:hover {
        opacity: 0.9;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .filter-bar {
            flex-direction: row;
            gap: 12px;
            padding: 16px;
            position: relative;
        }

        .dropdowns-row {
            display: flex;
            gap: 12px;
            width: 100%;
        }

        .dropdowns-row .custom-dropdown {
            flex: 1;
            min-width: auto;
        }

        .search-box {
            display: flex;
            order: 0;
            min-width: 100%;
            margin-bottom: 12px;
        }

        .search-box.mobile-visible {
            display: flex;
        }

        .custom-dropdown {
            min-width: calc(50% - 0.25rem);
        }

        .filter-buttons {
            display: flex;
            gap: 12px;
            width: 100%;
        }

        .filter-button {
            flex: 1;
            padding: 0.75rem;
            font-size: 0.8rem;
        }
    }
</style>
