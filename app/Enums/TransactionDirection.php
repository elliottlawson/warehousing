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

    public function isSource(): bool
    {
        return $this->isFrom();
    }

    public function isFrom(): bool
    {
        return $this->is(self::FROM);
    }

    public function isDestination(): bool
    {
        return $this->isTo();
    }

    public function isTo(): bool
    {
        return $this->is(self::TO);
    }
}
