<?php

use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\QuizUserAttempt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Chapters, quizzes and quiz attempts each carried a `status` column. They all
 * mean the same kind of thing, and the app already has a table for it, so the
 * values move into `statuses` and the columns go.
 *
 * The values are copied first, so nothing is lost. The default for a row that
 * somehow has none matches what the column defaulted to.
 */
return new class extends Migration
{
    /**
     * The tables this moves, and the model each is reached through — the morph
     * type is the class name, so it has to match what the models write.
     */
    private array $tables = [
        'chapters' => [Chapter::class, 'Draft'],
        'quizzes' => [Quiz::class, 'draft'],
        'quiz_user_attempts' => [QuizUserAttempt::class, 'in_progress'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => [$model, $default]) {
            if (! Schema::hasColumn($table, 'status')) {
                continue;
            }

            $this->copyOut($table, $model, $default);

            // An index on the status column alone has nothing to keep once
            // the column goes. sqlite leaves it behind and then refuses to
            // read the table at all, so it is dropped first.
            foreach ($this->statusOnlyIndexesOn($table) as $index) {
                Schema::table($table, function (Blueprint $blueprint) use ($index) {
                    $blueprint->dropIndex($index);
                });
            }

            // MySQL will not drop a column an index leads with, and the
            // attempts table indexes (user_id, status) and (quiz_id, status).
            // It also will not release either of those while a foreign key
            // still depends on them, and it only lets go once another index
            // leads with the same column — so the replacements go in first.
            foreach ($this->indexesOn($table) as $index => $columns) {
                Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                    $blueprint->index([$columns[0]]);
                });

                Schema::table($table, function (Blueprint $blueprint) use ($index) {
                    $blueprint->dropIndex($index);
                });
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => [$model, $default]) {
            if (Schema::hasColumn($table, 'status')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($default) {
                $blueprint->string('status')->default($default);
            });

            DB::table('statuses')
                ->where('statusable_type', $model)
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->statusable_id)
                            ->update(['status' => $row->status]);
                    }
                });

            DB::table('statuses')->where('statusable_type', $model)->delete();

            foreach ($this->indexesOn($table) as $index => $columns) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $index));
            }

            foreach ($this->statusOnlyIndexesOn($table) as $index) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index(['status'], $index));
            }
        }
    }

    /**
     * Copies one table's statuses into the statuses table, skipping any row
     * that already has one so the migration is safe to re-run.
     */
    private function copyOut(string $table, string $model, string $default): void
    {
        $now = now();

        DB::table($table)
            ->orderBy('id')
            ->select('id', 'status')
            ->chunkById(500, function ($rows) use ($model, $default, $now) {
                $existing = DB::table('statuses')
                    ->where('statusable_type', $model)
                    ->whereIn('statusable_id', collect($rows)->pluck('id'))
                    ->pluck('statusable_id')
                    ->all();

                $insert = [];

                foreach ($rows as $row) {
                    if (in_array($row->id, $existing)) {
                        continue;
                    }

                    $insert[] = [
                        'uuid' => (string) Str::uuid(),
                        'status' => $row->status ?: $default,
                        'statusable_type' => $model,
                        'statusable_id' => $row->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($insert) {
                    DB::table('statuses')->insert($insert);
                }
            });
    }

    /**
     * The composite indexes that lead with, or include, the status column.
     *
     * @return array<string, array<int, string>>
     */
    private function indexesOn(string $table): array
    {
        return match ($table) {
            'quiz_user_attempts' => [
                'quiz_user_attempts_user_id_status_index' => ['user_id', 'status'],
                'quiz_user_attempts_quiz_id_status_index' => ['quiz_id', 'status'],
            ],
            default => [],
        };
    }

    /**
     * Indexes on the status column alone, which have nothing to replace them
     * with — they simply go.
     *
     * @return array<int, string>
     */
    private function statusOnlyIndexesOn(string $table): array
    {
        return match ($table) {
            'chapters' => ['chapters_status_index'],
            default => [],
        };
    }
};
