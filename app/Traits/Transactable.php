<?php

namespace App\Traits;

use App\Models\Transactions;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Transactable
{
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transactions::class, 'transactable');
    }
}
