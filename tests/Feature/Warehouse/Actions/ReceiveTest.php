<?php

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
            'lot' => $this->lot,
        ]);
});

it('can receive new inventory into the default location', function () {
    $quantity = 100;

    $receive_result = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->execute();

    ray($receive_result);

    expect($receive_result->batch->destinationTransaction()->transactable)
        ->not()->toBeNull()
        ->quantity->toBe($quantity)
        ->location->name->toBe(config('warehouse.receiving.destination'))
        ->transactions->not()->toBeNull();

    expect($receive_result->batch->sourceTransaction()->transactable)
        ->not()->toBeNull()
        ->quantity->toBe(0);

    expect($receive_result->batch)
        ->transactions->count()->toBe(2)
        ->transactions->each(fn ($transaction) => $transaction->quantity->toBe($quantity));

    expect($receive_result->batch->sourceTransaction())
        ->quantity->toBe($quantity)
        ->location->name->toBe(config('warehouse.receiving.source'));
});

it('can receive new inventory into a set location', function () {
    $quantity = 150;

    $receive_result = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    expect($receive_result->batch->destinationTransaction()->transactable)
        ->not()->toBeNull()
        ->quantity->toBe($quantity)
        ->location->id->toBe($this->location->id);
});

it('can rollback a receive transaction', function () {
    $quantity = 1000;

    $receive_result = Warehouse::receive($quantity)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $reverted_batch = Warehouse::rollback($receive_result->batch);

    expect($receive_result->batch->destinationTransaction()->transactable)
        ->quantity->toBe(0)
        ->transactions->count()->toBe(2);

    expect($receive_result->batch)
        ->reverted_at->not()->toBeNull()
        ->transactions->each(fn ($transaction) => $transaction->reverted_at->not()->toBeNull());

    expect($reverted_batch)
        ->not()->toBeNull()
        ->transactions->not()->toBeNull()
        ->transactions->count()->toBe(2);

    expect($reverted_batch->sourceTransaction())
        ->quantity->toBe($quantity)
        ->location->id->toBe($receive_result->batch->destinationTransaction()->location->id);

    expect($reverted_batch->destinationTransaction())
        ->quantity->toBe($quantity)
        ->location->id->toBe($receive_result->batch->sourceTransaction()->location->id)
        ->transactable->quantity->toBe($quantity);
});
