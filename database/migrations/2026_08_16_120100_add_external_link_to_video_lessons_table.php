<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `external_link` is declared in the create_video_lessons migration, but
     * that file was edited after it had already run, so the column never
     * reached the database. This adds it for environments already migrated.
     */
    public function up(): void
    {
        if (Schema::hasColumn('video_lessons', 'external_link')) {
            return;
        }

        Schema::table('video_lessons', function (Blueprint $table) {
            $table->text('external_link')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('video_lessons', function (Blueprint $table) {
            $table->dropColumn('external_link');
        });
    }
};
