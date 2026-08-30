<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A deck, built from whatever it was made out of — a note, a summary, a
     * topic — which is what the `flashcardable` morph holds.
     */
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('title');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->nullableMorphs('flashcardable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};