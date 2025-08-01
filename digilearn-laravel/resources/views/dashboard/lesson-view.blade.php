<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $lesson['title'] ?? 'Lesson' }} - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Quill.js Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <!-- Additional Libraries for Enhanced Functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <style>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-25);
            color: var(--gray-900);
            line-height: 1.6;
        }

        /* Top Header - Enhanced */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0.5rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 1001;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        /* Enhanced sticky video styles with scroll-triggered transitions */
        .sticky-video-section {
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hamburger-menu {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .hamburger-menu:hover {
            background-color: var(--gray-100);
        }

        .hamburger-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-700);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo img {
            height: 36px;
            width: auto;
        }

        .sidebar-brand {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-red);
            letter-spacing: -0.025em;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background-color: var(--gray-100);
        }

        .notification-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-600);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        /* Enhanced Collapsible Sidebar */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .collapsible-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 300px;
            height: 100vh;
            background-color: var(--white);
            box-shadow: var(--shadow-xl);
            z-index: 1001;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .collapsible-sidebar.active {
            transform: translateX(0);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background-color: var(--white);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sidebar-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .sidebar-close:hover {
            background-color: var(--gray-100);
        }

        .sidebar-content {
            padding: 1.5rem 0;
        }

        .sidebar-section {
            margin-bottom: 2rem;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            padding: 0.5rem 1.5rem;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
            border-left-color: var(--gray-300);
        }

        .sidebar-menu-item.active {
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            border-left-color: var(--primary-red);
            font-weight: 600;
        }

        .sidebar-menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-menu-text {
            font-size: 0.875rem;
            font-weight: 500;
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
        }

        .back-button:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 500px;
            min-width: 300px;
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
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            padding: 2rem 0.5rem;
            max-width: none;
            margin: 0;
        }

        /* Enhanced Left Content */
        .left-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Enhanced Video Container with smooth transitions */
        .video-container {
            aspect-ratio: 16/9;
            background-color: #000;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .video-container:hover {
            box-shadow: var(--shadow-xl);
        }

        .video-player {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        /* Comments List - Updated to match screenshot */
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
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
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
        }

        .comment-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
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
            width: 400px;
            min-width: 400px;
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
        }

        .action-btn:hover {
            border-color: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .action-btn svg {
            width: 18px;
            height: 18px;
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

        /* Enhanced Rich Text Editor - Modern Implementation */
        .notes-editor-section {
            background-color: var(--white);
            border: 3px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
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

        .notes-title-input {
            flex: 1;
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
            margin-left: 1rem;
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

        /* Quill Editor Customization */
        .notes-editor-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        #notesQuillEditor {
            flex: 1;
            min-height: 400px;
        }

        .ql-toolbar {
            border: none !important;
            border-bottom: 1px solid var(--gray-200) !important;
            background-color: var(--gray-50);
            padding: 1rem !important;
        }

        .ql-container {
            border: none !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 0.875rem !important;
            line-height: 1.6 !important;
        }

        .ql-editor {
            padding: 1.5rem !important;
            min-height: 350px !important;
        }

        .ql-editor.ql-blank::before {
            color: var(--gray-500) !important;
            font-style: normal !important;
            font-weight: 500 !important;
        }

        /* Custom Quill Toolbar Styling */
        .ql-toolbar .ql-formats {
            margin-right: 1rem !important;
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
            margin-bottom: 1rem;
        }

        .video-item:hover {
            background-color: var(--gray-25);
            transform: translateY(-1px);
        }

        .video-item:last-child {
            margin-bottom: 0;
        }

        .video-thumbnail {
            position: relative;
            width: 140px;
            height: 78px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
            background-color: var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        }

        .video-item:hover .play-overlay {
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
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .share-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .share-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            z-index: 2001;
            width: 90%;
            max-width: 500px;
            transition: all 0.3s ease;
        }

        .share-modal-overlay.active .share-modal {
            transform: translate(-50%, -50%) scale(1);
        }

        .share-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .share-modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .share-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            color: var(--gray-500);
            transition: all 0.2s ease;
        }

        .share-modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }

        .share-platforms {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .share-platform {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .share-platform:hover {
            transform: translateY(-2px);
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
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .share-url-container {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .share-url-input {
            flex: 1;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background-color: var(--gray-50);
            color: var(--secondary-blue);
            font-weight: 500;
        }

        .share-url-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            background-color: var(--white);
        }

        .share-copy-btn {
            padding: 0.875rem 1.5rem;
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .share-copy-btn:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
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

        /* Enhanced Responsive Design */
        @media (max-width: 1200px) {
            .main-layout {
                grid-template-columns: 1fr 350px;
                gap: 1.5rem;
            }
        }

        @media (max-width: 1024px) {
            .main-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .right-sidebar {
                order: 0; /* Reset to natural order */
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
            .main-layout {
                display: flex;
                flex-direction: column;
                padding: 1rem 0.5rem;
                margin: 0;
                gap: 1.5rem;
                overflow: hidden;
            }
            
            .top-header, .filter-bar {
                display: none;
            }
            
            /* FIXED: Natural sticky positioning - no overlapping */
            .sticky-video-section {
                position: sticky;
                top: 0;
                z-index: 100;
                background-color: var(--white);
                box-shadow: var(--shadow-lg);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                margin-bottom: 1rem;
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
                font-size: 1.5rem;
            }

            .lesson-info-card {
                padding: 1.5rem;
            }

            /* Adjust lesson actions */
            .lesson-actions {
                flex-direction: row !important;
                gap: 0.75rem;
            }

            .action-btn-primary, 
            .action-btn-secondary {
                flex: 1;
                min-width: auto;
                justify-content: center;
                padding: 0.75rem 1rem;
            }

            .action-buttons-grid {
                margin: 1rem 0 1.5rem;
            }

            /* FIXED: Comments section follows naturally - no overlap! */
            .comments-card {
                display: block;
                margin-top: 0; /* Natural spacing */
            }

            /* Related videos follow naturally */
            .related-videos-card {
                margin-top: 1rem;
            }

            /* Hide the right sidebar container */
            .right-sidebar {
                display: contents; /* Children become direct grid children */
                width: 100%;
                min-width: unset;
            }

            .collapsible-sidebar {
                width: 100%;
                max-width: 320px;
            }

            .video-thumbnail {
                width: 120px;
                height: 68px;
            }

            /* Video first */
            .video-container {
                border-radius: 0;
            }

            .share-modal {
                padding: 1.5rem;
                margin: 1rem;
                width: calc(100% - 2rem);
            }
            
            .share-platforms {
                gap: 0.5rem;
            }
            
            .share-platform-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .share-url-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .share-url-input,
            .share-copy-btn {
                width: 100%;
            }

            .notes-editor-actions {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .notes-action-btn {
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 480px) {
            .main-layout {
                padding: 1rem 0.25rem;
            }
            
            .lesson-actions {
                flex-direction: column;
            }

            .action-btn-primary,
            .action-btn-secondary {
                justify-content: center;
            }

            .comment {
                padding: 1rem;
            }

            /* Adjust video item layout */
            .video-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .video-thumbnail {
                width: 100%;
                height: auto;
                aspect-ratio: 16/9;
                margin-bottom: 0.75rem;
            }

            /* FIXED: Smaller compact video for very small screens */
            .sticky-video-section.compact .video-container {
                transform: scale(0.35);
                margin-bottom: -65%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Enhanced Collapsible Sidebar -->
    <div class="collapsible-sidebar" id="collapsibleSidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
            </div>
            <button class="sidebar-close" id="sidebarClose">
                <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="sidebar-content">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main</div>
                <a href="/dashboard/main" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2M8 5a2 2 0 000 4h8a2 2 0 000-4M8 5v0"/>
                    </svg>
                    <span class="sidebar-menu-text">Dashboard</span>
                </a>
                <a href="/dashboard/digilearn" class="sidebar-menu-item active">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="sidebar-menu-text">Lessons</span>
                </a>
                <a href="/subjects" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="sidebar-menu-text">Subjects</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Learning</div>
                <a href="/dashboard/my-progress" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="sidebar-menu-text">My Progress</span>
                </a>
                <a href="/dashboard/saved-lessons" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span class="sidebar-menu-text">Saved Lessons</span>
                </a>
                <a href="/dashboard/my-projects" class="sidebar-menu-item">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="sidebar-menu-text">My Projects</span>
                        <div class="tooltip">My Projects</div>
                    </a>
                <a href="{{ route('dashboard.notes') }}" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="sidebar-menu-text">My Notes</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="/profile" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="sidebar-menu-text">Profile</span>
                </a>
                <a href="/settings" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="sidebar-menu-text">Settings</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="sidebar-menu-item" style="border: none; background: none; width: 100%; text-align: left;">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sidebar-menu-text">Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced Top Header -->
    <div class="top-header">
        <div class="header-left">
            <button class="hamburger-menu" id="hamburgerMenu">
                <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            
            <div class="sidebar-logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
            </div>
        </div>
        
        <div class="header-right">
            <button class="notification-btn">
                <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v0.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>
            
            <div class="user-avatar">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
        </div>
    </div>

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
    <div class="main-layout">
        <!-- Enhanced Left Content -->
        <div class="left-content">
            <!-- Sticky Video Section with scroll-triggered animations -->
            <div class="sticky-video-section" id="stickyVideoSection">
                <!-- Enhanced Video Player -->
                <div class="video-container">
                    <video controls class="video-player" poster="{{ secure_asset($lesson['thumbnail'] ?? '') }}">
                        <source src="{{ secure_asset($lesson['video_url'] ?? '') }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
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
            
            <!-- Enhanced Comments Section -->
            <div class="comments-card">
                <div class="comments-header">
                    <div class="comments-header-left">
                        <span class="comments-count">
                            {{ count($comments ?? []) }} Comments
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
                    <input type="text" class="comment-input" placeholder="Add a comment..." />
                </div>
                
                <div class="comments-list" id="commentsList">
                    <!-- Sample comments to match screenshot -->
                    @for($i = 1; $i <= 7; $i++)
                    <div class="comment">
                        <div class="comment-avatar">E</div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-author">einana kojo</span>
                                <span class="comment-time">3 hours ago</span>
                            </div>
                            <p class="comment-text">very interesting and helpful lesson please add more to it. this time make it more easy.</p>
                            <div class="comment-actions">
                                <button class="comment-action">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="comment-like-count">22</span>
                                </button>
                                <button class="comment-action">
                                    <i class="fas fa-thumbs-down"></i>
                                </button>
                                <button class="comment-action">
                                    Reply
                                </button>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
        
        <!-- Enhanced Right Sidebar -->
        <div class="right-sidebar">
            <!-- Enhanced Action Buttons -->
            <div class="action-buttons-grid">
                <button class="action-btn">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Test
                </button>
                <button class="action-btn action-navigate-btn" data-href="{{ route('dashboard.lesson.document', ['lessonId' => $lesson['id'], 'type' => 'pdf']) }}">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                    </svg>
                    Document
                </button>
                <button class="action-btn action-navigate-btn" data-href="{{ route('dashboard.lesson.document', ['lessonId' => $lesson['id'], 'type' => 'ppt']) }}">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="9" x2="15" y2="9"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                    </svg>
                    PPT
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

            <!-- Enhanced Rich Text Editor with Quill.js -->
            <div id="notesEditorSection" class="notes-editor-section hidden">
                <div class="notes-editor-container">
                    <div class="notes-editor-header">
                        <input type="text" class="notes-title-input" placeholder="Enter note title..." />
                        <div class="notes-editor-actions">
                            <button class="notes-action-btn save" id="saveNotesBtn">
                                <i class="fas fa-save"></i>
                                Save
                            </button>
                            <button class="notes-action-btn export" id="exportNotesBtn">
                                <i class="fas fa-download"></i>
                                Export
                            </button>
                            <button class="notes-action-btn delete" id="deleteNotesBtn">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                    <div class="notes-editor-main">
                        <div id="notesQuillEditor"></div>
                    </div>
                </div>
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
        let quillEditor = null;
        let notesData = {
            title: '',
            content: '',
            lastSaved: null
        };

        // Scroll tracking variables for mobile video section
        let lastScrollY = 0;
        let isScrollingDown = false;
        let scrollThreshold = 100; // Pixels to scroll before triggering compact mode

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all functionality
            initializeSidebar();
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
        });

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

        // Enhanced sidebar functionality
        function initializeSidebar() {
            const hamburgerMenu = document.getElementById('hamburgerMenu');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const collapsibleSidebar = document.getElementById('collapsibleSidebar');
            const sidebarClose = document.getElementById('sidebarClose');

            function openSidebar() {
                sidebarOverlay.classList.add('active');
                collapsibleSidebar.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebarOverlay.classList.remove('active');
                collapsibleSidebar.classList.remove('active');
                document.body.style.overflow = '';
            }

            hamburgerMenu.addEventListener('click', openSidebar);
            sidebarClose.addEventListener('click', closeSidebar);
            sidebarOverlay.addEventListener('click', closeSidebar);

            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && collapsibleSidebar.classList.contains('active')) {
                    closeSidebar();
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
            const commentActions = document.querySelectorAll('.comment-action');
            commentActions.forEach(action => {
                action.addEventListener('click', function(e) {
                    e.preventDefault();
                    const actionText = this.textContent.trim();
                    console.log('Comment action:', actionText);
                    
                    // Add visual feedback for interactions
                    if (actionText.includes('👍') || this.querySelector('.fa-thumbs-up')) {
                        this.style.color = 'var(--primary-red)';
                        setTimeout(() => {
                            this.style.color = '';
                        }, 200);
                    }
                });
            });
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

            // Navigation buttons
            document.querySelectorAll('.action-navigate-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                window.location.href = this.dataset.href;
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
            const notesEditorSection = document.getElementById('notesEditorSection');
            const notesTitleInput = document.querySelector('.notes-title-input');
            const saveNotesBtn = document.getElementById('saveNotesBtn');
            const exportNotesBtn = document.getElementById('exportNotesBtn');
            const deleteNotesBtn = document.getElementById('deleteNotesBtn');

            // Initialize Quill editor
            function initializeQuill() {
                if (quillEditor) return;

                quillEditor = new Quill('#notesQuillEditor', {
                    theme: 'snow',
                    placeholder: 'Start writing your notes here...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'align': [] }],
                            ['blockquote', 'code-block'],
                            ['link', 'image'],
                            ['clean']
                        ]
                    }
                });

                // Auto-save functionality
                quillEditor.on('text-change', function() {
                    notesData.content = quillEditor.root.innerHTML;
                    debounce(autoSave, 2000)();
                });
            }

            // Toggle notes editor
            function toggleNotesEditor() {
                if (notesEditorSection.classList.contains('hidden')) {
                    notesEditorSection.classList.remove('hidden');
                    addNotesBtn.innerHTML = `
                        Hide notes
                        <svg fill="currentColor" viewBox="0 0 24 24" style="transform: rotate(45deg);">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    `;
                    
                    // Initialize Quill editor when shown
                    setTimeout(initializeQuill, 100);
                } else {
                    notesEditorSection.classList.add('hidden');
                    addNotesBtn.innerHTML = `
                        Add notes
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    `;
                }
            }

            // Auto-save functionality
            function autoSave() {
                if (notesTitleInput.value.trim() || (quillEditor && quillEditor.getText().trim())) {
                    notesData.title = notesTitleInput.value.trim();
                    notesData.content = quillEditor ? quillEditor.root.innerHTML : '';
                    notesData.lastSaved = new Date().toISOString();
                    
                    // Save to localStorage for demo purposes
                    localStorage.setItem('lessonNotes', JSON.stringify(notesData));
                    
                    console.log('Notes auto-saved:', notesData);
                }
            }

            // Manual save functionality
            function saveNotes() {
                if (!notesTitleInput.value.trim() && (!quillEditor || !quillEditor.getText().trim())) {
                    alert('Please add a title or content before saving.');
                    return;
                }

                const saveBtn = saveNotesBtn;
                const originalContent = saveBtn.innerHTML;
                
                // Show loading state
                saveBtn.innerHTML = '<div class="loading-spinner"></div> Saving...';
                saveBtn.disabled = true;

                // Simulate API call
                setTimeout(() => {
                    notesData.title = notesTitleInput.value.trim();
                    notesData.content = quillEditor ? quillEditor.root.innerHTML : '';
                    notesData.lastSaved = new Date().toISOString();
                    
                    // Save to localStorage for demo purposes
                    localStorage.setItem('lessonNotes', JSON.stringify(notesData));
                    
                    // Show success state
                    saveBtn.innerHTML = '<i class="fas fa-check"></i> Saved!';
                    saveBtn.style.backgroundColor = '#10b981';
                    
                    // Show success message
                    showSuccessMessage('Notes saved successfully!');
                    
                    setTimeout(() => {
                        saveBtn.innerHTML = originalContent;
                        saveBtn.style.backgroundColor = '';
                        saveBtn.disabled = false;
                    }, 2000);
                }, 1000);
            }

            // Export functionality
            function exportNotes() {
                if (!notesTitleInput.value.trim() && (!quillEditor || !quillEditor.getText().trim())) {
                    alert('No content to export.');
                    return;
                }

                const exportBtn = exportNotesBtn;
                const originalContent = exportBtn.innerHTML;
                
                exportBtn.innerHTML = '<div class="loading-spinner"></div> Exporting...';
                exportBtn.disabled = true;

                setTimeout(() => {
                    const title = notesTitleInput.value.trim() || 'Lesson Notes';
                    const content = quillEditor ? quillEditor.root.innerHTML : '';
                    
                    // Create HTML content
                    const htmlContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>${title}</title>
                            <style>
                                body { font-family: 'Inter', sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 2rem; }
                                h1 { color: #E11E2D; border-bottom: 2px solid #E11E2D; padding-bottom: 0.5rem; }
                                .meta { color: #6b7280; font-size: 0.875rem; margin-bottom: 2rem; }
                            </style>
                        </head>
                        <body>
                            <h1>${title}</h1>
                            <div class="meta">Exported on ${new Date().toLocaleDateString()}</div>
                            <div>${content}</div>
                        </body>
                        </html>
                    `;
                    
                    // Create and download file
                    const blob = new Blob([htmlContent], { type: 'text/html' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.html`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    
                    exportBtn.innerHTML = '<i class="fas fa-check"></i> Exported!';
                    exportBtn.style.backgroundColor = '#10b981';
                    
                    showSuccessMessage('Notes exported successfully!');
                    
                    setTimeout(() => {
                        exportBtn.innerHTML = originalContent;
                        exportBtn.style.backgroundColor = '';
                        exportBtn.disabled = false;
                    }, 2000);
                }, 1000);
            }

            // Delete functionality
            function deleteNotes() {
                if (confirm('Are you sure you want to delete these notes? This action cannot be undone.')) {
                    notesTitleInput.value = '';
                    if (quillEditor) {
                        quillEditor.setContents([]);
                    }
                    
                    // Clear localStorage
                    localStorage.removeItem('lessonNotes');
                    
                    showSuccessMessage('Notes deleted successfully!');
                    console.log('Notes deleted');
                }
            }

            // Load saved notes
            function loadSavedNotes() {
                const saved = localStorage.getItem('lessonNotes');
                if (saved) {
                    try {
                        const data = JSON.parse(saved);
                        notesTitleInput.value = data.title || '';
                        if (quillEditor && data.content) {
                            quillEditor.root.innerHTML = data.content;
                        }
                        notesData = data;
                    } catch (e) {
                        console.error('Error loading saved notes:', e);
                    }
                }
            }

            // Event listeners
            addNotesBtn.addEventListener('click', toggleNotesEditor);
            saveNotesBtn.addEventListener('click', saveNotes);
            exportNotesBtn.addEventListener('click', exportNotes);
            deleteNotesBtn.addEventListener('click', deleteNotes);
            
            notesTitleInput.addEventListener('input', function() {
                notesData.title = this.value.trim();
                debounce(autoSave, 2000)();
            });

            // Load saved notes on page load
            setTimeout(loadSavedNotes, 500);
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
</body>
</html>
