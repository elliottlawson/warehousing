<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Models\Stock;
use App\Services\Warehouse\TransactionDTO;

interface TransactionInterface
{
    public function handle(TransactionDTO $data): Stock;

    public function rollback(Batch $batch): Batch;
}
