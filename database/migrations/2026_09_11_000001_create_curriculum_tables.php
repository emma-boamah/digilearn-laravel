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
        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('education_body')->default('GES'); // GES, WAEC, NaCCA, etc.
            $table->string('academic_year')->nullable(); // e.g. 2019, 2023/2024
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('level_group_id')->nullable()->constrained('level_groups')->nullOnDelete();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('extraction_status')->default('pending'); // pending, processing, extracted, failed
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('extraction_error')->nullable();
            $table->longText('raw_extraction_json')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('curriculum_strands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('grade_label')->nullable(); // e.g. "JHS 1", "Primary 4" for multi-grade PDFs
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('curriculum_sub_strands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strand_id')->constrained('curriculum_strands')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content_standard')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('curriculum_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_strand_id')->constrained('curriculum_sub_strands')->cascadeOnDelete();
            $table->string('indicator_code')->nullable(); // e.g. B7.1.1.1.1
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('exemplars')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('curriculum_media', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->integer('page_number')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_media');
        Schema::dropIfExists('curriculum_indicators');
        Schema::dropIfExists('curriculum_sub_strands');
        Schema::dropIfExists('curriculum_strands');
        Schema::dropIfExists('curricula');
    }
};
