<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every file in the app: course covers, chapter images, video uploads and
     * the handouts hanging off notes, topics and summaries.
     *
     * `collection` is what tells those apart, so one record can hold a single
     * cover image and any number of documents. The original filename is kept
     * because the stored path is hashed, and a form has to be able to say which
     * file is attached.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->longText('file_path');
            $table->string('collection')->default('files')->index();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->morphs('attachmentable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};