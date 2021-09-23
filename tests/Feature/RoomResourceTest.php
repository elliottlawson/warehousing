<?php

use App\Models\Room;

beforeEach(function () {
    $this->room = Room::factory()->create(['name' => 'Utility Closet']);
});

it('can instantiate a modal')
    ->assertDatabaseHas('rooms', ['name' => 'Utility Closet']);

it('can create and update a location modal')
    ->tap(fn () => $this->room->update(['name' => 'Tool Room']))
    ->assertDatabaseHas('rooms', ['name' => 'Tool Room']);

// it('has a good relationship with locations')
//     ->tap(fn () => Location::factory()->create()->room()->attach($this->room))
//     ->expect(fn() => $this->room->refresh()->locations->first())
//     ->not()->toBeNull()
//     ->toBeInstanceOf(Location::class);
