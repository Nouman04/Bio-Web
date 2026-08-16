<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questionable_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->unsignedBigInteger('question_id');
            $table->morphs('questionable');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('set null');
            $table->foreign('question_id')->references('id')->on('question_bank')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionable_type');
    }
};
