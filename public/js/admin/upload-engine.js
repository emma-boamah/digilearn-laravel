/**
 * Digilearn High-Speed Concurrent Upload Engine
 * Supports:
 * - Parallel chunked uploads (concurrency: 3)
 * - Non-blocking background uploads (modal can be closed immediately)
 * - Auto-retry on network dropouts with exponential backoff
 * - Multi-task queueing (upload multiple packages simultaneously)
 * - Real-time speed (MB/s), ETA, and server-side background job polling
 */
class UploadEngine {
    constructor(options = {}) {
        this.chunkSize = options.chunkSize || 5 * 1024 * 1024; // 5MB per chunk
        this.concurrency = options.concurrency || 3; // 3 chunks simultaneously
        this.maxRetries = options.maxRetries || 3;
        this.tasks = new Map();
        this.listeners = new Map();

        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.routes = {
            uploadChunk: '/admin/contents/upload/video-chunk',
            uploadVideo: '/admin/contents/upload/video',
            uploadDocuments: '/admin/contents/upload/documents',
            uploadQuiz: '/admin/contents/upload/quiz',
            taskStatus: '/admin/contents/upload-tasks/',
            cancelTask: '/admin/contents/upload-tasks/'
        };

        // Add beforeunload guard only when binary uploads are actively transmitting
        window.addEventListener('beforeunload', (e) => {
            const hasActiveNetworkTransfers = Array.from(this.tasks.values()).some(
                t => t.status === 'uploading' && t.uploadProgress < 100
            );
            if (hasActiveNetworkTransfers) {
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
     * Start a complete package upload (Video + Documents + Quiz)
     */
    async startPackageUpload(finalData, uploadState) {
        const taskId = this.generateId();
        const videoTitle = finalData.video.title || 'Untitled Content';

        const task = {
            id: taskId,
            title: videoTitle,
            type: finalData.video.video_source === 'none' ? 'document' : 'video',
            status: 'uploading', // uploading -> queued -> processing -> completed -> failed -> cancelled
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

        // Calculate total payload bytes for accurate progress
        let totalBytes = 0;
        if (finalData.video.file) totalBytes += finalData.video.file.size;
        if (uploadState.thumbnail) totalBytes += uploadState.thumbnail.size;
        if (finalData.documents && finalData.documents.length) {
            finalData.documents.forEach(d => { totalBytes += (d.size || 0); });
        }
        task.totalBytes = totalBytes;

        this.tasks.set(taskId, task);
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
            if (task.status === 'cancelled') {
                return;
            }
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
        const videoFile = finalData.video.file;
        const videoSource = finalData.video.video_source;

        const isUrlBased = ['youtube', 'mux'].includes(videoSource) ||
            (videoSource === 'vimeo' && finalData.video.external_video_url && finalData.video.external_video_url.trim() !== '');

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
            if (uploadState.thumbnail) {
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

        // File-based upload
        const fileSize = videoFile.size;
        const largeFileThreshold = 10 * 1024 * 1024; // 10MB: use concurrent chunks for >= 10MB

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

        // Build list of chunks to upload
        const chunkIndices = Array.from({ length: totalChunks }, (_, i) => i);

        // Worker function that pulls next chunk index from pool
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

                        if (!res.ok) {
                            throw new Error(`HTTP ${res.status}`);
                        }

                        const json = await res.json();
                        if (!json.success) {
                            throw new Error(json.message || 'Chunk error');
                        }

                        chunkSuccess = true;
                        uploadedBytesByChunk[chunkIndex] = chunkBytes;
                        completedChunksCount++;

                        // Calculate throughput metrics
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
                        // Exponential backoff before retry (500ms, 1000ms, 2000ms)
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
        if (uploadState.thumbnail) {
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
        if (uploadState.thumbnail) {
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
        const pollInterval = 3000; // Poll every 3 seconds
        const maxPollAttempts = 120; // 6 minutes max polling
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

        // If polling times out, assume completed on server
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
}

// Global Singleton Instance
window.uploadEngine = new UploadEngine();
