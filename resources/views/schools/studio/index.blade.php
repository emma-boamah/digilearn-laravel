@extends('schools.admin.layout')

@section('title', 'Content Studio')

@section('topbar-actions')
    <a href="{{ route('school.studio.video.create') }}" class="sa-btn sa-btn-outline sa-btn-sm" style="background: var(--bg-card);">
        <i class="fas fa-video"></i> Upload Video
    </a>
    <a href="{{ route('school.studio.quiz.create') }}" class="sa-btn sa-btn-primary sa-btn-sm">
        <i class="fas fa-question-circle"></i> Create Quiz
    </a>
@endsection

@section('styles')
<style>
    .studio-tabs {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 2px;
    }

    .studio-tab {
        padding: 8px 16px;
        font-weight: 500;
        color: var(--text-muted);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
    }

    .studio-tab:hover {
        color: var(--primary);
    }

    .studio-tab.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .content-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }

    .content-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .content-thumb {
        height: 160px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 2rem;
        position: relative;
    }

    .content-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .content-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .content-body {
        padding: 16px;
    }

    .content-title {
        font-weight: 600;
        font-size: 1.05rem;
        margin-bottom: 8px;
        color: var(--text);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .content-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 12px;
    }

    .content-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border);
        padding-top: 12px;
    }
</style>
@endsection

@section('content')
    <div class="studio-tabs">
        <div class="studio-tab active" onclick="switchTab('videos')"><i class="fas fa-video"></i> Private Videos</div>
        <div class="studio-tab" onclick="switchTab('quizzes')"><i class="fas fa-tasks"></i> Private Quizzes</div>
    </div>

    <!-- Videos Section -->
    <div id="videos-section">
        <div class="content-grid">
            @forelse($videos as $video)
                <div class="content-card">
                    <div class="content-thumb">
                        @if($video->thumbnail_path)
                            <img src="{{ Storage::url($video->thumbnail_path) }}" alt="{{ $video->title }}">
                        @elseif($video->video_source == 'youtube' && $video->external_video_id)
                            <img src="https://img.youtube.com/vi/{{ $video->external_video_id }}/mqdefault.jpg" alt="Thumbnail">
                        @else
                            <i class="fas fa-play-circle"></i>
                        @endif
                        <div class="content-badge">{{ ucfirst($video->grade_level) }}</div>
                    </div>
                    <div class="content-body">
                        <div class="content-title">{{ $video->title }}</div>
                        <div class="content-meta">
                            <span><i class="fas fa-book"></i> {{ $video->subject->name ?? 'Any' }}</span>
                            <span><i class="fas fa-clock"></i> {{ gmdate("i:s", $video->duration_seconds ?? 0) }}</span>
                        </div>
                        <div class="content-actions">
                            <span style="font-size: 0.8rem; color: var(--success);"><i class="fas fa-check-circle"></i> Published</span>
                            <a href="#" class="sa-btn sa-btn-outline sa-btn-sm">Edit</a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--bg-card); border-radius: 12px; border: 1px dashed var(--border);">
                    <i class="fas fa-video" style="font-size: 3rem; color: var(--border); margin-bottom: 16px;"></i>
                    <h3 style="margin-bottom: 8px;">No Private Videos</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Upload your own instructional videos to share with your students.</p>
                    <a href="{{ route('school.studio.video.create') }}" class="sa-btn sa-btn-primary">Upload First Video</a>
                </div>
            @endforelse
        </div>
        <div style="margin-top: 24px;">{{ $videos->links() }}</div>
    </div>

    <!-- Quizzes Section -->
    <div id="quizzes-section" style="display: none;">
        <div class="content-grid">
            @forelse($quizzes as $quiz)
                <div class="content-card">
                    <div class="content-thumb" style="background: rgba(37, 99, 235, 0.05); color: var(--primary);">
                        <i class="fas fa-question-circle"></i>
                        <div class="content-badge" style="background: var(--primary);">{{ ucfirst($quiz->grade_level) }}</div>
                    </div>
                    <div class="content-body">
                        <div class="content-title">{{ $quiz->title }}</div>
                        <div class="content-meta">
                            <span><i class="fas fa-book"></i> {{ $quiz->subject->name ?? 'Any' }}</span>
                            <span><i class="fas fa-stopwatch"></i> {{ $quiz->time_limit_minutes }}m</span>
                        </div>
                        <div class="content-actions">
                            <div style="display: flex; gap: 8px;">
                                <a href="#" class="sa-btn sa-btn-outline sa-btn-sm" style="font-size: 0.75rem;">Manage</a>
                                @if(!$quiz->share_requested && $quiz->school_id)
                                    <form method="POST" action="{{ route('school.studio.quiz.share', $quiz->id) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-sm" style="background: rgba(37,99,235,0.1); color: var(--primary); border: none; font-size: 0.75rem;">
                                            <i class="fas fa-globe"></i> Share Globally
                                        </button>
                                    </form>
                                @elseif($quiz->share_requested && $quiz->school_id)
                                    <span style="font-size: 0.75rem; color: var(--warning); padding: 4px 8px; background: rgba(217,119,6,0.1); border-radius: 4px;">Pending Review</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--bg-card); border-radius: 12px; border: 1px dashed var(--border);">
                    <i class="fas fa-tasks" style="font-size: 3rem; color: var(--border); margin-bottom: 16px;"></i>
                    <h3 style="margin-bottom: 8px;">No Private Quizzes</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Create custom quizzes and CBTs for your students.</p>
                    <a href="{{ route('school.studio.quiz.create') }}" class="sa-btn sa-btn-primary">Create First Quiz</a>
                </div>
            @endforelse
        </div>
        <div style="margin-top: 24px;">{{ $quizzes->links() }}</div>
    </div>
@endsection

@section('scripts')
<script>
    function switchTab(tab) {
        // Update tabs
        document.querySelectorAll('.studio-tab').forEach(t => t.classList.remove('active'));
        event.currentTarget.classList.add('active');

        // Update content
        if (tab === 'videos') {
            document.getElementById('videos-section').style.display = 'block';
            document.getElementById('quizzes-section').style.display = 'none';
        } else {
            document.getElementById('videos-section').style.display = 'none';
            document.getElementById('quizzes-section').style.display = 'block';
        }
    }
</script>
@endsection
