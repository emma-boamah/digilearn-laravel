<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Models\Document;
use App\Models\Subject;
use App\Services\PptxParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PptxDocumentViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pptx_parser_handles_nonexistent_file_gracefully()
    {
        $result = PptxParser::parse('/nonexistent/path/presentation.pptx');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_document_content_viewer_graceful_fallback()
    {
        // 1. Create dependencies
        $user = User::factory()->create([
            'is_superuser' => true,
            'grade' => 'SHS 1',
        ]);

        \App\Models\UserProgress::create([
            'user_id' => $user->id,
            'level_group' => 'shs',
            'current_level' => 'SHS 1',
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'name' => 'Computer Science',
            'seo_url' => 'computer-science'
        ]);

        $video = Video::factory()->create([
            'title' => 'Introduction to Logic',
            'grade_level' => 'SHS 1',
            'status' => 'approved',
            'subject_id' => $subject->id,
            'uploaded_by' => $user->id,
        ]);

        $document = Document::create([
            'video_id' => $video->id,
            'title' => 'Logic Slides.pptx',
            'file_path' => 'documents/logic_slides.pptx',
            'description' => 'Logic explanation presentation',
            'uploaded_by' => $user->id,
        ]);

        // 2. Mock auth session and levels
        session(['selected_level_group' => 'shs']);

        $response = $this->actingAs($user)
            ->get(route('dashboard.lesson.document.content', [$video->id, 'ppt']));

        // Assert redirect or ok status
        $response->assertStatus(200);
        $response->assertSee('Logic Slides.pptx');
        $response->assertSee('Document Overview');
        $response->assertSee('Logic explanation presentation');
    }
}
