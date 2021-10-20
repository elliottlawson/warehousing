<?php

namespace App\Services\Warehouse;

use App\Models\Batch;
use App\Services\Warehouse\Rules\RuleOutcomeDTO;
use App\Traits\Makeable;

class TransactionDTO
{
    use Makeable;

    public bool $success;

    public ActionDTO $transaction;

    public ?Batch $batch;

    public RuleOutcomeDTO $rulesOutcome;
}
