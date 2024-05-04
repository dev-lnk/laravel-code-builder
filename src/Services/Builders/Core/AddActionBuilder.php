<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\AddActionBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class AddActionBuilder extends AbstractBuilder implements AddActionBuilderContract
{
    /**
     * @throws FileNotFoundException
     * @throws NotFoundCodePathException
     */
    public function build(): void
    {
        $actionPath = $this->codePath->path(BuildType::ADD_ACTION->value);
        $modelPath = $this->codePath->path(BuildType::MODEL->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($actionPath->file(), [
                '{namespace}' => $actionPath->namespace(),
                '{model_namespace}' => $modelPath->namespace() . '\\' . $modelPath->rawName(),
                '{name}' => $actionPath->rawName(),
                '{model_name}' => $modelPath->rawName(),
            ]);
    }
}
