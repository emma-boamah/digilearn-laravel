@extends('layouts.dashboard')

@section('content')
<!-- KaTeX for math formulas -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css" crossorigin="anonymous">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js" crossorigin="anonymous" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" crossorigin="anonymous" nonce="{{ request()->attributes->get('csp_nonce') }}"
    onload="renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '$', right: '$', display: false}]});"></script>

<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 space-y-8" x-data="topicReaderApp()">
    <!-- Navigation Breadcrumbs & Completion Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('guided-learning.index', ['subject_id' => $subject->id]) }}"
               class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors gap-1.5 mb-2">
                <i class="fas fa-arrow-left"></i> Back to {{ $subject->name }} Curriculum
            </a>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span>{{ $strand->title }}</span>
                <span>/</span>
                <span>{{ $subStrand->title }}</span>
            </div>
        </div>

        <div>
            <button type="button" 
                    @click="toggleComplete()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm"
                    :class="status === 'completed' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200'">
                <i class="fas" :class="status === 'completed' ? 'fa-check-circle' : 'fa-circle'"></i>
                <span x-text="status === 'completed' ? 'Completed' : 'Mark as Complete'"></span>
            </button>
        </div>
    </div>

    <!-- Topic Card Header -->
    <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex items-center gap-3">
            @if($indicator->indicator_code)
                <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-mono font-bold text-xs rounded-lg border border-blue-200 dark:border-blue-800">
                    {{ $indicator->indicator_code }}
                </span>
            @endif
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Learning Objective</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">
            {{ $indicator->title }}
        </h1>

        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
            {{ $indicator->description }}
        </p>

        @if($indicator->exemplars)
        <div class="mt-4 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 text-amber-900 dark:text-amber-200">
            <h4 class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 mb-1 text-amber-800 dark:text-amber-300">
                <i class="fas fa-lightbulb"></i> Teaching Exemplars & Key Activities
            </h4>
            <div class="text-xs space-y-1 whitespace-pre-line leading-relaxed">
                {{ $indicator->exemplars }}
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content Area: Textbook Lessons -->
    @if($textbookSections->isNotEmpty())
        @foreach($textbookSections as $sec)
        <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-6">
            <div class="border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-book-reader text-blue-600"></i>
                    {{ $sec->title }}
                </h2>
                @if($sec->chapter)
                    <span class="text-xs text-gray-400">From {{ $sec->chapter->textbook->title ?? 'Textbook' }}</span>
                @endif
            </div>

            <!-- Content HTML/Markdown with math rendering -->
            <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 text-sm leading-relaxed space-y-4">
                {!! $sec->content_html !!}
            </div>

            <!-- Media / Diagrams for this section -->
            @if($sec->media->isNotEmpty())
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Educational Diagrams & Visual Aids</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($sec->media as $mediaItem)
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-900">
                        <img src="{{ Storage::url($mediaItem->file_path) }}" alt="{{ $mediaItem->caption ?? 'Diagram' }}" class="w-full h-auto object-cover">
                        @if($mediaItem->caption)
                            <p class="p-3 text-xs text-gray-600 dark:text-gray-400 italic text-center">{{ $mediaItem->caption }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach
    @else
    <!-- Placeholder if textbook lesson not yet extracted -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 text-center shadow-sm">
        <i class="fas fa-feather-alt text-3xl text-indigo-400 mb-2"></i>
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Detailed Lesson Being Compiled</h3>
        <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">
            The textbook lessons for this indicator are being synthesized by AI instructional designers. Use the related platform video lectures and practice quizzes below to master this topic!
        </p>
    </div>
    @endif

    <!-- Related Platform Resources -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Related Video Lessons -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-play-circle text-red-500"></i>
                Recommended Video Lessons
            </h3>

            <div class="space-y-3">
                @forelse($relatedVideos as $video)
                <a href="{{ route('dashboard.main') }}" class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 hover:bg-red-50/40 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <i class="fas fa-play text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate group-hover:text-red-600 transition-colors">{{ $video->title }}</h4>
                        <p class="text-[11px] text-gray-400 truncate">{{ $video->unit_name ?? 'Lesson Lecture' }}</p>
                    </div>
                </a>
                @empty
                <p class="text-xs text-gray-400 italic py-2">No direct video match found.</p>
                @endforelse
            </div>
        </div>

        <!-- Practice Quizzes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i>
                Check Your Knowledge (Quiz)
            </h3>

            <div class="space-y-3">
                @forelse($relatedQuizzes as $quiz)
                <a href="{{ route('quiz.take', $quiz) }}" class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-750 hover:bg-emerald-50/40 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <i class="fas fa-question text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate group-hover:text-emerald-600 transition-colors">{{ $quiz->title }}</h4>
                        <p class="text-[11px] text-gray-400 truncate">Practice Assessment</p>
                    </div>
                </a>
                @empty
                <p class="text-xs text-gray-400 italic py-2">No practice quizzes available for this topic yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Student Personal Study Notes -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-pen text-blue-600"></i>
                Personal Study Notes
            </h3>
            <span class="text-[11px] text-gray-400" x-text="saveStatus"></span>
        </div>
        <textarea x-model="notes" @input.debounce.800ms="saveNotes()" rows="3"
            placeholder="Jot down formulas, reminders, or questions for your tutor on this topic..."
            class="w-full text-sm rounded-2xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white p-3.5 focus:ring-blue-500 focus:border-blue-500"></textarea>
    </div>

    <!-- Bottom Navigation (Previous / Next) -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        @if($prevIndicatorId)
            <a href="{{ route('guided-learning.topic', $prevIndicatorId) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-left text-xs"></i> Previous Objective
            </a>
        @else
            <div></div>
        @endif

        @if($nextIndicatorId)
            <a href="{{ route('guided-learning.topic', $nextIndicatorId) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition-colors">
                Next Objective <i class="fas fa-chevron-right text-xs"></i>
            </a>
        @endif
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function topicReaderApp() {
    return {
        status: '{{ $status }}',
        notes: @json($progress->notes ?? ''),
        saveStatus: 'Saved',

        async toggleComplete() {
            try {
                const response = await fetch(`/guided-learning/topic/{{ $indicator->id }}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: this.status === 'completed' ? 'not_started' : 'completed'
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.status = data.status;
                }
            } catch (e) {
                console.error('Failed to update status:', e);
            }
        },

        async saveNotes() {
            this.saveStatus = 'Saving...';
            try {
                const response = await fetch(`/guided-learning/topic/{{ $indicator->id }}/notes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ notes: this.notes })
                });
                const data = await response.json();
                if (data.success) {
                    this.saveStatus = 'Saved ✓';
                }
            } catch (e) {
                this.saveStatus = 'Failed to save';
            }
        }
    };
}
</script>
@endsection
