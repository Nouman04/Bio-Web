<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A question and its answer are written in a rich text editor now, so both hold
 * markup as well as words — a question with a table, a formula or a list runs
 * well past what `text` holds once the tags are counted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->longText('question')->change();
        });

        Schema::table('question_answers', function (Blueprint $table) {
            $table->longText('expected_answer')->nullable()->change();
            $table->longText('description')->change();
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->longText('title')->change();
        });
    }

    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->text('question')->change();
        });

        Schema::table('question_answers', function (Blueprint $table) {
            $table->text('expected_answer')->nullable()->change();
            $table->text('description')->change();
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->text('title')->change();
        });
    }
};
