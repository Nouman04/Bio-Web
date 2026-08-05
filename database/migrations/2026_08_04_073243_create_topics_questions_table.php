<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topics_id')->constrained('topics')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_bank')->cascadeOnDelete();
            $table->date('referenced_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics_questions');
    }
};
