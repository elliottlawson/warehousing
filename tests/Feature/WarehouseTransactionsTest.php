<?php

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Services\StockService;
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


it('can check the on-hand quantity of inventory across locations', function () {
    $original_total = StockService::onHand($this->inventory);

    expect($original_total)->toBe($this->total_stock);

    $this->location = Location::factory()->create();

    Warehouse::receive(50)
        ->of($this->inventory)
        ->into($this->location)
        ->execute();

    $total = StockService::onHand($this->inventory);

    expect($total)->toBe($original_total + 50);
});

it('can check availability of stock by lot number', function () {
    $lot = 4455;
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

    $stock_with_one_location = StockService::onHandOflot($stock_1->inventory, $lot);

    expect($stock_with_one_location)->toBe($quantity_1);

    $stock_2 = Stock::factory()
        ->for($this->inventory)
        ->forLocation()
        ->create([
            'quantity' => $quantity_2,
            'lot'      => $lot,
        ]);

    $stock_with_multiple_locations = StockService::onHandOfLot($stock_2->inventory, $lot);

    expect($stock_with_multiple_locations)->toBe($quantity_1 + $quantity_2);

    $receive_result = Warehouse::receive($quantity_3)
        ->of($this->inventory)
        ->execute();

    $stock_with_multiple_lots = StockService::onHandOfLot($this->inventory, [
        $lot,
        $receive_result->batch->destinationTransaction()->transactable->lot,
    ]);

    expect($stock_with_multiple_lots)->toBe($quantity_1 + $quantity_2 + $quantity_3);
});
