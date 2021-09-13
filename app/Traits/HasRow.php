<?php

namespace App\Traits;

use App\Models\Row;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRow
{
    public function row(): BelongsToMany
    {
        return $this->belongsToMany(Row::class);
    }
}