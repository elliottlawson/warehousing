<?php

namespace App\Services\Warehouse;

use App\Enums\TransactionType;
use App\Models\Batch;
use App\Services\Warehouse\Rules\RuleOutcomeDTO;
use App\Traits\Makeable;

class TransactionDTO
{
    use Makeable;

    public TransactionType $action;

    public bool $success;

    public TransactionDTO $transaction;

    public Batch $batch;

    public RuleOutcomeDTO $rulesOutcome;
}
