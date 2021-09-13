<?php

namespace Database\Seeders;

use App\Models\Row;
use Illuminate\Database\Seeder;

class RowSeeder extends Seeder
{
    public function run(): void
    {
        Row::factory()
            ->count(20)
            ->create();
    }
}
