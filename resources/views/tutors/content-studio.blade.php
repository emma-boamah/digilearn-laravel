@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Content Studio & Marketplace Portfolio</h2>
        <a href="{{ route('tutors.content.course.create') }}" style="background: var(--secondary-blue); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.85rem;">
            <i class="fa-solid fa-plus" style="margin-right: 0.35rem;"></i> Create New Course
        </a>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Content Stats Overview -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Published Courses</span>
                    <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $courses->total() ?? count($courses) }}</h3>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Videos Uploaded</span>
                    <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $videosCount ?? 0 }}</h3>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-video"></i>
                </div>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">PDF Documents</span>
                    <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $documentsCount ?? 0 }}</h3>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Quizzes Built</span>
                    <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $quizzesCount ?? 0 }}</h3>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(225, 30, 45, 0.1); color: var(--primary-red); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
            </div>
        </div>

        <!-- Course Cards Grid -->
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">My Courses</h3>
        @if(isset($courses) && count($courses) > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                @foreach($courses as $course)
                    <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="height: 140px; background: var(--gray-200); position: relative; overflow: hidden;">
                                @if($course->thumbnail_path)
                                    <img src="{{ asset('storage/' . $course->thumbnail_path) }}" alt="{{ $course->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); font-size: 2.5rem;">
                                        <i class="fa-solid fa-book-bookmark"></i>
                                    </div>
                                @endif
                                <span style="position: absolute; top: 10px; right: 10px; background: {{ $course->status === 'published' ? '#d1fae5' : '#fef3c7' }}; color: {{ $course->status === 'published' ? '#065f46' : '#92400e' }}; padding: 0.25rem 0.65rem; border-radius: 9999px; font-weight: 700; font-size: 0.75rem;">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </div>
                            <div style="padding: 1.25rem;">
                                <h4 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 0.35rem 0; color: var(--text-main);">{{ $course->title }}</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 0.85rem 0; line-height: 1.4;">{{ Str::limit($course->description, 90) }}</p>
                                
                                <div style="display: flex; gap: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                                    <span><i class="fa-solid fa-video"></i> {{ $course->videos->count() }} Videos</span>
                                    <span><i class="fa-solid fa-file"></i> {{ $course->documents->count() }} Docs</span>
                                    <span><i class="fa-solid fa-circle-question"></i> {{ $course->quizzes->count() }} Quizzes</span>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-main);">
                            <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                {{ $course->price > 0 ? 'GHS ' . number_format($course->price, 2) : 'Free' }}
                            </span>
                            <form action="{{ route('tutors.content.course.delete', $course->id) }}" method="POST" onsubmit="return confirm('Delete this course?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: var(--primary-red); cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 3rem; text-align: center; color: var(--text-muted); margin-bottom: 2rem;">
                <i class="fa-solid fa-book-open-reader" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p style="margin: 0 0 1rem 0; font-size: 1rem;">You haven't created any courses yet.</p>
                <a href="{{ route('tutors.content.course.create') }}" style="display: inline-block; background: var(--secondary-blue); color: white; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                    Build Your First Course
                </a>
            </div>
        @endif

        <!-- Quick Asset Upload Modals / Forms -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <!-- Upload Video Form -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <i class="fa-solid fa-cloud-arrow-up" style="color: var(--secondary-blue); margin-right: 0.35rem;"></i> Add Video Lesson (Vimeo / YouTube)
                </h4>
                <form action="{{ route('tutors.content.video.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 0.85rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Video Title</label>
                        <input type="text" name="title" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                    <div style="margin-bottom: 0.85rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">YouTube or Vimeo URL</label>
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Grade Level</label>
                            <input type="text" name="grade_level" value="JHS 1" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Subject ID</label>
                            <input type="number" name="subject_id" value="1" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                        </div>
                    </div>
                    <button type="submit" style="width: 100%; background: var(--secondary-blue); color: white; border: none; padding: 0.6rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        Save Video Asset
                    </button>
                </form>
            </div>

            <!-- Upload Document Form -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <i class="fa-solid fa-file-arrow-up" style="color: #10b981; margin-right: 0.35rem;"></i> Upload Document Resource (PDF / DOC)
                </h4>
                <form action="{{ route('tutors.content.document.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 0.85rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Document Title</label>
                        <input type="text" name="title" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                    <div style="margin-bottom: 0.85rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Document File (PDF/DOCX)</label>
                        <input type="file" name="document_file" required style="width: 100%; padding: 0.35rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Grade Level</label>
                        <input type="text" name="grade_level" value="JHS 1" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                    <button type="submit" style="width: 100%; background: #10b981; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        Upload Document
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
