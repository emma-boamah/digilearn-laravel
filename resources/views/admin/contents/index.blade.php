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

    /* Always enable horizontal scrolling for better UX */
    .content-table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .content-table {
        min-width: 1400px;
    }

    /* Video Preview Modal Styles */
    #videoPreviewModal video {
        max-height: 70vh;
        object-fit: contain;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .content-table {
            min-width: 1600px;
        }

        .actions-cell {
            min-width: 120px;
            white-space: nowrap;
        }

        #videoPreviewModal .aspect-video {
            aspect-ratio: 16/9;
        }

        #videoPreviewModal video {
            max-height: 50vh;
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
        <a href="{{ route('admin.contents.index', ['q' => $query, 'type' => 'pending', 'sort' => $sort]) }}"
           class="filter-tab {{ $type === 'pending' ? 'active' : '' }}">
           Pending Review
           @if($stats['pending_reviews'] > 0)
               <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $stats['pending_reviews'] }}</span>
           @endif
        </a>
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
                    <th>Subject</th>
                    <th>Grade Level</th>
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
                    <td>
                        @if($content->subject_name)
                            {{ $content->subject_name }}
                        @else
                            <span class="text-gray-500">—</span>
                        @endif
                    </td>
                    <td>
                        @if($content->grade_level)
                            {{ $content->grade_level }}
                        @else
                            <span class="text-gray-500">—</span>
                        @endif
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
                        @if($type === 'pending' && $content->content_type === 'video')
                            <button class="action-btn preview-btn" title="Preview Video" data-video-id="{{ $content->id }}" data-video-title="{{ $content->title }}" data-video-url="{{ route('admin.content.videos.stream', $content->id) }}" style="color: #3b82f6;">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="action-btn approve-btn" title="Approve" data-video-id="{{ $content->id }}" style="color: #10b981;">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="action-btn reject-btn" title="Reject" data-video-id="{{ $content->id }}" style="color: #ef4444;">
                                <i class="fas fa-times"></i>
                            </button>
                        @else
                            <button class="action-btn edit-btn" title="Edit" data-content-id="{{ $content->id }}" data-content-type="{{ $content->content_type }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete delete-btn" title="Delete" data-content-id="{{ $content->id }}" data-content-type="{{ $content->content_type }}" data-video-source="{{ $content->video_source ?? '' }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="action-btn" title="More">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        @endif
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

                <!-- Video Source Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video Source</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative">
                            <input type="radio" name="video_source" value="local" class="sr-only peer" checked>
                            <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                <div class="flex items-center">
                                    <i class="fas fa-server text-2xl text-gray-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Local Upload</div>
                                        <div class="text-sm text-gray-500">Upload MP4, MOV, AVI files</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="video_source" value="youtube" class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-gray-300 transition-colors">
                                <div class="flex items-center">
                                    <i class="fab fa-youtube text-2xl text-red-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">YouTube</div>
                                        <div class="text-sm text-gray-500">Paste YouTube video URL</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="video_source" value="vimeo" class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                <div class="flex items-center">
                                    <i class="fab fa-vimeo text-2xl text-blue-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Vimeo</div>
                                        <div class="text-sm text-gray-500">Upload file or paste Vimeo URL</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="video_source" value="mux" class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300 transition-colors">
                                <div class="flex items-center">
                                    <i class="fas fa-video text-2xl text-purple-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Mux</div>
                                        <div class="text-sm text-gray-500">Paste Mux stream URL</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Upload Destination (for Local Upload) -->
                <div class="mb-4 hidden" id="uploadDestinationSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Destination</label>
                    <select id="upload_destination" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="local">Store Locally</option>
                        <option value="vimeo">Upload to Vimeo</option>
                        <option value="mux">Upload to Mux</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Choose where to upload your video file</p>
                </div>

                <!-- Vimeo Upload Options -->
                <div class="mb-4 hidden" id="vimeoUploadOptions">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vimeo Upload Method</label>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" name="vimeo_method" value="file" class="mr-2">
                            <span class="text-sm">Upload video file to Vimeo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="vimeo_method" value="url" class="mr-2" checked>
                            <span class="text-sm">Use existing Vimeo URL</span>
                        </label>
                    </div>
                </div>

                <!-- Local File Upload Area -->
                <div class="mb-4" id="localUploadSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video File</label>
                    <div id="fileUploadArea" class="file-upload-area">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Click to upload or drag and drop</p>
                        <p class="text-sm text-gray-500">MP4, MOV, AVI up to 32GB</p>
                    </div>
                    <input type="file" id="fileInput" class="hidden" accept=".mp4,.mov,.avi">
                </div>

                <!-- External URL Input -->
                <div class="mb-4 hidden" id="externalUrlSection">
                    <label for="external_video_url" class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                    <input type="url" id="external_video_url" placeholder="https://vimeo.com/123456789 or https://youtube.com/watch?v=..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Paste the full URL of your video from the selected platform</p>
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

                <!-- Subject -->
                <div class="mb-4">
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                    <select id="subject_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Subject</option>
                        @if(isset($subjects) && $subjects->count())
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        @endif
                    </select>
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
                        <p class="text-sm text-gray-500">JPG, PNG, GIF up to 5MB</p>
                    </div>
                    <input type="file" id="thumbnail_file" class="hidden" accept="image/jpeg,image/png,image/gif">
                </div>
            </div>

            <!-- Step 2: Documents Upload -->
            <div class="step-pane" id="step2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Related Documents <span class="text-sm text-gray-500">(Optional)</span></h3>
                <p class="text-gray-600 mb-4">Upload PDF, DOC, or DOCX files related to this video lesson. Max file size: 32GB per document.</p>

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

