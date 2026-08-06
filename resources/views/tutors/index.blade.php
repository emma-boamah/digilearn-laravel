@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
@endsection

@section('subjects-filter')
    <div class="subjects-filter">
        <span class="filter-label">Subjects:</span>
        <a href="{{ route('tutors.index') }}" class="subject-chip {{ !request('subject_id') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="fas fa-th-large"></i> All Subjects
        </a>
        @foreach($subjects as $subject)
            <a href="{{ route('tutors.index', ['subject_id' => $subject->id]) }}" class="subject-chip {{ request('subject_id') == $subject->id ? 'active' : '' }}" style="text-decoration: none;">
                <i class="fas fa-book"></i> {{ $subject->name }}
            </a>
        @endforeach
    </div>
@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-section" style="background: linear-gradient(135deg, var(--gray-900), var(--secondary-blue)); position: relative; height: 280px; display: flex; align-items: center; justify-content: center; text-align: center; color: white;">
        <div style="position: absolute; inset: 0; background: url('{{ asset('images/personalized.jpeg') }}') center/cover; opacity: 0.3;"></div>
        <div class="hero-content" style="position: relative; z-index: 10;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; color: white;">Personalized Learning</h1>
            <p style="font-size: 1.125rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Connect with expert tutors and learn at your own pace.</p>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content-section" style="padding: 2rem;">
        
        @if($tutors->count() > 0)
            <div class="content-grid">
                @foreach($tutors as $tutor)
                    <div class="lesson-card" style="display: flex; flex-direction: column; background: var(--bg-surface); border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="lesson-thumbnail" style="position: relative; height: 160px; background: var(--gray-100); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--border-color);">
                            <div style="padding: 1rem;">
                                {!! $tutor->getAvatarHtml(80, 'shadow-md') !!}
                            </div>
                        </div>
                        <div class="lesson-info" style="padding: 1.25rem; display: flex; flex-direction: column; flex: 1;">
                            <h3 class="lesson-title" style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">{{ $tutor->name }}</h3>
                            <div class="lesson-meta" style="font-size: 0.875rem; color: var(--primary-red); font-weight: 500; margin-bottom: 0.75rem;">
                                @foreach($tutor->tutorSubjects as $ts)
                                    {{ $ts->subject->name }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                            
                            <div style="font-size: 0.875rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 1rem; flex: 1;">
                                {{ $tutor->tutorProfile->bio }}
                            </div>
                            
                            <div class="lesson-actions" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: auto;">
                                <div style="font-weight: 700; color: var(--text-main);">
                                    @if($tutor->tutorSubjects->count() > 0)
                                        GHS {{ number_format($tutor->tutorSubjects->min('hourly_rate'), 2) }} <span style="font-size: 0.75rem; font-weight: 400; color: var(--text-muted);">/hr</span>
                                    @else
                                        GHS 0.00 <span style="font-size: 0.75rem; font-weight: 400; color: var(--text-muted);">/hr</span>
                                    @endif
                                </div>
                                <a href="{{ route('tutors.show', $tutor->id) }}" style="background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue); padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; font-size: 0.875rem; text-decoration: none; transition: background 0.2s;">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div style="margin-top: 2rem; display: flex; justify-content: center;">
                {{ $tutors->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 4rem 1rem; background: var(--bg-surface); border-radius: 12px; border: 1px dashed var(--border-color);">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--gray-400); margin: 0 auto 1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <h3 style="font-size: 1.125rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem;">No tutors found</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Try adjusting your filters or check back later.</p>
            </div>
        @endif
    </div>
@endsection
