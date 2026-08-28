<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'file_path',
        'attachmentable_id',
        'attachmentable_type',
    ];

    public function attachmentable()
    {
        return $this->morphTo();
    }
}
