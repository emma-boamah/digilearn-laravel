@extends('layouts.admin')

@section('title', 'Contents Management')
@section('page-title', 'Contents')
@section('page-description', 'Manage all your videos, documents, and quizzes')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Content Table Styles - YouTube-inspired */
    .content-table-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .content-table {
        width: 100%;
        border-collapse: collapse;
    }

    .content-table thead {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }

    .content-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .content-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
    }

    .content-table tbody tr:hover {
        background: #f8fafc;
    }

    .content-table tbody tr:last-child {
        border-bottom: none;
    }

    .content-table td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #334155;
    }

    /* Video Column */
    .video-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
    }

    .video-thumbnail {
        position: relative;
        width: 120px;
        height: 68px;
        border-radius: 6px;
        overflow: hidden;
        flex-shrink: 0;
        background: #000;
    }

    .video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-thumbnail-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 1.5rem;
    }

    .video-duration {
        position: absolute;
        bottom: 4px;
        right: 4px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .video-info {
        flex: 1;
        min-width: 0;
    }

    .video-title {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .video-description {
        font-size: 0.75rem;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Date Column */
    .date-cell {
        min-width: 140px;
    }

    .date-primary {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .date-secondary {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Stats Columns */
    .stats-cell {
        text-align: center;
        font-weight: 500;
        color: #1e293b;
        min-width: 80px;
    }

    /* Badge Styles */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .badge-available {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-none {
        background: #f1f5f9;
        color: #64748b;
    }

    .badge-mcq {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-essay {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-mixed {
        background: #e0e7ff;
        color: #4338ca;
    }

    /* Email Column */
    .email-cell {
        font-size: 0.8125rem;
        color: #475569;
        min-width: 180px;
    }

    /* Actions Column */
    .actions-cell {
        text-align: right;
        min-width: 100px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .action-btn:hover {
        background: #f1f5f9;
        color: #2563eb;
    }

    .action-btn.delete:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Checkbox Column */
    .checkbox-cell {
        width: 48px;
        padding-left: 20px;
    }

    .checkbox-cell input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #2563eb;
    }

    /* Toolbar */
    .toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toolbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .toolbar-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .toolbar-btn.primary {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .toolbar-btn.primary:hover {
        background: #1d4ed8;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: white;
        min-width: 300px;
    }

    .search-box input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 0.875rem;
        color: #1e293b;
    }

    .search-box input::placeholder {
        color: #94a3b8;
    }

    .search-box i {
        color: #94a3b8;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 64px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748b;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.875rem;
    }

    /* Filter Tabs */
    .filter-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 24px;
    }

    .filter-tab {
        padding: 12px 20px;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 500;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .filter-tab:hover {
        color: #2563eb;
    }

    .filter-tab.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    /* Upload Modal Styles */
    .upload-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .upload-modal.show {
        display: flex;
    }

    .upload-form {
        background: white;
        border-radius: 12px;
        padding: 24px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .file-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
    }

    .file-upload-area:hover,
    .file-upload-area.dragover {
        border-color: #2563eb;
        background: #f0f9ff;
    }

    .file-upload-area.has-video {
        padding: 0;
        border: none;
        background: transparent;
    }

    .video-preview {
        width: 100%;
        aspect-ratio: 16/9;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .video-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumbnail-preview {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
        display: none;
    }

    .remove-thumbnail-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(0,0,0,0.6);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        font-size: 16px;
        transition: background 0.2s ease;
    }

    .remove-thumbnail-btn:hover {
        background: rgba(0,0,0,0.8);
    }

    /* Step Indicators */
    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .step-indicator.active {
        opacity: 1;
    }

    .step-indicator.completed {
        opacity: 1;
    }

    .step-indicator.completed .step-number {
        background: #10b981;
        color: white;
    }

    .step-indicator.active .step-number {
        background: #2563eb;
        color: white;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .step-label {
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        text-align: center;
        white-space: nowrap;
    }

    .step-connector {
        width: 60px;
        height: 2px;
        background: #e5e7eb;
        margin: 0 8px;
    }

    .step-pane {
        display: none;
    }

    .step-pane.active {
        display: block;
    }

    .question-item {
        position: relative;
    }

    .question-item .remove-question {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        font-size: 16px;
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .content-table-container {
            overflow-x: auto;
        }

        .content-table {
            min-width: 1200px;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Stats Overview -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_videos']) }}</div>
            <div class="stat-label">Videos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_documents']) }}</div>
            <div class="stat-label">Documents</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_quizzes']) }}</div>
            <div class="stat-label">Quizzes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_views']) }}</div>
            <div class="stat-label">Total Views</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('admin.contents.index', ['q' => $query, 'sort' => $sort]) }}"
           class="filter-tab {{ $type === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.contents.index', ['q' => $query, 'type' => 'videos', 'sort' => $sort]) }}"
           class="filter-tab {{ $type === 'videos' ? 'active' : '' }}">Videos</a>
        <a href="{{ route('admin.contents.index', ['q' => $query, 'type' => 'documents', 'sort' => $sort]) }}"
           class="filter-tab {{ $type === 'documents' ? 'active' : '' }}">Documents</a>
        <a href="{{ route('admin.contents.index', ['q' => $query, 'type' => 'quizzes', 'sort' => $sort]) }}"
           class="filter-tab {{ $type === 'quizzes' ? 'active' : '' }}">Quizzes</a>
    </div>

    <!-- Content Table -->
    <div class="content-table-container">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <button class="toolbar-btn" id="filterBtn">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search contents..." value="{{ $query }}" id="searchInput">
                </div>
            </div>
            <button class="toolbar-btn primary" id="uploadBtn">
                <i class="fas fa-upload"></i>
                Upload
            </button>
        </div>

        <!-- Table -->
        @if($contents->count() > 0)
        <table class="content-table">
            <thead>
                <tr>
                    <th class="checkbox-cell">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th>Content</th>
                    <th>Date</th>
                    <th class="stats-cell">Views</th>
                    <th class="stats-cell">Comments</th>
                    <th>Documents</th>
                    <th>Quiz</th>
                    <th>Quiz Type</th>
                    <th>Quiz Ratings</th>
                    <th>Uploader</th>
                    <th class="actions-cell"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($contents as $content)
                <tr>
                    <td class="checkbox-cell">
                        <input type="checkbox" class="content-checkbox" value="{{ $content->id }}">
                    </td>
                    <td>
                        <div class="video-cell">
                            <div class="video-thumbnail">
                                @if($content->content_type === 'video' && $content->thumbnail_path)
                                    <img src="{{ asset('storage/' . $content->thumbnail_path) }}"
                                          alt="{{ $content->title }}">
                                @else
                                    <div class="video-thumbnail-placeholder">
                                        @if($content->content_type === 'video')
                                            <i class="fas fa-play"></i>
                                        @elseif($content->content_type === 'document')
                                            <i class="fas fa-file-alt"></i>
                                        @else
                                            <i class="fas fa-question-circle"></i>
                                        @endif
                                    </div>
                                @endif
                                @if($content->content_type === 'video' && $content->duration_formatted && $content->duration_formatted !== '00:00:00')
                                    <span class="video-duration">{{ $content->duration_formatted }}</span>
                                @endif
                            </div>
                            <div class="video-info">
                                <div class="video-title">{{ $content->title }}</div>
                                @if($content->description)
                                    <div class="video-description">{{ $content->description }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="date-cell">
                        <div class="date-primary">{{ $content->published_date }}</div>
                        <div class="date-secondary">{{ $content->status }}</div>
                    </td>
                    <td class="stats-cell">{{ number_format($content->views) }}</td>
                    <td class="stats-cell">{{ number_format($content->comments_count) }}</td>
                    <td>
                        @if($content->content_type === 'video')
                            @if($content->documents_count > 0 || $content->document_path)
                                <span class="badge badge-available">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            @else
                                <span class="badge badge-none">—</span>
                            @endif
                        @else
                            <span class="badge badge-none">—</span>
                        @endif
                    </td>
                    <td>
                        @if($content->content_type === 'video')
                            @if($content->quizzes_count > 0 || $content->quiz_id)
                                <span class="badge badge-available">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            @else
                                <span class="badge badge-none">—</span>
                            @endif
                        @else
                            <span class="badge badge-none">—</span>
                        @endif
                    </td>
                    <td>
                        @if($content->content_type === 'video' && ($content->quizzes_count > 0 || $content->quiz_id))
                            @php
                                // Get quiz types from related quizzes
                                $quizzes = $content->quizzes ?? collect();

                                // Also check the single quiz if it exists
                                if($content->quiz_id && $content->quiz) {
                                    $quizzes = $quizzes->push($content->quiz);
                                }

                                $hasMcq = $quizzes->contains(function($quiz) {
                                    $quizData = json_decode($quiz->quiz_data ?? '{}', true);
                                    return collect($quizData['questions'] ?? [])->contains(function($q) {
                                        return ($q['type'] ?? '') === 'mcq';
                                    });
                                });
                                $hasEssay = $quizzes->contains(function($quiz) {
                                    $quizData = json_decode($quiz->quiz_data ?? '{}', true);
                                    return collect($quizData['questions'] ?? [])->contains(function($q) {
                                        return ($q['type'] ?? '') === 'essay';
                                    });
                                });

                                if($hasMcq && $hasEssay) {
                                    $quizType = 'Mixed';
                                } elseif($hasMcq) {
                                    $quizType = 'MCQ';
                                } elseif($hasEssay) {
                                    $quizType = 'Essay';
                                } else {
                                    $quizType = 'Unknown';
                                }
                            @endphp
                            @if($quizType === 'MCQ')
                                <span class="badge badge-mcq">MCQ</span>
                            @elseif($quizType === 'Essay')
                                <span class="badge badge-essay">Essay</span>
                            @elseif($quizType === 'Mixed')
                                <span class="badge badge-mixed">Mixed</span>
                            @else
                                <span class="badge badge-none">—</span>
                            @endif
                        @else
                            <span class="badge badge-none">—</span>
                        @endif
                    </td>
                    <td>
                        @if($content->content_type === 'quiz')
                            @if($content->average_rating)
                                <div class="flex items-center gap-1">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $content->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-gray-600 ml-1">{{ $content->average_rating }} ({{ $content->total_ratings }})</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-500">No ratings yet</span>
                            @endif
                        @else
                            <span class="badge badge-none">—</span>
                        @endif
                    </td>
                    <td class="email-cell">{{ $content->uploader_email ?? $content->uploader_name }}</td>
                    <td class="actions-cell">
                        <button class="action-btn" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="action-btn" title="More">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No content found</h3>
            <p>Get started by uploading your first video, document, or quiz.</p>
        </div>
        @endif
    </div>
</div>

<!-- Multi-Step Upload Wizard -->
<div id="uploadModal" class="upload-modal">
    <div class="upload-form">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Create Content Package</h2>
            <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Step Indicators -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div class="step-indicator active" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-label">Video</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-indicator" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-label">Documents</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-indicator" data-step="3">
                    <span class="step-number">3</span>
                    <span class="step-label">Quiz</span>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="step-content">
            <!-- Step 1: Video Upload -->
            <div class="step-pane active" id="step1">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Video</h3>

                <!-- Video Preview -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video Preview</label>
                    <div id="videoPreviewContainer" class="video-preview hidden">
                        <video id="videoPreview" controls></video>
                        <img id="thumbnailPreview" class="thumbnail-preview" alt="Thumbnail preview">
                        <button id="removeThumbnailBtn" class="remove-thumbnail-btn hidden">&times;</button>
                    </div>
                </div>

                <!-- File Upload Area -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video File</label>
                    <div id="fileUploadArea" class="file-upload-area">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Click to upload or drag and drop</p>
                        <p class="text-sm text-gray-500">MP4, MOV, AVI up to 600MB</p>
                    </div>
                    <input type="file" id="fileInput" class="hidden" accept=".mp4,.mov,.avi">
                </div>

                <!-- Title -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" id="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Grade Level -->
                <div class="mb-4">
                    <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                    <select id="grade_level"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Grade Level</option>
                        <option value="Primary 1">Primary 1</option>
                        <option value="Primary 2">Primary 2</option>
                        <option value="Primary 3">Primary 3</option>
                        <option value="JHS 1">JHS 1</option>
                        <option value="JHS 2">JHS 2</option>
                        <option value="JHS 3">JHS 3</option>
                        <option value="SHS 1">SHS 1</option>
                        <option value="SHS 2">SHS 2</option>
                        <option value="SHS 3">SHS 3</option>
                    </select>
                </div>

                <!-- Thumbnail -->
                <div class="mb-4">
                    <label for="thumbnail_file" class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Image <span class="text-sm text-gray-500">(Optional)</span></label>
                    <div class="file-upload-area" id="thumbnailUploadArea">
                        <i class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Click to upload thumbnail</p>
                        <p class="text-sm text-gray-500">JPG, PNG, GIF up to 2MB</p>
                    </div>
                    <input type="file" id="thumbnail_file" class="hidden" accept="image/jpeg,image/png,image/gif">
                </div>
            </div>

            <!-- Step 2: Documents Upload -->
            <div class="step-pane" id="step2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Related Documents <span class="text-sm text-gray-500">(Optional)</span></h3>
                <p class="text-gray-600 mb-4">Upload PDF, DOC, or DOCX files related to this video lesson.</p>

                <div id="documentsList" class="space-y-3 mb-4">
                    <!-- Documents will be added here -->
                </div>

                <button type="button" id="addDocumentBtn" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-400 hover:text-blue-600 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Document
                </button>
                <input type="file" id="documentInput" class="hidden" accept=".pdf,.doc,.docx" multiple>
            </div>

            <!-- Step 3: Quiz Builder -->
            <div class="step-pane" id="step3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Quiz <span class="text-sm text-gray-500">(Optional)</span></h3>
                <p class="text-gray-600 mb-4">Build a quiz to test student understanding of this lesson.</p>

                <div id="quizBuilder" class="space-y-4">
                    <!-- Quiz Settings -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Quiz Settings</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Difficulty Level -->
                            <div>
                                <label for="quiz_difficulty" class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                                <select id="quiz_difficulty" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="easy">Easy</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                                <div class="mt-2 flex space-x-2">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                        <span class="text-xs text-gray-600">Easy</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                                        <span class="text-xs text-gray-600">Medium</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                                        <span class="text-xs text-gray-600">Hard</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Limit -->
                            <div>
                                <label for="quiz_time_limit" class="block text-sm font-medium text-gray-700 mb-2">Time Limit (minutes)</label>
                                <input type="number" id="quiz_time_limit" min="1" max="300" value="15"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Set to 0 for no time limit</p>
                            </div>
                        </div>
                    </div>

                    <div id="questionsList">
                        <!-- Questions will be added here -->
                    </div>

                    <div class="flex space-x-2">
                        <button type="button" id="addMcqBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Add MCQ
                        </button>
                        <button type="button" id="addEssayBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-plus mr-2"></i>Add Essay
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8">
            <button type="button" id="prevBtn" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50" disabled>
                <i class="fas fa-arrow-left mr-2"></i>Previous
            </button>

            <div class="flex space-x-2">
                <button type="button" id="skipBtn" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Skip Step
                </button>
                <button type="button" id="nextBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button type="button" id="finishBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 hidden">
                    <i class="fas fa-check mr-2"></i>Finish
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Multi-step upload wizard and table functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Table functionality
        const selectAll = document.getElementById('selectAll');
        const contentCheckboxes = document.querySelectorAll('.content-checkbox');
        const searchInput = document.getElementById('searchInput');

        // Select all checkbox
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                contentCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const url = new URL(window.location);
                    url.searchParams.set('q', this.value);
                    window.location.href = url.toString();
                }
            });
        }

        // Upload Modal Elements
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadModal = document.getElementById('uploadModal');
        const closeModal = document.getElementById('closeModal');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const skipBtn = document.getElementById('skipBtn');
        const finishBtn = document.getElementById('finishBtn');

        // Step elements
        const stepIndicators = document.querySelectorAll('.step-indicator');
        const stepPanes = document.querySelectorAll('.step-pane');

        // Form data storage
        let uploadData = {
            video: null,
            thumbnail: null,
            documents: [],
            quiz: {
                questions: [],
                difficulty_level: 'medium',
                time_limit_minutes: 15
            }
        };

        let currentStep = 1;

        // Initialize wizard
        initializeWizard();

        function initializeWizard() {
            if (!uploadBtn || !uploadModal || !closeModal || !prevBtn || !nextBtn || !skipBtn || !finishBtn) {
                console.error('Required modal elements not found');
                return;
            }

            // Open modal
            uploadBtn.addEventListener('click', () => {
                uploadModal.classList.add('show');
                resetWizard();
            });

            // Close modal
            closeModal.addEventListener('click', () => {
                uploadModal.classList.remove('show');
                resetWizard();
            });

            // Close modal when clicking outside
            uploadModal.addEventListener('click', (e) => {
                if (e.target === uploadModal) {
                    uploadModal.classList.remove('show');
                    resetWizard();
                }
            });

            // Navigation
            prevBtn.addEventListener('click', () => navigateStep(currentStep - 1));
            nextBtn.addEventListener('click', () => navigateStep(currentStep + 1));
            skipBtn.addEventListener('click', () => navigateStep(currentStep + 1));
            finishBtn.addEventListener('click', submitWizard);

            // Step 1: Video upload
            initializeVideoStep();
            initializeThumbnailStep();

            // Step 2: Documents
            initializeDocumentsStep();

            // Step 3: Quiz builder
            initializeQuizStep();
            initializeQuizSettings();
        }

        function resetWizard() {
            currentStep = 1;
            uploadData = {
                video: null,
                thumbnail: null,
                documents: [],
                quiz: {
                    questions: [],
                    difficulty_level: 'medium',
                    time_limit_minutes: 15
                }
            };
            navigateStep(1);
            clearAllSteps();
        }

        function clearAllSteps() {
            // Clear video step
            document.getElementById('fileInput').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('grade_level').value = '';
            document.getElementById('fileUploadArea').innerHTML = `
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-gray-600">Click to upload or drag and drop</p>
                <p class="text-sm text-gray-500">MP4, MOV, AVI up to 600MB</p>
            `;

            // Clear thumbnail step
            document.getElementById('thumbnail_file').value = '';
            document.getElementById('thumbnailUploadArea').innerHTML = `
                <i class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                <p class="text-gray-600">Click to upload thumbnail</p>
                <p class="text-sm text-gray-500">JPG, PNG, GIF up to 2MB</p>
            `;

            // Clear documents step
            document.getElementById('documentsList').innerHTML = '';

            // Clear quiz step
            document.getElementById('questionsList').innerHTML = '';
            document.getElementById('quiz_difficulty').value = 'medium';
            document.getElementById('quiz_time_limit').value = '15';

            // Clear video preview
            const videoPreview = document.getElementById('videoPreview');
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');
            if (videoPreview) videoPreview.src = '';
            if (videoPreviewContainer) videoPreviewContainer.classList.add('hidden');
        }

        function navigateStep(step) {
            // Validate current step before proceeding
            if (step > currentStep && !validateCurrentStep()) {
                return;
            }

            // Update step indicators
            stepIndicators.forEach((indicator, index) => {
                const stepNum = index + 1;
                if (stepNum < step) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                } else if (stepNum === step) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            });

            // Update step panes
            stepPanes.forEach((pane, index) => {
                if (index + 1 === step) {
                    pane.classList.add('active');
                } else {
                    pane.classList.remove('active');
                }
            });

            currentStep = step;
            updateNavigationButtons();
        }

        function validateCurrentStep() {
            switch (currentStep) {
                case 1:
                    const title = document.getElementById('title').value.trim();
                    const gradeLevel = document.getElementById('grade_level').value;
                    if (!uploadData.video) {
                        alert('Please upload a video file.');
                        return false;
                    }
                    if (!title) {
                        alert('Please enter a title.');
                        return false;
                    }
                    if (!gradeLevel) {
                        alert('Please select a grade level.');
                        return false;
                    }
                    return true;
                default:
                    return true;
            }
        }

        function updateNavigationButtons() {
            prevBtn.disabled = currentStep === 1;
            skipBtn.style.display = currentStep < 3 ? 'block' : 'none';
            nextBtn.style.display = currentStep < 3 ? 'block' : 'none';
            finishBtn.style.display = currentStep === 3 ? 'block' : 'none';
        }

        // Step 1: Video Upload
        function initializeVideoStep() {
            const fileUploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('fileInput');

            fileUploadArea.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    uploadData.video = file;
                    updateVideoUploadArea(file);
                }
            });

            // Drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, unhighlight, false);
            });

            fileUploadArea.addEventListener('drop', handleVideoDrop, false);
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight() {
            document.getElementById('fileUploadArea').classList.add('dragover');
        }

        function unhighlight() {
            document.getElementById('fileUploadArea').classList.remove('dragover');
        }

        function handleVideoDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('video/')) {
                    uploadData.video = file;
                    document.getElementById('fileInput').files = files;
                    updateVideoUploadArea(file);
                } else {
                    alert('Please upload a video file.');
                }
            }
        }

        function updateVideoUploadArea(file) {
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');
            const videoPreview = document.getElementById('videoPreview');
            const fileURL = URL.createObjectURL(file);

            uploadData.video = file;

            // Show video preview
            videoPreview.src = fileURL;
            videoPreviewContainer.classList.remove('hidden');
            document.getElementById('fileUploadArea').classList.add('has-video');
        }

        // Step 1: Thumbnail Upload
        function initializeThumbnailStep() {
            const thumbnailUploadArea = document.getElementById('thumbnailUploadArea');
            const thumbnailInput = document.getElementById('thumbnail_file');
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const removeBtn = document.getElementById('removeThumbnailBtn');

            thumbnailUploadArea.addEventListener('click', () => thumbnailInput.click());

            thumbnailInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please upload a valid image file (JPG, PNG, or GIF).');
                        thumbnailInput.value = '';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        alert('Thumbnail file size must be less than 2MB.');
                        thumbnailInput.value = '';
                        return;
                    }

                    uploadData.thumbnail = file;
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        thumbnailPreview.src = ev.target.result;
                        thumbnailPreview.style.display = 'block';
                        removeBtn.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                    updateThumbnailUploadArea(file);
                }
            });

            removeBtn.addEventListener('click', () => {
                uploadData.thumbnail = null;
                thumbnailPreview.src = '';
                thumbnailPreview.style.display = 'none';
                removeBtn.classList.add('hidden');
            });
        }

        function updateThumbnailUploadArea(file) {
            const fileSize = (file.size / 1024).toFixed(1);
            document.getElementById('thumbnailUploadArea').innerHTML = `
                <i class="fas fa-image text-2xl text-green-600 mb-2"></i>
                <p class="text-gray-900 font-medium">${file.name}</p>
                <p class="text-sm text-gray-500">${fileSize} KB</p>
            `;
        }

        // Step 2: Documents
        function initializeDocumentsStep() {
            const addDocumentBtn = document.getElementById('addDocumentBtn');
            const documentInput = document.getElementById('documentInput');
            const documentsList = document.getElementById('documentsList');

            addDocumentBtn.addEventListener('click', () => documentInput.click());

            documentInput.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    if (file.type === 'application/pdf' ||
                        file.type === 'application/msword' ||
                        file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                        uploadData.documents.push(file);
                        addDocumentToList(file);
                    }
                });
                documentInput.value = '';
            });
        }

        function addDocumentToList(file) {
            const documentsList = document.getElementById('documentsList');
            const documentItem = document.createElement('div');
            documentItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
            documentItem.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                    <div>
                        <p class="font-medium text-gray-900">${file.name}</p>
                        <p class="text-sm text-gray-500">${(file.size / 1024).toFixed(1)} KB</p>
                    </div>
                </div>
                <button type="button" class="text-red-600 hover:text-red-800 remove-document">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            documentItem.querySelector('.remove-document').addEventListener('click', () => {
                uploadData.documents = uploadData.documents.filter(f => f !== file);
                documentItem.remove();
            });

            documentsList.appendChild(documentItem);
        }

        // Step 3: Quiz Builder
        function initializeQuizStep() {
            const addMcqBtn = document.getElementById('addMcqBtn');
            const addEssayBtn = document.getElementById('addEssayBtn');

            addMcqBtn.addEventListener('click', () => addQuestion('mcq'));
            addEssayBtn.addEventListener('click', () => addQuestion('essay'));
        }

        // Step 3: Quiz Settings
        function initializeQuizSettings() {
            const difficultySelect = document.getElementById('quiz_difficulty');
            const timeLimitInput = document.getElementById('quiz_time_limit');

            if (difficultySelect) {
                difficultySelect.addEventListener('change', (e) => {
                    uploadData.quiz.difficulty_level = e.target.value;
                });
            }

            if (timeLimitInput) {
                timeLimitInput.addEventListener('input', (e) => {
                    uploadData.quiz.time_limit_minutes = parseInt(e.target.value) || 15;
                });
            }
        }

        function addQuestion(type) {
            const questionsList = document.getElementById('questionsList');
            const questionId = Date.now();
            const question = {
                id: questionId,
                type: type,
                question: '',
                options: type === 'mcq' ? ['', '', '', ''] : null,
                correct_answer: type === 'mcq' ? 0 : '',
                points: 1
            };

            uploadData.quiz.questions.push(question);

            const questionElement = createQuestionElement(question);
            questionsList.appendChild(questionElement);
        }

        function createQuestionElement(question) {
            const div = document.createElement('div');
            div.className = 'question-item bg-white border border-gray-200 rounded-lg p-4 mb-4';
            div.dataset.questionId = question.id;

            if (question.type === 'mcq') {
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-medium text-gray-900">Multiple Choice Question</h4>
                        <button type="button" class="text-red-600 hover:text-red-800 remove-question">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg question-text"
                            placeholder="Enter your question..." value="${question.question}">
                    </div>
                    <div class="space-y-2 mb-3">
                        ${question.options.map((option, index) => `
                            <div class="flex items-center">
                                <input type="radio" name="correct_${question.id}" value="${index}"
                                    class="mr-2 correct-answer" ${question.correct_answer === index ? 'checked' : ''}>
                                <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg option-text"
                                    placeholder="Option ${index + 1}" value="${option}">
                            </div>
                        `).join('')}
                    </div>
                    <div class="flex items-center">
                        <label class="mr-2 text-sm text-gray-600">Points:</label>
                        <input type="number" class="w-20 px-2 py-1 border border-gray-300 rounded question-points"
                            value="${question.points}" min="1">
                    </div>
                `;
            } else {
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-medium text-gray-900">Essay Question</h4>
                        <button type="button" class="text-red-600 hover:text-red-800 remove-question">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg question-text"
                            placeholder="Enter your essay question..." value="${question.question}">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-600 mb-1">Sample Answer (for reference)</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg correct-answer"
                                rows="3" placeholder="Enter sample answer...">${question.correct_answer}</textarea>
                    </div>
                    <div class="flex items-center">
                        <label class="mr-2 text-sm text-gray-600">Points:</label>
                        <input type="number" class="w-20 px-2 py-1 border border-gray-300 rounded question-points"
                            value="${question.points}" min="1">
                    </div>
                `;
            }

            // Add event listeners
            div.querySelector('.remove-question').addEventListener('click', () => {
                uploadData.quiz.questions = uploadData.quiz.questions.filter(q => q.id !== question.id);
                div.remove();
            });

            div.querySelector('.question-text').addEventListener('input', (e) => {
                question.question = e.target.value;
            });

            div.querySelector('.question-points').addEventListener('input', (e) => {
                question.points = parseInt(e.target.value) || 1;
            });

            if (question.type === 'mcq') {
                div.querySelectorAll('.option-text').forEach((input, index) => {
                    input.addEventListener('input', (e) => {
                        question.options[index] = e.target.value;
                    });
                });

                div.querySelectorAll('.correct-answer').forEach((radio, index) => {
                    radio.addEventListener('change', () => {
                        question.correct_answer = index;
                    });
                });
            } else {
                div.querySelector('.correct-answer').addEventListener('input', (e) => {
                    question.correct_answer = e.target.value;
                });
            }

            return div;
        }

        // Final submission
        async function submitWizard() {
            try {
                const finalData = {
                    video: {
                        file: uploadData.video,
                        title: document.getElementById('title').value.trim(),
                        description: document.getElementById('description').value.trim(),
                        grade_level: document.getElementById('grade_level').value
                    },
                    documents: uploadData.documents,
                    quiz: uploadData.quiz
                };

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('video_file', finalData.video.file);
                formData.append('title', finalData.video.title);
                formData.append('description', finalData.video.description);
                formData.append('grade_level', finalData.video.grade_level);

                if (uploadData.thumbnail) {
                    formData.append('thumbnail_file', uploadData.thumbnail);
                }

                finalData.documents.forEach((doc, index) => {
                    formData.append(`documents[${index}]`, doc);
                });

                if (finalData.quiz.questions.length > 0) {
                    const quizData = {
                        questions: finalData.quiz.questions,
                        difficulty_level: finalData.quiz.difficulty_level,
                        time_limit_minutes: finalData.quiz.time_limit_minutes
                    };
                    formData.append('quiz_data', JSON.stringify(quizData));
                    formData.append('difficulty_level', finalData.quiz.difficulty_level);
                    formData.append('time_limit_minutes', finalData.quiz.time_limit_minutes);
                }

                const response = await fetch('{{ route("admin.contents.store") }}', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    uploadModal.classList.remove('show');
                    resetWizard();
                    window.location.href = '{{ route("admin.contents.index") }}';
                } else {
                    const error = await response.json();
                    alert('Upload failed: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('Upload failed. Please try again.');
            }
        }
    });
</script>
@endsection