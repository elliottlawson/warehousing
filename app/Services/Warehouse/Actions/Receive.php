<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\ActionDTO;

class Receive extends WarehouseActionsBase
{
    public function handle(ActionDTO $data): Batch
    {
        $source_stock = self::retrieveOrCreateStockFromLocation(LocationService::defaultReceivingSource(), $data);

        $destination = $data->destination ?? LocationService::defaultReceivingDestination();

        $destination_stock = self::createStockInLocation($destination, $data);

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $destination_stock->transactions->last()->batch;
    }
}
