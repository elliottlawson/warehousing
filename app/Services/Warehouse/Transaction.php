<?php

namespace App\Services\Warehouse;

use App\Models\Batch;
use App\Models\Stock;

interface Transaction
{
    public function handle(TransactionDTO $data): Stock;

    public function rollback(Batch $batch): Batch;
}
