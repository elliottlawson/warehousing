<?php

namespace App\Services\Warehouse\Rules;

use App\Traits\Makeable;
use Illuminate\Support\Collection;

class RuleOutcomeDTO
{
    use Makeable;

    public bool $success;

    /** @var Collection<RuleDTO> */
    public Collection $results;

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function setResults(Collection $results): self
    {
        $this->results = $results;

        return $this;
    }
}
