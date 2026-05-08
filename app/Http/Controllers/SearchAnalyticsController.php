<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchAnalytic;
use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\Note;
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
     * Get auto-complete suggestions.
     *
     * Blends past search history with real content titles from the database,
     * so suggestions are always relevant to what actually exists.
     */
    public function suggestions(Request $request)
    {
        $query = strtolower(trim($request->input('q', '')));
        $domain = $request->input('domain', 'lesson');
        $limit = 8;

        if (empty($query)) {
            // When input is empty: show trending searches for this domain
            $suggestions = SearchAnalytic::where('domain', $domain)
                ->orderBy('hits', 'desc')
                ->limit(5)
                ->pluck('query');

            return response()->json(['suggestions' => $suggestions]);
        }

        // 1. Get matching past searches (ranked by popularity)
        $historySuggestions = SearchAnalytic::where('domain', $domain)
            ->where('query', 'like', $query . '%')
            ->orderBy('hits', 'desc')
            ->limit($limit)
            ->pluck('query')
            ->toArray();

        // 2. Get matching content titles from the actual database
        $contentSuggestions = $this->getContentSuggestions($domain, $query, $limit);

        // 3. Merge, deduplicate, and prioritize (history first, then content)
        $merged = collect($historySuggestions)
            ->merge($contentSuggestions)
            ->unique()
            ->take($limit)
            ->values();

        return response()->json(['suggestions' => $merged]);
    }

    /**
     * Fetch real content titles matching the query for the given domain.
     */
    protected function getContentSuggestions(string $domain, string $query, int $limit): array
    {
        switch ($domain) {
            case 'quiz':
                return Quiz::where('title', 'like', '%' . $query . '%')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->pluck('title')
                    ->map(fn($t) => strtolower($t))
                    ->toArray();

            case 'lesson':
            case 'saved_lesson':
                return Lesson::where('title', 'like', '%' . $query . '%')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->pluck('title')
                    ->map(fn($t) => strtolower($t))
                    ->toArray();

            case 'note':
                if (class_exists(Note::class)) {
                    return Note::where('title', 'like', '%' . $query . '%')
                        ->where('user_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->pluck('title')
                        ->map(fn($t) => strtolower($t))
                        ->toArray();
                }
                return [];

            default:
                return [];
        }
    }
}
