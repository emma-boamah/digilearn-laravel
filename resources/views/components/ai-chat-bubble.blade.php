{{-- 
    AI Chat Bubble — Grok-style floating assistant widget
    Included globally in dashboard-components layout via @include
    Reuses existing /api/agent/ask and /api/agent/session/{id} endpoints
--}}

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
/* =============== Floating Bubble Button =============== */
.ai-bubble-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35), 0 2px 6px rgba(0,0,0,0.1);
    z-index: 9998;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    touch-action: none;
    user-select: none;
    -webkit-user-select: none;
}
.ai-bubble-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45), 0 3px 8px rgba(0,0,0,0.15);
}
.ai-bubble-btn:active {
    transform: scale(0.95);
}
.ai-bubble-btn.dragging {
    transition: none;
    transform: scale(1.1);
    box-shadow: 0 8px 28px rgba(37, 99, 235, 0.5);
}
.ai-bubble-btn svg {
    width: 26px;
    height: 26px;
    pointer-events: none;
}

/* Pulse ring animation */
.ai-bubble-btn::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid rgba(37, 99, 235, 0.3);
    animation: aiBubblePulse 3s ease-in-out infinite;
}
@keyframes aiBubblePulse {
    0%, 100% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.15); opacity: 0; }
}

/* Remaining requests badge */
.ai-bubble-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    background: #dc2626;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    border: 2px solid var(--bg-surface, #fff);
    pointer-events: none;
    line-height: 1;
}

/* =============== Drawer Scrim / Overlay =============== */
.ai-drawer-scrim {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}
.ai-drawer-scrim.open {
    opacity: 1;
    visibility: visible;
}

/* =============== Drawer Panel =============== */
.ai-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 400px;
    max-width: 100vw;
    background: var(--bg-surface, #ffffff);
    z-index: 10000;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.12);
}
.ai-drawer.open {
    transform: translateX(0);
}

@media (max-width: 480px) {
    .ai-drawer {
        width: 100vw;
    }
}

