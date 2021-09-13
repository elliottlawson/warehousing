<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoom;
use App\Http\Requests\UpdateRoom;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class RoomController extends Controller
{
    public function index(): Collection
    {
        return Room::all();
    }

    public function create(): void
    {
        //
    }

    public function store(StoreRoom $request): void
    {
        Room::create($request->safe());
    }

    public function show(Room $room): Room
    {
        return $room;
    }

    public function edit(Room $room): void
    {
        //
    }

    public function update(UpdateRoom $request, Room $room): void
    {
        $room->update($request->safe());
    }

    public function destroy(Room $room): void
    {
        $room->delete();
    }
}
