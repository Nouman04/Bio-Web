<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An attachment was only ever a stored path. Two things need more than that:
 *
 *  - a record can now hold several *kinds* of file (a course's cover image and
 *    its handouts are both attachments), which is what `collection` separates;
 *  - a form has to show the name of the file that was picked, and the stored
 *    path is a hashed name, so the original one is kept alongside it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('collection')->default('files')->after('file_path')->index();
            $table->string('original_name')->nullable()->after('collection');
            $table->string('mime_type')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex(['collection']);
            $table->dropColumn(['collection', 'original_name', 'mime_type', 'size']);
        });
    }
};
