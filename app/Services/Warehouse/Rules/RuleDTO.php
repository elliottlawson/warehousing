<?php

namespace App\Services\Warehouse\Rules;

use App\Models\Rule;
use App\Services\Warehouse\TransactionDTO;
use App\Traits\Makeable;

class RuleDTO
{
    use Makeable;

    public Rule $rule;

    public TransactionDTO $transaction;

    protected string $message;

    protected bool $passed;

    public function passed(): bool
    {
        return $this->passed;
    }

    public function setToPassed(): self
    {
        $this->passed = true;

        return $this;
    }

    public function failed(): bool
    {
        return ! $this->passed;
    }

    public function setToFailed(): self
    {
        $this->passed = false;

        return $this;
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function setMessageTo(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function setRuleTo(Rule $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function setTransactionTo(TransactionDTO $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }
}