/* Drawer Header */
.ai-drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    flex-shrink: 0;
    background: var(--bg-surface, #ffffff);
}
.ai-drawer-header-title {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ai-drawer-header-title .ai-icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ai-drawer-header-title .ai-icon-circle svg {
    width: 16px;
    height: 16px;
    color: #fff;
}
.ai-drawer-header-title h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main, #111827);
    margin: 0;
    line-height: 1.2;
}
.ai-drawer-header-title .ai-label {
    font-size: 10px;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    margin: 0;
}
.ai-drawer-header-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.ai-drawer-header-actions button,
.ai-drawer-header-actions a {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border-color, #e5e7eb);
    background: transparent;
    color: var(--text-muted, #6b7280);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    text-decoration: none;
    font-size: 14px;
}
.ai-drawer-header-actions button:hover,
.ai-drawer-header-actions a:hover {
    background: var(--gray-100, #f3f4f6);
    color: var(--text-main, #111827);
}

/* Context Banner */
.ai-context-banner {
    padding: 8px 16px;
    background: rgba(37, 99, 235, 0.06);
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
[data-theme="dark"] .ai-context-banner {
    background: rgba(37, 99, 235, 0.1);
}
.ai-context-banner i {
    color: #2563eb;
    font-size: 11px;
    flex-shrink: 0;
}
.ai-context-banner span {
    font-size: 11px;
    color: var(--text-muted, #6b7280);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ai-context-banner strong {
    color: var(--text-main, #111827);
    font-weight: 600;
}

/* Messages Area */
.ai-messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    scroll-behavior: smooth;
}

/* Empty State */
.ai-empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
    gap: 12px;
}
.ai-empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(37, 99, 235, 0.05));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 22px;
}
.ai-empty-state h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main, #111827);
    margin: 0;
}
.ai-empty-state p {
    font-size: 12px;
    color: var(--text-muted, #6b7280);
    margin: 0;
    line-height: 1.5;
    max-width: 260px;
}

/* Suggestion chips */
.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-top: 4px;
}
.ai-suggestion-chip {
    font-size: 11px;
    padding: 6px 12px;
    border-radius: 16px;
    border: 1px solid var(--border-color, #e5e7eb);
    background: var(--bg-surface, #fff);
    color: var(--text-muted, #6b7280);
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.ai-suggestion-chip:hover {
    border-color: #2563eb;
    color: #2563eb;
    background: rgba(37, 99, 235, 0.05);
}

/* Message bubbles */
.ai-msg {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    max-width: 100%;
}
.ai-msg.user {
    flex-direction: row-reverse;
}
.ai-msg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}
.ai-msg.bot .ai-msg-avatar {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
}
.ai-msg.user .ai-msg-avatar {
    background: var(--gray-200, #e5e7eb);
    color: var(--text-main, #111827);
}
.ai-msg-body {
    max-width: calc(100% - 44px);
}
.ai-msg-bubble {
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 13px;
    line-height: 1.6;
    word-break: break-word;
}
.ai-msg.bot .ai-msg-bubble {
    background: var(--gray-100, #f3f4f6);
    color: var(--text-main, #111827);
    border-bottom-left-radius: 4px;
}
.ai-msg.user .ai-msg-bubble {
    background: #2563eb;
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

/* Markdown styling inside bot bubbles */
.ai-msg.bot .ai-msg-bubble p { margin: 0 0 8px 0; }
.ai-msg.bot .ai-msg-bubble p:last-child { margin-bottom: 0; }
.ai-msg.bot .ai-msg-bubble strong { font-weight: 700; }
.ai-msg.bot .ai-msg-bubble em { font-style: italic; }
.ai-msg.bot .ai-msg-bubble code {
    background: rgba(37, 99, 235, 0.08);
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 12px;
    font-family: 'Courier New', monospace;
}
.ai-msg.bot .ai-msg-bubble pre {
    background: var(--gray-200, #e5e7eb);
    padding: 10px 12px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 8px 0;
    font-size: 11px;
}
.ai-msg.bot .ai-msg-bubble ul,
.ai-msg.bot .ai-msg-bubble ol {
    padding-left: 18px;
    margin: 6px 0;
}
.ai-msg.bot .ai-msg-bubble li { margin-bottom: 3px; }

/* Typing indicator */
.ai-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 2px;
}
.ai-typing-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--gray-400, #9ca3af);
    animation: aiTypingBounce 1.4s ease-in-out infinite;
}
.ai-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.ai-typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes aiTypingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-6px); }
}

/* Input Area */
.ai-input-area {
    padding: 12px 16px;
    border-top: 1px solid var(--border-color, #e5e7eb);
    flex-shrink: 0;
    background: var(--bg-surface, #ffffff);
}
.ai-input-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    background: var(--gray-50, #f9fafb);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 16px;
    padding: 4px 4px 4px 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.ai-input-row:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
.ai-input-row textarea {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 13px;
    line-height: 1.5;
    color: var(--text-main, #111827);
    resize: none;
    padding: 8px 0;
    max-height: 100px;
    min-height: 20px;
    font-family: inherit;
}
.ai-input-row textarea::placeholder {
    color: var(--gray-400, #9ca3af);
}
.ai-send-btn {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    border: none;
    background: #2563eb;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s ease;
}
.ai-send-btn:hover { background: #1d4ed8; }
.ai-send-btn:disabled {
    background: var(--gray-300, #d1d5db);
    cursor: not-allowed;
}
.ai-send-btn svg {
    width: 16px;
    height: 16px;
}

/* Footer info */
.ai-input-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 4px 0;
}
.ai-input-footer span {
    font-size: 10px;
    color: var(--gray-400, #9ca3af);
}
.ai-requests-left {
    font-size: 10px;
    color: var(--gray-400, #9ca3af);
    display: flex;
    align-items: center;
    gap: 4px;
}
.ai-requests-left .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #22c55e;
}
.ai-requests-left .dot.low { background: #f59e0b; }
.ai-requests-left .dot.empty { background: #dc2626; }
</style>

<div x-data="aiChatBubble()" x-cloak>

    {{-- ===== Floating Bubble Button ===== --}}
    <button class="ai-bubble-btn"
            :class="{ 'dragging': isDragging }"
            :style="bubbleStyle"
            @pointerdown.prevent="startDrag($event)"
            @click="handleBubbleClick()"
            title="Ask AI Tutor"
            aria-label="Open AI Tutor chat">
        {{-- Sparkle/Stars icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L14.09 8.26L20 9.27L15.55 13.97L16.91 20L12 16.9L7.09 20L8.45 13.97L4 9.27L9.91 8.26L12 2Z"/>
        </svg>
        <template x-if="remainingRequests !== null && remainingRequests <= 5">
            <span class="ai-bubble-badge" x-text="remainingRequests"></span>
        </template>
    </button>

    {{-- ===== Drawer Scrim ===== --}}
    <div class="ai-drawer-scrim" :class="{ 'open': drawerOpen }" @click="closeDrawer()"></div>

    {{-- ===== Drawer Panel ===== --}}
    <div class="ai-drawer" :class="{ 'open': drawerOpen }">

        {{-- Header --}}
        <div class="ai-drawer-header">
            <div class="ai-drawer-header-title">
                <div class="ai-icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L14.09 8.26L20 9.27L15.55 13.97L16.91 20L12 16.9L7.09 20L8.45 13.97L4 9.27L9.91 8.26L12 2Z"/>
                    </svg>
                </div>
                <div>
                    <h3>AI Tutor</h3>
                    <p class="ai-label">DigiLearn Assistant</p>
                </div>
            </div>
            <div class="ai-drawer-header-actions">
                <a href="{{ route('dashboard.agent') }}" title="Open full page">
                    <i class="fas fa-expand-alt" style="font-size:12px"></i>
                </a>
                <button @click="closeDrawer()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Context Banner (shows what page/topic user is viewing) --}}
        <template x-if="pageContext && pageContext.subject">
            <div class="ai-context-banner">
                <i class="fas fa-map-pin"></i>
                <span>
                    Studying: <strong x-text="pageContext.indicator || pageContext.subject"></strong>
                </span>
            </div>
        </template>

        {{-- Messages Area --}}
        <div class="ai-messages-area" x-ref="messagesArea">

            {{-- Empty state --}}
            <template x-if="messages.length === 0 && !isLoading">
                <div class="ai-empty-state">
                    <div class="ai-empty-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>Hi there! 👋</h4>
                    <p>I'm your AI study buddy. Ask me anything about your lessons — I'll explain step by step.</p>

                    <div class="ai-suggestions">
                        <button class="ai-suggestion-chip" @click="sendSuggestion('Explain this topic simply')">
                            Explain this topic
                        </button>
                        <button class="ai-suggestion-chip" @click="sendSuggestion('Give me practice questions')">
                            Practice questions
                        </button>
                        <button class="ai-suggestion-chip" @click="sendSuggestion('Summarize the key points')">
                            Summarize key points
                        </button>
                    </div>
                </div>
            </template>

            {{-- Chat messages --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div class="ai-msg" :class="msg.role === 'user' ? 'user' : 'bot'">
                    <div class="ai-msg-avatar">
                        <template x-if="msg.role === 'user'">
                            <i class="fas fa-user" style="font-size:11px"></i>
                        </template>
                        <template x-if="msg.role !== 'user'">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px">
                                <path d="M12 2L14.09 8.26L20 9.27L15.55 13.97L16.91 20L12 16.9L7.09 20L8.45 13.97L4 9.27L9.91 8.26L12 2Z"/>
                            </svg>
                        </template>
                    </div>
                    <div class="ai-msg-body">
                        <div class="ai-msg-bubble" x-html="msg.role === 'user' ? escapeHtml(msg.text) : renderMarkdown(msg.text)"></div>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <template x-if="isLoading">
                <div class="ai-msg bot">
                    <div class="ai-msg-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px">
                            <path d="M12 2L14.09 8.26L20 9.27L15.55 13.97L16.91 20L12 16.9L7.09 20L8.45 13.97L4 9.27L9.91 8.26L12 2Z"/>
                        </svg>
                    </div>
                    <div class="ai-msg-body">
                        <div class="ai-msg-bubble">
                            <div class="ai-typing">
                                <div class="ai-typing-dot"></div>
                                <div class="ai-typing-dot"></div>
                                <div class="ai-typing-dot"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input Area --}}
        <div class="ai-input-area">
            <div class="ai-input-row">
                <textarea x-ref="chatInput"
                          x-model="inputText"
                          @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                          @input="autoResize($event.target)"
                          placeholder="Ask me anything..."
                          rows="1"
                          :disabled="isLoading"
                          maxlength="2000"></textarea>
                <button class="ai-send-btn"
                        @click="sendMessage()"
                        :disabled="isLoading || !inputText.trim()"
                        title="Send message">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
            <div class="ai-input-footer">
                <span x-text="inputText.length + '/2000'"></span>
                <div class="ai-requests-left" x-show="remainingRequests !== null">
                    <span class="dot" :class="{
                        'low': remainingRequests <= 3 && remainingRequests > 0,
                        'empty': remainingRequests <= 0
                    }"></span>
                    <span x-text="remainingRequests + ' requests left today'"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function aiChatBubble() {
    return {
        drawerOpen: false,
        messages: [],
        inputText: '',
        isLoading: false,
        sessionId: null,
        remainingRequests: null,
        pageContext: null,

        // Drag state
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        bubbleX: null,
        bubbleY: null,
        dragMoved: false,
        dragThreshold: 6,

        init() {
            // Restore session from localStorage
            try {
                const saved = localStorage.getItem('ai_bubble_session');
                if (saved) {
                    const data = JSON.parse(saved);
                    this.sessionId = data.sessionId || null;
                    this.messages = data.messages || [];
                }
            } catch (e) {}

            // Restore bubble position
            try {
                const pos = localStorage.getItem('ai_bubble_pos');
                if (pos) {
                    const p = JSON.parse(pos);
                    this.bubbleX = p.x;
                    this.bubbleY = p.y;
                }
            } catch (e) {}

            // Read page context
            this.readPageContext();

            // Listen for Escape key to close drawer
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.drawerOpen) {
                    this.closeDrawer();
                }
            });
        },

        get bubbleStyle() {
            if (this.bubbleX !== null && this.bubbleY !== null) {
                return `position:fixed; left:${this.bubbleX}px; top:${this.bubbleY}px; bottom:auto; right:auto;`;
            }
            return '';
        },

        // ===== Drag logic (pointer events for mouse + touch) =====
        startDrag(e) {
            this.isDragging = false;
            this.dragMoved = false;
            this.dragStartX = e.clientX;
            this.dragStartY = e.clientY;

            const btn = e.currentTarget;
            const rect = btn.getBoundingClientRect();
            this._dragOffsetX = e.clientX - rect.left;
            this._dragOffsetY = e.clientY - rect.top;

            // If no stored position yet, initialize from current position
            if (this.bubbleX === null) {
                this.bubbleX = rect.left;
                this.bubbleY = rect.top;
            }

            btn.setPointerCapture(e.pointerId);

            this._onPointerMove = (ev) => this.onDragMove(ev);
            this._onPointerUp = (ev) => this.endDrag(ev);
            btn.addEventListener('pointermove', this._onPointerMove);
            btn.addEventListener('pointerup', this._onPointerUp);
            btn.addEventListener('pointercancel', this._onPointerUp);
        },

        onDragMove(e) {
            const dx = Math.abs(e.clientX - this.dragStartX);
            const dy = Math.abs(e.clientY - this.dragStartY);

            if (!this.dragMoved && (dx > this.dragThreshold || dy > this.dragThreshold)) {
                this.dragMoved = true;
                this.isDragging = true;
            }

            if (this.dragMoved) {
                let newX = e.clientX - this._dragOffsetX;
                let newY = e.clientY - this._dragOffsetY;

                // Clamp within viewport
                newX = Math.max(0, Math.min(newX, window.innerWidth - 56));
                newY = Math.max(0, Math.min(newY, window.innerHeight - 56));

                this.bubbleX = newX;
                this.bubbleY = newY;
            }
        },

        endDrag(e) {
            const btn = e.currentTarget;
            btn.removeEventListener('pointermove', this._onPointerMove);
            btn.removeEventListener('pointerup', this._onPointerUp);
            btn.removeEventListener('pointercancel', this._onPointerUp);
            btn.releasePointerCapture(e.pointerId);

            if (this.dragMoved) {
                // Save position
                try {
                    localStorage.setItem('ai_bubble_pos', JSON.stringify({ x: this.bubbleX, y: this.bubbleY }));
                } catch (e) {}
            }

            this.isDragging = false;
        },

        handleBubbleClick() {
            // Only open drawer if we didn't just drag
            if (!this.dragMoved) {
                this.openDrawer();
            }
            this.dragMoved = false;
        },

        // ===== Drawer =====
        openDrawer() {
            this.readPageContext();
            this.drawerOpen = true;
            this.$nextTick(() => {
                if (this.$refs.chatInput) {
                    this.$refs.chatInput.focus();
                }
                this.scrollToBottom();
            });
        },

        closeDrawer() {
            this.drawerOpen = false;
        },

        // ===== Page Context =====
        readPageContext() {
            this.pageContext = window.__aiContext || null;
        },

        // ===== Chat =====
        sendSuggestion(text) {
            this.inputText = text;
            this.sendMessage();
        },

        async sendMessage() {
            const text = this.inputText.trim();
            if (!text || this.isLoading) return;

            // Add user message
            this.messages.push({ role: 'user', text: text });
            this.inputText = '';
            this.isLoading = true;

            // Reset textarea height
            if (this.$refs.chatInput) {
                this.$refs.chatInput.style.height = 'auto';
            }

            this.$nextTick(() => this.scrollToBottom());

            // Build conversation history for the API
            const apiMessages = this.messages.map(m => ({
                role: m.role === 'user' ? 'user' : 'model',
                text: m.text
            }));

            // Prepend context to the latest user message if available
            let contextPrefix = '';
            if (this.pageContext) {
                const parts = [];
                if (this.pageContext.subject) parts.push('Subject: ' + this.pageContext.subject);
                if (this.pageContext.code) parts.push('Checkpoint Code: ' + this.pageContext.code);
                if (this.pageContext.indicator) parts.push('Topic: ' + this.pageContext.indicator);
                if (this.pageContext.strand) parts.push('Strand: ' + this.pageContext.strand);
                if (this.pageContext.subStrand) parts.push('Sub-Strand: ' + this.pageContext.subStrand);
                if (this.pageContext.level) parts.push('Level: ' + this.pageContext.level);
                if (this.pageContext.takeaways && Array.isArray(this.pageContext.takeaways) && this.pageContext.takeaways.length > 0) {
                    parts.push('Lesson Objectives: ' + this.pageContext.takeaways.join('; '));
                }
                if (this.pageContext.hasTextbook) {
                    parts.push('Curriculum Textbook: Verified notes & diagrams available on this page');
                }
                if (parts.length > 0) {
                    contextPrefix = '[Student is studying in Guided Learning: ' + parts.join(' | ') + ']\n\n';
                }
            }

            // Attach context to the latest user message for the API only
            if (contextPrefix && apiMessages.length > 0) {
                const lastIdx = apiMessages.length - 1;
                if (apiMessages[lastIdx].role === 'user') {
                    apiMessages[lastIdx] = {
                        ...apiMessages[lastIdx],
                        text: contextPrefix + apiMessages[lastIdx].text
                    };
                }
            }

            try {
                const response = await fetch('/api/agent/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: text,
                        messages: apiMessages,
                        type: 'lesson',
                        session_id: this.sessionId
                    })
                });

                const data = await response.json();

                if (data.success && data.message) {
                    this.messages.push({ role: 'model', text: data.message });
                    if (data.session_id) {
                        this.sessionId = data.session_id;
                    }
                    if (data.remaining_requests !== undefined) {
                        this.remainingRequests = data.remaining_requests;
                    }
                } else {
                    this.messages.push({
                        role: 'model',
                        text: data.message || 'Sorry, I couldn\'t process that. Please try again.'
                    });
                }
            } catch (err) {
                console.error('AI Bubble chat error:', err);
                this.messages.push({
                    role: 'model',
                    text: 'Oops! Something went wrong. Please check your connection and try again.'
                });
            }

            this.isLoading = false;
            this.saveSession();
            this.$nextTick(() => this.scrollToBottom());
        },

        saveSession() {
            try {
                localStorage.setItem('ai_bubble_session', JSON.stringify({
                    sessionId: this.sessionId,
                    messages: this.messages.slice(-30) // Keep last 30 messages
                }));
            } catch (e) {}
        },

        scrollToBottom() {
            if (this.$refs.messagesArea) {
                this.$refs.messagesArea.scrollTop = this.$refs.messagesArea.scrollHeight;
            }
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 100) + 'px';
        },

        // ===== Rendering =====
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        renderMarkdown(text) {
            if (!text) return '';
            let html = this.escapeHtml(text);

            // ===== Math formulas (process BEFORE other markdown) =====
            var mathReplace = function(expr) {
                return expr
                    .replace(/\\div/g, '÷')
                    .replace(/\\times/g, '×')
                    .replace(/\\frac\{([^}]*)\}\{([^}]*)\}/g, '<sup>$1</sup>⁄<sub>$2</sub>')
                    .replace(/(\w+)\^\{([^}]+)\}/g, '$1<sup>$2</sup>')
                    .replace(/(\w+)\^(\w)/g, '$1<sup>$2</sup>')
                    .replace(/(\w+)_\{([^}]+)\}/g, '$1<sub>$2</sub>')
                    .replace(/(\w+)_(\w)/g, '$1<sub>$2</sub>')
                    .replace(/\\sqrt\{([^}]+)\}/g, '√($1)')
                    .replace(/\\pm/g, '±').replace(/\\leq/g, '≤').replace(/\\geq/g, '≥')
                    .replace(/\\neq/g, '≠').replace(/\\cdot/g, '·')
                    .replace(/\\rightarrow/g, '→').replace(/\\leftarrow/g, '←');
            };

            // Display math: $$...$$ → centered block
            html = html.replace(/\$\$([^$]+?)\$\$/g, function(m, expr) {
                return '<div style="text-align:center;margin:8px 0;font-size:15px;">' + mathReplace(expr) + '</div>';
            });

            // Inline math: $...$ → inline styled span
            html = html.replace(/\$([^$]+?)\$/g, function(m, expr) {
                return '<span style="font-style:italic;">' + mathReplace(expr) + '</span>';
            });

            // Plain-text exponents outside $ delimiters (e.g., 4^2 → 4<sup>2</sup>)
            html = html.replace(/(\w+)\^\{([^}]+)\}/g, '$1<sup>$2</sup>');
            html = html.replace(/(\w+)\^(\w+)/g, '$1<sup>$2</sup>');
            html = html.replace(/([a-zA-Z])_(\d+)/g, '$1<sub>$2</sub>');

            // Horizontal rules
            html = html.replace(/^---$/gm, '<hr style="border:none;border-top:1px solid rgba(128,128,128,0.3);margin:12px 0;">');

            // Code blocks (``` ... ```)
            html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>');

            // Inline code
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

            // Bold
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

            // Italic
            html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

            // Unordered lists
            html = html.replace(/^[-•]\s+(.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>[\s\S]*?<\/li>)/g, '<ul>$1</ul>');
            // Clean up nested <ul> wrappers
            html = html.replace(/<\/ul>\s*<ul>/g, '');

            // Ordered lists
            html = html.replace(/^\d+\.\s+(.+)$/gm, '<li>$1</li>');

            // Headers
            html = html.replace(/^### (.+)$/gm, '<strong style="display:block;margin:8px 0 4px;">$1</strong>');
            html = html.replace(/^## (.+)$/gm, '<strong style="display:block;font-size:14px;margin:10px 0 4px;">$1</strong>');

            // Paragraphs (double newline)
            html = html.replace(/\n\n/g, '</p><p>');
            html = '<p>' + html + '</p>';

            // Single newlines to <br> (but not inside pre/code)
            html = html.replace(/(?<!<\/li>|<\/p>|<\/pre>|<\/ul>|<\/ol>)\n/g, '<br>');

            // Clean empty paragraphs
            html = html.replace(/<p>\s*<\/p>/g, '');

            return html;
        }
    };
}
</script>
