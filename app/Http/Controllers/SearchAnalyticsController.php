<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchAnalytic;
use Illuminate\Support\Facades\Auth;

class SearchAnalyticsController extends Controller
{
    /**
     * Track a search query
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'domain' => 'required|string|in:lesson,quiz,note,saved_lesson',
        ]);

        $query = strtolower(trim($validated['query']));
        $domain = $validated['domain'];
        $userId = Auth::id();

        // Find existing query for this domain (and user if we want per-user tracking, or global)
        // Global tracking is better for YouTube-style suggestions
        $analytic = SearchAnalytic::firstOrNew([
            'query' => $query,
            'domain' => $domain,
        ]);

        if ($analytic->exists) {
            $analytic->hits += 1;
            $analytic->last_searched_at = now();
        } else {
            $analytic->user_id = $userId;
            $analytic->hits = 1;
            $analytic->last_searched_at = now();
        }

        $analytic->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Get auto-complete suggestions
     */
    public function suggestions(Request $request)
    {
        $query = $request->input('q', '');
        $domain = $request->input('domain', 'lesson');

        if (empty(trim($query))) {
            // Optional: return popular default searches if query is empty
            $suggestions = SearchAnalytic::where('domain', $domain)
                ->orderBy('hits', 'desc')
                ->limit(5)
                ->pluck('query');
                
            return response()->json(['suggestions' => $suggestions]);
        }

        $suggestions = SearchAnalytic::where('domain', $domain)
            ->where('query', 'like', strtolower($query) . '%')
            ->orderBy('hits', 'desc')
            ->limit(8)
            ->pluck('query');

        return response()->json(['suggestions' => $suggestions]);
    }
}
