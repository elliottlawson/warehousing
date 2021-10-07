<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Services\Warehouse\ActionDTO;

interface TransactionInterface
{
    public function handle(ActionDTO $data): Batch;

    public function rollback(Batch $batch): Batch;
}
