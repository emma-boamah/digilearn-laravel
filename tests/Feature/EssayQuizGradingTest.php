<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\UrlObfuscator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EssayQuizGradingTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_essay_filters_only_essay_questions_and_aligns_indices()
    {
        $user = User::factory()->create([
            'grade' => 'JHS 1',
            'is_superuser' => true,
        ]);

        $quizData = [
            'questions' => [
                [
                    'id' => 101,
                    'type' => 'mcq',
                    'question' => 'MCQ Question',
                    'correct_answer' => 0,
                    'points' => 1
                ],
                [
                    'id' => 102,
                    'type' => 'essay',
                    'question' => 'Essay Question 1',
                    'correct_answer' => 'Sample essay answer 1',
                    'keywords' => ['essay', 'one'],
                    'points' => 5
                ],
                [
                    'id' => 103,
                    'type' => 'essay',
                    'question' => 'Essay Question 2',
                    'correct_answer' => 'Sample essay answer 2',
                    'keywords' => ['essay', 'two'],
                    'points' => 5
                ]
            ],
            'difficulty_level' => 'medium',
            'time_limit_minutes' => 15,
            'shuffle_questions' => 0
        ];

        $quiz = Quiz::create([
            'title' => 'Test Mix Quiz',
            'quiz_data' => json_encode($quizData),
            'grade_level' => 'JHS 1',
            'time_limit_minutes' => 15,
            'total_questions' => 3,
            'is_published' => true,
            'uploaded_by' => $user->id
        ]);

        // Mock submitted answers for the two essay questions
        $submittedAnswers = [
            0 => 'This is my response for essay one.',
            1 => 'This is my response for essay two.'
        ];

        $this->withoutMiddleware();
        $this->withoutExceptionHandling();

        $response = $this->actingAs($user)
            ->post(route('quiz.essay.submit', UrlObfuscator::encode($quiz->id)), [
                'essay' => 'This is the combined essay body.',
                'answers' => json_encode($submittedAnswers),
                'time_spent' => 120
            ]);

        $response->assertRedirect();

        // Assert that the attempt was recorded
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->first();

        $this->assertNotNull($attempt);
        // Only 2 essay questions should be in question_details
        $this->assertCount(2, $attempt->question_details);
        $this->assertEquals('essay', $attempt->question_details[0]['type']);
        $this->assertEquals('essay', $attempt->question_details[1]['type']);
        $this->assertEquals('Essay Question 1', $attempt->question_details[0]['question']);
        $this->assertEquals('Essay Question 2', $attempt->question_details[1]['question']);

        // Assert the local/automated grade details are mapped to index keys 0 and 1
        $this->assertArrayHasKey('0', $attempt->answers);
        $this->assertArrayHasKey('1', $attempt->answers);
        $this->assertEquals('This is my response for essay one.', $attempt->answers[0]);
        $this->assertEquals('This is my response for essay two.', $attempt->answers[1]);
    }
}
