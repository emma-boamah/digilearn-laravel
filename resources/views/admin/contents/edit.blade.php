@extends('layouts.admin')

@section('title', 'Edit Content')
@section('page-title', 'Edit Content')
@section('page-description', 'Edit content details, subject, grade level, and associations')

@include('admin.contents.partials.quiz-builder-assets')

@push('styles')
    <style>
        .card-header-premium {
            background: linear-gradient(to right, #f8fafc, #f1f5f9);
            border-bottom: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('admin.contents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Contents
            </a>
        </div>

        <!-- Content Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Content Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $content->title }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Content Type</label>
                    <p class="mt-1 text-sm text-gray-900 capitalize">{{ $contentType }}</p>
                </div>
                @if(isset($content->status))
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="mt-1 text-sm text-gray-900 capitalize">{{ $content->status }}</p>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700">Uploaded By</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $content->uploader->name ?? 'Unknown' }}</p>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <form action="{{ route('admin.contents.update', $content->id) }}" method="POST" enctype="multipart/form-data" id="editContentForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="content_type" value="{{ $contentType }}">
            <input type="hidden" name="quiz_data" id="quiz_data_input">

            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="px-6 py-4 card-header-premium">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Details</h2>
                </div>
                <div class="p-6">
                    <!-- Title (Editable for Quizzes, maybe read-only for Videos if Title is fixed) -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ $content->title }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $contentType === 'video' ? $content->description : ($content->video->description ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Subject Selection -->
                        <div>
                            <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <select id="subject_id" name="subject_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $content->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Grade Level Selection -->
                        <div>
                            <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-2">
                                Grade Level <span class="text-red-500">*</span>
                            </label>
                            <select id="grade_level" name="grade_level" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Grade Level</option>
                                @foreach($levelGroups as $group)
                                    <optgroup label="{{ $group->title }}">
                                        @foreach($group->levels as $level)
                                            <option value="{{ $level->title }}" {{ $content->grade_level == $level->title ? 'selected' : '' }}>
                                                {{ $level->title }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Content Categories -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content Context / Exam Category</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                           {{ $content->categories->contains($category->id) ? 'checked' : '' }}
                                           class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if($contentType === 'video')
                        <!-- Video Specific Associations -->
                        <div class="mt-6 border-t pt-6">
                            <h3 class="text-md font-semibold text-gray-800 mb-4">Associations</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Associated Quiz -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Associated Quiz</label>
                                    <select name="quiz_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">No Quiz Associated</option>
                                        @foreach($availableQuizzes as $quiz)
                                            <option value="{{ $quiz->id }}" {{ $content->quiz_id == $quiz->id ? 'selected' : '' }}>
                                                {{ $quiz->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Associated Documents -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Associated Documents</label>
                                    <div class="max-h-40 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50">
                                        @foreach($availableDocuments as $document)
                                            <label class="flex items-center mb-2 last:mb-0">
                                                <input type="checkbox" name="document_ids[]" value="{{ $document->id }}"
                                                       {{ $content->documents->contains($document->id) ? 'checked' : '' }}
                                                       class="mr-2 rounded text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700">{{ $document->title }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Featured Status -->
                        <div class="mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" value="1" {{ $content->is_featured ? 'checked' : '' }}
                                       class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="text-sm font-medium text-gray-700">Mark as Featured</span>
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quiz Content Editor (Standalone Quiz or Video Associated Quiz) -->
            @php
                $hasQuiz = $contentType === 'quiz' || ($contentType === 'video' && $content->quiz);
                $quizModel = $contentType === 'quiz' ? $content : ($content->quiz ?? null);
            @endphp

            @if($hasQuiz)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 card-header-premium flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900">Quiz Questions</h2>
                        <div class="flex gap-2">
                            <button type="button" id="addMcqBtn" class="flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>MCQ
                            </button>
                            <button type="button" id="addEssayBtn" class="flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Essay
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Quiz Global Settings -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Difficulty</label>
                                <select id="quiz_difficulty" name="quiz_difficulty" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Time Limit (Min)</label>
                                <input type="number" id="quiz_time_limit" name="quiz_time_limit" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" value="15">
                            </div>
                            <div class="col-span-1 md:col-span-2 mt-2">
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        @php
                                            $isShuffle = old('shuffle_questions') !== null 
                                                ? old('shuffle_questions') == 1 
                                                : ($quizModel->shuffle_questions ?? true);
                                        @endphp
                                        <input type="hidden" name="shuffle_questions" value="0">
                                        <input type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" 
                                               {{ $isShuffle ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors"></div>
                                        <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Shuffle Questions</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 ml-13">When enabled, questions will appear in a different order for each student.</p>
                            </div>
                        </div>

                        <!-- Question Navigation -->
                        <div id="quizNavigation" class="quiz-navigation-wrapper mb-6">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Question Navigation</div>
                            <div id="quizNavGrid" class="quiz-nav-grid">
                                <!-- Navigation items injected via JS -->
                            </div>
                        </div>

                        <div id="questionsList" class="space-y-6">
                            <!-- Questions initialized via JS -->
                        </div>

                        <!-- Pagination Footer -->
                        <div class="pagination-footer mt-8">
                            <div id="currentQuestionLabel" class="text-sm font-semibold text-gray-600">Question 1 of 1</div>
                            <div class="nav-btn-group">
                                <button type="button" id="prevQuestionBtn" class="btn-nav">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </button>
                                <button type="button" id="nextQuestionBtn" class="btn-nav">
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.contents.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('extra-js')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Initialize data for the quiz builder
    const uploadData = {
        contentType: @json($contentType),
        quiz: @json($quizModel),
        questions_to_remove: [] // Track questions to remove if needed (though currently we just overwrite quiz_data)
    };

    if (uploadData.quiz) {
        // Standardize quiz_data
        let questions = [];
        if (typeof uploadData.quiz.quiz_data === 'string') {
            try {
                const parsed = JSON.parse(uploadData.quiz.quiz_data);
                questions = parsed.questions || [];
            } catch (e) { console.error('Error parsing quiz_data', e); }
        } else if (uploadData.quiz.quiz_data && uploadData.quiz.quiz_data.questions) {
            questions = uploadData.quiz.quiz_data.questions;
        }
        
        uploadData.quiz.questions = questions;

        // Initialize builder
        initializeQuizStep();
        initializeQuizSettings();

        // Render existing questions
        if (questions.length > 0) {
            questions.forEach(q => {
                addQuestion(q.type, q);
            });
        }
    }

    // Form submission handling
    const form = document.getElementById('editContentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (uploadData.quiz) {
                const quizDataInput = document.getElementById('quiz_data_input');
                if (quizDataInput) {
                    quizDataInput.value = JSON.stringify({
                        questions: uploadData.quiz.questions
                    });
                }
            }
        });
    }
</script>
@endpush