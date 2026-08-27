@extends('layouts.admin')

@section('title', 'Contents Management')
@section('page-title', 'Contents')
@section('page-description', 'Manage all your videos, documents, and quizzes')

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
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
            background: #0f172a;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .video-thumbnail-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            position: relative;
        }

        .video-thumbnail-placeholder.placeholder-video {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        }

        .video-thumbnail-placeholder.placeholder-quiz {
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
        }

        .video-thumbnail-placeholder.placeholder-document {
            background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
        }

        .content-type-badge-overlay {
            position: absolute;
            top: 4px;
            left: 4px;
            background: rgba(0, 0, 0, 0.65);
            color: #ffffff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            line-height: 1.2;
            z-index: 2;
        }

        .video-play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30px;
            height: 30px;
            background: rgba(0, 0, 0, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            pointer-events: none;
            transition: all 0.2s ease;
            z-index: 2;
        }

        .video-thumbnail:hover .video-play-overlay {
            transform: translate(-50%, -50%) scale(1.15);
            background: rgba(225, 29, 72, 0.9);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .video-duration {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
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
            margin-top: 6px;
            max-height: 80px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .video-description::-webkit-scrollbar {
            width: 4px;
        }

        .video-description::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .video-description p {
            margin: 0 0 4px 0;
        }

        .video-description p:last-child {
            margin-bottom: 0;
        }

        .video-description a {
            color: #2563eb;
            text-decoration: none;
        }

        .video-description a:hover {
            text-decoration: underline;
        }

        .video-description img {
            max-width: 100%;
            max-height: 40px;
            object-fit: contain;
            border-radius: 4px;
            margin: 4px 0;
            display: block;
        }

        .video-description ul,
        .video-description ol {
            margin: 0 0 6px 16px;
            padding: 0;
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

        .custom-select-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .custom-select-wrapper .toolbar-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 36px 8px 14px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .custom-select-wrapper .toolbar-select:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .custom-select-wrapper .toolbar-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .custom-select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 0.75rem;
            pointer-events: none;
            transition: color 0.15s ease;
        }

        .custom-select-wrapper:hover .custom-select-arrow {
            color: #0f172a;
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

        /* Upload Modal Styles (Canva-inspired UI) */
        .upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .upload-modal.show {
            display: flex;
        }

        .upload-modal-container {
            position: relative;
            width: 100%;
            max-width: 850px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        /* External Floating Close Button (Canva Style) */
        .canva-close-btn {
            position: absolute;
            top: 0;
            right: -56px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(226, 232, 240, 0.8);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            font-size: 1.125rem;
        }

        .canva-close-btn:hover {
            background: #ffffff;
            color: #ef4444;
            transform: scale(1.1);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.4);
        }

        @media (max-width: 960px) {
            .canva-close-btn {
                top: -52px;
                right: 0;
            }
        }

        .upload-form {
            background: white;
            border-radius: 20px;
            padding: 0;
            width: 100%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
        }

        .upload-modal-header {
            background: white;
            padding: 24px 28px 16px 28px;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
            z-index: 10;
        }

        .upload-modal-body {
            padding: 24px 28px;
            overflow-y: auto;
            flex: 1;
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

        /* Enhanced Batch Dropdown Select Styling */
        .batch-select-input {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #ffffff !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%232563eb' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 0.875rem 0.875rem !important;
            padding-right: 2.25rem !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.625rem !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            transition: all 0.2s ease-in-out !important;
        }

        .batch-select-input:hover {
            border-color: #3b82f6 !important;
            background-color: #f8fafc !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.08) !important;
        }

        .batch-select-input:focus {
            border-color: #2563eb !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
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
            background: rgba(0, 0, 0, 0.6);
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
            background: rgba(0, 0, 0, 0.8);
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
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        .content-table {
            min-width: 1000px;
        }

        /* Video Preview Modal Styles */
        #videoPreviewModal video {
            max-height: 70vh;
            object-fit: contain;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-table {
                min-width: 900px;
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
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .grade-picker-column:nth-child(1) .grade-picker-header::before {
            background: #7c8dff;
        }

        .grade-picker-column:nth-child(2) .grade-picker-header::before {
            background: #818cf8;
        }

        .grade-picker-column:nth-child(3) .grade-picker-header::before {
            background: #6366f1;
        }

        .grade-picker-column:nth-child(4) .grade-picker-header::before {
            background: #4338ca;
        }

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
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
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
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://unpkg.com/mathlive"></script>
@endpush

@section('content')
    <div class="min-h-screen bg-gray-50 relative">
        <!-- Floating Bulk Action Bar -->
        <div id="bulkActionBar" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-6 py-3.5 rounded-full shadow-2xl z-50 transition-all duration-300 transform translate-y-24 opacity-0 pointer-events-none flex items-center gap-4 border border-slate-700">
            <div class="flex items-center gap-2 pr-4 border-r border-slate-700 font-semibold text-sm">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold" id="bulkSelectedCount">0</span>
                <span class="text-slate-200">selected</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="bulkApproveBtn" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-full text-xs font-semibold transition-colors flex items-center gap-1.5 shadow">
                    <i class="fas fa-check"></i>
                    <span>Approve</span>
                </button>
                <button type="button" id="bulkDeleteBtn" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-full text-xs font-semibold transition-colors flex items-center gap-1.5 shadow">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete Selected</span>
                </button>
                <button type="button" id="bulkDeselectBtn" class="px-3 py-1.5 text-slate-400 hover:text-white text-xs font-medium transition-colors">
                    Deselect All
                </button>
            </div>
        </div>

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
            <a href="{{ route('admin.contents.index', ['q' => $query, 'type' => 'drafts', 'sort' => $sort]) }}"
                class="filter-tab {{ $type === 'drafts' || $type === 'draft' ? 'active' : '' }}">
                Drafts
                @if(($stats['total_drafts'] ?? 0) > 0)
                    <span class="ml-2 bg-slate-600 text-white text-xs px-2 py-0.5 rounded-full font-bold">{{ $stats['total_drafts'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.contents.index', ['q' => $query, 'type' => 'pending', 'sort' => $sort]) }}"
                class="filter-tab {{ $type === 'pending' ? 'active' : '' }}">
                Pending Review
                @if($stats['pending_reviews'] > 0)
                    <span
                        class="ml-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full font-bold">{{ $stats['pending_reviews'] }}</span>
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
                    <div class="custom-select-wrapper">
                        <select id="levelGroupFilter" class="toolbar-select">
                            <option value="">All Levels</option>
                            @foreach($levelGroups as $group)
                                <option value="{{ $group->slug }}">{{ $group->title }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down custom-select-arrow"></i>
                    </div>
                    <div class="custom-select-wrapper">
                        <select id="contextFilter" class="toolbar-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                @if(strtolower($category->slug) !== 'normal')
                                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down custom-select-arrow"></i>
                    </div>
                </div>
                <button class="toolbar-btn primary" id="uploadBtnToolbar">
                    <i class="fas fa-upload"></i>
                    Upload
                </button>
            </div>

            <!-- Table -->
            @if($contents->count() > 0)
                <div class="table-responsive-wrapper">
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Content</th>
                                <th>Subject</th>
                                <th>Grade Level</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="stats-cell">Views</th>
                                <th>Uploader</th>
                                <th class="actions-cell"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contents as $content)
                                <tr>
                                    <td class="checkbox-cell">
                                        <input type="checkbox" class="content-checkbox" value="{{ $content->id }}" data-type="{{ $content->content_type }}">
                                    </td>
                                    <td>
                                        <div class="video-cell">
                                            <div class="video-thumbnail">
                                                @php
                                                    $resolvedThumbnail = null;
                                                    if ($content->content_type === 'video') {
                                                        if (isset($content->thumbnail_url) && $content->thumbnail_url && !str_contains($content->thumbnail_url, 'video-placeholder.jpg')) {
                                                            $resolvedThumbnail = $content->thumbnail_url;
                                                        } elseif (method_exists($content, 'getThumbnailUrl')) {
                                                            $url = $content->getThumbnailUrl();
                                                            if ($url && !str_contains($url, 'video-placeholder.jpg')) {
                                                                $resolvedThumbnail = $url;
                                                            }
                                                        } elseif (!empty($content->thumbnail_path)) {
                                                            $resolvedThumbnail = asset('storage/' . $content->thumbnail_path);
                                                        }
                                                    }
                                                @endphp

                                                @if($content->content_type === 'video')
                                                    @if($resolvedThumbnail)
                                                        <img src="{{ $resolvedThumbnail }}"
                                                            alt="{{ $content->title }}"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="video-thumbnail-placeholder placeholder-video" style="display: none;">
                                                            <i class="fas fa-play"></i>
                                                        </div>
                                                        <div class="video-play-overlay">
                                                            <i class="fas fa-play" style="margin-left: 2px;"></i>
                                                        </div>
                                                    @else
                                                        <div class="video-thumbnail-placeholder placeholder-video">
                                                            <i class="fas fa-play"></i>
                                                        </div>
                                                    @endif
                                                    @if($content->duration_formatted && $content->duration_formatted !== '00:00:00' && $content->duration_formatted !== 'N/A')
                                                        <span class="video-duration">{{ $content->duration_formatted }}</span>
                                                    @endif
                                                @elseif($content->content_type === 'document')
                                                    @php
                                                        $docCover = null;
                                                        if (!empty($content->thumbnail_url) && !str_contains($content->thumbnail_url, 'video-placeholder.jpg')) {
                                                            $docCover = $content->thumbnail_url;
                                                        } elseif (!empty($content->file_path)) {
                                                            $docCover = \App\Services\PdfParser::getCoverThumbnailUrl($content->file_path);
                                                        } elseif (!empty($content->document_path)) {
                                                            $docCover = \App\Services\PdfParser::getCoverThumbnailUrl($content->document_path);
                                                        } elseif (!empty($content->documents) && $content->documents->count() > 0) {
                                                            $firstDoc = $content->documents->first();
                                                            $docCover = $firstDoc ? \App\Services\PdfParser::getCoverThumbnailUrl($firstDoc->file_path) : null;
                                                        }
                                                    @endphp
                                                    @if($docCover)
                                                        <img src="{{ $docCover }}"
                                                            alt="{{ $content->title }}"
                                                            style="width: 100%; height: 100%; object-fit: cover; object-position: center top;"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="video-thumbnail-placeholder placeholder-document" style="display: none;">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                    @else
                                                        <div class="video-thumbnail-placeholder placeholder-document">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                    @endif
                                                    <span class="content-type-badge-overlay">DOC</span>
                                                @else
                                                    <div class="video-thumbnail-placeholder placeholder-quiz">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </div>
                                                    <span class="content-type-badge-overlay">QUIZ</span>
                                                @endif
                                            </div>
                                            <div class="video-info">
                                                <div class="video-title">
                                                    <a href="{{ route('admin.contents.show', ['contentId' => $content->id, 'type' => $content->content_type]) }}" class="hover:text-blue-600 transition-colors">
                                                        {{ $content->title }}
                                                    </a>
                                                    @if(isset($content->is_agent_generated) && $content->is_agent_generated)
                                                        <span
                                                            style="font-size: 0.65rem; padding: 2px 6px; border-radius: 999px; font-weight: 500; background: #dbeafe; color: #1e40af; display: inline-flex; align-items: center; gap: 2px; vertical-align: middle; margin-left: 6px;">
                                                            <svg width="10" height="10" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                            </svg>
                                                            AI
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($content->content_type === 'video' && (($content->documents_count ?? 0) > 0 || !empty($content->document_path) || ($content->quizzes_count ?? 0) > 0 || !empty($content->quiz_id)))
                                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                        @if(($content->documents_count ?? 0) > 0 || !empty($content->document_path))
                                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-300" title="{{ ($content->documents_count ?? 1) }} Attached Document(s)">
                                                                <i class="fas fa-paperclip text-[9px] text-slate-500"></i> {{ ($content->documents_count ?? 1) }} Doc{{ ($content->documents_count ?? 1) > 1 ? 's' : '' }}
                                                            </span>
                                                        @endif
                                                        @if(($content->quizzes_count ?? 0) > 0 || !empty($content->quiz_id))
                                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200" title="Attached Quiz">
                                                                <i class="fas fa-clipboard-check text-[9px] text-blue-500"></i> Quiz
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($content->description)
                                                    <div class="video-description">{!! $content->description !!}</div>
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
                                    <td class="status-cell">
                                        @if(($content->status ?? '') === 'published' || ($content->status ?? '') === 'approved')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fas fa-check-circle text-[10px] text-blue-600"></i> Published
                                            </span>
                                        @elseif(($content->status ?? '') === 'draft')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                                <i class="fas fa-pencil-alt text-[10px] text-slate-500"></i> Draft
                                            </span>
                                        @elseif(($content->status ?? '') === 'pending')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300">
                                                <i class="fas fa-clock text-[10px] text-blue-600"></i> Pending
                                            </span>
                                        @elseif(($content->status ?? '') === 'rejected')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                                <i class="fas fa-times-circle text-[10px] text-red-600"></i> Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                                {{ ucfirst($content->status ?? 'Draft') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="date-cell">
                                        <div class="date-primary">{{ $content->published_date }}</div>
                                    </td>
                                    <td class="stats-cell">{{ number_format($content->views) }}</td>
                                    <td class="uploader-cell">
                                        @if($content->uploader)
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200 flex items-center justify-center">
                                                    <x-user-avatar :user="$content->uploader" :size="28" />
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-xs font-semibold text-slate-800 truncate max-w-[120px]" title="{{ $content->uploader->name }}">
                                                        {{ $content->uploader->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1.5 text-slate-400">
                                                <i class="fas fa-user-circle text-base"></i>
                                                <span class="text-xs text-slate-500 font-medium">{{ $content->uploader_name ?? 'System' }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="actions-cell">
                                        <a href="{{ route('admin.contents.show', ['contentId' => $content->id, 'type' => $content->content_type]) }}" class="action-btn" title="View Details" style="color: #64748b;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($type === 'pending' && $content->content_type === 'video')
                                            <button class="action-btn preview-btn" title="Preview Video"
                                                data-video-id="{{ $content->id }}" data-video-title="{{ $content->title }}"
                                                data-video-url="{{ route('admin.content.videos.stream', $content->id) }}"
                                                style="color: #3b82f6;">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="action-btn approve-btn" title="Approve" data-video-id="{{ $content->id }}"
                                                style="color: #10b981;">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="action-btn reject-btn" title="Reject" data-video-id="{{ $content->id }}"
                                                style="color: #ef4444;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @else
                                            <button class="action-btn edit-btn" title="Edit" data-content-id="{{ $content->id }}"
                                                data-content-type="{{ $content->content_type }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="action-btn delete delete-btn" title="Delete"
                                                data-content-id="{{ $content->id }}" data-content-type="{{ $content->content_type }}"
                                                data-video-source="{{ $content->video_source ?? '' }}"
                                                data-has-docs="{{ ($content->documents_count ?? 0) > 0 || !empty($content->document_path) ? 'true' : 'false' }}"
                                                data-has-quizzes="{{ ($content->quizzes_count ?? 0) > 0 || !empty($content->quiz_id) ? 'true' : 'false' }}">
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
                </div>
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
        <div class="upload-modal-container">
            <!-- External Floating Close Button (Canva Style) -->
            <button id="closeModal" class="canva-close-btn" aria-label="Close modal" title="Close modal">
                <i class="fas fa-times"></i>
            </button>

            <div class="upload-form">
                <!-- Fixed Header with Mode Switcher -->
                <div class="upload-modal-header">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 tracking-tight">Upload Content</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Select your preferred upload mode below</p>
                        </div>
                    </div>

                    <!-- Prominent Brand-Colored Mode Switcher Tab Bar -->
                    <div class="grid grid-cols-2 p-1.5 bg-blue-50/80 border border-blue-100 rounded-xl text-sm font-bold shadow-inner max-w-md mx-auto">
                        <button type="button" id="modeSingleBtn" class="py-2.5 px-4 rounded-lg text-white bg-blue-600 shadow-md border border-blue-700 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-box-open text-base"></i> Single Package Wizard
                        </button>
                        <button type="button" id="modeBatchBtn" class="py-2.5 px-4 rounded-lg text-blue-800 hover:bg-white/80 transition-all flex items-center justify-center gap-2 font-bold">
                            <i class="fas fa-layer-group text-base text-blue-600"></i> Multi-Content Batch <span class="bg-red-600 text-white text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full font-black shadow-sm">New</span>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Modal Body -->
                <div class="upload-modal-body">

            <!-- Single Package Wizard Container -->
            <div id="singlePackageWizard">
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Lesson Details & Video <span
                            class="text-sm font-normal text-gray-500">(Video is optional)</span></h3>
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Tip:</strong> You can create quiz-only or document-only content! Simply fill in the
                            Title, Subject & Grade below, select <strong>"No Video"</strong> as the source, then click Next
                            to add your documents or quizzes.
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
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-800 mb-2.5">Video Source</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                            <!-- Local Upload -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="video_source" value="local" class="sr-only peer" checked>
                                <div class="p-3 border-2 border-gray-200 rounded-2xl peer-checked:border-blue-600 peer-checked:bg-blue-50/70 peer-checked:shadow-sm hover:border-gray-300 transition-all flex flex-col items-center text-center h-full bg-white">
                                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 flex items-center justify-center mb-2 shadow-2xs group-hover:scale-105 transition-transform">
                                        <i class="fas fa-server text-lg"></i>
                                    </div>
                                    <div class="font-bold text-xs text-gray-900">Local Upload</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">MP4, MOV, AVI</div>
                                </div>
                            </label>

                            <!-- YouTube -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="video_source" value="youtube" class="sr-only peer">
                                <div class="p-3 border-2 border-gray-200 rounded-2xl peer-checked:border-[#FF0000] peer-checked:bg-red-50/70 peer-checked:shadow-sm hover:border-gray-300 transition-all flex flex-col items-center text-center h-full bg-white">
                                    <div class="w-11 h-11 rounded-xl bg-red-50 text-[#FF0000] border border-red-100/80 flex items-center justify-center mb-2 shadow-2xs group-hover:scale-105 transition-transform">
                                        <i class="fab fa-youtube text-2xl"></i>
                                    </div>
                                    <div class="font-bold text-xs text-gray-900">YouTube</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Video URL</div>
                                </div>
                            </label>

                            <!-- Vimeo -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="video_source" value="vimeo" class="sr-only peer">
                                <div class="p-3 border-2 border-gray-200 rounded-2xl peer-checked:border-[#1AB7EA] peer-checked:bg-sky-50/70 peer-checked:shadow-sm hover:border-gray-300 transition-all flex flex-col items-center text-center h-full bg-white">
                                    <div class="w-11 h-11 rounded-xl bg-sky-50 text-[#1AB7EA] border border-sky-100/80 flex items-center justify-center mb-2 shadow-2xs group-hover:scale-105 transition-transform">
                                        <i class="fab fa-vimeo-v text-xl"></i>
                                    </div>
                                    <div class="font-bold text-xs text-gray-900">Vimeo</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">URL or Direct API</div>
                                </div>
                            </label>

                            <!-- Mux (Official Logo Mark) -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="video_source" value="mux" class="sr-only peer">
                                <div class="p-3 border-2 border-gray-200 rounded-2xl peer-checked:border-slate-900 peer-checked:bg-slate-50 peer-checked:shadow-sm hover:border-gray-300 transition-all flex flex-col items-center text-center h-full bg-white">
                                    <div class="w-11 h-11 rounded-xl bg-slate-900 text-white border border-slate-800 flex items-center justify-center mb-2 shadow-2xs group-hover:scale-105 transition-transform p-1.5">
                                        <!-- Official MUX Logo Mark -->
                                        <svg class="w-8 h-4 text-white" viewBox="0 0 100 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.5 33.5C6.01472 33.5 4 31.4853 4 29V13C4 10.5147 6.01472 8.5 8.5 8.5C10.9853 8.5 13 10.5147 13 13V21.5L20.8 11.2C21.8 9.9 23.6 9.3 25.1 10C26.5 10.7 27.3 12.2 27.1 13.8L27 14.5V13C27 10.5147 29.0147 8.5 31.5 8.5C33.9853 8.5 36 10.5147 36 13V29C36 31.4853 33.9853 33.5 31.5 33.5C29.0147 33.5 27 31.4853 27 29V20.5L19.2 30.8C18.2 32.1 16.4 32.7 14.9 32C13.5 31.3 12.7 29.8 12.9 28.2L13 27.5V29C13 31.4853 10.9853 33.5 8.5 33.5Z" fill="currentColor"/>
                                            <circle cx="8.5" cy="13" r="2.2" fill="#0F172A"/>
                                            <circle cx="31.5" cy="13" r="2.2" fill="#0F172A"/>
                                            <circle cx="19.5" cy="29" r="2.2" fill="#0F172A"/>
                                            <path d="M44.5 8.5C46.9853 8.5 49 10.5147 49 13V24C49 26.2091 50.7909 28 53 28C55.2091 28 57 26.2091 57 24V13C57 10.5147 59.0147 8.5 61.5 8.5C63.9853 8.5 66 10.5147 66 13V24C66 31.1797 60.1797 37 53 37C45.8203 37 40 31.1797 40 24V13C40 10.5147 42.0147 8.5 44.5 8.5Z" fill="currentColor"/>
                                            <circle cx="61.5" cy="13" r="2.2" fill="#0F172A"/>
                                            <path d="M72.2 10.2C73.8 8.4 76.5 8.2 78.3 9.8L84 14.8L89.7 9.8C91.5 8.2 94.2 8.4 95.8 10.2C97.4 12 97.2 14.7 95.4 16.3L89.7 21.3L95.4 26.3C97.2 27.9 97.4 30.6 95.8 32.4C94.2 34.2 91.5 34.4 89.7 32.8L84 27.8L78.3 32.8C76.5 34.4 73.8 34.2 72.2 32.4C70.6 30.6 70.8 27.9 72.6 26.3L78.3 21.3L72.6 16.3C70.8 14.7 70.6 12 72.2 10.2Z" fill="currentColor"/>
                                            <circle cx="89.5" cy="31.5" r="2.2" fill="#0F172A"/>
                                        </svg>
                                    </div>
                                    <div class="font-bold text-xs text-gray-900">Mux</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Stream URL</div>
                                </div>
                            </label>

                            <!-- No Video -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="video_source" value="none" class="sr-only peer">
                                <div class="p-3 border-2 border-gray-200 rounded-2xl peer-checked:border-emerald-600 peer-checked:bg-emerald-50/70 peer-checked:shadow-sm hover:border-gray-300 transition-all flex flex-col items-center text-center h-full bg-white">
                                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 flex items-center justify-center mb-2 shadow-2xs group-hover:scale-105 transition-transform">
                                        <i class="fas fa-file-alt text-lg"></i>
                                    </div>
                                    <div class="font-bold text-xs text-gray-900">No Video</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Quiz/Docs only</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Vimeo Upload Options (Segmented Tab Bar) -->
                    <div class="mb-5 hidden" id="vimeoUploadOptions">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600">Vimeo Integration Method</label>
                            <span class="text-[11px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-semibold border border-blue-100">
                                <i class="fab fa-vimeo mr-1"></i>Vimeo Pro/API
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-100/90 rounded-xl border border-slate-200 text-xs font-bold shadow-inner max-w-md">
                            <label class="cursor-pointer">
                                <input type="radio" name="vimeo_method" value="url" class="sr-only peer" checked>
                                <div class="py-2 px-3 rounded-lg text-center text-gray-600 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm peer-checked:border peer-checked:border-slate-200/80 transition-all flex items-center justify-center gap-1.5">
                                    <i class="fas fa-link text-blue-500"></i>
                                    <span>Paste Vimeo URL</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="vimeo_method" value="file" class="sr-only peer">
                                <div class="py-2 px-3 rounded-lg text-center text-gray-600 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm peer-checked:border peer-checked:border-slate-200/80 transition-all flex items-center justify-center gap-1.5">
                                    <i class="fas fa-cloud-upload-alt text-indigo-500"></i>
                                    <span>Upload File to Vimeo</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Local / Vimeo File Upload Area -->
                    <div class="mb-5" id="localUploadSection">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-800">Video File</label>
                            <span id="localDropzoneBadge" class="hidden text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full">
                                <i class="fab fa-vimeo mr-1"></i>Direct Upload to Vimeo
                            </span>
                        </div>
                        <div id="fileUploadArea" class="file-upload-area rounded-2xl border-2 border-dashed border-gray-300 hover:border-blue-500 hover:bg-blue-50/40 p-6 text-center transition-all cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-2 shadow-2xs">
                                <i class="fas fa-cloud-upload-alt text-xl"></i>
                            </div>
                            <p class="text-sm font-bold text-gray-800">Click to upload or drag & drop video file</p>
                            <p class="text-xs text-gray-500 mt-1">MP4, MOV, AVI, MKV up to 30GB</p>
                        </div>
                        <input type="file" id="fileInput" class="hidden"
                            accept=".mp4,.mov,.avi,.mkv,.webm,.3gp,.mpeg,.ogg,.flv,.wmv">
                        <!-- Video Validation Error Message (Format & Size) -->
                        <div id="videoValidationError" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-xl hidden">
                            <p class="text-xs font-semibold text-red-700">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                <span id="videoValidationErrorMessage">Video file exceeds maximum size of 30GB</span>
                            </p>
                        </div>
                    </div>

                    <!-- External URL Input -->
                    <div class="mb-5 hidden" id="externalUrlSection">
                        <label for="external_video_url" class="block text-sm font-bold text-gray-800 mb-2" id="externalUrlLabel">
                            <span id="externalUrlPlatformName">Video</span> URL
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400" id="externalUrlIcon">
                                <i class="fas fa-link text-base"></i>
                            </div>
                            <input type="url" id="external_video_url"
                                placeholder="https://..."
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5" id="externalUrlHelper">Paste the full URL of your video from the selected platform.</p>
                    </div>

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Lesson / Main Title</label>
                        <input type="text" id="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">This is the primary name for the content package.</p>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <div id="quill-description-editor" class="bg-white rounded-b-lg border-gray-300 min-h-[100px]">
                        </div>
                        <textarea id="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 hidden"></textarea>
                        <p class="text-xs text-gray-500 mt-1">This description is meant for the video lesson and will be
                            displayed below the video player. It is not for the quiz.</p>
                    </div>

                    <!-- Subject -->
                    <div class="mb-4">
                        <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">Subject <span
                                class="text-red-500">*</span></label>
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
                            <button type="button" class="grade-picker-trigger" id="gradePickerTrigger"
                                onclick="toggleGradePicker()">
                                <span class="grade-picker-trigger-text" id="gradePickerTriggerText">Select Grade
                                    Level</span>
                                <svg class="grade-picker-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
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
                                                    <button type="button" class="grade-picker-item" data-grade="{{ $level->title }}"
                                                        onclick="selectGrade(this, '{{ $level->title }}')">
                                                        <span class="grade-picker-label">{{ $level->title }}</span>
                                                        <svg class="grade-picker-check" xmlns="http://www.w3.org/2000/svg"
                                                            width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                            stroke-linejoin="round">
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
                            <span class="text-xs font-normal text-gray-500 italic ml-1">(Leave blank for normal
                                content)</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($categories as $category)
                                @if(strtolower($category->slug) !== 'normal')
                                    <label
                                        class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                        <input type="checkbox" name="upload_category_ids[]" value="{{ $category->id }}"
                                            class="mr-2 h-4 w-4 text-blue-600 rounded">
                                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Thumbnail -->
                    <div class="mb-4">
                        <label for="thumbnail_file" class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Image
                            <span class="text-sm text-gray-500">(Optional)</span></label>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add Related Documents <span
                            class="text-sm text-gray-500">(Optional)</span></h3>
                    <p class="text-gray-600 mb-2">Upload PDF, DOC, DOCX, PPT, or PPTX files related to this lesson. Max file
                        size: 32GB per document.</p>
                    <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Document titles will be automatically set to their original filenames.
                        </p>
                    </div>

                    <div id="documentsList" class="space-y-3 mb-4">
                        <!-- Documents will be added here -->
                    </div>

                    <button type="button" id="addDocumentBtn"
                        class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-400 hover:text-blue-600 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Document
                    </button>
                    <input type="file" id="documentInput" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx" multiple>
                </div>

                <!-- Step 3: Quiz Builder -->
                <div class="step-pane" id="step3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create Quiz <span
                            class="text-sm text-gray-500">(Optional)</span></h3>
                    <p class="text-gray-600 mb-4">Build a quiz to test student understanding of this lesson.</p>

                    <div id="quizBuilder" class="space-y-4">
                        <!-- Quiz Settings -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Quiz Settings</h4>

                            <div class="mb-4">
                                <label for="quiz_title" class="block text-sm font-medium text-gray-700 mb-2">Quiz Title
                                    (Optional)</label>
                                <input type="text" id="quiz_title" name="quiz_title"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Leave empty to use the Lesson / Main Title">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Difficulty Level -->
                                <div>
                                    <label for="quiz_difficulty"
                                        class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                                    <select id="quiz_difficulty"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                    <label for="quiz_time_limit" class="block text-sm font-medium text-gray-700 mb-2">Time
                                        Limit (minutes)</label>
                                    <input type="number" id="quiz_time_limit" min="0" max="300" value="15"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-gray-500 mt-1">Set to 0 for no time limit</p>
                                </div>

                                <!-- Shuffle Questions -->
                                <div class="col-span-1 md:col-span-2 mt-2">
                                    <label class="flex items-center cursor-pointer group">
                                        <div class="relative">
                                            <input type="checkbox" id="quiz_shuffle_questions" name="shuffle_questions"
                                                value="1" checked class="sr-only peer">
                                            <div
                                                class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors">
                                            </div>
                                            <div
                                                class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full peer-checked:translate-x-5 transition-transform">
                                            </div>
                                        </div>
                                        <span
                                            class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Shuffle
                                            Questions</span>
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500 ml-13">When enabled, questions will appear in a
                                        different order for each student.</p>
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
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Question
                                    Navigation</div>
                                <div id="quizNavGrid" class="quiz-nav-grid">
                                    <!-- Navigation items injected via JS -->
                                </div>
                            </div>

                            <div id="questionsList" class="space-y-6">
                                <!-- Questions will be added here -->
                            </div>

                            <!-- Pagination Footer -->
                            <div class="pagination-footer mt-8">
                                <div id="currentQuestionLabel" class="text-sm font-semibold text-gray-600">No questions
                                    added</div>
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
                            <button type="button" id="addMcqBtn"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Add MCQ
                            </button>
                            <button type="button" id="addEssayBtn"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>Add Essay
                            </button>
                            <button type="button" id="addAiBtn" onclick="window.openAiModal && window.openAiModal()"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-sm border border-purple-500">
                                <i class="fas fa-magic mr-2"></i>AI Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
                <button type="button" id="prevBtn"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50" disabled>
                    <i class="fas fa-arrow-left mr-2"></i>Previous
                </button>

                <div class="flex space-x-2">
                    <button type="button" id="skipBtn"
                        class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Skip Step
                    </button>
                    <button type="button" id="nextBtn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <button type="button" id="saveDraftBtn"
                        class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 hidden">
                        <i class="fas fa-save mr-2"></i>Save as Draft
                    </button>
                    <button type="button" id="finishBtn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 hidden">
                        <i class="fas fa-check mr-2"></i>Publish
                    </button>
                </div>
            </div>
            </div><!-- End #singlePackageWizard -->

            <!-- Multi-Content Batch Container -->
            <div id="batchUploadContainer" class="hidden">
                <!-- Global Defaults Bar -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100/40 border border-blue-200 rounded-xl p-4 mb-6 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-blue-950 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-sliders-h text-blue-600"></i> Batch Default Settings (Applies to all dropped files)
                        </h4>
                        <span class="text-[11px] text-blue-700 font-medium">You can override per file below</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1">
                                <i class="fas fa-book text-blue-600 text-[10px]"></i> Default Subject
                            </label>
                            <select id="batchGlobalSubject" class="w-full text-xs py-2 px-3 batch-select-input">
                                <option value="">-- Select Subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1">
                                <i class="fas fa-graduation-cap text-blue-600 text-[10px]"></i> Default Grade Level
                            </label>
                            <select id="batchGlobalGrade" class="w-full text-xs py-2 px-3 batch-select-input">
                                <option value="General">General (All Grades)</option>
                                @if(isset($levelGroups) && $levelGroups->count())
                                    @foreach($levelGroups as $group)
                                        <optgroup label="{{ strtoupper($group->name ?? $group->title) }}">
                                            @foreach($group->levels as $level)
                                                <option value="{{ $level->title }}">{{ $level->title }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @else
                                    <optgroup label="LOWER PRIMARY">
                                        <option value="Basic 1">Basic 1</option>
                                        <option value="Basic 2">Basic 2</option>
                                        <option value="Basic 3">Basic 3</option>
                                    </optgroup>
                                    <optgroup label="UPPER PRIMARY">
                                        <option value="Basic 4">Basic 4</option>
                                        <option value="Basic 5">Basic 5</option>
                                        <option value="Basic 6">Basic 6</option>
                                    </optgroup>
                                    <optgroup label="JUNIOR HIGH SCHOOL">
                                        <option value="JHS 1">JHS 1</option>
                                        <option value="JHS 2">JHS 2</option>
                                        <option value="JHS 3">JHS 3</option>
                                    </optgroup>
                                    <optgroup label="SENIOR HIGH SCHOOL">
                                        <option value="SHS 1">SHS 1</option>
                                        <option value="SHS 2">SHS 2</option>
                                        <option value="SHS 3">SHS 3</option>
                                    </optgroup>
                                    <optgroup label="HIGHER EDUCATION">
                                        <option value="Tertiary">Tertiary</option>
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1">
                                <i class="fas fa-server text-blue-600 text-[10px]"></i> Video Destination
                            </label>
                            <select id="batchGlobalDestination" class="w-full text-xs py-2 px-3 batch-select-input">
                                <option value="local">Local Server Storage</option>
                                <option value="vimeo">Vimeo Cloud Upload</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1">
                                <i class="fas fa-check-circle text-blue-600 text-[10px]"></i> Default Status
                            </label>
                            <select id="batchGlobalStatus" class="w-full text-xs py-2 px-3 batch-select-input">
                                <option value="pending">Pending Review</option>
                                <option value="approved">Auto-Approved</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Drag and Drop Dropzone -->
                <div id="batchDropzone" class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center bg-blue-50/30 hover:bg-blue-50/70 transition cursor-pointer mb-6 group">
                    <input type="file" id="batchFileInput" multiple accept="video/*,.pdf,.doc,.docx,.ppt,.pptx,.json,.csv" class="hidden">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-cloud-upload-alt text-xl"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 mb-1">Drag & Drop Two or More Files Here</h4>
                    <p class="text-xs text-gray-500 mb-3">Upload Videos (.mp4), Documents (.pdf, .docx), and Quiz Papers simultaneously</p>
                    <span class="inline-block px-4 py-1.5 bg-white border border-blue-200 text-blue-700 rounded-full text-xs font-bold shadow-sm hover:bg-blue-50 transition-colors">
                        <i class="fas fa-folder-open mr-1.5 text-blue-600"></i> Browse Files from Computer
                    </span>
                </div>

                <!-- Batch Items Matrix Table -->
                <div id="batchTableWrapper" class="hidden mb-6 border rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50 px-4 py-2.5 border-b flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">
                            Files in Batch Queue (<span id="batchCountBadge">0</span>)
                        </span>
                        <button type="button" id="clearBatchBtn" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                            <i class="fas fa-trash-alt mr-1"></i> Clear All
                        </button>
                    </div>
                    <div class="overflow-x-auto max-h-72">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 text-gray-600 font-semibold uppercase tracking-wider border-b">
                                <tr>
                                    <th class="p-3">File</th>
                                    <th class="p-3">Title</th>
                                    <th class="p-3">Type / PDF Action</th>
                                    <th class="p-3">Subject & Grade</th>
                                    <th class="p-3">Link To Video</th>
                                    <th class="p-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="batchTableBody" class="divide-y divide-gray-200 bg-white">
                                <!-- Rows dynamically generated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Batch Submit Action Bar -->
                <div class="flex justify-end items-center gap-3 pt-4 border-t">
                    <button type="button" id="cancelBatchBtn" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="button" id="submitBatchBtn" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md transition-all flex items-center gap-2 text-xs disabled:opacity-50" disabled>
                        <i class="fas fa-rocket"></i> Start Batch Upload Process
                    </button>
                </div>
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
        <div class="upload-modal-container max-w-3xl">
            <!-- External Floating Close Button (Canva Style) -->
            <button id="closeProgressModal" class="canva-close-btn" aria-label="Close modal" title="Close modal">
                <i class="fas fa-times"></i>
            </button>

            <div class="upload-form">
                <!-- Modal Header -->
                <div class="upload-modal-header border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-black">
                            <i class="fas fa-cloud-upload-alt text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">Uploading Content</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Please stay on this page while your package is being processed</p>
                        </div>
                    </div>
                </div>

                <div class="upload-modal-body p-6 space-y-6">
                    <!-- Video Upload Progress -->
                    <div id="videoProgressSection" class="hidden p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-video text-blue-600"></i>
                                <span class="font-bold text-gray-900 text-sm">Video Upload</span>
                            </div>
                            <span id="videoProgressText" class="px-2.5 py-0.5 bg-blue-100/80 text-blue-800 font-black text-xs rounded-full border border-blue-200">0%</span>
                        </div>
                        <div class="w-full bg-gray-200/70 rounded-full h-3.5 p-0.5 overflow-hidden">
                            <div id="videoProgressBar" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-xs">
                            <p id="videoProgressStatus" class="font-medium text-gray-600">Preparing upload...</p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 pt-3 border-t border-slate-200/60 text-xs text-gray-600">
                            <div class="bg-white p-2.5 rounded-lg border border-slate-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Uploaded</span>
                                <span class="font-bold text-gray-800"><span id="videoUploadedBytes">0 B</span> / <span id="videoTotalBytes">0 B</span></span>
                            </div>
                            <div class="bg-white p-2.5 rounded-lg border border-slate-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Speed</span>
                                <span id="videoSpeed" class="font-bold text-gray-800">0 MB/s</span>
                            </div>
                            <div id="videoChunkInfo" class="hidden bg-white p-2.5 rounded-lg border border-slate-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Chunks</span>
                                <span id="videoChunkStatus" class="font-bold text-gray-800">0/0</span>
                            </div>
                            <div class="bg-white p-2.5 rounded-lg border border-slate-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Remaining</span>
                                <span id="videoTimeRemaining" class="font-bold text-gray-800">--</span>
                            </div>
                        </div>
                    </div>

                    <!-- Document Upload Progress -->
                    <div id="documentProgressSection" class="hidden p-4 rounded-xl bg-emerald-50/50 border border-emerald-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file-pdf text-emerald-600"></i>
                                <span class="font-bold text-gray-900 text-sm">Document Upload</span>
                            </div>
                            <span id="documentProgressText" class="px-2.5 py-0.5 bg-emerald-100/80 text-emerald-800 font-black text-xs rounded-full border border-emerald-200">0%</span>
                        </div>
                        <div class="w-full bg-gray-200/70 rounded-full h-3.5 p-0.5 overflow-hidden">
                            <div id="documentProgressBar" class="bg-gradient-to-r from-emerald-500 to-teal-600 h-full rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-xs">
                            <p id="documentProgressStatus" class="font-medium text-gray-600">Preparing documents...</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-emerald-200/50 text-xs text-gray-600">
                            <div class="bg-white p-2.5 rounded-lg border border-emerald-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Uploaded</span>
                                <span class="font-bold text-gray-800"><span id="documentUploadedBytes">0 B</span> / <span id="documentTotalBytes">0 B</span></span>
                            </div>
                            <div class="bg-white p-2.5 rounded-lg border border-emerald-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Speed</span>
                                <span id="documentSpeed" class="font-bold text-gray-800">0 MB/s</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Upload Progress -->
                    <div id="quizProgressSection" class="hidden p-4 rounded-xl bg-purple-50/50 border border-purple-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-question-circle text-purple-600"></i>
                                <span class="font-bold text-gray-900 text-sm">Quiz Upload</span>
                            </div>
                            <span id="quizProgressText" class="px-2.5 py-0.5 bg-purple-100/80 text-purple-800 font-black text-xs rounded-full border border-purple-200">0%</span>
                        </div>
                        <div class="w-full bg-gray-200/70 rounded-full h-3.5 p-0.5 overflow-hidden">
                            <div id="quizProgressBar" class="bg-gradient-to-r from-purple-500 to-indigo-600 h-full rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-xs">
                            <p id="quizProgressStatus" class="font-medium text-gray-600">Preparing quiz...</p>
                        </div>
                        <div class="grid grid-cols-1 gap-3 mt-3 pt-3 border-t border-purple-200/50 text-xs text-gray-600">
                            <div class="bg-white p-2.5 rounded-lg border border-purple-100 shadow-2xs">
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Status Speed</span>
                                <span id="quizSpeed" class="font-bold text-gray-800">0 MB/s</span>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Progress Section -->
                    <div class="p-5 rounded-2xl bg-indigo-50/60 border border-indigo-100/80 shadow-xs">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-tasks text-indigo-600"></i>
                                <span class="font-black text-gray-900 text-base">Overall Progress</span>
                            </div>
                            <span id="overallProgressText" class="px-3 py-1 bg-indigo-600 text-white font-black text-xs rounded-full shadow-sm">0%</span>
                        </div>
                        <div class="w-full bg-gray-200/80 rounded-full h-4 p-0.5 overflow-hidden shadow-inner">
                            <div id="overallProgressBar" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 h-full rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
                        </div>
                        <p id="overallProgressStatus" class="text-xs font-semibold text-indigo-900 mt-2.5">Starting upload process...</p>
                    </div>

                    <!-- Error Messages Box -->
                    <div id="uploadErrors" class="hidden">
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-triangle text-red-500 text-lg mr-2"></i>
                                <span class="font-bold text-red-900">Upload Errors</span>
                            </div>
                            <ul id="errorList" class="text-xs text-red-700 font-medium space-y-1 pl-6 list-disc"></ul>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="p-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                    <button id="cancelUploadBtn" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 text-xs transition-colors">
                        Cancel Upload
                    </button>
                    <button id="closeUploadBtn" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md text-xs transition-colors hidden">
                        Done & Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://unpkg.com/quill-magic-url"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        let quillDescModal = null;
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('quill-description-editor')) {
                const imageHandler = function () {
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
                                const range = quillDescModal.getSelection(true);
                                quillDescModal.insertEmbed(range.index, 'image', result.url);
                            } else {
                                alert('Image upload failed: ' + (result.message || 'Unknown error'));
                            }
                        } catch (error) {
                            console.error('Upload error:', error);
                            alert('Error uploading image');
                        }
                    };
                };

                quillDescModal = new Quill('#quill-description-editor', {
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
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                ['clean']
                            ],
                            handlers: {
                                image: imageHandler
                            }
                        }
                    }
                });

                const descTextarea = document.getElementById('description');
                quillDescModal.on('text-change', function () {
                    if (quillDescModal.getText().trim() === '') {
                        descTextarea.value = '';
                    } else {
                        descTextarea.value = quillDescModal.root.innerHTML;
                    }
                });
            }
        });
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
        document.addEventListener('click', function (e) {
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

            // Select all & Bulk Action Bar logic
            const bulkActionBar = document.getElementById('bulkActionBar');
            const bulkSelectedCount = document.getElementById('bulkSelectedCount');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkApproveBtn = document.getElementById('bulkApproveBtn');
            const bulkDeselectBtn = document.getElementById('bulkDeselectBtn');

            function updateBulkSelection() {
                const checkedBoxes = document.querySelectorAll('.content-checkbox:checked');
                const count = checkedBoxes.length;

                if (bulkSelectedCount) bulkSelectedCount.textContent = count;

                if (bulkActionBar) {
                    if (count > 0) {
                        bulkActionBar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                        bulkActionBar.classList.add('translate-y-0', 'opacity-100');
                    } else {
                        bulkActionBar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
                        bulkActionBar.classList.remove('translate-y-0', 'opacity-100');
                    }
                }

                if (selectAll) {
                    selectAll.checked = contentCheckboxes.length > 0 && count === contentCheckboxes.length;
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    contentCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkSelection();
                });
            }

            contentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkSelection);
            });

            if (bulkDeselectBtn) {
                bulkDeselectBtn.addEventListener('click', function() {
                    contentCheckboxes.forEach(checkbox => checkbox.checked = false);
                    if (selectAll) selectAll.checked = false;
                    updateBulkSelection();
                });
            }

            function getSelectedItems() {
                const selected = [];
                document.querySelectorAll('.content-checkbox:checked').forEach(cb => {
                    selected.push({
                        id: cb.value,
                        type: cb.getAttribute('data-type') || 'video'
                    });
                });
                return selected;
            }

            async function performBulkAction(action, extraPayload = {}) {
                const items = getSelectedItems();
                if (items.length === 0) {
                    alert('Please select at least one item.');
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                try {
                    const response = await fetch('{{ route("admin.contents.bulk-action") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: action,
                            items: items,
                            ...extraPayload
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message || 'Bulk action completed successfully.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to complete bulk action.');
                    }
                } catch (error) {
                    console.error('Bulk action error:', error);
                    alert('An unexpected error occurred during bulk operation.');
                }
            }

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const items = getSelectedItems();
                    if (items.length === 0) return;
                    
                    if (confirm(`Are you sure you want to delete ${items.length} selected item(s)? This action cannot be undone.`)) {
                        performBulkAction('delete', { delete_related: true });
                    }
                });
            }

            if (bulkApproveBtn) {
                bulkApproveBtn.addEventListener('click', function() {
                    const items = getSelectedItems();
                    if (items.length === 0) return;

                    if (confirm(`Approve ${items.length} selected item(s)?`)) {
                        performBulkAction('approve');
                    }
                });
            }

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('keypress', function (e) {
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

                levelGroupFilter.addEventListener('change', function () {
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

                contextFilter.addEventListener('change', function () {
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
            document.addEventListener('click', function (e) {
                const target = e.target;

                // Edit button
                const editBtn = target.closest('.action-btn.edit-btn');
                if (editBtn) {
                    e.preventDefault();
                    const contentId = editBtn.getAttribute('data-content-id');
                    const contentType = editBtn.getAttribute('data-content-type');
                    if (contentId) {
                        let editUrl = `{{ route("admin.contents.edit", ":contentId") }}`.replace(':contentId', contentId);
                        if (contentType) {
                            editUrl += (editUrl.includes('?') ? '&' : '?') + 'type=' + encodeURIComponent(contentType);
                        }
                        window.location.href = editUrl;
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
                    // Use unified delete endpoint with explicit content type
                    const response = await fetch(`{{ route("admin.contents.destroy", ":contentId") }}`.replace(':contentId', contentId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ delete_related: deleteRelated, type: contentType })
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
            initializeBatchUpload();

            // Multi-Content Batch Upload Logic
            let batchItems = [];

            function initializeBatchUpload() {
                const modeSingleBtn = document.getElementById('modeSingleBtn');
                const modeBatchBtn = document.getElementById('modeBatchBtn');
                const singlePackageWizard = document.getElementById('singlePackageWizard');
                const batchUploadContainer = document.getElementById('batchUploadContainer');
                const uploadFormModal = document.querySelector('.upload-form');

                if (modeSingleBtn && modeBatchBtn) {
                    modeSingleBtn.addEventListener('click', () => {
                        modeSingleBtn.className = "py-2.5 px-4 rounded-lg text-white bg-blue-600 shadow-md border border-blue-700 transition-all flex items-center justify-center gap-2 font-bold";
                        modeBatchBtn.className = "py-2.5 px-4 rounded-lg text-blue-800 hover:bg-white/80 transition-all flex items-center justify-center gap-2 font-bold";
                        if (singlePackageWizard) singlePackageWizard.classList.remove('hidden');
                        if (batchUploadContainer) batchUploadContainer.classList.add('hidden');
                        if (uploadFormModal) uploadFormModal.style.maxWidth = "800px";
                    });

                    modeBatchBtn.addEventListener('click', () => {
                        modeBatchBtn.className = "py-2.5 px-4 rounded-lg text-white bg-blue-600 shadow-md border border-blue-700 transition-all flex items-center justify-center gap-2 font-bold";
                        modeSingleBtn.className = "py-2.5 px-4 rounded-lg text-blue-800 hover:bg-white/80 transition-all flex items-center justify-center gap-2 font-bold";
                        if (singlePackageWizard) singlePackageWizard.classList.add('hidden');
                        if (batchUploadContainer) batchUploadContainer.classList.remove('hidden');
                        if (uploadFormModal) uploadFormModal.style.maxWidth = "1100px";
                    });
                }

                const batchDropzone = document.getElementById('batchDropzone');
                const batchFileInput = document.getElementById('batchFileInput');
                const batchTableWrapper = document.getElementById('batchTableWrapper');
                const batchTableBody = document.getElementById('batchTableBody');
                const batchCountBadge = document.getElementById('batchCountBadge');
                const submitBatchBtn = document.getElementById('submitBatchBtn');
                const clearBatchBtn = document.getElementById('clearBatchBtn');
                const cancelBatchBtn = document.getElementById('cancelBatchBtn');

                if (cancelBatchBtn && uploadModal) {
                    cancelBatchBtn.addEventListener('click', () => {
                        uploadModal.classList.remove('show');
                        batchItems = [];
                        renderBatchTable();
                    });
                }

                if (batchDropzone && batchFileInput) {
                    batchDropzone.addEventListener('click', (e) => {
                        if (e.target !== batchFileInput) {
                            batchFileInput.click();
                        }
                    });

                    batchDropzone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        batchDropzone.classList.add('bg-blue-100/70', 'border-blue-500');
                    });

                    batchDropzone.addEventListener('dragleave', () => {
                        batchDropzone.classList.remove('bg-blue-100/70', 'border-blue-500');
                    });

                    batchDropzone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        batchDropzone.classList.remove('bg-blue-100/70', 'border-blue-500');
                        if (e.dataTransfer.files && e.dataTransfer.files.length) {
                            handleBatchFiles(e.dataTransfer.files);
                        }
                    });

                    batchFileInput.addEventListener('change', (e) => {
                        if (e.target.files && e.target.files.length) {
                            handleBatchFiles(e.target.files);
                        }
                    });
                }

                function handleBatchFiles(files) {
                    const globalSubj = document.getElementById('batchGlobalSubject')?.value || '';
                    const globalGrade = document.getElementById('batchGlobalGrade')?.value || 'General';

                    Array.from(files).forEach(file => {
                        const ext = file.name.split('.').pop().toLowerCase();
                        let type = 'document';
                        if (['mp4', 'mov', 'avi', 'mkv', 'webm'].includes(ext)) {
                            type = 'video';
                        } else if (['json', 'csv'].includes(ext)) {
                            type = 'quiz';
                        }

                        const tempId = 'item_' + Math.random().toString(36).substr(2, 9);
                        const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");

                        batchItems.push({
                            temp_id: tempId,
                            file: file,
                            title: nameWithoutExt,
                            type: type,
                            is_pdf: ext === 'pdf',
                            convert_pdf_to_quiz: false,
                            subject_id: globalSubj,
                            grade_level: globalGrade,
                            parent_temp_id: '',
                            status: 'ready'
                        });
                    });

                    renderBatchTable();
                }

                function renderBatchTable() {
                    if (!batchTableBody || !batchTableWrapper) return;

                    if (batchItems.length === 0) {
                        batchTableWrapper.classList.add('hidden');
                        if (submitBatchBtn) submitBatchBtn.disabled = true;
                        if (batchCountBadge) batchCountBadge.textContent = '0';
                        return;
                    }

                    batchTableWrapper.classList.remove('hidden');
                    if (submitBatchBtn) submitBatchBtn.disabled = false;
                    if (batchCountBadge) batchCountBadge.textContent = batchItems.length;

                    const videoItems = batchItems.filter(i => i.type === 'video');

                    batchTableBody.innerHTML = batchItems.map((item, index) => {
                        const iconClass = item.type === 'video' ? 'fa-video text-blue-500' : (item.type === 'quiz' ? 'fa-question-circle text-purple-500' : 'fa-file-pdf text-red-500');

                        let typeCellHTML = `
                            <select onchange="window.updateBatchItemType(${index}, this.value)" class="text-xs py-1 px-2.5 batch-select-input font-semibold">
                                <option value="video" ${item.type === 'video' ? 'selected' : ''}>Video</option>
                                <option value="document" ${item.type === 'document' ? 'selected' : ''}>Document</option>
                                <option value="quiz" ${item.type === 'quiz' ? 'selected' : ''}>Quiz</option>
                            </select>
                        `;

                        if (item.is_pdf) {
                            typeCellHTML += `
                                <label class="flex items-center gap-1 mt-1 text-[11px] font-semibold text-blue-700 cursor-pointer">
                                    <input type="checkbox" onchange="window.togglePdfQuiz(${index}, this.checked)" ${item.convert_pdf_to_quiz ? 'checked' : ''} class="rounded text-blue-600 focus:ring-blue-500">
                                    <span>🧠 Convert to AI Quiz</span>
                                </label>
                            `;
                        }

                        let linkVideoOptions = `<option value="">-- Standalone Item --</option>`;
                        videoItems.forEach(v => {
                            if (v.temp_id !== item.temp_id) {
                                linkVideoOptions += `<option value="${v.temp_id}" ${item.parent_temp_id === v.temp_id ? 'selected' : ''}>Attach to: ${v.title}</option>`;
                            }
                        });

                        return `
                            <tr>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas ${iconClass} text-base"></i>
                                        <div>
                                            <div class="font-medium text-gray-900 truncate max-w-[140px]">${item.file ? item.file.name : 'Uploaded'}</div>
                                            <div class="text-[10px] text-gray-400">${item.file ? (item.file.size / (1024*1024)).toFixed(1) + ' MB' : ''}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <input type="text" value="${item.title}" onchange="window.updateBatchItemField(${index}, 'title', this.value)" class="w-full text-xs rounded-lg border-gray-300 py-1.5 px-2.5 font-medium focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                <td class="p-3">
                                    ${typeCellHTML}
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col gap-1.5">
                                        <select onchange="window.updateBatchItemField(${index}, 'subject_id', this.value)" class="text-xs py-1 px-2.5 batch-select-input">
                                            <option value="">Subject</option>
                                            @if(isset($subjects))
                                                @foreach($subjects as $subj)
                                                    <option value="{{ $subj->id }}" ${String(item.subject_id) === '{{ $subj->id }}' ? 'selected' : ''}>{{ $subj->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <select onchange="window.updateBatchItemField(${index}, 'grade_level', this.value)" class="text-xs py-1 px-2.5 batch-select-input">
                                            <option value="General" ${item.grade_level === 'General' ? 'selected' : ''}>General (All Grades)</option>
                                            @if(isset($levelGroups) && $levelGroups->count())
                                                @foreach($levelGroups as $group)
                                                    <optgroup label="{{ strtoupper($group->name ?? $group->title) }}">
                                                        @foreach($group->levels as $level)
                                                            <option value="{{ $level->title }}" ${item.grade_level === '{{ $level->title }}' ? 'selected' : ''}>{{ $level->title }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            @else
                                                <optgroup label="LOWER PRIMARY">
                                                    <option value="Basic 1" ${item.grade_level === 'Basic 1' ? 'selected' : ''}>Basic 1</option>
                                                    <option value="Basic 2" ${item.grade_level === 'Basic 2' ? 'selected' : ''}>Basic 2</option>
                                                    <option value="Basic 3" ${item.grade_level === 'Basic 3' ? 'selected' : ''}>Basic 3</option>
                                                </optgroup>
                                                <optgroup label="UPPER PRIMARY">
                                                    <option value="Basic 4" ${item.grade_level === 'Basic 4' ? 'selected' : ''}>Basic 4</option>
                                                    <option value="Basic 5" ${item.grade_level === 'Basic 5' ? 'selected' : ''}>Basic 5</option>
                                                    <option value="Basic 6" ${item.grade_level === 'Basic 6' ? 'selected' : ''}>Basic 6</option>
                                                </optgroup>
                                                <optgroup label="JUNIOR HIGH SCHOOL">
                                                    <option value="JHS 1" ${item.grade_level === 'JHS 1' ? 'selected' : ''}>JHS 1</option>
                                                    <option value="JHS 2" ${item.grade_level === 'JHS 2' ? 'selected' : ''}>JHS 2</option>
                                                    <option value="JHS 3" ${item.grade_level === 'JHS 3' ? 'selected' : ''}>JHS 3</option>
                                                </optgroup>
                                                <optgroup label="SENIOR HIGH SCHOOL">
                                                    <option value="SHS 1" ${item.grade_level === 'SHS 1' ? 'selected' : ''}>SHS 1</option>
                                                    <option value="SHS 2" ${item.grade_level === 'SHS 2' ? 'selected' : ''}>SHS 2</option>
                                                    <option value="SHS 3" ${item.grade_level === 'SHS 3' ? 'selected' : ''}>SHS 3</option>
                                                </optgroup>
                                                <optgroup label="HIGHER EDUCATION">
                                                    <option value="Tertiary" ${item.grade_level === 'Tertiary' ? 'selected' : ''}>Tertiary</option>
                                                </optgroup>
                                            @endif
                                        </select>
                                    </div>
                                </td>
                                <td class="p-3">
                                    ${item.type !== 'video' ? `<select onchange="window.updateBatchItemField(${index}, 'parent_temp_id', this.value)" class="text-xs py-1 px-2.5 batch-select-input w-full">${linkVideoOptions}</select>` : '<span class="text-gray-400 text-[11px]">— Parent Video —</span>'}
                                </td>
                                <td class="p-3 text-right">
                                    <button type="button" onclick="window.removeBatchItem(${index})" class="text-red-500 hover:text-red-700 p-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                window.updateBatchItemField = function(index, field, value) {
                    if (batchItems[index]) {
                        batchItems[index][field] = value;
                        if (field === 'type') renderBatchTable();
                    }
                };

                window.updateBatchItemType = function(index, type) {
                    if (batchItems[index]) {
                        batchItems[index].type = type;
                        renderBatchTable();
                    }
                };

                window.togglePdfQuiz = function(index, checked) {
                    if (batchItems[index]) {
                        batchItems[index].convert_pdf_to_quiz = checked;
                    }
                };

                window.removeBatchItem = function(index) {
                    batchItems.splice(index, 1);
                    renderBatchTable();
                };

                if (clearBatchBtn) {
                    clearBatchBtn.addEventListener('click', () => {
                        batchItems = [];
                        renderBatchTable();
                    });
                }

                if (submitBatchBtn) {
                    submitBatchBtn.addEventListener('click', async () => {
                        if (!batchItems.length) return;

                        submitBatchBtn.disabled = true;
                        submitBatchBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Uploading Files & Processing Batch...`;

                        const batchId = 'batch_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
                        const globalDefaults = {
                            subject_id: document.getElementById('batchGlobalSubject')?.value || null,
                            grade_level: document.getElementById('batchGlobalGrade')?.value || 'General',
                            video_source: document.getElementById('batchGlobalDestination')?.value || 'local',
                            status: document.getElementById('batchGlobalStatus')?.value || 'pending',
                        };

                        const submittedItems = [];

                        for (let i = 0; i < batchItems.length; i++) {
                            const item = batchItems[i];
                            let tempFilePath = null;

                            if (item.file) {
                                const formData = new FormData();
                                formData.append('documents[]', item.file);
                                formData.append('document_file', item.file);
                                formData.append('_token', '{{ csrf_token() }}');

                                try {
                                    const res = await fetch('{{ route("admin.contents.upload.documents") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Accept': 'application/json'
                                        },
                                        body: formData
                                    });
                                    const resData = await res.json();
                                    if (resData.success && resData.file_path) {
                                        tempFilePath = resData.file_path;
                                    } else if (resData.documents && resData.documents[0]) {
                                        tempFilePath = resData.documents[0].file_path;
                                    }
                                } catch (err) {
                                    console.warn('File upload direct warning:', err);
                                }
                            }

                            submittedItems.push({
                                temp_id: item.temp_id,
                                type: item.type,
                                title: item.title,
                                subject_id: item.subject_id || globalDefaults.subject_id,
                                grade_level: item.grade_level || globalDefaults.grade_level,
                                convert_pdf_to_quiz: item.convert_pdf_to_quiz,
                                parent_temp_id: item.parent_temp_id,
                                temp_file_path: tempFilePath,
                                video_source: globalDefaults.video_source,
                                status: globalDefaults.status,
                            });
                        }

                        try {
                            const response = await fetch('{{ route("admin.contents.batch-store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    batch_id: batchId,
                                    global_defaults: globalDefaults,
                                    items: submittedItems
                                })
                            });

                            const result = await response.json();

                            if (result.success) {
                                alert('Success! ' + result.message + '\nCreated: ' + result.counts.videos + ' videos, ' + result.counts.documents + ' documents, ' + result.counts.quizzes + ' quizzes.');
                                window.location.reload();
                            } else {
                                alert('Batch Upload Error: ' + (result.message || 'Unknown error'));
                                submitBatchBtn.disabled = false;
                                submitBatchBtn.innerHTML = `<i class="fas fa-rocket"></i> Start Batch Upload Process`;
                            }
                        } catch (err) {
                            alert('Server error processing batch: ' + err.message);
                            submitBatchBtn.disabled = false;
                            submitBatchBtn.innerHTML = `<i class="fas fa-rocket"></i> Start Batch Upload Process`;
                        }
                    });
                }
            }

            function initializeWizard() {
                if (!uploadModal || !closeModal || !prevBtn || !nextBtn || !skipBtn || !finishBtn) {
                    console.error('Required modal elements not found');
                    return;
                }

                // Open modal
                const openModal = () => {
                    uploadModal.classList.add('show');
                    resetWizard();
                    setTimeout(checkAndRestoreDraft, 100);
                };
                if (uploadBtn) uploadBtn.addEventListener('click', openModal);
                const uploadBtnToolbar = document.getElementById('uploadBtnToolbar');
                if (uploadBtnToolbar) uploadBtnToolbar.addEventListener('click', openModal);

                // Close modal
                closeModal.addEventListener('click', () => {
                    uploadModal.classList.remove('show');
                    if (typeof closeAiModal === 'function') closeAiModal();
                    resetWizard();
                });

                // Close modal when clicking outside
                uploadModal.addEventListener('click', (e) => {
                    // If AI modal is open, do not close the upload modal
                    const aiModal = document.getElementById('aiGenerateModal');
                    if (aiModal && !aiModal.classList.contains('hidden')) {
                        return;
                    }
                    if (e.target === uploadModal) {
                        uploadModal.classList.remove('show');
                        if (typeof closeAiModal === 'function') closeAiModal();
                        resetWizard();
                    }
                });

                // Navigation
                prevBtn.addEventListener('click', () => navigateStep(currentStep - 1));
                nextBtn.addEventListener('click', () => navigateStep(currentStep + 1));
                skipBtn.addEventListener('click', () => navigateStep(currentStep + 1));
                finishBtn.addEventListener('click', () => submitWizard('published'));

                // Save as Draft button
                const saveDraftBtn = document.getElementById('saveDraftBtn');
                if (saveDraftBtn) {
                    saveDraftBtn.addEventListener('click', () => submitWizard('draft'));
                }

                // Step 1: Video upload
                initializeVideoSourceSelection();
                initializeVideoStep();
                initializeThumbnailStep();

                // Step 2: Documents
                initializeDocumentsStep();

                // Step 3: Quiz builder
                initializeQuizStep();
                initializeQuizSettings();

                // Setup Auto-save interval (every 10 seconds)
                setInterval(autoSaveWizardState, 10000);
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
                if (typeof quillDescModal !== 'undefined' && quillDescModal) {
                    quillDescModal.setContents([]);
                }
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

            function autoSaveWizardState() {
                if (!document.getElementById('uploadModal').classList.contains('show')) return; // Only autosave when modal is open

                const title = document.getElementById('title');
                const subjectId = document.getElementById('subject_id');
                const description = document.getElementById('description');
                const gradeLevel = document.getElementById('grade_level');
                const quizTitle = document.getElementById('quiz_title');

                const selectedCategoryIds = Array.from(document.querySelectorAll('input[name="upload_category_ids[]"]:checked'))
                    .map(cb => cb.value);

                const draftData = {
                    title: title ? title.value : '',
                    subject_id: subjectId ? subjectId.value : '',
                    description: description ? description.value : '',
                    grade_level: gradeLevel ? gradeLevel.value : '',
                    category_ids: selectedCategoryIds,
                    video_source: uploadData.video_source,
                    external_video_url: uploadData.external_video_url,
                    quiz: uploadData.quiz,
                    quiz_title_input: quizTitle ? quizTitle.value : ''
                };

                localStorage.setItem('digilearn_upload_draft', JSON.stringify(draftData));
            }

            function checkAndRestoreDraft() {
                const draftJson = localStorage.getItem('digilearn_upload_draft');
                if (!draftJson) return;

                try {
                    const draftData = JSON.parse(draftJson);

                    // If it's completely empty, skip
                    if (!draftData.title && !draftData.description && (!draftData.quiz || draftData.quiz.questions.length === 0)) {
                        return;
                    }

                    if (confirm('We found an unsaved draft from a previous session. Would you like to restore your text inputs and quiz questions? (Note: You will need to re-select any video or document files)')) {
                        // Restore text fields
                        const title = document.getElementById('title');
                        if (title) title.value = draftData.title || '';

                        const subjectId = document.getElementById('subject_id');
                        if (subjectId) subjectId.value = draftData.subject_id || '';

                        const description = document.getElementById('description');
                        if (description) {
                            description.value = draftData.description || '';
                            if (typeof quillDescModal !== 'undefined' && quillDescModal && draftData.description) {
                                quillDescModal.clipboard.dangerouslyPasteHTML(draftData.description);
                            }
                        }

                        if (draftData.grade_level) {
                            const gradeLevel = document.getElementById('grade_level');
                            if (gradeLevel) gradeLevel.value = draftData.grade_level;
                            const gradePickerTriggerText = document.getElementById('gradePickerTriggerText');
                            if (gradePickerTriggerText) gradePickerTriggerText.textContent = draftData.grade_level;
                            const gradePickerTrigger = document.getElementById('gradePickerTrigger');
                            if (gradePickerTrigger) gradePickerTrigger.classList.add('has-value');

                            // Select the visual item
                            const item = document.querySelector(`.grade-picker-item[data-grade="${draftData.grade_level}"]`);
                            if (item) item.classList.add('selected');
                        }

                        if (draftData.category_ids && draftData.category_ids.length > 0) {
                            draftData.category_ids.forEach(id => {
                                const cb = document.querySelector(`input[name="upload_category_ids[]"][value="${id}"]`);
                                if (cb) cb.checked = true;
                            });
                        }

                        if (draftData.video_source) {
                            const sourceRadio = document.querySelector(`input[name="video_source"][value="${draftData.video_source}"]`);
                            if (sourceRadio) {
                                sourceRadio.checked = true;
                                if (typeof toggleVideoSourceSections === 'function') toggleVideoSourceSections(draftData.video_source);
                            }
                        }

                        if (draftData.external_video_url) {
                            const externalUrlInput = document.getElementById('external_video_url');
                            if (externalUrlInput) externalUrlInput.value = draftData.external_video_url;
                        }

                        if (draftData.quiz_title_input) {
                            const quizTitle = document.getElementById('quiz_title');
                            if (quizTitle) quizTitle.value = draftData.quiz_title_input;
                        }

                        if (draftData.quiz) {
                            uploadData.quiz = draftData.quiz;

                            // Apply settings
                            const quizDifficulty = document.getElementById('quiz_difficulty');
                            if (quizDifficulty && draftData.quiz.difficulty_level) {
                                quizDifficulty.value = draftData.quiz.difficulty_level;
                            }

                            const quizTimeLimit = document.getElementById('quiz_time_limit');
                            if (quizTimeLimit && draftData.quiz.time_limit_minutes !== undefined) {
                                quizTimeLimit.value = draftData.quiz.time_limit_minutes;
                            }

                            const quizShuffle = document.getElementById('quiz_shuffle_questions');
                            if (quizShuffle && draftData.quiz.shuffle_questions !== undefined) {
                                quizShuffle.checked = draftData.quiz.shuffle_questions;
                            }

                            // Re-render quiz builder if function exists
                            if (typeof renderQuizList === 'function') {
                                renderQuizList();
                            }
                        }
                    } else {
                        // User declined, clear it
                        localStorage.removeItem('digilearn_upload_draft');
                    }
                } catch (e) {
                    console.error("Failed to restore draft", e);
                    localStorage.removeItem('digilearn_upload_draft');
                }
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
                const saveDraftBtnNav = document.getElementById('saveDraftBtn');
                if (saveDraftBtnNav) saveDraftBtnNav.style.display = currentStep === 3 ? 'block' : 'none';
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
                        reader.onload = function (ev) {
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
                            file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
                            file.type === 'application/vnd.ms-powerpoint' ||
                            file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
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

                let iconClass = 'fa-file-alt text-blue-600';
                const fileName = file.name.toLowerCase();
                if (fileName.endsWith('.pdf')) {
                    iconClass = 'fa-file-pdf text-red-600';
                } else if (fileName.endsWith('.doc') || fileName.endsWith('.docx')) {
                    iconClass = 'fa-file-word text-blue-600';
                } else if (fileName.endsWith('.ppt') || fileName.endsWith('.pptx')) {
                    iconClass = 'fa-file-powerpoint text-orange-600';
                }

                const documentItem = document.createElement('div');
                documentItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                documentItem.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas ${iconClass} mr-3"></i>
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
                const quizTitleInput = document.getElementById('quiz_title');
                const difficultySelect = document.getElementById('quiz_difficulty');
                const timeLimitInput = document.getElementById('quiz_time_limit');

                if (quizTitleInput) {
                    uploadData.quiz.quiz_title = quizTitleInput.value;
                    quizTitleInput.addEventListener('input', (e) => {
                        uploadData.quiz.quiz_title = e.target.value;
                    });
                }

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
                    keywords: [], // Keywords for AI grading fallback
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

            // --- Keywords Tag Input Helper ---
            function setupKeywordsInput(inputEl, containerEl, dataObj) {
                if (!inputEl || !containerEl || !dataObj) return;
                if (!dataObj.keywords) dataObj.keywords = [];

                function renderTags() {
                    // Remove all existing tags
                    containerEl.querySelectorAll('.keyword-tag').forEach(t => t.remove());
                    // Re-render from model
                    dataObj.keywords.forEach((kw, i) => {
                        const tag = document.createElement('span');
                        tag.className = 'keyword-tag';
                        tag.dataset.index = i;
                        tag.innerHTML = `${kw}<button type="button" class="keyword-remove" data-index="${i}">&times;</button>`;
                        containerEl.insertBefore(tag, inputEl);

                        tag.querySelector('.keyword-remove').addEventListener('click', (e) => {
                            e.stopPropagation();
                            dataObj.keywords.splice(i, 1);
                            renderTags();
                        });
                    });
                    inputEl.placeholder = dataObj.keywords.length === 0 ? 'Type a keyword and press Enter...' : 'Add more...';
                }

                function addKeyword(value) {
                    const trimmed = value.trim().toLowerCase();
                    if (trimmed && !dataObj.keywords.includes(trimmed)) {
                        dataObj.keywords.push(trimmed);
                        renderTags();
                    }
                    inputEl.value = '';
                }

                inputEl.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        addKeyword(inputEl.value);
                    }
                    // Allow backspace to remove last tag when input is empty
                    if (e.key === 'Backspace' && inputEl.value === '' && dataObj.keywords.length > 0) {
                        dataObj.keywords.pop();
                        renderTags();
                    }
                });

                // Handle paste of comma-separated keywords
                inputEl.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    pasted.split(',').forEach(kw => addKeyword(kw));
                });

                // Also handle blur to add any typed keyword
                inputEl.addEventListener('blur', () => {
                    if (inputEl.value.trim()) addKeyword(inputEl.value);
                });

                // Wire up existing remove buttons (for initial render from AI data)
                containerEl.querySelectorAll('.keyword-remove').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const idx = parseInt(btn.dataset.index);
                        dataObj.keywords.splice(idx, 1);
                        renderTags();
                    });
                });
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
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-tool insert-image-btn" data-command="insertImage" title="Insert Image">
                                <i class="fas fa-image"></i>
                            </button>
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
                        <div class="mb-6 p-5 border border-blue-100 rounded-xl bg-blue-50/30">
                            <div class="mb-6 editor-wrapper essay-question-main-text">
                                <label class="block text-sm font-semibold text-gray-700 mb-2 main-question-label">Question Text</label>
                                <p class="text-xs text-gray-500 mb-2 main-question-hint hidden">Leave this blank to start directly with Question 1a, 1b, etc.</p>
                                 ${toolbarHtml}
                                 <div class="rich-text-editor question-text bg-white" contenteditable="true" 
                                     placeholder="Type the question or shared context here..."
                                     aria-label="Question text">${question.question}</div>
                            </div>

                            <!-- Essay Sample Answer -->
                            <div class="editor-wrapper">
                                <label class="block text-sm font-semibold text-gray-700 mb-2 main-answer-label">Reference Answer (Sample)</label>
                                 ${toolbarHtml}
                                 <div class="rich-text-editor correct-answer bg-white" contenteditable="true" 
                                     placeholder="Describe the expected answer for grading reference..."
                                     aria-label="Sample answer">${question.correct_answer}</div>
                            </div>

                            <!-- Keywords for AI Grading -->
                            <div class="mt-4 keywords-section" id="keywordsSection_${question.id}">
                                <div class="flex items-center gap-2 mb-2 cursor-pointer keywords-toggle" data-target="keywordsBody_${question.id}">
                                    <i class="fas fa-key text-amber-500 text-xs"></i>
                                    <label class="text-xs font-bold text-amber-700 uppercase cursor-pointer">Grading Keywords</label>
                                    <span class="text-[10px] text-gray-400 font-normal">(used for AI fallback grading)</span>
                                    <i class="fas fa-chevron-down text-gray-400 text-[10px] keywords-chevron transition-transform"></i>
                                </div>
                                <div id="keywordsBody_${question.id}" class="keywords-body">
                                    <div class="keywords-tags-container" id="keywordsTags_${question.id}">
                                        ${(question.keywords || []).map((kw, i) => `
                                            <span class="keyword-tag" data-index="${i}">
                                                ${kw}
                                                <button type="button" class="keyword-remove" data-index="${i}">&times;</button>
                                            </span>
                                        `).join('')}
                                        <input type="text" class="keyword-input" id="keywordInput_${question.id}"
                                               placeholder="${(question.keywords || []).length === 0 ? 'Type a keyword and press Enter...' : 'Add more...'}" />
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1">Press <kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px]">Enter</kbd> or <kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-[9px]">,</kbd> to add. These keywords are used by the grading engine when AI is unavailable.</p>
                                </div>
                            </div>
                        </div>

                        <div class="sub-questions-container" id="subQuestionsContainer_${question.id}">
                            <!-- Sub-questions will be injected here -->
                        </div>

                        <div class="mb-6">
                            <button type="button" class="add-sub-question-btn" id="addSubQuestionBtn_${question.id}">
                                <i class="fas fa-plus-circle"></i> Add Sub-part (a, b, c...)
                            </button>
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
                        } else if (tool.classList.contains('insert-image-btn')) {
                            tool.addEventListener('click', (e) => {
                                e.preventDefault();
                                handleImageUpload(editor);
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

                function handleImageUpload(editor) {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/png,image/jpeg,image/jpg,image/webp');
                    input.click();

                    input.onchange = async () => {
                        const file = input.files[0];
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

                        // Show uploading indicator on the button
                        const btn = editor.closest('.editor-wrapper')?.querySelector('.insert-image-btn');
                        const originalContent = btn ? btn.innerHTML : '';
                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            btn.disabled = true;
                        }

                        try {
                            const formData = new FormData();
                            formData.append('image', file);
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}');

                            const response = await fetch('{{ route("admin.contents.upload.image") }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                                }
                            });

                            const result = await response.json();

                            if (result.success && result.url) {
                                editor.focus();
                                const imgHtml = `<img src="${result.url}" alt="Uploaded image" style="max-width: 100%; height: auto; border-radius: 8px; margin: 8px 0; display: block;">`;
                                document.execCommand('insertHTML', false, imgHtml);
                                updateQuestionModelFromEditor(editor);
                            } else {
                                alert('Image upload failed: ' + (result.message || 'Unknown error'));
                            }
                        } catch (error) {
                            console.error('Image upload error:', error);
                            alert('Error uploading image. Please try again.');
                        } finally {
                            if (btn) {
                                btn.innerHTML = originalContent;
                                btn.disabled = false;
                            }
                        }
                    };
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
                    } else if (editor.classList.contains('sub-question-answer')) {
                        const subItem = editor.closest('.sub-question-item');
                        const subId = subItem.dataset.subId;
                        const subQuestion = question.sub_questions.find(sq => sq.id == subId);
                        if (subQuestion) subQuestion.sample_answer = finalHtml;
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
                            sample_answer: '',
                            keywords: [],
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
                        const mainAnswerLabel = div.querySelector('.main-answer-label');

                        // Determine parent question number
                        const qItems = Array.from(document.querySelectorAll('.question-item'));
                        const qIndex = qItems.indexOf(div) + 1;

                        if (question.sub_questions.length > 0) {
                            if (mainLabel) mainLabel.textContent = 'Shared Content / Instructions (Optional)';
                            if (mainHint) mainHint.classList.remove('hidden');
                            if (mainAnswerLabel) mainAnswerLabel.textContent = 'Shared Content Reference Answer (Optional)';

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
                            if (mainAnswerLabel) mainAnswerLabel.textContent = 'Reference Answer (Sample)';
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
                                <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Question Text:</label>
                                ${toolbarHtml}
                                <div class="rich-text-editor sub-question-text" contenteditable="true" 
                                     placeholder="Type sub-question part here..."
                                     aria-label="Sub-question text">${subQuestion.text || ''}</div>
                            </div>
                            <div class="editor-wrapper mb-3">
                                <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block text-blue-600">Sample/Model Answer (Required for AI Grading):</label>
                                ${toolbarHtml}
                                <div class="rich-text-editor sub-question-answer" contenteditable="true" 
                                     placeholder="Reference answer for this part..."
                                     aria-label="Sub-question sample answer">${subQuestion.sample_answer || ''}</div>
                            </div>
                            <!-- Sub-question Keywords -->
                            <div class="mt-2 mb-3 keywords-section sub-keywords-section">
                                <div class="flex items-center gap-2 mb-1 cursor-pointer keywords-toggle" data-target="subKeywordsBody_${subQuestion.id}">
                                    <i class="fas fa-key text-amber-500 text-[10px]"></i>
                                    <label class="text-[10px] font-bold text-amber-700 uppercase cursor-pointer">Keywords</label>
                                    <i class="fas fa-chevron-down text-gray-400 text-[10px] keywords-chevron transition-transform"></i>
                                </div>
                                <div id="subKeywordsBody_${subQuestion.id}" class="keywords-body">
                                    <div class="keywords-tags-container" id="subKeywordsTags_${subQuestion.id}">
                                        ${(subQuestion.keywords || []).map((kw, i) => `
                                            <span class="keyword-tag" data-index="${i}">
                                                ${kw}
                                                <button type="button" class="keyword-remove" data-index="${i}">&times;</button>
                                            </span>
                                        `).join('')}
                                        <input type="text" class="keyword-input" id="subKeywordInput_${subQuestion.id}"
                                               placeholder="${(subQuestion.keywords || []).length === 0 ? 'Add keyword...' : 'More...'}" />
                                    </div>
                                </div>
                            </div>
                            <div class="sub-question-footer">
                                <label class="text-xs font-bold text-gray-500 uppercase">Marks for this part:</label>
                                <input type="number" class="w-20 px-2 py-1 border border-gray-300 rounded sub-points" 
                                       value="${subQuestion.points}" min="1">
                            </div>
                        `;

                        // Handle input and toolbar for all editors in this sub-question
                        subDiv.querySelectorAll('.rich-text-editor').forEach(editor => {
                            editor.addEventListener('input', () => updateQuestionModelFromEditor(editor));
                            setupEditorToolbar(subDiv, editor);
                        });

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

                        // Handle sub-question keywords
                        setupKeywordsInput(
                            subDiv.querySelector(`#subKeywordInput_${subQuestion.id}`),
                            subDiv.querySelector(`#subKeywordsTags_${subQuestion.id}`),
                            subQuestion
                        );

                        // Handle sub-question keywords toggle
                        const subKwToggle = subDiv.querySelector('.keywords-toggle');
                        if (subKwToggle) {
                            subKwToggle.addEventListener('click', () => {
                                const targetId = subKwToggle.dataset.target;
                                const body = document.getElementById(targetId);
                                const chevron = subKwToggle.querySelector('.keywords-chevron');
                                if (body) body.classList.toggle('collapsed');
                                if (chevron) chevron.classList.toggle('rotated');
                            });
                        }

                        return subDiv;
                    }

                    // Main question keywords input
                    setupKeywordsInput(
                        div.querySelector(`#keywordInput_${question.id}`),
                        div.querySelector(`#keywordsTags_${question.id}`),
                        question
                    );

                    // Main question keywords toggle
                    const mainKwToggle = div.querySelector(`#keywordsSection_${question.id} .keywords-toggle`);
                    if (mainKwToggle) {
                        mainKwToggle.addEventListener('click', () => {
                            const targetId = mainKwToggle.dataset.target;
                            const body = document.getElementById(targetId);
                            const chevron = mainKwToggle.querySelector('.keywords-chevron');
                            if (body) body.classList.toggle('collapsed');
                            if (chevron) chevron.classList.toggle('rotated');
                        });
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
                const vimeoUploadOptions = document.getElementById('vimeoUploadOptions');
                const externalVideoUrl = document.getElementById('external_video_url');
                const externalUrlPlatformName = document.getElementById('externalUrlPlatformName');
                const externalUrlIcon = document.getElementById('externalUrlIcon');
                const externalUrlHelper = document.getElementById('externalUrlHelper');
                const fileInput = document.getElementById('fileInput');
                const videoPreviewContainer = document.getElementById('videoPreviewContainer');
                const videoPreviewWrapper = document.getElementById('videoPreviewWrapper');
                const localDropzoneBadge = document.getElementById('localDropzoneBadge');

                // Hide all sections first
                if (localSection) localSection.classList.add('hidden');
                if (externalSection) externalSection.classList.add('hidden');
                if (vimeoUploadOptions) vimeoUploadOptions.classList.add('hidden');
                if (localDropzoneBadge) localDropzoneBadge.classList.add('hidden');

                // Show/hide video preview wrapper based on source
                if (source === 'none') {
                    if (videoPreviewWrapper) videoPreviewWrapper.classList.add('hidden');
                } else {
                    if (videoPreviewWrapper) videoPreviewWrapper.classList.remove('hidden');
                }

                if (source === 'local') {
                    uploadData.upload_destination = 'local';
                    if (localSection) localSection.classList.remove('hidden');
                    if (externalVideoUrl) externalVideoUrl.value = '';
                    uploadData.external_video_url = '';
                } else if (source === 'vimeo') {
                    // Show Vimeo-specific options
                    if (vimeoUploadOptions) vimeoUploadOptions.classList.remove('hidden');
                    // Initialize Vimeo method handling
                    handleVimeoMethodSelection();
                } else if (source === 'youtube') {
                    uploadData.upload_destination = 'youtube';
                    if (externalSection) externalSection.classList.remove('hidden');
                    if (externalUrlPlatformName) externalUrlPlatformName.textContent = 'YouTube Video';
                    if (externalVideoUrl) externalVideoUrl.placeholder = 'https://www.youtube.com/watch?v=... or https://youtu.be/...';
                    if (externalUrlIcon) externalUrlIcon.innerHTML = '<i class="fab fa-youtube text-red-600 text-lg"></i>';
                    if (externalUrlHelper) externalUrlHelper.textContent = 'Paste a public or unlisted YouTube video URL.';
                    if (fileInput) fileInput.value = '';
                    uploadData.video = null;
                    if (videoPreviewContainer) videoPreviewContainer.classList.add('hidden');
                } else if (source === 'mux') {
                    uploadData.upload_destination = 'mux';
                    if (externalSection) externalSection.classList.remove('hidden');
                    if (externalUrlPlatformName) externalUrlPlatformName.textContent = 'Mux Stream';
                    if (externalVideoUrl) externalVideoUrl.placeholder = 'https://stream.mux.com/{PLAYBACK_ID}.m3u8 or Playback ID';
                    if (externalUrlIcon) externalUrlIcon.innerHTML = `<svg class="w-6 h-3 text-slate-800" viewBox="0 0 100 42" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.5 33.5C6.01472 33.5 4 31.4853 4 29V13C4 10.5147 6.01472 8.5 8.5 8.5C10.9853 8.5 13 10.5147 13 13V21.5L20.8 11.2C21.8 9.9 23.6 9.3 25.1 10C26.5 10.7 27.3 12.2 27.1 13.8L27 14.5V13C27 10.5147 29.0147 8.5 31.5 8.5C33.9853 8.5 36 10.5147 36 13V29C36 31.4853 33.9853 33.5 31.5 33.5C29.0147 33.5 27 31.4853 27 29V20.5L19.2 30.8C18.2 32.1 16.4 32.7 14.9 32C13.5 31.3 12.7 29.8 12.9 28.2L13 27.5V29C13 31.4853 10.9853 33.5 8.5 33.5Z" fill="currentColor"/><circle cx="8.5" cy="13" r="2.2" fill="#FFFFFF"/><circle cx="31.5" cy="13" r="2.2" fill="#FFFFFF"/><circle cx="19.5" cy="29" r="2.2" fill="#FFFFFF"/><path d="M44.5 8.5C46.9853 8.5 49 10.5147 49 13V24C49 26.2091 50.7909 28 53 28C55.2091 28 57 26.2091 57 24V13C57 10.5147 59.0147 8.5 61.5 8.5C63.9853 8.5 66 10.5147 66 13V24C66 31.1797 60.1797 37 53 37C45.8203 37 40 31.1797 40 24V13C40 10.5147 42.0147 8.5 44.5 8.5Z" fill="currentColor"/><circle cx="61.5" cy="13" r="2.2" fill="#FFFFFF"/><path d="M72.2 10.2C73.8 8.4 76.5 8.2 78.3 9.8L84 14.8L89.7 9.8C91.5 8.2 94.2 8.4 95.8 10.2C97.4 12 97.2 14.7 95.4 16.3L89.7 21.3L95.4 26.3C97.2 27.9 97.4 30.6 95.8 32.4C94.2 34.2 91.5 34.4 89.7 32.8L84 27.8L78.3 32.8C76.5 34.4 73.8 34.2 72.2 32.4C70.6 30.6 70.8 27.9 72.6 26.3L78.3 21.3L72.6 16.3C70.8 14.7 70.6 12 72.2 10.2Z" fill="currentColor"/><circle cx="89.5" cy="31.5" r="2.2" fill="#FFFFFF"/></svg>`;
                    if (externalUrlHelper) externalUrlHelper.textContent = 'Paste your Mux HLS stream URL or Playback ID.';
                    if (fileInput) fileInput.value = '';
                    uploadData.video = null;
                    if (videoPreviewContainer) videoPreviewContainer.classList.add('hidden');
                } else if (source === 'none') {
                    uploadData.upload_destination = 'none';
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
                const externalUrlPlatformName = document.getElementById('externalUrlPlatformName');
                const externalUrlIcon = document.getElementById('externalUrlIcon');
                const externalUrlHelper = document.getElementById('externalUrlHelper');
                const fileInput = document.getElementById('fileInput');
                const videoPreviewContainer = document.getElementById('videoPreviewContainer');
                const localDropzoneBadge = document.getElementById('localDropzoneBadge');

                function updateVimeoSections() {
                    const checkedRadio = document.querySelector('input[name="vimeo_method"]:checked');
                    const selectedMethod = checkedRadio ? checkedRadio.value : 'url';

                    // Hide both sections first
                    if (localSection) localSection.classList.add('hidden');
                    if (externalSection) externalSection.classList.add('hidden');
                    if (localDropzoneBadge) localDropzoneBadge.classList.add('hidden');

                    if (selectedMethod === 'file') {
                        if (localSection) localSection.classList.remove('hidden');
                        if (localDropzoneBadge) localDropzoneBadge.classList.remove('hidden');
                        if (externalVideoUrl) externalVideoUrl.value = '';
                        uploadData.external_video_url = '';
                        uploadData.upload_destination = 'vimeo';
                    } else if (selectedMethod === 'url') {
                        if (externalSection) externalSection.classList.remove('hidden');
                        if (externalUrlPlatformName) externalUrlPlatformName.textContent = 'Vimeo Video';
                        if (externalVideoUrl) externalVideoUrl.placeholder = 'https://vimeo.com/123456789 or player.vimeo.com/video/...';
                        if (externalUrlIcon) externalUrlIcon.innerHTML = '<i class="fab fa-vimeo text-blue-600 text-lg"></i>';
                        if (externalUrlHelper) externalUrlHelper.textContent = 'Paste your existing Vimeo video link or embed URL.';
                        if (fileInput) fileInput.value = '';
                        uploadData.video = null;
                        uploadData.upload_destination = 'vimeo';
                        if (videoPreviewContainer) videoPreviewContainer.classList.add('hidden');
                    }
                }

                // Set initial state
                updateVimeoSections();

                // Add event listeners
                vimeoMethodRadios.forEach(radio => {
                    radio.removeEventListener('change', updateVimeoSections);
                    radio.addEventListener('change', updateVimeoSections);
                });
            }

            // Final submission with progress tracking
            async function submitWizard(status = 'published') {
                // Store status so uploadQuiz can use it
                window._quizUploadStatus = status;
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

                    // Ensure time and difficulty are captured accurately right before submit
                    const quizDifficulty = document.getElementById('quiz_difficulty');
                    if (quizDifficulty) uploadData.quiz.difficulty_level = quizDifficulty.value;
                    const quizTimeLimit = document.getElementById('quiz_time_limit');
                    if (quizTimeLimit) uploadData.quiz.time_limit_minutes = parseInt(quizTimeLimit.value) || 15;

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
                    const isDraftSave = window._quizUploadStatus === 'draft';
                    if (finalData.quiz.questions.length > 0 || isDraftSave) {
                        const quizLabel = isDraftSave ? 'Saving quiz draft...' : 'Uploading quiz...';
                        updateOverallProgress(overallProgress + 10, quizLabel);
                        document.getElementById('quizProgressSection').classList.remove('hidden');

                        const quizResult = await uploadQuiz(finalData);
                        if (quizResult.success) {
                            const successMsg = isDraftSave ? 'Quiz saved as draft' : 'Quiz uploaded successfully';
                            updateProgress('quiz', 100, successMsg);
                            overallProgress += 30;
                            updateOverallProgress(100, isDraftSave ? 'Draft saved successfully!' : 'All uploads completed successfully!');
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
                        localStorage.removeItem('digilearn_upload_draft'); // Clear autosaved draft on success
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

                const roundedPct = Math.min(100, Math.max(0, Math.round(Number(percentage) || 0)));

                if (progressBar) {
                    progressBar.style.width = `${roundedPct}%`;
                    if (isError) {
                        progressBar.className = 'bg-gradient-to-r from-red-500 to-rose-600 h-full rounded-full transition-all duration-300 shadow-sm';
                    }
                }
                if (progressText) progressText.textContent = `${roundedPct}%`;
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

                        const response = await new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', '{{ route("admin.contents.upload.video") }}', true);
                            xhr.setRequestHeader('Accept', 'application/json');
                            
                            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                            if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));

                            xhr.upload.onprogress = function(e) {
                                if (e.lengthComputable && videoFile) {
                                    const elapsedSeconds = (Date.now() - startTime) / 1000;
                                    const uploadSpeed = elapsedSeconds > 0 ? e.loaded / elapsedSeconds : 0;
                                    const percent = 30 + (e.loaded / e.total) * 69; // 30% to 99%
                                    
                                    updateProgress('video', percent, 'Uploading video file...', false, {
                                        uploadedBytes: e.loaded,
                                        totalBytes: e.total,
                                        speed: uploadSpeed,
                                        timeRemaining: uploadSpeed > 0 ? (e.total - e.loaded) / uploadSpeed : 0
                                    });
                                }
                            };

                            xhr.onload = function() {
                                resolve({
                                    ok: xhr.status >= 200 && xhr.status < 300,
                                    status: xhr.status,
                                    json: async () => JSON.parse(xhr.responseText)
                                });
                            };

                            xhr.onerror = function() {
                                reject(new Error('Network error during upload'));
                            };

                            xhr.send(formData);
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

                    const documentStartTime = Date.now();
                    const response = await new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '{{ route("admin.contents.upload.documents") }}', true);
                        xhr.setRequestHeader('Accept', 'application/json');
                        
                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        if (tokenMeta) xhr.setRequestHeader('X-CSRF-TOKEN', tokenMeta.getAttribute('content'));

                        xhr.upload.onprogress = function(e) {
                            if (e.lengthComputable) {
                                const elapsedSeconds = (Date.now() - documentStartTime) / 1000;
                                const uploadSpeed = elapsedSeconds > 0 ? e.loaded / elapsedSeconds : 0;
                                const percent = 5 + (e.loaded / e.total) * 90; // 5% to 95%
                                
                                updateProgress('document', percent, 'Uploading documents...', false, {
                                    uploadedBytes: e.loaded,
                                    totalBytes: e.total,
                                    speed: uploadSpeed
                                });
                            }
                        };

                        xhr.onload = function() {
                            resolve({
                                ok: xhr.status >= 200 && xhr.status < 300,
                                status: xhr.status,
                                json: async () => JSON.parse(xhr.responseText)
                            });
                        };

                        xhr.onerror = function() {
                            reject(new Error('Network error during document upload'));
                        };

                        xhr.send(formData);
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
                    formData.append('quiz_title', finalData.quiz.quiz_title || '');
                    formData.append('difficulty_level', difficultyLevel);
                    formData.append('time_limit_minutes', timeLimitMinutes);
                    formData.append('shuffle_questions', finalData.quiz.shuffle_questions ? '1' : '0');
                    formData.append('status', window._quizUploadStatus || 'published');

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

                const roundedPct = Math.min(100, Math.max(0, Math.round(Number(percentage) || 0)));

                if (progressBar) progressBar.style.width = `${roundedPct}%`;
                if (progressText) progressText.textContent = `${roundedPct}%`;
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

            // --- AI Question Generation Logic ---
            window.openAiModal = function() {
                // Auto-detect Exam Type from selected categories
                const categoryCheckboxes = document.querySelectorAll('input[name="upload_category_ids[]"]:checked, input[name="category_ids[]"]:checked');
                let detectedType = 'normal';
                categoryCheckboxes.forEach(cb => {
                    const labelText = cb.nextElementSibling ? cb.nextElementSibling.textContent.toLowerCase() : '';
                    if (labelText.includes('bece')) detectedType = 'bece';
                    if (labelText.includes('wassce')) detectedType = 'wassce';
                });

                const examTypeSelect = document.getElementById('aiExamType');
                if (examTypeSelect) {
                    examTypeSelect.value = detectedType;
                    examTypeSelect.dispatchEvent(new Event('change')); // Triggers the year dropdown visibility
                }

                // Pre-fill the AI drawer with values from Step 1 / uploadData
                const mainTitle = document.getElementById('title')?.value?.trim() || uploadData.title || '';
                const mainSubject = document.getElementById('subject_id')?.value || uploadData.subject_id || '';
                const mainGrade = document.getElementById('grade_level')?.value || uploadData.grade_level || '';
                
                const aiTitle = document.getElementById('ai_title');
                if (aiTitle && mainTitle) aiTitle.value = mainTitle;
                
                const aiSubject = document.getElementById('ai_subject_id');
                if (aiSubject && mainSubject) aiSubject.value = mainSubject;
                
                const aiGrade = document.getElementById('ai_grade_level');
                if (aiGrade && mainGrade) aiGrade.value = mainGrade;

                document.getElementById('aiGenerateModal').classList.remove('hidden');
            }

            window.closeAiModal = function (e) {
                if (e && typeof e.stopPropagation === 'function') {
                    e.stopPropagation();
                }
                const aiModal = document.getElementById('aiGenerateModal');
                if (aiModal) {
                    aiModal.classList.add('hidden');
                }
            }

            window.handleAiGeneration = async function() {
                // Get data directly from the AI drawer fields
                const title = document.getElementById('ai_title')?.value?.trim() || '';
                const subjectSelect = document.getElementById('ai_subject_id');
                const subjectValue = subjectSelect ? subjectSelect.value : '';
                const gradeLevel = document.getElementById('ai_grade_level')?.value || '';

                let subjectName = '';
                if (subjectSelect && subjectSelect.selectedIndex > 0) {
                    subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;
                }

                // Get data from the AI modal settings
                const additionalContext = document.getElementById('aiTopic').value;
                const aiModel = document.getElementById('aiModelSelect') ? document.getElementById('aiModelSelect').value : 'gemini';
                const quizType = document.getElementById('aiQuizType').value;
                const count = document.getElementById('aiCount').value;
                const examType = document.getElementById('aiExamType').value;
                const examYear = document.getElementById('aiExamYear').value;
                const sourceMaterial = document.getElementById('aiSourceMaterial')?.value || '';

                if (!title || !subjectValue || !gradeLevel) {
                    alert('Please fill out the Title, Subject, and Grade Level in the AI Generator before proceeding.');
                    return;
                }

                // Sync back to the main wizard to ensure consistency
                const mainTitleInput = document.getElementById('title');
                if (mainTitleInput) mainTitleInput.value = title;
                uploadData.title = title;
                
                const mainSubjectInput = document.getElementById('subject_id');
                if (mainSubjectInput) mainSubjectInput.value = subjectValue;
                uploadData.subject_id = subjectValue;
                
                const mainGradeInput = document.getElementById('grade_level');
                if (mainGradeInput) mainGradeInput.value = gradeLevel;
                uploadData.grade_level = gradeLevel;
                
                // Update the visual grade picker in Step 1 if it exists
                const item = document.querySelector(`.grade-picker-item[data-grade="${gradeLevel}"]`);
                if (item && typeof selectGrade === 'function') {
                    selectGrade(item, gradeLevel);
                }

                // Auto-assemble the prompt topic
                let assembledTopic = '';
                if (examType === 'bece') {
                    assembledTopic = `Set BECE past questions for the subject "${subjectName}"`;
                    if (examYear) assembledTopic += ` for the year ${examYear}`;
                } else if (examType === 'wassce') {
                    assembledTopic = `Set WASSCE past questions for the subject "${subjectName}"`;
                    if (examYear) assembledTopic += ` for the year ${examYear}`;
                } else {
                    assembledTopic = `Set questions on the topic "${title}" for subject "${subjectName}" at the ${gradeLevel} level.`;
                }

                if (additionalContext) {
                    assembledTopic += ` Additional instructions: ${additionalContext}`;
                }

                const useKuulchat = (examType === 'bece' || examType === 'wassce');

                const btn = document.getElementById('aiGenerateSubmitBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
                btn.disabled = true;

                let progressInterval;
                const totalCount = parseInt(count, 10);
                if (totalCount > 10) {
                    const batches = Math.ceil(totalCount / 10);
                    const messages = [
                        "Generating...",
                        "Assembling knowledge...",
                        "Structuring questions...",
                        "This may take a moment...",
                        `Processing ${batches} batches...`,
                        "Reviewing generated content...",
                        "Almost there..."
                    ];
                    let msgIndex = 0;
                    progressInterval = setInterval(() => {
                        msgIndex = (msgIndex + 1) % messages.length;
                        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${messages[msgIndex]}`;
                    }, 4500); // Change message every 4.5 seconds
                }

                try {
                    const response = await fetch('{{ route("admin.contents.generate-ai-questions") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            topic: assembledTopic,
                            grade_level: gradeLevel || 'General',
                            quiz_type: quizType,
                            count: parseInt(count),
                            ai_model: aiModel,
                            use_kuulchat: useKuulchat,
                            use_kuulchat_year: useKuulchat ? (examYear || null) : null,
                            source_material: sourceMaterial
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.questions && data.questions.length > 0) {
                        // Replace random IDs with actual unique IDs
                        const processedQuestions = data.questions.map(q => {
                            q.id = Date.now() + Math.floor(Math.random() * 10000);
                            if (q.sub_questions) {
                                q.sub_questions = q.sub_questions.map(sq => {
                                    sq.id = Date.now() + Math.floor(Math.random() * 10000);
                                    if (sq.has_sub_parts && sq.sub_parts) {
                                        sq.sub_parts = sq.sub_parts.map(sp => {
                                            sp.id = Date.now() + Math.floor(Math.random() * 10000);
                                            return sp;
                                        });
                                    }
                                    return sq;
                                });
                            }
                            return q;
                        });

                        // Append to existing questions
                        uploadData.quiz.questions = [...uploadData.quiz.questions, ...processedQuestions];
                        
                        // Render each new question into the DOM
                        const questionsList = document.getElementById('questionsList');
                        if (questionsList) {
                            processedQuestions.forEach(q => {
                                const el = createQuestionElement(q);
                                questionsList.appendChild(el);
                            });
                            // Scroll to the first newly added question
                            const firstNewEl = questionsList.querySelector(`[data-question-id="${processedQuestions[0].id}"]`);
                            if (firstNewEl) {
                                firstNewEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                        
                        closeAiModal();
                        document.getElementById('aiTopic').value = ''; // Reset
                    } else {
                        alert(data.message || 'Failed to generate questions. Please try again.');
                    }
                } catch (error) {
                    console.error('AI Generation Error:', error);
                    alert('An error occurred during generation: ' + error.message + '\n\nPlease check the browser console for more details.');
                } finally {
                    clearInterval(progressInterval);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }

            // Global Quiz Sync Listener for external modals/drawers
            window.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'QUIZ_UPDATED') {
                    if (event.data.quizData && event.data.quizData.questions) {
                        uploadData.quiz.questions = event.data.quizData.questions;
                        // Re-render all questions in the DOM
                        const questionsList = document.getElementById('questionsList');
                        if (questionsList) {
                            questionsList.innerHTML = '';
                            uploadData.quiz.questions.forEach(q => {
                                const el = createQuestionElement(q);
                                questionsList.appendChild(el);
                            });
                        }
                    }
                }
            });
            
            const addAiBtn = document.getElementById('addAiBtn');
            if (addAiBtn) {
                addAiBtn.addEventListener('click', window.openAiModal);
            }

            // Toggle Exam Year dropdown and adjust question counts based on Exam Type
            const examTypeSelect = document.getElementById('aiExamType');
            const examYearSection = document.getElementById('aiExamYearSection');
            const quizTypeSelect = document.getElementById('aiQuizType');
            const aiSubjectSelect = document.getElementById('ai_subject_id');
            
            function adjustAiCount() {
                if (!examTypeSelect || !quizTypeSelect) return;
                
                const countInput = document.getElementById('aiCount');
                if (!countInput) return;
                
                const type = examTypeSelect.value;
                const qType = quizTypeSelect.value;
                const isExam = type === 'bece' || type === 'wassce';
                
                if (qType === 'essay') {
                    let essayCount = isExam ? 4 : 5;
                    
                    if (isExam && aiSubjectSelect && aiSubjectSelect.selectedIndex >= 0) {
                        const subj = aiSubjectSelect.options[aiSubjectSelect.selectedIndex].text.toLowerCase();
                        
                        if (type === 'bece') {
                            if (subj.includes('math')) essayCount = 5;
                            else if (subj.includes('science')) essayCount = 5;
                            else if (subj.includes('english')) essayCount = 3;
                            else if (subj.includes('social')) essayCount = 4;
                            else if (subj.includes('religious') || subj.includes('rme') || subj.includes('moral')) essayCount = 4;
                        } else if (type === 'wassce') {
                            if (subj.includes('core math')) essayCount = 13;
                            else if (subj.includes('english')) essayCount = 5;
                            else if (subj.includes('integrated science')) essayCount = 6;
                            else if (subj.includes('elective') || subj.includes('physics') || subj.includes('economics')) essayCount = 10;
                            else essayCount = 6;
                        }
                    }
                    countInput.value = essayCount;
                } else {
                    if (type === 'bece') {
                        countInput.value = 40;
                    } else if (type === 'wassce') {
                        countInput.value = 50;
                    } else {
                        const current = parseInt(countInput.value);
                        if ([40, 50, 4, 5, 3, 6, 13, 10].includes(current)) {
                            countInput.value = 5;
                        }
                    }
                }
            }

            if (examTypeSelect && examYearSection && quizTypeSelect) {
                examTypeSelect.addEventListener('change', () => {
                    const type = examTypeSelect.value;
                    const isExam = type === 'bece' || type === 'wassce';
                    examYearSection.classList.toggle('hidden', !isExam);
                    
                    if (!isExam) {
                        const yearSelect = document.getElementById('aiExamYear');
                        if (yearSelect) yearSelect.value = '';
                    }

                    adjustAiCount();
                });
                
                quizTypeSelect.addEventListener('change', adjustAiCount);
                if (aiSubjectSelect) aiSubjectSelect.addEventListener('change', adjustAiCount);
            }
        }

        // Robust document ready handling
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDigilearn);
        } else {
            initializeDigilearn();
        }
    </script>

    <!-- AI Generation Slide-over Drawer (Layered above all modal dialogs at z-[2100]) -->
    <div id="aiGenerateModal" class="hidden fixed inset-0 z-[2100]">
        <!-- Clickable Dark Backdrop to safely close AI drawer without affecting upload modal -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[2100] transition-opacity" onclick="closeAiModal(event)"></div>

        <!-- Drawer Panel -->
        <div class="fixed inset-y-0 right-0 z-[2110] w-full max-w-md bg-white shadow-2xl flex flex-col transform transition-transform duration-300 ease-in-out sm:rounded-l-2xl border-l border-gray-200">
            
            <!-- Drawer Header -->
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white flex justify-between items-center sm:rounded-tl-2xl">
                <h3 class="text-xl font-bold text-gray-900 flex items-center tracking-tight">
                    <div class="bg-white shadow-sm p-2 rounded-xl mr-3 text-purple-600 border border-purple-100">
                        <i class="fas fa-magic"></i>
                    </div>
                    Generate with AI
                </h3>
                <button type="button" onclick="closeAiModal(event)"
                    class="text-gray-400 hover:text-gray-700 transition-colors bg-white rounded-full p-2 hover:bg-gray-100 shadow-sm border border-transparent hover:border-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="p-6 flex-1 overflow-y-auto space-y-5 custom-scrollbar">
                
                <!-- Integrated Lesson Details -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 space-y-4">
                    <h4 class="text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-book-open mr-2 text-purple-500"></i> Lesson Details
                    </h4>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Lesson Title <span class="text-red-500">*</span></label>
                        <input type="text" id="ai_title" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500" placeholder="e.g. Introduction to Algebra">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                            <select id="ai_subject_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Select Subject</option>
                                @if(isset($subjects) && $subjects->count())
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Grade Level <span class="text-red-500">*</span></label>
                            <select id="ai_grade_level" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Select Grade</option>
                                @if(isset($levelGroups))
                                    @foreach($levelGroups as $group)
                                        <optgroup label="{{ strtoupper($group->name) }}">
                                            @foreach($group->levels as $level)
                                                <option value="{{ $level->title }}">{{ $level->title }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Context (Optional)</label>
                    <textarea id="aiTopic" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="e.g. Focus specifically on balancing chemical equations..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question Type</label>
                        <select id="aiQuizType" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="mcq">Multiple Choice</option>
                            <option value="essay">Essay</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Questions</label>
                        <input type="number" id="aiCount" value="5" min="1" max="50"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="mt-4 p-3 bg-purple-50 rounded-lg border border-purple-100">
                    <label class="block text-sm font-medium text-purple-900 mb-1">Question Source / Exam Type</label>
                    <select id="aiExamType" class="w-full px-3 py-2 border border-purple-200 rounded-md bg-white focus:ring-purple-500 focus:border-purple-500 text-purple-800">
                        <option value="normal">Normal Lesson Quiz</option>
                        <option value="bece">BECE Past Questions (Kuulchat)</option>
                        <option value="wassce">WASSCE Past Questions (Kuulchat)</option>
                    </select>
                    <p class="text-xs text-purple-700 mt-1">Select BECE or WASSCE to retrieve exact verified past questions.</p>
                </div>

                <div id="aiExamYearSection" class="hidden mt-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                    <label class="block text-sm font-medium text-purple-900 mb-1">Exam Year (Optional)</label>
                    <select id="aiExamYear"
                        class="w-full px-3 py-2 border border-purple-200 rounded-md bg-white focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Any Year</option>
                        @for($y = 2026; $y >= 2000; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                    <p class="text-xs text-purple-600 mt-1">Select a year to retrieve a full paper from that specific exam session.</p>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex justify-between">
                        <span>Raw Source Material (Optional)</span>
                        <span class="text-xs text-purple-600 font-semibold bg-purple-100 px-2 py-0.5 rounded">Bypasses API Quota</span>
                    </label>
                    <textarea id="aiSourceMaterial" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 font-mono text-xs" placeholder="Paste raw text from Kuulchat or a PDF here. The AI will extract and cleanly format exactly these questions."></textarea>
                    <p class="text-xs text-gray-500 mt-1">If provided, the AI will ONLY format the questions pasted above and will NOT generate new ones.</p>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-6 border-t border-gray-100 bg-gray-50 flex flex-col gap-4 sm:rounded-bl-2xl shrink-0">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">AI Model Engine <span class="text-gray-500 font-normal ml-1">(Used for Generation)</span></label>
                    <select id="aiModelSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 bg-white text-sm">
                        <option value="gemini" selected>Google Gemini (Standard)</option>
                        <option value="gpt-4o-mini">OpenAI GPT-4o-mini (Fast & Reliable)</option>
                        <option value="gpt-4o">OpenAI GPT-4o (High Precision)</option>
                    </select>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                    <button type="button" onclick="closeAiModal(event)"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-xl transition-all shadow-sm font-medium">
                        Cancel
                    </button>
                    <button type="button" id="aiGenerateSubmitBtn" onclick="handleAiGeneration()"
                        class="w-full sm:w-auto px-5 py-2.5 bg-purple-600 text-white hover:bg-purple-700 hover:shadow-md hover:-translate-y-0.5 transform rounded-xl transition-all flex items-center justify-center font-medium shadow-sm">
                        <i class="fas fa-magic mr-2"></i> Generate Questions
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection