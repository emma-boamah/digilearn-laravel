<!-- Floating Background Upload Manager Drawer (Canva / YouTube Studio Style) -->
<div id="uploadManagerDrawerContainer" class="fixed bottom-4 right-4 z-[9999] select-none font-sans transition-all duration-300 pointer-events-none hidden">

    <!-- 1. COLLAPSED FLOATING PILL -->
    <div id="uploadManagerPill" class="pointer-events-auto cursor-pointer bg-slate-900 text-white px-4 py-2.5 rounded-full shadow-2xl border border-slate-700/80 flex items-center gap-3 hover:bg-slate-800 transition-all transform hover:scale-[1.02] active:scale-95">
        <div class="relative flex items-center justify-center">
            <i id="pillIcon" class="fas fa-cloud-upload-alt text-blue-400 text-base animate-pulse"></i>
            <span id="pillBadgeCount" class="absolute -top-1.5 -right-2 bg-blue-600 text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-slate-900">1</span>
        </div>
        <div class="flex items-center gap-2">
            <span id="pillStatusText" class="text-xs font-bold text-slate-200">Uploading 1 item...</span>
            <span id="pillPercent" class="text-xs font-black text-blue-400">0%</span>
        </div>
        <button id="expandDrawerBtn" class="text-slate-400 hover:text-white transition-colors ml-1" title="Expand upload drawer">
            <i class="fas fa-chevron-up text-xs"></i>
        </button>
    </div>

    <!-- 2. EXPANDED UPLOAD DRAWER -->
    <div id="uploadManagerDrawer" class="pointer-events-auto bg-white rounded-2xl shadow-2xl border border-slate-200/90 w-96 max-w-[calc(100vw-2rem)] overflow-hidden transition-all duration-300 hidden">
        <!-- Header -->
        <div class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2">
                <i class="fas fa-cloud-upload-alt text-blue-400"></i>
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-200">Upload Manager</h4>
                <span id="drawerActiveBadge" class="bg-blue-600/80 text-white text-[10px] font-black px-2 py-0.5 rounded-full border border-blue-500/40">1 Active</span>
            </div>
            <div class="flex items-center gap-1">
                <button id="clearCompletedUploadsBtn" class="text-slate-400 hover:text-white text-xs px-2 py-1 rounded hover:bg-slate-800 transition-colors" title="Clear completed tasks">
                    <i class="fas fa-check-double text-[11px]"></i>
                </button>
                <button id="minimizeDrawerBtn" class="text-slate-400 hover:text-white text-xs px-2 py-1 rounded hover:bg-slate-800 transition-colors" title="Minimize">
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Task List -->
        <div id="uploadTaskList" class="p-3 max-h-80 overflow-y-auto space-y-2.5 bg-slate-50/50">
            <!-- Dynamic task cards inserted here by upload-engine -->
        </div>

        <!-- Drawer Footer Info -->
        <div class="p-2.5 bg-white border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
            <span>You can close modals &amp; navigate freely</span>
            <button id="openNewUploadFromDrawer" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
                <i class="fas fa-plus text-[10px]"></i> New Upload
            </button>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION CONTAINER -->
