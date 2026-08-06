<?php

namespace App\Services;

use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;
use App\Models\ContentCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class BulkContentActionService
{
    /**
     * Delete multiple contents (Videos, Documents, Quizzes) in batch safely.
     *
     * @param array $items Array of items (e.g. [['id' => 1, 'type' => 'video'], ...] or array of IDs)
     * @param bool $deleteRelated Whether to delete attached sub-documents & sub-quizzes
     * @return array Summary of operation results
     */
    public function bulkDelete(array $items, bool $deleteRelated = true): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $messages = [];

        foreach ($items as $itemData) {
            $id = is_array($itemData) ? ($itemData['id'] ?? null) : $itemData;
            $type = is_array($itemData) ? ($itemData['type'] ?? null) : null;

            if (!$id) {
                continue;
            }

            try {
                $content = null;
                $contentType = $type;

                // If type is not specified, resolve model by ID
                if (!$contentType) {
                    if ($content = Video::find($id)) {
                        $contentType = 'video';
                    } elseif ($content = Document::find($id)) {
                        $contentType = 'document';
                    } elseif ($content = Quiz::find($id)) {
                        $contentType = 'quiz';
                    }
                } else {
                    switch ($contentType) {
                        case 'video':
                            $content = Video::find($id);
                            break;
                        case 'document':
                            $content = Document::find($id);
                            break;
                        case 'quiz':
                            $content = Quiz::find($id);
                            break;
                    }
                }

                if (!$content) {
                    $failedCount++;
                    $errors[] = "Content ID {$id} not found.";
                    continue;
                }

                // Delete based on resolved type
                if ($contentType === 'video') {
                    $this->deleteSingleVideo($content, $deleteRelated);
                } elseif ($contentType === 'document') {
                    $this->deleteSingleDocument($content);
                } elseif ($contentType === 'quiz') {
                    $this->deleteSingleQuiz($content);
                }

                $successCount++;
                Log::info("Bulk delete: deleted {$contentType} #{$id}", [
                    'admin_id' => Auth::id(),
                    'content_id' => $id,
                    'type' => $contentType,
                ]);
            } catch (Exception $e) {
                $failedCount++;
                $errors[] = "Failed to delete item #{$id}: " . $e->getMessage();
                Log::error("Bulk delete failed for item #{$id}", [
                    'admin_id' => Auth::id(),
                    'item' => $itemData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = "Successfully deleted {$successCount} content item(s).";
        if ($failedCount > 0) {
            $message .= " {$failedCount} item(s) failed.";
        }

        return [
            'success' => $successCount > 0,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    /**
     * Approve multiple pending videos or quizzes in batch.
     */
    public function bulkApprove(array $items): array
    {
        $approvedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($items as $itemData) {
            $id = is_array($itemData) ? ($itemData['id'] ?? null) : $itemData;
            $type = is_array($itemData) ? ($itemData['type'] ?? 'video') : 'video';

            if (!$id) {
                continue;
            }

            try {
                if ($type === 'quiz') {
                    $quiz = Quiz::find($id);
                    if ($quiz) {
                        $quiz->update(['status' => 'approved']);
                        $approvedCount++;
                    }
                } else {
                    $video = Video::find($id);
                    if ($video) {
                        $video->update([
                            'status' => 'approved',
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        $approvedCount++;
                    }
                }
            } catch (Exception $e) {
                $failedCount++;
                $errors[] = "Failed to approve item #{$id}: " . $e->getMessage();
            }
        }

        return [
            'success' => $approvedCount > 0,
            'approved_count' => $approvedCount,
            'failed_count' => $failedCount,
            'message' => "Successfully approved {$approvedCount} item(s).",
            'errors' => $errors,
        ];
    }

    /**
     * Attach a category to multiple selected contents in batch.
     */
    public function bulkCategorize(array $items, int $categoryId): array
    {
        $category = ContentCategory::find($categoryId);
        if (!$category) {
            return [
                'success' => false,
                'message' => 'Selected category not found.',
            ];
        }

        $count = 0;
        foreach ($items as $itemData) {
            $id = is_array($itemData) ? ($itemData['id'] ?? null) : $itemData;
            $type = is_array($itemData) ? ($itemData['type'] ?? 'video') : 'video';

            if (!$id) continue;

            if ($type === 'video' || !$type) {
                $video = Video::find($id);
                if ($video && method_exists($video, 'categories')) {
                    $video->categories()->syncWithoutDetaching([$categoryId]);
                    $count++;
                }
            }
        }

        return [
            'success' => true,
            'count' => $count,
            'message' => "Categorized {$count} item(s) as '{$category->name}'.",
        ];
    }

    /**
     * Delete a single video and associated remote/disk assets.
     */
    private function deleteSingleVideo(Video $video, bool $deleteRelated = true): void
    {
        // Handle Vimeo media deletion if hosted on Vimeo
        if ($video->video_source === 'vimeo' && $video->vimeo_id) {
            try {
                $vimeoService = new VimeoService();
                $vimeoService->deleteVideo($video->vimeo_id);
            } catch (Exception $e) {
                Log::warning("Vimeo deletion warning for video #{$video->id}: " . $e->getMessage());
            }
        }

        if ($deleteRelated) {
            // Delete attached document files
            if ($video->documents) {
                foreach ($video->documents as $document) {
                    $this->deleteSingleDocument($document);
                }
            }

            // Delete attached quizzes
            if ($video->quizzes) {
                foreach ($video->quizzes as $quiz) {
                    $this->deleteSingleQuiz($quiz);
                }
            }
            if ($video->quiz) {
                $this->deleteSingleQuiz($video->quiz);
            }
        }

        // Delete video disk files and database record
        if (method_exists($video, 'deleteFiles')) {
            $video->deleteFiles();
        }
        $video->delete();
    }

    /**
     * Delete a single document record and disk file.
     */
    private function deleteSingleDocument(Document $document): void
    {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
    }

    /**
     * Delete a single quiz record.
     */
    private function deleteSingleQuiz(Quiz $quiz): void
    {
        $quiz->delete();
    }
}
