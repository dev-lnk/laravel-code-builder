<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundBuilderException;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final readonly class BuildFactory
{
    public function __construct(
        private CodeStructure $codeStructure,
        private CodePath $codePath,

    ) {
    }

    /**
     * @throws FileNotFoundException
     * @throws NotFoundCodePathException
     * @throws NotFoundBuilderException
     */
    public function call(string $buildType, string $stub): void
    {
        match($buildType) {
            BuildType::MODEL => ModelBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            BuildType::ADD_ACTION => AddActionBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            BuildType::EDIT_ACTION => EditActionBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            BuildType::REQUEST => RequestBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            BuildType::CONTROLLER => ControllerBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            BuildType::ROUTE => RouteBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            BuildType::FORM => FormBuilder::make(
                $this->codeStructure,
                $this->codePath,
                $stub,
            )->build(),
            default => throw new NotFoundBuilderException()
        };
    }
}