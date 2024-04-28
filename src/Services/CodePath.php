<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services;

final class CodePath
{
    private string $modelDir;

    private string $modelNamespace;

    public function setModel(string $dir, string $namespace): self
    {
        $this->modelDir = $dir;
        $this->modelNamespace = $namespace;
        return $this;
    }

    public function modelDir(): string
    {
        return $this->modelDir;
    }

    public function modelNamespace(): string
    {
        return $this->modelNamespace;
    }
}