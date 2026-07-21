@extends('schools.admin.layout')

@section('title', isset($quiz) ? 'Edit Quiz' : 'Create Quiz')

@section('content')
<div class="min-h-screen bg-gray-50 quiz-builder-area" style="padding: 2rem;">
    <div class="max-w-4xl mx-auto py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('school.studio.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i>Back to Studio
            </a>
            <h1 class="text-2xl font-bold text-gray-900" style="font-size: 1.5rem;">
                {{ isset($quiz) ? 'Edit Quiz' : 'Create Quiz' }}
            </h1>
        </div>

        <form action="{{ isset($quiz) ? route('school.studio.quiz.update', $quiz->id) : route('school.studio.quiz.store') }}" method="POST" id="quizForm">
            @csrf
            @if(isset($quiz)) @method('PUT') @endif
            <input type="hidden" name="quiz_data" id="quiz_data_input">
            <input type="hidden" name="status" id="status_input" value="published">

            <!-- Quiz Details Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6 border border-gray-200">
                <div class="px-6 py-4 card-header-premium">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center" style="font-size: 1.25rem;">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i> Quiz Details
                    </h2>
                </div>
                <div class="p-6">
                    <!-- Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Quiz Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title', $quiz->title ?? '') }}" required placeholder="e.g. Mid-Term Science Assessment"
                               class="w-full transition-shadow">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem;">
                        <!-- Subject -->
                        <div>
                            <label for="subject_id" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <select id="subject_id" name="subject_id" class="w-full bg-white" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $quiz->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Grade Level -->
                        <div>
                            <label for="grade_level" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Grade Level <span class="text-red-500">*</span>
                            </label>
                            <select id="grade_level" name="grade_level" class="w-full bg-white" required>
                                <option value="">Select Grade Level</option>
                                @foreach($levelGroups as $group)
                                    <optgroup label="{{ $group->title }}">
                                        @foreach($group->levels as $level)
                                            <option value="{{ $level->title }}" {{ old('grade_level', $quiz->grade_level ?? '') == $level->title ? 'selected' : '' }}>
                                                {{ $level->title }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('grade_level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <!-- Difficulty Mapping (Invisible form inputs mapped by component) -->
                        <input type="hidden" name="difficulty_level" id="form_difficulty" value="{{ old('difficulty_level', $quiz->difficulty_level ?? 'medium') }}">
                        <input type="hidden" name="time_limit_minutes" id="form_time_limit" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes ?? 30) }}">
                    </div>
                </div>
            </div>

            <!-- Quiz Editor Component -->
            <x-quiz-editor :quiz="$quiz ?? null" role="school_admin" />

            <!-- Footer Actions -->
            <div class="flex justify-end gap-4 pb-12" style="display: flex; justify-content: flex-end; gap: 1rem; padding-bottom: 3rem;">
                <button type="button" onclick="submitAs('draft')" class="px-6 py-2.5 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-lg font-medium hover:bg-yellow-100 transition-colors shadow-sm flex items-center">
                    <i class="fas fa-save mr-2"></i> Save as Draft
                </button>
                @if(isset($quiz) && $quiz->status === 'published')
                    <button type="button" onclick="submitAs('published')" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-md flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Save Changes
                    </button>
                @else
                    <button type="button" onclick="submitAs('published')" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-md flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> {{ isset($quiz) ? 'Publish' : 'Create & Publish' }}
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    const form = document.getElementById('quizForm');
    
    // Sync component settings into the parent form inputs
    const settingsDifficulty = document.getElementById('quiz_difficulty_settings');
    const formDifficulty = document.getElementById('form_difficulty');
    if (settingsDifficulty && formDifficulty) {
        settingsDifficulty.addEventListener('change', (e) => {
            formDifficulty.value = e.target.value;
        });
    }

    const settingsTimeLimit = document.getElementById('quiz_time_limit');
    const formTimeLimit = document.getElementById('form_time_limit');
    if (settingsTimeLimit && formTimeLimit) {
        settingsTimeLimit.addEventListener('change', (e) => {
            formTimeLimit.value = e.target.value;
        });
    }

    function submitAs(status) {
        document.getElementById('status_input').value = status;
        
        const questionsData = extractQuizPayload();
        
        if (questionsData.length === 0) {
            alert('Please add at least one question to the quiz.');
            return;
        }

        // Validate basic rules
        let isValid = true;
        document.querySelectorAll('.question-card').forEach((card, index) => {
            const type = card.dataset.type;
            const contentDiv = card.querySelector('.rich-text-content');
            
            if (contentDiv && contentDiv.innerHTML.trim() === '<p><br></p>') {
                alert(`Question ${index + 1} content is empty.`);
                isValid = false;
                return;
            }

            if (type === 'mcq') {
                const options = Array.from(card.querySelectorAll('.option-input')).map(input => input.value.trim());
                if (options.some(opt => !opt)) {
                    alert(`All options in Question ${index + 1} must be filled.`);
                    isValid = false;
                    return;
                }
                const correctOption = card.querySelector('.correct-option-radio:checked');
                if (!correctOption) {
                    alert(`Please select a correct option for Question ${index + 1}.`);
                    isValid = false;
                    return;
                }
            }
        });

        if (!isValid) return;

        const payload = {
            questions: questionsData,
            settings: {
                difficulty: document.getElementById('quiz_difficulty_settings').value,
                time_limit: parseInt(document.getElementById('quiz_time_limit').value) || 30,
                shuffle: document.getElementById('shuffle_questions').checked
            }
        };

        document.getElementById('quiz_data_input').value = JSON.stringify(payload);
        
        if (form.reportValidity()) {
            form.submit();
        }
    }
</script>
@endpush
