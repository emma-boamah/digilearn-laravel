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
                        <a href="?search={{ urlencode($s->name) }}" class="text-sm text-gray-700 hover:text-blue-600">{{ $s->name }}</a>
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
                    <label for="difficulty_level" class="block text-sm font-medium text-gray-700">Difficulty</label>
                    <select name="difficulty_level" id="difficulty_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Difficulties</option>
                        <option value="easy" {{ request('difficulty_level') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ request('difficulty_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ request('difficulty_level') == 'hard' ? 'selected' : '' }}>Hard</option>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
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
                                    Subject: {{ $quiz->subject->name }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quiz->grade_level ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($quiz->difficulty_level)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($quiz->difficulty_level === 'easy') bg-green-100 text-green-800
                                        @elseif($quiz->difficulty_level === 'medium') bg-yellow-100 text-yellow-800
                                        @elseif($quiz->difficulty_level === 'hard') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($quiz->difficulty_level) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
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
                               <button onclick="openEditModal(this, {{ $quiz->id }}, '{{ addslashes($quiz->title) }}', '{{ $quiz->subject_id }}', '{{ $quiz->video_id }}', '{{ $quiz->grade_level }}', {{ $quiz->is_featured ? 'true' : 'false' }})" data-quiz-data='@json($quiz->quiz_data)' class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
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
        <form action="{{ route('admin.contents.store') }}" method="POST" class="mt-4">
            @csrf
            <div class="mb-4">
                <label for="add_title" class="block text-sm font-medium text-gray-700">Quiz Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="add_title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="add_subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                <select name="subject_id" id="add_subject_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Subject</option>
                    @if(isset($subjects) && $subjects->count())
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    @endif
                </select>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Quiz Questions</label>
                <div id="add_questions_container" class="space-y-4">
                    <!-- Questions will be added here -->
                </div>
                <button type="button" onclick="addQuestion('add')" class="mt-3 bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Question
                </button>
            </div>
            <input type="hidden" name="quiz_data" id="add_quiz_data">
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
                <label for="edit_subject_id" class="block text-sm font-medium text-gray-700">Subject (Optional)</label>
                <select name="subject_id" id="edit_subject_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select Subject</option>
                    @if(isset($subjects) && $subjects->count())
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    @endif
                </select>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Quiz Questions</label>
                <div id="edit_questions_container" class="space-y-4">
                    <!-- Questions will be added here -->
                </div>
                <button type="button" onclick="addQuestion('edit')" class="mt-3 bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Question
                </button>
            </div>
            <input type="hidden" name="quiz_data" id="edit_quiz_data">
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

    function openEditModal(button, id, title, subject, videoId, gradeLevel, isFeatured) {
        const form = document.getElementById('editQuizForm');
        form.action = `/admin/content/quizzes/${id}`; // Update action URL

        document.getElementById('edit_title').value = title;
        document.getElementById('edit_subject_id').value = subject;
        document.getElementById('edit_video_id').value = videoId;
        document.getElementById('edit_grade_level').value = gradeLevel;
        document.getElementById('edit_is_featured').checked = isFeatured;

        // Clear existing questions
        document.getElementById('edit_questions_container').innerHTML = '';

        // Load existing quiz data from button's data attribute
        const quizData = button.getAttribute('data-quiz-data');
        if (quizData) {
            try {
                const quizObj = JSON.parse(quizData);
                if (quizObj.questions && Array.isArray(quizObj.questions)) {
                    quizObj.questions.forEach((question, index) => {
                        addQuestion('edit', question);
                    });
                }
            } catch (e) {
                console.error('Invalid quiz data:', e);
            }
        }

        openModal('editQuizModal');
    }

    function addQuestion(type, questionData = null) {
        const container = document.getElementById(`${type}_questions_container`);
        const questionIndex = container.children.length;

        const questionDiv = document.createElement('div');
        questionDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
        questionDiv.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium text-gray-700">Question ${questionIndex + 1}</h4>
                <button type="button" onclick="removeQuestion(this)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Question Text</label>
                <input type="text" class="question-text w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Enter your question" value="${questionData ? questionData.text || '' : ''}">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                ${['A', 'B', 'C', 'D'].map((letter, index) => `
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Option ${letter}</label>
                        <input type="text" class="option-text w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Enter option ${letter}" value="${questionData && questionData.options ? questionData.options[index] || '' : ''}">
                    </div>
                `).join('')}
            </div>
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-2">Correct Answer</label>
                <div class="flex space-x-4">
                    ${['A', 'B', 'C', 'D'].map((letter, index) => `
                        <label class="flex items-center">
                            <input type="radio" name="correct_${type}_${questionIndex}" value="${index}" class="correct-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" ${questionData && questionData.correct === index ? 'checked' : ''}>
                            <span class="ml-2 text-sm text-gray-700">${letter}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `;

        container.appendChild(questionDiv);
    }

    function removeQuestion(button) {
        button.closest('.border').remove();
        updateQuestionNumbers();
    }

    function updateQuestionNumbers() {
        document.querySelectorAll('#add_questions_container .border, #edit_questions_container .border').forEach((div, index) => {
            div.querySelector('h4').textContent = `Question ${index + 1}`;
        });
    }

    function generateQuizData(type) {
        const container = document.getElementById(`${type}_questions_container`);
        const questions = [];

        container.querySelectorAll('.border').forEach((questionDiv, questionIndex) => {
            const questionText = questionDiv.querySelector('.question-text').value.trim();
            const optionInputs = questionDiv.querySelectorAll('.option-text');
            const correctRadio = questionDiv.querySelector(`input[name="correct_${type}_${questionIndex}"]:checked`);

            if (questionText && optionInputs.length === 4 && correctRadio) {
                const options = Array.from(optionInputs).map(input => input.value.trim()).filter(opt => opt);
                if (options.length === 4) {
                    questions.push({
                        id: questionIndex + 1,
                        text: questionText,
                        options: options,
                        correct: parseInt(correctRadio.value)
                    });
                }
            }
        });

        const quizData = { questions: questions };
        document.getElementById(`${type}_quiz_data`).value = JSON.stringify(quizData, null, 2);
        return questions.length > 0;
    }

    // Form submission handlers
    document.getElementById('addQuizModal').querySelector('form').addEventListener('submit', function(e) {
        if (!generateQuizData('add')) {
            e.preventDefault();
            alert('Please add at least one complete question with 4 options and select a correct answer.');
        }
    });

    document.getElementById('editQuizForm').addEventListener('submit', function(e) {
        if (!generateQuizData('edit')) {
            e.preventDefault();
            alert('Please add at least one complete question with 4 options and select a correct answer.');
        }
    });
</script>
@endpush
@endsection
