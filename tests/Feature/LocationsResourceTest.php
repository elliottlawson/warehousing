<?php

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Room;
use App\Models\Row;

beforeEach(function () {
    $this->location = Location::factory()->create(['name' => 'Refinery']);
});

it('can instantiate a modal')
    ->tap(fn () => true)
    ->assertDatabaseHas('locations', ['name' => 'Refinery']);

it('can create and update a modal')
    ->tap(fn () => $this->location->update(['name' => 'long term storage']))
    ->assertDatabaseHas('locations', ['name' => 'long term storage']);

it('has a good relationship with locations')
    ->tap(fn () => $this->location->inventory()->attach(Inventory::factory()->create()))
    ->expect(fn () => $this->location->refresh()->inventory->first())
    ->toBeInstanceOf(Inventory::class);

it('has a good relationship with rows')
    ->tap(fn () => $this->location->row()->attach(Row::factory()->create()))
    ->expect(fn () => $this->location->refresh()->row->first())
    ->toBeInstanceOf(Row::class);

it('has a good relationship with rooms')
    ->tap(fn () => $this->location->room()->attach(Room::factory()->create()))
    ->expect(fn () => $this->location->refresh()->room->first())
    ->toBeInstanceOf(Room::class);
