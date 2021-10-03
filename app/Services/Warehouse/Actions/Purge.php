<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

class Purge extends WarehouseActionsBase
{
    public function handle(TransactionDTO $data): Stock
    {
        $source_stock = self::retrieveStockFromLocation($data->source, $data);

        $source_stock->quantity -= $data->quantity;
        $source_stock->save();

        $destination = LocationService::defaultPurgeDestination();

        $destination_stock = Stock::withTrashed()
            ->firstOrCreate([
                'location_id'  => $destination->id,
                'inventory_id' => $data->inventory->id,
            ]);

        if ($destination_stock->trashed()) {
            $destination_stock->restore();
        }

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $source_stock;
    }
}
