<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cached percentages, so a dashboard listing twenty courses does not run
     * twenty aggregates. Nothing here is authoritative — every row can be
     * rebuilt from user_module_progress, and RecalculateProgressJob does.
     *
     * A row with `chapter_id` set holds that chapter's percentage; a row with
     * it null holds the whole course's. Keeping both in one table means one
     * write per completion covers the chapter and the course above it.
     */
    public function up(): void
    {
        Schema::create('user_course_progress', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->cascadeOnDelete();
            // MySQL counts NULLs as distinct in a unique index, so chapter_id
            // alone would not stop duplicate course-level rows. This mirrors it
            // with 0 standing in for "the whole course", and carries the key.
            $table->unsignedBigInteger('chapter_key')->default(0);
            // The percentage, and the weights it was worked out from.
            $table->decimal('progress', 5, 2)->default(0);
            $table->unsignedInteger('completed_weight')->default(0);
            $table->unsignedInteger('total_weight')->default(0);
            $table->timestamp('recalculated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id', 'chapter_key'], 'user_course_progress_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_course_progress');
    }
};
