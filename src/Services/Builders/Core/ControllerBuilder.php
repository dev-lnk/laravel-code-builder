<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\StubValue;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\ControllerBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class ControllerBuilder extends AbstractBuilder implements ControllerBuilderContract
{
    /**
     * @throws FileNotFoundException
     * @throws NotFoundCodePathException
     */
    public function build(): void
    {
        $controllerPath = $this->codePath->path(BuildType::CONTROLLER->value);
        $addActionPath = $this->codePath->path(BuildType::ADD_ACTION->value);
        $editActionPath = $this->codePath->path(BuildType::EDIT_ACTION->value);
        $requestPath = $this->codePath->path(BuildType::REQUEST->value);

        StubBuilder::make($this->stubFile)
            ->setKey(
                StubValue::USE_CONTROLLER->key(),
                StubValue::USE_CONTROLLER->value(),
                $controllerPath->namespace() !== 'App\\Http\\Controllers'
            )
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
