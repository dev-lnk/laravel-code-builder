<?php

namespace DevLnk\LaravelCodeBuilder\Enums;

interface BuildTypeContract
{
    public function value(): string;

    public function stub(): string;
}
