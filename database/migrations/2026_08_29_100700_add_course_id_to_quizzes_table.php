<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The course a quiz belongs to.
 *
 * It was only ever implied — through the chapters pivot, and only once a
 * chapter had been attached — so a quiz built from the sidenav belonged to
 * nothing until its questions were picked. The builder asks for the course up
 * front now, because it is what the question search is narrowed by.
 *
 * Existing quizzes take the course of the first chapter they are attached to,
 * which is what they were already implicitly scoped to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('id')
                ->constrained('courses')->nullOnDelete();
        });

        DB::table('quizzes')
            ->orderBy('quizzes.id')
            ->select('quizzes.id')
            ->chunkById(200, function ($quizzes) {
                foreach ($quizzes as $quiz) {
                    $courseId = DB::table('quizzes_chapters')
                        ->join('chapters', 'chapters.id', '=', 'quizzes_chapters.chapter_id')
                        ->where('quizzes_chapters.quizz_id', $quiz->id)
                        ->orderBy('quizzes_chapters.id')
                        ->value('chapters.course_id');

                    if ($courseId) {
                        DB::table('quizzes')->where('id', $quiz->id)->update(['course_id' => $courseId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
