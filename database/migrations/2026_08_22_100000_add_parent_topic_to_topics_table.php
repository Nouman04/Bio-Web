<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A topic may sit under another topic, and the parent need not be in the
     * same chapter — a chapter often elaborates on something introduced
     * earlier, so the link crosses chapters on purpose.
     *
     * Nulled rather than cascaded on delete: removing a parent should orphan
     * its children, not delete them.
     */
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->foreignId('parent_topic_id')
                ->nullable()
                ->after('chapter_id')
                ->constrained('topics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_topic_id');
        });
    }
};
