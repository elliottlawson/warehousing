<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Typeable
{
    public function type(): BelongsTo
    {
        return $this->belongsTo(static::$typeClass);
    }
}
