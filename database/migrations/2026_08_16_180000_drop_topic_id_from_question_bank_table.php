<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questions belong to a chapter and reach topics through the
     * `questionable_type` links, so the direct topic_id is redundant.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('question_bank', 'topic_id')) {
            return;
        }

        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });
    }

    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('chapter_id')
                ->constrained('topics')->nullOnDelete();
        });
    }
};
