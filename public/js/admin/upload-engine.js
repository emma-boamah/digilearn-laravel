/**
 * Digilearn High-Speed Concurrent Upload Engine & Drawer Controller
 * - Concurrency: 3 parallel chunks
 * - Built-in UI drawer controller (CSP-compliant, zero inline scripts required)
 * - Auto-retry on network disconnects
 * - Background queue tracking & multi-upload support
 */
class UploadEngine {
    constructor(options = {}) {
        this.chunkSize = options.chunkSize || 5 * 1024 * 1024; // 5MB per chunk
        this.concurrency = options.concurrency || 3; // 3 parallel chunk uploads
        this.maxRetries = options.maxRetries || 3;
        this.tasks = new Map();
        this.listeners = new Map();
        this.isExpanded = true;
        this.uiInitialized = false;

        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.routes = {
            uploadChunk: '/admin/contents/upload/video-chunk',
            uploadVideo: '/admin/contents/upload/video',
            uploadDocuments: '/admin/contents/upload/documents',
            uploadQuiz: '/admin/contents/upload/quiz',
            taskStatus: '/admin/contents/upload-tasks/',
            cancelTask: '/admin/contents/upload-tasks/'
        };

        // Initialize UI bindings when ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initUI());
        } else {
            this.initUI();
        }

