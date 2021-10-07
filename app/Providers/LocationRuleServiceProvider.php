<?php

namespace App\Providers;

use App\Enums\RuleType;
use App\Services\Warehouse\Rules\AbstractRule;
use App\Services\Warehouse\Rules\QuantityLimitation;

class LocationRuleServiceProvider
{
    // Maps the handling class to the appropriate Rule Type
    private static array $rules = [
        RuleType::QUANTITY_LIMITATION => QuantityLimitation::class,
    ];

    public static function resolve(RuleType $rule): AbstractRule
    {
        return new self::$rules[$rule->value]();
    }
}
