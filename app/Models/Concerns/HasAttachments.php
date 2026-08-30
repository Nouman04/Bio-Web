<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Gives a model files, kept in the shared `attachments` table.
 *
 * Files are grouped by collection: `files` is the default pile of handouts,
 * `image` is the single cover a course or chapter carries, `video` is the one
 * upload behind a video lesson. Reading one is the same either way, which is
 * what lets a form show "what is attached" without knowing which kind it is.
 */
trait HasAttachments
{
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    /**
     * The record's cover image, if it has one.
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachmentable')
            ->where('collection', Attachment::IMAGE)
            ->latestOfMany();
    }

    /**
     * The plain files — the handouts, not the cover. This is what a listing of
     * "attachments" should show, so a cover image does not appear twice.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable')
            ->where('collection', Attachment::DEFAULT);
    }

    /**
     * Somewhere to show the cover, or null when there is none.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image?->url;
    }
}
