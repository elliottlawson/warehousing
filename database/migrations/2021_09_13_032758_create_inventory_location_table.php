<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryLocationTable extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_location', function (Blueprint $table) {
            $table->foreignId('inventory_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_location');
    }
}
