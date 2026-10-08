@extends('layouts.admin')

@section('title', 'Textbook Lessons - ' . $textbook->title)

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="textbookViewerApp()">
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
                <span>{{ $textbook->chapters->count() }} Chapters</span>
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
                <!-- Collapsible technical details -->
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

    <!-- Chapters & Sections Table of Contents (Editable) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-base">
                <i class="fas fa-list-ol text-blue-600"></i>
                Table of Contents & Lesson Mappings
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500">
                    Click titles to edit • Map lessons to curriculum indicators
                </span>
                <button type="button"
                        @click="addChapter()"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-plus mr-1.5"></i> Add Chapter
                </button>
            </div>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700" id="toc-chapters-list">
            @forelse($textbook->chapters as $chapter)
            <div class="p-5" x-data="{ chapterOpen: true, editing: false, editTitle: '{{ addslashes($chapter->title) }}', editPageStart: '{{ $chapter->page_start }}', editPageEnd: '{{ $chapter->page_end }}' }" id="chapter-row-{{ $chapter->id }}">
                <!-- Chapter Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <i class="fas fa-folder text-blue-500 text-lg flex-shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <!-- Display mode -->
                            <template x-if="!editing">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm cursor-pointer hover:text-blue-600 transition-colors"
                                        @click="editing = true"
                                        title="Click to edit">
                                        {{ $chapter->title }}
                                        <i class="fas fa-pencil-alt text-[10px] text-gray-300 ml-1"></i>
                                    </h4>
                                    <p class="text-xs text-gray-400">
                                        @if($chapter->page_start) Pages {{ $chapter->page_start }} - {{ $chapter->page_end ?? 'end' }} • @endif
                                        {{ $chapter->sections->count() }} sub-sections
                                    </p>
                                </div>
                            </template>
                            <!-- Edit mode -->
                            <template x-if="editing">
                                <div class="space-y-2">
                                    <input type="text" x-model="editTitle"
                                           @keydown.enter="saveChapter({{ $chapter->id }}, editTitle, editPageStart, editPageEnd); editing = false"
                                           @keydown.escape="editing = false"
                                           x-ref="chapterTitleInput"
                                           x-init="$watch('editing', v => { if(v) $nextTick(() => $refs.chapterTitleInput?.focus()) })"
                                           class="w-full text-sm font-bold rounded-lg border-blue-300 dark:border-blue-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 focus:ring-blue-500 focus:border-blue-500">
                                    <div class="flex items-center gap-2">
                                        <input type="number" x-model="editPageStart" placeholder="Start page"
                                               class="w-24 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2">
                                        <span class="text-gray-400 text-xs">to</span>
                                        <input type="number" x-model="editPageEnd" placeholder="End page"
                                               class="w-24 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2">
                                        <button @click="saveChapter({{ $chapter->id }}, editTitle, editPageStart, editPageEnd); editing = false"
                                                class="px-2.5 py-1 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                            <i class="fas fa-check mr-1"></i> Save
                                        </button>
                                        <button @click="editing = false"
                                                class="px-2.5 py-1 text-xs text-gray-500 hover:text-gray-700 rounded-lg">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                        <button @click="addSection({{ $chapter->id }})"
                                class="px-2.5 py-1 text-xs border border-emerald-200 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-md font-medium transition-colors"
                                title="Add sub-section">
                            <i class="fas fa-plus mr-1"></i> Add Section
                        </button>
                        <form action="{{ route('admin.textbooks.extract-content', $textbook) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <button type="submit" class="px-2.5 py-1 text-xs border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-600 dark:text-gray-300 rounded-md transition-colors">
                                <i class="fas fa-sync-alt mr-1"></i> Extract Chapter
                            </button>
                        </form>
                        <button @click="if(confirm('Delete chapter \'{{ addslashes($chapter->title) }}\' and all its sections?')) deleteChapter({{ $chapter->id }})"
                                class="px-2 py-1 text-xs text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-md transition-colors"
                                title="Delete chapter">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button @click="chapterOpen = !chapterOpen" class="p-1">
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="{'rotate-180': chapterOpen}"></i>
                        </button>
                    </div>
                </div>

                <!-- Sections under this chapter -->
                <div x-show="chapterOpen" class="mt-4 pl-4 border-l-2 border-blue-100 dark:border-gray-700 space-y-3" id="chapter-{{ $chapter->id }}-sections">
                    @forelse($chapter->sections as $section)
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-3"
                         x-data="{ sectionEditing: false, sectionTitle: '{{ addslashes($section->title) }}', sectionPageStart: '{{ $section->page_start }}', sectionPageEnd: '{{ $section->page_end }}' }"
                         id="section-row-{{ $section->id }}">
                        <div class="flex-1 min-w-0">
                            <!-- Display mode -->
                            <template x-if="!sectionEditing">
                                <div>
                                    <div class="font-semibold text-xs text-gray-900 dark:text-white flex items-center gap-2 cursor-pointer hover:text-blue-600 transition-colors"
                                         @click="sectionEditing = true" title="Click to edit">
                                        <i class="fas fa-file-alt text-gray-400"></i>
                                        {{ $section->title }}
                                        <i class="fas fa-pencil-alt text-[9px] text-gray-300"></i>
                                    </div>
                                    @if($section->page_start)
                                        <div class="text-[11px] text-gray-400 mt-0.5 ml-6">Pages {{ $section->page_start }} - {{ $section->page_end ?? 'end' }}</div>
                                    @endif
                                    @if($section->content_markdown)
                                        <div class="text-[11px] text-emerald-600 font-medium mt-1 ml-6 flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Lesson content extracted
                                        </div>
                                    @endif
                                </div>
                            </template>
                            <!-- Edit mode -->
                            <template x-if="sectionEditing">
                                <div class="space-y-2">
                                    <input type="text" x-model="sectionTitle"
                                           @keydown.enter="saveSection({{ $section->id }}, sectionTitle, sectionPageStart, sectionPageEnd); sectionEditing = false"
                                           @keydown.escape="sectionEditing = false"
                                           x-ref="sectionTitleInput"
                                           x-init="$watch('sectionEditing', v => { if(v) $nextTick(() => $refs.sectionTitleInput?.focus()) })"
                                           class="w-full text-xs font-semibold rounded-lg border-blue-300 dark:border-blue-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3 focus:ring-blue-500 focus:border-blue-500">
                                    <div class="flex items-center gap-2">
                                        <input type="number" x-model="sectionPageStart" placeholder="Start pg"
                                               class="w-20 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2">
                                        <span class="text-gray-400 text-xs">to</span>
                                        <input type="number" x-model="sectionPageEnd" placeholder="End pg"
                                               class="w-20 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2">
                                        <button @click="saveSection({{ $section->id }}, sectionTitle, sectionPageStart, sectionPageEnd); sectionEditing = false"
                                                class="px-2 py-1 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button @click="sectionEditing = false"
                                                class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <!-- Link to Curriculum Indicator -->
                            <select @change="linkIndicator($event.target.value, {{ $section->id }})"
                                class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2.5 max-w-[220px]">
                                <option value="">Select Indicator</option>
                                @foreach($availableIndicators as $ind)
                                    <option value="{{ $ind->id }}" {{ $section->curriculum_indicator_id == $ind->id ? 'selected' : '' }}>
                                        [{{ $ind->indicator_code ?? 'IND' }}] {{ \Illuminate\Support\Str::limit($ind->title, 35) }}
                                    </option>
                                @endforeach
                            </select>
                            <button @click="if(confirm('Delete section \'{{ addslashes($section->title) }}\'?')) deleteSection({{ $section->id }})"
                                    class="px-2 py-1 text-xs text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-md transition-colors"
                                    title="Delete section">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 italic py-1">No sub-sections yet. Click "Add Section" to create one.</p>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400 text-sm">
                No Table of Contents extracted yet. Click "Add Chapter" to build one manually.
            </div>
            @endforelse
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function textbookViewerApp() {
    return {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,

        headers() {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            };
        },

        // ── Link indicator (existing) ──
        async linkIndicator(indicatorId, sectionId) {
            try {
                const res = await fetch(`/admin/textbooks/sections/${sectionId}/link-indicator`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ curriculum_indicator_id: indicatorId || null })
                });
                const data = await res.json();
                if (!data.success) throw new Error('Server error');
            } catch (e) {
                alert('Failed to link indicator: ' + e.message);
            }
        },

        // ── Save chapter title & pages ──
        async saveChapter(chapterId, title, pageStart, pageEnd) {
            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapterId}`, {
                    method: 'PUT', headers: this.headers(),
                    body: JSON.stringify({
                        title: title,
                        page_start: pageStart || null,
                        page_end: pageEnd || null
                    })
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (e) {
                alert('Failed to save chapter: ' + e.message);
            }
        },

        // ── Save section title & pages ──
        async saveSection(sectionId, title, pageStart, pageEnd) {
            try {
                const res = await fetch(`/admin/textbooks/sections/${sectionId}`, {
                    method: 'PUT', headers: this.headers(),
                    body: JSON.stringify({
                        title: title,
                        page_start: pageStart || null,
                        page_end: pageEnd || null
                    })
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (e) {
                alert('Failed to save section: ' + e.message);
            }
        },

        // ── Add new chapter ──
        async addChapter() {
            const title = prompt('Enter chapter title:');
            if (!title || !title.trim()) return;

            try {
                const res = await fetch(`/admin/textbooks/{{ $textbook->id }}/chapters`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ title: title.trim() })
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (e) {
                alert('Failed to add chapter: ' + e.message);
            }
        },

        // ── Add new section under a chapter ──
        async addSection(chapterId) {
            const title = prompt('Enter section/sub-topic title:');
            if (!title || !title.trim()) return;

            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapterId}/sections`, {
                    method: 'POST', headers: this.headers(),
                    body: JSON.stringify({ title: title.trim() })
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (e) {
                alert('Failed to add section: ' + e.message);
            }
        },

        // ── Delete chapter ──
        async deleteChapter(chapterId) {
            try {
                const res = await fetch(`/admin/textbooks/chapters/${chapterId}`, {
                    method: 'DELETE', headers: this.headers()
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById(`chapter-row-${chapterId}`);
                    if (row) row.remove();
                }
            } catch (e) {
                alert('Failed to delete chapter: ' + e.message);
            }
        },

        // ── Delete section ──
        async deleteSection(sectionId) {
            try {
                const res = await fetch(`/admin/textbooks/sections/${sectionId}`, {
                    method: 'DELETE', headers: this.headers()
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById(`section-row-${sectionId}`);
                    if (row) row.remove();
                }
            } catch (e) {
                alert('Failed to delete section: ' + e.message);
            }
        }
    };
}
</script>
@endsection
