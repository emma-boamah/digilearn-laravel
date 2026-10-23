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

<!-- KaTeX & MathLive for Mathematical and Scientific Typography -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
<script src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<!-- Mermaid for Flowcharts & Diagrams -->
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>

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

    /* Educational Markdown Tables */
    .lesson-reader table {
        width: 100%;
        margin-top: 1.25rem;
        margin-bottom: 1.75rem;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        font-size: 0.9375rem;
    }
    .dark .lesson-reader table, [data-theme="dark"] .lesson-reader table {
        border-color: #374151;
    }
    .lesson-reader thead {
        background-color: #f8fafc;
    }
    .dark .lesson-reader thead, [data-theme="dark"] .lesson-reader thead {
        background-color: #1e293b;
    }
    .lesson-reader th {
        padding: 0.75rem 1rem;
        font-weight: 700;
        text-align: left;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
    }
    .dark .lesson-reader th, [data-theme="dark"] .lesson-reader th {
        color: #f3f4f6;
        border-color: #374151;
    }
    .lesson-reader td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
        vertical-align: middle;
    }
    .dark .lesson-reader td, [data-theme="dark"] .lesson-reader td {
        border-color: #1f2937;
        color: #d1d5db;
    }
    .lesson-reader tbody tr:last-child td {
        border-bottom: none;
    }
    .lesson-reader tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }
    .dark .lesson-reader tbody tr:nth-child(even), [data-theme="dark"] .lesson-reader tbody tr:nth-child(even) {
        background-color: rgba(30, 41, 59, 0.4);
    }

    /* Callouts & Quotes */
    .lesson-reader blockquote {
        border-left: 4px solid #3b82f6;
        padding: 0.75rem 1rem;
        background: #eff6ff;
        border-radius: 0 0.5rem 0.5rem 0;
        margin: 1.25rem 0;
        font-style: italic;
        color: #1e40af;
    }
    .dark .lesson-reader blockquote, [data-theme="dark"] .lesson-reader blockquote {
        background: #1e293b;
        border-color: #60a5fa;
        color: #93c5fd;
    }

    /* Diagrams & SVGs */
    .lesson-reader svg {
        max-width: 100%;
        height: auto;
        margin: 1.5rem auto;
        display: block;
        border-radius: 0.75rem;
    }

    /* Figure Captions & Diagram Labels */
    .lesson-reader .figure-caption,
    .lesson-reader span.text-slate-500,
    .lesson-reader span.figure-caption-styled {
        display: block;
        text-align: center;
        font-size: 0.78rem;
        line-height: 1.35;
        font-weight: 600;
        letter-spacing: 0.015em;
        margin-top: 0.65rem;
        margin-bottom: 1.25rem;
        color: #64748b;
    }
    .dark .lesson-reader .figure-caption,
    [data-theme="dark"] .lesson-reader .figure-caption,
    .dark .lesson-reader span.text-slate-500,
    [data-theme="dark"] .lesson-reader span.text-slate-500,
    .dark .lesson-reader span.figure-caption-styled,
    [data-theme="dark"] .lesson-reader span.figure-caption-styled {
        color: #94a3b8;
    }

    /* Mermaid Flowcharts & Charts */
    .lesson-reader .mermaid {
        display: flex;
        justify-content: center;
        margin: 1.5rem 0;
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
    }
    .dark .lesson-reader .mermaid, [data-theme="dark"] .lesson-reader .mermaid {
        background: #1e293b;
        border-color: #334155;
    }

    /* MathLive & KaTeX Mathematical and Scientific Typography */
    math-field {
        font-size: 1.12em;
        background: transparent !important;
        display: inline-block;
        border: none !important;
        outline: none !important;
        cursor: default;
        pointer-events: none;
        vertical-align: middle;
        padding: 0 2px;
        line-height: normal;
    }
    math-field::part(virtual-keyboard-toggle),
    math-field::part(menu-toggle) {
        display: none !important;
    }
    .dark math-field, [data-theme="dark"] math-field {
        color: #f3f4f6;
    }
    .katex {
        font-size: 1.08em;
    }
    .katex-display {
        margin: 1.25rem 0 !important;
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
                <div class="mb-10 rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 sm:p-10 shadow-sm space-y-6">
                    <!-- Section Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                {{ $sec->chapter?->title ?? 'Module Reading' }}
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ $sec->title }}
                            </h2>
                        </div>
                        @if($sec->chapter)
                            <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg self-start sm:self-auto">
                                {{ $sec->chapter->textbook->title ?? 'Textbook Resource' }}
                            </span>
                        @endif
                    </div>

                    <!-- Full Width Reading Text & Pedagogical Logic -->
                    <div class="lesson-reader max-w-none prose dark:prose-invert">
                        {!! \App\Services\StudentLessonService::cleanMathTypography($sec->content_html) !!}
                    </div>
                </div>
                @endforeach

                <!-- Interactive Checkpoint Assessment Quiz for Textbook Lessons -->
                @if(!empty($studentLesson['quiz']) && is_array($studentLesson['quiz']) && count($studentLesson['quiz']) > 0)
                <div id="lesson-checkpoint-quiz" class="mb-10 rounded-2xl border-2 transition-all p-6 sm:p-8 space-y-6 shadow-sm"
                     :class="{
                         'border-green-500/50 bg-green-50/20 dark:bg-green-950/20': quizPassed || status === 'completed',
                         'border-blue-600/30 bg-white dark:bg-gray-800': !(quizPassed || status === 'completed')
                     }">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shadow-2xs"
                                  :class="quizPassed || status === 'completed' ? 'bg-green-600 text-white' : 'bg-blue-600 text-white'">
                                <i class="fas" :class="quizPassed || status === 'completed' ? 'fa-check-double' : 'fa-clipboard-check'"></i>
                            </span>
                            <div>
                                <h3 class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>Lesson Checkpoint Challenge</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                        {{ count($studentLesson['quiz']) }} Questions
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Test your understanding of the key concepts and rules from this lesson.
                                </p>
                            </div>
                        </div>

                        <template x-if="quizPassed || status === 'completed'">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-950/60 px-3 py-1.5 rounded-xl border border-green-200 dark:border-green-800">
                                <i class="fas fa-check-circle"></i> Checkpoint Passed &bull; Topic Mastered
                            </span>
                        </template>
                    </div>

                    <!-- Questions Loop -->
                    <div class="space-y-6">
                        @foreach($studentLesson['quiz'] as $qIdx => $q)
                        @php
                            $qId = (string) ($q['id'] ?? $qIdx);
                            $qNum = $qIdx + 1;
                        @endphp
                        <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                                    {{ $qNum }}
                                </span>
                                <p class="text-sm font-bold text-gray-900 dark:text-white leading-relaxed">
                                    {{ $q['question'] }}
                                </p>
                            </div>

                            <!-- Options -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                @foreach($q['options'] as $optIdx => $opt)
                                <div @click="if (!quizPassed) { quizAnswers['{{ $qId }}'] = {{ $optIdx }}; }"
                                     class="flex items-center gap-3 p-3 rounded-xl border text-xs sm:text-sm font-medium transition-all"
                                     :class="{
                                         'border-blue-600 bg-blue-50/80 dark:bg-blue-950/60 text-blue-900 dark:text-blue-100 shadow-2xs cursor-pointer': quizAnswers['{{ $qId }}'] === {{ $optIdx }} && !quizFeedback,
                                         'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-blue-300 cursor-pointer': quizAnswers['{{ $qId }}'] !== {{ $optIdx }} && !quizFeedback,
                                         'border-green-500 bg-green-50 dark:bg-green-950/40 text-green-900 dark:text-green-200 font-bold': quizFeedback && getCorrectAnswer('{{ $qId }}', {{ $q['correct_answer'] ?? 0 }}) === {{ $optIdx }},
                                         'border-red-400 bg-red-50 dark:bg-red-950/40 text-red-900 dark:text-red-200': quizFeedback && quizAnswers['{{ $qId }}'] === {{ $optIdx }} && getCorrectAnswer('{{ $qId }}', {{ $q['correct_answer'] ?? 0 }}) !== {{ $optIdx }},
                                         'cursor-default opacity-75': quizFeedback && getCorrectAnswer('{{ $qId }}', {{ $q['correct_answer'] ?? 0 }}) !== {{ $optIdx }} && quizAnswers['{{ $qId }}'] !== {{ $optIdx }}
                                     }">
                                    <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                          :class="quizAnswers['{{ $qId }}'] === {{ $optIdx }} ? 'border-blue-600 bg-blue-600' : 'border-gray-300 dark:border-gray-600'">
                                        <span x-show="quizAnswers['{{ $qId }}'] === {{ $optIdx }}" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    </span>
                                    <span class="flex-1">{{ $opt }}</span>
                                </div>
                                @endforeach
                            </div>

                            <!-- Explanation when submitted -->
                            <template x-if="quizFeedback">
                                <div class="p-3 rounded-lg text-xs leading-relaxed mt-2"
                                     :class="isQuestionCorrect('{{ $qId }}', {{ $q['correct_answer'] ?? 0 }}) ? 'bg-green-50 dark:bg-green-950/40 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-900' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 border border-amber-200 dark:border-amber-900'">
                                    <span class="font-bold flex items-center gap-1.5 mb-1">
                                        <i class="fas" :class="isQuestionCorrect('{{ $qId }}', {{ $q['correct_answer'] ?? 0 }}) ? 'fa-check text-green-600' : 'fa-info-circle text-amber-600'"></i>
                                        <span x-text="isQuestionCorrect('{{ $qId }}', {{ $q['correct_answer'] ?? 0 }}) ? 'Correct!' : 'Explanation:'"></span>
                                    </span>
                                    <span>{{ $q['explanation'] ?? 'Review the lesson concepts above to understand the solution.' }}</span>
                                </div>
                            </template>
                        </div>
                        @endforeach
                    </div>

                    <!-- Submit / Retry Button Bar -->
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="text-xs text-gray-500">
                            <template x-if="!quizFeedback">
                                <span>Select an answer for all {{ count($studentLesson['quiz']) }} questions to verify.</span>
                            </template>
                            <template x-if="quizFeedback && quizFeedback.passed">
                                <span class="font-bold text-green-600">Great job! You scored 100% on the checkpoint challenge.</span>
                            </template>
                            <template x-if="quizFeedback && !quizFeedback.passed">
                                <span class="font-bold text-red-500">You answered <span x-text="quizFeedback.correct_count"></span> of {{ count($studentLesson['quiz']) }} correctly. Please review and try again!</span>
                            </template>
                        </div>

                        <div>
                            <template x-if="!quizPassed">
                                <button type="button"
                                        @click="submitSectionQuiz()"
                                        :disabled="quizSubmitting"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer">
                                    <i class="fas" :class="quizSubmitting ? 'fa-spinner fa-spin' : 'fa-check-circle'"></i>
                                    <span x-text="quizSubmitting ? 'Evaluating Answers...' : (quizFeedback && !quizFeedback.passed ? 'Try Again' : 'Submit Checkpoint Answers')"></span>
                                </button>
                            </template>
                            <template x-if="quizPassed">
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 text-white text-xs font-bold shadow-sm">
                                    <i class="fas fa-award"></i>
                                    <span>All Challenges Solved</span>
                                </span>
                            </template>
                        </div>
                    </div>

                </div>
                @endif

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
                            @if($segment['id'] === 1)
                                {{-- Part 1: Always fully visible — this is the main teaching content --}}
                                <div class="lesson-reader max-w-none">
                                    {!! \App\Services\StudentLessonService::cleanMathTypography($segment['content_html']) !!}
                                </div>
                            @else
                                {{-- Parts 2 & 3: Hidden behind a collapsible study-guide tip icon --}}
                                <details class="group rounded-xl border border-indigo-200 dark:border-indigo-800/50 bg-indigo-50/30 dark:bg-indigo-950/20 transition-all">
                                    <summary class="flex items-center gap-3 px-5 py-3.5 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                                        <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-sm flex-shrink-0 transition-transform group-open:rotate-12">
                                            💡
                                        </span>
                                        <div class="flex-1">
                                            <span class="text-sm font-bold text-indigo-900 dark:text-indigo-200">
                                                @if($segment['id'] === 2)
                                                    Study Guide: Rules & Worked Examples
                                                @else
                                                    Study Guide: Pro-Tips & Key Takeaways
                                                @endif
                                            </span>
                                            <p class="text-[11px] text-indigo-600/70 dark:text-indigo-400/70 mt-0.5">
                                                Tap to expand reference notes
                                            </p>
                                        </div>
                                        <span class="text-indigo-400 dark:text-indigo-500 text-xs transition-transform group-open:rotate-180">
                                            <i class="fas fa-chevron-down"></i>
                                        </span>
                                    </summary>
                                    <div class="px-5 pb-5 pt-2 lesson-reader max-w-none border-t border-indigo-200/50 dark:border-indigo-800/30 mt-1">
                                        {!! \App\Services\StudentLessonService::cleanMathTypography($segment['content_html']) !!}
                                    </div>
                                </details>
                            @endif

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
                            @else
                            {{-- Reading & Layman Foundation Completion Bar --}}
                            <div class="pt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-t border-gray-100 dark:border-gray-700/60 mt-6">
                                <div class="flex items-center gap-2.5 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs flex-shrink-0">
                                        <i class="fas fa-lightbulb"></i>
                                    </span>
                                    <span>Take your time to absorb this foundation before moving to the step-by-step rules.</span>
                                </div>

                                <div>
                                    <template x-if="!completedSegments.includes({{ $segment['id'] }})">
                                        <button type="button"
                                                @click="completeReadingSegment({{ $segment['id'] }})"
                                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition-all">
                                            <span>Understood! Let's Learn How To Do It</span>
                                            <i class="fas fa-arrow-right text-[10px]"></i>
                                        </button>
                                    </template>
                                    <template x-if="completedSegments.includes({{ $segment['id'] }})">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-950/40 px-3.5 py-1.5 rounded-xl border border-green-200 dark:border-green-800">
                                            <i class="fas fa-check-circle text-green-600"></i> Foundation Understood &bull; Unlocked Next Part
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

                @php
                    $curatedList = $curatedMedia ?? ($studentLesson['media_resources'] ?? []);
                    if (is_string($curatedList)) {
                        $curatedList = json_decode($curatedList, true) ?? [];
                    }
                    $hasAnyVideos = !empty($curatedList) || (isset($relatedVideos) && $relatedVideos->isNotEmpty());
                    $totalVideoCount = count($curatedList) + (isset($relatedVideos) ? $relatedVideos->count() : 0);
                @endphp

                <!-- Unified Video Lessons / Media Dock -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-2.5">
                        <h3 class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fab fa-youtube text-red-600 text-sm"></i>
                            <span>Video Lessons</span>
                        </h3>
                        @if($hasAnyVideos)
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                            {{ $totalVideoCount }}
                        </span>
                        @endif
                    </div>

                    <div class="space-y-3">
                        {{-- 1. Curated Educational Videos (YouTube from Lesson Studio) --}}
                        @if(!empty($curatedList))
                            @foreach($curatedList as $cMedia)
                                @if(($cMedia['type'] ?? '') === 'youtube' && !empty($cMedia['video_id']))
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-750/70 overflow-hidden shadow-2xs hover:shadow-md transition-all group cursor-pointer"
                                     @click="openVideoModal(@js($cMedia))">
                                    <div class="relative aspect-video bg-gray-900 overflow-hidden">
                                        <img src="{{ $cMedia['thumbnail_url'] ?? ('https://img.youtube.com/vi/' . $cMedia['video_id'] . '/hqdefault.jpg') }}"
                                             alt="{{ $cMedia['title'] ?? 'Video' }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/25 flex items-center justify-center group-hover:bg-black/10 transition-colors">
                                            <span class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                                                <i class="fas fa-play text-[10px] ml-0.5"></i>
                                            </span>
                                        </div>
                                        <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 rounded bg-black/75 text-white text-[9px] font-mono font-bold tracking-wider">
                                            YouTube
                                        </span>
                                    </div>
                                    <div class="p-2.5">
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {{ $cMedia['title'] ?? 'Educational Tutorial' }}
                                        </h5>
                                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                            <i class="fab fa-youtube text-red-500 text-xs"></i>
                                            <span class="truncate">{{ $cMedia['channel'] ?? 'Educational Tutorial' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif

                        {{-- 2. Platform Related Videos --}}
                        @if(isset($relatedVideos) && $relatedVideos->isNotEmpty())
                            @foreach($relatedVideos as $video)
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
                            @endforeach
                        @endif

                        {{-- Empty Fallback --}}
                        @if(!$hasAnyVideos)
                        <div class="text-center py-4 space-y-2.5">
                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-750 flex items-center justify-center mx-auto text-gray-400">
                                <i class="fab fa-youtube text-lg"></i>
                            </div>
                            <p class="text-xs text-gray-400 italic">No direct video tutorials attached to this topic yet.</p>
                            <a href="https://www.youtube.com/results?search_query={{ urlencode($indicator->student_title . ' ' . $subject->name . ' explained') }}"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 dark:bg-red-950/40 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900 transition-colors">
                                <i class="fab fa-youtube"></i> Search on YouTube
                                <i class="fas fa-external-link-alt text-[9px]"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Practice Quizzes / Checkpoint Challenge -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-2.5">
                        <h3 class="font-bold text-xs text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-blue-600"></i>
                            <span>Practice Quiz</span>
                        </h3>
                        @if(!empty($studentLesson['quiz']) && count($studentLesson['quiz']) > 0)
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                              :class="status === 'completed' || quizPassed ? 'bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300'">
                            <span x-text="status === 'completed' || quizPassed ? 'Completed' : '{{ count($studentLesson['quiz']) }} Qs'"></span>
                        </span>
                        @endif
                    </div>

                    {{-- Section Lesson Checkpoint Quiz Trigger --}}
                    @if(!empty($studentLesson['quiz']) && count($studentLesson['quiz']) > 0)
                    <div class="p-3.5 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/50 space-y-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg text-white flex items-center justify-center text-xs font-bold shadow-2xs"
                                  :class="status === 'completed' || quizPassed ? 'bg-green-600' : 'bg-blue-600'">
                                <i class="fas" :class="status === 'completed' || quizPassed ? 'fa-check' : 'fa-tasks'"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate">Lesson Checkpoint</h4>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    <span x-show="status === 'completed' || quizPassed">Mastered &bull; 100% Score</span>
                                    <span x-show="!(status === 'completed' || quizPassed)">Required to complete lesson</span>
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                @click="scrollToQuiz()"
                                class="w-full mt-2 py-2 px-3 rounded-lg text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer"
                                :class="status === 'completed' || quizPassed ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'">
                            <span x-text="status === 'completed' || quizPassed ? 'Review Checkpoint Results' : 'Take Checkpoint Quiz'"></span>
                            <i class="fas fa-arrow-down text-[10px]"></i>
                        </button>
                    </div>
                    @endif

                    {{-- Platform Related Quizzes --}}
                    @if(isset($relatedQuizzes) && $relatedQuizzes->isNotEmpty())
                    <div class="space-y-2 pt-1">
                        @foreach($relatedQuizzes as $quiz)
                        <a href="{{ route('quiz.take', $quiz) }}" class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors group border border-transparent hover:border-blue-200">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                <i class="fas fa-question text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 transition-colors">{{ $quiz->title }}</h4>
                                <p class="text-[10px] text-gray-400 truncate">Practice Assessment</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @elseif(empty($studentLesson['quiz']) || count($studentLesson['quiz']) === 0)
                    <p class="text-xs text-gray-400 italic py-2 text-center">No practice quizzes yet.</p>
                    @endif
                </div>

            </div>
        </div>{{-- END right sidebar --}}

    </div>{{-- END grid --}}

    <!-- Sticky Bottom Navigation Bar (full width, below grid) -->
    <div class="sticky bottom-6 z-20 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xl flex items-center justify-between gap-4 mt-8">
        @if($prevIndicatorId)
            @php
                $prevRoute = !empty($isTextbookSection)
                    ? route('guided-learning.section', $prevIndicatorId)
                    : route('guided-learning.topic', $prevIndicatorId);
            @endphp
            <a href="{{ $prevRoute }}"
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
            @php
                $nextRoute = !empty($isTextbookSection)
                    ? route('guided-learning.section', $nextIndicatorId)
                    : route('guided-learning.topic', $nextIndicatorId);
            @endphp
            <button type="button"
                    @click="handleCompleteAndNext('{{ $nextRoute }}')"
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

    <!-- Video Player Modal -->
    <div x-show="videoModalOpen" x-cloak style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/85 backdrop-blur-xs"
         @keydown.escape.window="closeVideoModal()">
        <div class="bg-gray-900 rounded-2xl max-w-4xl w-full border border-gray-700 overflow-hidden shadow-2xl"
             @click.away="closeVideoModal()">
            <div class="p-3 sm:p-4 border-b border-gray-800 flex items-center justify-between text-white">
                <h4 class="text-xs sm:text-sm font-bold truncate flex items-center gap-2">
                    <i class="fab fa-youtube text-red-500"></i>
                    <span x-text="activeVideo?.title || 'Educational Video Tutorial'"></span>
                </h4>
                <button type="button" @click="closeVideoModal()" class="text-gray-400 hover:text-white p-1.5 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <div class="aspect-video w-full bg-black">
                <template x-if="activeVideo?.video_id">
                    <iframe :src="'https://www.youtube-nocookie.com/embed/' + activeVideo.video_id + '?autoplay=1'"
                            class="w-full h-full border-0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </template>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
// Universal Mathematical and Scientific Typography Renderer (KaTeX & MathLive)
window.renderAllMath = function(root) {
    const target = root || document.getElementById('topic-content') || document.body;
    if (!target) return;

    // 1. Normalize any unrendered parenthesized LaTeX notation:
    // e.g. (\frac{1}{10} = 0.1) or (\text{GH₵ } 0.01) -> $\frac{1}{10} = 0.1$
    const walker = document.createTreeWalker(target, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    while (walker.nextNode()) textNodes.push(walker.currentNode);
    textNodes.forEach(node => {
        if (node.parentElement && (node.parentElement.closest('svg') || node.parentElement.closest('.mermaid') || node.parentElement.closest('pre'))) return;
        let t = node.textContent;
        if (/\(\s*(\\\\(?:frac|text|mathbf|sqrt|alpha|beta|theta|pi|ge|le|approx|times|div|pm|circ)\b[^)]*)\)/.test(t)) {
            node.textContent = t.replace(/\(\s*(\\\\(?:frac|text|mathbf|sqrt|alpha|beta|theta|pi|ge|le|approx|times|div|pm|circ)\b[^)]*)\)/g, '$$$1$$');
        }
    });

    const delimiters = [
        { left: '$$', right: '$$', display: true },
        { left: '\\[', right: '\\]', display: true },
        { left: '\\(', right: '\\)', display: false },
        { left: '$', right: '$', display: false }
    ];

    // 2. Render static LaTeX with KaTeX auto-render if available
    if (typeof renderMathInElement === 'function') {
        try {
            renderMathInElement(target, {
                delimiters: delimiters,
                ignoredTags: ["script", "noscript", "style", "textarea", "pre", "code", "annotation", "annotation-xml", "svg"],
                throwOnError: false,
                trust: true
            });
        } catch(e) {
            console.warn('KaTeX render error:', e);
        }
    }

    // 3. Render with MathLive if available
    const mathliveRender = window.MathLive?.renderMathInElement || (window.MathfieldElement && window.MathfieldElement.renderMathInElement);
    if (typeof mathliveRender === 'function' && mathliveRender !== window.renderMathInElement) {
        try {
            mathliveRender(target, {
                processEnvironments: true,
                TeX: {
                    processEnvironments: true,
                    delimiters: {
                        inline: [['$', '$'], ['\\(', '\\)']],
                        display: [['$$', '$$'], ['\\[', '\\]']]
                    }
                }
            });
        } catch(e) {
            console.warn('MathLive render error:', e);
        }
    }

    // 4. Ensure all math-field elements are strictly read-only with no keyboard popups
    if (window.customElements && typeof customElements.whenDefined === 'function') {
        customElements.whenDefined('math-field').then(() => {
            target.querySelectorAll('math-field').forEach(mf => {
                mf.readOnly = true;
                mf.setAttribute('read-only', '');
                mf.setAttribute('math-virtual-keyboard-policy', 'none');
                mf.removeAttribute('contenteditable');
                mf.removeAttribute('tabindex');
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.renderAllMath();

    // Re-check after async MathLive script loads
    var checks = 0;
    var interval = setInterval(function() {
        checks++;
        window.renderAllMath();
        if (checks > 12 || (window.MathLive && window.katex)) {
            clearInterval(interval);
        }
    }, 200);
});

function topicReaderApp() {
    return {
        status: '{{ $status }}',
        notes: @json($progress->notes ?? ''),
        saveStatus: 'Saved',

        // Video modal state
        videoModalOpen: false,
        activeVideo: null,

        openVideoModal(video) {
            this.activeVideo = video;
            this.videoModalOpen = true;
        },

        closeVideoModal() {
            this.videoModalOpen = false;
            this.activeVideo = null;
        },

        // Textbook Checkpoint Quiz State
        quizQuestions: @json($studentLesson['quiz'] ?? []),
        quizAnswers: {},
        quizFeedback: null,
        quizSubmitting: false,
        quizPassed: {{ ($progress && ($progress->quiz_passed_at !== null || $progress->status === 'completed')) ? 'true' : 'false' }},

        scrollToQuiz() {
            const quizEl = document.getElementById('lesson-checkpoint-quiz');
            if (quizEl) {
                quizEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                quizEl.classList.add('ring-4', 'ring-blue-500/30');
                setTimeout(() => quizEl.classList.remove('ring-4', 'ring-blue-500/30'), 1500);
            }
        },

        getCorrectAnswer(qId, fallback) {
            if (!this.quizFeedback || !Array.isArray(this.quizFeedback.results)) return fallback;
            const res = this.quizFeedback.results.find(r => String(r.id) === String(qId));
            return res ? res.correct_answer : fallback;
        },

        isQuestionCorrect(qId, fallback) {
            if (!this.quizFeedback || !Array.isArray(this.quizFeedback.results)) return false;
            const res = this.quizFeedback.results.find(r => String(r.id) === String(qId));
            return res ? !!res.is_correct : false;
        },

        async submitSectionQuiz() {
            const qCount = this.quizQuestions.length;
            if (Object.keys(this.quizAnswers).length < qCount) {
                alert(`Please select an answer for all ${qCount} questions before submitting.`);
                return;
            }
            this.quizSubmitting = true;
            try {
                const isSection = {{ !empty($isTextbookSection) ? 'true' : 'false' }};
                const endpoint = isSection
                    ? `/guided-learning/section/{{ $encodedIndicatorId ?? $indicator->id }}/quiz`
                    : `/guided-learning/topic/{{ $encodedIndicatorId ?? $indicator->id }}/quiz`;

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ answers: this.quizAnswers })
                });
                const data = await res.json();
                this.quizSubmitting = false;

                if (data.success) {
                    this.quizFeedback = data;
                    if (data.passed) {
                        this.quizPassed = true;
                        this.status = 'completed';
                    }
                    this.$nextTick(() => {
                        if (window.renderAllMath) window.renderAllMath();
                    });
                }
            } catch (err) {
                this.quizSubmitting = false;
                console.error(err);
            }
        },

        // Brilliant-Style Interactive Segments State
        interactiveSegments: @json($studentLesson['interactive_segments'] ?? []),
        completedSegments: {{ ($progress && ($progress->quiz_passed_at !== null || $progress->status === 'completed')) ? json_encode(array_column($studentLesson['interactive_segments'] ?? [], 'id')) : '[]' }},

        // Pre-initialized segment state objects for Alpine reactivity
        segmentAnswers: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: undefined,@endforeach },
        segmentFeedback: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: null,@endforeach },
        segmentChecking: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: false,@endforeach },
        hintsRevealed: { @foreach($studentLesson['interactive_segments'] ?? [] as $seg){{ $seg['id'] }}: false,@endforeach },

        renderMermaidDiagrams() {
            const codeBlocks = document.querySelectorAll('.lesson-reader pre code.language-mermaid, .lesson-reader code.language-mermaid');
            if (!codeBlocks || codeBlocks.length === 0) return;

            codeBlocks.forEach((block, idx) => {
                const pre = block.closest('pre') || block;
                const container = document.createElement('div');
                container.className = 'mermaid';
                container.id = 'topic-mermaid-' + idx + '-' + Date.now();

                // Clean up any HTML entities that might cause Mermaid syntax errors
                let rawText = block.textContent.trim();
                rawText = rawText
                    .replace(/&rarr;/g, '-->')
                    .replace(/&larr;/g, '<--')
                    .replace(/&gt;/g, '>')
                    .replace(/&lt;/g, '<')
                    .replace(/&amp;/g, '&');

                container.textContent = rawText;
                pre.parentNode.replaceChild(container, pre);
            });

            if (window.mermaid) {
                try {
                    window.mermaid.initialize({
                        startOnLoad: false,
                        theme: (document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark') ? 'dark' : 'default',
                        securityLevel: 'loose'
                    });
                    window.mermaid.run({ nodes: document.querySelectorAll('.lesson-reader .mermaid') });
                } catch (mErr) {
                    console.warn("Mermaid execution error:", mErr);
                }
            }
        },

        renderFigureCaptions() {
            document.querySelectorAll('.lesson-reader').forEach(reader => {
                reader.querySelectorAll('pre, code, p').forEach(el => {
                    const html = el.innerHTML.trim();
                    const match = html.match(/(?:&lt;|<)span\s+class=(?:&quot;|["'])text-xs text-slate-500[^>"'&]*?(?:&quot;|["'])(?:&gt;|>)([\s\S]*?)(?:&lt;|<)\/span(?:&gt;|>)/i);
                    if (match) {
                        const target = el.closest('pre') || el;
                        const captionText = match[1].replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>').trim();
                        const span = document.createElement('span');
                        span.className = 'figure-caption-styled text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium text-center block tracking-wide';
                        span.textContent = captionText;
                        target.parentNode.replaceChild(span, target);
                    }
                });
            });
        },

        init() {
            this.renderFigureCaptions();
            this.renderMermaidDiagrams();
            this.$nextTick(() => {
                this.renderFigureCaptions();
                if (window.renderAllMath) window.renderAllMath();
            });
        },

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
            const flow = document.getElementById('interactive-learning-flow') || document.getElementById('lesson-checkpoint-quiz');
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
                    this.$nextTick(() => window.renderAllMath());

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

        async completeReadingSegment(segmentId) {
            if (!this.completedSegments.includes(segmentId)) {
                this.completedSegments.push(segmentId);
            }

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
                        reading_completed: true,
                        completed_segments: this.completedSegments
                    })
                });

                const data = await response.json();
                if (data.all_completed) {
                    this.status = 'completed';
                }
            } catch (e) {
                console.warn('Failed to sync segment completion', e);
            }

            // Smooth scroll to next segment
            const segIndex = this.interactiveSegments.findIndex(s => s.id === segmentId);
            if (segIndex >= 0 && segIndex < this.interactiveSegments.length - 1) {
                const nextSeg = this.interactiveSegments[segIndex + 1];
                setTimeout(() => {
                    const nextCard = document.getElementById(`segment-card-${nextSeg.id}`);
                    if (nextCard) {
                        nextCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 300);
            }

            this.$nextTick(() => window.renderAllMath());
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
                const isSection = {{ !empty($isTextbookSection) ? 'true' : 'false' }};
                const endpoint = isSection
                    ? `/guided-learning/section/{{ $encodedIndicatorId ?? $indicator->id }}/toggle`
                    : `/guided-learning/topic/{{ $encodedIndicatorId ?? $indicator->id }}/toggle`;
                const response = await fetch(endpoint, {
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
