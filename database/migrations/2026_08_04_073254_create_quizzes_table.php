<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A quiz, and the terms it is run on.
     *
     * `marking` decides who marks the written answers: an instructor, or the
     * student themselves — and a self-marked paper is never scored, so it
     * carries no passing score and its questions carry no marks.
     *
     * The publication state lives in the shared `statuses` table rather than in
     * a column here; see the statuses migration and the HasStatus trait.
     */
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            // Which course the quiz is set for. It decides which questions the
            // builder offers, so it is a column rather than something inferred
            // from whichever chapter happens to be attached.
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['theory', 'mcqs', 'mixed'])->default('mixed');
            // Minutes allowed for an attempt; null means untimed.
            $table->unsignedSmallInteger('duration')->nullable();
            $table->decimal('passing_score', 8, 2)->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->enum('marking', ['instructor', 'self'])->default('instructor');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};