@guest
    @if(!empty(config('services.google.client_id')))
        <!-- Google Identity Services One Tap for Guest Visitors -->
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            window.handleGoogleOneTap = function(response) {
                if (!response || !response.credential) {
                    return;
                }

                var csrfToken = document.querySelector('meta[name="csrf-token"]') 
                    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    : '{{ csrf_token() }}';

                fetch("{{ route('auth.google.one-tap') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        credential: response.credential
                    })
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.suspended && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        console.warn('Google One Tap:', data.message || 'Authentication unsuccessful');
                    }
                })
                .catch(function(err) {
                    console.error('Google One Tap request failed:', err);
                });
            };
        </script>

        <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://accounts.google.com/gsi/client" async defer></script>

        <div id="g_id_onload"
             data-client_id="{{ config('services.google.client_id') }}"
             data-callback="handleGoogleOneTap"
             data-auto_prompt="true"
             data-auto_select="false"
             data-cancel_on_tap_outside="false"
             data-itp_support="true">
        </div>
    @endif
@endguest
