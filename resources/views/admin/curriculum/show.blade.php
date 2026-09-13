@extends('layouts.admin')

@section('title', 'Review Curriculum - ' . $curriculum->title)

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="curriculumReviewApp()">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.curriculum.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-arrow-left text-[10px]"></i> Back to Curricula
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-xs font-medium text-gray-500">{{ $curriculum->education_body }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                {{ $curriculum->title }}
            </h1>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="px-2.5 py-1 rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-semibold border border-blue-100 dark:border-blue-800">
                    <i class="fas fa-book mr-1 text-blue-600"></i> {{ $curriculum->subject->name }}
                </span>
                <span>•</span>
                <!-- Extraction Status Badge in Header -->
                <template x-if="status === 'processing'">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 animate-pulse">
                        <i class="fas fa-spinner fa-spin mr-1.5 text-blue-600"></i> Extracting Content...
                    </span>
                </template>
                <template x-if="status === 'pending'">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200">
                        <i class="fas fa-clock mr-1.5"></i> In Queue
                    </span>
                </template>
                <template x-if="status === 'failed'">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200">
                        <i class="fas fa-exclamation-triangle mr-1.5"></i> Extraction Failed
                    </span>
                </template>
                <template x-if="status === 'extracted'">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200">
                        <i class="fas fa-check-circle mr-1.5"></i> Extracted
                    </span>
                </template>
                <span>•</span>
                <span>Uploaded by {{ $curriculum->uploader->name ?? 'Admin' }} ({{ $curriculum->created_at->format('M d, Y') }})</span>
                @if($curriculum->file_size_bytes)
                    <span>•</span>
                    <span class="text-red-600 font-medium"><i class="fas fa-file-pdf mr-1"></i> PDF: {{ round($curriculum->file_size_bytes / 1048576, 2) }} MB</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(!$curriculum->is_approved)
                <form action="{{ route('admin.curriculum.approve', $curriculum) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-all">
                        <i class="fas fa-check-double mr-2"></i> Approve Curriculum
                    </button>
                </form>
            @else
                <div class="inline-flex items-center px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold rounded-xl">
                    <i class="fas fa-shield-check mr-1.5 text-emerald-600"></i> Approved & Active
                </div>
            @endif

            <form action="{{ route('admin.curriculum.re-extract', $curriculum) }}" method="POST" onsubmit="return confirm('Re-running AI extraction will overwrite any existing strands. Continue?');">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium rounded-xl transition-all">
                    <i class="fas fa-sync-alt mr-1.5 text-gray-500"></i> Re-extract
                </button>
            </form>
        </div>
    </div>

    <!-- Processing Alert Banner (Shown during active background extraction) -->
    <div x-show="status === 'processing' || status === 'pending'" x-cloak class="p-5 rounded-2xl bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100 shadow-sm">
        <div class="flex items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fas fa-spinner fa-spin text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-blue-900 dark:text-white flex items-center gap-2">
                        AI Curriculum Content Extraction in Progress
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                    </h4>
                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5 font-medium" x-text="progressNotes || 'Analyzing curriculum structure and extracting all strands across grades...'"></p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 flex items-center gap-1.5">
                    <i class="fas fa-satellite-dish text-[10px] animate-pulse"></i> Live Sync
                </span>
            </div>
        </div>
    </div>

    <!-- Error Alert Banner -->
    <div x-show="status === 'failed'" x-cloak class="p-5 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm text-red-900">Extraction Encountered an Issue</h4>
                <p class="text-xs text-red-700 mt-1 font-mono bg-white/90 p-3 rounded-xl border border-red-100 break-words" x-text="errorMessage || '{{ addslashes($curriculum->extraction_error ?? 'Unknown error during extraction.') }}'"></p>
                <div class="flex items-center gap-3 mt-3">
                    <form action="{{ route('admin.curriculum.re-extract', $curriculum) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all">
                            <i class="fas fa-redo-alt mr-1.5"></i> Retry Extraction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Review Area: Split View -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Tree Navigation -->
        <div class="lg:col-span-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-4 max-h-[800px] overflow-y-auto shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                <h3 class="font-bold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-sitemap text-blue-600"></i> Curriculum Strands
                </h3>
                <span class="text-xs font-semibold px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                    {{ $curriculum->strands->count() }} Strands
                </span>
            </div>

            <div class="space-y-4">
                @php
                    $groupedStrands = $curriculum->strands->groupBy(function($s) {
                        return $s->grade_label ?: 'All Grades';
                    });
                @endphp

                @forelse($groupedStrands as $gradeLabel => $strands)
                    <div class="space-y-2">
                        @if($groupedStrands->count() > 1)
                            <div class="px-2 py-1 bg-gray-100 dark:bg-gray-700/60 rounded-lg flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-200">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-graduation-cap text-blue-600"></i> {{ $gradeLabel }}
                                </span>
                                <span class="text-[10px] text-gray-500">{{ $strands->count() }} Strands</span>
                            </div>
                        @endif

                        <div class="space-y-2.5">
                            @foreach($strands as $strand)
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden" x-data="{ open: true }">
                                <div class="w-full flex items-center justify-between p-2.5 transition-colors"
                                    :class="selectedStrandId === {{ $strand->id }} && !selectedIndicatorId && !selectedSubStrandId ? 'bg-blue-600 text-white' : 'bg-gray-50 dark:bg-gray-750 hover:bg-blue-50/50 text-gray-900 dark:text-white'">
                                    <button type="button" @click="selectStrand({{ $strand->id }})"
                                        class="flex-1 text-left flex items-center gap-2 text-xs font-bold truncate">
                                        <i class="fas fa-layer-group text-xs" :class="selectedStrandId === {{ $strand->id }} && !selectedIndicatorId && !selectedSubStrandId ? 'text-white' : 'text-blue-600'"></i>
                                        <span class="truncate">{{ $strand->title }}</span>
                                    </button>
                                    <div class="flex items-center gap-2 text-xs flex-shrink-0 ml-2">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                                              :class="selectedStrandId === {{ $strand->id }} && !selectedIndicatorId && !selectedSubStrandId ? 'bg-blue-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                                            {{ $strand->subStrands->count() }} sub
                                        </span>
                                        <button type="button" @click.stop="open = !open" class="p-1 hover:opacity-75">
                                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{'rotate-180': open}"></i>
                                        </button>
                                    </div>
                                </div>

                                <div x-show="open" class="p-2 space-y-2 bg-white dark:bg-gray-800">
                                    @foreach($strand->subStrands as $subStrand)
                                    <div class="pl-2 border-l-2 border-blue-200 dark:border-gray-700 space-y-1">
                                        <button type="button" @click="selectSubStrand({{ $subStrand->id }})"
                                            class="w-full text-left py-1.5 px-2 text-xs rounded-lg flex items-center justify-between transition-colors"
                                            :class="selectedSubStrandId === {{ $subStrand->id }} && !selectedIndicatorId ? 'bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-100 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                            <span class="truncate flex-1">{{ $subStrand->title }}</span>
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded ml-2"
                                                  :class="selectedSubStrandId === {{ $subStrand->id }} && !selectedIndicatorId ? 'bg-blue-200 text-blue-900' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                                                {{ $subStrand->indicators->count() }} ind.
                                            </span>
                                        </button>

                                        <!-- Indicator Items -->
                                        @if($subStrand->indicators->count() > 0)
                                        <div class="pl-2 space-y-1">
                                            @foreach($subStrand->indicators as $indicator)
                                            <button type="button" @click="selectIndicator({{ $indicator->id }})"
                                                class="w-full text-left py-1 px-2 text-[11px] rounded-lg flex items-center gap-1.5 transition-all"
                                                :class="selectedIndicatorId === {{ $indicator->id }} ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                                <span class="font-mono text-[9px] px-1 py-0.5 rounded font-bold"
                                                      :class="selectedIndicatorId === {{ $indicator->id }} ? 'bg-blue-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'">
                                                    {{ $indicator->indicator_code ?? 'IND' }}
                                                </span>
                                                <span class="truncate">{{ $indicator->title }}</span>
                                            </button>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                <!-- Empty State (adapts if processing vs idle) -->
                <div class="text-center py-10 px-4">
                    <template x-if="status === 'processing' || status === 'pending'">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mx-auto mb-3 text-xl animate-spin">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200">Extracting strands...</p>
                            <p class="text-[11px] text-gray-400 mt-1">Strands and sub-strands will appear here as soon as processing completes.</p>
                        </div>
                    </template>
                    <template x-if="status !== 'processing' && status !== 'pending'">
                        <div>
                            <p class="text-xs text-gray-400 italic">No strands extracted yet.</p>
                        </div>
                    </template>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Inspector & Inline Editor -->
        <div class="lg:col-span-8 bg-white dark:bg-gray-800 p-6 sm:p-7 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm min-h-[500px]">
            <!-- Processing State in Right Panel if no indicators yet -->
            <div x-show="(status === 'processing' || status === 'pending') && Object.keys(indicatorsMap).length === 0" class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-brain fa-pulse"></i>
                </div>
                <h4 class="font-bold text-gray-900 dark:text-white text-base">Analyzing Curriculum Document</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mt-1.5 leading-relaxed">
                    The AI engine is reading your PDF to classify Strands, Sub-strands, and Learning Indicators. You don't need to refresh — this panel will update automatically.
                </p>
                <div class="w-48 bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full mt-5 overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full animate-indeterminate"></div>
                </div>
            </div>

            <!-- No Selection State (when strands exist or extraction complete) -->
            <div x-show="!activeIndicator && !activeStrand && !activeSubStrand && (status !== 'processing' && status !== 'pending' || Object.keys(indicatorsMap).length > 0)" class="flex flex-col items-center justify-center py-24 text-gray-400 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">Select an item from the left hierarchy</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">Click any Strand, Sub-strand, or Indicator to inspect, verify, and update the extracted content.</p>
            </div>

            <!-- STRAND INSPECTOR -->
            <div x-show="activeStrand && !activeIndicator && !activeSubStrand" class="space-y-6" style="display: none;">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-bold rounded-lg border border-blue-100 flex items-center gap-1.5">
                            <i class="fas fa-layer-group"></i> Strand Details
                        </span>
                        <span class="px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-semibold rounded-md" x-text="activeStrand?.grade_label || 'All Grades'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="reExtractStrand(activeStrand.id)" :disabled="isExtractingStrand"
                            class="h-9 px-3.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fas" :class="isExtractingStrand ? 'fa-spinner fa-spin' : 'fa-magic'"></i>
                            <span x-text="isExtractingStrand ? 'Extracting Indicators...' : 'Re-extract Indicators'"></span>
                        </button>
                        <button type="button" @click="saveStrand()" class="h-9 px-3.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-save"></i> Save Strand
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Strand Title</label>
                        <input type="text" x-model="activeStrand.title" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Grade / Level Label</label>
                            <input type="text" x-model="activeStrand.grade_label" placeholder="e.g. Basic 7 (JHS 1)" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Mapped Level</label>
                            <select x-model="activeStrand.level_id" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                                <option value="">Default (from curriculum)</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Description / Overview</label>
                        <textarea rows="3" x-model="activeStrand.description" placeholder="Brief summary of what this strand covers..."
                            class="w-full p-3.5 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
                    </div>
                </div>

                <!-- Sub-strands List in this Strand -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-folder-tree text-blue-600"></i> Sub-strands under this Strand (<span x-text="activeStrand?.sub_strands?.length || 0"></span>)
                        </h4>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="sub in activeStrand?.sub_strands || []" :key="sub.id">
                            <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-xl border border-gray-200/60 dark:border-gray-700 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="sub.title"></h5>
                                    <p class="text-[11px] text-gray-500 truncate mt-0.5" x-text="sub.content_standard || 'No content standard defined'"></p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                        <span x-text="sub.indicators?.length || 0"></span> indicators
                                    </span>
                                    <button type="button" @click="selectSubStrand(sub.id)" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors">
                                        Inspect
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- SUB-STRAND INSPECTOR -->
            <div x-show="activeSubStrand && !activeIndicator" class="space-y-6" style="display: none;">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="selectStrand(activeSubStrand.strand_id)" class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1">
                            <i class="fas fa-arrow-left"></i> Strand
                        </button>
                        <span class="text-gray-300">/</span>
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-bold rounded-lg border border-blue-100">
                            Sub-strand Details
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showAddIndicatorModal = true" class="h-9 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-plus"></i> Add Indicator
                        </button>
                        <button type="button" @click="saveSubStrand()" class="h-9 px-3.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-save"></i> Save Sub-strand
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Sub-strand Title</label>
                        <input type="text" x-model="activeSubStrand.title" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Content Standard Code & Title</label>
                        <input type="text" x-model="activeSubStrand.content_standard" placeholder="e.g. B7.1.1.1 Demonstrate understanding of place value of large numbers" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Description</label>
                        <textarea rows="3" x-model="activeSubStrand.description" placeholder="Optional description..."
                            class="w-full p-3.5 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
                    </div>
                </div>

                <!-- Indicators Under this Sub-strand -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-5 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-list-check text-blue-600"></i> Learning Indicators (<span x-text="activeSubStrand?.indicators?.length || 0"></span>)
                        </h4>
                        <!-- View Switcher in Sub-strand -->
                        <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-700 p-1 border border-gray-200 dark:border-gray-600">
                            <button type="button" @click="viewMode = 'table'"
                                class="px-3 py-1 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5"
                                :class="viewMode === 'table' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                                <i class="fas fa-table text-[11px]"></i>
                                <span>PDF Document View</span>
                            </button>
                            <button type="button" @click="viewMode = 'form'"
                                class="px-3 py-1 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5"
                                :class="viewMode === 'form' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                                <i class="fas fa-list text-[11px]"></i>
                                <span>List / Edit View</span>
                            </button>
                        </div>
                    </div>

                    <template x-if="!activeSubStrand?.indicators || activeSubStrand.indicators.length === 0">
                        <div class="p-8 text-center bg-gray-50 dark:bg-gray-750 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            <i class="fas fa-clipboard-list text-gray-300 dark:text-gray-600 text-3xl mb-2"></i>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No indicators in this sub-strand yet</p>
                            <p class="text-[11px] text-gray-400 mt-1 max-w-sm mx-auto">You can click "Re-extract Indicators" on the Strand page, or click "Add Indicator" above to manually define one.</p>
                            <button type="button" @click="showAddIndicatorModal = true" class="mt-3 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Add Indicator Now
                            </button>
                        </div>
                    </template>

                    <!-- PDF Document Page Mock for the entire Sub-strand -->
                    <div x-show="viewMode === 'table' && activeSubStrand?.indicators?.length > 0" class="space-y-4">
                        <!-- Header banner -->
                        <div class="bg-gray-800 text-white rounded-t-xl px-5 py-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
                            <div>
                                <div class="text-[11px] font-semibold text-gray-300 uppercase tracking-wider flex items-center gap-2">
                                    <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-[10px] font-bold" x-text="activeSubStrand?.grade_label || 'CURRICULUM'"></span>
                                    <span x-text="activeSubStrand?.strand_title || 'Strand'"></span>
                                </div>
                                <h4 class="text-sm font-bold text-white mt-0.5" x-text="activeSubStrand?.title"></h4>
                            </div>
                            <span class="text-[11px] text-gray-300 bg-gray-700/80 px-2.5 py-1 rounded-md border border-gray-600 flex items-center gap-1">
                                <i class="fas fa-file-pdf text-red-400"></i> NaCCA / GES Document Page
                            </span>
                        </div>

                        <!-- 3-Column Table -->
                        <div class="border-2 border-gray-700 dark:border-gray-600 rounded-b-xl overflow-hidden bg-white dark:bg-gray-900 shadow-sm -mt-4">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr class="bg-gray-600 text-white text-[11px] font-bold tracking-wider uppercase">
                                        <th class="p-3.5 w-1/4 border-r border-gray-500">CONTENT STANDARD</th>
                                        <th class="p-3.5 w-1/2 border-r border-gray-500">INDICATORS AND EXEMPLARS</th>
                                        <th class="p-3.5 w-1/4">CORE COMPETENCIES</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                                    <template x-for="(ind, index) in activeSubStrand?.indicators || []" :key="ind.id">
                                        <tr class="align-top">
                                            <!-- Standard cell (span first row or show standard) -->
                                            <td class="p-4 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-xs font-semibold leading-relaxed">
                                                <div class="sticky top-4">
                                                    <div class="text-blue-700 dark:text-blue-400 font-bold font-mono text-sm mb-1.5"
                                                        x-text="(activeSubStrand.content_standard ? (activeSubStrand.content_standard.match(/^[A-Z0-9\.]+/i) || [''])[0] : (ind.indicator_code ? ind.indicator_code.replace(/\.\d+$/, '') : 'STANDARD'))"></div>
                                                    <p class="text-gray-800 dark:text-gray-200 font-medium"
                                                        x-text="activeSubStrand.content_standard ? (activeSubStrand.content_standard.replace(/^[A-Z0-9\.]+\s*/i, '') || activeSubStrand.content_standard) : activeSubStrand.title"></p>
                                                </div>
                                            </td>

                                            <!-- Indicators & Exemplars cell -->
                                            <td class="p-4 border-r border-gray-300 dark:border-gray-700 space-y-3.5">
                                                <div>
                                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-mono text-xs font-bold px-2 py-0.5 bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-200 rounded" x-text="ind.indicator_code || 'IND'"></span>
                                                            <h5 class="text-xs font-bold text-gray-900 dark:text-white" x-text="ind.title"></h5>
                                                        </div>
                                                        <button type="button" @click="selectIndicator(ind.id, 'form')" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                                                            <i class="fas fa-edit text-[10px]"></i> Edit
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium" x-text="ind.description"></p>
                                                </div>

                                                <!-- Exemplars -->
                                                <template x-if="ind.exemplars">
                                                    <div class="text-xs text-gray-800 dark:text-gray-200 font-sans leading-relaxed space-y-1 bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/80"
                                                        x-html="formatExemplars(ind.exemplars)"></div>
                                                </template>

                                                <!-- Diagrams preview in table row -->
                                                <template x-if="ind.media && ind.media.length > 0">
                                                    <div class="flex items-center gap-2 overflow-x-auto py-1">
                                                        <template x-for="item in ind.media" :key="item.id">
                                                            <div class="w-24 h-24 flex-shrink-0 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 p-1">
                                                                <img :src="'/storage/' + item.file_path" class="w-full h-full object-contain">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </td>

                                            <!-- Core Competencies cell -->
                                            <td class="p-4 bg-gray-50/50 dark:bg-gray-800/50 space-y-2">
                                                <template x-for="comp in getCompetencies(ind.exemplars + ' ' + ind.description)" :key="comp.code">
                                                    <div class="p-2 rounded-lg border text-[11px] leading-snug font-medium" :class="comp.color">
                                                        <span class="font-bold font-mono px-1 py-0.2 rounded text-[9px] bg-white/80 shadow-sm mr-1" x-text="comp.code"></span>
                                                        <span class="font-bold" x-text="comp.label"></span>
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- List View for Sub-strand -->
                    <div x-show="viewMode === 'form'" class="space-y-2">
                        <template x-for="ind in activeSubStrand?.indicators || []" :key="ind.id">
                            <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-xl border border-gray-200/60 dark:border-gray-700 flex items-center justify-between gap-3 hover:border-blue-400 transition-colors">
                                <div class="min-w-0 flex-1 flex items-center gap-2.5">
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded font-bold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200" x-text="ind.indicator_code || 'IND'"></span>
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white truncate" x-text="ind.title"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectIndicator(ind.id, 'table')" class="px-2.5 py-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-table text-[10px] text-blue-600"></i> Table
                                    </button>
                                    <button type="button" @click="selectIndicator(ind.id, 'form')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-edit text-[10px]"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Manual Add Indicator Modal -->
            <div x-show="showAddIndicatorModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-plus-circle text-blue-600"></i> Add Learning Indicator
                        </h4>
                        <button type="button" @click="showAddIndicatorModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-3 text-left">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Indicator Code</label>
                            <input type="text" x-model="newIndicator.indicator_code" placeholder="e.g. B7.1.1.1.1" class="w-full h-10 px-3 rounded-lg text-xs font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Indicator Title</label>
                            <input type="text" x-model="newIndicator.title" placeholder="Short description of the indicator" class="w-full h-10 px-3 rounded-lg text-xs font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Full Description</label>
                            <textarea rows="3" x-model="newIndicator.description" placeholder="Pedagogical outcome..." class="w-full p-3 rounded-lg text-xs font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Exemplars (Optional)</label>
                            <textarea rows="3" x-model="newIndicator.exemplars" placeholder="Teacher examples, classroom tasks..." class="w-full p-3 rounded-lg text-xs font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showAddIndicatorModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">
                            Cancel
                        </button>
                        <button type="button" @click="createNewIndicator()" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">
                            Create Indicator
                        </button>
                    </div>
                </div>
            </div>

            <!-- Indicator Detail (PDF Table View & Form Editor) -->
            <div x-show="activeIndicator" class="space-y-5" style="display: none;">
                <!-- Header with Navigation and View Mode Toggle -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" @click="activeIndicator?.sub_strand_id ? selectSubStrand(activeIndicator.sub_strand_id) : activeIndicator = null"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mr-1 transition-colors">
                            <i class="fas fa-arrow-left"></i> Sub-strand
                        </button>
                        <span class="text-gray-300">/</span>
                        <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-mono font-bold rounded-lg border border-blue-100" x-text="activeIndicator?.indicator_code || 'No Code'"></span>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white truncate max-w-md" x-text="activeIndicator?.title"></h3>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Dual Mode Toggle Buttons -->
                        <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-700 p-1 border border-gray-200 dark:border-gray-600">
                            <button type="button" @click="viewMode = 'table'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5"
                                :class="viewMode === 'table' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                                <i class="fas fa-table text-[11px]"></i>
                                <span>PDF Table View</span>
                            </button>
                            <button type="button" @click="viewMode = 'form'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5"
                                :class="viewMode === 'form' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                                <i class="fas fa-edit text-[11px]"></i>
                                <span>Edit Form</span>
                            </button>
                        </div>

                        <button type="button" @click="deleteIndicator(activeIndicator.id)" class="h-9 px-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 border border-red-200 dark:border-red-800 text-xs font-semibold rounded-xl transition-all flex items-center gap-1.5">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button type="button" @click="saveIndicator()" class="h-9 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-save"></i>
                            <span>Save</span>
                        </button>
                    </div>
                </div>

                <!-- 1. PDF TABLE MOCK VIEW (Official GES / NaCCA Curriculum Document Format) -->
                <div x-show="viewMode === 'table'" class="space-y-4">
                    <!-- Curriculum Page Header Box (mimicking syllabus header strip) -->
                    <div class="bg-gray-800 text-white rounded-t-xl px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 shadow-sm">
                        <div>
                            <div class="text-[11px] font-semibold text-gray-300 uppercase tracking-wider flex items-center gap-2">
                                <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-[10px] font-bold" x-text="activeIndicator?.grade_label || 'CURRICULUM'"></span>
                                <span x-text="activeIndicator?.strand_title || 'Strand'"></span>
                            </div>
                            <h4 class="text-sm font-bold text-white mt-0.5" x-text="activeIndicator?.sub_strand_title || 'Sub-strand'"></h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-gray-300 bg-gray-700/80 px-2.5 py-1 rounded-md border border-gray-600 flex items-center gap-1">
                                <i class="fas fa-file-pdf text-red-400"></i> NaCCA / GES Format
                            </span>
                            <button type="button" @click="viewMode = 'form'" class="text-xs bg-white text-gray-900 hover:bg-gray-100 font-bold px-3 py-1 rounded-md shadow transition-colors flex items-center gap-1">
                                <i class="fas fa-pencil-alt text-[10px] text-blue-600"></i> Edit Content
                            </button>
                        </div>
                    </div>

                    <!-- The Official 3-Column Syllabus Table -->
                    <div class="border-2 border-gray-700 dark:border-gray-600 rounded-b-xl overflow-hidden bg-white dark:bg-gray-900 shadow-sm -mt-4">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="bg-gray-600 text-white text-[11px] font-bold tracking-wider uppercase">
                                    <th class="p-3.5 w-1/4 border-r border-gray-500">CONTENT STANDARD</th>
                                    <th class="p-3.5 w-1/2 border-r border-gray-500">INDICATORS AND EXEMPLARS</th>
                                    <th class="p-3.5 w-1/4">CORE COMPETENCIES</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-300 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                                <tr class="align-top">
                                    <!-- Column 1: CONTENT STANDARD -->
                                    <td class="p-4 border-r border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-xs font-semibold leading-relaxed">
                                        <template x-if="activeIndicator?.content_standard">
                                            <div>
                                                <div class="text-blue-700 dark:text-blue-400 font-bold font-mono text-sm mb-1.5" x-text="(activeIndicator.content_standard.match(/^[A-Z0-9\.]+/i) || [''])[0]"></div>
                                                <p class="text-gray-800 dark:text-gray-200 font-medium" x-text="activeIndicator.content_standard.replace(/^[A-Z0-9\.]+\s*/i, '') || activeIndicator.content_standard"></p>
                                            </div>
                                        </template>
                                        <template x-if="!activeIndicator?.content_standard">
                                            <div class="text-gray-400 italic font-normal text-[11px]">
                                                <span>Standard inherited from:</span>
                                                <div class="font-semibold text-gray-700 dark:text-gray-300 mt-1" x-text="activeIndicator?.sub_strand_title"></div>
                                            </div>
                                        </template>
                                    </td>

                                    <!-- Column 2: INDICATORS AND EXEMPLARS -->
                                    <td class="p-4 border-r border-gray-300 dark:border-gray-700 space-y-4">
                                        <!-- Indicator Header in Cell -->
                                        <div class="border-b border-gray-200 dark:border-gray-700 pb-2.5">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-mono text-xs font-bold px-2 py-0.5 bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-200 rounded" x-text="activeIndicator?.indicator_code || 'IND'"></span>
                                                <h5 class="text-xs font-bold text-gray-900 dark:text-white" x-text="activeIndicator?.title"></h5>
                                            </div>
                                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium" x-text="activeIndicator?.description"></p>
                                        </div>

                                        <!-- Exemplars List -->
                                        <div>
                                            <h6 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2 flex items-center justify-between">
                                                <span>Teacher Exemplars</span>
                                                <button type="button" @click="viewMode = 'form'" class="text-[10px] text-blue-600 hover:underline">Edit</button>
                                            </h6>
                                            <template x-if="activeIndicator?.exemplars">
                                                <div class="text-xs text-gray-800 dark:text-gray-200 font-sans leading-relaxed space-y-2 bg-gray-50 dark:bg-gray-800/60 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80"
                                                    x-html="formatExemplars(activeIndicator?.exemplars)"></div>
                                            </template>
                                            <template x-if="!activeIndicator?.exemplars">
                                                <div class="text-xs text-gray-400 italic bg-gray-50 dark:bg-gray-800/40 p-3 rounded-lg border border-dashed border-gray-200">
                                                    No exemplars recorded. Click "Edit Form" to add classroom activities and examples.
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Attached Visuals / Diagrams Gallery in Table -->
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <h6 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                                    <i class="fas fa-image text-blue-600"></i> Attached Diagrams & Mathematical Visuals
                                                </h6>
                                                <label class="cursor-pointer text-[11px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                                    <i class="fas fa-plus"></i> Upload Visual
                                                    <input type="file" accept="image/*" class="hidden" @change="uploadIndicatorMedia($event)">
                                                </label>
                                            </div>

                                            <template x-if="activeIndicator?.media?.length > 0">
                                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                                    <template x-for="item in activeIndicator.media" :key="item.id">
                                                        <div class="group relative rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800 p-2 shadow-sm">
                                                            <img :src="'/storage/' + item.file_path" :alt="item.caption || 'Curriculum Diagram'" class="w-full h-32 object-contain rounded-lg bg-gray-50 dark:bg-gray-900">
                                                            <div class="mt-1.5 px-1 flex items-center justify-between text-[10px] text-gray-600 dark:text-gray-400">
                                                                <span class="truncate font-medium" x-text="item.caption || ('Page ' + (item.page_number || 'N/A'))"></span>
                                                                <button type="button" @click="deleteMedia(item.id)" class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!activeIndicator?.media || activeIndicator?.media?.length === 0">
                                                <p class="text-[11px] text-gray-400 italic">No diagrams attached to this indicator. Upload visuals to display geometric figures, tables, or charts.</p>
                                            </template>
                                        </div>
                                    </td>

                                    <!-- Column 3: CORE COMPETENCIES -->
                                    <td class="p-4 bg-gray-50/50 dark:bg-gray-800/50 space-y-3">
                                        <div class="space-y-2">
                                            <template x-for="comp in getCompetencies(activeIndicator?.exemplars + ' ' + activeIndicator?.description)" :key="comp.code">
                                                <div class="p-2.5 rounded-xl border text-xs leading-snug font-medium transition-all" :class="comp.color">
                                                    <div class="flex items-center gap-1.5 mb-0.5">
                                                        <span class="font-bold font-mono px-1.5 py-0.2 rounded text-[10px] bg-white/70 shadow-sm" x-text="comp.code"></span>
                                                        <span class="font-bold" x-text="comp.label"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 text-[10px] text-gray-400">
                                            <i class="fas fa-info-circle text-blue-500 mr-1"></i> Mapped automatically from Ghana Education Service Common Core Standards.
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. FORM EDIT MODE (For editing fields, text, descriptions, & textbooks) -->
                <div x-show="viewMode === 'form'" class="space-y-5">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center justify-between text-xs text-blue-900 dark:text-blue-200">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-pen-to-square text-blue-600"></i> Editing indicator fields. Click "PDF Table View" above at any time to return to document view.
                        </span>
                        <button type="button" @click="viewMode = 'table'" class="font-bold underline text-blue-700 dark:text-blue-300">
                            Switch to Table View
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Indicator Code</label>
                            <input type="text" x-model="activeIndicator.indicator_code" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Short Title</label>
                            <input type="text" x-model="activeIndicator.title" class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Detailed Description / Learning Outcome</label>
                        <textarea rows="4" x-model="activeIndicator.description" class="w-full p-3.5 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Exemplars & Teacher Guidelines</label>
                        <textarea rows="6" x-model="activeIndicator.exemplars" placeholder="Specific classroom activities, examples, sample questions..."
                            class="w-full p-3.5 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
                    </div>

                    <!-- Extracted Diagrams & Visual Media in Form Mode -->
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <div class="flex items-center justify-between mb-2.5">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-image text-blue-600"></i> Extracted Diagrams & Visuals
                            </h4>
                            <label class="cursor-pointer text-[11px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i class="fas fa-plus"></i> Add Diagram
                                <input type="file" accept="image/*" class="hidden" @change="uploadIndicatorMedia($event)">
                            </label>
                        </div>

                        <template x-if="activeIndicator?.media?.length > 0">
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                <template x-for="item in activeIndicator.media" :key="item.id">
                                    <div class="group relative rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-750 p-1.5">
                                        <img :src="'/storage/' + item.file_path" :alt="item.caption || 'Diagram'" class="w-full h-28 object-contain bg-white rounded-lg">
                                        <div class="mt-1.5 px-1 flex items-center justify-between text-[10px] text-gray-500">
                                            <span class="truncate" x-text="item.caption || ('Page ' + (item.page_number || 'N/A'))"></span>
                                            <button type="button" @click="deleteMedia(item.id)" class="text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!activeIndicator?.media || activeIndicator?.media?.length === 0">
                            <p class="text-xs text-gray-400 italic">No diagrams attached to this indicator yet. Upload or extract page visuals to display symbols, geometric shapes, or charts.</p>
                        </template>
                    </div>

                    <!-- Attached Textbook Sections Preview -->
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                            <i class="fas fa-book-open text-blue-600"></i> Linked Textbook Sections
                        </h4>
                        <template x-if="activeIndicator?.textbook_sections?.length > 0">
                            <div class="space-y-2">
                                <template x-for="sec in activeIndicator.textbook_sections" :key="sec.id">
                                    <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-xl flex items-center justify-between text-xs border border-gray-200/60 dark:border-gray-700">
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="sec.title"></span>
                                        <span class="text-gray-500 font-medium">Pages: <span x-text="sec.page_start || 'N/A'"></span> - <span x-text="sec.page_end || 'N/A'"></span></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!activeIndicator?.textbook_sections || activeIndicator?.textbook_sections?.length === 0">
                            <p class="text-xs text-gray-400 italic">No textbook content linked to this indicator yet. Upload textbooks in the Textbooks tab to link lessons automatically.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function curriculumReviewApp() {
    return {
        status: '{{ $curriculum->extraction_status }}',
        errorMessage: '{{ addslashes($curriculum->extraction_error ?? '') }}',
        progressNotes: '{{ addslashes($curriculum->notes ?? '') }}',
        selectedIndicatorId: null,
        selectedStrandId: null,
        selectedSubStrandId: null,
        activeIndicator: null,
        activeStrand: null,
        activeSubStrand: null,
        isExtractingStrand: false,
        showAddIndicatorModal: false,
        newIndicator: {
            indicator_code: '',
            title: '',
            description: '',
            exemplars: ''
        },

        strandsMap: {
            @foreach($curriculum->strands as $strand)
                {{ $strand->id }}: {
                    id: {{ $strand->id }},
                    title: @json($strand->title),
                    grade_label: @json($strand->grade_label),
                    level_id: @json($strand->level_id),
                    description: @json($strand->description),
                    sub_strands: [
                        @foreach($strand->subStrands as $ss)
                            {
                                id: {{ $ss->id }},
                                strand_id: {{ $strand->id }},
                                title: @json($ss->title),
                                content_standard: @json($ss->content_standard),
                                description: @json($ss->description),
                                indicators: [
                                    @foreach($ss->indicators as $ind)
                                        {
                                            id: {{ $ind->id }},
                                            sub_strand_id: {{ $ss->id }},
                                            indicator_code: @json($ind->indicator_code),
                                            title: @json($ind->title)
                                        },
                                    @endforeach
                                ]
                            },
                        @endforeach
                    ]
                },
            @endforeach
        },

        subStrandsMap: {
            @foreach($curriculum->strands as $strand)
                @foreach($strand->subStrands as $ss)
                    {{ $ss->id }}: {
                        id: {{ $ss->id }},
                        strand_id: {{ $strand->id }},
                        strand_title: @json($strand->title),
                        grade_label: @json($strand->grade_label),
                        title: @json($ss->title),
                        content_standard: @json($ss->content_standard),
                        description: @json($ss->description),
                        indicators: [
                            @foreach($ss->indicators as $ind)
                                {
                                    id: {{ $ind->id }},
                                    sub_strand_id: {{ $ss->id }},
                                    indicator_code: @json($ind->indicator_code),
                                    title: @json($ind->title),
                                    description: @json($ind->description),
                                    exemplars: @json($ind->exemplars),
                                    media: @json($ind->media),
                                    textbook_sections: @json($ind->textbookSections)
                                },
                            @endforeach
                        ]
                    },
                @endforeach
            @endforeach
        },

        indicatorsMap: {
            @foreach($curriculum->strands as $strand)
                @foreach($strand->subStrands as $subStrand)
                    @foreach($subStrand->indicators as $ind)
                        {{ $ind->id }}: {
                            id: {{ $ind->id }},
                            sub_strand_id: {{ $subStrand->id }},
                            sub_strand_title: @json($subStrand->title),
                            content_standard: @json($subStrand->content_standard),
                            strand_id: {{ $strand->id }},
                            strand_title: @json($strand->title),
                            grade_label: @json($strand->grade_label),
                            indicator_code: @json($ind->indicator_code),
                            title: @json($ind->title),
                            description: @json($ind->description),
                            exemplars: @json($ind->exemplars),
                            media: @json($ind->media),
                            textbook_sections: @json($ind->textbookSections)
                        },
                    @endforeach
                @endforeach
            @endforeach
        },

        init() {
            if (this.status === 'processing' || this.status === 'pending') {
                this.pollStatus();
            }
            // Auto select first indicator if exists, or first strand
            const firstIndKey = Object.keys(this.indicatorsMap)[0];
            if (firstIndKey) {
                this.selectIndicator(parseInt(firstIndKey));
            } else {
                const firstStrandKey = Object.keys(this.strandsMap)[0];
                if (firstStrandKey) {
                    this.selectStrand(parseInt(firstStrandKey));
                }
            }
        },

        viewMode: 'table', // 'table' (PDF layout) or 'form' (edit inputs)

        selectIndicator(id, mode = 'table') {
            this.selectedIndicatorId = id;
            this.selectedSubStrandId = this.indicatorsMap[id]?.sub_strand_id || null;
            this.activeIndicator = Object.assign({}, this.indicatorsMap[id]);
            this.activeSubStrand = null;
            this.activeStrand = null;
            if (mode) this.viewMode = mode;
        },

        selectStrand(id) {
            this.selectedStrandId = id;
            this.selectedSubStrandId = null;
            this.selectedIndicatorId = null;
            this.activeIndicator = null;
            this.activeSubStrand = null;
            this.activeStrand = Object.assign({}, this.strandsMap[id]);
        },

        selectSubStrand(id) {
            this.selectedSubStrandId = id;
            this.selectedIndicatorId = null;
            this.activeIndicator = null;
            this.activeStrand = null;
            this.activeSubStrand = Object.assign({}, this.subStrandsMap[id]);
            this.viewMode = 'table';
        },

        formatExemplars(text) {
            if (!text) return '';
            // Escape HTML characters
            let escaped = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
            
            // Highlight E.g. or Example headings
            escaped = escaped.replace(/(E\.g\.\s*\d+|Example\s*\d+|E\.g\.)/gi, '<strong class="text-blue-900 font-bold block mt-2.5 mb-1">$1</strong>');
            
            // Format line breaks
            return escaped.replace(/\n/g, '<br>');
        },

        getCompetencies(text) {
            if (!text) {
                return [
                    { code: 'CC', label: 'Communication and Collaboration', color: 'bg-blue-50 text-blue-800 border-blue-200' },
                    { code: 'CP', label: 'Critical Thinking and Problem Solving', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' }
                ];
            }
            const comps = [];
            if (/CC|Communication/i.test(text)) comps.push({ code: 'CC', label: 'Communication and Collaboration', color: 'bg-blue-50 text-blue-800 border-blue-200' });
            if (/CP|Critical|Problem/i.test(text)) comps.push({ code: 'CP', label: 'Critical Thinking and Problem Solving', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' });
            if (/CI|Creativity|Innovation/i.test(text)) comps.push({ code: 'CI', label: 'Creativity and Innovation', color: 'bg-purple-50 text-purple-800 border-purple-200' });
            if (/CG|Cultural|Global/i.test(text)) comps.push({ code: 'CG', label: 'Cultural Identity and Global Citizenship', color: 'bg-amber-50 text-amber-800 border-amber-200' });
            if (/PL|Personal|Leadership/i.test(text)) comps.push({ code: 'PL', label: 'Personal Development and Leadership', color: 'bg-indigo-50 text-indigo-800 border-indigo-200' });
            if (/DL|Digital|Literacy/i.test(text)) comps.push({ code: 'DL', label: 'Digital Literacy', color: 'bg-cyan-50 text-cyan-800 border-cyan-200' });
            
            if (comps.length === 0) {
                comps.push({ code: 'CC', label: 'Communication and Collaboration', color: 'bg-blue-50 text-blue-800 border-blue-200' });
                comps.push({ code: 'CP', label: 'Critical Thinking and Problem Solving', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' });
            }
            return comps;
        },

        async saveStrand() {
            if (!this.activeStrand) return;

            try {
                const response = await fetch(`/admin/curriculum/strands/${this.activeStrand.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.activeStrand.title,
                        grade_label: this.activeStrand.grade_label,
                        level_id: this.activeStrand.level_id,
                        description: this.activeStrand.description
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.strandsMap[this.activeStrand.id] = Object.assign(this.strandsMap[this.activeStrand.id], this.activeStrand);
                    alert('Strand updated successfully!');
                }
            } catch (e) {
                alert('Error updating strand: ' + e.message);
            }
        },

        async saveSubStrand() {
            if (!this.activeSubStrand) return;

            try {
                const response = await fetch(`/admin/curriculum/sub-strands/${this.activeSubStrand.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.activeSubStrand.title,
                        content_standard: this.activeSubStrand.content_standard,
                        description: this.activeSubStrand.description
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.subStrandsMap[this.activeSubStrand.id] = Object.assign(this.subStrandsMap[this.activeSubStrand.id], this.activeSubStrand);
                    alert('Sub-strand updated successfully!');
                }
            } catch (e) {
                alert('Error updating sub-strand: ' + e.message);
            }
        },

        async reExtractStrand(strandId) {
            if (!confirm('Re-extract indicators for this strand? This will query Gemini to extract detailed learning indicators and exemplars.')) return;

            this.isExtractingStrand = true;
            try {
                const response = await fetch(`/admin/curriculum/strands/${strandId}/extract`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message || 'Indicators extracted successfully!');
                    window.location.reload();
                } else {
                    alert('Extraction error: ' + (data.message || 'Unknown error.'));
                }
            } catch (e) {
                alert('Extraction request failed: ' + e.message);
            } finally {
                this.isExtractingStrand = false;
            }
        },

        async createNewIndicator() {
            if (!this.activeSubStrand) return;
            if (!this.newIndicator.title) {
                alert('Please enter an indicator title.');
                return;
            }

            try {
                const response = await fetch(`/admin/curriculum/sub-strands/${this.activeSubStrand.id}/indicators`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newIndicator)
                });

                const data = await response.json();
                if (data.success) {
                    this.showAddIndicatorModal = false;
                    this.newIndicator = { indicator_code: '', title: '', description: '', exemplars: '' };
                    alert('Indicator created successfully!');
                    window.location.reload();
                } else {
                    alert('Error creating indicator: ' + (data.message || 'Unknown error'));
                }
            } catch (e) {
                alert('Failed to create indicator: ' + e.message);
            }
        },

        async deleteIndicator(indicatorId) {
            if (!confirm('Are you sure you want to delete this indicator?')) return;

            try {
                const response = await fetch(`/admin/curriculum/indicators/${indicatorId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    delete this.indicatorsMap[indicatorId];
                    alert('Indicator deleted successfully.');
                    window.location.reload();
                }
            } catch (e) {
                alert('Failed to delete indicator: ' + e.message);
            }
        },

        async saveIndicator() {
            if (!this.activeIndicator) return;

            try {
                const response = await fetch(`/admin/curriculum/indicators/${this.activeIndicator.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        indicator_code: this.activeIndicator.indicator_code,
                        title: this.activeIndicator.title,
                        description: this.activeIndicator.description,
                        exemplars: this.activeIndicator.exemplars
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.indicatorsMap[this.activeIndicator.id] = Object.assign({}, this.activeIndicator);
                    alert('Indicator updated successfully!');
                }
            } catch (e) {
                alert('Error updating indicator: ' + e.message);
            }
        },

        async uploadIndicatorMedia(event) {
            const file = event.target.files[0];
            if (!file || !this.activeIndicator) return;

            const formData = new FormData();
            formData.append('media_file', file);
            formData.append('caption', file.name.replace(/\.[^/.]+$/, ''));

            try {
                const response = await fetch(`/admin/curriculum/indicators/${this.activeIndicator.id}/media`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    if (!this.activeIndicator.media) {
                        this.activeIndicator.media = [];
                    }
                    this.activeIndicator.media.push(data.media);
                    this.indicatorsMap[this.activeIndicator.id].media = this.activeIndicator.media;
                    event.target.value = '';
                } else {
                    alert(data.message || 'Could not upload diagram.');
                }
            } catch (e) {
                alert('Error uploading diagram: ' + e.message);
            }
        },

        async deleteMedia(mediaId) {
            if (!confirm('Are you sure you want to remove this diagram?')) return;

            try {
                const response = await fetch(`/admin/curriculum/media/${mediaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.activeIndicator.media = this.activeIndicator.media.filter(m => m.id !== mediaId);
                    this.indicatorsMap[this.activeIndicator.id].media = this.activeIndicator.media;
                }
            } catch (e) {
                alert('Error removing diagram: ' + e.message);
            }
        },

        pollStatus() {
            const interval = setInterval(async () => {
                try {
                    const res = await fetch(`{{ route('admin.curriculum.status', $curriculum) }}`);
                    const data = await res.json();
                    this.status = data.status;
                    if (data.notes) {
                        this.progressNotes = data.notes;
                    }
                    if (data.error) {
                        this.errorMessage = data.error;
                    }
                    if (data.status === 'extracted' || data.status === 'failed') {
                        clearInterval(interval);
                        // Refresh page to populate the extracted strands and indicators hierarchy
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    }
                } catch (e) {}
            }, 4000);
        }
    };
}
</script>
@endsection
