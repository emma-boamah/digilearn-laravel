@extends('layouts.admin')

@section('title', 'Invite New Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Invite New Admin</h1>
                <p class="text-gray-600 mt-1">Send a secure invitation to a new administrator</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Users
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="mb-8 p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-indigo-400 mt-1"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-indigo-800">Secure Invitation Model</h3>
                        <div class="mt-2 text-sm text-indigo-700">
                            <p>This will create a new account with the <strong>Restricted Admin</strong> role. The user
                                will receive an email with a link to securely set their own password and access their
                                dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.users.invite.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-300 @enderror">
                        </div>
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-300 @enderror">
                        </div>
                        @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('admin.users') }}"
                            class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                            <i class="fas fa-paper-plane mr-2"></i>Send Invitation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection