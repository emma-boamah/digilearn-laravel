@extends('layouts.admin')

@section('title', 'Manage Superuser Credentials')
@section('page-title', 'Superuser Credentials')
@section('page-description', 'Manage primary and secondary keys')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Credentials Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Superuser Credentials</h2>
                <p class="text-gray-600 mt-1">Manage your primary and secondary keys for website locking</p>
            </div>
            
            <div class="p-6">
                <!-- Primary Key Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Primary Security Key</h3>
                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Primary key is your superuser email address
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" 
                                   id="primary-email" 
                                   value="{{ auth()->user()->email }}"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   disabled>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Change Email</label>
                            <input type="email" 
                                   id="new-email"
                                   placeholder="Enter new email address"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Secondary Key Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Secondary Security Key</h3>
                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Secondary key is used for website unlocking
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" 
                                   id="current-password"
                                   placeholder="Enter current password"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" 
                                   id="new-password"
                                   placeholder="Enter new password"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" 
                                   id="confirm-password"
                                   placeholder="Confirm new password"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button id="cancel-btn" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button id="save-credentials" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Recovery Codes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Recovery Codes</h2>
                <p class="text-gray-600 mt-1">Use these codes to unlock the website if you forget your credentials</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    @foreach($recoveryCodes as $code)
                    <div class="bg-gray-50 p-3 rounded-lg text-center font-mono text-sm">
                        {{ $code }}
                    </div>
                    @endforeach
                </div>
                
                <div class="flex justify-between">
                    <button id="generate-recovery" 
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        Generate New Codes
                    </button>
                    
                    <button id="download-codes" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-download mr-2"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.getElementById('save-credentials').addEventListener('click', function() {
        const newEmail = document.getElementById('new-email').value;
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        if (newPassword !== confirmPassword) {
            alert('Passwords do not match');
            return;
        }
        
        const formData = new FormData();
        if (newEmail) formData.append('email', newEmail);
        if (currentPassword) formData.append('current_password', currentPassword);
        if (newPassword) formData.append('new_password', newPassword);
        
        fetch('{{ route("admin.credentials.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Credentials updated successfully');
                if (data.new_email) {
                    document.getElementById('primary-email').value = data.new_email;
                    document.getElementById('new-email').value = '';
                }
                document.getElementById('current-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            } else {
                alert(data.message || 'Error updating credentials');
            }
        });
    });
    
    document.getElementById('generate-recovery').addEventListener('click', function() {
        if (confirm('Generating new recovery codes will invalidate your old codes. Continue?')) {
            fetch('{{ route("admin.credentials.recovery") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('New recovery codes generated');
                    location.reload();
                } else {
                    alert('Error generating recovery codes');
                }
            });
        }
    });
    
    document.getElementById('download-codes').addEventListener('click', function() {
        const codes = @json($recoveryCodes);
        const content = "Recovery Codes:\n\n" + codes.join("\n");
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'superuser-recovery-codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
    
    document.getElementById('cancel-btn').addEventListener('click', function() {
        document.getElementById('new-email').value = '';
        document.getElementById('current-password').value = '';
        document.getElementById('new-password').value = '';
        document.getElementById('confirm-password').value = '';
    });
</script>
@endsection