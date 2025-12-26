@extends('layouts.admin')

@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')
@section('page-description', 'Update subject information')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Subject</h1>
                    <p class="text-gray-600">Update subject details</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.subjects.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Subjects
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Subject Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Subject Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', $subject->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., Mathematics, Science, English"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description <span class="text-gray-500">(Optional)</span>
                        </label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Describe what this subject covers...">{{ old('description', $subject->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Provide a brief description of the subject to help organize content.</p>
                    </div>

                    <!-- Usage Statistics -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Current Usage</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Videos:</span>
                                <span class="font-medium text-gray-900">{{ $subject->videos_count + $subject->primary_videos_count }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Quizzes:</span>
                                <span class="font-medium text-gray-900">{{ $subject->quizzes_count }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">This subject is currently associated with {{ $subject->videos_count + $subject->primary_videos_count + $subject->quizzes_count }} content items.</p>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.subjects.index') }}"
                           class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i> Update Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection