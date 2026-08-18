<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The passing score is a mark total now, not a percentage, so it has to hold
 * the same shape of value as quiz_questions.marks rather than 0-100.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->decimal('passing_score', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedTinyInteger('passing_score')->nullable()->change();
        });
    }
};
