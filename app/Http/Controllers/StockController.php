<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(): Collection
    {
        return Stock::all();
    }

    public function create(): void
    {
        //
    }

    public function store(Request $request): void
    {
        //
    }

    public function show(Stock $stock): Stock
    {
        return $stock;
    }

    public function edit($id): void
    {
        //
    }

    public function update(Request $request, Stock $stock): void
    {
        //
    }

    public function destroy($id): void
    {
        //
    }
}
