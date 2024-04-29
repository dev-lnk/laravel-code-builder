<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Types\BuildType;

final class CodePath
{
    /**
     * @var array<int, CodePathContract>
     */
    private array $paths = [];

    /**
     * @throws NotFoundCodePathException
     */
    public function path(string $alias): CodePathContract
    {
        return $this->paths[$alias] ?? throw new NotFoundCodePathException();
    }

    public function model(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::MODEL])) {
            return $this;
        }
        $this->paths[BuildType::MODEL] = new ModelPath($name, $dir, $namespace);
        return $this;
    }

    public function addAction(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::ADD_ACTION])) {
            return $this;
        }
        $this->paths[BuildType::ADD_ACTION] = new AddActionPath($name, $dir, $namespace);
        return $this;
    }

    public function editAction(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::EDIT_ACTION])) {
            return $this;
        }
        $this->paths[BuildType::EDIT_ACTION] = new EditActionPath($name, $dir, $namespace);
        return $this;
    }
}