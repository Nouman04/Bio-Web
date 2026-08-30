<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who marks a quiz's written answers.
 *
 * `instructor` is what the app has always done: a paper with written answers
 * goes to whoever owns the course, and the student waits. `self` hands the
 * paper straight back with the model answers beside it, for the student to
 * judge their own — no instructor is notified, and nothing sits in a marking
 * queue. The answers are stored either way.
 *
 * Only theory and mixed quizzes have anything to mark, so an MCQ quiz ignores
 * this entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('marking', ['instructor', 'self'])
                ->default('instructor')
                ->after('shuffle_questions');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('marking');
        });
    }
};
