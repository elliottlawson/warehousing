<?php

use App\Models\Location;
use App\Models\Types\InventoryType;
use App\Models\Types\LocationType;

beforeEach(function () {
    $this->location = Location::factory()->create(['name' => 'Refinery']);
});

it('can instantiate a modal')
    ->tap(fn () => true)
    ->assertDatabaseHas('locations', ['name' => 'Refinery']);

it('can create and update a modal')
    ->tap(fn () => $this->location->update(['name' => 'long term storage']))
    ->assertDatabaseHas('locations', ['name' => 'long term storage']);

// it('has a good relationship with locations')
//     ->tap(fn () => $this->location->inventory()->attach(Inventory::factory()->create()))
//     ->expect(fn () => $this->location->refresh()->inventory->first())
//     ->toBeInstanceOf(Inventory::class);
//

it('can handle typing properly', function () {
    InventoryType::factory()->create(['name' => 'FG']);
    InventoryType::factory()->create(['name' => 'PK']);
    InventoryType::factory()->create(['name' => 'IN']);

    $row = LocationType::factory()->create(['name' => 'row']);


    $location_types  = LocationType::all()->toArray();
    $inventory_types = InventoryType::all()->toArray();

    $location = Location::factory()
        ->for($row)
        ->create();

    ray($location);
});
