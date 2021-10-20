<?php

namespace App\Models;

use App\Services\LocationService;
use App\Traits\Transactable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Stock extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Transactable;

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    protected $guarded = ['id'];

    public static function booted(): void
    {
        // We want a 'default' lot number if none is set
        static::creating(fn ($query) => $query->lot ??= self::generateLot());

        static::addGlobalScope('suppressReceiveAndPurgeStock', function (Builder $query) {
            $query->whereDoesntHave('location', function (Builder $query) {
                $query->whereIn('id', [
                    LocationService::defaultReceivingSource()->id,
                    LocationService::defaultPurgeDestination()->id,
                ]);
            });
        });
    }

    public function scopeIncludeSystemStocks(Builder $query): void
    {
        $query->withoutGlobalScope('suppressReceiveAndPurgeStock');
    }

    public function scopeExcludeSystemStocks(Builder $query): void
    {
        $query->whereDoesntHave('location', function (Builder $query) {
            $query->whereIn('id', [
                LocationService::defaultReceivingSource()->id,
                LocationService::defaultPurgeDestination()->id,
            ]);
        });
    }

    private static function generateLot(): string
    {
        return (string) random_int(1000, 99999);
    }

    public function batch(): Batch
    {
        return $this->transactions->first()->batch;
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeInLocation(Builder $query, Location $location): Builder
    {
        return $query->whereRelation('location', 'id', $location->id);
    }

    public function scopeOfInventory(Builder $query, Inventory $inventory): Builder
    {
        return $query->whereRelation('inventory', 'id', $inventory->id);
    }

    public function scopeHasLotNumbers(Builder $query, array|string $lotNumbers): Builder
    {
        return $query->whereIn('lot', Arr::wrap($lotNumbers));
    }

    public function add(int $quantity): self
    {
        $this->quantity += $quantity;
        $this->save();

        return $this;
    }

    public function subtract(int $quantity): self
    {
        $this->quantity -= $quantity;
        $this->save();

        return $this;
    }
}
