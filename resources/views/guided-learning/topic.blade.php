@extends('layouts.dashboard-components')

@section('head')
<script src="https://cdn.tailwindcss.com"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
  tailwind.config = {
    darkMode: ['class', '[data-theme="dark"]'],
  }
</script>
<!-- MathLive for Math & Scientific Formula Rendering -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
<script defer src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script type="module" nonce="{{ request()->attributes->get('csp_nonce') }}">
    import { renderMathInElement } from "https://unpkg.com/mathlive?module";
    window.renderMathInElement = renderMathInElement;
</script>
@endsection

@section('content')

<div class="max-w-7xl mx-auto pt-20 sm:pt-24 pb-12 px-4 sm:px-6" x-data="topicReaderApp()" id="topic-content">
    
    <!-- Top Navigation Bar & Completion Controls (full width) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('guided-learning.index', ['subject_id' => $encodedSubjectId ?? $subject->id]) }}"
                   class="inline-flex items-center text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i> {{ $subject->name }} Roadmap
                </a>
                <span class="text-gray-300 dark:text-gray-600">•</span>
                <a href="{{ route('guided-learning.index') }}"
                   class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    All Subjects
                </a>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                <span class="font-medium">{{ $strand->title }}</span>
                <span>/</span>
                <span>{{ $subStrand->title }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="toggleComplete()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs"
                    :class="status === 'completed' 
                        ? 'border-2 border-blue-600 bg-gray-100 dark:bg-gray-700 text-blue-600 dark:text-blue-400' 
                        : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200'">
                <i class="fas" :class="status === 'completed' ? 'fa-check-circle' : 'fa-circle'"></i>
                <span x-text="status === 'completed' ? 'Checkpoint Completed' : 'Mark as Complete'"></span>
            </button>

            @if($nextIndicatorId)
            <button type="button"
                    @click="completeAndNext('{{ route('guided-learning.topic', $nextIndicatorId) }}')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-blue-600 bg-gray-50 hover:bg-blue-50/60 dark:bg-gray-750 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 text-xs font-bold transition-all shadow-2xs">
                <span>Complete & Next</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
            @endif
        </div>
    </div>

    <!-- ===== Main Grid: Content (left) + Sidebar (right) ===== -->
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- ===== LEFT: Main Reading Content ===== -->
        <div class="flex-1 min-w-0">

            <!-- Topic Title & Meta Header -->
            <div class="mb-8">
                <div class="flex items-center gap-2 flex-wrap mb-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 rounded-lg">
                        {{ $subject->name }}
                    </span>
                    @if($strand->grade_label)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        {{ $strand->grade_label }}
                    </span>
                    @endif
                    <span class="text-xs font-mono font-medium px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                        {{ $indicator->indicator_code }}
                    </span>
                    @if(($studentLesson['source'] ?? '') === 'textbook' || $textbookSections->isNotEmpty())
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 flex items-center gap-1.5">
                        <i class="fas fa-book-open text-[10px]"></i> Verified Textbook
                    </span>
                    @elseif(($studentLesson['source'] ?? '') === 'ai')
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 flex items-center gap-1.5">
                        <i class="fas fa-wand-magic-sparkles text-[10px] text-blue-500"></i> AI Guided Lesson
                    </span>
                    @else
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 flex items-center gap-1.5">
                        <i class="fas fa-graduation-cap text-[10px] text-blue-500"></i> Structured Study Guide
                    </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">
                    {{ $indicator->student_title }}
                </h1>
                @if($indicator->description && $indicator->description !== $indicator->title)
                <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed mt-3">
                    {{ $indicator->description }}
                </p>
                @endif
            </div>

            <!-- Key Takeaways (if provided) -->
            @if(!empty($studentLesson['key_takeaways']) && is_array($studentLesson['key_takeaways']))
            <div class="mb-8 rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/20 p-5 sm:p-6 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                        <i class="fas fa-bullseye"></i>
                    </span>
                    <span>What You Will Learn In This Lesson</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mt-2">
                    @foreach($studentLesson['key_takeaways'] as $takeaway)
                    <div class="flex items-start gap-2 text-xs text-gray-700 dark:text-gray-300 font-medium">
                        <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 mt-0.5 text-xs flex-shrink-0"></i>
                        <span>{{ $takeaway }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Main Content Area: Textbook Lessons or Synthesized Student Lesson -->
            @if($textbookSections->isNotEmpty())
                @foreach($textbookSections as $sec)
                <div class="mb-10">
                    <!-- Section Header: Bold blue-accented heading -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200 dark:border-gray-700 pb-3 mb-5">
                        <h2 class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $sec->title }}
                        </h2>
                        @if($sec->chapter)
                            <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg">
                                {{ $sec->chapter->textbook->title ?? 'Textbook Resource' }}
                            </span>
                        @endif
                    </div>

                    <!-- Content HTML/Markdown with math rendering -->
                    <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 text-sm leading-relaxed space-y-4">
                        {!! $sec->content_html !!}
                    </div>

                    <!-- Media / Diagrams for this section -->
                    @if($sec->media->isNotEmpty())
                    <div class="mt-6 space-y-4">
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

                <!-- Exemplars / Practice Section for Textbook Lessons -->
                @if($indicator->exemplars)
                <div class="mb-10 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-5 sm:p-6 space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                            <i class="fas fa-lightbulb"></i>
                        </span>
                        <span>Let's Practice</span>
                    </div>
                    <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $indicator->exemplars }}</div>
                </div>
                @endif
            @elseif(!empty($studentLesson['html']))
                <!-- Synthesized / AI Student Lesson (Interactive & Explained for Students) -->
                <div class="mb-10 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 sm:p-8 shadow-2xs space-y-6">
                    <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 text-sm leading-relaxed space-y-4">
                        {!! $studentLesson['html'] !!}
                    </div>
                </div>
            @else
                <!-- Placeholder if lesson notes could not be synthesized -->
                <div class="py-12 text-center mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fas fa-feather-alt"></i>
                    </div>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white">Content Coming Soon</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 leading-relaxed">
                        The lesson notes for this topic are being prepared. Use the video lessons and practice quizzes on the right to start learning!
                    </p>
                </div>
            @endif

            <!-- My Notes (stays in main content flow) -->
            <div class="bg-white dark:bg-gray-800 p-5 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-3 mb-8">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-pen text-blue-600"></i>
                        My Notes
                    </h3>
                    <span class="text-[11px] font-semibold text-gray-400" x-text="saveStatus"></span>
                </div>
                <textarea x-model="notes" @input.debounce.800ms="saveNotes()" rows="3"
                    placeholder="Jot down formulas, reminders, or questions for your tutor on this topic..."
                    class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-750 text-gray-900 dark:text-white p-3.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"></textarea>
            </div>

        </div>{{-- END left column --}}

        <!-- ===== RIGHT: Sticky Sidebar ===== -->
        <div class="lg:w-72 xl:w-80 flex-shrink-0">
            <div class="lg:sticky lg:top-24 space-y-5">

                <!-- Video Lessons -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-3">
                    <h3 class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-play-circle text-red-600"></i>
                        Video Lessons
                    </h3>

                    <div class="space-y-3">
                        @forelse($relatedVideos as $video)
                        <a href="{{ route('dashboard.lesson.view', $video->id) }}" class="block group">
                            <div class="relative aspect-video w-full rounded-xl overflow-hidden bg-gray-900 border border-gray-200/80 dark:border-gray-700/80">
                                <img src="{{ $video->getThumbnailUrl() }}" 
                                     alt="{{ $video->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     onerror="this.onerror=null; this.src='{{ asset('images/video-placeholder.jpg') }}';">
                                <div class="absolute inset-0 bg-black/25 group-hover:bg-black/15 transition-colors flex items-center justify-center">
                                    <div class="w-8 h-8 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                                        <i class="fas fa-play text-[10px] ml-0.5"></i>
                                    </div>
                                </div>
                                @if($video->duration_seconds)
                                <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 rounded bg-black/80 text-[10px] font-semibold text-white">
                                    {{ gmdate($video->duration_seconds >= 3600 ? "H:i:s" : "i:s", $video->duration_seconds) }}
                                </span>
                                @endif
                            </div>
                            <h4 class="text-xs font-semibold text-gray-800 dark:text-gray-200 line-clamp-2 mt-1.5 leading-snug group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $video->title }}
                            </h4>
                        </a>
                        @empty
                        <p class="text-xs text-gray-400 italic py-2">No video lectures linked yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Practice Quizzes -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-3">
                    <h3 class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-check-circle text-blue-600"></i>
                        Practice Quiz
                    </h3>

                    <div class="space-y-2">
                        @forelse($relatedQuizzes as $quiz)
                        <a href="{{ route('quiz.take', $quiz) }}" class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors group border border-transparent hover:border-blue-200">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                <i class="fas fa-question text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 transition-colors">{{ $quiz->title }}</h4>
                                <p class="text-[10px] text-gray-400 truncate">Practice Assessment</p>
                            </div>
                        </a>
                        @empty
                        <p class="text-xs text-gray-400 italic py-2">No practice quizzes yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>{{-- END right sidebar --}}

    </div>{{-- END grid --}}

    <!-- Sticky Bottom Navigation Bar (full width, below grid) -->
    <div class="sticky bottom-6 z-20 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xl flex items-center justify-between gap-4 mt-8">
        @if($prevIndicatorId)
            <a href="{{ route('guided-learning.topic', $prevIndicatorId) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-chevron-left text-[10px]"></i>
                <span class="hidden sm:inline">Previous Checkpoint</span>
                <span class="sm:hidden">Prev</span>
            </a>
        @else
            <div></div>
        @endif

        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="toggleComplete()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
                    :class="status === 'completed' 
                        ? 'border border-blue-600 bg-gray-100 dark:bg-gray-700 text-blue-600 dark:text-blue-400' 
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200'">
                <i class="fas" :class="status === 'completed' ? 'fa-check-circle' : 'fa-circle'"></i>
                <span x-text="status === 'completed' ? 'Completed' : 'Mark Done'"></span>
            </button>

            @if($nextIndicatorId)
            <button type="button"
                    @click="completeAndNext('{{ route('guided-learning.topic', $nextIndicatorId) }}')"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl border-2 border-blue-600 bg-gray-50 hover:bg-blue-50/60 dark:bg-gray-750 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-2xs transition-all">
                <span>Next Checkpoint</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
            @else
            <a href="{{ route('guided-learning.index', ['subject_id' => $encodedSubjectId ?? $subject->id]) }}"
               class="inline-flex items-center gap-2 px-5 py-2 rounded-xl border-2 border-blue-600 bg-gray-50 hover:bg-blue-50/60 dark:bg-gray-750 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-2xs transition-all">
                <i class="fas fa-flag-checkered"></i>
                <span>Finish Roadmap</span>
            </a>
            @endif
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
// Auto-detect common math notation and render with HTML sup/sub tags
function preprocessMathNotation(container) {
    const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    while (walker.nextNode()) textNodes.push(walker.currentNode);

    textNodes.forEach(function(node) {
        let text = node.textContent;
        let changed = false;

        // Replace X^{Y} patterns first (e.g., 4^{12} → 4<sup>12</sup>)
        if (/\w\^\{/.test(text)) {
            text = text.replace(/(\w+)\^\{([^}]+)\}/g, '$1<sup>$2</sup>');
            changed = true;
        }

        // Replace X^Y patterns (e.g., 4^2 → 4<sup>2</sup>, x^n → x<sup>n</sup>)
        if (/\w\^\w/.test(text)) {
            text = text.replace(/(\w+)\^(\w+)/g, '$1<sup>$2</sup>');
            changed = true;
        }

        // Replace X_Y subscript patterns (e.g., a_1 → a<sub>1</sub>)
        if (/[a-zA-Z]_\d/.test(text)) {
            text = text.replace(/([a-zA-Z])_(\d+)/g, '$1<sub>$2</sub>');
            changed = true;
        }

        if (changed) {
            var span = document.createElement('span');
            span.innerHTML = text;
            node.parentNode.replaceChild(span, node);
        }
    });
}

