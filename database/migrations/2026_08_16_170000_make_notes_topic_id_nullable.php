<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A note now belongs to its chapter, with the topic optional — so the
     * column has to allow null, and deleting a topic should orphan the note
     * rather than delete it.
     */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->change();
            $table->foreign('topic_id')->references('id')->on('topics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable(false)->change();
            $table->foreign('topic_id')->references('id')->on('topics')->cascadeOnDelete();
        });
    }
};
