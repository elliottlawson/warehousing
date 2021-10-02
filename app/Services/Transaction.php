<?php

namespace App\Services;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Stock;
use App\Models\Transactions;
use App\Traits\Makeable;
use Illuminate\Support\Carbon;
use RuntimeException;

class Transaction
{
    use Makeable;

    protected ?Batch $old_batch;
    protected Batch $batch;
    protected TransactionType $type;
    protected Stock $source_stock;
    protected Stock $destination_stock;
    protected int $quantity;
    protected Carbon $timestamp;

    public function __construct()
    {
        $this->timestamp = now();

        $this->createBatch();
    }

    public static function record(
        TransactionType $type,
        int $quantity,
        Stock $source_stock,
        Stock $destination_stock,
        Batch $rollback_batch = null,
    ): Batch {
        $self = self::make();

        $self->type              = $type;
        $self->quantity          = $quantity;
        $self->old_batch         = $rollback_batch;
        $self->source_stock      = $source_stock;
        $self->destination_stock = $destination_stock;

        if ($type->is(TransactionType::ROLLBACK())) {
            if (is_null($self->old_batch)) {
                throw new RuntimeException('Batch is a required parameter for preforming a rollback');
            }

            $self->markPreviousBatchAsReverted();
        }

        $self->createTransactions();

        return $self->batch;
    }

    public function markPreviousBatchAsReverted(): void
    {
        $this->old_batch->update(['reverted_at' => $this->timestamp]);

        $this->old_batch->transactions->each->update(['reverted_at' => $this->timestamp]);

        $this->batch->reverted()->associate($this->old_batch);
    }

    private function createTransactions(): void
    {
        $this->createSourceTransaction();
        $this->createDestinationTransaction();
    }

    private function createSourceTransaction(): void
    {
        $this->createTransaction(TransactionDirection::FROM());
    }

    private function createDestinationTransaction(): void
    {
        $this->createTransaction(TransactionDirection::TO());
    }

    private function createTransaction(TransactionDirection $direction): void
    {
        $stock = $direction->isSource() ? $this->source_stock : $this->destination_stock;

        Transactions::make()
            ->fill([
                'type'        => $this->type,
                'direction'   => $direction,
                'quantity'    => $this->quantity,
                'location_id' => $stock->location_id,
                'batch_id'    => $this->batch->id,
                'created_at'  => $this->timestamp,
                'updated_at'  => $this->timestamp,
            ])
            ->transactable()->associate($stock)
            ->save();
    }

    private function createBatch(): void
    {
        $this->batch = Batch::create([
            'created_at' => $this->timestamp,
            'updated_at' => $this->timestamp,
        ]);
    }
}
