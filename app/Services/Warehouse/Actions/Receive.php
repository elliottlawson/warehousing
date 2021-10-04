<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

class Receive extends WarehouseActionsBase
{
    public function handle(TransactionDTO $data): Stock
    {
        $source_stock = self::retrieveOrCreateStockFromLocation(LocationService::defaultReceivingSource(), $data);

        $destination = $data->destination ?? LocationService::defaultReceivingDestination();

        $destination_stock = self::createStockInLocation($destination, $data);

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $destination_stock;
    }
}
