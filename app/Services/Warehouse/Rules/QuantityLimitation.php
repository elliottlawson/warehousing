<?php

namespace App\Services\Warehouse\Rules;

use App\Models\Stock;

class QuantityLimitation extends AbstractRule
{
    public function handle(): bool
    {
        $quantity_in_location = Stock::query()
            ->inLocation($this->results->rule->location)
            ->sum('quantity');

        $quantity_after_transaction = $quantity_in_location + $this->results->transaction->quantity;

        // @todo - implement getValue() method to auto cast
        //       - will also need a type column on the rules schema
        return $quantity_after_transaction <= (int) $this->results->rule->value;
    }

    public function errorMessage(): string
    {
        return "Transaction would increase the location quantity beyond the allowed quantity of {$this->results->rule->value}";
    }
}
