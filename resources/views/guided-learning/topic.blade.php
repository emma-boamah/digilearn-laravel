@extends('layouts.dashboard-components')

@section('head')
<script src="https://cdn.tailwindcss.com"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
  tailwind.config = {
    darkMode: ['class', '[data-theme="dark"]'],
    theme: {
      extend: {
        fontFamily: {
          sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
        }
      }
    }
  }
</script>
<!-- Educational Typography: Plus Jakarta Sans & Inter from Bunny Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

<!-- MathLive for Math & Scientific Formula Rendering -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
<script defer src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script type="module" nonce="{{ request()->attributes->get('csp_nonce') }}">
    import { renderMathInElement } from "https://unpkg.com/mathlive?module";
    window.renderMathInElement = renderMathInElement;
</script>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .lesson-reader {
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        font-size: 1.0625rem; /* 17px optimal reading size */
        line-height: 1.85;
        letter-spacing: -0.011em;
        color: #1f2937;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }
    .dark .lesson-reader, [data-theme="dark"] .lesson-reader {
        color: #e5e7eb;
    }

    /* Headings */
    .lesson-reader h2 {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        line-height: 1.35;
        color: #111827;
        margin-top: 1.75rem;
        margin-bottom: 1.15rem;
    }
    .dark .lesson-reader h2, [data-theme="dark"] .lesson-reader h2 {
        color: #f9fafb;
    }

    .lesson-reader h3 {
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.4;
        color: #1d4ed8;
        margin-top: 2.25rem;
        margin-bottom: 1rem;
        padding-bottom: 0.4rem;
        border-bottom: 2px solid #e5e7eb;
    }
    .dark .lesson-reader h3, [data-theme="dark"] .lesson-reader h3 {
        color: #60a5fa;
        border-bottom-color: #374151;
    }

    .lesson-reader h4 {
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: -0.015em;
        line-height: 1.45;
        color: #1f2937;
        margin-top: 1.75rem;
        margin-bottom: 0.65rem;
    }
    .dark .lesson-reader h4, [data-theme="dark"] .lesson-reader h4 {
        color: #e5e7eb;
    }

    /* Paragraphs */
    .lesson-reader p {
        margin-bottom: 1.35rem;
        line-height: 1.85;
    }

    /* Strong text */
    .lesson-reader strong {
        font-weight: 700;
        color: #111827;
    }
    .dark .lesson-reader strong, [data-theme="dark"] .lesson-reader strong {
        color: #ffffff;
    }

    /* Ordered & Unordered Lists */
    .lesson-reader ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin-top: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .lesson-reader ol > li {
        margin-bottom: 0.85rem;
        line-height: 1.75;
        padding-left: 0.35rem;
    }
    .lesson-reader ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-top: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .lesson-reader ul > li {
        margin-bottom: 0.65rem;
        line-height: 1.75;
        padding-left: 0.35rem;
    }

    /* Inline Code & Formulas */
    .lesson-reader code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.9em;
        background-color: #f3f4f6;
        color: #1d4ed8;
        padding: 0.15rem 0.4rem;
        border-radius: 0.375rem;
        font-weight: 600;
    }
    .dark .lesson-reader code, [data-theme="dark"] .lesson-reader code {
        background-color: #1e293b;
        color: #93c5fd;
    }

    /* MathLive Formulas Sizing */
    .lesson-reader math-field,
    .lesson-reader .ML__base {
        font-size: 1.15em !important;
    }
