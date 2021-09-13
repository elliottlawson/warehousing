<?php

namespace App\Providers;

use App\Models\Location;
use App\Models\Row;
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
            'location' => Location::class,
            'row'      => Row::class,
            'user'     => User::class,
        ]);
    }
}
