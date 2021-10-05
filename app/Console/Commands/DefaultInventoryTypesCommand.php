<?php

namespace App\Console\Commands;

use App\Models\Types\InventoryType;
use Illuminate\Console\Command;

class DefaultInventoryTypesCommand extends Command
{
    protected $signature = 'generate:inventory-types';

    protected $description = 'Generate Default Inventory Types';

    protected static array $data = [
        ['name' => 'Finished Goods', 'abbreviation' => 'FG'],
        ['name' => 'Packaging', 'abbreviation' => 'PK'],
        ['name' => 'Raw Materials', 'abbreviation' => 'RM'],
        ['name' => 'Work In Process', 'abbreviation' => 'WIP'],
    ];

    public function handle(): int
    {
        collect(self::$data)
            ->each(fn ($record) => InventoryType::create($record));

        return self::SUCCESS;
    }
}
