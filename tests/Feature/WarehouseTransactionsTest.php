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

it('can add stock to an existing location', function () {
    $stock = Warehouse::add(100)
        ->of($this->inventory, $this->lot)
        ->into($this->location)
        ->execute();

    expect($stock->quantity)->toBe($this->total_stock + 100);
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
    expect($stock->location_id)->toBe($location->id);
    expect($stock->quantity)->toBe($quantity);
    expect($stock->lot)->toBe($this->stock->lot);
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

    Warehouse::rollback($batch);

    expect($this->stock->refresh()->quantity)->toBe($this->total_stock);
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

it('can check the on-hand quantity of inventory across locations', function () {
    $original_total = Warehouse::onHand($this->inventory);

    expect($original_total)->toBe($this->total_stock);

    $this->location = Location::factory()->create();

    Warehouse::receive(50)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $total = Warehouse::onHand($this->inventory);

    expect($total)->toBe($original_total + 50);
});

it('can check availability of stock by lot number', function () {
    $lot        = 4455;
    $quantity_1 = 5000;
    $quantity_2 = 2500;
    $quantity_3 = 3000;

    $stock_1 = Stock::factory()
        ->for($this->inventory)
        ->forLocation()
        ->create([
            'quantity' => $quantity_1,
            'lot'      => $lot,
        ]);

    $stock_with_one_location = Warehouse::onHandOflot($stock_1->inventory, $lot);

    expect($stock_with_one_location)->toBe($quantity_1);

    $stock_2 = Stock::factory()
        ->for($this->inventory)
        ->forLocation()
        ->create([
            'quantity' => $quantity_2,
            'lot'      => $lot,
        ]);

    $stock_with_multiple_locations = Warehouse::onHandOfLot($stock_2->inventory, $lot);

    expect($stock_with_multiple_locations)->toBe($quantity_1 + $quantity_2);

    $stock_3 = Warehouse::receive($quantity_3)
        ->of($this->inventory)
        ->execute();

    $stock_with_multiple_lots = Warehouse::onHandOfLot($this->inventory, [
        $lot,
        $stock_3->lot,
    ]);

    expect($stock_with_multiple_lots)->toBe($quantity_1 + $quantity_2 + $quantity_3);
});
