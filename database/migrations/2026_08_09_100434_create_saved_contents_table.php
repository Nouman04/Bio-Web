<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('contentable');
            $table->timestamps();

            // A reader saves a given thing once.
            $table->unique(
                ['user_id', 'contentable_type', 'contentable_id'],
                'saved_contents_owner_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_contents');
    }
};