<div id="uploadToastContainer" class="fixed top-5 right-5 z-[10000] space-y-2 pointer-events-none"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('uploadManagerDrawerContainer');
    const pill = document.getElementById('uploadManagerPill');
    const drawer = document.getElementById('uploadManagerDrawer');
    const taskList = document.getElementById('uploadTaskList');
    const pillStatusText = document.getElementById('pillStatusText');
    const pillPercent = document.getElementById('pillPercent');
    const pillBadgeCount = document.getElementById('pillBadgeCount');
    const drawerActiveBadge = document.getElementById('drawerActiveBadge');
    const pillIcon = document.getElementById('pillIcon');
    const expandBtn = document.getElementById('expandDrawerBtn');
    const minimizeBtn = document.getElementById('minimizeDrawerBtn');
    const clearBtn = document.getElementById('clearCompletedUploadsBtn');
    const openNewBtn = document.getElementById('openNewUploadFromDrawer');

    let isExpanded = false;

    // Toggle Expand/Collapse
    function setDrawerState(expanded) {
        isExpanded = expanded;
        if (expanded) {
            pill.classList.add('hidden');
            drawer.classList.remove('hidden');
        } else {
            pill.classList.remove('hidden');
            drawer.classList.add('hidden');
        }
    }

    pill.addEventListener('click', () => setDrawerState(true));
    expandBtn.addEventListener('click', (e) => { e.stopPropagation(); setDrawerState(true); });
    minimizeBtn.addEventListener('click', () => setDrawerState(false));

    clearBtn.addEventListener('click', () => {
        if (window.uploadEngine) {
            window.uploadEngine.clearCompleted();
            renderDrawer();
        }
    });

    if (openNewBtn) {
        openNewBtn.addEventListener('click', () => {
            const uploadBtn = document.getElementById('uploadBtn') || document.querySelector('[data-upload-trigger]');
            if (uploadBtn) {
                uploadBtn.click();
            } else if (typeof window.openUploadModal === 'function') {
                window.openUploadModal();
            }
        });
    }

    // Helper: format toast
    function showUploadToast(title, message, isSuccess = true) {
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto transform transition-all duration-300 ease-out translate-y-2 opacity-0 flex items-center gap-3 p-4 rounded-xl shadow-2xl border ${
            isSuccess ? 'bg-slate-900 text-white border-slate-700' : 'bg-red-900 text-white border-red-700'
        } min-w-[320px] max-w-md`;

        toast.innerHTML = `
            <div class="w-8 h-8 rounded-full ${isSuccess ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'} flex items-center justify-center flex-shrink-0">
                <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'} text-base"></i>
            </div>
            <div class="flex-1 text-xs">
                <div class="font-bold">${title}</div>
                <div class="text-slate-300 mt-0.5">${message}</div>
            </div>
            <button class="text-slate-400 hover:text-white text-xs">&times;</button>
        `;

        const closeBtn = toast.querySelector('button');
        closeBtn.onclick = () => { toast.remove(); };

        const toastContainer = document.getElementById('uploadToastContainer');
        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 50);

        // Auto remove after 6s
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 6000);
    }

    // Render task cards in drawer
    function renderDrawer() {
        if (!window.uploadEngine) return;

        const tasks = Array.from(window.uploadEngine.tasks.values());
        if (tasks.length === 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');

        const activeTasks = tasks.filter(t => ['uploading', 'queued', 'processing'].includes(t.status));
        const completedTasks = tasks.filter(t => t.status === 'completed');

        // Update Pill Info
        pillBadgeCount.innerText = activeTasks.length || tasks.length;
        drawerActiveBadge.innerText = `${activeTasks.length} Active`;

        if (activeTasks.length > 0) {
            const firstActive = activeTasks[0];
            pillStatusText.innerText = `${firstActive.title.substring(0, 18)}...`;
            pillPercent.innerText = `${firstActive.overallProgress || firstActive.uploadProgress}%`;
            pillIcon.className = 'fas fa-cloud-upload-alt text-blue-400 text-base animate-pulse';
        } else if (completedTasks.length > 0) {
            pillStatusText.innerText = 'All uploads complete!';
            pillPercent.innerText = '100%';
            pillIcon.className = 'fas fa-check-circle text-emerald-400 text-base';
        }

        // Render List HTML
        taskList.innerHTML = tasks.map(task => {
            const isCompleted = task.status === 'completed';
            const isFailed = task.status === 'failed';
            const isProcessing = task.status === 'processing' || task.status === 'queued';
            const isCancelled = task.status === 'cancelled';

            let statusBadge = '';
            if (isCompleted) {
                statusBadge = '<span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Completed</span>';
            } else if (isFailed) {
                statusBadge = '<span class="text-[10px] font-bold text-red-700 bg-red-50 px-1.5 py-0.5 rounded border border-red-200">Failed</span>';
            } else if (isProcessing) {
                statusBadge = '<span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 animate-pulse">Processing</span>';
            } else if (isCancelled) {
                statusBadge = '<span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">Cancelled</span>';
            } else {
                statusBadge = `<span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">${task.speed ? task.speed + ' MB/s' : 'Uploading'}</span>`;
            }

            let progressBarColor = 'from-blue-500 to-indigo-600';
            if (isCompleted) progressBarColor = 'from-emerald-500 to-teal-500';
            if (isFailed) progressBarColor = 'from-red-500 to-rose-600';
            if (isProcessing) progressBarColor = 'from-amber-400 to-blue-500 animate-pulse';

            return `
                <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <i class="fas ${task.type === 'document' ? 'fa-file-pdf text-emerald-600' : 'fa-video text-blue-600'} text-xs"></i>
                            <span class="font-bold text-xs text-slate-800 truncate" title="${task.title}">${task.title}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            ${statusBadge}
                            ${!isCompleted && !isFailed && !isCancelled ? `
                                <button onclick="window.uploadEngine.cancelTask('${task.id}')" class="text-slate-400 hover:text-red-600 text-xs p-1" title="Cancel upload">
                                    <i class="fas fa-times"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-1.5">
                        <div class="bg-gradient-to-r ${progressBarColor} h-full rounded-full transition-all duration-300" style="width: ${task.overallProgress || task.uploadProgress}%"></div>
                    </div>

                    <!-- Details subtext -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium">
                        <span class="truncate max-w-[210px]" title="${task.stepDescription}">${task.stepDescription}</span>
                        <span class="font-bold text-slate-700">${task.overallProgress || task.uploadProgress}%</span>
                    </div>

                    ${isCompleted && task.videoId ? `
                        <div class="mt-2 pt-2 border-t border-slate-100 flex justify-end">
                            <a href="/admin/contents/${task.videoId}?type=${task.type}" class="text-[11px] font-bold text-blue-600 hover:underline inline-flex items-center gap-1">
                                View Content <i class="fas fa-arrow-right text-[9px]"></i>
                            </a>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
    }

    // Attach Engine Listeners
    if (window.uploadEngine) {
        window.uploadEngine.on('taskAdded', (task) => {
            renderDrawer();
            setDrawerState(true); // Open drawer so user sees task queued
        });

        window.uploadEngine.on('taskProgress', () => {
            renderDrawer();
        });

        window.uploadEngine.on('taskCompleted', (task) => {
            renderDrawer();
            showUploadToast(
                'Upload Complete!',
                `"${task.title}" was processed and published successfully.`
            );

            // Trigger table reload or notify if contents table exists
            if (typeof window.fetchContentsData === 'function') {
                window.fetchContentsData();
            }
        });

        window.uploadEngine.on('taskFailed', (task) => {
            renderDrawer();
            showUploadToast(
                'Upload Failed',
                `"${task.title}": ${task.error || 'Failed to complete.'}`,
                false
            );
        });

        window.uploadEngine.on('taskCancelled', () => {
            renderDrawer();
        });

        window.uploadEngine.on('tasksCleared', () => {
            renderDrawer();
        });
    }

    // Check for active tasks on page load
    fetch('/admin/contents/upload-tasks/active')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.tasks && data.tasks.length > 0) {
                data.tasks.forEach(t => {
                    if (['uploading', 'queued', 'processing'].includes(t.status)) {
                        // Register into engine as server processing task
                        if (!window.uploadEngine.tasks.has(t.id)) {
                            const task = {
                                id: t.id,
                                title: t.title,
                                type: t.content_type,
                                status: t.status,
                                overallProgress: t.progress,
                                uploadProgress: 100,
                                stepDescription: t.step_description || 'Processing on server...',
                                videoId: t.related_video_id,
                                abortController: new AbortController()
                            };
                            window.uploadEngine.tasks.set(t.id, task);
                            window.uploadEngine.pollServerProcessing(task);
                        }
                    }
                });
                renderDrawer();
            }
        })
        .catch(err => console.warn('Could not load active upload tasks:', err));
});
</script>
