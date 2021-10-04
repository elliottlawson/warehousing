<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

class Add extends WarehouseActionsBase
{
    public function handle(TransactionDTO $data): Stock
    {
        $source_stock = self::retrieveOrCreateStockFromLocation(LocationService::defaultAddSource(), $data);

        $destination_stock = self::retrieveStockFromLocation($data->destination, $data);

        $destination_stock->quantity += $data->quantity;
        $destination_stock->save();

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $destination_stock;
    }
}
