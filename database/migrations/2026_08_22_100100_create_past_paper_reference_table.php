<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where a question came from in a past paper.
     *
     * It hangs off the question's link row rather than off the question itself:
     * the same bank question can be attached to several topics, and each
     * attachment may cite a different paper.
     *
     * `source` is the only optional part — a reference without a date, paper
     * number and marks is not a reference.
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
