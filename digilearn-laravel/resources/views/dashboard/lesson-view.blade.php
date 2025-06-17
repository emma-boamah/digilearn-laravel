@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --primary-red: #E11E2D;
        --secondary-blue: #2677B8;
        --white: #ffffff;
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
    }

    /* Top Header */
    .top-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        background-color: var(--white);
        border-bottom: 1px solid var(--gray-200);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sidebar-logo img {
        height: 40px;
        width: auto;
    }

    .sidebar-brand {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-red);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .notification-icon {
        width: 20px;
        height: 20px;
        color: var(--gray-600);
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: var(--gray-600);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 0.875rem;
        font-weight: 600;
    }

    /* Filter Bar */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 2rem;
        background-color: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
    }

    .back-button {
        background: none;
        border: none;
        color: var(--primary-red);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .back-button:hover {
        background-color: var(--gray-300);
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 0.75rem 0.75rem 2.5rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        background-color: var(--white);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
    }

    .filter-dropdown {
        position: relative;
    }

    .dropdown-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        background-color: var(--white);
        color: var(--primary-red);
        font-size: 0.875rem;
        cursor: pointer;
        min-width: 120px;
    }

    .dropdown-button:hover {
        background-color: var(--gray-50);
    }

    .dropdown-chevron {
        width: 16px;
        height: 16px;
        color: var(--primary-red);
    }

    .filter-button {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        background-color: var(--white);
        color: var(--primary-red);
        font-size: 0.875rem;
        cursor: pointer;
        font-weight: 500;
    }

    .filter-button:hover {
        background-color: var(--gray-50);
    }

    .filter-button.active {
        background-color: var(--primary-red);
        color: var(--white);
        border-color: var(--primary-red);
    }

    /* Main Layout */
    .main-layout {
        display: grid;
        grid-template-columns: 2fr 400px;
        gap: 2rem;
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Left Content */
    .left-content {
        background-color: var(--white);
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Remove video-section styles and update action buttons */
    .action-buttons-top {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background-color: var(--primary-red);
        color: var(--white);
        border: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        flex: 1;
        justify-content: center;
        min-width: 80px;
    }

    .video-container {
        position: relative;
        aspect-ratio: 16/9;
        background-color: #000;
        border-radius: 0.5rem;
        overflow: hidden;
        margin: 1rem;
    }

    .video-player {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .add-notes-section {
        background-color: var(--white);
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .add-notes-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background-color: var(--primary-red);
        color: var(--white);
        border: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        width: 100%;
        justify-content: center;
    }

    .add-notes-btn:hover {
        background-color: #c41e2a;
    }

    /* Lesson Info */
    .lesson-info {
        padding: 1.5rem;
    }

    .lesson-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .lesson-meta {
        color: var(--gray-500);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .lesson-actions {
        display: flex;
        gap: 1rem;
        margin: 1.5rem 0 2rem 0;
    }

    .save-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background-color: var(--primary-red);
        color: var(--white);
        border: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        cursor: pointer;
        font-weight: 500;
    }

    .share-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background-color: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        cursor: pointer;
        font-weight: 500;
    }

    /* Comments Section */
    .comments-section {
        border-top: 1px solid var(--gray-200);
        padding: 1.5rem;
    }

    .comments-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .comments-count {
        font-weight: 600;
        color: var(--gray-900);
        font-size: 1rem;
    }

    .comment-input-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .comment-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--secondary-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-weight: 600;
        flex-shrink: 0;
        font-size: 0.875rem;
    }

    .comment-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        resize: none;
        min-height: 80px;
    }

    .comment-input:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
    }

    /* Comments List */
    .comment {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
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
        cursor: pointer;
        background: none;
        border: none;
        padding: 0.25rem 0;
    }

    .comment-action:hover {
        color: var(--gray-700);
    }

    /* Right Sidebar */
    .right-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .sidebar-section {
        background-color: var(--white);
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .video-item {
        display: flex;
        gap: 1rem;
        padding: 0.75rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-bottom: 0.5rem;
    }

    .video-item:hover {
        background-color: var(--gray-50);
    }

    .video-item:last-child {
        margin-bottom: 0;
    }

    .video-thumbnail {
        position: relative;
        width: 120px;
        height: 68px;
        border-radius: 0.375rem;
        overflow: hidden;
        flex-shrink: 0;
        background-color: var(--gray-200);
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
        background-color: rgba(0, 0, 0, 0.3);
    }

    .play-icon {
        width: 24px;
        height: 24px;
        color: var(--white);
    }

    .video-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .video-title {
        font-weight: 500;
        color: var(--gray-900);
        font-size: 0.875rem;
        line-height: 1.3;
        margin-bottom: 0.25rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .video-meta {
        color: var(--gray-500);
        font-size: 0.75rem;
    }

    .video-menu {
        color: var(--gray-400);
        cursor: pointer;
        padding: 0.25rem;
        align-self: flex-start;
    }

    .video-menu:hover {
        color: var(--gray-600);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .main-layout {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .right-sidebar {
            order: -1;
        }
    }

    @media (max-width: 768px) {
        .filter-bar {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .main-layout {
            padding: 1rem;
        }
        
        .action-buttons {
            flex-wrap: wrap;
        }

        .header-left {
            gap: 1rem;
        }

        .shoutout-section {
            display: none;
        }
    }

    /* Notes Modal */
    .notes-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 2rem;
    }

    .notes-modal {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        width: 100%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        position: relative;
    }

    .notes-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .notes-modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .notes-modal-close {
        background: none;
        border: none;
        color: var(--gray-400);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notes-modal-close:hover {
        background-color: var(--gray-100);
        color: var(--gray-600);
    }

    .notes-modal-body {
        padding: 1.5rem;
    }

    .notes-textarea {
        width: 100%;
        min-height: 300px;
        padding: 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        resize: vertical;
        font-family: inherit;
    }

    .notes-textarea:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
    }

    .notes-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding: 1.5rem;
        border-top: 1px solid var(--gray-200);
        background-color: var(--gray-50);
    }

    .notes-cancel-btn {
        padding: 0.75rem 1.5rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        background-color: var(--white);
        color: var(--gray-700);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .notes-cancel-btn:hover {
        background-color: var(--gray-50);
    }

    .notes-save-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.375rem;
        background-color: var(--primary-red);
        color: var(--white);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .notes-save-btn:hover {
        background-color: #c41e2a;
    }

    .hidden {
        display: none;
    }

    /* Notes Editor Section */
    .notes-editor-section {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
        overflow: hidden;
        width: 100%;
    }

    .notes-editor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid var(--gray-200);
        background-color: var(--gray-50);
    }

    .notes-title-input {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        background-color: var(--white);
        margin-right: 1rem;
    }

    .notes-title-input:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 2px rgba(225, 30, 45, 0.1);
    }

    .notes-toolbar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .toolbar-btn {
        width: 40px;
        height: 40px;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        background-color: var(--white);
        color: var(--gray-600);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .toolbar-btn:hover {
        background-color: var(--secondary-blue);
        color: var(--white);
        border-color: var(--secondary-blue);
    }

    .toolbar-btn.active {
        background-color: var(--secondary-blue);
        color: var(--white);
        border-color: var(--secondary-blue);
    }

    .notes-editor-body {
        padding: 1rem;
    }

    .notes-editor-textarea {
        width: 100%;
        min-height: 250px;
        padding: 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        line-height: 1.5;
        resize: vertical;
        font-family: inherit;
        background-color: var(--white);
    }

    .notes-editor-textarea:focus {
        outline: none;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 2px rgba(225, 30, 45, 0.1);
    }
</style>

<!-- Top Header -->
<div class="top-header">
    <div class="header-left">
        <div class="header-left">
            <div class="brand-section sidebar-logo">
                <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
            </div>
        </div>
    </div>
    
    <div class="header-right">
        <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
        </svg>
        
        <div class="user-avatar">
            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <button class="back-button" onclick="history.back()">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    
    <div class="search-box">
        <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" class="search-input" placeholder="Search">
    </div>
    
    <div class="filter-dropdown">
        <button class="dropdown-button">
            <span>{{ ucwords(str_replace('-', ' ', $selectedLevel)) }}</span>
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

<!-- Main Layout -->
<div class="main-layout">
    <!-- Left Content -->
    <div class="left-content">
        <!-- Video Player -->
        <div class="video-container">
            <video controls class="video-player" poster="{{ asset($lesson['thumbnail']) }}">
                <source src="{{ asset($lesson['video_url']) }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <!-- Lesson Info -->
        <div class="lesson-info">
            <h1 class="lesson-title">{{ $lesson['title'] }}</h1>
            <p class="lesson-meta">({{ $lesson['subject'] }})</p>
            <p class="lesson-meta">{{ $lesson['instructor'] }} | {{ $lesson['year'] }}</p>
            
            <div class="lesson-actions">
                <button class="save-btn">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    Save
                </button>
                <button class="share-btn">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
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
        
        <!-- Comments Section -->
        <div class="comments-section">
            <div class="comments-header">
                <span class="comments-count">{{ count($comments) }} Comments</span>
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 4h18v2H3V4zm0 7h18v2H3v-2zm0 7h18v2H3v-2z"/>
                </svg>
            </div>
            
            <div class="comment-input-container">
                <div class="comment-avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                <textarea class="comment-input" placeholder="Add a comment..." rows="3"></textarea>
            </div>
            
            <div class="comments-list">
                @foreach($comments as $comment)
                <div class="comment">
                    <div class="comment-avatar">{{ $comment['user_avatar'] }}</div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author">{{ $comment['user_name'] }}</span>
                            <span class="comment-time">{{ $comment['time_ago'] }}</span>
                        </div>
                        <p class="comment-text">{{ $comment['comment'] }}</p>
                        <div class="comment-actions">
                            <button class="comment-action">
                                👍 {{ $comment['likes'] ?? 22 }}
                            </button>
                            <button class="comment-action">
                                👎
                            </button>
                            <button class="comment-action">
                                Reply
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <div class="right-sidebar">
        <!-- Action Buttons - Above related videos -->
        <div class="action-buttons-top">
            <button class="action-btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                Test
            </button>
            <button class="action-btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
                Document
            </button>
            <button class="action-btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="9" y1="9" x2="15" y2="9"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                </svg>
                PPT
            </button>
        </div>

        <!-- Notes Editor Section (Hidden by default) -->
        <div class="add-notes-section">
            <button class="add-notes-btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2v10m0 0l3-3m-3 3l-3-3"/>
                    <path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M19 21l-7-7 3-3 7 7v3z"/>
                </svg>
                Add notes
            </button>
        </div>
        <div id="notesEditorSection" class="notes-editor-section hidden">
            <div class="notes-editor-header">
                <input type="text" class="notes-title-input" placeholder="Title" />
                <div class="notes-toolbar">
                    <button class="toolbar-btn save-btn" title="Save">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                    </button>
                    <button class="toolbar-btn delete-btn" title="Delete">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                            <polyline points="3,6 5,6 21,6"/>
                            <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/>
                        </svg>
                    </button>
                    <button class="toolbar-btn bold-btn" title="Bold">
                        <span style="font-weight: bold; font-size: 16px;">B</span>
                    </button>
                    <button class="toolbar-btn italic-btn" title="Italic">
                        <span style="font-style: italic; font-size: 16px;">I</span>
                    </button>
                    <button class="toolbar-btn font-btn" title="Font">
                        <span style="font-size: 14px;">Aa</span>
                    </button>
                    <button class="toolbar-btn font-size-btn" title="Font Size">
                        <span style="font-size: 14px;">Tt</span>
                    </button>
                </div>
            </div>
            <div class="notes-editor-body">
                <textarea class="notes-editor-textarea" placeholder="Write your notes here..."></textarea>
            </div>
        </div>

        <div class="sidebar-section">
            @foreach($relatedLessons as $relatedLesson)
            <div class="video-item" onclick="window.location.href='/dashboard/lesson/{{ $relatedLesson['id'] }}'">
                <div class="video-thumbnail">
                    <img src="{{ asset($relatedLesson['thumbnail']) }}" alt="{{ $relatedLesson['title'] }}" 
                         onerror="this.src='/placeholder.svg?height=68&width=120'">
                    <div class="play-overlay">
                        <svg class="play-icon" fill="currentColor" viewBox="0 0 24 24">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                    </div>
                </div>
                <div class="video-details">
                    <h4 class="video-title">{{ $relatedLesson['title'] }}</h4>
                    <p class="video-meta">{{ $relatedLesson['instructor'] }} {{ $relatedLesson['year'] }}</p>
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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles
    const dropdownButtons = document.querySelectorAll('.dropdown-button');
    
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Dropdown clicked');
        });
    });
    
    // Handle filter buttons
    const filterButtons = document.querySelectorAll('.filter-button');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Handle comment actions
    const commentActions = document.querySelectorAll('.comment-action');
    
    commentActions.forEach(action => {
        action.addEventListener('click', function() {
            console.log('Comment action clicked:', this.textContent.trim());
        });
    });
    
    // Handle video item clicks
    const videoItems = document.querySelectorAll('.video-item');
    
    videoItems.forEach(item => {
        item.addEventListener('click', function() {
            console.log('Video item clicked');
        });
    });

    // Handle action buttons
    const actionButtons = document.querySelectorAll('.action-btn');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            console.log('Action clicked:', action);
            
            // You can add specific functionality for each action here
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

    // Handle notes editor functionality
    const notesToolbar = document.querySelector('.notes-toolbar');
    const notesTextarea = document.querySelector('.notes-editor-textarea');
    const notesTitleInput = document.querySelector('.notes-title-input');

    if (notesToolbar) {
        notesToolbar.addEventListener('click', function(e) {
            const button = e.target.closest('.toolbar-btn');
            if (!button) return;

            if (button.classList.contains('save-btn')) {
                saveNotes();
            } else if (button.classList.contains('delete-btn')) {
                deleteNotes();
            } else if (button.classList.contains('bold-btn')) {
                toggleFormat('bold');
            } else if (button.classList.contains('italic-btn')) {
                toggleFormat('italic');
            }
        });
    }

    function saveNotes() {
        const title = notesTitleInput.value.trim();
        const content = notesTextarea.value.trim();
        
        if (title || content) {
            console.log('Saving notes:', { title, content });
            alert('Notes saved successfully!');
        } else {
            alert('Please add a title or content before saving.');
        }
    }

    function deleteNotes() {
        if (confirm('Are you sure you want to delete these notes?')) {
            notesTitleInput.value = '';
            notesTextarea.value = '';
            console.log('Notes deleted');
        }
    }

    function toggleFormat(format) {
        const button = document.querySelector(`.${format}-btn`);
        button.classList.toggle('active');
        
        // Apply formatting to selected text in textarea
        if (notesTextarea.selectionStart !== notesTextarea.selectionEnd) {
            const start = notesTextarea.selectionStart;
            const end = notesTextarea.selectionEnd;
            const selectedText = notesTextarea.value.substring(start, end);
            
            let formattedText;
            if (format === 'bold') {
                formattedText = `**${selectedText}**`;
            } else if (format === 'italic') {
                formattedText = `*${selectedText}*`;
            }
            
            notesTextarea.value = notesTextarea.value.substring(0, start) + formattedText + notesTextarea.value.substring(end);
            notesTextarea.focus();
            notesTextarea.setSelectionRange(start, start + formattedText.length);
        }
    }

    // Handle add notes button
    const addNotesBtn = document.querySelector('.add-notes-btn');
    if (addNotesBtn) {
        addNotesBtn.addEventListener('click', function() {
            toggleNotesEditor();
        });
    }

    function toggleNotesEditor() {
        const notesEditor = document.getElementById('notesEditorSection');
        const addNotesBtn = document.querySelector('.add-notes-btn');
        
        if (notesEditor.classList.contains('hidden')) {
            notesEditor.classList.remove('hidden');
            addNotesBtn.textContent = 'Hide notes';
            addNotesBtn.querySelector('svg').style.transform = 'rotate(180deg)';
        } else {
            notesEditor.classList.add('hidden');
            addNotesBtn.innerHTML = `
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2v10m0 0l3-3m-3 3l-3-3"/>
                    <path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M19 21l-7-7 3-3 7 7v3z"/>
                </svg>
                Add notes
            `;
        }
    }
});
</script>
@endsection
