<?php

namespace App\Services\Warehouse\Rules;

use App\Models\Rule;
use App\Services\Warehouse\TransactionDTO;

abstract class AbstractRule implements RulesInterface
{
    protected RuleDTO $results;

    public function __construct()
    {
        $this->results = RuleDTO::make();
    }

    public function use(Rule $rule): self
    {
        $this->results->setRuleTo($rule); // @todo - Should we just use the dto to pass data around?

        return $this;
    }

    public function evaluate(TransactionDTO $transaction): RuleDTO
    {
        $this->results->setTransactionTo($transaction);

        $evaluation = $this->handle();

        if ($evaluation === false) {
            return $this->results
                ->setToFailed()
                ->setMessageTo($this->errorMessage());
        }

        return $this->results->setToPassed();
    }
}
