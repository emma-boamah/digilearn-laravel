@extends('layouts.dashboard-components')

@section('title', 'My Progress')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    :root {
        --app-blue: #2677B8;
        --app-blue-light: #3b8ccb;
        --app-blue-dark: #1e5a8a;
        --success-green: #10b981;
    }

    /* ── Page wrapper ── */
    .progress-page {
        padding: 2rem 1.5rem;
        max-width: 100%;
        margin-top: 60px;
        margin-left: 0;
        margin-right: 0;
    }

    /* ── Page header row ── */
    .progress-page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 1.75rem;
    }

    /* ── Hero Progress Section ── */
    .hero-section {
        background: linear-gradient(135deg, var(--app-blue), var(--app-blue-light));
        border-radius: 1.5rem;
        padding: 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .hero-section::after {
        content: '';
        position: absolute;
        top: -20%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 2rem;
        padding: 0.4rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
    }

    .hero-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.65rem;
        letter-spacing: -0.02em;
    }

    .hero-subtitle {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        max-width: 500px;
    }

    .hero-content-flex {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .hero-progress-wrapper {
        min-width: 240px;
        flex: 0 0 320px;
        position: relative;
        padding-top: 2.5rem; /* room for the big % number */
        z-index: 1;
    }

    .hero-progress-label {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .hero-progress-percent {
        font-size: 2.25rem;
        font-weight: 800;
        position: absolute;
        top: 0;
        right: 0;
    }

    .hero-progress-bar {
        width: 100%;
        height: 12px;
        background: rgba(255,255,255,0.2);
        border-radius: 1rem;
        overflow: hidden;
    }

    .hero-progress-fill {
        height: 100%;
        background: var(--success-green);
        border-radius: 1rem;
        transition: width 1s ease-out;
    }

    /* ── Performance Metrics ── */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .metric-tile {
        background: #fff;
        padding: 1.125rem 1.25rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.03); /* Extremely subtle border */
    }

    .metric-icon {
        width: 42px;
        height: 42px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .metric-label {
        font-size: 0.78rem;
        color: var(--gray-500);
        font-weight: 600;
        margin-bottom: 0.15rem;
    }

    .metric-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .icon-lessons { color: var(--app-blue);     background: rgba(38,119,184,0.1); }
    .icon-score   { color: #ef4444;              background: rgba(239,68,68,0.1);  }
    .icon-time    { color: #eab308;              background: rgba(234,179,8,0.1);  }
    .icon-streak  { color: #f97316;              background: rgba(249,115,22,0.1); }

    /* ── Section header ── */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .view-report-link {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--app-blue);
        text-decoration: none;
    }
    .view-report-link:hover { text-decoration: underline; }

    /* ══════════════════════════════════════════════
       INDIVIDUAL GRADE PROGRESS — Negative-space layout
       Cards are separated purely by white space and
       column rhythm. No borders, no shadows, no dividers.
       ══════════════════════════════════════════════ */
    .grades-panel {
        /* Transparent — sits directly on page background */
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        column-gap: 2.5rem;   /* the "breathing room" between columns */
        margin-bottom: 3rem;
    }

    /* Each grade column — Elevated Card Style */
    .grade-col {
        background: #fff;
        padding: 1.25rem;
        border-radius: 1rem;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .grade-col:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    /* Locked grades: muted palette signals "not yet" */
    .grade-col.locked {
        opacity: 0.7;
        background: rgba(255,255,255,0.6);
    }

    /* ── Grade header ── */
    .grade-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 0.5rem;
        /* A thin accent line at the top acts as the card's "roof" —
           this single border replaces the whole bounding box */
        padding-top: 0.75rem;
        border-top: 3px solid var(--gray-200);
    }
    .grade-col.completed .grade-card-header  { border-top-color: var(--success-green); }
    .grade-col.in-progress .grade-card-header { border-top-color: var(--app-blue); }
    .grade-col.locked .grade-card-header     { border-top-color: var(--gray-200); }

    .grade-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
    }
    .grade-col.locked .grade-name { color: var(--gray-500); }

    .grade-subtitle {
        font-size: 0.75rem;
        color: var(--gray-400);
        font-weight: 500;
        margin-top: 0.15rem;
    }

    /* Status label — plain coloured text, no pill or background */
    .status-badge {
        font-size: 0.62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        white-space: nowrap;
        background: none;
        border: none;
        padding: 0;
    }
    .status-badge.completed   { color: #16a34a; }
    .status-badge.in-progress { color: var(--app-blue); }
    .status-badge.locked      { color: var(--gray-400); }

    /* ── Stat rows ── */
    .grade-stat-row { margin-top: 0.6rem; }

    .stat-label-flex {
        display: flex;
        justify-content: space-between;
        font-size: 0.825rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .stat-label { color: var(--gray-500); }
    .stat-value { color: var(--gray-900); font-weight: 700; }
    .grade-col.locked .stat-value { color: var(--gray-400); }

    /* ── Progress bars ── */
    .mini-progress-bar {
        height: 5px;
        background: var(--gray-100);
        border-radius: 1rem;
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .mini-progress-fill                               { height: 100%; background: var(--gray-200); border-radius: 1rem; }
    .grade-col.completed   .mini-progress-fill        { background: var(--success-green); }
    .grade-col.in-progress .mini-progress-fill.lessons { background: var(--app-blue); }

    /* Stack columns on mobile */
    @media (max-width: 700px) {
        .grades-panel { grid-template-columns: 1fr; row-gap: 2rem; }
        .grade-card-header { padding-top: 0.5rem; }
    }

    /* ── Learning Pathway ── */
    .pathway-section { margin-bottom: 2rem; }

    .pathway-container {
        position: relative;
        background: #fff;
        padding: 2rem 1.5rem 2.5rem;
        border-radius: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.03);
    }

    .pathway-line {
        position: absolute;
        /* center of the 58px icons: padding-top(16px) + icon-center(29px) = 45px */
        top: calc(1rem + 29px);
        left: 6.25%;   /* roughly half of each 25%-wide step from the edge */
        right: 6.25%;
        height: 3px;
        background: var(--gray-200);
        z-index: 0;
    }

    .pathway-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        z-index: 1;
    }

    .pathway-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 25%;
    }

    .step-icon {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #fff;
        border: 4px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 0.9rem;
        transition: all 0.3s ease;
    }

    .pathway-step.active .step-icon {
        border-color: #bfdbfe;
        color: var(--app-blue);
        box-shadow: 0 0 0 8px rgba(38,119,184,0.1);
    }

    .pathway-step.completed .step-icon {
        background: var(--success-green);
        border-color: var(--success-green);
        color: #fff;
    }

    .pathway-step.future .step-icon {
        border-style: dashed;
        color: var(--gray-300);
    }

    .step-title  { font-size: 0.88rem; font-weight: 700; color: var(--gray-700); margin-bottom: 0.2rem; }
    .step-status { font-size: 0.73rem; font-weight: 700; text-transform: uppercase; }

    .pathway-step.completed  .step-status { color: var(--success-green); }
    .pathway-step.active     .step-status { color: var(--app-blue); }
    .pathway-step.milestone  .step-status { color: var(--gray-500); }
    .pathway-step.future     .step-status { color: var(--gray-400); }

    /* ── Grade Switcher ── */
    .report-tabs {
        display: flex;
        gap: 1.25rem;
        background: #fff;
        padding: 0.75rem 1.25rem;
        border-radius: 1rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        border: 1px solid rgba(0,0,0,0.03);
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .report-tabs::-webkit-scrollbar { display: none; }

    .report-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-500);
        text-decoration: none;
        border-radius: 2rem;
        white-space: nowrap;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }

    .report-tab:hover:not(.locked) {
        color: var(--app-blue);
        background: rgba(38,119,184,0.05);
    }

    .report-tab.active {
        color: var(--app-blue);
        background: rgba(38,119,184,0.1);
        border-color: var(--app-blue);
    }

    .report-tab.locked {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
        background: var(--gray-50);
    }

    .report-tab i { font-size: 0.75rem; }

    /* ── Animations ── */
    @keyframes fillUp {
        from { width: 0; }
        to   { width: var(--w); }
    }
    .animate-fill { animation: fillUp 1.4s cubic-bezier(0.1,0.5,0.1,1) forwards; }

    /* ── Mobile ── */
    @media (max-width: 640px) {
        .progress-page   { padding: 1rem; }
        .hero-title      { font-size: 1.5rem; }
        .hero-content-flex { flex-direction: column; }
        .hero-progress-wrapper { flex: 1 1 100%; padding-top: 0; }
        .hero-progress-percent { position: static; font-size: 1.75rem; margin-bottom: 0.5rem; }
        .pathway-steps   { flex-wrap: wrap; gap: 1.5rem; }
        .pathway-step    { width: 45%; }
        .pathway-line    { display: none; }
    }
</style>
@endpush

@section('content')
<div class="progress-page">

    <h1 class="progress-page-title">My Progress Dashboard</h1>

    {{-- Hero Progress Section --}}
    <div class="hero-section">
        <div class="hero-badge">Active Level</div>
        <div class="hero-content-flex">
            <div class="hero-text">
                @php
                    $levelDisplayName = ucwords(str_replace('-', ' ', $currentLevel));
                    if ($currentLevel === 'primary-upper') $levelDisplayName = 'Primary Upper / Grades 4-6';
                    elseif ($currentLevel === 'primary-lower') $levelDisplayName = 'Primary Lower / Grades 1-3';
                    elseif ($currentLevel === 'jhs') $levelDisplayName = 'JHS / Grades 7-9';
                    elseif ($currentLevel === 'shs') $levelDisplayName = 'SHS / Grades 10-12';
                @endphp
                <h2 class="hero-title">{{ $levelDisplayName }}</h2>
                <p class="hero-subtitle">
                    You're making great progress! Keep going to unlock
                    {{ $progressionStatus['next_level'] ? ucwords(str_replace('-', ' ', $progressionStatus['next_level'])) . ' level.' : 'the next level.' }}
                </p>
            </div>

            <div class="hero-progress-wrapper">
                <div class="hero-progress-percent">{{ round($progress->completion_percentage ?? 0) }}%</div>
                <div class="hero-progress-label">Overall Completion</div>
                <div class="hero-progress-bar">
                    <div class="hero-progress-fill animate-fill"
                         style="--w: {{ $progress->completion_percentage ?? 0 }}%;
                                width: {{ $progress->completion_percentage ?? 0 }}%;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Metrics --}}
    <div class="metrics-grid">
        <div class="metric-tile">
            <div class="metric-icon icon-lessons"><i class="fas fa-play-circle"></i></div>
            <div>
                <div class="metric-label">Lessons Completed</div>
                <div class="metric-value">{{ $progress->completed_lessons ?? 0 }} / {{ $progress->total_lessons_in_level ?? '?' }}</div>
            </div>
        </div>

        <div class="metric-tile">
            <div class="metric-icon icon-score"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="metric-label">Avg Quiz Score</div>
                <div class="metric-value">{{ round($progress->average_quiz_score ?? 0) }}%</div>
            </div>
        </div>

        <div class="metric-tile">
            <div class="metric-icon icon-time"><i class="fas fa-clock"></i></div>
            <div>
                <div class="metric-label">Time Invested</div>
                <div class="metric-value">{{ $analytics['engagement']['time_spent_formatted'] ?? '0m' }}</div>
            </div>
        </div>

        <div class="metric-tile">
            <div class="metric-icon icon-streak"><i class="fas fa-fire"></i></div>
            <div>
                <div class="metric-label">Learning Streak</div>
                <div class="metric-value">{{ $analytics['engagement']['current_streak'] ?? 0 }} Days</div>
            </div>
        </div>
    </div>

    {{-- Grade Navigation Tabs --}}
    <div class="report-tabs">
        @foreach($canonicalGrades as $grade)
            @php
                $isUnlocked = in_array($grade, $unlockedGrades);
                $isUserGrade = strcasecmp($grade, auth()->user()->grade) === 0;
            @endphp
            <a href="{{ $isUnlocked ? route('dashboard.digilearn', ['grade' => $grade]) : '#' }}" 
               class="report-tab {{ $isUnlocked ? '' : 'locked' }} {{ $isUserGrade ? 'active' : '' }}"
               {!! $isUnlocked ? '' : 'title="Locked"' !!}>
                @if(!$isUnlocked) <i class="fas fa-lock"></i> @endif
                {{ $grade }}
            </a>
        @endforeach
    </div>

    {{-- Individual Grade Progress --}}
    <div class="section-header">
        <h2 class="section-title">Individual Grade Progress</h2>
        <a href="{{ route('dashboard.detailed-report') }}" class="view-report-link">View Detailed Report</a>
    </div>

    <div class="grades-panel">
        @foreach($gradeStats as $grade => $stats)
            @php
                $isUnlocked = in_array($grade, $unlockedGrades);
                $statusClass = $stats['is_completed'] ? 'completed' : ($stats['is_in_progress'] ? 'in-progress' : 'locked');
                if (!$isUnlocked) $statusClass = 'locked';
                
                $statusLabel = $stats['is_completed'] ? 'Completed' : ($stats['is_in_progress'] ? 'In Progress' : 'Locked');
                if (!$isUnlocked) $statusLabel = 'Locked';

                $subtitles = [
                    'Grade 4' => 'Lower Transition',
                    'Grade 5' => 'Current Focus',
                    'Grade 6' => 'Final Stage',
                    'Grade 1' => 'Foundation',
                    'Grade 2' => 'Building Blocks',
                    'Grade 3' => 'Upper Foundation',
                    'Grade 7' => 'Junior High Entry',
                    'Grade 8' => 'Middle Stage',
                    'Grade 9' => 'Final JHS',
                ];
                $subtitle = $subtitles[$grade] ?? '';
                $isUnlocked = in_array($grade, $unlockedGrades);
            @endphp
            <a href="{{ $isUnlocked ? route('dashboard.digilearn', ['grade' => $grade]) : '#' }}" 
               class="grade-col {{ $statusClass }}" 
               style="text-decoration: none; display: block;"
               @if(!$isUnlocked) title="Locked" @endif>
                <div class="grade-card-header">
                    <div>
                        <div class="grade-name">
                            @if(!$isUnlocked) <i class="fas fa-lock" style="font-size: 0.8rem; margin-right: 0.25rem;"></i> @endif
                            {{ $grade }}
                        </div>
                        @if($subtitle)
                            <div class="grade-subtitle">{{ $subtitle }}</div>
                        @endif
                    </div>
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                {{-- 1. Lessons Attempted --}}
                @php $attemptedLessonPerc = $stats['total_lessons'] > 0 ? min(100, ($stats['attempted_lessons'] / $stats['total_lessons']) * 100) : 0; @endphp
                <div class="grade-stat-row">
                    <div class="stat-label-flex">
                        <span class="stat-label">Lessons Attempted</span>
                        <span class="stat-value">{{ $stats['attempted_lessons'] }}/{{ $stats['total_lessons'] }}</span>
                    </div>
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill lessons animate-fill"
                             style="--w: {{ $attemptedLessonPerc }}%; width: {{ $attemptedLessonPerc }}%;"></div>
                    </div>
                </div>

                {{-- 2. Fully Watched Lessons --}}
                @php $completedLessonPerc = $stats['total_lessons'] > 0 ? min(100, ($stats['completed_lessons'] / $stats['total_lessons']) * 100) : 0; @endphp
                <div class="grade-stat-row">
                    <div class="stat-label-flex">
                        <span class="stat-label">Fully Watched</span>
                        <span class="stat-value">{{ $stats['completed_lessons'] }}/{{ $stats['total_lessons'] }}</span>
                    </div>
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill lessons animate-fill"
                             style="--w: {{ $completedLessonPerc }}%; width: {{ $completedLessonPerc }}%; background: #16a34a;"></div>
                    </div>
                </div>

                {{-- 3. Quizzes Attempted --}}
                @php $attemptedQuizPerc = $stats['total_quizzes'] > 0 ? min(100, ($stats['attempted_quizzes'] / $stats['total_quizzes']) * 100) : 0; @endphp
                <div class="grade-stat-row">
                    <div class="stat-label-flex">
                        <span class="stat-label">Quizzes Attempted</span>
                        <span class="stat-value">{{ $stats['attempted_quizzes'] }}/{{ $stats['total_quizzes'] }}</span>
                    </div>
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill animate-fill"
                             style="--w: {{ $attemptedQuizPerc }}%; width: {{ $attemptedQuizPerc }}%;"></div>
                    </div>
                </div>

                {{-- 4. Fully Completed Quizzes --}}
                @php $passedQuizPerc = $stats['total_quizzes'] > 0 ? min(100, ($stats['passed_quizzes'] / $stats['total_quizzes']) * 100) : 0; @endphp
                <div class="grade-stat-row">
                    <div class="stat-label-flex">
                        <span class="stat-label">Fully Completed</span>
                        <span class="stat-value">{{ $stats['passed_quizzes'] }}/{{ $stats['total_quizzes'] }}</span>
                    </div>
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill animate-fill"
                             style="--w: {{ $passedQuizPerc }}%; width: {{ $passedQuizPerc }}%; background: #16a34a;"></div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Learning Pathway --}}
    <div class="pathway-section">
        <div class="section-title">Learning Pathway</div>
        <div class="pathway-container">
            <div class="pathway-line"></div>
            <div class="pathway-steps">
                @php
                    $allLevels = [
                        ['id' => 'primary-lower', 'title' => 'Primary Lower'],
                        ['id' => 'primary-upper', 'title' => 'Primary Upper'],
                        ['id' => 'jhs',           'title' => 'JHS 1-3'],
                        ['id' => 'shs',           'title' => 'SHS & University'],
                    ];
                    $levelOrder = ['primary-lower' => 0, 'primary-upper' => 1, 'jhs' => 2, 'shs' => 3];
                    $currentIndex = $levelOrder[$currentLevel] ?? 0;
                @endphp

                @foreach($allLevels as $levelItem)
                    @php
                        $itemIndex    = $levelOrder[$levelItem['id']];
                        $isCompleted  = $itemIndex < $currentIndex;
                        $isCurrent    = $levelItem['id'] === $currentLevel;
                        $isFutureLast = $levelItem['id'] === 'shs' && !$isCurrent && !$isCompleted;

                        $stepClass  = $isCompleted ? 'completed' : ($isCurrent ? 'active' : ($isFutureLast ? 'future' : 'milestone'));
                        $statusText = $isCompleted ? 'Completed' : ($isCurrent ? 'Current Level' : ($isFutureLast ? 'Future' : 'Next Milestone'));

                        if ($isCompleted)                  $icon = 'fas fa-check';
                        elseif ($isCurrent)                $icon = 'fas fa-book-open';
                        elseif ($levelItem['id'] === 'jhs') $icon = 'fas fa-bolt';
                        else                               $icon = 'fas fa-flask';
                    @endphp
                    <div class="pathway-step {{ $stepClass }}">
                        <div class="step-icon"><i class="{{ $icon }}"></i></div>
                        <div class="step-title">{{ $levelItem['title'] }}</div>
                        <div class="step-status">{{ $statusText }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
