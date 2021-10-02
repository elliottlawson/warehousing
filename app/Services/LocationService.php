<?php

namespace App\Services;

use App\Models\Location;

class LocationService
{
    public static function defaultReceivingSource(): Location
    {
        return Location::firstOrCreate([
            'name' => config('warehouse.receiving.source'),
        ], [
            'description' => 'Default Receiving Source',
        ]);
    }

    public static function defaultReceivingDestination(): Location
    {
        return Location::firstOrCreate([
            'name' => config('warehouse.receiving.destination'),
        ], [
            'description' => 'Default Receiving Destination',
        ]);
    }

    public static function defaultAddSource(): Location
    {
        return Location::firstOrCreate([
            'name' => config('warehouse.add.source'),
        ], [
            'description' => 'Default Add Source',
        ]);
    }

    public static function defaultPurgeDestination(): Location
    {
        return Location::firstOrCreate([
            'name' => config('warehouse.purge.destination'),
        ], [
            'description' => 'Default Purge Destination',
        ]);
    }
}
