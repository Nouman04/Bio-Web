<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_chapter_id')->constrained('quizzes_chapters')->cascadeOnDelete();
            $table->foreignId('question_bank_id')->constrained('question_bank')->cascadeOnDelete();
            $table->decimal('marks', 8, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
