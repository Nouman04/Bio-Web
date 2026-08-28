<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two create-migrations were edited after they had already run, so the
     * columns they declare never reached the database:
     *
     *   topics.content            (create_topics_table)
     *   question_bank.topic_id    (create_question_bank_table)
     *
     * Both are guarded, so a fresh `migrate:fresh` — where the create
     * migrations do add them — passes over this one harmlessly.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('topics', 'content')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->longText('content')->nullable()->after('title');
            });
        }

        if (! Schema::hasColumn('question_bank', 'topic_id')) {
            Schema::table('question_bank', function (Blueprint $table) {
                $table->foreignId('topic_id')->nullable()->after('chapter_id')
                    ->constrained('topics')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('question_bank', 'topic_id')) {
            Schema::table('question_bank', function (Blueprint $table) {
                $table->dropConstrainedForeignId('topic_id');
            });
        }

        if (Schema::hasColumn('topics', 'content')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->dropColumn('content');
            });
        }
    }
};
