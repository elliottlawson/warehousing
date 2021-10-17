<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Warehouse\ActionDTO;

class Add extends WarehouseActionsBase
{
    public function setSourceStock(ActionDTO $data): Stock
    {
        return self::retrieveOrCreateStockFromLocation(LocationService::defaultAddSource(), $data);
    }

    public function setDestinationStock(ActionDTO $data): Stock
    {
        return self::retrieveStockFromLocation($data->destination, $data);
    }
}
