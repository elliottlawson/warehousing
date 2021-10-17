<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Location;

class StockService
{
    public static function onHand(Inventory $inventory): int
    {
        return $inventory->stock()->sum('quantity');
    }

    public static function onHandOfLot(Inventory $inventory, string|array $lot): int
    {
        return $inventory->stock()
            ->hasLotNumbers($lot)
            ->sum('quantity');
    }

    public static function onHandOfLotInLocation(Inventory $inventory, string|array $lot, Location $location): int
    {
        return $inventory->stock()
            ->hasLotNumbers($lot)
            ->inLocation($location)
            ->sum('quantity');
    }
}
