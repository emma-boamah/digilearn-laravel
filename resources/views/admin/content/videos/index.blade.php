@extends('layouts.admin')

@section('title', 'Manage Learning Videos')
@section('page-title', 'Manage Learning Videos')
@section('page-description', 'Upload, edit, and organize educational videos with modern drag & drop interface.')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Added custom CSS with blue, red, white color scheme for video management */
    .video-container {
        max-width: 80rem;
        margin: 0 auto;
        padding: 1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--gray-200);
    }

    .stat-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.primary {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
    }

    .stat-icon.secondary {
        background-color: var(--accent-red-light);
        color: var(--accent-red);
    }

    .stat-icon.accent {
        background-color: #f0f9ff;
        color: #0284c7;
    }

    .upload-section {
        position: relative;
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-200);
        padding: 2rem;
        margin-bottom: 2rem;
        display: none; /* Hidden by default, shown when upload button is clicked */
    }

    .upload-section.show {
        display: block;
    }

    .upload-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .additional-files-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .drag-drop-zone {
        border: 2px dashed var(--gray-300);
        border-radius: 0.75rem;
        padding: 2rem;
        text-align: center;
        background-color: #f8fafc;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .drag-drop-zone:hover {
        background-color: #f1f5f9;
        border-color: var(--primary-blue);
    }

    .drag-drop-zone.drag-over {
        border-color: var(--primary-blue);
        background-color: var(--primary-blue-light);
        transform: scale(1.02);
    }

    .upload-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    .upload-icon.primary {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
    }

    .upload-icon.secondary {
        background-color: var(--accent-red-light);
        color: var(--accent-red);
    }

    .upload-icon.accent {
        background-color: #f0f9ff;
        color: #0284c7;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-300);
        background-color: var(--white);
        color: var(--gray-900);
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px var(--primary-blue-light);
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .btn-upload {
        background-color: var(--primary-blue);
        color: var(--white);
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-upload:hover {
        background-color: var(--primary-blue-hover);
    }

    .btn-secondary {
        background-color: var(--accent-red);
        color: var(--white);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary:hover {
        background-color: var(--accent-red-hover);
    }

    .videos-section {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
    }

    .filter-section {
        background-color: #f8fafc;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .videos-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid var(--gray-200);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .videos-table th {
        background-color: #f8fafc;
        padding: 0.75rem 1.5rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .videos-table td {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        background-color: var(--white);
    }

    .videos-table tr:hover td {
        background-color: #f8fafc;
    }

    .action-btn {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        margin-right: 0.5rem;
        transition: all 0.2s;
    }

    .action-btn.watch {
        background-color: #f0f9ff;
        color: #0284c7;
    }

    .action-btn.edit {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
    }

    .action-btn.delete {
        background-color: var(--accent-red-light);
        color: var(--accent-red);
    }

    .action-btn:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-icon {
        width: 4rem;
        height: 4rem;
        background-color: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: var(--gray-400);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .upload-grid {
            grid-template-columns: 1fr;
        }
        
        .additional-files-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="video-container">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-500);">Total Videos</h3>
                <p style="font-size: 2.25rem; font-weight: 700; color: var(--primary-blue); margin-top: 0.5rem;">{{ $totalVideos }}</p>
                <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">
                    {{ $approvedCount }} approved • {{ $pendingCount }} pending • {{ $rejectedCount }} rejected
                </div>
            </div>
            <div class="stat-icon primary">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-500);">Most Watched Video</h3>
                @if($mostWatchedVideo)
                    <p style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin-top: 0.5rem;">{{ Str::limit($mostWatchedVideo->title, 20) }}</p>
                    <p style="font-size: 0.875rem; color: var(--gray-500);">Views: {{ number_format($mostWatchedVideo->views) }}</p>
                @else
                    <p style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin-top: 0.5rem;">N/A</p>
                    <p style="font-size: 0.875rem; color: var(--gray-500);">No videos yet</p>
                @endif
            </div>
            <div class="stat-icon accent">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-500);">Average Duration</h3>
                <p style="font-size: 2.25rem; font-weight: 700; color: var(--accent-red); margin-top: 0.5rem;">{{ $averageDuration }}</p>
            </div>
            <div class="stat-icon secondary">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Video Review Section -->
    @include('admin.content.videos.review-section')

    <!-- Upload Section - Hidden by default, shown when button is clicked -->
    <div class="upload-section" id="uploadSection">
        <!-- close button -->
        <div style="position: absolute; top: 1rem; right: 1rem;">
            <button type="button" id="closeUploadSectionBtn" style="background: none; border: none; font-size: 1.5rem; color: var(--gray-400); cursor: pointer;" aria-label="Close upload section">
                &times;
            </button>
        </div>
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 id="formHeaderTitle" style="font-size: 1.875rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem;">Upload New Video</h2>
            <p id="formHeaderSubtitle" style="color: var(--gray-600);">Drag and drop your files or click to browse</p>
        </div>

        <form id="videoUploadForm" method="POST" action="{{ route('admin.content.videos.store') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Video Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label for="title" class="form-label">Video Title *</label>
                    <input type="text" name="title" id="title" required class="form-input">
                </div>
                <div>
                    <label for="grade_level" class="form-label">Grade Level</label>
                    <select name="grade_level" id="grade_level" class="form-input">
                        <option value="">Select Grade</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Upload Grid -->
            <div class="upload-grid">
                <!-- Video Upload Zone -->
                <div>
                    <label class="form-label" id="videoFileLabel">Video File *</label>
                    <div class="drag-drop-zone" id="videoDropZone">
                        <input type="file" name="video_file" id="video_file" accept="video/*" style="display: none;">
                        <div class="upload-content">
                            <div class="upload-icon primary">
                                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">Drop your video here</h3>
                            <p style="color: var(--gray-600); margin-bottom: 1rem;">or click to browse files</p>
                            <div class="btn-upload" style="display: inline-flex;">
                                Select Video File
                            </div>
                            <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.75rem;">Max size: 600MB • Duration: up to 60 minutes</p>
                        </div>
                    </div>
                </div>

                <!-- Thumbnail Upload Zone -->
                <div>
                    <label class="form-label">Thumbnail</label>
                    <div class="drag-drop-zone" id="thumbnailDropZone" style="height: 12rem;">
                        <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/*" style="display: none;">
                        <div class="upload-content" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                            <div class="upload-icon accent" style="width: 3rem; height: 3rem;">
                                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h4 style="font-weight: 500; color: var(--gray-900); margin-bottom: 0.25rem;">Add Thumbnail</h4>
                            <p style="font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.75rem;">JPG, PNG, GIF</p>
                            <div style="background-color: #f0f9ff; color: #0284c7; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; display: inline-block;">
                                Browse
                            </div>
                            <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">Max: 2MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Files Grid -->
            <div class="additional-files-grid">
                <!-- Document Upload Zone -->
                <div>
                    <label class="form-label">Attach Slides / Doc (Optional)</label>
                    <div class="drag-drop-zone" id="documentDropZone">
                        <input type="file" name="document_file" id="document_file" accept=".pdf,.doc,.docx,.ppt,.pptx" style="display: none;">
                        <div class="upload-content">
                            <div class="upload-icon secondary" style="width: 3rem; height: 3rem;">
                                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h4 style="font-weight: 500; color: var(--gray-900); margin-bottom: 0.25rem;">Add Slides or Document</h4>
                            <p style="font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.75rem;">PDF, DOC, PPT, PPTX</p>
                            <div class="btn-secondary" style="font-size: 0.875rem;">
                                Browse Files
                            </div>
                            <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">Max: 10MB</p>
                        </div>
                    </div>
                </div>

                <!-- Quiz Selection -->
                <div>
                    <label for="quiz_id" class="form-label">Related Quiz (Optional)</label>
                    <div style="background-color: var(--white); border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem;">
                        <select name="quiz_id" id="quiz_id" class="form-input">
                            <option value="">No Quiz</option>
                            @foreach($quizzes as $quiz)
                                <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                            @endforeach
                        </select>
                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">Link a quiz to this video for enhanced learning</p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 2rem;">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea name="description" id="description" rows="4" class="form-input" placeholder="Describe what students will learn from this video..."></textarea>
            </div>

            <!-- Featured Toggle -->
            @if(Auth::user()->is_superuser)
            <div style="display: flex; align-items: center; margin-bottom: 2rem; padding: 1rem; background-color: #f8fafc; border-radius: 0.75rem; border: 1px solid var(--gray-200);">
                <input type="checkbox" name="is_featured" id="is_featured" style="height: 1.25rem; width: 1.25rem; color: var(--primary-blue); border: 1px solid var(--gray-300); border-radius: 0.25rem;">
                <label for="is_featured" style="margin-left: 0.75rem; font-size: 0.875rem; font-weight: 500; color: var(--gray-900);">
                    Mark as Featured Video
                    <span style="display: block; font-size: 0.75rem; color: var(--gray-500);">Featured videos appear prominently to students</span>
                </label>
            </div>
            @endif

            <!-- Current Files Section (Edit Mode Only) -->
            <div id="currentFilesSection" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background-color: #f8fafc; border: 1px solid var(--gray-200); border-radius: 0.75rem;">
                <h4 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem; font-size: 0.95rem;">Current Attached Files</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <!-- Thumbnail info -->
                    <div id="currentThumbnailInfo" style="display: none; align-items: center; gap: 1rem;">
                        <img id="currentThumbnailImg" src="" alt="Thumbnail" style="height: 4rem; width: 6rem; object-fit: cover; border-radius: 0.375rem; border: 1px solid var(--gray-200);">
                        <div>
                            <span style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-900);">Current Thumbnail</span>
                            <span style="font-size: 0.75rem; color: var(--gray-500);">Uploading a new thumbnail will replace this.</span>
                        </div>
                    </div>
                    <!-- Document/Slides info -->
                    <div id="currentDocumentInfo" style="display: none; align-items: center; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg style="width: 1.5rem; height: 1.5rem; color: var(--gray-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <span style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-900);">Slides / Document Attached</span>
                                <a id="currentDocumentLink" href="" target="_blank" style="font-size: 0.75rem; color: var(--primary-blue); text-decoration: underline;">View Current File</a>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; margin-left: auto;">
                            <input type="checkbox" name="delete_document" id="delete_document" value="1" style="height: 1rem; width: 1rem; color: var(--primary-blue); border: 1px solid var(--gray-300); border-radius: 0.25rem;">
                            <label for="delete_document" style="margin-left: 0.5rem; font-size: 0.75rem; font-weight: 500; color: var(--accent-red); cursor: pointer;">
                                Remove Attached Slides
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="hideUploadBtn" style="background: none; border: none; color: var(--gray-500); cursor: pointer; text-decoration: underline;">
                    Cancel
                </button>
                <button type="submit" id="submitFormBtn" class="btn-upload">
                    <span id="submitFormBtnIcon">
                        <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: middle; margin-right: 0.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </span>
                    <span id="submitFormBtnText">Upload Video</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Videos List -->
    <div class="videos-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--gray-900);">All Videos</h2>
            <!-- Updated button to show upload dialog instead of scrolling -->
            <button id="showUploadBtn" class="btn-upload">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8l-8-8-8 8"></path>
                </svg>
                Upload New Video
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="filter-section">
            <form action="{{ route('admin.content.videos.index') }}" method="GET" class="filter-grid">
                <div>
                    <label for="search" class="form-label">Search Title/Description</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search videos..." class="form-input">
                </div>
                <div>
                    <label for="grade_level_filter" class="form-label">Filter by Grade</label>
                    <select name="grade_level" id="grade_level_filter" class="form-input">
                        <option value="">All Grades</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="is_featured_filter" class="form-label">Featured Status</label>
                    <select name="is_featured" id="is_featured_filter" class="form-input">
                        <option value="">All</option>
                        <option value="1" {{ request('is_featured') === '1' ? 'selected' : '' }}>Featured</option>
                        <option value="0" {{ request('is_featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                    </select>
                </div>
                <div>
                    <label for="upload_date" class="form-label">Upload Date</label>
                    <input type="date" name="upload_date" id="upload_date" value="{{ request('upload_date') }}" class="form-input">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn-upload">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.content.videos.index') }}" style="background-color: var(--gray-100); color: var(--gray-500); padding: 0.75rem 1rem; border-radius: 0.5rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Videos Table -->
        <div style="overflow-x: auto;">
            <table class="videos-table">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th style="width: 6rem;">Grade</th>
                        <th style="width: 8rem;">Slides</th>
                        <th style="width: 6rem;">Duration</th>
                        <th style="width: 6rem;">Views</th>
                        <th style="width: 6rem;">Featured</th>
                        <th style="width: 16rem;">Uploader</th>
                        <th style="width: 10rem;">Uploaded Date</th>
                        <th style="width: 8rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($videos as $video)
                    <tr>
                        <td>
                            @if($video->thumbnail_path)
                                <img src="{{ Storage::url($video->thumbnail_path) }}" alt="{{ $video->title }} Thumbnail" style="height: 4rem; width: 6rem; object-fit: cover; border-radius: 0.375rem; border: 1px solid var(--gray-200);">
                            @else
                                <div style="height: 4rem; width: 6rem; background-color: var(--gray-100); border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; color: var(--gray-400); font-size: 0.75rem; border: 1px solid var(--gray-200);">No Thumbnail</div>
                            @endif
                        </td>
                        <td style="font-size: 0.875rem; font-weight: 500; color: var(--gray-900);">{{ $video->title }}</td>
                        <td style="font-size: 0.875rem; color: var(--gray-500);">{{ $video->grade_level ?? 'N/A' }}</td>
                        <td>
                            @if($video->document_path)
                                @php
                                    $ext = strtolower(pathinfo($video->document_path, PATHINFO_EXTENSION));
                                    $isPpt = in_array($ext, ['ppt', 'pptx']);
                                @endphp
                                <a href="{{ Storage::url($video->document_path) }}" target="_blank" 
                                   title="Download attached slides"
                                   style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 0.375rem; text-decoration: none; 
                                          background-color: {{ $isPpt ? '#eff6ff' : '#fef2f2' }}; 
                                          color: {{ $isPpt ? '#1d4ed8' : '#b91c1c' }}; 
                                          border: 1px solid {{ $isPpt ? '#bfdbfe' : '#fecaca' }};">
                                    @if($isPpt)
                                        <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        PPT/Slides
                                    @else
                                        <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        PDF/Doc
                                    @endif
                                </a>
                            @else
                                <span style="font-size: 0.75rem; color: var(--gray-400);">None</span>
                            @endif
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-500);">
                            {{ gmdate("H:i:s", $video->duration_seconds) }}
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-500);">{{ number_format($video->views) }}</td>
                        <td>
                            <form action="{{ route('admin.content.videos.toggle-feature', $video) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" style="background: none; border: none; cursor: pointer; color: {{ $video->is_featured ? 'var(--accent-red)' : 'var(--gray-400)' }}; transition: color 0.2s;">
                                    <svg style="width: 1.25rem; height: 1.25rem;" fill="{{ $video->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </button>
                            </form>
                        </td>
                        <td>
                            @if($video->uploader)
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <x-user-avatar :user="$video->uploader" :size="32" />
                                    <div style="font-size: 0.875rem;">
                                        <div style="color: var(--gray-900); font-weight: 500;">{{ $video->uploader->name }}</div>
                                        <div style="color: var(--gray-500);">{{ $video->uploader->email }}</div>
                                        @if($video->uploader_ip)
                                            <div style="font-size: 0.75rem; color: var(--gray-500);">IP: {{ $video->uploader_ip }}</div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span style="font-size: 0.875rem; color: var(--gray-500);">Unknown</span>
                            @endif
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-500);">{{ $video->created_at->format('M d, Y') }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <a href="{{ Storage::url($video->video_path) }}" target="_blank" class="action-btn watch">
                                    Watch
                                </a>
                                <button data-video-id="{{ $video->id }}" class="action-btn edit editVideoBtn">
                                    Edit
                                </button>
                                <form action="{{ route('admin.content.videos.destroy', $video) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="empty-state">
                            <div class="empty-icon">
                                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 style="font-size: 1.125rem; font-weight: 500; color: var(--gray-900); margin-bottom: 0.5rem;">No videos found</h3>
                            <p style="color: var(--gray-600); margin-bottom: 1rem;">Get started by uploading your first video</p>
                            <button onclick="showUploadSection()" class="btn-upload">
                                Upload Video
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 1.5rem;">
            {{ $videos->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function showUploadSection() {
        document.getElementById('uploadSection').classList.add('show');
        document.getElementById('uploadSection').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }

    function hideUploadSection() {
        document.getElementById('uploadSection').classList.remove('show');
    }

    // Drag and Drop Functionality
    function setupDragAndDrop() {
        const dropZones = [
            { zone: 'videoDropZone', input: 'video_file', accept: 'video' },
            { zone: 'thumbnailDropZone', input: 'thumbnail_file', accept: 'image' },
            { zone: 'documentDropZone', input: 'document_file', accept: 'document' }
        ];

        dropZones.forEach(({ zone, input, accept }) => {
            const dropZone = document.getElementById(zone);
            const fileInput = document.getElementById(input);

            if (!dropZone || !fileInput) return;

            // Click to browse
            dropZone.addEventListener('click', () => fileInput.click());

            // Drag events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
            });

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    const file = files[0];
                    if (validateFile(file, accept)) {
                        fileInput.files = files;
                        updateDropZoneUI(dropZone, file, accept);
                    }
                }
            }

            // File input change
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    if (validateFile(this.files[0], accept)) {
                        updateDropZoneUI(dropZone, this.files[0], accept);
                    } else {
                        this.value = ''; // Reset input on invalid file
                    }
                }
            });
        });
    }

    function validateFile(file, accept) {
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        const validExtensions = {
            video: ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm'],
            image: ['jpeg', 'jpg', 'png', 'gif'],
            document: ['pdf', 'doc', 'docx', 'ppt', 'pptx']
        };

        const validTypes = {
            video: ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm'],
            image: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
            document: [
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                'application/vnd.ms-powerpoint', 
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/octet-stream' // fallback
            ]
        };

        const hasValidExt = validExtensions[accept].includes(fileExt);
        const hasValidMime = file.type ? validTypes[accept].includes(file.type) : false;

        if (!hasValidExt && !hasValidMime) {
            alert(`Please select a valid ${accept} file (.${validExtensions[accept].join(', .')}).`);
            return false;
        }

        const maxSizes = {
            video: 600 * 1024 * 1024, // 600MB
            image: 2 * 1024 * 1024,   // 2MB
            document: 10 * 1024 * 1024 // 10MB
        };

        if (file.size > maxSizes[accept]) {
            const sizeInMB = (maxSizes[accept] / (1024 * 1024)).toFixed(0);
            alert(`File size exceeds the maximum limit of ${sizeInMB}MB for ${accept} files.`);
            return false;
        }

        return true;
    }

    function updateDropZoneUI(dropZone, file, accept) {
        const uploadContent = dropZone.querySelector('.upload-content');
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';

        uploadContent.innerHTML = `
            <div class="upload-icon primary" style="width: 3rem; height: 3rem;">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h4 style="font-weight: 500; color: var(--gray-900); margin-bottom: 0.25rem;">${fileName}</h4>
            <p style="font-size: 0.75rem; color: var(--gray-500); margin-bottom: 0.5rem;">${fileSize}</p>
            <div style="background-color: var(--primary-blue-light); color: var(--primary-blue); padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; display: inline-block;">
                File Selected
            </div>
        `;
    }

    function resetFormToCreateMode() {
        const form = document.getElementById('videoUploadForm');
        if (!form) return;

        // Reset title and subtitle
        document.getElementById('formHeaderTitle').innerText = 'Upload New Video';
        document.getElementById('formHeaderSubtitle').innerText = 'Drag and drop your files or click to browse';

        // Revert action to store route
        form.action = "{{ route('admin.content.videos.store') }}";

        // Remove PUT method if exists
        const methodField = form.querySelector('input[name="_method"]');
        if (methodField) {
            methodField.remove();
        }

        // Reset inputs
        form.reset();

        // Reset dropzones to original state
        resetDropZoneUI('videoDropZone', 'Drop your video here', 'or click to browse files', 'Select Video File', 'Max size: 600MB • Duration: up to 60 minutes');
        resetDropZoneUI('thumbnailDropZone', 'Add Thumbnail', 'JPG, PNG, GIF', 'Browse', 'Max: 2MB');
        resetDropZoneUI('documentDropZone', 'Add Slides or Document', 'PDF, DOC, PPT, PPTX', 'Browse Files', 'Max: 10MB');

        // Restore video required label
        const videoLabel = document.getElementById('videoFileLabel');
        if (videoLabel) {
            videoLabel.innerHTML = 'Video File *';
        }

        // Hide current files section
        const currentFilesSection = document.getElementById('currentFilesSection');
        if (currentFilesSection) {
            currentFilesSection.style.display = 'none';
        }

        // Restore submit button
        document.getElementById('submitFormBtnText').innerText = 'Upload Video';
        document.getElementById('submitFormBtnIcon').innerHTML = `
            <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: middle; margin-right: 0.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
        `;
    }

    function resetDropZoneUI(zoneId, title, sub, btnText, sizeInfo) {
        const dropZone = document.getElementById(zoneId);
        if (!dropZone) return;
        const uploadContent = dropZone.querySelector('.upload-content');
        if (!uploadContent) return;

        let iconColor = 'primary';
        let svgPath = '';
        if (zoneId === 'videoDropZone') {
            iconColor = 'primary';
            svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>';
        } else if (zoneId === 'thumbnailDropZone') {
            iconColor = 'accent';
            svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>';
        } else {
            iconColor = 'secondary';
            svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
        }

        const isThumb = zoneId === 'thumbnailDropZone';
        
        uploadContent.innerHTML = isThumb ? `
            <div class="upload-icon ${iconColor}" style="width: 3rem; height: 3rem;">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${svgPath}
                </svg>
            </div>
            <h4 style="font-weight: 500; color: var(--gray-900); margin-bottom: 0.25rem;">${title}</h4>
            <p style="font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.75rem;">${sub}</p>
            <div style="background-color: #f0f9ff; color: #0284c7; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; display: inline-block;">
                ${btnText}
            </div>
            <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">${sizeInfo}</p>
        ` : `
            <div class="upload-icon ${iconColor}">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${svgPath}
                </svg>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 0.5rem;">${title}</h3>
            <p style="color: var(--gray-600); margin-bottom: 1rem;">${sub}</p>
            <div class="btn-${zoneId === 'videoDropZone' ? 'upload' : 'secondary'}" style="display: inline-flex;">
                ${btnText}
            </div>
            <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.75rem;">${sizeInfo}</p>
        `;
    }

    function loadVideoForEdit(videoId) {
        // Reset form first
        resetFormToCreateMode();

        // Perform AJAX request to fetch video data
        fetch(`/admin/content/videos/${videoId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load video details.');
            }
            return response.json();
        })
        .then(video => {
            const form = document.getElementById('videoUploadForm');
            if (!form) return;

            // Change header to Edit
            document.getElementById('formHeaderTitle').innerText = `Edit Video: ${video.title}`;
            document.getElementById('formHeaderSubtitle').innerText = 'Modify video details, thumbnail, or attached slides below';

            // Change form action to update route
            form.action = `/admin/content/videos/${video.seo_url}`;

            // Inject PUT method
            let methodField = form.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';

            // Fill text/select inputs
            document.getElementById('title').value = video.title || '';
            document.getElementById('grade_level').value = video.grade_level || '';
            document.getElementById('quiz_id').value = video.quiz_id || '';
            document.getElementById('description').value = video.description || '';
            
            const isFeatured = document.getElementById('is_featured');
            if (isFeatured) {
                isFeatured.checked = !!video.is_featured;
            }

            // Mark video file optional
            const videoLabel = document.getElementById('videoFileLabel');
            if (videoLabel) {
                videoLabel.innerHTML = 'Video File (Optional)';
            }

            // Setup current files section
            const currentFilesSection = document.getElementById('currentFilesSection');
            const currentThumbnailInfo = document.getElementById('currentThumbnailInfo');
            const currentThumbnailImg = document.getElementById('currentThumbnailImg');
            const currentDocumentInfo = document.getElementById('currentDocumentInfo');
            const currentDocumentLink = document.getElementById('currentDocumentLink');
            const deleteDocumentCheckbox = document.getElementById('delete_document');

            let hasFiles = false;

            if (video.thumbnail_path) {
                currentThumbnailImg.src = `/storage/${video.thumbnail_path}`;
                currentThumbnailInfo.style.display = 'flex';
                hasFiles = true;
            } else {
                currentThumbnailInfo.style.display = 'none';
            }

            if (video.document_path) {
                currentDocumentLink.href = `/storage/${video.document_path}`;
                currentDocumentInfo.style.display = 'flex';
                if (deleteDocumentCheckbox) {
                    deleteDocumentCheckbox.checked = false;
                }
                hasFiles = true;
            } else {
                currentDocumentInfo.style.display = 'none';
            }

            if (hasFiles && currentFilesSection) {
                currentFilesSection.style.display = 'block';
            } else if (currentFilesSection) {
                currentFilesSection.style.display = 'none';
            }

            // Change submit button
            document.getElementById('submitFormBtnText').innerText = 'Save Changes';
            document.getElementById('submitFormBtnIcon').innerHTML = `
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: middle; margin-right: 0.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;

            // Open the upload section visually
            showUploadSection();
        })
        .catch(error => {
            console.error(error);
            alert('Error loading video data. Please try again.');
        });
    }

    // Initialize drag and drop when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        setupDragAndDrop();

        // Handle Edit Video button click via event delegation
        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.editVideoBtn');
            if (editBtn) {
                e.preventDefault();
                const videoId = editBtn.getAttribute('data-video-id');
                loadVideoForEdit(videoId);
            }
        });

        // Show upload section
        const showUploadBtn = document.getElementById('showUploadBtn');
        if (showUploadBtn) {
            showUploadBtn.addEventListener('click', function() {
                resetFormToCreateMode();
                showUploadSection();
            });
        }

        // Close (X) button for upload section
        const closeBtn = document.getElementById('closeUploadSectionBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                hideUploadSection();
                resetFormToCreateMode();
            });
        }

        // Hide upload section
        const hideUploadBtn = document.getElementById('hideUploadBtn');
        if (hideUploadBtn) {
            hideUploadBtn.addEventListener('click', function() {
                hideUploadSection();
                resetFormToCreateMode();
            });
        }

        // Empty state upload button
        const emptyUploadBtn = document.querySelector('.empty-state .btn-upload');
        if (emptyUploadBtn) {
            emptyUploadBtn.addEventListener('click', function() {
                resetFormToCreateMode();
                showUploadSection();
            });
        }

        // Add form validation
        const uploadForm = document.getElementById('videoUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                const videoFile = document.getElementById('video_file');
                const titleInput = document.querySelector('input[name="title"]');
                
                const isEditMode = uploadForm.querySelector('input[name="_method"]')?.value === 'PUT';
                
                // Only require video file when creating a new record
                if (!isEditMode && (!videoFile.files || videoFile.files.length === 0)) {
                    e.preventDefault();
                    alert('Please select a video file to upload.');
                    return false;
                }
                
                // Check if title is provided
                if (!titleInput.value.trim()) {
                    e.preventDefault();
                    alert('Please provide a title for the video.');
                    titleInput.focus();
                    return false;
                }
            });
        }
    });
</script>
@endpush
@endsection
