@extends('layouts.dashboard-components')

@section('head')
    <title>AI Tutor - {{ config('app.name') }}</title>
    <meta name="description" content="Ask the AI Tutor to find you the perfect lesson on any topic.">
@endsection

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive/mathlive-static.css" />
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* ============= Agent Page Container ============= */
        .agent-page {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            padding: 1.5rem;
            padding-top: calc(76px + var(--safe-area-inset-top, 0px));
            max-width: 900px;
            margin: 0 auto;
            min-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            background-image: radial-gradient(rgba(38, 119, 184, 0.06) 1px, transparent 1px);
            background-size: 24px 24px;
            border-radius: 1.5rem;
            position: relative;
        }

        [data-theme="dark"] .agent-page {
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        /* ============= History Icon (Top Right) ============= */
        .history-icon-btn {
            position: fixed;
            top: calc(76px + var(--safe-area-inset-top, 0px));
            right: 1.5rem;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 500;
            box-shadow: var(--shadow-sm);
        }

        .history-icon-btn:hover {
            color: #2677B8;
            border-color: #2677B8;
            background: var(--bg-surface);
            box-shadow: var(--shadow-md);
            transform: scale(1.05);
        }

        .history-icon-btn svg {
            width: 20px;
            height: 20px;
        }

        .history-icon-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            background: #2677B8;
            border-radius: 50%;
            border: 2px solid var(--bg-surface);
        }

        /* ============= History Drawer Scrim ============= */
        .history-drawer-scrim {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0);
            z-index: 999;
            pointer-events: none;
            transition: background 0.35s cubic-bezier(0.32, 0.72, 0, 1);
        }

        .history-drawer-scrim.open {
            background: rgba(0, 0, 0, 0.3);
            pointer-events: auto;
        }

        [data-theme="dark"] .history-drawer-scrim.open {
            background: rgba(0, 0, 0, 0.55);
        }

        /* ============= History Drawer ============= */
        .history-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 340px;
            height: 100vh;
            background: var(--bg-surface);
            border-left: 1px solid var(--border-color);
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.38s cubic-bezier(0.32, 0.72, 0, 1);
            display: flex;
            flex-direction: column;
            box-shadow: -8px 0 30px rgba(0, 0, 0, 0.08);
            will-change: transform;
        }

        .history-drawer.open {
            transform: translateX(0);
        }

        [data-theme="dark"] .history-drawer {
            box-shadow: -8px 0 30px rgba(0, 0, 0, 0.3);
        }

        .drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .drawer-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .drawer-close-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: var(--gray-100);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .drawer-close-btn:hover {
            background: var(--gray-200);
            color: var(--text-main);
        }

        .drawer-new-chat-btn {
            margin: 1rem 1.25rem 0.75rem;
            padding: 0.625rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .drawer-new-chat-btn:hover {
            border-color: #2677B8;
            color: #2677B8;
            background: rgba(38, 119, 184, 0.04);
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 1.25rem 1.25rem;
        }

        .drawer-body::-webkit-scrollbar {
            width: 4px;
        }

        .drawer-body::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        /* ============= Chat Area ============= */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1rem 0;
            overflow-y: auto;
            min-height: 200px;
        }

        .chat-bubble {
            max-width: 85%;
            padding: 0.875rem 1.125rem;
            border-radius: 1.125rem;
            font-size: 1rem;
            line-height: 1.75;
            font-weight: 400;
            animation: slideUp 0.3s ease-out;
            word-wrap: break-word;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-bubble.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #2677B8 0%, #1a508b 100%);
            color: #fff;
            border-bottom-right-radius: 0.375rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .bubble-attachment {
            margin-bottom: 0.5rem;
        }

        .bubble-attachment-image img {
            max-width: 240px;
            max-height: 200px;
            border-radius: 0.75rem;
            object-fit: cover;
            display: block;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(255, 255, 255, 0.25);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .bubble-attachment-image img:hover {
            transform: scale(1.02);
        }

        .bubble-attachment-file {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 0.5rem;
            font-size: 0.8rem;
            max-width: 100%;
        }

        .bubble-attachment-file svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .bubble-file-name {
            color: #fff;
            font-weight: 600;
            text-decoration: underline;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .bubble-file-size {
            font-size: 0.7rem;
            opacity: 0.8;
            margin-left: 0.25rem;
        }

        .chat-bubble.agent {
            align-self: flex-start;
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-bottom-left-radius: 0.375rem;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            line-height: 1.8;
            font-weight: 400;
            max-width: 750px;
        }

        .chat-bubble.agent strong {
            font-weight: 600;
        }

        .chat-bubble.agent.error {
            border-left: 4px solid #ef4444;
            background: var(--bg-surface);
            color: var(--text-main);
            padding: 1rem 1.25rem;
            box-shadow: var(--shadow-sm);
        }

        .chat-bubble.agent.error::before {
            content: '⚠️ AI Tutor Note';
            display: block;
            font-weight: 800;
            font-size: 0.65rem;
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.375rem;
        }

        [data-theme="dark"] .chat-bubble.agent.error {
            border-left-color: #f87171;
            background: var(--bg-surface);
        }

        /* ============= Typing Indicator ============= */
        .typing-indicator {
            display: none;
            align-self: flex-start;
            padding: 0.875rem 1.25rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 1.125rem;
            border-bottom-left-radius: 0.375rem;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--text-muted);
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0.6);
                opacity: 0.4;
            }

            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* ============= Result Card ============= */
        .result-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            max-width: 420px;
            animation: slideUp 0.4s ease-out;
            box-shadow: var(--shadow-md);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .result-card-thumb {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: var(--gray-200);
        }

        .result-card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .result-card-duration {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }

        .result-card-body {
            padding: 1rem;
        }

        .result-card-body h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .result-card-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .result-card-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 500;
        }

        .result-card-badge.existing {
            background: #dbeafe;
            color: #1e40af;
        }

        .result-card-badge.new {
            background: #d1fae5;
            color: #065f46;
        }

        [data-theme="dark"] .result-card-badge.existing {
            background: rgba(30, 64, 175, 0.2);
            color: #93c5fd;
        }

        [data-theme="dark"] .result-card-badge.new {
            background: rgba(6, 95, 70, 0.2);
            color: #6ee7b7;
        }

        .watch-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #2677B8 0%, #1a508b 100%);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 0.625rem;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }

        .watch-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #fff;
        }

        .summary-box {
            margin: 0.5rem 0 1rem 0;
            padding: 0.5rem 0.75rem;
            background: var(--gray-50);
            border-left: 2px solid #2677B8;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            line-height: 1.4;
            color: var(--text-main);
        }

        /* ============= Tutor Explanation Bubble ============= */
        .chat-bubble.tutor-explanation {
            font-family: 'Inter', sans-serif;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            max-width: 750px;
            padding: 1.5rem;
            padding-bottom: 2.5rem;
            /* Space for the toggle */
            font-size: 1.05rem;
            line-height: 1.8;
            font-weight: 400;
            box-shadow: var(--shadow-sm);
            border-radius: 1rem;
            margin-bottom: 2rem;
            position: relative;
            max-height: 180px;
            /* Approx 4-5 lines */
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .chat-bubble.tutor-explanation strong {
            font-weight: 600;
        }

        [data-theme="dark"] .chat-bubble.tutor-explanation {
            background: var(--bg-surface);
            border-color: #374151;
        }

        .explanation-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Poppins', 'Inter', sans-serif;
            font-weight: 700;
            color: #2677B8;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .explanation-title svg {
            color: #2677B8;
        }

        .chat-bubble.tutor-explanation.expanded {
            max-height: 2000px;
            padding-bottom: 3rem;
        }

        .tutor-explanation-toggle {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 0.5rem 1.25rem;
            background: linear-gradient(to top, var(--bg-surface) 70%, transparent);
            border: none;
            color: #2677B8;
            font-weight: 700;
            font-size: 0.8rem;
            text-align: left;
            cursor: pointer;
            display: none;
            z-index: 10;
        }

        .chat-bubble.tutor-explanation.expanded .tutor-explanation-toggle {
            background: var(--bg-surface);
        }

        .chat-bubble.tutor-explanation p {
            margin-bottom: 1rem;
        }

        .chat-bubble.tutor-explanation p:last-child {
            margin-bottom: 0;
        }

        .explanation-list {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        ul.explanation-list {
            list-style-type: disc;
        }

        ol.explanation-list {
            list-style-type: decimal;
        }

        .explanation-list li {
            margin-bottom: 0.5rem;
            line-height: 1.7;
        }

        .explanation-list li:last-child {
            margin-bottom: 0;
        }

        .summary-box p {
            margin-bottom: 0.5rem;
        }

        .summary-box p:last-child {
            margin-bottom: 0;
        }

        /* ============= Roadmap Card ============= */
        .roadmap-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.4s ease-out;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .roadmap-header {
            padding: 1.25rem;
            background: linear-gradient(135deg, #2677B8 0%, #1a508b 100%);
            color: #fff;
        }

        .roadmap-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .roadmap-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 2px 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .roadmap-body {
            padding: 1rem;
        }

        .roadmap-steps {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            position: relative;
        }

        .roadmap-steps::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 10px;
            bottom: 10px;
            width: 2px;
            background: var(--border-color);
        }

        .roadmap-step {
            display: flex;
            gap: 1rem;
            position: relative;
            z-index: 1;
            text-decoration: none;
            color: inherit;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background 0.2s;
        }

        .roadmap-step:hover {
            background: var(--gray-50);
            color: inherit;
        }

        .step-number {
            width: 32px;
            height: 32px;
            background: var(--bg-surface);
            border: 2px solid #2677B8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #2677B8;
            flex-shrink: 0;
        }

        .roadmap-step:hover .step-number {
            background: #2677B8;
            color: #fff;
        }

        .step-content {
            flex: 1;
        }

        .step-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.125rem;
        }

        .step-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* ============= Quiz Card ============= */
        .quiz-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            max-width: 420px;
            animation: slideUp 0.4s ease-out;
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
        }

        .quiz-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .quiz-card-icon {
            width: 40px;
            height: 40px;
            background: #fef3c7;
            color: #F59E0B;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .quiz-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .quiz-card-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 1.25rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .quiz-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid #2677B8;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }

        .quiz-btn:hover {
            background-color: rgba(38, 119, 184, 0.05);
            color: #2677B8;
        }

        [data-theme="dark"] .quiz-btn {
            border-color: #3b82f6;
            color: #e5e7eb;
        }

        [data-theme="dark"] .quiz-btn:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }

        /* ============= Suggestion Chips ============= */
        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            padding: 0.5rem 0;
        }

        .suggestion-chip {
            padding: 0.5rem 1rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 999px;
            font-size: 0.8rem;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .suggestion-chip img {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }

        .suggestion-chip:hover {
            background: var(--gray-100);
            color: #2677B8;
            border-color: #2677B8;
            transform: translateY(-1px);
        }

        /* ============= Quick Starters (above input in welcome state) ============= */
        .quick-starters {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .quick-starter-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quick-starter-btn:hover {
            border-color: #2677B8;
            color: #2677B8;
            background: rgba(38, 119, 184, 0.04);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .quick-starter-btn.active {
            background: #2677B8;
            border-color: #2677B8;
            color: #fff;
        }

        .quick-starter-btn svg {
            width: 15px;
            height: 15px;
        }

        .roadmap-tag {
            font-size: 0.55rem;
            padding: 1px 4px;
            border-radius: 4px;
            background: #2677B8;
            color: #fff;
            font-weight: 700;
            margin-left: 2px;
        }

        .quick-starter-btn.active .roadmap-tag {
            background: rgba(255, 255, 255, 0.3);
        }

        /* ============= Requests Badge (above input) ============= */
        .requests-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            margin-bottom: 1rem;
            padding: 0.25rem 0.75rem;
            background: var(--gray-100);
            border-radius: 999px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .requests-badge .count {
            font-weight: 600;
            color: var(--accent);
        }

        /* ============= Input Area ============= */
        .input-area {
            padding: 1rem 1.25rem;
            background: var(--bg-surface);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 2px solid rgba(0, 0, 0, 0.12);
            z-index: 100;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            max-width: 700px;
        }

        .input-area:focus-within {
            border-color: #2677B8;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 0 3px rgba(38, 119, 184, 0.15);
        }

        [data-theme="dark"] .input-area {
            background: var(--gray-50);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.15);
        }

        [data-theme="dark"] .input-area:focus-within {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6), 0 0 0 3px rgba(38, 119, 184, 0.2);
        }

        /* When chat is active, make input sticky at bottom */
        .agent-page.chat-active .input-area {
            position: sticky;
            bottom: 1.5rem;
            margin-top: 1rem;
            max-width: 100%;
        }

        .input-wrapper {
            display: flex;
            gap: 0.5rem;
            background: transparent;
            border: none;
            padding: 0;
            align-items: center;
        }

        .input-inner {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-inner input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.95rem;
            color: var(--text-main);
            padding: 0.5rem 0.5rem 0.5rem 0.75rem;
            caret-color: #2677B8;
            width: 100%;
        }

        .fake-caret {
            position: absolute;
            left: 0.75rem;
            width: 2px;
            height: 1.2rem;
            background-color: #2677B8;
            animation: caretBlink 1s step-end infinite;
            pointer-events: none;
            display: none;
        }

        .input-inner input:placeholder-shown:not(:focus)~.fake-caret {
            display: block;
        }

        @keyframes caretBlink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .input-wrapper input::placeholder {
            color: var(--text-muted);
        }

        /* ============= Plus Button & Dropdown ============= */
        .plus-btn-wrapper {
            position: relative;
            flex-shrink: 0;
        }

        .plus-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1.5px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .plus-btn:hover {
            border-color: #2677B8;
            color: #2677B8;
            background: rgba(38, 119, 184, 0.04);
        }

        .plus-btn.active {
            border-color: #2677B8;
            color: #2677B8;
            transform: rotate(45deg);
        }

        .plus-btn svg {
            width: 18px;
            height: 18px;
        }

        .plus-dropdown {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 0;
            min-width: 180px;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transform: translateY(6px);
            pointer-events: none;
            transition: all 0.18s cubic-bezier(0.32, 0.72, 0, 1);
            z-index: 200;
            overflow: hidden;
        }

        .plus-dropdown.open {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .plus-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-main);
            cursor: pointer;
            transition: background 0.15s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .plus-dropdown-item:hover {
            background: var(--gray-100);
        }

        .plus-dropdown-item:first-child {
            border-bottom: 1px solid var(--border-color);
        }

        .plus-dropdown-item svg {
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        .plus-dropdown-item:hover svg {
            color: #2677B8;
        }

        /* ============= Attachment Preview (Above Input) ============= */
        .attachment-preview {
            display: none;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.75rem;
            margin-top: 0;
            background: var(--gray-100);
            border-radius: 0.75rem;
            font-size: 0.8rem;
            color: var(--text-main);
            animation: fadeInUp 0.2s ease-out;
            border: 1px solid var(--border-color);
        }

        .attachment-preview.visible {
            display: flex;
        }

        .attachment-preview-icon {
            width: 36px;
            height: 36px;
            border-radius: 0.5rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .attachment-preview-icon:hover {
            transform: scale(1.06);
            box-shadow: var(--shadow-sm);
        }

        .attachment-preview-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .attachment-preview-icon svg {
            width: 18px;
            height: 18px;
            color: var(--text-muted);
        }

        .attachment-preview-name {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }

        .attachment-preview-size {
            color: var(--text-muted);
            font-size: 0.7rem;
            flex-shrink: 0;
        }

        .attachment-remove-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: none;
            background: var(--gray-200);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .attachment-remove-btn:hover {
            background: #ef4444;
            color: #fff;
        }

        .attachment-remove-btn svg {
            width: 12px;
            height: 12px;
        }

        /* ============= Image Lightbox Modal ============= */
        .image-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.88);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s cubic-bezier(0.32, 0.72, 0, 1), visibility 0.25s;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .image-lightbox.open {
            opacity: 1;
            visibility: visible;
        }

        .image-lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .image-lightbox-img {
            max-width: 90vw;
            max-height: 82vh;
            border-radius: 0.75rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            object-fit: contain;
            user-select: none;
            animation: zoomIn 0.25s ease-out;
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.92);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .image-lightbox-caption {
            color: #f3f4f6;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.75rem;
            text-align: center;
            max-width: 80vw;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .image-lightbox-close {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, transform 0.15s;
            z-index: 2001;
        }

        .image-lightbox-close:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: scale(1.08);
        }

        /* ============= Drag & Drop Overlay ============= */
        .drag-drop-overlay {
            position: absolute;
            inset: 0.5rem;
            background: rgba(38, 119, 184, 0.08);
            border: 2px dashed #2677B8;
            border-radius: 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            color: #2677B8;
            font-weight: 600;
            font-size: 1.1rem;
            z-index: 600;
            opacity: 0;
            pointer-events: none;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            transition: opacity 0.2s ease;
        }

        .drag-drop-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .drag-drop-overlay svg {
            width: 44px;
            height: 44px;
        }

        [data-theme="dark"] .drag-drop-overlay {
            background: rgba(38, 119, 184, 0.18);
            border-color: #3b82f6;
            color: #60a5fa;
        }

        .send-btn {
            position: relative;
            padding: 0.5rem 1.25rem;
            background: transparent;
            color: var(--text-main);
            border: none;
            border-radius: 0.625rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            overflow: hidden;
            z-index: 1;
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .send-btn::before {
            content: '';
            position: absolute;
            z-index: -2;
            top: -150%;
            left: -150%;
            width: 400%;
            height: 400%;
            background: conic-gradient(transparent,
                    #2677B8, var(--primary-red),
                    transparent 30%,
                    #1a508b, var(--primary-red),
                    transparent 50%);
            animation: rotate-border 4s linear infinite;
        }

        .send-btn::after {
            content: '';
            position: absolute;
            z-index: -1;
            inset: 2px;
            background: var(--gray-50);
            border-radius: calc(0.625rem - 2px);
            transition: background 0.2s;
        }

        .send-btn:hover::after {
            background: var(--gray-100);
        }

        .send-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .send-btn:disabled::before {
            animation-play-state: paused;
        }

        .send-btn svg {
            width: 18px;
            height: 18px;
            position: relative;
            z-index: 2;
        }

        .send-btn .btn-text {
            position: relative;
            z-index: 2;
        }

        @keyframes rotate-border {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ============= History Items (reused inside drawer) ============= */
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .history-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: var(--text-main);
        }

        .history-item:hover {
            border-color: #2677B8;
            background: var(--gray-100);
        }

        .history-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
            background: var(--gray-200);
        }

        .history-item-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .history-item-icon.no-thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .history-item-info {
            flex: 1;
            min-width: 0;
        }

        .history-item-query {
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-item-time {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .history-item-status {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 999px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .status-created,
        .status-found_existing {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        [data-theme="dark"] .status-created,
        [data-theme="dark"] .status-found_existing {
            background: rgba(6, 95, 70, 0.2);
            color: #6ee7b7;
        }

        [data-theme="dark"] .status-failed {
            background: rgba(127, 29, 29, 0.2);
            color: #fca5a5;
        }

        /* ============= Welcome State (no messages) ============= */
        .welcome-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 2rem 0;
        }

        /* ============= Responsive ============= */
        @media (max-width: 640px) {
            .agent-page {
                padding: 1rem;
                padding-top: calc(72px + var(--safe-area-inset-top, 0px));
            }

            .chat-bubble {
                max-width: 92%;
                font-size: 0.9375rem;
                line-height: 1.7;
            }

            .chat-bubble.agent,
            .chat-bubble.tutor-explanation {
                max-width: 95%;
                font-size: 0.9375rem;
            }

            .chat-bubble.user {
                font-size: 0.875rem;
            }

            .result-card {
                max-width: 100%;
            }

            .suggestion-chip {
                font-size: 0.75rem;
                padding: 0.375rem 0.75rem;
            }

            .history-drawer {
                width: 85vw;
            }

            .input-area {
                max-width: 100%;
            }

            .quick-starters {
                gap: 0.375rem;
            }

            .quick-starter-btn {
                font-size: 0.75rem;
                padding: 0.375rem 0.75rem;
            }

            .history-icon-btn {
                top: calc(72px + var(--safe-area-inset-top, 0px));
                right: 1rem;
                width: 38px;
                height: 38px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="agent-page" id="agentPage">
        <!-- Drag & Drop Overlay -->
        <div class="drag-drop-overlay" id="dragDropOverlay">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span>Drop image or file here to attach</span>
        </div>

        <!-- History Icon (Top Right) -->
        <button type="button" class="history-icon-btn" id="historyIconBtn" title="Chat History">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            @if(count($allSessions) > 0)
                <span class="history-icon-badge"></span>
            @endif
        </button>

        <!-- Chat Area -->
        <div class="chat-area" id="chatArea">
            <!-- Welcome state shown when no messages -->
            <div class="welcome-state" id="welcomeState">
                <div class="suggestions" id="suggestions">
                    <button class="suggestion-chip" data-suggestion="Roadmap for JHS 3 Science">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 3v13a3 3 0 0 0 6 0V3" />
                            <path d="M8 3h8" />
                            <path d="M12 9h.01" />
                        </svg>
                        Roadmap for JHS 3 Science
                    </button>
                    <button class="suggestion-chip" data-suggestion="Create a quiz on Photosynthesis">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" />
                        </svg>
                        Create a quiz on Photosynthesis
                    </button>
                    <button class="suggestion-chip" data-suggestion="Teach me Quadratic Equations">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20" />
                            <path d="M8 7v6l6-6v6" />
                            <path d="M15 13h5" />
                        </svg>
                        Teach me Quadratic Equations
                    </button>
                    <button class="suggestion-chip" data-suggestion="Roadmap for Core Mathematics">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="20" x="4" y="2" rx="2" />
                            <line x1="8" x2="16" y1="6" y2="6" />
                            <line x1="16" x2="16" y1="14" y2="18" />
                            <path d="M16 10h.01" />
                            <path d="M12 10h.01" />
                            <path d="M8 10h.01" />
                            <path d="M12 14h.01" />
                            <path d="M8 14h.01" />
                            <path d="M12 18h.01" />
                            <path d="M8 18h.01" />
                        </svg>
                        Roadmap for Core Mathematics
                    </button>
                    <button class="suggestion-chip" data-suggestion="Create a quiz on Electricity">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                        </svg>
                        Create a quiz on Electricity
                    </button>
                    <button class="suggestion-chip" data-suggestion="Explain DNA and Genetics">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 15c6.667-6 13.333 0 20-6" />
                            <path d="M9 22c1.798-1.998 2.518-3.995 2.807-5.993" />
                            <path d="M15 2c-1.798 1.998-2.518 3.995-2.807 5.993" />
                            <path d="M17 9l.819 3.276" />
                            <path d="M2 9c6.667 6 13.333 0 20 6" />
                        </svg>
                        Explain DNA and Genetics
                    </button>
                </div>

                <!-- Quick Starter Mode Buttons -->
                <div class="quick-starters" id="quickStarters">
                    <button type="button" class="quick-starter-btn active" data-mode="lesson">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Single Lesson
                    </button>
                    <button type="button" class="quick-starter-btn" data-mode="roadmap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Learning Roadmap <span class="roadmap-tag">GES</span>
                    </button>
                    <button type="button" class="quick-starter-btn" data-mode="quiz">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Interactive Quiz
                    </button>
                </div>

                <!-- Requests Badge -->
                <div class="requests-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="count" id="remainingCount">{{ $remainingRequests }}</span> requests remaining today
                </div>

                <!-- Input Area (centered in welcome state) -->
                <div class="input-area" id="inputArea">
                    <!-- Attachment Preview (Above text field) -->
                    <div class="attachment-preview" id="attachmentPreview">
                        <div class="attachment-preview-icon" id="attachmentIcon" title="Click to view larger">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="attachment-preview-name" id="attachmentName"></span>
                        <span class="attachment-preview-size" id="attachmentSize"></span>
                        <button type="button" class="attachment-remove-btn" id="attachmentRemoveBtn" title="Remove attachment">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form class="input-wrapper" id="agentForm">
                        <!-- Plus Button -->
                        <div class="plus-btn-wrapper">
                            <button type="button" class="plus-btn" id="plusBtn" title="Attach image or file">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                            <!-- Plus Dropdown -->
                            <div class="plus-dropdown" id="plusDropdown">
                                <button type="button" class="plus-dropdown-item" id="attachImageBtn">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Image
                                </button>
                                <button type="button" class="plus-dropdown-item" id="attachFileBtn">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    File
                                </button>
                            </div>
                        </div>
                        <div class="input-inner">
                            <input type="text" id="agentInput"
                                placeholder=" Ask for a lesson... e.g. 'Teach me about photosynthesis'" maxlength="500"
                                autocomplete="off" required>
                            <div class="fake-caret"></div>
                        </div>
                        <button type="submit" class="send-btn" id="sendBtn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <span class="btn-text">Ask AI</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Typing indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>

        <!-- Hidden file inputs -->
        <input type="file" id="imageFileInput" accept="image/*" style="display: none;">
        <input type="file" id="docFileInput" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx" style="display: none;">
    </div>

    <!-- History Drawer Scrim -->
    <div class="history-drawer-scrim" id="historyDrawerScrim"></div>

    <!-- History Drawer -->
    <div class="history-drawer" id="historyDrawer">
        <div class="drawer-header">
            <h3>Chat History</h3>
            <button type="button" class="drawer-close-btn" id="drawerCloseBtn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <button type="button" class="drawer-new-chat-btn" id="newChatBtn">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Chat
        </button>
        <div class="drawer-body">
            @if(count($allSessions) > 0)
                <div class="history-list" id="historyList">
                    @foreach($allSessions as $index => $session)
                        <a href="#" class="history-item"
                            data-session-id="{{ $session->id }}">
                            <div class="history-item-icon no-thumb">
                                💬
                            </div>
                            <div class="history-item-info">
                                <div class="history-item-query">
                                    {{ $session->title ?: 'New Chat' }}
                                </div>
                                <div class="history-item-time">{{ $session->updated_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-muted small text-center py-3">No recent chats yet.</div>
            @endif
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div class="image-lightbox" id="imageLightbox">
        <button type="button" class="image-lightbox-close" id="lightboxCloseBtn" title="Close (Esc)">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div class="image-lightbox-content">
            <img src="" alt="Expanded View" class="image-lightbox-img" id="lightboxImg">
            <div class="image-lightbox-caption" id="lightboxCaption"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" nonce="{{ request()->attributes->get('csp_nonce') }}">
        import { renderMathInElement } from "https://unpkg.com/mathlive?module";
        window.renderMathInElement = renderMathInElement;
    </script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let isProcessing = false;
        let remaining = {{ $remainingRequests }};
        let currentMode = 'lesson';
        let activeContextId = null;
        let activeSessionId = {{ $chatSession ? $chatSession->id : 'null' }};
        let chatHistory = {!! json_encode($chatMessages->map(function($msg) { return ['role' => $msg->role, 'text' => $msg->text, 'metadata' => $msg->metadata]; })->toArray()) !!};
        let currentAttachment = null;
        let currentAttachmentUrl = null;

        /* ============= Image Lightbox Modal ============= */
        function openImageLightbox(src, caption) {
            if (!src) return;
            var lightbox = document.getElementById('imageLightbox');
            var img = document.getElementById('lightboxImg');
            var cap = document.getElementById('lightboxCaption');
            if (lightbox && img) {
                img.src = src;
                if (cap) cap.textContent = caption || '';
                lightbox.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImageLightbox() {
            var lightbox = document.getElementById('imageLightbox');
            if (lightbox) {
                lightbox.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        /* ============= Mode Selection ============= */
        function setMode(mode) {
            currentMode = mode;
            // Update quick starters
            document.querySelectorAll('.quick-starter-btn').forEach(function(btn) {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });

            // Update placeholder
            var input = document.getElementById('agentInput');
            if (mode === 'roadmap') {
                input.placeholder = " What would you like a learning roadmap for? e.g. 'JHS 3 Science'";
            } else if (mode === 'quiz') {
                input.placeholder = " What topic do you want a quiz on? e.g. 'Photosynthesis'";
            } else {
                input.placeholder = " Ask for a lesson... e.g. 'Teach me about photosynthesis'";
            }
            input.focus();
        }

        /* ============= History Drawer ============= */
        function openDrawer() {
            document.getElementById('historyDrawer').classList.add('open');
            document.getElementById('historyDrawerScrim').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            document.getElementById('historyDrawer').classList.remove('open');
            document.getElementById('historyDrawerScrim').classList.remove('open');
            document.body.style.overflow = '';
        }

        /* ============= Plus Dropdown ============= */
        function togglePlusDropdown(forceClose) {
            var dropdown = document.getElementById('plusDropdown');
            var btn = document.getElementById('plusBtn');
            var isOpen = dropdown.classList.contains('open');

            if (forceClose || isOpen) {
                dropdown.classList.remove('open');
                btn.classList.remove('active');
            } else {
                dropdown.classList.add('open');
                btn.classList.add('active');
            }
        }

        /* ============= Attachment Handling ============= */
        function handleFileSelect(file) {
            if (!file) return;

            currentAttachment = file;
            currentAttachmentUrl = null;
            var preview = document.getElementById('attachmentPreview');
            var nameEl = document.getElementById('attachmentName');
            var sizeEl = document.getElementById('attachmentSize');
            var iconEl = document.getElementById('attachmentIcon');

            nameEl.textContent = file.name;
            var sizeMB = (file.size / 1024 / 1024).toFixed(1);
            var sizeKB = (file.size / 1024).toFixed(0);
            sizeEl.textContent = file.size > 1048576 ? sizeMB + ' MB' : sizeKB + ' KB';

            // Show image preview if it's an image
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    currentAttachmentUrl = e.target.result;
                    iconEl.innerHTML = '<img src="' + e.target.result + '" alt="' + file.name + '" title="Click to view full image">';
                };
                reader.readAsDataURL(file);
            } else {
                currentAttachmentUrl = null;
                iconEl.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>';
            }

            preview.classList.add('visible');
            togglePlusDropdown(true);
        }

        function removeAttachment() {
            currentAttachment = null;
            currentAttachmentUrl = null;
            var preview = document.getElementById('attachmentPreview');
            preview.classList.remove('visible');
            document.getElementById('imageFileInput').value = '';
            document.getElementById('docFileInput').value = '';
        }

        /* ============= Chat Helpers ============= */
        function formatDuration(seconds) {
            if (!seconds) return '';
            var m = Math.floor(seconds / 60);
            var s = seconds % 60;
            return m + ':' + String(s).padStart(2, '0');
        }

        function formatLinks(text) {
            if (!text) return '';
            // 1. Escape HTML to prevent XSS
            var escaped = String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            // 2. Parse Markdown-style bolding: **text**
            escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

            // 3. Parse Markdown-style links: [Link Text](url)
            escaped = escaped.replace(/\[([^\]]+)\]\(((?:https?:\/\/[^\s)]+|\/[^\s)]*?))\)/gi, function (match, label, url) {
                return '<a href="' + url + '" target="_blank" rel="noopener noreferrer" style="color: #2677B8; text-decoration: underline; font-weight: 600;">' + label + '</a>';
            });

            // 4. Parse raw URLs (http:// or https://)
            escaped = escaped.replace(/(?<!href=")(https?:\/\/[^\s<)]+)/gi, function (url) {
                return '<a href="' + url + '" target="_blank" rel="noopener noreferrer" style="color: #2677B8; text-decoration: underline; font-weight: 600;">' + url + '</a>';
            });

            return escaped;
        }

        function markdownToHtml(text) {
            if (!text) return '';
            var lines = text.split('\n');
            var html = '';
            var inList = null; // 'ul', 'ol', or null

            lines.forEach(function(line) {
                var trimmed = line.trim();
                if (!trimmed) {
                    if (inList) {
                        html += '</' + inList + '>';
                        inList = null;
                    }
                    return;
                }

                // Check for bullet list item: starts with - or * followed by space
                var bulletMatch = trimmed.match(/^[-*]\s+(.*)/);
                // Check for numbered list item: starts with digit(s) followed by dot and space
                var numberMatch = trimmed.match(/^(\d+)\.\s+(.*)/);

                if (bulletMatch) {
                    if (inList !== 'ul') {
                        if (inList) html += '</' + inList + '>';
                        html += '<ul class="explanation-list">';
                        inList = 'ul';
                    }
                    html += '<li>' + formatLinks(bulletMatch[1]) + '</li>';
                } else if (numberMatch) {
                    if (inList !== 'ol') {
                        if (inList) html += '</' + inList + '>';
                        html += '<ol class="explanation-list">';
                        inList = 'ol';
                    }
                    html += '<li>' + formatLinks(numberMatch[2]) + '</li>';
                } else {
                    if (inList) {
                        html += '</' + inList + '>';
                        inList = null;
                    }
                    html += '<p>' + formatLinks(trimmed) + '</p>';
                }
            });

            if (inList) {
                html += '</' + inList + '>';
            }

            return html;
        }

        function renderMath(element) {
            if (window.renderMathInElement) {
                window.renderMathInElement(element, {
                    TeX: {
                        delimiters: {
                            inline: [
                                ['$', '$'],
                                ['\\(', '\\)']
                            ],
                            display: [
                                ['$$', '$$'],
                                ['\\[', '\\]']
                            ]
                        }
                    }
                });
            }
        }

        function switchToChatMode() {
            var welcomeState = document.getElementById('welcomeState');
            var agentPage = document.getElementById('agentPage');
            var inputArea = document.getElementById('inputArea');
            var chatArea = document.getElementById('chatArea');

            if (welcomeState) welcomeState.style.display = 'none';
            agentPage.classList.add('chat-active');

            // Move input area out of welcome state and append to agent page
            if (inputArea.parentElement === welcomeState) {
                agentPage.appendChild(inputArea);
            }
        }

        function addBubble(text, type, attachment) {
            if (!text && !attachment) return;

            switchToChatMode();

            var chatArea = document.getElementById('chatArea');
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble ' + type;

            var attachmentHtml = '';
            if (attachment) {
                var isImage = attachment.is_image || 
                    (attachment.mime_type && attachment.mime_type.startsWith('image/')) || 
                    (attachment.type && attachment.type.startsWith('image/'));
                
                var fileUrl = attachment.url || (attachment instanceof File ? URL.createObjectURL(attachment) : '#');
                var fileName = attachment.name || attachment.original_name || 'File';
                var sizeText = '';
                if (attachment.size) {
                    sizeText = attachment.size > 1048576 
                        ? (attachment.size / 1048576).toFixed(1) + ' MB' 
                        : (attachment.size / 1024).toFixed(0) + ' KB';
                }

                if (isImage && fileUrl && fileUrl !== '#') {
                    attachmentHtml = 
                        '<div class="bubble-attachment bubble-attachment-image">' +
                            '<img src="' + fileUrl + '" alt="' + fileName + '" loading="lazy" class="lightbox-trigger" data-lightbox-src="' + fileUrl + '" data-lightbox-caption="' + fileName + '" title="Click to view full image">' +
                        '</div>';
                } else {
                    attachmentHtml = 
                        '<div class="bubble-attachment bubble-attachment-file">' +
                            '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>' +
                            '<span class="bubble-file-name" title="' + fileName + '">' + fileName + '</span>' +
                            (sizeText ? '<span class="bubble-file-size">(' + sizeText + ')</span>' : '') +
                        '</div>';
                }
            }

            if (type === 'tutor-explanation') {
                var parsedBody = markdownToHtml(text);
                bubble.innerHTML =
                    attachmentHtml +
                    '<div class="explanation-title">' +
                        '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>' +
                        'Topic Explanation' +
                    '</div>' +
                    '<div class="explanation-body">' + parsedBody + '</div>' +
                    '<button class="tutor-explanation-toggle">Show More</button>';

                chatArea.appendChild(bubble);
                renderMath(bubble);

                // Handle toggle visibility
                setTimeout(function() {
                    var toggle = bubble.querySelector('.tutor-explanation-toggle');
                    if (bubble.scrollHeight > 160) {
                        toggle.style.display = 'block';
                    }
                    toggle.addEventListener('click', function() {
                        bubble.classList.toggle('expanded');
                        toggle.textContent = bubble.classList.contains('expanded') ? 'Show Less' : 'Show More';
                    });
                }, 100);
            } else {
                bubble.innerHTML = attachmentHtml + (text ? markdownToHtml(text) : '');
                chatArea.appendChild(bubble);
                renderMath(bubble);
            }
            chatArea.scrollTop = chatArea.scrollHeight;
        }

        function addResultCard(data) {
            var chatArea = document.getElementById('chatArea');

            if (data.type === 'quiz') {
                var card = document.createElement('div');
                card.className = 'quiz-card';

                card.innerHTML =
                    '<div class="quiz-card-header">' +
                        '<div class="quiz-card-icon">' +
                            '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>' +
                            '</svg>' +
                        '</div>' +
                        '<h4 class="quiz-card-title">' + data.topic + '</h4>' +
                    '</div>' +
                    '<div class="quiz-card-meta">' +
                        '<span class="result-card-badge ' + (data.is_existing ? 'existing' : 'new') + '">' + (data.is_existing ? '📚 Library Quiz' : '✨ Freshly Created') + '</span>' +
                        '<span><i class="fas fa-tag"></i> ' + (data.quiz_type === 'essay' ? 'Essay' : 'MCQ') + '</span>' +
                        '<span><i class="fas fa-brain"></i> GES</span>' +
                    '</div>' +
                    '<a href="' + data.quiz_url + '" class="quiz-btn">' +
                        '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2V15H6L11 19V5Z"></path>' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"></path>' +
                        '</svg>' +
                        'Start Quiz Now' +
                    '</a>';
                chatArea.appendChild(card);
            } else if (data.roadmap) {
                var card = document.createElement('div');
                card.className = 'roadmap-card';

                var stepsHtml = '';
                data.roadmap.steps.forEach(function(step, index) {
                    stepsHtml +=
                        '<a href="' + (step.lesson_url || '#') + '" class="roadmap-step"' + (!step.lesson_url ? ' onclick="return false"' : '') + '>' +
                            '<div class="step-number">' + (index + 1) + '</div>' +
                            '<div class="step-content">' +
                                '<div class="step-title">' + step.title + '</div>' +
                                '<div class="step-desc">' + step.description + '</div>' +
                            '</div>' +
                        '</a>';
                });

                card.innerHTML =
                    '<div class="roadmap-header">' +
                        '<div class="roadmap-badge">' +
                            '<svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 1.55l7.834 3.35a1 1 0 01.666.92v6.57a1 1 0 01-.17.55l-8 11a1 1 0 01-1.66 0l-8-11a1 1 0 01-1.66 0l-8-11a1 1 0 01-.17-.55V5.82a1 1 0 01.666-.92zM10 3.24l-6 2.57v5.61l6 8.25 6-8.25v-5.61l-6-2.57z" clip-rule="evenodd"></path></svg>' +
                            'GES Syllabus Aligned • ' + (data.is_existing ? '📚 Library' : '✨ New') +
                        '</div>' +
                        '<h3>' + data.roadmap.roadmap_title + '</h3>' +
                        '<p style="font-size: 0.75rem; opacity: 0.9; margin: 0;">' + data.roadmap.description + '</p>' +
                    '</div>' +
                    '<div class="roadmap-body">' +
                        '<div class="roadmap-steps">' +
                            stepsHtml +
                        '</div>' +
                    '</div>';
                chatArea.appendChild(card);
            } else if (data.quiz_url || data.type === 'quiz') {
                var card = document.createElement('div');
                card.className = 'quiz-card';

                card.innerHTML =
                    '<div class="quiz-card-header">' +
                        '<div class="quiz-card-icon">' +
                            '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>' +
                        '</div>' +
                        '<h4 class="quiz-card-title">' + (data.topic || data.title) + '</h4>' +
                    '</div>' +
                    '<div class="quiz-card-meta">' +
                        '<span>' + (data.quiz_type === 'essay' ? '📝 Structured Essay' : '✅ Multiple Choice') + '</span>' +
                    '</div>' +
                    '<a href="' + data.quiz_url + '" class="quiz-btn">' +
                        'Start Quiz Now' +
                    '</a>';
                chatArea.appendChild(card);
            } else {
                var card = document.createElement('div');
                card.className = 'result-card';

                var durationDisplay = data.duration ? formatDuration(data.duration) : '';
                var badgeClass = data.is_existing ? 'existing' : 'new';
                var badgeText = data.is_existing ? '📚 Already in Library' : '✨ Freshly Found';

                card.innerHTML =
                    (data.thumbnail ?
                    '<div class="result-card-thumb">' +
                        '<img src="' + data.thumbnail + '" alt="' + (data.title || data.topic) + '" loading="lazy">' +
                        (durationDisplay ? '<span class="result-card-duration">' + durationDisplay + '</span>' : '') +
                    '</div>' : '') +
                    '<div class="result-card-body">' +
                        '<h4>' + (data.title || data.topic) + '</h4>' +
                        '<div class="result-card-meta">' +
                            '<span class="result-card-badge ' + badgeClass + '">' + badgeText + '</span>' +
                        '</div>' +
                        '<a href="' + data.lesson_url + '" class="watch-btn">' +
                            '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                            '</svg>' +
                            'Watch Lesson' +
                        '</a>' +
                    '</div>';
                chatArea.appendChild(card);
            }

            chatArea.scrollTop = chatArea.scrollHeight;
        }

        function showRoadmapInChat(roadmapData, query) {
            query = query || 'Roadmap Request';
            switchToChatMode();
            addBubble(query, 'user');
            addResultCard({
                success: true,
                type: 'roadmap',
                roadmap: roadmapData,
                is_existing: true,
                message: 'Here is the learning roadmap I designed for you.'
            });
        }

        function showTyping(show) {
            document.getElementById('typingIndicator').classList.toggle('active', show);
            if (show) {
                var chatArea = document.getElementById('chatArea');
                chatArea.scrollTop = chatArea.scrollHeight;
            }
        }

        function setLoading(loading) {
            isProcessing = loading;
            var sendBtn = document.getElementById('sendBtn');
            var input = document.getElementById('agentInput');
            sendBtn.disabled = loading;
            input.disabled = loading;

            var btnText = currentMode === 'roadmap' ? 'Designing Roadmap...' : (currentMode === 'quiz' ? 'Generating Quiz...' : 'Searching...');
            sendBtn.querySelector('.btn-text').textContent = loading ? btnText : 'Ask AI';
        }

        function askSuggestion(el, text) {
            var query = text || el.textContent.replace(/^[\u{1F000}-\u{1FFFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]\s*/u, '').trim();
            document.getElementById('agentInput').value = query;
            handleSubmit(new Event('submit'));
        }

        async function handleSubmit(e) {
            e.preventDefault();

            if (isProcessing) return false;

            var input = document.getElementById('agentInput');
            var query = input.value.trim();
            var attachmentToSend = currentAttachment;

            if (!query && !attachmentToSend) return false;

            if (remaining <= 0) {
                addBubble('You\'ve used all your AI requests for today. Come back tomorrow!', 'agent error');
                return false;
            }

            // Show user message with attachment preview
            addBubble(query, 'user', attachmentToSend);
            input.value = '';

            // Construct client-side representation for chat history
            var userMsgText = query || (attachmentToSend ? 'Attached: ' + attachmentToSend.name : '');
            var userMeta = attachmentToSend ? {
                attachment: {
                    name: attachmentToSend.name,
                    size: attachmentToSend.size,
                    type: attachmentToSend.type,
                    is_image: attachmentToSend.type && attachmentToSend.type.startsWith('image/'),
                    url: URL.createObjectURL(attachmentToSend)
                }
            } : null;

            chatHistory.push({ role: 'user', text: userMsgText, metadata: userMeta });
            if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);

            // Clear attachment selection
            removeAttachment();

            setLoading(true);
            showTyping(true);

            try {
                var formData = new FormData();
                if (query) formData.append('query', query);
                formData.append('type', currentMode);
                if (activeContextId) formData.append('context_id', activeContextId);
                if (activeSessionId) formData.append('session_id', activeSessionId);
                
                // Pass text-only history array for backend
                formData.append('messages', JSON.stringify(chatHistory.map(function(m) {
                    return { role: m.role, text: m.text };
                })));

                if (attachmentToSend) {
                    formData.append('attachment', attachmentToSend);
                }

                var response = await fetch('{{ route("api.agent.ask") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                var data = await response.json();

                showTyping(false);

                // Show result card if successful
                if (data.success) {
                    if (data.request_id) {
                        activeContextId = data.request_id;
                    }
                    if (data.session_id) {
                        activeSessionId = data.session_id;
                    }

                    if (data.message) {
                        addBubble(data.message, 'ai');
                        // Add AI response to history
                        var meta = null;
                        if (data.type) {
                            meta = {
                                type: data.type,
                                topic: data.topic,
                                lesson_url: data.lesson_url,
                                quiz_url: data.quiz_url,
                                quiz_type: data.quiz_type,
                                roadmap: data.roadmap,
                                is_existing: data.is_existing,
                                thumbnail: data.thumbnail
                            };
                        }
                        chatHistory.push({ role: 'model', text: data.message, metadata: meta });
                    }
                    if (data.summary) {
                        addBubble(data.summary, 'tutor-explanation');
                    }

                    if (data.suggested_actions && data.suggested_actions.length > 0) {
                        var chatArea = document.getElementById('chatArea');
                        var actionsDiv = document.createElement('div');
                        actionsDiv.className = 'suggestions';
                        actionsDiv.style.marginTop = '-1rem';
                        actionsDiv.style.marginBottom = '1.5rem';
                        actionsDiv.style.justifyContent = 'flex-start';

                        data.suggested_actions.forEach(function(action) {
                            var btn = document.createElement('button');
                            btn.className = 'suggestion-chip';
                            // Icon based on the label for better UI
                            var iconSvg = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>';
                            if (action.label.toLowerCase().includes('essay')) {
                                iconSvg = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2V15H6L11 19V5Z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"></path></svg>';
                            }

                            btn.innerHTML = iconSvg + ' ' + action.label;
                            btn.addEventListener('click', function() {
                                if (action.mode) setMode(action.mode);
                                document.getElementById('agentInput').value = action.query;
                                handleSubmit(new Event('submit'));
                            });
                            actionsDiv.appendChild(btn);
                        });

                        chatArea.appendChild(actionsDiv);
                        chatArea.scrollTop = chatArea.scrollHeight;
                    }

                    if (data.lesson_url || data.quiz_url || data.roadmap) {
                        addResultCard(data);
                    }
                } else {
                    addBubble(data.message, 'agent error');
                }

                // Update remaining count
                remaining = Math.max(0, remaining - 1);
                document.getElementById('remainingCount').textContent = remaining;

            } catch (error) {
                showTyping(false);
                addBubble('Oops! Something went wrong. Please try again.', 'agent error');
                console.error('Agent error:', error);
            }

            setLoading(false);
            return false;
        }

        // CSP Compliant Event Listeners
        document.addEventListener('DOMContentLoaded', function () {
            var agentForm = document.getElementById('agentForm');
            var quickStarters = document.getElementById('quickStarters');
            var suggestions = document.getElementById('suggestions');
            var historyList = document.getElementById('historyList');

            // Render Initial Chat History
            if (chatHistory && chatHistory.length > 0) {
                chatHistory.forEach(function(msg) {
                    var attachment = msg.metadata && msg.metadata.attachment ? msg.metadata.attachment : null;
                    addBubble(msg.text, msg.role === 'model' ? 'ai' : 'user', attachment);
                    if (msg.metadata && msg.metadata.type) {
                        addResultCard(msg.metadata);
                    }
                });
            }

            if (agentForm) {
                agentForm.addEventListener('submit', handleSubmit);
            }

            // Quick Starter mode buttons
            if (quickStarters) {
                quickStarters.addEventListener('click', function (e) {
                    var btn = e.target.closest('.quick-starter-btn');
                    if (btn) setMode(btn.dataset.mode);
                });
            }

            if (suggestions) {
                suggestions.addEventListener('click', function (e) {
                    var btn = e.target.closest('.suggestion-chip');
                    if (btn) askSuggestion(btn);
                });
            }

            // History Drawer
            var historyIconBtn = document.getElementById('historyIconBtn');
            var drawerCloseBtn = document.getElementById('drawerCloseBtn');
            var historyDrawerScrim = document.getElementById('historyDrawerScrim');

            if (historyIconBtn) {
                historyIconBtn.addEventListener('click', openDrawer);
            }
            if (drawerCloseBtn) {
                drawerCloseBtn.addEventListener('click', closeDrawer);
            }
            if (historyDrawerScrim) {
                historyDrawerScrim.addEventListener('click', closeDrawer);
            }

            // History list click (inside drawer)
            if (historyList) {
                historyList.addEventListener('click', async function (e) {
                    var item = e.target.closest('.history-item');
                    if (item) {
                        e.preventDefault();
                        var sessionId = item.dataset.sessionId;
                        if (!sessionId) return;

                        // Close drawer
                        closeDrawer();

                        // Switch to chat mode
                        switchToChatMode();

                        var chatArea = document.getElementById('chatArea');
                        chatArea.innerHTML = '';

                        setLoading(true);
                        showTyping(true);

                        try {
                            var response = await fetch('/api/agent/session/' + sessionId);
                            var data = await response.json();

                            if (data.success) {
                                activeSessionId = data.session_id;
                                chatHistory = data.messages;

                                chatHistory.forEach(function(msg) {
                                    var attachment = msg.metadata && msg.metadata.attachment ? msg.metadata.attachment : null;
                                    addBubble(msg.text, msg.role === 'model' ? 'ai' : 'user', attachment);
                                    if (msg.metadata && msg.metadata.type) {
                                        addResultCard(msg.metadata);
                                    }
                                });
                            }
                        } catch (error) {
                            console.error('Error loading session:', error);
                            addBubble('Failed to load chat history.', 'agent error');
                        }

                        showTyping(false);
                        setLoading(false);
                    }
                });
            }

            // New Chat button (inside drawer)
            var newChatBtn = document.getElementById('newChatBtn');
            if (newChatBtn) {
                newChatBtn.addEventListener('click', function() {
                    activeSessionId = null;
                    chatHistory = [];
                    closeDrawer();

                    var chatArea = document.getElementById('chatArea');
                    var agentPage = document.getElementById('agentPage');
                    var welcomeState = document.getElementById('welcomeState');
                    var inputArea = document.getElementById('inputArea');

                    // Reset to welcome state
                    agentPage.classList.remove('chat-active');
                    chatArea.innerHTML = '';

                    // Move input back inside welcome state
                    if (welcomeState) {
                        welcomeState.style.display = 'flex';
                        welcomeState.appendChild(inputArea);
                    }
                    chatArea.appendChild(welcomeState);
                });
            }

            // Plus button dropdown
            var plusBtn = document.getElementById('plusBtn');
            if (plusBtn) {
                plusBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    togglePlusDropdown();
                });
            }

            // Close plus dropdown on outside click
            document.addEventListener('click', function(e) {
                var dropdown = document.getElementById('plusDropdown');
                var plusBtnEl = document.getElementById('plusBtn');
                if (dropdown && dropdown.classList.contains('open') && !dropdown.contains(e.target) && e.target !== plusBtnEl) {
                    togglePlusDropdown(true);
                }
            });

            // Attach Image
            var attachImageBtn = document.getElementById('attachImageBtn');
            var imageFileInput = document.getElementById('imageFileInput');
            if (attachImageBtn && imageFileInput) {
                attachImageBtn.addEventListener('click', function() {
                    imageFileInput.click();
                });
                imageFileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        handleFileSelect(this.files[0]);
                    }
                });
            }

            // Attach File
            var attachFileBtn = document.getElementById('attachFileBtn');
            var docFileInput = document.getElementById('docFileInput');
            if (attachFileBtn && docFileInput) {
                attachFileBtn.addEventListener('click', function() {
                    docFileInput.click();
                });
                docFileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        handleFileSelect(this.files[0]);
                    }
                });
            }

            // Remove attachment
            var attachmentRemoveBtn = document.getElementById('attachmentRemoveBtn');
            if (attachmentRemoveBtn) {
                attachmentRemoveBtn.addEventListener('click', removeAttachment);
            }

            // Drag and Drop support
            var agentPage = document.getElementById('agentPage');
            var dragDropOverlay = document.getElementById('dragDropOverlay');
            var dragCounter = 0;

            if (agentPage && dragDropOverlay) {
                window.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    dragCounter++;
                    if (e.dataTransfer && e.dataTransfer.types && Array.from(e.dataTransfer.types).includes('Files')) {
                        dragDropOverlay.classList.add('active');
                    }
                });

                window.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dragCounter--;
                    if (dragCounter <= 0) {
                        dragCounter = 0;
                        dragDropOverlay.classList.remove('active');
                    }
                });

                window.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    if (e.dataTransfer) {
                        e.dataTransfer.dropEffect = 'copy';
                    }
                });

                window.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dragCounter = 0;
                    dragDropOverlay.classList.remove('active');

                    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        var file = e.dataTransfer.files[0];
                        handleFileSelect(file);
                    }
                });
            }

            // Clipboard Paste support (Ctrl+V / Cmd+V for images and files)
            window.addEventListener('paste', function(e) {
                if (!e.clipboardData || !e.clipboardData.items) return;

                var items = e.clipboardData.items;
                for (var i = 0; i < items.length; i++) {
                    if (items[i].kind === 'file') {
                        var file = items[i].getAsFile();
                        if (file) {
                            // If it's a pasted screenshot/image, give it a friendly timestamped name
                            if (file.type.startsWith('image/') && (!file.name || file.name === 'image.png')) {
                                file = new File([file], 'screenshot-' + Date.now() + '.png', { type: file.type || 'image/png' });
                            }
                            handleFileSelect(file);
                            break;
                        }
                    }
                }
            });

            // Image Lightbox Triggers
            var attachmentIcon = document.getElementById('attachmentIcon');
            if (attachmentIcon) {
                attachmentIcon.addEventListener('click', function() {
                    if (currentAttachmentUrl) {
                        openImageLightbox(currentAttachmentUrl, currentAttachment ? currentAttachment.name : 'Attached Image');
                    }
                });
            }

            // Click on any attached image in chat area to view in lightbox
            var chatArea = document.getElementById('chatArea');
            if (chatArea) {
                chatArea.addEventListener('click', function(e) {
                    var trigger = e.target.closest('.lightbox-trigger');
                    if (trigger) {
                        e.preventDefault();
                        var src = trigger.dataset.lightboxSrc || trigger.src;
                        var caption = trigger.dataset.lightboxCaption || trigger.alt || '';
                        openImageLightbox(src, caption);
                    }
                });
            }

            // Close lightbox
            var lightboxCloseBtn = document.getElementById('lightboxCloseBtn');
            var imageLightbox = document.getElementById('imageLightbox');
            if (lightboxCloseBtn) {
                lightboxCloseBtn.addEventListener('click', closeImageLightbox);
            }
            if (imageLightbox) {
                imageLightbox.addEventListener('click', function(e) {
                    if (e.target === imageLightbox || e.target.closest('#lightboxCloseBtn')) {
                        closeImageLightbox();
                    }
                });
            }
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImageLightbox();
                }
            });

            // Auto-focus input
            var agentInput = document.getElementById('agentInput');
            if (agentInput) agentInput.focus();
        });
    </script>
@endpush