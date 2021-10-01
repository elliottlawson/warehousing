<?php

namespace App\Traits;

use App\Enums\TransactionDirection;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTransactions
{
    public function transactions(): HasMany
    {
        return $this->hasMany(Transactions::class);
    }

    public function sourceTransaction(): Transactions
    {
        return $this->transactions->firstWhere('direction', TransactionDirection::FROM);
    }

    public function destinationTransaction(): Transactions
    {
        return $this->transactions->firstWhere('direction', TransactionDirection::TO);
    }
}
