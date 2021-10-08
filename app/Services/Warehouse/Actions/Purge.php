<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\ActionDTO;

class Purge extends WarehouseActionsBase
{
    public function handle(ActionDTO $data): Batch
    {
        $source_stock = self::retrieveStockFromLocation($data->source, $data);

        $source_stock->quantity -= $data->quantity;
        $source_stock->save();

        $destination_stock = self::retrieveOrCreateStockFromLocation(LocationService::defaultPurgeDestination(), $data);

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $source_stock->transactions->last()->batch;
    }
}
