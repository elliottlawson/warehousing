<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Location;
use App\Providers\TransactionServiceProvider;
use App\Services\Warehouse\ActionDTO;
use App\Services\Warehouse\Rules\RuleOutcomeDTO;
use App\Services\Warehouse\RuleService;
use App\Services\Warehouse\TransactionDTO;
use App\Traits\Makeable;

class Warehouse
{
    use Makeable;

    protected ActionDTO $data;
    protected bool $checksHaveFailed;
    protected TransactionDTO $transactionDTO;
    protected RuleOutcomeDTO $ruleOutcome;
    protected ?Batch $batch = null;

    public function __construct()
    {
        $this->data = ActionDTO::make();
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

    public static function onHandOfLotInLocation(Inventory $inventory, string|array $lot, Location $location): int
    {
        return $inventory->stock()
            ->hasLotNumbers($lot)
            ->inLocation($location)
            ->sum('quantity');
    }

    public function of(Inventory $inventory, string|int $lot = null): self
    {
        $this->data->inventory = $inventory;

        if ($lot) {
            $this->data->lot = (string) $lot;
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

        if ($this->runChecks()) {
            return $this->buildResponse(); // make abort method
        }

        $this->batch = TransactionServiceProvider::resolve($this->data->action)->handle($this->data);

        return $this->buildResponse();
    }

    public static function rollback(Batch $batch): Batch
    {
        return TransactionServiceProvider::resolve($batch->sourceTransaction()->type)->rollback($batch);
    }

    protected function runChecks(): bool
    {
        // @todo - this is bad! fixme please
        if ($this->data->action->is(TransactionType::RECEIVE)) {
            $this->ruleOutcome = RuleOutcomeDTO::make()->setSuccess(true);

            return false;
        }
        $this->ruleOutcome = RuleService::for($this->data->destination)->evaluate($this->data);

        return ! $this->ruleOutcome->success;
    }

    protected function buildResponse(): TransactionDTO
    {
        $response = TransactionDTO::make();

        $response->transaction = $this->data;
        $response->batch = $this->batch;
        $response->rulesOutcome = $this->ruleOutcome;
        $response->success = $this->ruleOutcome->success;

        return $response;
    }
}
