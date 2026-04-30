@extends('layouts.admin')

@section('title', 'Detailed Activity - ' . $user->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex items-center text-sm text-gray-500 space-x-2">
                <a href="{{ route('admin.users') }}" class="hover:text-blue-600 transition-colors">Users</a>
                <span class="text-gray-400">&gt;</span>
                <a href="{{ route('admin.users.show', $user->id) }}" class="hover:text-blue-600 transition-colors">{{ $user->name }}</a>
                <span class="text-gray-400">&gt;</span>
                <span class="text-gray-700 font-medium">Detailed Activity</span>
            </nav>
        </div>

        <div class="flex items-center space-x-4 mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Activity Report: {{ $user->name }}</h1>
        </div>

        <!-- Alpine Tabs -->
        <div x-data="{ activeTab: 'videos' }" class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Tab Headers -->
            <div class="flex border-b border-gray-200">
                <button @click="activeTab = 'videos'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'videos', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'videos'}"
                        class="w-1/3 py-4 px-6 text-center border-b-2 font-medium text-sm flex items-center justify-center transition-colors">
                    <i class="fas fa-video mr-2"></i> Videos Watched
                </button>
                <button @click="activeTab = 'comments'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'comments', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'comments'}"
                        class="w-1/3 py-4 px-6 text-center border-b-2 font-medium text-sm flex items-center justify-center transition-colors">
                    <i class="fas fa-comments mr-2"></i> Comments & Edits
                </button>
                <button @click="activeTab = 'quizzes'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'quizzes', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'quizzes'}"
                        class="w-1/3 py-4 px-6 text-center border-b-2 font-medium text-sm flex items-center justify-center transition-colors">
                    <i class="fas fa-question-circle mr-2"></i> Quiz Attempts
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Videos Tab -->
                <div x-show="activeTab === 'videos'" style="display: none;">
                    @if($videosWatched->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($videosWatched as $engagement)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($engagement->video)
                                                        <div class="flex-shrink-0 h-10 w-16 bg-gray-200 rounded overflow-hidden">
                                                            <img class="h-10 w-16 object-cover" src="{{ Storage::url($engagement->video->thumbnail) }}" alt="">
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ Str::limit($engagement->video->title, 40) }}</div>
                                                            <div class="text-xs text-gray-500">{{ $engagement->video->subject }}</div>
                                                        </div>
                                                    @else
                                                        <span class="text-sm text-gray-500 italic">Video Deleted (ID: {{ $engagement->content_id }})</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $engagement->action == 'complete' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($engagement->action) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ gmdate("H:i:s", $engagement->duration_seconds ?? 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $engagement->created_at->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $videosWatched->appends(['comments_page' => request('comments_page'), 'quizzes_page' => request('quizzes_page')])->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">No videos watched yet.</div>
                    @endif
                </div>

                <!-- Comments Tab -->
                <div x-show="activeTab === 'comments'" style="display: none;">
                    @if($comments->count() > 0)
                        <div class="space-y-6">
                            @foreach($comments as $comment)
                                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="font-semibold text-gray-900">
                                                @if($comment->video)
                                                    On: {{ $comment->video->title }}
                                                @else
                                                    On: Unknown Video
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-500 ml-2">{{ $comment->created_at->format('M d, Y H:i') }}</span>
                                        </div>
                                        @if($comment->is_edited)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Edited
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-gray-800 bg-white p-3 rounded border border-gray-100">
                                        {{ $comment->content }}
                                    </div>

                                    @if($comment->edits && $comment->edits->count() > 0)
                                        <div x-data="{ showEdits: false }" class="mt-3">
                                            <button @click="showEdits = !showEdits" class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none flex items-center">
                                                <i class="fas fa-history mr-1"></i>
                                                <span x-text="showEdits ? 'Hide Edit History' : 'View Edit History ({{ $comment->edits->count() }})'"></span>
                                            </button>
                                            
                                            <div x-show="showEdits" class="mt-3 pl-4 border-l-2 border-gray-300 space-y-3" style="display: none;">
                                                @foreach($comment->edits->sortByDesc('created_at') as $edit)
                                                    <div class="text-sm">
                                                        <div class="text-gray-500 text-xs mb-1">
                                                            Previous version from {{ $edit->created_at->format('M d, Y H:i') }}:
                                                        </div>
                                                        <div class="bg-gray-100 text-gray-700 p-2 rounded line-through opacity-75">
                                                            {{ $edit->old_content }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $comments->appends(['videos_page' => request('videos_page'), 'quizzes_page' => request('quizzes_page')])->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">No comments posted yet.</div>
                    @endif
                </div>

                <!-- Quizzes Tab -->
                <div x-show="activeTab === 'quizzes'" style="display: none;">
                    @if($quizAttempts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Taken</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($quizAttempts as $attempt)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $attempt->quiz_title }}</div>
                                                <div class="text-xs text-gray-500">{{ $attempt->quiz_subject }} • {{ $attempt->quiz_level }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($attempt->score_percentage, 1) }}%</div>
                                                <div class="text-xs text-gray-500">{{ $attempt->correct_answers }} / {{ $attempt->total_questions }} correct</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($attempt->passed)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Passed</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ gmdate("i:s", $attempt->time_taken_seconds ?? 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $attempt->created_at->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $quizAttempts->appends(['videos_page' => request('videos_page'), 'comments_page' => request('comments_page')])->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">No quizzes taken yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
