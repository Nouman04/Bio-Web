<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * question_bank becomes the structure; its words move to `questions`.
     *
     * A past-paper question and each of its parts are rows here, nested by
     * parent_id (Q3 -> 3(b) -> 3(b)(ii)), each with its printed label
     * (part_ref) and marks. Chapter and topic sit on each row as before.
     *
     * The text column goes: what it held moves to `questions` first.
     */
    public function up(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('chapter_id')->constrained('question_bank')->cascadeOnDelete();
            $table->string('part_ref', 40)->nullable()->after('parent_id');
            $table->decimal('marks', 8, 2)->nullable()->after('part_ref');
        });

        DB::table('question_bank')
            ->whereNotNull('question')->where('question', '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                DB::table('questions')->insert($rows->map(fn ($row) => [
                    'uuid' => (string) Str::uuid(),
                    'question_bank_id' => $row->id,
                    'question' => $row->question,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            });

        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropColumn('question');
        });
    }

    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->longText('question')->nullable()->after('question_categories_id');
        });

        DB::table('questions')->orderBy('id')->each(function ($row) {
            DB::table('question_bank')->where('id', $row->question_bank_id)->update(['question' => $row->question]);
        });

        DB::table('questions')->delete();

        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['part_ref', 'marks']);
        });
    }
};
