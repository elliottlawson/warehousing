<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Transaction;
use App\Services\Warehouse\TransactionDTO;

class Receive implements TransactionAction
{
    public function handle(TransactionDTO $data): Stock
    {
        $source      = LocationService::defaultReceivingSource();
        $destination = $data->destination ?? LocationService::defaultReceivingDestination();
        $stock       = Stock::make(['quantity' => $data->quantity]);

        $stock->inventory()->associate($data->inventory);
        $stock->location()->associate($destination);
        $stock->save();

        Transaction::record($data->action, $source, $destination, $stock, $data->quantity);

        return $stock;
    }

    public function rollback(Batch $batch): Batch
    {
        return Batch::factory()->create();
    }
}
