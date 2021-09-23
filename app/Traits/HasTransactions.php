<?php

namespace App\Traits;

use App\Models\Transactions;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTransactions
{
    public function transactions(): HasMany
    {
        return $this->hasMany(Transactions::class);
    }
}
