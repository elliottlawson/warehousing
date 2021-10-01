<?php

namespace App\Services\Warehouse;

use App\Models\Batch;
use App\Models\Stock;
use App\Services\Transaction as TransactionService;

class Add implements Transaction
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

        TransactionService::record($data->action, $data->source, $data->destination, $stock, $data->quantity);

        return $stock;
    }

    public function rollback(Batch $batch): Batch
    {
        // TODO: Implement rollback() method.
    }
}
