@extends('layouts.admin')

@section('title', 'Edit Content')
@section('page-title', 'Edit Content')
@section('page-description', 'Edit content details, subject, grade level, and associations')



@push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
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
            <input type="hidden" name="status" id="status_input" value="">

            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="px-6 py-4 card-header-premium">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Details</h2>
                </div>
                <div class="p-6">
                    <!-- Title (Editable for Quizzes, maybe read-only for Videos if Title is fixed) -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $contentType === 'video' ? 'Lesson / Video Title' : 'Quiz Title' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ $content->title }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    @if($contentType === 'video' && $content->quiz)
                    <div class="mb-6">
                        <label for="quiz_title" class="block text-sm font-medium text-gray-700 mb-2">
                            Associated Quiz Title
                        </label>
                        <input type="text" id="quiz_title" name="quiz_title" value="{{ $content->quiz->title }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Leave empty to use the video's title">
                        <p class="text-xs text-gray-500 mt-1">If specified, this title will be shown on the quiz dashboard instead of the Lesson / Video title.</p>
                    </div>
                    @endif

                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <div id="quill-description-editor" class="bg-white rounded-b-lg border-gray-300 min-h-[100px]"></div>
                        <textarea id="description" name="description" class="hidden">{{ $contentType === 'video' ? $content->description : ($content->video->description ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">This description is meant for the video lesson and will be displayed below the video player. It is not for the quiz.</p>
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
                <x-quiz-editor :quiz="$quizModel" role="admin" />
            @endif

            <!-- Submit Buttons -->
            @php
                $isDraft = false;
                if ($contentType === 'quiz') {
                    $isDraft = ($content->status ?? 'published') === 'draft';
                } elseif ($contentType === 'video' && $content->quiz) {
                    $isDraft = ($content->quiz->status ?? 'published') === 'draft';
                }
            @endphp
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.contents.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
                @if($isDraft)
                    <button type="submit" id="saveDraftBtn" class="px-6 py-2.5 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 shadow-lg shadow-amber-200 transition-all">
                        <i class="fas fa-save mr-2"></i>Save Draft
                    </button>
                    <button type="submit" id="publishBtn" class="px-8 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg shadow-green-200 transition-all">
                        <i class="fas fa-check-circle mr-2"></i>Publish
                    </button>
                @else
                    <button type="submit" id="revertDraftBtn" class="px-6 py-2.5 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 shadow-lg shadow-amber-200 transition-all">
                        <i class="fas fa-undo mr-2"></i>Revert to Draft
                    </button>
                    <button type="submit" id="savePublishedBtn" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('extra-js')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://unpkg.com/quill-magic-url"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Form submission handling — wire up status buttons
    const form = document.getElementById('editContentForm');
    const statusInput = document.getElementById('status_input');

    // Each button sets the status before submit
    ['saveDraftBtn', 'publishBtn', 'revertDraftBtn', 'savePublishedBtn'].forEach(id => {
        const btn = document.getElementById(id);
        if (!btn) return;
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (id === 'saveDraftBtn' || id === 'revertDraftBtn') {
                statusInput.value = 'draft';
            } else if (id === 'publishBtn') {
                statusInput.value = 'published';
            } else {
                statusInput.value = ''; // keep current
            }
            
            // Serialize quiz data if quiz editor is present
            const quizDataInput = document.getElementById('quiz_data_input');
            if (quizDataInput && typeof extractQuizPayload === 'function') {
                quizDataInput.value = JSON.stringify({
                    questions: extractQuizPayload()
                });
            }
            
            form.submit();
        });
    });

    // Initialize Quill for description
    if (document.getElementById('quill-description-editor')) {
        const imageHandler = function() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route('admin.contents.upload.image') }}', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        const range = quillDesc.getSelection(true);
                        quillDesc.insertEmbed(range.index, 'image', result.url);
                    } else {
                        alert('Image upload failed: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('Error uploading image');
                }
            };
        };

        const quillDesc = new Quill('#quill-description-editor', {
            theme: 'snow',
            placeholder: 'Write a description for the video lesson...',
            modules: {
                magicUrl: {
                    urlRegularExpression: /(https?:\/\/[^\s]+)|(www\.[^\s]+)|([a-zA-Z0-9-]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?)/i,
                    globalRegularExpression: /(https?:\/\/[^\s]+)|(www\.[^\s]+)|([a-zA-Z0-9-]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?)/gi
                },
                toolbar: {
                    container: [
                        ['bold', 'italic', 'underline', 'link', 'image'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ],
                    handlers: {
                        image: imageHandler
                    }
                }
            }
        });
        
        // Load initial content
        const descTextarea = document.getElementById('description');
        if (descTextarea.value) {
            quillDesc.clipboard.dangerouslyPasteHTML(descTextarea.value);
        }
        
        // Sync to textarea
        quillDesc.on('text-change', function() {
            // Only update if it's not effectively empty
            if (quillDesc.getText().trim() === '') {
                descTextarea.value = '';
            } else {
                descTextarea.value = quillDesc.root.innerHTML;
            }
        });
    }
</script>
@endpush