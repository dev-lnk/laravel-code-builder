<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

abstract readonly class AbstractPath implements CodePathContract
{
    public function __construct(
        private string $name,
        private string $dir,
        private string $namespace,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function file(): string
    {
        return $this->dir . '/' . $this->name;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function rawName(): string
    {
        return str_replace('.php', '', $this->name());
    }
}