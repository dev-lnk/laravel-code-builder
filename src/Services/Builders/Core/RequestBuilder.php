<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class RequestBuilder extends AbstractBuilder
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $requestPath = $this->codePath->path(BuildType::REQUEST->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($requestPath->file(), [
                '{namespace}' => $requestPath->namespace(),
                '{name}' => $requestPath->rawName(),
                '{rules}' => $this->codeStructure->columnsToRules(),
            ]);
    }
}
