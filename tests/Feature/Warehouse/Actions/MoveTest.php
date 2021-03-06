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

it('can move inventory to a location', function () {
    $location = Location::factory()->create();
    $quantity = 100;

    $stock = Warehouse::move($quantity)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->into($location)
        ->execute();

    expect($this->stock->refresh()->quantity)->toBe($this->total_stock - $quantity);

    expect($stock)
        ->location_id->toBe($location->id)
        ->quantity->toBe($quantity)
        ->lot->toBe($this->stock->lot);
});

it('can move inventory to a location even if the stock has been deleted', function () {
    $location = Location::factory()->create();
    $quantity = 200;

    $this->stock->delete();

    $stock = Warehouse::move($quantity)
        ->of($this->inventory, $this->lot)
        ->from($this->location)
        ->into($location)
        ->execute();

    expect($this->stock->refresh())->quantity->toBe($this->total_stock - $quantity);
    expect($stock)
        ->location_id->toBe($location->id)
        ->quantity->toBe($quantity)
        ->lot->toBe($this->stock->lot);
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

    expect($this->stock->refresh()->quantity)->toBe($this->total_stock);
    expect($reverted_batch)->not()->toBeNull();

    expect($batch)
        ->reverted_at->not()->toBeNull()
        ->transactions->each(fn ($transaction) => $transaction->reverted_at->not()->toBeNull());

    expect($reverted_batch)
        ->transactions->not()->toBeNull()
        ->transactions->count()->toBe(2)
        ->sourceTransaction()->location->id->toBe($batch->destinationTransaction()->location->id)
        ->destinationTransaction()->location->id->toBe($batch->sourceTransaction()->location->id);
});
