<?php

namespace App\Traits;

use App\Models\Location;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasLocations
{
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'stocks');
    }
}
