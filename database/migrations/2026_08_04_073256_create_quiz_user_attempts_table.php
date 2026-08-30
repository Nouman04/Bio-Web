<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One student sitting one quiz: when it started, when it must end, what was
     * scored, and — for written answers — who marked it and when.
     *
     * The attempt's state lives in the shared `statuses` table rather than in a
     * column here; see the statuses migration and the HasStatus trait. The
     * states it moves through are:
     *
     *   in_progress     the clock is running
     *   submitted       marked automatically; MCQ only
     *   pending_review  waiting on an instructor
     *   self_marked     handed back for the student to judge
     *   graded          an instructor has marked it
     *   expired         the clock ran out before anything was submitted
     */
    public function up(): void
    {
        Schema::create('quiz_user_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamp('started_at')->nullable();
            // When the clock runs out. Null for a quiz with no duration set.
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Marks in the same unit as quiz_questions.marks. Both stay null on
            // a paper nobody scores.
            $table->decimal('earned_marks', 8, 2)->nullable();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->boolean('passed')->nullable();

            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->text('feedback')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_user_attempts');
    }
};