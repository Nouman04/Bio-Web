<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quizzes_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->foreignId('attempt_id')->constrained('quiz_user_attempts')->cascadeOnDelete();
            $table->foreignId('selected_option')->nullable()->constrained('question_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->text('answer_content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};
