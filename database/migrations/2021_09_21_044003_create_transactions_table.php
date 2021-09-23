<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('direction');
            $table->integer('quantity');
            $table->foreignId('location_id')->constrained();
            $table->foreignId('batch_id')->constrained();
            $table->morphs('transactable');
            $table->timestamps();
            $table->timestamp('reverted_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}
