<?php

namespace App\Traits;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasInventory
{
    public function inventory(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'stocks');
    }
}
