<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where a question came from, when it came from a past paper. Hangs off the
     * link between the question and the record it was attached to, not off the
     * question itself — the same question can be cited differently elsewhere.
     */
    public function up(): void
    {
        Schema::create('past_paper_reference', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('questionable_type_id')
                ->constrained('questionable_type')
                ->cascadeOnDelete();
            $table->date('date');
            $table->string('paper_no');
            $table->string('question_no')->nullable();
            $table->decimal('marks', 8, 2);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->index('questionable_type_id', 'past_paper_reference_link_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('past_paper_reference');
    }
};