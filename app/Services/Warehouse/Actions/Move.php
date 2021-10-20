<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\Warehouse\ActionDTO;

class Move extends WarehouseActionsBase
{
    public function setSourceStock(ActionDTO $data): Stock
    {
        return self::retrieveOrCreateStockFromLocation($data->source, $data);
    }

    public function setDestinationStock(ActionDTO $data): Stock
    {
        return self::retrieveOrCreateStockFromLocation($data->destination, $data);
    }
}
