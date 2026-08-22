<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The completable units of a chapter — one row per video, quiz, note,
     * diagram, guide or summary. Progress is tracked against these rather than
     * against the content tables directly, so a chapter's denominator is one
     * query instead of six.
     *
     * `moduleable` points at the record the row stands for. The content lives
     * in its own table; this is only the registry that says it counts.
     *
     * `weight` is how much the unit is worth when a percentage is worked out.
     * Leaving every weight at 1 gives plain "n of m done"; raising it makes a
     * unit count for more — a final quiz at 3 is worth three ordinary notes.
     */
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->enum('type', ['video', 'quiz', 'note', 'diagram', 'guide', 'summary']);
            $table->morphs('moduleable');
            $table->unsignedInteger('weight')->default(1);
            $table->unsignedInteger('order')->default(0);
            // Content that is removed stops counting, without erasing the
            // progress rows of everyone who already completed it.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['moduleable_type', 'moduleable_id'], 'course_modules_moduleable_unique');
            $table->index(['chapter_id', 'is_active'], 'course_modules_chapter_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
