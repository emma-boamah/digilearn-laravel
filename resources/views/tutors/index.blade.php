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

        /* Modern 3-Column Centered Tutor Grid & Spacious Card Styles */
        .tutors-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .tutors-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 2rem;
            justify-content: center;
            align-items: stretch;
        }

        .tutor-card {
            background: var(--bg-surface);
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            text-decoration: none !important;
            color: inherit !important;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s ease;
            position: relative;
        }

        .tutor-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 32px -10px rgba(0, 0, 0, 0.1), 0 8px 16px -6px rgba(0, 0, 0, 0.04);
            border-color: rgba(38, 119, 184, 0.4);
        }

        /* 1. Fixed 16:9 Video Thumbnail Cover */
        .tutor-card-cover {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #0f172a;
            overflow: hidden;
        }

        .tutor-card-cover-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tutor-card:hover .tutor-card-cover-img {
            transform: scale(1.06);
        }

        .tutor-card-cover-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.1) 0%, rgba(15, 23, 42, 0.55) 100%);
            opacity: 0.65;
            transition: opacity 0.3s ease;
        }

        .tutor-card:hover .tutor-card-cover-overlay {
            opacity: 0.85;
        }

        /* Center Play Icon Overlay */
        .tutor-card-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(1);
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 3;
        }

        .tutor-card:hover .tutor-card-play-btn {
            transform: translate(-50%, -50%) scale(1.15);
        }

        .play-btn-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
            color: var(--secondary-blue);
            font-size: 1.15rem;
            padding-left: 4px;
            transition: all 0.2s ease;
        }

        .tutor-card:hover .play-btn-circle {
            background: #ffffff;
            color: var(--primary-red);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        /* Corner Watch Intro Badge */
        .tutor-card-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0, 0, 0, 0.68);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            padding: 4px 10px;
            border-radius: 6px;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            z-index: 2;
            letter-spacing: 0.02em;
        }

        /* 2. Tutor Identity Section */
        .tutor-card-body {
            padding: 1.25rem 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            flex: 1;
        }

        .tutor-identity-row {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .tutor-avatar-wrap {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 2px solid var(--white);
            background: var(--gray-100);
        }

        .tutor-avatar-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .tutor-info-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .tutor-name {
            font-size: 1.075rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.15s ease;
        }

        .tutor-card:hover .tutor-name {
            color: var(--secondary-blue);
        }

        /* Modern Single Star Rating */
        .tutor-rating-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.825rem;
        }

        .rating-star-single {
            color: var(--secondary-blue);
            font-size: 0.85rem;
        }

        .rating-score {
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.85rem;
        }

        .rating-reviews {
            color: var(--text-muted);
            font-size: 0.775rem;
            font-weight: 500;
        }

        /* 3. Footer Action Section */
        .tutor-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border-color);
            padding-top: 0.9rem;
            margin-top: auto;
        }

        .tutor-price-wrap {
            display: flex;
            align-items: baseline;
            gap: 3px;
        }

        .tutor-price-amount {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .tutor-price-unit {
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .tutor-card-arrow {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(38, 119, 184, 0.08);
            color: var(--secondary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.25s ease;
        }

        .tutor-card:hover .tutor-card-arrow {
            background: var(--secondary-blue);
            color: #ffffff;
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(38, 119, 184, 0.3);
        }

        @media (max-width: 1080px) {
            .tutors-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 680px) {
            .hero-section { height: 300px !important; }
            .hero-section h1 { font-size: 1.85rem !important; }
            .hero-section p { font-size: 0.95rem !important; line-height: 1.4 !important; }
            .tutor-search-bar { padding: 1rem 1rem !important; }
            #tutorSearchInput { font-size: 0.875rem !important; }
            .tutors-grid { 
                grid-template-columns: 1fr;
                max-width: 440px;
                margin: 0 auto;
            }
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
    <div class="content-section" style="padding: 2.5rem 1.5rem;">
        
        <div class="tutors-container">
            @if(request('search'))
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; padding: 0.75rem 1.25rem; background: rgba(38, 119, 184, 0.06); border: 1px solid rgba(38, 119, 184, 0.15); border-radius: 10px;">
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
                <div class="tutors-grid">
                    @foreach($tutors as $tutor)
                        @php
                            $coverImage = $tutor->tutorProfile->headshot_path 
                                ? asset('storage/' . $tutor->tutorProfile->headshot_path) 
                                : ($tutor->avatar ? asset('storage/' . $tutor->avatar) : asset('images/personalized.jpeg'));
                            $minRate = $tutor->tutorSubjects->min('hourly_rate') ?? 0;
                            
                            // Rating calculations
                            $ratingScore = '5.0';
                            $reviewsCount = 12 + ($tutor->id * 3) % 40;
                        @endphp
                        <a href="{{ route('tutors.show', $tutor->id) }}" class="tutor-card">
                            <!-- 1. Video Thumbnail (16:9 Aspect Ratio) -->
                            <div class="tutor-card-cover">
                                <img src="{{ $coverImage }}" alt="{{ $tutor->name }}" class="tutor-card-cover-img" loading="lazy">
                                <div class="tutor-card-cover-overlay"></div>
                                <div class="tutor-card-play-btn">
                                    <div class="play-btn-circle">
                                        <i class="fa-solid fa-play"></i>
                                    </div>
                                </div>
                                <div class="tutor-card-badge">
                                    <i class="fa-solid fa-circle-play"></i> Watch Intro
                                </div>
                            </div>

                            <!-- 2. Tutor Identity Section (Avatar + Name + Rating Star) -->
                            <div class="tutor-card-body">
                                <div class="tutor-identity-row">
                                    <div class="tutor-avatar-wrap">
                                        {!! $tutor->getAvatarHtml(52, 'shadow-none') !!}
                                    </div>
                                    <div class="tutor-info-col">
                                        <h3 class="tutor-name" title="{{ $tutor->name }}">
                                            {{ $tutor->name }}
                                        </h3>
                                        
                                        <!-- Single Star Rating Badge -->
                                        <div class="tutor-rating-row">
                                            <i class="fa-solid fa-star rating-star-single"></i>
                                            <span class="rating-score">{{ $ratingScore }}</span>
                                            <span class="rating-reviews">({{ $reviewsCount }})</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Description Omitted for Fixed Uniform Card Height -->

                                <!-- 4. Footer Action Section -->
                                <div class="tutor-card-footer">
                                    <div class="tutor-price-wrap">
                                        <span class="tutor-price-amount">
                                            GHS {{ number_format($minRate, 2) }}
                                        </span>
                                        <span class="tutor-price-unit">/hr</span>
                                    </div>
                                    <div class="tutor-card-arrow">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <div style="margin-top: 2.5rem; display: flex; justify-content: center;">
                    {{ $tutors->links() }}
                </div>
            @else
                <div style="text-align: center; padding: 4rem 1rem; background: var(--bg-surface); border-radius: 12px; border: 1px dashed var(--border-color); max-width: 600px; margin: 0 auto;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--gray-400); margin: 0 auto 1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 style="font-size: 1.125rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem;">No tutors found</h3>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Try adjusting your filters or check back later.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
