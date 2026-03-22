<style nonce="{{ request()->attributes->get('csp_nonce') }}">
  .question-text, .option {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }
  .blocker-content-wrap { background:white; padding:3rem; border-radius:1.5rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1); max-width:500px; width:90%; border-top:8px solid #2563EB; }
  .blocker-icon-wrap { width:64px; height:64px; background:#DBEAFE; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; }
  .blocker-title { color:#111827; margin-bottom:1rem; font-weight:800; font-size:1.5rem; }
  .blocker-msg { color:#4B5563; margin-bottom:2rem; line-height:1.6; font-size:1rem; }
  .blocker-btn { background:#2563EB; color:white; padding:12px 24px; border-radius:0.75rem; border:none; cursor:pointer; font-weight:700; font-size:1.125rem; transition:transform 0.1s; }
  
  .modal-container { background:white; border-radius:1.5rem; padding:2.5rem; max-width:480px; width:90%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); text-align:center; }
  .modal-icon-wrap { width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; }
  .modal-title { color:#111827; font-size:1.5rem; font-weight:800; margin-bottom:0.75rem; }
  .modal-msg { color:#4B5563; font-size:1rem; line-height:1.5; margin-bottom:1.5rem; }
  .modal-details { background:#F9FAFB; border-radius:1rem; padding:1rem; margin-bottom:2rem; font-size:0.875rem; color:#6B7280; font-weight:500; }
  .modal-actions { display:flex; flex-direction:column; gap:0.75rem; }
  .btn-primary { color:white; border:none; padding:1rem; border-radius:0.75rem; font-weight:700; cursor:pointer; transition:transform 0.1s; }
  .btn-secondary { background:transparent; color:#6B7280; border:none; padding:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; }
  .modal-timer { margin-top:1.5rem; font-size:0.75rem; color:#9CA3AF; font-weight:bold; }
</style>
<script id="security-script" nonce="{{ request()->attributes->get('csp_nonce') }}">
(function() {
  const quizId = {!! json_encode($quiz['id'] ?? null) !!};
  // Use the verified route structure
  const baseUrl = '{{ url("/quiz") }}/' + quizId;
  const violationUrl = baseUrl + '/violation';
  const heartbeatUrl = baseUrl + '/heartbeat';

  let violationPoints = 0;
  const MAX_POINTS = 10;
  let isLockedOut = false;
  let isClearingBlocker = false;
  let isInitialLoad = true; // Prevents instant lockout on page load

  // Enhanced Mobile Detection (Hardware-Level)
  const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

  // Detection logic for "Fake Mobile" (Desktop pretending to be mobile)
  let isMobile = isMobileUA; // Strictly follow UA for security enforcement
  const isRealMobile = isTouchDevice && isMobileUA;

  // Randomized security identifier
  const SECURITY_ID = 'sb_' + Math.random().toString(36).substring(2, 9);

  // Weighted Points Configuration
  const POINT_VALUES = {
    'copy': 10,
    'cut': 10,
    'paste': 10,
    'screenshot': 10,
    'dev_tools': 10,
    'share_attempt': 10,
    'fullscreen_exit': 5,
    'tab_switch': 3,
    'window_blur': 2,
    'selection': 2,
    'context_menu': 2,
    'drag': 2,
    'image_save': 2,
    'multi_touch': 3,
    'zoom_gesture': 2,
    'long_press': 3
  };

  // State Tracking
  let lastBlurTime = null;
  let lastVisibilityHiddenTime = null;
  let lastEnvViolationTime = 0;

  /**
   * Sync heartbeat with server
   */
  async function sendHeartbeat() {
    if (isLockedOut) return;
    try {
      const response = await fetch(heartbeatUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        }
      });
      const data = await response.json();
      if (data.current_points !== undefined) {
        // Sync local points with server state to handle refreshes
        violationPoints = data.current_points;
        if (violationPoints >= MAX_POINTS) {
          triggerLockout('server_sync', 'Integrity threshold reached on server.');
        }
      }
    } catch (e) {
      console.error('Heartbeat failed', e);
    }
  }

  // Start heartbeat interval
  setInterval(sendHeartbeat, 30000);
  sendHeartbeat(); // Initial ping

  /**
   * Specialized Violation Handler
   */
  async function reportViolation(type, details, pointsOverride = null) {
    if (isLockedOut) return;

    let points = pointsOverride !== null ? pointsOverride : (POINT_VALUES[type] || 1);
    
    // Grace Period Logic: If returning within 3 seconds, reduce/negate points for blur events
    if (type === 'window_blur' || type === 'tab_switch') {
      // Points will be finalized on "focus" or "visibilitychange" to visible
      return; 
    }

    violationPoints += points;
    await syncViolation(type, details, points);

    if (violationPoints >= MAX_POINTS && !isInitialLoad) {
      triggerLockout(type, details);
    } else if (points > 0) {
      showWarningModal(type, details, MAX_POINTS - violationPoints);
    }
  }

  async function syncViolation(type, details, points) {
    try {
      await fetch(violationUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ type, details, points })
      });
    } catch (e) { }
  }

  /**
   * UI: Lockout Modal (Permanent)
   */
  function triggerLockout(type, details) {
    if (isLockedOut) return;
    isLockedOut = true;

    // Hide quiz content immediately
    const quizForm = document.querySelector('form[data-quiz-form]');
    if (quizForm) quizForm.style.display = 'none';

    renderModal({
      id: 'lockout-modal',
      title: 'Academic Integrity Lockout',
      message: 'This quiz attempt has been terminated due to persistent integrity violations.',
      type: 'error',
      details: details || getViolationDisplayName(type),
      primaryAction: {
        text: 'Submit & Exit',
        callback: () => submitQuizWithViolation(true)
      },
      secondaryAction: {
        text: 'Appeal to Instructor',
        callback: () => requestAppeal(type)
      },
      autoSubmit: true
    });
  }

  /**
   * UI: Warning Modal (Temporary)
   */
  function showWarningModal(type, details, pointsLeft) {
    renderModal({
      id: 'warning-modal',
      title: 'Integrity Warning',
      message: `Suspicious activity detected. Future violations will result in automatic failure.`,
      type: 'warning',
      details: `Reason: ${getViolationDisplayName(type)} (${pointsLeft} points remaining)`,
      primaryAction: {
        text: 'I Understand, Continue',
        callback: (modal) => {
          modal.remove();
          document.body.style.overflow = '';
          if (!isMobile) {
            document.documentElement.requestFullscreen().catch(() => { });
          }
        }
      }
    });
  }

  /**
   * UI: General Modal Renderer
   */
  function renderModal(config) {
    const existing = document.getElementById(config.id);
    if (existing) existing.remove();

    const color = config.type === 'error' ? '#E11E2D' : '#F59E0B';
    const bg = config.type === 'error' ? 'rgba(0, 0, 0, 0.9)' : 'rgba(0, 0, 0, 0.6)';

    const modal = document.createElement('div');
    modal.id = config.id;
    Object.assign(modal.style, {
      position: 'fixed',
      top: '0',
      left: '0',
      right: '0',
      bottom: '0',
      background: bg,
      zIndex: '100000',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      backdropFilter: 'blur(8px)',
      fontFamily: "'Inter', sans-serif"
    });

    modal.innerHTML = `
      <div class="modal-container" style="border-top: 8px solid ${color}">
        <div class="modal-icon-wrap" style="background: ${config.type === 'error' ? '#FEE2E2' : '#FEF3C7'}">
          <svg width="36" height="36" fill="${color}" viewBox="0 0 24 24">
             ${config.type === 'error' ? '<path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>' : '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>'}
          </svg>
        </div>
        <h2 class="modal-title">${config.title}</h2>
        <p class="modal-msg">${config.message}</p>
        <div class="modal-details">
          ${config.details}
        </div>
        <div class="modal-actions">
          <button id="modal-primary-btn" class="btn-primary" style="background: ${color}">${config.primaryAction.text}</button>
          ${config.secondaryAction ? `<button id="modal-secondary-btn" class="btn-secondary">${config.secondaryAction.text}</button>` : ''}
        </div>
        ${config.autoSubmit ? '<p class="modal-timer">Automatic submission in <span id="countdown">5</span>s</p>' : ''}
      </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Actions
    modal.querySelector('#modal-primary-btn').addEventListener('click', () => config.primaryAction.callback(modal));
    if (config.secondaryAction) {
      modal.querySelector('#modal-secondary-btn').addEventListener('click', () => config.secondaryAction.callback(modal));
    }

    if (config.autoSubmit) {
      let count = 5;
      const interval = setInterval(() => {
        count--;
        if (modal.querySelector('#countdown')) modal.querySelector('#countdown').textContent = count;
        if (count <= 0) {
          clearInterval(interval);
          config.primaryAction.callback(modal);
        }
      }, 1000);
    }
  }

  /**
   * Environmental Enforcement & Enhanced DevTools Detection
   */
  let devToolsOpenedByConsole = false;

  // Method 1: The Getter Trap
  // When DevTools opens, it evaluates objects to display them. This triggers the getter.
  const element = new Image();
  Object.defineProperty(element, 'id', {
    get: function () {
      // This ONLY runs if a human (or the DevTools UI) inspects the object
      devToolsOpenedByConsole = true;
    }
  });

  function isDevToolsOpen() {
    // 1. Reset the getter flag
    devToolsOpenedByConsole = false;

    // 2. The Getter Trap (Reliable for Firefox and Chrome)
    // We log the element. If DevTools is closed, the 'id' getter is never touched.
    console.log(element);
    console.clear();

    // 3. Calibrated Dimension Analysis (Fixes Chrome false positives)
    // We increase the threshold to 200 to account for scrollbars/high-DPI scaling
    const threshold = 200;
    const widthDiff = window.outerWidth - window.innerWidth > threshold;
    const heightDiff = window.outerHeight - window.innerHeight > threshold;

    // 4. Firebug/External detection check
    const isFirebug = window.Firebug && window.Firebug.chrome && window.Firebug.chrome.isInitialized;

    return widthDiff || heightDiff || devToolsOpenedByConsole || isFirebug;
  }

  /**
   * Display & Monitor Enforcement
   */
  function checkMultiMonitor() {
    if (isLockedOut) return false;

    // Logic: If the screen width is significantly larger than a standard monitor 
    // or the window's width, it usually means an extended desktop is active.
    const isExtendedDisplay = window.screen.width > 2560 && window.innerWidth < (window.screen.width / 1.1);

    if (isExtendedDisplay) {
      updateBlockerMessage(
        "Multiple monitors or an ultra-wide extended display detected. Please disconnect external screens and use a single standard monitor.",
        "I have disconnected extra screens",
        () => { location.reload(); }
      );
      return true;
    }
    return false;
  }

  // Self-Defending Logic: Debugger Trap (Refined with timing analysis)
  function runDebuggerTrap() {
    if (isDevToolsOpen() && !isInitialLoad) {
      const start = Date.now();
      (function () { return false; })['constructor']('debugger')['call']();
      const end = Date.now();
      // If the "debugger" line took more than 100ms to execute, 
      // it means a human was interacting with the breakpoint.
      if (end - start > 100) {
        reportViolation('dev_tools', 'Debugger interaction detected', 10);
      }
    }
  }

  setInterval(runDebuggerTrap, 1500);

  const blocker = document.createElement('div');
  blocker.id = SECURITY_ID;
  Object.assign(blocker.style, {
    position: 'fixed',
    top: '0',
    left: '0',
    right: '0',
    bottom: '0',
    background: 'rgba(243, 244, 246, 0.98)',
    zIndex: '2147483647',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backdropFilter: 'blur(15px)',
    fontFamily: "'Inter', sans-serif",
    textAlign: 'center',
    padding: '20px'
  });

  function updateBlockerMessage(msg, buttonText = "Retry Connection", buttonCallback = () => location.reload()) {
    blocker.style.display = 'flex';
    blocker.innerHTML = `
      <div class="blocker-content-wrap">
        <div class="blocker-icon-wrap">
          <svg width="32" height="32" fill="#2563EB" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
          </svg>
        </div>
        <h2 class="blocker-title">Environment Check Required</h2>
        <p class="blocker-msg">${msg}</p>
        <button id="blocker-action-btn" class="blocker-btn">${buttonText}</button>
      </div>`;

    if (!document.getElementById(SECURITY_ID)) {
      document.body.appendChild(blocker);
    }

    const actionBtn = blocker.querySelector('#blocker-action-btn');
    if (actionBtn) {
      actionBtn.addEventListener('click', buttonCallback);
    }
  }

  function monitorEnvironment() {
    if (isLockedOut) return;
    
    // Auto-Correct: If screen is large, it's NOT a mobile device bypass
    if (isMobile && window.screen.width > 1024) {
        console.warn('[Security] Screen width > 1024 detected. Overriding isMobile to false.');
        isMobile = false; 
    }

    // Diagnostic log
    console.log('[Security] Environment Monitor:', { 
        isMobile, 
        isRealMobile, 
        fullscreen: !!document.fullscreenElement,
        res: `${window.screen.width}x${window.screen.height}`,
        win: `${window.innerWidth}x${window.innerHeight}`
    });

    const now = Date.now();
    const canReportEnv = (now - lastEnvViolationTime) > 10000; // 10s throttle

    // 1. DevTools Detection (The Getter Trap + Dimension)
    const devOpen = isDevToolsOpen();

    if (!isRealMobile && devOpen) {
      updateBlockerMessage("Developer Tools detected. Please close all side panels/inspectors and refresh.");
      if (canReportEnv && !isInitialLoad) {
        reportViolation('dev_tools', 'DevTools detected via advanced analysis', 5);
        lastEnvViolationTime = now;
      }
      return;
    }

    // 2. Secondary Monitor Check
    if (checkMultiMonitor()) return;

    // 3. Maximization Check (Desktop Only)
    // Checks if the browser window is at least 90% of the available screen area
    const isNotMaximized = (window.screen.availWidth - window.outerWidth > 150) ||
      (window.screen.availHeight - window.outerHeight > 150);

    if (!isMobile && isNotMaximized) {
      updateBlockerMessage(
        "Browser window is not maximized. Please maximize the window and set zoom to 100% to continue.",
        "Window is Maximized",
        () => { monitorEnvironment(); } // Re-check without reloading
      );
      return;
    }

    // 4. Fullscreen Enforcement (Desktop Only)
    if (!isMobile && !document.fullscreenElement) {
      updateBlockerMessage(
        "Fullscreen mode is required to ensure a focused environment.",
        "Enter Fullscreen",
        () => {
          document.documentElement.requestFullscreen().catch(() => {
            alert("Fullscreen failed. Please ensure you are not using a 'Private/Incognito' window that blocks it.");
          });
        }
      );
      return;
    }

    // If all checks pass, remove blocker
    const existingBlocker = document.getElementById(SECURITY_ID);
    if (existingBlocker) {
      isClearingBlocker = true;
      existingBlocker.remove();
      setTimeout(() => isClearingBlocker = false, 100);
    }
  }

  // Anti-Tampering: Mutation Observer
  const securityWatchdog = new MutationObserver((mutations) => {
    if (isInitialLoad || isClearingBlocker || isLockedOut) return;
    mutations.forEach((mutation) => {
      if (mutation.removedNodes.length) {
        mutation.removedNodes.forEach(node => {
          if (node.id === SECURITY_ID || node.id === 'security-script') {
            // Only lock if the blocker was removed while environment was still invalid
            if (!document.fullscreenElement && !isMobile) {
              triggerLockout('tampering', 'Critical security modules were modified or removed.');
            }
          }
        });
      }
    });
  });
  securityWatchdog.observe(document.body, { childList: true, subtree: true });

  // Disable the "Initial Load" protection after 3 seconds
  setTimeout(() => { isInitialLoad = false; }, 3000);

  // Run monitor every 1000ms (Slowed down to prevent race conditions)
  setInterval(monitorEnvironment, 1000);
  document.addEventListener('fullscreenchange', monitorEnvironment);
  window.addEventListener('resize', () => { if (!isInitialLoad) monitorEnvironment(); });
  monitorEnvironment(); // Run immediately

  /**
   * (Cleanup old fullscreen logic)
   */


  /**
   * Event Listeners (Enhanced)
   */

  // 1. Visibility & Blur (With Grace Period & Screenshot Defense)
  window.addEventListener('blur', () => { 
    lastBlurTime = Date.now();
    // Screenshot Defense: Instant Blur Overlay
    updateBlockerMessage("Security Alert: Quiz content is hidden while browser focus is lost to prevent unauthorized captures.", "Resume Quiz", () => { window.focus(); });
  });

  document.addEventListener('visibilitychange', () => { 
    if (document.hidden) {
      lastVisibilityHiddenTime = Date.now();
      updateBlockerMessage("Security Alert: Quiz content is hidden while tab is inactive.", "Resume Quiz", () => { window.focus(); });
    } else {
      handleReturn('tab_switch');
    }
  });

  window.addEventListener('focus', () => {
    handleReturn('window_blur');
    // Ensure environment is still clean
    monitorEnvironment();
  });

  async function handleReturn(type) {
    if (isLockedOut) return;
    const lastTime = type === 'window_blur' ? lastBlurTime : lastVisibilityHiddenTime;
    if (!lastTime) return;

    const awayDuration = (Date.now() - lastTime) / 1000;

    if (awayDuration < 3) {
      // Small grace period violation (0 or 1 point)
      reportViolation(type, `Brief absence (${Math.round(awayDuration, 1)}s)`, 1);
    } else {
      // Normal violation
      reportViolation(type, `Extended absence (${Math.round(awayDuration)}s)`, POINT_VALUES[type]);
    }

    lastBlurTime = null;
    lastVisibilityHiddenTime = null;
    monitorEnvironment();
  }

  // 2. High Intent Violations
  ['copy', 'cut', 'paste'].forEach(evt => {
    document.addEventListener(evt, (e) => {
      e.preventDefault();
      reportViolation('copy', `Intentional Content ${evt.toUpperCase()}`);
    });
  });

  document.addEventListener('selectstart', (e) => {
    // 1. Whitelist: Allow selection on navigation, buttons, and inputs
    const isNav = e.target.closest('.nav-btn, .pagination, button, a, .step-indicator');
    const isInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA';

    if (isNav || isInput) return;

    // 2. Blacklist: Prevent selection on protected content
    if (e.target.closest('.option') || e.target.closest('.question-text')) {
      e.preventDefault();
      // We rely on CSS for the visual block; JS only prevents the gesture
    }
  });

  document.addEventListener('contextmenu', (e) => { e.preventDefault(); reportViolation('context_menu', 'Right-click menu blocked'); });

  // 3. DevTools, Fullscreen Exit & App Switching
  document.addEventListener('keydown', (e) => {
    // Block common cheat keys/shortcuts
    const blockedKeys = ['F12', 'PrintScreen', 'Alt', 'Meta', 'Control'];
    const isAppSwitch = (e.altKey && e.key === 'Tab') || (e.metaKey && e.key === 'Tab') || (e.key === 'Meta') || (e.key === 'OS');

    if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) || ((e.ctrlKey || e.metaKey) && e.key === 'U')) {
      e.preventDefault();
      reportViolation('dev_tools', 'Developer Tools shortcut detected');
    }
    if (e.key === 'PrintScreen') { e.preventDefault(); reportViolation('screenshot', 'PrintScreen key pressed'); }

    if (isAppSwitch) {
      // We can't actually stop the OS from switching, but we can log the intent
      reportViolation('window_blur', 'App switching attempt detected');
    }
  });

  // 4. Mobile & Gestures
  if (isMobile) {
    document.addEventListener('touchstart', (e) => {
      if (e.touches.length > 1) reportViolation('multi_touch', 'Multi-touch gesture suspicious');
    }, { passive: false });

    document.addEventListener('gesturestart', (e) => { e.preventDefault(); reportViolation('zoom_gesture', 'Zooming disabled during quiz'); });
  }

  /**
   * Utilities
   */
  function submitQuizWithViolation(isFinal) {
    let form = document.querySelector('form[data-quiz-form]');

    // If form doesn't exist ( lockout before user clicks submit), create it
    if (!form) {
      form = document.createElement('form');
      form.method = 'POST';
      form.action = baseUrl + '/submit';
      form.setAttribute('data-quiz-form', 'true');

      // Use global variables from take.blade.php if available
      if (typeof answers !== 'undefined') {
        const answersInput = document.createElement('input');
        answersInput.type = 'hidden';
        answersInput.name = 'answers';
        answersInput.value = JSON.stringify(answers);
        form.appendChild(answersInput);
      }

      if (typeof timeLimitMinutes !== 'undefined' && typeof timeRemaining !== 'undefined') {
        const timeSpentInput = document.createElement('input');
        timeSpentInput.type = 'hidden';
        timeSpentInput.name = 'time_spent';
        timeSpentInput.value = (timeLimitMinutes * 60) - timeRemaining;
        form.appendChild(timeSpentInput);
      }

      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
      form.appendChild(csrfInput);

      document.body.appendChild(form);
    }

    if (isFinal) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'failed_due_to_violation';
      input.value = '1';
      form.appendChild(input);
    }

    form.submit();
  }

  function requestAppeal() {
    alert("An appeal has been logged. Your instructor will review the activity logs for this attempt.");
    submitQuizWithViolation(true);
  }

  function getViolationDisplayName(type) {
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }

}) ();
</script>