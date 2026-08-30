<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A file belonging to some record — a course cover, a chapter image, a video
 * lesson's upload, or one of the handouts hanging off a note or summary.
 *
 * `collection` is what tells those apart: a record can hold one cover image and
 * any number of documents without the two ever being confused for each other.
 */
class Attachment extends Model
{
    /**
     * The collection a file lands in when nothing else is said.
     */
    public const DEFAULT = 'files';

    /**
     * The single-file collections. A record holds at most one of each, so
     * storing a new one replaces what was there.
     */
    public const IMAGE = 'image';
    public const VIDEO = 'video';

    protected $fillable = [
        'file_path',
        'collection',
        'original_name',
        'mime_type',
        'size',
        'attachmentable_id',
        'attachmentable_type',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function attachmentable()
    {
        return $this->morphTo();
    }

    public function scopeCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Where the file can actually be fetched from.
     *
     * Built with asset() rather than Storage::url(), because the latter is
     * pinned to APP_URL and breaks whenever the app is served on another port
     * or from a subdirectory.
     */
    public function getUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * The name to show a person: what they uploaded, falling back to the stored
     * filename for rows written before the original was kept.
     */
    public function getNameAttribute(): string
    {
        return $this->original_name ?: basename((string) $this->file_path);
    }

    /**
     * The size as a person would read it — "1.4 MB". Null when unrecorded.
     */
    public function getReadableSizeAttribute(): ?string
    {
        if (! $this->size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log(max($this->size, 1), 1024)), count($units) - 1);

        return round($this->size / (1024 ** $power), $power ? 1 : 0) . ' ' . $units[$power];
    }

    /**
     * Removes the row and the file behind it — nothing should be left orphaned
     * on disk.
     */
    public function purge(): void
    {
        Storage::disk('public')->delete($this->file_path);
        $this->delete();
    }
}
