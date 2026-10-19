<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Level;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('levelables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->morphs('levelable');
            $table->timestamps();

            $table->unique(['level_id', 'levelable_id', 'levelable_type'], 'levelable_unique');
        });

        // Backfill existing data
        $this->backfillLevelables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levelables');
    }

    /**
     * Backfill existing records into levelables table
     */
    protected function backfillLevelables(): void
    {
        $levels = Level::all();
        if ($levels->isEmpty()) {
            return;
        }

        $resolveLevel = function (?string $gradeLevel) use ($levels): ?Level {
            if (!$gradeLevel) {
                return null;
            }
            $clean = trim($gradeLevel);
            // Direct title or slug match
            $matched = $levels->first(function ($l) use ($clean) {
                return strcasecmp($l->title, $clean) === 0 || strcasecmp($l->slug, $clean) === 0;
            });
            if ($matched) {
                return $matched;
            }

            // Handle patterns like "Basic 7 (JHS 1)" or "jhs-1"
            if (preg_match('/JHS\s*1/i', $clean) || strtolower($clean) === 'jhs-1') {
                return $levels->firstWhere('slug', 'jhs-1');
            }
            if (preg_match('/JHS\s*2/i', $clean) || strtolower($clean) === 'jhs-2') {
                return $levels->firstWhere('slug', 'jhs-2');
            }
            if (preg_match('/JHS\s*3/i', $clean) || strtolower($clean) === 'jhs-3') {
                return $levels->firstWhere('slug', 'jhs-3');
            }
            if (preg_match('/SHS\s*1/i', $clean) || strtolower($clean) === 'shs-1') {
                return $levels->firstWhere('slug', 'shs-1');
            }
            if (preg_match('/SHS\s*2/i', $clean) || strtolower($clean) === 'shs-2') {
                return $levels->firstWhere('slug', 'shs-2');
            }
            if (preg_match('/SHS\s*3/i', $clean) || strtolower($clean) === 'shs-3') {
                return $levels->firstWhere('slug', 'shs-3');
            }

            return null;
        };

        // Backfill Videos
        foreach (Video::whereNotNull('grade_level')->where('grade_level', '!=', '')->cursor() as $video) {
            $level = $resolveLevel($video->grade_level);
            if ($level) {
                DB::table('levelables')->updateOrInsert(
                    [
                        'level_id' => $level->id,
                        'levelable_id' => $video->id,
                        'levelable_type' => Video::class,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Backfill Documents
        foreach (Document::whereNotNull('grade_level')->where('grade_level', '!=', '')->cursor() as $doc) {
            $level = $resolveLevel($doc->grade_level);
            if ($level) {
                DB::table('levelables')->updateOrInsert(
                    [
                        'level_id' => $level->id,
                        'levelable_id' => $doc->id,
                        'levelable_type' => Document::class,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Backfill Quizzes
        foreach (Quiz::whereNotNull('grade_level')->where('grade_level', '!=', '')->cursor() as $quiz) {
            $level = $resolveLevel($quiz->grade_level);
            if ($level) {
                DB::table('levelables')->updateOrInsert(
                    [
                        'level_id' => $level->id,
                        'levelable_id' => $quiz->id,
                        'levelable_type' => Quiz::class,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
};
