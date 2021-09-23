<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('weight')->nullable();
            $table->foreignId('type_id')->constrained('types');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
}
