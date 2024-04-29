<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePathContract;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Support\Traits\Makeable;

abstract class AbstractBuilder implements BuilderContract
{
    use Makeable;

    public function __construct(
        protected CodeStructure $codeStructure,
        protected CodePathContract $path,
        protected ?string $onlyFlag,
        protected string $stubFile,
    ) {
    }
}