<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Essay Quiz - {{ str_replace('Quiz for: ', '', $quiz['title'] ?? 'Quiz') }} -
        {{ config('app.name', 'ShoutOutGh') }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- MathLive for Matrix/Formula Rendering -->
    <script defer src="https://unpkg.com/mathlive" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>

    <!-- Quill.js for Rich Text Editing -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        /* Clean Student Math Display */
        math-field {
            font-size: 1.1rem;
            background: transparent;
            display: inline-block;
            border: none;
            outline: none;
            cursor: default;
            pointer-events: none;
        }

        math-field::part(virtual-keyboard-toggle),
        math-field::part(menu-toggle) {
            display: none !important;
        }

        :root {
            --primary-blue: #2677B8;
            --primary-blue-hover: #1e5a8a;
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
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
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --header-height: 64px;
        }

        [data-theme="dark"] {
            --bg-main: #000000;
            --bg-surface: #16181c;
            --text-main: #ffffff;
            --text-muted: #71767b;
            --border-color: #2f3336;
            --gray-25: #000000;
            --gray-50: #16181c;
            --gray-100: #202327;
            --gray-200: #2f3336;
            --gray-300: #3e4144;
            --gray-400: #71767b;
            --gray-500: #8b98a5;
            --gray-900: #ffffff;
            --white: #16181c;
            color-scheme: dark;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-25);
            color: var(--gray-900);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .header {
            height: var(--header-height);
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 1000;
            flex-shrink: 0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .back-link {
            text-decoration: none;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--gray-900);
        }

        .logo img {
            height: 32px;
            width: auto;
        }

        .quiz-info {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .quiz-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--gray-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 400px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .timer-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--gray-100);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--gray-800);
            border: 1px solid var(--gray-200);
        }

        .timer-box.warning {
            color: #d97706;
            background-color: #fffbeb;
            border-color: #fcd34d;
        }

        .timer-box.danger {
            color: #dc2626;
            background-color: #fef2f2;
            border-color: #fca5a5;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-blue-hover);
            transform: translateY(-1px);
        }

        /* Main Viewport */
        .workspace {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Left Side: Question Paper */
        .question-pane {
            flex: 1;
            overflow-y: auto;
            padding: 2.5rem;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            scrollbar-width: thin;
        }

        .paper-section {
            max-width: 700px;
            margin: 0 auto;
        }

        .section-header {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary-blue);
            letter-spacing: 0.1em;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .question-item {
            margin-bottom: 4rem;
            scroll-margin-top: 2rem;
        }

        .question-item.active {
            padding: 1.5rem;
            background-color: rgba(38, 119, 184, 0.03);
            border-radius: 1rem;
            border: 1px solid rgba(38, 119, 184, 0.1);
        }

        .question-label {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--gray-900);
        }

        .preamble {
            background-color: var(--gray-50);
            padding: 1.5rem;
            border-left: 4px solid var(--primary-blue);
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .question-text {
            font-size: 1.125rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .question-image {
            max-width: 100%;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        /* Right Side: Answer Booklet */
        .answer-pane {
            flex: 1;
            background-color: var(--gray-50);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .booklet-tabs {
            padding: 1rem 1.5rem 0;
            display: flex;
            gap: 0.5rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
        }

        .tab {
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem 0.75rem 0 0;
            background-color: var(--gray-100);
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid var(--gray-200);
            border-bottom: none;
        }

        .tab.active {
            background-color: var(--gray-25);
            color: var(--primary-blue);
            border-top: 3px solid var(--primary-blue);
        }

        .booklet-container {
            flex: 1;
            overflow: hidden;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .booklet {
            display: none;
            flex: 1;
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            flex-direction: column;
        }

        .booklet.active {
            display: flex;
        }

        .booklet-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booklet-title {
            font-weight: 700;
            font-size: 0.875rem;
            color: var(--gray-500);
            text-transform: uppercase;
        }

        .word-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-400);
        }

        .editor-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .ql-container.ql-snow {
            border: none !important;
            flex: 1;
            font-size: 1.125rem;
            font-family: 'Inter', sans-serif;
            overflow-y: auto;
        }

        .ql-toolbar.ql-snow {
            border: none !important;
            border-bottom: 1px solid var(--gray-100) !important;
            padding: 0.5rem 1rem !important;
        }

        /* Footer Navigation for Booklets */
        .workspace-footer {
            padding: 1rem 1.5rem;
            background-color: var(--white);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-btn {
            background: none;
            border: 1px solid var(--gray-300);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            color: var(--gray-900);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover:not(:disabled) {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
        }

        .nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Mobile Adjustments */
        @media (max-width: 900px) {
            .workspace {
                flex-direction: column;
            }

            .question-pane {
                flex: 0 0 40%;
                border-right: none;
                border-bottom: 1px solid var(--gray-200);
                padding: 1.5rem;
            }

            .answer-pane {
                flex: 1;
            }
        }

        /* Structured Sub-questions */
        .sub-questions-list { 
            margin-top: 1.5rem; 
            display: flex; 
            flex-direction: column; 
            gap: 1.25rem; 
        }
        .sub-question-row { 
            display: flex; 
            gap: 1rem; 
            align-items: flex-start; 
        }
        .sub-label { 
            font-weight: 700; 
            color: var(--gray-900); 
            min-width: 1.5rem; 
        }
        .sub-text { 
            flex: 1; 
            font-size: 1.05rem; 
            line-height: 1.5; 
            color: var(--gray-700); 
        }
        .sub-marks { 
            font-weight: 600; 
            color: var(--gray-500); 
            font-size: 0.875rem; 
            white-space: nowrap; 
            margin-left: 1rem; 
            font-style: italic; 
        }

        /* Hide Scrollbars */

        .question-pane::-webkit-scrollbar {
            width: 6px;
        }

        .question-pane::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <a href="{{ route('quiz.instructions', $quiz['encoded_id']) }}" class="back-link"
                onclick="return confirm('Exit quiz? Progress will be lost.')">
                <i class="fas fa-arrow-left"></i>
                <span>Quit</span>
            </a>
            <div class="logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
            </div>
        </div>

        <div class="quiz-info">
            <div class="quiz-title">{{ str_replace('Quiz for: ', '', $quiz['title'] ?? 'Essay Examination') }}</div>
        </div>

        <div class="header-right">
            <div class="timer-box" id="timerBox">
                <i class="far fa-clock"></i>
                <span id="countdown">--:--</span>
            </div>
            <button class="btn-submit" onclick="submitEssay()">
                <i class="fas fa-paper-plane"></i>
                <span>Finish & Submit</span>
            </button>
        </div>
    </header>

    <!-- Workspace -->
    <main class="workspace">
        <!-- Question Pane -->
        <div class="question-pane" id="questionPane">
            <div class="paper-section">
                <div class="section-header">Question Paper</div>

                @forelse($quiz['questions'] ?? [] as $index => $question)
                    <div class="question-item" id="q-{{ $index }}">
                        @php
                            $hasMainContent = !empty(trim(strip_tags($question['question'])));
                            $hasSubQuestions = !empty($question['sub_questions']) && count($question['sub_questions']) > 0;
                        @endphp

                        @if($hasMainContent || !$hasSubQuestions)
                            <div class="question-label">Question {{ $index + 1 }}</div>
                            @if(!empty($question['preamble']))
                                <div class="preamble">{!! $question['preamble'] !!}</div>
                            @endif
                            <div class="question-text">{!! $question['question'] !!}</div>
                        @else
                            @if(!empty($question['preamble']))
                                <div class="preamble" style="margin-bottom: 1rem;">{!! $question['preamble'] !!}</div>
                            @endif
                        @endif

                        @if($hasSubQuestions)
                            <div class="sub-questions-list" style="margin-top: 1rem;">
                                @foreach($question['sub_questions'] as $sIdx => $sub)
                                    <div class="sub-question-row">
                                        <div class="sub-label">
                                            @if(!$hasMainContent && $sIdx === 0)
                                                {{ $index + 1 }}{{ $sub['label'] }})
                                            @else
                                                {{ $sub['label'] }})
                                            @endif
                                        </div>
                                        <div class="sub-text">{!! $sub['text'] !!}</div>
                                        <div class="sub-marks">[{{ $sub['points'] }} {{ Str::plural('mark', $sub['points']) }}]</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($question['image']))

                            <img src="{{ $question['image'] }}" class="question-image"
                                alt="Visual aid for question {{ $index + 1 }}">
                        @endif
                    </div>
                @empty
                    <div class="preamble">No essay questions available for this quiz.</div>
                @endforelse
            </div>
        </div>

        <!-- Answer Pane -->
        <div class="answer-pane">
            <div class="booklet-tabs">
                @foreach($quiz['questions'] ?? [] as $index => $question)
                    <div class="tab {{ $index === 0 ? 'active' : '' }}" onclick="switchBooklet({{ $index }})"
                        id="tab-{{ $index }}">
                        Booklet {{ $index + 1 }}
                    </div>
                @endforeach
            </div>

            <div class="booklet-container">
                @foreach($quiz['questions'] ?? [] as $index => $question)
                    <div class="booklet {{ $index === 0 ? 'active' : '' }}" id="booklet-{{ $index }}">
                        <div class="booklet-header">
                            <div class="booklet-title">
                                @php
                                    $hasMainContent = !empty(trim(strip_tags($question['question'] ?? '')));
                                    $hasSubQuestions = !empty($question['sub_questions']) && count($question['sub_questions']) > 0;
                                @endphp
                                @if(!$hasMainContent && $hasSubQuestions)
                                    Booklet {{ $index + 1 }}{{ $question['sub_questions'][0]['label'] }}...
                                @else
                                    Booklet {{ $index + 1 }}
                                @endif
                                <span style="margin-left: 8px; opacity: 0.6; font-size: 11px;">(Total {{ $question['points'] }} Marks)</span>
                            </div>
                            @if(empty($question['sub_questions']))
                                <div class="word-count" id="count-{{ $index }}">0 characters</div>
                            @endif
                        </div>

                        @if(!empty($question['sub_questions']) && count($question['sub_questions']) > 0)
                            <div class="sub-editors-container" style="display: flex; flex-direction: column; gap: 1.5rem; overflow-y: auto; padding: 1.5rem; flex: 1;">
                                @foreach($question['sub_questions'] as $sIndex => $sub)
                                    <div class="sub-editor-group" style="display: flex; flex-direction: column; border: 1px solid var(--gray-200); border-radius: 0.75rem; overflow: hidden; background: var(--white); min-height: 250px;">
                                        <div class="sub-editor-header" style="background: var(--gray-50); padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200); font-size: 0.875rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                                            <span style="color: var(--primary-blue)">
                                                @if(!$hasMainContent && $sIndex === 0)
                                                    Answer for {{ $index + 1 }}{{ $sub['label'] }})
                                                @else
                                                    Answer for {{ $sub['label'] ?? '' }}) 
                                                @endif
                                                <span style="font-size: 0.75rem; color: var(--gray-500); margin-left: 0.5rem; font-weight: normal;">[{{ $sub['points'] ?? 0 }} Marks]</span>
                                            </span>
                                            <span class="word-count" id="count-{{ $index }}-{{ $sIndex }}">0 characters</span>
                                        </div>
                                        <div class="editor-wrapper" style="flex: 1; display: flex; flex-direction: column; border: none;">
                                            <div id="editor-{{ $index }}-{{ $sIndex }}" class="editor-instance" data-qidx="{{ $index }}" data-sidx="{{ $sIndex }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="editor-wrapper">
                                <div id="editor-{{ $index }}" class="editor-instance" data-qidx="{{ $index }}"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="workspace-footer">
                <button class="nav-btn" id="prevBtn" onclick="prevBooklet()" disabled>
                    <i class="fas fa-chevron-left"></i> Previous Question
                </button>
                <div class="muted" style="font-size: 0.8rem; color: var(--gray-400);">
                    <i class="fas fa-save"></i> Auto-saving...
                </div>
                <button class="nav-btn" id="nextBtn" onclick="nextBooklet()">
                    Next Question <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </main>

    <!-- Hidden Form -->
    <form id="submissionForm" method="POST" action="{{ route('quiz.essay.submit', $quiz['encoded_id']) }}"
        data-quiz-form="true" style="display: none;">
        @csrf
        <textarea name="essay" id="finalEssay"></textarea>
        <input type="hidden" name="answers" id="finalAnswers" value="">
        <input type="hidden" name="time_spent" id="time_spent" value="0">
    </form>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const quizDataQuestions = @json($quiz['questions'] ?? []);
        let currentIdx = 0;
        const totalQuestions = {{ count($quiz['questions'] ?? []) }};
        const quillInstances = [];
        let timeRemaining = {{ (int) ($seconds ?? 180) }};
        const timeLimit = {{ (int) ($seconds ?? 180) }};

        // Anti-cheat sync globals
        window.answers = {};
        window.timeLimitMinutes = Math.ceil(timeLimit / 60);
        window.timeRemaining = timeRemaining;

        // Initialize Editors & Math
        document.addEventListener('DOMContentLoaded', () => {
            const quizId = "{{ $quiz['id'] ?? 'default' }}";
            const storageKeyPrefix = `essay_save_${quizId}_`;

            document.querySelectorAll('.editor-instance').forEach(editorDiv => {
                const qIdx = editorDiv.dataset.qidx;
                const sIdx = editorDiv.dataset.sidx;
                const idSuffix = sIdx !== undefined ? `${qIdx}_${sIdx}` : qIdx;
                const containerId = editorDiv.id;
                
                let placeholderText = `Write your answer for Question ${parseInt(qIdx) + 1} here...`;
                if (sIdx !== undefined) {
                    const subLabel = quizDataQuestions[qIdx]?.sub_questions[sIdx]?.label ?? '?';
                    placeholderText = `Write your answer for Part ${subLabel}) here...`;
                }

                const quill = new Quill(`#${containerId}`, {
                    theme: 'snow',
                    placeholder: placeholderText,
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['clean']
                        ]
                    },
                    scrollingContainer: sIdx !== undefined ? `#${containerId}` : `#booklet-${qIdx}`,
                    spellcheck: false // Explicitly disable native spellcheck
                });

                const countElId = sIdx !== undefined ? `count-${qIdx}-${sIdx}` : `count-${qIdx}`;

                // Load from LocalStorage
                const saved = localStorage.getItem(storageKeyPrefix + idSuffix);
                if (saved) {
                    quill.root.innerHTML = saved;
                    updateSpecificWordCount(quill, countElId);
                }

                // Auto-save & Word Count
                quill.on('text-change', () => {
                    const content = quill.root.innerHTML;
                    localStorage.setItem(storageKeyPrefix + idSuffix, content);
                    updateSpecificWordCount(quill, countElId);

                    // Sync to window.answers for anti-cheat auto-submission
                    if (sIdx !== undefined) {
                        if (!window.answers[qIdx]) window.answers[qIdx] = {};
                        window.answers[qIdx][sIdx] = content;
                    } else {
                        window.answers[qIdx] = content;
                    }
                });

                quillInstances.push({
                    qIdx: parseInt(qIdx),
                    sIdx: sIdx !== undefined ? parseInt(sIdx) : undefined,
                    quill: quill,
                    idSuffix: idSuffix
                });
            });

            // Sync MathLive styles reliably
            if (window.MathLive) {
                renderMath();
            } else {
                window.addEventListener('load', renderMath);
            }

            startTimer();
            updateNavButtons();
        });

        function renderMath() {
            customElements.whenDefined('math-field').then(() => {
                document.querySelectorAll('math-field').forEach(mf => {
                    mf.readOnly = true;
                    mf.removeAttribute('contenteditable');
                    mf.removeAttribute('tabindex');
                });
            });
        }

        function updateSpecificWordCount(quill, elId) {
            const el = document.getElementById(elId);
            if(el) {
                const text = quill.getText().trim();
                const count = text.length;
                el.textContent = count + ' characters';
            }
        }

        function switchBooklet(idx) {
            // Update Active Classes
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.booklet').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.question-item').forEach(q => q.classList.remove('active'));

            document.getElementById(`tab-${idx}`).classList.add('active');
            document.getElementById(`booklet-${idx}`).classList.add('active');

            const qEl = document.getElementById(`q-${idx}`);
            if (qEl) {
                qEl.classList.add('active');
                qEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            currentIdx = idx;
            updateNavButtons();
        }

        function nextBooklet() {
            if (currentIdx < totalQuestions - 1) switchBooklet(currentIdx + 1);
        }

        function prevBooklet() {
            if (currentIdx > 0) switchBooklet(currentIdx - 1);
        }

        function updateNavButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (prevBtn) prevBtn.disabled = currentIdx === 0;
            if (nextBtn) nextBtn.disabled = currentIdx === totalQuestions - 1;
        }

        function startTimer() {
            const timerEl = document.getElementById('countdown');
            const timerBox = document.getElementById('timerBox');

            const tick = () => {
                if (timeRemaining <= 0) {
                    autoSubmit();
                    return;
                }

                const m = Math.floor(timeRemaining / 60).toString().padStart(2, '0');
                const s = (timeRemaining % 60).toString().padStart(2, '0');
                if (timerEl) timerEl.textContent = `${m}:${s}`;

                if (timeRemaining <= 60) timerBox.className = 'timer-box danger';
                else if (timeRemaining <= 300) timerBox.className = 'timer-box warning';

                timeRemaining--;
                window.timeRemaining = timeRemaining; // Sync for anti-cheat
            };

            tick();
            setInterval(tick, 1000);
        }

        function submitEssay() {
            if (!confirm('Are you sure you want to finish and submit your paper?')) return;
            performSubmission();
        }

        function autoSubmit() {
            alert('Time is up! Your answers are being submitted automatically.');
            performSubmission();
        }

        function performSubmission() {
            let fullEssay = '';
            
            // Group instances by question
            const grouped = {};
            quillInstances.forEach(item => {
                if(!grouped[item.qIdx]) grouped[item.qIdx] = [];
                grouped[item.qIdx].push(item);
            });

            for(let i = 0; i < totalQuestions; i++) {
                const items = grouped[i] || [];
                const questionData = quizDataQuestions[i] || {};
                
                let qContent = '';
                if(items.length > 0) {
                    if (items.length === 1 && items[0].sIdx === undefined) {
                        qContent = items[0].quill.root.innerHTML;
                    } else {
                        // Sub-questions - sort by sIdx to ensure proper order
                        items.sort((a, b) => a.sIdx - b.sIdx).forEach(item => {
                            const subQ = questionData.sub_questions && questionData.sub_questions[item.sIdx] ? questionData.sub_questions[item.sIdx] : null;
                            const label = subQ ? subQ.label : '?';
                            qContent += `
                                <div class="sub-answer-part" style="margin-bottom: 1.5rem;">
                                    <h3 style="color: #4b5563; font-size: 1.1rem; margin-bottom: 0.5rem;">Part ${label})</h3>
                                    <div style="padding-left: 1rem; border-left: 3px solid #e5e7eb;">
                                        ${item.quill.root.innerHTML}
                                    </div>
                                </div>
                            `;
                        });
                    }
                }

                if (totalQuestions > 1) {
                    fullEssay += `<div class="essay-submission-part" style="margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #eee; border-radius: 8px; background: #fff;">
                                    <h2 style="color: #2677B8; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem;">Answer for Question ${i + 1}</h2>
                                    ${qContent}
                                  </div>`;
                } else {
                    fullEssay += qContent;
                }
            }

            const answersObj = {};
            for (let i in grouped) {
                const items = grouped[i];
                if (items.length === 1 && items[0].sIdx === undefined) {
                    answersObj[i] = items[0].quill.root.innerHTML;
                } else {
                    const subAnswers = {};
                    items.forEach(item => {
                        subAnswers[item.sIdx] = item.quill.root.innerHTML;
                    });
                    answersObj[i] = subAnswers;
                }
            }

            document.getElementById('finalEssay').value = fullEssay;
            document.getElementById('finalAnswers').value = JSON.stringify(answersObj);
            document.getElementById('time_spent').value = timeLimit - timeRemaining;

            const quizId = "{{ $quiz['id'] ?? 'default' }}";
            quillInstances.forEach(item => {
                localStorage.removeItem(`essay_save_${quizId}_` + item.idSuffix);
            });

            window.isSubmitting = true;
            document.getElementById('submissionForm').submit();
        }
    </script>

    @include('dashboard.quiz.partials.anti-cheat')
</body>

</html>