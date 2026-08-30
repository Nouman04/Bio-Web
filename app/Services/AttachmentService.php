<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

/**
 * Files attached to a record — course covers, chapter images, video uploads and
 * the handouts on topics, notes and summaries.
 *
 * Handouts are added to whatever is already there rather than replacing it, so
 * editing a record does not silently drop its existing files. A cover image and
 * a video upload are single by nature, so storing one replaces what was there.
 */
class AttachmentService
{
    /**
     * Adds files to a record's collection.
     *
     * @param  array<int, UploadedFile|null>  $files
     */
    public function store(Model $record, array $files, string $directory, string $collection = Attachment::DEFAULT): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $record->attachments()->create($this->attributesFor($file, $directory, $collection));
        }
    }

    /**
     * Stores the one file a single-file collection holds, replacing whatever
     * was there — the old file goes with it, so nothing is orphaned on disk.
     *
     * A null file leaves the existing one alone: an edit form that was not
     * given a new upload should keep what the record already has.
     */
    public function replace(Model $record, ?UploadedFile $file, string $directory, string $collection): ?Attachment
    {
        if (! $file) {
            return $record->attachments()->where('collection', $collection)->latest('id')->first();
        }

        foreach ($record->attachments()->where('collection', $collection)->get() as $existing) {
            $existing->purge();
        }

        $record->unsetRelation('attachments')->unsetRelation('image');

        return $record->attachments()->create($this->attributesFor($file, $directory, $collection));
    }

    /**
     * Removes a record's attachments, files included. Narrow it to one
     * collection to leave the rest alone.
     */
    public function purge(Model $record, ?string $collection = null): void
    {
        $attachments = $record->attachments();

        if ($collection !== null) {
            $attachments->where('collection', $collection);
        }

        foreach ($attachments->get() as $attachment) {
            $attachment->purge();
        }

        $record->unsetRelation('attachments')->unsetRelation('image');
    }

    /**
     * What gets written for one upload. The original filename is kept so a form
     * can show the reader which file is attached rather than a hashed path.
     *
     * @return array<string, mixed>
     */
    private function attributesFor(UploadedFile $file, string $directory, string $collection): array
    {
        return [
            'file_path' => $file->store($directory, 'public'),
            'collection' => $collection,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }
}
