<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationRowTable extends Migration
{
    public function up(): void
    {
        Schema::create('location_row', function (Blueprint $table) {
            $table->foreignId('location_id')->constrained();
            $table->foreignId('row_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_row');
    }
}
