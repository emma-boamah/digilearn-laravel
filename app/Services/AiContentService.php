<?php

namespace App\Services;

use App\Models\Video;
use App\Models\Quiz;
use App\Models\Document;
use App\Models\AgentRequest;
use App\Models\LevelGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AiContentService
{
    /**
     * Fetch unified AI-generated content (Videos, Quizzes, Documents)
     */
    public function getAiContents(
        ?string $query = '',
        ?string $type = 'all',
        ?string $sort = 'newest',
        ?string $levelGroupSlug = '',
        ?string $contextSlug = ''
    ): Collection {
        $query = (string) ($query ?? '');
        $type = (string) ($type ?? 'all');
        $sort = (string) ($sort ?? 'newest');
        $levelGroupSlug = (string) ($levelGroupSlug ?? '');
        $contextSlug = (string) ($contextSlug ?? '');

        $contents = collect();

        // 1. Get AI Videos
        if ($type === 'all' || $type === 'videos') {
            $videoQuery = Video::with(['uploader:id,name,email', 'subject:id,name', 'documents', 'quizzes', 'categories'])
                ->where('is_agent_generated', true)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($sub) use ($query) {
                        $sub->where('title', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%")
                            ->orWhere('agent_query', 'like', "%{$query}%")
                            ->orWhere('agent_topic', 'like', "%{$query}%");
                    });
                });

            if ($levelGroupSlug) {
                $levelGroup = LevelGroup::where('slug', $levelGroupSlug)->with('levels')->first();
                if ($levelGroup) {
                    $levelTitles = $levelGroup->levels->pluck('title')->toArray();
                    $videoQuery->whereIn('grade_level', $levelTitles);
                }
            }

            if ($contextSlug) {
                $videoQuery->whereHas('categories', function ($catQ) use ($contextSlug) {
                    $catQ->where('slug', $contextSlug);
                });
            }

            $videos = $videoQuery->select([
                'id',
                'title',
                'description',
                'thumbnail_path',
                'views',
                'comments_count',
                'created_at',
                'uploaded_by',
                'status',
                'grade_level',
                'duration_seconds',
                'document_path',
                'quiz_id',
                'subject_id',
                'is_agent_generated',
                'agent_query',
                'agent_topic',
                DB::raw("'video' as content_type"),
                DB::raw('0 as likes'),
                DB::raw('0 as dislikes')
            ])->get();

            $contents = $contents->merge($videos->map(function ($item) {
                $item->published_date = $item->created_at ? $item->created_at->format('M d, Y') : '';
                $item->uploader_name = $item->uploader->name ?? 'AI Learning Agent';
                $item->uploader_email = $item->uploader->email ?? 'ai@system';
                $item->duration_formatted = $item->duration_seconds ? gmdate('H:i:s', $item->duration_seconds) : '00:00:00';
                $item->subject_name = $item->subject->name ?? null;
                $item->documents_count = $item->documents ? $item->documents->count() : 0;
                $item->quizzes_count = $item->quizzes ? $item->quizzes->count() : 0;
                return $item;
            }));
        }

        // 2. Get AI Quizzes
        if ($type === 'all' || $type === 'quizzes') {
            $quizzesQuery = Quiz::with(['uploader:id,name,email', 'ratings', 'subject:id,name'])
                ->where('is_agent_generated', true)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($sub) use ($query) {
                        $sub->where('title', 'like', "%{$query}%")
                            ->orWhereHas('subject', function ($subQ) use ($query) {
                                $subQ->where('name', 'like', "%{$query}%");
                            });
                    });
                });

            if ($levelGroupSlug) {
                $levelGroup = LevelGroup::where('slug', $levelGroupSlug)->with('levels')->first();
                if ($levelGroup) {
                    $levelTitles = $levelGroup->levels->pluck('title')->toArray();
                    $quizzesQuery->whereIn('grade_level', $levelTitles);
                }
            }

            if ($contextSlug) {
                $quizzesQuery->whereHas('categories', function ($catQ) use ($contextSlug) {
                    $catQ->where('slug', $contextSlug);
                });
            }

            $quizzes = $quizzesQuery->select([
                'id',
                'title',
                'created_at',
                'uploaded_by',
                'grade_level',
                'is_featured',
                'subject_id',
                'video_id',
                'is_agent_generated',
                DB::raw("'quiz' as content_type"),
                DB::raw('attempts_count as views'),
                DB::raw('0 as likes'),
                DB::raw('0 as dislikes'),
                DB::raw('0 as comments_count'),
                DB::raw('NULL as duration_seconds')
            ])->get();

            $contents = $contents->merge($quizzes->map(function ($item) {
                $item->published_date = $item->created_at ? $item->created_at->format('M d, Y') : '';
                $item->uploader_name = $item->uploader->name ?? 'AI Learning Agent';
                $item->uploader_email = $item->uploader->email ?? 'ai@system';
                $item->thumbnail_path = null;
                $item->status = 'approved';
                $item->duration_formatted = 'N/A';
                $item->description = $item->subject->name ?? 'No Subject';
                $item->subject_name = $item->subject->name ?? null;

                $ratings = $item->ratings;
                $item->average_rating = $ratings && $ratings->count() > 0 ? round($ratings->avg('rating'), 1) : null;
                $item->total_ratings = $ratings ? $ratings->count() : 0;

                return $item;
            }));
        }

        // Sort contents
        switch ($sort) {
            case 'oldest':
                $contents = $contents->sortBy('created_at');
                break;
            case 'most_viewed':
                $contents = $contents->sortByDesc('views');
                break;
            case 'newest':
            default:
                $contents = $contents->sortByDesc('created_at');
                break;
        }

        return $contents;
    }

    /**
     * Find a specific AI content item with full details and agent request logs
     */
    public function findAiContent(int $id, string $type = 'video')
    {
        if ($type === 'quiz') {
            $item = Quiz::with(['uploader', 'subject', 'ratings'])->where('is_agent_generated', true)->find($id);
            if ($item) {
                $item->content_type = 'quiz';
                $item->setRelation('questions', $item->questions());
                $item->agent_request = AgentRequest::where('quiz_id', $id)->with('user')->first();
            }
            return $item;
        }

        $item = Video::with(['uploader', 'subject', 'documents', 'quizzes', 'categories'])->where('is_agent_generated', true)->find($id);
        if (!$item) {
            $item = Quiz::with(['uploader', 'subject', 'ratings'])->where('is_agent_generated', true)->find($id);
            if ($item) {
                $item->content_type = 'quiz';
                $item->setRelation('questions', $item->questions());
                $item->agent_request = AgentRequest::where('quiz_id', $id)->with('user')->first();
                return $item;
            }
        } else {
            $item->content_type = 'video';
            $item->agent_request = AgentRequest::where('video_id', $id)->with('user')->first();
        }

        return $item;
    }

    /**
     * Get aggregate statistics for AI-generated contents and AI tutor requests
     */
    public function getAiStats(): array
    {
        return [
            'total_ai_videos' => Video::where('is_agent_generated', true)->count(),
            'total_ai_quizzes' => Quiz::where('is_agent_generated', true)->count(),
            'total_ai_requests' => AgentRequest::count(),
            'successful_requests' => AgentRequest::successful()->count(),
            'failed_requests' => AgentRequest::failed()->count(),
            'total_ai_views' => Video::where('is_agent_generated', true)->sum('views'),
        ];
    }
}
