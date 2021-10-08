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
            'lot'      => $this->lot,
        ]);
});

it('can purge some stock from a location', function () {
    $initial_quantity = 5000;
    $purge_quantity = 1500;

    $receive_result = Warehouse::receive($initial_quantity)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $lot = $receive_result->batch->destinationTransaction()->transactable->lot; // @todo - add lot accessor to batch

    $purge_result = Warehouse::purge($purge_quantity)
        ->of($this->inventory, $lot)
        ->from($this->location)
        ->execute();

    expect($purge_result->batch->sourceTransaction()->transactable)
        ->not()->toBeNull()
        ->quantity->toBe($initial_quantity - $purge_quantity);

    expect($receive_result->batch->transactions)
        ->not()->toBeNull()
        ->count()->toBe(2);

    expect($purge_result->batch->sourceTransaction())
        ->quantity->toBe($purge_quantity)
        ->location->id->toBe($this->location->id);

    expect($purge_result->batch->destinationTransaction())
        ->quantity->toBe($purge_quantity)
        ->location->name->toBe(config('warehouse.purge.destination'));
});

it('can purge all stock from a location', function () {
    Warehouse::purge($this->total_stock)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->execute();

    expect($this->inventory->stock()->sum('quantity'))->toBe(0);
});

it('can drive stock negative with a purge', function () {
    $receive_result = Warehouse::receive(50)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $lot = $receive_result->batch->destinationTransaction()->transactable->lot;

    $purge_result = Warehouse::purge(100)
        ->of($this->inventory, $lot)
        ->from($this->location)
        ->execute();

    expect($purge_result->batch->sourceTransaction()->transactable)
        ->not()->toBeNull()
        ->quantity->toBe(-50);
});

it('can rollback a purge transaction', function () {
    $inventory = Inventory::factory()->create();

    $initial_quantity = 5000;
    $purge_quantity = 1500;

    $receive_result = Warehouse::receive($initial_quantity)
        ->of($inventory)
        ->into($this->location)
        ->execute();

    $lot = $receive_result->batch->destinationTransaction()->transactable->lot;

    $purge_result = Warehouse::purge($purge_quantity)
        ->of($inventory, $lot)
        ->from($this->location)
        ->execute();

    $reverted_batch = Warehouse::rollback($purge_result->batch);

    expect($reverted_batch)
        ->not()->toBeNull()
        ->transactions->count()->toBe(2)
        ->sourceTransaction()->quantity->toBe($purge_quantity)
        ->destinationTransaction()->quantity->toBe($purge_quantity);

    expect($purge_result->batch)
        ->reverted_at->not()->toBeNull()
        ->transactions->each(fn ($transactions) => $transactions->reverted_at->not()->toBeNull());

    expect($purge_result->batch->sourceTransaction()->transactable->quantity)->toBe($initial_quantity);
});
