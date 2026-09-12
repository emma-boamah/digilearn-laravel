@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 space-y-8" x-data="studentCurriculumApp()">
    <!-- Hero Header -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold backdrop-blur-md mb-3 text-blue-200">
                    <i class="fas fa-compass text-blue-400"></i> National Guided Curriculum
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    {{ $currentSubject->name ?? 'Guided Learning' }}
                </h1>
                <p class="text-sm text-slate-300 max-w-xl mt-2">
                    Master every official national standard step-by-step. Track your progress with FreeCodeCamp-style checkpoints and unlock topic notes, videos, and quizzes.
                </p>
            </div>

            <!-- Subject Progress Dial -->
            <div class="bg-white/10 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-white/10 text-center min-w-[160px]">
                <div class="text-3xl font-black text-emerald-400" x-text="(stats.overall_percentage || 0) + '%'">
                    {{ $curriculumData['stats']['overall_percentage'] ?? 0 }}%
                </div>
                <div class="text-xs text-slate-300 font-medium mt-1">Curriculum Completed</div>
                <div class="w-full bg-white/20 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full transition-all duration-500"
                        :style="'width: ' + (stats.overall_percentage || 0) + '%'"
                        style="width: {{ $curriculumData['stats']['overall_percentage'] ?? 0 }}%;"></div>
                </div>
                <div class="text-[11px] text-slate-400 mt-2">
                    <span x-text="stats.completed_indicators || 0">{{ $curriculumData['stats']['completed_indicators'] ?? 0 }}</span> of 
                    <span x-text="stats.total_indicators || 0">{{ $curriculumData['stats']['total_indicators'] ?? 0 }}</span> Objectives
                </div>
            </div>
        </div>

        <!-- Subject Switcher Pills -->
        @if($availableSubjects->isNotEmpty())
        <div class="flex items-center gap-2 overflow-x-auto pt-6 mt-6 border-t border-white/10 no-scrollbar">
            @foreach($availableSubjects as $sub)
            <a href="{{ route('guided-learning.index', ['subject_id' => $sub->id]) }}"
                class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $currentSubject->id === $sub->id ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white/5 hover:bg-white/15 text-slate-300' }}">
                {{ $sub->name }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <!-- FreeCodeCamp Style Learning Roadmap -->
    @if(!empty($curriculumData['strands']))
    <div class="space-y-6">
        @foreach($curriculumData['strands'] as $strandIndex => $strand)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm transition-all"
             x-data="{ strandOpen: {{ $strandIndex === 0 ? 'true' : 'false' }} }">
            
            <!-- Strand Header -->
            <div class="p-5 sm:p-6 bg-gray-50/70 dark:bg-gray-750 flex items-center justify-between cursor-pointer border-b border-gray-100 dark:border-gray-700"
                 @click="strandOpen = !strandOpen">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                        {{ $strandIndex + 1 }}
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base sm:text-lg flex items-center gap-2">
                            {{ $strand['title'] }}
                            @if(!empty($strand['grade_label']))
                                <span class="text-xs px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded font-normal">
                                    {{ $strand['grade_label'] }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $strand['description'] ?? 'Standard curriculum strand' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <span class="text-xs font-bold text-blue-600">{{ $strand['completed_topics'] }} / {{ $strand['total_topics'] }}</span>
                        <span class="text-xs text-gray-400">Done</span>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-200" :class="{'rotate-180': strandOpen}"></i>
                </div>
            </div>

            <!-- Sub-Strands & Checklist Items -->
            <div x-show="strandOpen" class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($strand['sub_strands'] as $subStrand)
                <div class="p-5 sm:p-6">
                    <div class="mb-3">
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-bookmark text-blue-500 text-xs"></i>
                            {{ $subStrand['title'] }}
                        </h4>
                        @if(!empty($subStrand['content_standard']))
                            <p class="text-xs text-gray-500 mt-1 pl-5">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Standard:</span> {{ $subStrand['content_standard'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Indicators (Checklist Rows) -->
                    <div class="space-y-2 pl-2 sm:pl-5">
                        @foreach($subStrand['indicators'] as $ind)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-750/50 transition-all border border-transparent hover:border-gray-200 dark:hover:border-gray-700 group">
                            
                            <!-- Left: Checkbox Icon + Topic Link -->
                            <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                <!-- FreeCodeCamp Completion Circle (Clickable Toggle) -->
                                <button type="button" 
                                        @click="toggleIndicator({{ $ind['id'] }})"
                                        class="w-6 h-6 rounded-full flex items-center justify-center transition-all flex-shrink-0"
                                        :class="{
                                            'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30': indicatorsStatus[{{ $ind['id'] }}] === 'completed',
                                            'border-2 border-blue-400 text-blue-600 bg-blue-50': indicatorsStatus[{{ $ind['id'] }}] === 'in_progress',
                                            'border-2 border-gray-300 dark:border-gray-600 hover:border-blue-400 bg-transparent': !indicatorsStatus[{{ $ind['id'] }}] || indicatorsStatus[{{ $ind['id'] }}] === 'not_started'
                                        }">
                                    <template x-if="indicatorsStatus[{{ $ind['id'] }}] === 'completed'">
                                        <i class="fas fa-check text-xs"></i>
                                    </template>
                                    <template x-if="indicatorsStatus[{{ $ind['id'] }}] === 'in_progress'">
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    </template>
                                </button>

                                <!-- Title Link -->
                                <a href="{{ route('guided-learning.topic', $ind['id']) }}" class="flex items-center gap-2.5 truncate group-hover:text-blue-600 transition-colors">
                                    @if(!empty($ind['code']))
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ $ind['code'] }}
                                        </span>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                          :class="{'line-through text-gray-400 dark:text-gray-500': indicatorsStatus[{{ $ind['id'] }}] === 'completed'}">
                                        {{ $ind['title'] }}
                                    </span>
                                </a>
                            </div>

                            <!-- Right: Action Button -->
                            <div class="flex items-center gap-2 pl-3">
                                <a href="{{ route('guided-learning.topic', $ind['id']) }}"
                                   class="px-3 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-blue-600 hover:text-white text-gray-700 dark:text-gray-200 transition-all opacity-0 group-hover:opacity-100 sm:opacity-100">
                                    Start Lesson <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-200 dark:border-gray-700 shadow-sm max-w-lg mx-auto">
        <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Curriculum in Preparation</h3>
        <p class="text-xs text-gray-500 mt-2">
            The national curriculum for {{ $currentSubject->name ?? 'this subject' }} is being extracted and verified by administrators. Check back shortly!
        </p>
    </div>
    @endif
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function studentCurriculumApp() {
    return {
        stats: @json($curriculumData['stats'] ?? ['overall_percentage' => 0, 'completed_indicators' => 0, 'total_indicators' => 0]),
        indicatorsStatus: {
            @if(!empty($curriculumData['strands']))
                @foreach($curriculumData['strands'] as $strand)
                    @foreach($strand['sub_strands'] as $subStrand)
                        @foreach($subStrand['indicators'] as $ind)
                            {{ $ind['id'] }}: '{{ $ind['status'] }}',
                        @endforeach
                    @endforeach
                @endforeach
            @endif
        },

        async toggleIndicator(id) {
            try {
                const response = await fetch(`/guided-learning/topic/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.indicatorsStatus[id] = data.status;
                    if (data.stats) {
                        this.stats = data.stats;
                    }
                }
            } catch (e) {
                console.error('Failed to toggle indicator:', e);
            }
        }
    };
}
</script>
@endsection
