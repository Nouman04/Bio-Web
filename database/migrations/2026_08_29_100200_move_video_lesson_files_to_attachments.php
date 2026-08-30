<?php

use App\Models\Attachment;
use App\Models\VideoLesson;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A video lesson's uploaded file becomes an attachment like every other upload
 * in the app, rather than a column of its own.
 *
 * The existing paths are copied across before the column goes, so nothing that
 * was uploaded stops playing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('video_lessons', 'file_path')) {
            return;
        }

        DB::table('video_lessons')
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->orderBy('id')
            ->select('id', 'file_path')
            ->chunkById(200, function ($lessons) {
                foreach ($lessons as $lesson) {
                    Attachment::firstOrCreate([
                        'attachmentable_type' => VideoLesson::class,
                        'attachmentable_id' => $lesson->id,
                        'collection' => 'video',
                    ], [
                        'file_path' => $lesson->file_path,
                        'original_name' => basename($lesson->file_path),
                    ]);
                }
            });

        Schema::table('video_lessons', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('video_lessons', function (Blueprint $table) {
            $table->text('file_path')->nullable()->after('description');
        });

        Attachment::where('attachmentable_type', VideoLesson::class)
            ->where('collection', 'video')
            ->chunkById(200, function ($attachments) {
                foreach ($attachments as $attachment) {
                    DB::table('video_lessons')
                        ->where('id', $attachment->attachmentable_id)
                        ->update(['file_path' => $attachment->file_path]);
                }
            });
    }
};
