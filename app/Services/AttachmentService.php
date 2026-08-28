<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Files attached to a record — topics, notes, summaries and the rest.
 *
 * Uploads are added to whatever is already there rather than replacing it, so
 * editing a record does not silently drop its existing files.
 */
class AttachmentService
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function store(Model $record, array $files, string $directory): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $record->attachments()->create([
                'file_path' => $file->store($directory, 'public'),
            ]);
        }
    }

    /**
     * Removes a record's attachments, files included — nothing should be left
     * orphaned on disk.
     */
    public function purge(Model $record): void
    {
        foreach ($record->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
    }
}
