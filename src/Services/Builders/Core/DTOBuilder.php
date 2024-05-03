<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class DTOBuilder extends AbstractBuilder
{
    /**
     * @throws FileNotFoundException
     * @throws NotFoundCodePathException
     */
    public function build(): void
    {
        $dtoPath = $this->codePath->path(BuildType::DTO->value);
        $modelPath = $this->codePath->path(BuildType::MODEL->value);
        $requestPath = $this->codePath->path(BuildType::REQUEST->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($dtoPath->file(), [
                '{namespace}' => $dtoPath->namespace(),
                '{use_model}' => $modelPath->namespace() . '\\' . $modelPath->rawName(),
                '{use_request}' => $requestPath->namespace() . '\\' . $requestPath->rawName(),
                '{name}' => $dtoPath->rawName(),
                '{properties}' => $this->codeStructure->columnsToDTO(),
                '{array_inputs}' => $this->codeStructure->columnsToArrayDTO(),
                '{request_name}' => $requestPath->rawName(),
                '{request_inputs}' => $this->codeStructure->columnsToRequestDTO(),
                '{model_name}' => $modelPath->rawName(),
                '{model_inputs}' => $this->codeStructure->columnsToModelDTO(),
                '{getters}' => $this->codeStructure->columnsToGettersDTO(),
                '{to_array}' => $this->codeStructure->columnsFromArrayDTO(),
            ]);
    }
}
