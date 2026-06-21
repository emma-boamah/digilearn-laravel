@push('extra-css')
<style>
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
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
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

    .toolbar-tool:hover {
        background: #f1f5f9;
        color: #2563eb;
    }

    .toolbar-tool.active {
        background: #e0e7ff;
        color: #4338ca;
        border-color: #c7d2fe;
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

    /* Keywords Tag Editor */
    .keywords-section {
        border-top: 1px dashed #e5e7eb;
        padding-top: 12px;
    }

    .keywords-body {
        overflow: hidden;
        max-height: 200px;
        transition: max-height 0.3s ease, opacity 0.3s ease;
        opacity: 1;
    }

    .keywords-body.collapsed {
        max-height: 0;
        opacity: 0;
        padding: 0;
    }

    .keywords-chevron {
        transition: transform 0.3s ease;
    }

    .keywords-chevron.rotated {
        transform: rotate(-90deg);
    }

    .keywords-tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        padding: 6px 8px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        min-height: 36px;
        cursor: text;
    }

    .keywords-tags-container:focus-within {
        border-color: #f59e0b;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.15);
    }

    .keyword-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fef3c7;
        color: #92400e;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 999px;
        border: 1px solid #fcd34d;
        line-height: 1.2;
        white-space: nowrap;
    }

    .keyword-remove {
        background: none;
        border: none;
        color: #b45309;
        cursor: pointer;
        font-size: 0.875rem;
        line-height: 1;
        padding: 0 2px;
        opacity: 0.6;
        transition: opacity 0.15s ease;
    }

    .keyword-remove:hover {
        opacity: 1;
        color: #dc2626;
    }

    .keyword-input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.75rem;
        color: #78350f;
        min-width: 100px;
        flex: 1;
        padding: 2px 0;
    }

    .keyword-input::placeholder {
        color: #d97706;
        opacity: 0.5;
    }

    .sub-keywords-section {
        border-top: none;
        padding-top: 0;
    }

    .sub-keywords-section .keywords-tags-container {
        padding: 4px 6px;
        min-height: 30px;
    }

    .sub-keywords-section .keyword-tag {
        font-size: 0.6875rem;
        padding: 2px 6px;
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

    /* Quiz Navigation & Pagination */
    .quiz-navigation-wrapper {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .quiz-nav-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-start;
    }

    .quiz-nav-item {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: white;
        border: 1px solid #e2e8f0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .quiz-nav-item:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        color: #3b82f6;
    }

    .quiz-nav-item.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }

    .quiz-nav-item.mcq { border-bottom: 3px solid #3b82f6; }
    .quiz-nav-item.essay { border-bottom: 3px solid #8b5cf6; }

    .pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .nav-btn-group {
        display: flex;
        gap: 12px;
    }

    .btn-nav {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569;
        cursor: pointer;
    }

    .btn-nav:hover:not(:disabled) {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .btn-nav:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-nav.primary {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .btn-nav.primary:hover:not(:disabled) {
        background: #2563eb;
    }

    /* Question Item Visibility */
    .question-item {
        display: none; /* Hidden by default */
        animation: slideIn 0.3s ease-out;
    }

    .question-item.active-question {
        display: block !important;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* MathField Styles */
    math-field {
        width: 100%;
        min-height: 48px;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
        font-size: 1.1rem;
        outline: none;
        margin-top: 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    math-field:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
</style>
@endpush

@push('scripts')
<script defer src="https://unpkg.com/mathlive"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    /**
     * Shared Quiz Builder Functions
     * Expects a global 'uploadData' object with 'quiz' property.
     */
    let currentQuestionIndex = 0;

    function initializeQuizStep() {
        const addMcqBtn = document.getElementById('addMcqBtn');
        const addEssayBtn = document.getElementById('addEssayBtn');

        if (!addMcqBtn || !addEssayBtn) {
            console.error('Quiz builder elements not found');
            return;
        }

        // Clean up any existing listeners by replacing the elements (standard trick for single-page style apps)
        // or just ensure we don't double-attach if possible.
        const newMcqBtn = addMcqBtn.cloneNode(true);
        const newEssayBtn = addEssayBtn.cloneNode(true);
        
        addMcqBtn.parentNode.replaceChild(newMcqBtn, addMcqBtn);
        addEssayBtn.parentNode.replaceChild(newEssayBtn, addEssayBtn);

        newMcqBtn.addEventListener('click', () => addQuestion('mcq'));
        newEssayBtn.addEventListener('click', () => addQuestion('essay'));

        // Initialize Pagination Controls
        const prevBtn = document.getElementById('prevQuestionBtn');
        const nextBtn = document.getElementById('nextQuestionBtn');

        if (prevBtn) prevBtn.addEventListener('click', () => showQuestion(currentQuestionIndex - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => showQuestion(currentQuestionIndex + 1));

        renderQuestionNavigation();
        showQuestion(0);
    }

    function initializeQuizSettings() {
        const difficultySelect = document.getElementById('quiz_difficulty');
        const timeLimitInput = document.getElementById('quiz_time_limit');

        if (difficultySelect) {
            difficultySelect.value = uploadData.quiz.difficulty_level || 'medium';
            difficultySelect.addEventListener('change', (e) => {
                uploadData.quiz.difficulty_level = e.target.value;
            });
        }

        if (timeLimitInput) {
            timeLimitInput.value = uploadData.quiz.time_limit_minutes || 15;
            timeLimitInput.addEventListener('input', (e) => {
                uploadData.quiz.time_limit_minutes = parseInt(e.target.value) || 15;
            });
        }
    }

    function addQuestion(type, existingData = null) {
        const questionsList = document.getElementById('questionsList');
        if (!questionsList) {
            console.error('Questions list element not found');
            return;
        }

        const questionId = existingData ? (existingData.id || Date.now()) : Date.now();
        const question = existingData || {
            id: questionId,
            type: type,
            question: '',
            preamble: null,
            options: type === 'mcq' ? ['', '', '', ''] : null,
            sub_questions: [],
            correct_answer: type === 'mcq' ? 0 : '',
            points: 1,
            image: null,
            imageFile: null
        };
        
        // Ensure sub_questions exists for existingData
        if (!question.sub_questions) {
            question.sub_questions = [];
        }

        if (!existingData) {
            uploadData.quiz.questions.push(question);
        }

        const questionElement = createQuestionElement(question);
        questionsList.appendChild(questionElement);

        renderQuestionNavigation();
        showQuestion(uploadData.quiz.questions.length - 1);
    }

    function renderQuestionNavigation() {
        const navGrid = document.getElementById('quizNavGrid');
        if (!navGrid) return;

        navGrid.innerHTML = '';
        uploadData.quiz.questions.forEach((q, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `quiz-nav-item ${q.type} ${index === currentQuestionIndex ? 'active' : ''}`;
            btn.textContent = index + 1;
            btn.addEventListener('click', () => showQuestion(index));
            navGrid.appendChild(btn);
        });
    }

    function showQuestion(index) {
        const questions = uploadData.quiz.questions;
        if (index < 0 || index >= questions.length && questions.length > 0) return;

        currentQuestionIndex = index;

        // Toggle question visibility
        const questionElements = document.querySelectorAll('.question-item');
        questionElements.forEach((el, idx) => {
            if (idx === index) {
                el.classList.add('active-question');
            } else {
                el.classList.remove('active-question');
            }
        });

        // Update nav active state
        document.querySelectorAll('.quiz-nav-item').forEach((btn, idx) => {
            btn.classList.toggle('active', idx === index);
        });

        // Update Pagination Buttons
        const prevBtn = document.getElementById('prevQuestionBtn');
        const nextBtn = document.getElementById('nextQuestionBtn');
        const currentLabel = document.getElementById('currentQuestionLabel');

        if (prevBtn) prevBtn.disabled = (index <= 0);
        if (nextBtn) nextBtn.disabled = (index >= questions.length - 1);
        if (currentLabel) {
            currentLabel.textContent = questions.length > 0 
                ? `Question ${index + 1} of ${questions.length}`
                : 'No questions added';
        }
    }

    function setupQuestionImageUpload(questionElement, question) {
        const uploadArea = questionElement.querySelector('.question-image-upload-area');
        const fileInput = questionElement.querySelector('.question-image-input');
        const uploadDiv = questionElement.querySelector(`#questionImageUpload_${question.id}`);
        const previewDiv = questionElement.querySelector(`#questionImagePreview_${question.id}`);
        const previewImg = previewDiv ? previewDiv.querySelector('.question-preview-img') : null;
        const removeImageBtn = previewDiv ? previewDiv.querySelector('.remove-question-image') : null;

        if (uploadArea) {
            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;

                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    return;
                }

                const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload a PNG, JPG, or WEBP image');
                    return;
                }

                const objectUrl = URL.createObjectURL(file);
                question.imageFile = file;
                question.image = objectUrl;

                if (previewImg) previewImg.src = objectUrl;
                if (uploadDiv) uploadDiv.classList.add('hidden');
                if (previewDiv) previewDiv.classList.remove('hidden');
            });
        }

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', () => {
                if (question.image && question.image.startsWith('blob:')) {
                    URL.revokeObjectURL(question.image);
                }

                question.imageFile = null;
                question.image = null;

                if (fileInput) fileInput.value = '';
                if (uploadDiv) uploadDiv.classList.remove('hidden');
                if (previewDiv) previewDiv.classList.add('hidden');
            });
        }
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
                        <button type="button" class="toolbar-tool insert-table-btn" data-command="insertTable" title="Insert Table">
                            <i class="fas fa-table"></i>
                        </button>
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
                    <div class="mb-6 editor-wrapper essay-question-main-text">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 main-question-label">Question Text</label>
                        <p class="text-xs text-gray-500 mb-2 main-question-hint hidden">Leave this blank to start directly with Question 1a, 1b, etc.</p>
                         ${toolbarHtml}
                         <div class="rich-text-editor question-text" contenteditable="true" 
                             placeholder="Type the question or shared context here..."
                             aria-label="Question text">${question.question}</div>
                    </div>

                    <div class="sub-questions-container" id="subQuestionsContainer_${question.id}">
                        <!-- Sub-questions will be injected here -->
                    </div>

                    <div class="mb-6">
                        <button type="button" class="add-sub-question-btn" id="addSubQuestionBtn_${question.id}">
                            <i class="fas fa-plus-circle"></i> Add Sub-part (a, b, c...)
                        </button>
                    </div>

                    <!-- Essay Sample Answer -->
                    <div class="mb-6 editor-wrapper">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Reference Answer (Sample)</label>
                         ${toolbarHtml}
                         <div class="rich-text-editor correct-answer" contenteditable="true" 
                             placeholder="Describe the expected answer for grading reference..."
                             aria-label="Sample answer">${question.correct_answer}</div>
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
                    } else if (tool.classList.contains('insert-table-btn')) {
                        tool.addEventListener('click', (e) => {
                            e.preventDefault();
                            handleTableInsert(editor);
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

            function handleTableInsert(editor) {
                editor.focus();
                const tableHtml = `
                    <div class="table-responsive my-3 overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300 text-sm" style="min-width: 100%;">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2 text-left"><br></th>
                                    <th class="border border-gray-300 p-2 text-left"><br></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 p-2 text-left"><br></td>
                                    <td class="border border-gray-300 p-2 text-left"><br></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 p-2 text-left"><br></td>
                                    <td class="border border-gray-300 p-2 text-left"><br></td>
                                </tr>
                            </tbody>
                        </table>
                    </div><p><br></p>
                `;
                document.execCommand('insertHTML', false, tableHtml);
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
                } else if (editor.classList.contains('sub-question-sample-answer')) {
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

            // Sub-question Logic
            if (question.type === 'essay') {
                const addSubBtn = div.querySelector(`#addSubQuestionBtn_${question.id}`);
                const subContainer = div.querySelector(`#subQuestionsContainer_${question.id}`);

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
                    const subLabel = String.fromCharCode(97 + (question.sub_questions ? question.sub_questions.length : 0)); // a, b, c...
                    const subQuestion = {
                        id: subId,
                        label: subLabel,
                        text: '',
                        points: 1
                    };
                    if (!question.sub_questions) question.sub_questions = [];
                    question.sub_questions.push(subQuestion);
                    
                    const subEl = createSubQuestionElement(subQuestion, question, div);
                    subContainer.appendChild(subEl);
                    updateTotalPoints();
                });

                function updateTotalPoints() {
                    const mainLabel = div.querySelector('.main-question-label');
                    const mainHint = div.querySelector('.main-question-hint');

                    // Determine parent question number
                    const qItems = Array.from(document.querySelectorAll('.question-item'));
                    const qIndex = qItems.indexOf(div) + 1;

                    if (question.sub_questions && question.sub_questions.length > 0) {
                        if (mainLabel) mainLabel.textContent = 'Shared Content / Instructions (Optional)';
                        if (mainHint) mainHint.classList.remove('hidden');

                        // Re-label sub-questions
                        div.querySelectorAll('.sub-question-item').forEach((item, idx) => {
                            const label = String.fromCharCode(97 + idx); // a, b, c...
                            const labelSpan = item.querySelector('.sub-question-label');
                            if (labelSpan) {
                                // Dynamic label style: Question Number + letter (e.g. 1a) for first part, just letter (e.g. b) for others
                                labelSpan.textContent = (idx === 0) ? `${qIndex}${label})` : `${label})`;
                            }
                            const sq = question.sub_questions.find(s => s.id == item.dataset.subId);
                            if (sq) sq.label = label;
                        });

                        const total = question.sub_questions.reduce((sum, sq) => sum + sq.points, 0);
                        question.points = total;
                        const questionPointsInp = div.querySelector('.question-points');
                        if (questionPointsInp) {
                            questionPointsInp.value = total;
                            questionPointsInp.readOnly = true;
                            questionPointsInp.classList.add('bg-gray-50');
                        }
                    } else {
                        if (mainLabel) mainLabel.textContent = 'Question Text';
                        if (mainHint) mainHint.classList.add('hidden');
                        const questionPointsInp = div.querySelector('.question-points');
                        if (questionPointsInp) {
                            questionPointsInp.readOnly = false;
                            questionPointsInp.classList.remove('bg-gray-50');
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
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Question Part Text:</label>
                            ${toolbarHtml}
                            <div class="rich-text-editor sub-question-text" contenteditable="true" 
                                 placeholder="Type sub-question here..."
                                 aria-label="Sub-question text">${subQuestion.text || ''}</div>
                        </div>
                        <div class="editor-wrapper mb-3">
                            <label class="text-xs font-bold text-success-green uppercase mb-2 block flex items-center gap-2">
                                <i class="fas fa-check-double"></i> Marking Scheme / Sample Answer (for Auto-Grading)
                            </label>
                            ${toolbarHtml}
                            <div class="rich-text-editor sub-question-sample-answer" contenteditable="true" 
                                 placeholder="Type expected answer or key points here..."
                                 aria-label="Sample answer">${subQuestion.sample_answer || ''}</div>
                        </div>
                        <div class="sub-question-footer">
                            <label class="text-xs font-bold text-gray-500 uppercase">Marks for this part:</label>
                            <input type="number" class="w-20 px-2 py-1 border border-gray-300 rounded sub-points" 
                                   value="${subQuestion.points}" min="1">
                        </div>
                    `;

                    // Initialize editors in sub-question
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

                    return subDiv;
                }
            } // end if essay

            // MCQ Logic
            if (question.type === 'mcq') {
                const correctAnswers = div.querySelectorAll('.correct-answer');
                correctAnswers.forEach((radio, index) => {
                    radio.addEventListener('change', () => {
                        question.correct_answer = index;
                    });
                });
            }

            // Image upload handling
            setupQuestionImageUpload(div, question);

            // Standard event listeners
            const removeBtn = div.querySelector('.remove-question');
            const questionPoints = div.querySelector('.question-points');

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    uploadData.quiz.questions = uploadData.quiz.questions.filter(q => q.id !== question.id);
                    div.remove();
                    
                    // Update navigation and current index
                    if (currentQuestionIndex >= uploadData.quiz.questions.length) {
                        currentQuestionIndex = Math.max(0, uploadData.quiz.questions.length - 1);
                    }
                    
                    renderQuestionNavigation();
                    showQuestion(currentQuestionIndex);

                    // Update question numbers in headings (for visible logic)
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

            // Toolbar state listeners
            div.addEventListener('keyup', () => updateToolbarState(div));
            div.addEventListener('mouseup', () => updateToolbarState(div));

            function updateToolbarState(container) {
                container.querySelectorAll('.toolbar-tool').forEach(tool => {
                    const command = tool.dataset.command;
                    tool.classList.toggle('active', document.queryCommandState(command));
                });
            }

            return div;
        }

        // --- AI Question Generation Logic ---
        function openAiModal() {
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

            document.getElementById('aiGenerateModal').classList.remove('hidden');
        }

        function closeAiModal() {
            const uploadModal = document.getElementById('uploadModal');
            if (uploadModal) uploadModal.classList.remove('hidden');
            document.getElementById('aiGenerateModal').classList.add('hidden');
        }

        async function handleAiGeneration() {
            // Get data from the main form/page
            const title = document.getElementById('title')?.value || '';
            const subjectSelect = document.getElementById('subject_id');
            const subjectName = subjectSelect && subjectSelect.options[subjectSelect.selectedIndex] ? subjectSelect.options[subjectSelect.selectedIndex].text : '';
            const gradeLevel = document.getElementById('grade_level')?.value || '';

            // Get data from the AI modal
            const additionalContext = document.getElementById('aiTopic').value;
            const quizType = document.getElementById('aiQuizType').value;
            const count = document.getElementById('aiCount').value;
            const examType = document.getElementById('aiExamType').value;
            const examYear = document.getElementById('aiExamYear').value;
            const sourceMaterial = document.getElementById('aiSourceMaterial')?.value || '';

            if (!title && !subjectName) {
                alert('Please fill out the Lesson Title and Subject in the main form first.');
                return;
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
                                return sq;
                            });
                        }
                        return q;
                    });

                    // Append to existing questions
                    uploadData.quiz.questions = [...uploadData.quiz.questions, ...processedQuestions];
                    
                    // Render UI
                    renderQuestionNavigation();
                    
                    // Go to the first newly added question
                    const newIndex = uploadData.quiz.questions.length - processedQuestions.length;
                    showQuestion(newIndex);
                    
                    closeAiModal();
                    document.getElementById('aiTopic').value = ''; // Reset
                } else {
                    alert(data.message || 'Failed to generate questions. Please try again.');
                }
            } catch (error) {
                console.error('AI Generation Error:', error);
                alert('An error occurred during generation: ' + error.message + '\n\nPlease check the browser console for more details.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Attach event listener if button exists (it will be added in index and edit views)
        document.addEventListener('DOMContentLoaded', () => {
            const addAiBtn = document.getElementById('addAiBtn');
            if (addAiBtn) {
                addAiBtn.addEventListener('click', openAiModal);
                // Toggle Exam Year dropdown based on Exam Type
                const examTypeSelect = document.getElementById('aiExamType');
                const examYearSection = document.getElementById('aiExamYearSection');
                if (examTypeSelect && examYearSection) {
                    examTypeSelect.addEventListener('change', () => {
                        const isExam = examTypeSelect.value === 'bece' || examTypeSelect.value === 'wassce';
                        examYearSection.classList.toggle('hidden', !isExam);
                        if (!isExam) {
                            const yearSelect = document.getElementById('aiExamYear');
                            if (yearSelect) yearSelect.value = '';
                        }
                    });
                }
            }
        });
</script>

<!-- AI Generation Slide-over Drawer -->
<div id="aiGenerateModal" class="hidden fixed inset-0 z-[60]">
    <!-- Invisible Backdrop to capture clicks -->
    <div class="fixed inset-0 bg-transparent" onclick="closeAiModal()"></div>

    <!-- Drawer Panel -->
    <div class="fixed inset-y-0 right-0 z-[60] w-full max-w-md bg-white shadow-2xl flex flex-col transform transition-transform duration-300 ease-in-out sm:rounded-l-2xl border-l border-gray-200">
        
        <!-- Drawer Header -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white flex justify-between items-center sm:rounded-tl-2xl">
            <h3 class="text-xl font-bold text-gray-900 flex items-center tracking-tight">
                <div class="bg-white shadow-sm p-2 rounded-xl mr-3 text-purple-600 border border-purple-100">
                    <i class="fas fa-magic"></i>
                </div>
                Generate with AI
            </h3>
            <button onclick="closeAiModal()" class="text-gray-400 hover:text-gray-700 transition-colors bg-white rounded-full p-2 hover:bg-gray-100 shadow-sm border border-transparent hover:border-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Drawer Body -->
        <div class="p-6 flex-1 overflow-y-auto space-y-5 custom-scrollbar">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Context (Optional)</label>
                    <textarea id="aiTopic" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="e.g. Focus specifically on balancing chemical equations..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">We'll automatically use the Title, Subject, and Grade Level from your main form.</p>
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
                        <input type="number" id="aiCount" value="5" min="1" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-md">
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
                    <select id="aiExamYear" class="w-full px-3 py-2 border border-purple-200 rounded-md bg-white focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Any Year</option>
                        @for($y = 2024; $y >= 2010; $y--)
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
                    <p class="text-xs text-gray-500 mt-1 mb-3">If provided, the AI will ONLY format the questions pasted above and will NOT generate new ones.</p>
                    
                    <div class="bg-amber-50 border border-amber-200 rounded-md p-3">
                        <h4 class="text-amber-800 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fas fa-lightbulb text-amber-500 mr-1"></i> Pro Tips for Best Results
                        </h4>
                        <ul class="text-xs text-amber-700 space-y-1.5 list-disc pl-4 mt-2">
                            <li><strong>Avoid Timeout Errors:</strong> Generate complex exams in batches of <strong>10-15 questions</strong> at a time. Trying to generate 40+ questions at once often causes the AI to timeout or return broken JSON.</li>
                            <li><strong>Missing Images:</strong> The AI cannot read copied images. The AI will now automatically detect if an image is missing and insert an <span class="font-mono bg-amber-100 px-1 rounded">Image Placeholder</span> for you. You can then click the placeholder in the Quiz Builder and use the <strong>Insert Image</strong> toolbar button to upload the correct diagram!</li>
                        </ul>
                    </div>
                </div>
        </div>

        <!-- Drawer Footer -->
        <div class="p-6 border-t border-gray-100 bg-gray-50 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:rounded-bl-2xl shrink-0">
            <button type="button" onclick="closeAiModal()" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-xl transition-all shadow-sm font-medium">
                Cancel
            </button>
            <button type="button" id="aiGenerateSubmitBtn" onclick="handleAiGeneration()" class="w-full sm:w-auto px-5 py-2.5 bg-purple-600 text-white hover:bg-purple-700 hover:shadow-md hover:-translate-y-0.5 transform rounded-xl transition-all flex items-center justify-center font-medium shadow-sm">
                <i class="fas fa-magic mr-2"></i> Generate Questions
            </button>
        </div>
    </div>
</div>
@endpush
