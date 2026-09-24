<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('curricula', function (Blueprint $table) {
            $table->string('extraction_method')->default('ai')->after('extraction_status'); // 'ai', 'local', 'hybrid'
            $table->string('extraction_quality')->default('medium')->after('extraction_method'); // 'high', 'medium', 'low'
            $table->boolean('requires_review')->default(true)->after('extraction_quality');
            $table->boolean('ocr_required')->default(false)->after('requires_review');
            $table->integer('source_page_count')->nullable()->after('ocr_required');
            $table->string('local_parser_version')->nullable()->after('source_page_count');
        });

        Schema::table('textbooks', function (Blueprint $table) {
            $table->string('extraction_method')->default('ai')->after('content_extraction_status'); // 'ai', 'local', 'hybrid'
            $table->string('extraction_quality')->default('medium')->after('extraction_method'); // 'high', 'medium', 'low'
            $table->boolean('requires_review')->default(true)->after('extraction_quality');
            $table->boolean('ocr_required')->default(false)->after('requires_review');
            $table->string('local_parser_version')->nullable()->after('ocr_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curricula', function (Blueprint $table) {
            $table->dropColumn([
                'extraction_method',
                'extraction_quality',
                'requires_review',
                'ocr_required',
                'source_page_count',
                'local_parser_version'
            ]);
        });

        Schema::table('textbooks', function (Blueprint $table) {
            $table->dropColumn([
                'extraction_method',
                'extraction_quality',
                'requires_review',
                'ocr_required',
                'local_parser_version'
            ]);
        });
    }
};
