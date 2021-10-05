<?php

namespace App\Models\Types;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class Type extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'types';

    protected $guarded = ['id'];

    protected $hidden = ['class'];

    protected static string $factory;

    protected static function booted(): void
    {
        static::creating(fn ($query) => $query->class = self::morphName());

        static::addGlobalScope('type', function (Builder $builder) {
            $builder->whereClass(self::morphName());
        });
    }

    // Returns the morph name of the current class
    public static function morphName(): string
    {
        return array_search(static::class, Relation::morphMap(), true);
    }
}
