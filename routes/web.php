<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RowController;
use App\Models\Inventory;
use App\Models\Stock;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::resource('inventory', Inventory::class);
Route::resource('stock', Stock::class);
Route::resource('location', LocationController::class);
Route::resource('room', RoomController::class);
Route::resource('row', RowController::class);
