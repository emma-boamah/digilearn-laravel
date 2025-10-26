<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
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

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo img {
            height: 36px;
            width: auto;
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

        /* Main Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Left Sidebar */
        .quiz-sidebar {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .quiz-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .timer-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .timer {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-hover));
            color: var(--white);
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            box-shadow: var(--shadow-md);
            min-width: 120px;
            text-align: center;
        }

        .timer.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            animation: pulse 1s infinite;
        }

        .timer.danger {
            background: linear-gradient(135deg, var(--primary-red), #dc2626);
            animation: pulse 0.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .questions-section {
            margin-bottom: 2rem;
        }

        .questions-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
            text-align: center;
        }

        .questions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }

        .question-number {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid var(--gray-200);
            background-color: var(--white);
            color: var(--gray-600);
        }

        .question-number:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        .question-number.current {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        .question-number.answered {
            background-color: var(--secondary-blue);
            color: var(--white);
            border-color: var(--secondary-blue);
        }

        .quiz-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .submit-quiz-btn {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-blue-hover));
            color: var(--white);
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .submit-quiz-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .submit-quiz-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Right Content */
        .quiz-content {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .question-label {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .answer-instruction {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .question-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .options-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: var(--white);
        }

        .option:hover {
            border-color: var(--gray-300);
            background-color: var(--gray-50);
        }

        .option.selected {
            border-color: var(--primary-red);
            background-color: rgba(225, 30, 45, 0.05);
        }

        .option-letter {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--gray-200);
            color: var(--gray-700);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .option.selected .option-letter {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .option-text {
            font-size: 1rem;
            color: var(--gray-700);
            font-weight: 500;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .nav-btn {
            padding: 0.875rem 1.5rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.75rem;
            background-color: var(--white);
            color: var(--gray-700);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover {
            border-color: var(--gray-400);
            background-color: var(--gray-50);
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .nav-btn.primary {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        .nav-btn.primary:hover {
            background-color: var(--primary-red-hover);
            border-color: var(--primary-red-hover);
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .main-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1rem;
            }

            .quiz-sidebar {
                position: static;
                order: -1;
            }

            .questions-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        @media (max-width: 768px) {
            .quiz-sidebar {
                padding: 1.5rem;
            }

            .quiz-content {
                padding: 1.5rem;
            }

            .options-container {
                grid-template-columns: 1fr;
            }

            .navigation-buttons {
                flex-direction: column;
            }

            .questions-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .question-text {
                font-size: 1.125rem;
            }
        }

        @media (max-width: 480px) {
            .quiz-sidebar {
                padding: 1rem;
            }

            .quiz-content {
                padding: 1rem;
            }

            .timer {
                font-size: 1.25rem;
                padding: 0.75rem 1rem;
            }

            .questions-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .question-number {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }
        }

        /* Quiz Completion Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
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

        .modal-overlay.active .modal {
            transform: translate(-50%, -50%) scale(1);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .modal-btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn.primary {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .modal-btn.secondary {
            background-color: var(--gray-200);
            color: var(--gray-700);
        }

        .modal-btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <button class="back-button" onclick="confirmExit()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
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
            
            <x-user-avatar :user="auth()->user()" :size="36" class="border-2 border-white" />
        </div>
    </div>

    <!-- Main Layout -->
    <div class="main-layout">
        <!-- Left Sidebar -->
        <div class="quiz-sidebar">
            <h2 class="quiz-title">{{ $quiz['title'] ?? 'Living and non-living things Quiz 1' }}</h2>
            
            <div class="timer-container">
                <div class="timer" id="timer">2:59</div>
            </div>
            
            <div class="questions-section">
                <h3 class="questions-title">Questions</h3>
                <div class="questions-grid" id="questionsGrid">
                    <!-- Questions will be populated by JavaScript -->
                </div>
            </div>
            
            <div class="quiz-actions">
                <button class="submit-quiz-btn" id="submitQuizBtn" onclick="submitQuiz()">Submit Quiz</button>
            </div>
        </div>

        <!-- Right Content -->
        <div class="quiz-content">
            <div class="question-header">
                <span class="question-label" id="questionLabel">Question 1</span>
                <span class="answer-instruction">Select your answer</span>
            </div>
            
            <div class="question-text" id="questionText">
                <!-- Question text will be populated by JavaScript -->
            </div>
            
            <div class="options-container" id="optionsContainer">
                <!-- Options will be populated by JavaScript -->
            </div>
            
            <div class="navigation-buttons">
                <button class="nav-btn" id="prevBtn" onclick="previousQuestion()" disabled>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </button>
                <button class="nav-btn primary" id="nextBtn" onclick="nextQuestion()">
                    Next
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Quiz Completion Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Submit Quiz?</h3>
                <p class="modal-subtitle">Are you sure you want to submit your quiz? You cannot change your answers after submission.</p>
            </div>
            <div class="modal-actions">
                <button class="modal-btn secondary" onclick="closeModal()">Cancel</button>
                <button class="modal-btn primary" onclick="confirmSubmit()">Submit</button>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Quiz data and state
        let currentQuestion = 0;
        let timeLimitMinutes = {{ $quiz['time_limit_minutes'] ?? 3 }};
        let timeRemaining = timeLimitMinutes * 60; // Use time limit from database
        let answers = {};
        let timerInterval;

        const questions = @json($quiz['questions'] ?? []);

        document.addEventListener('DOMContentLoaded', function() {
            initializeQuiz();
            startTimer();
        });

        function initializeQuiz() {
            renderQuestionsGrid();
            renderCurrentQuestion();
            updateNavigationButtons();
        }

        function renderQuestionsGrid() {
            const grid = document.getElementById('questionsGrid');
            grid.innerHTML = '';
            
            questions.forEach((question, index) => {
                const questionBtn = document.createElement('div');
                questionBtn.className = 'question-number';
                questionBtn.textContent = index + 1;
                questionBtn.onclick = () => goToQuestion(index);
                
                if (index === currentQuestion) {
                    questionBtn.classList.add('current');
                } else if (answers[index] !== undefined) {
                    questionBtn.classList.add('answered');
                }
                
                grid.appendChild(questionBtn);
            });
        }

        function renderCurrentQuestion() {
            if (!questions || questions.length === 0) {
                console.log('No questions available');
                document.getElementById('questionText').textContent = 'No questions available for this quiz.';
                return;
            }

            const question = questions[currentQuestion];
            console.log('Rendering question:', question);

            document.getElementById('questionLabel').textContent = `Question ${currentQuestion + 1}`;
            document.getElementById('questionText').textContent = question ? question.question : 'Question not available';

            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';

            if (question && question.options && Array.isArray(question.options)) {
                question.options.forEach((option, index) => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'option';
                    optionDiv.onclick = () => selectOption(index);

                    if (answers[currentQuestion] === index) {
                        optionDiv.classList.add('selected');
                    }

                    optionDiv.innerHTML = `
                        <div class="option-letter">${String.fromCharCode(65 + index)}</div>
                        <div class="option-text">${option}</div>
                    `;

                    optionsContainer.appendChild(optionDiv);
                });
            } else {
                console.log('No options available for this question');
                optionsContainer.innerHTML = '<p>No answer options available.</p>';
            }
        }

        function selectOption(optionIndex) {
            answers[currentQuestion] = optionIndex;
            renderCurrentQuestion();
            renderQuestionsGrid();
            updateNavigationButtons(); // Update button states when answer is selected
        }

        function goToQuestion(questionIndex) {
            currentQuestion = questionIndex;
            renderCurrentQuestion();
            renderQuestionsGrid();
            updateNavigationButtons();
        }

        function previousQuestion() {
            if (currentQuestion > 0) {
                currentQuestion--;
                renderCurrentQuestion();
                renderQuestionsGrid();
                updateNavigationButtons();
            }
        }

        function nextQuestion() {
            if (currentQuestion < questions.length - 1) {
                currentQuestion++;
                renderCurrentQuestion();
                renderQuestionsGrid();
                updateNavigationButtons();
            }
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitQuizBtn');

            prevBtn.disabled = currentQuestion === 0;

            // Enable submit button if all questions are answered, regardless of timer
            const allAnswered = Object.keys(answers).length === questions.length;
            submitBtn.disabled = !allAnswered;

            // Change next button to "Finish" on last question
            if (currentQuestion === questions.length - 1) {
                nextBtn.textContent = 'Finish';
                nextBtn.onclick = submitQuiz;
                nextBtn.disabled = false; // Allow finishing even if timer is running
            } else {
                nextBtn.innerHTML = `
                    Next
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                `;
                nextBtn.onclick = nextQuestion;
                nextBtn.disabled = false;
            }

            // Update submit button state in real-time
            if (submitBtn) {
                submitBtn.disabled = !allAnswered;
            }
        }

        function startTimer() {
            const timerElement = document.getElementById('timer');
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                // Change timer color based on time remaining
                if (timeRemaining <= 30) {
                    timerElement.className = 'timer danger';
                } else if (timeRemaining <= 60) {
                    timerElement.className = 'timer warning';
                }
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    autoSubmitQuiz();
                }
            }, 1000);
        }

        function submitQuiz() {
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        function confirmSubmit() {
            clearInterval(timerInterval);

            // Calculate score
            let correctAnswers = 0;
            questions.forEach((question, index) => {
                const userAnswer = answers[index];
                if (userAnswer !== undefined && userAnswer === question.correct_answer) {
                    correctAnswers++;
                }
            });

            const totalQuestions = questions.length;
            const percentage = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;

            // Show loading state
            const submitBtn = document.querySelector('.modal-btn.primary');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;

            // Submit the quiz
            console.log('Starting quiz submission...');
            console.log('Submitting to:', `/quiz/{{ $quiz['id'] }}/submit`);
            console.log('Form data:', {
                answers: answers,
                time_spent: (timeLimitMinutes * 60) - timeRemaining
            });

            // Create a form element and submit it normally
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/quiz/{{ $quiz['id'] }}/submit`;
            form.setAttribute('data-quiz-form', 'true'); // Add the data attribute for anti-cheat detection

            // Add hidden inputs for the data
            const answersInput = document.createElement('input');
            answersInput.type = 'hidden';
            answersInput.name = 'answers';
            answersInput.value = JSON.stringify(answers);
            form.appendChild(answersInput);

            const timeSpentInput = document.createElement('input');
            timeSpentInput.type = 'hidden';
            timeSpentInput.name = 'time_spent';
            timeSpentInput.value = (timeLimitMinutes * 60) - timeRemaining;
            form.appendChild(timeSpentInput);

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);

            // Append to body and submit
            document.body.appendChild(form);
            console.log('Submitting form...');
            form.submit();
        }

        function autoSubmitQuiz() {
            alert('Time is up! Your quiz will be submitted automatically.');
            confirmSubmit();
        }

        function confirmExit() {
            if (confirm('Are you sure you want to exit the quiz? Your progress will be lost.')) {
                clearInterval(timerInterval);
                window.history.back();
            }
        }

        // Prevent accidental page refresh (only when quiz is active and not submitting)
        window.addEventListener('beforeunload', function(e) {
            if (timeRemaining > 0 && Object.keys(answers).length > 0 && !document.querySelector('.modal-overlay.active')) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                previousQuestion();
            } else if (e.key === 'ArrowRight') {
                nextQuestion();
            } else if (e.key >= '1' && e.key <= '4') {
                const optionIndex = parseInt(e.key) - 1;
                selectOption(optionIndex);
            }
        });
    </script>
    @include('dashboard.quiz.partials.anti-cheat')
</body>
</html>
