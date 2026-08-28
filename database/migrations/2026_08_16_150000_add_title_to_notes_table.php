<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The note form has always asked for a title, but the column never existed
     * — notes were stored as content only. Nullable so existing rows survive.
     */
    public function up(): void
    {
        if (Schema::hasColumn('notes', 'title')) {
            return;
        }

        Schema::table('notes', function (Blueprint $table) {
            $table->string('title')->nullable()->after('topic_id');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
