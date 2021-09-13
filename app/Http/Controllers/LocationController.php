<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocation;
use App\Http\Requests\UpdateLocation;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class LocationController extends Controller
{
    public function index(): Collection
    {
        return Location::all();
    }

    public function create(): void
    {
        //
    }

    public function store(StoreLocation $request): void
    {
        Location::create($request->safe());
    }

    public function show(Location $location): Location
    {
        return $location;
    }

    public function edit(Location $location): void
    {
        //
    }

    public function update(UpdateLocation $request, Location $location): void
    {
        $location->update($request->safe());
    }

    public function destroy(Location $location): void
    {
        $location->delete();
    }
}
