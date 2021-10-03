<?php

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Services\Warehouse;

beforeEach(function () {
    $this->inventory   = Inventory::factory()->create();
    $this->location    = Location::factory()->create();
    $this->total_stock = 1000;
    $this->lot         = '12345';
    $this->stock       = Stock::factory()
        ->for($this->inventory)
        ->for($this->location)
        ->create([
            'quantity' => $this->total_stock,
            'lot'      => $this->lot,
        ]);
});

it('can add stock to an existing location', function () {
    $stock = Warehouse::add(100)
        ->of($this->inventory, $this->lot)
        ->into($this->location)
        ->execute();

    expect($stock->quantity)->toBe($this->total_stock + 100);
});
