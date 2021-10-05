<?php

namespace App\Console\Commands;

use App\Models\Types\InventoryType;
use Illuminate\Console\Command;

class DefaultInventoryCommand extends Command
{
    protected $signature = 'generate:inventory-types';

    protected $description = 'Generate Default Inventory Types';

    protected static array $data = [
        ['name' => 'Finished Goods', 'abbreviation' => 'FG'],
        ['name' => 'Packaging', 'abbreviation' => 'PK'],
        ['name' => 'Raw Materials', 'abbreviation' => 'RM'],
        ['name' => 'Raw Materials', 'abbreviation' => 'RM'],
    ];

    public function handle(): int
    {
        $record = collect(self::$data)->first();
        InventoryType::insert($record);

        return self::SUCCESS;
    }
}
