<?php

namespace App\Services\Warehouse\Actions;

use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transactions;
use App\Services\Transaction;
use App\Services\Warehouse\ActionDTO;
use App\Services\Warehouse\StockTransactionService;

abstract class WarehouseActionsBase implements TransactionInterface
{
    public function handle(ActionDTO $data): Batch
    {
        $data->source_stock      = $this->setSourceStock($data);
        $data->destination_stock = $this->setDestinationStock($data);

        StockTransactionService::transfer($data->quantity)
            ->from($data->source_stock)
            ->to($data->destination_stock);

        Transaction::record($data->action, $data->quantity, $data->source_stock, $data->destination_stock);

        return $data->destination_stock->transactions->last()->batch;
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

    protected static function createStockInLocation(Location $location, ActionDTO $data): Stock
    {
        return Stock::create([
            'location_id'  => $location->id,
            'inventory_id' => $data->inventory->id,
        ]);
    }

    protected static function retrieveOrCreateStockFromLocation(Location $location, ActionDTO $data): Stock
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

    protected static function retrieveStockFromLocation(Location $location, ActionDTO $data): Stock
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
        $stock = $transaction->transactable()->includeSystemStocks()->first(); // @TODO - Refactor?

        if ($stock->trashed()) {
            $stock->restore();
        }

        if ($stock->location->trashed()) {
            $stock->location->restore();
        }

        return $stock;
    }
}
