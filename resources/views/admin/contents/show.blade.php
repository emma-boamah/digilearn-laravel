@extends('layouts.admin')

@section('title', 'Content Inspection — ' . $content->title)
@section('page-title', 'Content Inspection & Details')
@section('page-description', 'Detailed telemetry, storage destinations, attached resources, and academic metadata')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
<style>
    /* MathLive read-only rendering for inspection view */
    math-field {
        display: inline-block;
        font-size: 1.05rem;
        border: none;
        padding: 2px 4px;
        background: transparent;
        pointer-events: none;
        vertical-align: middle;
    }
    math-field::part(virtual-keyboard-toggle),
    math-field::part(menu-toggle) {
        display: none !important;
    }
    .math-wrapper {
        display: inline-block;
        vertical-align: middle;
    }
    /* Ensure question text with math renders inline properly */
    .quiz-question-text math-field,
    .quiz-option-text math-field,
    .content-description math-field {
        font-size: inherit;
    }
    /* Content description custom typography styling */
    .content-description p {
        margin-bottom: 0.75rem;
    }
    .content-description p:last-child {
        margin-bottom: 0;
    }
    .content-description ul, .content-description ol {
        margin-left: 1.25rem;
        margin-bottom: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script defer src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script type="module" nonce="{{ request()->attributes->get('csp_nonce') }}">
    import { renderMathInElement } from "https://unpkg.com/mathlive?module";
    window.renderMathInElement = renderMathInElement;
    // Auto-render any LaTeX or math-field elements once MathLive is loaded
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.quiz-question-text, .quiz-option-text, .content-description').forEach(el => {
            if (window.renderMathInElement) {
                renderMathInElement(el);
            }
        });
        // Set all math-fields to read-only
        document.querySelectorAll('math-field').forEach(mf => {
            mf.setAttribute('read-only', '');
            mf.setAttribute('math-virtual-keyboard-policy', 'none');
        });
    });
