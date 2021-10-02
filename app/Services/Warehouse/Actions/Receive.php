<?php

namespace App\Services\Warehouse\Actions;

use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Stock;
use App\Models\Transactions;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

class Receive implements TransactionAction
{
    public function handle(TransactionDTO $data): Stock
    {
        $source = LocationService::defaultReceivingSource();

        $source_stock = Stock::withTrashed()
            ->firstOrCreate([
                'location_id'  => $source->id,
                'inventory_id' => $data->inventory->id,
            ]);

        if ($source_stock->trashed()) {
            $source_stock->restore();
        }

        $destination = $data->destination ?? LocationService::defaultReceivingDestination();

        $destination_stock = Stock::create([
            'quantity'     => $data->quantity,
            'location_id'  => $destination->id,
            'inventory_id' => $data->inventory->id,
        ]);

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $destination_stock;
    }

    public function rollback(Batch $batch): Batch
    {
        $batch->loadMissing('transactions', 'transactions.transactable');

        $quantity = $batch->sourceTransaction()->quantity;

        $source_stock = self::retrieveStockFromTransaction($batch->destinationTransaction());

        $source_stock->quantity -= $quantity;
        $source_stock->save();

        $destination_stock = self::retrieveStockFromTransaction($batch->sourceTransaction());

        $destination_stock->quantity += $quantity;
        $destination_stock->save();

        return Transaction::record(TransactionType::ROLLBACK(), $quantity, $source_stock, $destination_stock, $batch);
    }

    protected static function retrieveStockFromTransaction(Transactions $transaction): Stock
    {
        /** @var Stock $stock */
        $stock = $transaction->transactable->loadMissing('location');

        if ($stock->trashed()) {
            $stock->restore();
        }

        if ($stock->location->trashed()) {
            $stock->location->restore();
        }

        return $stock;
    }
}
