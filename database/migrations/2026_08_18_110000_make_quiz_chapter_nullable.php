<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quiz questions hang off a quizzes_chapters row, so a quiz built from the
 * sidenav — where questions may belong to no chapter at all — needs a row that
 * carries no chapter either.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes_chapters', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes_chapters', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable(false)->change();
        });
    }
};
