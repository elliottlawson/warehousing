<?php

namespace App\Models\Types;

use App\Models\Location;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationType extends Type
{
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'class');
    }
}