</style>
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
                @if(!empty($strand->grade_label))
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-bold text-[11px]">
                    <i class="fas fa-graduation-cap text-[9px]"></i> {{ $strand->grade_label }}
                </span>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                @endif
                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $strand->title }}</span>
                <span class="text-gray-400">/</span>
                <span class="font-medium text-gray-600 dark:text-gray-400">{{ $subStrand->title }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <template x-if="status === 'completed'">
                <button type="button" 
                        @click="toggleComplete()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs border-2 border-green-500 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                    <i class="fas fa-check-circle"></i>
                    <span>Completed</span>
                </button>
            </template>
            <template x-if="status !== 'completed'">
                <button type="button" 
                        @click="scrollToInteractiveFlow()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200">
                    <i class="fas fa-layer-group text-blue-600"></i>
                    <span>Complete Challenges</span>
                </button>
            </template>

            @if($nextIndicatorId)
            <button type="button"
                    @click="handleCompleteAndNext('{{ route('guided-learning.topic', $nextIndicatorId) }}')"
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
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 rounded-lg">
                        {{ $subject->name }}
                    </span>
                    @if(!empty($strand->grade_label))
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-lg bg-blue-600 text-white shadow-xs">
                        <i class="fas fa-graduation-cap text-[10px]"></i> {{ $strand->grade_label }}
                    </span>
                    @endif
                    <span class="text-xs font-mono font-medium px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
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

            <!-- Key Takeaways (What You Will Learn In This Lesson) -->
            @if(!empty($studentLesson['key_takeaways']) && is_array($studentLesson['key_takeaways']))
            <div class="mb-8 rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/20 p-5 sm:p-6 space-y-3.5 shadow-2xs">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                        <i class="fas fa-bullseye"></i>
                    </span>
                    <span>What You Will Learn In This Lesson</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                    @foreach($studentLesson['key_takeaways'] as $takeaway)
                    <div class="flex items-start gap-2.5 p-3 sm:p-3.5 rounded-xl bg-white/85 dark:bg-gray-800/80 border border-blue-100 dark:border-blue-900/40 shadow-2xs">
                        <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 mt-0.5 text-sm flex-shrink-0"></i>
                        <div class="lesson-reader text-sm leading-relaxed text-gray-800 dark:text-gray-200 font-medium takeaway-text">
                            {!! $takeaway !!}
                        </div>
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
                    <div class="lesson-reader max-w-none">
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
                    <div class="lesson-reader max-w-none whitespace-pre-line">{{ $indicator->exemplars }}</div>
                </div>
                @endif
            @elseif(!empty($studentLesson['interactive_segments']) && is_array($studentLesson['interactive_segments']) && count($studentLesson['interactive_segments']) > 0)
                <!-- Brilliant-Style Interleaved Interactive Learning Segments -->
                <div class="space-y-8 mb-10" id="interactive-learning-flow">
                    
                    {{-- Interactive Segments Flow Header / Progress Tracker --}}
                    <div class="flex items-center justify-between p-4 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                                <i class="fas fa-layer-group"></i>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Interactive Lesson Flow</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Read concept &bull; Solve embedded challenge &bull; Unlock next section
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                    <span x-text="completedSegments.length"></span> of {{ count($studentLesson['interactive_segments']) }} Solved
                                </span>
                                <div class="w-24 bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden mt-1">
                                    <div class="bg-blue-600 h-full rounded-full transition-all duration-500"
                                         :style="'width: ' + ((completedSegments.length / {{ count($studentLesson['interactive_segments']) }}) * 100) + '%'"></div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] font-bold text-amber-700 dark:text-amber-300">
                                🔥 <span x-text="completedSegments.length"></span> Streak
                            </span>
                        </div>
                    </div>

                    {{-- Loop through each Segment --}}
                    @foreach($studentLesson['interactive_segments'] as $index => $segment)
                    @php $segNum = $index + 1; @endphp
                    <div id="segment-card-{{ $segment['id'] }}"
                         class="rounded-2xl border transition-all duration-300 overflow-hidden shadow-sm"
                         :class="{
                             'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700': isSegmentUnlocked({{ $segment['id'] }}),
                             'bg-gray-50 dark:bg-gray-800/40 border-gray-200/60 dark:border-gray-800 opacity-60 pointer-events-none select-none': !isSegmentUnlocked({{ $segment['id'] }})
                         }">
                        
                        {{-- Segment Header --}}
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-750/30">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-2xs"
                                      :class="{
                                          'bg-green-100 text-green-700 dark:bg-green-900/60 dark:text-green-300': completedSegments.includes({{ $segment['id'] }}),
                                          'bg-blue-600 text-white': isSegmentUnlocked({{ $segment['id'] }}) && !completedSegments.includes({{ $segment['id'] }}),
                                          'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400': !isSegmentUnlocked({{ $segment['id'] }})
                                      }">
                                    <template x-if="completedSegments.includes({{ $segment['id'] }})">
                                        <i class="fas fa-check text-[11px]"></i>
                                    </template>
                                    <template x-if="!completedSegments.includes({{ $segment['id'] }})">
                                        <span>{{ $segNum }}</span>
                                    </template>
                                </span>
                                <div>
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $segment['title'] }}</h3>
                                    @if(!empty($segment['subtitle']))
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $segment['subtitle'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <template x-if="completedSegments.includes({{ $segment['id'] }})">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/40 px-2 py-0.5 rounded-full border border-green-200 dark:border-green-900">
                                        <i class="fas fa-check-circle text-[10px]"></i> Solved
                                    </span>
                                </template>
                                <template x-if="!isSegmentUnlocked({{ $segment['id'] }})">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-400">
                                        <i class="fas fa-lock text-[10px]"></i> Locked
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- Segment Body (shown when unlocked) --}}
                        <div class="p-6 sm:p-8 space-y-6">
                            
                            {{-- Concept Explanation --}}
                            <div class="lesson-reader max-w-none">
                                {!! $segment['content_html'] !!}
                            </div>

                            {{-- Embedded Hands-on Challenge Card --}}
                            @if(!empty($segment['challenge']))
                            <div class="p-5 sm:p-6 rounded-2xl border-2 transition-all"
                                 :class="{
                                     'border-green-500/40 bg-green-50/20 dark:bg-green-950/20': completedSegments.includes({{ $segment['id'] }}),
                                     'border-blue-600/30 bg-blue-50/20 dark:bg-blue-950/10': !completedSegments.includes({{ $segment['id'] }})
                                 }">
                                
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">
                                            <i class="fas fa-puzzle-piece"></i>
                                        </span>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                            Interactive Challenge
                                        </h4>
                                    </div>

                                    {{-- Hint Toggle Button --}}
                                    @if(!empty($segment['challenge']['hint']))
                                    <button type="button"
                                            @click="toggleHint({{ $segment['id'] }})"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-900 transition-colors">
                                        <i class="fas fa-lightbulb text-[10px]"></i>
                                        <span x-text="hintsRevealed[{{ $segment['id'] }}] ? 'Hide Hint' : '💡 Need a hint?'"></span>
                                    </button>
                                    @endif
                                </div>

                                {{-- Challenge Prompt --}}
                                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-relaxed mb-4">
                                    {{ $segment['challenge']['prompt'] }}
                                </p>

                                {{-- Hint Drawer --}}
                                @if(!empty($segment['challenge']['hint']))
                                <div x-show="hintsRevealed[{{ $segment['id'] }}]"
                                     x-transition
                                     class="mb-4 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-300 leading-relaxed flex items-start gap-2.5">
                                    <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
                                    <div>
                                        <strong class="font-bold">Hint:</strong> {{ $segment['challenge']['hint'] }}
                                    </div>
                                </div>
                                @endif

                                {{-- Multiple Choice Options --}}
                                @if(($segment['challenge']['type'] ?? 'multiple-choice') === 'multiple-choice')
                                <div class="grid grid-cols-1 gap-2.5 mb-4">
                                    @foreach($segment['challenge']['options'] as $optIdx => $option)
                                    <div @click="if (!completedSegments.includes({{ $segment['id'] }}) && !segmentChecking[{{ $segment['id'] }}]) { segmentAnswers[{{ $segment['id'] }}] = {{ $optIdx }}; }"
                                         class="flex items-center gap-3 p-3.5 rounded-xl border text-sm font-medium transition-all"
                                         :class="{
                                             'border-blue-600 bg-blue-50/70 dark:bg-blue-950/50 text-blue-900 dark:text-blue-100 shadow-2xs cursor-pointer': segmentAnswers[{{ $segment['id'] }}] === {{ $optIdx }} && !completedSegments.includes({{ $segment['id'] }}),
                                             'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-blue-300 cursor-pointer': segmentAnswers[{{ $segment['id'] }}] !== {{ $optIdx }} && !completedSegments.includes({{ $segment['id'] }}),
                                             'border-green-500 bg-green-50 dark:bg-green-950/40 text-green-900 dark:text-green-200 font-bold': completedSegments.includes({{ $segment['id'] }}) && {{ $optIdx }} === (segmentFeedback[{{ $segment['id'] }}]?.correct_answer ?? {{ $segment['challenge']['correct_answer'] ?? 0 }}),
                                             'opacity-60 cursor-default': completedSegments.includes({{ $segment['id'] }})
                                         }">
                                        <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                              :class="segmentAnswers[{{ $segment['id'] }}] === {{ $optIdx }} ? 'border-blue-600 bg-blue-600' : 'border-gray-300 dark:border-gray-600'">
                                            <span x-show="segmentAnswers[{{ $segment['id'] }}] === {{ $optIdx }}" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        </span>
                                        <span class="flex-1">{!! $option !!}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Feedback & Explanation Box --}}
                                <div x-show="segmentFeedback[{{ $segment['id'] }}]" class="mb-4">
                                    <template x-if="segmentFeedback[{{ $segment['id'] }}]?.is_correct">
                                        <div class="p-3.5 rounded-xl bg-green-100 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-xs text-green-900 dark:text-green-200 space-y-1">
                                            <div class="flex items-center gap-1.5 font-bold">
                                                <i class="fas fa-check-circle text-green-600"></i>
                                                <span>Awesome! Correct answer.</span>
                                            </div>
                                            <p class="leading-relaxed" x-text="segmentFeedback[{{ $segment['id'] }}]?.explanation"></p>
                                        </div>
                                    </template>
                                    <template x-if="segmentFeedback[{{ $segment['id'] }}] && !segmentFeedback[{{ $segment['id'] }}]?.is_correct">
                                        <div class="p-3.5 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-xs text-red-900 dark:text-red-200 space-y-1">
                                            <div class="flex items-center gap-1.5 font-bold">
                                                <i class="fas fa-times-circle text-red-500"></i>
                                                <span>Not quite right. Review the explanation or click the hint to try again!</span>
                                            </div>
                                            <p class="leading-relaxed text-gray-700 dark:text-gray-300" x-text="segmentFeedback[{{ $segment['id'] }}]?.explanation"></p>
                                        </div>
                                    </template>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center justify-between gap-3 pt-2">
                                    <template x-if="!completedSegments.includes({{ $segment['id'] }})">
                                        <button type="button"
                                                @click="checkSegmentChallenge({{ $segment['id'] }})"
                                                :disabled="segmentAnswers[{{ $segment['id'] }}] === undefined || segmentChecking[{{ $segment['id'] }}]"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md transition-all">
                                            <template x-if="segmentChecking[{{ $segment['id'] }}]">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </template>
                                            <template x-if="!segmentChecking[{{ $segment['id'] }}]">
                                                <i class="fas fa-arrow-right text-[10px]"></i>
                                            </template>
                                            <span x-text="segmentChecking[{{ $segment['id'] }}] ? 'Checking...' : 'Check & Unlock Next'"></span>
                                        </button>
                                    </template>

                                    <template x-if="completedSegments.includes({{ $segment['id'] }})">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-green-700 dark:text-green-300">
                                            <i class="fas fa-check-double text-green-600"></i> Segment Mastered & Unlocked
                                        </span>
                                    </template>
                                </div>

                            </div>
                            @endif

                        </div>
                    </div>
                    @endforeach

                </div>
            @elseif(!empty($studentLesson['html']))
                <!-- Fallback: Standard Synthesized Student Lesson -->
                <div class="mb-10 rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 sm:p-10 shadow-sm space-y-6">
                    <div class="lesson-reader max-w-none">
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
                        <div class="text-center py-3 space-y-2">
                            <p class="text-xs text-gray-400 italic">No matching video lessons found for this topic.</p>
                            <a href="https://www.youtube.com/results?search_query={{ urlencode($indicator->student_title . ' ' . $subject->name . ' explained') }}"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-red-600 hover:text-red-700 transition-colors">
                                <i class="fab fa-youtube"></i> Search YouTube
                                <i class="fas fa-external-link-alt text-[8px]"></i>
                            </a>
                        </div>
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
                <span class="hidden sm:inline">Previous Topic</span>
                <span class="sm:hidden">Prev</span>
            </a>
        @else
            <div></div>
        @endif

        <div class="flex items-center gap-2">
            <template x-if="status === 'completed'">
                <button type="button" 
                        @click="toggleComplete()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all border border-green-500 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                    <i class="fas fa-check-circle"></i>
                    <span>Completed</span>
                </button>
            </template>
            <template x-if="status !== 'completed'">
                <button type="button" 
                        @click="scrollToInteractiveFlow()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-blue-600 hover:bg-blue-700 text-white shadow-2xs">
                    <i class="fas fa-layer-group"></i>
                    <span>Complete Challenges</span>
                </button>
            </template>

            @if($nextIndicatorId)
            <button type="button"
                    @click="handleCompleteAndNext('{{ route('guided-learning.topic', $nextIndicatorId) }}')"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl border-2 border-blue-600 bg-gray-50 hover:bg-blue-50/60 dark:bg-gray-750 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 text-xs font-bold shadow-2xs transition-all">
                <span>Next Topic</span>
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
// Auto-detect common math notation and clean plain text arrows/numbers/symbols
function preprocessMathNotation(container) {
    if (!container) return;
    const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    while (walker.nextNode()) textNodes.push(walker.currentNode);

    textNodes.forEach(function(node) {
        let text = node.textContent;
        let changed = false;

        // 1. Clean arrows in text (e.g. $\rightarrow$, \rightarrow)
        if (/\$?\\(rightarrow|to|longrightarrow)\$?/.test(text)) {
            text = text.replace(/\$?\\(rightarrow|to|longrightarrow)\$?([a-zA-Z0-9\s])/g, ' → $2')
                       .replace(/\$?\\(rightarrow|to|longrightarrow)\$?/g, ' → ');
            changed = true;
        }
        if (/\$?\\(leftarrow|longleftarrow)\$?/.test(text)) {
            text = text.replace(/\$?\\(leftarrow|longleftarrow)\$?/g, ' ← ');
            changed = true;
        }
        if (/\$?\\(leftrightarrow)\$?/.test(text)) {
            text = text.replace(/\$?\\(leftrightarrow)\$?/g, ' ↔ ');
            changed = true;
        }

        // 2. Clean comparison symbols in dollars: $>$, $<$, $=$
        if (/\$([><=])\$/.test(text)) {
            text = text.replace(/\$([><=])\$/g, ' $1 ');
            changed = true;
        }

        // 3. Clean isolated plain numbers in dollars (e.g. $1$, $100$, $1,000,000,000$)
        if (/\$([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?)\$/.test(text)) {
            text = text.replace(/\$([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?)\$/g, '$1');
            changed = true;
        }

        // 4. Clean basic LaTeX operator commands if unrendered
        if (/\$?\\times\$?/.test(text)) {
            text = text.replace(/\$?\\times\$?/g, ' × ');
            changed = true;
        }
        if (/\$?\\div\$?/.test(text)) {
            text = text.replace(/\$?\\div\$?/g, ' ÷ ');
            changed = true;
        }
        if (/\$?\\(le|leq)\$?/.test(text)) {
            text = text.replace(/\$?\\(le|leq)\$?/g, ' ≤ ');
            changed = true;
        }
        if (/\$?\\(ge|geq)\$?/.test(text)) {
            text = text.replace(/\$?\\(ge|geq)\$?/g, ' ≥ ');
            changed = true;
        }
        if (/\$?\\(ne|neq)\$?/.test(text)) {
            text = text.replace(/\$?\\(ne|neq)\$?/g, ' ≠ ');
            changed = true;
        }

        // 5. Replace X^{Y} patterns first (e.g., 4^{12} → 4<sup>12</sup>)
        if (/\w\^\{/.test(text)) {
            text = text.replace(/(\w+)\^\{([^}]+)\}/g, '$1<sup>$2</sup>');
            changed = true;
        }

        // 6. Replace X^Y patterns (e.g., 4^2 → 4<sup>2</sup>, x^n → x<sup>n</sup>)
        if (/\w\^\w/.test(text)) {
            text = text.replace(/(\w+)\^(\w+)/g, '$1<sup>$2</sup>');
            changed = true;
        }

        // 7. Replace X_Y subscript patterns (e.g., a_1 → a<sub>1</sub>)
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

// Render MathLive formulas with full TeX delimiter support
document.addEventListener('DOMContentLoaded', function() {
    const texConfig = {
        TeX: {
            delimiters: {
                inline: [['$', '$'], ['\\(', '\\)']],
                display: [['$$', '$$'], ['\\[', '\\]']]
            }
        }
    };

    function renderAll() {
        const elements = new Set(document.querySelectorAll('.lesson-reader, .takeaway-text'));
        elements.forEach(function(el) {
            preprocessMathNotation(el);
            const renderFn = window.renderMathInElement || (window.MathfieldElement && window.MathfieldElement.renderMathInElement);
            if (typeof renderFn === 'function') {
                try {
                    renderFn(el, texConfig);
                } catch(e) {
                    console.warn('MathLive render error:', e);
                }
            }
        });
    }

    // Run initial typography pass immediately
    renderAll();

    // Check if MathLive loads asynchronously and re-render
    var interval = setInterval(function() {
        const renderFn = window.renderMathInElement || (window.MathfieldElement && window.MathfieldElement.renderMathInElement);
        if (typeof renderFn === 'function') {
            clearInterval(interval);
            renderAll();
        }
    }, 200);
    setTimeout(function() { clearInterval(interval); }, 6000);
});

function topicReaderApp() {
    return {
        status: '{{ $status }}',
        notes: @json($progress->notes ?? ''),
        saveStatus: 'Saved',

        // Brilliant-Style Interactive Segments State
        interactiveSegments: @json($studentLesson['interactive_segments'] ?? []),
        completedSegments: {{ ($progress && ($progress->quiz_passed_at !== null || $progress->status === 'completed')) ? json_encode(array_column($studentLesson['interactive_segments'] ?? [], 'id')) : '[]' }},

        // Pre-initialized segment state objects for Alpine reactivity
        segmentAnswers: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: undefined,@endforeach },
        segmentFeedback: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: null,@endforeach },
        segmentChecking: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: false,@endforeach },
        hintsRevealed: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: false,@endforeach },

        isSegmentUnlocked(segmentId) {
            // If already completed or topic is mastered, unlock all
            if (this.status === 'completed') return true;
            if (this.completedSegments.includes(segmentId)) return true;

            // First segment is always unlocked
            const segIndex = this.interactiveSegments.findIndex(s => s.id === segmentId);
            if (segIndex <= 0) return true;

            // Prior segment must be completed
            const prevSeg = this.interactiveSegments[segIndex - 1];
            return prevSeg && this.completedSegments.includes(prevSeg.id);
        },

        toggleHint(segmentId) {
            this.hintsRevealed[segmentId] = !this.hintsRevealed[segmentId];
        },

        scrollToInteractiveFlow() {
            const flow = document.getElementById('interactive-learning-flow');
            if (flow) {
                flow.scrollIntoView({ behavior: 'smooth', block: 'start' });
                flow.classList.add('ring-4', 'ring-blue-500/30');
                setTimeout(() => flow.classList.remove('ring-4', 'ring-blue-500/30'), 1500);
            }
        },

        async checkSegmentChallenge(segmentId) {
            const answer = this.segmentAnswers[segmentId];
            if (answer === undefined) return;

            this.segmentChecking[segmentId] = true;

            try {
                const response = await fetch(`/guided-learning/topic/{{ $encodedIndicatorId ?? $indicator->id }}/verify-segment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        segment_id: segmentId,
                        answer: answer,
                        completed_segments: this.completedSegments
                    })
                });

                const data = await response.json();
                this.segmentChecking[segmentId] = false;

                if (data.success) {
                    this.segmentFeedback[segmentId] = data;

                    if (data.is_correct) {
                        if (!this.completedSegments.includes(segmentId)) {
                            this.completedSegments.push(segmentId);
                        }

                        // If all segments solved, mark completed!
                        if (data.all_completed) {
                            this.status = 'completed';

                            if (data.next_url) {
                                setTimeout(() => {
                                    window.location.href = data.next_url;
                                }, 2400);
                            }
                        } else {
                            // Find and scroll to next segment
                            const segIndex = this.interactiveSegments.findIndex(s => s.id === segmentId);
                            if (segIndex >= 0 && segIndex < this.interactiveSegments.length - 1) {
                                const nextSeg = this.interactiveSegments[segIndex + 1];
                                setTimeout(() => {
                                    const nextCard = document.getElementById(`segment-card-${nextSeg.id}`);
                                    if (nextCard) {
                                        nextCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    }
                                }, 500);
                            }
                        }
                    }
                }
            } catch (e) {
                this.segmentChecking[segmentId] = false;
                console.error(e);
            }
        },

        async handleCompleteAndNext(nextUrl) {
            if (this.status === 'completed') {
                window.location.href = nextUrl;
                return;
            }

            // Scroll to interactive challenges if not yet completed
            this.scrollToInteractiveFlow();
        },

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
                } else if (data.requires_interactive) {
                    this.scrollToInteractiveFlow();
                }
            } catch (e) {
                console.error('Failed to update status:', e);
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
        indicator: @json($indicator->student_title),
        code: @json($indicator->indicator_code),
        strand: @json($strand->title),
        subStrand: @json($subStrand->title),
        takeaways: @json($studentLesson['key_takeaways'] ?? []),
        hasTextbook: @json($textbookSections->isNotEmpty()),
        lessonSource: @json($studentLesson['source'] ?? 'none'),
    };
</script>
@endpush

@endsection
