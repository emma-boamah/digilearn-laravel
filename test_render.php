<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lesson = \App\Models\Video::first() ?? ['id' => '123', 'title' => 'Test', 'subject' => 'Test', 'level' => 'test', 'level_group' => 'test', 'total_duration' => 300];
$course = null;
$rendered = view('dashboard.lesson-view', ['lesson' => $lesson, 'course' => $course, 'selectedLevelGroup' => 'test'])->render();

file_put_contents('rendered.html', $rendered);
echo "Rendered successfully to rendered.html. Total lines: " . count(explode("\n", $rendered)) . "\n";
