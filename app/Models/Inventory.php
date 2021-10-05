<?php

namespace App\Models;

use App\Models\Types\InventoryType;
use App\Traits\HasLocations;
use App\Traits\HasStock;
use App\Traits\Typeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;
    use HasLocations;
    use HasStock;
    use SoftDeletes;
    use Typeable;

    protected $table = 'inventory';

    protected $guarded = ['id'];

    protected static string $typeClass = InventoryType::class;
}
