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

it('can record a receive transaction', function () {
    $stock = Warehouse::receive(500)
        ->of($this->inventory)
        ->execute();

    expect($stock->transactions)->not()->toBeNull();
    expect($stock->transactions->count())->toBe(2);
    expect($stock->sourceTransaction()->location->name)
        ->toBe(config('warehouse.receiving.source'));
    expect($stock->destinationTransaction()->location->name)
        ->toBe(config('warehouse.receiving.destination'));
});

it('can record a move transaction', function () {
    $location = Location::factory()->create();

    $stock = Warehouse::move(100)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->into($location)
        ->execute();

    expect($stock->transactions)->not()->toBeNull();
    expect($stock->transactions->count())->toBe(2);
});

it('can record an add transaction', function () {
    $quantity = 1000;

    $stock = Warehouse::add($quantity)
        ->of($this->inventory, $this->lot)
        ->into($this->location)
        ->execute();

    expect($stock->transactions)->not()->toBeNull();
    expect($stock->transactions->count())->toBe(2);

    $from_transaction = $stock->sourceTransaction();

    expect($from_transaction->location->name)->toBe(config('warehouse.add.source'));
    expect($from_transaction->quantity)->toBe($quantity);
    expect($stock->quantity)->toBe($this->total_stock + $quantity);
});

it('can record a purge transaction', function () {
    $quantity = 200;

    $stock = Warehouse::purge($quantity)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->execute();

    expect($stock->transactions)->not()->toBeNull();
    expect($stock->transactions->count())->toBe(2);

    $to_transaction = $stock->destinationTransaction();

    expect($to_transaction->location->name)->toBe(config('warehouse.purge.destination'));
    expect($to_transaction->quantity)->toBe($quantity);
    expect($stock->quantity)->toBe($this->total_stock - $quantity);
});

/**
 * Check if locations are deleted and restore them if so
 */
it('can rollback a transaction', function () {
    $location = Location::factory()->create();

    $batch = Warehouse::move(100)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->into($location)
        ->execute()
        ->batch(); // stock should not be null

    $reverted_batch = Warehouse::rollback($batch);

    expect($reverted_batch->transactions)->not()->toBeNull();
    expect($reverted_batch->transactions->count())->toBe(2);
    expect($reverted_batch->sourceTransaction()->location->name)
        ->toBe($batch->destinationTransaction()->location->name);
    expect($reverted_batch->destinationTransaction()->location->name)
        ->toBe($batch->sourceTransaction()->location->name);
});
