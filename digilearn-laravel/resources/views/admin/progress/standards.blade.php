@extends('layouts.admin')

@section('title', 'Progression Standards')
@section('page-title', 'Progression Standards')
@section('page-description', 'Configure requirements for level progression')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Progression Standards</h2>
                <p class="text-gray-600 mt-1">Set the requirements students must meet to progress to the next level</p>
            </div>
            <button onclick="openModal('addStandardModal')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i> Add Standard
            </button>
        </div>

        <!-- Standards Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level Group</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lesson Completion</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz Completion</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Quiz Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Quiz Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Watch Threshold</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($standards as $standard)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $levelGroups[$standard->level_group] ?? $standard->level_group }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $standard->required_lesson_completion_percentage }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $standard->required_quiz_completion_percentage }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $standard->required_average_quiz_score }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $standard->minimum_quiz_score }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $standard->lesson_watch_threshold_percentage }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($standard->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-pause-circle mr-1"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openEditModal({{ $standard->id }}, '{{ $standard->level_group }}', {{ $standard->required_lesson_completion_percentage }}, {{ $standard->required_quiz_completion_percentage }}, {{ $standard->required_average_quiz_score }}, {{ $standard->minimum_quiz_score }}, {{ $standard->lesson_watch_threshold_percentage }}, {{ $standard->is_active ? 'true' : 'false' }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <form action="{{ route('admin.progress.standards.toggle', $standard) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="text-{{ $standard->is_active ? 'orange' : 'green' }}-600 hover:text-{{ $standard->is_active ? 'orange' : 'green' }}-900">
                                        {{ $standard->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No progression standards configured yet.
                                <br><small class="text-gray-400">Default standards will be used until custom ones are set.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Default Standards Info -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="text-sm font-semibold text-blue-800 mb-2">Default Standards (Used When No Custom Standards Exist)</h4>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                <div>
                    <span class="font-medium text-blue-700">Lesson Completion:</span>
                    <span class="text-blue-600">80%</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Quiz Completion:</span>
                    <span class="text-blue-600">70%</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Avg Quiz Score:</span>
                    <span class="text-blue-600">70%</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Min Quiz Score:</span>
                    <span class="text-blue-600">70%</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Watch Threshold:</span>
                    <span class="text-blue-600">90%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Standard Modal -->
<div id="addStandardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Add Progression Standard</h3>
            <button onclick="closeModal('addStandardModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form action="{{ route('admin.progress.standards.store') }}" method="POST" class="mt-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="level_group" class="block text-sm font-medium text-gray-700">Level Group <span class="text-red-500">*</span></label>
                    <select name="level_group" id="level_group" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <option value="">Select Level Group</option>
                        @foreach($levelGroups as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="required_lesson_completion_percentage" class="block text-sm font-medium text-gray-700">Lesson Completion Required (%)</label>
                    <input type="number" name="required_lesson_completion_percentage" id="required_lesson_completion_percentage" min="0" max="100" value="80" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Percentage of lessons student must complete</p>
                </div>

                <div>
                    <label for="required_quiz_completion_percentage" class="block text-sm font-medium text-gray-700">Quiz Completion Required (%)</label>
                    <input type="number" name="required_quiz_completion_percentage" id="required_quiz_completion_percentage" min="0" max="100" value="70" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Percentage of quizzes student must pass</p>
                </div>

                <div>
                    <label for="required_average_quiz_score" class="block text-sm font-medium text-gray-700">Average Quiz Score Required (%)</label>
                    <input type="number" name="required_average_quiz_score" id="required_average_quiz_score" min="0" max="100" value="70" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Minimum average score across all quizzes</p>
                </div>

                <div>
                    <label for="minimum_quiz_score" class="block text-sm font-medium text-gray-700">Minimum Quiz Score (%)</label>
                    <input type="number" name="minimum_quiz_score" id="minimum_quiz_score" min="0" max="100" value="70" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Minimum score required to pass individual quizzes</p>
                </div>

                <div class="md:col-span-2">
                    <label for="lesson_watch_threshold_percentage" class="block text-sm font-medium text-gray-700">Lesson Watch Threshold (%)</label>
                    <input type="number" name="lesson_watch_threshold_percentage" id="lesson_watch_threshold_percentage" min="0" max="100" value="90" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Percentage of lesson that must be watched to count as completed</p>
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" onclick="closeModal('addStandardModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Add Standard</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Standard Modal -->
<div id="editStandardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Edit Progression Standard</h3>
            <button onclick="closeModal('editStandardModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="editStandardForm" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Level Group</label>
                    <input type="text" id="edit_level_group_display" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                </div>

                <div>
                    <label for="edit_required_lesson_completion_percentage" class="block text-sm font-medium text-gray-700">Lesson Completion Required (%)</label>
                    <input type="number" name="required_lesson_completion_percentage" id="edit_required_lesson_completion_percentage" min="0" max="100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>

                <div>
                    <label for="edit_required_quiz_completion_percentage" class="block text-sm font-medium text-gray-700">Quiz Completion Required (%)</label>
                    <input type="number" name="required_quiz_completion_percentage" id="edit_required_quiz_completion_percentage" min="0" max="100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>

                <div>
                    <label for="edit_required_average_quiz_score" class="block text-sm font-medium text-gray-700">Average Quiz Score Required (%)</label>
                    <input type="number" name="required_average_quiz_score" id="edit_required_average_quiz_score" min="0" max="100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>

                <div>
                    <label for="edit_minimum_quiz_score" class="block text-sm font-medium text-gray-700">Minimum Quiz Score (%)</label>
                    <input type="number" name="minimum_quiz_score" id="edit_minimum_quiz_score" min="0" max="100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>

                <div class="md:col-span-2">
                    <label for="edit_lesson_watch_threshold_percentage" class="block text-sm font-medium text-gray-700">Lesson Watch Threshold (%)</label>
                    <input type="number" name="lesson_watch_threshold_percentage" id="edit_lesson_watch_threshold_percentage" min="0" max="100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" onclick="closeModal('editStandardModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Update Standard</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function openEditModal(id, levelGroup, lessonReq, quizReq, avgScoreReq, minScore, watchThreshold, isActive) {
    const levelGroups = @json($levelGroups);
    document.getElementById('edit_level_group_display').value = levelGroups[levelGroup] || levelGroup;
    document.getElementById('edit_required_lesson_completion_percentage').value = lessonReq;
    document.getElementById('edit_required_quiz_completion_percentage').value = quizReq;
    document.getElementById('edit_required_average_quiz_score').value = avgScoreReq;
    document.getElementById('edit_minimum_quiz_score').value = minScore;
    document.getElementById('edit_lesson_watch_threshold_percentage').value = watchThreshold;

    document.getElementById('editStandardForm').action = `/admin/progress/standards/${id}`;
    openModal('editStandardModal');
}
</script>
@endsection