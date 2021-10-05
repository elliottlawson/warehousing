<?php

namespace App\Models;

use App\Models\Types\LocationType;
use App\Traits\HasInventory;
use App\Traits\HasStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use HasInventory;
    use HasStock;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function type(): MorphTo
    {
        return $this->morphTo(LocationType::class, 'typeable');
    }
}
