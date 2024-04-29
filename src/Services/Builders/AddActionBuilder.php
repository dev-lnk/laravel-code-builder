<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class AddActionBuilder extends AbstractBuilder
{
    /**
     * @throws FileNotFoundException
     * @throws NotFoundCodePathException
     */
    public function build(): void
    {
        $actionPath = $this->codePath->path(BuildType::ADD_ACTION);
        $modelPath = $this->codePath->path(BuildType::MODEL);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($actionPath->file(), [
                '{namespace}' => $actionPath->namespace(),
                '{model_namespace}' => $modelPath->namespace() . '\\' . $modelPath->rawName(),
                '{name}' => $this->codeStructure->entity()->ucFirstSingular() . 'Action',
                '{model_name}' => $modelPath->rawName(),
            ]);
    }
}