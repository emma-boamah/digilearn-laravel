@extends('layouts.dashboard-components')

@section('head')
    <title>AI Tutor - {{ config('app.name') }}</title>
    <meta name="description" content="Ask the AI Tutor to find you the perfect lesson on any topic.">
@endsection

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* ============= Agent Page Container ============= */
    .agent-page {
        padding: 1.5rem;
        max-width: 900px;
        margin: 0 auto;
        min-height: calc(100vh - 80px);
        display: flex;
        flex-direction: column;
    }

    /* ============= Header ============= */
    .agent-header {
        text-align: center;
        padding: 2rem 1rem 1.5rem;
    }

    .agent-logo {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, #2677B8 0%, #1a508b 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 32px rgba(38, 119, 184, 0.3);
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    .agent-logo svg {
        width: 36px;
        height: 36px;
        color: #fff;
    }

    .agent-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .agent-header p {
        font-size: 0.875rem;
        color: var(--text-muted);
        max-width: 460px;
        margin: 0 auto;
    }

    .requests-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        margin-top: 0.75rem;
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
        font-size: 0.9rem;
        line-height: 1.5;
        animation: slideUp 0.3s ease-out;
        word-wrap: break-word;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chat-bubble.user {
        align-self: flex-end;
        background: linear-gradient(135deg, #2677B8 0%, #1a508b 100%);
        color: #fff;
        border-bottom-right-radius: 0.375rem;
    }

    .chat-bubble.agent {
        align-self: flex-start;
        background: var(--bg-surface);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-bottom-left-radius: 0.375rem;
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

    .typing-indicator.active { display: flex; }

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

    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes bounce {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
        40% { transform: scale(1); opacity: 1; }
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
        background: rgba(0,0,0,0.8);
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
        background: #fff;
        border: 1px solid var(--border-color);
        color: var(--text-main);
        max-width: 90%;
        padding: 1.5rem;
        padding-bottom: 2.5rem; /* Space for the toggle */
        font-size: 0.95rem;
        line-height: 1.8;
        box-shadow: var(--shadow-sm);
        border-radius: 1rem;
        margin-bottom: 2rem;
        position: relative;
        max-height: 180px; /* Approx 4-5 lines */
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    [data-theme="dark"] .chat-bubble.tutor-explanation {
        background: var(--bg-surface);
        border-color: #374151;
    }

    .explanation-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.8;
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
        background: linear-gradient(to top, #fff 70%, transparent);
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
        background: #fff;
    }

    .chat-bubble.tutor-explanation p {
        margin-bottom: 1rem;
    }

    .chat-bubble.tutor-explanation p:last-child {
        margin-bottom: 0;
    }

    .explanation-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        color: #2677B8;
        margin-bottom: 0.75rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
        background: rgba(255,255,255,0.2);
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
        color: #d97706;
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
        background: #d97706;
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.625rem;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        width: 100%;
        justify-content: center;
    }

    .quiz-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #fff;
    }

    /* ============= Roadmap Toggle ============= */
    .mode-toggle {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .mode-btn {
        padding: 0.375rem 0.875rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }

    .mode-btn.active {
        background: #2677B8;
        border-color: #2677B8;
        color: #fff;
    }

    .roadmap-tag {
        font-size: 0.6rem;
        padding: 1px 4px;
        border-radius: 4px;
        background: #2677B8;
        color: #fff;
        font-weight: 700;
        margin-left: 4px;
    }
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
    }

    .suggestion-chip:hover {
        background: #F3F7FB;
        color: #2677B8;
        border-color: #2677B8;
        transform: translateY(-1px);
    }

    /* ============= Input Area ============= */
    .input-area {
        position: sticky;
        bottom: 0;
        padding: 1rem 0;
        background: #F3F7FB;
    }

    .input-wrapper {
        display: flex;
        gap: 0.5rem;
        background: var(--bg-surface);
        border: 2px solid var(--border-color);
        border-radius: 1rem;
        padding: 0.5rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-wrapper:focus-within {
        border-color: #2677B8;
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.15);
    }

    .input-wrapper input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.9rem;
        color: var(--text-main);
        padding: 0.5rem;
    }

    .input-wrapper input::placeholder {
        color: var(--text-muted);
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
                #2677B8,
                transparent 30%,
                #1a508b,
                transparent 50%);
        animation: rotate-border 4s linear infinite;
    }

    .send-btn::after {
        content: '';
        position: absolute;
        z-index: -1;
        inset: 2px;
        background: #F3F7FB;
        border-radius: calc(0.625rem - 2px);
        transition: background 0.2s;
    }

    .send-btn:hover::after {
        background: #eef2f8;
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
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ============= History Section ============= */
    .history-section {
        margin-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1.5rem;
    }

    .history-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
    }

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
        background: #F3F7FB;
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

    .status-created, .status-found_existing {
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

    .show-more-history-btn {
        width: 100%;
        padding: 0.5rem;
        margin-top: 0.5rem;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: #2677B8;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .show-more-history-btn:hover {
        background: #F3F7FB;
        border-color: #2677B8;
    }

    /* ============= Welcome State (no messages) ============= */
    .welcome-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        padding: 2rem 0;
    }

    /* ============= Responsive ============= */
    @media (max-width: 640px) {
        .agent-page { padding: 1rem; }
        .agent-header { padding: 1.5rem 0.5rem 1rem; }
        .agent-logo { width: 56px; height: 56px; border-radius: 14px; }
        .agent-logo svg { width: 28px; height: 28px; }
        .agent-header h1 { font-size: 1.25rem; }
        .chat-bubble { max-width: 92%; }
        .result-card { max-width: 100%; }
        .suggestion-chip { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="agent-page" id="agentPage">
    <!-- Header -->
    <div class="agent-header">
        <div class="agent-logo">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
        </div>
        <h1>AI Tutor</h1>
        <p>Ask me to find a lesson, design a learning roadmap, or create an interactive quiz on any topic. I'm here to help you master your subjects!</p>
        <div class="requests-badge">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span class="count" id="remainingCount">{{ $remainingRequests }}</span> requests remaining today
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area" id="chatArea">
        <!-- Welcome state shown when no messages -->
        <div class="welcome-state" id="welcomeState">
            <div class="suggestions" id="suggestions">
                <button class="suggestion-chip" data-suggestion="🗺️ Roadmap for JHS 3 Science">🗺️ Roadmap for JHS 3 Science</button>
                <button class="suggestion-chip" data-suggestion="📝 Create a quiz on Photosynthesis">📝 Create a quiz on Photosynthesis</button>
                <button class="suggestion-chip" data-suggestion="📐 Teach me Quadratic Equations">📐 Teach me Quadratic Equations</button>
                <button class="suggestion-chip" data-suggestion="📚 Roadmap for Core Mathematics">📚 Roadmap for Core Mathematics</button>
                <button class="suggestion-chip" data-suggestion="⚡ Create a quiz on Electricity">⚡ Create a quiz on Electricity</button>
                <button class="suggestion-chip" data-suggestion="🧬 Explain DNA and Genetics">🧬 Explain DNA and Genetics</button>
            </div>
        </div>
    </div>

    <!-- Typing indicator -->
    <div class="typing-indicator" id="typingIndicator">
        <div class="typing-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

    <!-- Input Area -->
    <div class="input-area">
        <div class="mode-toggle" id="modeToggle">
            <button type="button" class="mode-btn active" data-mode="lesson" id="modeLesson">Single Lesson</button>
            <button type="button" class="mode-btn" data-mode="roadmap" id="modeRoadmap">Learning Roadmap <span class="roadmap-tag">GES</span></button>
            <button type="button" class="mode-btn" data-mode="quiz" id="modeQuiz">Interactive Quiz</button>
        </div>
        <form class="input-wrapper" id="agentForm">
            <input
                type="text"
                id="agentInput"
                placeholder="Ask for a lesson... e.g. 'Teach me about photosynthesis'"
                maxlength="500"
                autocomplete="off"
                required
            >
            <button type="submit" class="send-btn" id="sendBtn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span class="btn-text">Ask AI</span>
            </button>
        </form>
    </div>

    <!-- History Section -->
    @if(count($history) > 0)
    <div class="history-section" id="historySection">
        <div class="history-title">Recent Requests</div>
        <div class="history-list" id="historyList">
            @foreach($history as $index => $item)
            <a href="#"
               class="history-item {{ $index >= 5 ? 'hidden-history-item' : '' }}"
               style="{{ $index >= 5 ? 'display: none;' : '' }}"
               data-type="{{ $item['type'] }}"
               data-id="{{ $item['id'] }}"
               data-roadmap="{{ $item['type'] === 'roadmap' ? json_encode($item['roadmap']) : '' }}"
               data-query="{{ addslashes($item['query']) }}"
               data-lesson-url="{{ $item['lesson_url'] ?? '' }}"
               data-quiz-url="{{ $item['quiz_url'] ?? '' }}"
               data-quiz-type="{{ $item['quiz_type'] ?? '' }}"
               data-summary="{{ $item['summary'] ?? '' }}">
                <div class="history-item-icon {{ $item['thumbnail'] ? '' : 'no-thumb' }}">
                    @if($item['thumbnail'])
                        <img src="{{ $item['thumbnail'] }}" alt="" loading="lazy">
                    @elseif($item['type'] === 'roadmap')
                        🗺️
                    @else
                        🔍
                    @endif
                </div>
                <div class="history-item-info">
                    <div class="history-item-query">
                        @if($item['type'] === 'roadmap')
                            <span class="roadmap-tag">ROADMAP</span>
                        @endif
                        {{ $item['query'] }}
                    </div>
                    <div class="history-item-time">{{ $item['created_at'] }}</div>
                </div>
                <span class="history-item-status status-{{ $item['status'] }}">
                    {{ $item['status'] === 'found_existing' ? 'Found' : ucfirst($item['status']) }}
                </span>
            </a>
            @endforeach
        </div>
        @if(count($history) > 5)
        <button id="showMoreHistoryBtn" class="show-more-history-btn" type="button">
            Show More <i class="fas fa-chevron-down ml-1" id="showMoreHistoryIcon"></i>
        </button>
        @endif
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let isProcessing = false;
    let remaining = {{ $remainingRequests }};
    let currentMode = 'lesson';
    let activeContextId = null;

    function setMode(mode) {
        currentMode = mode;
        document.getElementById('modeLesson').classList.toggle('active', mode === 'lesson');
        document.getElementById('modeRoadmap').classList.toggle('active', mode === 'roadmap');
        document.getElementById('modeQuiz').classList.toggle('active', mode === 'quiz');
        
        const input = document.getElementById('agentInput');
        if (mode === 'roadmap') {
            input.placeholder = "e.g. 'Roadmap for JHS Mathematics based on GES'";
        } else if (mode === 'quiz') {
            input.placeholder = "e.g. 'Create a quiz about photosynthesis' or 'Science essay quiz'";
        } else {
            input.placeholder = "Ask for a lesson... e.g. 'Teach me about photosynthesis'";
        }
    }

    function formatDuration(seconds) {
        if (!seconds) return '';
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return m + ':' + String(s).padStart(2, '0');
    }

    function addBubble(text, type) {
        if (!text) return;
        
        const welcomeState = document.getElementById('welcomeState');
        if (welcomeState) welcomeState.style.display = 'none';

        const chatArea = document.getElementById('chatArea');
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + type;
        
        if (type === 'tutor-explanation') {
            const paragraphs = text.split('\n').filter(p => p.trim()).map(p => `<p>${p}</p>`).join('');
            bubble.innerHTML = `
                <div class="explanation-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Topic Explanation
                </div>
                <div class="explanation-body">${paragraphs}</div>
                <button class="tutor-explanation-toggle">Show More</button>
            `;
            
            chatArea.appendChild(bubble);
            
            // Handle toggle visibility
            setTimeout(() => {
                const toggle = bubble.querySelector('.tutor-explanation-toggle');
                if (bubble.scrollHeight > 160) {
                    toggle.style.display = 'block';
                }
                toggle.addEventListener('click', () => {
                    bubble.classList.toggle('expanded');
                    toggle.textContent = bubble.classList.contains('expanded') ? 'Show Less' : 'Show More';
                });
            }, 100);
        } else {
            bubble.textContent = text;
            chatArea.appendChild(bubble);
        }
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    function addResultCard(data) {
        const chatArea = document.getElementById('chatArea');
        
        if (data.type === 'quiz') {
            const card = document.createElement('div');
            card.className = 'quiz-card';
            
            card.innerHTML = `
                <div class="quiz-card-header">
                    <div class="quiz-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h4 class="quiz-card-title">${data.topic}</h4>
                </div>
                <div class="quiz-card-meta">
                    <span class="result-card-badge ${data.is_existing ? 'existing' : 'new'}">${data.is_existing ? '📚 Library Quiz' : '✨ Freshly Created'}</span>
                    <span><i class="fas fa-tag"></i> ${data.quiz_type === 'essay' ? 'Essay' : 'MCQ'}</span>
                    <span><i class="fas fa-brain"></i> GES</span>
                </div>
                <a href="${data.quiz_url}" class="quiz-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2V15H6L11 19V5Z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"></path>
                    </svg>
                    Start Quiz Now
                </a>
            `;
            chatArea.appendChild(card);
        } else if (data.roadmap) {
            const card = document.createElement('div');
            card.className = 'roadmap-card';
            
            let stepsHtml = '';
            data.roadmap.steps.forEach((step, index) => {
                stepsHtml += `
                    <a href="${step.lesson_url || '#'}" class="roadmap-step" ${!step.lesson_url ? 'onclick="return false"' : ''}>
                        <div class="step-number">${index + 1}</div>
                        <div class="step-content">
                            <div class="step-title">${step.title}</div>
                            <div class="step-desc">${step.description}</div>
                        </div>
                    </a>
                `;
            });

            card.innerHTML = `
                <div class="roadmap-header">
                    <div class="roadmap-badge">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 1.55l7.834 3.35a1 1 0 01.666.92v6.57a1 1 0 01-.17.55l-8 11a1 1 0 01-1.66 0l-8-11a1 1 0 01-.17-.55V5.82a1 1 0 01.666-.92zM10 3.24l-6 2.57v5.61l6 8.25 6-8.25v-5.61l-6-2.57z" clip-rule="evenodd"></path></svg>
                        GES Syllabus Aligned • ${data.is_existing ? '📚 Library' : '✨ New'}
                    </div>
                    <h3>${data.roadmap.roadmap_title}</h3>
                    <p style="font-size: 0.75rem; opacity: 0.9; margin: 0;">${data.roadmap.description}</p>
                </div>
                <div class="roadmap-body">
                    <div class="roadmap-steps">
                        ${stepsHtml}
                    </div>
                </div>
            `;
            chatArea.appendChild(card);
        } else if (data.quiz_url || data.type === 'quiz') {
            const card = document.createElement('div');
            card.className = 'quiz-card';
            
            card.innerHTML = `
                <div class="quiz-card-header">
                    <div class="quiz-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h4 class="quiz-card-title">${data.topic || data.title}</h4>
                </div>
                <div class="quiz-card-meta">
                    <span>${data.quiz_type === 'essay' ? '📝 Structured Essay' : '✅ Multiple Choice'}</span>
                </div>
                <a href="${data.quiz_url}" class="quiz-btn">
                    Start Quiz Now
                </a>
            `;
            chatArea.appendChild(card);
        } else {
            const card = document.createElement('div');
            card.className = 'result-card';

            const durationDisplay = data.duration ? formatDuration(data.duration) : '';
            const badgeClass = data.is_existing ? 'existing' : 'new';
            const badgeText = data.is_existing ? '📚 Already in Library' : '✨ Freshly Found';

            card.innerHTML = `
                ${data.thumbnail ? `
                <div class="result-card-thumb">
                    <img src="${data.thumbnail}" alt="${data.title || data.topic}" loading="lazy">
                    ${durationDisplay ? `<span class="result-card-duration">${durationDisplay}</span>` : ''}
                </div>` : ''}
                <div class="result-card-body">
                    <h4>${data.title || data.topic}</h4>
                    <div class="result-card-meta">
                        <span class="result-card-badge ${badgeClass}">${badgeText}</span>
                    </div>
                    <a href="${data.lesson_url}" class="watch-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Watch Lesson
                    </a>
                </div>
            `;
            chatArea.appendChild(card);
        }
        
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    function showRoadmapInChat(roadmapData, query = 'Roadmap Request') {
        const welcomeState = document.getElementById('welcomeState');
        if (welcomeState) welcomeState.style.display = 'none';
        
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
            const chatArea = document.getElementById('chatArea');
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    }

    function setLoading(loading) {
        isProcessing = loading;
        const sendBtn = document.getElementById('sendBtn');
        const input = document.getElementById('agentInput');
        sendBtn.disabled = loading;
        input.disabled = loading;
        
        const btnText = currentMode === 'roadmap' ? 'Designing Roadmap...' : (currentMode === 'quiz' ? 'Generating Quiz...' : 'Searching...');
        sendBtn.querySelector('.btn-text').textContent = loading ? btnText : 'Ask AI';
    }

    function askSuggestion(el, text) {
        const query = text || el.textContent.replace(/^[\u{1F000}-\u{1FFFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]\s*/u, '').trim();
        document.getElementById('agentInput').value = query;
        handleSubmit(new Event('submit'));
    }

    async function handleSubmit(e) {
        e.preventDefault();

        if (isProcessing) return false;

        const input = document.getElementById('agentInput');
        const query = input.value.trim();
        if (!query || query.length < 3) return false;

        if (remaining <= 0) {
            addBubble('You\'ve used all your AI requests for today. Come back tomorrow!', 'agent error');
            return false;
        }

        // Show user message
        addBubble(query, 'user');
        input.value = '';

        setLoading(true);
        showTyping(true);

        try {
            const response = await fetch('{{ route("api.agent.ask") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ 
                    query: query,
                    type: currentMode,
                    context_id: activeContextId
                }),
            });

            const data = await response.json();

            showTyping(false);

            // Show result card if successful
            if (data.success) {
                if (data.request_id) {
                    activeContextId = data.request_id;
                }
                
                addBubble(data.message, 'ai');
                if (data.summary) {
                    addBubble(data.summary, 'tutor-explanation');
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
    document.addEventListener('DOMContentLoaded', function() {
        const agentForm = document.getElementById('agentForm');
        const modeToggle = document.getElementById('modeToggle');
        const suggestions = document.getElementById('suggestions');
        const historyList = document.getElementById('historyList');

        if (agentForm) {
            agentForm.addEventListener('submit', handleSubmit);
        }

        if (modeToggle) {
            modeToggle.addEventListener('click', function(e) {
                const btn = e.target.closest('.mode-btn');
                if (btn) setMode(btn.dataset.mode);
            });
        }

        if (suggestions) {
            suggestions.addEventListener('click', function(e) {
                const btn = e.target.closest('.suggestion-chip');
                if (btn) askSuggestion(btn);
            });
        }

        if (historyList) {
            historyList.addEventListener('click', function(e) {
                const item = e.target.closest('.history-item');
                if (item) {
                    const type = item.dataset.type;
                    const itemId = item.dataset.id;
                    const roadmap = item.dataset.roadmap;
                    const query = item.dataset.query;
                    const lessonUrl = item.dataset.lessonUrl;
                    const summary = item.dataset.summary;

                    e.preventDefault();
                    
                    // Set active context ID
                    activeContextId = itemId;
                    
                    if (type === 'roadmap' && roadmap) {
                        showRoadmapInChat(JSON.parse(roadmap), query);
                    } else if (type === 'quiz' || item.dataset.quizUrl) {
                        const quizUrl = item.dataset.quizUrl;
                        const quizType = item.dataset.quizType;
                        
                        // Clear welcome state if visible
                        const welcomeState = document.getElementById('welcomeState');
                        if (welcomeState) welcomeState.style.display = 'none';
                        
                        // Re-show what was given for that prompt
                        addBubble(query, 'user');
                        addBubble('Here is the quiz I generated for you earlier.', 'ai');
                        addResultCard({
                            success: true,
                            type: 'quiz',
                            topic: query,
                            quiz_url: quizUrl,
                            quiz_type: quizType,
                            is_existing: true
                        });
                    } else if (lessonUrl) {
                        // Clear welcome state if visible
                        const welcomeState = document.getElementById('welcomeState');
                        if (welcomeState) welcomeState.style.display = 'none';
                        
                        // Re-show what was given for that prompt
                        addBubble(query, 'user');
                        addBubble('Here is the lesson I found for you earlier.', 'ai');
                        if (summary) {
                            addBubble(summary, 'tutor-explanation');
                        }
                        addResultCard({
                            success: true,
                            type: 'lesson',
                            title: query,
                            lesson_url: lessonUrl,
                            thumbnail: item.querySelector('img')?.src,
                            is_existing: true
                        });
                    } else {
                        // Fallback: trigger a new search if no URL is stored
                        askSuggestion(null, query);
                    }
                }
            });
        }

        const showMoreHistoryBtn = document.getElementById('showMoreHistoryBtn');
        if (showMoreHistoryBtn) {
            showMoreHistoryBtn.addEventListener('click', function() {
                const hiddenItems = document.querySelectorAll('.hidden-history-item');
                
                let isShowingAll = this.dataset.showing === 'true';
                
                hiddenItems.forEach(item => {
                    item.style.display = isShowingAll ? 'none' : 'flex';
                });
                
                if (isShowingAll) {
                    this.dataset.showing = 'false';
                    this.innerHTML = 'Show More <i class="fas fa-chevron-down ml-1" id="showMoreHistoryIcon"></i>';
                } else {
                    this.dataset.showing = 'true';
                    this.innerHTML = 'Show Less <i class="fas fa-chevron-up ml-1" id="showMoreHistoryIcon"></i>';
                }
            });
        }

        // Auto-focus input
        const agentInput = document.getElementById('agentInput');
        if (agentInput) agentInput.focus();
    });
</script>
@endpush
