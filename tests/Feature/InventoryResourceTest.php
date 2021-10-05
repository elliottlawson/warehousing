<?php

use App\Models\Inventory;
use App\Models\Types\InventoryType;

beforeEach(function () {
    $this->inventory = Inventory::factory()
//        ->forType(['name' => 'Finished Goods', 'abbreviation' => 'FG'])
        ->create(['item_number' => 'Large Paperclips']);
});

it('can instantiate a model')
    ->with() // hack to suppress 'Member has protected visibility' warning
    ->assertDatabaseHas('inventory', ['item_number' => 'Large Paperclips']);

it('can create and update a model')
    ->tap(fn () => $this->inventory->update(['item_number' => 'Small Paperclips']))
    ->assertDatabaseHas('inventory', ['item_number' => 'Small Paperclips']);

it('can handle typing properly', function () {
    $inventory = Inventory::factory()
        ->forType(['name' => 'FG'])
        ->create();

    expect($inventory->type)
        ->not()->toBeNull()
        ->name->toBe('FG')
        ->class->toBe(InventoryType::morphName());
});
