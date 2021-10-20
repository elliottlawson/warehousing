<?php

namespace App\Services\Warehouse;

use App\Models\Stock;
use App\Traits\Makeable;

class StockTransactionService
{
    use Makeable;

    protected Stock $source;

    protected Stock $destination;

    protected int $quantity;

    public static function transfer(int $quantity): self
    {
        $self = self::make();

        $self->quantity = $quantity;

        return $self;
    }

    public function from(Stock $stock): self
    {
        $this->source = $stock;

        return $this;
    }

    public function to(Stock $stock): void
    {
        $this->destination = $stock;

        $this->execute();
    }

    protected function execute(): void
    {
        $this->source->subtract($this->quantity);
        $this->destination->add($this->quantity);
    }
}
