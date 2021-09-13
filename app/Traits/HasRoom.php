<?php

namespace App\Traits;

use App\Models\Room;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoom
{
    public function room(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }
}