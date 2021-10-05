<?php

use App\Models\Location;
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

// it('has a good relationship with locations')o
//     ->tap(fn () => $this->location->inventory()->attach(Inventory::factory()->create()))
//     ->expect(fn () => $this->location->refresh()->inventory->first())
//     ->toBeInstanceOf(Inventory::class);
//

it('can handle typing properly', function () {
    $location = Location::factory()
        ->forType(['name' => 'row'])
        ->create();

    expect($location->type)
        ->not()->toBeNull()
        ->name->toBe('row')
        ->class->toBe(LocationType::morphName());
});
