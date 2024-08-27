<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Factory;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundBuilderException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Advanced\Contracts\TypeScriptContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\AddActionBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\ControllerBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\DTOBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\EditActionBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\FormBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\ModelBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\RequestBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\RouteBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\TableBuilderContract;

final readonly class BuildFactory extends AbstractBuildFactory
{
    /**
     * @throws NotFoundBuilderException
     */
    public function call(string $buildType, string $stub): void
    {
        $classParameters = [
            'codeStructure' => $this->codeStructure,
            'codePath' => $this->codePath,
            'stubFile' => $stub,
        ];

        /**
         * @var AbstractBuilder $builder
         */
        $builder = match($buildType) {
            BuildType::MODEL->value => app(
                ModelBuilderContract::class,
                $classParameters
            ),
            BuildType::ADD_ACTION->value => app(
                AddActionBuilderContract::class,
                $classParameters
            ),
            BuildType::EDIT_ACTION->value => app(
                EditActionBuilderContract::class,
                $classParameters
            ),
            BuildType::REQUEST->value => app(
                RequestBuilderContract::class,
                $classParameters
            ),
            BuildType::CONTROLLER->value => app(
                ControllerBuilderContract::class,
                $classParameters
            ),
            BuildType::ROUTE->value => app(
                RouteBuilderContract::class,
                $classParameters
            ),
            BuildType::FORM->value => app(
                FormBuilderContract::class,
                $classParameters
            ),
            BuildType::DTO->value => app(
                DTOBuilderContract::class,
                $classParameters
            ),
            BuildType::TABLE->value => app(
                TableBuilderContract::class,
                $classParameters
            ),
            BuildType::TYPE_SCRIPT->value => app(
                TypeScriptContract::class,
                $classParameters
            ),
            default => throw new NotFoundBuilderException()
        };

        $builder->build();
    }
}
