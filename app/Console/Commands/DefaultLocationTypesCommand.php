<?php

namespace App\Console\Commands;

use App\Models\Types\LocationType;
use Illuminate\Console\Command;

class DefaultLocationTypesCommand extends Command
{
    protected $signature = 'generate:location-types';

    protected $description = 'Generate Default Location Types';

    protected array $types = [
        'slot', 'row', 'room', 'silo',
    ];

    public function handle(): int
    {
        collect($this->types)
            ->each(fn ($name) => LocationType::create(['name' => $name]));

        return self::SUCCESS;
    }
}
