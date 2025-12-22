@extends('layouts.dashboard-components')

@section('title', (isset($course) ? $course->title : ($lesson['title'] ?? 'Lesson')) . ' - ' . config('app.name', 'ShoutOutGh'))

@section('head')
    <!-- Quill.js Rich Text Editor (Using multiple CDN fallbacks) -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Fallback if primary CDN fails
        if (typeof Quill === 'undefined') {
            console.log('Loading Quill fallback from Cloudflare');
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js';
            script.onload = () => console.log('Quill fallback loaded successfully');
            script.onerror = () => console.error('All Quill CDNs failed');
            document.head.appendChild(script);
        }
    </script>

    <!-- Additional Libraries for Enhanced Functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endsection

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --white: #ffffff;
            --gray-25: #fcfcfd;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        /* Enhanced sticky video styles with scroll-triggered transitions */
        .sticky-video-section {
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced Filter Bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0.5rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .back-button {
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .back-button:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        /* Made search box fully responsive with flexible sizing */
        .search-box {
            position: relative;
            flex: 1;
            max-width: 500px;
            min-width: 0; /* changed from 300px to allow fully flexibility */
            width: 100%;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            z-index: 1;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 0.875rem;
            background-color: var(--gray-50);
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-red);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(225, 30, 45, 0.1);
        }

        .filter-dropdown {
            position: relative;
        }

        .dropdown-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            min-width: 120px;
            transition: all 0.2s ease;
        }

        .dropdown-button:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        .dropdown-chevron {
            width: 16px;
            height: 16px;
            color: var(--gray-500);
        }

        .filter-button {
            padding: 0.875rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .filter-button:hover {
            border-color: var(--primary-red);
            color: var(--primary-red);
        }

        .filter-button.active {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        /* Enhanced Main Layout - YouTube-like */
        .lesson-page {
            display: grid;
            grid-template-columns: 1fr minmax(300px, 400px);
            gap: 1rem;
            padding: 1rem 1.5rem;
            max-width: 100%;
            margin: 0;
            overflow-x: hidden;
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lesson-main {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-width: 0;
        }

        .lesson-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            min-width: 0;
            max-width: 400px;
            position: relative;
            z-index: 0;
        }

        /* Prevent unwanted dark backgrounds on hover */
        .lesson-page-content:hover,
        .lesson-page:hover,
        .left-content:hover,
        .lesson-sidebar:hover {
            background-color: transparent !important;
        }

        /* Enhanced Left Content */
        .left-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-width: 0; /* Prevents Overflow */
        }

        /* Enhanced Video Container with smooth transitions */
        .video-container {
            position: relative;
            aspect-ratio: 16/9;
            background-color: #000;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            max-width: 100%;
        }

        .video-container:hover {
            box-shadow: var(--shadow-xl);
        }

        .video-player {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            padding-bottom: 0 !important;
        }

        /* Enhanced Lesson Info Card with smooth transitions */
        .lesson-info-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lesson-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            line-height: 1.3;
            letter-spacing: -0.025em;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            word-wrap: break-word;
        }

        .lesson-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lesson-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .lesson-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-btn-primary {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-blue-hover));
            color: var(--white);
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .action-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-btn-secondary {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background-color: var(--white);
            color: var(--gray-700);
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn-secondary:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        /* Enhanced Comments Section */
        .comments-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .comments-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .comments-header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .comments-count {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .comments-dropdown {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-600);
            padding: 0.25rem;
        }

        .comments-dropdown .dropdown-icon {
            transition: transform 0.3s ease;
        }

        .comments-dropdown.open .dropdown-icon {
            transform: rotate(180deg);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .header-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .save-header-btn {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
        }

        .save-header-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .share-header-btn {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
        }

        .share-header-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .comment-input-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: flex-start;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            flex-shrink: 0;
            font-size: 0.875rem;
            box-shadow: var(--shadow-sm);
        }

        .comment-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            resize: none;
            min-height: 40px;
            font-family: inherit;
            transition: all 0.2s ease;
            background-color: var(--gray-50);
            color: var(--gray-500);
        }

        .comment-input:focus {
            outline: none;
            border-color: var(--primary-red);
            background-color: var(--white);
            color: var(--gray-900);
        }

        .comment-submit-btn {
            display: none;
        }

        /* Comments List - Updated */
        .comment {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 0;
            background-color: transparent;
            border: none;
            border-radius: 0;
        }

        .comment-content {
            flex: 1;
            min-width: 0; /* Prevents Overflow */
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }

        .comment-author {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
        }

        .comment-time {
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .comment-text {
            color: var(--gray-700);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
            word-wrap: break-word;
        }

        .comment-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .comment-action {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0.25rem 0;
            transition: all 0.2s ease;
        }

        .comment-action:hover {
            color: var(--gray-700);
        }

        .comment-like-count {
            margin-left: 0.25rem;
        }

        /* Enhanced Right Sidebar */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            min-width: 0; /* changed from 300px to prevent overflow */
            max-width: 400px;
        }

        /* Enhanced Action Buttons - Updated to match screenshot */
        .action-buttons-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border: 2px solid var(--secondary-blue);
            color: var(--gray-600);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            min-width: 0; /* Prevents overflow */
        }

        .action-btn:hover {
            border-color: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Document availability indicator */
        .document-indicator {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 16px;
            height: 16px;
            background-color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .action-btn {
            position: relative;
        }

        /* Enhanced Notes Section - Updated to match screenshot */
        .notes-section {
            background-color: var(--gray-100);
            border-radius: 1rem;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .add-notes-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: 2px solid var(--secondary-blue);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .add-notes-btn:hover {
            background-color: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .add-notes-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Active state for notes button */
        .add-notes-btn.active {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
            color: var(--white);
        }

        .add-notes-btn.active:hover {
            background-color: var(--primary-red-hover);
            border-color: var(--primary-red-hover);
        }

        /* Transition for button state changes */
        .add-notes-btn {
            transition: all 0.3s ease;
        }

        /* Enhanced Rich Text Editor - Modern Implementation */
        .notes-editor-section {
            background-color: var(--white);
            border: 3px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative;
            z-index: 1;
        }

        .notes-editor-section.hidden {
            display: none !important;
        }

        .notes-editor-container {
            display: flex;
            flex-direction: column;
            min-height: 500px;
        }

        .notes-editor-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background-color: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        .notes-header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .notes-character-count {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .notes-character-count.warning {
            color: #f59e0b;
        }

        .notes-character-count.error {
            color: #ef4444;
        }

        .notes-title-input {
            flex: 1;
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            background-color: var(--white);
            color: var(--gray-900);
            transition: all 0.2s ease;
        }

        .notes-title-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        .notes-title-input::placeholder {
            color: var(--gray-500);
            font-weight: 500;
        }

        .notes-editor-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
            width: 100%;
        }

        .notes-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .notes-action-btn:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
        }

        .notes-action-btn.save {
            color: var(--gray-400);
            border-color: var(--secondary-blue);
        }

        .notes-action-btn.save:hover {
            background-color: var(--gray-200);
            color: var(--secondary-blue);
        }

        .notes-action-btn.delete {
            color: var(--gray-400);
            border-color: var(--primary-red);
        }

        .notes-action-btn.delete:hover {
            background-color: var(--gray-200);
            color: var(--primary-red);
        }

        .notes-action-btn.export {
            color: var(--gray-400);
            border-color: #10b981;
        }

        .notes-action-btn.export:hover {
            background-color:var(--gray-200);
            color: #10b981;
        }

        .notes-action-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Quill Editor Customization */
        .notes-editor-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        #notesQuillEditor {
            flex: 1;
            min-height: 400px;
            display: block !important;
            visibility: visible !important;
        }

        /* Ensure Quill elements are visible */
        .ql-toolbar {
            display: block !important;
            visibility: visible !important;
        }

        .ql-container {
            display: block !important;
            visibility: visible !important;
        }

        .ql-editor {
            display: block !important;
            visibility: visible !important;
        }

        /* Consolidated Quill styles */
        .ql-toolbar {
            border: none !important;
            border-bottom: 1px solid var(--gray-200) !important;
            background-color: var(--gray-50) !important;
            padding: 1rem 1.25rem !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Fix Quill Editor Container */
        #notesWrapper {
            background-color: var(--white);
            border: 3px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            display: none; /* Hidden by default */
        }

        #notesWrapper.active {
            display: block;
        }

        #notes-editor-container {
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }

        /* Ensure Quill toolbar doesn't overlap */
        .ql-toolbar {
            position: relative !important;
            z-index: 100 !important;
            background: var(--white) !important;
            border: 1px solid var(--gray-200) !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 0.5rem !important;
        }

        .ql-container {
            position: relative !important;
            z-index: 50 !important;
            min-height: 250px !important;
            max-height: 400px !important;
            overflow-y: auto !important;
            border: 1px solid var(--gray-200) !important;
            border-top: none !important;
            border-radius: 0 0 0.5rem 0.5rem !important;
        }

        .ql-editor {
            min-height: 250px !important;
        }

        /* Fix Save Button positioning */
        #saveNotesBtn {
            margin: 1rem auto;
            display: block;
            padding: 0.75rem 2rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #saveNotesBtn:hover {
            background-color: var(--secondary-blue-hover);
        }

        /* Ensure Related Videos Card has proper spacing */
        .related-videos-card {
            margin-top: 1.5rem !important;
            position: relative;
            z-index: 10;
        }

        .ql-container {
            border: none !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
            font-size: 0.9375rem !important;
            min-height: 400px !important;
            background-color: var(--white) !important;
            line-height: 1.6 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            min-height: 150px;
        }

        .ql-editor {
            padding: 1.5rem !important;
            min-height: 400px !important;
            line-height: 1.7 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .ql-editor.ql-blank::before {
            color: var(--gray-500) !important;
            font-style: normal !important;
            left: 1.5rem !important;
            opacity: 1 !important;
        }

        .ql-stroke {
            stroke: var(--gray-600) !important;
        }

        .ql-fill {
            fill: var(--gray-600) !important;
        }

        .ql-picker-label {
            color: var(--gray-700) !important;
        }

        .ql-toolbar button {
            border-radius: 0.375rem !important;
            margin: 0 0.125rem;
            transition: all 0.2s ease !important;
            height: auto;
        }

        .ql-toolbar button:hover {
            background-color: var(--gray-200) !important;
            color: var(--white) !important;
        }

        .ql-toolbar button.ql-active {
            background-color: var(--gray-400) !important;
            color: var(--white) !important;
        }

        .ql-toolbar .ql-picker {
            border-radius: 0.375rem !important;
        }

        .ql-toolbar .ql-picker-label {
            border-radius: 0.375rem !important;
            transition: all 0.2s ease !important;
        }

        .ql-toolbar .ql-picker-label:hover {
            background-color: var(--gray-200) !important;
            color: var(--secondary-blue) !important;
        }

        /* Enhanced Related Videos */
        .related-videos-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .related-videos-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .video-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .video-item:hover {
            background-color: var(--gray-50);
        }

        .video-item:last-child {
            margin-bottom: 0;
        }

        .video-thumbnail {
            width: 168px;
            height: 94px;
            border-radius: 0.5rem;
            object-fit: cover;
            flex-shrink: 0;
            background-color: var(--gray-200);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 0; /* Prevents overflow */
        }

        .video-info-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-wrap: break-word;
        }

        .video-info-channel {
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .video-info-meta {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none; /* Prevents interferance with other elements */
        }

        /* Only show play overlay when hovering over video-item or video-thumbnail */
        .video-item:hover .play-overlay,
        .video-thumbnail:hover .play-overlay {
            opacity: 1;
        }

        .play-icon {
            width: 28px;
            height: 28px;
            color: var(--white);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .video-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }

        .video-title {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .video-menu {
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.5rem;
            align-self: flex-start;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .video-menu:hover {
            color: var(--gray-600);
            background-color: var(--gray-100);
        }

        .hidden {
            display: none;
        }

        /* Share Modal */
        .share-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .share-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .share-modal {
            transform: scale(0.9);
            background-color: var(--white);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            z-index: 2001;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .share-modal-overlay.active .share-modal {
            transform: scale(1);
        }

        .share-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .share-modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .share-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: var(--gray-500);
            transition: all 0.2s ease;
        }

        .share-modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .share-platforms {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .share-platform {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .share-platform:hover .share-platform-icon {
            transform: scale(1.1);
        }

        .share-platform-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .share-platform-icon:hover {
            box-shadow: var(--shadow-md);
        }

        .share-platform-icon.whatsapp {
            background-color: #25D366;
        }

        .share-platform-icon.facebook {
            background-color: #1877F2;
        }

        .share-platform-icon.email {
            background-color: var(--gray-400);
        }

        .share-platform-icon.instagram {
            background: linear-gradient(45deg, #F56040, #E1306C, #C13584, #833AB4);
        }

        .share-platform-icon.twitter {
            background-color: var(--white);
            color: #1DA1F2;
            border: 2px solid var(--gray-200);
        }

        .share-platform-name {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .share-url-container {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.75rem;
            border: 2px solid var(--gray-200);
        }

        .share-url-input {
            flex: 1;
            background: none;
            border: none;
            font-size: 0.875rem;
            color: var(--gray-700);
            min-width: 0;
        }

        .share-url-input:focus {
            outline: none;
        }

        .share-copy-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .share-copy-btn:hover {
            background-color: var(--primary-red-hover);
        }

        .share-copy-btn.copied {
            background-color: #10b981;
        }

        /* Loading and Success States */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background-color: #10b981;
            color: var(--white);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 1rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Course Content Styles */
        .course-content-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .course-header-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .course-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            line-height: 1.3;
            letter-spacing: -0.025em;
        }

        .course-description {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .course-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .course-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .course-stats {
            display: flex;
            gap: 2rem;
            justify-content: center;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-red);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .course-tabs {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
        }

        .tab-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .tab-btn:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
        }

        .tab-btn.active {
            color: var(--primary-red);
            background-color: rgba(225, 30, 45, 0.05);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--primary-red);
        }

        .tab-content {
            display: none;
            padding: 2rem;
            min-height: 400px;
        }

        .tab-content.active {
            display: block;
        }

        .course-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            background-color: var(--white);
        }

        .course-item:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--gray-300);
        }

        .item-thumbnail {
            position: relative;
            width: 200px;
            height: 112px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-icon {
            width: 64px;
            height: 64px;
            background-color: var(--gray-100);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            flex-shrink: 0;
        }

        .video-item .item-icon,
        .document-item .item-icon,
        .quiz-item .item-icon {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .document-item .item-icon {
            background-color: #10b981;
        }

        .quiz-item .item-icon {
            background-color: #7c3aed;
        }

        .item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .item-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .item-description {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .item-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .item-status.approved {
            color: #10b981;
            font-weight: 500;
        }

        .item-status.pending {
            color: #f59e0b;
            font-weight: 500;
        }

        .item-status.rejected {
            color: #ef4444;
            font-weight: 500;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .action-btn-small {
            padding: 0.5rem 1rem;
            border: 2px solid var(--secondary-blue);
            background-color: var(--white);
            color: var(--secondary-blue);
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn-small:hover {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .action-btn-small.primary {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .action-btn-small.primary:hover {
            background-color: var(--secondary-blue-hover);
        }

        .empty-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            text-align: center;
            color: var(--gray-500);
        }

        .empty-tab svg {
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-tab h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-tab p {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Collapsible Sidebar */
        .collapsible-sidebar {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .collapsible-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            background-color: var(--white);
            transition: all 0.2s ease;
        }

        .collapsible-header:hover {
            background-color: var(--gray-50);
        }

        .collapsible-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .collapsible-chevron {
            width: 20px;
            height: 20px;
            color: var(--gray-500);
            transition: transform 0.3s ease;
        }

        .collapsible-header.open .collapsible-chevron {
            transform: rotate(180deg);
        }

        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .collapsible-content.open {
            max-height: 2000px;
        }

        .collapsible-body {
            padding: 0 1.5rem 1.5rem;
        }       


        /* Enhanced Responsive Design */
        @media (max-width: 1200px) {
            .lesson-page {
                grid-template-columns: 1fr 350px;
                gap: 1.5rem;
            }
        }

        @media (max-width: 1024px) {
            .lesson-page {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .lesson-sidebar {
                order: 0; /* Reset to natural order */
                max-width: 100%;
            }

            /* Stack action buttons vertically */
            .action-buttons-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .comments-card{
                display: block;
                order: 2;
            }

            .notes-editor-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .notes-editor-actions {
                margin-left: 0;
                justify-content: center;
            }
        }

        /* FIXED: Mobile responsive with NATURAL BLOCK FLOW - No overlapping! */
        @media (max-width: 768px) {
            .lesson-page {
                display: flex;
                flex-direction: column;
                padding: 0.75rem;
                margin: 0;
                gap: 1.5rem;
                overflow: hidden;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                padding-top: 80px; /* Account for header height + some spacing */
            }
            
            .filter-bar {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .search-box {
                min-width: 0;
                max-width: 100%;
                flex: 1 1 auto;
            }

            .dropdown-button,
            .filter-button {
                padding: 0.75rem 1rem;
                font-size: 0.8125rem;
                min-width: auto;
            }
            
            /* FIXED: Natural sticky positioning - no overlapping */
            .sticky-video-section {
                position: relative;
                width: 100%;
                max-width: 100%;
            }

            /* FIXED: Simple compact state - video only */
            .sticky-video-section.compact {
                box-shadow: var(--shadow-xl);
            }

            .sticky-video-section.compact .video-container {
                aspect-ratio: 16/9;
                transform: scale(0.4);
                transform-origin: top left;
                border-radius: 0.5rem;
                margin-bottom: -60%;
            }

            /* FIXED: Hide lesson info completely in compact mode */
            .sticky-video-section.compact .lesson-info-card {
                display: none;
            }
            
            .lesson-title {
                font-size: 1.25rem;
                line-height: 1.4;
            }

            .lesson-info-card {
                padding: 1.25rem;
            }

            /* Adjust lesson actions */
            .lesson-actions {
                flex-direction: column !important;
                gap: 0.75rem;
            }

            .action-btn-primary, 
            .action-btn-secondary {
                width: 100%;
                justify-content: center;
                padding: 0.875rem 1.25rem;;
            }

            .action-buttons-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
                margin: 1rem 0 1.5rem;
            }

            .action-btn {
                flex-direction: column;
                padding: 0.75rem 0.5rem;
                font-size: 0.75rem;
                gap: 0.25rem;
            }

            .action-btn svg {
                width: 20px;
                height: 20px;
            }

            /* FIXED: Comments section follows naturally - no overlap! */
            .comments-card {
                display: block;
                padding: 1.25rem;
            }

            .comments-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .comment-input-container {
                gap: 0.75rem;
            }

            .comment-avatar {
                width: 36px;
                height: 36px;
                font-size: 0.8125rem;
            }

            .related-videos-card {
                padding: 1.25rem;
            }

            /* Related videos follow naturally */
            .related-videos-card {
                margin-top: 1rem;
            }

            /* Hide the right sidebar container */
            .lesson-sidebar {
                display: contents; /* Children become direct grid children */
                width: 100%;
                min-width: unset;
                max-width: 100%;
            }

            .collapsible-sidebar {
                width: 100%;
            }

            .video-thumbnail {
                width: 120px;
                height: 68px;
            }

            .video-info-title {
                font-size: 0.8125rem;
                -webkit-line-clamp: 3;
            }

            /* Video first */
            .video-container {
                border-radius: 0.5rem;
                width: 100%;
                max-width: 100%;
            }

            .share-modal {
                padding: 1.5rem;
                margin: 1rem;
                width: calc(100% - 2rem);
                max-width: 100%;
            }
            
            .share-platforms {
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
            }
            
            .share-platform-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .share-url-container {
                flex-direction: column;
                gap: 0.75rem;
                padding: 0.875rem;
            }
            
            .share-url-input {
                width: 100%;
                text-align: center;
            }
            .share-copy-btn {
                width: 100%;
                justify-content: center;
            }

            .notes-editor-section {
                border-width: 2px;
            }

            .notes-section {
                padding: 2rem 1rem;
            }

            .notes-editor-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .notes-action-btn {
                width: 100%;
                justify-content: center;
            }

            .notes-title-input {
                font-size: 0.9375rem;
            }

            .ql-container {
                min-height: 300px;
            }

            .ql-editor {
                padding: 1rem;
                min-height: 300px;
            }

            .ql-toolbar {
                padding: 0.75rem 1rem;
            }

            /* Fix Quill editor on mobile */
            #notesWrapper {
                margin: 1rem 0;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            #notes-editor-container {
                min-height: 250px;
            }

            .ql-container {
                min-height: 200px !important;
                max-height: 300px !important;
            }

            .ql-toolbar {
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            /* Ensure save button is visible */
            #saveNotesBtn {
                position: relative;
                z-index: 100;
                margin: 1rem auto;
                width: 90%;
            }

            /* Ensure related videos don't overlap */
            .related-videos-card {
                margin-top: 2rem !important;
                clear: both;
                position: relative;
                z-index: 5;
            }
        }

        @media (max-width: 480px) {
            .lesson-page {
                padding: 0.5rem;
            }

            .filter-bar {
                padding: 0.5rem;
            }

            .lesson-title {
                font-size: 1.125rem;
            }

            .lesson-info-card {
                padding: 1rem;
            }

            .lesson-meta {
                gap: 0.5rem;
            }

            .lesson-meta-item {
                padding: 0.375rem 0.75rem;
                font-size: 0.8125rem;
            }
            
            .lesson-actions {
                flex-direction: column;
            }

            .action-btn-primary,
            .action-btn-secondary {
                justify-content: center;
            }

            .comments-card {
                padding: 1rem;
            }

            .comment {
                padding: 0;
                margin-bottom: 1.25rem;
            }

            .comment-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.75rem;
            }


            .action-buttons-grid {
                gap: 0.375rem;
            }

            .action-btn {
                padding: 0.625rem 0.375rem;
                font-size: 0.6875rem;
            }

            .action-btn svg {
                width: 18px;
                height: 18px;
            }
            /* Adjust video item layout */
            .video-item {
                flex-direction: column;
                padding: 0.75rem;
            }

            .video-thumbnail {
                width: 100%;
                height: auto;
                aspect-ratio: 16/9;
            }

            .video-info-title {
                -webkit-line-clamp: 2;
            }

            .related-videos-card {
                padding: 1rem;
            }

            .share-platforms {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }

            .share-platform-icon {
                width: 45px;
                height: 45px;
                font-size: 1.125rem;
            }

            .notes-section {
                padding: 1.5rem 0.75rem;
            }

            /* FIXED: Smaller compact video for very small screens */
            .sticky-video-section.compact .video-container {
                transform: scale(0.35);
                margin-bottom: -65%;
            }
        }

        /* Landscape mobile orientation fix */
        @media (max-width: 900px) and (max-height: 500px) {
            .video-container {
                aspect-ratio: 21/9;
            }

            .lesson-info-card {
                padding: 1rem;
            }

            .lesson-title {
                font-size: 1.125rem;
            }
        }
    </style>

    <!-- Enhanced Filter Bar -->
    <div class="filter-bar">
        <button class="back-button" id="backButton">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        
        <div class="search-box">
            <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="search-input" placeholder="Search lessons, subjects, or topics...">
        </div>
        
        <div

        <div class="filter-dropdown">
            <button class="dropdown-button">
                <span>{{ ucwords(str_replace('-', ' ', $selectedLevel ?? 'Level')) }}</span>
                <svg class="dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        
        <div class="filter-dropdown">
            <button class="dropdown-button">
                <span>Subjects</span>
                <svg class="dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        
        <button class="filter-button">Question</button>
        <button class="filter-button">Quiz</button>
    </div>

    <!-- Enhanced Main Layout -->
    <div class="lesson-page">
        <!-- Main Content Area -->
        <main class="lesson-main">
            <!-- Enhanced Left Content -->
            <div class="left-content">
            @if(isset($course))
                <!-- Course Content with Tabs -->
                <div class="course-content-section">
                    <!-- Course Header -->
                    <div class="course-header-card">
                        <h1 class="course-title">{{ $course->title }}</h1>
                        <p class="course-description">{{ $course->description }}</p>

                        <div class="course-meta">
                            <div class="course-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ $course->subject }}
                            </div>
                            <div class="course-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $course->creator->name }}
                            </div>
                            <div class="course-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 9a2 2 0 002 2h6a2 2 0 002-2l-2-9"/>
                                </svg>
                                {{ $course->grade_level }}
                            </div>
                        </div>

                        <div class="course-stats">
                            <div class="stat-item">
                                <span class="stat-number">{{ $course->videos->count() }}</span>
                                <span class="stat-label">Videos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">{{ $course->documents->count() }}</span>
                                <span class="stat-label">Documents</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">{{ $course->quizzes->count() }}</span>
                                <span class="stat-label">Quizzes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Course Content Tabs -->
                    <div class="course-tabs">
                        <div class="tab-buttons">
                            <button class="tab-btn active" data-tab="videos">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Videos ({{ $course->videos->count() }})
                            </button>
                            <button class="tab-btn" data-tab="documents">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14,2 14,8 20,8"/>
                                </svg>
                                Documents ({{ $course->documents->count() }})
                            </button>
                            <button class="tab-btn" data-tab="quizzes">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Quizzes ({{ $course->quizzes->count() }})
                            </button>
                        </div>

                        <!-- Videos Tab -->
                        <div class="tab-content active" id="videos-tab">
                            @forelse($course->videos as $video)
                            <div class="course-item video-item">
                                <div class="item-thumbnail">
                                    <video class="item-video" muted preload="metadata" poster="{{ secure_asset($video->thumbnail_path) }}">
                                        <source src="{{ secure_asset($video->video_path) }}" type="video/mp4">
                                    </video>
                                    <div class="play-overlay">
                                        <div class="play-button">
                                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="item-duration">{{ $video->duration_seconds ? floor($video->duration_seconds / 60) . ':' . str_pad($video->duration_seconds % 60, 2, '0', STR_PAD_LEFT) : 'N/A' }}</div>
                                </div>
                                <div class="item-info">
                                    <h4 class="item-title">{{ $video->title }}</h4>
                                    <p class="item-description">{{ Str::limit($video->description, 100) }}</p>
                                    <div class="item-meta">
                                        <span class="item-views">{{ number_format($video->views ?? 0) }} views</span>
                                        <span class="item-status {{ $video->status }}">{{ ucfirst($video->status) }}</span>
                                    </div>
                                </aside>
                            </div>
                            @empty
                            <div class="empty-tab">
                                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <h3>No videos in this course</h3>
                                <p>Videos will be added to this course soon.</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-content" id="documents-tab">
                            @forelse($course->documents as $document)
                            <div class="course-item document-item">
                                <div class="item-icon">
                                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                    </svg>
                                </div>
                                <div class="item-info">
                                    <h4 class="item-title">{{ $document->title }}</h4>
                                    <p class="item-description">{{ Str::limit($document->description, 100) }}</p>
                                    <div class="item-meta">
                                        <span class="item-size">{{ $document->getFormattedFileSize() }}</span>
                                        <span class="item-type">{{ strtoupper($document->file_type ?? 'PDF') }}</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="{{ route('dashboard.lesson.document', ['lessonId' => $document->id, 'type' => 'pdf']) }}" class="action-btn-small">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 3v4a1 1 0 001 1h4"/>
                                            <path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                            <path d="M9 9h6"/>
                                            <path d="M9 13h6"/>
                                            <path d="M9 17h6"/>
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="empty-tab">
                                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14,2 14,8 20,8"/>
                                </svg>
                                <h3>No documents in this course</h3>
                                <p>Documents will be added to this course soon.</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- Quizzes Tab -->
                        <div class="tab-content" id="quizzes-tab">
                            @forelse($course->quizzes as $quiz)
                            <div class="course-item quiz-item">
                                <div class="item-icon">
                                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="item-info">
                                    <h4 class="item-title">{{ $quiz->title }}</h4>
                                    <p class="item-description">{{ Str::limit($quiz->description, 100) }}</p>
                                    <div class="item-meta">
                                        <span class="item-questions">{{ $quiz->questions_count ?? 0 }} questions</span>
                                        <span class="item-subject">{{ $quiz->subject }}</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="{{ route('quiz.take', ['quizId' => $quiz->id]) }}" class="action-btn-small primary">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14.23 12.004a2.236 2.236 0 0 0 .53-1.482 2.236 2.236 0 0 0-.53-1.482l-4.23-2.882a2.236 2.236 0 0 0-2.46 0L3.31 9.04a2.236 2.236 0 0 0 0 3.848l4.23 2.882a2.236 2.236 0 0 0 2.46 0l4.23-2.882z"/>
                                        </svg>
                                        Take Quiz
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="empty-tab">
                                <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3>No quizzes in this course</h3>
                                <p>Quizzes will be added to this course soon.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <!-- Sticky Video Section with scroll-triggered animations -->
                <div class="sticky-video-section" id="stickyVideoSection">
                    <!-- Enhanced Video Player -->
                    <div class="video-container">
                        @if(isset($lesson) && $lesson instanceof \App\Models\Video)
                             @php
                                 $videoUrl = $lesson->getVideoUrl();
                                 $embedHtml = $lesson->getEmbedHtml();
                                 \Log::info('Video Debug - Model Instance', [
                                     'lesson_id' => $lesson->id,
                                     'video_url' => $videoUrl,
                                     'embed_html_length' => strlen($embedHtml),
                                     'video_source' => $lesson->video_source,
                                     'status' => $lesson->status,
                                     'mux_playback_id' => $lesson->mux_playback_id ?? 'none',
                                     'vimeo_id' => $lesson->vimeo_id ?? 'none',
                                     'video_path' => $lesson->video_path,
                                     'temp_file_path' => $lesson->temp_file_path,
                                     'is_temp_expired' => $lesson->isTempExpired()
                                 ]);
                             @endphp
                             @if($embedHtml)
                                 <div id="video-debug-info" style="background: #f0f0f0; padding: 10px; margin-bottom: 10px; font-size: 12px; border: 1px solid #ccc;">
                                     <strong>Debug Info:</strong><br>
                                     Video ID: {{ $lesson->id }}<br>
                                     Source: {{ $lesson->video_source }}<br>
                                     Status: {{ $lesson->status }}<br>
                                     Embed HTML Length: {{ strlen($embedHtml) }}<br>
                                     Video URL: {{ $videoUrl }}
                                 </div>
                                 @if($lesson->video_source === 'youtube')
                                     <div class="video-player" style="width: 100%; height: 100%; position: relative; padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                                         {!! $embedHtml !!}
                                     </div>
                                 @elseif($lesson->video_source === 'vimeo')
                                     <div class="video-player" style="width: 100%; height: 100%; position: relative; padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                                         {!! $embedHtml !!}
                                     </div>
                                 @elseif($lesson->video_source === 'mux')
                                     {!! $embedHtml !!}
                                 @else
                                     <video controls class="video-player">
                                         <source src="{{ $videoUrl }}" type="video/mp4">
                                         Your browser does not support the video tag.
                                     </video>
                                 @endif
                             @else
                                 <div style="background: #ffebee; padding: 20px; border: 1px solid #f44336; border-radius: 5px;">
                                     <strong>Error:</strong> No embed HTML generated for this video.
                                     <br>Video Source: {{ $lesson->video_source }}
                                     <br>Status: {{ $lesson->status }}
                                 </div>
                             @endif
                         @else
                             @php
                                 $videoSrc = secure_asset($lesson['video_url'] ?? '');
                                 $posterSrc = secure_asset($lesson['thumbnail'] ?? '');
                                 \Log::info('Video Debug - Array Data', [
                                     'lesson_id' => $lesson['id'] ?? 'unknown',
                                     'video_url_raw' => $lesson['video_url'] ?? 'none',
                                     'thumbnail_raw' => $lesson['thumbnail'] ?? 'none',
                                     'video_src_secure' => $videoSrc,
                                     'poster_src_secure' => $posterSrc,
                                     'lesson_keys' => array_keys($lesson)
                                 ]);
                             @endphp
                             <!-- <div id="video-debug-info" style="background: #f0f0f0; padding: 10px; margin-bottom: 10px; font-size: 12px; border: 1px solid #ccc;">
                                 <strong>Debug Info (Array Data):</strong><br>
                                 Lesson ID: {{ $lesson['id'] ?? 'unknown' }}<br>
                                 Video URL Raw: {{ $lesson['video_url'] ?? 'none' }}<br>
                                 Video Src Secure: {{ $videoSrc }}<br>
                                 Poster Src Secure: {{ $posterSrc }}<br>
                                 Available Keys: {{ implode(', ', array_keys($lesson)) }}
                             </div> -->
                             @if(strpos($lesson['video_url'] ?? '', 'youtube.com/embed') !== false)
                                 <div class="video-player" style="width: 100%; height: 100%; position: relative; padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                                     <iframe src="{{ $lesson['video_url'] }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                                 </div>
                             @else
                                 <video controls class="video-player" poster="{{ $posterSrc }}">
                                     <source src="{{ $videoSrc }}" type="video/mp4">
                                     Your browser does not support the video tag.
                                 </video>
                             @endif
                         @endif
                    </div>

                    <!-- Enhanced Lesson Info -->
                    <div class="lesson-info-card">
                        <h1 class="lesson-title">{{ $lesson['title'] ?? 'Living and Non Living organism' }}</h1>

                        <div class="lesson-meta">
                            <div class="lesson-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ $lesson['subject'] ?? 'Science' }}
                            </div>
                            <div class="lesson-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $lesson['instructor'] ?? 'Prof. Aboagye' }}
                            </div>
                            <div class="lesson-meta-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-2 9a2 2 0 002 2h6a2 2 0 002-2l-2-9"/>
                                </svg>
                                {{ $lesson['year'] ?? '2022' }}
                            </div>
                        </div>

                        <div class="lesson-actions">
                            <button class="action-btn-primary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                                Save Lesson
                            </button>
                            <button class="action-btn-secondary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="18" cy="5" r="3"/>
                                    <circle cx="6" cy="12" r="3"/>
                                    <circle cx="18" cy="19" r="3"/>
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                                </svg>
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Enhanced Comments Section -->
            <div class="comments-card">
                <div class="comments-header">
                    <div class="comments-header-left">
                        <span class="comments-count">
                            <span id="commentsCount">0</span> Comments
                            <button class="comments-dropdown" id="commentsToggleBtn">
                                <svg class="dropdown-icon" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>

                <div class="comment-input-container">
                    <div class="comment-avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                    <input type="text" class="comment-input" id="commentInput" placeholder="Add a comment..." />
                    <button class="comment-submit-btn" id="commentSubmitBtn">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>

                <div class="comments-list" id="commentsList">
                    <div class="loading-comments" id="loadingComments">
                        <div class="loading-spinner"></div>
                        <span>Loading comments...</span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Enhanced Right Sidebar -->
        <aside class="lesson-sidebar">
            <!-- Enhanced Action Buttons -->
            <div class="action-buttons-grid">
                <button class="action-btn">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Test
                </button>
                <button class="action-btn action-navigate-btn" data-href="{{ route('dashboard.lesson.document', ['lessonId' => $lesson['id'], 'type' => 'pdf']) }}" data-document-type="pdf">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                    </svg>
                    Document
                    <div class="document-indicator" id="pdf-indicator" style="display: none;">
                        <i class="fas fa-check"></i>
                    </div>
                </button>
                <button class="action-btn action-navigate-btn" data-href="{{ route('dashboard.lesson.document', ['lessonId' => $lesson['id'], 'type' => 'ppt']) }}" data-document-type="ppt">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="9" x2="15" y2="9"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                    </svg>
                    PPT
                    <div class="document-indicator" id="ppt-indicator" style="display: none;">
                        <i class="fas fa-check"></i>
                    </div>
                </button>
            </div>

            <!-- Enhanced Notes Section -->
            <div class="notes-section">
                <button class="add-notes-btn" id="addNotesBtn">
                    Add notes
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </button>
            </div>

            <!-- Notes Wrapper (hidden by default) -->
            <div id="notesWrapper" style="display: none; margin-top: 1rem;">
                <!-- Editor Container -->
                <div id="notes-editor-container">
                    <!-- Quill will create its own toolbar inside this div -->
                    <div id="notes-editor"></div>
                </div>

                <!-- Save Button -->
                <button id="saveNotesBtn" class="btn btn-success mt-2">
                    Save Notes
                </button>
            </div>

            <!-- Enhanced Related Videos -->
            <div class="related-videos-card">
                <h3 class="related-videos-title">Related Lessons</h3>
                
                @if(isset($relatedLessons))
                    @foreach($relatedLessons as $relatedLesson)
                    <div class="video-item related-video-item" data-href="/dashboard/lesson/{{ $relatedLesson['id'] ?? '#' }}">
                        <div class="video-thumbnail">
                            <img src="{{ secure_asset($relatedLesson['thumbnail'] ?? '') }}" alt="{{ $relatedLesson['title'] ?? 'Lesson' }}" 
                                 onerror="this.src='/placeholder.svg?height=78&width=140'">
                            <div class="play-overlay">
                                <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="video-details">
                            <h4 class="video-title">{{ $relatedLesson['title'] ?? 'Living and non-living organisms' }}</h4>
                            <p class="video-meta">{{ $relatedLesson['instructor'] ?? 'Prof. Aboagye' }} • {{ $relatedLesson['year'] ?? '2022' }}</p>
                        </div>
                        <div class="video-menu">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="1"/>
                                <circle cx="19" cy="12" r="1"/>
                                <circle cx="5" cy="12" r="1"/>
                            </svg>
                        </div>
                    </div>
                    @endforeach
                @else
                    <!-- Sample related videos for demo -->
                    @for($i = 1; $i <= 8; $i++)
                    <div class="video-item">
                        <div class="video-thumbnail">
                            <img src="/placeholder.svg?height=78&width=140" alt="Related Lesson {{ $i }}">
                            <div class="play-overlay">
                                <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="video-details">
                            <h4 class="video-title">Living and non-living organisms</h4>
                            <p class="video-meta">Prof. Aboagye • 2022</p>
                        </div>
                        <div class="video-menu">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="1"/>
                                <circle cx="19" cy="12" r="1"/>
                                <circle cx="5" cy="12" r="1"/>
                            </svg>
                        </div>
                    </div>
                    @endfor
                @endif
            </div>
        </div>
    </div>
    </div>

    <!-- Share Modal -->
    <div class="share-modal-overlay" id="shareModalOverlay">
        <div class="share-modal">
            <div class="share-modal-header">
                <h3 class="share-modal-title">share</h3>
                <button class="share-modal-close" id="shareModalClose">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="share-platforms">
                <div class="share-platform" data-platform="whatsapp">
                    <div class="share-platform-icon whatsapp">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.346"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">WhatsApp</span>
                </div>
                
                <div class="share-platform" data-platform="facebook">
                    <div class="share-platform-icon facebook">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Facebook</span>
                </div>
                
                <div class="share-platform" data-platform="email">
                    <div class="share-platform-icon email">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Email</span>
                </div>
                
                <div class="share-platform" data-platform="instagram">
                    <div class="share-platform-icon instagram">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Instagram</span>
                </div>
                
                <div class="share-platform" data-platform="twitter">
                    <div class="share-platform-icon twitter">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </div>
                    <span class="share-platform-name">Twitter</span>
                </div>
            </div>
            
            <div class="share-url-container">
                <input type="text" class="share-url-input" id="shareUrlInput" readonly>
                <button class="share-copy-btn" id="shareCopyBtn">Copy</button>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Global variables for the rich text editor
        let notesQuill = null;

        // Global constants and functions for the rich text editor
        const MAX_CHARS = 1000;

        // Global function for character count updates
        function updateCharCount() {
            if (!quillEditor || !document.querySelector('.notes-character-count')) return;

            const text = quillEditor.getText();
            const charCount = text.length;
            const charCountElement = document.querySelector('.notes-character-count');

            charCountElement.textContent = `${charCount}/${MAX_CHARS}`;

            // Update styling based on character count
            charCountElement.classList.remove('warning', 'error');

            if (charCount > MAX_CHARS * 0.9) {
                charCountElement.classList.add('warning');
            }
            if (charCount > MAX_CHARS) {
                charCountElement.classList.add('error');
            }
        }

        // Scroll tracking variables for mobile video section
        let lastScrollY = 0;
        let isScrollingDown = false;
        let scrollThreshold = 100; // Pixels to scroll before triggering compact mode

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, checking Quill availability...');
            console.log('CSP Nonce available:', !!document.querySelector('meta[name="csp-nonce"]'));
            console.log('CSRF Token available:', !!document.querySelector('meta[name="csrf-token"]'));


            // Check if Quill scripts are present in DOM
            const quillScripts = document.querySelectorAll('script[src*="quill"]');
            console.log('Quill script tags found:', quillScripts.length);
            quillScripts.forEach((script, index) => {
                console.log(`Quill script ${index + 1}:`, script.src, 'loaded:', script.complete);
            });

            // Check if Quill is loaded before initializing
            if (typeof Quill === 'undefined') {
                console.warn('Quill.js not loaded yet, waiting...');

                // Try loading Quill manually as a test
                console.log('Attempting manual Quill load...');
                const manualScript = document.createElement('script');
                manualScript.src = 'https://cdn.quilljs.com/1.3.7/quill.min.js';
                manualScript.onload = () => {
                    console.log('Manual Quill load successful');
                    initializeAll();
                };
                manualScript.onerror = () => {
                    console.error('Manual Quill load failed');
                    alert('The notes editor failed to load. Please refresh the page.');
                };
                document.head.appendChild(manualScript);

                return;
            } else {
                console.log('Quill.js already loaded, version:', Quill.version || 'unknown');
                initializeAll();
            }
        });

        // Test basic Quill functionality (removed to prevent orphaned toolbars)

        function initializeAll() {
            // Initialize all functionality
            initializeFilters();
            initializeComments();
            initializeActionButtons();
            initializeNotesEditor();
            initializeShareModal();
            initializeSaveLesson();
            initializeSearch();
            initializeVideoItems();
            initializeKeyboardShortcuts();
            initializeNavigation();
            initializeCommentsToggle();
            initializeMobileVideoScroll();
            initializeCourseTabs();
            checkDocumentAvailability();
            initializeVideoProgressTracking();
        }

        // Enhanced mobile video scroll functionality
        function initializeMobileVideoScroll() {
            if (window.innerWidth <= 768) {
                const stickyVideoSection = document.getElementById('stickyVideoSection');
                
                window.addEventListener('scroll', function() {
                    const currentScrollY = window.scrollY;
                    isScrollingDown = currentScrollY > lastScrollY;
                    
                    // Add compact class when scrolling down past threshold
                    if (currentScrollY > scrollThreshold && isScrollingDown) {
                        stickyVideoSection.classList.add('compact');
                    } 
                    // Remove compact class when scrolling back to top
                    else if (currentScrollY <= scrollThreshold) {
                        stickyVideoSection.classList.remove('compact');
                    }
                    
                    lastScrollY = currentScrollY;
                }, { passive: true });
            }
            
            // Re-initialize on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    const stickyVideoSection = document.getElementById('stickyVideoSection');
                    stickyVideoSection.classList.remove('compact');
                }
            });
        }


        // Enhanced filter functionality
        function initializeFilters() {
            const filterButtons = document.querySelectorAll('.filter-button');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }

        // Enhanced comment interactions
        function initializeComments() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';
            const commentInput = document.getElementById('commentInput');
            const commentSubmitBtn = document.getElementById('commentSubmitBtn');

            // Load comments on page load
            loadComments();
    
            // Initialize real-time comment broadcasting
            initializeCommentBroadcasting();

            // Show submit button when typing
            if (commentInput) {
                commentInput.addEventListener('input', function() {
                    if (this.value.trim().length > 0) {
                        commentSubmitBtn.style.display = 'block';
                    } else {
                        commentSubmitBtn.style.display = 'none';
                    }
                });

                // Submit comment on Enter key
                commentInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        submitComment();
                    }
                });
            }

            // Submit comment button click
            if (commentSubmitBtn) {
                commentSubmitBtn.addEventListener('click', submitComment);
            }
        }

        // Load comments from server
        function loadComments() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';
            const commentsList = document.getElementById('commentsList');
            const loadingComments = document.getElementById('loadingComments');

            console.log('Loading comments for lesson ID:', lessonId);

            if (!lessonId) {
                console.error('No lesson ID available for comments');
                return;
            }

            // Check CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            console.log('CSRF token available:', !!csrfToken, 'Value:', csrfToken ? csrfToken.getAttribute('content').substring(0, 10) + '...' : 'none');

            const requestUrl = `/dashboard/lesson/${lessonId}/comments`;
            console.log('Making request to:', requestUrl);

            fetch(requestUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    console.log('Comments response status:', response.status);
                    console.log('Comments response headers:', Object.fromEntries(response.headers.entries()));
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Comments data received:', data);
                    if (data.success) {
                        renderComments(data.comments);
                        document.getElementById('commentsCount').textContent = data.total_count;
                        console.log('Comments loaded successfully, count:', data.total_count);
                    } else {
                        console.error('Comments API returned error:', data);
                        commentsList.innerHTML = '<div class="error-message">Failed to load comments: ' + (data.message || 'Unknown error') + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                    console.error('Error details:', {
                        message: error.message,
                        stack: error.stack,
                        lessonId: lessonId,
                        url: requestUrl
                    });
                    commentsList.innerHTML = '<div class="error-message">Failed to load comments: ' + error.message + '</div>';
                });
        }

        // Render comments in the UI
        function renderComments(comments) {
            const commentsList = document.getElementById('commentsList');

            if (comments.length === 0) {
                commentsList.innerHTML = '<div class="no-comments">No comments yet. Be the first to comment!</div>';
                return;
            }

            const commentsHtml = comments.map(comment => `
                <div class="comment" data-comment-id="${comment.id}">
                    <div class="comment-avatar">${comment.user.avatar_initial}</div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author">${comment.user.name}</span>
                            <span class="comment-time">${comment.time_ago}</span>
                        </div>
                        <p class="comment-text">${comment.content}</p>
                        <div class="comment-actions">
                            <button class="comment-action like-btn" data-action="like" data-comment-id="${comment.id}">
                                <i class="fas fa-thumbs-up"></i>
                                <span class="comment-like-count">${comment.likes_count}</span>
                            </button>
                            <button class="comment-action dislike-btn" data-action="dislike" data-comment-id="${comment.id}">
                                <i class="fas fa-thumbs-down"></i>
                                <span class="comment-dislike-count">${comment.dislikes_count}</span>
                            </button>
                            <button class="comment-action reply-btn" data-comment-id="${comment.id}">
                                Reply
                            </button>
                        </div>
                        ${comment.replies && comment.replies.length > 0 ? `
                            <div class="comment-replies">
                                ${comment.replies.map(reply => `
                                    <div class="comment reply" data-comment-id="${reply.id}">
                                        <div class="comment-avatar">${reply.user.avatar_initial}</div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <span class="comment-author">${reply.user.name}</span>
                                                <span class="comment-time">${reply.time_ago}</span>
                                            </div>
                                            <p class="comment-text">${reply.content}</p>
                                            <div class="comment-actions">
                                                <button class="comment-action like-btn" data-action="like" data-comment-id="${reply.id}">
                                                    <i class="fas fa-thumbs-up"></i>
                                                    <span class="comment-like-count">${reply.likes_count}</span>
                                                </button>
                                                <button class="comment-action dislike-btn" data-action="dislike" data-comment-id="${reply.id}">
                                                    <i class="fas fa-thumbs-down"></i>
                                                    <span class="comment-dislike-count">${reply.dislikes_count}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `).join('');

            commentsList.innerHTML = commentsHtml;

            // Add event listeners for comment actions
            attachCommentEventListeners();
        }

        // Submit a new comment
        function submitComment(parentId = null) {
            const commentInput = document.getElementById('commentInput');
            const commentText = commentInput.value.trim();
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!commentText || !lessonId) return;

            const submitBtn = document.getElementById('commentSubmitBtn');
            const originalContent = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML = '<div class="loading-spinner"></div>';
            submitBtn.disabled = true;

            fetch(`/dashboard/lesson/${lessonId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    comment: commentText,
                    parent_id: parentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentInput.value = '';
                    submitBtn.style.display = 'none';
                    loadComments(); // Reload comments to show the new one
                    showSuccessMessage('Comment posted successfully!');
                } else {
                    alert(data.message || 'Failed to post comment');
                }
            })
            .catch(error => {
                console.error('Error posting comment:', error);
                alert('Failed to post comment. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            });
        }

        // Attach event listeners to comment actions
        function attachCommentEventListeners() {
            // Like/dislike buttons
            document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentId = this.dataset.commentId;
                    const action = this.dataset.action;

                    fetch(`/dashboard/comment/${commentId}/like`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ action: action })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the counts
                            const likeCount = this.querySelector('.comment-like-count');
                            const dislikeCount = this.querySelector('.comment-dislike-count');

                            if (likeCount) likeCount.textContent = data.likes_count;
                            if (dislikeCount) dislikeCount.textContent = data.dislikes_count;

                            // Visual feedback
                            this.style.color = 'var(--primary-red)';
                            setTimeout(() => {
                                this.style.color = '';
                            }, 200);
                        }
                    })
                    .catch(error => console.error('Error updating comment:', error));
                });
            });

            // Reply buttons
            document.querySelectorAll('.reply-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentId = this.dataset.commentId;
                    const commentInput = document.getElementById('commentInput');

                    // Focus on input and set placeholder
                    commentInput.placeholder = 'Reply to this comment...';
                    commentInput.focus();

                    // Store parent comment ID for reply
                    commentInput.dataset.parentId = commentId;

                    // Update submit handler to include parent ID
                    const submitBtn = document.getElementById('commentSubmitBtn');
                    submitBtn.onclick = () => submitComment(commentId);
                });
            });
        }

        // Initialize real-time comment broadcasting
        function initializeCommentBroadcasting() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!lessonId || typeof Echo === 'undefined') {
                console.log('Broadcasting not available or lesson ID missing');
                return;
            }

            // Listen for new comments on this lesson
            Echo.channel(`lesson.${lessonId}`)
                .listen('.comment.created', (e) => {
                    console.log('New comment received:', e);

                    // Only add the comment if it's not from the current user
                    // (since they already see their own comment after posting)
                    const currentUserId = '{{ auth()->id() }}';
                    if (e.comment.user.id != currentUserId) {
                        // Add the new comment to the UI
                        addRealTimeComment(e.comment);

                        // Update comment count
                        const commentsCount = document.getElementById('commentsCount');
                        if (commentsCount) {
                            const currentCount = parseInt(commentsCount.textContent) || 0;
                            commentsCount.textContent = currentCount + 1;
                        }

                        // Show success notification
                        showSuccessMessage('New comment added!');
                    }
                });
        }

        // Add a real-time comment to the UI
        function addRealTimeComment(commentData) {
            const commentsList = document.getElementById('commentsList');

            // Remove "no comments" message if it exists
            const noCommentsMsg = commentsList.querySelector('.no-comments');
            if (noCommentsMsg) {
                noCommentsMsg.remove();
            }

            // Create new comment element
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
            commentElement.setAttribute('data-comment-id', commentData.id);

            commentElement.innerHTML = `
                <div class="comment-avatar">${commentData.user.avatar_initial}</div>
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="comment-author">${commentData.user.name}</span>
                        <span class="comment-time">${commentData.time_ago}</span>
                    </div>
                    <p class="comment-text">${commentData.content}</p>
                    <div class="comment-actions">
                        <button class="comment-action like-btn" data-action="like" data-comment-id="${commentData.id}">
                            <i class="fas fa-thumbs-up"></i>
                            <span class="comment-like-count">${commentData.likes_count}</span>
                        </button>
                        <button class="comment-action dislike-btn" data-action="dislike" data-comment-id="${commentData.id}">
                            <i class="fas fa-thumbs-down"></i>
                            <span class="comment-dislike-count">${commentData.dislikes_count}</span>
                        </button>
                        <button class="comment-action reply-btn" data-comment-id="${commentData.id}">
                            Reply
                        </button>
                    </div>
                </div>
            `;

            // Add to the top of the comments list
            const firstComment = commentsList.querySelector('.comment');
            if (firstComment) {
                commentsList.insertBefore(commentElement, firstComment);
            } else {
                commentsList.appendChild(commentElement);
            }

            // Re-attach event listeners for the new comment
            attachCommentEventListeners();
        }

        function initializeCommentsToggle() {
            const commentsToggleBtn = document.getElementById('commentsToggleBtn');
            const commentsList = document.getElementById('commentsList');

            function updateCommentsVisibility() {
                if (window.innerWidth <= 768) {
                    commentsList.classList.add('hidden');
                    commentsToggleBtn.classList.add('open');
                } else {
                    commentsList.classList.remove('hidden');
                    commentsToggleBtn.classList.remove('open');
                }
            }
            
            if (commentsToggleBtn && commentsList) {
                // Set initial state
                updateCommentsVisibility();

                // Update on resize
                window.addEventListener('resize', updateCommentsVisibility);

                commentsToggleBtn.addEventListener('click', function() {
                    this.classList.toggle('open');
                    commentsList.classList.toggle('hidden');
                });
            }
        }

        // Check document availability and show indicators
        function checkDocumentAvailability() {
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!lessonId) return;

            // Check PDF documents
            fetch(`/dashboard/lesson/${lessonId}/document/pdf`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const pdfIndicator = document.getElementById('pdf-indicator');
                if (data.exists && pdfIndicator) {
                    pdfIndicator.style.display = 'flex';
                }
            })
            .catch(error => console.error('Error checking PDF availability:', error));

            // Check PPT documents
            fetch(`/dashboard/lesson/${lessonId}/document/ppt`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const pptIndicator = document.getElementById('ppt-indicator');
                if (data.exists && pptIndicator) {
                    pptIndicator.style.display = 'flex';
                }
            })
            .catch(error => console.error('Error checking PPT availability:', error));
        }

        // Enhanced action button functionality
        function initializeActionButtons() {
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    console.log('Action clicked:', action);
                    
                    // Add loading state
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<div class="loading-spinner"></div>';
                    
                    setTimeout(() => {
                        this.innerHTML = originalContent;
                    }, 1000);
                    
                    switch(action) {
                        case 'Test':
                            // Handle test functionality
                            break;
                        case 'Document':
                            // Handle document functionality
                            break;
                        case 'PPT':
                            // Handle PPT functionality
                            break;
                    }
                });
            });

            // Navigation buttons - Check if documents exist before navigating
            document.querySelectorAll('.action-navigate-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.dataset.href;
                    const buttonText = this.textContent.trim().toLowerCase();

                    // Check if this is a document or PPT button
                    if (buttonText.includes('document') || buttonText.includes('ppt')) {
                        // Make AJAX call to check if documents exist
                        fetch(href, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error === 'level_required') {
                                // Level selection required, redirect to level selection
                                window.location.href = '/dashboard/level-selection';
                            } else if (data.exists) {
                                // Documents exist, proceed with navigation
                                window.location.href = href;
                            } else {
                                // No documents found, show message
                                showNoDocumentsMessage(buttonText.includes('ppt') ? 'PPT' : 'Document');
                            }
                        })
                        .catch(error => {
                            console.error('Error checking documents:', error);
                            // On error, show message instead of redirecting
                            showNoDocumentsMessage(buttonText.includes('ppt') ? 'PPT' : 'Document');
                        });
                    } else {
                        // For other buttons, proceed normally
                        window.location.href = href;
                    }
                });
            });
        }

        // Add to initializeVideoItems()
        function initializeVideoItems() {
            // Related video items
            document.querySelectorAll('.related-video-item').forEach(item => {
                item.addEventListener('click', function() {
                const url = this.dataset.href;
                // Visual feedback
                this.style.opacity = '0.7';
                this.style.transform = 'scale(0.98)';
                // Navigation
                setTimeout(() => window.location.href = url, 200);
                });
            });
        }

        // Add new function for back button
        function initializeNavigation() {
            document.getElementById('backButton').addEventListener('click', () => {
                history.back();
            });
        }

        // Enhanced Rich Text Editor with Quill.js
        function initializeNotesEditor() {
            const addNotesBtn = document.getElementById('addNotesBtn');
            const notesWrapper = document.getElementById('notesWrapper');
            const saveNotesBtn = document.getElementById('saveNotesBtn');
            let notesQuill = null;
            let isEditorOpen = false;

            // Update button appearance based on state
            function updateButtonState() {
                if (isEditorOpen) {
                    addNotesBtn.innerHTML = `
                        Close notes editor
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    `;
                    addNotesBtn.classList.add('active');
                } else {
                    addNotesBtn.innerHTML = `
                        Add notes
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    `;
                    addNotesBtn.classList.remove('active');
                }
            }

            // Initialize Quill editor
            function initNotesEditor() {
                if (notesQuill) {
                    // Editor already exists, just show it
                    notesWrapper.style.display = 'block';
                    return;
                }

                // Initialize Quill with a simple toolbar
                notesQuill = new Quill('#notes-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    },
                    placeholder: 'Write your notes here...',
                    bounds: '#notes-editor-container'
                });

                console.log('Quill editor initialized');
            }

            // Destroy Quill editor
            function destroyNotesEditor() {
                if (notesQuill) {
                    // Get the container element
                    const editorContainer = document.getElementById('notes-editor');
                    if (editorContainer) {
                        // Remove all child elements (Quill adds its own structure)
                        while (editorContainer.firstChild) {
                            editorContainer.removeChild(editorContainer.firstChild);
                        }
                        // Reset the container
                        editorContainer.innerHTML = '<div></div>';
                    }
                    notesQuill = null;
                }
            }

            // Toggle editor visibility
            function toggleNotesEditor() {
                isEditorOpen = !isEditorOpen;

                if (isEditorOpen) {
                    // Show editor
                    initNotesEditor();
                    notesWrapper.style.display = 'block';
                    // Focus on the editor after a small delay
                    setTimeout(() => {
                        if (notesQuill) {
                            notesQuill.focus();
                        }
                    }, 100);
                } else {
                    // Hide editor
                    notesWrapper.style.display = 'none';
                    // Don't destroy the editor, just hide it
                    // This preserves the content for next time
                }

                updateButtonState();
            }

            // Initialize button state
            updateButtonState();

            // Handle button click
            addNotesBtn.addEventListener('click', toggleNotesEditor);

            // Save notes handler
            saveNotesBtn.addEventListener('click', () => {
                if (!notesQuill) {
                    alert('Please open the notes editor first.');
                    return;
                }

                const content = notesQuill.root.innerHTML;
                const text = notesQuill.getText().trim();

                if (!text) {
                    alert('Please write some notes before saving.');
                    return;
                }

                // Show loading state
                const originalText = saveNotesBtn.textContent;
                saveNotesBtn.innerHTML = '<div class="loading-spinner"></div> Saving...';
                saveNotesBtn.disabled = true;

                fetch('/dashboard/lesson/{{ $lesson["id"] ?? "" }}/user-notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        title: '', // Optional title
                        content: content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage('Notes saved successfully!');
                        // Optionally hide the editor after saving
                        // toggleNotesEditor();
                    } else {
                        alert('Error saving notes: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error saving notes:', error);
                    alert('Failed to save notes. Please try again.');
                })
                .finally(() => {
                    // Restore button state
                    saveNotesBtn.textContent = originalText;
                    saveNotesBtn.disabled = false;
                });
            });

            // Clean up on page unload (optional)
            window.addEventListener('beforeunload', () => {
                if (notesQuill) {
                    // Save notes automatically before leaving?
                    // You could add auto-save functionality here
                }
            });
        }

        // Share Modal Functionality
        function initializeShareModal() {
            const shareModalOverlay = document.getElementById('shareModalOverlay');
            const shareModalClose = document.getElementById('shareModalClose');
            const shareUrlInput = document.getElementById('shareUrlInput');
            const shareCopyBtn = document.getElementById('shareCopyBtn');
            const shareButtons = document.querySelectorAll('.action-btn-secondary, .share-header-btn');

            function openShareModal() {
                shareModalOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Set the current page URL
                shareUrlInput.value = window.location.href;
            }

            function closeShareModal() {
                shareModalOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            // Open modal when share buttons are clicked
            shareButtons.forEach(button => {
                if (button.textContent.toLowerCase().includes('share')) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        openShareModal();
                    });
                }
            });

            // Close modal events
            shareModalClose.addEventListener('click', closeShareModal);
            shareModalOverlay.addEventListener('click', function(e) {
                if (e.target === shareModalOverlay) {
                    closeShareModal();
                }
            });

            // Keyboard close
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && shareModalOverlay.classList.contains('active')) {
                    closeShareModal();
                }
            });

            // Copy URL functionality
            shareCopyBtn.addEventListener('click', function() {
                shareUrlInput.select();
                shareUrlInput.setSelectionRange(0, 99999); // For mobile devices
                
                try {
                    document.execCommand('copy');
                    
                    // Show success feedback
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    this.classList.add('copied');
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy: ', err);
                }
            });

            // Social platform sharing
            const sharePlatforms = document.querySelectorAll('.share-platform');
            sharePlatforms.forEach(platform => {
                platform.addEventListener('click', function() {
                    const platformType = this.dataset.platform;
                    const url = encodeURIComponent(shareUrlInput.value);
                    const title = encodeURIComponent(document.title);
                    
                    let shareUrl = '';
                    
                    switch(platformType) {
                        case 'whatsapp':
                            shareUrl = `https://wa.me/?text=${title}%20${url}`;
                            break;
                        case 'facebook':
                            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                            break;
                        case 'twitter':
                            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                            break;
                        case 'email':
                            shareUrl = `mailto:?subject=${title}&body=${url}`;
                            break;
                        case 'instagram':
                            // Instagram doesn't support direct URL sharing, so we'll copy to clipboard
                            shareUrlInput.select();
                            document.execCommand('copy');
                            alert('Link copied! You can now paste it in Instagram.');
                            return;
                    }
                    
                    if (shareUrl) {
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                });
            });
        }

        // Enhanced save lesson functionality
        function initializeSaveLesson() {
            const saveButton = document.querySelector('.action-btn-primary');
            
            if (saveButton && saveButton.textContent.trim().includes('Save Lesson')) {
                // Check if lesson is already saved
                const lessonId = '{{ $lesson["id"] ?? "" }}';
                
                // Check saved status on page load
                fetch(`/dashboard/lesson/${lessonId}/check-saved`)
                    .then(response => response.json())
                    .then(data => {
                        updateSaveButton(saveButton, data.saved);
                    })
                    .catch(error => console.error('Error checking save status:', error));
                
                saveButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const isSaved = this.dataset.saved === 'true';
                    const originalContent = this.innerHTML;
                    
                    // Show loading state
                    this.innerHTML = '<div class="loading-spinner"></div> ' + (isSaved ? 'Removing...' : 'Saving...');
                    this.disabled = true;
                    
                    const url = isSaved ? 
                        `/dashboard/lesson/${lessonId}/unsave` : 
                        `/dashboard/lesson/${lessonId}/save`;
                    
                    const method = isSaved ? 'DELETE' : 'POST';
                    
                    const requestData = isSaved ? {} : {
                        lesson_title: '{{ $lesson["title"] ?? "" }}',
                        lesson_subject: '{{ $lesson["subject"] ?? "" }}',
                        lesson_instructor: '{{ $lesson["instructor"] ?? "" }}',
                        lesson_year: '{{ $lesson["year"] ?? "" }}',
                        lesson_duration: '{{ $lesson["duration"] ?? "" }}',
                        lesson_thumbnail: '{{ $lesson["thumbnail"] ?? "" }}',
                        lesson_video_url: '{{ $lesson["video_url"] ?? "" }}',
                        selected_level: '{{ $selectedLevel ?? "" }}'
                    };
                    
                    fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateSaveButton(this, data.saved);
                            showSuccessMessage(data.message);
                        } else {
                            this.innerHTML = originalContent;
                            this.disabled = false;
                            alert(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.innerHTML = originalContent;
                        this.disabled = false;
                        alert('Failed to save lesson. Please try again.');
                    });
                });
            }
        }

        function updateSaveButton(button, isSaved) {
            if (isSaved) {
                button.innerHTML = `
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    Saved
                `;
                button.style.backgroundColor = '#10b981';
                button.dataset.saved = 'true';
            } else {
                button.innerHTML = `
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    Save Lesson
                `;
                button.style.backgroundColor = 'var(--primary-red)';
                button.dataset.saved = 'false';
            }
            button.disabled = false;
        }

        // Enhanced search functionality
        function initializeSearch() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const query = this.value.trim();
                        if (query.length > 2) {
                            console.log('Searching for:', query);
                            // Implement search functionality here
                        }
                    }, 300);
                });
            }
        }

        // Keyboard shortcuts
        function initializeKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + S to save notes
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    if (!document.getElementById('notesEditorSection').classList.contains('hidden')) {
                        document.getElementById('saveNotesBtn').click();
                    }
                }
                
                // Ctrl/Cmd + E to export notes
                if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                    e.preventDefault();
                    if (!document.getElementById('notesEditorSection').classList.contains('hidden')) {
                        document.getElementById('exportNotesBtn').click();
                    }
                }
                
                // Ctrl/Cmd + N to toggle notes editor
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    document.getElementById('addNotesBtn').click();
                }
            });
        }

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                ${message}
            `;

            document.body.appendChild(successDiv);

            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        function showNoDocumentsMessage(type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'no-documents-message';
            messageDiv.innerHTML = `
                <i class="fas fa-info-circle"></i>
                No ${type.toLowerCase()} documents are currently attached to this video lesson.
            `;

            // Style the message
            messageDiv.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: var(--white);
                border: 2px solid var(--gray-200);
                border-radius: 1rem;
                padding: 2rem;
                box-shadow: var(--shadow-xl);
                z-index: 2000;
                max-width: 400px;
                text-align: center;
                font-size: 0.875rem;
                color: var(--gray-700);
                animation: slideIn 0.3s ease;
            `;

            // Add close button
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.style.cssText = `
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: none;
                border: none;
                cursor: pointer;
                color: var(--gray-500);
                font-size: 1.25rem;
            `;
            closeBtn.onclick = () => messageDiv.remove();

            messageDiv.appendChild(closeBtn);
            document.body.appendChild(messageDiv);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 5000);
        }

        // IndexedDB Notes Manager for offline-first functionality
        class NotesManager {
            constructor() {
                this.dbName = 'DigiLearnNotes';
                this.version = 1;
                this.db = null;
                this.lessonId = '{{ $lesson["id"] ?? "" }}';
                this.userId = '{{ auth()->id() }}';
                this.syncInterval = null;
                this.isOnline = navigator.onLine;
                this.pendingSyncs = new Set();
            }

            async init() {
                try {
                    this.db = await this.openDB();
                    this.setupOnlineOfflineListeners();
                    this.startPeriodicSync();
                    console.log('NotesManager initialized');
                } catch (error) {
                    console.error('Failed to initialize NotesManager:', error);
                    // Fallback to localStorage
                    this.useLocalStorage = true;
                }
            }

            async openDB() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(this.dbName, this.version);

                    request.onerror = () => {
                        console.warn('IndexedDB not available, falling back to localStorage');
                        this.useLocalStorage = true;
                        reject(new Error('IndexedDB not available'));
                    };

                    request.onsuccess = (event) => {
                        resolve(event.target.result);
                    };

                    request.onupgradeneeded = (event) => {
                        const db = event.target.result;

                        // Create notes store
                        if (!db.objectStoreNames.contains('notes')) {
                            const notesStore = db.createObjectStore('notes', { keyPath: 'id' });
                            notesStore.createIndex('lessonId', 'lessonId', { unique: false });
                            notesStore.createIndex('userId', 'userId', { unique: false });
                            notesStore.createIndex('lastModified', 'lastModified', { unique: false });
                        }

                        // Create sync queue store
                        if (!db.objectStoreNames.contains('syncQueue')) {
                            const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
                            syncStore.createIndex('lessonId', 'lessonId', { unique: false });
                            syncStore.createIndex('timestamp', 'timestamp', { unique: false });
                        }
                    };
                });
            }

            setupOnlineOfflineListeners() {
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    console.log('Back online - syncing notes');
                    this.syncWithServer();
                });

                window.addEventListener('offline', () => {
                    this.isOnline = false;
                    console.log('Gone offline - notes will be cached locally');
                });
            }

            startPeriodicSync() {
                // Sync every 30 seconds when online
                this.syncInterval = setInterval(() => {
                    if (this.isOnline && !this.useLocalStorage) {
                        this.syncWithServer();
                    }
                }, 30000);
            }

            async saveNoteLocally(noteData) {
                // Validate character limit
                const MAX_CHARS = 1000;
                const textContent = this.extractTextFromHTML(noteData.content || '');
                if (textContent.length > MAX_CHARS) {
                    throw new Error(`Note content exceeds ${MAX_CHARS} character limit. Current: ${textContent.length}`);
                }

                if (this.useLocalStorage) {
                    const key = `note_${this.lessonId}`;
                    localStorage.setItem(key, JSON.stringify({
                        ...noteData,
                        lastModified: Date.now(),
                        synced: false
                    }));
                    return;
                }

                const note = {
                    id: `${this.userId}_${this.lessonId}`,
                    userId: this.userId,
                    lessonId: this.lessonId,
                    title: noteData.title || '',
                    content: noteData.content || '',
                    lastModified: Date.now(),
                    synced: false,
                    version: Date.now() // For conflict resolution
                };

                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readwrite');
                    const store = transaction.objectStore('notes');
                    const request = store.put(note);

                    request.onsuccess = () => {
                        console.log('Note saved locally');
                        resolve(note);
                    };

                    request.onerror = () => {
                        console.error('Failed to save note locally');
                        reject(new Error('Failed to save locally'));
                    };
                });
            }

            extractTextFromHTML(html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                return tempDiv.textContent || tempDiv.innerText || '';
            }

            async loadNoteLocally() {
                if (this.useLocalStorage) {
                    const key = `note_${this.lessonId}`;
                    const data = localStorage.getItem(key);
                    return data ? JSON.parse(data) : null;
                }

                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readonly');
                    const store = transaction.objectStore('notes');
                    const request = store.get(`${this.userId}_${this.lessonId}`);

                    request.onsuccess = () => {
                        resolve(request.result || null);
                    };

                    request.onerror = () => {
                        reject(new Error('Failed to load note locally'));
                    };
                });
            }

            async syncWithServer() {
                if (!this.isOnline || this.useLocalStorage) return;

                try {
                    // Get all unsynced notes
                    const unsyncedNotes = await this.getUnsyncedNotes();

                    for (const note of unsyncedNotes) {
                        await this.syncNoteToServer(note);
                    }

                    // Load latest from server to check for conflicts
                    await this.loadFromServer();

                } catch (error) {
                    console.error('Sync failed:', error);
                }
            }

            async getUnsyncedNotes() {
                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readonly');
                    const store = transaction.objectStore('notes');
                    const index = store.index('lastModified');
                    const request = index.openCursor();
                    const unsynced = [];

                    request.onsuccess = (event) => {
                        const cursor = event.target.result;
                        if (cursor) {
                            if (!cursor.value.synced) {
                                unsynced.push(cursor.value);
                            }
                            cursor.continue();
                        } else {
                            resolve(unsynced);
                        }
                    };

                    request.onerror = () => reject(new Error('Failed to get unsynced notes'));
                });
            }

            async syncNoteToServer(note) {
                try {
                    const response = await fetch(`/dashboard/lesson/${this.lessonId}/user-notes`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            title: note.title,
                            content: note.content
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Mark as synced
                        await this.markNoteSynced(note.id);
                        console.log('Note synced to server');
                    }
                } catch (error) {
                    console.error('Failed to sync note:', error);
                    // Will retry on next sync
                }
            }

            async markNoteSynced(noteId) {
                return new Promise((resolve, reject) => {
                    const transaction = this.db.transaction(['notes'], 'readwrite');
                    const store = transaction.objectStore('notes');
                    const request = store.get(noteId);

                    request.onsuccess = () => {
                        const note = request.result;
                        if (note) {
                            note.synced = true;
                            const updateRequest = store.put(note);
                            updateRequest.onsuccess = () => resolve();
                            updateRequest.onerror = () => reject(new Error('Failed to mark synced'));
                        } else {
                            resolve();
                        }
                    };

                    request.onerror = () => reject(new Error('Failed to get note for sync'));
                });
            }

            async loadFromServer() {
                try {
                    const response = await fetch(`/dashboard/lesson/${this.lessonId}/user-notes`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.success && data.note) {
                        const serverNote = data.note;
                        const localNote = await this.loadNoteLocally();

                        // Check for conflicts
                        if (localNote && localNote.lastModified > new Date(serverNote.updated_at).getTime()) {
                            // Local is newer, keep local but mark for sync
                            console.log('Local note is newer, keeping local version');
                        } else {
                            // Server is newer or same, update local
                            await this.saveNoteLocally({
                                title: serverNote.title || '',
                                content: serverNote.content || '',
                                synced: true
                            });
                            console.log('Updated local note from server');
                        }
                    }
                } catch (error) {
                    console.error('Failed to load from server:', error);
                }
            }

            destroy() {
                if (this.syncInterval) {
                    clearInterval(this.syncInterval);
                }
                if (this.db) {
                    this.db.close();
                }
            }
        }

        // Course tabs functionality
        function initializeCourseTabs() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.dataset.tab;

                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Hide all tab contents
                    tabContents.forEach(content => content.classList.remove('active'));
                    // Show selected tab content
                    const targetContent = document.getElementById(tabName + '-tab');
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        }

        // Video progress tracking functionality
        let videoProgressTracker = null;

        // Initialize video progress tracking
        function initializeVideoProgressTracking() {
            // Look for iframe elements within video-player containers
            const videoElement = document.querySelector('.video-player iframe') ||
                                document.querySelector('.video-player video') ||
                                document.querySelector('iframe[src*="youtube.com"]') ||
                                document.querySelector('iframe[src*="vimeo.com"]');
            const lessonId = '{{ $lesson["id"] ?? "" }}';

            if (!videoElement || !lessonId) {
                console.log('Video element or lesson ID not found, skipping progress tracking');
                console.log('Available video elements:', document.querySelectorAll('iframe, video'));
                return;
            }

            console.log('Found video element:', videoElement);
            videoProgressTracker = new VideoProgressTracker(videoElement, lessonId);
            videoProgressTracker.init();
        }

        class VideoProgressTracker {
            constructor(videoElement, lessonId) {
                this.videoElement = videoElement;
                this.lessonId = lessonId;
                this.watchTime = 0;
                this.lastUpdateTime = Date.now();
                this.totalDuration = 0;
                this.progressInterval = null;
                this.isTracking = false;
                this.lastReportedProgress = 0;
                this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }

            init() {
                console.log('Initializing video progress tracking for lesson:', this.lessonId);

                // Handle different video types (HTML5 video vs iframe)
                if (this.videoElement.tagName === 'VIDEO') {
                    this.initHTML5Video();
                } else if (this.videoElement.tagName === 'IFRAME') {
                    this.initIframeVideo();
                }

                // Track page visibility to pause/resume tracking
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.pauseTracking();
                    } else {
                        this.resumeTracking();
                    }
                });

                // Track before page unload
                window.addEventListener('beforeunload', () => {
                    this.reportProgress(true);
                });
            }

            initHTML5Video() {
                console.log('Setting up HTML5 video tracking');

                this.videoElement.addEventListener('loadedmetadata', () => {
                    this.totalDuration = this.videoElement.duration;
                    console.log('Video duration loaded:', this.totalDuration);
                });

                this.videoElement.addEventListener('play', () => {
                    console.log('Video started playing');
                    this.startTracking();
                });

                this.videoElement.addEventListener('pause', () => {
                    console.log('Video paused');
                    this.pauseTracking();
                    this.reportProgress();
                });

                this.videoElement.addEventListener('ended', () => {
                    console.log('Video ended');
                    this.pauseTracking();
                    this.reportProgress(true);
                });

                // Periodic progress updates during playback
                this.videoElement.addEventListener('timeupdate', () => {
                    if (this.isTracking) {
                        const currentTime = Date.now();
                        const timeDiff = (currentTime - this.lastUpdateTime) / 1000; // Convert to seconds
                        this.watchTime += timeDiff;
                        this.lastUpdateTime = currentTime;

                        // Report progress every 10 seconds or when significant progress is made
                        const currentProgress = (this.videoElement.currentTime / this.totalDuration) * 100;
                        if (Math.abs(currentProgress - this.lastReportedProgress) >= 5 || this.watchTime >= 10) {
                            this.reportProgress();
                        }
                    }
                });
            }

            initIframeVideo() {
                console.log('Setting up iframe video tracking (limited functionality)');

                // For iframe videos (YouTube, Vimeo), we can only track basic events
                // More advanced tracking would require their APIs

                // Simulate progress tracking for iframe videos
                // This is a basic implementation - real iframe tracking needs platform-specific APIs
                let playStartTime = null;

                // Listen for iframe messages if available
                window.addEventListener('message', (event) => {
                    // Handle YouTube API messages if implemented
                    if (event.origin.includes('youtube.com') || event.origin.includes('vimeo.com')) {
                        try {
                            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;

                            if (data.event === 'onStateChange') {
                                if (data.info === 1) { // Playing
                                    this.startTracking();
                                } else if (data.info === 2 || data.info === 0) { // Paused or ended
                                    this.pauseTracking();
                                    this.reportProgress(data.info === 0); // true if ended
                                }
                            }
                        } catch (e) {
                            // Ignore parsing errors
                        }
                    }
                });

                // Fallback: Track based on visibility and time spent on page
                // Start tracking immediately for iframe videos since we can't detect play events
                this.startTracking();
                this.startPeriodicTracking();
            }

            startPeriodicTracking() {
                // For iframe videos without API access, track periodically
                this.progressInterval = setInterval(() => {
                    if (!document.hidden && this.isTracking) {
                        this.watchTime += 5; // Add 5 seconds every interval
                        this.reportProgress();
                    }
                }, 5000); // Every 5 seconds
            }

            startTracking() {
                if (!this.isTracking) {
                    this.isTracking = true;
                    this.lastUpdateTime = Date.now();
                    console.log('Started tracking video progress');
                }
            }

            pauseTracking() {
                this.isTracking = false;
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
            }

            resumeTracking() {
                if (!this.isTracking && !document.hidden) {
                    this.startTracking();
                }
            }

            async reportProgress(forceComplete = false) {
                if (this.watchTime < 1) return; // Don't report if less than 1 second watched

                try {
                    const progressData = {
                        watch_time: Math.floor(this.watchTime),
                        total_duration: Math.floor(this.totalDuration) || 300, // Default 5 minutes if unknown
                        lesson_data: {
                            id: this.lessonId,
                            title: '{{ $lesson["title"] ?? "Unknown Lesson" }}',
                            subject: '{{ $lesson["subject"] ?? "General" }}',
                            level: '{{ $selectedLevel ?? "primary-lower" }}',
                            level_group: '{{ $selectedLevel ?? "primary-lower" }}'
                        }
                    };

                    console.log('Reporting progress:', progressData);

                    const response = await fetch(`/dashboard/lesson/${this.lessonId}/progress`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(progressData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        console.log('Progress reported successfully:', result);
                        this.lastReportedProgress = result.completion_percentage || 0;
                        this.watchTime = 0; // Reset watch time after successful report

                        // Show completion message if lesson is fully completed
                        if (result.fully_completed) {
                            showSuccessMessage('Lesson completed! 🎉');
                        }
                    } else {
                        console.error('Failed to report progress:', result);
                    }
                } catch (error) {
                    console.error('Error reporting progress:', error);
                }
            }

            destroy() {
                this.pauseTracking();
                this.reportProgress(true); // Final report on destroy
            }
        }

        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endsection
