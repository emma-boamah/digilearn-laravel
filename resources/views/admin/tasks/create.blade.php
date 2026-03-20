@extends('layouts.admin')

@section('title', 'Assign New Task')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Assign New Task</h1>
            <p class="text-gray-600 mt-1">Fill in the details to assign a task to another admin</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                        <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="assignee_id" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                        <select name="assignee_id" id="assignee_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Admin</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ old('assignee_id') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }} ({{ $admin->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('admin.tasks.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Assign Task
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
