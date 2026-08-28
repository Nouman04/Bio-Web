<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saved content is per reader, but the table had no owner — every row
     * belonged to everybody. This gives each save a user, and stops the same
     * person saving one thing twice.
     */
    public function up(): void
    {
        Schema::table('saved_contents', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();

            $table->unique(
                ['user_id', 'contentable_type', 'contentable_id'],
                'saved_contents_owner_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('saved_contents', function (Blueprint $table) {
            $table->dropUnique('saved_contents_owner_unique');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
