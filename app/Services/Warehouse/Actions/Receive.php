<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Warehouse\ActionDTO;

class Receive extends WarehouseActionsBase
{
    public function setSourceStock(ActionDTO $data): Stock
    {
        return self::retrieveOrCreateStockFromLocation(LocationService::defaultReceivingSource(), $data);
    }

    public function setDestinationStock(ActionDTO $data): Stock
    {
        $data->destination = $data->destination ?? LocationService::defaultReceivingDestination();

        return self::createStockInLocation($data->destination, $data);
    }
}
