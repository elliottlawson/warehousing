<?php

namespace App\Traits;

use App\Enums\TransactionDirection;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Transactable
{
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transactions::class, 'transactable');
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
