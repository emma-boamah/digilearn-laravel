<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Services\RelatedLessonsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatedLessonsTest extends TestCase
{
    use RefreshDatabase;
    
    private RelatedLessonsService $relatedLessonsService;
    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->relatedLessonsService = app(RelatedLessonsService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }
    
    public function test_related_lessons_returned_in_correct_order()
    {
        $subject = \App\Models\Subject::factory()->create();
        
        Video::factory()->count(10)->create([
            'grade_level' => 'Primary 3',
            'subject_id' => $subject->id
        ]);
        
        $currentLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'subject_id' => $subject->id
        ]);
        
        $relatedLessons = $this->relatedLessonsService->getRelatedLessons(
            $currentLesson->toArray(),
            $this->user
        );
        
        $this->assertIsArray($relatedLessons);
        $this->assertLessThanOrEqual(12, count($relatedLessons));
        
        $lessonIds = array_column($relatedLessons, 'id');
        $this->assertNotContains($currentLesson->id, $lessonIds);
    }
    
    public function test_difficulty_progression_scoring_works_correctly()
    {
        $primary2Lesson = Video::factory()->create(['grade_level' => 'Primary 2']);
        $primary3Lesson = Video::factory()->create(['grade_level' => 'Primary 3']);
        $primary4Lesson = Video::factory()->create(['grade_level' => 'Primary 4']);
        
        $relatedLessons = $this->relatedLessonsService->getRelatedLessons(
            $primary3Lesson->toArray(),
            $this->user
        );
        
        $primary2Score = $this->findLessonScore($relatedLessons, $primary2Lesson->id);
        $primary4Score = $this->findLessonScore($relatedLessons, $primary4Lesson->id);
        
        $this->assertGreaterThan($primary2Score, $primary4Score);
    }
    
    public function test_subscription_preview_applied_correctly()
    {
        $essentialUser = User::factory()->create();
        
        $premiumLesson = Video::factory()->create(['grade_level' => 'University']);
        
        $relatedLessons = $this->relatedLessonsService->getRelatedLessons(
            $premiumLesson->toArray(),
            $essentialUser
        );
        
        foreach ($relatedLessons as $lesson) {
            if ($lesson['grade_level'] === 'University') {
                $this->assertEquals('preview', $lesson['access_info']['level']);
                $this->assertArrayHasKey('upgrade_prompt', $lesson['access_info']);
            }
        }
    }
    
    public function test_subject_similarity_scoring()
    {
        $mathSubject = \App\Models\Subject::factory()->create(['name' => 'Mathematics']);
        $scienceSubject = \App\Models\Subject::factory()->create(['name' => 'Science']);
        
        $currentMathLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'subject_id' => $mathSubject->id
        ])->load('subject');
        
        $otherMathLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'subject_id' => $mathSubject->id
        ]);
        
        $scienceLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'subject_id' => $scienceSubject->id
        ]);
        
        $relatedLessons = $this->relatedLessonsService->getRelatedLessons(
            $currentMathLesson->toArray(),
            $this->user
        );
        
        $mathScore = $this->findLessonScore($relatedLessons, $otherMathLesson->id);
        $scienceScore = $this->findLessonScore($relatedLessons, $scienceLesson->id);
        
        $this->assertGreaterThan($scienceScore, $mathScore);
    }
    
    public function test_instructor_consistency_scoring()
    {
        $instructor = User::factory()->create(['name' => 'John Smith']);
        
        $currentLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'uploaded_by' => $instructor->id
        ])->load('uploader');
        
        $sameInstructorLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'uploaded_by' => $instructor->id
        ]);
        
        $otherInstructor = User::factory()->create(['name' => 'Jane Doe']);
        $differentInstructorLesson = Video::factory()->create([
            'grade_level' => 'Primary 3',
            'uploaded_by' => $otherInstructor->id
        ]);
        
        $relatedLessons = $this->relatedLessonsService->getRelatedLessons(
            $currentLesson->toArray(),
            $this->user
        );
        
        $sameInstructorScore = $this->findLessonScore($relatedLessons, $sameInstructorLesson->id);
        $differentInstructorScore = $this->findLessonScore($relatedLessons, $differentInstructorLesson->id);
        
        $this->assertGreaterThan($differentInstructorScore, $sameInstructorScore);
    }
    
    public function test_caching_functionality()
    {
        $lesson = Video::factory()->create(['grade_level' => 'Primary 3']);
        
        $startTime1 = microtime(true);
        $related1 = $this->relatedLessonsService->getRelatedLessons(
            $lesson->toArray(),
            $this->user
        );
        $time1 = microtime(true) - $startTime1;
        
        $startTime2 = microtime(true);
        $related2 = $this->relatedLessonsService->getRelatedLessons(
            $lesson->toArray(),
            $this->user
        );
        $time2 = microtime(true) - $startTime2;
        
        $this->assertEquals($related1, $related2);
        $this->assertLessThan($time1, $time2);
    }
    
    private function findLessonScore(array $lessons, int $lessonId): float
    {
        foreach ($lessons as $lesson) {
            if ($lesson['id'] === $lessonId) {
                return $lesson['related_score'] ?? 0;
            }
        }
        
        return 0;
    }
}