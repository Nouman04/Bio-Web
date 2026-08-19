<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Gives a model a stable public identifier. The uuid is what appears in URLs —
 * auto-increment ids are never exposed — so route model binding resolves on it
 * and route() emits it for any model passed as a parameter.
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * The column route model binding matches against.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