        // Add beforeunload confirmation only during active binary file uploads
        window.addEventListener('beforeunload', (e) => {
            const hasActiveUploads = Array.from(this.tasks.values()).some(
                t => t.status === 'uploading' && t.uploadProgress < 100
            );
            if (hasActiveUploads) {
                e.preventDefault();
                e.returnValue = 'You have uploads in progress. Leaving this page will cancel active file transfers.';
                return e.returnValue;
            }
        });
    }

    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    emit(event, data) {
        if (this.listeners.has(event)) {
            this.listeners.get(event).forEach(cb => {
                try { cb(data); } catch (err) { console.error('UploadEngine listener error:', err); }
            });
        }
    }

    generateId() {
        return 'task_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
    }

    /**
     * Bind UI DOM elements for floating drawer
     */
    initUI() {
        if (this.uiInitialized) return;

        this.dom = {
            container: document.getElementById('uploadManagerDrawerContainer'),
            pill: document.getElementById('uploadManagerPill'),
            drawer: document.getElementById('uploadManagerDrawer'),
            taskList: document.getElementById('uploadTaskList'),
            pillStatusText: document.getElementById('pillStatusText'),
            pillPercent: document.getElementById('pillPercent'),
            pillBadgeCount: document.getElementById('pillBadgeCount'),
            drawerActiveBadge: document.getElementById('drawerActiveBadge'),
            pillIcon: document.getElementById('pillIcon'),
            expandBtn: document.getElementById('expandDrawerBtn'),
            minimizeBtn: document.getElementById('minimizeDrawerBtn'),
            clearBtn: document.getElementById('clearCompletedUploadsBtn'),
            openNewBtn: document.getElementById('openNewUploadFromDrawer'),
            toastContainer: document.getElementById('uploadToastContainer')
        };

        if (!this.dom.container) {
            // Container not in DOM yet, retry shortly
            setTimeout(() => this.initUI(), 150);
            return;
        }

        // CRITICAL: Move the drawer and toast containers to document.body
        // so they are never trapped inside a hidden modal parent (e.g. .upload-modal with display:none)
        document.body.appendChild(this.dom.container);
        if (this.dom.toastContainer) {
            document.body.appendChild(this.dom.toastContainer);
        }

        this.uiInitialized = true;

        // Pill click -> expand
        if (this.dom.pill) {
            this.dom.pill.addEventListener('click', () => {
                this.isExpanded = true;
                this.renderUI();
            });
        }

        if (this.dom.expandBtn) {
            this.dom.expandBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.isExpanded = true;
                this.renderUI();
            });
        }

        if (this.dom.minimizeBtn) {
            this.dom.minimizeBtn.addEventListener('click', () => {
                this.isExpanded = false;
                this.renderUI();
            });
        }

        if (this.dom.clearBtn) {
            this.dom.clearBtn.addEventListener('click', () => {
                this.clearCompleted();
            });
        }

        if (this.dom.openNewBtn) {
            this.dom.openNewBtn.addEventListener('click', () => {
                const uploadBtn = document.getElementById('uploadBtn') || document.querySelector('[data-upload-trigger]');
                if (uploadBtn) {
                    uploadBtn.click();
                } else if (typeof window.openUploadModal === 'function') {
                    window.openUploadModal();
                }
            });
        }

        // Cancel button delegation
        if (this.dom.taskList) {
            this.dom.taskList.addEventListener('click', (e) => {
                const cancelBtn = e.target.closest('[data-cancel-task]');
                if (cancelBtn) {
                    const taskId = cancelBtn.getAttribute('data-cancel-task');
                    this.cancelTask(taskId);
                }
            });
        }

        // Listen to engine lifecycle events to update drawer
        this.on('taskAdded', () => {
            this.isExpanded = true;
            this.renderUI();
        });

        this.on('taskProgress', () => {
            this.renderUI();
        });

        this.on('taskCompleted', (task) => {
            this.renderUI();
            this.showToast('Upload Complete!', `"${task.title}" was processed successfully.`, true);
            if (typeof window.fetchContentsData === 'function') {
                window.fetchContentsData();
            }
        });

        this.on('taskFailed', (task) => {
            this.renderUI();
            this.showToast('Upload Failed', `"${task.title}": ${task.error || 'Failed'}`, false);
        });

        this.on('taskCancelled', () => this.renderUI());
        this.on('tasksCleared', () => this.renderUI());

        // Check for active tasks on server
        this.fetchActiveServerTasks();
    }

    showDrawer() {
        if (!this.uiInitialized) this.initUI();
        this.isExpanded = true;
        this.renderUI();
    }

    /**
     * Render the floating drawer & pill cards
     */
    renderUI() {
        if (!this.uiInitialized || !this.dom.container) return;

        const tasks = Array.from(this.tasks.values());
        if (tasks.length === 0) {
            this.dom.container.style.display = 'none';
            return;
        }

        this.dom.container.style.display = 'block';

        if (this.isExpanded) {
            if (this.dom.pill) this.dom.pill.style.display = 'none';
            if (this.dom.drawer) this.dom.drawer.style.display = 'block';
        } else {
            if (this.dom.pill) this.dom.pill.style.display = 'flex';
            if (this.dom.drawer) this.dom.drawer.style.display = 'none';
        }

        const activeTasks = tasks.filter(t => ['uploading', 'queued', 'processing'].includes(t.status));
        const completedTasks = tasks.filter(t => t.status === 'completed');

        // Update Pill Info
        if (this.dom.pillBadgeCount) this.dom.pillBadgeCount.innerText = activeTasks.length || tasks.length;
        if (this.dom.drawerActiveBadge) this.dom.drawerActiveBadge.innerText = `${activeTasks.length} Active`;

        if (activeTasks.length > 0) {
            const firstActive = activeTasks[0];
            if (this.dom.pillStatusText) this.dom.pillStatusText.innerText = `${firstActive.title.substring(0, 18)}...`;
            if (this.dom.pillPercent) this.dom.pillPercent.innerText = `${firstActive.overallProgress || firstActive.uploadProgress}%`;
            if (this.dom.pillIcon) this.dom.pillIcon.className = 'fas fa-cloud-upload-alt text-blue-400 text-base animate-pulse';
        } else if (completedTasks.length > 0) {
            if (this.dom.pillStatusText) this.dom.pillStatusText.innerText = 'All uploads complete!';
            if (this.dom.pillPercent) this.dom.pillPercent.innerText = '100%';
            if (this.dom.pillIcon) this.dom.pillIcon.className = 'fas fa-check-circle text-emerald-400 text-base';
        }

        // Render Cards in Task List
        if (this.dom.taskList) {
            this.dom.taskList.innerHTML = tasks.map(task => {
                const isCompleted = task.status === 'completed';
                const isFailed = task.status === 'failed';
                const isProcessing = task.status === 'processing' || task.status === 'queued';
                const isCancelled = task.status === 'cancelled';

                let statusBadge = '';
                if (isCompleted) {
                    statusBadge = '<span style="font-size: 10px; font-weight: 700; color: #047857; background-color: #ecfdf5; padding: 2px 6px; border-radius: 4px; border: 1px solid #a7f3d0;">Completed</span>';
                } else if (isFailed) {
                    statusBadge = '<span style="font-size: 10px; font-weight: 700; color: #b91c1c; background-color: #fef2f2; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca;">Failed</span>';
                } else if (isProcessing) {
                    statusBadge = '<span style="font-size: 10px; font-weight: 700; color: #b45309; background-color: #fffbeb; padding: 2px 6px; border-radius: 4px; border: 1px solid #fde68a;">Processing</span>';
                } else if (isCancelled) {
                    statusBadge = '<span style="font-size: 10px; font-weight: 700; color: #64748b; background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1;">Cancelled</span>';
                } else {
                    statusBadge = `<span style="font-size: 10px; font-weight: 700; color: #1d4ed8; background-color: #eff6ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #bfdbfe;">${task.speed ? task.speed + ' MB/s' : 'Uploading'}</span>`;
                }

                let barColor = 'linear-gradient(to right, #3b82f6, #4f46e5)';
                if (isCompleted) barColor = 'linear-gradient(to right, #10b981, #14b8a6)';
                if (isFailed) barColor = 'linear-gradient(to right, #ef4444, #f43f5e)';
                if (isProcessing) barColor = 'linear-gradient(to right, #f59e0b, #3b82f6)';

                const percent = task.overallProgress || task.uploadProgress || 0;

                return `
                    <div style="background: white; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                                <i class="fas ${task.type === 'document' ? 'fa-file-pdf text-emerald-600' : 'fa-video text-blue-600'}" style="font-size: 12px;"></i>
                                <span style="font-weight: 700; font-size: 12px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 190px;" title="${task.title}">${task.title}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                ${statusBadge}
                                ${!isCompleted && !isFailed && !isCancelled ? `
                                    <button type="button" data-cancel-task="${task.id}" style="background: none; border: none; color: #94a3b8; cursor: pointer; padding: 2px 4px; font-size: 12px;" title="Cancel upload">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div style="width: 100%; background-color: #f1f5f9; border-radius: 9999px; height: 8px; overflow: hidden; margin-bottom: 6px;">
                            <div style="background: ${barColor}; height: 100%; border-radius: 9999px; width: ${percent}%; transition: width 0.3s ease;"></div>
                        </div>

                        <!-- Details subtext -->
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: #64748b; font-weight: 500;">
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;" title="${task.stepDescription}">${task.stepDescription}</span>
                            <span style="font-weight: 700; color: #334155;">${percent}%</span>
                        </div>

                        ${isCompleted && task.videoId ? `
                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
                                <a href="/admin/contents/${task.videoId}?type=${task.type}" style="font-size: 11px; font-weight: 700; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    View Content <i class="fas fa-arrow-right" style="font-size: 9px;"></i>
                                </a>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }
    }

    showToast(title, message, isSuccess = true) {
        const toast = document.createElement('div');
        toast.style.cssText = `pointer-events: auto; transform: translateY(8px); opacity: 0; transition: all 0.3s ease-out; display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); background-color: ${isSuccess ? '#0f172a' : '#7f1d1d'}; color: white; border: 1px solid ${isSuccess ? '#334155' : '#991b1b'}; min-width: 320px; max-width: 400px;`;

        toast.innerHTML = `
            <div style="width: 32px; height: 32px; border-radius: 9999px; background-color: ${isSuccess ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)'}; color: ${isSuccess ? '#34d399' : '#f87171'}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'}" style="font-size: 16px;"></i>
            </div>
            <div style="flex: 1; font-size: 12px;">
                <div style="font-weight: 700; margin-bottom: 2px;">${title}</div>
                <div style="color: #cbd5e1;">${message}</div>
            </div>
            <button type="button" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 14px;">&times;</button>
        `;

        toast.querySelector('button').onclick = () => toast.remove();

        const container = document.getElementById('uploadToastContainer') || document.body;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 50);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            setTimeout(() => toast.remove(), 350);
        }, 6000);
    }

    /**
     * Start package upload pipeline
     */
    async startPackageUpload(finalData, uploadState = {}) {
        if (!this.uiInitialized) this.initUI();

        const taskId = this.generateId();
        const videoTitle = (finalData && finalData.video && finalData.video.title) ? finalData.video.title : 'Untitled Content';

        const task = {
            id: taskId,
            title: videoTitle,
            type: (finalData && finalData.video && finalData.video.video_source === 'none') ? 'document' : 'video',
            status: 'uploading',
            overallProgress: 0,
            uploadProgress: 0,
            serverProgress: 0,
            stepDescription: 'Starting upload...',
            speed: 0,
            timeRemaining: '--',
            uploadedBytes: 0,
            totalBytes: 0,
            videoId: null,
            error: null,
            abortController: new AbortController(),
            startTime: Date.now(),
            createdAt: new Date().toISOString()
        };

        // Calculate total payload size safely
        let totalBytes = 0;
        if (finalData && finalData.video && finalData.video.file) totalBytes += finalData.video.file.size;
        if (uploadState && uploadState.thumbnail && uploadState.thumbnail.size) totalBytes += uploadState.thumbnail.size;
        if (finalData && finalData.documents && Array.isArray(finalData.documents)) {
            finalData.documents.forEach(d => { totalBytes += (d.size || 0); });
        }
        task.totalBytes = totalBytes;

        this.tasks.set(taskId, task);

        // Instantly show floating drawer
        this.showDrawer();
        this.emit('taskAdded', task);

        // Run upload pipeline asynchronously
        this.executePackagePipeline(task, finalData, uploadState);

        return task;
    }

    /**
     * Pipeline execution without blocking UI
     */
    async executePackagePipeline(task, finalData, uploadState) {
        try {
            // STEP 1: Upload Video
            task.stepDescription = 'Uploading video...';
            this.emit('taskProgress', task);

            const videoResult = await this.uploadVideoStep(task, finalData, uploadState);
            if (!videoResult.success) {
                throw new Error(videoResult.error || 'Video upload failed');
            }

            task.videoId = videoResult.video_id;
            task.overallProgress = (finalData.documents && finalData.documents.length > 0) || (finalData.quiz && finalData.quiz.questions && finalData.quiz.questions.length > 0) ? 60 : 85;
            task.stepDescription = 'Video uploaded successfully';
            this.emit('taskProgress', task);

            // STEP 2: Upload Documents (if any)
            if (finalData.documents && finalData.documents.length > 0) {
                task.stepDescription = `Uploading ${finalData.documents.length} document(s)...`;
                this.emit('taskProgress', task);

                const docResult = await this.uploadDocumentsStep(task, finalData.documents, task.videoId);
                if (!docResult.success) {
                    console.warn('Document upload had issues:', docResult.error);
                }
                task.overallProgress = (finalData.quiz && finalData.quiz.questions && finalData.quiz.questions.length > 0) ? 80 : 90;
                this.emit('taskProgress', task);
            }

            // STEP 3: Upload Quiz (if any)
            if (finalData.quiz && (finalData.quiz.questions.length > 0 || window._quizUploadStatus === 'draft')) {
                task.stepDescription = 'Saving quiz...';
                this.emit('taskProgress', task);

                const quizResult = await this.uploadQuizStep(task, finalData.quiz, task.videoId);
                if (!quizResult.success) {
                    console.warn('Quiz upload had issues:', quizResult.error);
                }
                task.overallProgress = 90;
                this.emit('taskProgress', task);
            }

            // STEP 4: Server Background Processing & Polling
            task.status = 'processing';
            task.stepDescription = 'Queued on server for background processing...';
            task.uploadProgress = 100;
            task.overallProgress = 95;
            this.emit('taskProgress', task);

            // Start polling background server job
            await this.pollServerProcessing(task);

        } catch (err) {
            if (task.status === 'cancelled') return;
            console.error(`Upload error for task ${task.id}:`, err);
            task.status = 'failed';
            task.error = err.message || 'Upload failed';
            task.stepDescription = 'Failed: ' + (err.message || 'Error occurred');
            this.emit('taskFailed', task);
        }
    }

    /**
     * Video Upload Step: Supports URL, Direct, and High-Speed Concurrent Chunks
     */
    async uploadVideoStep(task, finalData, uploadState) {
        const videoFile = finalData.video ? finalData.video.file : null;
        const videoSource = finalData.video ? finalData.video.video_source : 'none';

        const isUrlBased = ['youtube', 'mux'].includes(videoSource) ||
            (videoSource === 'vimeo' && finalData.video && finalData.video.external_video_url && finalData.video.external_video_url.trim() !== '');

        if (isUrlBased || !videoFile) {
            // Direct URL registration
            const formData = new FormData();
            formData.append('_token', this.csrfToken);
            formData.append('title', finalData.video.title);
            formData.append('subject_id', finalData.video.subject_id);
            formData.append('description', finalData.video.description || '');
            formData.append('grade_level', finalData.video.grade_level);
            formData.append('video_source', videoSource);
            formData.append('task_id', task.id);

            if (finalData.video.category_ids && finalData.video.category_ids.length > 0) {
                finalData.video.category_ids.forEach(id => formData.append('category_ids[]', id));
            }
            if (videoSource === 'vimeo') {
                formData.append('vimeo_url', finalData.video.external_video_url);
            } else if (finalData.video.external_video_url) {
                formData.append('external_video_url', finalData.video.external_video_url);
            }
            if (uploadState && uploadState.thumbnail) {
                formData.append('thumbnail_file', uploadState.thumbnail);
            }

            const response = await fetch(this.routes.uploadVideo, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                signal: task.abortController.signal
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to create URL video record');
            }

            return { success: true, video_id: result.data.video_id };
        }

        // File-based upload: 10MB+ uses parallel chunks
        const fileSize = videoFile.size;
        const largeFileThreshold = 10 * 1024 * 1024;

        if (fileSize >= largeFileThreshold) {
            return await this.uploadVideoConcurrentChunks(task, finalData, uploadState);
        } else {
            return await this.uploadVideoDirect(task, finalData, uploadState);
        }
    }

    /**
     * High-speed parallel chunked upload with concurrency pool
     */
    async uploadVideoConcurrentChunks(task, finalData, uploadState) {
        const file = finalData.video.file;
        const totalSize = file.size;
        const totalChunks = Math.ceil(totalSize / this.chunkSize);
        const uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);

        let uploadedBytesByChunk = new Array(totalChunks).fill(0);
        let completedChunksCount = 0;
        const startTime = Date.now();

        const chunkIndices = Array.from({ length: totalChunks }, (_, i) => i);

        // Worker function pulls next chunk from pool
        const uploadWorker = async () => {
            while (chunkIndices.length > 0) {
                if (task.status === 'cancelled') return;

                const chunkIndex = chunkIndices.shift();
                const start = chunkIndex * this.chunkSize;
                const end = Math.min(start + this.chunkSize, totalSize);
                const chunkBlob = file.slice(start, end);
                const chunkBytes = end - start;

                let attempt = 0;
                let chunkSuccess = false;

                while (attempt < this.maxRetries && !chunkSuccess) {
                    attempt++;
                    try {
                        const chunkFormData = new FormData();
                        chunkFormData.append('_token', this.csrfToken);
                        chunkFormData.append('upload_id', uploadId);
                        chunkFormData.append('chunk_index', chunkIndex);
                        chunkFormData.append('total_chunks', totalChunks);
                        chunkFormData.append('chunk', chunkBlob);
                        chunkFormData.append('filename', file.name);

                        const res = await fetch(this.routes.uploadChunk, {
                            method: 'POST',
                            body: chunkFormData,
                            signal: task.abortController.signal
                        });

                        if (!res.ok) throw new Error(`HTTP ${res.status}`);

                        const json = await res.json();
                        if (!json.success) throw new Error(json.message || 'Chunk error');

                        chunkSuccess = true;
                        uploadedBytesByChunk[chunkIndex] = chunkBytes;
                        completedChunksCount++;

                        const currentUploaded = uploadedBytesByChunk.reduce((a, b) => a + b, 0);
                        const elapsedSeconds = (Date.now() - startTime) / 1000;
                        const speedBps = elapsedSeconds > 0 ? currentUploaded / elapsedSeconds : 0;
                        const speedMbps = (speedBps / (1024 * 1024)).toFixed(1);
                        const remainingSeconds = speedBps > 0 ? Math.ceil((totalSize - currentUploaded) / speedBps) : 0;

                        task.uploadedBytes = currentUploaded;
                        task.uploadProgress = Math.min(95, Math.floor((currentUploaded / totalSize) * 100));
                        task.speed = speedMbps;
                        task.timeRemaining = remainingSeconds > 60
                            ? `${Math.floor(remainingSeconds / 60)}m ${remainingSeconds % 60}s`
                            : `${remainingSeconds}s`;
                        task.stepDescription = `Uploading chunks (${completedChunksCount}/${totalChunks}) at ${speedMbps} MB/s`;

                        this.emit('taskProgress', task);

                    } catch (err) {
                        if (task.status === 'cancelled') return;
                        if (attempt >= this.maxRetries) {
                            throw new Error(`Chunk ${chunkIndex + 1} failed after ${this.maxRetries} attempts: ${err.message}`);
                        }
                        await new Promise(r => setTimeout(r, Math.pow(2, attempt) * 250));
                    }
                }
            }
        };

        // Launch concurrent worker pool
        const workers = Array.from({ length: Math.min(this.concurrency, totalChunks) }, () => uploadWorker());
        await Promise.all(workers);

        if (task.status === 'cancelled') {
            return { success: false, error: 'Upload cancelled' };
        }

        task.stepDescription = 'Assembling chunks and registering video on server...';
        task.uploadProgress = 95;
        this.emit('taskProgress', task);

        // Finalize chunked video registration
        const finalFormData = new FormData();
        finalFormData.append('_token', this.csrfToken);
        finalFormData.append('upload_id', uploadId);
        finalFormData.append('task_id', task.id);
        finalFormData.append('filename', file.name);
        finalFormData.append('title', finalData.video.title);
        finalFormData.append('subject_id', finalData.video.subject_id);
        finalFormData.append('description', finalData.video.description || '');
        finalFormData.append('grade_level', finalData.video.grade_level);
        finalFormData.append('video_source', finalData.video.video_source);
        finalFormData.append('upload_destination', finalData.video.upload_destination || '');

        if (finalData.video.category_ids && finalData.video.category_ids.length > 0) {
            finalData.video.category_ids.forEach(id => finalFormData.append('category_ids[]', id));
        }
        if (uploadState && uploadState.thumbnail) {
            finalFormData.append('thumbnail_file', uploadState.thumbnail);
        }

        const finalRes = await fetch(this.routes.uploadVideo, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: finalFormData,
            signal: task.abortController.signal
        });

        const finalResult = await finalRes.json();
        if (!finalRes.ok || !finalResult.success) {
            throw new Error(finalResult.message || 'Failed to finalize video assembly');
        }

        return { success: true, video_id: finalResult.data.video_id };
    }

    /**
     * Direct upload for smaller video files
     */
    async uploadVideoDirect(task, finalData, uploadState) {
        const file = finalData.video.file;
        const formData = new FormData();
        formData.append('_token', this.csrfToken);
        formData.append('title', finalData.video.title);
        formData.append('subject_id', finalData.video.subject_id);
        formData.append('description', finalData.video.description || '');
        formData.append('grade_level', finalData.video.grade_level);
        formData.append('video_source', file ? finalData.video.video_source : 'none');
        formData.append('task_id', task.id);

        if (finalData.video.category_ids && finalData.video.category_ids.length > 0) {
            finalData.video.category_ids.forEach(id => formData.append('category_ids[]', id));
        }
        if (file) {
            formData.append('video_file', file);
            formData.append('upload_destination', finalData.video.upload_destination || '');
        }
        if (uploadState && uploadState.thumbnail) {
            formData.append('thumbnail_file', uploadState.thumbnail);
        }

        const startTime = Date.now();
        const response = await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.routes.uploadVideo, true);
            xhr.setRequestHeader('Accept', 'application/json');
            if (this.csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const elapsed = (Date.now() - startTime) / 1000;
                    const speed = elapsed > 0 ? (e.loaded / elapsed / (1024 * 1024)).toFixed(1) : 0;
                    task.uploadedBytes = e.loaded;
                    task.uploadProgress = Math.min(95, Math.floor((e.loaded / e.total) * 100));
                    task.speed = speed;
                    task.stepDescription = `Uploading video directly (${speed} MB/s)`;
                    this.emit('taskProgress', task);
                }
            };

            xhr.onload = () => {
                try {
                    resolve({
                        ok: xhr.status >= 200 && xhr.status < 300,
                        status: xhr.status,
                        json: async () => JSON.parse(xhr.responseText)
                    });
                } catch (e) {
                    reject(new Error('Invalid JSON server response'));
                }
            };

            xhr.onerror = () => reject(new Error('Network error during direct upload'));
            task.abortController.signal.addEventListener('abort', () => xhr.abort());
            xhr.send(formData);
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Direct video upload failed');
        }

        return { success: true, video_id: result.data.video_id };
    }

    /**
     * Upload Documents Step
     */
    async uploadDocumentsStep(task, documents, videoId) {
        const formData = new FormData();
        formData.append('_token', this.csrfToken);
        formData.append('video_id', videoId || '');
        formData.append('task_id', task.id);

        documents.forEach((doc, idx) => {
            formData.append(`documents[${idx}]`, doc);
        });

        const response = await fetch(this.routes.uploadDocuments, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            signal: task.abortController.signal
        });

        const result = await response.json();
        return { success: response.ok && result.success, error: result.message };
    }

    /**
     * Upload Quiz Step
     */
    async uploadQuizStep(task, quiz, videoId) {
        const formData = new FormData();
        formData.append('_token', this.csrfToken);
        formData.append('video_id', videoId || '');

        const quizData = {
            questions: [],
            difficulty_level: quiz.difficulty_level || 'medium',
            time_limit_minutes: quiz.time_limit_minutes || 15,
            shuffle_questions: quiz.shuffle_questions
        };

        if (quiz.questions && Array.isArray(quiz.questions)) {
            quiz.questions.forEach((q, idx) => {
                const questionData = {
                    id: q.id,
                    type: q.type,
                    question: q.question,
                    preamble: q.preamble,
                    points: q.points,
                    sub_questions: q.sub_questions || []
                };

                if (q.type === 'mcq') {
                    questionData.options = q.options;
                    questionData.correct_answer = q.correct_answer;
                } else {
                    questionData.correct_answer = q.correct_answer;
                }

                if (q.imageFile) {
                    formData.append(`question_images[${idx}]`, q.imageFile);
                    questionData.has_image = true;
                    questionData.image_index = idx;
                } else {
                    questionData.has_image = false;
                }

                quizData.questions.push(questionData);
            });
        }

        formData.append('quiz_data', JSON.stringify(quizData));
        formData.append('quiz_title', quiz.quiz_title || '');
        formData.append('difficulty_level', quiz.difficulty_level || 'medium');
        formData.append('time_limit_minutes', quiz.time_limit_minutes || 15);
        formData.append('shuffle_questions', quiz.shuffle_questions ? '1' : '0');
        formData.append('status', window._quizUploadStatus || 'published');

        const response = await fetch(this.routes.uploadQuiz, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            signal: task.abortController.signal
        });

        const result = await response.json();
        return { success: response.ok && result.success, error: result.message };
    }

    /**
     * Poll server for background queue job progress
     */
    async pollServerProcessing(task) {
        const pollInterval = 3000;
        const maxPollAttempts = 120;
        let attempts = 0;

        while (attempts < maxPollAttempts) {
            if (task.status === 'cancelled') return;

            await new Promise(r => setTimeout(r, pollInterval));
            attempts++;

            try {
                const res = await fetch(`${this.routes.taskStatus}${task.id}`, {
                    headers: { 'Accept': 'application/json' },
                    signal: task.abortController.signal
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.task) {
                        const serverTask = data.task;
                        task.status = serverTask.status;
                        task.stepDescription = serverTask.step_description || task.stepDescription;

                        if (serverTask.status === 'completed') {
                            task.overallProgress = 100;
                            task.uploadProgress = 100;
                            this.emit('taskCompleted', task);
                            return;
                        } else if (serverTask.status === 'failed') {
                            task.error = serverTask.error_message || 'Background processing failed';
                            this.emit('taskFailed', task);
                            return;
                        }

                        this.emit('taskProgress', task);
                    }
                }
            } catch (pollErr) {
                if (task.status === 'cancelled') return;
                console.warn('Poll status error:', pollErr);
            }
        }

        if (task.status !== 'completed' && task.status !== 'failed') {
            task.status = 'completed';
            task.overallProgress = 100;
            task.stepDescription = 'Upload completed in background!';
            this.emit('taskCompleted', task);
        }
    }

    /**
     * Cancel an active task
     */
    async cancelTask(taskId) {
        const task = this.tasks.get(taskId);
        if (task) {
            task.status = 'cancelled';
            task.stepDescription = 'Cancelled';
            task.abortController.abort();

            try {
                await fetch(`${this.routes.cancelTask}${taskId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });
            } catch (e) {
                console.warn('Error sending cancellation to server:', e);
            }

            this.emit('taskCancelled', task);
        }
    }

    /**
     * Clear finished tasks from memory
     */
    clearCompleted() {
        for (const [id, task] of this.tasks.entries()) {
            if (['completed', 'failed', 'cancelled'].includes(task.status)) {
                this.tasks.delete(id);
            }
        }
        this.emit('tasksCleared');
    }

    fetchActiveServerTasks() {
        fetch('/admin/contents/upload-tasks/active')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.tasks && data.tasks.length > 0) {
                    data.tasks.forEach(t => {
                        if (['uploading', 'queued', 'processing'].includes(t.status)) {
                            if (!this.tasks.has(t.id)) {
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
                                this.tasks.set(t.id, task);
                                this.pollServerProcessing(task);
                            }
                        }
                    });
                    this.renderUI();
                }
            })
            .catch(err => console.warn('Could not load active upload tasks:', err));
    }
}

// Global Singleton Instance
window.uploadEngine = new UploadEngine();
window.showUploadDrawer = () => window.uploadEngine.showDrawer();
