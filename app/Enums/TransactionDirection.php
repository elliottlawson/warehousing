<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionDirection extends Enum
{
    public const FROM = 'from';
    public const TO = 'to';
}
