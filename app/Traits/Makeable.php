<?php

namespace App\Traits;

trait Makeable
{
    protected static function make(): self
    {
        return new static();
    }
}
