<?php

namespace App\Http\Controllers;

use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminHeroBannerController extends Controller
{
    /**
     * Display a listing of hero banners.
     */
    public function index()
    {
        $banners = HeroBanner::ordered()->get();
        return view('admin.hero-banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new hero banner.
     */
    public function create()
    {
        return view('admin.hero-banners.create');
    }

    /**
     * Store a newly created hero banner in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'media_type' => 'required|in:image,video',
            'media_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,webm,quicktime|max:51200',
            'badge_text' => 'nullable|string|max:50',
            'cta_text' => 'nullable|string|max:50',
            'cta_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $path = $request->file('media_file')->store('hero_banners', 'public');

        HeroBanner::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'media_type' => $request->media_type,
            'media_path' => $path,
            'badge_text' => $request->badge_text,
            'cta_text' => $request->cta_text,
            'cta_url' => $request->cta_url,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Hero banner slide created successfully.');
    }

    /**
     * Show the form for editing the specified hero banner.
     */
    public function edit($id)
    {
        $banner = HeroBanner::findOrFail($id);
        return view('admin.hero-banners.edit', compact('banner'));
    }

    /**
     * Update the specified hero banner in storage.
     */
    public function update(Request $request, $id)
    {
        $banner = HeroBanner::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'media_type' => 'required|in:image,video',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,webm,quicktime|max:51200',
            'badge_text' => 'nullable|string|max:50',
            'cta_text' => 'nullable|string|max:50',
            'cta_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'media_type' => $request->media_type,
            'badge_text' => $request->badge_text,
            'cta_text' => $request->cta_text,
            'cta_url' => $request->cta_url,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('media_file')) {
            // Delete old file if stored in hero_banners directory
            if (str_starts_with($banner->media_path, 'hero_banners/')) {
                Storage::disk('public')->delete($banner->media_path);
            }

            $data['media_path'] = $request->file('media_file')->store('hero_banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Hero banner slide updated successfully.');
    }

    /**
     * Remove the specified hero banner from storage.
     */
    public function destroy($id)
    {
        $banner = HeroBanner::findOrFail($id);

        if (str_starts_with($banner->media_path, 'hero_banners/')) {
            Storage::disk('public')->delete($banner->media_path);
        }

        $banner->delete();

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Hero banner slide deleted successfully.');
    }

    /**
     * Toggle active status of a hero banner.
     */
    public function toggleActive($id)
    {
        $banner = HeroBanner::findOrFail($id);
        $banner->update(['is_active' => !$banner->is_active]);

        return back()->with('success', 'Hero banner status updated.');
    }
}
