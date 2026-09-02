<!-- Floating Background Upload Manager Drawer (Canva / YouTube Studio Style) -->
<div id="uploadManagerDrawerContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; display: none; max-width: calc(100vw - 30px); font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

    <!-- 1. COLLAPSED FLOATING PILL -->
    <div id="uploadManagerPill" style="display: none; cursor: pointer; background-color: #0f172a; color: white; padding: 10px 18px; border-radius: 9999px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3); border: 1px solid #334155; align-items: center; gap: 12px;">
        <div style="position: relative; display: flex; align-items: center; justify-content: center;">
            <i id="pillIcon" class="fas fa-cloud-upload-alt text-blue-400 text-base animate-pulse"></i>
            <span id="pillBadgeCount" style="position: absolute; top: -6px; right: -8px; background-color: #2563eb; color: white; font-size: 10px; font-weight: 900; width: 16px; height: 16px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; border: 1px solid #0f172a;">1</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span id="pillStatusText" style="font-size: 12px; font-weight: 700; color: #e2e8f0;">Uploading 1 item...</span>
            <span id="pillPercent" style="font-size: 12px; font-weight: 900; color: #60a5fa;">0%</span>
        </div>
        <button type="button" id="expandDrawerBtn" style="background: none; border: none; color: #94a3b8; cursor: pointer; margin-left: 4px;" title="Expand upload drawer">
            <i class="fas fa-chevron-up text-xs"></i>
        </button>
    </div>

    <!-- 2. EXPANDED UPLOAD DRAWER -->
    <div id="uploadManagerDrawer" style="display: none; background: white; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #cbd5e1; width: 384px; max-width: 100%; overflow: hidden;">
        <!-- Header -->
        <div style="background-color: #0f172a; color: white; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-cloud-upload-alt text-blue-400"></i>
                <h4 style="margin: 0; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #f1f5f9;">Upload Manager</h4>
                <span id="drawerActiveBadge" style="background-color: rgba(37, 99, 235, 0.8); color: white; font-size: 10px; font-weight: 900; padding: 2px 8px; border-radius: 9999px; border: 1px solid rgba(59, 130, 246, 0.4);">1 Active</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <button type="button" id="clearCompletedUploadsBtn" style="background: none; border: none; color: #94a3b8; font-size: 12px; padding: 4px 8px; border-radius: 4px; cursor: pointer;" title="Clear completed tasks">
                    <i class="fas fa-check-double text-[11px]"></i>
                </button>
                <button type="button" id="minimizeDrawerBtn" style="background: none; border: none; color: #94a3b8; font-size: 12px; padding: 4px 8px; border-radius: 4px; cursor: pointer;" title="Minimize">
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Task List -->
        <div id="uploadTaskList" style="padding: 12px; max-height: 320px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background-color: #f8fafc;">
            <!-- Dynamic task cards inserted here by upload-engine -->
        </div>

        <!-- Drawer Footer Info -->
        <div style="padding: 10px 14px; background-color: white; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #64748b; font-weight: 500;">
            <span>You can close modals &amp; navigate freely</span>
            <button type="button" id="openNewUploadFromDrawer" style="background: none; border: none; color: #2563eb; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                <i class="fas fa-plus text-[10px]"></i> New Upload
            </button>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION CONTAINER -->
<div id="uploadToastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1000000; display: flex; flex-direction: column; gap: 8px; pointer-events: none;"></div>
