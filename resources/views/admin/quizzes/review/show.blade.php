@extends('layouts.admin')

@section('title', 'Quiz Attempt Review')
@section('page-title', 'Attempt Forensic Review')
@section('page-description', 'Detailed investigation of a student\'s quiz session and security events')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <a href="{{ route('admin.quizzes.review.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to All Attempts
        </a>
        <div class="flex items-center gap-3">
            @if(!$attempt->failed_due_to_violation)
            <form action="{{ route('admin.quizzes.review.invalidate', $attempt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to invalidate this attempt? This will set the score to 0 and mark it as a violation.')">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium shadow-sm">
                    <i class="fas fa-ban mr-2"></i> Invalidate Attempt
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Student Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Student Information</h3>
            <div class="flex items-center">
                <x-user-avatar :user="$attempt->user" :size="48" />
                <div class="ml-4">
                    <div class="text-lg font-bold text-gray-900">{{ $attempt->user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $attempt->user->email }}</div>
                    <div class="text-xs text-gray-400 mt-1">User ID: {{ $attempt->user_id }}</div>
                </div>
            </div>
        </div>

        <!-- Quiz Results -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Quiz Results</h3>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Score</span>
                <span class="text-lg font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($attempt->score_percentage, 1) }}%
                </span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Correct Answers</span>
                <span class="text-sm font-medium">{{ $attempt->correct_answers }} / {{ $attempt->total_questions }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Time Taken</span>
                <span class="text-sm font-medium">{{ floor($attempt->time_taken_seconds / 60) }}m {{ $attempt->time_taken_seconds % 60 }}s</span>
            </div>
        </div>

        <!-- Proctoring Report -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Security & Proctoring</h3>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full {{ $attempt->trust_score < 70 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }} flex items-center justify-center mr-3">
                        <i class="fas {{ $attempt->trust_score < 70 ? 'fa-shield-alt' : 'fa-check-circle' }}"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold {{ $attempt->trust_score < 70 ? 'text-red-800' : 'text-green-800' }}">TRUST SCORE</div>
                        <div class="text-xs text-gray-500">Security assessment</div>
                    </div>
                </div>
                <div class="text-2xl font-black {{ $attempt->trust_score < 70 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $attempt->trust_score }}%
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Violation Points</span>
                    <span class="font-bold text-red-600">+{{ 100 - $attempt->trust_score }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full {{ $attempt->trust_score < 70 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $attempt->trust_score }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Analytics Alerts -->
    @php $alerts = $attempt->proctoring_insights; @endphp
    @if(count($alerts) > 0)
    <div class="grid grid-cols-1 gap-4">
        @foreach($alerts as $alert)
        <div class="flex items-center p-4 rounded-xl border border-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-200 bg-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-50">
            <div class="flex-shrink-0 mr-4">
                <div class="h-10 w-10 rounded-full bg-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-100 flex items-center justify-center text-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-600">
                    <i class="fas fa-robot text-lg"></i>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-bold text-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-900">{{ $alert['label'] }}</h4>
                <p class="text-xs text-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-700">{{ $alert['details'] }}</p>
            </div>
            <div class="ml-auto">
                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-200 text-{{ $alert['severity'] === 'high' ? 'red' : 'yellow' }}-800">
                    {{ $alert['severity'] }} Risk
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Forensic Timeline -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Security Event Timeline</h2>
            <p class="text-sm text-gray-500">Chronological log of activities detected during the quiz</p>
        </div>
        <div class="p-6">
            <div class="relative">
                <!-- Timeline Line -->
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                <div class="space-y-8 relative">
                    <!-- Session Start -->
                    <div class="flex items-start">
                        <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white ring-4 ring-white">
                            <i class="fas fa-play text-xs"></i>
                        </div>
                        <div class="ml-12">
                            <div class="text-sm font-bold text-gray-900">Quiz Started</div>
                            <div class="text-xs text-gray-500">{{ $attempt->started_at->format('H:i:s • M d, Y') }}</div>
                        </div>
                    </div>

                    @forelse($violations as $violation)
                    <div class="flex items-start">
                        @php
                            $icon = 'fa-exclamation-circle';
                            $colorClass = 'bg-yellow-500';
                            $label = 'Security Alert';

                            switch($violation->violation_type) {
                                case 'tab_switch':
                                case 'focus_loss':
                                    $icon = 'fa-external-link-alt';
                                    $colorClass = 'bg-orange-500';
                                    $label = 'Focus Lost (Tab Switch)';
                                    break;
                                case 'screenshot_attempt':
                                    $icon = 'fa-camera';
                                    $colorClass = 'bg-red-600';
                                    $label = 'Screenshot Attempted';
                                    break;
                                case 'devtools_open':
                                    $icon = 'fa-code';
                                    $colorClass = 'bg-purple-600';
                                    $label = 'Developer Tools Detected';
                                    break;
                                case 'bot_detection':
                                    $icon = 'fa-robot';
                                    $colorClass = 'bg-red-800';
                                    $label = 'Bot Activity / Honey Pot';
                                    break;
                                case 'appeal_request':
                                    $icon = 'fa-comment-alt';
                                    $colorClass = 'bg-blue-600';
                                    $label = 'Appeal Requested';
                                    break;
                                case 'session_conflict':
                                    $icon = 'fa-users-slash';
                                    $colorClass = 'bg-red-700';
                                    $label = 'Session Conflict (Multi-device)';
                                    break;
                            }
                        @endphp
                        <div class="absolute left-0 w-8 h-8 rounded-full {{ $colorClass }} flex items-center justify-center text-white ring-4 ring-white shadow-sm">
                            <i class="fas {{ $icon }} text-xs"></i>
                        </div>
                        <div class="ml-12 bg-gray-50 rounded-lg p-3 border border-gray-100 flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-bold text-gray-900">{{ $label }}</span>
                                <span class="text-xs text-gray-500">{{ $violation->occurred_at->format('H:i:s') }}</span>
                            </div>
                            <div class="text-sm text-gray-600">{{ $violation->details }}</div>
                            @if($violation->points > 0)
                                <div class="mt-2 text-xs font-semibold text-red-600">Severity Points: {{ $violation->points }}</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="ml-12 text-sm text-gray-500 italic py-4">No suspicious events recorded during this session.</div>
                    @endforelse

                    <!-- Session End -->
                    @if($attempt->completed_at)
                    <div class="flex items-start">
                        <div class="absolute left-0 w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white ring-4 ring-white">
                            <i class="fas fa-flag-checkered text-xs"></i>
                        </div>
                        <div class="ml-12">
                            <div class="text-sm font-bold text-gray-900">Quiz Submitted</div>
                            <div class="text-xs text-gray-500">{{ $attempt->completed_at->format('H:i:s • M d, Y') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
