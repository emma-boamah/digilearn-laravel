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
        .drawer-backdrop {
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }
        .drawer-slide-panel {
            transition: transform 0.3s ease-in-out;
        }
        .tab-btn-active {
            background-color: #ffffff;
            border-bottom-color: #3b82f6;
            color: #1d4ed8;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .tab-btn-inactive {
            background-color: #f8fafc;
            border-bottom-color: transparent;
            color: #64748b;
        }
        .tab-btn-inactive:hover {
            background-color: #f1f5f9;
            color: #334155;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-4xl mx-auto py-8">
        <!-- Back Button & Page Title -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('admin.contents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-sm text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Contents
            </a>
            <span class="text-xs font-semibold px-3 py-1 bg-gray-200 text-gray-700 rounded-full capitalize">
                {{ $contentType }} Content
            </span>
        </div>

        <!-- Main Form -->
        <form action="{{ route('admin.contents.update', $content->id) }}" method="POST" enctype="multipart/form-data" id="editContentForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="content_type" value="{{ $contentType }}">
            <input type="hidden" name="quiz_data" id="quiz_data_input">
            <input type="hidden" name="quiz_id" id="quiz_id_input" value="{{ $content->quiz_id }}">
            <input type="hidden" name="status" id="status_input" value="">

            @if($contentType === 'video')
                <!-- Tabbed Workspace Navigation Header -->
                <div class="bg-white rounded-t-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                    <div class="flex border-b border-gray-200">
                        <button type="button" id="mainTabBtnVideo" onclick="switchMainTab('video')" class="flex-1 py-4 px-6 text-left border-b-2 font-bold text-sm transition-all tab-btn-active flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                    <i class="fas fa-video text-base"></i>
                                </div>
                                <div>
                                    <span class="block text-gray-900 text-sm font-bold">Video Details</span>
                                    <span class="block text-xs text-gray-500 font-normal mt-0.5">Title, description, subject & grade</span>
                                </div>
                            </div>
                        </button>
                        
                        <button type="button" id="mainTabBtnQuiz" onclick="switchMainTab('quiz')" class="flex-1 py-4 px-6 text-left border-b-2 font-bold text-sm transition-all tab-btn-inactive flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    <i class="fas fa-tasks text-base"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="block text-gray-900 text-sm font-bold">Associated Quiz</span>
                                        <span id="quizTabBadge" class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $content->quiz ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $content->quiz ? 'Linked' : 'No Quiz' }}
                                        </span>
                                    </div>
                                    <span class="block text-xs text-gray-500 font-normal mt-0.5" id="quizTabSubtitle">
                                        {{ $content->quiz ? $content->quiz->title : 'Click to attach or generate quiz' }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            @endif

            <!-- TAB 1: VIDEO LESSON DETAILS & METADATA -->
            <div id="mainTabContentVideo" class="space-y-6">
                
                <!-- Content Summary Info Bar -->
                <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-gray-400 font-bold uppercase tracking-wider block">Content Title</span>
                            <span class="font-bold text-gray-800 text-sm mt-0.5 truncate block">{{ $content->title }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold uppercase tracking-wider block">Content Type</span>
                            <span class="font-semibold text-gray-700 capitalize text-sm mt-0.5 block">{{ $contentType }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold uppercase tracking-wider block">Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ ($content->status ?? 'published') === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }} capitalize mt-0.5">
                                {{ $content->status ?? 'published' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold uppercase tracking-wider block">Uploaded By</span>
                            <span class="font-semibold text-gray-700 text-sm mt-0.5 block truncate">{{ $content->uploader->name ?? 'Admin' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Edit Form Details -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
                    <div class="px-6 py-4 card-header-premium flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-edit text-blue-600 mr-2"></i> Edit Details
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $contentType === 'video' ? 'Lesson / Video Title' : 'Quiz Title' }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title" value="{{ $content->title }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>

                        @if($contentType === 'video' && $content->quiz)
                        <div class="mb-6">
                            <label for="quiz_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Associated Quiz Display Title
                            </label>
                            <input type="text" id="quiz_title" name="quiz_title" value="{{ $content->quiz->title }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   placeholder="Leave empty to use the video's title">
                            <p class="text-xs text-gray-500 mt-1">If specified, this title will be shown on the student quiz dashboard instead of the Lesson title.</p>
                        </div>
                        @endif

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <div id="quill-description-editor" class="bg-white rounded-b-lg border-gray-300 min-h-[120px]"></div>
                            <textarea id="description" name="description" class="hidden">{{ $contentType === 'video' ? $content->description : ($content->video->description ?? '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Displayed below the video player for students. Not for the quiz.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Subject Selection -->
                            <div>
                                <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <select id="subject_id" name="subject_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
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
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                               {{ $content->categories->contains($category->id) ? 'checked' : '' }}
                                               class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        @if($contentType === 'video')
                            <!-- Associated Documents & Featured Status -->
                            <div class="mt-8 border-t pt-6">
                                <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-paperclip text-indigo-600 mr-2"></i> Documents & Options
                                </h3>
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Associated Documents</label>
                                    <div class="max-h-40 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50">
                                        @foreach($availableDocuments as $document)
                                            <label class="flex items-center mb-2 last:mb-0 cursor-pointer">
                                                <input type="checkbox" name="document_ids[]" value="{{ $document->id }}"
                                                       {{ $content->documents->contains($document->id) ? 'checked' : '' }}
                                                       class="mr-2 rounded text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700">{{ $document->title }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_featured" value="1" {{ $content->is_featured ? 'checked' : '' }}
                                               class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="text-sm font-medium text-gray-700">Mark as Featured Content</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Bottom Quick Quiz Switcher Banner on Tab 1 -->
                            <div class="mt-8 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
                                        <i class="fas fa-question text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold uppercase tracking-wider text-indigo-900">Quiz Status</span>
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ $content->quiz ? 'Linked: ' . $content->quiz->title : 'No quiz attached to this lesson' }}
                                        </span>
                                    </div>
                                </div>
                                <button type="button" onclick="switchMainTab('quiz')" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-xs font-bold shadow-md transition-all flex items-center gap-1.5">
                                    Manage Quiz Questions <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 2: ASSOCIATED QUIZ & QUESTION BUILDER -->
            @if($contentType === 'video')
            <div id="mainTabContentQuiz" class="space-y-6 hidden">
                
                <!-- Associated Quiz Summary Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <div id="quizSummaryCard" class="bg-gradient-to-r from-slate-50 to-indigo-50/40 p-5 rounded-xl border border-indigo-100 shadow-sm">
                        @if($content->quiz)
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-md">
                                        <i class="fas fa-question-circle text-lg"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-gray-900 text-base" id="attachedQuizTitleDisplay">
                                                {{ $content->quiz->title }}
                                            </h4>
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                Linked
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-3">
                                            <span><i class="fas fa-layer-group text-indigo-500 mr-1"></i>{{ $content->quiz->subject->name ?? 'General' }}</span>
                                            <span><i class="fas fa-graduation-cap text-indigo-500 mr-1"></i>{{ $content->quiz->grade_level ?? 'All Levels' }}</span>
                                            <span><i class="fas fa-tachometer-alt text-indigo-500 mr-1"></i>{{ ucfirst($content->quiz->difficulty_level ?? 'Medium') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button type="button" onclick="openQuizDrawer('ai')" class="px-3 py-1.5 bg-purple-600 text-white hover:bg-purple-700 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                        <i class="fas fa-magic"></i> AI Add Questions
                                    </button>
                                    <button type="button" onclick="openQuizDrawer('library')" class="px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 border border-gray-300 rounded-lg text-xs font-medium shadow-sm transition-all flex items-center gap-1.5">
                                        <i class="fas fa-exchange-alt text-gray-500"></i> Swap Quiz
                                    </button>
                                    <button type="button" onclick="detachQuiz()" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5">
                                        <i class="fas fa-unlink"></i> Detach
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-6">
                                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800 text-sm">No Quiz Associated</h4>
                                <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 mb-4">
                                    Attach an existing quiz from your library, generate a customized quiz using AI, or create a new blank quiz.
                                </p>
                                <div class="flex items-center justify-center gap-3 flex-wrap">
                                    <button type="button" onclick="openQuizDrawer('library')" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-xs font-bold shadow-md transition-all flex items-center gap-2">
                                        <i class="fas fa-search"></i> Select from Library
                                    </button>
                                    <button type="button" onclick="openQuizDrawer('ai')" class="px-4 py-2 bg-purple-600 text-white hover:bg-purple-700 rounded-lg text-xs font-bold shadow-md transition-all flex items-center gap-2">
                                        <i class="fas fa-magic"></i> Generate with AI
                                    </button>
                                    <button type="button" onclick="openQuizDrawer('create')" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                                        <i class="fas fa-plus-circle text-emerald-600"></i> Create Blank
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quiz Content Editor Component -->
                @php
                    $hasQuiz = $contentType === 'quiz' || ($contentType === 'video' && $content->quiz);
                    $quizModel = $contentType === 'quiz' ? $content : ($content->quiz ?? null);
                @endphp

                <div id="quizEditorWrapper" class="{{ $hasQuiz ? '' : 'hidden' }}">
                    <x-quiz-editor :quiz="$quizModel" role="admin" />
                </div>
            </div>
            @endif

            @if($contentType === 'quiz')
                <!-- Standalone Quiz View Editor -->
                @php
                    $quizModel = $content;
                @endphp
                <div id="quizEditorWrapper">
                    <x-quiz-editor :quiz="$quizModel" role="admin" />
                </div>
            @endif

            <!-- Persistent Submit Buttons Bar (Visible across all tabs) -->
            @php
                $isDraft = false;
                if ($contentType === 'quiz') {
                    $isDraft = ($content->status ?? 'published') === 'draft';
                } elseif ($contentType === 'video' && $content->quiz) {
                    $isDraft = ($content->quiz->status ?? 'published') === 'draft';
                }
            @endphp
            <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-4">
                <a href="{{ route('admin.contents.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors text-sm">
                    Cancel
                </a>
                @if($isDraft)
                    <button type="submit" id="saveDraftBtn" class="px-6 py-2.5 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 shadow-lg shadow-amber-200 transition-all text-sm">
                        <i class="fas fa-save mr-2"></i>Save Draft
                    </button>
                    <button type="submit" id="publishBtn" class="px-8 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg shadow-green-200 transition-all text-sm">
                        <i class="fas fa-check-circle mr-2"></i>Publish
                    </button>
                @else
                    <button type="submit" id="revertDraftBtn" class="px-6 py-2.5 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 shadow-lg shadow-amber-200 transition-all text-sm">
                        <i class="fas fa-undo mr-2"></i>Revert to Draft
                    </button>
                    <button type="submit" id="savePublishedBtn" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all text-sm">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Slide-Over Quiz Management Drawer Panel -->
<div id="quizManagementDrawer" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="drawer-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="absolute inset-0 drawer-backdrop transition-opacity" onclick="closeQuizDrawer()"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
        <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col drawer-slide-panel">
            
            <!-- Drawer Header -->
            <div class="p-6 bg-gradient-to-r from-slate-900 to-indigo-900 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold flex items-center gap-2" id="drawer-title">
                        <i class="fas fa-tasks text-indigo-400"></i> Quiz Management
                    </h2>
                    <p class="text-xs text-indigo-200 mt-0.5">Attach, build or generate quiz for this lesson</p>
                </div>
                <button type="button" onclick="closeQuizDrawer()" class="text-indigo-200 hover:text-white text-xl p-1 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Drawer Tabs -->
            <div class="flex border-b border-gray-200 bg-gray-50">
                <button type="button" id="tabBtnLibrary" onclick="switchDrawerTab('library')" class="flex-1 py-3 px-3 text-xs font-bold uppercase tracking-wider text-center border-b-2 border-indigo-600 text-indigo-600 focus:outline-none transition-all">
                    <i class="fas fa-search mr-1"></i> Library
                </button>
                <button type="button" id="tabBtnAi" onclick="switchDrawerTab('ai')" class="flex-1 py-3 px-3 text-xs font-bold uppercase tracking-wider text-center text-gray-500 hover:text-gray-700 border-b-2 border-transparent focus:outline-none transition-all">
                    <i class="fas fa-magic text-purple-500 mr-1"></i> AI Generator
                </button>
                <button type="button" id="tabBtnCreate" onclick="switchDrawerTab('create')" class="flex-1 py-3 px-3 text-xs font-bold uppercase tracking-wider text-center text-gray-500 hover:text-gray-700 border-b-2 border-transparent focus:outline-none transition-all">
                    <i class="fas fa-plus-circle text-emerald-500 mr-1"></i> Create New
                </button>
            </div>

            <!-- Drawer Content (Scrollable) -->
            <div class="p-6 flex-1 overflow-y-auto space-y-6">
                
                <!-- TAB 1: LIBRARY -->
                <div id="tabContentLibrary" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Search Quizzes</label>
                        <div class="relative">
                            <input type="text" id="librarySearchInput" onkeyup="filterLibraryQuizzes()" placeholder="Search by quiz title..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <div class="space-y-3 mt-4" id="availableQuizzesList">
                        @forelse($availableQuizzes as $quizItem)
                            <div class="quiz-library-item p-4 bg-gray-50 hover:bg-indigo-50/50 rounded-xl border border-gray-200 transition-all flex items-center justify-between gap-3" data-title="{{ strtolower($quizItem->title) }}">
                                <div>
                                    <h5 class="font-bold text-sm text-gray-900">{{ $quizItem->title }}</h5>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <span>{{ $quizItem->subject->name ?? 'General' }}</span> • 
                                        <span>{{ $quizItem->grade_level ?? 'All' }}</span> • 
                                        <span class="capitalize">{{ $quizItem->difficulty_level ?? 'Medium' }}</span>
                                    </p>
                                </div>
                                <button type="button" onclick="attachQuizFromLibrary('{{ $quizItem->id }}', '{{ addslashes($quizItem->title) }}')" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 shadow-sm transition-all flex-shrink-0">
                                    Attach
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 text-sm">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                <p>No existing standalone quizzes found in the library.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- TAB 2: AI GENERATOR -->
                <div id="tabContentAi" class="space-y-4 hidden">
                    <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 text-xs text-purple-800">
                        <i class="fas fa-sparkles text-purple-600 mr-1"></i> AI will automatically draft questions based on your video lesson topic and parameters.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Quiz Topic / Title</label>
                        <input type="text" id="aiDrawerTopic" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none" value="{{ $content->title }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Exam Type</label>
                            <select id="aiDrawerExamType" onchange="toggleAiDrawerYear()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <option value="normal">Standard / Class Quiz</option>
                                <option value="bece">BECE Past Paper</option>
                                <option value="wassce">WASSCE Past Paper</option>
                            </select>
                        </div>
                        <div id="aiDrawerYearContainer" class="hidden">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Exam Year</label>
                            <input type="text" id="aiDrawerYear" placeholder="e.g. 2022" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Question Type</label>
                            <select id="aiDrawerQuizType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <option value="mcq">Multiple Choice (MCQ)</option>
                                <option value="essay">Essay / Open Ended</option>
                                <option value="mixed">Mixed Types</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Questions Count</label>
                            <select id="aiDrawerCount" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <option value="5">5 Questions</option>
                                <option value="10" selected>10 Questions</option>
                                <option value="15">15 Questions</option>
                                <option value="20">20 Questions</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">AI Model Engine</label>
                        <select id="aiDrawerModel" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <option value="gemini" selected>Google Gemini 2.5 (Recommended)</option>
                            <option value="gpt-4o-mini">OpenAI GPT-4o Mini (Fast)</option>
                            <option value="gpt-4o">OpenAI GPT-4o (High Accuracy)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Additional Prompt / Source Material</label>
                        <textarea id="aiDrawerSourceMaterial" rows="3" placeholder="Optional text or specific questions instructions..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                    </div>

                    <button type="button" id="aiDrawerGenerateBtn" onclick="handleDrawerAiGeneration()" class="w-full py-3 bg-purple-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-magic"></i> Generate & Append Questions
                    </button>
                </div>

                <!-- TAB 3: CREATE NEW -->
                <div id="tabContentCreate" class="space-y-4 hidden">
                    <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 text-xs text-emerald-800">
                        <i class="fas fa-plus-circle text-emerald-600 mr-1"></i> Start a fresh quiz for this lesson. Questions can be added manually.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Quiz Title</label>
                        <input type="text" id="newQuizTitleInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" value="{{ $content->title }} Quiz">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Difficulty</label>
                            <select id="newQuizDifficulty" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Time Limit (Min)</label>
                            <input type="number" id="newQuizTimeLimit" value="30" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <button type="button" onclick="initializeNewQuiz()" class="w-full py-3 bg-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-check"></i> Initialize Quiz Editor
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('extra-js')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://unpkg.com/quill-magic-url"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Main Tab Switching Logic (Video Details vs Associated Quiz)
    window.switchMainTab = function(tab) {
        const videoBtn = document.getElementById('mainTabBtnVideo');
        const quizBtn = document.getElementById('mainTabBtnQuiz');
        const videoContent = document.getElementById('mainTabContentVideo');
        const quizContent = document.getElementById('mainTabContentQuiz');

        if (!videoBtn || !quizBtn || !videoContent || !quizContent) return;

        if (tab === 'video') {
            videoBtn.className = 'flex-1 py-4 px-6 text-left border-b-2 font-bold text-sm transition-all tab-btn-active flex items-center justify-between';
            quizBtn.className = 'flex-1 py-4 px-6 text-left border-b-2 font-bold text-sm transition-all tab-btn-inactive flex items-center justify-between';
            videoContent.classList.remove('hidden');
            quizContent.classList.add('hidden');
        } else {
            quizBtn.className = 'flex-1 py-4 px-6 text-left border-b-2 font-bold text-sm transition-all tab-btn-active flex items-center justify-between';
            videoBtn.className = 'flex-1 py-4 px-6 text-left border-b-2 font-bold text-sm transition-all tab-btn-inactive flex items-center justify-between';
            quizContent.classList.remove('hidden');
            videoContent.classList.add('hidden');
        }
    };

    // Form submission handling — wire up status buttons
    const form = document.getElementById('editContentForm');
    const statusInput = document.getElementById('status_input');

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
                statusInput.value = '';
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
        
        const descTextarea = document.getElementById('description');
        if (descTextarea.value) {
            quillDesc.clipboard.dangerouslyPasteHTML(descTextarea.value);
        }
        
        quillDesc.on('text-change', function() {
            if (quillDesc.getText().trim() === '') {
                descTextarea.value = '';
            } else {
                descTextarea.value = quillDesc.root.innerHTML;
            }
        });
    }

    // --- Drawer Panel Controls & Functions ---
    window.openQuizDrawer = function(tab = 'library') {
        document.getElementById('quizManagementDrawer').classList.remove('hidden');
        switchDrawerTab(tab);
    };

    window.closeQuizDrawer = function() {
        document.getElementById('quizManagementDrawer').classList.add('hidden');
    };

    window.switchDrawerTab = function(tab) {
        ['Library', 'Ai', 'Create'].forEach(t => {
            const btn = document.getElementById(`tabBtn${t}`);
            const content = document.getElementById(`tabContent${t}`);
            if (t.toLowerCase() === tab.toLowerCase()) {
                btn.className = 'flex-1 py-3 px-3 text-xs font-bold uppercase tracking-wider text-center border-b-2 border-indigo-600 text-indigo-600 focus:outline-none transition-all';
                content.classList.remove('hidden');
            } else {
                btn.className = 'flex-1 py-3 px-3 text-xs font-bold uppercase tracking-wider text-center text-gray-500 hover:text-gray-700 border-b-2 border-transparent focus:outline-none transition-all';
                content.classList.add('hidden');
            }
        });
    };

    window.toggleAiDrawerYear = function() {
        const type = document.getElementById('aiDrawerExamType').value;
        const container = document.getElementById('aiDrawerYearContainer');
        if (type === 'bece' || type === 'wassce') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    };

    window.filterLibraryQuizzes = function() {
        const query = document.getElementById('librarySearchInput').value.toLowerCase();
        const items = document.querySelectorAll('.quiz-library-item');
        items.forEach(item => {
            const title = item.getAttribute('data-title');
            if (title.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    };

    window.attachQuizFromLibrary = function(quizId, title) {
        document.getElementById('quiz_id_input').value = quizId;
        closeQuizDrawer();
        form.submit();
    };

    window.detachQuiz = function() {
        if (confirm('Are you sure you want to detach this quiz from the video lesson?')) {
            document.getElementById('quiz_id_input').value = '';
            const wrapper = document.getElementById('quizEditorWrapper');
            if (wrapper) wrapper.classList.add('hidden');
            form.submit();
        }
    };

    window.initializeNewQuiz = function() {
        const title = document.getElementById('newQuizTitleInput').value.trim() || 'New Quiz';
        const difficulty = document.getElementById('newQuizDifficulty').value;
        const timeLimit = document.getElementById('newQuizTimeLimit').value;

        document.getElementById('quiz_id_input').value = '';
        
        switchMainTab('quiz');
        
        const wrapper = document.getElementById('quizEditorWrapper');
        if (wrapper) wrapper.classList.remove('hidden');

        const diffSelect = document.getElementById('quiz_difficulty_settings');
        if (diffSelect) diffSelect.value = difficulty;

        const timeInput = document.getElementById('quiz_time_limit');
        if (timeInput) timeInput.value = timeLimit;

        closeQuizDrawer();
        alert('Quiz editor initialized. Add questions manually or use AI Generator to populate questions.');
    };

    window.handleDrawerAiGeneration = async function() {
        const topic = document.getElementById('aiDrawerTopic').value.trim();
        const gradeLevel = document.getElementById('grade_level') ? document.getElementById('grade_level').value : 'JHS 3';
        const examType = document.getElementById('aiDrawerExamType').value;
        const examYear = document.getElementById('aiDrawerYear').value;
        const quizType = document.getElementById('aiDrawerQuizType').value;
        const count = document.getElementById('aiDrawerCount').value;
        const aiModel = document.getElementById('aiDrawerModel').value;
        const sourceMaterial = document.getElementById('aiDrawerSourceMaterial').value;

        if (!topic) {
            alert('Please enter a Quiz Topic/Title');
            return;
        }

        let assembledTopic = topic;
        if (examType === 'bece') {
            assembledTopic = `Set BECE past questions for topic "${topic}"`;
            if (examYear) assembledTopic += ` for year ${examYear}`;
        } else if (examType === 'wassce') {
            assembledTopic = `Set WASSCE past questions for topic "${topic}"`;
            if (examYear) assembledTopic += ` for year ${examYear}`;
        }

        const btn = document.getElementById('aiDrawerGenerateBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating Questions...';

        try {
            const response = await fetch('{{ route('admin.contents.generate-ai-questions') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    topic: assembledTopic,
                    grade_level: gradeLevel,
                    quiz_type: quizType,
                    count: parseInt(count),
                    ai_model: aiModel,
                    use_kuulchat: (examType === 'bece' || examType === 'wassce'),
                    use_kuulchat_year: examYear,
                    source_material: sourceMaterial
                })
            });

            const data = await response.json();
            if (data.success && data.questions && data.questions.length > 0) {
                switchMainTab('quiz');

                const wrapper = document.getElementById('quizEditorWrapper');
                if (wrapper) wrapper.classList.remove('hidden');

                if (typeof addQuestion === 'function') {
                    data.questions.forEach(q => {
                        addQuestion(q.type, q);
                    });
                }

                closeQuizDrawer();
                alert(`Successfully generated and appended ${data.questions.length} questions to the quiz!`);
            } else {
                alert('Generation Error: ' + (data.message || 'Failed to generate questions.'));
            }
        } catch (err) {
            console.error('AI Generation error:', err);
            alert('An error occurred while calling the AI service.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    };
</script>
@endpush