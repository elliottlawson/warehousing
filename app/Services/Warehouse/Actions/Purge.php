<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Stock;
use App\Services\Warehouse\TransactionDTO;

class Purge extends WarehouseActionsBase
{
    public function handle(TransactionDTO $data): Stock
    {
        $stock = Stock::query()
            ->hasLotNumbers($data->lot)
            ->ofInventory($data->inventory)
            ->inLocation($data->source)
            ->firstOrFail();

        if ($stock->quantity === $data->quantity) {
            $stock->delete();
        } else {
            // Note: the location will go negative if the amount purged is greater than the quantity on hand
            $stock->quantity -= $data->quantity;
            $stock->save();
        }

//        Transaction::record($data->action, $data->source, $data->destination, $stock, $data->quantity);

        return $stock;
    }
}
