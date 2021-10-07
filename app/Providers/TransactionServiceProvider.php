<?php

namespace App\Providers;

use App\Enums\TransactionType;
use App\Services\Warehouse\Actions\Add;
use App\Services\Warehouse\Actions\Move;
use App\Services\Warehouse\Actions\Purge;
use App\Services\Warehouse\Actions\Receive;
use App\Services\Warehouse\Actions\TransactionInterface;

class TransactionServiceProvider
{
    // Maps the handling class to the appropriate Transaction Type
    private static array $actions = [
        TransactionType::ADD => Add::class,
        TransactionType::MOVE => Move::class,
        TransactionType::PURGE => Purge::class,
        TransactionType::RECEIVE => Receive::class,
    ];

    public static function resolve(TransactionType $action): TransactionInterface
    {
        return new self::$actions[$action->value]();
    }
}
