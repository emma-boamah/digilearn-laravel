@extends('layouts.dashboard-simple')

@section('title', 'My Classes - Teacher Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold" style="color: var(--text-dark);">My Assigned Classes</h1>
            <p style="color: var(--text-muted);">Manage your classes and enter student grades.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        @forelse($classSubjects as $classSubject)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; background: var(--bg-card);">
                    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, var(--primary-red) 0%, #b20a11 100%); color: white;">
                        <h5 class="card-title mb-0 fw-bold">{{ $classSubject->schoolClass->name }}</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(226, 28, 33, 0.1); color: var(--primary-red);">
                                <i class="fas fa-book-open fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ $classSubject->subject->name ?? 'Unknown Subject' }}</h6>
                                <small style="color: var(--text-muted);">Subject</small>
                            </div>
                        </div>
                        <div class="mt-auto pt-3">
                            <a href="{{ route('teacher.gradebook', $classSubject->id) }}" class="btn w-100 fw-bold text-white" style="background-color: var(--primary-red); border-radius: 8px;">
                                <i class="fas fa-table me-2"></i> Open Gradebook
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="empty-state">
                    <div class="mb-3">
                        <i class="fas fa-chalkboard text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--text-dark);">No Classes Assigned</h5>
                    <p style="color: var(--text-muted);">You have not been assigned to any classes yet. Please contact your school administrator.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
</style>
@endsection
