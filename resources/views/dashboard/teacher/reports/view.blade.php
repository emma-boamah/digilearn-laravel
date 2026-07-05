@extends('layouts.dashboard-simple')

@section('title', 'View Report Cards - Teacher Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.reports.index') }}" style="color: var(--primary-red);">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold" style="color: var(--text-dark);">
                {{ $schoolClass->name }} - {{ $term->term_name }}
            </h1>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="px-4 py-3 border-0">Student</th>
                        <th class="text-center py-3 border-0">Total Score</th>
                        <th class="text-center py-3 border-0">Average</th>
                        <th class="text-center py-3 border-0">Position</th>
                        <th class="text-end px-4 py-3 border-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportCards as $reportCard)
                        <tr>
                            <td class="px-4 py-3 d-flex align-items-center gap-3">
                                <img src="{{ $reportCard->student->avatar_url }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark">{{ $reportCard->student->name }}</div>
                                    <div class="small text-muted">{{ $reportCard->student->email }}</div>
                                </div>
                            </td>
                            <td class="text-center fw-medium text-dark">{{ $reportCard->total_score }}</td>
                            <td class="text-center fw-medium text-dark">{{ $reportCard->average_score }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-bold">
                                    {{ $reportCard->position_in_class }}
                                    <sup class="text-muted" style="font-size: 0.6rem;">
                                        @if($reportCard->position_in_class % 10 == 1 && $reportCard->position_in_class != 11) st
                                        @elseif($reportCard->position_in_class % 10 == 2 && $reportCard->position_in_class != 12) nd
                                        @elseif($reportCard->position_in_class % 10 == 3 && $reportCard->position_in_class != 13) rd
                                        @else th
                                        @endif
                                    </sup>
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('teacher.reports.pdf', $reportCard->id) }}" class="btn btn-sm text-white rounded-3 fw-bold px-3" style="background-color: var(--primary-red);">
                                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-file-alt fs-2 mb-3 opacity-50"></i>
                                    <h5 class="fw-bold text-dark">No Report Cards Found</h5>
                                    <p>Reports haven't been generated for this class and term yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
