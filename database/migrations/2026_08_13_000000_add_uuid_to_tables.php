<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tables that get a unique, auto-generated `uuid` column alongside
     * their existing auto-increment primary key. The primary/foreign keys
     * are left untouched — `uuid` is purely an additional external-facing
     * identifier, populated by the HasUuid model trait going forward.
     */
    private array $tables = [
        'users',
        'categories',
        'courses',
        'chapters',
        'topics',
        'notes',
        'question_categories',
        'question_bank',
        'question_options',
        'question_answers',
        'flashcards',
        'quizzes',
        'quizzes_chapters',
        'quiz_questions',
        'quiz_user_attempts',
        'quiz_attempt_answers',
        'worksheets',
        'worksheets_chapters',
        'worksheets_questions',
        'video_lessons',
        'guides',
        'diagrams',
        'statuses',
        'saved_contents',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });

            // Backfill any existing rows so every record has a uuid immediately.
            DB::table($tableName)->whereNull('uuid')->orderBy('id')->select('id')
                ->chunkById(500, function ($rows) use ($tableName) {
                    foreach ($rows as $row) {
                        DB::table($tableName)->where('id', $row->id)->update([
                            'uuid' => (string) Str::uuid(),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
