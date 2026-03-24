@extends('layouts.admin')

@section('title', 'Quiz Integrity Review')
@section('page-title', 'Quiz Integrity')
@section('page-description', 'Monitor quiz attempts and investigate potential academic integrity violations')

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.quizzes.review.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm" 
                        placeholder="Search by name or email...">
                </div>
            </div>
            <div>
                <label for="violation" class="block text-sm font-medium text-gray-700 mb-1">Violation Status</label>
                <select name="violation" id="violation" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">All Attempts</option>
                    <option value="yes" {{ request('violation') == 'yes' ? 'selected' : '' }}>Violations Detected</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    Filter Results
                </button>
            </div>
        </form>
    </div>

    <!-- Attempts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Integrity Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attempts as $attempt)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-user-avatar :user="$attempt->user" :size="32" class="flex-shrink-0" />
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $attempt->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attempt->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $attempt->quiz_title }}</div>
                            <div class="text-xs text-gray-500">{{ $attempt->quiz_subject }} • {{ $attempt->quiz_level }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($attempt->score_percentage, 1) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->failed_due_to_violation)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Violation Triggered
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Clean Record
                                </span>
                            @endif
                            
                            <div class="mt-1 text-[10px] font-bold uppercase tracking-wider {{ $attempt->trust_score < 70 ? 'text-red-500' : 'text-gray-400' }}">
                                Trust Score: <span class="{{ $attempt->trust_score < 70 ? 'text-red-600' : 'text-gray-600' }}">{{ $attempt->trust_score }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : 'In Progress' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.quizzes.review.show', $attempt->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md transition-colors">
                                Review Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-shield-alt text-4xl text-gray-200 mb-3"></i>
                                <p>No quiz attempts found matching your filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $attempts->links() }}
        </div>
    </div>
</div>
@endsection
