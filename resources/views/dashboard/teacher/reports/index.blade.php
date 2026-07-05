@extends('layouts.dashboard-simple')

@section('title', 'Report Cards - Teacher Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold" style="color: var(--text-dark);">Report Cards</h1>
            <p style="color: var(--text-muted);">Generate and view terminal report cards for your classes.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <!-- Generate Reports Card -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-white pt-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-magic me-2" style="color: var(--primary-red);"></i> Generate Reports</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Lock in grades and generate the official terminal report cards for a specific class.</p>
                    <form action="{{ route('teacher.reports.generate') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium">Select Class</label>
                            <select name="school_class_id" class="form-select bg-light" required>
                                <option value="">-- Choose Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Select Term</label>
                            <select name="academic_term_id" class="form-select bg-light" required>
                                <option value="">-- Choose Term --</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->term_name }} ({{ $term->academicYear->year_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: var(--primary-red); border-color: var(--primary-red);" onclick="return confirm('Are you sure? This will lock in current grades into the permanent report card records.')">
                            Generate Report Cards
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Reports Card -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-white pt-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-folder-open me-2" style="color: var(--primary-red);"></i> View Generated Reports</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">View previously generated report cards and download them as PDFs.</p>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Select Class</label>
                        <select id="view-class-id" class="form-select bg-light">
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Select Term</label>
                        <select id="view-term-id" class="form-select bg-light">
                            <option value="">-- Choose Term --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->term_name }} ({{ $term->academicYear->year_name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" id="view-reports-btn" class="btn btn-outline-secondary w-100 fw-bold border-2">
                        View Report Cards
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.getElementById('view-reports-btn').addEventListener('click', function() {
        const classId = document.getElementById('view-class-id').value;
        const termId = document.getElementById('view-term-id').value;
        
        if (!classId || !termId) {
            alert('Please select both a class and a term.');
            return;
        }
        
        window.location.href = `/dashboard/teacher/reports/class/${classId}/term/${termId}`;
    });
</script>
@endsection
