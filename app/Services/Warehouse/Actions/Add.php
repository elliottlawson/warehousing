<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\Warehouse\TransactionDTO;

class Add extends WarehouseActionsBase
{
    public function handle(TransactionDTO $data): Stock
    {
        $stock = Stock::query()
            ->hasLotNumbers($data->lot)
            ->ofInventory($data->inventory)
            ->inLocation($data->destination)
            ->firstOrFail();

        $stock->quantity += $data->quantity;
        $stock->save();

//        Transaction::record($data->action, $data->quantity $data->source, $data- );

        return $stock;
    }
}
