<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\RouteBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class RouteBuilder extends AbstractBuilder implements RouteBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $routePath = $this->codePath->path(BuildType::ROUTE->value);
        $controllerPath = $this->codePath->path(BuildType::CONTROLLER->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($routePath->file(), [
                '{controller_namespace}' => $controllerPath->namespace() . '\\' . $controllerPath->rawName(),
                '{entity_plural}' => $this->codeStructure->entity()->plural(),
                '{controller_name}' => $controllerPath->rawName(),
            ]);
    }
}
