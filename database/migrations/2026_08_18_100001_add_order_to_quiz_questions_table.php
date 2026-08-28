<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Questions in a quiz are answered in a set sequence, so the join row carries
 * its position the same way `assessments.order` does for flashcards.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quiz_questions', 'order')) {
            return;
        }

        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('question_bank_id');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
