<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;

abstract readonly class AbstractPath implements CodePathContract
{
    public function __construct(
        private string $name,
        private string $dir,
        private string $namespace,
    ) {
    }

    abstract public function getBuildType(): BuildType;

    public function name(): string
    {
        return $this->name;
    }

    public function dir(): string
    {
        if(! is_dir($this->dir)) {
            mkdir($this->dir, recursive: true);
        }
        return $this->dir;
    }

    public function file(): string
    {
        return $this->dir() . '/' . $this->name;
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
