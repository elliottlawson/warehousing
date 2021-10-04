<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeablesTable extends Migration
{
    public function up(): void
    {
        Schema::create('typeables', function (Blueprint $table) {
            $table->foreignId('type_id')->constrained('types');
            $table->morphs('typeable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typeables');
    }
}
