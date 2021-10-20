<?php

namespace App\Models;

use App\Models\Types\LocationType;
use App\Traits\HasInventory;
use App\Traits\HasRules;
use App\Traits\HasStock;
use App\Traits\Typeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use HasInventory;
    use HasRules;
    use HasStock;
    use Typeable;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected static string $typeClass = LocationType::class;
}
