<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchAnalytic;
use App\Models\Quiz;
use App\Models\Video;
use App\Models\UserNote;
use App\Models\LevelGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
     * filtered by the user's current level group for relevance.
     */
    public function suggestions(Request $request)
    {
        $query = strtolower(trim($request->input('q', '')));
        $domain = $request->input('domain', 'lesson');
        $levelGroup = $request->input('level_group', '');
        $limit = 8;

        // Resolve level group slug to its grade level titles (cached for 10 min)
        $gradeLevels = $this->resolveGradeLevels($levelGroup);

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

        // 2. Get matching content titles from the database, filtered by level group
        $contentSuggestions = $this->getContentSuggestions($domain, $query, $gradeLevels, $limit);

        // 3. Merge, deduplicate, and prioritize (history first, then content)
        $merged = collect($historySuggestions)
            ->merge($contentSuggestions)
            ->unique()
            ->take($limit)
            ->values();

        return response()->json(['suggestions' => $merged]);
    }

    /**
     * Resolve a level group slug to its grade level titles.
     * Cached for 10 minutes to avoid repeated DB lookups.
     *
     * @return array e.g. ['JHS 1', 'JHS 2', 'JHS 3']
     */
    protected function resolveGradeLevels(string $levelGroupSlug): array
    {
        if (empty($levelGroupSlug)) {
            return [];
        }

        return Cache::remember("level_group_grades:{$levelGroupSlug}", 600, function () use ($levelGroupSlug) {
            $group = LevelGroup::where('slug', $levelGroupSlug)->with('levels')->first();
            if (!$group) {
                return [];
            }
            return $group->levels->pluck('title')->toArray();
        });
    }

    /**
     * Fetch real content titles matching the query for the given domain,
     * filtered by the user's current level group grade levels.
     *
     * Uses select() for efficiency — only fetches the title column.
     */
    protected function getContentSuggestions(string $domain, string $query, array $gradeLevels, int $limit): array
    {
        switch ($domain) {
            case 'quiz':
                $q = Quiz::select('title')
                    ->where('title', 'like', '%' . $query . '%');

                if (!empty($gradeLevels)) {
                    $q->whereIn('grade_level', $gradeLevels);
                }

                return $q->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->pluck('title')
                    ->map(fn($t) => strtolower($t))
                    ->toArray();

            case 'lesson':
            case 'saved_lesson':
                $q = Video::select('title')
                    ->where('title', 'like', '%' . $query . '%');

                if (!empty($gradeLevels)) {
                    $q->whereIn('grade_level', $gradeLevels);
                }

                return $q->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->pluck('title')
                    ->map(fn($t) => strtolower($t))
                    ->toArray();

            case 'note':
                // Notes are user-specific, no grade level filtering needed
                return UserNote::select('title')
                    ->where('title', 'like', '%' . $query . '%')
                    ->where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->pluck('title')
                    ->map(fn($t) => strtolower($t))
                    ->toArray();

            default:
                return [];
        }
    }
}
