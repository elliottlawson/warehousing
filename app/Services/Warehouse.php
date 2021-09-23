<?php

namespace App\Services;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transactions;
use App\Traits\Makeable;
use Illuminate\Support\Str;
use RuntimeException;

class Warehouse
{
    use Makeable;

    protected ?Inventory $inventory = null;
    protected ?Location $source = null;
    protected ?Location $destination = null;
    protected ?Stock $stock = null;
    protected ?int $quantity = null;
    protected ?string $lot = null;
    private ?TransactionType $action = null;
    private array $requirements = [];

    public static function receive(int $quantity): self
    {
        return tap(self::make(), function ($self) use ($quantity) {
            $self->action   = TransactionType::RECEIVE();
            $self->quantity = $quantity;
            $self->require('quantity', 'inventory');
        });
    }

    public static function add(int $quantity): self
    {
        return tap(self::make(), function ($self) use ($quantity) {
            $self->action   = TransactionType::ADD();
            $self->quantity = $quantity;
            $self->source   = LocationService::defaultAddSource();
            $self->require('quantity', 'lot', 'inventory', 'destination');
        });
    }

    public static function move(int $quantity): self
    {
        return tap(self::make(), function ($self) use ($quantity) {
            $self->action   = TransactionType::MOVE();
            $self->quantity = $quantity;
            $self->require('quantity', 'inventory', 'lot', 'source', 'destination');
        });
    }

    public static function purge(int $quantity): self
    {
        return tap(self::make(), function ($self) use ($quantity) {
            $self->action      = TransactionType::PURGE();
            $self->quantity    = $quantity;
            $self->destination = LocationService::defaultPurgeDestination();
            $self->require('quantity', 'lot', 'inventory', 'location');
        });
    }

    public static function onHand(Inventory $inventory): int
    {
        return $inventory->stock()->sum('quantity');
    }

    public static function onHandOfLot(Inventory $inventory, string|array $lot): int
    {
        return $inventory->stock()
            ->withLotNumbers($lot)
            ->sum('quantity');
    }

    private static function dispense(): void
    {
        // tbd
    }

    private static function consume(): void
    {
        // tbd
    }

    public function of(Inventory $inventory, int $lot = null): self
    {
        $this->inventory = $inventory;

        if ($lot) {
            $this->lot = $lot;
        }

        return $this;
    }

    public function into(Location $location): self
    {
        $this->destination = $location;

        return $this;
    }

    public function from(Location $location): self
    {
        $this->source = $location;

        return $this;
    }

    public function execute(): ?Stock
    {
        $this->validate();

        $method = 'perform' . Str::ucfirst($this->action);

        return $this->$method();
    }

    public static function rollback(Transactions|Batch $identifier): ?Stock
    {
        $batch = $identifier instanceof Batch
            ? $identifier
            : $identifier->batch;

        $method = self::resolveRollbackMethod($batch);

        return self::$method($batch);
    }

    protected static function resolveRollbackMethod(Batch $batch): string
    {
        $type = $batch->transactions()->first()->type;

        return 'rollback' . Str::ucfirst($type->value);
    }

    protected function performReceive(): ?Stock
    {
        $source      = LocationService::defaultReceivingSource();
        $destination = $this->destination ?? LocationService::defaultReceivingDestination();
        $stock       = Stock::make(['quantity' => $this->quantity]);
        $stock->inventory()->associate($this->inventory);
        $stock->location()->associate($destination);
        $stock->save();

        Transaction::record($this->action, $source, $destination, $stock, $this->quantity);

        return $stock;
    }

    protected function performAdd(): ?Stock
    {
        $stock = Stock::query()
            ->withLotNumbers($this->lot)
            ->ofInventory($this->inventory)
            ->inLocation($this->destination)
            ->first();

        if (is_null($stock)) {
            return null;
        }

        $stock->quantity += $this->quantity;
        $stock->save();

        Transaction::record($this->action, $this->source, $this->destination, $stock, $this->quantity);

        return $stock;
    }

