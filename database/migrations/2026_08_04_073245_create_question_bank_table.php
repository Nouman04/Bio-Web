<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->foreignId('question_categories_id')->constrained('question_categories')->cascadeOnDelete();
            $table->text('question');
            $table->string('difficulty_level')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank');
    }
};
