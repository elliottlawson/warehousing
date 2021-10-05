<?php

namespace App\Models\Types;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryType extends Type
{
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
