<?php

use App\Models\Row;

beforeEach(function () {
    $this->row = Row::factory()->create(['name' => 'Main Row']);
});

it('can instantiate a modal')
    ->assertDatabaseHas('rows', ['name' => 'Main Row']);

it('can create and update a modal')
    ->tap(fn() => $this->row->update(['name' => 'Row #07']))
    ->assertDatabaseHas('rows', ['name' => 'Row #07']);

// it('has a good relationship with locations')
//     ->tap(fn () => Location::factory()->create()->row()->attach($this->row))
//     ->expect(fn() => $this->row->refresh()->locations->first())
//     ->not()->toBeNull()
//     ->toBeInstanceOf(Location::class);

// it('has a good relationship with rooms')
//     ->tap(fn () => $this->location->room()->associate(Room::factory()->create()))
//     ->expect(fn() => $this->location->room)
//     ->toBeInstanceOf(Room::class);
