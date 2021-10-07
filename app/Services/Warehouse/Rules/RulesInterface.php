<?php

namespace App\Services\Warehouse\Rules;

use App\Services\Warehouse\ActionDTO;

interface RulesInterface
{
    // return true to allow the action to proceed
    public function evaluate(ActionDTO $transaction): RuleDTO;

    // if evaluation is false this message will be returned
    public function errorMessage(): string;

    // Logic for if the transaction should be allowed
    public function handle(): bool;
}
