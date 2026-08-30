<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Fills in the uuids that never got written.
 *
 * The seeders ran under WithoutModelEvents, which muted the `creating` hook the
 * HasUuid trait boots, so every seeded row landed with a null uuid. Routes are
 * keyed on the uuid, so those rows could not be linked to: the students table
 * failed on route('students.show', null) rather than rendering.
 *
 * The seeder no longer mutes events; this repairs the rows already written.
 */
return new class extends Migration
{
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
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'uuid')) {
                continue;
            }

            DB::table($table)->whereNull('uuid')->orderBy('id')->select('id')
                ->chunkById(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('id', $row->id)->update([
                            'uuid' => (string) Str::uuid(),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Nothing to undo: a filled-in uuid is not something to take away.
    }
};
