<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionType extends Enum
{
    public const ADD = 'add';
    public const CONSUME = 'consume';
    public const DELETE = 'delete';
    public const LOCK = 'lock';
    public const MOVE = 'move';
    public const PURGE = 'purge';
    public const RECEIVE = 'receive';
    public const ROLLBACK = 'rollback';
    public const TRANSFER = 'transfer';
}
