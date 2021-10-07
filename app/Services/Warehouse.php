<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Providers\TransactionServiceProvider;
use App\Services\Warehouse\RuleService;
use App\Services\Warehouse\ActionDTO;
use App\Services\Warehouse\TransactionDTO;
use App\Traits\Makeable;

class Warehouse
{
    use Makeable;

    protected ActionDTO $data;
    protected bool $checksHaveFailed;
    protected TransactionDTO $transactionDTO;

    public function __construct()
    {
        $this->data = ActionDTO::make();
        $this->transactionDTO = TransactionDTO::make();
    }

    public static function receive(int $quantity): self
    {
        return tap(self::make(), static function (self $self) use ($quantity) {
            $self->data->action = TransactionType::RECEIVE();
            $self->data->quantity = $quantity;
            $self->data->require('quantity', 'inventory');
        });
    }

    public static function add(int $quantity): self
    {
        return tap(self::make(), static function (self $self) use ($quantity) {
            $self->data->action = TransactionType::ADD();
            $self->data->quantity = $quantity;
            $self->data->source = LocationService::defaultAddSource();
            $self->data->require('quantity', 'lot', 'inventory', 'destination');
        });
    }

    public static function move(int $quantity): self
    {
        return tap(self::make(), static function (self $self) use ($quantity) {
            $self->data->action = TransactionType::MOVE();
            $self->data->quantity = $quantity;
            $self->data->require('quantity', 'inventory', 'lot', 'source', 'destination');
        });
    }

    public static function purge(int $quantity): self
    {
        return tap(self::make(), static function (self $self) use ($quantity) {
            $self->data->action = TransactionType::PURGE();
            $self->data->quantity = $quantity;
            $self->data->destination = LocationService::defaultPurgeDestination();
            $self->data->require('quantity', 'lot', 'inventory', 'location');
        });
    }

    public static function onHand(Inventory $inventory): int
    {
        return $inventory->stock()->sum('quantity');
    }

    public static function onHandOfLot(Inventory $inventory, string|array $lot): int
    {
        return $inventory->stock()
            ->hasLotNumbers($lot)
            ->sum('quantity');
    }

    public function of(Inventory $inventory, int $lot = null): self
    {
        $this->data->inventory = $inventory;

        if ($lot) {
            $this->data->lot = $lot;
        }

        return $this;
    }

    public function into(Location $location): self
    {
        $this->data->destination = $location;

        return $this;
    }

    public function from(Location $location): self
    {
        $this->data->source = $location;

        return $this;
    }

    public function execute(): TransactionDTO
    {
        $this->data->validate();

        $this->runChecks();

        $this->transactionDTO->action = $this->data->action;

        if ($this->checksHaveFailed) {
            $this->transactionDTO->success = false;

            return $this->transactionDTO;
        }

        $batch = TransactionServiceProvider::resolve($this->data->action)->handle($this->data);

        $this->transactionDTO->batch = $batch;

        return $this->transactionDTO;
    }

    public static function rollback(Batch $batch): Batch
    {
        return TransactionServiceProvider::resolve($batch->sourceTransaction()->type)->rollback($batch);
    }

    protected function runChecks(): void
    {
        $this->transactionDTO->rulesOutcome = RuleService::for($this->data->destination)->evaluate($this->data);
        $this->transactionDTO->success = $this->transactionDTO->rulesOutcome->success;

        $this->checksHaveFailed = ! $this->transactionDTO->success;
    }
}
