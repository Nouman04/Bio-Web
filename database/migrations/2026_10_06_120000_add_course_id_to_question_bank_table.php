<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A question belongs to a course even when it has no chapter yet, as
     * past-paper imports do. Existing questions take it from their chapter.
     */
    public function up(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('uuid')->constrained('courses')->nullOnDelete();
        });

        DB::table('question_bank')
            ->join('chapters', 'chapters.id', '=', 'question_bank.chapter_id')
            ->update(['question_bank.course_id' => DB::raw('chapters.course_id')]);
    }

    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
