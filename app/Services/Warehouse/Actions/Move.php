<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Models\Stock;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

class Move implements TransactionAction
{
    public function handle(TransactionDTO $data): Stock
    {
        $source_stock = $data->source->stock()
            ->hasLotNumbers($data->lot)
            ->inLocation($data->source)
            ->firstOrFail();

        if ($source_stock->quantity === $data->quantity) {
            $source_stock->location()->associate($data->destination)->save();

            Transaction::record($data->action, $data->source, $data->destination, $source_stock, $data->quantity);

            return $source_stock;
        }

        $source_stock->quantity -= $data->quantity;
        $source_stock->save();

        $stock = Stock::firstOrNew([
            'lot'          => $data->lot,
            'location_id'  => $data->destination->id,
            'inventory_id' => $data->inventory->id,
        ]);

        $stock->quantity = $data->quantity;
        $stock->save();

        Transaction::record($data->action, $data->source, $data->destination, $stock, $data->quantity);

        return $stock;
    }

    public function rollback(Batch $batch): Batch
    {
        // use transactions to determine how to put things back
        // source <- was (to) transaction location
        // destination <- was (from) transaction location
        // reverse source and destination
        // use quantity to determine if we just swapped the location_id in stock
        // Also need to check if there are any dependant transactions that have happened since and abort...

        $from_transaction = $batch->sourceTransaction();
        $to_transaction   = $batch->destinationTransaction();

        $lot                  = $from_transaction->transactable->lot;
        $transaction_quantity = $from_transaction->quantity;

        // Reverse locations
        $source      = $to_transaction->location; // @todo - What if stock is not in this location??
        $destination = $from_transaction->location;

        /** @var Stock $source_stock */
        $source_stock = $to_transaction->transactable;

        // what if this is false, we need to resuscitate the stock / location / lot no
        $destination_stock = Stock::query()
            ->hasLotNumbers($lot)
            ->inLocation($destination)
            ->first();

        // exact quantity and no pre-existing stock conflict
        if ($source_stock->quantity === $transaction_quantity && is_null($destination_stock)) {
            $source_stock->location()->associate($destination);

            return $source_stock->sourceTransaction()->batch;
        }

        $source_stock->quantity -= $transaction_quantity;
        $source_stock->save();

        if (is_null($destination_stock)) {
            $destination_stock = Stock::make([
                'lot' => $lot,
            ]);
            $destination_stock->inventory()->associate($source_stock->inventory);
            $destination_stock->location()->associate($destination);
        }

        $destination_stock->quantity += $transaction_quantity;
        $destination_stock->save();

        /**
         * The issue here is that we are operating on batches in isolation from the stock
         */

        return Transaction::rollback($batch, $destination_stock);
    }
}
