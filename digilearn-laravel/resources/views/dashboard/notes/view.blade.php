<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $note['title'] ?? 'Note' }} - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Quill.js for rich text editing -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <style>
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            align-items: center;
            height: 60px;
        }

        .header-left {
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-right: 1px solid var(--gray-200);
            height: 100%;
        }

        .hamburger-menu {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-right: 1rem;
        }

        .hamburger-menu:hover {
            background-color: var(--gray-100);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .logo-text {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-red);
            letter-spacing: -0.025em;
        }

        .header-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .shoutout-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shoutout-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .shoutout-tagline {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Navigation Bar */
        .nav-bar {
            background-color: var(--gray-200);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: var(--white);
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .back-button:hover {
            background-color: var(--gray-50);
            box-shadow: var(--shadow-md);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 140px);
        }

        /* Note Container */
        .note-container {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Note Header */
        .note-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .note-title-container {
            flex: 1;
        }

        .note-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .note-title-input {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--secondary-blue);
            border: none;
            background: transparent;
            width: 100%;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .note-title-input:focus {
            outline: none;
            background-color: var(--gray-50);
        }

        .note-meta {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .note-actions {
            display: flex;
            gap: 0.75rem;
        }

        .note-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 2px solid;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .note-action-btn.edit {
            color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .note-action-btn.edit:hover {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .note-action-btn.delete {
            color: var(--primary-red);
            border-color: var(--primary-red);
        }

        .note-action-btn.delete:hover {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .note-action-btn.save {
            color: #10b981;
            border-color: #10b981;
        }

        .note-action-btn.save:hover {
            background-color: #10b981;
            color: var(--white);
        }

        .note-action-btn.cancel {
            color: var(--gray-600);
            border-color: var(--gray-300);
        }

        .note-action-btn.cancel:hover {
            background-color: var(--gray-100);
        }

        /* Note Content */
        .note-content {
            padding: 2rem;
        }

        .note-text {
            font-size: 1rem;
            line-height: 1.8;
            color: var(--gray-700);
        }

        /* Editor Styles */
        .note-editor {
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
            font-size: 1rem !important;
            line-height: 1.8 !important;
        }

        .ql-editor {
            padding: 2rem !important;
            min-height: 350px !important;
        }

        .ql-editor.ql-blank::before {
            color: var(--gray-500) !important;
            font-style: normal !important;
            font-weight: 500 !important;
        }

        /* Floating Toolbar */
        .floating-toolbar {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--gray-800);
            border-radius: 2rem;
            padding: 0.75rem 1.5rem;
            box-shadow: var(--shadow-lg);
            display: none;
            align-items: center;
            gap: 1rem;
            z-index: 1000;
        }

        .floating-toolbar.active {
            display: flex;
        }

        .toolbar-btn {
            background: none;
            border: none;
            color: var(--white);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toolbar-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .toolbar-btn.active {
            background-color: var(--secondary-blue);
        }

        .toolbar-separator {
            width: 1px;
            height: 24px;
            background-color: var(--gray-600);
        }

        .ask-to-edit-btn {
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .ask-to-edit-btn:hover {
            background-color: #1e5a8a;
        }

        /* Hidden class */
        .hidden {
            display: none !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .note-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding: 1.5rem 1rem 1rem;
            }

            .note-actions {
                justify-content: center;
            }

            .note-content {
                padding: 1.5rem 1rem;
            }

            .note-title {
                font-size: 1.5rem;
            }

            .note-title-input {
                font-size: 1.5rem;
            }

            .floating-toolbar {
                bottom: 1rem;
                left: 1rem;
                right: 1rem;
                transform: none;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <button class="hamburger-menu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <a href="{{ route('dashboard.main') }}" class="logo">
                    <span class="logo-text">DigiLearn</span>
                </a>
            </div>
            
            <div class="header-right">
                <div class="shoutout-logo">
                    <div>
                        <div class="shoutout-text">ShoutOutGh</div>
                        <div class="shoutout-tagline">Educating through Entertainment</div>
                    </div>
                </div>
                
                <div class="user-menu">
                    <x-user-avatar :user="auth()->user()" :size="32" class="border-2 border-white" />
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="nav-bar">
        <button class="back-button" onclick="history.back()">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="note-container">
            <!-- Note Header -->
            <div class="note-header">
                <div class="note-title-container">
                    <h1 class="note-title" id="noteTitle">{{ $note['title'] ?? 'Living and non-living things' }}</h1>
                    <input type="text" class="note-title-input hidden" id="noteTitleInput" value="{{ $note['title'] ?? 'Living and non-living things' }}">
                    <p class="note-meta">{{ $note['subject'] ?? 'Science - Grade 1-3' }} • {{ $note['created_at'] ?? 'April 2025' }}</p>
                </div>
                
                <div class="note-actions">
                    <div id="viewActions">
                        <button class="note-action-btn edit" id="editBtn">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <button class="note-action-btn delete" id="deleteBtn">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                    
                    <div id="editActions" class="hidden">
                        <button class="note-action-btn save" id="saveBtn">
                            <i class="fas fa-save"></i>
                            Save
                        </button>
                        <button class="note-action-btn cancel" id="cancelBtn">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Note Content -->
            <div class="note-content">
                <div id="noteDisplay" class="note-text">
                    {!! $note['content'] ?? 'Lorem ipsum dolor sit amet consectetur. Vivamus arcu neque morbi magna est egestas. Diam dictum lacus nisl eros nullam orci et massa turpis. Dolor purus quisque quam at scelerisque aliquet non etiam. A volutpat vitae donec nibh curabitur lacinia neque venenatis. Adipiscing amet nullam cursus lectus velit congue. Consectetur iaculis egestas parturient nunc at vivamus. Maecenas porttitor suspendisse pulvinar in dui et nisl eget. Egestas egestas sed in id sit commodo vitae praesent. Arcu lectus lacus massa a eu.

Lorem ipsum dolor sit amet consectetur. Vivamus arcu neque morbi magna est egestas. Diam dictum lacus nisl eros nullam orci et massa turpis. Dolor purus quisque quam at scelerisque aliquet non etiam. A volutpat vitae donec nibh curabitur lacinia neque venenatis. Adipiscing amet nullam cursus lectus velit congue. Consectetur iaculis egestas parturient nunc at vivamus. Maecenas porttitor suspendisse pulvinar in dui et nisl eget. Egestas egestas sed in id sit commodo vitae praesent. Arcu lectus lacus massa a eu.

Lorem ipsum dolor sit amet consectetur. Vivamus arcu neque morbi magna est egestas. Diam dictum lacus nisl eros nullam orci et massa turpis. Dolor purus quisque quam at scelerisque aliquet non etiam. A volutpat vitae donec nibh curabitur lacinia neque venenatis. Adipiscing amet nullam cursus lectus velit congue. Consectetur iaculis egestas parturient nunc at vivamus. Maecenas porttitor suspendisse pulvinar in dui et nisl eget. Egestas egestas sed in id sit commodo vitae praesent. Arcu lectus lacus massa a eu.' !!}
                </div>
                
                <div id="noteEditor" class="note-editor hidden"></div>
            </div>
        </div>
    </div>

    <!-- Floating Toolbar -->
    <div class="floating-toolbar" id="floatingToolbar">
        <button class="toolbar-btn" data-action="cursor">
            <i class="fas fa-mouse-pointer"></i>
        </button>
        <button class="toolbar-btn" data-action="hand">
            <i class="fas fa-hand-paper"></i>
        </button>
        <button class="toolbar-btn" data-action="comment">
            <i class="fas fa-comment"></i>
        </button>
        <div class="toolbar-separator"></div>
        <button class="ask-to-edit-btn" id="askToEditBtn">Ask to edit</button>
        <div class="toolbar-separator"></div>
        <button class="toolbar-btn" data-action="format">
            <i class="fas fa-paint-brush"></i>
        </button>
        <button class="toolbar-btn" data-action="code">
            <i class="fas fa-code"></i>
        </button>
        <button class="toolbar-btn" data-action="export">
            <i class="fas fa-external-link-alt"></i>
        </button>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        let quillEditor = null;
        let isEditing = false;
        let originalContent = '';
        let originalTitle = '';

        document.addEventListener('DOMContentLoaded', function() {
            initializeNoteViewer();
            initializeFloatingToolbar();
        });

        function initializeNoteViewer() {
            const editBtn = document.getElementById('editBtn');
            const deleteBtn = document.getElementById('deleteBtn');
            const saveBtn = document.getElementById('saveBtn');
            const cancelBtn = document.getElementById('cancelBtn');

            editBtn.addEventListener('click', enterEditMode);
            deleteBtn.addEventListener('click', deleteNote);
            saveBtn.addEventListener('click', saveNote);
            cancelBtn.addEventListener('click', cancelEdit);
        }

        function enterEditMode() {
            isEditing = true;
            
            // Store original content
            originalContent = document.getElementById('noteDisplay').innerHTML;
            originalTitle = document.getElementById('noteTitle').textContent;
            
            // Toggle UI elements
            document.getElementById('viewActions').classList.add('hidden');
            document.getElementById('editActions').classList.remove('hidden');
            document.getElementById('noteTitle').classList.add('hidden');
            document.getElementById('noteTitleInput').classList.remove('hidden');
            document.getElementById('noteDisplay').classList.add('hidden');
            document.getElementById('noteEditor').classList.remove('hidden');
            document.getElementById('floatingToolbar').classList.add('active');
            
            // Initialize Quill editor
            if (!quillEditor) {
                quillEditor = new Quill('#noteEditor', {
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
            }
            
            // Set editor content
            quillEditor.root.innerHTML = originalContent;
        }

        function cancelEdit() {
            isEditing = false;
            
            // Restore original content
            document.getElementById('noteTitle').textContent = originalTitle;
            document.getElementById('noteTitleInput').value = originalTitle;
            
            // Toggle UI elements
            document.getElementById('editActions').classList.add('hidden');
            document.getElementById('viewActions').classList.remove('hidden');
            document.getElementById('noteTitleInput').classList.add('hidden');
            document.getElementById('noteTitle').classList.remove('hidden');
            document.getElementById('noteEditor').classList.add('hidden');
            document.getElementById('noteDisplay').classList.remove('hidden');
            document.getElementById('floatingToolbar').classList.remove('active');
        }

        function saveNote() {
            const newTitle = document.getElementById('noteTitleInput').value.trim();
            const newContent = quillEditor ? quillEditor.root.innerHTML : '';
            
            if (!newTitle) {
                alert('Please enter a title for your note.');
                return;
            }
            
            // Show loading state
            const saveBtn = document.getElementById('saveBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Update display
                document.getElementById('noteTitle').textContent = newTitle;
                document.getElementById('noteDisplay').innerHTML = newContent;
                
                // Exit edit mode
                isEditing = false;
                document.getElementById('editActions').classList.add('hidden');
                document.getElementById('viewActions').classList.remove('hidden');
                document.getElementById('noteTitleInput').classList.add('hidden');
                document.getElementById('noteTitle').classList.remove('hidden');
                document.getElementById('noteEditor').classList.add('hidden');
                document.getElementById('noteDisplay').classList.remove('hidden');
                document.getElementById('floatingToolbar').classList.remove('active');
                
                // Reset button
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                
                // Show success message
                showSuccessMessage('Note saved successfully!');
            }, 1000);
        }

        function deleteNote() {
            if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                // Show loading and redirect
                showSuccessMessage('Note deleted successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("dashboard.notes") }}';
                }, 1500);
            }
        }

        function initializeFloatingToolbar() {
            const toolbarBtns = document.querySelectorAll('.toolbar-btn');
            const askToEditBtn = document.getElementById('askToEditBtn');
            
            toolbarBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Toggle active state
                    toolbarBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const action = this.dataset.action;
                    handleToolbarAction(action);
                });
            });
            
            askToEditBtn.addEventListener('click', function() {
                if (!isEditing) {
                    enterEditMode();
                }
            });
        }

        function handleToolbarAction(action) {
            console.log('Toolbar action:', action);
            
            switch(action) {
                case 'cursor':
                    // Handle cursor tool
                    break;
                case 'hand':
                    // Handle hand tool
                    break;
                case 'comment':
                    // Handle comment tool
                    break;
                case 'format':
                    // Handle format tool
                    break;
                case 'code':
                    // Handle code tool
                    break;
                case 'export':
                    exportNote();
                    break;
            }
        }

        function exportNote() {
            const title = document.getElementById('noteTitle').textContent;
            const content = document.getElementById('noteDisplay').innerHTML;
            
            // Create HTML content
            const htmlContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { font-family: 'Inter', sans-serif; line-height: 1.8; max-width: 800px; margin: 0 auto; padding: 2rem; }
                        h1 { color: #2677B8; border-bottom: 2px solid #2677B8; padding-bottom: 0.5rem; }
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
            
            showSuccessMessage('Note exported successfully!');
        }

        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #10b981;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 600;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            successDiv.textContent = message;
            
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isEditing) {
                cancelEdit();
            }
            
            if ((e.ctrlKey || e.metaKey) && e.key === 's' && isEditing) {
                e.preventDefault();
                saveNote();
            }
            
            if ((e.ctrlKey || e.metaKey) && e.key === 'e' && !isEditing) {
                e.preventDefault();
                enterEditMode();
            }
        });
    </script>
</body>
</html>
