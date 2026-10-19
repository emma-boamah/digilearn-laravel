@extends('layouts.admin')

@section('title', 'Textbook Lessons - ' . $textbook->title)

@section('content')
<!-- SortableJS library for smooth drag and drop reordering -->
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<!-- KaTeX, Marked.js & Mermaid.js for Pedagogical Mathematical & Markdown Rendering in Lesson Studio -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    [x-cloak] { display: none !important; }

    /* Educational Reading Styles for Lesson Studio Preview */
    .lesson-reader {
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        color: #1f2937;
        line-height: 1.8;
    }
    .dark .lesson-reader, [data-theme="dark"] .lesson-reader {
        color: #e5e7eb;
    }
    .lesson-reader h1, .lesson-reader h2, .lesson-reader h3, .lesson-reader h4 {
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #111827;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .dark .lesson-reader h1, .dark .lesson-reader h2, .dark .lesson-reader h3, .dark .lesson-reader h4 {
        color: #f9fafb;
    }
    .lesson-reader h3 {
        color: #2563eb;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.35rem;
    }
    .dark .lesson-reader h3 {
        color: #60a5fa;
        border-bottom-color: #334155;
    }
    /* Tables */
    .lesson-reader table {
        width: 100%;
        margin: 1.5rem 0;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        font-size: 0.875rem;
    }
    .dark .lesson-reader table {
        border-color: #334155;
    }
    .lesson-reader th {
        background: #f1f5f9;
        color: #1e293b;
        font-weight: 700;
        text-align: left;
        padding: 0.65rem 0.9rem;
        border-bottom: 2px solid #cbd5e1;
    }
    .dark .lesson-reader th {
        background: #1e293b;
        color: #f1f5f9;
    }
    .lesson-reader td {
        padding: 0.6rem 0.9rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .lesson-reader td {
        border-bottom-color: #334155/60;
    }
    .lesson-reader tr:last-child td {
        border-bottom: none;
    }
    /* Callout boxes */
    .lesson-reader blockquote {
        border-left: 4px solid #3b82f6;
        background: #eff6ff;
        padding: 0.85rem 1.25rem;
        border-radius: 0 0.75rem 0.75rem 0;
        margin: 1.25rem 0;
        color: #1e40af;
        font-style: normal;
    }
    .dark .lesson-reader blockquote {
        background: rgba(30, 58, 138, 0.25);
        color: #93c5fd;
    }
    /* Diagrams / SVGs */
    .lesson-reader svg {
        max-width: 100%;
        height: auto;
        margin: 1.5rem auto;
        display: block;
        border-radius: 0.75rem;
    }
    .lesson-reader .mermaid {
        display: flex;
        justify-content: center;
        margin: 1.5rem 0;
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
    }
    .dark .lesson-reader .mermaid {
        background: #1e293b;
        border-color: #334155;
    }
</style>

<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="textbookViewerApp()" x-cloak>
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.textbooks.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Back to Textbooks
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-xs text-gray-500">{{ $textbook->subject->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                {{ $textbook->title }}
            </h1>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-600 dark:text-gray-400">
                @if($textbook->author)
                    <span>Author: {{ $textbook->author }}</span>
                    <span>•</span>
                @endif
                @if($textbook->curriculum)
                    <span class="text-blue-600 font-medium">Curriculum: {{ $textbook->curriculum->title }}</span>
                    <span>•</span>
                @endif
                <span class="font-semibold text-gray-700 dark:text-gray-300"><span x-text="chapters.length"></span> Chapters</span>
                <span>•</span>
                <span class="text-gray-500"><span x-text="totalSectionsCount"></span> Total Sub-sections</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Active Student Course Status & Toggle -->
            <form action="{{ route('admin.textbooks.toggle-course-active', $textbook) }}" method="POST" class="inline">
                @csrf
                @if($textbook->is_course_active)
                    <button type="submit"
                            title="Click to deactivate as student course"
                            class="inline-flex items-center px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 text-xs font-bold rounded-xl shadow-2xs transition-all">
                        <i class="fas fa-star text-amber-500 mr-1.5"></i> Active Student Course
                    </button>
                @else
                    <button type="submit"
                            title="Designate this textbook as the active course for students on Guided Learning"
                            class="inline-flex items-center px-3.5 py-2 bg-gray-50 hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 dark:bg-gray-800 dark:hover:bg-indigo-950/40 dark:text-gray-300 dark:hover:text-indigo-300 border border-gray-300 dark:border-gray-600 hover:border-indigo-400 text-xs font-semibold rounded-xl transition-all shadow-2xs">
                        <i class="far fa-star text-gray-400 mr-1.5"></i> Set as Student Course
                    </button>
                @endif
            </form>

            @if(!$textbook->is_toc_approved)
                <form action="{{ route('admin.textbooks.approve-toc', $textbook) }}" method="POST">
                    @csrf
                    <input type="hidden" name="start_content_extraction" value="1">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl shadow-sm">
                        <i class="fas fa-check mr-2"></i> Approve TOC & Extract Content
                    </button>
                </form>
            @else
                <div class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold rounded-lg">
                    <i class="fas fa-check-circle mr-1.5"></i> TOC Approved
                </div>

                <form action="{{ route('admin.textbooks.extract-content', $textbook) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm">
                        <i class="fas fa-brain mr-2"></i> Extract Lessons with AI
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($textbook->toc_extraction_status === 'processing' || $textbook->toc_extraction_status === 'pending' || $textbook->content_extraction_status === 'processing')
    <div x-data="{
            pollInterval: null,
            init() {
                this.pollInterval = setInterval(async () => {
                    try {
                        const res = await fetch('{{ route('admin.textbooks.status', $textbook) }}');
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data.toc_extraction_status !== 'processing' && data.toc_extraction_status !== 'pending' && data.content_extraction_status !== 'processing') {
                            clearInterval(this.pollInterval);
                            window.location.reload();
                        }
                    } catch (e) {
                        console.error('Textbook status polling error:', e);
                    }
                }, 3500);
            }
        }"
        class="p-5 rounded-2xl bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100 shadow-sm">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fas fa-spinner fa-spin text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-blue-900 dark:text-white flex items-center gap-2">
                        AI Textbook Extraction in Progress
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                    </h4>
                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                        Gemini is analyzing the textbook. This page will update automatically once complete.
                    </p>
                </div>
            </div>
            <span class="text-[11px] font-medium text-blue-600 dark:text-blue-400 bg-blue-100/70 dark:bg-blue-900/50 px-2.5 py-1 rounded-full flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                Checking live status...
            </span>
        </div>
    </div>
    @elseif($textbook->toc_extraction_status === 'failed')
        @php
            $errorData = null;
            if (!empty($textbook->extraction_error)) {
                $errorData = is_array($textbook->extraction_error) ? $textbook->extraction_error : json_decode($textbook->extraction_error, true);
            }
            $isTokenExhausted = ($errorData['type'] ?? '') === 'token_exhausted';
            $isAuthError = ($errorData['type'] ?? '') === 'auth_error';
        @endphp

        <div class="p-6 rounded-2xl {{ $isTokenExhausted ? 'bg-amber-50/90 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700' : 'bg-red-50/90 dark:bg-red-950/40 border-2 border-red-300 dark:border-red-700' }} shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-xl {{ $isTokenExhausted ? 'bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300' : 'bg-red-100 dark:bg-red-900/60 text-red-600 dark:text-red-300' }} flex items-center justify-center flex-shrink-0 text-xl shadow-xs">
                        @if($isTokenExhausted)
                            <i class="fas fa-coins"></i>
                        @elseif($isAuthError)
                            <i class="fas fa-key"></i>
                        @else
                            <i class="fas fa-exclamation-triangle"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-bold text-base {{ $isTokenExhausted ? 'text-amber-950 dark:text-amber-100' : 'text-red-950 dark:text-red-100' }}">
                                {{ $errorData['title'] ?? 'Table of Contents Extraction Failed' }}
                            </h4>
                            @if($isTokenExhausted)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900 border border-amber-300">
                                    Credits Depleted
                                </span>
                            @endif
                        </div>
                        <p class="text-sm mt-1 leading-relaxed {{ $isTokenExhausted ? 'text-amber-900 dark:text-amber-200' : 'text-red-900 dark:text-red-200' }}">
                            {{ $errorData['message'] ?? 'An error occurred while communicating with Google Gemini AI during Table of Contents extraction.' }}
                        </p>

                        @if(!empty($errorData['action']))
                            <div class="mt-3">
                                <a href="{{ $errorData['action'] }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-sm transition-all">
                                    <i class="fas fa-external-link-alt"></i>
                                    {{ $errorData['action_label'] ?? 'Open AI Studio Billing' }}
                                </a>
                                <span class="text-xs text-amber-700 dark:text-amber-300 ml-2">
                                    Once credits are renewed, click "Retry TOC Extraction" below.
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Retry Button -->
                <div class="flex-shrink-0">
                    <form action="{{ route('admin.textbooks.retry-toc', $textbook) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-redo mr-2"></i>
                            Retry TOC Extraction
                        </button>
                    </form>
                </div>
            </div>

            @if(!empty($errorData['raw']))
                <div x-data="{ showRaw: false }" class="border-t {{ $isTokenExhausted ? 'border-amber-200 dark:border-amber-800/60' : 'border-red-200 dark:border-red-800/60' }} pt-3">
                    <button type="button" @click="showRaw = !showRaw"
                            class="text-xs font-medium {{ $isTokenExhausted ? 'text-amber-700 hover:text-amber-900' : 'text-red-700 hover:text-red-900' }} flex items-center gap-1.5 focus:outline-none">
                        <i class="fas fa-code text-[10px]"></i>
                        <span x-text="showRaw ? 'Hide technical error details' : 'View technical error details'"></span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="{'rotate-180': showRaw}"></i>
                    </button>
                    <div x-show="showRaw" x-cloak style="display: none;" class="mt-2.5 p-3 rounded-xl bg-slate-900 text-slate-200 text-xs font-mono overflow-x-auto max-h-48 leading-normal select-all">
                        {{ $errorData['raw'] }}
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Chapters & Sections Table of Contents (Full Interactive & Reorderable Tree) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <!-- Card Header -->
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-800/80">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-base">
                    <i class="fas fa-list-ol text-blue-600"></i>
                    Table of Contents & Lesson Mappings
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Drag items to reorder • Click titles to edit inline • Add single or bulk sub-sections
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Bulk Add Chapters from PDF -->
                <button type="button"
                        @click="openBulkChaptersModal()"
                        class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold rounded-xl transition-all">
                    <i class="fas fa-paste mr-1.5 text-indigo-500"></i> Bulk Paste Chapters
                </button>
                <!-- Add Single Chapter -->
                <button type="button"
                        @click="createChapterPrompt()"
                        class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                    <i class="fas fa-plus mr-1.5"></i> Add Chapter
                </button>
            </div>
        </div>

        <!-- Chapters List Container (SortableJS draggable) -->
        <div class="divide-y divide-gray-200 dark:divide-gray-700" id="chapters-list-container">
            <template x-for="(chapter, cIndex) in chapters" :key="chapter.id">
                <div class="p-5 transition-colors chapter-item" :id="'chapter-row-' + chapter.id" :data-id="chapter.id">
                    <!-- Chapter Header Row -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <!-- Drag Handle & Reorder Arrows -->
                            <div class="flex items-center gap-1 flex-shrink-0 text-gray-400">
                                <span class="chapter-drag-handle p-1.5 hover:text-gray-700 dark:hover:text-gray-200 cursor-grab active:cursor-grabbing text-xs" title="Drag to reorder chapter">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                                <div class="flex flex-col">
                                    <button type="button" @click="moveChapter(cIndex, -1)" :disabled="cIndex === 0"
                                            class="text-[9px] p-0.5 hover:text-blue-600 disabled:opacity-20 disabled:cursor-not-allowed leading-none">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <button type="button" @click="moveChapter(cIndex, 1)" :disabled="cIndex === chapters.length - 1"
                                            class="text-[9px] p-0.5 hover:text-blue-600 disabled:opacity-20 disabled:cursor-not-allowed leading-none">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Chapter Icon -->
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="fas fa-folder"></i>
                            </div>

                            <!-- Title & Pages (View vs Edit Mode) -->
                            <div class="flex-1 min-w-0">
                                <!-- View Mode -->
                                <template x-if="!chapter.isEditing">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-gray-900 dark:text-white text-sm cursor-pointer hover:text-blue-600 transition-colors"
                                                @click="startEditChapter(chapter)"
                                                title="Click to edit title">
                                                <span x-text="chapter.title"></span>
                                                <i class="fas fa-pencil-alt text-[10px] text-gray-300 dark:text-gray-600 ml-1"></i>
                                            </h4>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            <span x-show="chapter.page_start">
                                                Pages <span x-text="chapter.page_start"></span> - <span x-text="chapter.page_end || 'end'"></span> •
                                            </span>
                                            <span class="font-medium text-gray-500 dark:text-gray-400">
                                                <span x-text="chapter.sections.length"></span> sub-sections
                                            </span>
                                        </p>
                                    </div>
                                </template>

                                <!-- Inline Edit Mode -->
                                <template x-if="chapter.isEditing">
                                    <div class="space-y-2 py-1">
                                        <input type="text" x-model="chapter.editTitle"
                                               @keydown.enter="saveChapter(chapter)"
                                               @keydown.escape="chapter.isEditing = false"
                                               class="w-full text-sm font-bold rounded-lg border-blue-400 dark:border-blue-500 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        <div class="flex items-center gap-2">
                                            <input type="number" x-model="chapter.editPageStart" placeholder="Start pg"
                                                   class="w-24 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2.5">
                                            <span class="text-gray-400 text-xs">to</span>
                                            <input type="number" x-model="chapter.editPageEnd" placeholder="End pg"
                                                   class="w-24 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2.5">
                                            <button type="button" @click="saveChapter(chapter)"
                                                    class="px-2.5 py-1 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-xs">
                                                <i class="fas fa-check mr-1"></i> Save
                                            </button>
                                            <button type="button" @click="chapter.isEditing = false"
                                                    class="px-2.5 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Chapter Action Buttons -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <!-- Bulk Add Sections Button -->
                            <button type="button"
                                    @click="openBulkSectionsModal(chapter)"
                                    class="px-2.5 py-1 text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg font-bold transition-all"
                                    title="Paste or batch-add multiple sub-sections from PDF">
                                <i class="fas fa-paste mr-1 text-emerald-600"></i> Bulk Add
                            </button>

                            <!-- Batch Generate All Sections Button -->
                            <button type="button"
                                    @click="batchGenerateChapter(chapter)"
                                    :disabled="chapter.isBatchGenerating"
                                    class="px-2.5 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg font-bold transition-all disabled:opacity-50"
                                    title="Generate reading text for all sub-sections in this chapter using First-Principles AI Scheme">
                                <span x-show="!chapter.isBatchGenerating"><i class="fas fa-wand-magic-sparkles mr-1 text-indigo-600"></i> Generate All</span>
                                <span x-show="chapter.isBatchGenerating"><i class="fas fa-spinner fa-spin mr-1"></i> Generating...</span>
                            </button>

                            <!-- Extract Content Button -->
                            <form :action="'/admin/textbooks/{{ $textbook->id }}/extract-content'" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="chapter_id" :value="chapter.id">
                                <button type="submit" class="px-2.5 py-1 text-xs border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-600 dark:text-gray-300 rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt mr-1"></i> Extract
                                </button>
                            </form>

                            <!-- Delete Chapter Button -->
                            <button type="button"
                                    @click="requestDelete('chapter', chapter.id, null, chapter.title)"
                                    class="p-1.5 text-xs text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                                    title="Delete chapter">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <!-- Toggle Expand / Collapse -->
                            <button type="button" @click="chapter.isOpen = !chapter.isOpen" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': chapter.isOpen}"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Sections Accordion Body -->
                    <div x-show="chapter.isOpen" x-collapse class="mt-4 pl-4 sm:pl-8 border-l-2 border-blue-100 dark:border-gray-700 space-y-3">
                        <!-- Sections Container (SortableJS draggable) -->
                        <div class="space-y-2.5 sections-container" :id="'chapter-' + chapter.id + '-sections-container'" :data-chapter-id="chapter.id">
                            <template x-for="(section, sIndex) in chapter.sections" :key="section.id">
                                <div class="p-3.5 bg-gray-50/90 dark:bg-gray-750/80 hover:bg-gray-100/70 dark:hover:bg-gray-750 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-3 border border-gray-200/60 dark:border-gray-700/60 transition-all section-item"
                                     :id="'section-row-' + section.id" :data-id="section.id">
                                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                        <!-- Drag Handle & Arrows -->
                                        <div class="flex items-center gap-1 text-gray-300 dark:text-gray-600 flex-shrink-0">
                                            <span class="section-drag-handle p-1 hover:text-gray-600 dark:hover:text-gray-300 cursor-grab active:cursor-grabbing text-xs" title="Drag to reorder section">
                                                <i class="fas fa-grip-vertical"></i>
                                            </span>
                                            <div class="flex flex-col">
                                                <button type="button" @click="moveSection(chapter, sIndex, -1)" :disabled="sIndex === 0"
                                                        class="text-[8px] p-0.5 hover:text-blue-600 disabled:opacity-20 disabled:cursor-not-allowed leading-none">
                                                    <i class="fas fa-chevron-up"></i>
                                                </button>
                                                <button type="button" @click="moveSection(chapter, sIndex, 1)" :disabled="sIndex === chapter.sections.length - 1"
                                                        class="text-[8px] p-0.5 hover:text-blue-600 disabled:opacity-20 disabled:cursor-not-allowed leading-none">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <i class="fas fa-file-alt text-gray-400 flex-shrink-0 text-xs"></i>

                                        <!-- Title & Pages View / Edit -->
                                        <div class="flex-1 min-w-0">
                                            <template x-if="!section.isEditing">
                                                <div>
                                                    <div class="font-semibold text-xs text-gray-900 dark:text-white flex items-center gap-1.5 cursor-pointer hover:text-blue-600 transition-colors"
                                                         @click="startEditSection(section)"
                                                         title="Click to edit title">
                                                        <span x-text="section.title"></span>
                                                        <i class="fas fa-pencil-alt text-[9px] text-gray-300 dark:text-gray-600"></i>
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span x-show="section.page_start" class="text-[11px] text-gray-400">
                                                            Pages <span x-text="section.page_start"></span> - <span x-text="section.page_end || 'end'"></span>
                                                        </span>
                                                        <span x-show="section.has_content" class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                                            <i class="fas fa-check-circle"></i> Extracted
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Inline Edit Mode for Section -->
                                            <template x-if="section.isEditing">
                                                <div class="space-y-1.5 py-0.5">
                                                    <input type="text" x-model="section.editTitle"
                                                           @keydown.enter="saveSection(section)"
                                                           @keydown.escape="section.isEditing = false"
                                                           class="w-full text-xs font-semibold rounded-lg border-blue-400 dark:border-blue-500 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2.5 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                    <div class="flex items-center gap-2">
                                                        <input type="number" x-model="section.editPageStart" placeholder="Start"
                                                               class="w-16 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-0.5 px-2">
                                                        <span class="text-gray-400 text-xs">to</span>
                                                        <input type="number" x-model="section.editPageEnd" placeholder="End"
                                                               class="w-16 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-0.5 px-2">
                                                        <button type="button" @click="saveSection(section)"
                                                                class="px-2 py-0.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded shadow-xs">
                                                            Save
                                                        </button>
                                                        <button type="button" @click="section.isEditing = false"
                                                                class="px-2 py-0.5 text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Indicator Mapping, Lesson Studio & Delete -->
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <!-- Lesson Studio Button -->
                                        <button type="button"
                                                @click="openLessonStudio(section, chapter)"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition-colors shadow-xs"
                                                title="Open First-Principles Lesson Studio (Build reading texts, formulas, and quizzes)">
                                            <i class="fas fa-book-open text-indigo-500"></i>
                                            <span>Lesson Studio</span>
                                            <span x-show="section.is_published && section.has_content" class="w-2 h-2 rounded-full bg-emerald-500 inline-block" title="Published with Content"></span>
                                        </button>

                                        <select @change="linkIndicator($event.target.value, section.id)"
                                                class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2 max-w-[220px]">
                                            <option value="">Map Indicator...</option>
                                            <template x-for="ind in availableIndicators" :key="ind.id">
                                                <option :value="ind.id" :selected="section.curriculum_indicator_id == ind.id"
                                                        x-text="'[' + ind.code + '] ' + ind.title"></option>
                                            </template>
                                        </select>
                                        
                                        <button type="button"
                                                @click="requestDelete('section', section.id, chapter.id, section.title)"
                                                class="p-1 text-xs text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded transition-colors"
                                                title="Delete section">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Empty state -->
                        <div x-show="chapter.sections.length === 0" class="p-3 bg-gray-50 dark:bg-gray-750/40 rounded-xl text-center text-xs text-gray-400 italic">
                            No sub-sections yet. Use the quick-add row below or click "Bulk Add" above.
                        </div>

                        <!-- Inline Fast-Add Row (Press Enter to add rapidly) -->
                        <div class="p-3 rounded-xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 flex flex-col sm:flex-row items-center gap-2 mt-2">
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1 flex-shrink-0">
                                <i class="fas fa-plus-circle"></i> Quick Add:
                            </span>
                            <input type="text"
                                   x-model="chapter.newTitle"
                                   @keydown.enter="addQuickSection(chapter)"
                                   placeholder="Sub-section title (press Enter to save)..."
                                   class="flex-1 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 focus:ring-2 focus:ring-blue-500 focus:outline-none w-full sm:w-auto">
                            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                                <input type="number"
                                       x-model="chapter.newPageStart"
                                       @keydown.enter="addQuickSection(chapter)"
                                       placeholder="Start"
                                       class="w-16 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-2">
                                <span class="text-gray-400 text-xs">-</span>
                                <input type="number"
                                       x-model="chapter.newPageEnd"
                                       @keydown.enter="addQuickSection(chapter)"
                                       placeholder="End"
                                       class="w-16 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-2">
                                <button type="button"
                                        @click="addQuickSection(chapter)"
                                        :disabled="chapter.isSavingQuick"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-xs flex-shrink-0 disabled:opacity-50">
                                    <span x-show="!chapter.isSavingQuick"><i class="fas fa-plus"></i> Add</span>
                                    <span x-show="chapter.isSavingQuick"><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Global Empty State -->
            <div x-show="chapters.length === 0" class="text-center py-16 text-gray-400 space-y-3">
                <i class="fas fa-book-open text-3xl text-gray-300 dark:text-gray-600"></i>
                <p class="text-sm">No Table of Contents extracted yet.</p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" @click="openBulkChaptersModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 shadow-sm">
                        <i class="fas fa-paste mr-1.5"></i> Bulk Paste Chapters from PDF
                    </button>
                    <button type="button" @click="createChapterPrompt()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-sm">
                        <i class="fas fa-plus mr-1.5"></i> Add First Chapter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: Bulk Add Sections from PDF Text      -->
    <!-- ========================================== -->
    <div x-show="bulkSectionsModal.show" x-cloak style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="bulkSectionsModal.show = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
             @click.away="bulkSectionsModal.show = false">
            <!-- Modal Header -->
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-750/30">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-base">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Bulk Add Sections</h3>
                        <p class="text-xs text-gray-500">Adding to: <span class="font-semibold text-blue-600" x-text="bulkSectionsModal.chapterTitle"></span></p>
                    </div>
                </div>
                <button type="button" @click="bulkSectionsModal.show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Paste TOC Lines Directly from PDF
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Copy the lines from your textbook PDF and paste them below. The parser automatically strips dots (<code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">....</code>) and detects page numbers!
                    </p>
                    <textarea x-model="bulkSectionsModal.rawText"
                              @input="parseBulkSections()"
                              rows="5"
                              placeholder="1.1 Counting and writing numbers.................... 1&#10;1.2 Ordering numbers................................ 4&#10;1.3 Rounding numbers................................ 7&#10;1.4 Prime and composite numbers..................... 9"
                              class="w-full text-xs font-mono rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <!-- Parsed Live Preview Table -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            Parsed Preview (<span x-text="bulkSectionsModal.rows.length"></span> items)
                        </h4>
                        <button type="button" @click="addEmptySectionRow()" class="text-xs text-emerald-600 dark:text-emerald-400 font-bold hover:underline">
                            + Add Empty Row
                        </button>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-56 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-750 text-gray-600 dark:text-gray-300 font-bold sticky top-0">
                                <tr>
                                    <th class="p-2.5 w-8">#</th>
                                    <th class="p-2.5">Sub-section Title</th>
                                    <th class="p-2.5 w-20">Start Pg</th>
                                    <th class="p-2.5 w-20">End Pg</th>
                                    <th class="p-2.5 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                <template x-for="(row, rIdx) in bulkSectionsModal.rows" :key="rIdx">
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/50">
                                        <td class="p-2 text-gray-400 font-mono text-[11px]" x-text="rIdx + 1"></td>
                                        <td class="p-1.5">
                                            <input type="text" x-model="row.title" class="w-full text-xs rounded border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-1 px-2 focus:ring-1 focus:ring-emerald-500">
                                        </td>
                                        <td class="p-1.5">
                                            <input type="number" x-model="row.page_start" class="w-full text-xs rounded border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-1 px-2 text-center">
                                        </td>
                                        <td class="p-1.5">
                                            <input type="number" x-model="row.page_end" class="w-full text-xs rounded border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-1 px-2 text-center">
                                        </td>
                                        <td class="p-1.5 text-center">
                                            <button type="button" @click="bulkSectionsModal.rows.splice(rIdx, 1)" class="text-red-400 hover:text-red-600 p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750/30 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Will create <strong x-text="bulkSectionsModal.rows.length"></strong> sections silently.
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="bulkSectionsModal.show = false" class="px-3.5 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitBulkSections()"
                            :disabled="bulkSectionsModal.saving || bulkSectionsModal.rows.length === 0"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs disabled:opacity-50 flex items-center gap-1.5">
                        <span x-show="!bulkSectionsModal.saving"><i class="fas fa-check mr-1"></i> Save All Sections</span>
                        <span x-show="bulkSectionsModal.saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: Bulk Add Chapters from PDF Text      -->
    <!-- ========================================== -->
    <div x-show="bulkChaptersModal.show" x-cloak style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="bulkChaptersModal.show = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-4xl w-full border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
             @click.away="bulkChaptersModal.show = false">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-750/30">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-base">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Bulk Paste Table of Contents</h3>
                        <p class="text-xs text-gray-500">Intelligently detects main chapters (1, 2...) and sub-sections (1.1, 1.2...) directly from PDF text</p>
                    </div>
                </div>
                <button type="button" @click="bulkChaptersModal.show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Paste Table of Contents Lines from PDF</span>
                        <span class="text-[11px] font-normal text-indigo-600 dark:text-indigo-400">Chapters: 1. Name... • Sub-sections: 1.1 Topic...</span>
                    </label>
                    <textarea x-model="bulkChaptersModal.rawText"
                              @input="parseBulkChapters()"
                              rows="5"
                              placeholder="1. NUMBERS AND NUMERALS ........................... 1&#10;   1.1 Counting and writing numbers ............... 1&#10;   1.2 Ordering numbers ........................... 4&#10;2. SETS ........................................... 35&#10;   2.1 Idea of a set .............................. 35"
                              class="w-full text-xs font-mono rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            Parsed Hierarchy (<span x-text="bulkChaptersModal.chapters.length"></span> Chapters, <span x-text="bulkSectionsCount"></span> Sub-sections)
                        </h4>
                        <button type="button" @click="addEmptyBulkChapter()" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                            + Add Empty Chapter
                        </button>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-80 overflow-y-auto p-3 bg-gray-50/50 dark:bg-gray-850/50 space-y-3">
                        <template x-if="bulkChaptersModal.chapters.length === 0">
                            <div class="py-8 text-center text-gray-400 text-xs">
                                <i class="fas fa-file-alt text-2xl mb-2 text-gray-300 dark:text-gray-600 block"></i>
                                Paste your textbook Table of Contents lines above to preview parsed chapters & sub-sections.
                            </div>
                        </template>

                        <template x-for="(ch, cIdx) in bulkChaptersModal.chapters" :key="cIdx">
                            <div class="border border-gray-200/90 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-2xs">
                                <!-- Chapter Header Row -->
                                <div class="p-2.5 bg-indigo-50/70 dark:bg-indigo-950/40 border-b border-indigo-100 dark:border-indigo-900/40 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white font-bold text-[10px] uppercase flex-shrink-0">
                                            Chapter <span x-text="cIdx + 1"></span>
                                        </span>
                                        <input type="text" x-model="ch.title" placeholder="Chapter title"
                                               class="flex-1 min-w-0 text-xs font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 py-1 px-2.5 focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <div class="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400">
                                            <span>Pg:</span>
                                            <input type="number" x-model="ch.page_start" placeholder="Start" class="w-14 text-xs text-center rounded border border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-0.5 px-1">
                                            <span>-</span>
                                            <input type="number" x-model="ch.page_end" placeholder="End" class="w-14 text-xs text-center rounded border border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-0.5 px-1">
                                        </div>
                                        <button type="button" @click="addEmptySectionToBulkChapter(cIdx)" class="px-2 py-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors" title="Add sub-section under this chapter">
                                            <i class="fas fa-plus mr-0.5"></i> Sub
                                        </button>
                                        <button type="button" @click="bulkChaptersModal.chapters.splice(cIdx, 1)" class="p-1 text-red-400 hover:text-red-600 transition-colors" title="Delete chapter">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Sub-sections List Under Chapter -->
                                <div class="p-2 space-y-1.5 bg-gray-50/40 dark:bg-gray-800/40">
                                    <template x-if="!ch.sections || ch.sections.length === 0">
                                        <div class="py-1 px-3 text-[11px] text-gray-400 italic">
                                            No sub-sections under this chapter (standalone chapter).
                                        </div>
                                    </template>
                                    <template x-for="(sec, sIdx) in ch.sections" :key="sIdx">
                                        <div class="flex items-center justify-between gap-2 pl-3 pr-1 py-1 rounded-lg hover:bg-white dark:hover:bg-gray-750 border border-transparent hover:border-gray-200 dark:hover:border-gray-700 text-xs transition-all">
                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                                <span class="text-gray-400 font-mono text-[11px]">↳</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 flex-shrink-0">
                                                    Sec <span x-text="(cIdx + 1) + '.' + (sIdx + 1)"></span>
                                                </span>
                                                <input type="text" x-model="sec.title" placeholder="Sub-section title"
                                                       class="flex-1 min-w-0 text-xs text-gray-800 dark:text-gray-200 bg-transparent rounded border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-indigo-500 py-0.5 px-1.5">
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                                <div class="flex items-center gap-1 text-[11px] text-gray-400">
                                                    <input type="number" x-model="sec.page_start" placeholder="Start" class="w-12 text-[11px] text-center rounded border border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-0.5 px-1">
                                                    <span>-</span>
                                                    <input type="number" x-model="sec.page_end" placeholder="End" class="w-12 text-[11px] text-center rounded border border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-0.5 px-1">
                                                </div>
                                                <button type="button" @click="promoteSectionToChapter(cIdx, sIdx)" class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" title="Promote to top-level Chapter">
                                                    <i class="fas fa-outdent text-[11px]"></i>
                                                </button>
                                                <button type="button" @click="ch.sections.splice(sIdx, 1)" class="p-1 text-red-400 hover:text-red-600 transition-colors" title="Delete sub-section">
                                                    <i class="fas fa-times text-[11px]"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750/30 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Will create <strong x-text="bulkChaptersModal.chapters.length"></strong> chapters and <strong x-text="bulkSectionsCount"></strong> sub-sections.
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="bulkChaptersModal.show = false" class="px-3.5 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitBulkChapters()"
                            :disabled="bulkChaptersModal.saving || bulkChaptersModal.chapters.length === 0"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs disabled:opacity-50 flex items-center gap-1.5">
                        <span x-show="!bulkChaptersModal.saving"><i class="fas fa-check mr-1"></i> Save All Chapters & Sub-sections</span>
                        <span x-show="bulkChaptersModal.saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: Pedagogical Lesson Studio Modal    -->
    <!-- ========================================== -->
    <div x-show="lessonStudio.show" x-cloak style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-900/75 backdrop-blur-xs"
         @keydown.escape.window="if(!lessonStudio.generating) lessonStudio.show = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-6xl w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden flex flex-col h-[94vh]"
             @click.away="if(!lessonStudio.generating) lessonStudio.show = false">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/70 dark:bg-gray-750/50">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Lesson Studio</span>
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <span class="text-xs text-gray-500 truncate" x-text="lessonStudio.chapter?.title"></span>
                            <span x-show="lessonStudio.is_published" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Published
                            </span>
                            <span x-show="!lessonStudio.is_published" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Draft
                            </span>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base sm:text-lg truncate" x-text="lessonStudio.title"></h3>
                    </div>
                </div>

                <!-- Scheme & Action Controls -->
                <div class="flex items-center gap-2.5 flex-wrap flex-shrink-0">
                    <!-- Scheme Selector -->
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400">Scheme:</label>
                        <select x-model="lessonStudio.scheme"
                                class="text-xs rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 font-semibold focus:ring-2 focus:ring-indigo-500 shadow-2xs cursor-pointer">
                            @foreach(\App\Services\PedagogicalCourseSchemeService::AVAILABLE_SCHEMES as $sKey => $sData)
                                <option value="{{ $sKey }}">{{ $sData['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Generate AI Button -->
                    <button type="button"
                            @click="generateLessonStudioContent()"
                            :disabled="lessonStudio.generating"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white shadow-xs disabled:opacity-50 flex items-center gap-1.5 transition-all">
                        <span x-show="!lessonStudio.generating"><i class="fas fa-bolt"></i> Generate with Scheme</span>
                        <span x-show="lessonStudio.generating"><i class="fas fa-spinner fa-spin"></i> Synthesizing...</span>
                    </button>

                    <!-- Close Button -->
                    <button type="button" @click="lessonStudio.show = false"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="px-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="lessonStudio.activeTab = 'text'"
                            class="py-3 px-3.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2"
                            :class="lessonStudio.activeTab === 'text' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        <i class="fas fa-file-alt"></i>
                        <span>Reading Text & Math Logic</span>
                    </button>

                    <button type="button"
                            @click="lessonStudio.activeTab = 'takeaways'"
                            class="py-3 px-3.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2"
                            :class="lessonStudio.activeTab === 'takeaways' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        <i class="fas fa-bullseye"></i>
                        <span>Key Takeaways</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300"
                              x-text="lessonStudio.takeaways ? lessonStudio.takeaways.length : 0"></span>
                    </button>

                    <button type="button"
                            @click="lessonStudio.activeTab = 'quiz'"
                            class="py-3 px-3.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2"
                            :class="lessonStudio.activeTab === 'quiz' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        <i class="fas fa-brain"></i>
                        <span>Checkpoint Quiz</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300"
                              x-text="lessonStudio.quiz ? lessonStudio.quiz.length : 0"></span>
                    </button>
                    <button type="button"
                            @click="lessonStudio.activeTab = 'media'"
                            class="py-3 px-3.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2"
                            :class="lessonStudio.activeTab === 'media' ? 'border-red-600 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        <i class="fab fa-youtube text-red-500"></i>
                        <span>Media & Video Dock</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 font-semibold"
                              x-text="lessonStudio.mediaResources ? lessonStudio.mediaResources.length : 0"></span>
                    </button>
                </div>

                <!-- Scheme Guidance Hint -->
                <div class="hidden md:flex items-center gap-1.5 text-xs text-gray-400">
                    <i class="fas fa-info-circle text-indigo-500"></i>
                    <span>First-Principles: Etymology &bull; Real-World Use &bull; Operator Logic &bull; Worked Examples</span>
                </div>
            </div>

            <!-- Modal Body (Tab Panels) -->
            <div class="flex-1 overflow-hidden p-5 bg-gray-50/50 dark:bg-gray-900/40">
                <!-- Loading State -->
                <div x-show="lessonStudio.loading" class="h-full flex items-center justify-center">
                    <div class="text-center space-y-2">
                        <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
                        <p class="text-xs text-gray-500">Loading lesson content...</p>
                    </div>
                </div>

                <!-- TAB 1: Reading Text & Formula Split Editor / Preview -->
                <div x-show="!lessonStudio.loading && lessonStudio.activeTab === 'text'" class="h-full flex flex-col md:flex-row gap-4">
                    <!-- Left: Markdown Editor -->
                    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs">
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-750/30">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-edit text-indigo-500"></i> Markdown Editor
                            </span>
                            <span class="text-[11px] text-gray-400">Supports LaTeX math: <code>$L \times W$</code></span>
                        </div>
                        <div class="flex-1 p-2">
                            <textarea x-model="lessonStudio.markdown"
                                      @input="renderStudioPreview()"
                                      placeholder="Write or generate reading text using the Pedagogical Course Scheme..."
                                      class="w-full h-full text-xs font-mono rounded-lg border-0 bg-transparent text-gray-900 dark:text-gray-100 p-3 resize-none focus:outline-none focus:ring-0 leading-relaxed"></textarea>
                        </div>
                    </div>

                    <!-- Right: Live Rendered Preview with KaTeX Math -->
                    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs">
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-750/30">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-eye text-emerald-500"></i> Student View Preview
                            </span>
                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                <i class="fas fa-square-root-alt"></i> KaTeX Math Live
                            </span>
                        </div>
                        <div class="flex-1 p-5 overflow-y-auto">
                            <div id="studio-rendered-preview" class="lesson-reader text-sm leading-relaxed prose dark:prose-invert max-w-none">
                                <p class="text-gray-400 italic text-xs">Generating reading text will populate the formatted lesson preview here...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Key Takeaways -->
                <div x-show="!lessonStudio.loading && lessonStudio.activeTab === 'takeaways'" class="h-full overflow-y-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Key Takeaways (High-Yield Concept Anchors)</h4>
                            <p class="text-xs text-gray-500">These bullet points are shown to students as lesson learning outcomes.</p>
                        </div>
                        <button type="button" @click="addTakeawayItem()" class="px-3 py-1.5 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg border border-indigo-200 transition-colors">
                            + Add Takeaway
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(item, tIdx) in lessonStudio.takeaways" :key="tIdx">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-755/50 border border-gray-200 dark:border-gray-700">
                                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5" x-text="tIdx + 1"></span>
                                <input type="text" x-model="lessonStudio.takeaways[tIdx]"
                                       class="flex-1 text-xs rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 focus:ring-2 focus:ring-indigo-500">
                                <button type="button" @click="lessonStudio.takeaways.splice(tIdx, 1)" class="p-1.5 text-red-400 hover:text-red-600 text-xs">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </template>
                        <div x-show="!lessonStudio.takeaways || lessonStudio.takeaways.length === 0" class="py-8 text-center text-xs text-gray-400 italic">
                            No takeaways defined yet. Click "Generate with Scheme" or "+ Add Takeaway" above.
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Checkpoint Quiz -->
                <div x-show="!lessonStudio.loading && lessonStudio.activeTab === 'quiz'" class="h-full overflow-y-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Concept Checkpoint Quiz</h4>
                            <p class="text-xs text-gray-500">Embedded comprehension questions validating etymology, usage, and operator logic.</p>
                        </div>
                        <button type="button" @click="addQuizQuestion()" class="px-3 py-1.5 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-lg border border-indigo-200 transition-colors">
                            + Add Question
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(q, qIdx) in lessonStudio.quiz" :key="qIdx">
                            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-750/50 border border-gray-200 dark:border-gray-700 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="font-bold text-indigo-600 text-xs" x-text="'Q' + (qIdx + 1) + '.'"></span>
                                        <input type="text" x-model="q.question" placeholder="Question prompt..."
                                               class="w-full text-xs font-semibold rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <button type="button" @click="lessonStudio.quiz.splice(qIdx, 1)" class="p-1.5 text-red-400 hover:text-red-600 text-xs">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-gray-800 border"
                                             :class="q.answer === opt ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio" :name="'correct-opt-' + qIdx" :value="opt"
                                                   :checked="q.answer === opt"
                                                   @change="q.answer = opt; q.correct_index = oIdx"
                                                   class="text-emerald-600 focus:ring-emerald-500">
                                            <input type="text" x-model="q.options[oIdx]"
                                                   @input="if(q.correct_index === oIdx) q.answer = q.options[oIdx]"
                                                   class="w-full text-xs border-0 bg-transparent text-gray-900 dark:text-white py-0 px-1 focus:ring-0">
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1">Pedagogical Explanation:</label>
                                    <input type="text" x-model="q.explanation" placeholder="Explain why this answer is correct..."
                                           class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-3">
                                </div>
                            </div>
                        </template>
                        <div x-show="!lessonStudio.quiz || lessonStudio.quiz.length === 0" class="py-8 text-center text-xs text-gray-400 italic">
                            No checkpoint questions yet. Click "Generate with Scheme" or "+ Add Question" above.
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Media & Video Dock -->
                <div x-show="!lessonStudio.loading && lessonStudio.activeTab === 'media'" class="h-full overflow-y-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6 shadow-xs">
                    <!-- Header with AI Suggestion & Manual Add buttons -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fab fa-youtube text-red-500 text-base"></i>
                                <span>Educational Videos & Multimedia Resources</span>
                            </h4>
                            <p class="text-xs text-gray-500">Curated YouTube tutorials and visual diagrams presented in the student's lesson dock.</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button"
                                    @click="fetchAiMediaSuggestions()"
                                    :disabled="lessonStudio.suggestingMedia"
                                    class="px-3.5 py-1.5 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 rounded-xl border border-indigo-200 flex items-center gap-1.5 transition-colors disabled:opacity-50 cursor-pointer">
                                <i class="fas" :class="lessonStudio.suggestingMedia ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                                <span>AI Suggest Videos</span>
                            </button>
                        </div>
                    </div>

                    <!-- Add Custom YouTube URL bar -->
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-750/50 border border-gray-200 dark:border-gray-700 space-y-3">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            + Add YouTube Tutorial by URL or Video ID
                        </label>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="lessonStudio.newVideoUrl"
                                   @keydown.enter.prevent="addYoutubeVideoFromInput()"
                                   placeholder="e.g. https://www.youtube.com/watch?v=ivbh1khoWoU or ivbh1khoWoU"
                                   class="flex-1 text-xs rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-2 px-3 focus:ring-2 focus:ring-indigo-500">
                            <button type="button"
                                    @click="addYoutubeVideoFromInput()"
                                    :disabled="!lessonStudio.newVideoUrl || lessonStudio.resolvingUrl"
                                    class="px-4 py-2 text-xs font-bold rounded-xl bg-red-600 hover:bg-red-700 text-white disabled:opacity-50 flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <span x-show="!lessonStudio.resolvingUrl"><i class="fas fa-plus"></i> Add Video</span>
                                <span x-show="lessonStudio.resolvingUrl"><i class="fas fa-spinner fa-spin"></i> Checking...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Attached Media Grid -->
                    <div class="space-y-3">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Attached Videos for this Topic (<span x-text="lessonStudio.mediaResources ? lessonStudio.mediaResources.length : 0"></span>)
                        </h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-show="lessonStudio.mediaResources && lessonStudio.mediaResources.length > 0">
                            <template x-for="(med, mIdx) in lessonStudio.mediaResources" :key="med.id || mIdx">
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                                    <div>
                                        <!-- Video Thumbnail with Play Overlay -->
                                        <div class="relative aspect-video bg-slate-900 group cursor-pointer" @click="previewVideo(med)">
                                            <img :src="med.thumbnail_url || 'https://img.youtube.com/vi/' + med.video_id + '/hqdefault.jpg'"
                                                 :alt="med.title"
                                                 class="w-full h-full object-cover group-hover:opacity-85 transition-opacity"
                                                 x-on:error="$event.target.src='https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400&q=80'">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="w-11 h-11 rounded-full bg-red-600 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                                    <i class="fas fa-play text-sm ml-0.5"></i>
                                                </span>
                                            </div>
                                            <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/75 text-white text-[10px] font-mono">
                                                YouTube
                                            </span>
                                        </div>

                                        <!-- Video Info -->
                                        <div class="p-3 space-y-2">
                                            <input type="text" x-model="med.title" placeholder="Video Title"
                                                   class="w-full text-xs font-bold border-0 p-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 focus:bg-gray-50 dark:focus:bg-gray-700 text-gray-900 dark:text-white">
                                            <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                                                <i class="fab fa-youtube text-red-500"></i>
                                                <input type="text" x-model="med.channel" placeholder="Channel / Author"
                                                       class="w-full border-0 p-0 text-[11px] text-gray-500 bg-transparent focus:ring-0">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer / Actions -->
                                    <div class="p-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between bg-gray-50/50 dark:bg-gray-750/30">
                                        <button type="button" @click="previewVideo(med)" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1 cursor-pointer">
                                            <i class="fas fa-play-circle"></i> Preview
                                        </button>
                                        <button type="button" @click="lessonStudio.mediaResources.splice(mIdx, 1)" class="p-1 text-red-400 hover:text-red-600 text-xs cursor-pointer" title="Remove video">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="!lessonStudio.mediaResources || lessonStudio.mediaResources.length === 0"
                             class="py-12 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 space-y-2">
                            <i class="fab fa-youtube text-4xl text-gray-300 dark:text-gray-600"></i>
                            <p class="text-xs text-gray-500 font-medium">No video tutorials attached yet.</p>
                            <p class="text-[11px] text-gray-400">Click "AI Suggest Videos" or paste a YouTube URL above to attach educational videos for students.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Video Preview Modal -->
            <div x-show="lessonStudio.previewModalOpen" x-cloak style="display: none;"
                 class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/85 backdrop-blur-xs"
                 @keydown.escape.window="closeVideoPreview()">
                <div class="bg-gray-900 rounded-2xl max-w-3xl w-full border border-gray-700 overflow-hidden shadow-2xl"
                     @click.away="closeVideoPreview()">
                    <div class="p-3 border-b border-gray-800 flex items-center justify-between text-white">
                        <h4 class="text-xs font-bold truncate flex items-center gap-2">
                            <i class="fab fa-youtube text-red-500"></i>
                            <span x-text="lessonStudio.previewingVideo?.title || 'Video Preview'"></span>
                        </h4>
                        <button type="button" @click="closeVideoPreview()" class="text-gray-400 hover:text-white p-1 cursor-pointer">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="aspect-video w-full bg-black">
                        <template x-if="lessonStudio.previewingVideo?.video_id">
                            <iframe :src="'https://www.youtube-nocookie.com/embed/' + lessonStudio.previewingVideo.video_id + '?autoplay=1'"
                                    class="w-full h-full border-0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="lessonStudio.is_published" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Publish to Student Dashboard
                        </span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="lessonStudio.show = false"
                            class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                        Cancel
                    </button>
                    <button type="button" @click="saveLessonStudioContent(false)"
                            :disabled="lessonStudio.saving"
                            class="px-4 py-2 text-xs font-bold rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-200 disabled:opacity-50">
                        <span x-show="!lessonStudio.saving">Save as Draft</span>
                        <span x-show="lessonStudio.saving"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    </button>
                    <button type="button" @click="saveLessonStudioContent(true)"
                            :disabled="lessonStudio.saving"
                            class="px-5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs disabled:opacity-50 flex items-center gap-1.5">
                        <span x-show="!lessonStudio.saving"><i class="fas fa-check mr-1"></i> Save & Publish</span>
                        <span x-show="lessonStudio.saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Live Toast Notification -->

    <!-- ========================================== -->
    <!-- MODAL: Custom Delete Confirmation          -->
    <!-- ========================================== -->
    <div x-show="deleteModal.show" x-cloak style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="deleteModal.show = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden p-6 space-y-4"
             @click.away="deleteModal.show = false">
            <div class="flex items-center gap-3.5 text-red-600">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">
                        Delete <span x-text="deleteModal.type === 'chapter' ? 'Chapter' : 'Sub-section'"></span>?
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>

            <p class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-750 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                Are you sure you want to delete <strong class="text-gray-900 dark:text-white" x-text="deleteModal.title"></strong>?
                <span x-show="deleteModal.type === 'chapter'" class="block mt-1 text-red-500 font-semibold">
                    All sub-sections inside this chapter will also be removed.
                </span>
            </p>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="deleteModal.show = false" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                    Cancel
                </button>
                <button type="button" @click="confirmDelete()"
                        :disabled="deleteModal.deleting"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs disabled:opacity-50">
                    <span x-show="!deleteModal.deleting"><i class="fas fa-trash-alt mr-1"></i> Delete</span>
                    <span x-show="deleteModal.deleting"><i class="fas fa-spinner fa-spin mr-1"></i> Deleting...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Live Toast Notification -->
    <div x-show="toast.show" x-cloak style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl text-xs font-bold"
         :class="{
             'bg-emerald-600 text-white': toast.type === 'success',
             'bg-red-600 text-white': toast.type === 'error',
             'bg-blue-600 text-white': toast.type === 'info'
         }">
        <i :class="{
            'fas fa-check-circle': toast.type === 'success',
            'fas fa-exclamation-circle': toast.type === 'error',
            'fas fa-info-circle': toast.type === 'info'
        }"></i>
        <span x-text="toast.message"></span>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function textbookViewerApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
        textbookId: {{ $textbook->id }},
        
        // Reactive Chapters Array with nested sections
        chapters: @json($chaptersData ?? []),

        availableIndicators: @json($indicatorsData ?? []),

        // Toast State
        toast: { show: false, message: '', type: 'success' },
        notify(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3200);
        },

        // Bulk Sections Modal State
        bulkSectionsModal: {
            show: false,
            chapterId: null,
            chapterTitle: '',
            rawText: '',
            rows: [],
            saving: false
        },

        // Bulk Chapters & Sub-chapters Modal State
        bulkChaptersModal: {
            show: false,
            rawText: '',
            chapters: [],
            saving: false
        },

        // Lesson Studio Modal State
        lessonStudio: {
            show: false,
            loading: false,
            generating: false,
            saving: false,
            section: null,
            chapter: null,
            activeTab: 'text',
            title: '',
            scheme: 'math_first_principles',
            markdown: '',
            html: '',
            takeaways: [],
            quiz: [],
            mediaResources: [],
            newVideoUrl: '',
            resolvingUrl: false,
            suggestingMedia: false,
            previewModalOpen: false,
            previewingVideo: null,
            is_published: true,
            availableSchemes: {}
        },

        get bulkSectionsCount() {
            if (!this.bulkChaptersModal || !this.bulkChaptersModal.chapters) return 0;
            return this.bulkChaptersModal.chapters.reduce((acc, ch) => acc + (ch.sections ? ch.sections.length : 0), 0);
        },

        // Delete Confirm Modal State
        deleteModal: {
            show: false,
            type: '',
            id: null,
            parentId: null,
            title: '',
            deleting: false
        },

        get totalSectionsCount() {
            return this.chapters.reduce((sum, ch) => sum + (ch.sections ? ch.sections.length : 0), 0);
        },

        headers() {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            };
        },

        init() {
            this.$nextTick(() => {
                this.initSortable();
            });
        },

        // ──────────────────────────────────────────
        // DRAG & DROP INITIALIZATION VIA SORTABLEJS
        // ──────────────────────────────────────────
        initSortable() {
            if (typeof Sortable === 'undefined') return;

            // 1. Chapters Container Reordering
            const chaptersContainer = document.getElementById('chapters-list-container');
            if (chaptersContainer && !chaptersContainer._sortableInit) {
                chaptersContainer._sortableInit = true;
                Sortable.create(chaptersContainer, {
                    handle: '.chapter-drag-handle',
                    animation: 200,
                    ghostClass: 'bg-blue-50/80',
                    onEnd: (evt) => {
                        if (evt.oldIndex === evt.newIndex) return;

                        // Restore original DOM element position so Alpine handles reactivity diffing
                        const parent = evt.from;
                        const item = evt.item;
                        if (evt.oldIndex < evt.newIndex) {
                            parent.insertBefore(item, parent.children[evt.oldIndex]);
                        } else {
                            parent.insertBefore(item, parent.children[evt.oldIndex + 1]);
                        }

                        // Reorder in Alpine reactive state
                        const [moved] = this.chapters.splice(evt.oldIndex, 1);
                        this.chapters.splice(evt.newIndex, 0, moved);

                        // Persist to backend silently
                        this.persistChapterOrder();
                    }
                });
            }

            // 2. Section Containers Reordering for each Chapter
            this.initSectionSortables();
        },

        initSectionSortables() {
            if (typeof Sortable === 'undefined') return;
            this.$nextTick(() => {
                this.chapters.forEach(chapter => {
                    const container = document.getElementById(`chapter-${chapter.id}-sections-container`);
                    if (container && !container._sortableInit) {
                        container._sortableInit = true;
                        Sortable.create(container, {
                            handle: '.section-drag-handle',
                            animation: 200,
                            ghostClass: 'bg-indigo-50/80',
                            onEnd: (evt) => {
                                if (evt.oldIndex === evt.newIndex) return;

                                const parent = evt.from;
                                const item = evt.item;
                                if (evt.oldIndex < evt.newIndex) {
                                    parent.insertBefore(item, parent.children[evt.oldIndex]);
                                } else {
                                    parent.insertBefore(item, parent.children[evt.oldIndex + 1]);
                                }

                                const [moved] = chapter.sections.splice(evt.oldIndex, 1);
                                chapter.sections.splice(evt.newIndex, 0, moved);

                                this.persistSectionOrder(chapter.id);
                            }
                        });
                    }
                });
            });
        },

        // ──────────────────────────────────────────
        // REORDER PERSISTENCE & ARROW BUTTONS
        // ──────────────────────────────────────────
        async persistChapterOrder() {
            const ids = this.chapters.map(c => c.id);
            try {
                const res = await fetch(`/admin/textbooks/${this.textbookId}/reorder-chapters`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ chapter_ids: ids })
                });
                const data = await res.json();
                if (data.success) {
                    this.notify('Chapters reordered');
                }
            } catch (e) {
                this.notify('Failed to save chapter order: ' + e.message, 'error');
            }
        },

        async persistSectionOrder(chapterId) {
            const chapter = this.chapters.find(c => c.id === chapterId);
            if (!chapter) return;
            const ids = chapter.sections.map(s => s.id);
            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapterId}/reorder-sections`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ section_ids: ids })
                });
                const data = await res.json();
                if (data.success) {
                    this.notify('Sub-sections reordered');
                }
            } catch (e) {
                this.notify('Failed to save section order: ' + e.message, 'error');
            }
        },

        moveChapter(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.chapters.length) return;
            const [moved] = this.chapters.splice(index, 1);
            this.chapters.splice(newIndex, 0, moved);
            this.persistChapterOrder();
        },

        moveSection(chapter, index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= chapter.sections.length) return;
            const [moved] = chapter.sections.splice(index, 1);
            chapter.sections.splice(newIndex, 0, moved);
            this.persistSectionOrder(chapter.id);
        },

        // ──────────────────────────────────────────
        // INTELLIGENT TOC PARSER (STRIPS DOTS, EXTRACTS PAGES)
        // ──────────────────────────────────────────
        parseTocText(text) {
            if (!text || !text.trim()) return [];
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            const results = [];

            for (let i = 0; i < lines.length; i++) {
                let line = lines[i];
                let pageStart = null;
                let pageEnd = null;

                // Match range like "20 - 25" or "20-25" or "p. 20-25"
                const rangeMatch = line.match(/(?:[.\s\-_–—]+|\bp(?:g|age)?\.?\s*)(\d+)\s*(?:[-–—to]+)\s*(\d+)\s*$/i);
                if (rangeMatch) {
                    pageStart = parseInt(rangeMatch[1]);
                    pageEnd = parseInt(rangeMatch[2]);
                    line = line.substring(0, rangeMatch.index).trim();
                } else {
                    // Match single trailing page number like ".... 20" or " 20"
                    const singleMatch = line.match(/(?:[.\s\-_–—]+|\bp(?:g|age)?\.?\s*)(\d+)\s*$/i);
                    if (singleMatch) {
                        pageStart = parseInt(singleMatch[1]);
                        line = line.substring(0, singleMatch.index).trim();
                    }
                }

                // Clean dots, dashes, and extra spaces from the end of the title
                let title = line.replace(/[.\-_–—\s]+$/, '').trim();

                if (title.length > 0) {
                    results.push({
                        title: title,
                        page_start: pageStart,
                        page_end: pageEnd
                    });
                }
            }

            // Automatically compute end page if sequential items exist
            for (let j = 0; j < results.length; j++) {
                if (results[j].page_start && !results[j].page_end && j + 1 < results.length && results[j + 1].page_start) {
                    if (results[j + 1].page_start > results[j].page_start) {
                        results[j].page_end = results[j + 1].page_start - 1;
                    }
                }
            }

            return results;
        },

        // ──────────────────────────────────────────
        // BULK SECTIONS MODAL HANDLERS
        // ──────────────────────────────────────────
        openBulkSectionsModal(chapter) {
            this.bulkSectionsModal.chapterId = chapter.id;
            this.bulkSectionsModal.chapterTitle = chapter.title;
            this.bulkSectionsModal.rawText = '';
            this.bulkSectionsModal.rows = [];
            this.bulkSectionsModal.saving = false;
            this.bulkSectionsModal.show = true;
        },

        parseBulkSections() {
            this.bulkSectionsModal.rows = this.parseTocText(this.bulkSectionsModal.rawText);
        },

        addEmptySectionRow() {
            this.bulkSectionsModal.rows.push({
                title: '',
                page_start: null,
                page_end: null
            });
        },

        async submitBulkSections() {
            const validRows = this.bulkSectionsModal.rows.filter(r => r.title && r.title.trim().length > 0);
            if (validRows.length === 0) {
                this.notify('Please add at least one section with a title', 'error');
                return;
            }

            this.bulkSectionsModal.saving = true;
            try {
                const res = await fetch(`/admin/textbooks/chapters/${this.bulkSectionsModal.chapterId}/sections/bulk`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ sections: validRows })
                });
                const data = await res.json();
                if (data.success && data.sections) {
                    const chapter = this.chapters.find(c => c.id === this.bulkSectionsModal.chapterId);
                    if (chapter) {
                        data.sections.forEach(s => {
                            chapter.sections.push({
                                id: s.id,
                                chapter_id: s.chapter_id,
                                title: s.title,
                                page_start: s.page_start,
                                page_end: s.page_end,
                                sort_order: s.sort_order,
                                curriculum_indicator_id: s.curriculum_indicator_id || null,
                                has_content: false,
                                isEditing: false,
                                editTitle: s.title,
                                editPageStart: s.page_start,
                                editPageEnd: s.page_end,
                            });
                        });
                        this.initSectionSortables();
                    }
                    this.bulkSectionsModal.show = false;
                    this.notify(`Added ${data.count} sub-sections successfully!`);
                } else {
                    throw new Error(data.message || 'Error creating sections');
                }
            } catch (e) {
                this.notify('Failed to save sections: ' + e.message, 'error');
            } finally {
                this.bulkSectionsModal.saving = false;
            }
        },

        // ──────────────────────────────────────────
        // BULK CHAPTERS & SUB-CHAPTERS MODAL HANDLERS
        // ──────────────────────────────────────────
        openBulkChaptersModal() {
            this.bulkChaptersModal.rawText = '';
            this.bulkChaptersModal.chapters = [];
            this.bulkChaptersModal.saving = false;
            this.bulkChaptersModal.show = true;
        },

        parseBulkChapters() {
            this.bulkChaptersModal.chapters = this.parseHierarchicalToc(this.bulkChaptersModal.rawText);
        },

        addEmptyBulkChapter() {
            this.bulkChaptersModal.chapters.push({
                title: '',
                page_start: null,
                page_end: null,
                sections: []
            });
        },

        addEmptySectionToBulkChapter(cIdx) {
            if (!this.bulkChaptersModal.chapters[cIdx].sections) {
                this.bulkChaptersModal.chapters[cIdx].sections = [];
            }
            this.bulkChaptersModal.chapters[cIdx].sections.push({
                title: '',
                page_start: null,
                page_end: null
            });
        },

        promoteSectionToChapter(cIdx, sIdx) {
            const sec = this.bulkChaptersModal.chapters[cIdx].sections.splice(sIdx, 1)[0];
            if (sec) {
                this.bulkChaptersModal.chapters.splice(cIdx + 1, 0, {
                    title: sec.title,
                    page_start: sec.page_start,
                    page_end: sec.page_end,
                    sections: []
                });
            }
        },

        parseHierarchicalToc(text) {
            if (!text || !text.trim()) return [];
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            const chapters = [];
            let currentChapter = null;

            for (let i = 0; i < lines.length; i++) {
                let line = lines[i];
                let pageStart = null;
                let pageEnd = null;

                const rangeMatch = line.match(/(?:[.\s\-_–—]+|\bp(?:g|age)?\.?\s*)(\d+)\s*(?:[-–—to]+)\s*(\d+)\s*$/i);
                if (rangeMatch) {
                    pageStart = parseInt(rangeMatch[1]);
                    pageEnd = parseInt(rangeMatch[2]);
                    line = line.substring(0, rangeMatch.index).trim();
                } else {
                    const singleMatch = line.match(/(?:[.\s\-_–—]+|\bp(?:g|age)?\.?\s*)(\d+)\s*$/i);
                    if (singleMatch) {
                        pageStart = parseInt(singleMatch[1]);
                        line = line.substring(0, singleMatch.index).trim();
                    }
                }

                let title = line.replace(/[.\-_–—\s]+$/, '').trim();
                if (!title) continue;

                // Sub-chapter criteria:
                // - Starts with dot-numbered hierarchy: "1.1", "1.2", "2.1", "1.1.1", "B7.1.1" etc.
                // - OR starts with letter/bullet in parens: "(a)", "a)", "a." etc.
                const isSubChapter = /^\s*(?:\d+\.\d+(?:\.\d+)*|[A-Za-z]\.|\([A-Za-z\d]+\))\s*/.test(title);

                if (isSubChapter && currentChapter) {
                    currentChapter.sections.push({
                        title: title,
                        page_start: pageStart,
                        page_end: pageEnd
                    });
                } else {
                    currentChapter = {
                        title: title,
                        page_start: pageStart,
                        page_end: pageEnd,
                        sections: []
                    };
                    chapters.push(currentChapter);
                }
            }

            // Auto-calculate sequential page ends between chapters if missing
            for (let c = 0; c < chapters.length; c++) {
                if (chapters[c].page_start && !chapters[c].page_end && c + 1 < chapters.length && chapters[c + 1].page_start) {
                    if (chapters[c + 1].page_start > chapters[c].page_start) {
                        chapters[c].page_end = chapters[c + 1].page_start - 1;
                    }
                }
            }

            // Auto-calculate sequential page ends for sub-sections
            chapters.forEach(ch => {
                for (let s = 0; s < ch.sections.length; s++) {
                    if (ch.sections[s].page_start && !ch.sections[s].page_end && s + 1 < ch.sections.length && ch.sections[s + 1].page_start) {
                        if (ch.sections[s + 1].page_start > ch.sections[s].page_start) {
                            ch.sections[s + 1].page_end = ch.sections[s + 1].page_start - 1;
                        }
                    }
                }
                if (ch.page_end && ch.sections.length > 0 && !ch.sections[ch.sections.length - 1].page_end) {
                    ch.sections[ch.sections.length - 1].page_end = ch.page_end;
                }
                if (!ch.page_start && ch.sections.length > 0 && ch.sections[0].page_start) {
                    ch.page_start = ch.sections[0].page_start;
                }
                if (!ch.page_end && ch.sections.length > 0 && ch.sections[ch.sections.length - 1].page_end) {
                    ch.page_end = ch.sections[ch.sections.length - 1].page_end;
                }
            });

            return chapters;
        },

        async submitBulkChapters() {
            const validChapters = this.bulkChaptersModal.chapters.filter(c => (c.title && c.title.trim().length > 0) || (c.sections && c.sections.length > 0));
            if (validChapters.length === 0) {
                this.notify('Please add at least one chapter with a title', 'error');
                return;
            }

            const payload = validChapters.map(c => ({
                title: c.title.trim() || 'Untitled Chapter',
                page_start: c.page_start ? parseInt(c.page_start) : null,
                page_end: c.page_end ? parseInt(c.page_end) : null,
                sections: (c.sections || []).filter(s => s.title && s.title.trim().length > 0).map(s => ({
                    title: s.title.trim(),
                    page_start: s.page_start ? parseInt(s.page_start) : null,
                    page_end: s.page_end ? parseInt(s.page_end) : null,
                }))
            }));

            this.bulkChaptersModal.saving = true;
            try {
                const res = await fetch(`/admin/textbooks/${this.textbookId}/chapters/bulk`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ chapters: payload })
                });
                const data = await res.json();
                if (data.success && data.chapters) {
                    data.chapters.forEach(c => {
                        this.chapters.push({
                            id: c.id,
                            title: c.title,
                            page_start: c.page_start,
                            page_end: c.page_end,
                            sort_order: c.sort_order,
                            isOpen: true,
                            isEditing: false,
                            editTitle: c.title,
                            editPageStart: c.page_start,
                            editPageEnd: c.page_end,
                            newTitle: '',
                            newPageStart: '',
                            newPageEnd: '',
                            isSavingQuick: false,
                            sections: (c.sections || []).map(s => ({
                                id: s.id,
                                chapter_id: s.chapter_id,
                                title: s.title,
                                page_start: s.page_start,
                                page_end: s.page_end,
                                sort_order: s.sort_order,
                                curriculum_indicator_id: s.curriculum_indicator_id || null,
                                has_content: false,
                                isEditing: false,
                                editTitle: s.title,
                                editPageStart: s.page_start,
                                editPageEnd: s.page_end,
                            }))
                        });
                    });
                    this.initSortable();
                    this.initSectionSortables();
                    this.bulkChaptersModal.show = false;
                    const secCount = data.sections_count || 0;
                    this.notify(`Added ${data.count} chapters and ${secCount} sub-sections successfully!`);
                } else {
                    throw new Error(data.message || 'Error creating chapters');
                }
            } catch (e) {
                this.notify('Failed to save chapters: ' + e.message, 'error');
            } finally {
                this.bulkChaptersModal.saving = false;
            }
        },

        // ──────────────────────────────────────────
        // INLINE RAPID ADD SECTION (UNDER EACH CHAPTER)
        // ──────────────────────────────────────────
        async addQuickSection(chapter) {
            const title = chapter.newTitle ? chapter.newTitle.trim() : '';
            if (!title) return;

            chapter.isSavingQuick = true;
            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapter.id}/sections`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({
                        title: title,
                        page_start: chapter.newPageStart ? parseInt(chapter.newPageStart) : null,
                        page_end: chapter.newPageEnd ? parseInt(chapter.newPageEnd) : null,
                    })
                });
                const data = await res.json();
                if (data.success && data.section) {
                    const s = data.section;
                    chapter.sections.push({
                        id: s.id,
                        chapter_id: s.chapter_id,
                        title: s.title,
                        page_start: s.page_start,
                        page_end: s.page_end,
                        sort_order: s.sort_order,
                        curriculum_indicator_id: null,
                        has_content: false,
                        isEditing: false,
                        editTitle: s.title,
                        editPageStart: s.page_start,
                        editPageEnd: s.page_end,
                    });
                    chapter.newTitle = '';
                    chapter.newPageStart = '';
                    chapter.newPageEnd = '';
                    this.notify('Section added');
                    this.initSectionSortables();
                }
            } catch (e) {
                this.notify('Failed to add section: ' + e.message, 'error');
            } finally {
                chapter.isSavingQuick = false;
            }
        },

        // ──────────────────────────────────────────
        // SINGLE CHAPTER ADD (PROMPT ALTERNATIVE)
        // ──────────────────────────────────────────
        createChapterPrompt() {
            this.openBulkChaptersModal();
        },

        // ──────────────────────────────────────────
        // INLINE EDITING (CHAPTER & SECTION)
        // ──────────────────────────────────────────
        startEditChapter(chapter) {
            chapter.editTitle = chapter.title;
            chapter.editPageStart = chapter.page_start;
            chapter.editPageEnd = chapter.page_end;
            chapter.isEditing = true;
        },

        async saveChapter(chapter) {
            if (!chapter.editTitle || !chapter.editTitle.trim()) return;
            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapter.id}`, {
                    method: 'PUT', headers: this.headers(),
                    body: JSON.stringify({
                        title: chapter.editTitle.trim(),
                        page_start: chapter.editPageStart ? parseInt(chapter.editPageStart) : null,
                        page_end: chapter.editPageEnd ? parseInt(chapter.editPageEnd) : null
                    })
                });
                const data = await res.json();
                if (data.success) {
                    chapter.title = chapter.editTitle.trim();
                    chapter.page_start = chapter.editPageStart ? parseInt(chapter.editPageStart) : null;
                    chapter.page_end = chapter.editPageEnd ? parseInt(chapter.editPageEnd) : null;
                    chapter.isEditing = false;
                    this.notify('Chapter updated');
                }
            } catch (e) {
                this.notify('Failed to save chapter: ' + e.message, 'error');
            }
        },

        startEditSection(section) {
            section.editTitle = section.title;
            section.editPageStart = section.page_start;
            section.editPageEnd = section.page_end;
            section.isEditing = true;
        },

        async saveSection(section) {
            if (!section.editTitle || !section.editTitle.trim()) return;
            try {
                const res = await fetch(`/admin/textbooks/sections/${section.id}`, {
                    method: 'PUT', headers: this.headers(),
                    body: JSON.stringify({
                        title: section.editTitle.trim(),
                        page_start: section.editPageStart ? parseInt(section.editPageStart) : null,
                        page_end: section.editPageEnd ? parseInt(section.editPageEnd) : null
                    })
                });
                const data = await res.json();
                if (data.success) {
                    section.title = section.editTitle.trim();
                    section.page_start = section.editPageStart ? parseInt(section.editPageStart) : null;
                    section.page_end = section.editPageEnd ? parseInt(section.editPageEnd) : null;
                    section.isEditing = false;
                    this.notify('Section updated');
                }
            } catch (e) {
                this.notify('Failed to save section: ' + e.message, 'error');
            }
        },

        // ──────────────────────────────────────────
        // DELETE HANDLING (SILENT WITHOUT RELOAD)
        // ──────────────────────────────────────────
        requestDelete(type, id, parentId, title) {
            this.deleteModal.type = type;
            this.deleteModal.id = id;
            this.deleteModal.parentId = parentId;
            this.deleteModal.title = title;
            this.deleteModal.deleting = false;
            this.deleteModal.show = true;
        },

        async confirmDelete() {
            this.deleteModal.deleting = true;
            try {
                if (this.deleteModal.type === 'chapter') {
                    const res = await fetch(`/admin/textbooks/chapters/${this.deleteModal.id}`, {
                        method: 'DELETE', headers: this.headers()
                    });
                    const data = await res.json();
                    if (data.success) {
                        const idx = this.chapters.findIndex(c => c.id === this.deleteModal.id);
                        if (idx !== -1) this.chapters.splice(idx, 1);
                        this.notify('Chapter deleted');
                    }
                } else if (this.deleteModal.type === 'section') {
                    const res = await fetch(`/admin/textbooks/sections/${this.deleteModal.id}`, {
                        method: 'DELETE', headers: this.headers()
                    });
                    const data = await res.json();
                    if (data.success) {
                        const chapter = this.chapters.find(c => c.id === this.deleteModal.parentId);
                        if (chapter) {
                            const sIdx = chapter.sections.findIndex(s => s.id === this.deleteModal.id);
                            if (sIdx !== -1) chapter.sections.splice(sIdx, 1);
                        }
                        this.notify('Section deleted');
                    }
                }
            } catch (e) {
                this.notify('Delete failed: ' + e.message, 'error');
            } finally {
                this.deleteModal.deleting = false;
                this.deleteModal.show = false;
            }
        },

        // ──────────────────────────────────────────
        // LINK INDICATOR
        // ──────────────────────────────────────────
        async linkIndicator(indicatorId, sectionId) {
            try {
                const res = await fetch(`/admin/textbooks/sections/${sectionId}/link-indicator`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ curriculum_indicator_id: indicatorId || null })
                });
                const data = await res.json();
                if (data.success) {
                    // Update in local state
                    for (const ch of this.chapters) {
                        const sec = ch.sections.find(s => s.id === sectionId);
                        if (sec) {
                            sec.curriculum_indicator_id = indicatorId || null;
                            break;
                        }
                    }
                    this.notify('Indicator linked');
                }
            } catch (e) {
                this.notify('Failed to link indicator: ' + e.message, 'error');
            }
        },

        // ──────────────────────────────────────────
        // LESSON STUDIO & PEDAGOGICAL SCHEME ENGINE
        // ──────────────────────────────────────────
        async openLessonStudio(section, chapter) {
            this.lessonStudio.section = section;
            this.lessonStudio.chapter = chapter;
            this.lessonStudio.title = section.title;
            this.lessonStudio.show = true;
            this.lessonStudio.loading = true;
            this.lessonStudio.activeTab = 'text';

            try {
                const res = await fetch(`/admin/textbooks/sections/${section.id}/content`, {
                    headers: this.headers()
                });
                const data = await res.json();
                if (data.success && data.section) {
                    this.lessonStudio.scheme = data.section.pedagogical_scheme || 'math_first_principles';
                    this.lessonStudio.markdown = data.section.content_markdown || '';
                    this.lessonStudio.html = data.section.content_html || '';
                    this.lessonStudio.takeaways = Array.isArray(data.section.key_takeaways) ? [...data.section.key_takeaways] : [];
                    this.lessonStudio.quiz = Array.isArray(data.section.lesson_quiz) ? JSON.parse(JSON.stringify(data.section.lesson_quiz)) : [];
                    this.lessonStudio.mediaResources = Array.isArray(data.section.media_resources) ? JSON.parse(JSON.stringify(data.section.media_resources)) : [];
                    this.lessonStudio.newVideoUrl = '';
                    this.lessonStudio.is_published = !!data.section.is_published;
                    this.lessonStudio.availableSchemes = data.available_schemes || {};
                    
                    this.$nextTick(() => {
                        this.renderStudioPreview();
                    });
                }
            } catch (e) {
                this.notify('Failed to load section content: ' + e.message, 'error');
            } finally {
                this.lessonStudio.loading = false;
            }
        },

        renderMarkdown(markdown) {
            if (!markdown) return '';
            if (!window.marked) return markdown;

            // Protect SVG blocks from CommonMark paragraph splitting on blank lines
            const svgMap = {};
            let svgCounter = 0;
            const safeMarkdown = markdown.replace(/<svg\b[^>]*>[\s\S]*?<\/svg>/gi, (match) => {
                const placeholder = `<!--SVG_PREVIEW_PLACEHOLDER_${svgCounter++}-->`;
                svgMap[placeholder] = match;
                return `\n\n${placeholder}\n\n`;
            });

            let html = window.marked.parse(safeMarkdown);
            Object.keys(svgMap).forEach(key => {
                html = html.replace(`<p>${key}</p>`, svgMap[key]).replace(key, svgMap[key]);
            });

            return html;
        },

        renderStudioPreview() {
            const previewEl = document.getElementById('studio-rendered-preview');
            if (!previewEl) return;

            if (!this.lessonStudio.markdown || !this.lessonStudio.markdown.trim()) {
                previewEl.innerHTML = '<p class="text-gray-400 italic text-xs">No content yet. Click "Generate with Scheme" to synthesize course reading text.</p>';
                return;
            }

            try {
                previewEl.innerHTML = this.renderMarkdown(this.lessonStudio.markdown);

                // Process Mermaid diagrams
                const mermaidBlocks = previewEl.querySelectorAll('code.language-mermaid, pre code.language-mermaid');
                mermaidBlocks.forEach((block, idx) => {
                    const pre = block.closest('pre') || block;
                    const container = document.createElement('div');
                    container.className = 'mermaid';
                    container.id = 'studio-mermaid-' + idx + '-' + Date.now();
                    container.textContent = block.textContent.trim();
                    pre.parentNode.replaceChild(container, pre);
                });

                if (window.mermaid && previewEl.querySelectorAll('.mermaid').length > 0) {
                    try {
                        window.mermaid.run({ nodes: previewEl.querySelectorAll('.mermaid') });
                    } catch (mErr) {
                        console.warn("Mermaid execution error:", mErr);
                    }
                }

                if (window.renderMathInElement) {
                    window.renderMathInElement(previewEl, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\[', right: '\\]', display: true},
                            {left: '\\(', right: '\\)', display: false}
                        ],
                        ignoredTags: ["script", "noscript", "style", "textarea", "pre", "code", "annotation", "annotation-xml", "svg"],
                        throwOnError: false
                    });
                }
            } catch (err) {
                console.error("Preview render error:", err);
            }
        },

        async generateLessonStudioContent() {
            if (!this.lessonStudio.section) return;
            this.lessonStudio.generating = true;

            try {
                const res = await fetch(`/admin/textbooks/sections/${this.lessonStudio.section.id}/generate-content`, {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({ scheme: this.lessonStudio.scheme })
                });

                const data = await res.json();
                if (data.success && data.result) {
                    this.lessonStudio.markdown = data.result.markdown || '';
                    this.lessonStudio.html = data.result.html || '';
                    this.lessonStudio.takeaways = Array.isArray(data.result.key_takeaways) ? [...data.result.key_takeaways] : [];
                    this.lessonStudio.quiz = Array.isArray(data.result.quiz) ? JSON.parse(JSON.stringify(data.result.quiz)) : [];
                    if (Array.isArray(data.result.media_resources) && data.result.media_resources.length > 0) {
                        this.lessonStudio.mediaResources = JSON.parse(JSON.stringify(data.result.media_resources));
                    }
                    this.lessonStudio.is_published = true;

                    // Update section item in chapters list
                    this.lessonStudio.section.has_content = true;
                    this.lessonStudio.section.is_published = true;
                    this.lessonStudio.section.pedagogical_scheme = this.lessonStudio.scheme;

                    this.$nextTick(() => {
                        this.renderStudioPreview();
                    });

                    this.notify(data.message || 'Course text generated successfully!');
                } else {
                    throw new Error(data.error || 'Generation failed');
                }
            } catch (e) {
                this.notify('Generation error: ' + e.message, 'error');
            } finally {
                this.lessonStudio.generating = false;
            }
        },

        async saveLessonStudioContent(publish = true) {
            if (!this.lessonStudio.section) return;
            this.lessonStudio.saving = true;
            this.lessonStudio.is_published = publish;

            try {
                const res = await fetch(`/admin/textbooks/sections/${this.lessonStudio.section.id}/save-content`, {
                    method: 'PUT',
                    headers: this.headers(),
                    body: JSON.stringify({
                        title: this.lessonStudio.title,
                        content_markdown: this.lessonStudio.markdown,
                        content_html: this.renderMarkdown(this.lessonStudio.markdown),
                        key_takeaways: this.lessonStudio.takeaways,
                        lesson_quiz: this.lessonStudio.quiz,
                        media_resources: this.lessonStudio.mediaResources,
                        pedagogical_scheme: this.lessonStudio.scheme,
                        is_published: publish
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.lessonStudio.section.title = this.lessonStudio.title;
                    this.lessonStudio.section.has_content = !!this.lessonStudio.markdown;
                    this.lessonStudio.section.is_published = publish;
                    this.lessonStudio.section.pedagogical_scheme = this.lessonStudio.scheme;

                    this.notify(publish ? 'Lesson saved and published!' : 'Lesson draft saved successfully!');
                    this.lessonStudio.show = false;
                } else {
                    throw new Error(data.error || 'Failed to save');
                }
            } catch (e) {
                this.notify('Save error: ' + e.message, 'error');
            } finally {
                this.lessonStudio.saving = false;
            }
        },

        async fetchAiMediaSuggestions() {
            if (!this.lessonStudio.section) return;
            this.lessonStudio.suggestingMedia = true;

            try {
                const res = await fetch(`/admin/textbooks/sections/${this.lessonStudio.section.id}/suggest-media`, {
                    method: 'POST',
                    headers: this.headers()
                });

                const data = await res.json();
                if (data.success && Array.isArray(data.suggestions)) {
                    if (!Array.isArray(this.lessonStudio.mediaResources)) {
                        this.lessonStudio.mediaResources = [];
                    }
                    // Append new suggestions without duplicates
                    data.suggestions.forEach(s => {
                        const exists = this.lessonStudio.mediaResources.some(m => m.video_id === s.video_id || m.url === s.url);
                        if (!exists) {
                            this.lessonStudio.mediaResources.push(s);
                        }
                    });
                    this.notify(`Retrieved ${data.suggestions.length} video recommendations.`);
                }
            } catch (e) {
                this.notify('Failed to suggest media: ' + e.message, 'error');
            } finally {
                this.lessonStudio.suggestingMedia = false;
            }
        },

        async addYoutubeVideoFromInput() {
            if (!this.lessonStudio.newVideoUrl || !this.lessonStudio.newVideoUrl.trim()) return;
            this.lessonStudio.resolvingUrl = true;

            try {
                const res = await fetch('/admin/textbooks/resolve-youtube', {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({ url: this.lessonStudio.newVideoUrl.trim() })
                });

                const data = await res.json();
                if (data.success && data.media) {
                    if (!Array.isArray(this.lessonStudio.mediaResources)) {
                        this.lessonStudio.mediaResources = [];
                    }
                    this.lessonStudio.mediaResources.push(data.media);
                    this.lessonStudio.newVideoUrl = '';
                    this.notify('YouTube video added to lesson.');
                } else {
                    throw new Error(data.error || 'Failed to resolve YouTube URL.');
                }
            } catch (e) {
                this.notify(e.message, 'error');
            } finally {
                this.lessonStudio.resolvingUrl = false;
            }
        },

        previewVideo(med) {
            this.lessonStudio.previewingVideo = med;
            this.lessonStudio.previewModalOpen = true;
        },

        closeVideoPreview() {
            this.lessonStudio.previewModalOpen = false;
            this.lessonStudio.previewingVideo = null;
        },

        addTakeawayItem() {
            if (!Array.isArray(this.lessonStudio.takeaways)) {
                this.lessonStudio.takeaways = [];
            }
            this.lessonStudio.takeaways.push('New concept key takeaway...');
        },

        addQuizQuestion() {
            if (!Array.isArray(this.lessonStudio.quiz)) {
                this.lessonStudio.quiz = [];
            }
            this.lessonStudio.quiz.push({
                id: Date.now(),
                question: 'Question prompt...',
                options: ['Option A', 'Option B', 'Option C', 'Option D'],
                answer: 'Option A',
                correct_index: 0,
                explanation: 'Explanation of why this answer is correct.'
            });
        },

        async batchGenerateChapter(chapter) {
            if (!confirm(`Generate reading texts for all sub-sections in "${chapter.title}" using First-Principles AI Scheme?`)) {
                return;
            }

            chapter.isBatchGenerating = true;

            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapter.id}/generate-all`, {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({})
                });

                const data = await res.json();
                if (data.success) {
                    if (data.chapter && data.chapter.sections) {
                        chapter.sections.forEach(s => {
                            s.has_content = true;
                            s.is_published = true;
                        });
                    }
                    this.notify(data.message || 'Chapter generated successfully!');
                } else {
                    throw new Error(data.error || 'Batch generation failed');
                }
            } catch (e) {
                this.notify('Batch generation error: ' + e.message, 'error');
            } finally {
                chapter.isBatchGenerating = false;
            }
        }
    };
}
</script>
@endsection
