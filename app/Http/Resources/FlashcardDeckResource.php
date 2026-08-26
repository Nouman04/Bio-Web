<?php

namespace App\Http\Resources;

use App\Models\Flashcard as Record;
use Illuminate\Http\Request;

/**
 * One flashcard deck in a chapter listing.
 *
 * The reader's progress is not part of this: it is per-user state the
 * controller loads in one query and keeps beside the collection, keyed by id.
 *
 * @mixin Record
 */
class FlashcardDeckResource extends AppResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Kept so the view can look this record up in the progress map.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title ?: 'Untitled set',
            'cards' => (int) ($this->assessments_count ?? 0),
            'source_label' => $this->source_label,
            'source_kind' => $this->flashcardable_type ? \Illuminate\Support\Str::headline(class_basename($this->flashcardable_type)) : null,
        ];
    }
}
