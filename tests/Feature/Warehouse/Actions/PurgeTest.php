<?php

use App\Enums\TransactionType;
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

it('can purge some stock from a location', function () {
    $initial_quantity = 5000;
    $purge_quantity   = 1500;

    $stock = Warehouse::receive($initial_quantity)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $adjusted = Warehouse::purge($purge_quantity)
        ->of($this->inventory, $stock->lot)
        ->from($this->location)
        ->execute();

    expect($adjusted)
        ->not()->toBeNull()
        ->quantity->toBe($initial_quantity - $purge_quantity);
});

it('can purge all stock from a location', function () {
    Warehouse::purge($this->total_stock)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->execute();

    expect($this->inventory->stock()->sum('quantity'))->toBe(0);
});

it('can drive stock negative with a purge', function () {
    $stock = Warehouse::receive(50)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $purged = Warehouse::purge(100)
        ->of($this->inventory, $stock->lot)
        ->from($this->location)
        ->execute();

    expect($purged)
        ->not()->toBeNull()
        ->quantity->toBe(-50);
});

it('can rollback a purge transaction', function () {
    $inventory = Inventory::factory()->create();

    $initial_quantity = 5000;
    $purge_quantity   = 1500;

    $received = Warehouse::receive($initial_quantity)
        ->of($inventory)
        ->into($this->location)
        ->execute();

    $purged = Warehouse::purge($purge_quantity)
        ->of($inventory, $received->lot)
        ->from($this->location)
        ->execute();

    $batch_to_revert = $purged->transactions()
        ->whereType(TransactionType::PURGE())
        ->whereQuantity($purge_quantity)
        ->whereLocationId($purged->location->id)
        ->first()
        ->batch;

    $reverted_batch = Warehouse::rollback($batch_to_revert);

    $purged->refresh()->load('transactions', 'transactions.location');

    expect($reverted_batch)
        ->not()->toBeNull()
        ->transactions->count()->toBe(2);

    expect($batch_to_revert)
        ->reverted_at->not()->toBeNull()
        ->transactions->each(fn ($transactions) => $transactions->reverted_at->not()->toBeNull());

    expect($purged->quantity)->toBe($initial_quantity);

    expect($reverted_batch->sourceTransaction())->quantity->toBe($purge_quantity);
    expect($reverted_batch->destinationTransaction())->quantity->toBe($purge_quantity);
});
