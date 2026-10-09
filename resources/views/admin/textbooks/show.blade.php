@extends('layouts.admin')

@section('title', 'Textbook Lessons - ' . $textbook->title)

@section('content')
<!-- SortableJS library for smooth drag and drop reordering -->
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

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
                    <div x-show="showRaw" x-cloak class="mt-2.5 p-3 rounded-xl bg-slate-900 text-slate-200 text-xs font-mono overflow-x-auto max-h-48 leading-normal select-all">
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

                                    <!-- Indicator Mapping & Delete -->
                                    <div class="flex items-center gap-2 flex-shrink-0">
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
    <div x-show="bulkSectionsModal.show" x-cloak
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
    <div x-show="bulkChaptersModal.show" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="bulkChaptersModal.show = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full border border-gray-200 dark:border-gray-700 shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
             @click.away="bulkChaptersModal.show = false">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-750/30">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-base">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Bulk Paste Chapters</h3>
                        <p class="text-xs text-gray-500">Paste top-level chapters (e.g. 1. Numbers..., 2. Sets...)</p>
                    </div>
                </div>
                <button type="button" @click="bulkChaptersModal.show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Paste Chapter Lines from PDF
                    </label>
                    <textarea x-model="bulkChaptersModal.rawText"
                              @input="parseBulkChapters()"
                              rows="5"
                              placeholder="1. NUMBERS AND NUMERALS ........................... 1&#10;2. SETS ........................................... 35&#10;3. FRACTIONS ...................................... 68&#10;4. INTEGERS ....................................... 90"
                              class="w-full text-xs font-mono rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            Parsed Chapters (<span x-text="bulkChaptersModal.rows.length"></span> items)
                        </h4>
                        <button type="button" @click="addEmptyChapterRow()" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                            + Add Empty Row
                        </button>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-56 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-750 text-gray-600 dark:text-gray-300 font-bold sticky top-0">
                                <tr>
                                    <th class="p-2.5 w-8">#</th>
                                    <th class="p-2.5">Chapter Title</th>
                                    <th class="p-2.5 w-20">Start Pg</th>
                                    <th class="p-2.5 w-20">End Pg</th>
                                    <th class="p-2.5 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                <template x-for="(row, rIdx) in bulkChaptersModal.rows" :key="rIdx">
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/50">
                                        <td class="p-2 text-gray-400 font-mono text-[11px]" x-text="rIdx + 1"></td>
                                        <td class="p-1.5">
                                            <input type="text" x-model="row.title" class="w-full text-xs rounded border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-1 px-2 focus:ring-1 focus:ring-indigo-500">
                                        </td>
                                        <td class="p-1.5">
                                            <input type="number" x-model="row.page_start" class="w-full text-xs rounded border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-1 px-2 text-center">
                                        </td>
                                        <td class="p-1.5">
                                            <input type="number" x-model="row.page_end" class="w-full text-xs rounded border-gray-200 dark:border-gray-600 dark:bg-gray-700 py-1 px-2 text-center">
                                        </td>
                                        <td class="p-1.5 text-center">
                                            <button type="button" @click="bulkChaptersModal.rows.splice(rIdx, 1)" class="text-red-400 hover:text-red-600 p-1">
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

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750/30 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Will create <strong x-text="bulkChaptersModal.rows.length"></strong> chapters.
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="bulkChaptersModal.show = false" class="px-3.5 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitBulkChapters()"
                            :disabled="bulkChaptersModal.saving || bulkChaptersModal.rows.length === 0"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs disabled:opacity-50 flex items-center gap-1.5">
                        <span x-show="!bulkChaptersModal.saving"><i class="fas fa-check mr-1"></i> Save All Chapters</span>
                        <span x-show="bulkChaptersModal.saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: Custom Delete Confirmation          -->
    <!-- ========================================== -->
    <div x-show="deleteModal.show" x-cloak
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
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
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

        // Bulk Chapters Modal State
        bulkChaptersModal: {
            show: false,
            rawText: '',
            rows: [],
            saving: false
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
        // BULK CHAPTERS MODAL HANDLERS
        // ──────────────────────────────────────────
        openBulkChaptersModal() {
            this.bulkChaptersModal.rawText = '';
            this.bulkChaptersModal.rows = [];
            this.bulkChaptersModal.saving = false;
            this.bulkChaptersModal.show = true;
        },

        parseBulkChapters() {
            this.bulkChaptersModal.rows = this.parseTocText(this.bulkChaptersModal.rawText);
        },

        addEmptyChapterRow() {
            this.bulkChaptersModal.rows.push({
                title: '',
                page_start: null,
                page_end: null
            });
        },

        async submitBulkChapters() {
            const validRows = this.bulkChaptersModal.rows.filter(r => r.title && r.title.trim().length > 0);
            if (validRows.length === 0) {
                this.notify('Please add at least one chapter with a title', 'error');
                return;
            }

            this.bulkChaptersModal.saving = true;
            try {
                const res = await fetch(`/admin/textbooks/${this.textbookId}/chapters/bulk`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ chapters: validRows })
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
                            sections: []
                        });
                    });
                    this.initSortable();
                    this.bulkChaptersModal.show = false;
                    this.notify(`Added ${data.count} chapters successfully!`);
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
        }
    };
}
</script>
@endsection
