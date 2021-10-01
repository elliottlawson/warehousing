<?php

namespace App\Services;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transactions;
use Illuminate\Support\Carbon;

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
            'type'      => $type,
            'quantity'  => $quantity,
        ]);

        $target_transaction = Transactions::make([
            'direction' => TransactionDirection::TO,
            'type'      => $type,
            'quantity'  => $quantity,
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

    public static function rollback(Batch $batch, Stock $stock): Batch
    {
        $new_batch = self::rollbackBatchAndReplace($batch);

        self::createRollbackTransactions($batch, $new_batch, $stock);

        return $new_batch;
    }

    public static function rollbackBatchAndReplace(Batch $old_batch): Batch
    {
        $timestamp = now();

        $old_batch->update(['reverted_at' => $timestamp]);

        $old_batch->transactions->each->update(['reverted_at' => $timestamp]);

        return tap(self::initializeBatch($timestamp), static fn ($batch) => $batch->reverted()->associate($old_batch));
    }

    private static function createRollbackTransactions(Batch $old_batch, Batch $new_batch, Stock $stock): void
    {
        $original_source_transaction = $old_batch->sourceTransaction();
        $original_target_transaction = $old_batch->destinationTransaction();

        self::buildTransaction(TransactionDirection::FROM(), $original_target_transaction, $new_batch, $original_target_transaction->transactable);
        self::buildTransaction(TransactionDirection::TO(), $original_source_transaction, $new_batch, $stock);
    }

    private static function buildTransaction(
        TransactionDirection $type,
        Transactions $template,
        Batch $batch,
        Stock $stock,
    ) {
        tap(Transactions::make(), static function (Transactions $transaction) use ($type, $template, $batch, $stock) {
            $transaction
                ->fill([
                    'type'        => $template->type,
                    'direction'   => $type,
                    'quantity'    => $template->quantity,
                    'location_id' => $template->location_id,
                    'batch_id'    => $batch->id,
                    'created_at'  => $batch->created_at,
                    'updated_at'  => $batch->updated_at,
                ])
                ->transactable()->associate($stock)
                ->save();
        });
    }

    private static function initializeBatch(Carbon $timestamp = null): Batch
    {
        return Batch::create([
            'created_at' => $timestamp ?? now(),
            'updated_at' => $timestamp ?? now(),
        ]);
    }
}
