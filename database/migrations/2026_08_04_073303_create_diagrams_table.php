<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagrams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->foreignId('question_bank_id')->nullable()->constrained('question_bank')->nullOnDelete();
            $table->string('image_path');
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagrams');
    }
};
