<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quiz - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .difficulty-easy { background-color: #10B981; }
        .difficulty-medium { background-color: #F59E0B; }
        .difficulty-hard { background-color: #EF4444; }

        .time-input-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .time-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-align: center;
        }

        .time-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800">Admin Panel</h2>
            </div>
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="{{ route('admin.content') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-file-alt mr-3"></i> Content
                </a>
                <a href="{{ route('admin.content.quizzes.index') }}" class="flex items-center px-6 py-3 bg-blue-50 text-blue-700">
                    <i class="fas fa-question-circle mr-3"></i> Quizzes
                </a>
                <a href="{{ route('admin.content.quizzes.manage') }}" class="flex items-center px-6 py-3 bg-blue-100 text-blue-800 border-r-4 border-blue-500">
                    <i class="fas fa-cogs mr-3"></i> Manage Quiz
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">
                            @if(isset($quiz)) Edit Quiz @else Create New Quiz @endif
                        </h1>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('admin.content.quizzes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Quizzes
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <div class="max-w-4xl mx-auto">
                    <form action="{{ isset($quiz) ? route('admin.content.quizzes.update', $quiz) : route('admin.contents.store') }}"
                          method="POST" class="bg-white rounded-lg shadow-lg p-6">
                        @csrf
                        @if(isset($quiz))
                            @method('PUT')
                        @endif

                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Quiz Title *</label>
                                    <input type="text" id="title" name="title"
                                           value="{{ old('title', $quiz->title ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           required>
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                    <input type="text" id="subject" name="subject"
                                           value="{{ old('subject', $quiz->subject ?? '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error('subject')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                                    <select id="grade_level" name="grade_level"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Grade Level</option>
                                        @php
                                            $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
                                        @endphp
                                        @foreach($gradeLevels as $level)
                                            <option value="{{ $level }}" {{ (old('grade_level', $quiz->grade_level ?? '') == $level) ? 'selected' : '' }}>
                                                {{ $level }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grade_level')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="video_id" class="block text-sm font-medium text-gray-700 mb-2">Associated Video</label>
                                    <select id="video_id" name="video_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">No associated video</option>
                                        @foreach($videos ?? [] as $video)
                                            <option value="{{ $video->id }}" {{ (old('video_id', $quiz->video_id ?? '') == $video->id) ? 'selected' : '' }}>
                                                {{ $video->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('video_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Settings -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quiz Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Time Limit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Limit</label>
                                    <div class="time-input-group">
                                        <input type="number" id="time_limit_minutes" name="time_limit_minutes"
                                               value="{{ old('time_limit_minutes', $quiz->time_limit_minutes ?? 15) }}"
                                               min="1" max="300" step="1"
                                               class="time-input">
                                        <span class="time-label">minutes</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Set to 0 for no time limit</p>
                                    @error('time_limit_minutes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Difficulty Level -->
                                <div>
                                    <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                                    <select id="difficulty_level" name="difficulty_level"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="easy" {{ (old('difficulty_level', $quiz->difficulty_level ?? 'medium') == 'easy') ? 'selected' : '' }}>
                                            Easy
                                        </option>
                                        <option value="medium" {{ (old('difficulty_level', $quiz->difficulty_level ?? 'medium') == 'medium') ? 'selected' : '' }}>
                                            Medium
                                        </option>
                                        <option value="hard" {{ (old('difficulty_level', $quiz->difficulty_level ?? 'medium') == 'hard') ? 'selected' : '' }}>
                                            Hard
                                        </option>
                                    </select>
                                    <div class="mt-2 flex space-x-2">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full difficulty-easy mr-2"></div>
                                            <span class="text-xs text-gray-600">Easy</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full difficulty-medium mr-2"></div>
                                            <span class="text-xs text-gray-600">Medium</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full difficulty-hard mr-2"></div>
                                            <span class="text-xs text-gray-600">Hard</span>
                                        </div>
                                    </div>
                                    @error('difficulty_level')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Description -->
                        <div class="mb-8">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Describe what this quiz covers...">{{ old('description', $quiz->description ?? '') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quiz Data (JSON) -->
                        <div class="mb-8">
                            <label for="quiz_data" class="block text-sm font-medium text-gray-700 mb-2">Quiz Questions (JSON)</label>
                            <textarea id="quiz_data" name="quiz_data" rows="10"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                                      placeholder='{"questions": []}'></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Enter quiz questions in JSON format. Leave empty to create questions later.
                            </p>
                            @error('quiz_data')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Settings -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Settings</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                           {{ old('is_featured', $quiz->is_featured ?? false) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                                        Featured Quiz
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('admin.content.quizzes.index') }}"
                               class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>
                                {{ isset($quiz) ? 'Update Quiz' : 'Create Quiz' }}
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load existing quiz data if editing
            @if(isset($quiz) && $quiz->quiz_data)
                const quizData = @json($quiz->quiz_data);
                document.getElementById('quiz_data').value = JSON.stringify(quizData, null, 2);
            @endif

            // Format JSON on blur
            document.getElementById('quiz_data').addEventListener('blur', function() {
                try {
                    const parsed = JSON.parse(this.value);
                    this.value = JSON.stringify(parsed, null, 2);
                } catch (e) {
                    // Invalid JSON, leave as is
                }
            });
        });
    </script>
</body>
</html>