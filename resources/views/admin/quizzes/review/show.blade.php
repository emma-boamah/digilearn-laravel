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

    
    <!-- Response & Grading Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Response & Grading</h2>
                <p class="text-sm text-gray-500">Review student responses and award marks manually</p>
            </div>
            @if($attempt->status === 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 uppercase tracking-wider">
                    <i class="fas fa-clock mr-1"></i> Needs Grading
                </span>
            @elseif($attempt->status === 'graded')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 uppercase tracking-wider">
                    <i class="fas fa-check-double mr-1"></i> Graded
                </span>
            @endif
            <button type="button" id="autoSuggestBtn" class="ml-4 inline-flex items-center px-4 py-2 border border-blue-600 rounded-lg text-sm font-bold text-blue-600 hover:bg-blue-50 transition-colors shadow-sm">
                <i class="fas fa-magic mr-2"></i> Auto-Suggest Marks
            </button>
        </div>
        
        <form action="{{ route('admin.quizzes.review.grade', $attempt->id) }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-8">
                @php 
                    $userAnswers = is_array($attempt->answers) ? $attempt->answers : (json_decode($attempt->answers, true) ?? []);
                    $questions = is_array($attempt->question_details) ? $attempt->question_details : (json_decode($attempt->question_details, true) ?? []);
                    $gradingDetails = is_array($attempt->grading_details) ? $attempt->grading_details : (json_decode($attempt->grading_details, true) ?? []);
                    
                    $sanitizeMath = function($html) {
                        $html = str_replace('contenteditable="true"', 'contenteditable="false" read-only', $html);
                        $html = str_replace('tabindex="0"', 'tabindex="-1"', $html);
                        return $html;
                    };
                @endphp

                @foreach($questions as $qIdx => $q)
                    <div class="border border-gray-100 rounded-xl overflow-hidden bg-gray-50/30">
                        <div class="bg-gray-100/50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                            <span class="font-bold text-gray-800">Question {{ $qIdx + 1 }}</span>
                            @if(!empty($q['points']))
                                <span class="text-xs font-semibold px-2 py-1 bg-white rounded border border-gray-200 text-gray-600">Total: {{ $q['points'] }} Marks</span>
                            @endif
                        </div>

                        <div class="p-4 space-y-6">
                            <!-- Main Question Text (if any) -->
                            @if(!empty(trim(strip_tags($q['question'] ?? ''))))
                                <div class="text-gray-800 font-medium mb-4">{!! $sanitizeMath($q['question']) !!}</div>
                            @endif

                            @if(!empty($q['sub_questions']))
                                @foreach($q['sub_questions'] as $sIdx => $sub)
                                    <div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="font-bold text-blue-600">{{ $sub['label'] }}) {!! $sanitizeMath($sub['text']) !!}</div>
                                            <span class="text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded">[{{ $sub['points'] }} pts]</span>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            <!-- Student Response -->
                                            <div class="space-y-2">
                                                <div class="text-[10px] uppercase font-black text-gray-400 tracking-widest">Student's Response</div>
                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 text-sm leading-relaxed text-gray-700 min-h-[100px]">
                                                    @php
                                                        $ans = $userAnswers[$qIdx] ?? null;
                                                        $subAns = is_array($ans) && isset($ans[$sIdx]) ? $ans[$sIdx] : (is_string($ans) ? $ans : null);
                                                    @endphp
                                                    @if($subAns)
                                                        {!! $sanitizeMath($subAns) !!}
                                                    @else
                                                        <span class="italic text-gray-400">No response provided.</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Marking Scheme -->
                                            <div class="space-y-2">
                                                <div class="text-[10px] uppercase font-black text-green-500 tracking-widest">Marking Scheme (Model Answer)</div>
                                                <div class="p-4 bg-green-50 rounded-lg border border-green-100 text-sm leading-relaxed text-green-900 min-h-[100px]">
                                                    {!! $sanitizeMath($sub['sample_answer'] ?? 'No marking scheme provided.') !!}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Awarding Marks -->
                                        <div class="mt-4 pt-4 border-t border-gray-50 flex flex-wrap items-end gap-6">
                                            <div class="w-32">
                                                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Award Marks</label>
                                                <div class="relative">
                                                    <input type="number" step="0.5" min="0" max="{{ $sub['points'] }}" 
                                                           name="marks[{{ $qIdx }}_{{ $sIdx }}]" 
                                                           value="{{ $gradingDetails['marks']["{$qIdx}_{$sIdx}"] ?? '' }}"
                                                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-bold pr-12"
                                                           placeholder="0.0">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400">/ {{ $sub['points'] }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Feedback/Comment (Optional)</label>
                                                <input type="text" name="feedback[{{ $qIdx }}_{{ $sIdx }}]"
                                                       value="{{ $gradingDetails['feedback']["{$qIdx}_{$sIdx}"] ?? '' }}"
                                                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                       placeholder="Explain deductions or provide praise...">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- Standard Essay Question -->
                                <div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <div class="text-[10px] uppercase font-black text-gray-400 tracking-widest">Student's Response</div>
                                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 text-sm leading-relaxed text-gray-700 min-h-[100px]">
                                                {!! $sanitizeMath($userAnswers[$qIdx] ?? 'No response provided.') !!}
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="text-[10px] uppercase font-black text-green-500 tracking-widest">Marking Scheme</div>
                                            <div class="p-4 bg-green-50 rounded-lg border border-green-100 text-sm leading-relaxed text-green-900 min-h-[100px]">
                                                {!! $sanitizeMath($q['correct_answer'] ?? 'No marking scheme provided.') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-50 flex flex-wrap items-end gap-6">
                                        <div class="w-32">
                                            <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Award Marks</label>
                                            <div class="relative">
                                                <input type="number" step="0.5" min="0" max="{{ $q['points'] ?? 10 }}" 
                                                       name="marks[{{ $qIdx }}]" 
                                                       value="{{ $gradingDetails['marks'][$qIdx] ?? '' }}"
                                                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-bold pr-12"
                                                       placeholder="0.0">
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400">/ {{ $q['points'] ?? 10 }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Feedback/Comment</label>
                                            <input type="text" name="feedback[{{ $qIdx }}]"
                                                   value="{{ $gradingDetails['feedback'][$qIdx] ?? '' }}"
                                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                   placeholder="Grade justification...">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="pt-6 border-t border-gray-100">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Overall Feedback for the Student</label>
                    <textarea name="overall_feedback" rows="3" 
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                              placeholder="Great job! You showed deep understanding of... (This will appear on their results page)">{{ $gradingDetails['overall_feedback'] ?? '' }}</textarea>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-all font-bold shadow-lg shadow-blue-200">
                        <i class="fas fa-check-circle mr-2"></i> Save Changes & Update Scores
                    </button>
                </div>
            </div>
        </form>
    </div>

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

@push('scripts')
<script>
document.getElementById('autoSuggestBtn').addEventListener('click', async function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Analyzing...';
    
    try {
        const response = await fetch('{{ route('admin.quizzes.review.auto-grade', $attempt->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.marks) {
            // Fill in marks and individual feedback
            for (const key in data.marks) {
                const markInput = document.querySelector(`input[name="marks[${key}]"]`);
                const feedbackInput = document.querySelector(`input[name="feedback[${key}]"]`);
                
                if (markInput) markInput.value = data.marks[key];
                if (feedbackInput) feedbackInput.value = data.feedback[key];
            }
            
            // Fill in overall feedback with analysis if available
            const overallFeedback = document.querySelector('textarea[name="overall_feedback"]');
            if (overallFeedback) {
                let analysisText = "";
                if (data.analysis) {
                    if (data.analysis.strengths && data.analysis.strengths.length > 0) {
                        analysisText += "Strengths detected: " + data.analysis.strengths.join(", ") + ". ";
                    }
                    if (data.analysis.weaknesses && data.analysis.weaknesses.length > 0) {
                        analysisText += "Areas for improvement: " + data.analysis.weaknesses.join(", ") + ". ";
                    }
                }
                overallFeedback.value = (analysisText + (overallFeedback.value || "")).trim();
            }
            
            // Notify success
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-xl shadow-2xl z-50 animate-bounce';
            toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Suggestions applied! Please review and save.';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    } catch (error) {
        console.error('Auto-grade error:', error);
        alert('Failed to generate suggestions. Please try manual grading.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});
</script>
@endpush
