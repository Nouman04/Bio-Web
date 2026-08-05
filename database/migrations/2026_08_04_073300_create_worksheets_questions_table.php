<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Worksheet questions represent both question paper (all questions) and mark scheme (questions with answers).
     * - Clicking "Question Paper": list all questions for the worksheet chapter
     * - Clicking "Mark Scheme": list all questions with their answers for the worksheet chapter
     */
    public function up(): void
    {
        Schema::create('worksheets_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheets_chapters_id')->constrained('worksheets_chapters')->cascadeOnDelete();
            $table->foreignId('topics_id')->constrained('topics')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worksheets_questions');
    }
};
