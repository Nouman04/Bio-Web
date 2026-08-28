<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questions attached to something that assesses: a flashcard deck, a
     * worksheet or a quiz. `order` is the position within that parent.
     */
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->morphs('assessmentable');
            $table->foreignId('question_id')->constrained('question_bank')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['assessmentable_type', 'assessmentable_id', 'question_id'], 'assessments_parent_question_unique');
            $table->index(['assessmentable_type', 'assessmentable_id', 'order'], 'assessments_parent_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
