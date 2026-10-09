<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One question paper or mark scheme being built on the queue.
     *
     * A worksheet can run to thousands of questions, far too many to render
     * inside a request; the job writes its progress here as it goes, so the
     * page can show how far along it is, and the finished file's path.
     */
    public function up(): void
    {
        Schema::create('worksheet_pdfs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('worksheet_id')->constrained('worksheets')->cascadeOnDelete();
            $table->string('type', 20); // paper | mark-scheme
            $table->string('status', 20)->default('queued'); // queued | running | completed | failed
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('stage')->nullable();
            $table->string('path')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worksheet_pdfs');
    }
};
