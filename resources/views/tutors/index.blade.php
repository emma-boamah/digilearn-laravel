@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
@endsection

@section('subjects-filter')
    <div class="subjects-filter">
        <span class="filter-label">Subjects:</span>
        <a href="{{ route('tutors.index', request('search') ? ['search' => request('search')] : []) }}" class="subject-chip {{ !request('subject_id') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="fas fa-th-large"></i> All Subjects
        </a>
        @php
            $subjectIcons = [
                'Mathematics' => 'fas fa-calculator',
                'English' => 'fas fa-book',
                'Science' => 'fas fa-flask',
                'Social Studies' => 'fas fa-globe',
                'History' => 'fas fa-clock',
                'Computer Science' => 'fas fa-laptop-code',
                'Physics' => 'fas fa-bolt',
                'Chemistry' => 'fas fa-atom',
                'Biology' => 'fas fa-heartbeat',
                'Geography' => 'fas fa-globe-africa',
                'Natural Science' => 'fas fa-leaf',
                'Integrated Science' => 'fas fa-microscope',
                'Art' => 'fas fa-palette',
                'Music' => 'fas fa-music',
                'General Education' => 'fas fa-graduation-cap',
                'Primary Education' => 'fas fa-school',
                'Junior High School' => 'fas fa-chalkboard-teacher',
                'Senior High School' => 'fas fa-university',
                'Business Administration' => 'fas fa-briefcase',
                'Medicine' => 'fas fa-stethoscope',
                'French' => 'fas fa-language',
                'Economics' => 'fas fa-chart-line',
                'Literature' => 'fas fa-feather-alt',
                'Religious Studies' => 'fas fa-pray',
                'Physical Education' => 'fas fa-running',
                'ICT' => 'fas fa-desktop',
            ];
        @endphp
        @foreach($subjects as $subject)
            <a href="{{ route('tutors.index', array_merge(['subject_id' => $subject->id], request('search') ? ['search' => request('search')] : [])) }}" class="subject-chip {{ request('subject_id') == $subject->id ? 'active' : '' }}" style="text-decoration: none;">
                <i class="{{ $subjectIcons[$subject->name] ?? 'fas fa-book-open' }}"></i> {{ $subject->name }}
            </a>
        @endforeach
    </div>
@endsection

@section('content')
    <style>
        #tutorSearchInput::placeholder {
            color: var(--gray-400);
        }
        #tutorSearchInput:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }
        @media (max-width: 640px) {
            .hero-section { height: 320px !important; }
            .hero-section h1 { font-size: 1.85rem !important; }
            .hero-section p { font-size: 0.95rem !important; line-height: 1.4 !important; }
            .tutor-search-bar { padding: 1rem 1rem !important; }
            #tutorSearchInput { font-size: 0.875rem !important; }
        }
    </style>
    <!-- Hero Section (clean for ads) -->
    <div class="hero-section" style="background: linear-gradient(135deg, var(--gray-900), var(--secondary-blue)); position: relative; height: 340px; display: flex; align-items: center; justify-content: center; text-align: center; color: white;">
        <div style="position: absolute; inset: 0; background: url('{{ asset('images/personalized.jpeg') }}') center/cover; opacity: 0.3;"></div>
        <div class="hero-content" style="position: relative; z-index: 10; padding: 0 1rem;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.75rem; color: white;">Personalized Learning</h1>
            <p style="font-size: 1.125rem; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.5;">Connect with expert tutors and learn at your own pace.</p>
        </div>
    </div>

    <!-- Search Bar (below hero) -->
    <div class="tutor-search-bar" style="padding: 1.25rem 2rem; background: var(--bg-surface); border-bottom: 1px solid var(--border-color);">
        <form method="GET" action="{{ route('tutors.index') }}" id="tutorSearchForm" style="position: relative; max-width: 640px; margin: 0 auto;">
            @if(request('subject_id'))
                <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
            @endif
            <div style="position: relative; display: flex; align-items: center;">
                <i class="fas fa-search" style="position: absolute; left: 1.125rem; color: var(--gray-400); font-size: 0.95rem; pointer-events: none; z-index: 2;"></i>
                <input 
                    type="text" 
                    name="search" 
                    id="tutorSearchInput"
                    value="{{ request('search') }}" 
                    placeholder="Search by tutor name, subject, or expertise..." 
                    style="width: 100%; padding: 0.8rem 3rem 0.8rem 2.75rem; border: 1.5px solid var(--gray-200); border-radius: 50px; font-size: 0.925rem; color: var(--text-main); background: var(--bg-surface); outline: none; transition: all 0.25s ease;"
                >
                @if(request('search'))
                    <a href="{{ route('tutors.index', request('subject_id') ? ['subject_id' => request('subject_id')] : []) }}" 
                       style="position: absolute; right: 1rem; color: var(--gray-400); font-size: 0.95rem; z-index: 2; transition: color 0.2s;"
                       onmouseover="this.style.color='var(--gray-700)'" onmouseout="this.style.color='var(--gray-400)'"
                       title="Clear search">
                        <i class="fas fa-times-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Content Section -->
    <div class="content-section" style="padding: 2rem;">
        
        @if(request('search'))
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; padding: 0.75rem 1.25rem; background: rgba(38, 119, 184, 0.06); border: 1px solid rgba(38, 119, 184, 0.15); border-radius: 10px;">
                <i class="fas fa-search" style="color: var(--secondary-blue); font-size: 0.875rem;"></i>
                <span style="font-size: 0.9rem; color: var(--text-main);">
                    Showing results for "<strong>{{ request('search') }}</strong>"
                    <span style="color: var(--text-muted); margin-left: 0.25rem;">· {{ $tutors->total() }} {{ Str::plural('tutor', $tutors->total()) }} found</span>
                </span>
                <a href="{{ route('tutors.index', request('subject_id') ? ['subject_id' => request('subject_id')] : []) }}" 
                   style="margin-left: auto; font-size: 0.8rem; color: var(--secondary-blue); text-decoration: none; font-weight: 600; transition: opacity 0.2s;"
                   onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                    Clear
                </a>
            </div>
        @endif
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
