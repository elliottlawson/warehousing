<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            // $table->morphs('batchable');
            $table->foreignId('parent_id')->nullable()->constrained('batches');
            $table->foreignId('reverted_id')->nullable()->constrained('batches');
            $table->timestamp('reverted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
}
