<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per user per module, written the first time they touch it.
     *
     * `progress` is how far through the unit they are — the watched fraction of
     * a video, the score on a quiz — kept so a part-finished video is not lost
     * between visits. `is_completed` is the flag the percentages count; what
     * sets it depends on the module type and is decided in ProgressService.
     */
    public function up(): void
    {
        Schema::create('user_module_progress', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->unsignedTinyInteger('progress')->default(0);
            // How it was completed: watched, passed, viewed or manual. Useful
            // when a rule changes and old rows need telling apart.
            $table->string('completed_via')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_module_id'], 'user_module_progress_unique');
            $table->index(['user_id', 'is_completed'], 'user_module_progress_user_completed_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_progress');
    }
};
