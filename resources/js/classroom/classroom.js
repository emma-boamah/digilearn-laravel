/**
 * DigiLearn Virtual Classroom Master App
 * Glues Alpine.js UI, Fabric Whiteboard, WebRTC MediaEngine, and Laravel Echo together.
 */

import { VirtualWhiteboard } from './whiteboard.js';
import { ClassroomMediaEngine } from './webrtc.js';

export function virtualClassroom(config = {}) {
    return {
        roomId: config.roomId || '',
        currentUser: config.user || {},
        isTutor: !!config.isTutor,
        topic: config.topic || 'Interactive Session',
        gradeLevel: config.gradeLevel || 'General',

        // Classroom state
        activeStage: 'whiteboard', // 'whiteboard', 'video-grid', 'screen-share', 'presentation'
        isSidebarOpen: true,
        activeSidebarTab: 'chat', // 'chat', 'participants', 'materials', 'math'

        // Media state
        isAudioMuted: false,
        isVideoMuted: false,
        isScreenSharing: false,
        isHandRaised: false,

        // Whiteboard Tool state
        activeWhiteboardTool: 'pen',
        whiteboardColor: '#2677B8',
        whiteboardWidth: 3,

        // Participants & Chat state
        participants: [],
        messages: [],
        newMessage: '',
        raisedHands: [],

        // Math formula helper
        mathInput: '\\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}',
        mathPreviewHtml: '',

        // Materials state
        uploadedMaterials: [],
        isUploadingMaterial: false,

        // Engine instances
        whiteboard: null,
        mediaEngine: null,

        async init() {
            console.log('Initializing DigiLearn Virtual Classroom for room:', this.roomId);

            // 1. Initialize Interactive Whiteboard
            this.initWhiteboard();

            // 2. Initialize WebRTC Media Engine
            await this.initMedia();

            // 3. Connect Laravel Echo Presence Channel
            this.initEcho();

            // 4. Initial Math Preview
            this.updateMathPreview();
        },

        initWhiteboard() {
            // Check if fabric is available globally or via module
            if (typeof fabric === 'undefined' && window.fabric) {
                window.fabric = fabric;
            }

            this.whiteboard = new VirtualWhiteboard('whiteboard-canvas', {
                roomId: this.roomId,
                userId: this.currentUser.id,
                userName: this.currentUser.name,
                isTutor: this.isTutor,
                onSync: (action, data) => this.broadcastWhiteboardAction(action, data),
            });
        },

        async initMedia() {
            this.mediaEngine = new ClassroomMediaEngine({
                roomId: this.roomId,
                userId: this.currentUser.id,
                userName: this.currentUser.name,
                onStreamAdded: (remoteUserId, stream) => this.handleRemoteStreamAdded(remoteUserId, stream),
                onStreamRemoved: (remoteUserId) => this.handleRemoteStreamRemoved(remoteUserId),
                onSignal: (type, payload, targetUserId) => this.sendSignal(type, payload, targetUserId),
            });

            try {
                await this.mediaEngine.startLocalMedia('local-video-preview');
            } catch (e) {
                console.warn('Could not start local media automatically:', e);
            }
        },

        initEcho() {
            if (!window.Echo) {
                console.warn('Laravel Echo is not loaded on window.');
                return;
            }

            // Join Presence Channel
            window.Echo.join(`classroom.${this.roomId}`)
                .here((users) => {
                    this.participants = users;
                    // Connect WebRTC to all existing participants
                    users.forEach((user) => {
                        if (user.id !== this.currentUser.id) {
                            this.mediaEngine.createPeerConnection(user.id, true);
                        }
                    });
                })
                .joining((user) => {
                    this.participants.push(user);
                    this.addSystemMessage(`${user.name} joined the classroom.`);
                    this.mediaEngine.createPeerConnection(user.id, false);
                })
                .leaving((user) => {
                    this.participants = this.participants.filter((p) => p.id !== user.id);
                    this.addSystemMessage(`${user.name} left the classroom.`);
                    this.handleRemoteStreamRemoved(user.id);
                })
                .listen('.classroom.signal', (e) => {
                    this.handleIncomingSignal(e);
                })
                .listen('.classroom.message', (e) => {
                    this.messages.push(e.message);
                    this.scrollChatToBottom();
                })
                .listen('.classroom.whiteboard', (e) => {
                    if (this.whiteboard) {
                        this.whiteboard.handleRemoteAction(e.action, e.data);
                    }
                });
        },

        // Stage Switcher
        setStage(stage) {
            this.activeStage = stage;
            if (stage === 'whiteboard' && this.whiteboard) {
                setTimeout(() => this.whiteboard.resizeCanvas(), 100);
            }
        },

        // Whiteboard Tool Actions
        selectTool(tool) {
            this.activeWhiteboardTool = tool;
            if (this.whiteboard) {
                this.whiteboard.setTool(tool);
            }
        },

        setWhiteboardColor(color) {
            this.whiteboardColor = color;
            if (this.whiteboard) {
                this.whiteboard.setColor(color);
            }
        },

        setWhiteboardWidth(width) {
            this.whiteboardWidth = width;
            if (this.whiteboard) {
                this.whiteboard.setWidth(width);
            }
        },

        clearWhiteboard() {
            if (confirm('Are you sure you want to clear the whiteboard for all participants?')) {
                if (this.whiteboard) {
                    this.whiteboard.clearCanvas(true);
                }
            }
        },

        undoWhiteboard() {
            if (this.whiteboard) this.whiteboard.undo();
        },

        redoWhiteboard() {
            if (this.whiteboard) this.whiteboard.redo();
        },

        // Math Formula Tool
        updateMathPreview() {
            if (window.katex && this.mathInput.trim()) {
                try {
                    this.mathPreviewHtml = window.katex.renderToString(this.mathInput, {
                        displayMode: true,
                        throwOnError: false,
                    });
                } catch (e) {
                    this.mathPreviewHtml = `<span style="color: red;">Invalid LaTeX: ${e.message}</span>`;
                }
            }
        },

        insertMathOntoWhiteboard() {
            if (!this.mathInput.trim()) return;
            if (this.whiteboard) {
                this.whiteboard.insertMathFormula(this.mathInput);
                this.setStage('whiteboard');
            }
        },

        // Material Upload (PDF / Slides)
        async uploadMaterial(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            this.isUploadingMaterial = true;
            try {
                const response = await fetch(`/api/classroom/${this.roomId}/upload-material`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });
                const res = await response.json();
                if (res.status === 'success') {
                    this.uploadedMaterials.push(res);
                    if (res.mime === 'application/pdf') {
                        // Load into whiteboard
                        if (this.whiteboard) {
                            await this.whiteboard.loadPdf(res.url);
                            this.setStage('whiteboard');
                        }
                    }
                }
            } catch (err) {
                console.error('Upload failed:', err);
                alert('Upload failed. Please check the file size and format.');
            } finally {
                this.isUploadingMaterial = false;
            }
        },

        // WebRTC Media Controls
        toggleAudio() {
            if (this.mediaEngine) {
                this.isAudioMuted = this.mediaEngine.toggleAudio();
                this.sendSignal('mic-toggle', { muted: this.isAudioMuted });
            }
        },

        toggleVideo() {
            if (this.mediaEngine) {
                this.isVideoMuted = this.mediaEngine.toggleVideo();
                this.sendSignal('camera-toggle', { muted: this.isVideoMuted });
            }
        },

        async toggleScreenShare() {
            if (this.mediaEngine) {
                this.isScreenSharing = await this.mediaEngine.toggleScreenShare('screen-video-feed');
                if (this.isScreenSharing) {
                    this.setStage('screen-share');
                } else {
                    // Clear the video element so it doesn't hold a stale frame
                    const screenVideo = document.getElementById('screen-video-feed');
                    if (screenVideo) {
                        screenVideo.srcObject = null;
                    }
                    this.setStage('whiteboard');
                }
            }
        },

        toggleHandRaise() {
            this.isHandRaised = !this.isHandRaised;
            this.sendSignal('hand-raise', {
                raised: this.isHandRaised,
                userId: this.currentUser.id,
                userName: this.currentUser.name,
            });
            if (this.isHandRaised) {
                this.addSystemMessage(`${this.currentUser.name} raised their hand.`);
            }
        },

        // Chat Actions
        async sendMessage() {
            if (!this.newMessage.trim()) return;

            const msgText = this.newMessage.trim();
            this.newMessage = '';

            try {
                await fetch(`/api/classroom/${this.roomId}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: msgText }),
                });
            } catch (err) {
                console.error('Failed to send message:', err);
            }
        },

        addSystemMessage(text) {
            this.messages.push({
                id: Math.random().toString(36).substring(7),
                is_system: true,
                content: text,
                created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            });
            this.scrollChatToBottom();
        },

        scrollChatToBottom() {
            setTimeout(() => {
                const el = document.getElementById('chat-messages-container');
                if (el) el.scrollTop = el.scrollHeight;
            }, 50);
        },

        // Signal Dispatcher & Handler
        async sendSignal(type, payload, targetUserId = null) {
            try {
                await fetch(`/api/classroom/${this.roomId}/signal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        type: type,
                        payload: payload,
                        targetUserId: targetUserId,
                    }),
                });
            } catch (e) {
                console.error('Signaling error:', e);
            }
        },

        async broadcastWhiteboardAction(action, data) {
            try {
                await fetch(`/api/classroom/${this.roomId}/whiteboard`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        action: action,
                        data: data,
                    }),
                });
            } catch (e) {
                console.error('Whiteboard sync error:', e);
            }
        },

        handleIncomingSignal(e) {
            // Ignore if targeted to someone else
            if (e.targetUserId && e.targetUserId !== this.currentUser.id) {
                return;
            }

            const senderId = e.sender.id;

            if (e.type === 'offer') {
                this.mediaEngine.handleOffer(senderId, e.payload.sdp);
            } else if (e.type === 'answer') {
                this.mediaEngine.handleAnswer(senderId, e.payload.sdp);
            } else if (e.type === 'ice-candidate') {
                this.mediaEngine.handleIceCandidate(senderId, e.payload.candidate);
            } else if (e.type === 'hand-raise') {
                if (e.payload.raised) {
                    this.raisedHands.push(e.sender);
                    this.addSystemMessage(`✋ ${e.sender.name} raised hand`);
                } else {
                    this.raisedHands = this.raisedHands.filter(u => u.id !== e.sender.id);
                }
            } else if (e.type === 'screen-share-start') {
                this.setStage('screen-share');
                this.addSystemMessage(`${e.sender.name} started sharing screen.`);
            } else if (e.type === 'screen-share-stop') {
                this.setStage('whiteboard');
                this.addSystemMessage(`${e.sender.name} stopped sharing screen.`);
            }
        },

        handleRemoteStreamAdded(remoteUserId, stream) {
            let container = document.getElementById(`peer-video-wrap-${remoteUserId}`);
            if (!container) {
                const grid = document.getElementById('remote-videos-grid');
                if (grid) {
                    const wrap = document.createElement('div');
                    wrap.id = `peer-video-wrap-${remoteUserId}`;
                    wrap.className = 'remote-video-card';
                    wrap.innerHTML = `
                        <video id="peer-video-${remoteUserId}" autoplay playsinline class="video-feed-element"></video>
                        <span class="peer-name-tag">Student</span>
                    `;
                    grid.appendChild(wrap);
                }
            }

            const video = document.getElementById(`peer-video-${remoteUserId}`);
            if (video) {
                video.srcObject = stream;
            }
        },

        handleRemoteStreamRemoved(remoteUserId) {
            const wrap = document.getElementById(`peer-video-wrap-${remoteUserId}`);
            if (wrap) {
                wrap.remove();
            }
        },

        leaveClassroom() {
            if (confirm('Are you sure you want to leave the virtual classroom?')) {
                if (this.mediaEngine) {
                    this.mediaEngine.cleanup();
                }
                window.location.href = '/dashboard';
            }
        },
    };
}

// Attach globally to window
window.virtualClassroom = virtualClassroom;

// Register on Alpine instance
if (window.Alpine) {
    window.Alpine.data('virtualClassroom', virtualClassroom);
} else {
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) {
            window.Alpine.data('virtualClassroom', virtualClassroom);
        }
    });
}
