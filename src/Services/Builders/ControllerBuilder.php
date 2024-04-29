<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class ControllerBuilder extends AbstractBuilder
{

    /**
     * @throws FileNotFoundException
     * @throws NotFoundCodePathException
     */
    public function build(): void
    {
        $controllerPath = $this->codePath->path(BuildType::CONTROLLER);
        $addActionPath = $this->codePath->path(BuildType::ADD_ACTION);
        $editActionPath = $this->codePath->path(BuildType::EDIT_ACTION);
        $requestPath = $this->codePath->path(BuildType::REQUEST);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($controllerPath->file(), [
                '{namespace}' => $controllerPath->namespace(),
                '{add_action_namespace}' => $addActionPath->namespace() . '\\' . $addActionPath->rawName(),
                '{edit_action_namespace}' => $editActionPath->namespace() . '\\' . $editActionPath->rawName(),
                '{request_namespace}' => $requestPath->namespace() . '\\' . $requestPath->rawName(),
                '{name}' => $controllerPath->rawName(),
                '{request_name}' => $requestPath->rawName(),
                '{add_action_name}' => $addActionPath->rawName(),
                '{edit_action_name}' => $editActionPath->rawName(),
            ]);
    }
}