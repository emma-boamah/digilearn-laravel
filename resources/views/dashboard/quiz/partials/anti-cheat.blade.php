<script nonce="{{ request()->attributes->get('csp_nonce') }}">
(function() {
  const quizId = {!! json_encode($quiz['id'] ?? null) !!};
  const violationUrl = quizId ? '{{ url("/dashboard/quiz") }}/' + quizId + '/violation' : null;
  let violations = 0;
  const maxViolations = 1; // Render failed on first violation
  let lastVisibilityChange = Date.now();
  let visibilityChangeCount = 0;
  let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

  async function reportViolation(type, details) {
    violations += 1;
    try {
      if (violationUrl) {
        await fetch(violationUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ type, details })
        });
      }
    } catch (e) {}

    showViolationModal(type, details);
  }

  function showViolationModal(type, details) {
    // Remove any existing modal
    const existingModal = document.getElementById('violation-modal');
    if (existingModal) {
      existingModal.remove();
    }

    // Create violation modal
    const modal = document.createElement('div');
    modal.id = 'violation-modal';
    modal.innerHTML = `
      <div style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
      ">
        <div style="
          background: white;
          border-radius: 1rem;
          padding: 2rem;
          max-width: 500px;
          width: 90%;
          box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
          border: 2px solid #E11E2D;
          animation: modalSlideIn 0.3s ease-out;
        ">
          <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="
              width: 64px;
              height: 64px;
              background: #E11E2D;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              margin: 0 auto 1rem;
              animation: warningPulse 2s infinite;
            ">
              <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
            </div>
            <h2 style="
              color: #E11E2D;
              font-size: 1.5rem;
              font-weight: 700;
              margin-bottom: 0.5rem;
              font-family: 'Inter', sans-serif;
            ">Academic Integrity Violation</h2>
            <p style="
              color: #374151;
              font-size: 1rem;
              margin-bottom: 1rem;
              font-family: 'Inter', sans-serif;
            ">Your quiz attempt has been terminated due to a detected violation.</p>
          </div>

          <div style="
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
          ">
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
              <svg width="20" height="20" fill="#DC2626" viewBox="0 0 24 24" style="margin-top: 0.125rem; flex-shrink: 0;">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              <div>
                <p style="
                  color: #991B1B;
                  font-weight: 600;
                  margin-bottom: 0.25rem;
                  font-family: 'Inter', sans-serif;
                ">Violation Detected: ${getViolationDisplayName(type)}</p>
                <p style="
                  color: #7F1D1D;
                  font-size: 0.875rem;
                  margin: 0;
                  font-family: 'Inter', sans-serif;
                ">${details || 'This action violates our academic integrity policy.'}</p>
              </div>
            </div>
          </div>

          <div style="
            background: #F3F4F6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
          ">
            <p style="
              color: #374151;
              font-size: 0.875rem;
              margin: 0;
              text-align: center;
              font-family: 'Inter', sans-serif;
            ">
              <strong>Your quiz will be submitted automatically in <span id="countdown" style="color: #E11E2D; font-weight: 700;">5</span> seconds...</strong>
            </p>
          </div>

          <div style="text-align: center;">
            <button id="submit-now-btn" style="
              background: linear-gradient(135deg, #E11E2D, #C41E2A);
              color: white;
              border: none;
              padding: 0.75rem 2rem;
              border-radius: 0.5rem;
              font-size: 1rem;
              font-weight: 600;
              cursor: pointer;
              transition: all 0.2s ease;
              font-family: 'Inter', sans-serif;
            " onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'"
               onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
              Submit Quiz Now
            </button>
          </div>
        </div>
      </div>

      <style>
        @keyframes modalSlideIn {
          from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
          }
          to {
            opacity: 1;
            transform: scale(1) translateY(0);
          }
        }

        @keyframes warningPulse {
          0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(225, 30, 45, 0.4);
          }
          50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 8px rgba(225, 30, 45, 0);
          }
        }
      </style>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Countdown timer
    let countdown = 5;
    const countdownEl = modal.querySelector('#countdown');
    const submitBtn = modal.querySelector('#submit-now-btn');

    const timer = setInterval(() => {
      countdown--;
      countdownEl.textContent = countdown;

      if (countdown <= 0) {
        clearInterval(timer);
        submitQuizWithViolation();
      }
    }, 1000);

    // Submit now button
    submitBtn.addEventListener('click', () => {
      clearInterval(timer);
      submitQuizWithViolation();
    });
  }

  function getViolationDisplayName(type) {
    const names = {
      'copy': 'Copying Content',
      'cut': 'Cutting Content',
      'paste': 'Pasting Content',
      'selection': 'Text Selection',
      'screenshot': 'Screenshot Attempt',
      'context_menu': 'Right Click Menu',
      'tab_switch': 'Tab Switching',
      'window_blur': 'Window Focus Loss',
      'mobile_app_switch': 'App Switching (Mobile)',
      'long_press': 'Long Press Gesture',
      'multi_touch': 'Multi-Touch Gesture',
      'zoom_gesture': 'Zoom Gesture',
      'rapid_visibility_change': 'Rapid App Switching',
      'orientation_app_switch': 'Device Rotation During App Switch',
      'share_attempt': 'Content Sharing Attempt',
      'drag': 'Content Dragging',
      'image_save': 'Image Saving Attempt',
      'dev_tools': 'Developer Tools Detected',
      'fullscreen_exit': 'Fullscreen Mode Exit',
      'page_exit': 'Page Navigation Attempt',
      'extended_absence': 'Extended Absence'
    };
    return names[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }

  function submitQuizWithViolation() {
    console.log('Submitting quiz with violation...');
    const form = document.querySelector('form[data-quiz-form]');
    if (form) {
      console.log('Found quiz form, adding violation flag');
      // Mark as failed
      const failed = document.createElement('input');
      failed.type = 'hidden';
      failed.name = 'failed_due_to_violation';
      failed.value = '1';
      form.appendChild(failed);
      console.log('Submitting form...');
      form.submit();
    } else {
      console.log('No quiz form found, redirecting to index');
      window.location.href = '{{ route('quiz.index') }}';
    }
  }

  // Block copying and text selection
  document.addEventListener('copy', function(e) {
    e.preventDefault();
    reportViolation('copy', 'User attempted to copy text');
  });
  document.addEventListener('cut', function(e) {
    e.preventDefault();
    reportViolation('cut', 'User attempted to cut text');
  });
  document.addEventListener('paste', function(e) {
    e.preventDefault();
    reportViolation('paste', 'User attempted to paste text');
  });

  // Prevent text selection (only for non-quiz elements)
  document.addEventListener('selectstart', function(e) {
    // Allow text selection on quiz options and question text
    if (e.target.closest('.option') || e.target.closest('.question-text')) {
      return; // Allow selection
    }
    e.preventDefault();
    reportViolation('selection', 'User attempted to select text');
  });

  // Prevent context menu (right-click and long press on mobile)
  document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    reportViolation('context_menu', 'Context menu triggered (right-click/long press)');
  });

  // Enhanced screenshot detection
  document.addEventListener('keydown', function(e) {
    // PrintScreen key
    if (e.key === 'PrintScreen') {
      e.preventDefault();
      reportViolation('screenshot', 'PrintScreen key detected');
    }
    // Common screenshot shortcuts
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      reportViolation('screenshot', 'Screenshot shortcut detected (Ctrl+S/Cmd+S)');
    }
  });

  // Advanced visibility change detection for screenshots and app switching
  document.addEventListener('visibilitychange', function() {
    const now = Date.now();
    const timeDiff = now - lastVisibilityChange;
    lastVisibilityChange = now;
    visibilityChangeCount++;

    if (document.hidden) {
      let details = 'Document hidden';

      // Detect suspicious patterns
      if (timeDiff < 1000 && visibilityChangeCount > 1) {
        details += ' - Rapid visibility changes detected (possible screenshot attempt)';
        reportViolation('rapid_visibility_change', details);
      } else if (isMobile && timeDiff < 2000) {
        details += ' - Quick app switch on mobile (possible screenshot)';
        reportViolation('mobile_app_switch', details);
      } else {
        details += ' - Tab switch or minimize detected';
        reportViolation('tab_switch', details);
      }
    } else {
      // Document became visible again
      if (timeDiff > 5000) {
        reportViolation('extended_absence', `User was away for ${Math.round(timeDiff/1000)} seconds`);
      }
    }
  });

  // Window blur detection (switching apps/windows)
  window.addEventListener('blur', function() {
    if (isMobile) {
      reportViolation('mobile_app_switch', 'Window lost focus on mobile device');
    } else {
      reportViolation('window_blur', 'Window lost focus (app switch)');
    }
  });

  // Mobile-specific detections
  if (isMobile) {
    // Detect long press (common screenshot method on mobile)
    let longPressTimer;
    document.addEventListener('touchstart', function(e) {
      if (e.touches.length > 1) {
        reportViolation('multi_touch', 'Multiple touch points detected');
        return;
      }

      longPressTimer = setTimeout(function() {
        reportViolation('long_press', 'Long press detected (potential screenshot attempt)');
      }, 500);
    }, { passive: false });

    document.addEventListener('touchend', function() {
      clearTimeout(longPressTimer);
    }, { passive: true });

    document.addEventListener('touchmove', function() {
      clearTimeout(longPressTimer);
    }, { passive: true });

    // Detect device orientation changes (may indicate app switching)
    window.addEventListener('orientationchange', function() {
      // Allow some time for legitimate orientation changes
      setTimeout(function() {
        if (document.hidden) {
          reportViolation('orientation_app_switch', 'Orientation change while app hidden');
        }
      }, 1000);
    });

    // Prevent zoom gestures
    document.addEventListener('gesturestart', function(e) {
      e.preventDefault();
      reportViolation('zoom_gesture', 'Zoom gesture detected');
    });

    document.addEventListener('gesturechange', function(e) {
      e.preventDefault();
    });

    document.addEventListener('gestureend', function(e) {
      e.preventDefault();
    });

    // Detect when user tries to leave the page (back button, etc.)
    window.addEventListener('beforeunload', function(e) {
      reportViolation('page_exit', 'User attempted to leave the page');
    });

    // Monitor for Web Share API usage (sharing screenshots)
    if (navigator.share) {
      const originalShare = navigator.share;
      navigator.share = function(data) {
        reportViolation('share_attempt', 'User attempted to share content (possible screenshot)');
        return originalShare.apply(this, arguments);
      };
    }
  }

  // Additional security measures
  // Prevent drag and drop
  document.addEventListener('dragstart', function(e) {
    e.preventDefault();
    reportViolation('drag', 'Drag operation detected');
  });

  // Prevent image saving
  document.addEventListener('mousedown', function(e) {
    if (e.target.tagName === 'IMG') {
      reportViolation('image_save', 'Attempted to interact with image');
    }
  });

  // Detect if developer tools are opened (basic detection)
  let devtoolsOpen = false;
  const threshold = 160;
  setInterval(function() {
    if (window.outerHeight - window.innerHeight > threshold || window.outerWidth - window.innerWidth > threshold) {
      if (!devtoolsOpen) {
        devtoolsOpen = true;
        reportViolation('dev_tools', 'Developer tools detected');
      }
    } else {
      devtoolsOpen = false;
    }
  }, 500);

  // Prevent fullscreen exit (common after screenshots)
  document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement) {
      reportViolation('fullscreen_exit', 'Exited fullscreen mode');
    }
  });

})();
</script>