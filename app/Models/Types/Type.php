<?php

namespace App\Models\Types;

use Database\Factories\TypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
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

    protected static function booted(): void
    {
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->whereClass(self::alias());
        });
    }

    protected static function newFactory(): Factory
    {
        return TypeFactory::new(['class' => self::alias()]);
    }

    // Returns the morph name of the current class
    protected static function alias(): string
    {
        return array_search(static::class, Relation::morphMap(), true);
    }
}
