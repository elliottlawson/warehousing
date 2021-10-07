<?php

namespace App\Services\Warehouse\Rules;

use App\Services\Warehouse\TransactionDTO;

interface RulesInterface
{
    // return true to allow the action to proceed
    public function evaluate(TransactionDTO $transaction): RuleDTO;

    // if evaluation is false this message will be returned
    public function errorMessage(): string;

    // Logic for if the transaction should be allowed
    public function handle(): bool;
}
