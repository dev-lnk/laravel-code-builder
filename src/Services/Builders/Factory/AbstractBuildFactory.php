<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Factory;

use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePathContract;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;

abstract readonly class AbstractBuildFactory
{
    public function __construct(
        protected CodeStructure $codeStructure,
        protected CodePathContract $codePath,
    ) {
    }

    abstract public function call(string $buildType, string $stub): void;
}
