<?php

namespace App\Models;

use App\Traits\HasLocations;
use App\Traits\HasStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;
    use HasLocations;
    use HasStock;
    use SoftDeletes;

    protected $table = 'inventory';

    protected $guarded = ['id'];

//    public function type(): BelongsTo
//    {
//        return $this->belongsTo(Type::class);
//    }
}
