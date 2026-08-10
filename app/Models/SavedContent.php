<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedContent extends Model
{

    public function contentable()
    {
        return $this->morphTo();
    }
}
