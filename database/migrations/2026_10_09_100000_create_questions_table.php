<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The words of every question_bank row - a question or a past-paper part.
     *
     * Kept apart so a row with no text of its own (a question whose first line
     * is "3 (a) ...", all of which belongs to part (a)) stores nothing: one row
     * here per question_bank row that has text.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('question_bank_id')->unique()->constrained('question_bank')->cascadeOnDelete();
            $table->longText('question');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
