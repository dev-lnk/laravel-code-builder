<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\AddActionPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\ControllerPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\DTOPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\EditActionPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\FormPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\ModelPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\RequestPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\RoutePath;

final class CodePath
{
    /**
     * @var array<string, CodePathContract>
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
        if(isset($this->paths[BuildType::MODEL->value])) {
            return $this;
        }
        $this->paths[BuildType::MODEL->value] = new ModelPath($name, $dir, $namespace);

        return $this;
    }

    public function addAction(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::ADD_ACTION->value])) {
            return $this;
        }
        $this->paths[BuildType::ADD_ACTION->value] = new AddActionPath($name, $dir, $namespace);

        return $this;
    }

    public function editAction(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::EDIT_ACTION->value])) {
            return $this;
        }
        $this->paths[BuildType::EDIT_ACTION->value] = new EditActionPath($name, $dir, $namespace);

        return $this;
    }

    public function request(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::REQUEST->value])) {
            return $this;
        }
        $this->paths[BuildType::REQUEST->value] = new RequestPath($name, $dir, $namespace);

        return $this;
    }

    public function controller(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::CONTROLLER->value])) {
            return $this;
        }
        $this->paths[BuildType::CONTROLLER->value] = new ControllerPath($name, $dir, $namespace);

        return $this;
    }

    public function route(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::ROUTE->value])) {
            return $this;
        }
        $this->paths[BuildType::ROUTE->value] = new RoutePath($name, $dir, $namespace);

        return $this;
    }

    public function form(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::FORM->value])) {
            return $this;
        }
        $this->paths[BuildType::FORM->value] = new FormPath($name, $dir, $namespace);

        return $this;
    }

    public function dto(string $name, string $dir, string $namespace): self
    {
        if(isset($this->paths[BuildType::DTO->value])) {
            return $this;
        }
        $this->paths[BuildType::DTO->value] = new DTOPath($name, $dir, $namespace);

        return $this;
    }
}
