@extends('schools.admin.layout')

@section('title', isset($quiz) ? 'Edit Quiz' : 'Create Quiz')

@include('admin.contents.partials.quiz-builder-assets')

@section('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* ─── Layout ─── */
    .quiz-page-container {
        max-width: 960px;
        margin: 0 auto;
    }

    .section-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        background: linear-gradient(to right, #f8fafc, #f1f5f9);
        border-bottom: 1px solid var(--border);
    }

    .section-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-header h2 i {
        color: var(--primary);
    }

    .section-body {
        padding: 24px;
    }

    /* ─── Form Grid ─── */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-grid .full-width {
        grid-column: 1 / -1;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.925rem;
        font-family: inherit;
        background: var(--bg);
        color: var(--text);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-error {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 4px;
    }

    /* ─── Settings Row ─── */
    .settings-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 16px 20px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }

    .settings-toggle {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 4px;
    }

    .toggle-track {
        position: relative;
        width: 40px;
        height: 22px;
        flex-shrink: 0;
    }

    .toggle-track input { display: none; }

    .toggle-slider {
        position: absolute;
        inset: 0;
        background: #cbd5e1;
        border-radius: 11px;
        cursor: pointer;
        transition: background 0.25s;
    }

    .toggle-slider::before {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        transition: transform 0.25s;
    }

    .toggle-track input:checked + .toggle-slider {
        background: var(--primary);
    }

    .toggle-track input:checked + .toggle-slider::before {
        transform: translateX(18px);
    }

    .toggle-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
    }

    .toggle-hint {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* ─── Action Buttons ─── */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 20px 0;
    }

    .action-bar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s;
        text-decoration: none;
        font-family: inherit;
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border-color: var(--border);
    }

    .btn-cancel:hover { background: #e2e8f0; }

    .btn-draft {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .btn-draft:hover { background: #fde68a; }

    .btn-publish {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-publish:hover {
        background: var(--primary-dark);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
    }

    .btn-save {
        background: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .btn-save:hover { background: #047857; }

    .btn-revert {
        background: #f59e0b;
        color: white;
    }

    .btn-revert:hover { background: #d97706; }

    /* ─── Question Builder Button Group ─── */
    .add-question-group {
        display: flex;
        gap: 8px;
    }

    .add-question-group .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
    }

    .btn-add-mcq {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-add-mcq:hover { background: #bfdbfe; }

    .btn-add-essay {
        background: #ede9fe;
        color: #5b21b6;
    }

    .btn-add-essay:hover { background: #ddd6fe; }

    /* ─── Status Badge ─── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.draft {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.published {
        background: #dcfce7;
        color: #166534;
    }

    /* ─── Responsive ─── */
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
        .settings-row { grid-template-columns: 1fr; }
        .action-bar { flex-direction: column; align-items: stretch; }
        .add-question-group { flex-wrap: wrap; }
    }
</style>
@endsection

@section('content')
@php
    $isEditing = isset($quiz);
    $isDraft = $isEditing && ($quiz->status ?? 'published') === 'draft';
@endphp

<div class="quiz-page-container">
    <form
        action="{{ $isEditing ? route('school.studio.quiz.update', $quiz->id) : route('school.studio.quiz.store') }}"
        method="POST" id="quizForm">
        @csrf
        @if($isEditing)
            @method('PUT')
        @endif
        <input type="hidden" name="quiz_data" id="quiz_data_input">
        <input type="hidden" name="status" id="status_input" value="">

        {{-- ─── Section 1: Quiz Details ─── --}}
        <div class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-info-circle"></i> Quiz Details</h2>
                @if($isEditing)
                    <span class="status-badge {{ $isDraft ? 'draft' : 'published' }}">
                        <i class="fas fa-{{ $isDraft ? 'pencil-alt' : 'check-circle' }}"></i>
                        {{ $isDraft ? 'Draft' : 'Published' }}
                    </span>
                @endif
            </div>
            <div class="section-body">
                <div class="form-grid">
                    {{-- Title --}}
                    <div class="form-group full-width">
                        <label class="form-label">Quiz Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-input"
                            value="{{ old('title', $quiz->title ?? '') }}" required
                            placeholder="e.g. Mid-Term Science Assessment">
                        @error('title') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="form-group">
                        <label class="form-label">Subject <span class="required">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    {{ old('subject_id', $quiz->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Grade Level --}}
                    <div class="form-group">
                        <label class="form-label">Grade Level <span class="required">*</span></label>
                        <select name="grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            @foreach($levelGroups as $group)
                                <optgroup label="{{ $group->title }}">
                                    @foreach($group->levels as $level)
                                        <option value="{{ $level->title }}"
                                            {{ old('grade_level', $quiz->grade_level ?? '') == $level->title ? 'selected' : '' }}>
                                            {{ $level->title }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('grade_level') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Time Limit --}}
                    <div class="form-group">
                        <label class="form-label">Time Limit (Minutes) <span class="required">*</span></label>
                        <input type="number" name="time_limit_minutes" class="form-input"
                            value="{{ old('time_limit_minutes', $quiz->time_limit_minutes ?? 30) }}" min="1" required>
                        @error('time_limit_minutes') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Difficulty --}}
                    <div class="form-group">
                        <label class="form-label">Difficulty <span class="required">*</span></label>
                        <select name="difficulty_level" id="quiz_difficulty" class="form-select" required>
                            <option value="easy" {{ old('difficulty_level', $quiz->difficulty_level ?? 'medium') == 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty_level', $quiz->difficulty_level ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty_level', $quiz->difficulty_level ?? 'medium') == 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                        @error('difficulty_level') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Section 2: Quiz Questions ─── --}}
        <div class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-list-ol"></i> Questions</h2>
                <div class="add-question-group">
                    <button type="button" id="addMcqBtn" class="btn-add btn-add-mcq">
                        <i class="fas fa-plus"></i> MCQ
                    </button>
                    <button type="button" id="addEssayBtn" class="btn-add btn-add-essay">
                        <i class="fas fa-plus"></i> Essay
                    </button>
                </div>
            </div>
            <div class="section-body">
                {{-- Quiz Settings --}}
                <div class="settings-row">
                    <div class="form-group">
                        <label class="form-label">Difficulty</label>
                        <select id="quiz_difficulty_settings" class="form-select" style="font-size: 0.85rem;">
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Limit (Min)</label>
                        <input type="number" id="quiz_time_limit" class="form-input" value="{{ $quiz->time_limit_minutes ?? 30 }}" style="font-size: 0.85rem;">
                    </div>
                    <div class="settings-toggle">
                        <label class="toggle-track">
                            <input type="hidden" name="shuffle_questions" value="0">
                            <input type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1"
                                {{ old('shuffle_questions', $quiz->shuffle_questions ?? false) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <div>
                            <div class="toggle-label">Shuffle Questions</div>
                            <div class="toggle-hint">Questions will appear in a different order for each student.</div>
                        </div>
                    </div>
                </div>

                {{-- Question Navigation --}}
                <div id="quizNavigation" class="quiz-navigation-wrapper mb-6" style="margin-bottom: 20px;">
                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px;">Question Navigation</div>
                    <div id="quizNavGrid" class="quiz-nav-grid"></div>
                </div>

                {{-- Questions List --}}
                <div id="questionsList" class="space-y-6"></div>

                {{-- Pagination Footer --}}
                <div class="pagination-footer" style="margin-top: 24px;">
                    <div id="currentQuestionLabel" style="font-size: 0.875rem; font-weight: 600; color: #64748b;">No questions added yet</div>
                    <div class="nav-btn-group">
                        <button type="button" id="prevQuestionBtn" class="btn-nav" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button type="button" id="nextQuestionBtn" class="btn-nav" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Action Bar ─── --}}
        <div class="action-bar">
            <a href="{{ route('school.studio.index') }}" class="btn btn-cancel">
                <i class="fas fa-arrow-left"></i> Back to Studio
            </a>
            <div class="action-bar-left">
                @if($isEditing && !$isDraft)
                    <button type="button" id="revertDraftBtn" class="btn btn-revert">
                        <i class="fas fa-undo"></i> Revert to Draft
                    </button>
                    <button type="button" id="savePublishedBtn" class="btn btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                @else
                    <button type="button" id="saveDraftBtn" class="btn btn-draft">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="button" id="publishBtn" class="btn btn-publish">
                        <i class="fas fa-check-circle"></i> {{ $isEditing ? 'Publish' : 'Create & Publish' }}
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://unpkg.com/mathlive"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Initialize data for the quiz builder
    const uploadData = {
        quiz: @json($quiz ?? (object) [
            'questions' => [],
            'difficulty_level' => 'medium',
            'time_limit_minutes' => 30,
            'shuffle_questions' => false,
        ])
    };

    // Parse quiz_data if it exists
    if (uploadData.quiz.quiz_data) {
        let questions = [];
        if (typeof uploadData.quiz.quiz_data === 'string') {
            try {
                const parsed = JSON.parse(uploadData.quiz.quiz_data);
                questions = parsed.questions || [];
            } catch (e) { console.error('Error parsing quiz_data', e); }
        } else if (uploadData.quiz.quiz_data && uploadData.quiz.quiz_data.questions) {
            questions = uploadData.quiz.quiz_data.questions;
        }
        uploadData.quiz.questions = questions;
    }

    if (!uploadData.quiz.questions) {
        uploadData.quiz.questions = [];
    }

    // Initialize builder
    initializeQuizStep();
    initializeQuizSettings();

    // Sync the settings-row difficulty with the form difficulty
    const settingsDifficulty = document.getElementById('quiz_difficulty_settings');
    const formDifficulty = document.getElementById('quiz_difficulty');
    if (settingsDifficulty && formDifficulty) {
        settingsDifficulty.value = formDifficulty.value;
        settingsDifficulty.addEventListener('change', (e) => {
            formDifficulty.value = e.target.value;
        });
        formDifficulty.addEventListener('change', (e) => {
            settingsDifficulty.value = e.target.value;
        });
    }

    // Render existing questions if editing
    if (uploadData.quiz.questions.length > 0) {
        uploadData.quiz.questions.forEach(q => {
            addQuestion(q.type, q);
        });
    }

    // Form submission handling
    const form = document.getElementById('quizForm');
    const statusInput = document.getElementById('status_input');

    function serializeAndSubmit(status) {
        statusInput.value = status;
        const quizDataInput = document.getElementById('quiz_data_input');
        if (quizDataInput) {
            quizDataInput.value = JSON.stringify({
                questions: uploadData.quiz.questions
            });
        }
        form.submit();
    }

    ['saveDraftBtn', 'publishBtn', 'revertDraftBtn', 'savePublishedBtn'].forEach(id => {
        const btn = document.getElementById(id);
        if (!btn) return;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (id === 'saveDraftBtn' || id === 'revertDraftBtn') {
                serializeAndSubmit('draft');
            } else if (id === 'publishBtn') {
                serializeAndSubmit('published');
            } else {
                serializeAndSubmit(''); // keep current status
            }
        });
    });
</script>
@endsection