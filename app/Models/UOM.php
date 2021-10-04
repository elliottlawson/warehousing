<?php

namespace App\Models;

use App\Enums\OperatorType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UOM extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'uoms';

    protected $casts = [
        'operator' => OperatorType::class,
    ];

    protected $guarded = ['id'];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

//    public function type(): BelongsTo
//    {
//        return $this->belongsTo(Type::class);
//    }
}
