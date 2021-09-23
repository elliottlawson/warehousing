<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static DIVIDE()
 * @method static static MULTIPLY()
 */
final class OperatorType extends Enum
{
    public const DIVIDE = 'divide';
    public const MULTIPLY = 'multiply';
}
