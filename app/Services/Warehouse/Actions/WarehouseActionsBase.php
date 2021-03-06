<?php

namespace App\Services\Warehouse\Actions;

use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transactions;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

abstract class WarehouseActionsBase implements TransactionAction
{
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

    protected static function createStockInLocation(Location $location, TransactionDTO $data): Stock
    {
        return Stock::create([
            'quantity'     => $data->quantity,
            'location_id'  => $location->id,
            'inventory_id' => $data->inventory->id,
        ]);
    }

    protected static function retrieveOrCreateStockFromLocation(Location $location, TransactionDTO $data): Stock
    {
        $stock = Stock::withTrashed()
            ->firstOrCreate([
                'lot'          => $data->lot,
                'inventory_id' => $data->inventory->id,
                'location_id'  => $location->id,
            ]);

        if ($stock->trashed()) {
            $stock->restore();
        }

        return $stock;
    }

    protected static function retrieveStockFromLocation(Location $location, TransactionDTO $data): Stock
    {
        $stock = Stock::withTrashed()
            ->hasLotNumbers($data->lot)
            ->ofInventory($data->inventory)
            ->inLocation($location)
            ->firstOrFail();

        if ($stock->trashed()) {
            $stock->restore();
        }

        return $stock;
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
