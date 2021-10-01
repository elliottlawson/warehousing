<?php

namespace App\Services\Warehouse;

use App\Enums\TransactionType;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Stock;
use App\Traits\Makeable;
use RuntimeException;

class TransactionDTO
{
    use Makeable;

    public ?Inventory $inventory = null;
    public ?Location $source = null;
    public ?Location $destination = null;
    public ?Stock $stock = null;
    public ?int $quantity = null;
    public ?string $lot = null;
    public ?TransactionType $action = null;
    public array $requirements = [];

    public function require(...$requirements): void
    {
        $this->requirements = $requirements;
    }

    public function validate(): void
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
