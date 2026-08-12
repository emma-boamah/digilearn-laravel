@extends('layouts.admin')

@section('title', 'Hero Banners Management')
@section('page-title', 'Hero Banner Carousel')
@section('page-description', 'Manage front-facing hero carousel slides, media uploads, and badges.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Hero Slides</h3>
            <p class="text-xs text-slate-500 mt-0.5">Control images, videos, badges, and text shown on the student dashboard hero carousel.</p>
        </div>
        <a href="{{ route('admin.hero-banners.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-all shadow-sm flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Upload New Slide</span>
        </a>
    </div>

    <!-- Slides Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6">Media Preview</th>
                        <th class="py-3.5 px-6">Title & Subtitle</th>
                        <th class="py-3.5 px-6">Badge / CTA</th>
                        <th class="py-3.5 px-6 text-center">Sort Order</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($banners as $banner)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <div class="w-24 h-14 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center relative">
                                    @if($banner->media_type === 'video')
                                        <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center text-white text-lg z-10">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <video src="{{ $banner->media_url }}" class="w-full h-full object-cover" muted></video>
                                    @else
                                        <img src="{{ $banner->media_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 text-base">{{ $banner->title ?: 'Untitled Slide' }}</div>
                                <div class="text-xs text-slate-500 line-clamp-1 max-w-xs mt-0.5">{{ $banner->subtitle ?: 'No description' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-1 items-start">
                                    @if($banner->badge_text)
                                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-extrabold rounded-md uppercase tracking-wider">
                                            {{ $banner->badge_text }}
                                        </span>
                                    @endif
                                    @if($banner->cta_text)
                                        <span class="text-xs font-semibold text-slate-700">
                                            CTA: <span class="text-blue-600">{{ $banner->cta_text }}</span> 
                                            @if($banner->cta_url)<span class="text-slate-400">({{ $banner->cta_url }})</span>@endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-slate-700">
                                {{ $banner->sort_order }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <form action="{{ route('admin.hero-banners.toggle', $banner->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-full text-xs font-extrabold transition-all border {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.hero-banners.edit', $banner->id) }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Slide">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.hero-banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this slide?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Slide">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                No hero banners uploaded yet. Click "Upload New Slide" above to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
