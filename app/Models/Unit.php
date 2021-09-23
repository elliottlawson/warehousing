<?php

namespace App\Models;

use App\Traits\HasInventory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory;
    use HasInventory;
    use SoftDeletes;

    protected $guarded = ['id'];
}
