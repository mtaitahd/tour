<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mountain extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function routes(): HasMany
    {
        return $this->hasMany(MountainRoute::class)->orderBy('order')->orderBy('name');
    }
}
