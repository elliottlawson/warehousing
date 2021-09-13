<?php

namespace App\Models;

use App\Traits\HasLocations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Row extends Model
{
    use HasFactory;
    use HasLocations;
    use SoftDeletes;

    protected $guarded = ['id'];
}
