<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class RouteBuilder extends AbstractBuilder
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $routePath = $this->codePath->path(BuildType::ROUTE);
        $controllerPath = $this->codePath->path(BuildType::CONTROLLER);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($routePath->file(), [
                '{controller_namespace}' => $controllerPath->namespace() . '\\' . $controllerPath->rawName(),
                '{name}' => $routePath->rawName(),
                '{controller_name}' => $controllerPath->rawName(),
            ]);
    }
}