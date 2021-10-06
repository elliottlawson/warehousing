<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static QUANTITY_LIMITATION()
 * @method static static ITEM_LIMITATION()
 */
final class RuleType extends Enum
{
    const QUANTITY_LIMITATION = 'quantity_limitation';
    const ITEM_LIMITATION = 'item_limitation';
}
