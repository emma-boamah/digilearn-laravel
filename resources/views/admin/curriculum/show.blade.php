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

    <!-- Processing Alert Banner -->
    @if($curriculum->extraction_status === 'processing')
    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 flex items-center justify-between shadow-sm" x-show="isProcessing">
        <div class="flex items-center gap-3">
            <i class="fas fa-spinner fa-spin text-xl text-blue-600"></i>
            <div>
                <h4 class="font-bold text-sm">AI Curriculum Extraction in Progress</h4>
                <p class="text-xs text-blue-700 mt-0.5">Gemini is reading the curriculum structure, identifying strands, content standards, and measurable indicators. This page refreshes automatically.</p>
            </div>
        </div>
    </div>
    @elseif($curriculum->extraction_status === 'failed')
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-start gap-3 shadow-sm">
        <i class="fas fa-exclamation-circle text-xl text-red-600 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-sm">Extraction Encountered an Issue</h4>
            <p class="text-xs text-red-700 mt-1 font-mono bg-white/80 p-2.5 rounded-xl border border-red-100">{{ $curriculum->extraction_error ?? 'Unknown error occurred during extraction.' }}</p>
            <p class="text-xs text-red-600 mt-2">You can click "Re-extract" above to retry with alternative models.</p>
        </div>
    </div>
    @endif

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

            <div class="space-y-2.5">
                @forelse($curriculum->strands as $strand)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden" x-data="{ open: true }">
                    <button @click="open = !open; selectStrand({{ $strand->id }})"
                        class="w-full text-left p-3.5 bg-gray-50 dark:bg-gray-750 hover:bg-blue-50/50 flex items-center justify-between text-sm font-semibold text-gray-900 dark:text-white transition-colors">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-layer-group text-blue-600 text-xs"></i>
                            <span class="truncate max-w-[200px]">{{ $strand->title }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            @if($strand->grade_label)
                                <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-600 rounded-md text-[10px] font-semibold text-gray-700 dark:text-gray-200">{{ $strand->grade_label }}</span>
                            @endif
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{'rotate-180': open}"></i>
                        </div>
                    </button>

                    <div x-show="open" class="p-2 space-y-1.5 bg-white dark:bg-gray-800">
                        @foreach($strand->subStrands as $subStrand)
                        <div class="pl-2 border-l-2 border-blue-200 dark:border-gray-700">
                            <div class="py-1 px-2 text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                                <span class="truncate">{{ $subStrand->title }}</span>
                                <span class="text-[10px] text-gray-400 font-medium">{{ $subStrand->indicators->count() }} ind.</span>
                            </div>

                            <!-- Indicator Items -->
                            <div class="pl-2.5 space-y-1 mt-1">
                                @foreach($subStrand->indicators as $indicator)
                                <button type="button" @click="selectIndicator({{ $indicator->id }})"
                                    class="w-full text-left py-1.5 px-2.5 text-[11px] rounded-lg flex items-center gap-2 transition-all"
                                    :class="selectedIndicatorId === {{ $indicator->id }} ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded font-bold"
                                          :class="selectedIndicatorId === {{ $indicator->id }} ? 'bg-blue-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'">
                                        {{ $indicator->indicator_code ?? 'IND' }}
                                    </span>
                                    <span class="truncate">{{ $indicator->title }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400 text-xs">
                    No strands extracted yet.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Inspector & Inline Editor -->
        <div class="lg:col-span-8 bg-white dark:bg-gray-800 p-6 sm:p-7 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm min-h-[500px]">
            <!-- No Selection State -->
            <div x-show="!activeIndicator && !activeStrand" class="flex flex-col items-center justify-center py-24 text-gray-400 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-base">Select an item from the left hierarchy</h4>
                <p class="text-xs text-gray-400 max-w-sm mt-1">Click any Strand, Sub-strand, or Indicator to inspect, verify, and update the extracted content.</p>
            </div>

            <!-- Indicator Detail Editor -->
            <div x-show="activeIndicator" class="space-y-5" style="display: none;">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-mono font-bold rounded-lg border border-blue-100" x-text="activeIndicator?.indicator_code || 'No Code'"></span>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="activeIndicator?.title"></h3>
                    </div>
                    <button type="button" @click="saveIndicator()" class="h-10 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Changes
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
                    <textarea rows="5" x-model="activeIndicator.exemplars" placeholder="Specific classroom activities, examples, sample questions..."
                        class="w-full p-3.5 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
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

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function curriculumReviewApp() {
    return {
        isProcessing: {{ $curriculum->extraction_status === 'processing' ? 'true' : 'false' }},
        selectedIndicatorId: null,
        activeIndicator: null,
        activeStrand: null,

        indicatorsMap: {
            @foreach($curriculum->strands as $strand)
                @foreach($strand->subStrands as $subStrand)
                    @foreach($subStrand->indicators as $ind)
                        {{ $ind->id }}: {
                            id: {{ $ind->id }},
                            indicator_code: @json($ind->indicator_code),
                            title: @json($ind->title),
                            description: @json($ind->description),
                            exemplars: @json($ind->exemplars),
                            textbook_sections: @json($ind->textbookSections)
                        },
                    @endforeach
                @endforeach
            @endforeach
        },

        init() {
            if (this.isProcessing) {
                this.pollStatus();
            }
            // Auto select first indicator if exists
            const firstKey = Object.keys(this.indicatorsMap)[0];
            if (firstKey) {
                this.selectIndicator(parseInt(firstKey));
            }
        },

        selectIndicator(id) {
            this.selectedIndicatorId = id;
            this.activeIndicator = Object.assign({}, this.indicatorsMap[id]);
        },

        selectStrand(id) {
            // Optional strand view
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

        pollStatus() {
            const interval = setInterval(async () => {
                try {
                    const res = await fetch(`{{ route('admin.curriculum.status', $curriculum) }}`);
                    const data = await res.json();
                    if (data.status === 'extracted' || data.status === 'failed') {
                        clearInterval(interval);
                        window.location.reload();
                    }
                } catch (e) {}
            }, 6000);
        }
    };
}
</script>
@endsection
