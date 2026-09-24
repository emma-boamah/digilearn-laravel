@extends('layouts.dashboard-components')

@section('head')
<script src="https://cdn.tailwindcss.com"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
  tailwind.config = {
    darkMode: ['class', '[data-theme="dark"]'],
  }
</script>
@endsection

@section('content')
<!-- Outer Container with pt-20 sm:pt-24 to clear the 60px fixed top-header -->
<div class="max-w-6xl mx-auto pt-20 sm:pt-24 pb-12 px-4 sm:px-6 space-y-8">

    @if($isSelectingSubject)
    <!-- ========================================================= -->
    <!-- 1. SUBJECT SELECTION SCREEN                               -->
    <!-- ========================================================= -->
    
    <!-- Hero Header: Gray/off-white in light mode, Dark/black in dark mode -->
    <div class="bg-gray-100/90 dark:bg-gray-900 rounded-3xl p-6 sm:p-8 text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-gray-800 relative overflow-hidden transition-colors duration-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-white/10 text-xs font-semibold backdrop-blur-md mb-3 text-blue-700 dark:text-blue-200 border border-gray-200/80 dark:border-white/10 shadow-xs">
                    <i class="fas fa-compass text-blue-600 dark:text-blue-400"></i> Guided Learning
                </div>
                <!-- Clean title with NO description underneath -->
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Choose a Subject to Learn
                </h1>
            </div>

            <!-- Global Completed Objectives Stats -->
            <div class="bg-white dark:bg-white/10 backdrop-blur-md p-5 rounded-2xl border border-gray-200 dark:border-white/10 text-center min-w-[190px] shadow-sm">
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400">
                    {{ $totalCompleted }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mt-1">Total Checkpoints Mastered</div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2">
                    <i class="fas fa-medal text-red-500 mr-1"></i> Across All Subjects
                </div>
            </div>
        </div>

        <!-- Level Indicator & Specific Grade Filter Bar (matching dashboard/digilearn filter bar) -->
        <div class="flex items-center gap-2 overflow-x-auto pt-5 mt-5 border-t border-gray-200 dark:border-gray-800 no-scrollbar">
            <!-- Level Indicator Badge for the pre-chosen level group -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-white/10 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-transparent shadow-2xs flex-shrink-0">
                <span class="text-gray-500 dark:text-gray-400 font-normal">Level:</span>
                <span class="text-gray-900 dark:text-white font-bold">{{ $currentLevelGroup->title ?? 'Level' }}</span>
            </div>

            <div class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1 flex-shrink-0"></div>

            <!-- Grade Tabs within the chosen Level Group -->
            <a href="{{ route('guided-learning.index', ['grade' => 'all']) }}"
               class="whitespace-nowrap px-4 py-1.5 rounded-xl text-xs font-semibold transition-all {{ ($selectedGradeSlug ?? 'all') === 'all' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'bg-white dark:bg-white/5 hover:bg-gray-200/70 dark:hover:bg-white/10 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-transparent' }}">
                All
            </a>

            @if(isset($levelsInGroup) && $levelsInGroup->isNotEmpty())
                @foreach($levelsInGroup as $lvl)
                <a href="{{ route('guided-learning.index', ['grade' => $lvl->slug]) }}"
                   class="whitespace-nowrap px-4 py-1.5 rounded-xl text-xs font-semibold transition-all {{ ($selectedGradeSlug ?? '') === $lvl->slug ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'bg-white dark:bg-white/5 hover:bg-gray-200/70 dark:hover:bg-white/10 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-transparent' }}">
                    {{ $lvl->title }}
                </a>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Available Subjects Section -->
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Available Subjects
                    @if(isset($currentLevelGroup))
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">
                        ({{ ($selectedGradeSlug && $selectedGradeSlug !== 'all') ? optional($levelsInGroup->firstWhere('slug', $selectedGradeSlug))->title : $currentLevelGroup->title }})
                    </span>
                    @endif
                </h2>
            </div>
        </div>

        @if(isset($subjectCards) && count($subjectCards) > 0)
        <!-- Hugging Face Style Grid: Gray/off-white in light mode, Dark/black in dark mode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($subjectCards as $card)
            <a href="{{ route('guided-learning.index', ['subject_id' => $card['encoded_subject_id'] ?? $card['subject_id']]) }}"
               class="group relative flex flex-col justify-between p-6 rounded-2xl bg-gray-100/90 dark:bg-gray-900 border border-gray-200/90 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-white dark:hover:bg-gray-850 shadow-sm hover:shadow-lg transition-all duration-200 cursor-pointer block">
                
                <!-- Upper Right: Completed Check Badge / In-Progress / Arrow Up-Right -->
                <div class="absolute top-4 right-4">
                    @if($card['percentage'] == 100)
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shadow-md shadow-blue-500/30" title="Completed">
                            <i class="fas fa-check text-[10px]"></i>
                        </div>
                    @elseif($card['percentage'] > 0)
                        <div class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-600/20 border border-red-300 dark:border-red-500/40 text-red-600 dark:text-red-400 flex items-center justify-center text-[10px]" title="In Progress">
                            <i class="fas fa-circle text-[8px] animate-pulse"></i>
                        </div>
                    @else
                        <div class="text-gray-400 dark:text-gray-500 group-hover:text-blue-600 dark:group-hover:text-white group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform duration-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <div>
                    <!-- Top Center Visual Container (Larger & Centered) -->
                    <div class="flex justify-center pt-2">
                        @if(!empty($card['image']))
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 p-3 flex items-center justify-center group-hover:scale-105 group-hover:border-blue-500/50 shadow-xs transition-all duration-300">
                                <img src="{{ $card['image'] }}" 
                                     alt="{{ $card['subject_name'] }}" 
                                     class="w-full h-full object-contain filter dark:invert dark:brightness-125 transition-transform duration-300">
                            </div>
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 flex items-center justify-center text-3xl text-blue-600 dark:text-blue-400 group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-500 transition-all duration-300 shadow-xs">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Subject Title (Centered) -->
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mt-4 text-center">
                        {{ $card['subject_name'] }}
                    </h3>

                    <!-- Short Tagline Description (Centered) -->
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed line-clamp-2 text-center">
                        {{ $card['tagline'] }}
                    </p>
                </div>

                <!-- Bottom Metadata (Minimal sleek progress) -->
                <div class="mt-5 pt-3 border-t border-gray-200/80 dark:border-gray-800">
                    @if($card['percentage'] > 0)
                        <div class="flex items-center justify-between text-[11px] text-gray-600 dark:text-gray-400 mb-1.5">
                            <span>{{ $card['completed_indicators'] }}/{{ $card['total_indicators'] }} checkpoints</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $card['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-blue-600 dark:bg-blue-500 h-full rounded-full transition-all duration-500" style="width: {{ $card['percentage'] }}%"></div>
                        </div>
                    @else
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <span>{{ $card['total_indicators'] }} checkpoints</span>
                            <span class="text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 font-semibold flex items-center gap-1 transition-colors">
                                Start <i class="fas fa-arrow-right text-[9px] group-hover:translate-x-0.5 transition-transform"></i>
                            </span>
                        </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        @else
        <!-- No Subjects Available State -->
        <div class="bg-gray-100/90 dark:bg-gray-900 rounded-3xl p-12 text-center border border-gray-200 dark:border-gray-800 shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-full bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4 text-2xl border border-gray-200 dark:border-gray-700">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">No Curricula Found</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                There are no approved curriculum roadmaps available for this grade yet.
            </p>
        </div>
        @endif
    </div>

    @else
    <!-- ========================================================= -->
    <!-- 2. SUBJECT ROADMAP VIEW (Subject Selected)                -->
    <!-- ========================================================= -->
    <div x-data="studentCurriculumApp()" class="space-y-8">

        <!-- Top Navigation / Back to Subjects -->
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('guided-learning.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-gray-700 transition-colors shadow-2xs">
                <i class="fas fa-arrow-left text-[10px]"></i>
                <span>Choose Another Subject</span>
            </a>

            <!-- Quick Subject Switcher Pills -->
            @if(isset($availableSubjects) && $availableSubjects->isNotEmpty())
            <div class="hidden sm:flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                @foreach($availableSubjects as $sub)
                <a href="{{ route('guided-learning.index', ['subject_id' => \App\Services\UrlObfuscator::encode($sub->id)]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentSubject->id === $sub->id ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                    {{ $sub->name }}
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Hero Header for the Selected Subject: Harmonized Light & Dark Mode -->
        <div class="bg-gray-100/90 dark:bg-gray-900 rounded-3xl p-6 sm:p-8 text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-gray-800 relative overflow-hidden transition-colors duration-200">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-white/10 text-xs font-semibold backdrop-blur-md mb-3 text-blue-700 dark:text-blue-200 border border-gray-200/80 dark:border-white/10 shadow-2xs">
                        <i class="fas fa-bookmark text-blue-600 dark:text-blue-400"></i> Subject Learning Roadmap
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                        {{ $currentSubject->name }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-300 max-w-xl mt-2 leading-relaxed">
                        Follow the sequential learning path below. Every checkpoint includes verified explanations, worked exemplars, and self-assessment quizzes.
                    </p>
                </div>

                <!-- Subject Progress Card -->
                <div class="bg-white dark:bg-white/10 backdrop-blur-md p-5 rounded-2xl border border-gray-200 dark:border-white/10 text-center min-w-[190px] shadow-sm">
                    <div class="text-3xl font-black text-blue-600 dark:text-blue-400" x-text="(stats.overall_percentage || 0) + '%'">
                        {{ $curriculumData['stats']['overall_percentage'] ?? 0 }}%
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-300 font-medium mt-1">Curriculum Completed</div>
                    <div class="w-full bg-gray-200 dark:bg-white/20 h-2.5 rounded-full mt-3 overflow-hidden">
                        <div class="bg-blue-600 dark:bg-blue-500 h-full rounded-full transition-all duration-500"
                            :style="'width: ' + (stats.overall_percentage || 0) + '%'"
                            style="width: {{ $curriculumData['stats']['overall_percentage'] ?? 0 }}%;"></div>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 font-medium">
                        <span x-text="stats.completed_indicators || 0">{{ $curriculumData['stats']['completed_indicators'] ?? 0 }}</span> of 
                        <span x-text="stats.total_indicators || 0">{{ $curriculumData['stats']['total_indicators'] ?? 0 }}</span> Checkpoints
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($curriculumData['strands']))
        
        <!-- Next Up / Resume Learning Card -->
        <template x-if="nextTopic">
            <div class="bg-blue-50/30 dark:bg-gray-800 border border-blue-200/80 dark:border-blue-900/50 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="nextTopic.title"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="nextTopic.strandTitle"></p>
                </div>

                <a :href="'/guided-learning/topic/' + (nextTopic.encoded_id || nextTopic.id)"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition-all flex-shrink-0">
                    <span>Resume Learning</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </template>

        <!-- Toolbar: Search, Filters & Expand Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Search checkpoint topics..."
                       class="w-full h-10 pl-9 pr-8 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all">
                <button x-show="searchQuery" @click="searchQuery = ''" type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Filter Tabs & Expand All -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-xs">
                    <button type="button"
                            @click="activeFilter = 'all'"
                            :class="activeFilter === 'all' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        All
                    </button>
                    <button type="button"
                            @click="activeFilter = 'in_progress'"
                            :class="activeFilter === 'in_progress' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        In Progress
                    </button>
                    <button type="button"
                            @click="activeFilter = 'completed'"
                            :class="activeFilter === 'completed' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        Completed
                    </button>
                    <button type="button"
                            @click="activeFilter = 'not_started'"
                            :class="activeFilter === 'not_started' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        Remaining
                    </button>
                </div>

                <button type="button"
                        @click="toggleAllStrands()"
                        class="px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition-all flex-shrink-0 shadow-2xs">
                    <i class="fas" :class="allOpen ? 'fa-compress-alt' : 'fa-expand-alt'"></i>
                    <span class="ml-1" x-text="allOpen ? 'Collapse' : 'Expand'"></span>
                </button>
            </div>
        </div>

        <!-- Level / Grade Tabs (Visible when curriculum covers multiple levels e.g. Basic 7, Basic 8, Basic 9) -->
        @if(isset($curriculumData['grade_levels']) && count($curriculumData['grade_levels']) > 1)
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar pt-1">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 mr-1 flex items-center gap-1.5 flex-shrink-0 uppercase tracking-wider text-[11px]">
                <i class="fas fa-layer-group text-blue-600 dark:text-blue-400"></i> Grade Level:
            </span>
            <button type="button"
                    @click="selectedGrade = 'all'"
                    :class="selectedGrade === 'all' 
                        ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' 
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap shadow-2xs flex items-center gap-1.5">
                <span>All Levels</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                      :class="selectedGrade === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                    {{ $curriculumData['stats']['total_indicators'] }}
                </span>
            </button>
            @foreach($curriculumData['grade_levels'] as $gl)
            <button type="button"
                    @click="selectedGrade = '{{ addslashes($gl['label']) }}'"
                    :class="selectedGrade === '{{ addslashes($gl['label']) }}' 
                        ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' 
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap shadow-2xs flex items-center gap-1.5">
                <span>{{ $gl['label'] }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                      :class="selectedGrade === '{{ addslashes($gl['label']) }}' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                    {{ $gl['total_topics'] }}
                </span>
            </button>
            @endforeach
        </div>
        @endif

        <!-- FreeCodeCamp Style Learning Roadmap Grouped by Grade Level -->
        <div class="space-y-10">
            @foreach($curriculumData['grade_levels'] as $gradeLevel)
            <div class="space-y-4" x-show="isGradeLevelVisible('{{ addslashes($gradeLevel['label']) }}')">
                
                @if(count($curriculumData['grade_levels']) > 1)
                <!-- Level Section Header Banner with Clear Distinction -->
                <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-gray-100/90 to-white dark:from-gray-850 dark:to-gray-800 border-2 border-gray-200 dark:border-gray-700 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-blue-600/20 flex-shrink-0">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                    {{ $gradeLevel['label'] }}
                                </h2>
                                <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    {{ count($gradeLevel['strands']) }} Strands • {{ $gradeLevel['total_topics'] }} Checkpoints
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Curriculum milestones and checkpoints for {{ $gradeLevel['label'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Level Completion Metric -->
                    <div class="flex items-center gap-4 self-end sm:self-center pl-13 sm:pl-0">
                        <div class="flex flex-col items-end min-w-[130px]">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700 dark:text-gray-200">
                                <span x-text="getGradeCompleted('{{ addslashes($gradeLevel['label']) }}')">{{ $gradeLevel['completed_topics'] }}</span>
                                <span class="text-gray-400">/</span>
                                <span>{{ $gradeLevel['total_topics'] }} Completed</span>
                            </div>
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 h-2 rounded-full mt-1.5 overflow-hidden">
                                <div class="bg-blue-600 h-full rounded-full transition-all duration-300"
                                     :style="'width: ' + getGradePercentage('{{ addslashes($gradeLevel['label']) }}', {{ $gradeLevel['total_topics'] }}) + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Strands within this Grade Level -->
                <div class="space-y-4">
                    @foreach($gradeLevel['strands'] as $strand)
                    <div class="bg-gray-50/70 dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs transition-all"
                         x-show="isStrandVisible({{ $strand['id'] }}, '{{ addslashes($strand['grade_label'] ?? '') }}')">
                        
                        <!-- Strand Header (Unnumbered, clean title) -->
                        <div class="p-5 sm:p-6 bg-gray-100/80 dark:bg-gray-750 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer border-b border-gray-200/80 dark:border-gray-700"
                             @click="strandsOpen[{{ $strand['id'] }}] = !strandsOpen[{{ $strand['id'] }}]">
                            
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center text-sm border border-gray-200 dark:border-gray-600 shadow-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base sm:text-lg flex items-center gap-2 flex-wrap">
                                        {{ $strand['title'] }}
                                        @if(!empty($strand['grade_label']))
                                            <span class="text-[11px] px-2.5 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full font-semibold">
                                                {{ $strand['grade_label'] }}
                                            </span>
                                        @endif
                                    </h3>
                                </div>
                            </div>

                            <!-- Strand Progress & Accordion Arrow -->
                            <div class="flex items-center justify-between sm:justify-end gap-5 pl-14 sm:pl-0">
                                <div class="flex flex-col items-end min-w-[120px]">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700 dark:text-gray-200">
                                        <span x-text="getStrandCompleted({{ $strand['id'] }})">{{ $strand['completed_topics'] }}</span>
                                        <span class="text-gray-400">/</span>
                                        <span>{{ $strand['total_topics'] }} Completed</span>
                                    </div>
                                    <div class="w-28 bg-gray-200 dark:bg-gray-700 h-2 rounded-full mt-1.5 overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full transition-all duration-300"
                                             :style="'width: ' + getStrandPercentage({{ $strand['id'] }}, {{ $strand['total_topics'] }}) + '%'"></div>
                                    </div>
                                </div>

                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-transparent text-gray-500 flex items-center justify-center text-xs transition-transform duration-200"
                                     :class="{'rotate-180': strandsOpen[{{ $strand['id'] }}]}">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Strands & Checklist Items -->
                        <div x-show="strandsOpen[{{ $strand['id'] }}]" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach($strand['sub_strands'] as $subStrand)
                            <div class="p-5 sm:p-6" x-show="isSubStrandVisible({{ $subStrand['id'] }})">
                                <div class="mb-3">
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center text-[10px]">
                                            <i class="fas fa-bookmark"></i>
                                        </span>
                                        {{ $subStrand['title'] }}
                                    </h4>
                                </div>

                                <!-- Indicators (Checklist Rows - Entire row clickable, clean unnumbered titles) -->
                                <div class="space-y-2.5 pl-2 sm:pl-7">
                                    @foreach($subStrand['indicators'] as $ind)
                                    <a href="{{ route('guided-learning.topic', $ind['encoded_id'] ?? $ind['id']) }}"
                                       class="flex items-center justify-between p-3.5 rounded-2xl transition-all border group cursor-pointer block text-left"
                                       x-show="isIndicatorVisible({{ $ind['id'] }}, '{{ addslashes($ind['title']) }}')"
                                       :class="{
                                          'bg-gray-50/60 dark:bg-gray-800/40 border-gray-200/70 dark:border-gray-700/60 opacity-80 hover:opacity-100': indicatorsStatus[{{ $ind['id'] }}] === 'completed',
                                          'bg-blue-50/40 dark:bg-blue-950/20 border-2 border-blue-500 dark:border-blue-600 shadow-xs': indicatorsStatus[{{ $ind['id'] }}] === 'in_progress',
                                          'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-2xs hover:border-blue-500 hover:shadow-xs': !indicatorsStatus[{{ $ind['id'] }}] || indicatorsStatus[{{ $ind['id'] }}] === 'not_started'
                                       }">
                                        
                                        <!-- Left: Checkbox Icon + Topic Title -->
                                        <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                            <!-- FreeCodeCamp Completion Circle (Clickable Toggle) -->
                                            <button type="button" 
                                                    @click.stop.prevent="toggleIndicator({{ $ind['id'] }}, {{ $strand['id'] }})"
                                                    title="Click to toggle status"
                                                    class="w-7 h-7 rounded-full flex items-center justify-center transition-all transform active:scale-90 flex-shrink-0 shadow-2xs"
                                                    :class="{
                                                        'border-2 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-400 bg-gray-100 dark:bg-gray-700': indicatorsStatus[{{ $ind['id'] }}] === 'completed',
                                                        'border-2 border-blue-600 text-blue-600 bg-blue-50 dark:bg-blue-900/40 shadow-xs shadow-blue-600/20': indicatorsStatus[{{ $ind['id'] }}] === 'in_progress',
                                                        'border-2 border-gray-400 dark:border-gray-500 hover:border-blue-600 bg-white dark:bg-transparent': !indicatorsStatus[{{ $ind['id'] }}] || indicatorsStatus[{{ $ind['id'] }}] === 'not_started'
                                                    }">
                                                <template x-if="indicatorsStatus[{{ $ind['id'] }}] === 'completed'">
                                                    <i class="fas fa-check text-xs"></i>
                                                </template>
                                                <template x-if="indicatorsStatus[{{ $ind['id'] }}] === 'in_progress'">
                                                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></div>
                                                </template>
                                            </button>

                                            <!-- Topic Title (Clean, unnumbered - Bold for active, dimmed for completed) -->
                                            <div class="flex items-center gap-2.5 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                <span class="text-sm truncate"
                                                      :class="indicatorsStatus[{{ $ind['id'] }}] === 'completed'
                                                          ? 'line-through text-gray-400 dark:text-gray-500 font-normal'
                                                          : 'text-gray-900 dark:text-white font-bold'">
                                                    {{ $ind['title'] }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Right: Review Button (for completed) / In Progress Badge / Arrow -->
                                        <div class="flex items-center gap-2.5 pl-3 flex-shrink-0">
                                            <!-- Review Button: Only for completed items -->
                                            <span x-show="indicatorsStatus[{{ $ind['id'] }}] === 'completed'"
                                                  class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all inline-flex items-center bg-gray-100/90 group-hover:bg-gray-200 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                                Review
                                                <i class="fas fa-arrow-right text-[10px] ml-1.5"></i>
                                            </span>

                                            <!-- In Progress Badge -->
                                            <span x-show="indicatorsStatus[{{ $ind['id'] }}] === 'in_progress'"
                                                  class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 border-red-200 dark:border-red-800/40">
                                                In Progress
                                            </span>

                                            <!-- Subtle Chevron for uncompleted items -->
                                            <i x-show="indicatorsStatus[{{ $ind['id'] }}] !== 'completed'"
                                               class="fas fa-chevron-right text-xs text-gray-300 dark:text-gray-600 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all"></i>
                                        </div>
                                    </a>
                                    @endforeach
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
        <div class="bg-gray-100/90 dark:bg-gray-900 rounded-3xl p-12 text-center border border-gray-200 dark:border-gray-700 shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-full bg-white dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl border border-gray-200 dark:border-transparent">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Curriculum in Preparation</h3>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                The curriculum for {{ $currentSubject->name }} is being verified. Check back shortly!
            </p>
        </div>
        @endif
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function studentCurriculumApp() {
        return {
            searchQuery: '',
            activeFilter: 'all', // 'all', 'in_progress', 'completed', 'not_started'
            selectedGrade: 'all',
            allOpen: false,
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

            strandsOpen: {
                @if(!empty($curriculumData['strands']))
                    @foreach($curriculumData['strands'] as $strand)
                        {{ $strand['id'] }}: false,
                    @endforeach
                @endif
            },

            // Flat indicators metadata for nextTopic calculation and live filtering
            indicatorsList: [
                @if(!empty($curriculumData['strands']))
                    @foreach($curriculumData['strands'] as $strand)
                        @foreach($strand['sub_strands'] as $subStrand)
                            @foreach($subStrand['indicators'] as $ind)
                                {
                                    id: {{ $ind['id'] }},
                                    encoded_id: '{{ $ind['encoded_id'] ?? $ind['id'] }}',
                                    title: '{{ addslashes($ind['title']) }}',
                                    strandId: {{ $strand['id'] }},
                                    strandTitle: '{{ addslashes($strand['title']) }}',
                                    gradeLabel: '{{ addslashes($strand['grade_label'] ?? '') }}',
                                    subStrandId: {{ $subStrand['id'] }},
                                },
                            @endforeach
                        @endforeach
                    @endforeach
                @endif
            ],

            get nextTopic() {
                const inProgress = this.indicatorsList.find(i => this.indicatorsStatus[i.id] === 'in_progress');
                if (inProgress) return inProgress;
                return this.indicatorsList.find(i => (!this.indicatorsStatus[i.id] || this.indicatorsStatus[i.id] === 'not_started')) || null;
            },

            toggleAllStrands() {
                this.allOpen = !this.allOpen;
                for (let id in this.strandsOpen) {
                    this.strandsOpen[id] = this.allOpen;
                }
            },

            getStrandCompleted(strandId) {
                const strandIndicators = this.indicatorsList.filter(i => i.strandId === strandId);
                return strandIndicators.filter(i => this.indicatorsStatus[i.id] === 'completed').length;
            },

            getStrandPercentage(strandId, totalTopics) {
                if (!totalTopics || totalTopics === 0) return 0;
                const completed = this.getStrandCompleted(strandId);
                return Math.round((completed / totalTopics) * 100);
            },

            getGradeCompleted(gradeLabel) {
                const items = this.indicatorsList.filter(i => i.gradeLabel === gradeLabel);
                return items.filter(i => this.indicatorsStatus[i.id] === 'completed').length;
            },

            getGradePercentage(gradeLabel, total) {
                if (!total || total === 0) return 0;
                return Math.round((this.getGradeCompleted(gradeLabel) / total) * 100);
            },

            isGradeLevelVisible(gradeLabel) {
                if (this.selectedGrade !== 'all' && this.selectedGrade !== gradeLabel) {
                    return false;
                }
                const items = this.indicatorsList.filter(i => i.gradeLabel === gradeLabel);
                return items.some(i => this.isIndicatorVisible(i.id, i.title));
            },

            isIndicatorVisible(id, title) {
                const status = this.indicatorsStatus[id] || 'not_started';

                if (this.activeFilter === 'completed' && status !== 'completed') return false;
                if (this.activeFilter === 'in_progress' && status !== 'in_progress') return false;
                if (this.activeFilter === 'not_started' && status !== 'not_started') return false;

                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    return title.toLowerCase().includes(q);
                }

                return true;
            },

            isSubStrandVisible(subStrandId) {
                const items = this.indicatorsList.filter(i => i.subStrandId === subStrandId);
                return items.some(i => this.isIndicatorVisible(i.id, i.title));
            },

            isStrandVisible(strandId, gradeLabel) {
                if (this.selectedGrade !== 'all' && gradeLabel && this.selectedGrade !== gradeLabel) {
                    return false;
                }
                const items = this.indicatorsList.filter(i => i.strandId === strandId);
                return items.some(i => this.isIndicatorVisible(i.id, i.title));
            },

            async toggleIndicator(id, strandId) {
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
    @endif
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    window.__aiContext = {
        page: 'guided-learning',
        @if(!($isSelectingSubject ?? true) && isset($currentSubject))
            subject: @json($currentSubject->name),
        @endif
        @if(isset($currentLevelGroup))
            level: @json($currentLevelGroup->title ?? ''),
        @endif
    };
</script>
@endpush

@endsection
