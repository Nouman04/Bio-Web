<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Base for resources that also feed Blade listings.
 *
 * `forView()` exists because array access on a JsonResource forwards to the
 * underlying model, not to `toArray()` — so `$item['on_sale']` would quietly
 * read a model attribute that does not exist. Resolving each item first means a
 * view reads exactly the shape the resource declares, and nothing else.
 *
 * A paginator is mapped through rather than replaced, so `links()`, `total()`
 * and the rest still work in the view.
 */
abstract class AppResource extends JsonResource
{
    /**
     * @param  LengthAwarePaginator|Collection|iterable  $items
     * @return LengthAwarePaginator|Collection
     */
    public static function forView($items)
    {
        $resolve = fn ($model) => (new static($model))->resolve();

        return $items instanceof LengthAwarePaginator
            ? $items->through($resolve)
            : collect($items)->map($resolve);
    }
}
