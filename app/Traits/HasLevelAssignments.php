<?php

namespace App\Traits;

use App\Models\Level;
use App\Models\LevelGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasLevelAssignments
{
    /**
     * Get all assigned levels for this content.
     */
    public function levels(): MorphToMany
    {
        return $this->morphToMany(Level::class, 'levelable');
    }

    /**
     * Sync assigned levels by array of level IDs.
     * Also keeps the primary grade_level string column in sync for backward compatibility.
     *
     * @param array<int|string> $levelIds
     */
    public function syncLevels(array $levelIds): void
    {
        // Filter out empty/null values and cast to integer
        $cleanIds = array_values(array_filter(array_map('intval', $levelIds)));
        $this->levels()->sync($cleanIds);

        // Keep grade_level string in sync with the primary (first) level title
        if (!empty($cleanIds)) {
            $primaryLevel = Level::find($cleanIds[0]);
            if ($primaryLevel) {
                $this->grade_level = $primaryLevel->title;
                $this->saveQuietly();
            }
        }
    }

    /**
     * Add all levels in a level group to this content's assignments without removing existing ones.
     */
    public function assignLevelGroup(int|string $groupIdentifier): static
    {
        $group = is_numeric($groupIdentifier)
            ? LevelGroup::with('levels')->find($groupIdentifier)
            : LevelGroup::where('slug', $groupIdentifier)->with('levels')->first();

        if ($group) {
            $currentIds = $this->assigned_level_ids;
            $newIds = array_values(array_unique(array_merge($currentIds, $group->levels->pluck('id')->toArray())));
            $this->syncLevels($newIds);
        }

        return $this;
    }

    /**
     * Remove all levels belonging to a level group from this content's assignments.
     */
    public function removeLevelGroup(int|string $groupIdentifier): static
    {
        $group = is_numeric($groupIdentifier)
            ? LevelGroup::with('levels')->find($groupIdentifier)
            : LevelGroup::where('slug', $groupIdentifier)->with('levels')->first();

        if ($group) {
            $groupLevelIds = $group->levels->pluck('id')->toArray();
            $remainingIds = array_diff($this->assigned_level_ids, $groupLevelIds);
            $this->syncLevels(array_values($remainingIds));
        }

        return $this;
    }

    /**
     * Replace all assignments with all levels in a level group.
     */
    public function syncLevelGroup(int|string $groupIdentifier): static
    {
        $group = is_numeric($groupIdentifier)
            ? LevelGroup::with('levels')->find($groupIdentifier)
            : LevelGroup::where('slug', $groupIdentifier)->with('levels')->first();

        if ($group) {
            $this->syncLevels($group->levels->pluck('id')->toArray());
        }

        return $this;
    }

    /**
     * Scope query to items assigned to a specific level (by ID, slug, or title),
     * with fallback to legacy grade_level string column.
     */
    public function scopeForLevel(Builder $query, int|string $levelIdentifier): Builder
    {
        return $query->where(function (Builder $subQuery) use ($levelIdentifier) {
            $subQuery->whereHas('levels', function (Builder $levelQuery) use ($levelIdentifier) {
                if (is_numeric($levelIdentifier)) {
                    $levelQuery->where('levels.id', (int) $levelIdentifier);
                } else {
                    $levelQuery->where('levels.slug', $levelIdentifier)
                        ->orWhere('levels.title', $levelIdentifier);
                }
            })->orWhere(function (Builder $legacyQuery) use ($levelIdentifier) {
                if (is_numeric($levelIdentifier)) {
                    $level = Level::find($levelIdentifier);
                    if ($level) {
                        $legacyQuery->where('grade_level', $level->title)
                            ->orWhere('grade_level', $level->slug);
                    }
                } else {
                    $legacyQuery->where('grade_level', $levelIdentifier);
                }
            });
        });
    }

    /**
     * Scope query to items assigned to any level within a level group.
     */
    public function scopeForLevelGroup(Builder $query, int|string $groupIdentifier): Builder
    {
        return $query->where(function (Builder $subQuery) use ($groupIdentifier) {
            $subQuery->whereHas('levels.levelGroup', function (Builder $groupQuery) use ($groupIdentifier) {
                if (is_numeric($groupIdentifier)) {
                    $groupQuery->where('level_groups.id', (int) $groupIdentifier);
                } else {
                    $groupQuery->where('level_groups.slug', $groupIdentifier);
                }
            })->orWhere(function (Builder $legacyQuery) use ($groupIdentifier) {
                $group = is_numeric($groupIdentifier)
                    ? LevelGroup::with('levels')->find($groupIdentifier)
                    : LevelGroup::where('slug', $groupIdentifier)->with('levels')->first();

                if ($group && $group->levels->isNotEmpty()) {
                    $titles = $group->levels->pluck('title')->toArray();
                    $legacyQuery->whereIn('grade_level', $titles);
                }
            });
        });
    }

    /**
     * Get a formatted comma-separated list of assigned level titles.
     */
    public function getAssignedLevelsListAttribute(): string
    {
        if ($this->relationLoaded('levels') && $this->levels->isNotEmpty()) {
            return $this->levels->pluck('title')->implode(', ');
        }

        $levels = $this->levels()->get();
        if ($levels->isNotEmpty()) {
            return $levels->pluck('title')->implode(', ');
        }

        return $this->grade_level ?? 'Unassigned';
    }

    /**
     * Get an array of assigned level IDs.
     */
    public function getAssignedLevelIdsAttribute(): array
    {
        if ($this->relationLoaded('levels')) {
            return $this->levels->pluck('id')->toArray();
        }

        return $this->levels()->pluck('levels.id')->toArray();
    }
}