// Render MathLive formulas after page loads
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('topic-content');

    function renderAll() {
        // First preprocess plain-text math patterns into $ delimiters
        preprocessMathNotation(container);
        // Then let MathLive render all delimited math
        if (window.renderMathInElement) {
            renderMathInElement(container);
        }
    }

    if (window.renderMathInElement) {
        renderAll();
    } else {
        // Retry after MathLive loads (deferred script)
        var interval = setInterval(function() {
            if (window.renderMathInElement) {
                clearInterval(interval);
                renderAll();
            }
        }, 200);
        // Stop trying after 5 seconds
        setTimeout(function() { clearInterval(interval); }, 5000);
    }
});

function topicReaderApp() {
    return {
        status: '{{ $status }}',
        notes: @json($progress->notes ?? ''),
        saveStatus: 'Saved',

        async toggleComplete() {
            try {
                const response = await fetch(`/guided-learning/topic/{{ $encodedIndicatorId ?? $indicator->id }}/toggle`, {
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

        async completeAndNext(nextUrl) {
            try {
                await fetch(`/guided-learning/topic/{{ $encodedIndicatorId ?? $indicator->id }}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'completed' })
                });
                window.location.href = nextUrl;
            } catch (e) {
                window.location.href = nextUrl;
            }
        },

        async saveNotes() {
            this.saveStatus = 'Saving...';
            try {
                const response = await fetch(`/guided-learning/topic/{{ $encodedIndicatorId ?? $indicator->id }}/notes`, {
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

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    window.__aiContext = {
        page: 'guided-learning-topic',
        subject: @json($subject->name),
        indicator: @json($indicator->title),
        strand: @json($strand->title),
        subStrand: @json($subStrand->title),
    };
</script>
@endpush

@endsection
