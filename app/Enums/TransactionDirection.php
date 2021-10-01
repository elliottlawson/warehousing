<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static FROM()
 * @method static static TO()
 */
final class TransactionDirection extends Enum
{
    public const FROM = 'from';
    public const TO = 'to';
}
