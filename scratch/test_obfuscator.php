<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\UrlObfuscator;
use Illuminate\Support\Facades\Crypt;

// Manually bootstrap the application to use Crypt
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$id = 22;
$encoded = UrlObfuscator::encode($id);
echo "Encoded ID: " . $encoded . "\n";
echo "Decoded ID: " . UrlObfuscator::decode($encoded) . "\n";

// Test a real value from logs if possible
$fromLog = "ZXlKcGRpSTZJalo2YUZoblRXNXZXR012U1VSelozbzFMMUl6YUhjOVBTSXNJblpoYkhWbElqb2lMMnhSVjJJNE5HSlFZVFpWU3pWaGFVVlBlRzFZWnowOUlpd2liV0ZqSWpvaU1XWXpPR1kxTWpobE5qVXpNekkyWkRaa04yRTFaV001TTJObU1tUmlNVGd5TjJJeFpESmtZbU16WXpjM056QTVNalF6TWpKbFkyWTNOalZrTVRsa05DSXNJblJoWnlJNklpSjk";
echo "Decoded from log: " . UrlObfuscator::decode($fromLog) . "\n";
