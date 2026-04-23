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

    /* Grade Picker Dropdown */
    .grade-picker-dropdown {
        position: relative;
    }

    .grade-picker-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 10px 14px;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .grade-picker-trigger:hover {
        border-color: #93a3f8;
    }

    .grade-picker-trigger.has-value {
        color: #1f2937;
        border-color: #4f46e5;
        background: #f5f3ff;
    }

    .grade-picker-arrow {
        transition: transform 0.2s ease;
        flex-shrink: 0;
        color: #9ca3af;
    }

    .grade-picker-dropdown.open .grade-picker-arrow {
        transform: rotate(180deg);
    }

    .grade-picker-panel {
        display: none;
        margin-top: 6px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        animation: gradePickerSlideDown 0.15s ease-out;
    }

    .grade-picker-dropdown.open .grade-picker-panel {
        display: block;
    }

    @keyframes gradePickerSlideDown {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Grade Picker Grid */
    .grade-picker-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
    }

    .grade-picker-column {
        border-right: 1px solid #f0f0f0;
    }

    .grade-picker-column:last-child {
        border-right: none;
    }

    .grade-picker-header {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .grade-picker-header::before {
        content: '';
        display: block;
        width: 4px;
        height: 20px;
        border-radius: 2px;
        background: #a0aec0;
        flex-shrink: 0;
    }

    .grade-picker-column:nth-child(1) .grade-picker-header::before { background: #7c8dff; }
    .grade-picker-column:nth-child(2) .grade-picker-header::before { background: #818cf8; }
    .grade-picker-column:nth-child(3) .grade-picker-header::before { background: #6366f1; }
    .grade-picker-column:nth-child(4) .grade-picker-header::before { background: #4338ca; }

    .grade-picker-header-text {
        font-size: 0.75rem;
        font-weight: 700;
        color: #374151;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .grade-picker-items {
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-height: 120px;
    }

    .grade-picker-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 14px;
        border-radius: 8px;
        border: 2px solid transparent;
        background: transparent;
        color: #374151;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: left;
        width: 100%;
    }

    .grade-picker-item:hover {
        background: #f0f4ff;
        color: #4f46e5;
    }

    .grade-picker-item.selected {
        background: #4f46e5;
        color: #fff;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    }

    .grade-picker-check {
        display: none;
        flex-shrink: 0;
    }

    .grade-picker-item.selected .grade-picker-check {
        display: block;
        color: #fff;
    }

    /* Grade picker responsive */
    @media (max-width: 640px) {
        .grade-picker-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .grade-picker-column:nth-child(2) {
            border-right: none;
        }
        .grade-picker-column:nth-child(1),
        .grade-picker-column:nth-child(2) {
            border-bottom: 1px solid #f0f0f0;
        }
    }
    /* Quiz Editor Enhancements */
    .rich-text-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        margin-bottom: 8px;
        background: #f8fafc;
        padding: 4px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        width: fit-content;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .toolbar-group {
        display: flex;
        gap: 2px;
        padding: 0 4px;
        border-right: 1px solid #e2e8f0;
    }

    .toolbar-group:last-child {
        border-right: none;
    }

    .toolbar-tool {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        color: #475569;
        cursor: pointer;
        transition: all 0.1s ease;
        background: white;
        border: 1px solid transparent;
        font-size: 0.875rem;
    }

    .toolbar-tool:hover {
        background: #f1f5f9;
        color: #2563eb;
        border-color: #e2e8f0;
    }

    .toolbar-tool.active {
        background: #e0e7ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    .toolbar-tool.math-btn {
        width: auto;
        padding: 0 10px;
        gap: 6px;
        color: #4f46e5;
        font-weight: 600;
        border: 1px solid #c7d2fe;
        background: #f5f3ff;
    }

    .toolbar-tool.math-btn:hover {
        background: #ede9fe;
        color: #4338ca;
    }

    .rich-text-editor {
        min-height: 48px;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: #1e293b;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .rich-text-editor:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .rich-text-editor[placeholder]:empty:before {
        content: attr(placeholder);
        color: #94a3b8;
        cursor: text;
    }

    .preamble-section {
        background: #f0f4ff;
        border: 1px solid #dbeafe;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        animation: fadeIn 0.3s ease-out;
    }

    .preamble-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #4f46e5;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .add-preamble-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #4f46e5;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.15s ease;
        margin-bottom: 12px;
    }

    .add-preamble-btn:hover {
        background: #f5f3ff;
        text-decoration: underline;
    }

    /* Premium Question Card */
    .question-item {
        background: white !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
        padding: 24px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }

    .question-item:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* MathField Styles */
    math-field {
        font-size: 1.1rem;
        border: 1px solid transparent;
        border-radius: 4px;
        padding: 2px 4px;
        background: transparent;
        display: inline-block;
        min-width: 20px;
        outline: none;
        cursor: text;
        transition: all 0.2s;
    }

    /* Hide bulky default MathLive UI buttons */
    math-field::part(virtual-keyboard-toggle),
    math-field::part(menu-toggle) {
        display: none !important;
    }

    math-field:focus-within {
        border-color: #cbd5e1;
        background: #f8fafc;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.05);
    }

    /* Ensure Virtual Keyboard appears above the modal (modal is z-index: 1000) */
    math-virtual-keyboard,
    .ML__keyboard {
        z-index: 1050 !important;
    }
    :root {
        --keyboard-zindex: 1050;
    }

    /* Sub-question Styles */

    .sub-questions-container {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px dashed #e2e8f0;
    }

    .sub-question-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
    }

    .sub-question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .sub-question-label {
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .add-sub-question-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 0.5rem;
    }

    .add-sub-question-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    .sub-question-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }
</style>

<script defer src="https://unpkg.com/mathlive"></script>
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
                <select id="levelGroupFilter" class="toolbar-select">
                    <option value="">All Levels</option>
                    @foreach($levelGroups as $group)
                        <option value="{{ $group->slug }}">{{ $group->title }}</option>
                    @endforeach
                </select>
                <select id="contextFilter" class="toolbar-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        @if(strtolower($category->slug) !== 'normal')
                            <option value="{{ $category->slug }}">{{ $category->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button class="toolbar-btn primary" id="uploadBtnToolbar">
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
                    <th>Level Group</th>
                    <th>Grade Level</th>
                    <th>Categories</th>
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
                            @php
                                $level = \App\Models\Level::where('title', $content->grade_level)->with('levelGroup')->first();
                                $levelGroupTitle = $level ? $level->levelGroup->title : 'Unknown';
                            @endphp
                            {{ $levelGroupTitle }}
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
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @forelse($content->categories as $category)
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                    {{ $category->name }}
                                </span>
                            @empty
                                <span class="text-gray-400 text-xs italic">Normal</span>
                            @endforelse
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
                            <button class="action-btn delete delete-btn" title="Delete" data-content-id="{{ $content->id }}" data-content-type="{{ $content->content_type }}" data-video-source="{{ $content->video_source ?? '' }}" data-has-docs="{{ ($content->documents_count ?? 0) > 0 || !empty($content->document_path) ? 'true' : 'false' }}" data-has-quizzes="{{ ($content->quizzes_count ?? 0) > 0 || !empty($content->quiz_id) ? 'true' : 'false' }}">
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

        @if($contents->hasPages())
        <div class="mt-6 mb-4 px-4 sm:px-6 border-t border-gray-100 pt-4">
            {{ $contents->appends(request()->query())->links() }}
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
                    <span class="step-label">Lesson Info</span>
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
            <!-- Step 1: Lesson Details & Video (Optional) -->
            <div class="step-pane active" id="step1">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Lesson Details & Video <span class="text-sm font-normal text-gray-500">(Video is optional)</span></h3>
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Tip:</strong> You can create quiz-only or document-only content! Simply fill in the Title, Subject & Grade below, select <strong>"No Video"</strong> as the source, then click Next to add your documents or quizzes.
                    </p>
                </div>

                <!-- Video Preview -->
                <div class="mb-4" id="videoPreviewWrapper">
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
                        <label class="relative">
                            <input type="radio" name="video_source" value="none" class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-gray-300 transition-colors">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt text-2xl text-emerald-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">No Video</div>
                                        <div class="text-sm text-gray-500">Quiz / Documents only</div>
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
                        <p class="text-sm text-gray-500">MP4, MOV, AVI up to 30GB</p>
                    </div>
                    <input type="file" id="fileInput" class="hidden" accept=".mp4,.mov,.avi,.mkv,.webm,.3gp,.mpeg,.ogg,.flv,.wmv">
                    <!-- Video Validation Error Message (Format & Size) -->
                    <div id="videoValidationError" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg hidden">
                        <p class="text-sm text-red-700">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span id="videoValidationErrorMessage">Video file exceeds maximum size of 30GB</span>
                        </p>
                    </div>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                    <input type="hidden" id="grade_level" value="">
                    <div class="grade-picker-dropdown" id="gradePickerDropdown">
                        <button type="button" class="grade-picker-trigger" id="gradePickerTrigger" onclick="toggleGradePicker()">
                            <span class="grade-picker-trigger-text" id="gradePickerTriggerText">Select Grade Level</span>
                            <svg class="grade-picker-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="grade-picker-panel" id="gradePickerPanel">
                            <div class="grade-picker-grid">
                                @foreach($levelGroups as $group)
                                    <div class="grade-picker-column">
                                        <div class="grade-picker-header">
                                            <span class="grade-picker-header-text">{{ strtoupper($group->name) }}</span>
                                        </div>
                                        <div class="grade-picker-items">
                                            @foreach($group->levels as $level)
                                                <button type="button"
                                                        class="grade-picker-item"
                                                        data-grade="{{ $level->title }}"
                                                        onclick="selectGrade(this, '{{ $level->title }}')">
                                                    <span class="grade-picker-label">{{ $level->title }}</span>
                                                    <svg class="grade-picker-check" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Content Categories 
                        <span class="text-xs font-normal text-gray-500 italic ml-1">(Leave blank for normal content)</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($categories as $category)
                            @if(strtolower($category->slug) !== 'normal')
                                <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox" name="upload_category_ids[]" value="{{ $category->id }}" class="mr-2 h-4 w-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
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
                                <input type="number" id="quiz_time_limit" min="0" max="300" value="15"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Set to 0 for no time limit</p>
                            </div>

                            <!-- Shuffle Questions -->
                            <div class="col-span-1 md:col-span-2 mt-2">
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" id="quiz_shuffle_questions" name="shuffle_questions" value="1" checked
                                               class="sr-only peer">
                                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors"></div>
                                        <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Shuffle Questions</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 ml-13">When enabled, questions will appear in a different order for each student.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">


                        <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-edit text-blue-600"></i>
                            Quiz Content
                        </h4>

                        <!-- Question Navigation -->
                        <div id="quizNavigation" class="quiz-navigation-wrapper mb-6">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Question Navigation</div>
                            <div id="quizNavGrid" class="quiz-nav-grid">
                                <!-- Navigation items injected via JS -->
                            </div>
                        </div>

                        <div id="questionsList" class="space-y-6">
                            <!-- Questions will be added here -->
                        </div>

                        <!-- Pagination Footer -->
                        <div class="pagination-footer mt-8">
                            <div id="currentQuestionLabel" class="text-sm font-semibold text-gray-600">No questions added</div>
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

                    <div class="flex space-x-2 mt-6">
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
                    <div class="grid grid-cols-2 gap-2 mt-2 text-xs text-gray-500">
                        <div><span class="font-medium">Uploaded:</span> <span id="videoUploadedBytes">0 B</span> / <span id="videoTotalBytes">0 B</span></div>
                        <div><span class="font-medium">Speed:</span> <span id="videoSpeed">0 MB/s</span></div>
                        <div id="videoChunkInfo" class="hidden"><span class="font-medium">Chunks:</span> <span id="videoChunkStatus">0/0</span></div>
                        <div><span class="font-medium">Remaining:</span> <span id="videoTimeRemaining">--</span></div>
                    </div>
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
                    <div class="grid grid-cols-2 gap-2 mt-2 text-xs text-gray-500">
                        <div><span class="font-medium">Uploaded:</span> <span id="documentUploadedBytes">0 B</span> / <span id="documentTotalBytes">0 B</span></div>
                        <div><span class="font-medium">Speed:</span> <span id="documentSpeed">0 MB/s</span></div>
                    </div>
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
                    <div class="grid grid-cols-2 gap-2 mt-2 text-xs text-gray-500">
                        <div><span class="font-medium">Speed:</span> <span id="quizSpeed">0 MB/s</span></div>
                    </div>
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
    // Global state for upload wizard
    let uploadData = {
        video: null,
        thumbnail: null,
        video_source: 'local',
        external_video_url: '',
        category_ids: [],
        documents: [],
        quiz: {
            questions: [],
            difficulty_level: 'medium',
            time_limit_minutes: 15
        }
    };
    let currentStep = 1;

    // Grade picker toggle
    function toggleGradePicker() {
        const dropdown = document.getElementById('gradePickerDropdown');
        if (dropdown) dropdown.classList.toggle('open');
    }

    // Grade picker selection handler
    function selectGrade(el, gradeValue) {
        // Deselect all
        document.querySelectorAll('.grade-picker-item.selected').forEach(item => item.classList.remove('selected'));
        // Select clicked
        el.classList.add('selected');
        // Update hidden input
        const gradeInput = document.getElementById('grade_level');
        if (gradeInput) gradeInput.value = gradeValue;
        // Update trigger text
        const trigger = document.getElementById('gradePickerTrigger');
        const triggerText = document.getElementById('gradePickerTriggerText');
        if (triggerText) triggerText.textContent = gradeValue;
        if (trigger) trigger.classList.add('has-value');
        // Close dropdown
        const dropdown = document.getElementById('gradePickerDropdown');
        if (dropdown) dropdown.classList.remove('open');
    }

    // Close grade picker when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('gradePickerDropdown');
        if (dropdown && !dropdown.contains(e.target) && !e.target.closest('#gradePickerTrigger')) {
            dropdown.classList.remove('open');
        }
    });

    // Multi-step upload wizard and table functionality
    function initializeDigilearn() {
        console.log('DigiLearn Content Management Initializing...');
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

        // Level Group Filter
        const levelGroupFilter = document.getElementById('levelGroupFilter');
        if (levelGroupFilter) {
            // Set initial value from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('level_group')) {
                levelGroupFilter.value = urlParams.get('level_group');
            }

            levelGroupFilter.addEventListener('change', function() {
                const url = new URL(window.location);
                if (this.value) {
                    url.searchParams.set('level_group', this.value);
                } else {
                    url.searchParams.delete('level_group');
                }
                window.location.href = url.toString();
            });
        }

        // Context (Category) Filter
        const contextFilter = document.getElementById('contextFilter');
        if (contextFilter) {
            // Set initial value from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('context')) {
                contextFilter.value = urlParams.get('context');
            }

            contextFilter.addEventListener('change', function() {
                const url = new URL(window.location);
                if (this.value) {
                    url.searchParams.set('context', this.value);
                } else {
                    url.searchParams.delete('context');
                }
                window.location.href = url.toString();
            });
        }

        // Table actions delegation
        document.addEventListener('click', function(e) {
            const target = e.target;
            
            // Edit button
            const editBtn = target.closest('.action-btn.edit-btn');
            if (editBtn) {
                e.preventDefault();
                const contentId = editBtn.getAttribute('data-content-id');
                if (contentId) {
                    window.location.href = `{{ route("admin.contents.edit", ":contentId") }}`.replace(':contentId', contentId);
                }
                return;
            }

            // Delete button
            const deleteBtn = target.closest('.action-btn.delete-btn');
            if (deleteBtn) {
                e.preventDefault();
                const contentId = deleteBtn.getAttribute('data-content-id');
                const contentType = deleteBtn.getAttribute('data-content-type');
                const videoSource = deleteBtn.getAttribute('data-video-source');
                const hasDocs = deleteBtn.getAttribute('data-has-docs') === 'true';
                const hasQuizzes = deleteBtn.getAttribute('data-has-quizzes') === 'true';

                let confirmMessage = 'Are you sure you want to delete this content? This action cannot be undone.';
                if (contentType === 'video') {
                    if (videoSource === 'vimeo') {
                        confirmMessage = 'Are you sure you want to delete this video? This will permanently delete the video from both the database AND Vimeo. This action cannot be undone.';
                    } else if (videoSource === 'youtube') {
                        confirmMessage = 'Are you sure you want to delete this video? This will only delete the video from the database (YouTube videos cannot be deleted remotely). This action cannot be undone.';
                    }
                }

                if (confirm(confirmMessage)) {
                    let deleteRelated = true;
                    if (contentType === 'video' && (hasDocs || hasQuizzes)) {
                        let relatedText = [];
                        if (hasDocs) relatedText.push('documents');
                        if (hasQuizzes) relatedText.push('quizzes');
                        deleteRelated = confirm('This lesson has related ' + relatedText.join(' and ') + '.\n\nDo you ALSO want to delete these related items?\n\n(Click "Cancel" to ONLY delete the video media and keep the documents/quizzes as a video-less lesson)');
                    }
                    if (typeof deleteContent === 'function') {
                        deleteContent(contentId, contentType, videoSource, deleteRelated);
                    }
                }
                return;
            }

            // Preview button
            const previewBtn = target.closest('.action-btn.preview-btn');
            if (previewBtn) {
                e.preventDefault();
                const videoId = previewBtn.getAttribute('data-video-id');
                const videoTitle = previewBtn.getAttribute('data-video-title');
                const videoUrl = previewBtn.getAttribute('data-video-url');
                if (!videoUrl || videoUrl === 'null') {
                    alert('Video URL is not available.');
                    return;
                }
                if (typeof openVideoPreview === 'function') {
                    openVideoPreview(videoId, videoTitle, videoUrl);
                }
                return;
            }

            // Approve button
            const approveBtn = target.closest('.action-btn.approve-btn');
            if (approveBtn) {
                e.preventDefault();
                const videoId = approveBtn.getAttribute('data-video-id');
                if (confirm('Are you sure you want to approve this video?')) {
                    if (typeof approveVideo === 'function') approveVideo(videoId);
                }
                return;
            }

            // Reject button
            const rejectBtn = target.closest('.action-btn.reject-btn');
            if (rejectBtn) {
                e.preventDefault();
                const videoId = rejectBtn.getAttribute('data-video-id');
                const reason = prompt('Please provide a reason for rejection (optional):');
                if (typeof rejectVideo === 'function') rejectVideo(videoId, reason);
                return;
            }
        });

        async function deleteContent(contentId, contentType, videoSource, deleteRelated = true) {
            try {
                // Use unified delete endpoint
                const response = await fetch(`{{ route("admin.contents.destroy", ":contentId") }}`.replace(':contentId', contentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ delete_related: deleteRelated })
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

        // Initialize upload progress broadcasting
        initializeUploadProgressBroadcasting();

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

        // Wizard state handled globally


        // Initialize wizard
        initializeWizard();

        function initializeWizard() {
            if (!uploadModal || !closeModal || !prevBtn || !nextBtn || !skipBtn || !finishBtn) {
                console.error('Required modal elements not found');
                return;
            }

            // Open modal
            const openModal = () => {
                uploadModal.classList.add('show');
                resetWizard();
            };
            if (uploadBtn) uploadBtn.addEventListener('click', openModal);
            const uploadBtnToolbar = document.getElementById('uploadBtnToolbar');
            if (uploadBtnToolbar) uploadBtnToolbar.addEventListener('click', openModal);

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
                category_ids: [],
                documents: [],
                quiz: {
                    questions: [],
                    difficulty_level: 'medium',
                    time_limit_minutes: 15,
                    shuffle_questions: true
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
            // Clear grade picker visual selection and trigger text
            document.querySelectorAll('.grade-picker-item.selected').forEach(el => el.classList.remove('selected'));
            const gradePickerTrigger = document.getElementById('gradePickerTrigger');
            const gradePickerTriggerText = document.getElementById('gradePickerTriggerText');
            if (gradePickerTrigger) gradePickerTrigger.classList.remove('has-value');
            if (gradePickerTriggerText) gradePickerTriggerText.textContent = 'Select Grade Level';
            const gradePickerDropdown = document.getElementById('gradePickerDropdown');
            if (gradePickerDropdown) gradePickerDropdown.classList.remove('open');

            // Clear category checkboxes
            document.querySelectorAll('input[name="upload_category_ids[]"]').forEach(cb => cb.checked = false);

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
            const quizShuffle = document.getElementById('quiz_shuffle_questions');
            if (quizShuffle) quizShuffle.checked = true;

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

                    if (!titleValue) {
                        alert('Please enter a title for this lesson.');
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

                    // Video is now optional, but if a URL is provided, it must be valid
                    if (uploadData.video_source === 'vimeo') {
                        const vimeoMethod = document.querySelector('input[name="vimeo_method"]:checked');
                        if (vimeoMethod && vimeoMethod.value === 'url') {
                            const externalVideoUrl = document.getElementById('external_video_url');
                            const externalUrl = externalVideoUrl ? externalVideoUrl.value.trim() : '';
                            
                            if (externalUrl) {
                                try {
                                    new URL(externalUrl);
                                    uploadData.external_video_url = externalUrl;
                                } catch {
                                    alert('Please enter a valid URL.');
                                    return false;
                                }
                            }
                        }
                    } else if (uploadData.video_source === 'youtube') {
                        const externalVideoUrl = document.getElementById('external_video_url');
                        const externalUrl = externalVideoUrl ? externalVideoUrl.value.trim() : '';
                        
                        if (externalUrl) {
                            try {
                                new URL(externalUrl);
                                uploadData.external_video_url = externalUrl;
                            } catch {
                                alert('Please enter a valid URL.');
                                return false;
                            }
                        }
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

        // Video Format & Size Validation Constants
        const ALLOWED_VIDEO_FORMATS = ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'mpeg', 'ogg', 'flv', 'wmv'];
        const ALLOWED_VIDEO_MIME_TYPES = [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska',
            'video/webm',
            'video/3gpp',
            'video/mpeg',
            'video/ogg',
            'video/x-flv',
            'video/x-ms-wmv',
        ];
        const MAX_VIDEO_SIZE = 30 * 1024 * 1024 * 1024; // 30GB in bytes

        // Video Validation Helper Functions
        function showVideoValidationError(errorType, fileSize = null, fileName = null) {
            const errorDiv = document.getElementById('videoValidationError');
            const errorMessage = document.getElementById('videoValidationErrorMessage');
            
            if (errorDiv && errorMessage) {
                let message = '';
                
                if (errorType === 'format') {
                    const ext = fileName ? fileName.split('.').pop().toLowerCase() : 'unknown';
                    message = `❌ Invalid video format (.${ext}). Accepted formats: MP4, MOV, AVI, MKV, WebM, 3GP, MPEG, OGG, FLV, WMV`;
                } else if (errorType === 'size') {
                    const fileSizeGB = (fileSize / (1024 * 1024 * 1024)).toFixed(2);
                    message = `❌ Video file size (${fileSizeGB}GB) exceeds maximum allowed size of 30GB. Please choose a smaller file.`;
                } else if (errorType === 'notVideo') {
                    message = `❌ This file is not a valid video. Please upload a video file (MP4, MOV, AVI, etc.)`;
                }
                
                errorMessage.innerHTML = message;
                errorDiv.classList.remove('hidden');
            }
        }

        function hideVideoValidationError() {
            const errorDiv = document.getElementById('videoValidationError');
            if (errorDiv) {
                errorDiv.classList.add('hidden');
            }
        }

        function isValidVideoFormat(file) {
            // Check by MIME type first (more reliable)
            if (ALLOWED_VIDEO_MIME_TYPES.includes(file.type)) {
                return true;
            }
            
            // Fallback to extension check
            const ext = file.name.split('.').pop().toLowerCase();
            return ALLOWED_VIDEO_FORMATS.includes(ext);
        }

        function validateVideoFile(file) {
            // Check if file type starts with 'video/' or has valid extension
            if (!file.type.startsWith('video/') && !isValidVideoFormat(file)) {
                showVideoValidationError('notVideo', null, file.name);
                return false;
            }
            
            // Check file format
            if (!isValidVideoFormat(file)) {
                showVideoValidationError('format', null, file.name);
                return false;
            }
            
            // Check file size
            if (file.size > MAX_VIDEO_SIZE) {
                showVideoValidationError('size', file.size);
                return false;
            }
            
            return true;
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
                    // Validate video format and size
                    if (!validateVideoFile(file)) {
                        fileInput.value = '';
                        return;
                    }
                    hideVideoValidationError();
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
                
                // Validate video format and size
                if (!validateVideoFile(file)) {
                    return;
                }
                
                hideVideoValidationError();
                uploadData.video = file;
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    fileInput.files = files;
                }
                updateVideoUploadArea(file);
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
                // Set initial value
                uploadData.quiz.difficulty_level = difficultySelect.value;
                difficultySelect.addEventListener('change', (e) => {
                    uploadData.quiz.difficulty_level = e.target.value;
                });
            }

            if (timeLimitInput) {
                // Set initial value
                uploadData.quiz.time_limit_minutes = parseInt(timeLimitInput.value) || 15;
                timeLimitInput.addEventListener('input', (e) => {
                    uploadData.quiz.time_limit_minutes = parseInt(e.target.value) || 15;
                });
            }

            const shuffleCheckbox = document.getElementById('quiz_shuffle_questions');
            if (shuffleCheckbox) {
                // Set initial value
                uploadData.quiz.shuffle_questions = shuffleCheckbox.checked;
                shuffleCheckbox.addEventListener('change', (e) => {
                    uploadData.quiz.shuffle_questions = e.target.checked;
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
                preamble: null, // New field for optional context
                options: type === 'mcq' ? ['', '', '', ''] : null,
                sub_questions: [], // Array for structured BECE-style parts
                correct_answer: type === 'mcq' ? 0 : '',
                points: 1,
                image: null,
                imageFile: null
            };


            uploadData.quiz.questions.push(question);

            const questionElement = createQuestionElement(question);
            questionsList.appendChild(questionElement);
        }

        function setupQuestionImageUpload(questionElement, question) {
            const uploadArea = questionElement.querySelector('.question-image-upload-area');
            const fileInput = questionElement.querySelector('.question-image-input');
            const uploadDiv = questionElement.querySelector(`#questionImageUpload_${question.id}`);
            const previewDiv = questionElement.querySelector(`#questionImagePreview_${question.id}`);
            const previewImg = previewDiv ? previewDiv.querySelector('.question-preview-img') : null;
            const removeImageBtn = previewDiv ? previewDiv.querySelector('.remove-question-image') : null;

            // Handle image upload
            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    return;
                }

                // Validate file type
                const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload a PNG, JPG, or WEBP image');
                    return;
                }

                // Create object URL for preview
                const objectUrl = URL.createObjectURL(file);

                // Update question data
                question.imageFile = file;
                question.image = objectUrl; // Store object URL for preview

                // Update UI
                if (previewImg) previewImg.src = objectUrl;
                uploadDiv.classList.add('hidden');
                previewDiv.classList.remove('hidden');
            });

            // Handle image removal
            if (removeImageBtn) {
                removeImageBtn.addEventListener('click', () => {
                    // Clean up object URL
                    if (question.image && question.image.startsWith('blob:')) {
                        URL.revokeObjectURL(question.image);
                    }

                    // Update question data
                    question.imageFile = null;
                    question.image = null;

                    // Clear file input
                    fileInput.value = '';

                    // Update UI
                    uploadDiv.classList.remove('hidden');
                    previewDiv.classList.add('hidden');
                });
            }
        }

        function createQuestionElement(question) {
            const div = document.createElement('div');
            div.className = 'question-item bg-white border border-gray-200 rounded-lg p-6 mb-6';
            div.dataset.questionId = question.id;

            const questionHeading = question.type === 'mcq' ? 'Multiple Choice Question' : 'Essay Question';

            const toolbarHtml = `
                <div class="rich-text-toolbar mb-2">
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-tool" data-command="bold" title="Bold"><i class="fas fa-bold"></i></button>
                        <button type="button" class="toolbar-tool" data-command="italic" title="Italic"><i class="fas fa-italic"></i></button>
                        <button type="button" class="toolbar-tool" data-command="underline" title="Underline"><i class="fas fa-underline"></i></button>
                        <button type="button" class="toolbar-tool" data-command="strikeThrough" title="Strikethrough"><i class="fas fa-strikethrough"></i></button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-tool" data-command="insertUnorderedList" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="toolbar-tool" data-command="insertOrderedList" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                    </div>
                    <div class="toolbar-group bg-blue-50 border-blue-200">
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\frac{#?}{#?}" title="Fraction"><b style="font-family: serif;">x/y</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\sqrt{#?}" title="Square Root"><b style="font-family: serif;">√x</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="^{#?}" title="Power/Exponent"><b style="font-family: serif;">x<sup>y</sup></b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="_{#?}" title="Subscript"><b style="font-family: serif;">x<sub>y</sub></b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\sum_{#?}^{#?}" title="Summation"><b style="font-family: serif;">∑</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\int_{#?}^{#?}" title="Integral"><b style="font-family: serif;">∫</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\neq" title="Not Equal"><b style="font-family: serif;">≠</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\leq" title="Less or Equal"><b style="font-family: serif;">≤</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\geq" title="Greater or Equal"><b style="font-family: serif;">≥</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\pi" title="Pi"><b style="font-family: serif;">π</b></button>
                        <button type="button" class="toolbar-tool math-action" data-math-command="\\theta" title="Theta"><b style="font-family: serif;">θ</b></button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-tool math-btn" data-command="math" title="Insert Empty Math Box">
                            <i class="fas fa-infinity"></i>
                            Math Area
                        </button>
                        <button type="button" class="toolbar-tool" data-command="removeFormat" title="Clear Formatting"><i class="fas fa-eraser"></i></button>
                    </div>
                </div>
            `;

            div.innerHTML = `
                <div class="flex justify-between items-center mb-6">
                    <h4 class="font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
                            ${uploadData.quiz.questions.indexOf(question) + 1}
                        </span>
                        ${questionHeading}
                    </h4>
                    <button type="button" class="text-gray-400 hover:text-red-600 transition-colors remove-question" title="Remove Question">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>

                <!-- Preamble Section (Optional) -->
                <div class="preamble-container mb-4">
                    <div id="preambleSection_${question.id}" class="preamble-section ${question.preamble ? '' : 'hidden'} editor-wrapper">
                        <div class="preamble-label">
                            <i class="fas fa-align-left"></i> Preamble / Context
                        </div>
                        ${toolbarHtml}
                        <div class="rich-text-editor preamble-text" contenteditable="true" 
                             placeholder="Enter optional preamble or reading passage here..."
                             aria-label="Preamble text">${question.preamble || ''}</div>
                    </div>
                    <button type="button" class="add-preamble-btn ${question.preamble ? 'hidden' : ''}" 
                            id="addPreambleBtn_${question.id}">
                        <i class="fas fa-plus"></i> Add Preamble
                    </button>
                </div>

                <!-- Image Upload Section -->
                <div class="mb-6 question-image-section">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Question Illustration (Optional)</label>
                    <div class="space-y-4">
                        <!-- Image Upload Button -->
                        <div id="questionImageUpload_${question.id}" class="question-image-upload ${question.image ? 'hidden' : ''}">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 question-image-upload-area">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600 font-semibold mb-1">Click to upload image</p>
                                <p class="text-xs text-gray-500 text-uppercase">PNG, JPG, or WEBP up to 5MB</p>
                                <input type="file" class="hidden question-image-input" accept=".png,.jpg,.jpeg,.webp">
                            </div>
                        </div>

                        <!-- Image Preview -->
                        <div id="questionImagePreview_${question.id}" class="question-image-preview ${question.image ? '' : 'hidden'}">
                            <div class="relative border border-gray-200 rounded-xl overflow-hidden bg-gray-50 p-2">
                                <img src="${question.image || ''}" alt="Question image" class="w-full h-auto max-h-64 object-contain rounded-lg question-preview-img">
                                <div class="absolute top-4 right-4 flex space-x-2">
                                    <button type="button" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition-colors remove-question-image" title="Remove image">
                                        <i class="fas fa-trash-alt text-red-600"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                ${question.type === 'mcq' ? `
                    <!-- Question Text -->
                    <div class="mb-6 editor-wrapper">
                        <label class="flex justify-between items-center text-sm font-semibold text-gray-700 mb-2">
                            <span>Question Text</span>
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-200" title="Planning to use complex mathematical equations? Please contact the developer for a quick guide on how to properly use the integrated math toolkit."><i class="fas fa-info-circle mr-1"></i> Contact Dev for Math Tools <span class="hidden sm:inline">Guide</span></span>
                        </label>
                        ${toolbarHtml}
                        <div class="rich-text-editor question-text" contenteditable="true" 
                             placeholder="Type your question here..."
                             aria-label="Question text">${question.question}</div>
                    </div>

                    <!-- MCQ Options -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-4">Answer Options</label>
                        <div class="space-y-4">
                            ${question.options.map((option, index) => `
                                <div class="flex items-start gap-4 p-4 border border-gray-100 rounded-xl bg-gray-50 group transition-all hover:bg-white hover:border-blue-200">
                                    <div class="mt-2">
                                        <input type="radio" name="correct_${question.id}" value="${index}"
                                            class="h-5 w-5 text-blue-600 focus:ring-blue-500 correct-answer" ${question.correct_answer === index ? 'checked' : ''}>
                                    </div>
                                    <div class="flex-1 editor-wrapper">
                                         ${toolbarHtml}
                                         <div class="rich-text-editor option-text" contenteditable="true" 
                                             placeholder="Option ${String.fromCharCode(65 + index)}"
                                             aria-label="Option ${index + 1}">${option}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : `
                    <!-- Structured Essay Sections -->
                    <div class="mb-6 editor-wrapper essay-question-main-text">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 main-question-label">Question Text</label>
                        <p class="text-xs text-gray-500 mb-2 main-question-hint hidden">Leave this blank to start directly with Question 1a, 1b, etc.</p>
                         ${toolbarHtml}
                         <div class="rich-text-editor question-text" contenteditable="true" 
                             placeholder="Type the question or shared context here..."
                             aria-label="Question text">${question.question}</div>
                    </div>

                    <div class="sub-questions-container" id="subQuestionsContainer_${question.id}">
                        <!-- Sub-questions will be injected here -->
                    </div>

                    <div class="mb-6">
                        <button type="button" class="add-sub-question-btn" id="addSubQuestionBtn_${question.id}">
                            <i class="fas fa-plus-circle"></i> Add Sub-part (a, b, c...)
                        </button>
                    </div>

                    <!-- Essay Sample Answer -->
                    <div class="mb-6 editor-wrapper">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Reference Answer (Sample)</label>
                         ${toolbarHtml}
                         <div class="rich-text-editor correct-answer" contenteditable="true" 
                             placeholder="Describe the expected answer for grading reference..."
                             aria-label="Sample answer">${question.correct_answer}</div>
                    </div>
                `}




                <div class="flex items-center justify-between border-t pt-6">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-700">Points:</label>
                            <div class="relative w-24">
                                <input type="number" class="w-full pl-3 pr-8 py-2 border border-gray-300 rounded-lg question-points focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    value="${question.points}" min="1" max="100">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">pts</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Setup rich text editor behaviors
            function setupEditorToolbar(container, editor) {
               container.querySelectorAll('.toolbar-tool').forEach(tool => {
                    tool.addEventListener('mousedown', (e) => e.preventDefault());
                    
                    if (tool.classList.contains('math-action')) {
                         tool.addEventListener('click', (e) => {
                             e.preventDefault();
                             handleMathAction(tool, editor);
                         });
                    } else {
                        tool.addEventListener('click', (e) => {
                            e.preventDefault();
                            handleCommand(tool, editor);
                        });
                    }
                });
            }

            function handleCommand(tool, editor) {
                const command = tool.dataset.command;
                if (command === 'math') {
                    insertMathField(tool);
                } else {
                    document.execCommand(command, false, null);
                    editor.focus();
                }
                updateQuestionModelFromEditor(editor);
            }

            function handleMathAction(tool, editor) {
                const mathCommand = tool.dataset.mathCommand;
                const activeEl = document.activeElement;
                let targetMf = (activeEl && activeEl.tagName.toLowerCase() === 'math-field') ? activeEl : null;

                if (targetMf) {
                    targetMf.executeCommand(['insert', mathCommand]);
                    targetMf.focus();
                } else {
                    editor.focus();
                    const mathId = 'math_' + Date.now();
                    const mathHtml = `<span contenteditable="false" class="math-wrapper px-1 inline-block"><math-field id="${mathId}" math-virtual-keyboard-policy="none" style="min-width: 30px; padding: 2px 4px;"></math-field></span>&nbsp;`;
                    document.execCommand('insertHTML', false, mathHtml);
                    
                    const mf = document.getElementById(mathId);
                    if (mf) {
                        mf.addEventListener('focusin', () => { editor.contentEditable = "false"; });
                        mf.addEventListener('focusout', () => { editor.contentEditable = "true"; });
                        mf.addEventListener('input', () => updateQuestionModelFromEditor(editor));
                        setTimeout(() => { mf.focus(); mf.executeCommand(['insert', mathCommand]); }, 50);
                    }
                }
                updateQuestionModelFromEditor(editor);
            }

            // Initialize all editors in this div
            div.querySelectorAll('.editor-wrapper').forEach(wrapper => {
                const editor = wrapper.querySelector('.rich-text-editor');
                if (editor) {
                    editor.addEventListener('input', () => updateQuestionModelFromEditor(editor));
                    
                    // Prevent pasting formatted text
                    editor.addEventListener('paste', (e) => {
                        e.preventDefault();
                        const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                        document.execCommand('insertHTML', false, text);
                    });

                    setupEditorToolbar(wrapper, editor);
                }
            });

            function insertMathField(tool) {
                const container = tool.closest('.editor-wrapper');
                const editor = container.querySelector('.rich-text-editor');
                if (!editor) return;

                editor.focus();

                const mathId = 'math_' + Date.now();
                const mathHtml = `<span contenteditable="false" class="math-wrapper px-1 inline-block"><math-field id="${mathId}" math-virtual-keyboard-policy="none" style="min-width: 30px; padding: 2px 4px;">\\placeholder{}</math-field></span>&nbsp;`;
                
                document.execCommand('insertHTML', false, mathHtml);
                
                const mf = document.getElementById(mathId);
                if (mf) {
                    mf.addEventListener('mousedown', e => e.stopPropagation());
                    mf.addEventListener('click', e => { e.stopPropagation(); mf.focus(); });
                    mf.addEventListener('focusin', () => { editor.contentEditable = "false"; });
                    mf.addEventListener('focusout', () => { editor.contentEditable = "true"; });
                    mf.addEventListener('input', () => updateQuestionModelFromEditor(editor));
                    
                    setTimeout(() => mf.focus(), 50);
                }
                updateQuestionModelFromEditor(editor);
            }

            function updateQuestionModelFromEditor(editor) {
                // Clone the editor node so we don't disrupt the live typing environment
                const clone = editor.cloneNode(true);
                
                // Sync all MathLive values into the clone's light DOM so they are saved to the database
                const liveMathFields = editor.querySelectorAll('math-field');
                const cloneMathFields = clone.querySelectorAll('math-field');
                liveMathFields.forEach((mf, i) => {
                    if (cloneMathFields[i]) {
                        cloneMathFields[i].textContent = mf.value;
                    }
                });

                const finalHtml = clone.innerHTML;

                if (editor.classList.contains('question-text')) {
                    question.question = finalHtml;
                } else if (editor.classList.contains('preamble-text')) {
                    question.preamble = finalHtml;
                } else if (editor.classList.contains('sub-question-text')) {
                    const subItem = editor.closest('.sub-question-item');
                    const subId = subItem.dataset.subId;
                    const subQuestion = question.sub_questions.find(sq => sq.id == subId);
                    if (subQuestion) subQuestion.text = finalHtml;
                } else if (editor.classList.contains('option-text')) {
                    const allOptions = div.querySelectorAll('.option-text');
                    const index = Array.from(allOptions).indexOf(editor);
                    if (index !== -1) question.options[index] = finalHtml;
                } else if (editor.classList.contains('correct-answer')) {
                    question.correct_answer = finalHtml;
                }
            }

            // Toggle active state based on selection

            div.addEventListener('keyup', () => updateToolbarState(div));
            div.addEventListener('mouseup', () => updateToolbarState(div));

            function updateToolbarState(container) {
                container.querySelectorAll('.toolbar-tool').forEach(tool => {
                    const command = tool.dataset.command;
                    tool.classList.toggle('active', document.queryCommandState(command));
                });
            }

            // Preamble toggle
            const addPreambleBtn = div.querySelector(`#addPreambleBtn_${question.id}`);
            const preambleSection = div.querySelector(`#preambleSection_${question.id}`);
            if (addPreambleBtn && preambleSection) {
                addPreambleBtn.addEventListener('click', () => {
                    preambleSection.classList.remove('hidden');
                    addPreambleBtn.classList.add('hidden');
                    const editor = preambleSection.querySelector('.rich-text-editor');
                    if (editor) editor.focus();
                });
            }

            // Standard event listeners
            const removeBtn = div.querySelector('.remove-question');
            const questionPoints = div.querySelector('.question-points');

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    uploadData.quiz.questions = uploadData.quiz.questions.filter(q => q.id !== question.id);
                    div.remove();
                    // Update question numbers
                    document.querySelectorAll('.question-item').forEach((qDiv, idx) => {
                        const numSpan = qDiv.querySelector('.bg-blue-100');
                        if (numSpan) numSpan.textContent = idx + 1;
                    });
                });
            }

            if (questionPoints) {
                questionPoints.addEventListener('input', (e) => {
                    question.points = parseInt(e.target.value) || 1;
                });
            }

            // Sub-question Logic
            if (question.type === 'essay') {
                const addSubBtn = div.querySelector(`#addSubQuestionBtn_${question.id}`);
                const subContainer = div.querySelector(`#subQuestionsContainer_${question.id}`);
                const mainQuestionWrapper = div.querySelector('.essay-question-main-text');

                // Render existing sub-questions on load
                if (question.sub_questions && question.sub_questions.length > 0) {
                    question.sub_questions.forEach(sub => {
                        const subEl = createSubQuestionElement(sub, question, div);
                        subContainer.appendChild(subEl);
                    });
                    updateTotalPoints();
                }

                addSubBtn.addEventListener('click', () => {
                    const subId = Date.now();
                    const subLabel = String.fromCharCode(97 + question.sub_questions.length); // a, b, c...
                    const subQuestion = {
                        id: subId,
                        label: subLabel,
                        text: '',
                        points: 1
                    };
                    question.sub_questions.push(subQuestion);
                    
                    
                    const subEl = createSubQuestionElement(subQuestion, question, div);
                    subContainer.appendChild(subEl);
                    updateTotalPoints();
                });

                function updateTotalPoints() {
                    const mainLabel = div.querySelector('.main-question-label');
                    const mainHint = div.querySelector('.main-question-hint');
                    
                    // Determine parent question number
                    const qItems = Array.from(document.querySelectorAll('.question-item'));
                    const qIndex = qItems.indexOf(div) + 1;

                    if (question.sub_questions.length > 0) {
                        if (mainLabel) mainLabel.textContent = 'Shared Content / Instructions (Optional)';
                        if (mainHint) mainHint.classList.remove('hidden');
                        
                        // Re-label sub-questions
                        div.querySelectorAll('.sub-question-item').forEach((item, idx) => {
                            const label = String.fromCharCode(97 + idx); // a, b, c...
                            const labelSpan = item.querySelector('.sub-question-label');
                            if (labelSpan) {
                                // If first item, show "Na)", otherwise just "b)"
                                labelSpan.textContent = (idx === 0) ? `${qIndex}${label})` : `${label})`;
                            }
                            const sq = question.sub_questions.find(s => s.id == item.dataset.subId);
                            if (sq) sq.label = label;
                        });

                        const total = question.sub_questions.reduce((sum, sq) => sum + sq.points, 0);
                        question.points = total;
                        if (questionPoints) {
                            questionPoints.value = total;
                            questionPoints.readOnly = true;
                            questionPoints.classList.add('bg-gray-50');
                        }
                    } else {
                        if (mainLabel) mainLabel.textContent = 'Question Text';
                        if (mainHint) mainHint.classList.add('hidden');
                        if (questionPoints) {
                            questionPoints.readOnly = false;
                            questionPoints.classList.remove('bg-gray-50');
                        }
                    }
                }

                function createSubQuestionElement(subQuestion, parentQuestion, parentDiv) {
                    const subDiv = document.createElement('div');
                    subDiv.className = 'sub-question-item';
                    subDiv.dataset.subId = subQuestion.id;
                    
                    subDiv.innerHTML = `
                        <div class="sub-question-header">
                            <div class="sub-question-label">Part ${subQuestion.label})</div>
                            <button type="button" class="text-gray-400 hover:text-red-500 remove-sub-question">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="editor-wrapper mb-3">
                            ${toolbarHtml}
                            <div class="rich-text-editor sub-question-text" contenteditable="true" 
                                 placeholder="Type response here..."
                                 aria-label="Sub-question text"></div>
                        </div>
                        <div class="sub-question-footer">
                            <label class="text-xs font-bold text-gray-500 uppercase">Marks for this part:</label>
                            <input type="number" class="w-20 px-2 py-1 border border-gray-300 rounded sub-points" 
                                   value="${subQuestion.points}" min="1">
                        </div>
                    `;

                    // Handle input
                    const editor = subDiv.querySelector('.sub-question-text');
                    editor.addEventListener('input', () => updateQuestionModelFromEditor(editor));
                    
                    // Handle toolbar
                    setupEditorToolbar(subDiv, editor);

                    // Handle removal
                    subDiv.querySelector('.remove-sub-question').addEventListener('click', () => {
                        parentQuestion.sub_questions = parentQuestion.sub_questions.filter(sq => sq.id !== subQuestion.id);
                        subDiv.remove();
                        updateTotalPoints();
                    });

                    // Handle points
                    subDiv.querySelector('.sub-points').addEventListener('input', (e) => {
                        subQuestion.points = parseInt(e.target.value) || 0;
                        updateTotalPoints();
                    });

                    return subDiv;
                }
            } // end if essay

            // Image upload handling

            setupQuestionImageUpload(div, question);

            if (question.type === 'mcq') {
                const correctAnswers = div.querySelectorAll('.correct-answer');
                correctAnswers.forEach((radio, index) => {
                    radio.addEventListener('change', () => {
                        question.correct_answer = index;
                    });
                });
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
            const videoPreviewWrapper = document.getElementById('videoPreviewWrapper');
            const videoSourceLabel = document.querySelector('label[for="video_source_label"]');

            // Hide all sections first
            if (localSection) localSection.classList.add('hidden');
            if (externalSection) externalSection.classList.add('hidden');
            if (uploadDestinationSection) uploadDestinationSection.classList.add('hidden');
            if (vimeoUploadOptions) vimeoUploadOptions.classList.add('hidden');

            // Show/hide video preview wrapper based on source
            if (source === 'none') {
                if (videoPreviewWrapper) videoPreviewWrapper.classList.add('hidden');
            } else {
                if (videoPreviewWrapper) videoPreviewWrapper.classList.remove('hidden');
            }

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
            } else if (source === 'none') {
                // No video - hide all video-related upload sections
                if (fileInput) fileInput.value = '';
                if (externalVideoUrl) externalVideoUrl.value = '';
                uploadData.video = null;
                uploadData.external_video_url = '';
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
                // Video is now optional, so we'll just handle it by the backend
                
                // If a URL was provided but is empty, that's fine too now
                switch (uploadData.video_source) {
                    case 'youtube':
                    case 'mux':
                    case 'vimeo':
                        // Basic cleanup for empty strings if needed
                        if (uploadData.external_video_url && uploadData.external_video_url.trim() === '') {
                            uploadData.external_video_url = null;
                        }
                        break;
                }

                // Get form elements - try multiple selectors to be sure
                const title = document.getElementById('title') || document.querySelector('[name="title"]');
                const subjectId = document.getElementById('subject_id') || document.querySelector('[name="subject_id"]');
                const description = document.getElementById('description') || document.querySelector('[name="description"]');
                const gradeLevel = document.getElementById('grade_level') || document.querySelector('[name="grade_level"]');

                if (!title || !subjectId || !description || !gradeLevel) {
                    console.error('Required form elements not found:', {
                        hasTitle: !!title,
                        hasSubjectId: !!subjectId,
                        hasDescription: !!description,
                        hasGradeLevel: !!gradeLevel
                    });
                    alert('Form elements not found. Please refresh the page and try again.');
                    return;
                }

                // Validate all fields have values
                const titleValue = title.value ? title.value.trim() : '';
                const subjectIdValue = subjectId.value ? subjectId.value.trim() : '';
                const descriptionValue = description.value ? description.value.trim() : '';
                const gradeLevelValue = gradeLevel.value ? gradeLevel.value.trim() : '';

                if (!titleValue) {
                    alert('Please enter a title for the video.');
                    return;
                }

                if (!subjectIdValue) {
                    alert('Please select a subject.');
                    return;
                }

                if (!gradeLevelValue) {
                    alert('Please select a grade level.');
                    return;
                }

                // Collect selected category IDs
                const selectedCategoryIds = Array.from(document.querySelectorAll('input[name="upload_category_ids[]"]:checked'))
                    .map(cb => cb.value);
                uploadData.category_ids = selectedCategoryIds;

                // Log the data being sent for debugging
                console.log('Upload data collected:', {
                    hasVideo: !!uploadData.video,
                    videoName: uploadData.video ? uploadData.video.name : null,
                    videoSize: uploadData.video ? uploadData.video.size : null,
                    title: titleValue,
                    subject_id: subjectIdValue,
                    description: descriptionValue,
                    grade_level: gradeLevelValue,
                    video_source: uploadData.video_source,
                    upload_destination: uploadData.upload_destination
                });

                const finalData = {
                    video: {
                        file: uploadData.video,
                        title: titleValue,
                        subject_id: subjectIdValue,
                        description: descriptionValue,
                        grade_level: gradeLevelValue,
                        video_source: uploadData.video_source,
                        external_video_url: uploadData.external_video_url,
                        upload_destination: uploadData.upload_destination,
                        category_ids: uploadData.category_ids
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
                    // Stop if video/lesson creation fails
                    showUploadErrors(errors);
                    updateOverallProgress(100, 'Upload failed at video step');
                    return;
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

        // Utility functions for progress tracking
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }

        function formatSpeed(bytesPerSecond) {
            return (bytesPerSecond / (1024 * 1024)).toFixed(2) + ' MB/s';
        }

        function formatTimeRemaining(seconds) {
            if (seconds === undefined || seconds === null || isNaN(seconds) || seconds <= 0) {
                return '--';
            }
            const totalSeconds = Math.round(seconds);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const secs = totalSeconds % 60;
            
            if (hours > 0) {
                return `${hours}h ${minutes}m`;
            } else if (minutes > 0) {
                return `${minutes}m ${secs}s`;
            } else {
                return `${secs}s`;
            }
        }

        function calculateSpeed(uploadedBytes, elapsedSeconds) {
            if (elapsedSeconds === undefined || elapsedSeconds === null || isNaN(elapsedSeconds) || elapsedSeconds <= 0) {
                return 0;
            }
            const bytesPerSecond = uploadedBytes / elapsedSeconds;
            // Ensure we return a valid number
            return isNaN(bytesPerSecond) || !isFinite(bytesPerSecond) ? 0 : bytesPerSecond;
        }

        function updateProgress(type, percentage, status, isError = false, metrics = {}) {
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

            // Update metrics if provided
            if (metrics.uploadedBytes !== undefined) {
                const uploadedEl = document.getElementById(`${type}UploadedBytes`);
                if (uploadedEl) uploadedEl.textContent = formatBytes(metrics.uploadedBytes);
            }
            if (metrics.totalBytes !== undefined) {
                const totalEl = document.getElementById(`${type}TotalBytes`);
                if (totalEl) totalEl.textContent = formatBytes(metrics.totalBytes);
            }
            if (metrics.speed !== undefined) {
                const speedEl = document.getElementById(`${type}Speed`);
                if (speedEl) speedEl.textContent = formatSpeed(metrics.speed);
            }
            if (metrics.timeRemaining !== undefined) {
                const timeEl = document.getElementById(`${type}TimeRemaining`);
                if (timeEl) timeEl.textContent = formatTimeRemaining(metrics.timeRemaining);
            }
            if (metrics.chunkInfo !== undefined) {
                const chunkEl = document.getElementById(`${type}ChunkStatus`);
                if (chunkEl) chunkEl.textContent = metrics.chunkInfo;
                const chunkInfoDiv = document.getElementById(`${type}ChunkInfo`);
                if (chunkInfoDiv) chunkInfoDiv.classList.remove('hidden');
            }
        }

        async function uploadVideoInChunksHybrid(finalData) {
            const chunkSize = 10 * 1024 * 1024; // 10MB chunks
            const videoFile = finalData.video.file;
            const totalSize = videoFile.size;
            const totalChunks = Math.ceil(totalSize / chunkSize);
            const uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

            let uploadedBytes = 0;
            let startTime = Date.now();
            let lastUpdateTime = startTime;
            let lastUploadedBytes = 0;

            try {
                // Phase 1: Preparation (0%)
                updateProgress('video', 0, 'Preparing video data...', false, {
                    uploadedBytes: 0,
                    totalBytes: totalSize,
                    speed: 0,
                    timeRemaining: 0,
                    chunkInfo: `0/${totalChunks}`
                });

                await new Promise(resolve => setTimeout(resolve, 300)); // Brief pause for UX

                // Phase 2: Upload chunks (0-90%)
                for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                    const start = chunkIndex * chunkSize;
                    const end = Math.min(start + chunkSize, totalSize);
                    const chunk = videoFile.slice(start, end);

                    const chunkFormData = new FormData();
                    chunkFormData.append('_token', '{{ csrf_token() }}');
                    chunkFormData.append('upload_id', uploadId);
                    chunkFormData.append('chunk_index', chunkIndex);
                    chunkFormData.append('total_chunks', totalChunks);
                    chunkFormData.append('chunk', chunk);
                    chunkFormData.append('filename', videoFile.name);

                    const response = await fetch('{{ route("admin.contents.upload.video-chunk") }}', {
                        method: 'POST',
                        body: chunkFormData
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        return { success: false, error: error.message || 'Chunk upload failed' };
                    }

                    uploadedBytes = end;

                    // Calculate speed and time remaining - update more frequently
                    const currentTime = Date.now();
                    const elapsedTotalSeconds = (currentTime - startTime) / 1000;
                    const elapsedSinceLastUpdate = (currentTime - lastUpdateTime) / 1000;

                    // Calculate instantaneous speed from overall time (more stable)
                    const overallSpeed = elapsedTotalSeconds > 0 ? uploadedBytes / elapsedTotalSeconds : 0;
                    const remainingBytes = totalSize - uploadedBytes;
                    const estimatedTimeRemaining = overallSpeed > 0 ? remainingBytes / overallSpeed : 0;

                    // Update every chunk for smooth progress (not just every 1 second)
                    // Progress: 0% + (90% * progress through chunks)
                    const uploadProgress = Math.floor((uploadedBytes / totalSize) * 90);

                    updateProgress('video', uploadProgress, `Uploading... Chunk ${chunkIndex + 1}/${totalChunks}`, false, {
                        uploadedBytes: uploadedBytes,
                        totalBytes: totalSize,
                        speed: overallSpeed,
                        timeRemaining: estimatedTimeRemaining,
                        chunkInfo: `${chunkIndex + 1}/${totalChunks}`
                    });

                    lastUpdateTime = currentTime;
                    lastUploadedBytes = uploadedBytes;
                }

                // Phase 3: Server processing (90-100%)
                updateProgress('video', 90, 'Processing video on server...', false, {
                    uploadedBytes: totalSize,
                    totalBytes: totalSize,
                    chunkInfo: `${totalChunks}/${totalChunks}`
                });

                // Wait for server to finalize
                await new Promise(resolve => setTimeout(resolve, 800));

                // Complete upload metadata
                const finalFormData = new FormData();
                finalFormData.append('_token', '{{ csrf_token() }}');
                finalFormData.append('upload_id', uploadId);
                finalFormData.append('filename', videoFile.name);
                finalFormData.append('title', finalData.video.title);
                finalFormData.append('subject_id', finalData.video.subject_id);
                finalFormData.append('description', finalData.video.description);
                finalFormData.append('grade_level', finalData.video.grade_level);
                finalFormData.append('video_source', finalData.video.video_source);
                finalFormData.append('upload_destination', finalData.video.upload_destination);

                if (finalData.video.category_ids && finalData.video.category_ids.length > 0) {
                    finalData.video.category_ids.forEach(id => {
                        finalFormData.append('category_ids[]', id);
                    });
                }

                if (uploadData.thumbnail) {
                    finalFormData.append('thumbnail_file', uploadData.thumbnail);
                }

                const finalResponse = await fetch('{{ route("admin.contents.upload.video") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: finalFormData
                });

                if (finalResponse.ok) {
                    const result = await finalResponse.json();
                    updateProgress('video', 100, 'Video uploaded successfully!', false, {
                        uploadedBytes: totalSize,
                        totalBytes: totalSize
                    });

                    if (result.data && result.data.video_id) {
                        window.uploadedVideoId = result.data.video_id;
                    }

                    return { success: true };
                } else {
                    const error = await finalResponse.json();
                    return { success: false, error: error.message || 'Upload finalization failed' };
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        async function uploadVideo(finalData) {
            try {
                // Handle URL-based uploads (YouTube, Vimeo URLs, Mux) differently
                const isUrlBasedUpload = finalData.video.video_source === 'youtube' ||
                                        finalData.video.video_source === 'mux' ||
                                        (finalData.video.video_source === 'vimeo' &&
                                         finalData.video.external_video_url &&
                                         finalData.video.external_video_url.trim() !== '');

                if (isUrlBasedUpload) {
                    // For URL-based uploads, just create the video record without file upload
                    updateProgress('video', 0, 'Creating video record...', false, {
                        uploadedBytes: 0,
                        totalBytes: 0,
                        speed: 0,
                        timeRemaining: 0
                    });

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('title', finalData.video.title);
                    formData.append('subject_id', finalData.video.subject_id);
                    formData.append('description', finalData.video.description);
                    formData.append('grade_level', finalData.video.grade_level);
                    formData.append('video_source', finalData.video.video_source);

                    if (finalData.video.category_ids && finalData.video.category_ids.length > 0) {
                        finalData.video.category_ids.forEach(id => {
                            formData.append('category_ids[]', id);
                        });
                    }

                    if (finalData.video.video_source === 'vimeo') {
                        formData.append('vimeo_url', finalData.video.external_video_url);
                    } else if (finalData.video.video_source === 'youtube') {
                        formData.append('external_video_url', finalData.video.external_video_url);
                    } else if (finalData.video.video_source === 'mux') {
                        formData.append('external_video_url', finalData.video.external_video_url);
                    }

                    if (uploadData.thumbnail) {
                        formData.append('thumbnail_file', uploadData.thumbnail);
                    }

                    updateProgress('video', 50, 'Processing video URL...', false, {
                        uploadedBytes: 0,
                        totalBytes: 0,
                        speed: 0
                    });

                    const response = await fetch('{{ route("admin.contents.upload.video") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        updateProgress('video', 100, 'Video created successfully!', false, {
                            uploadedBytes: 0,
                            totalBytes: 0,
                            speed: 0,
                            timeRemaining: 0
                        });

                        // Store video ID for later use
                        if (result.data && result.data.video_id) {
                            window.uploadedVideoId = result.data.video_id;
                        }

                        return { success: true };
                    } else {
                        const error = await response.json();
                        return { success: false, error: error.message || 'Unknown error' };
                    }
                } else {
                    // Handle file-based uploads or metadata-only
                    const videoFile = finalData.video.file;
                    const fileSize = videoFile ? videoFile.size : 0;
                    const maxSize = 30 * 1024 * 1024 * 1024; // 30GB in bytes
                    const largeFileThreshold = 0.008 * maxSize; // 0.8% of max size ≈ 245MB

                    if (videoFile && fileSize > largeFileThreshold) {
                        return await uploadVideoInChunksHybrid(finalData);
                    }

                    // Direct upload for smaller files or metadata-only (no video)
                    const startTime = Date.now();

                    updateProgress('video', 0, videoFile ? 'Preparing video data...' : 'Creating lesson record...', false, {
                        uploadedBytes: 0,
                        totalBytes: fileSize,
                        speed: 0,
                        timeRemaining: 0
                    });

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('title', finalData.video.title);
                    formData.append('subject_id', finalData.video.subject_id);
                    formData.append('description', finalData.video.description);
                    formData.append('grade_level', finalData.video.grade_level);
                    formData.append('video_source', videoFile ? finalData.video.video_source : 'none');

                    if (finalData.video.category_ids && finalData.video.category_ids.length > 0) {
                        finalData.video.category_ids.forEach(id => {
                            formData.append('category_ids[]', id);
                        });
                    }

                    if (videoFile) {
                        if (finalData.video.video_source === 'local') {
                            formData.append('video_file', videoFile);
                            formData.append('upload_destination', finalData.video.upload_destination);
                        } else if (finalData.video.video_source === 'vimeo') {
                            formData.append('video_file', videoFile);
                            formData.append('upload_destination', 'vimeo');
                        }
                    }

                    if (uploadData.thumbnail) {
                        formData.append('thumbnail_file', uploadData.thumbnail);
                    }

                    updateProgress('video', 30, videoFile ? 'Uploading video file...' : 'Finalizing...', false, {
                        uploadedBytes: fileSize * 0.3,
                        totalBytes: fileSize,
                        speed: 0
                    });

                    const response = await fetch('{{ route("admin.contents.upload.video") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        const elapsedSeconds = (Date.now() - startTime) / 1000;
                        const uploadSpeed = elapsedSeconds > 0 ? fileSize / elapsedSeconds : 0;

                        updateProgress('video', 100, 'Video uploaded successfully!', false, {
                            uploadedBytes: fileSize,
                            totalBytes: fileSize,
                            speed: uploadSpeed,
                            timeRemaining: 0
                        });

                        // Store video ID for later use
                        if (result.data && result.data.video_id) {
                            window.uploadedVideoId = result.data.video_id;
                        }

                        return { success: true };
                    } else {
                        const error = await response.json();
                        return { success: false, error: error.message || 'Unknown error' };
                    }
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        async function uploadDocuments(finalData) {
            try {
                let totalDocSize = 0;
                finalData.documents.forEach(doc => {
                    totalDocSize += doc.size || 0;
                });

                updateProgress('document', 5, 'Preparing documents...', false, {
                    uploadedBytes: 0,
                    totalBytes: totalDocSize,
                    speed: 0
                });

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('video_id', window.uploadedVideoId || '');

                let uploadedDocSize = 0;
                finalData.documents.forEach((doc, index) => {
                    formData.append(`documents[${index}]`, doc);
                    uploadedDocSize += doc.size || 0;
                    
                    // Progress: 5% + (90% * progress through docs)
                    const docProgress = 5 + Math.floor((uploadedDocSize / totalDocSize) * 90);
                    updateProgress('document', docProgress, `Uploading ${doc.name}...`, false, {
                        uploadedBytes: uploadedDocSize,
                        totalBytes: totalDocSize,
                        speed: 0
                    });
                });

                updateProgress('document', 95, 'Finalizing documents...', false, {
                    uploadedBytes: totalDocSize,
                    totalBytes: totalDocSize
                });

                const response = await fetch('{{ route("admin.contents.upload.documents") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    updateProgress('document', 100, 'Documents uploaded successfully!', false, {
                        uploadedBytes: totalDocSize,
                        totalBytes: totalDocSize
                    });
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
                updateProgress('quiz', 5, 'Preparing quiz data...', false, {
                    speed: 0
                });

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('video_id', window.uploadedVideoId || '');

                // Create quiz data structure
                const quizData = {
                    questions: [],
                    difficulty_level: finalData.quiz.difficulty_level,
                    time_limit_minutes: finalData.quiz.time_limit_minutes,
                    shuffle_questions: finalData.quiz.shuffle_questions
                };

                // Process each question
                let questionIndex = 0;
                for (const question of finalData.quiz.questions) {
                    const questionData = {
                        id: question.id,
                        type: question.type,
                        question: question.question,
                        preamble: question.preamble, // Include preamble
                        points: question.points,
                        sub_questions: question.sub_questions || []
                    };

                    if (question.type === 'mcq') {
                        questionData.options = question.options;
                        questionData.correct_answer = question.correct_answer;
                    } else {
                        questionData.correct_answer = question.correct_answer;
                    }

                    // Add question image if exists
                    if (question.imageFile) {
                        formData.append(`question_images[${questionIndex}]`, question.imageFile);
                        questionData.has_image = true;
                        questionData.image_index = questionIndex;
                    } else {
                        questionData.has_image = false;
                    }

                    quizData.questions.push(questionData);
                    questionIndex++;
                }

                // Use the values from finalData.quiz for the separate fields
                const difficultyLevel = finalData.quiz.difficulty_level || 'medium';
                const timeLimitMinutes = finalData.quiz.time_limit_minutes || 15;

                formData.append('quiz_data', JSON.stringify(quizData));
                formData.append('difficulty_level', difficultyLevel);
                formData.append('time_limit_minutes', timeLimitMinutes);
                formData.append('shuffle_questions', finalData.quiz.shuffle_questions ? '1' : '0');

                updateProgress('quiz', 50, 'Sending quiz to server...', false, {
                    speed: 0
                });

                const response = await fetch('{{ route("admin.contents.upload.quiz") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    updateProgress('quiz', 100, 'Quiz uploaded successfully!', false, {
                        speed: 0
                    });
                    return { success: true };
                } else {
                    const error = await response.json();
                    return { success: false, error: error.message || 'Unknown error' };
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
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

        // Upload Progress Broadcasting
        function initializeUploadProgressBroadcasting() {
            if (typeof Echo === 'undefined') {
                console.log('Broadcasting not available - Echo not loaded');
                return;
            }

            const userId = '{{ Auth::id() }}';
            if (!userId) {
                console.log('User not authenticated for broadcasting');
                return;
            }

            console.log('Initializing upload progress broadcasting for user:', userId);

            // Listen for upload progress events
            Echo.private('upload-progress.' + userId)
                .listen('.upload.progress', (e) => {
                    console.log('Received upload progress:', e);

                    // Update video progress if it's a video upload
                    if (e.upload_id && e.upload_id.startsWith('video_')) {
                        updateProgress('video', e.progress, e.status, false, {
                            uploadedBytes: e.uploaded_bytes,
                            totalBytes: e.total_bytes,
                            speed: e.speed,
                            timeRemaining: e.time_remaining
                        });
                    }
                })
                .error((error) => {
                    console.error('Broadcasting error:', error);
                });

            console.log('Upload progress broadcasting initialized');
        }
    }

    // Robust document ready handling
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDigilearn);
    } else {
        initializeDigilearn();
    }
</script>
@endsection