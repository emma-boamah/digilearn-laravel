<?php

namespace Tests\Feature;

use App\Models\Video;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function agent_generated_videos_are_treated_as_approved_and_accessible()
    {
        // 1. Create an AI-generated video that is NOT yet "approved"
        $video = Video::create([
            'title' => 'AI Generated Lesson',
            'status' => 'pending',
            'is_agent_generated' => true,
            'video_source' => 'youtube',
            'external_video_id' => 'dQw4w9WgXcQ',
            'external_video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'
        ]);

        // 2. Verify model logic: isApproved() should return true despite 'pending' status
        $this->assertTrue($video->isApproved(), 'AI videos should be considered approved regardless of status.');

        // 3. Verify Query Scope: The video should be found in the approved() scope
        $exists = Video::approved()->where('id', $video->id)->exists();
        $this->assertTrue($exists, 'The approved scope must include agent-generated content.');

        // 4. Verify URL Logic: It should return the external YouTube URL
        $this->assertEquals(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            $video->getVideoUrl(),
            'getVideoUrl must return the YouTube URL for agent-generated lessons.'
        );

        // 5. Verify Route Access: Simulating a user clicking the video
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard.lesson.view', $video->seo_url));

        // If the fix is working, it should NOT redirect to 'digilearn' (302 fallback)
        $response->assertStatus(200);
    }
}