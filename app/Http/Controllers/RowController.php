<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRow;
use App\Http\Requests\UpdateRow;
use App\Models\Row;
use Illuminate\Database\Eloquent\Collection;

class RowController extends Controller
{
    public function index(): Collection
    {
        return Row::all();
    }

    public function create(): void
    {
        //
    }

    public function store(StoreRow $request): void
    {
        Row::create($request->safe());
    }

    public function show(Row $row): Row
    {
        return $row;
    }

    public function edit(Row $row): void
    {
        //
    }

    public function update(UpdateRow $request, Row $row): void
    {
        $row->update($request->safe());
    }

    public function destroy(Row $row): void
    {
        $row->delete();
    }
}
