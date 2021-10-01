<?php

namespace App\Services\Warehouse;

use App\Models\Batch;
use App\Models\Stock;
use App\Services\LocationService;
use App\Services\Transaction as TransactionService;

class Receive implements Transaction
{
    public function handle(TransactionDTO $data): Stock
    {
        $source      = LocationService::defaultReceivingSource();
        $destination = $data->destination ?? LocationService::defaultReceivingDestination();
        $stock       = Stock::make(['quantity' => $data->quantity]);
        $stock->inventory()->associate($data->inventory);
        $stock->location()->associate($destination);
        $stock->save();

        TransactionService::record($data->action, $source, $destination, $stock, $data->quantity);

        return $stock;
    }

    public function rollback(Batch $batch): Batch
    {
        return Batch::factory()->create();
    }
}
