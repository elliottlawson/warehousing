<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transactions;
use App\Models\Types\InventoryType;
use App\Models\Types\LocationType;
use App\Models\Types\Type;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::requireMorphMap();

        Relation::morphMap([
            'batch'          => Batch::class,
            'inventory'      => Inventory::class,
            'location'       => Location::class,
            'stock'          => Stock::class,
            'transaction'    => Transactions::class,
            'type'           => Type::class,
            'type.inventory' => InventoryType::class,
            'type.location'  => LocationType::class,
            'user'           => User::class,
        ]);
    }
}
