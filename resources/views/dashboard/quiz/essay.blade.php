<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Essay Quiz - {{ $quiz['title'] ?? 'Quiz' }} - {{ config('app.name', 'ShoutOutGh') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f9fafb; color:#111827; }
        .container { max-width: 960px; margin: 0 auto; padding: 1.5rem; }
        .card { background:#fff; border:1px solid #e5e7eb; border-radius: .75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .header { display:flex; justify-content:space-between; align-items:center; padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; position:sticky; top:0; background:#fff; z-index:10; }
        .title { font-size:1.25rem; font-weight:700; }
        .timer { font-weight:600; color:#111827; background:#f3f4f6; border-radius:.5rem; padding:.25rem .5rem; }
        .content { padding:1.25rem; }
        textarea { width:100%; min-height: 320px; padding: .75rem; font-size:1rem; line-height:1.5; border:1px solid #d1d5db; border-radius:.5rem; outline:none; }
        textarea:focus { border-color:#2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.2); }
        .actions { display:flex; gap:.75rem; justify-content:flex-end; padding:1rem 1.25rem; border-top:1px solid #e5e7eb; }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.625rem 1rem; border-radius:.5rem; font-weight:600; cursor:pointer; border:1px solid transparent; }
        .btn.secondary { background:#fff; color:#111827; border-color:#d1d5db; }
        .btn.primary { background:#2563eb; color:#fff; }
        .muted { color:#6b7280; font-size:.875rem; margin-top:.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="title">Essay: {{ $quiz['title'] ?? 'Quiz' }}</div>
                <div class="timer"><i class="fas fa-clock"></i> <span id="countdown"></span></div>
            </div>
            <div class="content">
                <form method="POST" action="{{ route('quiz.essay.submit', $quiz['encoded_id']) }}" data-quiz-form>
                    @csrf
                    <label for="essay" class="sr-only">Your Essay Answer</label>
                    <textarea id="essay" name="essay" placeholder="Type your answer here..." required></textarea>
                    <input type="hidden" name="time_spent" id="time_spent" value="0">
                    <p class="muted"><i class="fas fa-shield-alt"></i> Anti‑cheat active: screenshots, copy, and tab switching will fail the quiz.</p>
                    <div class="actions">
                        <a href="{{ route('quiz.instructions', $quiz['encoded_id']) }}" class="btn secondary"><i class="fas fa-arrow-left"></i> Back</a>
                        <button type="submit" class="btn primary"><i class="fas fa-paper-plane"></i> Submit Essay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function(){
            let remaining = {{ (int)($seconds ?? 0) }};
            const countdownEl = document.getElementById('countdown');
            const timeSpentEl = document.getElementById('time_spent');
            const tick = () => {
                if (remaining <= 0) {
                    document.querySelector('form[data-quiz-form]').submit();
                    return;
                }
                const m = Math.floor(remaining/60).toString().padStart(2,'0');
                const s = (remaining%60).toString().padStart(2,'0');
                countdownEl.textContent = `${m}:${s}`;
                timeSpentEl.value = ({{ (int)($seconds ?? 0) }} - remaining);
                remaining -= 1;
            };
            tick();
            setInterval(tick, 1000);
        })();
    </script>

    @include('dashboard.quiz.partials.anti-cheat')
</body>
</html>