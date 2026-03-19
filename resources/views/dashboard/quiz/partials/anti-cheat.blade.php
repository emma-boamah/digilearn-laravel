<script nonce="{{ request()->attributes->get('csp_nonce') }}">
(function() {
  const quizId = {!! json_encode($quiz['id'] ?? null) !!};
  // Use the verified route structure
  const baseUrl = '{{ url("/quiz") }}/' + quizId;
  const violationUrl = baseUrl + '/violation';
  const heartbeatUrl = baseUrl + '/heartbeat';
  
  let violationPoints = 0;
  const MAX_POINTS = 10;
  let isLockedOut = false;
  let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  
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

    if (violationPoints >= MAX_POINTS) {
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
    } catch (e) {}
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
          ensureFullscreen();
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
    modal.style = `position:fixed;top:0;left:0;right:0;bottom:0;background:${bg};z-index:100000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(8px);font-family:'Inter',sans-serif;`;
    
    modal.innerHTML = `
      <div style="background:white;border-radius:1.5rem;padding:2.5rem;max-width:480px;width:90%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);border-top:8px solid ${color};text-align:center;">
        <div style="width:72px;height:72px;background:${config.type === 'error' ? '#FEE2E2' : '#FEF3C7'};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
          <svg width="36" height="36" fill="${color}" viewBox="0 0 24 24">
             ${config.type === 'error' ? '<path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>' : '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>'}
          </svg>
        </div>
        <h2 style="color:#111827;font-size:1.5rem;font-weight:800;margin-bottom:0.75rem;">${config.title}</h2>
        <p style="color:#4B5563;font-size:1rem;line-height:1.5;margin-bottom:1.5rem;">${config.message}</p>
        <div style="background:#F9FAFB;border-radius:1rem;padding:1rem;margin-bottom:2rem;font-size:0.875rem;color:#6B7280;font-weight:500;">
          ${config.details}
        </div>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
          <button id="modal-primary-btn" style="background:${color};color:white;border:none;padding:1rem;border-radius:0.75rem;font-weight:700;cursor:pointer;transition:transform 0.1s;">${config.primaryAction.text}</button>
          ${config.secondaryAction ? `<button id="modal-secondary-btn" style="background:transparent;color:#6B7280;border:none;padding:0.5rem;font-size:0.875rem;font-weight:600;cursor:pointer;">${config.secondaryAction.text}</button>` : ''}
        </div>
        ${config.autoSubmit ? '<p style="margin-top:1.5rem;font-size:0.75rem;color:#9CA3AF;font-weight:bold;">Automatic submission in <span id="countdown">5</span>s</p>' : ''}
      </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Actions
    modal.querySelector('#modal-primary-btn').onclick = () => config.primaryAction.callback(modal);
    if (config.secondaryAction) {
      modal.querySelector('#modal-secondary-btn').onclick = () => config.secondaryAction.callback(modal);
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
   * Mandatory Fullscreen Enforcement
   */
  const contentOverlay = document.createElement('div');
  contentOverlay.id = 'fullscreen-enforcer';
  contentOverlay.style = 'position:fixed;top:0;left:0;right:0;bottom:0;background:white;z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;';
  contentOverlay.innerHTML = `
    <div style="text-align:center;padding:2rem;">
      <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1rem;">Fullscreen Required</h1>
      <p style="color:#6B7280;margin-bottom:2rem;">This quiz must be taken in fullscreen mode to ensure a focus-rich learning environment.</p>
      <button id="start-fullscreen" style="background:#2563EB;color:white;border:none;padding:1rem 2.5rem;border-radius:0.75rem;font-weight:700;cursor:pointer;font-size:1.125rem;">Enter Fullscreen & Start</button>
    </div>
  `;

  function checkFullscreen() {
    if (isLockedOut) return;
    if (!document.fullscreenElement && !isMobile) {
      if (!document.getElementById('fullscreen-enforcer')) {
        document.body.appendChild(contentOverlay);
      }
    } else {
      const enforcer = document.getElementById('fullscreen-enforcer');
      if (enforcer) enforcer.remove();
    }
  }

  function ensureFullscreen() {
    if (isMobile) return;
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => {});
    }
  }

  document.addEventListener('click', (e) => {
    if (e.target.id === 'start-fullscreen') {
      ensureFullscreen();
    }
  });

  document.addEventListener('fullscreenchange', checkFullscreen);
  setInterval(checkFullscreen, 1000);


  /**
   * Event Listeners (Enhanced)
   */

  // 1. Visibility & Blur (With Grace Period)
  window.addEventListener('blur', () => { lastBlurTime = Date.now(); });
  document.addEventListener('visibilitychange', () => { if (document.hidden) lastVisibilityHiddenTime = Date.now(); });

  window.addEventListener('focus', () => handleReturn('window_blur'));
  document.addEventListener('visibilitychange', () => { if (!document.hidden) handleReturn('tab_switch'); });

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
    checkFullscreen();
  }

  // 2. High Intent Violations
  ['copy', 'cut', 'paste'].forEach(evt => {
    document.addEventListener(evt, (e) => {
      e.preventDefault();
      reportViolation('copy', `Intentional Content ${evt.toUpperCase()}`);
    });
  });

  document.addEventListener('selectstart', (e) => {
    if (!e.target.closest('.option') && !e.target.closest('.question-text')) {
      e.preventDefault();
      reportViolation('selection', 'Unauthorized text selection attempt');
    }
  });

  document.addEventListener('contextmenu', (e) => { e.preventDefault(); reportViolation('context_menu', 'Right-click menu blocked'); });

  // 3. DevTools & Fullscreen Exit
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) || ((e.ctrlKey || e.metaKey) && e.key === 'U')) {
      e.preventDefault();
      reportViolation('dev_tools', 'Developer Tools shortcut detected');
    }
    if (e.key === 'PrintScreen') { e.preventDefault(); reportViolation('screenshot', 'PrintScreen key pressed'); }
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

})();
</script>