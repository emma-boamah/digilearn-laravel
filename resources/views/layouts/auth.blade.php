<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        'primary-dark': '#2563eb',
                        secondary: '#f59e0b',
                        danger: '#ef4444',
                    }
                }
            }
        }
    </script>
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root {
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
        }
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: var(--safe-area-inset-top);
        }
        
        .security-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 1rem;
            background: white;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3), 0 2px 4px -1px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="font-sans text-gray-800 antialiased">
    @yield('content')
    
    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-gray-600 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            &copy; {{ date('Y') }} {{ config('app.name', 'Shoutoutgh') }}. All rights reserved.
        </div>
    </footer>

    <!-- Scripts -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Toggle recovery code section
        document.addEventListener('DOMContentLoaded', function() {
            const showRecoveryBtn = document.getElementById('show-recovery');
            const hideRecoveryBtn = document.getElementById('hide-recovery');
            const recoverySection = document.getElementById('recovery-section');
            const secondaryKeySection = document.getElementById('secondary-key-section');
            
            if (showRecoveryBtn && recoverySection) {
                showRecoveryBtn.addEventListener('click', function() {
                    recoverySection.classList.remove('hidden');
                    secondaryKeySection.classList.add('hidden');
                    this.classList.add('hidden');
                });
            }
            
            if (hideRecoveryBtn && recoverySection) {
                hideRecoveryBtn.addEventListener('click', function() {
                    recoverySection.classList.add('hidden');
                    secondaryKeySection.classList.remove('hidden');
                    showRecoveryBtn.classList.remove('hidden');
                });
            }
        });
    </script>
</body>
</html>