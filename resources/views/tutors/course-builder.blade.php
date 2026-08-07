@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Course Curriculum Builder</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1000px; margin: 0 auto;">
        @if($errors->any())
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #fca5a5;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tutors.content.course.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Course Info -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    Step 1: Course Overview & Pricing
                </h3>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Course Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Master JHS 2 Mathematics & Algebra" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Course Description & Learning Outcomes</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-family: inherit;">{{ old('description') }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Subject</label>
                        <select name="subject" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->name }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Grade Level</label>
                        <select name="grade_level" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Course Price (GHS / Credits)</label>
                        <input type="number" step="0.50" min="0" name="price" value="{{ old('price', 0) }}" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Course Thumbnail Image</label>
                        <input type="file" name="thumbnail" accept="image/*" style="width: 100%; padding: 0.45rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Publication Status</label>
                        <select name="status" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                            <option value="published">Published (Visible in Public Marketplace)</option>
                            <option value="draft">Draft (Save for later editing)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 2: Content Selection -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    Step 2: Attach Video Lessons, Documents & Quizzes
                </h3>

                <!-- Videos Picker -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Select Video Lessons to Include:</label>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem; background: var(--bg-main);">
                        @if(isset($videos) && count($videos) > 0)
                            @foreach($videos as $vid)
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; cursor: pointer;">
                                    <input type="checkbox" name="videos[]" value="{{ $vid->id }}">
                                    <i class="fa-solid fa-video" style="color: var(--secondary-blue);"></i> {{ $vid->title }}
                                </label>
                            @endforeach
                        @else
                            <span style="font-size: 0.85rem; color: var(--text-muted);">No videos found in your studio. You can upload videos on the Content Studio page.</span>
                        @endif
                    </div>
                </div>

                <!-- Documents Picker -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Select Document Resources to Include:</label>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem; background: var(--bg-main);">
                        @if(isset($documents) && count($documents) > 0)
                            @foreach($documents as $doc)
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; cursor: pointer;">
                                    <input type="checkbox" name="documents[]" value="{{ $doc->id }}">
                                    <i class="fa-solid fa-file-pdf" style="color: #10b981;"></i> {{ $doc->title }}
                                </label>
                            @endforeach
                        @else
                            <span style="font-size: 0.85rem; color: var(--text-muted);">No documents found in your studio.</span>
                        @endif
                    </div>
                </div>

                <!-- Quizzes Picker -->
                <div>
                    <label style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Select Quizzes to Include:</label>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem; background: var(--bg-main);">
                        @if(isset($quizzes) && count($quizzes) > 0)
                            @foreach($quizzes as $quiz)
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; cursor: pointer;">
                                    <input type="checkbox" name="quizzes[]" value="{{ $quiz->id }}">
                                    <i class="fa-solid fa-circle-question" style="color: var(--primary-red);"></i> {{ $quiz->title }}
                                </label>
                            @endforeach
                        @else
                            <span style="font-size: 0.85rem; color: var(--text-muted);">No quizzes found in your studio.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 3rem;">
                <button type="submit" style="background: var(--secondary-blue); color: white; border: none; padding: 0.85rem 2.5rem; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 0.5rem;"></i> Publish Course to Marketplace
                </button>
            </div>
        </form>
    </div>
@endsection
