<?php

use App\Models\Location;

beforeEach(function () {
    $this->locations = Location::factory()->count(10)->create();
});

it('receives the correct response from the index route')
    ->get('/location')->assertStatus(200);
