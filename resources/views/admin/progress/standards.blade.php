@extends('layouts.admin')

@section('title', 'Progression Standards')
@section('page-title', 'Progression Standards')
@section('page-description', 'Configure requirements for level progression')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Modal Enhancements */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
        padding: 1rem;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 48rem;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(1rem);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        background-color: var(--gray-50);
    }

    .modal-header-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-header-icon {
        width: 1.5rem;
        height: 1.5rem;
        color: var(--primary-blue);
    }

    .modal-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--gray-400);
        cursor: pointer;
        padding: 0;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }

    .modal-close-btn:hover {
        background-color: var(--gray-200);
        color: var(--gray-600);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--gray-200);
    }

    .form-section-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--primary-blue);
    }

    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .form-section-description {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin: 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .form-grid.cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    @media (max-width: 640px) {
        .form-grid.cols-2,
        .form-grid.cols-3 {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .form-label-required {
        color: var(--accent-red);
    }

    .form-input {
        padding: 0.625rem 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        color: var(--gray-900);
        background-color: var(--white);
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-input:disabled {
        background-color: var(--gray-100);
        color: var(--gray-500);
        cursor: not-allowed;
    }

    .form-input-hint {
        font-size: 0.75rem;
        color: var(--gray-500);
        margin-top: 0.25rem;
    }

    .form-input-hint.success {
        color: #16a34a;
    }

    .form-input-hint.info {
        color: var(--primary-blue);
    }

    .modal-footer {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        padding: 1.5rem;
        border-top: 1px solid var(--gray-200);
        background-color: var(--gray-50);
    }

    .btn-modal {
        padding: 0.625rem 1.25rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-modal-primary {
        background-color: var(--primary-blue);
        color: var(--white);
    }

    .btn-modal-primary:hover {
        background-color: var(--primary-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-modal-secondary {
        background-color: var(--gray-300);
        color: var(--gray-800);
    }

    .btn-modal-secondary:hover {
        background-color: var(--gray-400);
    }

    /* Table Enhancements */
    .table-container {
        overflow-x: auto;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-200);
    }

    .standards-table {
        width: 100%;
        border-collapse: collapse;
    }

    .standards-table thead {
        background-color: var(--gray-50);
    }

    .standards-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--gray-200);
    }

    .standards-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.875rem;
        color: var(--gray-700);
    }

    .standards-table tbody tr:hover {
        background-color: var(--gray-50);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-badge.active {
        background-color: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background-color: var(--gray-100);
        color: var(--gray-600);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 0.375rem 0.75rem;
        border: none;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        background: none;
    }

    .action-btn-edit {
        color: var(--primary-blue);
    }

    .action-btn-edit:hover {
        background-color: var(--primary-blue-light);
    }

    .action-btn-toggle {
        color: #f59e0b;
    }

    .action-btn-toggle:hover {
        background-color: #fef3c7;
    }

    /* Info Box */
    .info-box {
        background-color: var(--primary-blue-light);
        border: 1px solid #bfdbfe;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .info-box-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 0.75rem;
    }

    .info-box-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .info-box-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .info-box-item {
        font-size: 0.875rem;
    }

    .info-box-label {
        font-weight: 500;
        color: #1e40af;
    }

    .info-box-value {
        color: #1e3a8a;
        font-weight: 600;
    }

    /* Header Section */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .page-header-content h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 0.25rem 0;
    }

    .page-header-content p {
        color: var(--gray-600);
        margin: 0;
    }

    .btn-add-standard {
        background-color: var(--primary-blue);
        color: var(--white);
        padding: 0.625rem 1.25rem;
        border: none;
        border-radius: 0.375rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-add-standard:hover {
        background-color: var(--primary-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .card {
        background-color: var(--white);
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
    }
</style>

<div class="card">
    <div class="page-header">
        <div class="page-header-content">
            <h2>Progression Standards</h2>
            <p>Set the requirements students must meet to progress to the next level</p>
        </div>
        <button class="btn-add-standard" id="addStandardBtn">
            <i class="fas fa-plus"></i> Add Standard
        </button>
    </div>

    <!-- Standards Table -->
    <div class="table-container">
        <table class="standards-table">
            <thead>
                <tr>
                    <th>Level Group</th>
                    <th>Group Progression</th>
                    <th>Individual Progression</th>
                    <th>Lessons/Quizzes Required</th>
                    <th>Min Quiz Score</th>
                    <th>Watch Threshold</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($standards as $standard)
                    <tr>
                        <td class="font-medium">
                            {{ $levelGroups[$standard->level_group] ?? $standard->level_group }}
                        </td>
                        <td>
                            <div style="font-size: 0.75rem; line-height: 1.5;">
                                <div><strong>L:</strong> {{ $standard->required_lesson_completion_percentage }}%</div>
                                <div><strong>Q:</strong> {{ $standard->required_quiz_completion_percentage }}%</div>
                                <div><strong>Avg:</strong> {{ $standard->required_average_quiz_score }}%</div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.75rem; line-height: 1.5;">
                                <div><strong>L:</strong> {{ $standard->individual_level_lesson_threshold ?? 75 }}%</div>
                                <div><strong>Q:</strong> {{ $standard->individual_level_quiz_threshold ?? 60 }}%</div>
                                <div><strong>Avg:</strong> {{ $standard->individual_level_score_threshold ?? 65 }}%</div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.75rem; line-height: 1.5;">
                                <div><strong>Individual:</strong> {{ $standard->required_number_of_lessons_individual ?? 10 }}L / {{ $standard->required_number_of_quizzes_individual ?? 5 }}Q</div>
                                <div><strong>Group:</strong> {{ $standard->required_number_of_lessons_group ?? 20 }}L / {{ $standard->required_number_of_quizzes_group ?? 10 }}Q</div>
                            </div>
                        </td>
                        <td>{{ $standard->minimum_quiz_score }}%</td>
                        <td>{{ $standard->lesson_watch_threshold_percentage }}%</td>
                        <td>
                            <span class="status-badge {{ $standard->is_active ? 'active' : 'inactive' }}">
                                <i class="fas {{ $standard->is_active ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>
                                {{ $standard->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn action-btn-edit edit-standard-btn" data-standard-id="{{ $standard->id }}" data-level-group="{{ $standard->level_group }}" data-lesson-req="{{ $standard->required_lesson_completion_percentage }}" data-quiz-req="{{ $standard->required_quiz_completion_percentage }}" data-avg-score="{{ $standard->required_average_quiz_score }}" data-min-score="{{ $standard->minimum_quiz_score }}" data-watch-threshold="{{ $standard->lesson_watch_threshold_percentage }}" data-individual-lesson="{{ $standard->individual_level_lesson_threshold ?? 75 }}" data-individual-quiz="{{ $standard->individual_level_quiz_threshold ?? 60 }}" data-individual-score="{{ $standard->individual_level_score_threshold ?? 65 }}" data-lessons-individual="{{ $standard->required_number_of_lessons_individual ?? 10 }}" data-quizzes-individual="{{ $standard->required_number_of_quizzes_individual ?? 5 }}" data-lessons-group="{{ $standard->required_number_of_lessons_group ?? 20 }}" data-quizzes-group="{{ $standard->required_number_of_quizzes_group ?? 10 }}">
                                    Edit
                                </button>
                                <form action="{{ route('admin.progress.standards.toggle', $standard) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="action-btn action-btn-toggle">
                                        {{ $standard->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">
                            <div style="color: var(--gray-500);">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                <p style="margin: 0;">No progression standards configured yet.</p>
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Default standards will be used until custom ones are set.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Default Standards Info -->
    <div class="info-box">
        <div class="info-box-title">
            <i class="fas fa-info-circle"></i> Default Standards (Used When No Custom Standards Exist)
        </div>
        <div class="info-box-grid">
            <div class="info-box-item">
                <div class="info-box-label">Lesson (Group)</div>
                <div class="info-box-value">80%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Quiz (Group)</div>
                <div class="info-box-value">70%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Avg Score (Group)</div>
                <div class="info-box-value">70%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Min Score</div>
                <div class="info-box-value">70%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Lesson (Individual)</div>
                <div class="info-box-value">75%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Quiz (Individual)</div>
                <div class="info-box-value">60%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Avg Score (Individual)</div>
                <div class="info-box-value">65%</div>
            </div>
            <div class="info-box-item">
                <div class="info-box-label">Watch Threshold</div>
                <div class="info-box-value">90%</div>
            </div>
        </div>
    </div>
</div>

<!-- Add Standard Modal -->
<div class="modal-overlay" id="addStandardModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-title">
                <svg class="modal-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <h3>Add Progression Standard</h3>
            </div>
            <button class="modal-close-btn" id="closeAddModal">&times;</button>
        </div>

        <form action="{{ route('admin.progress.standards.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <!-- Level Group Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <h4 class="form-section-title">Level Group</h4>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Select Level Group
                            <span class="form-label-required">*</span>
                        </label>
                        <select name="level_group" id="level_group" class="form-input" required>
                            <option value="">Choose a level group...</option>
                            @foreach($levelGroups as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Group Progression Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <div>
                            <h4 class="form-section-title">Group Progression Thresholds</h4>
                            <p class="form-section-description">Requirements for advancing between level groups</p>
                        </div>
                    </div>
                    <div class="form-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Lesson Completion (%)</label>
                            <input type="number" name="required_lesson_completion_percentage" min="0" max="100" value="80" class="form-input" required>
                            <div class="form-input-hint">For level group advancement</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quiz Completion (%)</label>
                            <input type="number" name="required_quiz_completion_percentage" min="0" max="100" value="70" class="form-input" required>
                            <div class="form-input-hint">For level group advancement</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Avg Quiz Score (%)</label>
                            <input type="number" name="required_average_quiz_score" min="0" max="100" value="70" class="form-input" required>
                            <div class="form-input-hint">For level group advancement</div>
                        </div>
                    </div>
                </div>

                <!-- Individual Progression Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div>
                            <h4 class="form-section-title">Individual Progression Thresholds</h4>
                            <p class="form-section-description">Requirements for advancing within level groups</p>
                        </div>
                    </div>
                    <div class="form-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Lesson Threshold (%)</label>
                            <input type="number" name="individual_level_lesson_threshold" min="0" max="100" value="75" class="form-input" required>
                            <div class="form-input-hint success">For individual level advancement</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quiz Threshold (%)</label>
                            <input type="number" name="individual_level_quiz_threshold" min="0" max="100" value="60" class="form-input" required>
                            <div class="form-input-hint success">For individual level advancement</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Score Threshold (%)</label>
                            <input type="number" name="individual_level_score_threshold" min="0" max="100" value="65" class="form-input" required>
                            <div class="form-input-hint success">For individual level advancement</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <h4 class="form-section-title">Additional Settings</h4>
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Minimum Quiz Score (%)</label>
                            <input type="number" name="minimum_quiz_score" min="0" max="100" value="70" class="form-input" required>
                            <div class="form-input-hint info">Minimum score required to pass individual quizzes</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lesson Watch Threshold (%)</label>
                            <input type="number" name="lesson_watch_threshold_percentage" min="0" max="100" value="90" class="form-input" required>
                            <div class="form-input-hint info">Percentage of lesson that must be watched</div>
                        </div>
                    </div>
                </div>

                <!-- Number-Based Progression Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div>
                            <h4 class="form-section-title">Number-Based Progression Settings</h4>
                            <p class="form-section-description">Set the number of lessons and quizzes required for progression</p>
                        </div>
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Lessons (Individual)</label>
                            <input type="number" name="required_number_of_lessons_individual" min="1" value="10" class="form-input" required>
                            <div class="form-input-hint">Number of lessons to complete for individual progression</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quizzes (Individual)</label>
                            <input type="number" name="required_number_of_quizzes_individual" min="1" value="5" class="form-input" required>
                            <div class="form-input-hint">Number of quizzes to complete for individual progression</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lessons (Group)</label>
                            <input type="number" name="required_number_of_lessons_group" min="1" value="20" class="form-input" required>
                            <div class="form-input-hint">Number of lessons to complete for group progression</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quizzes (Group)</label>
                            <input type="number" name="required_number_of_quizzes_group" min="1" value="10" class="form-input" required>
                            <div class="form-input-hint">Number of quizzes to complete for group progression</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" id="cancelAddModal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn-modal btn-modal-primary">
                    <i class="fas fa-check"></i> Add Standard
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Standard Modal -->
<div class="modal-overlay" id="editStandardModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-title">
                <svg class="modal-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <h3>Edit Progression Standard</h3>
            </div>
            <button class="modal-close-btn" id="closeEditModal">&times;</button>
        </div>

        <form id="editStandardForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <!-- Level Group Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <h4 class="form-section-title">Level Group</h4>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Level Group</label>
                        <input type="text" id="edit_level_group_display" class="form-input" disabled>
                    </div>
                </div>

                <!-- Group Progression Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <div>
                            <h4 class="form-section-title">Group Progression Thresholds</h4>
                            <p class="form-section-description">Requirements for advancing between level groups</p>
                        </div>
                    </div>
                    <div class="form-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Lesson Completion (%)</label>
                            <input type="number" name="required_lesson_completion_percentage" id="edit_required_lesson_completion_percentage" min="0" max="100" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quiz Completion (%)</label>
                            <input type="number" name="required_quiz_completion_percentage" id="edit_required_quiz_completion_percentage" min="0" max="100" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Avg Quiz Score (%)</label>
                            <input type="number" name="required_average_quiz_score" id="edit_required_average_quiz_score" min="0" max="100" class="form-input" required>
                        </div>
                    </div>
                </div>

                <!-- Individual Progression Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div>
                            <h4 class="form-section-title">Individual Progression Thresholds</h4>
                            <p class="form-section-description">Requirements for advancing within level groups</p>
                        </div>
                    </div>
                    <div class="form-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Lesson Threshold (%)</label>
                            <input type="number" name="individual_level_lesson_threshold" id="edit_individual_level_lesson_threshold" min="0" max="100" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quiz Threshold (%)</label>
                            <input type="number" name="individual_level_quiz_threshold" id="edit_individual_level_quiz_threshold" min="0" max="100" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Score Threshold (%)</label>
                            <input type="number" name="individual_level_score_threshold" id="edit_individual_level_score_threshold" min="0" max="100" class="form-input" required>
                        </div>
                    </div>
                </div>

                <!-- Number-Based Progression Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div>
                            <h4 class="form-section-title">Number-Based Progression Settings</h4>
                            <p class="form-section-description">Set the number of lessons and quizzes required for progression</p>
                        </div>
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Lessons (Individual)</label>
                            <input type="number" name="required_number_of_lessons_individual" id="edit_required_number_of_lessons_individual" min="1" class="form-input" required>
                            <div class="form-input-hint">Number of lessons to complete for individual progression</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quizzes (Individual)</label>
                            <input type="number" name="required_number_of_quizzes_individual" id="edit_required_number_of_quizzes_individual" min="1" class="form-input" required>
                            <div class="form-input-hint">Number of quizzes to complete for individual progression</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lessons (Group)</label>
                            <input type="number" name="required_number_of_lessons_group" id="edit_required_number_of_lessons_group" min="1" class="form-input" required>
                            <div class="form-input-hint">Number of lessons to complete for group progression</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quizzes (Group)</label>
                            <input type="number" name="required_number_of_quizzes_group" id="edit_required_number_of_quizzes_group" min="1" class="form-input" required>
                            <div class="form-input-hint">Number of quizzes to complete for group progression</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <svg class="form-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <h4 class="form-section-title">Additional Settings</h4>
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Minimum Quiz Score (%)</label>
                            <input type="number" name="minimum_quiz_score" id="edit_minimum_quiz_score" min="0" max="100" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lesson Watch Threshold (%)</label>
                            <input type="number" name="lesson_watch_threshold_percentage" id="edit_lesson_watch_threshold_percentage" min="0" max="100" class="form-input" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" id="cancelEditModal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn-modal btn-modal-primary">
                    <i class="fas fa-check"></i> Update Standard
                </button>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        const levelGroups = @json($levelGroups);
        
        // Modal elements
        const addStandardBtn = document.getElementById('addStandardBtn');
        const addStandardModal = document.getElementById('addStandardModal');
        const editStandardModal = document.getElementById('editStandardModal');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddModal = document.getElementById('cancelAddModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditModal = document.getElementById('cancelEditModal');
        const editStandardForm = document.getElementById('editStandardForm');
        const editStandardBtns = document.querySelectorAll('.edit-standard-btn');

        // Open Add Modal
        addStandardBtn.addEventListener('click', function() {
            addStandardModal.classList.add('active');
        });

        // Close Add Modal
        closeAddModal.addEventListener('click', function() {
            addStandardModal.classList.remove('active');
        });

        cancelAddModal.addEventListener('click', function() {
            addStandardModal.classList.remove('active');
        });

        // Close Edit Modal
        closeEditModal.addEventListener('click', function() {
            editStandardModal.classList.remove('active');
        });

        cancelEditModal.addEventListener('click', function() {
            editStandardModal.classList.remove('active');
        });

        // Close modal when clicking outside
        addStandardModal.addEventListener('click', function(e) {
            if (e.target === addStandardModal) {
                addStandardModal.classList.remove('active');
            }
        });

        editStandardModal.addEventListener('click', function(e) {
            if (e.target === editStandardModal) {
                editStandardModal.classList.remove('active');
            }
        });

        // Edit Standard Button Handlers
        editStandardBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const standardId = this.dataset.standardId;
                const levelGroup = this.dataset.levelGroup;
                const lessonReq = this.dataset.lessonReq;
                const quizReq = this.dataset.quizReq;
                const avgScore = this.dataset.avgScore;
                const minScore = this.dataset.minScore;
                const watchThreshold = this.dataset.watchThreshold;
                const individualLesson = this.dataset.individualLesson;
                const individualQuiz = this.dataset.individualQuiz;
                const individualScore = this.dataset.individualScore;
                const lessonsIndividual = this.dataset.lessonsIndividual;
                const quizzesIndividual = this.dataset.quizzesIndividual;
                const lessonsGroup = this.dataset.lessonsGroup;
                const quizzesGroup = this.dataset.quizzesGroup;

                // Populate form fields
                document.getElementById('edit_level_group_display').value = levelGroups[levelGroup] || levelGroup;
                document.getElementById('edit_required_lesson_completion_percentage').value = lessonReq;
                document.getElementById('edit_required_quiz_completion_percentage').value = quizReq;
                document.getElementById('edit_required_average_quiz_score').value = avgScore;
                document.getElementById('edit_minimum_quiz_score').value = minScore;
                document.getElementById('edit_lesson_watch_threshold_percentage').value = watchThreshold;
                document.getElementById('edit_individual_level_lesson_threshold').value = individualLesson;
                document.getElementById('edit_individual_level_quiz_threshold').value = individualQuiz;
                document.getElementById('edit_individual_level_score_threshold').value = individualScore;
                document.getElementById('edit_required_number_of_lessons_individual').value = lessonsIndividual;
                document.getElementById('edit_required_number_of_quizzes_individual').value = quizzesIndividual;
                document.getElementById('edit_required_number_of_lessons_group').value = lessonsGroup;
                document.getElementById('edit_required_number_of_quizzes_group').value = quizzesGroup;

                // Set form action
                editStandardForm.action = `/admin/progress/standards/${standardId}`;

                // Open modal
                editStandardModal.classList.add('active');
            });
        });
    });
</script>
@endsection
