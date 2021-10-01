<?php

namespace App\Services\Warehouse;

use App\Enums\TransactionType;

class TransactionServiceProvider
{
    // Maps the handling class to the appropriate Transaction Type
    private static array $actions = [
        TransactionType::ADD     => Add::class,
        TransactionType::MOVE    => Move::class,
        TransactionType::PURGE   => Purge::class,
        TransactionType::RECEIVE => Receive::class,
    ];

    public static function resolve(TransactionType $action): Transaction
    {
        return new self::$actions[$action->value]();
    }
}
