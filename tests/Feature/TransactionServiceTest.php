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
    $quantity = 500;

    $stock = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->execute();

    $batch = $stock->transactions->first()->batch;

    expect($stock->transactions)->not()->toBeNull();
    expect($batch->transactions->count())->toBe(2);
    expect($batch->transactions)->each(fn ($transaction) => $transaction->quantity->toBe($quantity));
});

it('can record a move transaction')->skip('Consider merging with actions test...');

it('can record an add transaction')->skip('Consider merging with actions test...');

it('can record a purge transaction', function () {
    $quantity = 200;

    $stock = Warehouse::purge($quantity)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->execute();

    $batch = $stock->batch();

    expect($stock->transactions)
        ->not()->toBeNull()
        ->count()->toBe(1);

    expect($batch->sourceTransaction())
        ->quantity->toBe($quantity)
        ->location->id->toBe($this->location->id);

    expect($batch->destinationTransaction())
        ->quantity->toBe($quantity)
        ->location->name->toBe(config('warehouse.purge.destination'));
});

it('can rollback a move transaction', function () {
    $location = Location::factory()->create();

    $batch = Warehouse::move(100)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->into($location)
        ->execute()
        ->batch();

    $reverted_batch = Warehouse::rollback($batch);

    expect($reverted_batch)->not()->toBeNull();
    expect($batch->reverted_at)->not()->toBeNull();
    expect($batch->transactions)
        ->each(fn ($transaction) => $transaction->reverted_at->not()->toBeNull());
    expect($reverted_batch->transactions)->not()->toBeNull();
    expect($reverted_batch->transactions->count())->toBe(2);
    expect($reverted_batch->sourceTransaction()->location->id)
        ->toBe($batch->destinationTransaction()->location->id);
    expect($reverted_batch->destinationTransaction()->location->id)
        ->toBe($batch->sourceTransaction()->location->id);
});

it('can rollback a receive transaction', function () {
    $quantity = 5000;
    $location = Location::factory()->create();

    $stock = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->into($location)
        ->execute();

    $batch = $stock->batch();

    $reverted_batch = Warehouse::rollback($batch);

    $stock->refresh();

    expect($reverted_batch)->not()->toBeNull();
    expect($stock->transactions->count())->toBe(2);
    expect($batch->reverted_at)->not()->toBeNull();
    expect($batch->transactions)->each(fn ($transaction) => $transaction->reverted_at->not()->toBeNull());
    expect($reverted_batch->transactions)->not()->toBeNull();
    expect($reverted_batch->transactions->count())->toBe(2);
    expect($reverted_batch->sourceTransaction()->location->id)->toBe($batch->destinationTransaction()->location->id);
    expect($reverted_batch->destinationTransaction()->location->id)->toBe($batch->sourceTransaction()->location->id);
    expect($reverted_batch->sourceTransaction()->quantity)->toBe($quantity);
    expect($reverted_batch->destinationTransaction()->quantity)->toBe($quantity);
});
