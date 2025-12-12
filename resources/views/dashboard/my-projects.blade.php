@extends('layouts.dashboard-components')

@section('title', 'My Projects - ' . config('app.name', 'ShoutOutGh'))

@section('content')
    <!-- Projects Content Container -->
    <div class="projects-container">
        <div class="content-header">
        <h1 class="page-title">My Projects</h1>
        <p class="page-subtitle">Track your learning progress and manage your projects</p>
    </div>

    <!-- Stats Overview -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Projects</span>
                <div class="stat-icon total">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats->total_projects ?? 0 }}</div>
            <div class="stat-label">All time</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Completed</span>
                <div class="stat-icon completed">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats->completed_projects ?? 0 }}</div>
            <div class="stat-label">{{ $stats->total_projects > 0 ? round(($stats->completed_projects / $stats->total_projects) * 100, 1) : 0 }}% completion rate</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">In Progress</span>
                <div class="stat-icon in-progress">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value">{{ $stats->in_progress_projects ?? 0 }}</div>
            <div class="stat-label">Active projects</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Time Spent</span>
                <div class="stat-icon paused">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value">
                @php
                    $totalSeconds = $stats->total_time_spent ?? 0;
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                @endphp
                {{ $hours > 0 ? $hours . 'h' : '' }} {{ $minutes }}m
            </div>
            <div class="stat-label">Learning time</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <div class="filters-header">
            <h2 class="filters-title">Filter Projects</h2>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <div class="filter-buttons">
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'all']) }}"
                       class="filter-button {{ $filter === 'all' ? 'active' : '' }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'in_progress']) }}"
                       class="filter-button {{ $filter === 'in_progress' ? 'active' : '' }}">In Progress</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'completed']) }}"
                       class="filter-button {{ $filter === 'completed' ? 'active' : '' }}">Completed</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'paused']) }}"
                       class="filter-button {{ $filter === 'paused' ? 'active' : '' }}">Paused</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'favorites']) }}"
                       class="filter-button {{ $filter === 'favorites' ? 'active' : '' }}">Favorites</a>
                </div>
            </div>
            <div class="filter-group">
                <label class="filter-label">Type</label>
                <div class="filter-buttons">
                    <a href="{{ request()->fullUrlWithQuery(['type' => 'all']) }}"
                       class="filter-button {{ $type === 'all' ? 'active' : '' }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['type' => 'lesson']) }}"
                       class="filter-button {{ $type === 'lesson' ? 'active' : '' }}">Lessons</a>
                    <a href="{{ request()->fullUrlWithQuery(['type' => 'quiz']) }}"
                       class="filter-button {{ $type === 'quiz' ? 'active' : '' }}">Quizzes</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="projects-section">
        <div class="section-header">
            <h2 class="section-title">
                @if($filter === 'all')
                    All Projects
                @elseif($filter === 'favorites')
                    Favorite Projects
                @else
                    {{ ucwords(str_replace('_', ' ', $filter)) }} Projects
                @endif
                ({{ $projects->total() }})
            </h2>
        </div>

        @if($projects->count() > 0)
            <div class="projects-grid">
                @foreach($projects as $project)
                <div class="project-card fade-in" data-project-id="{{ $project->id }}">
                    <div class="project-header">
                        <h3 class="project-title">{{ $project->project_title }}</h3>
                        <div class="project-meta">
                            <span class="project-subject">{{ $project->project_subject }}</span>
                            <span class="project-type {{ $project->project_type }}">{{ $project->project_type }}</span>
                        </div>
                        <div class="project-status">
                            <span class="status-badge {{ $project->status }}">{{ $project->getStatusText() }}</span>
                            <button class="favorite-button {{ $project->is_favorite ? 'active' : '' }}"
                                    onclick="toggleFavorite({{ $project->id }})">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: {{ $project->progress_percentage }}%"></div>
                            </div>
                            <div class="progress-text">{{ round($project->progress_percentage, 1) }}% complete</div>
                        </div>
                    </div>
                    <div class="project-footer">
                        <div class="project-time">
                            <div>{{ $project->getFormattedTimeSpent() }} spent</div>
                            <div style="font-size: 0.75rem; color: var(--gray-400);">
                                Last accessed {{ $project->last_accessed_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="project-actions">
                            @if($project->status === 'completed')
                                <button class="action-button secondary" onclick="viewProject({{ $project->id }})">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                </button>
                            @elseif($project->status === 'paused')
                                <button class="action-button primary" onclick="resumeProject({{ $project->id }})">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </button>
                            @else
                                <button class="action-button primary" onclick="continueProject({{ $project->id }})">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </button>
                            @endif

                            @if($project->status !== 'completed')
                                <button class="action-button secondary" onclick="pauseProject({{ $project->id }})">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($projects->hasPages())
                <div style="margin-top: 2rem; display: flex; justify-content: center;">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3 class="empty-state-title">
                    @if($filter === 'all')
                        No projects yet
                    @elseif($filter === 'favorites')
                        No favorite projects
                    @else
                        No {{ str_replace('_', ' ', $filter) }} projects
                    @endif
                </h3>
                <p class="empty-state-description">
                    @if($filter === 'all')
                        Start watching lessons or taking quizzes to see your projects here.
                    @elseif($filter === 'favorites')
                        Mark projects as favorites to see them here.
                    @else
                        You don't have any {{ str_replace('_', ' ', $filter) }} projects yet.
                    @endif
                </p>
                <a href="{{ route('dashboard.digilearn') }}" class="empty-state-action">
                    Start Learning
                </a>
            </div>
        @endif
    </div>
    </div>
@endsection

@push('styles')
<style>
    .projects-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
        overflow-x: hidden;
        width: 100%;
        box-sizing: border-box;
    }

    /* Ensure main content doesn't cause horizontal scroll */
    .main-content {
        overflow-x: hidden;
    }

    .content-header {
        background-color: var(--white);
        padding: 2rem 2rem 1rem;
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: var(--gray-600);
        font-size: 1rem;
    }

    /* Stats Overview */
    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background-color: var(--white);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .stat-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon {
        width: 32px;
        height: 32px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
    }

    .stat-icon.total { background-color: var(--secondary-blue); }
    .stat-icon.completed { background-color: #10b981; }
    .stat-icon.in-progress { background-color: #f59e0b; }
    .stat-icon.paused { background-color: var(--gray-500); }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-500);
    }

    /* Filters */
    .filters-section {
        background-color: var(--white);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        margin-bottom: 2rem;
    }

    .filters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .filters-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-button {
        padding: 0.5rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        background-color: var(--white);
        color: var(--gray-700);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .filter-button:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-400);
    }

    .filter-button.active {
        background-color: var(--primary-red);
        border-color: var(--primary-red);
        color: var(--white);
    }

    /* Projects Grid */
    .projects-section {
        margin-bottom: 2rem;
    }

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

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        width: 100%;
        box-sizing: border-box;
    }

    .project-card {
        background-color: var(--white);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .project-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid var(--gray-100);
    }

    .project-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .project-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .project-subject {
        font-size: 0.875rem;
        color: var(--secondary-blue);
        font-weight: 500;
    }

    .project-type {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .project-type.lesson {
        background-color: rgba(38, 119, 184, 0.1);
        color: var(--secondary-blue);
    }

    .project-type.quiz {
        background-color: rgba(225, 30, 45, 0.1);
        color: var(--primary-red);
    }

    .project-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.completed {
        background-color: #10b981;
        color: var(--white);
    }

    .status-badge.in-progress {
        background-color: #f59e0b;
        color: var(--white);
    }

    .status-badge.paused {
        background-color: var(--gray-500);
        color: var(--white);
    }

    .status-badge.not-started {
        background-color: var(--gray-300);
        color: var(--gray-700);
    }

    .progress-bar-container {
        margin-bottom: 1rem;
    }

    .progress-bar {
        width: 100%;
        height: 6px;
        background-color: var(--gray-200);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-red), var(--secondary-blue));
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .progress-text {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-top: 0.5rem;
    }

    .project-footer {
        padding: 1rem 1.5rem;
        background-color: var(--gray-50);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .project-time {
        font-size: 0.875rem;
        color: var(--gray-500);
    }

    .project-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-button {
        padding: 0.5rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-button.primary {
        background-color: var(--primary-red);
        color: var(--white);
    }

    .action-button.secondary {
        background-color: var(--gray-200);
        color: var(--gray-700);
    }

    .action-button:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }

    .favorite-button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .favorite-button.active {
        color: #f59e0b;
    }

    .favorite-button:not(.active) {
        color: var(--gray-400);
    }

    .favorite-button:hover {
        background-color: var(--gray-100);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background-color: var(--white);
        border-radius: 1rem;
        border: 1px solid var(--gray-200);
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--gray-300);
        margin-bottom: 1rem;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .empty-state-description {
        color: var(--gray-500);
        margin-bottom: 2rem;
    }

    .empty-state-action {
        background-color: var(--primary-red);
        color: var(--white);
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .empty-state-action:hover {
        background-color: var(--primary-red-hover);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .projects-grid {
            grid-template-columns: 1fr;
        }

        .project-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .project-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }

    /* Loading States */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid var(--gray-300);
        border-top: 2px solid var(--primary-red);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Animations */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Project management functions
    function continueProject(projectId) {
        // Get project details and redirect to appropriate page
        fetch(`/dashboard/projects/${projectId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const project = data.project;
                    if (project.project_type === 'lesson') {
                        window.location.href = `/dashboard/lesson/${project.project_id}`;
                    } else if (project.project_type === 'quiz') {
                        window.location.href = `/dashboard/quiz/${project.project_id}`;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading project', 'error');
            });
    }

    function resumeProject(projectId) {
        fetch(`/dashboard/projects/${projectId}/resume`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload page to update project status
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('Failed to resume project', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error resuming project', 'error');
        });
    }

    function pauseProject(projectId) {
        const notes = prompt('Add a note about why you\'re pausing this project (optional):');

        fetch(`/dashboard/projects/${projectId}/pause`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload page to update project status
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('Failed to pause project', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error pausing project', 'error');
        });
    }

    function viewProject(projectId) {
        // Get project details and redirect to view page
        fetch(`/dashboard/projects/${projectId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const project = data.project;
                    if (project.project_type === 'lesson') {
                        window.location.href = `/dashboard/lesson/${project.project_id}`;
                    } else if (project.project_type === 'quiz') {
                        window.location.href = `/dashboard/quiz/${project.project_id}/results`;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading project', 'error');
            });
    }

    function toggleFavorite(projectId) {
        const button = document.querySelector(`[data-project-id="${projectId}"] .favorite-button`);
        button.classList.add('loading');

        fetch(`/dashboard/projects/${projectId}/favorite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.classList.toggle('active', data.is_favorite);
                showNotification(data.message, 'success');
            } else {
                showNotification('Failed to update favorite', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating favorite', 'error');
        })
        .finally(() => {
            button.classList.remove('loading');
        });
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            z-index: 1000;
            max-width: 300px;
            animation: slideIn 0.3s ease;
            box-shadow: var(--shadow-lg);
        `;

        // Set background color based on type
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: 'var(--secondary-blue)'
        };

        notification.style.backgroundColor = colors[type] || colors.info;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Auto-refresh project data every 30 seconds for real-time updates
    setInterval(() => {
        // Only refresh if user is actively viewing the page
        if (!document.hidden) {
            updateProjectData();
        }
    }, 30000);

    function updateProjectData() {
        // Fetch updated project data without full page reload
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update only the stats section
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newStats = doc.querySelector('.stats-overview');
            const currentStats = document.querySelector('.stats-overview');

            if (newStats && currentStats) {
                currentStats.innerHTML = newStats.innerHTML;
            }
        })
        .catch(error => {
            console.log('Auto-refresh failed:', error);
        });
    }
</script>
@endpush
