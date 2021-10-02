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
        $source = LocationService::defaultReceivingSource();

        $source_stock = Stock::withTrashed()
            ->firstOrCreate([
                'location_id'  => $source->id,
                'inventory_id' => $data->inventory->id,
            ]);

        if ($source_stock->trashed()) {
            $source_stock->restore();
        }

        $destination = $data->destination ?? LocationService::defaultReceivingDestination();

        $destination_stock = Stock::create([
            'quantity'     => $data->quantity,
            'location_id'  => $destination->id,
            'inventory_id' => $data->inventory->id,
        ]);

        Transaction::record($data->action, $data->quantity, $source_stock, $destination_stock);

        return $destination_stock;
    }
}
