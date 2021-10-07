<?php

namespace App\Services\Warehouse;

use App\Models\Location;
use App\Models\Rule;
use App\Providers\LocationRuleServiceProvider;
use App\Services\Warehouse\Rules\RuleDTO;
use App\Services\Warehouse\Rules\RuleOutcomeDTO;
use App\Traits\Makeable;
use Illuminate\Support\Collection;

class RuleService
{
    use Makeable;

    protected Location $location;

    protected Collection $results;

    public static function for(Location $location): self
    {
        $self = self::make();

        $self->location = $location;

        return $self;
    }

    public function evaluate(ActionDTO $transaction): RuleOutcomeDTO
    {
        $rules = $this->location->rules;

        if (is_null($rules)) {
            return $this->allow();
        }

        $this->results = $rules->map(static function (Rule $rule) use ($transaction): RuleDTO {
            return LocationRuleServiceProvider::resolve($rule->type)
                ->use($rule)
                ->evaluate($transaction);
        });

        $success = $this->results->every(function (RuleDTO $result) {
            return $result->passed();
        });

        if (! $success) {
            return $this->deny();
        }

        return $this->allow();
    }

    public function allow(): RuleOutcomeDTO
    {
        return RuleOutcomeDTO::make()
            ->setResults($this->results)
            ->setSuccess(true);
    }

    public function deny(): RuleOutcomeDTO
    {
        return RuleOutcomeDTO::make()
            ->setResults($this->results)
            ->setSuccess(false);
    }
}
