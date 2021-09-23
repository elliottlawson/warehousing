<?php

namespace App\Services;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transactions;

class Transaction
{
    public static function record(
        TransactionType $type,
        ?Location $source,
        ?Location $target,
        Stock $stock,
        int $quantity
    ): void {
        if ($type->is(TransactionType::ROLLBACK())) {
            return;
        }

        self::createTransaction($type, $source, $target, $stock, $quantity);
    }

    private static function createTransaction(
        TransactionType $type,
        ?Location $source,
        ?Location $destination,
        Stock $stock,
        int $quantity
    ): void {
        $batch = self::initializeBatch();

        $source_transaction = Transactions::make([
            'direction' => TransactionDirection::FROM,
            'type' => $type,
            'quantity' => $quantity,
        ]);

        $target_transaction = Transactions::make([
            'direction' => TransactionDirection::TO,
            'type' => $type,
            'quantity' => $quantity,
        ]);

        $source_transaction->location()->associate($source);
        $target_transaction->location()->associate($destination);

        $source_transaction->batch()->associate($batch);
        $target_transaction->batch()->associate($batch);

        $stock->transactions()->saveMany([
            $source_transaction,
            $target_transaction,
        ]);
    }

    public static function rollback(Batch $batch): void
    {
        // tbd - mark existing transactions as rolled back?
        //     - create additional (rollback) transactions?
    }

    private static function initializeBatch(): Batch
    {
        return Batch::create();
    }
}
