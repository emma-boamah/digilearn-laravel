<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Virtual Classroom: {{ $virtualClass->topic ?? 'Live Session' }} - DigiLearn</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Fabric.js for HTML5 Vector Whiteboard Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <!-- KaTeX for Real-time LaTeX Math Formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <!-- PDF.js for Slide & Lesson Document Presentation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        if (window.pdfjsLib) {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>


    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/classroom/classroom.js'])

    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --bg-dark-base: #090d16;
            --bg-dark-surface: #0f172a;
            --bg-dark-elevated: #1e293b;
            --bg-dark-border: #334155;
            --primary-blue: #2677B8;
            --primary-blue-hover: #1e649e;
            --primary-red: #E11E2D;
            --primary-green: #10b981;
            --text-light-main: #f8fafc;
            --text-light-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark-base);
            color: var(--text-light-main);
            height: 100vh;
            overflow: hidden;
            user-select: none;
        }

        .classroom-root {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
            background: var(--bg-dark-base);
        }

        /* Top Header Bar */
        .classroom-header {
            height: 60px;
            background: var(--bg-dark-surface);
            border-bottom: 1px solid var(--bg-dark-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            flex-shrink: 0;
            z-index: 50;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .room-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--text-light-main);
            font-weight: 800;
            font-size: 1.1rem;
        }

        .room-info-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .room-topic-title {
            font-weight: 700;
            color: var(--text-light-main);
        }

        .room-id-tag {
            color: var(--text-light-muted);
            font-family: monospace;
            background: rgba(255, 255, 255, 0.06);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
        }

        /* Stage Selector Center Tabs */
        .stage-tabs {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            padding: 0.25rem;
            border-radius: 10px;
        }

        .stage-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: transparent;
            border: none;
            color: var(--text-light-muted);
            padding: 0.35rem 0.85rem;
            border-radius: 7px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .stage-tab-btn.active {
            background: var(--primary-blue);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(38, 119, 184, 0.3);
        }

        .stage-tab-btn:hover:not(.active) {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .live-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(225, 30, 45, 0.15);
            border: 1px solid rgba(225, 30, 45, 0.3);
            color: var(--primary-red);
            padding: 0.3rem 0.65rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            background: var(--primary-red);
            border-radius: 50%;
            animation: pulseAnimation 1.5s infinite;
        }

        @keyframes pulseAnimation {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }

        .btn-leave-room {
            background: var(--primary-red);
            color: #ffffff;
            border: none;
            padding: 0.45rem 0.95rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.15s ease;
        }

        .btn-leave-room:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        /* Classroom Body: Main Stage + Right Drawer */
        .classroom-body {
            display: flex;
            flex: 1;
            height: calc(100vh - 60px - 70px);
            overflow: hidden;
            position: relative;
        }

        .main-stage-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bg-dark-base);
            position: relative;
            overflow: hidden;
        }

        /* Stage 1: Interactive Whiteboard */
        .whiteboard-stage-wrapper {
            flex: 1;
            position: relative;
            display: flex;
            background: #1e293b;
            overflow: hidden;
        }

        .whiteboard-floating-toolbar {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--bg-dark-surface);
            border: 1px solid var(--bg-dark-border);
            border-radius: 12px;
            padding: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            z-index: 40;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
        }

        .wb-tool-btn {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: var(--text-light-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .wb-tool-btn:hover {
            background: var(--bg-dark-elevated);
            color: #ffffff;
        }

        .wb-tool-btn.active {
            background: var(--primary-blue);
            color: #ffffff;
        }

        .wb-divider-h {
            height: 1px;
            background: var(--bg-dark-border);
            margin: 0.25rem 0;
        }

        /* Color Palette Popover / Strip */
        .color-palette-strip {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.35rem;
            padding: 0.25rem 0;
        }

        .color-dot-choice {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .color-dot-choice.active {
            border-color: #ffffff;
            transform: scale(1.2);
        }

        #whiteboard-container {
            flex: 1;
            width: 100%;
            height: 100%;
            background: #ffffff;
            cursor: crosshair;
            overflow: hidden;
            position: relative;
        }

        /* Stage 2: Video Grid Gallery */
        .video-grid-stage {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-dark-base);
            overflow-y: auto;
            align-content: center;
        }

        .video-feed-card {
            background: var(--bg-dark-surface);
            border: 1px solid var(--bg-dark-border);
            border-radius: 14px;
            aspect-ratio: 16 / 9;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .video-feed-element {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000000;
        }

        .video-avatar-fallback {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--primary-blue);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 800;
        }

        .video-user-badge {
            position: absolute;
            bottom: 0.75rem;
            left: 0.75rem;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Stage 3: Screen Share Stage */
        .screen-share-stage {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000000;
            position: relative;
        }

        #screen-video-feed {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Right Drawer / Sidebar */
        .classroom-sidebar-drawer {
            width: 360px;
            background: var(--bg-dark-surface);
            border-left: 1px solid var(--bg-dark-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            z-index: 40;
            transition: width 0.2s ease;
        }

        .sidebar-nav-tabs {
            height: 48px;
            border-bottom: 1px solid var(--bg-dark-border);
            display: flex;
            align-items: center;
            padding: 0 0.5rem;
            background: var(--bg-dark-elevated);
        }

        .sidebar-tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-light-muted);
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.6rem 0.25rem;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            transition: all 0.15s ease;
        }

        .sidebar-tab-btn.active {
            background: var(--bg-dark-surface);
            color: var(--primary-blue);
        }

        .sidebar-tab-content-area {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        /* Chat Panel */
        .chat-messages-scroll {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-right: 0.25rem;
        }

        .chat-bubble {
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .chat-bubble-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }

        .chat-sender-name {
            font-weight: 700;
            color: var(--primary-blue);
        }

        .chat-time {
            color: var(--text-light-muted);
            font-size: 0.7rem;
        }

        .chat-system-msg {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-light-muted);
            background: rgba(255, 255, 255, 0.04);
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            margin: 0.2rem 0;
        }

        .chat-input-row {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--bg-dark-border);
            display: flex;
            gap: 0.5rem;
        }

        .chat-text-input {
            flex: 1;
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
            color: var(--text-light-main);
            font-size: 0.85rem;
            outline: none;
        }

        .chat-text-input:focus {
            border-color: var(--primary-blue);
        }

        .btn-chat-send {
            background: var(--primary-blue);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        /* Participants Panel */
        .participant-card-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .participant-left {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .participant-avatar-initial {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary-blue);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .participant-name-txt {
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--text-light-main);
        }

        .participant-role-tag {
            font-size: 0.68rem;
            background: rgba(38, 119, 184, 0.15);
            color: var(--primary-blue);
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            margin-left: 0.3rem;
        }

        /* Math & Formulas Tool Panel */
        .math-tool-box {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .math-input-area {
            width: 100%;
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            border-radius: 8px;
            padding: 0.65rem;
            color: var(--text-light-main);
            font-family: monospace;
            font-size: 0.85rem;
            resize: vertical;
            outline: none;
        }

        .math-preview-card {
            background: #ffffff;
            color: #0f172a;
            border-radius: 8px;
            padding: 1rem;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: auto;
        }

        .btn-insert-formula {
            background: var(--primary-blue);
            color: #ffffff;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        /* Bottom Control Dock */
        .classroom-control-dock {
            height: 70px;
            background: var(--bg-dark-surface);
            border-top: 1px solid var(--bg-dark-border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.85rem;
            padding: 0 1rem;
            flex-shrink: 0;
            z-index: 50;
        }

        .dock-btn {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            background: var(--bg-dark-elevated);
            border: 1px solid var(--bg-dark-border);
            color: var(--text-light-main);
            padding: 0.45rem 0.95rem;
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            min-width: 68px;
        }

        .dock-btn i {
            font-size: 1.15rem;
        }

        .dock-btn:hover {
            background: #273549;
            border-color: #475569;
            transform: translateY(-1px);
        }

        .dock-btn.btn-active-danger {
            background: var(--primary-red);
            border-color: var(--primary-red);
            color: #ffffff;
        }

        .dock-btn.btn-active-blue {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: #ffffff;
        }

        .dock-btn.btn-active-green {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: #ffffff;
        }

        @media (max-width: 900px) {
            .classroom-sidebar-drawer {
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 320px;
                box-shadow: -10px 0 25px rgba(0, 0, 0, 0.5);
            }
        }
    </style>
</head>
<body x-data="virtualClassroom({
    roomId: '{{ $virtualClass->room_id }}',
    user: {
        id: {{ auth()->id() }},
        name: '{{ auth()->user()->name }}',
        role: '{{ auth()->user()->role ?? (auth()->user()->tutorProfile ? 'tutor' : 'student') }}'
    },
    isTutor: {{ auth()->user()->tutorProfile ? 'true' : 'false' }},
    topic: '{{ addslashes($virtualClass->topic ?? 'Interactive Session') }}',
    gradeLevel: '{{ addslashes($virtualClass->grade_level ?? 'All Grades') }}'
})">
    <div class="classroom-root">
        <!-- 1. Top Header -->
        <header class="classroom-header">
            <div class="header-left">
                <a href="/dashboard" class="room-logo">
                    <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="DigiLearn" style="height: 28px;">
                </a>
                <div class="room-info-pill">
                    <i class="fa-solid fa-graduation-cap" style="color: var(--primary-blue);"></i>
                    <span class="room-topic-title" x-text="topic"></span>
                    <span class="room-id-tag">#<span x-text="roomId"></span></span>
                </div>
            </div>

            <!-- Stage View Selector Tabs -->
            <div class="stage-tabs">
                <button type="button" class="stage-tab-btn" :class="{ 'active': activeStage === 'whiteboard' }" @click="setStage('whiteboard')">
                    <i class="fa-solid fa-chalkboard"></i>
                    <span>Whiteboard</span>
                </button>
                <button type="button" class="stage-tab-btn" :class="{ 'active': activeStage === 'video-grid' }" @click="setStage('video-grid')">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Video Gallery</span>
                </button>
                <button type="button" class="stage-tab-btn" :class="{ 'active': activeStage === 'screen-share' }" @click="setStage('screen-share')">
                    <i class="fa-solid fa-display"></i>
                    <span>Shared Screen</span>
                </button>
            </div>

            <div class="header-right">
                <div class="live-status-badge">
                    <span class="pulse-dot"></span>
                    <span>LIVE</span>
                </div>
                <button type="button" class="btn-leave-room" @click="leaveClassroom()">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Leave</span>
                </button>
            </div>
        </header>

        <!-- 2. Main Body (Stage + Sidebar) -->
        <div class="classroom-body">
            <!-- Stage Center -->
            <main class="main-stage-area">
                <!-- STAGE A: Interactive Whiteboard -->
                <div class="whiteboard-stage-wrapper" x-show="activeStage === 'whiteboard'">
                    <!-- Floating Whiteboard Toolbar -->
                    <div class="whiteboard-floating-toolbar">
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'select' }" @click="selectTool('select')" title="Select / Move">
                            <i class="fa-solid fa-arrow-pointer"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'pen' }" @click="selectTool('pen')" title="Draw Pen">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'highlighter' }" @click="selectTool('highlighter')" title="Highlighter">
                            <i class="fa-solid fa-highlighter"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'rect' }" @click="selectTool('rect')" title="Rectangle">
                            <i class="fa-regular fa-square"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'circle' }" @click="selectTool('circle')" title="Circle">
                            <i class="fa-regular fa-circle"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'line' }" @click="selectTool('line')" title="Line">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'text' }" @click="selectTool('text')" title="Add Text">
                            <i class="fa-solid fa-font"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" :class="{ 'active': activeWhiteboardTool === 'eraser' }" @click="selectTool('eraser')" title="Eraser">
                            <i class="fa-solid fa-eraser"></i>
                        </button>

                        <div class="wb-divider-h"></div>

                        <!-- Color Palette -->
                        <div class="color-palette-strip">
                            <div class="color-dot-choice" style="background: #2677B8;" :class="{ 'active': whiteboardColor === '#2677B8' }" @click="setWhiteboardColor('#2677B8')"></div>
                            <div class="color-dot-choice" style="background: #E11E2D;" :class="{ 'active': whiteboardColor === '#E11E2D' }" @click="setWhiteboardColor('#E11E2D')"></div>
                            <div class="color-dot-choice" style="background: #10b981;" :class="{ 'active': whiteboardColor === '#10b981' }" @click="setWhiteboardColor('#10b981')"></div>
                            <div class="color-dot-choice" style="background: #f59e0b;" :class="{ 'active': whiteboardColor === '#f59e0b' }" @click="setWhiteboardColor('#f59e0b')"></div>
                            <div class="color-dot-choice" style="background: #7c3aed;" :class="{ 'active': whiteboardColor === '#7c3aed' }" @click="setWhiteboardColor('#7c3aed')"></div>
                            <div class="color-dot-choice" style="background: #0f172a;" :class="{ 'active': whiteboardColor === '#0f172a' }" @click="setWhiteboardColor('#0f172a')"></div>
                        </div>

                        <div class="wb-divider-h"></div>

                        <!-- Undo / Redo / Clear -->
                        <button type="button" class="wb-tool-btn" @click="undoWhiteboard()" title="Undo">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" @click="redoWhiteboard()" title="Redo">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                        <button type="button" class="wb-tool-btn" @click="clearWhiteboard()" title="Clear Canvas" style="color: var(--primary-red);">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>

                    <!-- Canvas Container -->
                    <div id="whiteboard-container">
                        <canvas id="whiteboard-canvas"></canvas>
                    </div>
                </div>

                <!-- STAGE B: Video Grid Gallery -->
                <div class="video-grid-stage" x-show="activeStage === 'video-grid'" id="remote-videos-grid">
                    <!-- Local Self Preview Tile -->
                    <div class="video-feed-card">
                        <video id="local-video-preview" autoplay playsinline muted class="video-feed-element" x-show="!isVideoMuted"></video>
                        <div class="video-avatar-fallback" x-show="isVideoMuted">
                            <span x-text="currentUser.name.charAt(0)"></span>
                        </div>
                        <div class="video-user-badge">
                            <i class="fa-solid" :class="isAudioMuted ? 'fa-microphone-slash text-red-500' : 'fa-microphone text-green-500'"></i>
                            <span>You (<span x-text="currentUser.name"></span>)</span>
                        </div>
                    </div>
                </div>

                <!-- STAGE C: Screen Share Stage -->
                <div class="screen-share-stage" x-show="activeStage === 'screen-share'">
                    <!-- Active screen share feed -->
                    <video id="screen-video-feed" autoplay playsinline x-show="isScreenSharing" style="max-width: 100%; max-height: 100%; object-fit: contain;"></video>

                    <!-- Empty state: no one is sharing yet -->
                    <div x-show="!isScreenSharing" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.25rem; text-align: center; padding: 2rem;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(38, 119, 184, 0.12); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-display" style="font-size: 2rem; color: var(--primary-blue);"></i>
                        </div>
                        <div>
                            <p style="font-size: 1.1rem; font-weight: 700; color: var(--text-light-main); margin-bottom: 0.35rem;">No Screen Being Shared</p>
                            <p style="font-size: 0.85rem; color: var(--text-light-muted); max-width: 340px;">Share your screen to present slides, demonstrate software, or show your browser to other participants.</p>
                        </div>
                        <button type="button" @click="toggleScreenShare()" style="background: var(--primary-blue); color: #fff; border: none; padding: 0.65rem 1.5rem; border-radius: 10px; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: background 0.15s ease;">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                            <span>Start Sharing Your Screen</span>
                        </button>
                    </div>
                </div>
            </main>

            <!-- Right Drawer / Sidebar -->
            <aside class="classroom-sidebar-drawer" x-show="isSidebarOpen">
                <div class="sidebar-nav-tabs">
                    <button type="button" class="sidebar-tab-btn" :class="{ 'active': activeSidebarTab === 'chat' }" @click="activeSidebarTab = 'chat'">
                        <i class="fa-regular fa-comments"></i>
                        <span>Chat</span>
                    </button>
                    <button type="button" class="sidebar-tab-btn" :class="{ 'active': activeSidebarTab === 'participants' }" @click="activeSidebarTab = 'participants'">
                        <i class="fa-solid fa-users"></i>
                        <span>Class (<span x-text="participants.length"></span>)</span>
                    </button>
                    <button type="button" class="sidebar-tab-btn" :class="{ 'active': activeSidebarTab === 'materials' }" @click="activeSidebarTab = 'materials'">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Materials</span>
                    </button>
                    <button type="button" class="sidebar-tab-btn" :class="{ 'active': activeSidebarTab === 'math' }" @click="activeSidebarTab = 'math'">
                        <i class="fa-solid fa-square-root-variable"></i>
                        <span>Math</span>
                    </button>
                </div>

                <div class="sidebar-tab-content-area">
                    <!-- Tab 1: Live Chat -->
                    <div x-show="activeSidebarTab === 'chat'" style="display: flex; flex-direction: column; height: 100%;">
                        <div class="chat-messages-scroll" id="chat-messages-container">
                            <div class="chat-system-msg">
                                <i class="fa-solid fa-shield-halved"></i> Welcome to the session! All messages are encrypted.
                            </div>
                            <template x-for="msg in messages" :key="msg.id">
                                <div>
                                    <template x-if="msg.is_system">
                                        <div class="chat-system-msg" x-text="msg.content"></div>
                                    </template>
                                    <template x-if="!msg.is_system">
                                        <div class="chat-bubble">
                                            <div class="chat-bubble-header">
                                                <span class="chat-sender-name" x-text="msg.user_name"></span>
                                                <span class="chat-time" x-text="msg.created_at"></span>
                                            </div>
                                            <div style="color: var(--text-light-main);" x-text="msg.content"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="chat-input-row">
                            <input type="text" class="chat-text-input" placeholder="Type message..." x-model="newMessage" @keydown.enter="sendMessage()">
                            <button type="button" class="btn-chat-send" @click="sendMessage()">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 2: Participants -->
                    <div x-show="activeSidebarTab === 'participants'">
                        <template x-for="user in participants" :key="user.id">
                            <div class="participant-card-item">
                                <div class="participant-left">
                                    <div class="participant-avatar-initial" x-text="user.avatar_initial || user.name.charAt(0)"></div>
                                    <div>
                                        <span class="participant-name-txt" x-text="user.name"></span>
                                        <span class="participant-role-tag" x-text="user.role"></span>
                                    </div>
                                </div>
                                <div>
                                    <i class="fa-solid fa-microphone text-green-500" style="font-size: 0.85rem;"></i>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Tab 3: Materials & PDF Slide Dropper -->
                    <div x-show="activeSidebarTab === 'materials'">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.4rem;">Upload Lesson Slide / PDF</label>
                            <input type="file" accept=".pdf,.png,.jpg,.jpeg" @change="uploadMaterial($event)" style="font-size: 0.78rem; color: var(--text-light-muted);">
                        </div>
                        <template x-for="mat in uploadedMaterials" :key="mat.url">
                            <div class="participant-card-item">
                                <span x-text="mat.filename"></span>
                                <a :href="mat.url" target="_blank" style="color: var(--primary-blue); font-size: 0.8rem;"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                            </div>
                        </template>
                    </div>

                    <!-- Tab 4: LaTeX Math Formula Helper -->
                    <div x-show="activeSidebarTab === 'math'">
                        <div class="math-tool-box">
                            <label style="font-size: 0.8rem; font-weight: 700;">LaTeX Equation Editor</label>
                            <textarea class="math-input-area" rows="3" x-model="mathInput" @input="updateMathPreview()"></textarea>

                            <label style="font-size: 0.75rem; color: var(--text-light-muted);">Live KaTeX Rendered Preview:</label>
                            <div class="math-preview-card" x-html="mathPreviewHtml"></div>

                            <button type="button" class="btn-insert-formula" @click="insertMathOntoWhiteboard()">
                                <i class="fa-solid fa-stamp"></i>
                                <span>Place on Whiteboard</span>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- 3. Bottom Control Dock -->
        <footer class="classroom-control-dock">
            <!-- Mute Audio -->
            <button type="button" class="dock-btn" :class="{ 'btn-active-danger': isAudioMuted }" @click="toggleAudio()">
                <i class="fa-solid" :class="isAudioMuted ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                <span x-text="isAudioMuted ? 'Unmute' : 'Mute'"></span>
            </button>

            <!-- Video Camera -->
            <button type="button" class="dock-btn" :class="{ 'btn-active-danger': isVideoMuted }" @click="toggleVideo()">
                <i class="fa-solid" :class="isVideoMuted ? 'fa-video-slash' : 'fa-video'"></i>
                <span x-text="isVideoMuted ? 'Start Video' : 'Stop Video'"></span>
            </button>

            <!-- Screen Share -->
            <button type="button" class="dock-btn" :class="{ 'btn-active-blue': isScreenSharing }" @click="toggleScreenShare()">
                <i class="fa-solid fa-display"></i>
                <span x-text="isScreenSharing ? 'Stop Share' : 'Share Screen'"></span>
            </button>

            <!-- Whiteboard Toggle -->
            <button type="button" class="dock-btn" :class="{ 'btn-active-blue': activeStage === 'whiteboard' }" @click="setStage('whiteboard')">
                <i class="fa-solid fa-chalkboard"></i>
                <span>Whiteboard</span>
            </button>

            <!-- Raise Hand -->
            <button type="button" class="dock-btn" :class="{ 'btn-active-green': isHandRaised }" @click="toggleHandRaise()">
                <i class="fa-solid fa-hand"></i>
                <span x-text="isHandRaised ? 'Hand Raised' : 'Raise Hand'"></span>
            </button>

            <!-- Toggle Drawer -->
            <button type="button" class="dock-btn" @click="isSidebarOpen = !isSidebarOpen">
                <i class="fa-solid fa-bars-staggered"></i>
                <span x-text="isSidebarOpen ? 'Hide Panel' : 'Show Panel'"></span>
            </button>
        </footer>
    </div>
</body>
</html>
