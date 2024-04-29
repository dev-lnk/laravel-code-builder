<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Support\Traits;

trait Makeable
{
    public static function make(mixed ...$arguments): static
    {
        return new static(...$arguments);
    }
}