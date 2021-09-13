<?php

use App\Models\Inventory;
use App\Models\Location;

beforeEach(function () {
    $this->inventory = Inventory::factory()->create(['name' => 'Blue Paperclips']);
});

it('can instantiate a modal')
    ->tap(fn () => true)
    ->assertDatabaseHas('inventories', ['name' => 'Blue Paperclips']);

it('can create and update a modal')
    ->tap(fn () => $this->inventory->update(['name' => 'Navy Paperclips']))
    ->assertDatabaseHas('inventories', ['name' => 'Navy Paperclips']);

it('has a good relationship with locations')
    ->tap(fn () => $this->inventory->locations()->attach(Location::factory()->create()))
    ->expect(fn () => $this->inventory->refresh()->locations->first())
    ->toBeInstanceOf(Location::class);
