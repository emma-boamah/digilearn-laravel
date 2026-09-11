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
        Schema::create('textbooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable();
            $table->string('edition')->nullable();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('level_group_id')->nullable()->constrained('level_groups')->nullOnDelete();
            $table->foreignId('curriculum_id')->nullable()->constrained('curricula')->nullOnDelete();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->integer('total_pages')->nullable();
            $table->string('toc_extraction_status')->default('pending'); // pending, processing, extracted, failed
            $table->string('content_extraction_status')->default('pending'); // pending, processing, extracted, failed
            $table->boolean('is_toc_approved')->default(false);
            $table->boolean('is_content_approved')->default(false);
            $table->longText('raw_toc_json')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('textbook_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('textbook_id')->constrained('textbooks')->cascadeOnDelete();
            $table->string('title');
            $table->integer('page_start')->nullable();
            $table->integer('page_end')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('curriculum_strand_id')->nullable()->constrained('curriculum_strands')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('textbook_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('textbook_chapters')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content_html')->nullable();
            $table->longText('content_markdown')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('page_start')->nullable();
            $table->integer('page_end')->nullable();
            $table->foreignId('curriculum_indicator_id')->nullable()->constrained('curriculum_indicators')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('textbook_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('textbook_sections')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->integer('page_number')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('textbook_media');
        Schema::dropIfExists('textbook_sections');
        Schema::dropIfExists('textbook_chapters');
        Schema::dropIfExists('textbooks');
    }
};
