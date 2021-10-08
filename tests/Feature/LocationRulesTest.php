<?php

use App\Enums\RuleType;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Rule;
use App\Services\Warehouse;

it('can apply a quantity limitation rule to a location', function () {
    $inventory = Inventory::factory()->create();
    $location = Location::factory()->create();

    Rule::factory()
        ->for($location)
        ->create([
            'name' => 'Limit location to 5,000 items',
            'type' => RuleType::QUANTITY_LIMITATION,
            'value' => 5000,
        ]);

    $transactionDTO = Warehouse::receive(3000)
        ->of($inventory)
        ->into($location)
        ->execute();

    $lot = $transactionDTO->batch->destinationTransaction()->transactable->lot;

    Warehouse::add(3000)
        ->of($inventory, $lot)
        ->into($location)
        ->execute();

    expect(Warehouse::onHandOfLotInLocation($inventory, $lot, $location))->toBe(3000);

    Warehouse::add(2000)
        ->of($inventory, $lot)
        ->into($location)
        ->execute();

    expect(Warehouse::onHandOfLotInLocation($inventory, $lot, $location))->toBe(5000);
});
