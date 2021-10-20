<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static QUANTITY_LIMITATION()
 * @method static static ITEM_LIMITATION()
 */
final class RuleType extends Enum
{
    public const QUANTITY_LIMITATION = 'quantity_limitation';
    public const ITEM_LIMITATION = 'item_limitation';
}
