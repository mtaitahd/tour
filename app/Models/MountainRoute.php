<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MountainRoute extends Model
{
    protected $fillable = ['mountain_id', 'name', 'description', 'order'];

    public function mountain(): BelongsTo
    {
        return $this->belongsTo(Mountain::class);
    }
}
