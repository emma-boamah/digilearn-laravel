<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Models\User;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing content upload functionality...\n\n";

// Create a test admin user if not exists
$admin = User::where('email', 'testadmin@example.com')->first();
if (!$admin) {
    $admin = User::create([
        'name' => 'Test Admin',
        'email' => 'testadmin@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);
    echo "Created test admin user\n";
}

// Authenticate as admin
Auth::login($admin);
echo "Authenticated as admin\n\n";

// Create test subject
$subject = Subject::firstOrCreate(
    ['name' => 'Test Mathematics'],
    ['description' => 'Test subject for content upload']
);
echo "Using subject: {$subject->name}\n";

// Prepare test data for content upload
$quizData = [
    'questions' => [
        [
            'question' => 'What is 2 + 2?',
            'type' => 'multiple_choice',
            'options' => ['3', '4', '5', '6'],
            'correct_answer' => '4',
            'explanation' => 'Basic addition'
        ],
        [
            'question' => 'What is the capital of France?',
            'type' => 'multiple_choice',
            'options' => ['London', 'Berlin', 'Paris', 'Madrid'],
            'correct_answer' => 'Paris',
            'explanation' => 'Geography question'
        ]
    ]
];

$requestData = [
    'title' => 'Test Video Content',
    'subject_id' => $subject->id,
    'description' => 'Test description for video content',
    'grade_level' => 'Primary 1',
    'video_source' => 'local',
    'quiz_data' => json_encode($quizData),
    'difficulty_level' => 'beginner',
    'time_limit_minutes' => 30
];

echo "Request data prepared:\n";
print_r($requestData);
echo "\n";

// Create a mock request
$request = new Request();
$request->merge($requestData);

// Create AdminController instance
$controller = new AdminController(app(\App\Services\NotificationService::class));

echo "Calling storeContentPackage method...\n";

try {
    // Call the method
    $response = $controller->storeContentPackage($request);

    echo "Response received:\n";
    print_r($response->getData());

    // Check if content was created
    echo "\nChecking database...\n";

    $videos = Video::where('title', 'Test Video Content')->get();
    echo "Videos found: " . $videos->count() . "\n";
    if ($videos->count() > 0) {
        foreach ($videos as $video) {
            echo "Video ID: {$video->id}, Title: {$video->title}, Quiz ID: {$video->quiz_id}\n";
        }
    }

    $quizzes = Quiz::where('title', 'like', 'Quiz for: Test Video Content')->get();
    echo "Quizzes found: " . $quizzes->count() . "\n";
    if ($quizzes->count() > 0) {
        foreach ($quizzes as $quiz) {
            echo "Quiz ID: {$quiz->id}, Title: {$quiz->title}, Video ID: {$quiz->video_id}\n";
        }
    }

    $documents = Document::where('title', 'like', '%Test Video Content%')->get();
    echo "Documents found: " . $documents->count() . "\n";

} catch (\Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";