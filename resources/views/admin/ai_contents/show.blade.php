@extends('layouts.admin')

@section('title', 'AI Content Details — ' . $content->title)
@section('page-title', 'AI Content Details')
@section('page-description', 'Detailed inspection of AI-generated lesson, quiz, and prompt telemetry')

@section('content')
    <div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

        <!-- Top Nav / Back -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.ai-contents.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
                Back to AI Contents List
            </a>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 flex items-center gap-1.5">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    AI Generated {{ ucfirst($content->content_type ?? 'Content') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content Display (2 Columns on Desktop) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Video or Quiz Primary Preview Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900">{{ $content->title }}</h2>
                        @if(isset($content->agent_query) && $content->agent_query)
                            <div class="mt-2 p-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 italic">
                                <span class="font-semibold not-italic text-slate-500">Original Prompt:</span>
                                "{{ $content->agent_query }}"
                            </div>
                        @endif
                    </div>

                    @if($content->content_type === 'video')
                        <div class="aspect-video bg-black flex items-center justify-center">
                            @if($content->external_video_url)
                                <iframe src="{{ $content->external_video_url }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                            @elseif($content->external_video_id)
                                <iframe src="https://www.youtube.com/embed/{{ $content->external_video_id }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                            @else
                                <div class="text-gray-400 text-center p-8">
                                    <i class="fas fa-video-slash text-4xl mb-2"></i>
                                    <p>No video player embed available.</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Summary / Description</h3>
                            <div class="text-sm text-gray-600 leading-relaxed bg-gray-50 p-4 rounded-lg border border-gray-100">
                                {!! nl2br(e($content->description ?? 'No description available.')) !!}
                            </div>
                        </div>

                        @if($content->content_type === 'quiz' && isset($content->questions))
                            <div>
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Generated Quiz Questions</h3>
                                <div class="space-y-3">
                                    @forelse($content->questions as $index => $question)
                                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                            <p class="font-semibold text-gray-900 mb-2">Q{{ $index + 1 }}. {{ $question->question_text ?? $question->question }}</p>
                                            @if(isset($question->options) && is_array($question->options))
                                                <ul class="space-y-1 pl-4 list-disc text-gray-600">
                                                    @foreach($question->options as $opt)
                                                        <li>{{ $opt }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-500 italic">No questions recorded for this quiz.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Agent Telemetry Details (if AgentRequest available) -->
                @if(isset($content->agent_request))
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-microchip text-blue-600"></i>
                            AI Tutor Execution Telemetry
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-500 block">Status</span>
                                <span class="font-semibold text-green-700">{{ strtoupper($content->agent_request->status) }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-500 block">Processing Time</span>
                                <span class="font-semibold text-slate-800">{{ number_format($content->agent_request->processing_time_ms ?? 0) }} ms</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-500 block">Target Subject</span>
                                <span class="font-semibold text-slate-800">{{ $content->agent_request->subject ?? 'General' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-500 block">Grade Level</span>
                                <span class="font-semibold text-slate-800">{{ $content->agent_request->grade_level ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Info Card (1 Column on Desktop) -->
            <div class="space-y-6">

                <!-- User & Creator Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Requesting User</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-lg">
                                {{ strtoupper(substr($content->uploader->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-base">{{ $content->uploader->name ?? 'Student / Guest' }}</h4>
                                <p class="text-xs text-gray-500">{{ $content->uploader->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Metadata List -->
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subject</span>
                            <span class="font-semibold text-gray-900">{{ $content->subject->name ?? ($content->subject_name ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Grade Level</span>
                            <span class="font-semibold text-gray-900">{{ $content->grade_level ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date Created</span>
                            <span class="font-semibold text-gray-900">{{ $content->created_at ? $content->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Views / Attempts</span>
                            <span class="font-semibold text-blue-600">{{ number_format($content->views ?? ($content->attempts_count ?? 0)) }}</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Actions -->
                    <div class="space-y-2">
                        @if($content->content_type === 'video')
                            <a href="{{ route('admin.contents.edit', $content->id) }}" class="w-full py-2.5 px-4 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-900 transition-colors inline-flex items-center justify-center gap-2">
                                <i class="fas fa-edit"></i>
                                Edit AI Content Metadata
                            </a>
                            <form action="{{ route('admin.contents.destroy', $content->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this AI generated content?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-2.5 px-4 bg-red-50 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-100 transition-colors inline-flex items-center justify-center gap-2">
                                    <i class="fas fa-trash-alt"></i>
                                    Delete AI Content
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
