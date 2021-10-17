<?php

namespace App\Services\Warehouse\Actions;

use App\Models\Batch;
use App\Models\Stock;
use App\Services\Warehouse\ActionDTO;

interface TransactionInterface
{
    public function handle(ActionDTO $data): Batch;

    public function setSourceStock(ActionDTO $data): Stock;

    public function setDestinationStock(ActionDTO $data): Stock;

    public function rollback(Batch $batch): Batch;
}
