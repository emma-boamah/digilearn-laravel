@props([
    'quiz' => null,
    'role' => 'admin', // 'admin' or 'school_admin'
])

@php
    $defaultQuiz = (object) [
        'questions' => [],
        'difficulty_level' => 'medium',
        'time_limit_minutes' => 30,
        'shuffle_questions' => false,
    ];
    $quizData = $quiz ?? $defaultQuiz;
@endphp

@if($role === 'school_admin')
    <!-- Inject Tailwind specifically for school admin who use Vanilla CSS layout -->
    @push('styles')
        <script src="https://cdn.tailwindcss.com"></script>
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
          tailwind.config = {
            corePlugins: {
              preflight: false,
            }
          }
        </script>
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <style nonce="{{ request()->attributes->get('csp_nonce') }}">
            .quiz-builder-area input[type="text"], .quiz-builder-area input[type="number"], .quiz-builder-area select {
                border: 1px solid #d1d5db;
                background-color: white;
                color: #111827;
                border-radius: 0.375rem;
                padding: 0.5rem 0.75rem;
            }
            .quiz-builder-area input[type="text"]:focus, .quiz-builder-area input[type="number"]:focus, .quiz-builder-area select:focus {
                outline: 2px solid transparent;
                outline-offset: 2px;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
            }
            .quiz-builder-area button { cursor: pointer; }
            .card-header-premium {
                background: linear-gradient(to right, #f8fafc, #f1f5f9);
                border-bottom: 1px solid #e2e8f0;
            }
        </style>
    @endpush
@endif

<!-- Shared Quiz Builder UI Component -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6 border border-gray-200 quiz-builder-area">
    <div class="px-6 py-4 card-header-premium flex justify-between items-center" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="text-xl font-semibold text-gray-900 flex items-center" style="font-size: 1.25rem;">
            <i class="fas fa-list-ol text-blue-600 mr-2"></i> Quiz Questions
        </h2>
        <div class="flex gap-2" style="display: flex; gap: 0.5rem;">
            <button type="button" id="addMcqBtn" class="flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium border border-blue-100 shadow-sm">
                <i class="fas fa-plus mr-2"></i>MCQ
            </button>
            <button type="button" id="addEssayBtn" class="flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors text-sm font-medium border border-indigo-100 shadow-sm">
                <i class="fas fa-plus mr-2"></i>Essay
            </button>
            <button type="button" id="addAiBtn" class="flex items-center px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors text-sm font-medium border border-purple-200 shadow-sm">
                <i class="fas fa-magic mr-2"></i>AI Generate
            </button>
        </div>
    </div>
    
    <div class="p-6">
        <!-- Global Quiz Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 bg-gray-50 rounded-xl border border-gray-200" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Difficulty</label>
                <select id="quiz_difficulty_settings" {{ $role === 'admin' ? 'name=quiz_difficulty' : '' }} class="w-full bg-white text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="easy" {{ ($quizData->difficulty_level ?? '') === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ ($quizData->difficulty_level ?? 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ ($quizData->difficulty_level ?? '') === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Time Limit (Min)</label>
                <input type="number" id="quiz_time_limit" {{ $role === 'admin' ? 'name=quiz_time_limit' : '' }} class="w-full bg-white text-sm border border-gray-300 rounded-lg px-3 py-2" value="{{ $quizData->time_limit_minutes ?? 30 }}">
            </div>
            <div class="col-span-1 md:col-span-2 mt-2" style="grid-column: span 2;">
                <label class="flex items-center cursor-pointer group" style="display: flex; align-items: center;">
                    <div class="relative" style="position: relative;">
                        <input type="hidden" name="shuffle_questions" value="0">
                        <input type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" 
                               {{ ($quizData->shuffle_questions ?? false) ? 'checked' : '' }}
                               class="sr-only peer" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
                        <div class="w-10 h-5 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 transition-colors" style="width: 2.5rem; height: 1.25rem; background-color: #d1d5db; border-radius: 9999px;"></div>
                    </div>
                    <div class="ml-3" style="margin-left: 0.75rem;">
                        <span class="block text-sm font-medium text-gray-700">Shuffle Questions</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Questions will appear in a different order for each student.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Question Navigation -->
        <div id="quizNavigation" class="quiz-navigation-wrapper mb-6">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Question Navigation</div>
            <div id="quizNavGrid" class="quiz-nav-grid">
                <!-- Navigation bubbles injected by JS -->
            </div>
        </div>

        <!-- Questions Container -->
        <div id="questionsList" class="space-y-6" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Questions injected by JS -->
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

@include('admin.contents.partials.quiz-builder-assets')

<!-- Initialization Script for the Quiz Editor -->
@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Initialize data for the quiz builder
    const uploadData = {
        quiz: {!! json_encode($quizData) !!}
    };

    // Parse quiz_data if it exists (for existing quizzes)
    if (uploadData.quiz.quiz_data) {
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
    }

    if (!uploadData.quiz.questions) {
        uploadData.quiz.questions = [];
    }

    // Initialize builder functions provided by quiz-builder-assets
    if(typeof initializeQuizStep === 'function') initializeQuizStep();
    if(typeof initializeQuizSettings === 'function') initializeQuizSettings();

    // Render existing questions
    if (uploadData.quiz.questions.length > 0) {
        uploadData.quiz.questions.forEach(q => {
            if(typeof addQuestion === 'function') addQuestion(q.type, q);
        });
    }

    // Helper to extract JSON representation of quiz for form submission
    function extractQuizPayload() {
        // Ensure MathLive active field is flushed
        if (document.activeElement && document.activeElement.tagName.toLowerCase() === 'math-field') {
            document.activeElement.blur();
        }
        if (typeof getQuestionsData === 'function') {
            return getQuestionsData();
        }
        return [];
    }
</script>
@endpush
