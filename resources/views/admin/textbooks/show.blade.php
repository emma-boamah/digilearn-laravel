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

    @if($textbook->toc_extraction_status === 'processing' || $textbook->content_extraction_status === 'processing')
    <div class="p-5 rounded-2xl bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100 shadow-sm">
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
                    Gemini is extracting the Table of Contents, structuring chapters, and formatting lesson content.
                </p>
            </div>
        </div>
    </div>
    @elseif($textbook->toc_extraction_status === 'failed')
    <div class="p-5 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-red-900">Textbook Extraction Issue</h4>
                <p class="text-xs text-red-700 mt-1">An error occurred while extracting the Table of Contents.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Chapters & Sections Table of Contents -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-base">
                <i class="fas fa-list-ol text-blue-600"></i>
                Table of Contents & Lesson Mappings
            </h3>
            <span class="text-xs text-gray-500">
                Map extracted textbook lessons to official curriculum indicators.
            </span>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($textbook->chapters as $chapter)
            <div class="p-5" x-data="{ chapterOpen: true }">
                <div class="flex items-center justify-between cursor-pointer" @click="chapterOpen = !chapterOpen">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-folder text-blue-500 text-lg"></i>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ $chapter->title }}</h4>
                            <p class="text-xs text-gray-400">
                                @if($chapter->page_start) Pages {{ $chapter->page_start }} - {{ $chapter->page_end ?? 'end' }} • @endif
                                {{ $chapter->sections->count() }} sub-sections
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('admin.textbooks.extract-content', $textbook) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <button type="submit" class="px-2.5 py-1 text-xs border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-600 dark:text-gray-300 rounded-md">
                                <i class="fas fa-sync-alt mr-1"></i> Extract Chapter
                            </button>
                        </form>
                        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="{'rotate-180': chapterOpen}"></i>
                    </div>
                </div>

                <!-- Sections under this chapter -->
                <div x-show="chapterOpen" class="mt-4 pl-4 border-l-2 border-blue-100 dark:border-gray-700 space-y-3">
                    @forelse($chapter->sections as $section)
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-750 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-xs text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-file-alt text-gray-400"></i>
                                {{ $section->title }}
                            </div>
                            @if($section->page_start)
                                <div class="text-[11px] text-gray-400 mt-0.5">Pages {{ $section->page_start }} - {{ $section->page_end ?? 'end' }}</div>
                            @endif
                            @if($section->content_markdown)
                                <div class="text-[11px] text-emerald-600 font-medium mt-1 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Lesson content extracted
                                </div>
                            @endif
                        </div>

                        <!-- Link to Curriculum Indicator -->
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-medium text-gray-500 whitespace-nowrap">Indicator:</label>
                            <select @change="linkIndicator({{ $section->id }}, $event.target.value)"
                                class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white py-1 px-2.5 max-w-[260px]">
                                <option value="">Select Curriculum Indicator</option>
                                @foreach($availableIndicators as $ind)
                                    <option value="{{ $ind->id }}" {{ $section->curriculum_indicator_id == $ind->id ? 'selected' : '' }}>
                                        [{{ $ind->indicator_code ?? 'IND' }}] {{ \Illuminate\Support\Str::limit($ind->title, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 italic py-1">No sub-sections extracted for this chapter yet.</p>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400 text-sm">
                No Table of Contents extracted yet.
            </div>
            @endforelse
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function textbookViewerApp() {
    return {
        async linkIndicator(sectionId, indicatorId) {
            try {
                const response = await fetch(`/admin/textbooks/sections/${sectionId}/link-indicator`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ curriculum_indicator_id: indicatorId || null })
                });
                const data = await response.json();
                if (data.success) {
                    // Linked successfully
                }
            } catch (e) {
                alert('Failed to link indicator: ' + e.message);
            }
        }
    };
}
</script>
@endsection
