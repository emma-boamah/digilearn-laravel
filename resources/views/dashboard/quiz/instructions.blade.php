<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz Instructions - {{ config('app.name', 'ShoutOutGh') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --brand-red: #C52828;
            --brand-red-hover: #A01E1E;
            --brand-blue-deep: #1e5a8a;
            /* Deep Navy for the "Environment" */
            --brand-blue-card: #2677B8;
            /* Lighter navy for containers */
            --brand-blue-accent: #2677B8;
            --text-white: #111827;
            --text-muted: #94A3B8;
            --integrity-red-bg: rgba(197, 40, 40, 0.1);
            --white: #ffffff;
            --gray-200: #e5e7eb;
        }

        [data-theme="dark"] {
            --brand-red: #E11E2D;
            --brand-red-hover: #c41e2a;
            --brand-blue-deep: #000000;
            --brand-blue-card: #16181c;
            --brand-blue-accent: #2f3336;
            --text-white: #ffffff;
            --text-muted: #71767b;
            --integrity-red-bg: rgba(225, 30, 45, 0.1);
            --white: #16181c;
            --gray-200: #2f3336;
            color-scheme: dark;
        }

        [data-theme="dark"] .integrity-banner::after {
            background: #16181c;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            /* Dark blue environment */
            color: var(--text-white);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Go Back Button */
        .btn-back {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background: var(--gray-200);
            border: 1px solid var(--brand-blue-accent);
            color: var(--text-muted);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
        }

        .btn-back:hover {
            color: var(--text-white);
            border-color: var(--brand-red);
            background: var(--brand-blue-accent);
        }

        .main-wrapper {
            width: 100%;
            max-width: 1000px;
            padding: 3rem;
            background: radial-gradient(circle at top right, #fcfcfd; , #ffffff);
            border: 1.5px solid var(--brand-blue-accent);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Header: Title is now White with Red emphasis */
        .head-section {
            text-align: left;
            margin-bottom: 0.5rem;
        }

        .main-title {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-white);
            letter-spacing: -1px;
        }

        .main-title span {
            color: var(--text-white);
        }

        .sub-description {
            color: var(--text-white);
            font-size: 1.1rem;
            border-left: 3px solid var(--brand-red);
            padding-left: 1rem;
            max-width: 700px;
            margin-bottom: 2rem;
        }

        /* Info Grid: High contrast navy cards */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .info-card {
            background: var(--white);
            border: 1px solid var(--brand-blue-accent);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }

        .info-card:hover {
            border-color: var(--brand-red);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        .icon-box {
            color: var(--brand-red);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--brand-blue-card);
            margin-bottom: 0.5rem;
        }

        .card-body {
            font-size: 0.95rem;
            color: var(--text-white);
            line-height: 1.6;
            font-family: 'JetBrains Mono', 'Roboto Mono', monospace;
        }

        /* Integrity Rules: Now looks like a secure "Warning" area */
        .integrity-banner {
            position: relative;
            background: transparent;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 3rem;
            z-index: 1;
            overflow: hidden;
        }

        .integrity-banner::before {
            content: '';
            position: absolute;
            z-index: -1;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(transparent,
                    var(--brand-red),
                    transparent 30%,
                    var(--brand-blue-deep),
                    transparent 50%);
        }

        .ready-to-start .integrity-banner::before {
            animation: rotate-border 4s linear infinite;
        }

        .integrity-banner::after {
            content: '';
            position: absolute;
            z-index: -1;
            inset: 1px;
            background: #faeaea;
            border-radius: 19px;
        }

        @keyframes rotate-border {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .banner-header {
            color: var(--brand-red);
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: 1px;
        }

        .rule-list {
            list-style: none;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .rule-item {
            color: var(--text-white);
            display: flex;
            gap: 1.25rem;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(197, 40, 40, 0.1);
        }

        .rule-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .rule-item i {
            color: var(--brand-red);
            font-size: 1.25rem;
        }

        .rule-item strong {
            color: var(--text-white);
            font-weight: 700;
            margin-right: 0.5rem;
        }

        /* Action: Large, Glowing Start Button */
        .action-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-start {
            background-color: var(--brand-red);
            color: white;
            width: 100%;
            max-width: 250px;
            padding: 1.5rem;
            border-radius: 16px;
            font-size: 1.25rem;
            font-weight: 800;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 20px -5px rgba(197, 40, 40, 0.4);
            transition: all 0.3s ease;
        }

        .ready-to-start .btn-start {
            animation: pulse-red 2s infinite;
        }

        .btn-start:hover {
            background-color: var(--brand-red-hover);
            box-shadow: 0 20px 30px -5px rgba(197, 40, 40, 0.6);
            transform: translateY(-2px) scale(1.02);
            animation: none;
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 10px 20px -5px rgba(197, 40, 40, 0.4), 0 0 0 0 rgba(197, 40, 40, 0.4);
            }

            70% {
                box-shadow: 0 10px 20px -5px rgba(197, 40, 40, 0.4), 0 0 0 10px rgba(197, 40, 40, 0);
            }

            100% {
                box-shadow: 0 10px 20px -5px rgba(197, 40, 40, 0.4), 0 0 0 0 rgba(197, 40, 40, 0);
            }
        }

        .btn-start:active {
            transform: scale(0.98);
        }

        .btn-essay {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
        }

        .btn-essay:hover {
            color: var(--text-white);
            background-color: var(--brand-blue-card);
        }

        .footer-wrapper {
            margin-top: 3rem;
            border-top: 1px solid var(--brand-blue-accent);
            padding-top: 2rem;
            width: 100%;
        }

        .footer-info {
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .support-link {
            color: var(--brand-red);
            text-decoration: none;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin: 2rem 0;
            }

            .main-wrapper {
                padding: 2rem;
                border-radius: 16px;
            }

            .main-title {
                font-size: 2rem;
            }

            .btn-start {
                max-width: 100%;
            }

            .footer-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- Back Button -->
        <button class="btn-back" id="btnGoBack" title="Go Back">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Header -->
        <div class="head-section">
            <h1 class="main-title">Quiz Instructions - <span>{{ str_replace('Quiz for: ', '', $quiz['title'] ?? 'Quiz')
                    }}</span></h1>
            <p class="sub-description">
                Please read the following guidelines carefully before starting your assessment.
            </p>
        </div>

        <!-- Detailed Info Grid -->
        <div class="info-grid">
            <!-- Timer Details -->
            <div class="info-card">
                <div class="icon-box">
                    <i class="fas fa-stopwatch"></i>
                </div>
                <h3 class="card-title">Timer Details</h3>
                <p class="card-body">
                    The quiz has a fixed duration of {{ $quiz['time_limit_minutes'] ?? '3' }} minutes.
                    The timer starts immediately after clicking 'Start Quiz'.
                </p>
            </div>

            <!-- Navigation Details -->
            <div class="info-card">
                <div class="icon-box">
                    <i class="fas fa-step-forward"></i>
                </div>
                <h3 class="card-title">Skip Questions</h3>
                <p class="card-body">
                    You can skip difficult questions and return to them later using the navigation panel on the right
                    during the exam.
                </p>
            </div>

            <!-- Results Details -->
            <div class="info-card">
                <div class="icon-box">
                    <i class="fas fa-poll"></i>
                </div>
                <h3 class="card-title">Immediate Scoring</h3>
                <p class="card-body">
                    Your final score will be displayed immediately upon submission, including a detailed breakdown of
                    correct answers.
                </p>
            </div>
        </div>

        <!-- Strict Integrity Rules -->
        <div class="integrity-banner">
            <div class="banner-header">
                <i class="fas fa-shield-halved"></i>
                Security Protocol
            </div>
            <ul class="rule-list">
                <li class="rule-item">
                    <i class="fas fa-circle-xmark"></i>
                    <span><strong>No Tab Switching</strong> Leaving this tab or opening new windows will be
                        flagged.</span>
                </li>
                <li class="rule-item">
                    <i class="fas fa-video-slash"></i>
                    <span><strong>No Screen Capture</strong> Screenshot and recording tools are strictly
                        prohibited.</span>
                </li>
                <li class="rule-item">
                    <i class="fas fa-user-lock"></i>
                    <span><strong>Session Locking</strong> Once the session begins, it cannot be paused or
                        restarted.</span>
                </li>
            </ul>
        </div>

        <!-- Action Area -->
        <div class="action-area">
            <button class="btn-start" id="btnStartQuiz">
                Start Quiz
            </button>

            @if(isset($quiz['encoded_id']))
            <a href="{{ route('quiz.essay', $quiz['encoded_id']) }}" class="btn-essay">
                <i class="fas fa-pen-nib"></i>
                View Essay Questions Format
            </a>
            @endif
        </div>

        <!-- Footer Info -->
        <div class="footer-wrapper">
            <div class="footer-info">
                <div class="technical-footer">
                    <i class="fas fa-circle-info"></i>
                    Need technical assistance? <a href="{{ route('contact') }}" class="support-link">Contact Proctoring
                        Support</a>
                </div>
                <div class="branding-footer">
                    Powered by ShoutOutGh Editorial Assessment Engine
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', () => {
            // Activate animations after a brief delay to focus attention
            setTimeout(() => {
                document.body.classList.add('ready-to-start');
            }, 1500);

            const btnBack = document.getElementById('btnGoBack');
            if (btnBack) btnBack.addEventListener('click', () => {
                btnBack.style.opacity = '0.5';
                window.history.back();
            });

            const startBtn = document.getElementById('btnStartQuiz');
            if (startBtn) {
                startBtn.addEventListener('click', () => {
                    startBtn.disabled = true;
                    startBtn.style.opacity = '0.7';
                    startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

                    @if (isset($quiz['encoded_id']))
                        window.location.href = `{{ route('quiz.take', $quiz['encoded_id']) }}`;
                    @endif
                });
            }
        });
    </script>
</body>

</html>