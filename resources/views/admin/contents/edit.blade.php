@extends('layouts.admin')

@section('title', 'Edit Content')
@section('page-title', 'Edit Content')
@section('page-description', 'Edit content details, subject, grade level, and associations')

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
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="mt-1 text-sm text-gray-900 capitalize">{{ $content->status }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Uploaded By</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $content->uploader->name ?? 'Unknown' }}</p>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <form action="{{ route('admin.contents.update', $content->id) }}" method="POST" class="bg-white rounded-lg shadow-lg p-6">
            @csrf
            @method('PUT')

            <h2 class="text-xl font-semibold text-gray-900 mb-6">Edit Content Details</h2>

            <!-- Subject Selection -->
            <div class="mb-6">
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
            <div class="mb-6">
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
            <!-- Content Categories (BECE/WASSCE/Normal) -->
            <div class="mb-6">
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
                <p class="text-xs text-gray-500 mt-2">Select if this content specifically belongs to BECE, WASSCE or is Regular content</p>
            </div>

            <!-- Associated Quizzes -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Associated Quiz</label>
                <select name="quiz_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">No Quiz Associated</option>
                    @foreach($availableQuizzes as $quiz)
                        <option value="{{ $quiz->id }}" {{ $content->quiz_id == $quiz->id ? 'selected' : '' }}>
                            {{ $quiz->title }} ({{ $quiz->subject->name ?? 'No Subject' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Select a quiz to associate with this video content</p>
            </div>

            <!-- Associated Documents -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Associated Documents</label>
                <div class="space-y-2">
                    @foreach($availableDocuments as $document)
                        <label class="flex items-center">
                            <input type="checkbox" name="document_ids[]" value="{{ $document->id }}"
                                   {{ $content->documents->contains($document->id) ? 'checked' : '' }}
                                   class="mr-2">
                            <span class="text-sm">{{ $document->title }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-1">Select documents to associate with this video content</p>
            </div>

            <!-- Featured Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ $content->is_featured ? 'checked' : '' }}
                           class="mr-2">
                    <span class="text-sm font-medium text-gray-700">Mark as Featured</span>
                </label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.contents.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Content
                </button>
            </div>
        </form>
    </div>
</div>
@endsection