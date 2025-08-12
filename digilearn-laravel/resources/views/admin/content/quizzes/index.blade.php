@extends('layouts.admin')

@section('title', 'Manage Quizzes')
@section('page-title', 'Manage Quizzes')
@section('page-description', 'Manage all quizzes on the platform')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">All Quizzes</h2>
            <button onclick="openModal('addQuizModal')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Quiz
            </button>
        </div>

        @if(isset($subjects) && $subjects->count())
        <div class="mb-6 grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-1 bg-gray-50 rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center"><i class="fas fa-book mr-2"></i> Subjects</h3>
                <ul class="space-y-2 max-h-64 overflow-auto pr-1">
                    @foreach($subjects as $s)
                    <li class="flex items-center justify-between">
                        <a href="?search={{ urlencode($s->subject) }}" class="text-sm text-gray-700 hover:text-blue-600">{{ $s->subject }}</a>
                        <span class="text-xs text-gray-500">{{ $s->count }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="lg:col-span-3">
        @endif

        <!-- Search and Filter Section -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <form action="{{ route('admin.content.quizzes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by title or subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="grade_level" class="block text-sm font-medium text-gray-700">Grade</label>
                    <select name="grade_level" id="grade_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Grades</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="video_id" class="block text-sm font-medium text-gray-700">Video Course</label>
                    <select name="video_id" id="video_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Videos</option>
                        @foreach($videos as $video)
                            <option value="{{ $video->id }}" {{ request('video_id') == $video->id ? 'selected' : '' }}>{{ $video->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="uploaded_by" class="block text-sm font-medium text-gray-700">Uploaded By</label>
                    <select name="uploaded_by" id="uploaded_by" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Uploaders</option>
                        @foreach($uploaders as $uploader)
                            <option value="{{ $uploader->id }}" {{ request('uploaded_by') == $uploader->id ? 'selected' : '' }}>{{ $uploader->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="is_featured" class="block text-sm font-medium text-gray-700">Featured</label>
                    <select name="is_featured" id="is_featured" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('is_featured') === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ request('is_featured') === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div>
                    <label for="upload_date" class="block text-sm font-medium text-gray-700">Upload Date</label>
                    <input type="date" name="upload_date" id="upload_date" value="{{ request('upload_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="md:col-span-3 lg:col-span-4 flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Apply Filters</button>
                    <a href="{{ route('admin.content.quizzes.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        <!-- Quizzes Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject / Video</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $quiz->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($quiz->video)
                                    <div>Video: {{ $quiz->video->title }}</div>
                                    <a href="{{ route('admin.content.videos.index') }}?search={{ urlencode($quiz->video->title) }}" class="text-xs text-blue-600 hover:text-blue-800">Open video</a>
                                @elseif($quiz->subject)
                                    Subject: {{ $quiz->subject }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quiz->grade_level ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quiz->uploader->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($quiz->views_count) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($quiz->attempts_count) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quiz->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <form action="{{ route('admin.content.quizzes.toggle-feature', $quiz) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="focus:outline-none">
                                        @if($quiz->is_featured)
                                            <i class="fas fa-star text-yellow-500 text-lg" title="Featured"></i>
                                        @else
                                            <i class="far fa-star text-gray-400 text-lg" title="Not Featured"></i>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openEditModal({{ $quiz->id }}, '{{ $quiz->title }}', '{{ $quiz->subject }}', '{{ $quiz->video_id }}', '{{ $quiz->grade_level }}', `{{ addslashes($quiz->quiz_data) }}`, {{ $quiz->is_featured ? 'true' : 'false' }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <form action="{{ route('admin.content.quizzes.destroy', $quiz) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No quizzes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $quizzes->links() }}
        </div>

        @if(isset($subjects) && $subjects->count())
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add Quiz Modal -->
<div id="addQuizModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Add New Quiz</h3>
            <button onclick="closeModal('addQuizModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form action="{{ route('admin.content.quizzes.store') }}" method="POST" class="mt-4">
            @csrf
            <div class="mb-4">
                <label for="add_title" class="block text-sm font-medium text-gray-700">Quiz Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="add_title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="add_subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <div class="flex gap-2">
                    <input type="text" name="subject" id="add_subject" placeholder="e.g., Mathematics" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @if(isset($subjects) && $subjects->count())
                    <select id="subject_select" class="mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Choose existing</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->subject }}">{{ $s->subject }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
            </div>
            <div class="mb-4">
                <label for="add_video_id" class="block text-sm font-medium text-gray-700">Video Course (Optional)</label>
                <select name="video_id" id="add_video_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach($videos as $video)
                        <option value="{{ $video->id }}">{{ $video->title }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Tip: Create a quiz with the same title as the video to link them.</p>
            </div>
            <div class="mb-4">
                <label for="add_grade_level" class="block text-sm font-medium text-gray-700">Grade Level (Optional)</label>
                <select name="grade_level" id="add_grade_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Grade</option>
                    @foreach($gradeLevels as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="add_quiz_data" class="block text-sm font-medium text-gray-700">Quiz Content (e.g., JSON or text)</label>
                <textarea name="quiz_data" id="add_quiz_data" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
            </div>
            @if(Auth::user()->is_superuser)
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="is_featured" id="add_is_featured" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="add_is_featured" class="ml-2 block text-sm text-gray-900">Is Featured</label>
            </div>
            @endif
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('addQuizModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Add Quiz</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Quiz Modal -->
<div id="editQuizModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Edit Quiz</h3>
            <button onclick="closeModal('editQuizModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="editQuizForm" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="edit_title" class="block text-sm font-medium text-gray-700">Quiz Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="edit_title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="edit_subject" class="block text-sm font-medium text-gray-700">Subject (Optional)</label>
                <input type="text" name="subject" id="edit_subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="edit_video_id" class="block text-sm font-medium text-gray-700">Video Course (Optional)</label>
                <select name="video_id" id="edit_video_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach($videos as $video)
                        <option value="{{ $video->id }}">{{ $video->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_grade_level" class="block text-sm font-medium text-gray-700">Grade Level (Optional)</label>
                <select name="grade_level" id="edit_grade_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Grade</option>
                    @foreach($gradeLevels as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_quiz_data" class="block text-sm font-medium text-gray-700">Quiz Content (e.g., JSON or text)</label>
                <textarea name="quiz_data" id="edit_quiz_data" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
            </div>
            @if(Auth::user()->is_superuser)
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="is_featured" id="edit_is_featured" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="edit_is_featured" class="ml-2 block text-sm text-gray-900">Is Featured</label>
            </div>
            @endif
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('editQuizModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Update Quiz</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" defer>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function openEditModal(id, title, subject, videoId, gradeLevel, quizData, isFeatured) {
        const form = document.getElementById('editQuizForm');
        form.action = `/admin/content/quizzes/${id}`; // Update action URL

        document.getElementById('edit_title').value = title;
        document.getElementById('edit_subject').value = subject;
        document.getElementById('edit_video_id').value = videoId;
        document.getElementById('edit_grade_level').value = gradeLevel;
        document.getElementById('edit_quiz_data').value = quizData;
        document.getElementById('edit_is_featured').checked = isFeatured;

        openModal('editQuizModal');
    }

    const subjectSelect = document.getElementById('subject_select');
    if (subjectSelect) {
        subjectSelect.addEventListener('change', function() {
            const v = this.value;
            if (v) {
                document.getElementById('add_subject').value = v;
            }
        });
    }
</script>
@endpush
@endsection
