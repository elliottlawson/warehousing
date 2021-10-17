<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ADD()
 * @method static static CONSUME()
 * @method static static DELETE()
 * @method static static LOCK()
 * @method static static MOVE()
 * @method static static PURGE()
 * @method static static RECEIVE()
 * @method static static ROLLBACK()
 * @method static static TRANSFER()
 */
final class TransactionType extends Enum
{
    public const ADD      = 'add';
    public const CONSUME  = 'consume';
    public const DELETE   = 'delete';
    public const LOCK     = 'lock';
    public const MOVE     = 'move';
    public const PURGE    = 'purge';
    public const RECEIVE  = 'receive';
    public const ROLLBACK = 'rollback';
    public const TRANSFER = 'transfer';
}
