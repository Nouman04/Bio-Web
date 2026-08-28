<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One run of the question-worksheet importer.
     *
     * A real worksheet runs to thousands of rows, so the import happens on the
     * queue and the page watches this row for progress. Failures are collected
     * rather than thrown: one malformed row should not abandon the other
     * fourteen thousand.
     */
    public function up(): void
    {
        Schema::create('worksheet_imports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            // The chapter the import was started from, used for rows that name
            // no chapter of their own.
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->string('filename');
            $table->string('path');
            $table->enum('status', ['queued', 'running', 'completed', 'failed'])->default('queued');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            // Chapters and topics the run created, so the result can say so.
            $table->unsignedInteger('created_topics')->default(0);
            $table->unsignedInteger('created_chapters')->default(0);
            $table->json('failures')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worksheet_imports');
    }
};
