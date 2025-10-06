@extends('layouts.admin')

@section('title', 'Student Progress Details - ' . $user->name)
@section('page-title', 'Student Progress Details')
@section('page-description', 'Detailed progress information for ' . $user->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Student Info Header -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <p class="text-sm text-gray-500">Grade: {{ $user->grade ?? 'Not set' }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Joined</div>
                <div class="font-semibold">{{ $user->created_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    @if($currentProgress)
    <!-- Current Progress Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Current Level</h3>
                    <p class="text-gray-600">{{ ucwords(str_replace('-', ' ', $currentProgress->current_level)) }}</p>
                </div>
                <div class="text-2xl">📚</div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progress</span>
                    <span>{{ number_format($currentProgress->completion_percentage, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $currentProgress->completion_percentage }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Lessons Completed</h3>
                    <p class="text-gray-600">{{ $currentProgress->completed_lessons }}/{{ $currentProgress->total_lessons_in_level }}</p>
                </div>
                <div class="text-2xl">🎬</div>
            </div>
            <div class="mt-4">
                <div class="text-sm text-gray-600">
                    Time spent: <span class="font-semibold">{{ $analytics['engagement']['time_spent_formatted'] ?? '0m' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Quiz Performance</h3>
                    <p class="text-gray-600">{{ $currentProgress->completed_quizzes }}/{{ $currentProgress->total_quizzes_in_level }} passed</p>
                </div>
                <div class="text-2xl">✍️</div>
            </div>
            <div class="mt-4">
                <div class="text-sm text-gray-600">
                    Average score: <span class="font-semibold">{{ number_format($currentProgress->average_quiz_score, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    @if(isset($analytics))
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Engagement Metrics -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Metrics</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Learning Streak</span>
                    <span class="font-semibold text-orange-600">{{ $analytics['engagement']['current_streak'] ?? 0 }} days</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Best Streak</span>
                    <span class="font-semibold text-green-600">{{ $analytics['engagement']['longest_streak'] ?? 0 }} days</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Time in Level</span>
                    <span class="font-semibold">{{ $analytics['level_info']['duration'] ?? 'Just started' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Last Activity</span>
                    <span class="font-semibold">{{ $currentProgress->last_activity_at ? $currentProgress->last_activity_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
        </div>

        <!-- Achievement Milestones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Achievements</h3>
            @if(isset($analytics['milestones']) && count($analytics['milestones']) > 0)
                <div class="space-y-3">
                    @foreach($analytics['milestones'] as $milestone)
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="text-2xl">{{ $milestone['icon'] }}</div>
                            <div>
                                <div class="font-semibold text-gray-900">{{ $milestone['title'] }}</div>
                                <div class="text-sm text-gray-600">
                                    @if($milestone['type'] === 'lessons')
                                        Completed {{ $milestone['count'] }} lessons
                                    @elseif($milestone['type'] === 'quizzes')
                                        @if($milestone['count'] >= 80)
                                            Achieved {{ $milestone['count'] }}% average score
                                        @else
                                            Passed {{ $milestone['count'] }} quizzes
                                        @endif
                                    @elseif($milestone['type'] === 'streak')
                                        {{ $milestone['count'] }}-day learning streak
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-500 py-4">
                    <div class="text-3xl mb-2">🏆</div>
                    <p>No achievements yet. Keep learning!</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Progress History -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress History</h3>
        @if($progressRecords->count() > 0)
            <div class="space-y-4">
                @foreach($progressRecords as $progress)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ ucwords(str_replace('-', ' ', $progress->current_level)) }}</h4>
                                <p class="text-sm text-gray-600">{{ $progress->level_started_at ? 'Started ' . $progress->level_started_at->format('M d, Y') : 'Not started' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Completion</div>
                                <div class="font-semibold">{{ number_format($progress->completion_percentage, 1) }}%</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Lessons:</span>
                                <span class="font-semibold">{{ $progress->completed_lessons }}/{{ $progress->total_lessons_in_level }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Quizzes:</span>
                                <span class="font-semibold">{{ $progress->completed_quizzes }}/{{ $progress->total_quizzes_in_level }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Avg Score:</span>
                                <span class="font-semibold">{{ number_format($progress->average_quiz_score, 1) }}%</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Status:</span>
                                @if($progress->level_completed)
                                    <span class="text-green-600 font-semibold">Completed</span>
                                @elseif($progress->eligible_for_next_level)
                                    <span class="text-blue-600 font-semibold">Ready to Progress</span>
                                @else
                                    <span class="text-orange-600 font-semibold">In Progress</span>
                                @endif
                            </div>
                        </div>

                        @if($progress->level_completed_at)
                            <div class="mt-3 text-sm text-gray-600">
                                Completed on {{ $progress->level_completed_at->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-gray-500 py-8">
                <div class="text-4xl mb-4">📊</div>
                <p>No progress records found for this student.</p>
            </div>
        @endif
    </div>

    <!-- Level Progression History -->
    @if($progressionHistory && $progressionHistory->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Level Progression History</h3>
        <div class="space-y-4">
            @foreach($progressionHistory as $progression)
                <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 7l5 5-5 5M6 12h12"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold text-gray-900">
                                {{ ucwords(str_replace('-', ' ', $progression->from_level)) }}
                                <span class="text-gray-500">→</span>
                                {{ ucwords(str_replace('-', ' ', $progression->to_level)) }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $progression->progressed_at->format('M d, Y') }}</span>
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            {{ $progression->lessons_completed }} lessons • {{ $progression->quizzes_passed }} quizzes passed • Final score: {{ number_format($progression->final_score, 1) }}%
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Manual Progression Action -->
    @if($currentProgress && $currentProgress->eligible_for_next_level)
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-green-800">Ready for Progression</h3>
                    <p class="text-green-700">This student has met all requirements and is ready to move to the next level.</p>
                </div>
                <form action="{{ route('admin.progress.user.progress', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="from_level" value="{{ $currentProgress->current_level }}">
                    <input type="hidden" name="to_level" value="{{ ['primary-lower' => 'primary-upper', 'primary-upper' => 'jhs', 'jhs' => 'shs', 'shs' => null][$currentProgress->current_level] }}">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors font-semibold">
                        Progress to Next Level
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endif
    </div>
    @endsection