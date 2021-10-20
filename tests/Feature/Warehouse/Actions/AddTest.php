<?php

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Services\Warehouse;

beforeEach(function () {
    $this->inventory = Inventory::factory()->create();
    $this->location = Location::factory()->create();
    $this->total_stock = 1000;
    $this->lot = '12345';
    $this->stock = Stock::factory()
        ->for($this->inventory)
        ->for($this->location)
        ->create([
            'quantity' => $this->total_stock,
            'lot'      => $this->lot,
        ]);
});

it('can add stock to an existing location', function () {
    $quantity = 100;

    $add_result = Warehouse::add($quantity)
        ->of($this->inventory, $this->lot)
        ->into($this->location)
        ->execute();

    /** @var Batch $batch */
    $batch = $add_result->batch;

    expect($batch->destinationTransaction()->transactable)
        ->quantity->toBe($this->total_stock + $quantity)
        ->transactions->not()->toBeNull()
        ->transactions->count()->toBe(1);

    expect($batch)
        ->sourceTransaction()->quantity->toBe($quantity)
        ->sourceTransaction()->location->name->toBe(config('warehouse.add.source'))
        ->destinationTransaction()->quantity->toBe($quantity);
});

it('can rollback an add transaction', function () {
    $quantity = 200;

    $add_result = Warehouse::add($quantity)
        ->of($this->inventory, $this->lot)
        ->into($this->location)
        ->execute();

    /** @var Batch $batch */
    $batch = $add_result->batch;

    $reverted_batch = Warehouse::rollback($batch);

    expect($batch->destinationTransaction()->transactable->refresh())
        ->quantity->toBe($this->total_stock)
        ->transactions->count()->toBe(2);

    expect($batch->refresh())
        ->reverted_at->not()->toBeNull()
        ->transactions->each(fn ($transaction) => $transaction->reverted_at->not()->toBeNull());

    expect($reverted_batch)
        ->transactions->not()->toBeNull()
        ->transactions->count()->toBe(2)
        ->sourceTransaction()->location->id->toBe($batch->destinationTransaction()->location->id)
        ->destinationTransaction()->location->id->toBe($batch->sourceTransaction()->location->id);
});
