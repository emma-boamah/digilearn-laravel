<script nonce="{{ request()->attributes->get('csp_nonce') }}">
(function() {
  const quizId = {{ json_encode($quiz['id'] ?? null) }};
  const violationUrl = quizId ? `{{ url('/dashboard/quiz') }}/${quizId}/violation` : null;
  let violations = 0;
  const maxViolations = 1; // Render failed on first violation

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

    alert('Quiz failed due to a violation: ' + type);
    // Optionally auto-submit if form exists
    const form = document.querySelector('form[data-quiz-form]');
    if (form) {
      // Mark as failed
      const failed = document.createElement('input');
      failed.type = 'hidden';
      failed.name = 'failed_due_to_violation';
      failed.value = '1';
      form.appendChild(failed);
      form.submit();
    } else {
      window.location.href = '{{ route('quiz.index') }}';
    }
  }

  // Block copying
  document.addEventListener('copy', function(e) {
    e.preventDefault();
    reportViolation('copy', 'User attempted to copy text');
  });
  document.addEventListener('cut', function(e) {
    e.preventDefault();
    reportViolation('cut', 'User attempted to cut text');
  });
  document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
  });

  // Attempt basic screenshot detection (PrintScreen key)
  document.addEventListener('keydown', function(e) {
    if (e.key === 'PrintScreen') {
      e.preventDefault();
      reportViolation('screenshot', 'PrintScreen key detected');
    }
  });

  // Detect tab switch/minimize via visibility change
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      reportViolation('tab_switch', 'Document hidden (tab switch/minimize)');
    }
  });

  // Detect window blur (switching apps)
  window.addEventListener('blur', function() {
    reportViolation('window_blur', 'Window lost focus');
  });
})();
</script>