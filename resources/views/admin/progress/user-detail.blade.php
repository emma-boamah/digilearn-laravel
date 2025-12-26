@extends('layouts.admin')

@section('title', 'Student Progress Details - ' . $user->name)
@section('page-title', 'Student Progress Details')
@section('page-description', 'Detailed progress information for ' . $user->name)

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Enhanced styling for user detail page */
    .detail-container {
        padding: 2rem 0;
    }

    .header-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 2rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .header-avatar {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 2rem;
        flex-shrink: 0;
    }

    .header-info h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .header-info p {
        color: #6b7280;
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
    }

    .header-right {
        text-align: right;
    }

    .header-right-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    .header-right-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
        margin-top: 0.25rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .summary-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .summary-card-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .summary-card-icon {
        font-size: 1.5rem;
    }

    .summary-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.75rem;
    }

    .summary-card-detail {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .progress-bar-container {
        margin-top: 1rem;
    }

    .progress-bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #2563eb);
        border-radius: 4px;
    }

    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .analytics-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .analytics-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1.25rem;
    }

    .metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .metric-row:last-child {
        border-bottom: none;
    }

    .metric-label {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .metric-value {
        font-weight: 700;
        color: #111827;
        font-size: 0.875rem;
    }

    .metric-value.orange {
        color: #f97316;
    }

    .metric-value.green {
        color: #10b981;
    }

    .achievement-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .achievement-item {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 10px;
        padding: 1rem;
        border: 1px solid #fcd34d;
        text-align: center;
    }

    .achievement-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .achievement-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 0.25rem;
    }

    .achievement-desc {
        font-size: 0.75rem;
        color: #b45309;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .history-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }

    .history-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1.5rem;
    }

    .history-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .history-item:last-child {
        margin-bottom: 0;
    }

    .history-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .history-item-title {
        font-weight: 700;
        color: #111827;
    }

    .history-item-date {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .history-item-completion {
        text-align: right;
    }

    .history-item-completion-label {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .history-item-completion-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }

    .history-item-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .history-item-stat {
        font-size: 0.875rem;
    }

    .history-item-stat-label {
        color: #6b7280;
    }

    .history-item-stat-value {
        font-weight: 700;
        color: #111827;
        margin-top: 0.25rem;
    }

    .history-item-status {
        padding-top: 1rem;
        border-top: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }

    .status-badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-ready {
        background: #bfdbfe;
        color: #1e40af;
    }

    .status-in-progress {
        background: #fed7aa;
        color: #92400e;
    }

    .progression-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .progression-item {
        display: flex;
        gap: 1rem;
    }

    .progression-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #d1fae5;
        color: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .progression-content {
        flex: 1;
    }

    .progression-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .progression-title {
        font-weight: 700;
        color: #111827;
    }

    .progression-date {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .progression-details {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }

    .action-banner {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 1px solid #6ee7b7;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .action-banner-content h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #065f46;
        margin: 0;
    }

    .action-banner-content p {
        color: #047857;
        margin: 0.5rem 0 0 0;
        font-size: 0.875rem;
    }

    .btn {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    @media (max-width: 768px) {
        .header-card {
            flex-direction: column;
            text-align: center;
        }

        .header-right {
            text-align: center;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .analytics-grid {
            grid-template-columns: 1fr;
        }

        .achievement-grid {
            grid-template-columns: 1fr;
        }

        .action-banner {
            flex-direction: column;
            text-align: center;
        }

        .history-item-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="detail-container">
    <!-- Student Info Header -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-avatar">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="header-info">
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <p>Grade: {{ $user->grade ?? 'Not set' }}</p>
            </div>
        </div>
        <div class="header-right">
            <div class="header-right-label">Joined</div>
            <div class="header-right-value">{{ $user->created_at->format('M d, Y') }}</div>
        </div>
    </div>

    @if($currentProgress)
    <!-- Current Progress Summary -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-header">
                <div class="summary-card-title">Current Level</div>
                <div class="summary-card-icon">📚</div>
            </div>
            <div class="summary-card-value">{{ ucwords(str_replace('-', ' ', $currentProgress->current_level)) }}</div>
            <div class="progress-bar-container">
                <div class="progress-bar-label">
                    <span>Progress</span>
                    <span>{{ number_format($currentProgress->completion_percentage, 1) }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: {{ $currentProgress->completion_percentage }}%"></div>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-header">
                <div class="summary-card-title">Lessons Completed</div>
                <div class="summary-card-icon">🎬</div>
            </div>
            <div class="summary-card-value">{{ $currentProgress->completed_lessons }}/{{ $currentProgress->total_lessons_in_level }}</div>
            <div class="summary-card-detail">
                Time spent: <strong>{{ $analytics['engagement']['time_spent_formatted'] ?? '0m' }}</strong>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-header">
                <div class="summary-card-title">Quiz Performance</div>
                <div class="summary-card-icon">✍️</div>
            </div>
            <div class="summary-card-value">{{ min($currentProgress->completed_quizzes, $currentProgress->total_quizzes_in_level) }}/{{ $currentProgress->total_quizzes_in_level }}</div>
            <div class="summary-card-detail">
                Average score: <strong>{{ number_format($currentProgress->average_quiz_score, 1) }}%</strong>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    @if(isset($analytics))
    <div class="analytics-grid">
        <!-- Engagement Metrics -->
        <div class="analytics-card">
            <h3 class="analytics-card-title">Engagement Metrics</h3>
            <div class="metric-row">
                <span class="metric-label">Learning Streak</span>
                <span class="metric-value orange">{{ $analytics['engagement']['current_streak'] ?? 0 }} days</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Best Streak</span>
                <span class="metric-value green">{{ $analytics['engagement']['longest_streak'] ?? 0 }} days</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Time in Level</span>
                <span class="metric-value">{{ $analytics['level_info']['duration'] ?? 'Just started' }}</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Last Activity</span>
                <span class="metric-value">{{ $currentProgress->last_activity_at ? $currentProgress->last_activity_at->diffForHumans() : 'Never' }}</span>
            </div>
        </div>

        <!-- Achievement Milestones -->
        <div class="analytics-card">
            <h3 class="analytics-card-title">Achievements</h3>
            @if(isset($analytics['milestones']) && count($analytics['milestones']) > 0)
                <div class="achievement-grid">
                    @foreach($analytics['milestones'] as $milestone)
                        <div class="achievement-item">
                            <div class="achievement-icon">{{ $milestone['icon'] }}</div>
                            <div class="achievement-title">{{ $milestone['title'] }}</div>
                            <div class="achievement-desc">
                                @if($milestone['type'] === 'lessons')
                                    {{ $milestone['count'] }} lessons
                                @elseif($milestone['type'] === 'quizzes')
                                    @if($milestone['count'] >= 80)
                                        {{ $milestone['count'] }}% score
                                    @else
                                        {{ $milestone['count'] }} quizzes
                                    @endif
                                @elseif($milestone['type'] === 'streak')
                                    {{ $milestone['count'] }}-day streak
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">🏆</div>
                    <p>No achievements yet. Keep learning!</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Progress History -->
    <div class="history-card">
        <h3 class="history-card-title">Progress History</h3>
        @if($progressRecords->count() > 0)
            <div>
                @foreach($progressRecords as $progress)
                    <div class="history-item">
                        <div class="history-item-header">
                            <div>
                                <div class="history-item-title">{{ ucwords(str_replace('-', ' ', $progress->current_level)) }}</div>
                                <div class="history-item-date">{{ $progress->level_started_at ? 'Started ' . $progress->level_started_at->format('M d, Y') : 'Not started' }}</div>
                            </div>
                            <div class="history-item-completion">
                                <div class="history-item-completion-label">Completion</div>
                                <div class="history-item-completion-value">{{ number_format($progress->completion_percentage, 1) }}%</div>
                            </div>
                        </div>

                        <div class="history-item-grid">
                            <div class="history-item-stat">
                                <div class="history-item-stat-label">Lessons</div>
                                <div class="history-item-stat-value">{{ $progress->completed_lessons }}/{{ $progress->total_lessons_in_level }}</div>
                            </div>
                            <div class="history-item-stat">
                                <div class="history-item-stat-label">Quizzes</div>
                                <div class="history-item-stat-value">{{ min($progress->completed_quizzes, $progress->total_quizzes_in_level) }}/{{ $progress->total_quizzes_in_level }}</div>
                            </div>
                            <div class="history-item-stat">
                                <div class="history-item-stat-label">Avg Score</div>
                                <div class="history-item-stat-value">{{ number_format($progress->average_quiz_score, 1) }}%</div>
                            </div>
                            <div class="history-item-stat">
                                <div class="history-item-stat-label">Status</div>
                                <div class="history-item-stat-value">
                                    @if($progress->level_completed)
                                        <span class="status-badge status-completed">Completed</span>
                                    @elseif($progress->eligible_for_next_level)
                                        <span class="status-badge status-ready">Ready</span>
                                    @else
                                        <span class="status-badge status-in-progress">In Progress</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($progress->level_completed_at)
                            <div class="history-item-status">
                                Completed on {{ $progress->level_completed_at->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <p>No progress records found for this student.</p>
            </div>
        @endif
    </div>

    <!-- Level Progression History -->
    @if($progressionHistory && $progressionHistory->count() > 0)
    <div class="history-card">
        <h3 class="history-card-title">Level Progression History</h3>
        <div class="progression-timeline">
            @foreach($progressionHistory as $progression)
                <div class="progression-item">
                    <div class="progression-icon">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="progression-content">
                        <div class="progression-header">
                            <span class="progression-title">
                                {{ ucwords(str_replace('-', ' ', $progression->from_level)) }}
                                <span style="color: #6b7280;">→</span>
                                {{ ucwords(str_replace('-', ' ', $progression->to_level)) }}
                            </span>
                            <span class="progression-date">{{ $progression->progressed_at->format('M d, Y') }}</span>
                        </div>
                        <div class="progression-details">
                            {{ $progression->lessons_completed }} lessons • {{ $progression->quizzes_passed }} quizzes passed • Final score: {{ number_format($progression->final_score, 1) }}%
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Manual Progression Action -->
    @if($currentProgress && $currentProgress->eligible_for_next_level)
        <div class="action-banner">
            <div class="action-banner-content">
                <h3>Ready for Progression</h3>
                <p>This student has met all requirements and is ready to move to the next level.</p>
            </div>
            <form action="{{ route('admin.progress.user.progress', $user->id) }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="from_level" value="{{ $currentProgress->current_level }}">
                <input type="hidden" name="to_level" value="{{ ['primary-lower' => 'primary-upper', 'primary-upper' => 'jhs', 'jhs' => 'shs', 'shs' => null][$currentProgress->current_level] }}">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-arrow-up"></i> Progress to Next Level
                </button>
            </form>
        </div>
    @endif
    @endif
</div>
@endsection
