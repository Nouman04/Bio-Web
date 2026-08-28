<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A lesson can be an uploaded file *or* an external link (YouTube, Vimeo),
     * so the stored path has to be optional.
     */
    public function up(): void
    {
        Schema::table('video_lessons', function (Blueprint $table) {
            $table->text('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('video_lessons', function (Blueprint $table) {
            $table->text('file_path')->nullable(false)->change();
        });
    }
};
