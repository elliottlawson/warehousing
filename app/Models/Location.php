<?php

namespace App\Models;

use App\Traits\HasInventory;
use App\Traits\HasRoom;
use App\Traits\HasRow;
use App\Traits\HasStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use HasInventory;
    use HasRoom;
    use HasRow;
    use HasStock;
    use SoftDeletes;

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    protected $guarded = ['id'];
}
