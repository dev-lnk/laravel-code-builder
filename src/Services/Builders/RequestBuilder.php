<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class RequestBuilder extends AbstractBuilder
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $requestPath = $this->codePath->path(BuildType::REQUEST);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($requestPath->file(), [
                '{namespace}' => $requestPath->namespace(),
                '{name}' => $requestPath->rawName(),
                '{rules}' => $this->codeStructure->columnsToRules(),
            ]);
    }
}