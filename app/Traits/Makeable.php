<?php

namespace App\Traits;

trait Makeable
{
    public static function make(): self
    {
        return new static();
    }
}
