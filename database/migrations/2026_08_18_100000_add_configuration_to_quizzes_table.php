<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The quizzes table only carried a title and a type, but the create form
 * collects the settings a quiz is actually run with. Each column is guarded so
 * the migration is safe to re-run against a database that already has some.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (! Schema::hasColumn('quizzes', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            // Minutes allowed for an attempt; null means untimed.
            if (! Schema::hasColumn('quizzes', 'duration')) {
                $table->unsignedSmallInteger('duration')->nullable()->after('type');
            }

            if (! Schema::hasColumn('quizzes', 'passing_score')) {
                $table->unsignedTinyInteger('passing_score')->nullable()->after('duration');
            }

            if (! Schema::hasColumn('quizzes', 'shuffle_questions')) {
                $table->boolean('shuffle_questions')->default(false)->after('passing_score');
            }

            if (! Schema::hasColumn('quizzes', 'status')) {
                $table->enum('status', ['draft', 'published', 'closed'])
                    ->default('draft')
                    ->after('shuffle_questions');
            }
        });

        // A quiz may mix both kinds of question, which the original enum had no
        // room for. Widening an enum is MySQL-specific; sqlite (used by
        // the test suite) has no MODIFY COLUMN and stores enums as plain text anyway.
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE quizzes MODIFY COLUMN type ENUM('theory', 'mcqs', 'mixed') NOT NULL DEFAULT 'mixed'");
        }
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['description', 'duration', 'passing_score', 'shuffle_questions', 'status']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE quizzes MODIFY COLUMN type ENUM('theory', 'mcqs') NOT NULL");
        }
    }
};
