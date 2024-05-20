<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;

interface CodePathContract
{
    public function initPaths(CodeStructure $codeStructure, string $generationPath, bool $isGenerationDir): void;

    public function setPath(AbstractPathItem $path): CodePathContract;

    public function path(string $alias): CodePathItemContract;
}
