<?php

namespace App\Traits;

use App\Models\Row;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRows
{
    public function rows()
    {
        return $this->belongsToMany(Row::class);
    }
}