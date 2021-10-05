<?php

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;

beforeEach(function () {
    $this->inventory = Inventory::factory()->create();
    $this->location = Location::factory()->create();
    $this->lot = '12345';
    $this->stock = Stock::factory()
        ->for($this->inventory)
        ->for($this->location)
        ->create([
            'quantity' => 100,
            'lot' => $this->lot,
        ]);
});

it('can create stock', function () {
    expect($this->stock)
        ->quantity->toBe(100)
        ->inventory->id->toBe($this->inventory->id)
        ->location->id->toBe($this->location->id);
});

it('has a good relationship with inventory and locations', function () {
    expect($this->stock->id)
        ->toBeIn($this->inventory->stock->pluck('id'))
        ->toBeIn($this->location->stock->pluck('id'));
});

it('can set the lot correctly', function () {
    $stock = Stock::factory()
        ->forInventory()
        ->forLocation()
        ->create(['quantity' => 500]);

    expect($this->stock->lot)->toBe($this->lot);
    expect($stock->lot)->not()->toBeNull();
});
