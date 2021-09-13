<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventory;
use App\Http\Requests\UpdateInventory;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;

class InventoryController extends Controller
{
    public function index(): Collection
    {
        return Inventory::all();
    }

    public function create(): void
    {
        //
    }

    public function store(StoreInventory $request): void
    {
        Inventory::create($request->safe());
    }

    public function show(Inventory $inventory): Inventory
    {
        return $inventory;
    }

    public function edit(Inventory $inventory): void
    {
        //
    }

    public function update(UpdateInventory $request, Inventory $inventory): void
    {
        $inventory->update($request->safe());
    }

    public function destroy(Inventory $inventory): void
    {
        $inventory->delete();
    }
}