<!-- Video Preview Modal -->
<div id="videoPreviewModal" class="upload-modal">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900" id="previewModalTitle">Video Preview</h3>
            <button id="closePreviewModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <div class="aspect-video bg-black rounded-lg overflow-hidden mb-4">
                <video id="previewVideoPlayer" controls class="w-full h-full" preload="metadata">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="previewApproveBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>Approve
                </button>
                <button id="previewRejectBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i>Reject
                </button>
                <button id="previewCloseBtn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vimeo Deletion Confirmation Modal -->
<div id="vimeoDeletionModal" class="upload-modal">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Delete from Vimeo</h3>
            <button id="closeVimeoModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-3"></i>
                <div>
                    <p class="text-gray-900 font-medium">Video deleted from database</p>
                    <p class="text-gray-600 text-sm">Do you also want to delete this video from Vimeo?</p>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="vimeoDeleteNo" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    No, keep on Vimeo
                </button>
                <button id="vimeoDeleteYes" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Yes, delete from Vimeo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Progress Modal -->
<div id="uploadProgressModal" class="upload-modal">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Uploading Content</h3>
            <button id="closeProgressModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <!-- Video Upload Progress -->
                <div id="videoProgressSection" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900">Video Upload</span>
                        <span id="videoProgressText" class="text-sm text-gray-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="videoProgressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="videoProgressStatus" class="text-sm text-gray-600 mt-1">Preparing upload...</p>
                </div>

                <!-- Document Upload Progress -->
                <div id="documentProgressSection" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900">Document Upload</span>
                        <span id="documentProgressText" class="text-sm text-gray-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="documentProgressBar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="documentProgressStatus" class="text-sm text-gray-600 mt-1">Preparing documents...</p>
                </div>

                <!-- Quiz Upload Progress -->
                <div id="quizProgressSection" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900">Quiz Upload</span>
                        <span id="quizProgressText" class="text-sm text-gray-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="quizProgressBar" class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="quizProgressStatus" class="text-sm text-gray-600 mt-1">Preparing quiz...</p>
                </div>

                <!-- Overall Progress -->
                <div class="border-t pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900">Overall Progress</span>
                        <span id="overallProgressText" class="text-sm text-gray-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="overallProgressBar" class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="overallProgressStatus" class="text-sm text-gray-600 mt-1">Starting upload process...</p>
                </div>

                <!-- Error Messages -->
                <div id="uploadErrors" class="hidden">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            <span class="font-medium text-red-900">Upload Errors</span>
                        </div>
                        <ul id="errorList" class="text-sm text-red-700 space-y-1"></ul>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button id="cancelUploadBtn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 mr-2">
                    Cancel
                </button>
                <button id="closeUploadBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 hidden">
                    Close
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

        // Edit functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.action-btn.edit-btn')) {
                e.preventDefault();
                const editBtn = e.target.closest('.action-btn.edit-btn');
                const contentId = editBtn.getAttribute('data-content-id');

                // Redirect to edit page
                window.location.href = `{{ route("admin.contents.edit", ":contentId") }}`.replace(':contentId', contentId);
            }
        });

        // Delete functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.action-btn.delete-btn')) {
                e.preventDefault();
                const deleteBtn = e.target.closest('.action-btn.delete-btn');
                const contentId = deleteBtn.getAttribute('data-content-id');
                const contentType = deleteBtn.getAttribute('data-content-type');
                const videoSource = deleteBtn.getAttribute('data-video-source');

                let confirmMessage = 'Are you sure you want to delete this content? This action cannot be undone.';

                if (contentType === 'video' && videoSource === 'vimeo') {
                    confirmMessage = 'Are you sure you want to delete this video? This will permanently delete the video from both the database AND Vimeo. This action cannot be undone.';
                } else if (contentType === 'video' && videoSource === 'youtube') {
                    confirmMessage = 'Are you sure you want to delete this video? This will only delete the video from the database (YouTube videos cannot be deleted remotely). This action cannot be undone.';
                }

                if (confirm(confirmMessage)) {
                    deleteContent(contentId, contentType, videoSource);
                }
            }
        });

        // Preview video functionality
        document.addEventListener('click', function(e) {
            console.log('Click event:', e.target);
            if (e.target.closest('.action-btn.preview-btn')) {
                e.preventDefault();
                console.log('Preview button found');
                const previewBtn = e.target.closest('.action-btn.preview-btn');
                const videoId = previewBtn.getAttribute('data-video-id');
                const videoTitle = previewBtn.getAttribute('data-video-title');
                const videoUrl = previewBtn.getAttribute('data-video-url');

                console.log('Preview button clicked:', { videoId, videoTitle, videoUrl });

                if (!videoUrl || videoUrl === 'null') {
                    alert('Video URL is not available. The video file may have expired or been deleted.');
                    return;
                }

                openVideoPreview(videoId, videoTitle, videoUrl);
            }
        });

        // Approve video functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.action-btn.approve-btn')) {
                e.preventDefault();
                const approveBtn = e.target.closest('.action-btn.approve-btn');
                const videoId = approveBtn.getAttribute('data-video-id');

                if (confirm('Are you sure you want to approve this video? It will be uploaded to the selected platform.')) {
                    approveVideo(videoId);
                }
            }
        });

        // Reject video functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.action-btn.reject-btn')) {
                e.preventDefault();
                const rejectBtn = e.target.closest('.action-btn.reject-btn');
                const videoId = rejectBtn.getAttribute('data-video-id');

                const reason = prompt('Please provide a reason for rejection (optional):');
                rejectVideo(videoId, reason);
            }
        });

        async function deleteContent(contentId, contentType, videoSource) {
            try {
                // Use unified delete endpoint
                const response = await fetch(`{{ route("admin.contents.destroy", ":contentId") }}`.replace(':contentId', contentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Check if we need to show Vimeo deletion modal
                    if (result.show_vimeo_modal && result.vimeo_id) {
                        showVimeoDeletionModal(result.vimeo_id, result.video_id);
                    } else {
                        alert(result.message || 'Content deleted successfully!');
                        window.location.reload();
                    }
                } else {
                    alert('Failed to delete content: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('An error occurred while deleting the content.');
            }
        }

        function showVimeoDeletionModal(vimeoId, videoId) {
            const modal = document.getElementById('vimeoDeletionModal');
            const closeBtn = document.getElementById('closeVimeoModal');
            const noBtn = document.getElementById('vimeoDeleteNo');
            const yesBtn = document.getElementById('vimeoDeleteYes');

            // Set up event handlers
            const closeModal = () => {
                modal.classList.remove('show');
                window.location.reload(); // Refresh to show the deletion
            };

            closeBtn.addEventListener('click', closeModal);
            noBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            yesBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('{{ route("admin.contents.vimeo.delete") }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            vimeo_id: vimeoId,
                            video_id: videoId
                        })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        alert(result.message || 'Video deleted from Vimeo successfully!');
                    } else {
                        alert('Failed to delete from Vimeo: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Vimeo delete error:', error);
                    alert('An error occurred while deleting from Vimeo.');
                }

                closeModal();
            });

            // Show modal
            modal.classList.add('show');
        }

        async function approveVideo(videoId) {
            try {
                const response = await fetch(`{{ route("admin.content.videos.approve", ":videoId") }}`.replace(':videoId', videoId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        review_notes: 'Approved from contents page'
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert(result.message || 'Video approved successfully!');
                    window.location.reload();
                } else {
                    alert('Failed to approve video: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Approve error:', error);
                alert('An error occurred while approving the video.');
            }
        }

        async function rejectVideo(videoId, reason = '') {
            try {
                const response = await fetch(`{{ route("admin.content.videos.reject", ":videoId") }}`.replace(':videoId', videoId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        review_notes: reason || 'Rejected from contents page'
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert(result.message || 'Video rejected successfully!');
                    window.location.reload();
                } else {
                    alert('Failed to reject video: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Reject error:', error);
                alert('An error occurred while rejecting the video.');
            }
        }

        function openVideoPreview(videoId, videoTitle, videoUrl) {
            console.log('Opening video preview:', { videoId, videoTitle, videoUrl });

            const modal = document.getElementById('videoPreviewModal');
            const modalTitle = document.getElementById('previewModalTitle');
            const videoPlayer = document.getElementById('previewVideoPlayer');
            const approveBtn = document.getElementById('previewApproveBtn');
            const rejectBtn = document.getElementById('previewRejectBtn');
            const closeBtn = document.getElementById('previewCloseBtn');
            const closeModalBtn = document.getElementById('closePreviewModal');

            console.log('Modal elements found:', { modal, modalTitle, videoPlayer });

            if (!modal || !modalTitle || !videoPlayer) {
                alert('Video preview modal not found. Please refresh the page.');
                return;
            }

            // Set modal title
            modalTitle.textContent = `Preview: ${videoTitle}`;

            // Set video source
            videoPlayer.src = videoUrl;
            videoPlayer.load();

            // Set up button handlers
            approveBtn.onclick = () => {
                if (confirm('Are you sure you want to approve this video? It will be uploaded to the selected platform.')) {
                    modal.classList.remove('show');
                    approveVideo(videoId);
                }
            };

            rejectBtn.onclick = () => {
                const reason = prompt('Please provide a reason for rejection (optional):');
                modal.classList.remove('show');
                rejectVideo(videoId, reason);
            };

            closeBtn.onclick = () => {
                modal.classList.remove('show');
                videoPlayer.pause();
                videoPlayer.src = '';
            };

            closeModalBtn.onclick = () => {
                modal.classList.remove('show');
                videoPlayer.pause();
                videoPlayer.src = '';
            };

            // Close modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    videoPlayer.pause();
                    videoPlayer.src = '';
                }
            });

            // Show modal
            modal.classList.add('show');
            console.log('Modal should now be visible');
        }

        // Ping functionality to keep session alive
        function pingServer() {
            fetch('{{ route("ping") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            }).catch(error => {
                console.error('Ping failed:', error);
            });
        }

        // Ping every 5 minutes to keep session alive
        setInterval(pingServer, 5 * 60 * 1000);

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
            video_source: 'local',
            external_video_url: '',
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
            initializeVideoSourceSelection();
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
            // Clear video source selection
            const localRadio = document.querySelector('input[name="video_source"][value="local"]');
            if (localRadio) localRadio.checked = true;
            uploadData.video_source = 'local';
            uploadData.external_video_url = '';
            uploadData.upload_destination = 'local'; // Default to local

            // Reset Vimeo method to URL
            const vimeoUrlRadio = document.querySelector('input[name="vimeo_method"][value="url"]');
            if (vimeoUrlRadio) vimeoUrlRadio.checked = true;

            toggleVideoSourceSections('local');

            // Clear video step
            const fileInput = document.getElementById('fileInput');
            const externalVideoUrl = document.getElementById('external_video_url');
            const title = document.getElementById('title');
            const subjectId = document.getElementById('subject_id');
            const description = document.getElementById('description');
            const gradeLevel = document.getElementById('grade_level');
            const fileUploadArea = document.getElementById('fileUploadArea');

            if (fileInput) fileInput.value = '';
            if (externalVideoUrl) externalVideoUrl.value = '';
            if (title) title.value = '';
            if (subjectId) subjectId.value = '';
            if (description) description.value = '';
            if (gradeLevel) gradeLevel.value = '';
            if (fileUploadArea) fileUploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-gray-600">Click to upload or drag and drop</p>
                <p class="text-sm text-gray-500">MP4, MOV, AVI up to 600MB</p>
            `;

            // Clear thumbnail step
            const thumbnailFile = document.getElementById('thumbnail_file');
            const thumbnailUploadArea = document.getElementById('thumbnailUploadArea');

            if (thumbnailFile) thumbnailFile.value = '';
            if (thumbnailUploadArea) thumbnailUploadArea.innerHTML = `
                <i class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                <p class="text-gray-600">Click to upload thumbnail</p>
                <p class="text-sm text-gray-500">JPG, PNG, GIF up to 2MB</p>
            `;

            // Clear documents step
            const documentsList = document.getElementById('documentsList');
            if (documentsList) documentsList.innerHTML = '';

            // Clear quiz step
            const questionsList = document.getElementById('questionsList');
            const quizDifficulty = document.getElementById('quiz_difficulty');
            const quizTimeLimit = document.getElementById('quiz_time_limit');

            if (questionsList) questionsList.innerHTML = '';
            if (quizDifficulty) quizDifficulty.value = 'medium';
            if (quizTimeLimit) quizTimeLimit.value = '15';

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
                    const title = document.getElementById('title');
                    const subjectId = document.getElementById('subject_id');
                    const gradeLevel = document.getElementById('grade_level');

                    if (!title || !subjectId || !gradeLevel) {
                        console.error('Required form elements not found');
                        return false;
                    }

                    const titleValue = title.value.trim();
                    const subjectIdValue = subjectId.value;
                    const gradeLevelValue = gradeLevel.value;

                    if (uploadData.video_source === 'local') {
                        if (!uploadData.video) {
                            alert('Please upload a video file.');
                            return false;
                        }
                    } else if (uploadData.video_source === 'vimeo') {
                        // Check Vimeo method
                        const vimeoMethod = document.querySelector('input[name="vimeo_method"]:checked');
                        if (vimeoMethod && vimeoMethod.value === 'file') {
                            if (!uploadData.video) {
                                alert('Please upload a video file.');
                                return false;
                            }
                        } else {
                            const externalVideoUrl = document.getElementById('external_video_url');
                            if (!externalVideoUrl) {
                                console.error('External video URL element not found');
                                return false;
                            }

                            const externalUrl = externalVideoUrl.value.trim();
                            if (!externalUrl) {
                                alert('Please enter a Vimeo video URL.');
                                return false;
                            }
                            // Basic URL validation
                            try {
                                new URL(externalUrl);
                            } catch {
                                alert('Please enter a valid URL.');
                                return false;
                            }
                            uploadData.external_video_url = externalUrl;
                        }
                    } else {
                        const externalVideoUrl = document.getElementById('external_video_url');
                        if (!externalVideoUrl) {
                            console.error('External video URL element not found');
                            return false;
                        }

                        const externalUrl = externalVideoUrl.value.trim();
                        if (!externalUrl) {
                            alert('Please enter a video URL.');
                            return false;
                        }
                        // Basic URL validation
                        try {
                            new URL(externalUrl);
                        } catch {
                            alert('Please enter a valid URL.');
                            return false;
                        }
                        uploadData.external_video_url = externalUrl;
                    }

                    if (!titleValue) {
                        alert('Please enter a title.');
                        return false;
                    }
                    if (!subjectIdValue) {
                        alert('Please select a subject.');
                        return false;
                    }
                    if (!gradeLevelValue) {
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

            if (!fileUploadArea || !fileInput) {
                console.error('Video upload elements not found');
                return;
            }

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
            const fileUploadArea = document.getElementById('fileUploadArea');
            if (fileUploadArea) {
                fileUploadArea.classList.add('dragover');
            }
        }

        function unhighlight() {
            const fileUploadArea = document.getElementById('fileUploadArea');
            if (fileUploadArea) {
                fileUploadArea.classList.remove('dragover');
            }
        }

        function handleVideoDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('video/')) {
                    uploadData.video = file;
                    const fileInput = document.getElementById('fileInput');
                    if (fileInput) {
                        fileInput.files = files;
                    }
                    updateVideoUploadArea(file);
                } else {
                    alert('Please upload a video file.');
                }
            }
        }

        function updateVideoUploadArea(file) {
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');
            const videoPreview = document.getElementById('videoPreview');
            const fileUploadArea = document.getElementById('fileUploadArea');

            if (!videoPreviewContainer || !videoPreview || !fileUploadArea) {
                console.error('Video preview elements not found');
                return;
            }

            const fileURL = URL.createObjectURL(file);

            uploadData.video = file;

            // Show video preview
            videoPreview.src = fileURL;
            videoPreviewContainer.classList.remove('hidden');
            fileUploadArea.classList.add('has-video');
        }

        // Step 1: Thumbnail Upload
        function initializeThumbnailStep() {
            const thumbnailUploadArea = document.getElementById('thumbnailUploadArea');
            const thumbnailInput = document.getElementById('thumbnail_file');
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const removeBtn = document.getElementById('removeThumbnailBtn');

            if (!thumbnailUploadArea || !thumbnailInput || !thumbnailPreview || !removeBtn) {
                console.error('Thumbnail upload elements not found');
                return;
            }

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
                        if (thumbnailPreview) {
                            thumbnailPreview.src = ev.target.result;
                            thumbnailPreview.style.display = 'block';
                        }
                        if (removeBtn) {
                            removeBtn.classList.remove('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                    updateThumbnailUploadArea(file);
                }
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    uploadData.thumbnail = null;
                    if (thumbnailPreview) {
                        thumbnailPreview.src = '';
                        thumbnailPreview.style.display = 'none';
                    }
                    removeBtn.classList.add('hidden');
                });
            }
        }

        function updateThumbnailUploadArea(file) {
            const thumbnailUploadArea = document.getElementById('thumbnailUploadArea');
            if (!thumbnailUploadArea) {
                console.error('Thumbnail upload area not found');
                return;
            }

            const fileSize = (file.size / 1024).toFixed(1);
            thumbnailUploadArea.innerHTML = `
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

            if (!addDocumentBtn || !documentInput || !documentsList) {
                console.error('Document upload elements not found');
                return;
            }

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
            if (!documentsList) {
                console.error('Documents list element not found');
                return;
            }

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

            const removeBtn = documentItem.querySelector('.remove-document');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    uploadData.documents = uploadData.documents.filter(f => f !== file);
                    documentItem.remove();
                });
            }

            documentsList.appendChild(documentItem);
        }

        // Step 3: Quiz Builder
        function initializeQuizStep() {
            const addMcqBtn = document.getElementById('addMcqBtn');
            const addEssayBtn = document.getElementById('addEssayBtn');

            if (!addMcqBtn || !addEssayBtn) {
                console.error('Quiz builder elements not found');
                return;
            }

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
            if (!questionsList) {
                console.error('Questions list element not found');
                return;
            }

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
            const removeBtn = div.querySelector('.remove-question');
            const questionText = div.querySelector('.question-text');
            const questionPoints = div.querySelector('.question-points');

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    uploadData.quiz.questions = uploadData.quiz.questions.filter(q => q.id !== question.id);
                    div.remove();
                });
            }

            if (questionText) {
                questionText.addEventListener('input', (e) => {
                    question.question = e.target.value;
                });
            }

            if (questionPoints) {
                questionPoints.addEventListener('input', (e) => {
                    question.points = parseInt(e.target.value) || 1;
                });
            }

            if (question.type === 'mcq') {
                const optionTexts = div.querySelectorAll('.option-text');
                const correctAnswers = div.querySelectorAll('.correct-answer');

                optionTexts.forEach((input, index) => {
                    if (input) {
                        input.addEventListener('input', (e) => {
                            question.options[index] = e.target.value;
                        });
                    }
                });

                correctAnswers.forEach((radio, index) => {
                    if (radio) {
                        radio.addEventListener('change', () => {
                            question.correct_answer = index;
                        });
                    }
                });
            } else {
                const correctAnswer = div.querySelector('.correct-answer');
                if (correctAnswer) {
                    correctAnswer.addEventListener('input', (e) => {
                        question.correct_answer = e.target.value;
                    });
                }
            }

            return div;
        }

        // Video source selection handler
        function initializeVideoSourceSelection() {
            const sourceRadios = document.querySelectorAll('input[name="video_source"]');

            sourceRadios.forEach(radio => {
                radio.addEventListener('change', (e) => {
                    const selectedSource = e.target.value;
                    uploadData.video_source = selectedSource;
                    toggleVideoSourceSections(selectedSource);
                });
            });
        }

        function toggleVideoSourceSections(source) {
            const localSection = document.getElementById('localUploadSection');
            const externalSection = document.getElementById('externalUrlSection');
            const uploadDestinationSection = document.getElementById('uploadDestinationSection');
            const vimeoUploadOptions = document.getElementById('vimeoUploadOptions');
            const externalVideoUrl = document.getElementById('external_video_url');
            const fileInput = document.getElementById('fileInput');
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');

            // Hide all sections first
            if (localSection) localSection.classList.add('hidden');
            if (externalSection) externalSection.classList.add('hidden');
            if (uploadDestinationSection) uploadDestinationSection.classList.add('hidden');
            if (vimeoUploadOptions) vimeoUploadOptions.classList.add('hidden');

            if (source === 'local') {
                if (localSection) localSection.classList.remove('hidden');
                if (uploadDestinationSection) uploadDestinationSection.classList.remove('hidden');
                // Clear external URL
                if (externalVideoUrl) externalVideoUrl.value = '';
                uploadData.external_video_url = '';
            } else if (source === 'vimeo') {
                // Show Vimeo-specific options
                if (vimeoUploadOptions) vimeoUploadOptions.classList.remove('hidden');
                // Initialize Vimeo method handling
                handleVimeoMethodSelection();
            } else if (source === 'youtube' || source === 'mux') {
                if (externalSection) externalSection.classList.remove('hidden');
                // Clear local file
                if (fileInput) fileInput.value = '';
                uploadData.video = null;
                // Hide video preview
                if (videoPreviewContainer) videoPreviewContainer.classList.add('hidden');
            }
        }

        function handleVimeoMethodSelection() {
            const vimeoMethodRadios = document.querySelectorAll('input[name="vimeo_method"]');
            const localSection = document.getElementById('localUploadSection');
            const externalSection = document.getElementById('externalUrlSection');
            const externalVideoUrl = document.getElementById('external_video_url');
            const fileInput = document.getElementById('fileInput');
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');

            function updateVimeoSections() {
                const selectedMethod = document.querySelector('input[name="vimeo_method"]:checked').value;

                // Hide both sections first
                if (localSection) localSection.classList.add('hidden');
                if (externalSection) externalSection.classList.add('hidden');

                if (selectedMethod === 'file') {
                    if (localSection) localSection.classList.remove('hidden');
                    // Clear external URL
                    if (externalVideoUrl) externalVideoUrl.value = '';
                    uploadData.external_video_url = '';
                    uploadData.upload_destination = 'vimeo'; // Set destination to Vimeo
                } else if (selectedMethod === 'url') {
                    if (externalSection) externalSection.classList.remove('hidden');
                    // Clear local file
                    if (fileInput) fileInput.value = '';
                    uploadData.video = null;
                    // Hide video preview
                    if (videoPreviewContainer) videoPreviewContainer.classList.add('hidden');
                }
            }

            // Set initial state
            updateVimeoSections();

            // Add event listeners
            vimeoMethodRadios.forEach(radio => {
                radio.addEventListener('change', updateVimeoSections);
            });
        }

        // Final submission with progress tracking
        async function submitWizard() {
            try {
                const title = document.getElementById('title');
                const subjectId = document.getElementById('subject_id');
                const description = document.getElementById('description');
                const gradeLevel = document.getElementById('grade_level');

                if (!title || !subjectId || !description || !gradeLevel) {
                    console.error('Required form elements not found for submission');
                    alert('Form elements not found. Please refresh the page and try again.');
                    return;
                }

                const finalData = {
                    video: {
                        file: uploadData.video,
                        title: title.value.trim(),
                        subject_id: subjectId.value,
                        description: description.value.trim(),
                        grade_level: gradeLevel.value,
                        video_source: uploadData.video_source,
                        external_video_url: uploadData.external_video_url,
                        upload_destination: uploadData.upload_destination
                    },
                    documents: uploadData.documents,
                    quiz: uploadData.quiz
                };

                // Show progress modal and start upload process
                showUploadProgressModal();
                await performStepByStepUpload(finalData);

            } catch (error) {
                console.error('Upload error:', error);
                showUploadError('Upload failed. Please try again.');
            }
        }

        function showUploadProgressModal() {
            const modal = document.getElementById('uploadProgressModal');
            const closeBtn = document.getElementById('closeProgressModal');
            const cancelBtn = document.getElementById('cancelUploadBtn');
            const closeUploadBtn = document.getElementById('closeUploadBtn');

            // Hide all progress sections initially
            document.getElementById('videoProgressSection').classList.add('hidden');
            document.getElementById('documentProgressSection').classList.add('hidden');
            document.getElementById('quizProgressSection').classList.add('hidden');
            document.getElementById('uploadErrors').classList.add('hidden');

            // Reset progress bars
            updateProgress('video', 0, 'Preparing...');
            updateProgress('document', 0, 'Preparing...');
            updateProgress('quiz', 0, 'Preparing...');
            updateOverallProgress(0, 'Starting upload process...');

            // Setup event handlers
            closeBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to cancel the upload?')) {
                    modal.classList.remove('show');
                    resetWizard();
                }
            });

            cancelBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to cancel the upload?')) {
                    modal.classList.remove('show');
                    resetWizard();
                }
            });

            closeUploadBtn.addEventListener('click', () => {
                modal.classList.remove('show');
                resetWizard();
                window.location.href = '{{ route("admin.contents.index") }}';
            });

            modal.classList.add('show');
        }

        async function performStepByStepUpload(finalData) {
            const errors = [];
            let overallProgress = 0;
            const totalSteps = 3; // video, documents, quiz

            try {
                // Step 1: Upload Video
                updateOverallProgress(10, 'Uploading video...');
                document.getElementById('videoProgressSection').classList.remove('hidden');

                const videoResult = await uploadVideo(finalData);
                if (videoResult.success) {
                    updateProgress('video', 100, 'Video uploaded successfully');
                    overallProgress += 30;
                    updateOverallProgress(overallProgress, 'Video uploaded, processing documents...');
                } else {
                    errors.push(`Video upload failed: ${videoResult.error}`);
                    updateProgress('video', 100, 'Failed', true);
                }

                // Step 2: Upload Documents
                if (finalData.documents.length > 0) {
                    updateOverallProgress(overallProgress + 10, 'Uploading documents...');
                    document.getElementById('documentProgressSection').classList.remove('hidden');

                    const documentResult = await uploadDocuments(finalData);
                    if (documentResult.success) {
                        updateProgress('document', 100, `${finalData.documents.length} documents uploaded successfully`);
                        overallProgress += 30;
                        updateOverallProgress(overallProgress, 'Documents uploaded, processing quiz...');
                    } else {
                        errors.push(`Document upload failed: ${documentResult.error}`);
                        updateProgress('document', 100, 'Failed', true);
                    }
                } else {
                    overallProgress += 30;
                    updateOverallProgress(overallProgress, 'No documents to upload, processing quiz...');
                }

                // Step 3: Upload Quiz
                if (finalData.quiz.questions.length > 0) {
                    updateOverallProgress(overallProgress + 10, 'Uploading quiz...');
                    document.getElementById('quizProgressSection').classList.remove('hidden');

                    const quizResult = await uploadQuiz(finalData);
                    if (quizResult.success) {
                        updateProgress('quiz', 100, 'Quiz uploaded successfully');
                        overallProgress += 30;
                        updateOverallProgress(100, 'All uploads completed successfully!');
                    } else {
                        errors.push(`Quiz upload failed: ${quizResult.error}`);
                        updateProgress('quiz', 100, 'Failed', true);
                    }
                } else {
                    overallProgress += 30;
                    updateOverallProgress(100, 'All uploads completed successfully!');
                }

                // Show results
                if (errors.length > 0) {
                    showUploadErrors(errors);
                    updateOverallProgress(100, 'Upload completed with errors');
                } else {
                    updateOverallProgress(100, 'All uploads completed successfully!');
                    setTimeout(() => {
                        document.getElementById('closeUploadBtn').classList.remove('hidden');
                        document.getElementById('cancelUploadBtn').classList.add('hidden');
                    }, 1000);
                }

            } catch (error) {
                console.error('Upload process error:', error);
                showUploadError('Upload process failed: ' + error.message);
            }
        }

        async function uploadVideo(finalData) {
            try {
                updateProgress('video', 10, 'Preparing video data...');

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('video_source', finalData.video.video_source);

                if (finalData.video.video_source === 'local') {
                    formData.append('video_file', finalData.video.file);
                    formData.append('upload_destination', finalData.video.upload_destination);
                } else if (finalData.video.video_source === 'vimeo') {
                    const vimeoMethod = document.querySelector('input[name="vimeo_method"]:checked');
                    if (vimeoMethod && vimeoMethod.value === 'file') {
                        formData.append('video_file', finalData.video.file);
                        formData.append('upload_destination', 'vimeo');
                        updateProgress('video', 30, 'Uploading to Vimeo...');
                    } else {
                        formData.append('vimeo_url', finalData.video.external_video_url);
                    }
                } else if (finalData.video.video_source === 'youtube') {
                    formData.append('external_video_url', finalData.video.external_video_url);
                } else if (finalData.video.video_source === 'mux') {
                    formData.append('external_video_url', finalData.video.external_video_url);
                }

                formData.append('title', finalData.video.title);
                formData.append('subject_id', finalData.video.subject_id);
                formData.append('description', finalData.video.description);
                formData.append('grade_level', finalData.video.grade_level);

                if (uploadData.thumbnail) {
                    formData.append('thumbnail_file', uploadData.thumbnail);
                }

                updateProgress('video', 50, 'Sending to server...');

                const response = await fetch('{{ route("admin.contents.upload.video") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    updateProgress('video', 90, 'Processing...');

                    // Store video ID for later use
                    if (result.data && result.data.video_id) {
                        window.uploadedVideoId = result.data.video_id;
                    }

                    return { success: true };
                } else {
                    const error = await response.json();
                    return { success: false, error: error.message || 'Unknown error' };
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        async function uploadDocuments(finalData) {
            try {
                updateProgress('document', 20, 'Preparing documents...');

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('video_id', window.uploadedVideoId || '');

                finalData.documents.forEach((doc, index) => {
                    formData.append(`documents[${index}]`, doc);
                    updateProgress('document', 40 + (index * 10), `Uploading ${doc.name}...`);
                });

                updateProgress('document', 80, 'Sending to server...');

                const response = await fetch('{{ route("admin.contents.upload.documents") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    return { success: true };
                } else {
                    const error = await response.json();
                    return { success: false, error: error.message || 'Unknown error' };
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        async function uploadQuiz(finalData) {
            try {
                updateProgress('quiz', 20, 'Preparing quiz data...');

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('video_id', window.uploadedVideoId || '');

                const quizData = {
                    questions: finalData.quiz.questions,
                    difficulty_level: finalData.quiz.difficulty_level,
                    time_limit_minutes: finalData.quiz.time_limit_minutes
                };

                formData.append('quiz_data', JSON.stringify(quizData));
                formData.append('difficulty_level', finalData.quiz.difficulty_level);
                formData.append('time_limit_minutes', finalData.quiz.time_limit_minutes);

                updateProgress('quiz', 60, 'Sending quiz to server...');

                const response = await fetch('{{ route("admin.contents.upload.quiz") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    return { success: true };
                } else {
                    const error = await response.json();
                    return { success: false, error: error.message || 'Unknown error' };
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        function updateProgress(type, percentage, status, isError = false) {
            const progressBar = document.getElementById(`${type}ProgressBar`);
            const progressText = document.getElementById(`${type}ProgressText`);
            const progressStatus = document.getElementById(`${type}ProgressStatus`);

            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
                if (isError) {
                    progressBar.classList.remove('bg-blue-600', 'bg-green-600', 'bg-purple-600');
                    progressBar.classList.add('bg-red-600');
                }
            }
            if (progressText) progressText.textContent = `${percentage}%`;
            if (progressStatus) progressStatus.textContent = status;
        }

        function updateOverallProgress(percentage, status) {
            const progressBar = document.getElementById('overallProgressBar');
            const progressText = document.getElementById('overallProgressText');
            const progressStatus = document.getElementById('overallProgressStatus');

            if (progressBar) progressBar.style.width = `${percentage}%`;
            if (progressText) progressText.textContent = `${percentage}%`;
            if (progressStatus) progressStatus.textContent = status;
        }

        function showUploadErrors(errors) {
            const errorSection = document.getElementById('uploadErrors');
            const errorList = document.getElementById('errorList');

            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });

            errorSection.classList.remove('hidden');
        }

        function showUploadError(message) {
            showUploadErrors([message]);
        }
    });
</script>
@endsection