</script>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50/60 dark:bg-gray-900 py-6 px-4 sm:px-6 lg:px-8">

    <!-- Top Navigation / Breadcrumbs & Action Bar -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.contents.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 transition-all shadow-xs">
                <i class="fas fa-arrow-left text-base"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Content Inspection</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-sm font-medium text-slate-500">ID #{{ $content->id }}</span>
                    @if($contentType === 'video')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                            <i class="fas fa-video mr-1"></i>Video Package
                        </span>
                    @elseif($contentType === 'document')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                            <i class="fas fa-file-alt mr-1"></i>Document
                        </span>
                    @elseif($contentType === 'quiz')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                            <i class="fas fa-clipboard-check mr-1"></i>Quiz
                        </span>
                    @endif

                    @if(isset($content->is_agent_generated) && $content->is_agent_generated)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 flex items-center gap-1">
                            <i class="fas fa-robot"></i>AI Generated
                        </span>
                    @endif

                    @if(($content->status ?? 'approved') === 'approved' || ($content->status ?? '') === 'published')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Published
                        </span>
                    @elseif(($content->status ?? '') === 'pending')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Pending Review
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            {{ ucfirst($content->status ?? 'Draft') }}
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1 line-clamp-1">
                    {{ $content->title }}
                </h1>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.contents.edit', $content->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs transition-colors">
                <i class="fas fa-edit"></i>
                Edit Content
            </a>
            <a href="{{ route('admin.contents.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 text-sm font-semibold rounded-xl border border-slate-200 dark:border-gray-700 transition-colors shadow-xs">
                <i class="fas fa-list"></i>
                All Contents
            </a>
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Primary Media & Resource Telemetry (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- 1. Primary Player / Viewer Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs overflow-hidden">
                @if($contentType === 'video')
                    <div class="aspect-video bg-slate-950 flex items-center justify-center relative group">
                        @if(!empty($content->vimeo_id))
                            <iframe src="https://player.vimeo.com/video/{{ $content->vimeo_id }}?title=0&byline=0&portrait=0" 
                                    class="w-full h-full" 
                                    frameborder="0" 
                                    allow="autoplay; fullscreen; picture-in-picture" 
                                    allowfullscreen></iframe>
                        @elseif(!empty($content->external_video_id))
                            <iframe src="https://www.youtube.com/embed/{{ $content->external_video_id }}" 
                                    class="w-full h-full" 
                                    frameborder="0" 
                                    allowfullscreen></iframe>
                        @elseif(!empty($content->video_path) || !empty($content->temp_file_path))
                            @php
                                $localStreamUrl = route('admin.content.videos.stream', $content->id);
                            @endphp
                            <video src="{{ $localStreamUrl }}" 
                                   controls 
                                   controlsList="nodownload" 
                                   poster="{{ $content->getThumbnailUrl() ?? '' }}"
                                   class="w-full h-full object-contain"></video>
                        @elseif(!empty($content->mux_playback_id))
                            <iframe src="https://stream.mux.com/{{ $content->mux_playback_id }}" 
                                    class="w-full h-full" 
                                    frameborder="0" 
                                    allowfullscreen></iframe>
                        @else
                            <div class="text-center p-8 text-slate-400">
                                <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-500 text-2xl">
                                    <i class="fas fa-video-slash"></i>
                                </div>
                                <h3 class="text-base font-bold text-slate-300">No Video Stream Configured</h3>
                                <p class="text-sm text-slate-500 mt-1 max-w-sm">This video package does not currently have an active video stream ID or local video file attached.</p>
                            </div>
                        @endif
                    </div>
                @elseif($contentType === 'document')
                    <div class="p-8 bg-gradient-to-br from-emerald-900/10 to-teal-900/5 dark:from-emerald-950/30 dark:to-gray-900 border-b border-slate-100 dark:border-gray-700 flex flex-col sm:flex-row items-center gap-6">
                        <div class="w-20 h-24 rounded-xl bg-white dark:bg-gray-800 shadow-md border border-slate-200 dark:border-gray-700 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <i class="fas fa-file-pdf text-4xl"></i>
                        </div>
                        <div class="space-y-2 text-center sm:text-left">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                Standalone Document
                            </span>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $content->title }}</h2>
                            <p class="text-sm text-slate-500 font-mono">{{ $content->file_path ?? 'No file path stored' }}</p>
                            @if(!empty($content->file_path))
                                <div class="pt-2">
                                    <a href="{{ asset('storage/' . $content->file_path) }}" target="_blank" download
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors">
                                        <i class="fas fa-download"></i>
                                        Download PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($contentType === 'quiz')
                    <div class="p-8 bg-gradient-to-br from-purple-900/10 to-indigo-900/5 dark:from-purple-950/30 dark:to-gray-900 border-b border-slate-100 dark:border-gray-700 flex flex-col sm:flex-row items-center gap-6">
                        <div class="w-20 h-24 rounded-xl bg-white dark:bg-gray-800 shadow-md border border-slate-200 dark:border-gray-700 flex items-center justify-center text-purple-600 flex-shrink-0">
                            <i class="fas fa-clipboard-check text-4xl"></i>
                        </div>
                        <div class="space-y-2 text-center sm:text-left">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                                Assessment / Quiz Hub
                            </span>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $content->title }}</h2>
                            <p class="text-sm text-slate-500 font-medium">
                                {{ isset($quizData['questions']) ? count($quizData['questions']) : 0 }} Assessment Questions Configured
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Content Description -->
                <div class="p-6">
                    <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                        <i class="fas fa-align-left text-blue-500"></i>
                        Description & Notes
                    </h3>
                    @if(!empty($content->description))
                        <div class="text-base text-slate-800 dark:text-gray-200 leading-relaxed bg-slate-50 dark:bg-gray-900/50 p-5 rounded-xl border border-slate-100 dark:border-gray-700/60 content-description">
                            {!! $content->description !!}
                        </div>
                    @else
                        <p class="text-sm italic text-slate-400">No written description provided for this content.</p>
                    @endif
                </div>
            </div>

            <!-- 2. Storage & File Destination Telemetry Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-gray-700 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base font-bold">
                            <i class="fas fa-server"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Media & File Storage Telemetry</h3>
                            <p class="text-xs text-slate-400">Hosting destinations, raw storage paths, and media parameters</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <!-- Source Platform -->
                    <div class="p-4 bg-slate-50 dark:bg-gray-900/40 rounded-xl border border-slate-100 dark:border-gray-700">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Primary Source Provider</span>
                        <span class="font-bold text-slate-800 dark:text-gray-200 text-sm capitalize flex items-center gap-1.5">
                            @if(($content->video_source ?? '') === 'vimeo' || !empty($content->vimeo_id))
                                <i class="fab fa-vimeo text-blue-500 text-base"></i> Vimeo Cloud Hosting
                            @elseif(($content->video_source ?? '') === 'youtube' || !empty($content->external_video_id))
                                <i class="fab fa-youtube text-red-500 text-base"></i> YouTube Embed
                            @elseif(($content->video_source ?? '') === 'mux' || !empty($content->mux_playback_id))
                                <i class="fas fa-play-circle text-purple-500 text-base"></i> Mux Video Streaming
                            @elseif(!empty($content->video_path) || !empty($content->temp_file_path))
                                <i class="fas fa-hdd text-emerald-500 text-base"></i> Local Server Storage
                            @else
                                <i class="fas fa-file text-slate-400 text-base"></i> {{ ucfirst($content->video_source ?? 'Direct File') }}
                            @endif
                        </span>
                    </div>

                    <!-- Duration / Size -->
                    <div class="p-4 bg-slate-50 dark:bg-gray-900/40 rounded-xl border border-slate-100 dark:border-gray-700">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Media Duration / Specs</span>
                        <span class="font-bold text-slate-800 dark:text-gray-200 text-sm">
                            @if(!empty($content->duration_seconds))
                                {{ gmdate('H:i:s', $content->duration_seconds) }} ({{ $content->duration_seconds }} seconds)
                            @else
                                N/A / Static Document
                            @endif
                        </span>
                    </div>

                    <!-- Destination Path / Video ID -->
                    <div class="p-4 bg-slate-50 dark:bg-gray-900/40 rounded-xl border border-slate-100 dark:border-gray-700 sm:col-span-2">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Raw File Path / Remote ID</span>
                        <div class="font-mono text-slate-700 dark:text-gray-300 break-all bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-slate-200 dark:border-gray-700 text-xs">
                            @if(!empty($content->vimeo_id))
                                vimeo_id: {{ $content->vimeo_id }}
                            @elseif(!empty($content->video_path))
                                storage/app/public/{{ $content->video_path }}
                            @elseif(!empty($content->temp_file_path))
                                temp_path: {{ $content->temp_file_path }}
                            @elseif(!empty($content->external_video_id))
                                external_id: {{ $content->external_video_id }}
                            @elseif(!empty($content->file_path))
                                storage/app/public/{{ $content->file_path }}
                            @else
                                <span class="text-slate-400 italic">No specific storage path or remote video ID bound</span>
                            @endif
                        </div>
                    </div>

                    <!-- Thumbnail Destination -->
                    @if(!empty($content->thumbnail_path) || !empty($content->thumbnail_url))
                        <div class="p-4 bg-slate-50 dark:bg-gray-900/40 rounded-xl border border-slate-100 dark:border-gray-700 sm:col-span-2">
                            <span class="text-xs font-semibold text-slate-400 block mb-1">Cover Thumbnail Storage Destination</span>
                            <div class="font-mono text-slate-700 dark:text-gray-300 break-all bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-slate-200 dark:border-gray-700 text-xs">
                                {{ $content->thumbnail_path ? 'storage/app/public/' . $content->thumbnail_path : ($content->thumbnail_url ?? 'N/A') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Attached Documents (if Video package) -->
            @if($contentType === 'video' && (($content->documents && $content->documents->count() > 0) || !empty($content->document_path)))
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-gray-700 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base font-bold">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Attached Study Documents & Worksheets</h3>
                                <p class="text-xs text-slate-400">Downloadable learning material linked to this lesson</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ $content->documents->count() > 0 ? $content->documents->count() : 1 }} File(s)
                        </span>
                    </div>

                    <div class="space-y-3">
                        @if($content->documents && $content->documents->count() > 0)
                            @foreach($content->documents as $doc)
                                <div class="p-4 bg-slate-50 dark:bg-gray-900/40 rounded-xl border border-slate-100 dark:border-gray-700 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0">
                                            <i class="fas fa-file-pdf"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-bold text-slate-800 dark:text-gray-200 truncate">{{ $doc->title }}</h4>
                                            <p class="text-xs font-mono text-slate-400 truncate">storage/app/public/{{ $doc->file_path }}</p>
                                        </div>
                                    </div>
                                    @if(!empty($doc->file_path))
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" download
                                           class="px-3.5 py-2 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-300 hover:bg-emerald-50 hover:text-emerald-700 text-xs font-bold transition-colors flex items-center gap-1.5 flex-shrink-0 shadow-2xs">
                                            <i class="fas fa-download"></i>
                                            Download
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        @elseif(!empty($content->document_path))
                            <div class="p-4 bg-slate-50 dark:bg-gray-900/40 rounded-xl border border-slate-100 dark:border-gray-700 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-gray-200 truncate">Primary PDF Document</h4>
                                        <p class="text-xs font-mono text-slate-400 truncate">storage/app/public/{{ $content->document_path }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $content->document_path) }}" target="_blank" download
                                   class="px-3.5 py-2 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-300 hover:bg-emerald-50 hover:text-emerald-700 text-xs font-bold transition-colors flex items-center gap-1.5 flex-shrink-0 shadow-2xs">
                                    <i class="fas fa-download"></i>
                                    Download
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 4. Attached Quiz & Questions Architecture (if quiz attached) -->
            @if($quizModel && $quizData && isset($quizData['questions']) && count($quizData['questions']) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-gray-700 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base font-bold">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Connected Quiz Questions & Answers</h3>
                                <p class="text-xs text-slate-400">Structure of assessments attached to this lesson package</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                            {{ count($quizData['questions']) }} Questions
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach($quizData['questions'] as $qIdx => $q)
                            @php
                                $qType = $q['type'] ?? 'mcq';
                                $options = $q['options'] ?? [];
                                $correctAnswer = $q['correct_answer'] ?? $q['answer'] ?? null;
                            @endphp
                            <div class="p-5 bg-slate-50/80 dark:bg-gray-900/50 rounded-xl border border-slate-200 dark:border-gray-700">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-extrabold uppercase {{ $qType === 'essay' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        Q{{ $qIdx + 1 }} • {{ strtoupper($qType) }}
                                    </span>
                                    @if(isset($q['points']))
                                        <span class="text-xs font-bold text-slate-500">{{ $q['points'] }} pt(s)</span>
                                    @endif
                                </div>
                                <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-3 quiz-question-text leading-snug">
                                    {!! $q['question_text'] ?? $q['question'] ?? 'Question text not found' !!}
                                </h4>

                                @if($qType === 'mcq' && is_array($options) && count($options) > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        @foreach($options as $optIdx => $opt)
                                            @php
                                                $optText = is_array($opt) ? ($opt['text'] ?? json_encode($opt)) : $opt;
                                                $optId = is_array($opt) ? ($opt['id'] ?? $optIdx) : $optIdx;
                                                $isCorrect = (string)$optId === (string)$correctAnswer || (string)$optText === (string)$correctAnswer;
                                            @endphp
                                            <div class="p-3 rounded-xl text-sm flex items-center gap-2.5 border {{ $isCorrect ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-semibold' : 'bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200' }}">
                                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $isCorrect ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                                                    {{ chr(65 + $optIdx) }}
                                                </span>
                                                <span class="flex-1 quiz-option-text">{!! $optText !!}</span>
                                                @if($isCorrect)
                                                    <i class="fas fa-check-circle text-emerald-600 text-sm ml-auto flex-shrink-0"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($qType === 'essay')
                                    <div class="p-4 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 rounded-xl text-sm text-amber-900 dark:text-amber-300">
                                        <span class="font-bold block mb-1">Open-ended Essay Response:</span>
                                        <p class="italic text-slate-600 dark:text-slate-400">Students will compose text answers graded manually or via AI.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Academic Categorization & Engagement Metadata (1 col) -->
        <div class="space-y-6">

            <!-- 1. Academic & Curriculum Classification Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-blue-600"></i>
                    Curriculum & Classification
                </h3>

                <div class="space-y-4 text-sm">
                    <!-- Subject -->
                    <div>
                        <span class="text-slate-400 block text-xs font-semibold uppercase tracking-wider mb-1">Subject</span>
                        <div class="font-bold text-slate-800 dark:text-gray-200 text-base flex items-center gap-2">
                            <span class="w-7 h-7 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center text-sm">
                                <i class="fas fa-book"></i>
                            </span>
                            {{ $content->subject->name ?? ($content->subject_name ?? 'General Subject') }}
                        </div>
                    </div>

                    <!-- Grade Level -->
                    <div>
                        <span class="text-slate-400 block text-xs font-semibold uppercase tracking-wider mb-1">Grade Level</span>
                        <span class="font-bold text-slate-800 dark:text-gray-200 text-sm bg-slate-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg inline-block">
                            {{ $content->grade_level ?? 'All Levels' }}
                        </span>
                    </div>

                    <!-- Level Group (The removed column restored here!) -->
                    <div>
                        <span class="text-slate-400 block text-xs font-semibold uppercase tracking-wider mb-1">Level Group Tier</span>
                        <div class="font-semibold text-slate-700 dark:text-gray-300">
                            @if($levelGroup)
                                <span class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 font-bold border border-indigo-100 text-sm inline-flex items-center">
                                    <i class="fas fa-layer-group mr-1.5"></i>{{ $levelGroup->title }}
                                </span>
                            @else
                                <span class="text-slate-400 italic text-sm">No specific Level Group bound</span>
                            @endif
                        </div>
                    </div>

                    <!-- Categories / Tags (The removed column restored here!) -->
                    <div>
                        <span class="text-slate-400 block text-xs font-semibold uppercase tracking-wider mb-1.5">Categories & Context Tags</span>
                        <div class="flex flex-wrap gap-1.5">
                            @if(isset($content->categories) && $content->categories->count() > 0)
                                @foreach($content->categories as $category)
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100">
                                        #{{ $category->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-slate-400 italic text-xs">Normal Curriculum</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Uploader & Authorship Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-circle text-blue-600"></i>
                    Uploader & Authorship
                </h3>

                @if($content->uploader)
                    <div class="flex items-center gap-3.5 mb-4">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                            <x-user-avatar :user="$content->uploader" :size="48" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-base font-bold text-slate-900 dark:text-white truncate">{{ $content->uploader->name }}</h4>
                            <p class="text-xs sm:text-sm text-slate-500 truncate">{{ $content->uploader->email }}</p>
                            @if($content->uploader->roles && $content->uploader->roles->count() > 0)
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($content->uploader->roles as $role)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-slate-100 text-slate-700">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-slate-500 mb-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-xl">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-800">{{ $content->uploader_name ?? 'System Administrator' }}</span>
                            <p class="text-xs text-slate-400">Automated / System</p>
                        </div>
                    </div>
                @endif

                <div class="pt-3 border-t border-slate-100 dark:border-gray-700 space-y-2 text-xs text-slate-500">
                    <div class="flex items-center justify-between">
                        <span>Created Date:</span>
                        <span class="font-semibold text-slate-700 dark:text-gray-300">{{ $content->created_at ? $content->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Last Updated:</span>
                        <span class="font-semibold text-slate-700 dark:text-gray-300">{{ $content->updated_at ? $content->updated_at->diffForHumans() : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Engagement & Analytics Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-600"></i>
                    Engagement Analytics
                </h3>

                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3.5 bg-blue-50/50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                        <span class="text-2xl font-black text-blue-700 dark:text-blue-300 block">
                            {{ number_format($content->views ?? 0) }}
                        </span>
                        <span class="text-xs uppercase font-bold text-blue-600/80">Lifetime Views</span>
                    </div>

                    <div class="p-3.5 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                        <span class="text-2xl font-black text-emerald-700 dark:text-emerald-300 block">
                            {{ number_format($content->comments_count ?? 0) }}
                        </span>
                        <span class="text-xs uppercase font-bold text-emerald-600/80">Discussions</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
