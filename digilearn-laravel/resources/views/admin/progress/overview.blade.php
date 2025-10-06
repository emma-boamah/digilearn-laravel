@extends('layouts.admin')

@section('title', 'Student Progress Overview')
@section('page-title', 'Student Progress Overview')
@section('page-description', 'Monitor and manage student learning progress')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_students']) }}</h3>
                    <p class="text-gray-600">Total Students</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($stats['eligible_students']) }}</h3>
                    <p class="text-gray-600">Ready to Progress</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-graduation-cap text-purple-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($stats['completed_levels']) }}</h3>
                    <p class="text-gray-600">Levels Completed</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-fire text-orange-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($stats['active_students']) }}</h3>
                    <p class="text-gray-600">Active This Week</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('admin.progress.overview') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search Student</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div>
                <label for="level_group" class="block text-sm font-medium text-gray-700">Level Group</label>
                <select name="level_group" id="level_group" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Levels</option>
                    @foreach($levelGroups as $key => $label)
                        <option value="{{ $key }}" {{ request('level_group') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="eligibility" class="block text-sm font-medium text-gray-700">Progress Status</label>
                <select name="eligibility" id="eligibility" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Students</option>
                    <option value="eligible" {{ request('eligibility') == 'eligible' ? 'selected' : '' }}>Ready to Progress</option>
                    <option value="not_eligible" {{ request('eligibility') == 'not_eligible' ? 'selected' : '' }}>Still Learning</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Apply Filters</button>
                <a href="{{ route('admin.progress.overview') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Reset</a>
            </div>
        </form>
    </div>

    <!-- Progress Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Student Progress Records</h3>
                <a href="{{ route('admin.progress.standards') }}" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors flex items-center text-sm">
                    <i class="fas fa-cog mr-2"></i> Progression Standards
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Level</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lessons</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quizzes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($progressRecords as $progress)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                            {{ substr($progress->user->name ?? 'U', 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $progress->user->name ?? 'Unknown' }}</div>
                                        <div class="text-sm text-gray-500">{{ $progress->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucwords(str_replace('-', ' ', $progress->current_level)) }}
                                <br><small class="text-gray-500">{{ $levelGroups[$progress->level_group] ?? $progress->level_group }}</small>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $progress->completed_lessons }}/{{ $progress->total_lessons_in_level }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $progress->completed_quizzes }}/{{ $progress->total_quizzes_in_level }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($progress->average_quiz_score, 1) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress->completion_percentage }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ number_format($progress->completion_percentage, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($progress->eligible_for_next_level)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Ready
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i> Learning
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.progress.user.detail', $progress->user_id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View Details</a>
                                @if($progress->eligible_for_next_level)
                                    <button onclick="openProgressModal({{ $progress->user_id }}, '{{ $progress->current_level }}')" class="text-green-600 hover:text-green-900">Progress</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No progress records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $progressRecords->links() }}
        </div>
    </div>
</div>

<!-- Manual Progress Modal -->
<div id="progressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Progress Student</h3>
            <button onclick="closeModal('progressModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="progressForm" method="POST" class="mt-4">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">From Level</label>
                <input type="text" id="fromLevelDisplay" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                <input type="hidden" name="from_level" id="fromLevel">
            </div>
            <div class="mb-4">
                <label for="to_level" class="block text-sm font-medium text-gray-700">To Level</label>
                <select name="to_level" id="toLevel" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <option value="">Select next level</option>
                    <option value="primary-upper">Primary Upper (P4-P6)</option>
                    <option value="jhs">Junior High School (JHS 1-3)</option>
                    <option value="shs">Senior High School (SHS 1-3)</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Reason for manual progression"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('progressModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Cancel</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">Progress Student</button>
            </div>
        </form>
    </div>
</div>

<script>
function openProgressModal(userId, fromLevel) {
    document.getElementById('fromLevelDisplay').value = fromLevel.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('fromLevel').value = fromLevel;
    document.getElementById('progressForm').action = `/admin/progress/user/${userId}/progress`;
    document.getElementById('progressModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
@endsection