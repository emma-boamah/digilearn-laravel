@extends('layouts.dashboard-components')

@section('title', 'Detailed Progress Report')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    :root {
        --app-blue: #2677B8;
        --app-blue-light: #3b8ccb;
        --app-blue-dark: #1e5a8a;
        --success-green: #10b981;
    }

    .report-page {
        padding: 2rem 1.5rem;
        max-width: 100%;
        margin: 60px auto 0;
    }

    /* ── Grade Tabs ── */
    .report-tabs {
        display: flex;
        gap: 2rem;
        background: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 1rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2.5rem;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .report-tab {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--gray-400);
        text-decoration: none;
        padding: 0.5rem 0;
        position: relative;
        transition: all 0.2s ease;
    }

    .report-tab:hover {
        color: var(--app-blue);
    }

    .report-tab.active {
        color: var(--app-blue);
    }

    .report-tab.active::after {
        content: '';
        position: absolute;
        bottom: -0.75rem;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--app-blue);
        border-radius: 2px;
    }

    .report-tab.locked {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .report-tab.locked i {
        font-size: 0.75rem;
        margin-right: 0.25rem;
    }

    /* ── Section Headers ── */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .section-subtitle {
        font-size: 0.875rem;
        color: var(--gray-400);
        font-weight: 500;
    }

    /* ── Performance Summary Grid ── */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .summary-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
    }

    .card-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    /* Icon theme: Blue for report */
    .card-icon-wrapper.ranking { background: #fff7ed; color: #f97316; } /* Kept ranking gold-ish */
    .card-icon-wrapper.average { background: #eff6ff; color: var(--app-blue); }
    .card-icon-wrapper.time    { background: #f5f3ff; color: #7c3aed; }

    .card-content {
        flex: 1;
    }

    .card-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-400);
        margin-bottom: 0.25rem;
    }

    .card-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 0.35rem;
    }

    .card-trend {
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .card-trend.up { color: var(--success-green); }

    /* ── Main Layout Split ── */
    .report-main-split {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* ── Quizzes Table Card ── */
    .quizzes-card, .mastery-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.03);
    }

    .quizzes-table {
        width: 100%;
        border-collapse: collapse;
    }

    .quizzes-table th {
        text-align: left;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-400);
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-50);
    }

    .quizzes-table td {
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--gray-50);
        vertical-align: middle;
    }

    .quiz-name-cell {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 0.9rem;
    }

    .quiz-subject-cell {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    .quiz-score-badge {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .quiz-score-badge.high { background: #ecfdf5; color: #059669; }
    .quiz-score-badge.med  { background: #fffbeb; color: #d97706; }
    .quiz-score-badge.low  { background: #fef2f2; color: #dc2626; }

    .quiz-status-cell {
        font-size: 0.8rem;
        color: var(--gray-400);
        font-style: italic;
    }

    .view-all-link {
        display: block;
        text-align: center;
        margin-top: 1.5rem;
        color: var(--app-blue);
        font-weight: 700;
        font-size: 0.875rem;
        text-decoration: none;
    }

    .view-all-link:hover { text-decoration: underline; }

    /* ── Subject Mastery ── */
    .mastery-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--gray-50);
    }

    .mastery-item:last-child { border-bottom: none; }

    .mastery-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mastery-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .mastery-icon.math { background: #eff6ff; color: #2563eb; }
    .mastery-icon.eng  { background: #ecfdf5; color: #059669; }
    .mastery-icon.sci  { background: #fff7ed; color: #c2410c; }

    .mastery-name {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 0.9rem;
    }

    .circular-progress {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background: radial-gradient(closest-side, white 79%, transparent 80% 100%),
                    conic-gradient(var(--app-blue) calc(var(--p) * 1%), var(--gray-100) 0);
    }

    .circular-progress::before {
        content: attr(data-percent) '%';
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--gray-900);
    }

    .mastery-footer {
        margin-top: 2rem;
    }

    .mastery-footer-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--gray-400);
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .footer-progress-bar {
        height: 8px;
        background: var(--gray-100);
        border-radius: 1rem;
        overflow: hidden;
    }

    .footer-progress-fill {
        height: 100%;
        background: var(--app-blue);
        border-radius: 1rem;
    }

    /* ── Lesson Engagement Grid ── */
    .engagement-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    .lesson-engagement-card {
        background: #fff;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s ease;
    }

    .lesson-engagement-card:hover {
        transform: translateY(-5px);
    }

    .lesson-thumb-wrapper {
        position: relative;
        aspect-ratio: 16/9;
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lesson-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ── Lesson Library Layout ── */
    .library-section {
        margin-top: 3rem;
    }

    .library-container {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 2rem;
        align-items: start;
    }

    /* Sidebar */
    .library-sidebar {
        background: #fff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.03);
    }

    .sidebar-label {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--gray-400);
        margin-bottom: 1rem;
        display: block;
    }

    .subject-filters {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .subject-btn {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-600);
        transition: all 0.2s;
        border: none;
        background: transparent;
        text-align: left;
        cursor: pointer;
        width: 100%;
    }

    .subject-btn:hover {
        background: var(--gray-50);
        color: var(--app-blue);
    }

    .subject-btn.active {
        background: #fff5f2; /* Light brand tint */
        color: var(--app-blue);
    }

    /* Library Main Content */
    .library-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .library-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        background: #fff;
        padding: 1rem;
        border-radius: 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.03);
    }

    .search-wrapper {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .library-search-input {
        width: 100%;
        padding: 0.65rem 1rem 0.65rem 2.5rem;
        border: 1px solid var(--gray-100);
        border-radius: 0.75rem;
        font-size: 0.875rem;
        outline: none;
        transition: all 0.2s;
    }

    .library-search-input:focus {
        border-color: var(--app-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .unit-select {
        padding: 0.65rem 2rem 0.65rem 1rem;
        border: 1px solid var(--gray-100);
        border-radius: 0.75rem;
        font-size: 0.875rem;
        outline: none;
        cursor: pointer;
        background: #fff;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='Wait, I'll just use a fontawesome chevron' %3E%3C/path%3E%3C/svg%3E");
    }

    .library-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .lesson-card {
        background: #fff;
        border-radius: 1.25rem;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .lesson-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .lesson-thumb-container {
        position: relative;
        aspect-ratio: 16/9;
        background: var(--gray-100);
    }

    .lesson-thumb-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lesson-duration-badge {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        background: rgba(0,0,0,0.7);
        color: #fff;
        padding: 0.2rem 0.5rem;
        border-radius: 0.4rem;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .lesson-card-info {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .lesson-meta-top {
        display: flex;
        gap: 0.5rem;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .meta-subject { color: var(--app-blue); }
    .meta-unit { color: var(--gray-400); }

    .lesson-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 1rem;
        line-height: 1.4;
        flex: 1;
    }

    .lesson-progress-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.7rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .progress-text { color: var(--gray-400); }
    .percent-text { color: var(--app-blue); }
    .lesson-card.completed .percent-text { color: #16a34a; }

    .lesson-card-progress {
        height: 6px;
        background: var(--gray-100);
        border-radius: 1rem;
        overflow: hidden;
    }

    .lesson-card-fill {
        height: 100%;
        background: var(--app-blue);
        transition: width 0.3s ease;
    }

    .lesson-card.completed .lesson-card-fill {
        background: #16a34a;
    }

    .load-more-container {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }

    .load-more-btn {
        background: #fff;
        border: 1px solid var(--gray-100);
        padding: 0.85rem 2rem;
        border-radius: 0.75rem;
        font-weight: 700;
        color: var(--gray-900);
        cursor: pointer;
        transition: all 0.2s;
    }

    .load-more-btn:hover {
        background: var(--gray-50);
        border-color: var(--gray-200);
    }

    /* ── Responsive adjustments ── */
    @media (max-width: 1024px) {
        .report-main-split { grid-template-columns: 1fr; }
        .library-container { grid-template-columns: 1fr; }
        .library-sidebar { display: none; } /* Hide sidebar on mobile for now or make it a horizontal list */
    }

    @media (max-width: 768px) {
        .summary-grid { grid-template-columns: 1fr; }
        .library-toolbar { flex-direction: column; }
        .search-wrapper { max-width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="report-page">
    {{-- Grade Navigation Tabs --}}
    <div class="report-tabs">
        @foreach($canonicalGrades as $grade)
            @php
                $isActive = $grade === $foundCanonical;
                $isUnlocked = in_array($grade, $unlockedGrades);
                $isUserActualGrade = strcasecmp($grade, auth()->user()->grade) === 0;
                
                $displayGrade = $isUserActualGrade ? $grade . ' (Current)' : $grade;
            @endphp
            <a href="{{ $isUnlocked ? route('dashboard.detailed-report', ['grade' => $grade]) : '#' }}" 
               class="report-tab {{ $isActive ? 'active' : '' }} {{ $isUnlocked ? '' : 'locked' }}"
               {!! $isUnlocked ? '' : 'title="Locked"' !!}>
                @if(!$isUnlocked) <i class="fas fa-lock"></i> @endif
                {{ $displayGrade }}
            </a>
        @endforeach
    </div>

    {{-- Performance Summary Section --}}
    <div class="section-header">
        <h2 class="section-title">Performance Summary</h2>
        <span class="section-subtitle">Academic Year 2023-2024</span>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="card-icon-wrapper ranking">
                <i class="fas fa-medal"></i>
            </div>
            <div class="card-content">
                <div class="card-label">Ranking Badge</div>
                <div class="card-value">{{ $performanceSummary['ranking'] }}</div>
                <div class="card-trend up"><i class="fas fa-chart-line"></i> {{ $performanceSummary['ranking_trend'] }}</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon-wrapper average">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="card-content">
                <div class="card-label">Grade Average</div>
                <div class="card-value">{{ $performanceSummary['grade_average'] }}%</div>
                <div class="card-trend up"><i class="fas fa-chart-line"></i> {{ $performanceSummary['average_trend'] }}</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon-wrapper time">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <div class="card-label">Total Study Time</div>
                <div class="card-value">{{ $performanceSummary['total_study_time'] }}</div>
                <div class="card-trend up"><i class="fas fa-chart-line"></i> {{ $performanceSummary['time_trend'] }}</div>
            </div>
        </div>
    </div>

    {{-- Split Main Content --}}
    <div class="report-main-split">
        {{-- Recent Quiz Scores --}}
        <div>
            <div class="section-header">
                <h2 class="section-title">Recent Quiz Scores</h2>
            </div>
            <div class="quizzes-card">
                <table class="quizzes-table">
                    <thead>
                        <tr>
                            <th>Quiz Name</th>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentQuizzes as $quiz)
                            @php
                                $scoreClass = $quiz->score >= 80 ? 'high' : ($quiz->score >= 60 ? 'med' : 'low');
                            @endphp
                            <tr>
                                <td class="quiz-name-cell">{{ $quiz->quiz_name }}</td>
                                <td class="quiz-subject-cell">{{ $quiz->subject }}</td>
                                <td>
                                    <span class="quiz-score-badge {{ $scoreClass }}">{{ round($quiz->score) }}%</span>
                                </td>
                                <td class="quiz-status-cell">{{ $quiz->completed_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-400">No quizzes attempted yet in this level.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <a href="{{ route('quiz.index') }}" class="view-all-link">View All Quiz History</a>
            </div>
        </div>

        {{-- Subject Mastery --}}
        <div>
            <div class="section-header">
                <h2 class="section-title">Subject Mastery</h2>
            </div>
            <div class="mastery-card">
                @php
                    $subjectIcons = [
                        'Mathematics' => ['icon' => 'fa-calculator', 'class' => 'math'],
                        'English' => ['icon' => 'fa-book', 'class' => 'eng'],
                        'Science' => ['icon' => 'fa-flask', 'class' => 'sci'],
                    ];
                @endphp
                @forelse($subjectMastery as $subject)
                    @php
                        $iconData = $subjectIcons[$subject->subject] ?? ['icon' => 'fa-graduation-cap', 'class' => 'math'];
                        $perc = round($subject->average_score);
                    @endphp
                    <div class="mastery-item">
                        <div class="mastery-info">
                            <div class="mastery-icon {{ $iconData['class'] }}">
                                <i class="fas {{ $iconData['icon'] }}"></i>
                            </div>
                            <span class="mastery-name">{{ $subject->subject }}</span>
                        </div>
                        <div class="circular-progress" style="--p: {{ $perc }}" data-percent="{{ $perc }}"></div>
                    </div>
                @empty
                    <div class="text-gray-400 py-4 text-center">Take quizzes to see mastery data.</div>
                @endforelse

                <div class="mastery-footer">
                    <div class="mastery-footer-label">Progress Overview</div>
                    <div class="footer-progress-bar">
                        @php
                            $overallMastery = $subjectMastery->avg('average_score') ?? 0;
                        @endphp
                        <div class="footer-progress-fill" style="width: {{ $overallMastery }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lesson Library Section --}}
    <div class="library-section">
        <div class="section-header">
            <h2 class="section-title">Lesson Library</h2>
            <span class="section-subtitle">Navigate through your academic content</span>
        </div>

        <div class="library-container">
            {{-- Subject Sidebar --}}
            <aside class="library-sidebar">
                <span class="sidebar-label">Subjects</span>
                <div class="subject-filters">
                    <button class="subject-btn active" data-subject="all">
                        <i class="fas fa-th-large fa-fw mr-2"></i> All Lessons
                    </button>
                    @foreach($librarySubjects as $subject)
                        <button class="subject-btn" data-subject="{{ $subject->name }}">
                            <i class="fas fa-book fa-fw mr-2"></i> {{ $subject->name }}
                        </button>
                    @endforeach
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="library-content">
                <div class="library-toolbar">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" class="library-search-input" placeholder="Search lessons..." id="lessonSearch">
                    </div>
                    <select class="unit-select" id="unitFilter">
                        <option value="all">All Units</option>
                        @foreach($libraryUnits as $unit)
                            <option value="{{ $unit }}">{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="library-grid" id="lessonGrid">
                    @foreach($libraryLessons as $lesson)
                        @php $isDone = $lesson->progress_percent >= 90; @endphp
                        <div class="lesson-card {{ $isDone ? 'completed' : '' }}" 
                             data-subject="{{ $lesson->subject->name ?? 'Unknown' }}" 
                             data-unit="{{ $lesson->unit_name ?? 'General' }}"
                             data-title="{{ strtolower($lesson->title) }}">
                            <div class="lesson-thumb-container">
                                <img src="{{ $lesson->thumbnail_url }}" alt="{{ $lesson->title }}">
                                <span class="lesson-duration-badge">{{ $lesson->duration_formatted }}</span>
                            </div>
                            <div class="lesson-card-info">
                                <div class="lesson-meta-top">
                                    <span class="meta-subject">{{ $lesson->subject->name ?? 'Subject' }}</span>
                                    <span class="meta-unit">{{ $lesson->unit_name ?? 'Unit' }}</span>
                                </div>
                                <h3 class="lesson-card-title">{{ $lesson->title }}</h3>
                                <div class="lesson-progress-row">
                                    <span class="progress-text">{{ $lesson->watch_time_formatted }} / {{ $lesson->duration_formatted }}</span>
                                    <span class="percent-text">{{ $lesson->progress_percent }}%</span>
                                </div>
                                <div class="lesson-card-progress">
                                    <div class="lesson-card-fill" style="width: {{ $lesson->progress_percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="load-more-container" id="loadMoreSection">
                    <button class="load-more-btn" id="loadMoreBtn">
                        Load More Lessons (<span id="remainingCount">{{ count($libraryLessons) }}</span> remaining)
                    </button>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lessonSearch = document.getElementById('lessonSearch');
    const unitFilter = document.getElementById('unitFilter');
    const subjectBtns = document.querySelectorAll('.subject-btn');
    const lessonCards = document.querySelectorAll('.lesson-card');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const remainingCountSpan = document.getElementById('remainingCount');
    
    let activeSubject = 'all';
    let currentLimit = 12;

    function applyFilters() {
        const searchTerm = lessonSearch.value.toLowerCase();
        const selectedUnit = unitFilter.value;
        let visibleCount = 0;
        let totalMatches = 0;

        lessonCards.forEach(card => {
            const cardSubject = card.getAttribute('data-subject');
            const cardUnit = card.getAttribute('data-unit');
            const cardTitle = card.getAttribute('data-title');

            const subjectMatch = activeSubject === 'all' || cardSubject === activeSubject;
            const unitMatch = selectedUnit === 'all' || cardUnit === selectedUnit;
            const searchMatch = cardTitle.includes(searchTerm);

            if (subjectMatch && unitMatch && searchMatch) {
                totalMatches++;
                if (visibleCount < currentLimit) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Update Load More visibility
        const remaining = totalMatches - visibleCount;
        const loadMoreSection = document.getElementById('loadMoreSection');
        if (remaining > 0) {
            loadMoreSection.style.display = 'flex';
            remainingCountSpan.textContent = remaining;
        } else {
            loadMoreSection.style.display = 'none';
        }
    }

    lessonSearch.addEventListener('input', () => {
        currentLimit = 12;
        applyFilters();
    });

    unitFilter.addEventListener('change', () => {
        currentLimit = 12;
        applyFilters();
    });

    subjectBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            subjectBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeSubject = btn.getAttribute('data-subject');
            currentLimit = 12;
            applyFilters();
        });
    });

    loadMoreBtn.addEventListener('click', () => {
        currentLimit += 12;
        applyFilters();
    });

    // Initial Filter
    applyFilters();
});
</script>
@endsection
