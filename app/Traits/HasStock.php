<?php

namespace App\Traits;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasStock
{
    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
