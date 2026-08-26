<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A quiz attempt was only ever a row saying "this user opened this quiz".
     * Sitting a quiz needs more: when it started, when it must end, what was
     * scored, and — for written answers — who marked it and when.
     *
     * `status` drives everything the student and the instructor see:
     *
     *   in_progress     the clock is running
     *   submitted       marked automatically; MCQ only
     *   pending_review  waiting on an instructor; theory and mixed
     *   graded          an instructor has marked it
     *   expired         the clock ran out before anything was submitted
     */
    public function up(): void
    {
        Schema::table('quiz_user_attempts', function (Blueprint $table) {
            $table->enum('status', ['in_progress', 'submitted', 'pending_review', 'graded', 'expired'])
                ->default('in_progress')
                ->after('user_id');

            $table->timestamp('started_at')->nullable()->after('status');
            // When the clock runs out. Null for a quiz with no duration set.
            $table->timestamp('expires_at')->nullable()->after('started_at');
            $table->timestamp('submitted_at')->nullable()->after('expires_at');

            // Marks in the same unit as quiz_questions.marks.
            $table->decimal('earned_marks', 8, 2)->nullable()->after('submitted_at');
            $table->decimal('total_marks', 8, 2)->nullable()->after('earned_marks');
            $table->boolean('passed')->nullable()->after('total_marks');

            $table->foreignId('graded_by')->nullable()->after('passed')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
            $table->text('feedback')->nullable()->after('graded_at');

            $table->index(['user_id', 'status']);
            $table->index(['quiz_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_user_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['quiz_id', 'status']);
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn([
                'status', 'started_at', 'expires_at', 'submitted_at',
                'earned_marks', 'total_marks', 'passed', 'graded_at', 'feedback',
            ]);
        });
    }
};
