<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\Transaction;
use App\Services\Warehouse\ActionDTO;

class Move extends WarehouseActionsBase
{
    public function handle(ActionDTO $data): Stock
    {
        $source_stock = self::retrieveOrCreateStockFromLocation($data->source, $data);

        $source_stock->quantity -= $data->quantity;
        $source_stock->save();

        $destination_stock = self::retrieveOrCreateStockFromLocation($data->destination, $data);

        $destination_stock->quantity = $data->quantity;
        $destination_stock->save();

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $destination_stock;
    }
}
