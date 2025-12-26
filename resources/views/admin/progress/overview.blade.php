@extends('layouts.admin')

@section('title', 'Student Progress Overview')
@section('page-title', 'Student Progress Overview')
@section('page-description', 'Monitor and manage student learning progress')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Enhanced styling for overview page */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .stat-card.green::before {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .stat-card.purple::before {
        background: linear-gradient(90deg, #8b5cf6, #a78bfa);
    }

    .stat-card.orange::before {
        background: linear-gradient(90deg, #f97316, #fb923c);
    }

    .stat-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: rgba(59, 130, 246, 0.1);
    }

    .stat-card.green .stat-icon {
        background: rgba(16, 185, 129, 0.1);
    }

    .stat-card.purple .stat-icon {
        background: rgba(139, 92, 246, 0.1);
    }

    .stat-card.orange .stat-icon {
        background: rgba(249, 115, 22, 0.1);
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-select {
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
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

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    .btn-settings {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
    }

    .btn-settings:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .table-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    th {
        padding: 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.2s;
    }

    tbody tr:hover {
        background-color: #f9fafb;
    }

    td {
        padding: 1rem;
        font-size: 0.875rem;
        color: #374151;
    }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .student-info h4 {
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .student-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0.25rem 0 0 0;
    }

    .progress-bar {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .progress-track {
        flex: 1;
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #2563eb);
        border-radius: 3px;
    }

    .progress-text {
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        min-width: 40px;
        text-align: right;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-ready {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-learning {
        background: #fef3c7;
        color: #92400e;
    }

    .action-links {
        display: flex;
        gap: 1rem;
    }

    .action-link {
        color: #3b82f6;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        transition: color 0.2s;
    }

    .action-link:hover {
        color: #2563eb;
    }

    .action-link.progress {
        color: #10b981;
    }

    .action-link.progress:hover {
        color: #059669;
    }

    .pagination-wrapper {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
        padding: 1rem;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        transition: color 0.2s;
    }

    .modal-close:hover {
        color: #111827;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .btn-cancel {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-cancel:hover {
        background: #d1d5db;
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
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
        }

        .filter-actions .btn {
            width: 100%;
            justify-content: center;
        }

        th, td {
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
        }

        .action-links {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>

<div style="padding: 2rem 0;">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($stats['total_students']) }}</div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($stats['eligible_students']) }}</div>
                    <div class="stat-label">Ready to Progress</div>
                </div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($stats['completed_levels']) }}</div>
                    <div class="stat-label">Levels Completed</div>
                </div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($stats['active_students']) }}</div>
                    <div class="stat-label">Active This Week</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filter-card">
        <form action="{{ route('admin.progress.overview') }}" method="GET" id="filterForm">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="search" class="form-label">Search Student</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or email" class="form-input">
                </div>

                <div class="form-group">
                    <label for="level_group" class="form-label">Level Group</label>
                    <select name="level_group" id="level_group" class="form-select">
                        <option value="">All Levels</option>
                        @foreach($levelGroups as $key => $label)
                            <option value="{{ $key }}" {{ request('level_group') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="eligibility" class="form-label">Progress Status</label>
                    <select name="eligibility" id="eligibility" class="form-select">
                        <option value="">All Students</option>
                        <option value="eligible" {{ request('eligibility') == 'eligible' ? 'selected' : '' }}>Ready to Progress</option>
                        <option value="not_eligible" {{ request('eligibility') == 'not_eligible' ? 'selected' : '' }}>Still Learning</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions" style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('admin.progress.overview') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
                <a href="{{ route('admin.progress.standards') }}" class="btn btn-settings" style="margin-left: auto;">
                    <i class="fas fa-cog"></i> Progression Standards
                </a>
            </div>
        </form>
    </div>

    <!-- Progress Table -->
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Student Progress Records</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Current Level</th>
                        <th>Lessons</th>
                        <th>Quizzes</th>
                        <th>Avg Score</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($progressRecords as $progress)
                        <tr>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        {{ substr($progress->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="student-info">
                                        <h4>{{ $progress->user->name ?? 'Unknown' }}</h4>
                                        <p>{{ $progress->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ ucwords(str_replace('-', ' ', $progress->current_level)) }}</div>
                                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">{{ $levelGroups[$progress->level_group] ?? $progress->level_group }}</div>
                            </td>
                            <td>{{ $progress->completed_lessons }}/{{ $progress->total_lessons_in_level }}</td>
                            <td>{{ min($progress->completed_quizzes, $progress->total_quizzes_in_level) }}/{{ $progress->total_quizzes_in_level }}</td>
                            <td>{{ number_format($progress->average_quiz_score, 1) }}%</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: {{ $progress->completion_percentage }}%"></div>
                                    </div>
                                    <div class="progress-text">{{ number_format($progress->completion_percentage, 0) }}%</div>
                                </div>
                            </td>
                            <td>
                                @if($progress->eligible_for_next_level)
                                    <span class="badge badge-ready">
                                        <i class="fas fa-check-circle"></i> Ready
                                    </span>
                                @else
                                    <span class="badge badge-learning">
                                        <i class="fas fa-clock"></i> Learning
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="{{ route('admin.progress.user.detail', $progress->user_id) }}" class="action-link">View Details</a>
                                    @if($progress->eligible_for_next_level)
                                        <button type="button" class="action-link progress" data-user-id="{{ $progress->user_id }}" data-current-level="{{ $progress->current_level }}" onclick="handleProgressClick(event)">Progress</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                No progress records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $progressRecords->links() }}
        </div>
    </div>
</div>

<!-- Manual Progress Modal -->
<div id="progressModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Progress Student</h3>
            <button type="button" class="modal-close" onclick="closeProgressModal()">&times;</button>
        </div>
        <form id="progressForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">From Level</label>
                    <input type="text" id="fromLevelDisplay" class="form-input" readonly style="background: #f3f4f6;">
                    <input type="hidden" name="from_level" id="fromLevel">
                </div>
                <div class="form-group">
                    <label for="toLevel" class="form-label">To Level</label>
                    <select name="to_level" id="toLevel" class="form-select" required>
                        <option value="">Select next level</option>
                        <option value="primary-upper">Primary Upper (P4-P6)</option>
                        <option value="jhs">Junior High School (JHS 1-3)</option>
                        <option value="shs">Senior High School (SHS 1-3)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reason" class="form-label">Reason (Optional)</label>
                    <textarea name="reason" id="reason" rows="3" class="form-input" placeholder="Reason for manual progression"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeProgressModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Progress Student</button>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function handleProgressClick(event) {
        event.preventDefault();
        const button = event.target;
        const userId = button.getAttribute('data-user-id');
        const currentLevel = button.getAttribute('data-current-level');
        openProgressModal(userId, currentLevel);
    }

    function openProgressModal(userId, fromLevel) {
        const levelDisplay = fromLevel.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        document.getElementById('fromLevelDisplay').value = levelDisplay;
        document.getElementById('fromLevel').value = fromLevel;
        document.getElementById('progressForm').action = `/admin/progress/user/${userId}/progress`;
        document.getElementById('progressModal').classList.add('active');
    }

    function closeProgressModal() {
        document.getElementById('progressModal').classList.remove('active');
        document.getElementById('progressForm').reset();
    }

    // Close modal when clicking outside
    document.getElementById('progressModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeProgressModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeProgressModal();
        }
    });
</script>
@endsection
