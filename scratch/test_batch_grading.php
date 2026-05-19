<?php

use App\Services\Quiz\QuizAutomatedGradingService;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Config;

if (!class_exists(Quiz::class)) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

// 1. Create a temporary Quiz
$quizData = [
    'questions' => [
        [
            'id' => 1001,
            'type' => 'essay',
            'question' => 'What is photosynthesis and why is chlorophyll important?',
            'preamble' => null,
            'points' => 10,
            'correct_answer' => 'Photosynthesis is the process by which plants use sunlight to synthesize food from carbon dioxide and water. Chlorophyll is the green pigment that absorbs light.',
            'keywords' => ['photosynthesis', 'sunlight', 'carbon', 'water', 'chlorophyll', 'pigment']
        ],
        [
            'id' => 1002,
            'type' => 'essay',
            'question' => 'Describe the states of water.',
            'preamble' => null,
            'points' => 5,
            'sub_questions' => [
                [
                    'id' => 1003,
                    'label' => 'a',
                    'text' => 'Name the three states of water.',
                    'sample_answer' => 'Ice, liquid water, and water vapor are the three states.',
                    'keywords' => ['ice', 'liquid', 'vapor'],
                    'points' => 2
                ],
                [
                    'id' => 1004,
                    'label' => 'b',
                    'text' => 'What causes the change of state?',
                    'sample_answer' => 'Temperature changes (heating and cooling) cause the change of state.',
                    'keywords' => ['temperature', 'heating', 'cooling'],
                    'points' => 3
                ]
            ]
        ]
    ],
    'difficulty_level' => 'medium',
    'time_limit_minutes' => 15,
    'shuffle_questions' => 0
];

$userId = User::first()->id ?? 1;

$quiz = Quiz::create([
    'title' => 'Temporary Test Quiz for Batch Grading',
    'quiz_data' => json_encode($quizData),
    'grade_level' => 'JHS 1',
    'time_limit_minutes' => 15,
    'total_questions' => 2,
    'is_published' => false,
    'uploaded_by' => $userId
]);

// 2. Create the Attempt with answers
$answers = [
    0 => 'Photosynthesis uses sunlight to make food. Chlorophyll is a green pigment.', // Q0
    1 => [
        0 => '', // Q1 Part a (blank)
        1 => 'It changes because of heating and cooling.' // Q1 Part b
    ]
];

$attempt = QuizAttempt::create([
    'user_id' => $userId,
    'quiz_id' => $quiz->id,
    'quiz_title' => $quiz->title,
    'quiz_subject' => 'General Science',
    'quiz_level' => $quiz->grade_level,
    'total_questions' => 2,
    'correct_answers' => 0,
    'incorrect_answers' => 0,
    'score_percentage' => 0,
    'time_taken_seconds' => 120,
    'passed' => false,
    'status' => 'pending',
    'attempt_number' => 1,
    'answers' => $answers,
    'question_details' => $quizData['questions'],
    'started_at' => now()->subMinutes(2),
    'completed_at' => now()
]);

try {
    echo "Temporary Quiz ID: {$quiz->id} and Attempt ID: {$attempt->id} created.\n";

    $service = new QuizAutomatedGradingService();

    // Test 1: Run with AI (using actual key)
    echo "\n=== Test 1: AI Grading (Gemini or OpenAI) ===\n";
    $startTime = microtime(true);
    $resultsAI = $service->suggestMarks($attempt);
    $endTime = microtime(true);
    echo "Driver used: " . $resultsAI['grading_driver'] . "\n";
    echo "Time taken: " . round($endTime - $startTime, 4) . " seconds\n";
    echo "Marks:\n";
    print_r($resultsAI['marks']);
    echo "Feedback:\n";
    print_r($resultsAI['feedback']);

    // Test 2: Run with Forced Local Grading (temporarily clearing API keys)
    echo "\n=== Test 2: Local Keyword Fallback Grading ===\n";
    $originalGeminiKey = Config::get('services.gemini.key');
    $originalOpenAIKey = Config::get('services.openai.key');

    Config::set('services.gemini.key', null);
    Config::set('services.openai.key', null);

    $startTime = microtime(true);
    $resultsLocal = $service->suggestMarks($attempt);
    $endTime = microtime(true);

    // Restore keys
    Config::set('services.gemini.key', $originalGeminiKey);
    Config::set('services.openai.key', $originalOpenAIKey);

    echo "Driver used: " . $resultsLocal['grading_driver'] . "\n";
    echo "Time taken: " . round($endTime - $startTime, 4) . " seconds\n";
    echo "Marks:\n";
    print_r($resultsLocal['marks']);
    echo "Feedback:\n";
    print_r($resultsLocal['feedback']);

} finally {
    echo "\nCleaning up database...\n";
    $attempt->delete();
    $quiz->delete();
    echo "Database cleaned successfully.\n";
}