    protected function performMove(): ?Stock
    {
        /** @var Stock $source_stock */
        $source_stock = $this->source->stock()
            ->withLotNumbers($this->lot)
            ->inLocation($this->source)
            ->firstOrFail();

        if ($source_stock->quantity === $this->quantity) {
            $source_stock->location()->associate($this->destination)->save();
            Transaction::record($this->action, $this->source, $this->destination, $source_stock, $this->quantity);

            return $source_stock;
        }

        $source_stock->quantity -= $this->quantity;
        $source_stock->save();

        $stock = Stock::firstOrNew([
            'lot' => $source_stock->lot,
            'location_id' => $this->destination->id,
            'inventory_id' => $this->inventory->id,
        ]);

        $stock->quantity = $this->quantity;
        $stock->save();

        Transaction::record($this->action, $this->source, $this->destination, $stock, $this->quantity);

        return $stock;
    }

    protected static function rollbackMove(Batch $batch): ?Stock
    {
        // use transactions to determine how to put things back
        // source <- was (to) transaction location
        // destination <- was (from) transaction location
        // reverse source and destination
        // use quantity to determine if we just swapped the location_id in stock
        // Also need to check if there are any dependant transactions that have happened since and abort...

        $timestamp = now();
        $batch->update(['reverted_at' => $timestamp]);
        $batch->transactions->each(fn($transaction) => $transaction->update(['reverted_at' => $timestamp]));

        /** @var Transactions $from_transaction */
        $from_transaction = $batch->transactions->firstWhere('direction', TransactionDirection::FROM);
        $to_transaction   = $batch->transactions->firstWhere('direction', TransactionDirection::TO);

        /** @var Stock $stock */
        $stock = $from_transaction->transactable;

        $transaction_quantity = $from_transaction->quantity;

        // Reverse locations
        /** @var Location $source */
        $source      = $to_transaction->location; // @todo - What if stock is not in this location??
        $destination = $from_transaction->location;

        $existing_stock = Stock::query()
            ->withLotNumbers($stock->lot)
            ->inLocation($destination)
            ->first();

        // exact quantity and no pre-existing stock conflict
        if ($stock->quantity === $transaction_quantity && is_null($existing_stock)) {
            $stock->location()->associate($destination);

            return $stock;
        }

        $stock->quantity -= $transaction_quantity;
        $stock->save();

        if (is_null($existing_stock)) {
            $existing_stock = Stock::make([
                'lot' => $stock->lot,
                'quantity' => $transaction_quantity,
            ]);
        }

        $existing_stock->inventory()->associate($stock->inventory);
        $existing_stock->location()->associate($destination);
        $existing_stock->save();

        return $existing_stock;
    }

    protected function performPurge(): ?Stock
    {
        $stock = Stock::query()
            ->withLotNumbers($this->lot)
            ->ofInventory($this->inventory)
            ->inLocation($this->source)
            ->first();

        if (is_null($stock)) {
            return null;
        }

        if ($stock->quantity === $this->quantity) {
            $stock->delete();
        } else {
            // Note: the location will go negative if the amount purged is greater than the quantity on hand
            $stock->quantity -= $this->quantity;
            $stock->save();
        }

        Transaction::record($this->action, $this->source, $this->destination, $stock, $this->quantity);

        return $stock;
    }

    protected function require(...$requirements): void
    {
        $this->requirements = $requirements;
    }

    protected function validate(): void
    {
        if (in_array('quantity', $this->requirements, true)) {
            if (is_null($this->quantity)) {
                throw new RuntimeException("Specifying a quantity is required to execute a {$this->action->value}");
            }

            if ($this->quantity < 1) {
                throw new RuntimeException("Cannot {$this->action->value} a quantity less than 1");
            }
        }

        if (in_array('lot', $this->requirements, true) && is_null($this->lot)) {
            throw new RuntimeException("Specifying a lot number is required to execute a {$this->action->value}");
        }

        if (in_array('stock', $this->requirements, true) && is_null($this->stock)) {
            throw new RuntimeException("Specifying stock is required to execute a {$this->action->value}");
        }

        if (in_array('inventory', $this->requirements, true) && is_null($this->inventory)) {
            throw new RuntimeException("Specifying inventory is required to execute a {$this->action->value}");
        }

        if (in_array('source', $this->requirements, true) && is_null($this->source)) {
            ray('no source');
            throw new RuntimeException("Specifying a source location is required to execute a {$this->action->value}");
        }

        if (in_array('destination', $this->requirements, true) && is_null($this->destination)) {
            throw new RuntimeException("Specifying a destination is required to execute a {$this->action->value}");
        }
    }
}
