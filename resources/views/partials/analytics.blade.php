@php
    $analyticsId = config('services.google_analytics.id');
@endphp

@if($analyticsId)
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $analyticsId }}', {
            'anonymize_ip': true,
            'cookie_flags': 'SameSite=None;Secure'
        });
    </script>
@endif
