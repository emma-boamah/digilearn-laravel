@extends('layouts.dashboard-simple')

@section('title', 'Gradebook - Teacher Dashboard')

@section('content')
<div class="container-fluid py-4 px-lg-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.classes') }}" style="color: var(--primary-red);">My Classes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gradebook</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0" style="color: var(--text-dark);">
                {{ $classSubject->schoolClass->name }} - {{ $classSubject->subject->name ?? 'Subject' }}
            </h1>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Term Selector -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $term->term_name }} ({{ $term->academicYear->year_name }})
                </button>
                <ul class="dropdown-menu">
                    @foreach($allTerms as $t)
                        <li>
                            <a class="dropdown-item {{ $t->id == $term->id ? 'active' : '' }}" 
                               href="{{ route('teacher.gradebook', ['classSubjectId' => $classSubject->id, 'termId' => $t->id]) }}">
                                {{ $t->term_name }} ({{ $t->academicYear->year_name }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <button type="button" class="btn shadow-sm fw-bold d-flex align-items-center gap-2 text-white" data-bs-toggle="modal" data-bs-target="#assignCbtModal" style="background-color: #2c3e50; border-color: #2c3e50;">
                <i class="fas fa-laptop-code"></i> Assign CBT Quiz
            </button>
            <button type="button" class="btn btn-primary shadow-sm fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newAssessmentModal" style="background-color: var(--primary-red); border-color: var(--primary-red);">
                <i class="fas fa-plus"></i> New Assessment
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Gradebook Spreadsheet -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <form action="{{ route('teacher.scores.save') }}" method="POST">
            @csrf
            
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle gradebook-table">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="student-col text-nowrap sticky-left" style="min-width: 250px; background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">Student Name</th>
                            
                            @foreach($assessments as $assessment)
                                <th class="text-center position-relative assessment-col" style="min-width: 120px; border-bottom: 2px solid #dee2e6;">
                                    <div class="fw-bold text-dark">
                                        @if($assessment->type === 'cbt')
                                            <i class="fas fa-laptop-code me-1" style="color: #2c3e50;" title="Computer-Based Test"></i>
                                        @endif
                                        {{ $assessment->title }}
                                    </div>
                                    <div class="small text-uppercase" style="font-size: 0.7rem; color: {{ $assessment->type === 'cbt' ? '#2c3e50' : '#6c757d' }};">
                                        {{ str_replace('_', ' ', $assessment->type) }}
                                        @if($assessment->type === 'cbt') <span class="text-muted">(auto-scored)</span> @endif
                                    </div>
                                    <span class="badge position-absolute top-0 end-0 m-1 {{ $assessment->type === 'cbt' ? 'text-white' : 'bg-secondary' }}" style="font-size: 0.65rem; {{ $assessment->type === 'cbt' ? 'background-color: #2c3e50;' : '' }}" title="Weight / Max Score">
                                        {{ $assessment->weight_percentage }}% / {{ $assessment->max_score }}
                                    </span>
                                </th>
                            @endforeach
                            
                            <!-- Computation Columns -->
                            <th class="text-center bg-light fw-bold" style="min-width: 100px; border-bottom: 2px solid #dee2e6; border-left: 2px solid #dee2e6;">Class (30%)</th>
                            <th class="text-center bg-light fw-bold" style="min-width: 100px; border-bottom: 2px solid #dee2e6;">Exam (70%)</th>
                            <th class="text-center bg-light fw-bold" style="min-width: 100px; border-bottom: 2px solid #dee2e6;">Total</th>
                            <th class="text-center text-white sticky-right" style="min-width: 80px; border-bottom: 2px solid #dee2e6; background-color: var(--primary-red);">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="sticky-left bg-white fw-medium d-flex align-items-center gap-3" style="border-right: 1px solid #dee2e6;">
                                    <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    {{ $student->name }}
                                </td>
                                
                                @foreach($assessments as $assessment)
                                    @php
                                        $scoreVal = isset($scores[$student->id][$assessment->id]) ? $scores[$student->id][$assessment->id]->score : '';
                                    @endphp
                                    <td class="text-center p-1">
                                        <input type="number" 
                                               name="scores[{{ $assessment->id }}][{{ $student->id }}]" 
                                               value="{{ $scoreVal }}" 
                                               class="form-control text-center border-0 bg-transparent score-input" 
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $assessment->max_score }}"
                                               placeholder="--">
                                    </td>
                                @endforeach
                                
                                <!-- Render computed grades -->
                                @php
                                    $final = $finalGrades[$student->id];
                                @endphp
                                <td class="text-center fw-medium text-muted bg-light" style="border-left: 2px solid #dee2e6;">{{ $final['class_score'] }}</td>
                                <td class="text-center fw-medium text-muted bg-light">{{ $final['exam_score'] }}</td>
                                <td class="text-center fw-bold text-dark bg-light">{{ $final['total_score'] }}</td>
                                <td class="text-center fw-bold text-white sticky-right" style="background-color: var(--primary-red);">{{ $final['grade'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($assessments) + 5 }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-graduate fs-3 mb-2 opacity-50"></i><br>
                                    No students enrolled in this class.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(count($students) > 0 && count($assessments) > 0)
            <div class="card-footer bg-white border-top-0 py-3 text-end">
                <button type="submit" class="btn btn-success fw-bold px-4 rounded-3 shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- New Assessment Modal -->
<div class="modal fade" id="newAssessmentModal" tabindex="-1" aria-labelledby="newAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white" style="background-color: var(--primary-red);">
                <h5 class="modal-title fw-bold" id="newAssessmentModalLabel"><i class="fas fa-plus-circle me-2"></i>New Assessment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teacher.assessments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="class_subject_id" value="{{ $classSubject->id }}">
                <input type="hidden" name="academic_term_id" value="{{ $term->id }}">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Assessment Title</label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light" required placeholder="e.g. Mid-term Exam, Class Exercise 1">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Type</label>
                            <select name="type" class="form-select bg-light" required>
                                <option value="exercise">Class Exercise</option>
                                <option value="homework">Homework</option>
                                <option value="project">Project Work</option>
                                <option value="mid_term">Mid-Term Exam</option>
                                <option value="exam">Final Exam</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Date (Optional)</label>
                            <input type="date" name="date_administered" class="form-control bg-light">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Maximum Score</label>
                            <div class="input-group">
                                <input type="number" name="max_score" class="form-control bg-light" value="100" min="1" required>
                                <span class="input-group-text border-0 bg-secondary text-white">pts</span>
                            </div>
                            <small class="text-muted">The total raw score the student is graded out of.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Weight Percentage</label>
                            <div class="input-group">
                                <input type="number" name="weight_percentage" class="form-control bg-light" value="10" min="0" max="100" required>
                                <span class="input-group-text border-0 bg-secondary text-white">%</span>
                            </div>
                            <small class="text-muted">How much this contributes to the final term grade.</small>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-bold rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 rounded-3" style="background-color: var(--primary-red); border-color: var(--primary-red);">Create Column</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign CBT Quiz Modal -->
<div class="modal fade" id="assignCbtModal" tabindex="-1" aria-labelledby="assignCbtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white" style="background-color: #2c3e50;">
                <h5 class="modal-title fw-bold" id="assignCbtModalLabel"><i class="fas fa-laptop-code me-2"></i>Assign CBT Quiz</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teacher.cbt.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="class_subject_id" value="{{ $classSubject->id }}">
                <input type="hidden" name="academic_term_id" value="{{ $term->id }}">
                
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Select a published quiz to assign as a Computer-Based Test. When students take this quiz, their scores will <strong>automatically</strong> appear in the gradebook.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select Quiz</label>
                        <select name="quiz_id" id="cbtQuizSelect" class="form-select bg-light" required>
                            <option value="" disabled selected>Loading available quizzes...</option>
                        </select>
                        <small class="text-muted">Showing quizzes matching this subject and grade level.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Maximum Score</label>
                            <div class="input-group">
                                <input type="number" name="max_score" class="form-control bg-light" value="100" min="1" required>
                                <span class="input-group-text border-0 text-white" style="background-color: #2c3e50;">pts</span>
                            </div>
                            <small class="text-muted">Quiz percentage will be scaled to this score.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Weight Percentage</label>
                            <div class="input-group">
                                <input type="number" name="weight_percentage" class="form-control bg-light" value="10" min="0" max="100" required>
                                <span class="input-group-text border-0 text-white" style="background-color: #2c3e50;">%</span>
                            </div>
                            <small class="text-muted">Contribution to the final term grade.</small>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-bold rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4 rounded-3 text-white" style="background-color: #2c3e50; border-color: #2c3e50;">Assign CBT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Spreadsheet Styles */
    .gradebook-table th, .gradebook-table td {
        vertical-align: middle;
    }
    
    .score-input {
        transition: all 0.2s;
    }
    
    .score-input:focus {
        background-color: #f8f9fa !important;
        box-shadow: inset 0 0 0 2px var(--primary-red);
        outline: none;
    }
    
    /* Sticky Columns */
    .sticky-left {
        position: sticky;
        left: 0;
        z-index: 2;
    }
    
    .sticky-right {
        position: sticky;
        right: 0;
        z-index: 2;
    }
    
    /* Input arrows hide for clean spreadsheet look */
    .score-input::-webkit-outer-spin-button,
    .score-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .score-input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Load available quizzes when the CBT modal opens
    document.addEventListener('DOMContentLoaded', function() {
        const cbtModal = document.getElementById('assignCbtModal');
        if (cbtModal) {
            cbtModal.addEventListener('show.bs.modal', function() {
                const select = document.getElementById('cbtQuizSelect');
                const classSubjectId = {{ $classSubject->id }};

                select.innerHTML = '<option value="" disabled selected>Loading...</option>';

                fetch(`{{ route('teacher.cbt.available-quizzes') }}?class_subject_id=${classSubjectId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(quizzes => {
                    select.innerHTML = '';
                    if (quizzes.length === 0) {
                        select.innerHTML = '<option value="" disabled selected>No matching quizzes found</option>';
                        return;
                    }
                    select.innerHTML = '<option value="" disabled selected>Choose a quiz...</option>';
                    quizzes.forEach(quiz => {
                        const opt = document.createElement('option');
                        opt.value = quiz.id;
                        opt.textContent = `${quiz.title} (${quiz.grade_level}${quiz.time_limit_minutes ? ' • ' + quiz.time_limit_minutes + ' min' : ''})`;
                        select.appendChild(opt);
                    });
                })
                .catch(err => {
                    select.innerHTML = '<option value="" disabled selected>Failed to load quizzes</option>';
                    console.error('Failed to load quizzes:', err);
                });
            });
        }
    });
</script>
@endsection
