<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUOMSTable extends Migration
{
    public function up(): void
    {
        Schema::create('uoms', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->foreignId('inventory_id')->constrained('inventory');
            $table->foreignId('type_id')->constrained();
            $table->string('operator')->comment('Flag to determine whether to multiply or divide');
            $table->unsignedBigInteger('factor');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uoms');
    }
}
