<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Models\Document;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\AdminMiddleware;
use Mockery;

class AdminVideoDocumentSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Only bypass CSRF and AdminMiddleware — keep SubstituteBindings (route model
        // binding) and StartSession (Auth) active so controllers resolve correctly.
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            AdminMiddleware::class,
        ]);

        // Create an admin user
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->subject = Subject::factory()->create();

        // Mock VideoDurationService so fake video files don't trigger a real probe.
        $mockDurationService = Mockery::mock(\App\Services\VideoDurationService::class);
        $mockDurationService->shouldReceive('getDuration')
            ->withAnyArgs()
            ->andReturn(120.55); // Fake 2-minute video

        $this->app->instance(\App\Services\VideoDurationService::class, $mockDurationService);
    }

    /**
     * Test uploading a video along with a PPTX presentation correctly syncs to the documents table.
     */
    public function test_admin_can_upload_video_with_pptx_slides_and_syncs_as_ppt()
    {
        Storage::fake('public');

        $videoFile = UploadedFile::fake()->create('sample_video.mp4', 1000, 'video/mp4');
        $thumbnailFile = UploadedFile::fake()->image('thumbnail.jpg');
        $documentFile = UploadedFile::fake()->create('lecture_slides.pptx', 200, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.content.videos.store'), [
                'title' => 'Biology 101 Lecture',
                'video_file' => $videoFile,
                'thumbnail_file' => $thumbnailFile,
                'document_file' => $documentFile,
                'grade_level' => 'JHS 1',
                'subject_id' => $this->subject->id,
                'description' => 'Introductory biology session',
                'is_featured' => 1,
            ]);

        $response->assertRedirect(route('admin.content.videos.index'));
        $response->assertSessionHas('success');

        // Check video is created
        $this->assertDatabaseHas('videos', [
            'title' => 'Biology 101 Lecture',
            'grade_level' => 'JHS 1',
            'subject_id' => $this->subject->id,
        ]);

        $video = Video::where('title', 'Biology 101 Lecture')->first();
        $this->assertNotNull($video->document_path);

        // Verify PPT file is stored
        Storage::disk('public')->assertExists($video->document_path);

        // Check synced Document model record
        $this->assertDatabaseHas('documents', [
            'video_id' => $video->id,
            'title' => 'Biology 101 Lecture Slides',
            'file_path' => $video->document_path,
            'file_type' => 'ppt',
            'grade_level' => 'JHS 1',
            'uploaded_by' => $this->adminUser->id,
        ]);
    }

    /**
     * Test uploading a video along with a PDF document correctly syncs to the documents table as pdf.
     */
    public function test_admin_can_upload_video_with_pdf_slides_and_syncs_as_pdf()
    {
        Storage::fake('public');

        $videoFile = UploadedFile::fake()->create('sample_video.mp4', 1000, 'video/mp4');
        $documentFile = UploadedFile::fake()->create('reading_notes.pdf', 150, 'application/pdf');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.content.videos.store'), [
                'title' => 'Calculus Basics',
                'video_file' => $videoFile,
                'document_file' => $documentFile,
                'grade_level' => 'SHS 3',
                'subject_id' => $this->subject->id,
                'description' => 'Derivatives and integration basics',
            ]);

        $response->assertRedirect(route('admin.content.videos.index'));

        $video = Video::where('title', 'Calculus Basics')->first();
        $this->assertNotNull($video->document_path);

        // Check synced Document model record for type 'pdf'
        $this->assertDatabaseHas('documents', [
            'video_id' => $video->id,
            'title' => 'Calculus Basics Slides',
            'file_path' => $video->document_path,
            'file_type' => 'pdf',
            'grade_level' => 'SHS 3',
        ]);
    }

    /**
     * Test editVideo AJAX request returns correct JSON with dynamic 'seo_url' appended.
     */
    public function test_admin_can_edit_video_populates_json_with_seo_url()
    {
        $video = Video::factory()->create([
            'title' => 'Introduction to Chemistry',
            'grade_level' => 'SHS 1',
            'subject_id' => $this->subject->id,
            'uploaded_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('admin.content.videos.edit', $video->seo_url));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id', 'title', 'seo_url', 'grade_level', 'description', 'document_path', 'thumbnail_path'
        ]);

        $responseData = $response->json();
        $this->assertEquals('Introduction to Chemistry', $responseData['title']);

        $parsed = \App\Services\UrlObfuscator::parseSeoUrl($responseData['seo_url']);
        $this->assertNotNull($parsed);
        $this->assertEquals($video->id, $parsed['id']);
    }

    /**
     * Test admin can update video metadata (title, description, grade) without having to re-upload the video file itself.
     */
    public function test_admin_can_update_video_metadata_without_reuploading_video_file()
    {
        Storage::fake('public');

        // Create an existing video with dummy paths
        $video = Video::factory()->create([
            'title' => 'Original Video Title',
            'video_path' => 'videos/original_file.mp4',
            'grade_level' => 'Primary 4',
            'subject_id' => $this->subject->id,
            'uploaded_by' => $this->adminUser->id,
            'document_path' => 'documents/original_slides.pdf',
        ]);

        // Create a dummy document record synced with it
        Document::create([
            'video_id' => $video->id,
            'title' => 'Original Video Title Slides',
            'file_path' => 'documents/original_slides.pdf',
            'grade_level' => 'Primary 4',
            'description' => 'Original Description',
            'uploaded_by' => $this->adminUser->id,
            'file_type' => 'pdf',
            'file_size_bytes' => 1024,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.content.videos.update', $video->seo_url), [
                'title' => 'Updated Video Title',
                'grade_level' => 'Primary 5',
                'description' => 'New video description',
                // video_file is omitted (optional)
            ]);

        $response->assertRedirect(route('admin.content.videos.index'));

        // Assert video details updated
        $video->refresh();
        $this->assertEquals('Updated Video Title', $video->title);
        $this->assertEquals('Primary 5', $video->grade_level);
        $this->assertEquals('videos/original_file.mp4', $video->video_path); // Kept unchanged!

        // Assert synced document details updated in tandem
        $this->assertDatabaseHas('documents', [
            'video_id' => $video->id,
            'title' => 'Updated Video Title Slides',
            'grade_level' => 'Primary 5',
        ]);
    }

    /**
     * Test admin can upload new slides to replace existing slides, deleting the old slide file from disk.
     */
    public function test_admin_can_update_video_and_replace_document()
    {
        Storage::fake('public');

        // Store a fake file first
        $oldPath = UploadedFile::fake()->create('old_slides.pdf', 100)->store('documents', 'public');

        $video = Video::factory()->create([
            'title' => 'Physics Chapter 1',
            'video_path' => 'videos/sample.mp4',
            'grade_level' => 'SHS 2',
            'subject_id' => $this->subject->id,
            'uploaded_by' => $this->adminUser->id,
            'document_path' => $oldPath,
        ]);

        Document::create([
            'video_id' => $video->id,
            'title' => 'Physics Chapter 1 Slides',
            'file_path' => $oldPath,
            'grade_level' => 'SHS 2',
            'uploaded_by' => $this->adminUser->id,
            'file_type' => 'pdf',
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // Upload new PPTX slides to replace PDF
        $newDocumentFile = UploadedFile::fake()->create('new_slides.pptx', 300, 'application/vnd.ms-powerpoint');

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.content.videos.update', $video->seo_url), [
                'title' => 'Physics Chapter 1',
                'grade_level' => 'SHS 2',
                'document_file' => $newDocumentFile,
            ]);

        $response->assertRedirect(route('admin.content.videos.index'));

        // Refresh model
        $video->refresh();

        // Verify old slides file is deleted from disk
        Storage::disk('public')->assertMissing($oldPath);

        // Verify new slides file exists
        Storage::disk('public')->assertExists($video->document_path);
        $this->assertNotEquals($oldPath, $video->document_path);

        // Verify document sync is updated
        $this->assertDatabaseHas('documents', [
            'video_id' => $video->id,
            'file_path' => $video->document_path,
            'file_type' => 'ppt', // Since new was pptx
        ]);

        // Old document file path should not exist in the database
        $this->assertDatabaseMissing('documents', [
            'file_path' => $oldPath,
        ]);
    }

    /**
     * Test admin can delete the attached document/slides from a video entirely.
     */
    public function test_admin_can_delete_attached_document_on_video()
    {
        Storage::fake('public');

        $filePath = UploadedFile::fake()->create('removable_slides.pdf', 100)->store('documents', 'public');

        $video = Video::factory()->create([
            'title' => 'Math Equations',
            'video_path' => 'videos/sample.mp4',
            'grade_level' => 'Primary 6',
            'subject_id' => $this->subject->id,
            'uploaded_by' => $this->adminUser->id,
            'document_path' => $filePath,
        ]);

        Document::create([
            'video_id' => $video->id,
            'title' => 'Math Equations Slides',
            'file_path' => $filePath,
            'grade_level' => 'Primary 6',
            'uploaded_by' => $this->adminUser->id,
            'file_type' => 'pdf',
        ]);

        Storage::disk('public')->assertExists($filePath);

        // Update the video with delete_document checked
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.content.videos.update', $video->seo_url), [
                'title' => 'Math Equations',
                'grade_level' => 'Primary 6',
                'delete_document' => '1', // Equivalent to checked checkbox
            ]);

        $response->assertRedirect(route('admin.content.videos.index'));

        $video->refresh();

        // Verify path is cleared and file is deleted from disk
        $this->assertNull($video->document_path);
        Storage::disk('public')->assertMissing($filePath);

        // Verify corresponding Document is removed from database
        $this->assertDatabaseMissing('documents', [
            'video_id' => $video->id,
        ]);
    }
}
