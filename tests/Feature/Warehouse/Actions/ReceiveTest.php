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

it('can receive new inventory into the default location', function () {
    $quantity = 100;

    $stock = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->execute();

    $batch = $stock->transactions->first()->batch;

    $destination_stock = Stock::query()
        ->ofInventory($stock->inventory)
        ->inLocation($batch->sourceTransaction()->location)
        ->first();

    expect($stock)->not()->toBeNull();
    expect($stock->quantity)->toBe($quantity);
    expect($stock->location->name)->toBe(config('warehouse.receiving.destination'));
    expect($batch->sourceTransaction()->quantity)->toBe($quantity);
    expect($batch->sourceTransaction()->location->name)->toBe(config('warehouse.receiving.source'));
    expect($destination_stock)->not()->toBeNull();
    expect($destination_stock->quantity)->toBe(0);
});

it('can receive new inventory into a set location', function () {
    $quantity = 150;

    $stock = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    expect($stock)->not()->toBeNull();
    expect($stock->quantity)->toBe($quantity);
    expect($stock->location->id)->toBe($this->location->id);
});

it('can rollback a receive transaction', function () {
    $quantity = 1000;

    $stock = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $batch = $stock->batch();

    $reverted = Warehouse::rollback($batch);

    $stock->refresh();

    $destination_stock = Stock::query()
        ->ofInventory($stock->inventory)
        ->inLocation($batch->sourceTransaction()->location)
        ->first();

    expect($reverted)->not()->toBeNull();
    expect($stock->quantity)->toBe(0);
    expect($destination_stock)->not()->toBeNull();
    expect($destination_stock->quantity)->toBe($quantity);
});
