@extends('layouts.admin')

@section('title', 'Edit Hero Banner Slide')
@section('page-title', 'Edit Hero Slide')
@section('page-description', 'Modify slide details or update media file.')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-bold text-slate-800">Edit Slide #{{ $banner->id }}</h3>
            <a href="{{ route('admin.hero-banners.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                ← Back to Slides
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <div><i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.hero-banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Current Media Preview -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Current Media</label>
                <div class="w-48 h-28 rounded-xl overflow-hidden border border-slate-200 bg-slate-900 flex items-center justify-center relative">
                    @if($banner->media_type === 'video')
                        <video src="{{ $banner->media_url }}" controls class="w-full h-full object-cover"></video>
                    @else
                        <img src="{{ $banner->media_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    @endif
                </div>
            </div>

            <!-- Media Type -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Media Type</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="media_type" value="image" @checked($banner->media_type === 'image') class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-slate-700"><i class="fas fa-image text-slate-400 mr-1"></i> Image</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="media_type" value="video" @checked($banner->media_type === 'video') class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-slate-700"><i class="fas fa-video text-slate-400 mr-1"></i> Video</span>
                    </label>
                </div>
            </div>

            <!-- Replace File Upload -->
            <div>
                <label for="media_file" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Replace File (Optional)</label>
                <input type="file" name="media_file" id="media_file" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">
                <p class="text-xs text-slate-400 mt-1">Leave empty to keep current media file.</p>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">
            </div>

            <!-- Subtitle -->
            <div>
                <label for="subtitle" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Subtitle / Description</label>
                <textarea name="subtitle" id="subtitle" rows="3" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">{{ old('subtitle', $banner->subtitle) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Badge Text</label>
                    <input type="text" name="badge_text" id="badge_text" value="{{ old('badge_text', $banner->badge_text) }}" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $banner->sort_order) }}" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- CTA Text -->
                <div>
                    <label for="cta_text" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">CTA Button Text</label>
                    <input type="text" name="cta_text" id="cta_text" value="{{ old('cta_text', $banner->cta_text) }}" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">
                </div>

                <!-- CTA URL -->
                <div>
                    <label for="cta_url" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">CTA Link / URL</label>
                    <input type="text" name="cta_url" id="cta_url" value="{{ old('cta_url', $banner->cta_url) }}" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" @checked($banner->is_active) class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm font-semibold text-slate-700">Active (Visible in Carousel)</label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-all shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
