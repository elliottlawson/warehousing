<?php

use App\Enums\RuleType;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Rule;
use App\Services\Warehouse;

it('can apply a quantity limitation rule to a location', function () {
    $inventory = Inventory::factory()->create();
    $location = Location::factory()->create();

    $rule = Rule::factory()
        ->for($location)
        ->create([
            'name' => 'Limit location to 5,000 items',
            'type' => RuleType::QUANTITY_LIMITATION,
            'value' => 5000,
        ]);

    $stock = Warehouse::receive(3000)
        ->of($inventory)
        ->into($location)
        ->execute();

    ray($rule, $stock);

    $result = Warehouse::add(3000)
        ->of($inventory, $stock->lot)
        ->into($location)
        ->execute();

    ray($result);
});
